<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramBiodataSchema;
use App\Models\ProgramBiodataSubmission;
use App\Models\Registration;
use Illuminate\Http\Request;
use App\Models\ProgramAnnouncement;
use App\Models\ProgramAnnouncementView;


class ProgramDashboardController extends Controller
{
    public function showBiodataForm($id)
    {
        $program = Program::findOrFail($id);

        // Pastikan dia memang sudah terdaftar resmi (mencegah akun asing bypass)
        $registration = Registration::where('user_id', auth()->id())->where('program_id', $id)->firstOrFail();

        // Cek jika sudah pernah mengisi berkas biodata program ini
        $alreadySubmitted = ProgramBiodataSubmission::where('user_id', auth()->id())->where('program_id', $id)->exists();
        if ($alreadySubmitted) {
            return redirect()->route('programs.internal.dashboard', $id);
        }

        $schemas = ProgramBiodataSchema::where('program_id', $id)->get();
        return view('pesertabiasa.program.biodataprogram_form', compact('program', 'schemas'));
    }

    public function submitBiodataForm(Request $request, $id)
    {
        $schemas = ProgramBiodataSchema::where('program_id', $id)->get();

        $rules = [];
        $messages = [];
        foreach ($schemas as $ch) {
            $inputName = "schema_" . $ch->id;
            $rule = $ch->is_required ? 'required' : 'nullable';

            if ($ch->field_type === 'number') {
                $rule .= '|numeric';
            } elseif ($ch->field_type === 'file') {
                $rule .= '|file|mimes:pdf,jpg,png|max:3072';
            } else {
                $rule .= '|string|max:500';
            }
            $rules[$inputName] = $rule;
            $messages[$inputName . '.required'] = "Data '" . $ch->field_name . "' mutlak wajib dipenuhi!";
        }
        $request->validate($rules, $messages);

        $processedAnswers = [];
        foreach ($schemas as $ch) {
            $inputName = "schema_" . $ch->id;
            $value = $request->input($inputName);

            if ($ch->field_type === 'file' && $request->hasFile($inputName)) {
                $value = $request->file($inputName)->store('program_biodata_attachments', 'public');
            }

            $processedAnswers[] = [
                'label' => $ch->field_name,
                'type' => $ch->field_type,
                'value' => $value
            ];
        }

        ProgramBiodataSubmission::create([
            'user_id' => auth()->id(),
            'program_id' => $id,
            'submitted_answers' => $processedAnswers
        ]);

        return redirect()->route('programs.internal.dashboard', $id)
            ->with('success', 'Formulir Biodata Program sukses terverifikasi. Selamat berkativitas di dalam dashboard program!');
    }

    public function index($id)
    {
        $program = Program::findOrFail($id);
        $registration = Registration::with('currentStage')->where('user_id', auth()->id())->where('program_id', $id)->firstOrFail();

        // Ambil riwayat evaluasi seluruh stage data milik peserta untuk dipasang di bagian transkrip nilai
        $stageLogs = \App\Models\RegistrationStageData::with('stage')
            ->where('registration_id', $registration->id)
            ->get();

        return view('pesertabiasa.program.internal_dashboard', compact('program', 'registration', 'stageLogs'));
    }

