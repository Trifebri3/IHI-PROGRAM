<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramStage;
use Illuminate\Http\Request;
// Tambahkan use statement ini di bagian paling atas controller jika belum ada
use App\Models\Registration;
use App\Models\RegistrationStageData;
use App\Models\ProgramBiodataSchema;
use App\Models\ProgramAnnouncement;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementBroadcastMail;
use Illuminate\Support\Str;

class ProgramWorkspaceController extends Controller
{
    public function show(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        // 1. Ambil data stages & schemas bawaan workspace Anda sebelumnya
        $stages = $program->stages()->orderBy('sequence')->get();
        $biodataSchemas = $program->biodataSchemas()->get(); // sesuaikan nama relasi Anda

        $editingStage = $request->has('edit_stage_id')
            ? $program->stages()->find($request->edit_stage_id)
            : null;

        $managingStage = $request->has('manage_stage_id')
            ? $program->stages()->find($request->manage_stage_id)
            : null;

        // 2. SOLUSI PERFORMA: Gunakan paginate alih-alih me-load ribuan user sekaligus ke memori
        $tab = $request->query('tab', 'pending');

        $query = Registration::with(['user.profile', 'user.address', 'currentStage', 'stageData'])
            ->where('program_id', $program->id);

        if ($tab === 'reviewed') {
            $query->whereIn('status', ['passed', 'failed']);
        } elseif ($tab === 'draft') {
            $query->where('status', 'process')
                ->whereHas('stageData', function ($q) {
                    $q->whereColumn('program_stage_id', 'registrations.current_stage_id')
                      ->where('status', 'draft');
                });
        } else {
            $query->where('status', 'process')
                ->whereDoesntHave('stageData', function ($q) {
                    $q->whereColumn('program_stage_id', 'registrations.current_stage_id')
                      ->where('status', 'draft');
                });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('province')) {
            $province = $request->province;
            $query->whereHas('user.address', function ($q) use ($province) {
                $q->where('provinsi', $province);
            });
        }

        $applicants = $query->latest()->paginate(10)->withQueryString();

        $provinces = \DB::table('addresses')
            ->join('users', 'addresses.user_id', '=', 'users.id')
            ->join('registrations', 'users.id', '=', 'registrations.user_id')
            ->where('registrations.program_id', $program->id)
            ->whereNotNull('addresses.provinsi')
            ->distinct()
            ->pluck('addresses.provinsi');

        $allApplicants = Registration::with('user')
            ->where('program_id', $program->id)
            ->latest()
            ->get();

        $pendingCount = Registration::where('program_id', $program->id)
            ->where('status', 'process')
            ->whereDoesntHave('stageData', function ($q) {
                $q->whereColumn('program_stage_id', 'registrations.current_stage_id')
                  ->where('status', 'draft');
            })
            ->count();

        $draftCount = Registration::where('program_id', $program->id)
            ->where('status', 'process')
            ->whereHas('stageData', function ($q) {
                $q->whereColumn('program_stage_id', 'registrations.current_stage_id')
                  ->where('status', 'draft');
            })
            ->count();

        $reviewedCount = Registration::where('program_id', $program->id)
            ->whereIn('status', ['passed', 'failed'])
            ->count();

        $announcements = ProgramAnnouncement::where('program_id', $id)->orderBy('created_at', 'desc')->get();

        // Ambil data konsultasi/pertanyaan GTU untuk program ini
        $consultations = \App\Models\GtuConsultation::with('user')
            ->where('program_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Pemuatan data isian kustom untuk preview on-screen per tahap
        $viewStageId = $request->query('view_stage_id');
        $viewStage = null;
        $stageSubmissions = null;

        if ($viewStageId) {
            $viewStage = ProgramStage::where('program_id', $id)->find($viewStageId);
        }

        // Default ke tahap pertama jika belum terpilih
        if (!$viewStage && $stages->isNotEmpty()) {
            $viewStage = $stages->first();
            $viewStageId = $viewStage->id;
        }

        if ($viewStage) {
            $stageSubmissions = RegistrationStageData::with('registration.user')
                ->where('program_stage_id', $viewStageId)
                ->whereNotNull('form_values')
                ->paginate(10)
                ->appends(['view_stage_id' => $viewStageId]);
        }

        $viewSubmission = null;
        $viewSubmissionId = $request->query('view_submission_id');
        if ($viewSubmissionId) {
            $viewSubmission = RegistrationStageData::with('registration.user')->find($viewSubmissionId);
        }

        // Load checking metadata
        $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
        $checkingData = [];
        if (file_exists($checkingFile)) {
            $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
        }

        // --- UPGRADE MONITORING & REKAPAN DASHBOARD DATA ---
        // 1. Total Pendaftar
        $totalPendaftar = $allApplicants->count();

        // 2. Sudah Diperiksa vs Belum Diperiksa
        $checkedCount = 0;
        $uncheckedCount = 0;
        foreach ($allApplicants as $app) {
            $meta = $checkingData[$app->id] ?? null;
            if ($meta && !empty($meta['is_checked'])) {
                $checkedCount++;
            } else {
                $uncheckedCount++;
            }
        }

        // 3. Status Seleksi
        $passedCount = $allApplicants->where('status', 'passed')->count();
        $failedCount = $allApplicants->where('status', 'failed')->count();
        $processCount = $allApplicants->where('status', 'process')->count();

        // 4. Rekapan Alumni
        $alumniProgram = \App\Models\AlumniProgram::where('program_id', $program->id)->first();
        $alumniCount = 0;
        if ($alumniProgram) {
            $alumniCount = \App\Models\UserAlumni::where('alumni_program_id', $alumniProgram->id)->count();
        }

        // 5. Rekapan Piagam/Sertifikat
        $certificateCount = 0;
        if ($alumniProgram) {
            $certificateCount = \App\Models\AlumniCertificate::where('alumni_program_id', $alumniProgram->id)->count();
        }

        // 6. Rekapan Pengumuman
        $announcementIds = $announcements->pluck('id');
        $announcementViewsCount = \App\Models\ProgramAnnouncementView::whereIn('program_announcement_id', $announcementIds)->count();

        // 7. Pengisian Form per Tahap
        $stageFormRecaps = [];
        foreach ($stages as $stg) {
            $submissionsCount = RegistrationStageData::where('program_stage_id', $stg->id)
                ->whereNotNull('form_values')
                ->count();
            $stageFormRecaps[] = [
                'name' => $stg->name,
                'sequence' => $stg->sequence,
                'count' => $submissionsCount
            ];
        }

        // 8. Rekapan Aktivitas / Audit Log
        $registeredUserIds = $allApplicants->pluck('user_id')->toArray();
        $recentLogs = \App\Models\AuditLog::with(['user', 'targetUser'])
            ->where(function($q) use ($registeredUserIds) {
                $q->whereIn('user_id', $registeredUserIds)
                  ->orWhereIn('target_user_id', $registeredUserIds);
            })
            ->latest()
            ->take(30)
            ->get();

        return view('adminprogram.program.workspace', compact(
            'program',
            'stages',
            'biodataSchemas',
            'editingStage',
            'managingStage',
            'applicants',
            'allApplicants',
            'announcements',
            'consultations',
            'viewStage',
            'stageSubmissions',
            'viewSubmission',
            'checkingData',
            'totalPendaftar',
            'checkedCount',
            'uncheckedCount',
            'passedCount',
            'failedCount',
            'processCount',
            'alumniCount',
            'certificateCount',
            'announcementViewsCount',
            'stageFormRecaps',
            'recentLogs',
            'provinces',
            'pendingCount',
            'draftCount',
            'reviewedCount'
        ));
    }

    public function storeStage(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'instruction' => 'nullable|string'
        ]);

