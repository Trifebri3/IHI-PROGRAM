<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Models\BiodataField;
use App\Models\UserBiodataValue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class IdentitasUserController extends Controller
{
    /**
     * Menampilkan data identitas lengkap user (Fitur LIHAT & Form EDIT)
     */
    public function index()
    {
        // Mengambil data user yang sedang login
        $user = Auth::user();

        // Mengambil data profil dan alamat terkait user tersebut
        $profile = UserProfile::where('user_id', $user->id)->first();
        $address = Address::where('user_id', $user->id)->first();

        // Mengambil seluruh struktur biodata dinamis beserta nilainya (jika sudah diisi sebelumnya)
        $biodataFields = BiodataField::all()->map(function ($field) use ($user) {
            $userValue = UserBiodataValue::where('user_id', $user->id)
                ->where('biodata_field_id', $field->id)
                ->first();

            // Menyisipkan nilai masukan user ke dalam objek field agar mudah dirender di View
            $field->user_value = $userValue ? $userValue->value : null;
            return $field;
        });

        // Mengembalikan ke halaman view (contoh: resources/views/identitas/index.blade.php)
        return view('identitas.index', compact('user', 'profile', 'address', 'biodataFields'));
    }

    /**
     * Menyimpan dan memperbarui perubahan data identitas secara menyeluruh (Fitur EDIT LENGKAP)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Aturan Validasi Dasar untuk Tabel Utama, Profil, dan Alamat
        $rules = [
            // Tabel: users
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,

            // Tabel: user_profiles
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Tabel: addresses
            'negara' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'kampung' => 'required|string|max:255',
            'detail_alamat' => 'nullable|string',
        ];

        // 2. Validasi Dinamis Menyesuaikan Aturan 'is_required' dari Tabel biodata_fields
        $biodataFields = BiodataField::all();
        foreach ($biodataFields as $field) {
            $rule = $field->is_required ? 'required' : 'nullable';
            if ($field->type === 'file') {
                $hasFileUploaded = UserBiodataValue::where('user_id', $user->id)
                    ->where('biodata_field_id', $field->id)
                    ->whereNotNull('value')
                    ->exists();
                
                if ($hasFileUploaded) {
                    $rule = 'nullable';
                } else {
                    $rule = $field->is_required ? 'required|file|max:2048' : 'nullable|file|max:2048';
                }
            }
            $rules['biodata.' . $field->id] = $rule;
        }

        // Jalankan proses validasi masukan formulir
        $validatedData = $request->validate($rules);

        // Menggunakan Database Transaction untuk memastikan semua data aman dan mencegah partial-save jika terjadi error di tengah jalan
        DB::beginTransaction();

        try {
            // A. Update Tabel: users
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // B. Update / Create Tabel: user_profiles (Penanganan Unggah Foto Profil)
            $profileData = [];
            if ($request->hasFile('profile_photo')) {
                // Ambil profil lama untuk cek kelayakan hapus berkas lama dari storage
                $existingProfile = UserProfile::where('user_id', $user->id)->first();
                if ($existingProfile && $existingProfile->profile_photo_path) {
                    Storage::disk('public')->delete($existingProfile->profile_photo_path);
                }

                // Simpan berkas foto baru ke direktori 'profiles' pada disk public
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $profileData['profile_photo_path'] = $path;
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            // C. Update / Create Tabel: addresses
            Address::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'negara' => $request->negara,
                    'provinsi' => $request->provinsi,
                    'kabupaten' => $request->kabupaten,
                    'kecamatan' => $request->kecamatan,
                    'desa' => $request->desa,
                    'kampung' => $request->kampung,
                    'detail_alamat' => $request->detail_alamat,
                ]
            );

            // D. Update / Create Tabel Nilai Dinamis: user_biodata_values
            foreach ($biodataFields as $field) {
                $value = null;
                if ($field->type === 'file') {
                    if ($request->hasFile("biodata.{$field->id}")) {
                        // Delete old file if exists
                        $existingVal = UserBiodataValue::where('user_id', $user->id)
                            ->where('biodata_field_id', $field->id)
                            ->first();
                        if ($existingVal && $existingVal->value) {
                            Storage::disk('public')->delete($existingVal->value);
                        }
                        
                        $path = $request->file("biodata.{$field->id}")->store('biodata_files', 'public');
                        $value = $path;
                    } else {
                        // Retain existing file if not re-uploaded
                        $existingVal = UserBiodataValue::where('user_id', $user->id)
                            ->where('biodata_field_id', $field->id)
                            ->first();
                        $value = $existingVal ? $existingVal->value : null;
                    }
                } else {
                    $value = $request->input("biodata.{$field->id}");
                }

                UserBiodataValue::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'biodata_field_id' => $field->id
                    ],
                    [
                        'value' => $value
                    ]
                );
            }

            // Jika semua operasi sukses, simpan permanen ke database
            DB::commit();

            return redirect()->back()->with('success', 'Identitas lengkap Anda berhasil diperbarui!');

        } catch (\Exception $e) {
            // Jika terjadi kegagalan sistem, batalkan seluruh perubahan yang sempat masuk
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui identitas: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Memperbarui Password User (Fitur Ubah Password)
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Jika user wajib ganti password (karena mitigasi/auto-password)
        if ($user->must_change_password) {
            $request->validate([
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ], [
                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            ]);

            $user->update([
                'password' => Hash::make($request->password),
                'must_change_password' => false,
            ]);

            return redirect()->route('dashboard')->with('success', 'Password Anda berhasil diperbarui! Silakan lanjutkan mengakses dashboard.');
        }

        // Validasi input password biasa
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Cek apakah password lama yang dimasukkan sesuai dengan yang ada di database
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success_password', 'Password Anda berhasil diperbarui!');
    }

}