    public function showAnnouncementGate($id)
{
    $program = Program::findOrFail($id);

    // Cari pengumuman instruksi terbaru yang belum divalidasi oleh user bersangkutan
    $announcement = ProgramAnnouncement::where('program_id', $id)
        ->where('type', 'instruction')
        ->orderBy('created_at', 'desc')
        ->firstOrFail();

    return view('pesertabiasa.program.announcement_gate', compact('program', 'announcement'));
}

public function confirmAnnouncementRead(Request $request, $id, $announcementId)
{
    // Cetak log absah konfirmasi baca detik ini juga ke database
    ProgramAnnouncementView::firstOrCreate([
        'user_id' => auth()->id(),
        'program_announcement_id' => $announcementId
    ], [
        'confirmed_at' => now()
    ]);

    return redirect()->route('programs.internal.dashboard', $id)
        ->with('success', 'Pakta instruksi berhasil ditandatangani secara digital. Selamat melanjutkan aktivitas!');
}

// TAMBAHKAN KODE INI DI DALAM CLASS PROGRAMDASHBOARDCONTROLLER PESERTA:

public function showGlobalAnnouncementGate()
{
    // Ambil pengumuman global bertipe instruksi terbaru
    $announcement = ProgramAnnouncement::whereNull('program_id')
        ->where('type', 'instruction')
        ->orderBy('created_at', 'desc')
        ->firstOrFail();

    return view('pesertabiasa.program.global_announcement_gate', compact('announcement'));
}

public function confirmGlobalAnnouncementRead(Request $request, $announcementId)
{
    ProgramAnnouncementView::firstOrCreate([
        'user_id' => auth()->id(),
        'program_announcement_id' => $announcementId
    ], [
        'confirmed_at' => now()
    ]);

    return redirect()->route('dashboard')
        ->with('success', 'Maklumat global berhasil ditandatangani. Seluruh akses aplikasi dibuka kembali!');
}

// TAMBAHKAN 2 METHOD INI DI DALAM PORTAL PESERTA PROGRAMDASHBOARDCONTROLLER:

public function printProgramCertificate($id)
{
    $program = Program::findOrFail($id);

    $registration = Registration::where('user_id', auth()->id())
        ->where('program_id', $id)
        ->where('status', 'passed')
        ->firstOrFail();

    if (!$program->program_certificate_template) {
        return redirect()->route('programs.internal.dashboard', $id)
            ->with('error', 'Panitia belum mengunggah berkas rancangan master piagam program.');
    }

    // --- 💥 PERBAIKAN: JIKA TOKEN KOSONG, GENERATE SEKARANG! ---
    if (empty($registration->secure_verification_token)) {
        $registration->secure_verification_token = \Illuminate\Support\Str::random(32) . '-' . $registration->id;
        $registration->save();
    }

    // Bangun URL verifikasi QR Code keaslian dokumen resmi kita
    $qrVerificationUrl = route('public.ereport.verify', ['token' => $registration->secure_verification_token]);

    return view('pesertabiasa.program.program_certificate_print', compact('program', 'registration', 'qrVerificationUrl'));
}

// 🔥 PUBLIC BYPASS SYSTEM: Membuka lembar transparansi data raport instan bagi instansi luar via scan QR Code
public function verifyEReport($token)
{
    // Temukan pendaftar berdasarkan token acak pengaman tanpa butuh auth login session
    $registration = Registration::with(['user', 'program'])->where('secure_verification_token', $token)->firstOrFail();

    return view('pesertabiasa.program.public_ereport_verify', compact('registration'));
}

/**
 * Menampilkan halaman Pos Pelayanan GTU (Konsultasi Program) untuk peserta
 */
public function showGtuConsultation($id)
{
    $program = Program::findOrFail($id);
    $registration = Registration::where('user_id', auth()->id())->where('program_id', $id)->firstOrFail();

    $consultations = \App\Models\GtuConsultation::where('program_id', $id)
        ->where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return view('pesertabiasa.program.gtu_consultation', compact('program', 'registration', 'consultations'));
}

/**
 * Menyimpan pertanyaan konsultasi GTU baru dan mengirimkan email ke email GTU program
 */
public function submitGtuConsultation(Request $request, $id)
{
    $program = Program::findOrFail($id);
    
    // Pastikan terdaftar
    Registration::where('user_id', auth()->id())->where('program_id', $id)->firstOrFail();

    $request->validate([
        'subject' => 'required|string|max:255',
        'question' => 'required|string|max:2000'
    ]);

    $consultation = \App\Models\GtuConsultation::create([
        'program_id' => $id,
        'user_id' => auth()->id(),
        'subject' => trim($request->subject),
        'question' => trim($request->question),
        'status' => 'pending'
    ]);

    // Kirim email ke email GTU Admin jika diset
    if ($program->gtu_email) {
        try {
            \Illuminate\Support\Facades\Mail::to($program->gtu_email)->send(
                new \App\Mail\GtuConsultationMail($consultation)
            );
        } catch (\Exception $e) {
            // Log error tapi jangan buat user crash demi ketahanan sistem
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email konsultasi GTU ke ' . $program->gtu_email . ': ' . $e->getMessage());
        }
    }

    return redirect()->route('programs.internal.gtu.index', $id)
        ->with('success', 'Pertanyaan Anda berhasil dikirim ke Pos Pelayanan GTU! Admin akan segera meninjau dan memberikan balasan.');
}

/**
 * Menyimpan harapan & motivasi dari pop-up dashboard program
 */
public function updateMotivation(Request $request, $id)
{
    $request->validate([
        'motivation' => 'required|string|min:10|max:2000'
    ], [
        'motivation.required' => 'Harapan & Motivasi wajib diisi!',
        'motivation.min' => 'Harapan & Motivasi minimal berisi 10 karakter!'
    ]);

    $registration = Registration::where('user_id', auth()->id())->where('program_id', $id)->firstOrFail();
    $registration->update([
        'motivation' => trim($request->motivation)
    ]);

    return redirect()->route('programs.internal.dashboard', $id)
        ->with('success', 'Harapan & Motivasi Anda berhasil disimpan! Terima kasih.');
}
}