        $nextSequence = ProgramStage::where('program_id', $id)->count() + 1;

        ProgramStage::create([
            'program_id' => $id,
            'name' => $request->name,
            'sequence' => $nextSequence,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'form_schema' => [], // Array kosong aman untuk JSON cast
            'pass_announcement' => $request->pass_announcement,
            'fail_announcement' => $request->fail_announcement,
            'instruction' => $request->instruction,
        ]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Tahapan baru sukses disimpan ke database MySQL!');
    }

    public function updateStage(Request $request, $id, $stageId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'instruction' => 'nullable|string'
        ]);

        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $stage->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'pass_announcement' => $request->pass_announcement,
            'fail_announcement' => $request->fail_announcement,
            'instruction' => $request->instruction,
        ]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Amandemen data tahapan berhasil diperbarui!');
    }

    public function deleteStage($id, $stageId)
    {
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $stage->delete();

        // Re-sequence otomatis urutan angka alur kompetisi
        $allStages = ProgramStage::where('program_id', $id)->orderBy('sequence')->get();
        foreach ($allStages as $index => $stg) {
            $stg->update(['sequence' => $index + 1]);
        }

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Tahapan berhasil dihapus dan urutan disinkronkan kembali.');
    }

    public function toggleLockStage($id, $stageId)
    {
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $stage->is_locked = !$stage->is_locked;
        $stage->save();

        $statusMsg = $stage->is_locked ? "Tahapan '{$stage->name}' berhasil dikunci!" : "Kunci tahapan '{$stage->name}' berhasil dibuka!";
        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', $statusMsg);
    }

    public function storeFormField(Request $request, $id, $stageId)
    {
        $request->validate([
            'new_field_name' => 'required|string|max:100',
            'new_field_type' => 'required|string|in:text,textarea,file,image,dropdown,datetime,options,checkbox,url',
            'new_field_instruction' => 'nullable|string|max:500',
            'new_field_placeholder' => 'nullable|string|max:255',
            'new_field_options' => 'nullable|string|max:1000'
        ]);

        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);

        // Ambil array schema lama, injeksi data baru ke dalamnya
        $formItems = $stage->form_schema ?? [];
        
        $optionsArray = [];
        if ($request->filled('new_field_options')) {
            $optionsArray = array_map('trim', explode(',', $request->new_field_options));
            $optionsArray = array_filter($optionsArray); // hilangkan string kosong
        }

        $formItems[] = [
            'name' => trim($request->new_field_name),
            'type' => $request->new_field_type,
            'required' => $request->has('new_field_required'), // Bernilai true jika dicentang
            'instruction' => trim($request->new_field_instruction),
            'placeholder' => trim($request->new_field_placeholder),
            'options' => array_values($optionsArray)
        ];

        // Paksa simpan balik ke kolom JSON string MySQL
        $stage->form_schema = $formItems;
        $stage->save();

        return redirect()->to(route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId]) . '#form-builder-workspace')
            ->with('success', 'Komponen kuesioner baru berhasil dipasang!');
    }

    public function deleteFormField($id, $stageId, $index)
    {
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);

        $formItems = $stage->form_schema ?? [];
        if (isset($formItems[$index])) {
            unset($formItems[$index]);
        }

        // Re-index susunan key array agar berurutan kembali semenjak pembuangan data tengah
        $stage->form_schema = array_values($formItems);
        $stage->save();

        return redirect()->to(route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId]) . '#form-builder-workspace')
            ->with('success', 'Atribut formulir berhasil dicabut.');
    }

    public function updateFormField(Request $request, $id, $stageId, $index)
    {
        $request->validate([
            'field_name' => 'required|string|max:100',
            'field_type' => 'required|string|in:text,textarea,file,image,dropdown,datetime,options,checkbox,url',
            'field_instruction' => 'nullable|string|max:500',
            'field_placeholder' => 'nullable|string|max:255',
            'field_options' => 'nullable|string|max:1000'
        ]);

        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $formItems = $stage->form_schema ?? [];

        if (!isset($formItems[$index])) {
            return redirect()->back()->with('error', 'Bidang kuesioner tidak ditemukan.');
        }

        $optionsArray = [];
        if ($request->filled('field_options')) {
            $optionsArray = array_map('trim', explode(',', $request->field_options));
            $optionsArray = array_filter($optionsArray);
        }

        $formItems[$index] = [
            'name' => trim($request->field_name),
            'type' => $request->field_type,
            'required' => $request->has('field_required'),
            'instruction' => trim($request->field_instruction),
            'placeholder' => trim($request->field_placeholder),
            'options' => array_values($optionsArray)
        ];

        $stage->form_schema = $formItems;
        $stage->save();

        return redirect()->to(route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId]) . '#form-builder-workspace')
            ->with('success', 'Komponen kuesioner berhasil diperbarui!');
    }

    public function moveFormFieldUp($id, $stageId, $index)
    {
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $formItems = $stage->form_schema ?? [];

        if (isset($formItems[$index]) && isset($formItems[$index - 1])) {
            $temp = $formItems[$index];
            $formItems[$index] = $formItems[$index - 1];
            $formItems[$index - 1] = $temp;

            $stage->form_schema = array_values($formItems);
            $stage->save();

            return redirect()->to(route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId]) . '#form-builder-workspace')
                ->with('success', 'Posisi komponen berhasil dipindahkan ke atas!');
        }

        return redirect()->back()->with('error', 'Gagal memindahkan posisi komponen.');
    }

    public function moveFormFieldDown($id, $stageId, $index)
    {
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $formItems = $stage->form_schema ?? [];

        if (isset($formItems[$index]) && isset($formItems[$index + 1])) {
            $temp = $formItems[$index];
            $formItems[$index] = $formItems[$index + 1];
            $formItems[$index + 1] = $temp;

            $stage->form_schema = array_values($formItems);
            $stage->save();

            return redirect()->to(route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId]) . '#form-builder-workspace')
                ->with('success', 'Posisi komponen berhasil dipindahkan ke bawah!');
        }

        return redirect()->back()->with('error', 'Gagal memindahkan posisi komponen.');
    }


    public function showApplicantSubmission($id, $registrationId)
{
    // 1. Validasi hak akses program
    $program = auth()->user()->managedPrograms()->findOrFail($id);

    // 2. Ambil data pendaftaran peserta beserta relasi user dan tahapan saat ini
    $registration = Registration::with([
        'user.profile',
        'user.address',
        'user.verification',
        'user.biodataValues.biodataField',
        'currentStage'
    ])->where('program_id', $id)->findOrFail($registrationId);

    // 3. Taruh data pengisian formulir spesifik khusus di tahap aktif tersebut
    $stageData = RegistrationStageData::where('registration_id', $registrationId)
        ->where('program_stage_id', $registration->current_stage_id)
        ->firstOrFail();

    // 4. Load data isian wajib program (jika ada)
    $biodataSubmission = \App\Models\ProgramBiodataSubmission::where('user_id', $registration->user_id)
        ->where('program_id', $id)
        ->first();

    // 5. Otomatis tandai status periksa di metadata menjadi 'opened' (Sudah Dibuka)
    $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
    $checkingData = [];
    if (file_exists($checkingFile)) {
        $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
    }
    
    $existing = $checkingData[$registrationId] ?? null;
    $currentStatus = $existing['status'] ?? (($existing && !empty($existing['is_checked'])) ? 'checked' : 'unopened');
    
    if ($currentStatus === 'unopened') {
        $checkingData[$registrationId] = [
            'is_checked' => false,
            'status' => 'opened',
            'checked_at' => now()->format('Y-m-d H:i'),
            'checked_by' => auth()->user()->name ?? 'Admin',
            'batch_name' => $existing['batch_name'] ?? null
        ];
        file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));
    }

    return view('adminprogram.program.applicant_detail', compact('program', 'registration', 'stageData', 'biodataSubmission'));
}

    public function resetApplicantAnswers($id, $registrationId)
    {
        // 1. Validasi hak akses program
        $program = auth()->user()->managedPrograms()->findOrFail($id);

        // 2. Ambil data pendaftaran peserta
        $registration = Registration::where('program_id', $id)->findOrFail($registrationId);

        // 3. Ambil data kuesioner aktif pendaftar dan hapus berkas-berkas fisiknya jika ada
        $stageData = RegistrationStageData::where('registration_id', $registrationId)
            ->where('program_stage_id', $registration->current_stage_id)
            ->first();

        if ($stageData) {
            // Hapus file fisik dari storage jika ada file yang diunggah
            if (is_array($stageData->form_values)) {
                foreach ($stageData->form_values as $item) {
                    if (isset($item['type']) && ($item['type'] === 'file' || $item['type'] === 'image')) {
                        $filePath = $item['value'] ?? null;
                        if ($filePath) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                        }
                    }
                }
            }

            // Hapus data stage data
            $stageData->delete();
        }

        // 4. Update status check metadata ke unopened
        $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
        if (file_exists($checkingFile)) {
            $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
            if (isset($checkingData[$registrationId])) {
                $checkingData[$registrationId]['is_checked'] = false;
                $checkingData[$registrationId]['status'] = 'unopened';
                file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));
            }
        }

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Jawaban kuesioner pendaftar berhasil dihapus/dikosongkan kembali!');
    }

    public function resetApplicantSingleAnswer(Request $request, $id, $registrationId)
    {
        $request->validate([
            'field_name' => 'required|string'
        ]);

        $fieldName = $request->field_name;

        // 1. Validasi hak akses program
        $program = auth()->user()->managedPrograms()->findOrFail($id);

        // 2. Ambil data pendaftaran peserta
        $registration = Registration::where('program_id', $id)->findOrFail($registrationId);

        // 3. Cari data kuesioner aktif pendaftar
        $stageData = RegistrationStageData::where('registration_id', $registrationId)
            ->where('program_stage_id', $registration->current_stage_id)
            ->first();

        if ($stageData && is_array($stageData->form_values)) {
            $formValues = $stageData->form_values;
            $updated = false;

            foreach ($formValues as &$item) {
                if (isset($item['field_name']) && $item['field_name'] === $fieldName) {
                    // Hapus file fisik dari storage jika ada file yang diunggah
                    if (isset($item['type']) && ($item['type'] === 'file' || $item['type'] === 'image')) {
                        $filePath = $item['value'] ?? null;
                        if ($filePath) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                        }
                    }

                    // Reset value menjadi null
                    $item['value'] = null;
                    $updated = true;
                    break;
                }
            }

            if ($updated) {
                $stageData->form_values = $formValues;
                $stageData->save();
            }
        }

        return redirect()->back()->with('success', "Jawaban untuk '" . $fieldName . "' berhasil dikosongkan secara bersih!");
    }

