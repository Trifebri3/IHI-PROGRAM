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
        // Menggunakan Eager Loading (with) untuk menghindari N+1 query problem pada relasi 'user' dan 'currentStage'
        $applicants = Registration::with(['user', 'currentStage'])
            ->where('program_id', $program->id)
            ->latest()
            ->paginate(10); // Menampilkan 10 data per halaman (bisa diubah sesuai preferensi)

        $allApplicants = Registration::with('user')
            ->where('program_id', $program->id)
            ->latest()
            ->get();

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
            'checkingData'
        ));
    }

    public function storeStage(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
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
        ]);

        return redirect()->route('adminprogram.programs.workspace', $id)
            ->with('success', 'Tahapan baru sukses disimpan ke database MySQL!');
    }

    public function updateStage(Request $request, $id, $stageId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $stage = ProgramStage::where('program_id', $id)->findOrFail($stageId);
        $stage->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'pass_announcement' => $request->pass_announcement,
            'fail_announcement' => $request->fail_announcement,
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

    public function storeFormField(Request $request, $id, $stageId)
    {
        $request->validate([
            'new_field_name' => 'required|string|max:100',
            'new_field_type' => 'required|string|in:text,textarea,file,image,dropdown,datetime,options,checkbox',
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

        return redirect()->route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId])
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

        return redirect()->route('adminprogram.programs.workspace', [$id, 'manage_stage_id' => $stageId])
            ->with('success', 'Atribut formulir berhasil dicabut.');
    }


    public function showApplicantSubmission($id, $registrationId)
{
    // 1. Validasi hak akses program
    $program = auth()->user()->managedPrograms()->findOrFail($id);

    // 2. Ambil data pendaftaran peserta beserta relasi user dan tahapan saat ini
    $registration = Registration::with(['user', 'currentStage'])->where('program_id', $id)->findOrFail($registrationId);

    // 3. Taruh data pengisian formulir spesifik khusus di tahap aktif tersebut
    $stageData = RegistrationStageData::where('registration_id', $registrationId)
        ->where('program_stage_id', $registration->current_stage_id)
        ->firstOrFail();

    return view('adminprogram.program.applicant_detail', compact('program', 'registration', 'stageData'));
}

public function evaluateApplicant(Request $request, $id, $registrationId)
{
    $request->validate([
        'action' => 'required|in:pass,fail',
        'reviewer_notes' => 'nullable|string|max:500',
        'generation_mode' => 'nullable|in:auto,manual',
        'manual_id_input' => 'nullable|string|max:50|unique:registrations,final_id_number'
    ]);

    // Menggunakan DB Transaction agar eksekusi SQL concurrent-safe (anti tabrakan data)
    \DB::transaction(function () use ($request, $id, $registrationId) {
        $reg = Registration::where('program_id', $id)->lockForUpdate()->findOrFail($registrationId);
        $currentStageId = $reg->current_stage_id;
        $currentStage = \App\Models\ProgramStage::findOrFail($currentStageId);

        // 1. Update data internal stage data aktif saat ini
        $statusStage = ($request->action === 'pass') ? 'passed' : 'failed';
        RegistrationStageData::where('registration_id', $reg->id)
            ->where('program_stage_id', $currentStageId)
            ->update([
                'status' => $statusStage,
                'reviewer_notes' => $request->reviewer_notes
            ]);

        // 2. Jika Dinyatakan Gagal
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
    });

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
        
        $headers = ['Nama Peserta', 'Email', 'Tanggal Submit', 'Status Review', 'Catatan Review'];
        foreach ($schema as $field) {
            $headers[] = $field['name'];
        }
        
        $submissions = RegistrationStageData::with('registration.user')
            ->where('program_stage_id', $stageId)
            ->whereNotNull('form_values')
            ->get();
            
        $callback = function() use ($submissions, $headers, $schema) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fwrite($file, "sep=;\n"); // Excel delimiter directive
            fputcsv($file, $headers, ';');
            
            foreach ($submissions as $sub) {
                if (!$sub->registration || !$sub->registration->user) continue;
                
                $row = [
                    $sub->registration->user->name,
                    $sub->registration->user->email,
                    $sub->updated_at ? $sub->updated_at->format('Y-m-d H:i:s') : '-',
                    strtoupper($sub->status),
                    $sub->reviewer_notes ?? '-'
                ];
                
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
        $registration = Registration::with('user')->where('program_id', $id)->findOrFail($registrationId);
        
        $stages = ProgramStage::where('program_id', $id)->orderBy('sequence')->get();
        
        $headers = ['Tahap Program', 'Nama Atribut/Pertanyaan', 'Tipe Jawaban', 'Jawaban / Nilai'];
        
        $callback = function() use ($registration, $stages, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fwrite($file, "sep=;\n"); // Excel delimiter directive
            
            // User Header
            fputcsv($file, ['REKAPAN JAWABAN PESERTA'], ';');
            fputcsv($file, ['Nama:', $registration->user->name], ';');
            fputcsv($file, ['Email:', $registration->user->email], ';');
            fputcsv($file, ['Program:', $registration->program->name], ';');
            fputcsv($file, ['Motivasi:', $registration->motivation ?? '-'], ';');
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

    public function updateCheckingMetadata(Request $request, $id)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'required|integer',
            'is_checked' => 'required|in:0,1',
            'batch_name' => 'nullable|string|max:100',
            'checked_by' => 'nullable|string|max:100',
            'checked_at' => 'nullable|date'
        ]);

        $checkingFile = storage_path('app/checking_metadata_' . $id . '.json');
        $checkingData = [];
        if (file_exists($checkingFile)) {
            $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
        }

        $isChecked = (bool) $request->is_checked;
        $batchName = trim($request->batch_name) ?: null;
        $checkedBy = trim($request->checked_by) ?: (auth()->user()->name ?? 'Admin');
        $checkedAt = $request->checked_at ? date('Y-m-d H:i', strtotime($request->checked_at)) : now()->format('Y-m-d H:i');

        foreach ($request->registration_ids as $regId) {
            if ($isChecked) {
                $checkingData[$regId] = [
                    'is_checked' => true,
                    'checked_at' => $checkedAt,
                    'checked_by' => $checkedBy,
                    'batch_name' => $batchName
                ];
            } else {
                if (isset($checkingData[$regId])) {
                    $checkingData[$regId]['is_checked'] = false;
                    $checkingData[$regId]['batch_name'] = $batchName;
                } else {
                    $checkingData[$regId] = [
                        'is_checked' => false,
                        'checked_at' => null,
                        'checked_by' => null,
                        'batch_name' => $batchName
                    ];
                }
            }
        }

        file_put_contents($checkingFile, json_encode($checkingData, JSON_PRETTY_PRINT));

        return redirect()->route('adminprogram.programs.workspace', [$id, 'active_panel' => 'checking'])
            ->with('success', 'Status pemeriksaan dan kelompok peserta berhasil diperbarui!');
    }
}
