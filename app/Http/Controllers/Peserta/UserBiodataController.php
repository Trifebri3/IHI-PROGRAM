<?php

namespace App\Http\Controllers\Peserta; // WAJIB ADA 'Peserta'

use App\Http\Controllers\Controller; // WAJIB IMPORT INI
use App\Models\BiodataField;
use App\Models\UserBiodataValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserBiodataController extends Controller
{
public function create()
{
    $fields = BiodataField::orderBy('id')->get();

    // Ambil data yang sudah ada
    $existingValues = UserBiodataValue::where('user_id', Auth::id())
                        ->pluck('value', 'biodata_field_id')
                        ->toArray();

    // 🔥 FIX: Pastikan semua field yang ada di DB masuk ke dalam array existingValues
    // Jika ada field baru yang belum diisi, kita biarkan null agar validasi 'required' berjalan
    foreach ($fields as $field) {
        if (!isset($existingValues[$field->id])) {
            $existingValues[$field->id] = null;
        }
    }

    return view('biodata.create', compact('fields', 'existingValues'));
}

public function store(Request $request)
{
    $fields = BiodataField::all();
    $rules = [];
    $userId = Auth::id();

    foreach ($fields as $field) {
        $key = "biodata.{$field->id}";

        // CEK APAKAH DATA SUDAH ADA DI DB
        $existingValue = UserBiodataValue::where('user_id', $userId)
                                         ->where('biodata_field_id', $field->id)
                                         ->value('value');

        // LOGIKA VALIDASI:
        // Wajib 'required' hanya jika: Field wajib DIISI DAN (Belum ada data lama ATAU sedang upload file baru)
        // Kita gunakan 'nullable' jika data sudah ada.
        $rules[$key] = ($field->is_required && empty($existingValue)) ? 'required' : 'nullable';

        if ($field->type === 'file') {
            // Validasi file hanya dilakukan JIKA user benar-benar mengupload file baru
            if ($request->hasFile($key)) {
                $rules[$key] .= '|file|mimes:jpg,png,pdf|max:2048';
            }
        }
    }

    $request->validate($rules);

    DB::transaction(function () use ($request, $fields, $userId) {
        foreach ($fields as $field) {
            $key = "biodata.{$field->id}";

            // JIKA USER UPLOAD FILE BARU
            if ($request->hasFile($key)) {
                // Hapus file lama jika ada
                $oldValue = UserBiodataValue::where('user_id', $userId)
                                           ->where('biodata_field_id', $field->id)
                                           ->value('value');
                if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                    Storage::disk('public')->delete($oldValue);
                }

                $value = $request->file($key)->store('biodata_files', 'public');

                UserBiodataValue::updateOrCreate(
                    ['user_id' => $userId, 'biodata_field_id' => $field->id],
                    ['value' => $value]
                );
            }
            // JIKA BUKAN FILE DAN ADA INPUTAN (TEXT/SELECT/DATE)
            elseif ($request->filled($key)) {
                UserBiodataValue::updateOrCreate(
                    ['user_id' => $userId, 'biodata_field_id' => $field->id],
                    ['value' => $request->input($key)]
                );
            }
            // JIKA TIDAK ADA FILE DAN TIDAK ADA INPUTAN, JANGAN LAKUKAN APA-APA (BIARKAN DATA LAMA)
        }
    });

    return redirect()->route('dashboard')->with('success', 'Biodata berhasil disimpan!');
}
}