public function evaluateApplicant(Request $request, $id, $registrationId)
{
    $request->validate([
        'action' => 'required|in:pass,fail,revision',
        'reviewer_notes' => 'nullable|string|max:500',
        'generation_mode' => 'nullable|in:auto,manual',
        'manual_id_input' => 'nullable|string|max:50|unique:registrations,final_id_number',
        'revision_fields' => 'nullable|array'
    ]);

    // Menggunakan DB Transaction agar eksekusi SQL concurrent-safe (anti tabrakan data)
    \DB::transaction(function () use ($request, $id, $registrationId) {
        $reg = Registration::where('program_id', $id)->lockForUpdate()->findOrFail($registrationId);
        $currentStageId = $reg->current_stage_id;
        $currentStage = \App\Models\ProgramStage::findOrFail($currentStageId);

        if ($request->action === 'revision') {
            $stageData = RegistrationStageData::where('registration_id', $reg->id)
                ->where('program_stage_id', $currentStageId)
                ->first();

            if ($stageData) {
                $formValues = $stageData->form_values ?? [];
                $requestedFields = $request->input('revision_fields', []);
                $fieldNotes = $request->input('revision_notes', []);

                foreach ($formValues as &$val) {
                    if (in_array($val['field_name'], $requestedFields)) {
                        $val['needs_revision'] = true;
                        $val['revision_note'] = $fieldNotes[$val['field_name']] ?? '';
                    } else {
                        $val['needs_revision'] = false;
                        $val['revision_note'] = '';
                    }
                }

                $stageData->update([
                    'status' => 'revision',
                    'form_values' => $formValues,
                    'reviewer_notes' => $request->reviewer_notes
                ]);
            }

            $reg->update(['status' => 'process']);
        } else {
            // Update data internal stage data aktif saat ini
            $statusStage = ($request->action === 'pass') ? 'passed' : 'failed';
            RegistrationStageData::where('registration_id', $reg->id)
                ->where('program_stage_id', $currentStageId)
                ->update([
                    'status' => $statusStage,
                    'reviewer_notes' => $request->reviewer_notes
                ]);

            // Jika Dinyatakan Gagal
            if ($request->action === 'fail') {
                $reg->update(['status' => 'failed']);
            } else {
            // 3. Jika Lolos, periksa apakah ada tahapan urutan (sequence) berikutnya?
            $nextStage = \App\Models\ProgramStage::where('program_id', $id)
                ->where('sequence', $currentStage->sequence + 1)
                ->first();

            if ($nextStage) {
                // Masih ada tahap selanjutnya -> Naikkan tingkat tahapan user
                $reg->update(['current_stage_id' => $nextStage->id]);

                // Buatkan baris log kosong bersetatus pending untuk menyambut berkas tahap baru
                RegistrationStageData::create([
                    'registration_id' => $reg->id,
                    'program_stage_id' => $nextStage->id,
                    'status' => 'pending'
                ]);
            } else {
                // TIDAK ADA TAHAP LAGI = LULUS FINAL UTUH!
                $reg->status = 'passed';

                // EKSEKUSI PEMBUATAN NOMOR INDUK PROGRAM
                if (empty($reg->final_id_number)) {
                    if ($request->generation_mode === 'manual' && $request->filled('manual_id_input')) {
                        $reg->final_id_number = strtoupper(trim($request->manual_id_input));
                    } else {
                        // Pola otomatis: PRG + TAHUN + INCREMENT URUTAN (Cth: PRG202600001)
                        $year = date('Y');
                        $count = Registration::whereYear('created_at', $year)->whereNotNull('final_id_number')->count() + 1;
                        $reg->final_id_number = 'PRG' . $year . str_pad($count, 5, '0', STR_PAD_LEFT);
                    }
                }
                $reg->save();
            }
        }
    }
});

    // Update checking metadata to sync with evaluation decision
    $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
    $checkingData = [];
    if (file_exists($checkingFile)) {
        $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
    }
    
    $actionStatus = 'unopened';
    if ($request->action === 'pass') {
        $actionStatus = 'passed';
    } elseif ($request->action === 'fail') {
        $actionStatus = 'failed';
    } elseif ($request->action === 'revision') {
        $actionStatus = 'revision';
    }
    
    $existing = $checkingData[$registrationId] ?? null;
    $checkingData[$registrationId] = [
        'is_checked' => in_array($actionStatus, ['passed', 'failed', 'revision']),
        'status' => $actionStatus,
        'checked_at' => now()->format('Y-m-d H:i'),
        'checked_by' => auth()->user()->name ?? 'Admin',
        'batch_name' => $existing['batch_name'] ?? null
    ];
    file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));

    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Keputusan evaluasi berkas kelulusan berhasil diterbitkan!');
}

