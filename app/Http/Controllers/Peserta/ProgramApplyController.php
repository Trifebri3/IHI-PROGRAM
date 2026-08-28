<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramStage;
use App\Models\Registration;
use App\Models\RegistrationStageData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramApplyController extends Controller
{
    public function index(Request $request)
    {
        // --- FEATURE: ENGINE FILTER & SORTING NATIVE ---
        $query = Program::where('status', 'published');

        // Filter berdasarkan pencarian nama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting Option
      // --- GANTI TOTAL MENJADI INI: ---
if ($request->sort === 'soonest') {
    $query->orderBy('end_date', 'asc');
} elseif ($request->sort === 'latest') {
    $query->orderBy('created_at', 'desc');
} else {
    $query->orderBy('id', 'desc'); // Default urut berdasarkan ID data terbaru (Aman & Gacor!)
}

        $activePrograms = $query->get();

        // Ambil data registrasi user lengkap dengan log stage datanya agar bisa di-render di Blade
        $userRegistrations = Registration::with(['stageData', 'currentStage'])
            ->where('user_id', auth()->id())
            ->get()
            ->keyBy('program_id');

        return view('pesertabiasa.program.index', compact('activePrograms', 'userRegistrations'));
    }

    public function showApply($id)
    {
        $program = Program::findOrFail($id);

        // Cek apakah pendaftaran sudah tutup
        $isClosed = !$program->is_open;
        if ($isClosed) {
            return redirect()->route('programs.catalog')->with('error', 'Pendaftaran untuk program ini telah ditutup.');
        }

        // Cari data pendaftaran existing milik user
        $registration = Registration::where('user_id', auth()->id())->where('program_id', $id)->first();

        // JIKA BELUM PERNAH DAFTAR: Buka Tahapan Urutan Ke-1 (Sequence 1)
        if (!$registration) {
            $currentStage = ProgramStage::where('program_id', $id)->where('sequence', 1)->first();
            if (!$currentStage) {
                return redirect()->route('programs.catalog')->with('error', 'Pendaftaran belum siap. Panitia belum menyusun kuesioner awal!');
            }
            $previousValues = collect();
            $stageData = null;
            $isStageLocked = (bool)$currentStage->is_locked;
            return view('pesertabiasa.program.apply', compact('program', 'currentStage', 'registration', 'previousValues', 'stageData', 'isStageLocked'));
        }

        // JIKA SUDAH DAFTAR TAPI STATUSNYA GAGAL ATAU LULUS FINAL: Kunci Akses Form
        if ($registration->status !== 'process') {
            return redirect()->route('programs.catalog')->with('error', 'Siklus pendaftaran Anda untuk program ini telah selesai.');
        }

        // JIKA SEDANG BERPROSES: Ambil tahapan aktif yang ditunjuk oleh `current_stage_id`
        $currentStage = ProgramStage::where('program_id', $id)->findOrFail($registration->current_stage_id);
        $isStageLocked = (bool)$currentStage->is_locked;

        // Cek apakah user sudah mengirim jawaban untuk tahap aktif ini?
        $stageData = RegistrationStageData::where('registration_id', $registration->id)
            ->where('program_stage_id', $currentStage->id)
            ->first();

        // Kunci jika sudah kirim DAN statusnya bukan 'failed', 'revision', atau 'draft' (artinya 'pending' atau 'passed')
        if ($stageData && !empty($stageData->form_values) && !in_array($stageData->status, ['failed', 'revision', 'draft'])) {
            return redirect()->route('programs.catalog')->with('error', 'Anda sudah mengirimkan berkas untuk ' . $currentStage->name . '. Mohon tunggu penilaian panitia!');
        }

        // Ambil data jawaban lama untuk perbaikan/revisi/draf
        $previousValues = $stageData ? collect($stageData->form_values)->keyBy('field_name') : collect();

        return view('pesertabiasa.program.apply', compact('program', 'currentStage', 'registration', 'previousValues', 'stageData', 'isStageLocked'));
    }

    public function submitApply(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        // Cek apakah pendaftaran sudah tutup
        if (now()->gt($program->end_date)) {
            return redirect()->back()->with('error', 'Pendaftaran program ini sudah ditutup.');
        }

        $registration = Registration::where('user_id', auth()->id())
            ->where('program_id', $id)
            ->first();

        if ($registration && $registration->status !== 'process') {
            return redirect()->route('programs.catalog')->with('error', 'Siklus pendaftaran Anda untuk program ini telah selesai.');
        }

        $currentStage = ProgramStage::where('program_id', $id)->findOrFail($registration ? $registration->current_stage_id : ProgramStage::where('program_id', $id)->orderBy('sequence')->firstOrFail()->id);

        if ($currentStage->is_locked) {
            return redirect()->back()->with('error', 'Tahapan ini belum dibuka atau sedang dikunci oleh panitia.');
        }

        $stageData = $registration ? RegistrationStageData::where('registration_id', $registration->id)
            ->where('program_stage_id', $currentStage->id)
            ->first() : null;

        $previousValues = $stageData ? collect($stageData->form_values)->keyBy('field_name') : collect();
        $formSchema = $currentStage->form_schema ?? [];

        // Auto-prepend protocol for URL fields before validation
        foreach ($formSchema as $index => $field) {
            $fieldName = "field_" . $index;
            if ($field['type'] === 'url' && $request->filled($fieldName)) {
                $val = trim($request->input($fieldName));
                if ($val && !preg_match('/^https?:\/\//i', $val)) {
                    $request->merge([$fieldName => 'https://' . $val]);
                }
            }
        }

        // --- VALIDASI FORM DINAMIS ---
        $rules = [];
        $messages = [];

        // Validasi harapan & motivasi jika pendaftaran baru (tahap awal)
        if (!$registration) {
            $rules['motivation'] = 'required|string|min:10|max:2000';
            $messages['motivation.required'] = 'Harapan & Motivasi wajib diisi untuk mengikuti program ini!';
            $messages['motivation.min'] = 'Harapan & Motivasi minimal berisi 10 karakter!';
        }

        foreach ($formSchema as $index => $field) {
            $fieldName = "field_" . $index;
            
            // Bila berkas sudah pernah diunggah sebelumnya, maka pengunggahan baru bersifat opsional (tidak wajib)
            $hasPreviousFile = $previousValues->has($field['name']) && !empty($previousValues->get($field['name'])['value']);
            $isFieldRequired = $field['required'] && !$hasPreviousFile;
            
            if ($field['type'] === 'checkbox') {
                $rules[$fieldName] = $isFieldRequired ? 'required|array|min:1' : 'nullable|array';
            } else {
                $rules[$fieldName] = $isFieldRequired ? 'required' : 'nullable';
                
                if ($field['type'] === 'file' || $field['type'] === 'image') {
                    $rules[$fieldName] .= '|file|mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png|max:5120';
                } elseif ($field['type'] === 'datetime') {
                    $rules[$fieldName] .= '|date';
                } elseif ($field['type'] === 'url') {
                    $rules[$fieldName] .= '|url|max:3000';
                } else {
                    $rules[$fieldName] .= '|string|max:3000';
                }
            }
            $messages[$fieldName . '.required'] = "Isian '" . $field['name'] . "' wajib dipenuhi!";
            $messages[$fieldName . '.max'] = "Ukuran berkas '" . $field['name'] . "' tidak boleh melebihi 5 MB!";
            if ($field['type'] === 'url') {
                $messages[$fieldName . '.url'] = "Isian '" . $field['name'] . "' wajib diisi dengan link URL yang valid (diawali dengan http:// atau https://)!";
            }
        }
        $request->validate($rules, $messages);

        // --- PROSES SIMPAN TRANSACTION-SAFE ---
        DB::transaction(function() use ($request, $id, $registration, $currentStage, $formSchema, $stageData, $previousValues) {
            $processedValues = [];

            foreach ($formSchema as $index => $field) {
                $fieldName = "field_" . $index;
                $value = $request->input($fieldName);

                if ($field['type'] === 'checkbox' && is_array($value)) {
                    $value = implode(', ', $value);
                }

                if ($field['type'] === 'file' || $field['type'] === 'image') {
                    if ($request->hasFile($fieldName)) {
                        $value = $request->file($fieldName)->store('program_submissions', 'public');
                        // Hapus file lama jika diunggah file pengganti baru
                        $oldPath = $previousValues->get($field['name'])['value'] ?? null;
                        if ($oldPath) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                        }
                    } else {
                        // Pertahankan file lama
                        $value = $previousValues->get($field['name'])['value'] ?? null;
                    }
                }

                $processedValues[] = [
                    'field_name' => $field['name'],
                    'type' => $field['type'],
                    'value' => $value
                ];
            }

            // Jika pendaftaran baru (Tahap 1)
            if (!$registration) {
                $registration = Registration::create([
                    'user_id' => auth()->id(),
                    'program_id' => $id,
                    'current_stage_id' => $currentStage->id,
                    'status' => 'process',
                    'motivation' => trim($request->motivation)
                ]);

                RegistrationStageData::create([
                    'registration_id' => $registration->id,
                    'program_stage_id' => $currentStage->id,
                    'form_values' => $processedValues,
                    'status' => 'pending'
                ]);
            } else {
                // Jika mengisi form tahap lanjutan atau perbaikan kuesioner
                RegistrationStageData::updateOrCreate(
                    [
                        'registration_id' => $registration->id,
                        'program_stage_id' => $currentStage->id
                    ],
                    [
                        'form_values' => $processedValues,
                        'status' => 'pending' // Kembalikan ke review tertunda (pending)
                    ]
                );
            }
        });

        return redirect()->route('programs.catalog')->with('success', 'Berkas untuk ' . $currentStage->name . ' berhasil diunggah!');
    }

    public function saveDraft(Request $request, $id)
    {
        try {
            $program = Program::findOrFail($id);

            $registration = Registration::where('user_id', auth()->id())
                ->where('program_id', $id)
                ->first();

            if ($registration && $registration->status !== 'process') {
                return response()->json(['success' => false, 'message' => 'Pendaftaran program ini sudah selesai.'], 403);
            }

            $currentStage = ProgramStage::where('program_id', $id)->findOrFail($registration ? $registration->current_stage_id : ProgramStage::where('program_id', $id)->orderBy('sequence')->firstOrFail()->id);

            if ($currentStage->is_locked) {
                return response()->json(['success' => false, 'message' => 'Tahapan ini belum dibuka atau sedang dikunci oleh panitia.'], 403);
            }

            $stageData = $registration ? RegistrationStageData::where('registration_id', $registration->id)
                ->where('program_stage_id', $currentStage->id)
                ->first() : null;

            $previousValues = $stageData ? collect($stageData->form_values)->keyBy('field_name') : collect();
            $formSchema = $currentStage->form_schema ?? [];

            // Auto-prepend protocol for URL fields before saving draft
            foreach ($formSchema as $index => $field) {
                $fieldName = "field_" . $index;
                if ($field['type'] === 'url' && $request->filled($fieldName)) {
                    $val = trim($request->input($fieldName));
                    if ($val && !preg_match('/^https?:\/\//i', $val)) {
                        $request->merge([$fieldName => 'https://' . $val]);
                    }
                }
            }

            $processedValues = [];

            foreach ($formSchema as $index => $field) {
                $fieldName = "field_" . $index;
                $value = $request->input($fieldName);

                if ($field['type'] === 'checkbox' && is_array($value)) {
                    $value = implode(', ', $value);
                }

                if ($field['type'] === 'file' || $field['type'] === 'image') {
                    if ($request->hasFile($fieldName)) {
                        $value = $request->file($fieldName)->store('program_submissions', 'public');
                        // Hapus file lama jika diunggah file pengganti baru
                        $oldPath = $previousValues->get($field['name'])['value'] ?? null;
                        if ($oldPath) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                        }
                    } else {
                        // Pertahankan file lama
                        $value = $previousValues->get($field['name'])['value'] ?? null;
                    }
                }

                $processedValues[] = [
                    'field_name' => $field['name'],
                    'type' => $field['type'],
                    'value' => $value
                ];
            }

            DB::transaction(function() use ($request, $id, &$registration, $currentStage, $processedValues) {
                if (!$registration) {
                    $registration = Registration::create([
                        'user_id' => auth()->id(),
                        'program_id' => $id,
                        'current_stage_id' => $currentStage->id,
                        'status' => 'process',
                        'motivation' => $request->motivation ? trim($request->motivation) : null
                    ]);

                    RegistrationStageData::create([
                        'registration_id' => $registration->id,
                        'program_stage_id' => $currentStage->id,
                        'form_values' => $processedValues,
                        'status' => 'draft'
                    ]);
                } else {
                    if ($request->has('motivation')) {
                        $registration->update([
                            'motivation' => $request->motivation ? trim($request->motivation) : $registration->motivation
                        ]);
                    }

                    RegistrationStageData::updateOrCreate(
                        [
                            'registration_id' => $registration->id,
                            'program_stage_id' => $currentStage->id
                        ],
                        [
                            'form_values' => $processedValues,
                            'status' => 'draft'
                        ]
                    );
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Draf berhasil disimpan di database!',
                'draft_saved_at' => now()->format('H:i:s')
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan draf ke database: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan draf.'], 500);
        }
    }
}