/**
 * Loloskan Instan Peserta langsung menjadi Alumni dan menghasilkan Piagam
 */
public function instantPass($id, $registrationId)
{
    $program = Program::findOrFail($id);
    
    \DB::transaction(function () use ($id, $registrationId) {
        $reg = Registration::where('program_id', $id)->lockForUpdate()->findOrFail($registrationId);

        // 1. Dapatkan tahapan aktif saat ini untuk program ini
        $stages = \App\Models\ProgramStage::where('program_id', $id)->orderBy('sequence')->get();

        // 2. Tandai data semua tahapan yang tersisa sebagai passed
        foreach ($stages as $stage) {
            RegistrationStageData::updateOrCreate(
                [
                    'registration_id' => $reg->id,
                    'program_stage_id' => $stage->id,
                ],
                [
                    'status' => 'passed',
                    'reviewer_notes' => 'Lulus Instan oleh Admin Program'
                ]
            );
        }

        // 3. Ubah status registrasi ke passed
        $reg->status = 'passed';

        // 4. Bangun ID Induk Program otomatis jika belum ada
        if (empty($reg->final_id_number)) {
            $year = date('Y');
            $count = Registration::whereYear('created_at', $year)->whereNotNull('final_id_number')->count() + 1;
            $reg->final_id_number = 'PRG' . $year . str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        $reg->save();

        // 5. Hubungkan / terbitkan data alumni secara instan dan buat berkas PDF
        $alumniService = app(\App\Services\AlumniService::class);
        $alumniService->registerAutoAlumni($reg);
    });

    // Update checking metadata to sync with instant pass decision
    $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
    $checkingData = [];
    if (file_exists($checkingFile)) {
        $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
    }
    
    $existing = $checkingData[$registrationId] ?? null;
    $checkingData[$registrationId] = [
        'is_checked' => true,
        'status' => 'passed',
        'checked_at' => now()->format('Y-m-d H:i'),
        'checked_by' => auth()->user()->name ?? 'Admin',
        'batch_name' => $existing['batch_name'] ?? null
    ];
    file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));

    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Peserta berhasil diluluskan instan, NIA dan piagam kelulusan otomatis diterbitkan!');
}


// TAMBAHKAN DI DALAM CLASS PROGRAMWORKSPACECONTROLLER:

public function storeBiodataSchema(Request $request, $id)
{
    $request->validate([
        'field_name' => 'required|string|max:100',
        'field_type' => 'required|in:text,number,file'
    ]);

    ProgramBiodataSchema::create([
        'program_id' => $id,
        'field_name' => trim($request->field_name),
        'field_type' => $request->field_type,
        'is_required' => $request->has('is_required')
    ]);

    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Kolom Formulir Biodata Program berhasil ditambahkan!');
}

public function deleteBiodataSchema($id, $schemaId)
{
    ProgramBiodataSchema::where('program_id', $id)->findOrFail($schemaId)->delete();
    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Kolom Formulir Biodata Program berhasil dihapus.');
}



public function storeAnnouncement(Request $request, $id)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'type' => 'required|in:info,instruction,warning'
    ]);

    // 1. Amankan simpan data pengumuman ke database MySQL
    $announcement = ProgramAnnouncement::create([
        'program_id' => $id,
        'title' => trim($request->title),
        'content' => $request->content,
        'type' => $request->type
    ]);

    // 2. Cari semua peserta yang terdaftar aktif di program kerja ini
    $activeParticipants = Registration::with('user')->where('program_id', $id)->get();

    // 3. AUTOMATION BROADCAST EMAIL HTML CUSTOM UNTUK SEMUA ANGGOTA
    foreach ($activeParticipants as $participant) {
        if ($participant->user && $participant->user->email) {
            // Menggunakan facade Mail bawaan Laravel
            Mail::to($participant->user->email)->send(
                new AnnouncementBroadcastMail($announcement, $participant->user->name)
            );
        }
    }

    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Pengumuman instruksi berhasil disiarkan dan disuntikkan ke email seluruh anggota!');
}

public function updateAnnouncement(Request $request, $id, $announcementId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'type' => 'required|in:info,instruction,warning'
    ]);

    $announcement = ProgramAnnouncement::where('program_id', $id)->findOrFail($announcementId);
    $announcement->update([
        'title' => trim($request->title),
        'content' => $request->content,
        'type' => $request->type
    ]);

    return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'broadcasting'])
        ->with('success', 'Pengumuman instruksi berhasil diperbarui!');
}

public function deleteAnnouncement($id, $announcementId)
{
    $announcement = ProgramAnnouncement::where('program_id', $id)->findOrFail($announcementId);
    
    // Hapus data log tracking pembacaan pengumuman
    \App\Models\ProgramAnnouncementView::where('program_announcement_id', $announcementId)->delete();
    
    $announcement->delete();

    return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'broadcasting'])
        ->with('success', 'Pengumuman instruksi berhasil dihapus secara permanen.');
}

// MASUKKAN METHOD-METHOD HARDCORE INI DI DALAM CLASS PROGRAMWORKSPACECONTROLLER:

public function storeAcademicSchema(Request $request, $id)
{
    $request->validate([
        'total_hours' => 'required|integer|min:1',
        'raw_criteria' => 'required|string' // Berisi teks kriteria dipisahkan oleh koma (Cth: Keaktifan, Uji Coding, Final Project)
    ]);

    $program = auth()->user()->managedPrograms()->findOrFail($id);

    // Pecah string koma menjadi array PHP murni yang bersih dari spasi liar
    $criteriaArray = array_map('trim', explode(',', $request->raw_criteria));
    $criteriaArray = array_filter($criteriaArray); // Buang jika ada array kosong

    $program->update([
        'total_hours' => $request->total_hours,
        'score_schema' => array_values($criteriaArray)
    ]);

    return redirect()->route('adminprogram.programs.workspace', $id)
        ->with('success', 'Struktur JP & Skema Kriteria Transkrip Nilai Program berhasil dikunci!');
}

public function uploadProgramCertificate(Request $request, $id)
{
    // Cek apakah ada file yang dikirim
    if (!$request->hasFile('program_certificate')) {
        return redirect()->back()->with('error', 'Tidak ada berkas yang terdeteksi. Pastikan input file terisi.');
    }

    $request->validate([
        'program_certificate' => 'required|image|mimes:png|max:5120' // Naikkan jadi 5MB (5120KB)
    ]);

    $program = auth()->user()->managedPrograms()->findOrFail($id);

    try {
        // Coba simpan
        $path = $request->file('program_certificate')->store('program_certificate_templates', 'public');

        if (!$path) {
            throw new \Exception("Gagal menyimpan file ke disk storage.");
        }

        $program->update(['program_certificate_template' => $path]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Template PNG Piagam Kelulusan Program berhasil diunggah!');

    } catch (\Exception $e) {
        // Ini akan memberitahu Anda persis di mana letak masalahnya
        return redirect()->back()->with('error', 'Gagal Upload: ' . $e->getMessage());
    }
}

public function saveApplicantScores(Request $request, $id, $registrationId)
{
    $program = auth()->user()->managedPrograms()->findOrFail($id);
    $registration = Registration::where('program_id', $id)->findOrFail($registrationId);

    $scoresData = [];
    // Loop dinamis membaca seluruh skema judul kustom yang sudah dipasang pada program kerja
    foreach ($program->score_schema ?? [] as $index => $criteriaName) {
        $inputKey = 'criterion_' . $index;
        $scoresData[] = [
            'title' => $criteriaName,
            'score' => $request->input($inputKey, 0) // Jika kosong, set nilai 0
        ];
    }

    // Bangun token pengaman QR Code unik jika belum pernah digenerate sebelumnya
    $secureToken = $registration->secure_verification_token ?? (Str::random(32) . '-' . $registration->id);

    $registration->update([
        'final_scores' => $scoresData,
        'secure_verification_token' => $secureToken
    ]);

    return redirect()->route('adminprogram.programs.applicant.show', [$id, $registrationId])
        ->with('success', 'Transkrip nilai kustom E-Raport peserta berhasil disimpan & Token QR diaktifkan!');
}

/**
     * Saklar Hardcore: Buka/Tutup Pendaftaran Instan
     */
    public function toggleRegistration($id)
    {
        // Cari data program atau gagalkan jika ID salah
        $program = Program::findOrFail($id);

        // Balikkan status: jika true menjadi false, jika false menjadi true
        $program->is_open = !$program->is_open;
        $program->save();

        // Siapkan pesan notifikasi info update
        $statusMessage = $program->is_open 
            ? "Pendaftaran untuk program '{$program->name}' RESMI DIBUKA!" 
            : "Pendaftaran untuk program '{$program->name}' RESMI DITUTUP!";

        return redirect()->back()->with('success', $statusMessage);
    }

    /**
     * Memperbarui email Pos Pelayanan GTU
     */
    public function updateGtuEmail(Request $request, $id)
    {
        $request->validate([
            'gtu_email' => 'required|email|max:255'
        ]);

        $program = Program::findOrFail($id);
        $program->update([
            'gtu_email' => trim($request->gtu_email)
        ]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Email Pos Pelayanan GTU berhasil diperbarui!');
    }

    /**
     * Memberikan atau memperbarui jawaban konsultasi GTU
     */
    public function replyGtuConsultation(Request $request, $id, $consultationId)
    {
        $request->validate([
            'reply' => 'required|string|max:5000'
        ]);

        $consultation = \App\Models\GtuConsultation::where('program_id', $id)->findOrFail($consultationId);
        $consultation->update([
            'reply' => trim($request->reply),
            'status' => 'answered',
            'answered_at' => now()
        ]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Jawaban konsultasi berhasil dikirim!');
    }

    // --- EXPORT REKAPAN JAWABAN (EXCEL & PDF) ---
    
    public function exportStageExcel($id, $stageId)
    {
        $program = Program::findOrFail($id);
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        
        $schema = $stage->form_schema ?? [];
        
        $headers = [
            'Nama Peserta', 
            'Email', 
            'Tanggal Submit', 
            'Status Review', 
            'Catatan Review',
            // Data Alamat Gatekeeper
            'Negara', 
            'Provinsi', 
            'Kabupaten/Kota', 
            'Kecamatan', 
            'Desa/Kelurahan', 
            'Kampung/Dusun', 
            'Detail Alamat'
        ];

        // Ambil skema profil/biodata global dinamis dari super admin
        $globalBiodataFields = \App\Models\BiodataField::all();
        foreach ($globalBiodataFields as $gField) {
            $headers[] = '[Profil] ' . $gField->name;
        }

        // Ambil skema biodata dinamis program
        $biodataSchemas = \App\Models\ProgramBiodataSchema::where('program_id', $id)->get();
        foreach ($biodataSchemas as $bSchema) {
            $headers[] = '[Biodata] ' . $bSchema->field_name;
        }

        // Dan kuesioner dinamis dari tahapan ini
        foreach ($schema as $field) {
            $headers[] = $field['name'];
        }
        
        $submissions = RegistrationStageData::with(['registration.user.address', 'registration.user.biodataValues.biodataField'])
            ->where('program_stage_id', $stageId)
            ->whereNotNull('form_values')
            ->get();
            
        $callback = function() use ($submissions, $headers, $schema, $biodataSchemas, $globalBiodataFields, $id) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fwrite($file, "sep=;\n"); // Excel delimiter directive
            fputcsv($file, $headers, ';');
            
            foreach ($submissions as $sub) {
                if (!$sub->registration || !$sub->registration->user) continue;
                
                $user = $sub->registration->user;
                $address = $user->address;
                
                // Ambil data profil/biodata global
                $userBiodataValues = $user->biodataValues->keyBy('biodata_field_id');

                // Ambil data biodata tambahan dari table ProgramBiodataSubmission
                $biodataSub = \App\Models\ProgramBiodataSubmission::where('user_id', $user->id)
                    ->where('program_id', $id)
                    ->first();
                $submittedAnswers = $biodataSub ? ($biodataSub->submitted_answers ?? []) : [];

                $row = [
                    $user->name,
                    $user->email,
                    $sub->updated_at ? $sub->updated_at->format('Y-m-d H:i:s') : '-',
                    strtoupper($sub->status),
                    $sub->reviewer_notes ?? '-',
                    // Alamat
                    $address->negara ?? '-',
                    $address->provinsi ?? '-',
                    $address->kabupaten ?? '-',
                    $address->kecamatan ?? '-',
                    $address->desa ?? '-',
                    $address->kampung ?? '-',
                    $address->detail_alamat ?? '-'
                ];

                // Tulis data profil/biodata global
                foreach ($globalBiodataFields as $gField) {
                    $valObj = $userBiodataValues->get($gField->id);
                    $ansValue = $valObj ? $valObj->value : '-';
                    
                    if (is_array($ansValue)) {
                        $ansValue = implode(', ', $ansValue);
                    } elseif (is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf'))) {
                        $ansValue = asset('storage/' . $ansValue);
                    }
                    $row[] = $ansValue ?? '-';
                }
                
                // Tulis kolom biodata dinamis program
                foreach ($biodataSchemas as $bSchema) {
                    $key = str_replace(' ', '_', $bSchema->field_name);
                    $ansValue = $submittedAnswers[$key] ?? ($submittedAnswers[$bSchema->field_name] ?? '-');
                    
                    if (is_array($ansValue)) {
                        $ansValue = implode(', ', $ansValue);
                    } elseif (is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf'))) {
                        $ansValue = asset('storage/' . $ansValue);
                    }
                    $row[] = $ansValue ?? '-';
                }

                // Tulis jawaban kuesioner dinamis tahap
                $values = collect($sub->form_values)->keyBy('field_name');
                foreach ($schema as $field) {
                    $fieldName = $field['name'];
                    $fieldVal = isset($values[$fieldName]) ? $values[$fieldName]['value'] : '';
                    if (($field['type'] === 'file' || $field['type'] === 'image') && $fieldVal) {
                        $fieldVal = asset('storage/' . $fieldVal);
                    }
                    $row[] = $fieldVal;
                }
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };
        
        $filename = "Rekap_Tahap_" . Str::slug($stage->name) . "_" . date('Ymd_His') . ".csv";
        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function exportStagePdf($id, $stageId)
    {
        $program = Program::findOrFail($id);
        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        
        $schema = $stage->form_schema ?? [];
        
        $submissions = RegistrationStageData::with('registration.user')
            ->where('program_stage_id', $stageId)
            ->whereNotNull('form_values')
            ->get();
            
        return view('adminprogram.program.export_stage_pdf', compact('program', 'stage', 'schema', 'submissions'));
    }

    public function exportUserExcel($id, $registrationId)
    {
        $program = Program::findOrFail($id);
        $registration = Registration::with(['user.address', 'user.biodataValues.biodataField'])->where('program_id', $id)->findOrFail($registrationId);
        
        $stages = ProgramStage::where('program_id', $id)->orderBy('sequence')->get();
        
        $headers = ['Tahap Program', 'Nama Atribut/Pertanyaan', 'Tipe Jawaban', 'Jawaban / Nilai'];
        
        $callback = function() use ($registration, $stages, $headers, $id) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fwrite($file, "sep=;\n"); // Excel delimiter directive
            
            // User Header
            fputcsv($file, ['REKAPAN JAWABAN PESERTA'], ';');
            fputcsv($file, ['Nama:', $registration->user->name], ';');
            fputcsv($file, ['Email:', $registration->user->email], ';');
            fputcsv($file, ['Program:', $registration->program->name], ';');
            fputcsv($file, ['Motivasi:', $registration->motivation ?? '-'], ';');
            
            // Data Alamat
            $address = $registration->user->address;
            fputcsv($file, ['Negara:', $address->negara ?? '-'], ';');
            fputcsv($file, ['Provinsi:', $address->provinsi ?? '-'], ';');
            fputcsv($file, ['Kabupaten/Kota:', $address->kabupaten ?? '-'], ';');
            fputcsv($file, ['Kecamatan:', $address->kecamatan ?? '-'], ';');
            fputcsv($file, ['Desa/Kelurahan:', $address->desa ?? '-'], ';');
            fputcsv($file, ['Kampung/Dusun:', $address->kampung ?? '-'], ';');
            fputcsv($file, ['Detail Alamat:', $address->detail_alamat ?? '-'], ';');

            // Data Profil / Biodata Global Super Admin
            $globalBiodataFields = \App\Models\BiodataField::all();
            $userBiodataValues = $registration->user->biodataValues->keyBy('biodata_field_id');
            foreach ($globalBiodataFields as $gField) {
                $valObj = $userBiodataValues->get($gField->id);
                $ansValue = $valObj ? $valObj->value : '-';
                if (is_array($ansValue)) {
                    $ansValue = implode(', ', $ansValue);
                } elseif (is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf'))) {
                    $ansValue = asset('storage/' . $ansValue);
                }
                fputcsv($file, ['[Profil] ' . $gField->name . ':', $ansValue ?? '-'], ';');
            }

            // Data Biodata Wajib Program
            $biodataSub = \App\Models\ProgramBiodataSubmission::where('user_id', $registration->user_id)
                ->where('program_id', $id)
                ->first();
            $submittedAnswers = $biodataSub ? ($biodataSub->submitted_answers ?? []) : [];
            $biodataSchemas = \App\Models\ProgramBiodataSchema::where('program_id', $id)->get();
            foreach ($biodataSchemas as $bSchema) {
                $key = str_replace(' ', '_', $bSchema->field_name);
                $ansValue = $submittedAnswers[$key] ?? ($submittedAnswers[$bSchema->field_name] ?? '-');
                if (is_array($ansValue)) {
                    $ansValue = implode(', ', $ansValue);
                } elseif (is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf'))) {
                    $ansValue = asset('storage/' . $ansValue);
                }
                fputcsv($file, ['[Biodata] ' . $bSchema->field_name . ':', $ansValue ?? '-'], ';');
            }

            fputcsv($file, [], ';');
            
            fputcsv($file, $headers, ';');
            
            foreach ($stages as $stage) {
                $stageData = RegistrationStageData::where('registration_id', $registration->id)
                    ->where('program_stage_id', $stage->id)
                    ->first();
                
                $values = $stageData ? ($stageData->form_values ?? []) : [];
                
                if (empty($values)) {
                    fputcsv($file, [$stage->name, '(Belum ada data/kuesioner)', '', ''], ';');
                    continue;
                }
                
                foreach ($values as $val) {
                    $ans = $val['value'] ?? '';
                    if (($val['type'] === 'file' || $val['type'] === 'image') && $ans) {
                        $ans = asset('storage/' . $ans);
                    }
                    fputcsv($file, [
                        $stage->name,
                        $val['field_name'],
                        $val['type'],
                        $ans
                    ], ';');
                }
            }
            fclose($file);
        };
        
        $filename = "Rekap_Peserta_" . Str::slug($registration->user->name) . "_" . date('Ymd_His') . ".csv";
        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function exportUserPdf($id, $registrationId)
    {
        $program = Program::findOrFail($id);
        $registration = Registration::with('user')->where('program_id', $id)->findOrFail($registrationId);
        
        $stages = ProgramStage::where('program_id', $id)->orderBy('sequence')->get();
        
        $stageSubmissions = [];
        foreach ($stages as $stage) {
            $stageSubmissions[] = [
                'stage' => $stage,
                'data' => RegistrationStageData::where('registration_id', $registrationId)
                    ->where('program_stage_id', $stage->id)
                    ->first()
            ];
        }
        
        return view('adminprogram.program.export_user_pdf', compact('program', 'registration', 'stages', 'stageSubmissions'));
    }

    public function resetSubmission($id, $submissionId)
    {
        $submission = RegistrationStageData::findOrFail($submissionId);
        
        // Hapus berkas file/gambar lampiran dari storage jika ada
        if (!empty($submission->form_values)) {
            foreach ($submission->form_values as $val) {
                if (($val['type'] === 'file' || $val['type'] === 'image') && !empty($val['value'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($val['value']);
                }
            }
        }
        
        $submission->delete();
        
        return redirect()->route('adminprogram.programs.workspace', [$id, 'view_stage_id' => $submission->program_stage_id, 'active_panel' => 'recap'])
            ->with('success', 'Jawaban kuesioner peserta berhasil di-reset menjadi nol / belum jawab!');
    }

    public function resetAllApplicants($id)
    {
        $program = Program::findOrFail($id);
        
        // 1. Ambil semua pendaftaran pendaftar untuk program ini
        $registrations = Registration::where('program_id', $id)->get();
        
        foreach ($registrations as $reg) {
            // 2. Cari seluruh data jawaban kuesioner dinamis peserta di setiap tahap
            $stageSubmissions = RegistrationStageData::where('registration_id', $reg->id)->get();
            
            foreach ($stageSubmissions as $sub) {
                // Hapus berkas fisik yang pernah diunggah
                if (!empty($sub->form_values)) {
                    foreach ($sub->form_values as $val) {
                        if (($val['type'] === 'file' || $val['type'] === 'image') && !empty($val['value'])) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($val['value']);
                        }
                    }
                }
                $sub->delete();
            }
            
            // 3. Hapus berkas/file data biodata wajib gatekeeper jika ada
            if (!empty($reg->biodata_values)) {
                foreach ($reg->biodata_values as $val) {
                    if (is_string($val) && (str_starts_with($val, 'gatekeeper_docs/') || str_starts_with($val, 'program_submissions/'))) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($val);
                    }
                }
            }
            
            // 4. Hapus data pendaftaran utama
            $reg->delete();
        }
        
        return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'recap'])
            ->with('success', 'Seluruh pendaftar program beserta semua file jawaban berkasnya telah berhasil dihapus permanen dari database!');
    }

    public function resetSpecificApplicant(Request $request, $id)
    {
        $request->validate([
            'registration_id' => 'required|integer|exists:registrations,id'
        ]);

        $program = Program::findOrFail($id);
        $reg = Registration::where('program_id', $id)->findOrFail($request->registration_id);
        
        // 1. Cari seluruh data jawaban kuesioner dinamis peserta di setiap tahap
        $stageSubmissions = RegistrationStageData::where('registration_id', $reg->id)->get();
        
        foreach ($stageSubmissions as $sub) {
            // Hapus berkas fisik yang pernah diunggah
            if (!empty($sub->form_values)) {
                foreach ($sub->form_values as $val) {
                    if (($val['type'] === 'file' || $val['type'] === 'image') && !empty($val['value'])) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($val['value']);
                    }
                }
            }
            $sub->delete();
        }
        
        // 2. Hapus berkas/file data biodata wajib gatekeeper jika ada
        if (!empty($reg->biodata_values)) {
            foreach ($reg->biodata_values as $val) {
                if (is_string($val) && (str_starts_with($val, 'gatekeeper_docs/') || str_starts_with($val, 'program_submissions/'))) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($val);
                }
            }
        }
        
        // 3. Hapus data pendaftaran utama
        $reg->delete();
        
        return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'recap'])
            ->with('success', 'Data pendaftaran peserta tersebut beserta seluruh berkas file jawabannya telah berhasil dihapus secara permanen!');
    }

    public function updateCheckingMetadata(Request $request, $id)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'required|integer',
            'is_checked' => 'required|string',
            'batch_name' => 'nullable|string|max:100',
            'checked_by' => 'nullable|string|max:100',
            'checked_at' => 'nullable|date'
        ]);

        $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
        $checkingData = [];
        if (file_exists($checkingFile)) {
            $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
        }

        $status = trim($request->is_checked);
        
        // Backward compatibility mapping for old 0/1 inputs
        if ($status === '1') {
            $status = 'checked';
        } elseif ($status === '0') {
            $status = 'unopened';
        }

        // is_checked boolean is true for checked, passed, failed, revision statuses
        $isChecked = in_array($status, ['checked', 'passed', 'failed', 'revision']);
        
        $batchName = trim($request->batch_name) ?: null;
        $checkedBy = trim($request->checked_by) ?: (auth()->user()->name ?? 'Admin');
        $checkedAt = $request->checked_at ? date('Y-m-d H:i', strtotime($request->checked_at)) : now()->format('Y-m-d H:i');

        foreach ($request->registration_ids as $regId) {
            $existing = $checkingData[$regId] ?? null;
            
            $checkingData[$regId] = [
                'is_checked' => $isChecked,
                'status' => $status,
                'checked_at' => $isChecked ? $checkedAt : ($status !== 'unopened' ? now()->format('Y-m-d H:i') : null),
                'checked_by' => $isChecked ? $checkedBy : ($status !== 'unopened' ? (auth()->user()->name ?? 'Admin') : null),
                'batch_name' => $batchName ?: ($existing['batch_name'] ?? null)
            ];
        }

        file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));

        return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'checking'])
            ->with('success', 'Status pemeriksaan dan kelompok peserta berhasil diperbarui!');
    }
}
