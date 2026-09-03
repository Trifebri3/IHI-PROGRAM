<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\BiodataController;
use App\Http\Controllers\SuperAdmin\SuperAnnouncementController;
use App\Http\Controllers\SuperAdmin\SuperEventController;
use App\Http\Controllers\AdminProgram\ProgramWorkspaceController;
use App\Http\Controllers\AdminProgram\WorkspaceMonitorController;
use App\Http\Controllers\AdminProgram\ParticipantProfileController;
use App\Http\Controllers\AdminProgram\CertificateManagementController;
use App\Http\Controllers\Peserta\ProgramApplyController;
use App\Http\Controllers\Peserta\ProgramDashboardController;
use App\Http\Controllers\Peserta\PesertaEventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\IdentityGateController;
use App\Http\Controllers\IdentitasUserController;
use App\Http\Controllers\Public\PublicStatisticController;

/*
|--------------------------------------------------------------------------
| 1. GUEST & LANDING ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $activeIklan = \App\Models\Announcement::where('is_active', true)->latest()->first();
    $pinnedProgram = \App\Models\Program::where('is_open', true)->where('is_pinned', true)->first();
    if ($pinnedProgram) {
        $openPrograms = \App\Models\Program::where('is_open', true)->where('id', '!=', $pinnedProgram->id)->get();
    } else {
        $openPrograms = \App\Models\Program::where('is_open', true)->get();
    }
    $closedPrograms = \App\Models\Program::where('is_open', false)->get();
    $highlights = \App\Models\PublicHighlight::where('is_active', true)->latest()->get();
    
    // Weighted random selection: 80% chance for verified participant registration, 20% chance for any registration
    $featuredRegistration = null;
    if (rand(1, 100) <= 80) {
        $featuredRegistration = \App\Models\Registration::whereHas('user.verification', function($q) {
            $q->where('status', 'verified');
        })->whereNotNull('motivation')
          ->where('motivation', '!=', '')
          ->with(['user.profile', 'program'])
          ->inRandomOrder()
          ->first();
    }

    if (!$featuredRegistration) {
        $featuredRegistration = \App\Models\Registration::whereNotNull('motivation')
          ->where('motivation', '!=', '')
          ->with(['user.profile', 'program'])
          ->inRandomOrder()
          ->first();
    }

    // Fetch upcoming events for landing page slider
    $events = \App\Models\Event::where('event_date', '>=', now()->toDateString())->orderBy('event_date', 'asc')->get();

    // Fetch 10 random registrations to populate the interactive capsules ("nozzles")
    $randomRegistrations = \App\Models\Registration::whereNotNull('motivation')
      ->where('motivation', '!=', '')
      ->with(['user.profile', 'user.verification', 'program'])
      ->inRandomOrder()
      ->limit(10)
      ->get();

    // Increment views count for all loaded highlights
    if ($highlights->isNotEmpty()) {
        \App\Models\PublicHighlight::whereIn('id', $highlights->pluck('id'))->increment('views_count');
    }

    return view('welcome', compact('activeIklan', 'pinnedProgram', 'openPrograms', 'closedPrograms', 'highlights', 'featuredRegistration', 'randomRegistrations', 'events'));
});

Route::get('/testimonial/{id}', function ($id) {
    $registration = \App\Models\Registration::with(['user.profile', 'program'])->findOrFail($id);
    return view('public.testimonial-share', compact('registration'));
})->name('public.testimonial.share');

Route::get('/highlights/{id}/click', function ($id) {
    $highlight = \App\Models\PublicHighlight::findOrFail($id);
    $highlight->increment('clicks_count');
    return redirect()->away($highlight->link_url);
})->name('public.highlights.click');

Route::get('/events/{id}', [\App\Http\Controllers\Public\PublicEventController::class, 'show'])->name('public.events.show')->whereNumber('id');
Route::post('/events/{id}/register', [\App\Http\Controllers\Public\PublicEventController::class, 'register'])->name('public.events.register')->whereNumber('id');
Route::get('/events/ticket/{ticket_number}', [\App\Http\Controllers\Public\PublicEventController::class, 'showTicket'])->name('public.events.ticket');
Route::post('/events/{id}/register-fast', [\App\Http\Controllers\Public\PublicEventController::class, 'registerFast'])->name('public.events.register_fast')->whereNumber('id');
Route::post('/events/autofill-account', [\App\Http\Controllers\Public\PublicEventController::class, 'autofillAccount'])->name('public.events.autofill_account');
Route::get('/events/{id}/attendance', [\App\Http\Controllers\Public\PublicEventController::class, 'showAttendance'])->name('public.events.attendance')->whereNumber('id');
Route::post('/events/{id}/attendance/verify-ticket', [\App\Http\Controllers\Public\PublicEventController::class, 'verifyTicketForAttendance'])->name('public.events.attendance.verify')->whereNumber('id');
Route::post('/events/{id}/attendance/submit', [\App\Http\Controllers\Public\PublicEventController::class, 'submitAttendance'])->name('public.events.attendance.submit')->whereNumber('id');





/*
|--------------------------------------------------------------------------
| 2. CORE AUTHENTICATED ROUTES (Breeze Default Profiles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 3. PARTICIPANT WORKFLOW - STEP 1 (Bebas Hambatan Blocker Profil)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/complete', function () {
        return view('pesertabiasa.profile.complete');
    })->name('profile.complete');
});

/*
|--------------------------------------------------------------------------
| 4. PARTICIPANT WORKFLOW - STEP 2 (Dilindungi Blocker Profil Lengkap)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'profile.completed', 'check.profile', 'terms.accepted'])->group(function () {
    // Dashboard Utama & Verifikasi Centang Biru
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/verification', function() { return view('pesertabiasa.verification.index'); })->name('verification.index');

    // Katalog Pendaftaran Program (Versi Controller Native)
    Route::get('/programs/catalog', [ProgramApplyController::class, 'index'])->name('programs.catalog');
    Route::get('/programs/{id}/apply', [ProgramApplyController::class, 'showApply'])->name('program.apply');
    Route::post('/programs/{id}/apply/store', [ProgramApplyController::class, 'submitApply'])->name('program.apply.store');
    Route::post('/programs/{id}/apply/draft', [ProgramApplyController::class, 'saveDraft'])->name('program.apply.draft');

    // Halaman Blocker Pengumuman Khusus (Bebas dari Blocker internal agar tidak looping redirect)
    Route::get('/programs/{id}/announcement-gate', [ProgramDashboardController::class, 'showAnnouncementGate'])->name('programs.internal.announcement.gate');
    Route::post('/programs/{id}/announcement-gate/{announcementId}/confirm', [ProgramDashboardController::class, 'confirmAnnouncementRead'])->name('programs.internal.announcement.confirm');

    // Halaman Blocker Pengumuman Global Super Admin
    Route::get('/global-announcement-gate', [ProgramDashboardController::class, 'showGlobalAnnouncementGate'])->name('announcements.global.gate');
    Route::post('/global-announcement-gate/{announcementId}/confirm', [ProgramDashboardController::class, 'confirmGlobalAnnouncementRead'])->name('announcements.global.confirm');

    // Pengisian Form Biodata Tambahan Khusus Program (Menghalangi Dashboard Internal)
    Route::get('/programs/{id}/biodataprogram', [ProgramDashboardController::class, 'showBiodataForm'])->name('programs.internal.biodata');
    Route::post('/programs/{id}/biodataprogram/store', [ProgramDashboardController::class, 'submitBiodataForm'])->name('programs.internal.biodata.store');

    // AREA SECURE: Dashboard Internal Eksklusif Program (Dilindungi Pertahanan Berlapis)
    Route::middleware(['program.biodata.completed', 'announcement.read'])->group(function () {
        Route::get('/programs/{id}/internal-dashboard', [ProgramDashboardController::class, 'index'])->name('programs.internal.dashboard');
        Route::get('/programs/{id}/internal-dashboard/certificate/download', [ProgramDashboardController::class, 'printProgramCertificate'])->name('programs.internal.certificate.print');
        
        // Pos Pelayanan GTU & Konsultasi
        Route::get('/programs/{id}/internal-dashboard/konsultasi', [ProgramDashboardController::class, 'showGtuConsultation'])->name('programs.internal.gtu.index');
        Route::post('/programs/{id}/internal-dashboard/konsultasi/store', [ProgramDashboardController::class, 'submitGtuConsultation'])->name('programs.internal.gtu.store');

        // Update Motivation (Pop-up Dashboard)
        Route::post('/programs/{id}/internal-dashboard/motivation', [ProgramDashboardController::class, 'updateMotivation'])->name('programs.internal.motivation.update');
    });
});

/*
|--------------------------------------------------------------------------
| 5. PARTICIPANT EVENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'profile.completed', 'check.profile'])->group(function () {
    Route::get('/events/catalog', [PesertaEventController::class, 'index'])->name('events.catalog');
    Route::get('/events/{id}/register', [PesertaEventController::class, 'showRegisterForm'])->name('events.register.form');
    Route::post('/events/{id}/register/store', [PesertaEventController::class, 'submitRegistration'])->name('events.register.store');
    Route::get('/events/{id}/dashboard', [PesertaEventController::class, 'showDashboard'])->name('events.dashboard');
    Route::post('/events/{id}/attendance/verify', [PesertaEventController::class, 'submitAttendance'])->name('events.attendance.verify');
    Route::get('/events/{id}/certificate/print', [PesertaEventController::class, 'printCertificate'])->name('events.certificate.print');

    // Alumni Portal Routes
    Route::get('/alumni', [\App\Http\Controllers\Peserta\AlumniPortalController::class, 'index'])->name('peserta.alumni.index');
    Route::get('/alumni/verify-manual', [\App\Http\Controllers\Peserta\AlumniPortalController::class, 'showVerifyForm'])->name('peserta.alumni.verify.form');
    Route::post('/alumni/verify-manual', [\App\Http\Controllers\Peserta\AlumniPortalController::class, 'submitVerifyRequest'])->name('peserta.alumni.verify.store');
    Route::get('/alumni/certificate/{uuid}/download', [\App\Http\Controllers\Peserta\AlumniPortalController::class, 'downloadCertificate'])->name('peserta.alumni.certificate.download');
});

/*
|--------------------------------------------------------------------------
| 6. ADMIN PROGRAM DESK (Operational Otoritas & Workspace)
|--------------------------------------------------------------------------
*/


Route::prefix('adminprogram')
    ->name('adminprogram.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/my-programs', function() {
            return view('adminprogram.program.index');
        })->name('programs.index');

        Route::get('/workspace-monitor', [WorkspaceMonitorController::class, 'index'])->name('workspace_monitor');

        // CRUD Alur Tahapan Kompetisi Workspace (Native Controller)
        Route::get('/programs/{id}/workspace', [ProgramWorkspaceController::class, 'show'])->name('programs.workspace');
        Route::post('/programs/{id}/workspace/stage/store', [ProgramWorkspaceController::class, 'storeStage'])->name('workspace.stage.store');
        Route::patch('/programs/{id}/workspace/stage/{stageId}/update', [ProgramWorkspaceController::class, 'updateStage'])->name('workspace.stage.update');
        Route::delete('/programs/{id}/workspace/stage/{stageId}/delete', [ProgramWorkspaceController::class, 'deleteStage'])->name('workspace.stage.delete');
        Route::post('/programs/{id}/workspace/stage/{stageId}/toggle-lock', [ProgramWorkspaceController::class, 'toggleLockStage'])->name('workspace.stage.toggle_lock');

// SAKLAR HARDCORE: Sekarang masuk ke grup dan menggunakan ProgramWorkspaceController yang benar
        Route::post('/programs/{id}/toggle-registration', [ProgramWorkspaceController::class, 'toggleRegistration'])
            ->name('programs.toggle');
    
    
        // Form Builder - Perakitan Field Kustom Pendaftaran
        Route::post('/programs/{id}/workspace/stage/{stageId}/field/store', [ProgramWorkspaceController::class, 'storeFormField'])->name('workspace.field.store');
        Route::post('/programs/{id}/workspace/stage/{stageId}/field/{index}/update', [ProgramWorkspaceController::class, 'updateFormField'])->name('workspace.field.update');
        Route::post('/programs/{id}/workspace/stage/{stageId}/field/{index}/move-up', [ProgramWorkspaceController::class, 'moveFormFieldUp'])->name('workspace.field.move_up');
        Route::post('/programs/{id}/workspace/stage/{stageId}/field/{index}/move-down', [ProgramWorkspaceController::class, 'moveFormFieldDown'])->name('workspace.field.move_down');
        Route::delete('/programs/{id}/workspace/stage/{stageId}/field/{index}/delete', [ProgramWorkspaceController::class, 'deleteFormField'])->name('workspace.field.delete');

        // Form Builder - Perakitan Google Form Biodata Wajib Tambahan Program
        Route::post('/programs/{id}/workspace/biodata/store', [ProgramWorkspaceController::class, 'storeBiodataSchema'])->name('workspace.biodata.store');
        Route::delete('/programs/{id}/workspace/biodata/{schemaId}/delete', [ProgramWorkspaceController::class, 'deleteBiodataSchema'])->name('workspace.biodata.delete');

        // Broadcasting - Pusat Penyiaran Berkas & Pesan Kustom Admin
        Route::post('/programs/{id}/workspace/announcement/store', [ProgramWorkspaceController::class, 'storeAnnouncement'])->name('workspace.announcement.store');
        Route::post('/programs/{id}/workspace/announcement/{announcementId}/update', [ProgramWorkspaceController::class, 'updateAnnouncement'])->name('workspace.announcement.update');
        Route::delete('/programs/{id}/workspace/announcement/{announcementId}/delete', [ProgramWorkspaceController::class, 'deleteAnnouncement'])->name('workspace.announcement.delete');

        Route::get('/programs/{id}/applicants/{registrationId}', [ProgramWorkspaceController::class, 'showApplicantSubmission'])->name('programs.applicant.show');
        Route::post('/programs/{id}/applicants/{registrationId}/evaluate', [ProgramWorkspaceController::class, 'evaluateApplicant'])->name('programs.applicant.evaluate');
        Route::post('/programs/{id}/applicants/{registrationId}/instant-pass', [ProgramWorkspaceController::class, 'instantPass'])->name('programs.applicant.instant-pass');
        Route::post('/programs/{id}/applicants/{registrationId}/reset-answers', [ProgramWorkspaceController::class, 'resetApplicantAnswers'])->name('programs.applicant.reset-answers');
        Route::post('/programs/{id}/applicants/{registrationId}/reset-single-answer', [ProgramWorkspaceController::class, 'resetApplicantSingleAnswer'])->name('programs.applicant.reset-single-answer');

        // Konfigurasi Struktur JP & Skema Kriteria Nilai Program
        Route::post('/programs/{id}/workspace/academic/schema', [ProgramWorkspaceController::class, 'storeAcademicSchema'])->name('workspace.academic.schema');
        Route::post('/programs/{id}/workspace/certificate/upload', [ProgramWorkspaceController::class, 'uploadProgramCertificate'])->name('workspace.certificate.upload');

        // Input Nilai Finis per Individu Peserta Lolos
        Route::post('/programs/{id}/applicants/{registrationId}/scores/save', [ProgramWorkspaceController::class, 'saveApplicantScores'])->name('programs.applicant.scores.save');

        // Pos Pelayanan GTU (Email & Reply)
        Route::post('/programs/{id}/workspace/gtu-email', [ProgramWorkspaceController::class, 'updateGtuEmail'])->name('workspace.gtu.email');
        Route::post('/programs/{id}/workspace/gtu-reply/{consultationId}', [ProgramWorkspaceController::class, 'replyGtuConsultation'])->name('workspace.gtu.reply');

        // Alumni Management Routes
        Route::get('/alumni', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'index'])->name('alumni.index');
        Route::get('/alumni/{id}/extra-info', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'editExtraInfo'])->name('alumni.edit-extra');
        Route::post('/alumni/{id}/extra-info', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'updateExtraInfo'])->name('alumni.update-extra');
        Route::post('/alumni/register-and-pass', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'registerAndPass'])->name('alumni.register-and-pass');
        Route::get('/alumni-templates', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'showTemplates'])->name('alumni.templates');
        Route::post('/alumni-templates', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'storeTemplate'])->name('alumni.templates.store');
        Route::get('/alumni-verifications', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'showVerificationRequests'])->name('alumni.verifications');
        Route::post('/alumni-verifications/{id}/process', [\App\Http\Controllers\AdminProgram\AlumniManagementController::class, 'processVerification'])->name('alumni.verifications.process');

        // Export & Recap Routes
        Route::get('/programs/{id}/export/stage/{stageId}/excel', [ProgramWorkspaceController::class, 'exportStageExcel'])->name('workspace.export.stage.excel');
        Route::get('/programs/{id}/export/stage/{stageId}/pdf', [ProgramWorkspaceController::class, 'exportStagePdf'])->name('workspace.export.stage.pdf');
        Route::get('/programs/{id}/export/user/{registrationId}/excel', [ProgramWorkspaceController::class, 'exportUserExcel'])->name('workspace.export.user.excel');
        Route::get('/programs/{id}/export/user/{registrationId}/pdf', [ProgramWorkspaceController::class, 'exportUserPdf'])->name('workspace.export.user.pdf');
        Route::post('/programs/{id}/workspace/submissions/{submissionId}/reset', [ProgramWorkspaceController::class, 'resetSubmission'])->name('workspace.submission.reset');
        Route::post('/programs/{id}/workspace/reset-all-applicants', [ProgramWorkspaceController::class, 'resetAllApplicants'])->name('workspace.reset_all_applicants');
        Route::post('/programs/{id}/workspace/reset-applicant', [ProgramWorkspaceController::class, 'resetSpecificApplicant'])->name('workspace.reset_applicant');
        Route::post('/programs/{id}/workspace/update-checking', [ProgramWorkspaceController::class, 'updateCheckingMetadata'])->name('workspace.update_checking');

        // Database Peserta & Profil
        Route::get('/participants', [ParticipantProfileController::class, 'index'])->name('participants.index');
        Route::post('/participants/bulk-action', [ParticipantProfileController::class, 'bulkAction'])->name('participants.bulk-action');
        Route::post('/participants/bulk-ni', [ParticipantProfileController::class, 'bulkGenerateNi'])->name('participants.bulk-ni');
        Route::get('/participants/export-ni-template', [ParticipantProfileController::class, 'exportNiTemplate'])->name('participants.ni.export-template');
        Route::post('/participants/import-ni', [ParticipantProfileController::class, 'importNi'])->name('participants.ni.import');
        Route::get('/participants/{id}', [ParticipantProfileController::class, 'show'])->name('participants.show');
        Route::post('/participants/{id}/update-profile', [ParticipantProfileController::class, 'updateProfile'])->name('participants.update-profile');
        Route::post('/participants/{id}/ni', [ParticipantProfileController::class, 'updateNi'])->name('participants.ni.update');
        Route::post('/participants/{id}/toggle-block', [ParticipantProfileController::class, 'toggleBlock'])->name('participants.toggle-block');

        // Sertifikat & Piagam
        Route::get('/certificates', [CertificateManagementController::class, 'index'])->name('certificates.index');
        Route::post('/certificates/bulk-generate', [CertificateManagementController::class, 'bulkGenerate'])->name('certificates.bulk-generate');
        Route::post('/certificates/bulk-upload', [CertificateManagementController::class, 'bulkUpload'])->name('certificates.bulk-upload');
        Route::post('/participants/{id}/upload-certificate', [CertificateManagementController::class, 'singleUpload'])->name('certificates.single-upload');
        Route::delete('/certificates/{id}', [CertificateManagementController::class, 'destroy'])->name('certificates.destroy');
    });

/*
|--------------------------------------------------------------------------
| 7. SUPER ADMIN DESK (Global Governance)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\SuperAdmin\FormBuilderController;

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // Manajemen Pembuat Form Biodata Pusat (EAV Engine)
        Route::get('/biodata', [BiodataController::class, 'index'])->name('biodata.index');
        Route::get('/dashboard/program-stats/{programId}', [DashboardController::class, 'getProgramStats'])->name('dashboard.program-stats');
        Route::get('/form-builder', [FormBuilderController::class, 'index'])->name('form-builder.index');
	Route::post('/form-builder/store', [FormBuilderController::class, 'store'])->name('form-builder.store-legacy');
        Route::delete('/form-builder/{id}/delete', [FormBuilderController::class, 'destroy'])->name('form-builder.delete');

        // Manajemen Master Pembuatan Program & Distribusi Delegasi Otoritas
        Route::get('/programs', function() {
            return view('superadmin.program.index');
        })->name('programs.index');

        // Penyiaran Maklumat Darurat Berskala Global Seluruh Sistem / Per Program
        Route::get('/announcements', [SuperAnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements/store', [SuperAnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{id}/delete', [SuperAnnouncementController::class, 'destroy'])->name('announcements.delete');

        // Manajemen Sorotan & Kegiatan Publik (Beranda Utama)
        Route::get('/public-highlights', [\App\Http\Controllers\SuperAdmin\PublicHighlightController::class, 'index'])->name('public-highlights.index');
        Route::post('/public-highlights/store', [\App\Http\Controllers\SuperAdmin\PublicHighlightController::class, 'store'])->name('public-highlights.store');
        Route::post('/public-highlights/{id}/toggle', [\App\Http\Controllers\SuperAdmin\PublicHighlightController::class, 'toggle'])->name('public-highlights.toggle');
        Route::delete('/public-highlights/{id}/delete', [\App\Http\Controllers\SuperAdmin\PublicHighlightController::class, 'destroy'])->name('public-highlights.delete');
        Route::put('/public-highlights/{id}/update', [\App\Http\Controllers\SuperAdmin\PublicHighlightController::class, 'update'])->name('public-highlights.update');

        // Event Management
        Route::get('/events', [SuperEventController::class, 'index'])->name('events.index');
        Route::post('/events/store', [SuperEventController::class, 'store'])->name('events.store');
        Route::delete('/events/{id}/delete', [SuperEventController::class, 'destroy'])->name('events.delete');
        Route::get('/events/{id}/dashboard', [SuperEventController::class, 'showDashboard'])->name('events.dashboard');
        Route::post('/events/{id}/form-builder/store', [SuperEventController::class, 'storeFormSchema'])->name('events.form.store');
        Route::delete('/events/{id}/form-builder/{index}/delete', [SuperEventController::class, 'deleteFormSchema'])->name('events.form.delete');
        Route::post('/events/{id}/attendance-form/store', [SuperEventController::class, 'storeAttendanceFormSchema'])->name('events.attendance_form.store');
        Route::delete('/events/{id}/attendance-form/{index}/delete', [SuperEventController::class, 'deleteAttendanceFormSchema'])->name('events.attendance_form.delete');
        Route::post('/events/{id}/attendance/toggle', [SuperEventController::class, 'toggleAttendance'])->name('events.attendance.toggle');
        Route::post('/events/{id}/attendance/settings', [SuperEventController::class, 'updateAttendanceSettings'])->name('events.attendance.settings');
        Route::get('/events/{id}/scanner', [SuperEventController::class, 'showScanner'])->name('events.scanner');
        Route::get('/events/{id}/recap/print', [SuperEventController::class, 'printRecap'])->name('events.recap.print');
        Route::get('/events/scan-checkin/{ticket_number}', [SuperEventController::class, 'scanCheckin'])->name('events.scan-checkin');
        Route::post('/events/{id}/certificate/upload', [SuperEventController::class, 'uploadCertificateTemplate'])->name('events.certificate.upload');



    Route::get('/form-builder', [FormBuilderController::class, 'index'])->name('form-builder.index');
    Route::post('/form-builder', [FormBuilderController::class, 'store'])->name('form-builder.store');
    Route::delete('/form-builder/{id}', [FormBuilderController::class, 'destroy'])->name('form-builder.destroy');

    // Gerbang OTP Kode Rahasia (Secret Gate)
    Route::get('/secret-gate', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'showSecretGate'])->name('secret-gate');
    Route::post('/secret-gate/verify', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'verifySecretGate'])->name('secret-gate.verify');
    Route::post('/secret-gate/lock', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'lockConsole'])->name('secret-gate.lock');

    // System Intelligence & Optimization Consoles (Protected by Secret Code & Role)
    Route::middleware(['secret.console'])->group(function() {
        Route::get('/system-intelligence', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'index'])->name('system-intelligence.index');
        Route::get('/system-intelligence/api', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'getRealtimeApi'])->name('system-intelligence.api');
        Route::post('/system-intelligence/self-healing', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'triggerSelfHealing'])->name('system-intelligence.self-healing');
        Route::post('/system-intelligence/refresh-all', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'refreshSystemTotal'])->name('system-intelligence.refresh-all');
        Route::post('/system-intelligence/toggle-user-block/{id}', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'toggleUserBlock'])->name('system-intelligence.toggle-user-block');
        Route::post('/system-intelligence/settings', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'saveSettings'])->name('system-intelligence.save-settings');
        Route::post('/system-intelligence/test-ai', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'testAiConnection'])->name('system-intelligence.test-ai');
        Route::post('/system-intelligence/errors/{id}/status', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'updateErrorStatus'])->name('system-intelligence.update-error-status');
        Route::get('/system-intelligence/export-excel', [\App\Http\Controllers\Admin\SystemIntelligenceController::class, 'exportExcel'])->name('system-intelligence.export-excel');

        // Modul Baru: Optimisasi Admin
        Route::get('/optimization', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'index'])->name('optimization.index');
        Route::get('/optimization/api', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'getRealtimeApi'])->name('optimization.api');
        Route::post('/optimization/check-system', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'checkSystemNow'])->name('optimization.check-system');
        Route::post('/optimization/toggle-maintenance', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'toggleMaintenanceMode'])->name('optimization.toggle-maintenance');
        Route::post('/optimization/toggle-defense', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'toggleDefenseMode'])->name('optimization.toggle-defense');
        Route::post('/optimization/toggle-secret-defense', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'toggleSecretDefense'])->name('optimization.toggle-secret-defense');
        Route::post('/optimization/run-test', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'runFeatureTest'])->name('optimization.run-test');
        Route::post('/optimization/test-gatekeeper-upload', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'testGatekeeperUpload'])->name('optimization.test-gatekeeper-upload');
        Route::get('/optimization/download-test-report', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'downloadTestReport'])->name('optimization.download-test-report');

        // Privileged Access Security Gate & Recovery Links
        Route::post('/privileged-access/verify-gate', [\App\Http\Controllers\SuperAdmin\PrivilegedAccessController::class, 'verifySecurityGate'])->name('privileged-access.verify-gate');
        Route::post('/privileged-access/generate-recovery', [\App\Http\Controllers\SuperAdmin\PrivilegedAccessController::class, 'generateRecoveryLink'])->name('privileged-access.generate-recovery');
        Route::post('/privileged-access/update-password', [\App\Http\Controllers\SuperAdmin\PrivilegedAccessController::class, 'updateGatePassword'])->name('privileged-access.update-password');
    });
    });









    use App\Http\Controllers\SuperAdmin\AdminProgramController;

Route::middleware(['auth', 'verified'])->prefix('superadmin')->group(function () {
    Route::get('/programs', [AdminProgramController::class, 'index'])->name('superadmin.programs.index');
    Route::post('/programs', [AdminProgramController::class, 'store'])->name('superadmin.programs.store');
    Route::put('/programs/{id}', [AdminProgramController::class, 'update'])->name('superadmin.programs.update');
    Route::delete('/programs/{id}', [AdminProgramController::class, 'destroy'])->name('superadmin.programs.destroy');
    Route::post('/programs/{id}/pin', [AdminProgramController::class, 'togglePin'])->name('superadmin.programs.pin');
    
    // Green Forum Moderation & Analytics Desk
    Route::get('/forum', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'index'])->name('superadmin.forum.index');
    Route::post('/forum/report/{id}/resolve', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'resolveReport'])->name('superadmin.forum.report.resolve');
    Route::delete('/forum/discussion/{id}/takedown', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'takedownDiscussion'])->name('superadmin.forum.discussion.takedown');
    Route::delete('/forum/comment/{id}/takedown', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'takedownComment'])->name('superadmin.forum.comment.takedown');
    Route::post('/forum/user/{id}/restrict', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'toggleRestrictUser'])->name('superadmin.forum.user.restrict');
    Route::post('/forum/user/{id}/block', [\App\Http\Controllers\SuperAdmin\SuperForumController::class, 'toggleBlockUser'])->name('superadmin.forum.user.block');

});

/*
|--------------------------------------------------------------------------
| 8. FORUM ROUTES
|--------------------------------------------------------------------------
*/

// Public View-Only Topic Route (Dapat diakses siapapun tanpa login)
Route::get('/forum/topic/{id}', [ForumController::class, 'showPublicTopic'])->name('forum.public.show');
Route::post('/forum/discussion/{id}/share', [ForumController::class, 'recordShare'])->name('forum.discussion.share');

Route::middleware(['auth'])->group(function () {
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum/discussion', [ForumController::class, 'storeDiscussion'])->name('forum.discussion.store');
    Route::post('/forum/comment/{id}', [ForumController::class, 'storeComment'])->name('forum.comment.store');
    Route::post('/forum/discussion/{id}/reaction', [ForumController::class, 'toggleReaction'])->name('forum.discussion.reaction');
    Route::post('/forum/discussion/{id}/repost', [ForumController::class, 'repostDiscussion'])->name('forum.discussion.repost');
    Route::post('/forum/discussion/{id}/favorite', [ForumController::class, 'toggleFavorite'])->name('forum.discussion.favorite');
    Route::post('/forum/discussion/{id}/report', [ForumController::class, 'reportDiscussion'])->name('forum.discussion.report');
    Route::delete('/forum/discussion/{id}', [ForumController::class, 'destroyDiscussion'])->name('forum.discussion.destroy');

    // Notifikasi Pengguna (Green Forum & Portal)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
});

/*
|--------------------------------------------------------------------------
| 9. IDENTITY GATE ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/identity-gate', [IdentityGateController::class, 'showIdentityForm'])->name('identity.gate');
Route::post('/identity-gate/store', [IdentityGateController::class, 'storeIdentity'])->name('identity.store');

// API Proxy Wilayah Indonesia (EMSIFA) untuk keandalan koneksi & CORS-free
Route::get('/api-wilayah/provinces', [IdentityGateController::class, 'getProvinces']);
Route::get('/api-wilayah/regencies/{provinceId}', [IdentityGateController::class, 'getRegencies']);
Route::get('/api-wilayah/districts/{regencyId}', [IdentityGateController::class, 'getDistricts']);
Route::get('/api-wilayah/villages/{districtId}', [IdentityGateController::class, 'getVillages']);

/*
|--------------------------------------------------------------------------
| 10. USER IDENTITY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/identitas', [IdentitasUserController::class, 'index'])->name('identitas.index');
    Route::put('/identitas', [IdentitasUserController::class, 'update'])->name('identitas.update');
    Route::put('/identitas/password', [IdentitasUserController::class, 'updatePassword'])->name('identitas.password');
});

/*
|--------------------------------------------------------------------------
| 11. PUBLIC ROUTES (Statistik & Informasi)
|--------------------------------------------------------------------------
*/
Route::get('/public/program', [PublicStatisticController::class, 'index'])->name('public.program.index');
Route::get('/public/program/map-data-all', [PublicStatisticController::class, 'mapDataAll'])->name('public.program.map.data.all');
Route::get('/public/program/{id}/stats', [PublicStatisticController::class, 'showProgramStats'])->name('public.program.stats');
Route::get('/public/program/{id}/map-data', [PublicStatisticController::class, 'mapData'])->name('public.program.map.data');
Route::get('/public/program/{id}/participants', [PublicStatisticController::class, 'participants'])->name('public.program.participants');

/*
|--------------------------------------------------------------------------
| 12. PUBLIC VERIFICATION ROUTE (QR Code tanpa login)
|--------------------------------------------------------------------------
*/
Route::get('/verification/e-report/{token}', [ProgramDashboardController::class, 'verifyEReport'])->name('public.ereport.verify');

Route::get('/verify-alumni/{uuid}', [\App\Http\Controllers\Public\AlumniVerificationController::class, 'verify'])->name('public.alumni.verify');
Route::get('/verify-alumni/{uuid}/download', [\App\Http\Controllers\Public\AlumniVerificationController::class, 'downloadCertificate'])->name('public.alumni.certificate.download');

/*
|--------------------------------------------------------------------------
| 13. STATIC PAGE ROUTES
|--------------------------------------------------------------------------
*/
Route::view('/syarat-ketentuan', 'syarat-ketentuan')->name('syarat-ketentuan');
Route::view('/kebijakan-privasi', 'kebijakan-privasi')->name('kebijakan-privasi');
Route::view('/faq', 'faq')->name('faq');
Route::redirect('/tentang-kami', 'https://instituthijauindonesia.or.id', 301)->name('tentang-kami');
Route::redirect('/kontak', 'https://instituthijauindonesia.or.id/#contact', 301)->name('kontak');

/*
|--------------------------------------------------------------------------
| 14. BREEZE AUTH ROUTES PIPELINE
|--------------------------------------------------------------------------
*/



use App\Http\Controllers\TermsController;
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::view('/terms-agreement', 'auth.terms-agreement')->name('terms.show');
    Route::post('/terms-agreement', [TermsController::class, 'store'])->name('terms.store');
});



use App\Http\Controllers\Peserta\UserBiodataController;
use App\Http\Controllers\Auth\EmailMitigationController;

Route::middleware(['auth'])->group(function () {
    Route::get('/biodata', [UserBiodataController::class, 'create'])->name('biodata.create');
    Route::post('/biodata', [UserBiodataController::class, 'store'])->name('biodata.store');
    Route::post('/verify-email/mitigation-ticket', [EmailMitigationController::class, 'submitTicket'])->name('verification.mitigation.store');
    Route::post('/iklan/track-view/{id}', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'trackView'])->name('iklan.track-view');
});

use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\SuperPowerController;

Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    Route::get('/users/export', [UserController::class, 'export'])->name('superadmin.users.export');
    Route::post('/users/toggle-mitigation-global', [UserController::class, 'toggleMitigationGlobal'])->name('superadmin.users.toggle-mitigation-global');
    Route::post('/users/bulk', [UserController::class, 'bulkAction'])->name('superadmin.users.bulk');
    Route::get('/users', [UserController::class, 'index'])->name('superadmin.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('superadmin.users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('superadmin.users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('superadmin.users.delete');
    Route::match(['get', 'post'], '/users/{id}/impersonate', [UserController::class, 'impersonate'])->name('superadmin.users.impersonate');
    Route::post('/users/{id}/toggle-block', [UserController::class, 'toggleBlock'])->name('superadmin.users.toggle-block');
    Route::post('/users/{id}/bypass-email', [UserController::class, 'bypassEmail'])->name('superadmin.users.bypass-email');
    Route::post('/users/{id}/force-password', [UserController::class, 'forcePassword'])->name('superadmin.users.force-password');

    // Super Power Panel Tools (Protected by Secret Code)
    Route::middleware(['secret.console'])->group(function() {
        Route::get('/power-panel', [SuperPowerController::class, 'index'])->name('superadmin.power-panel.index');
        Route::post('/power-panel/generate-dummy', [SuperPowerController::class, 'generateDummyUsers'])->name('superadmin.power-panel.generate-dummy');
        Route::post('/power-panel/delete-dummy', [SuperPowerController::class, 'deleteAllDummyUsers'])->name('superadmin.power-panel.delete-dummy');
        Route::post('/power-panel/import-users', [SuperPowerController::class, 'importUsers'])->name('superadmin.power-panel.import-users');
        Route::get('/power-panel/download-template', [SuperPowerController::class, 'downloadCsvTemplate'])->name('superadmin.power-panel.download-template');
        Route::post('/power-panel/force-register', [SuperPowerController::class, 'forceRegisterUsers'])->name('superadmin.power-panel.force-register');
        Route::post('/power-panel/toggle-mitigation', [SuperPowerController::class, 'toggleMitigation'])->name('superadmin.power-panel.toggle-mitigation');
        Route::post('/power-panel/resolve-ticket/{id}', [SuperPowerController::class, 'resolveTicket'])->name('superadmin.power-panel.resolve-ticket');
    });
});

Route::post('/impersonate/stop', [\App\Http\Controllers\Public\ImpersonationController::class, 'stop'])->name('impersonate.stop');

use App\Http\Controllers\UserVerificationController;

Route::middleware(['auth'])->group(function () {
    Route::get('/verifikasi', [UserVerificationController::class, 'create'])->name('verification.create');
    Route::post('/verifikasi', [UserVerificationController::class, 'store'])->name('verification.store');
});


use App\Http\Controllers\SuperAdmin\AdminVerificationController;

Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('superadmin.verifications.index');
    Route::post('/verifications/{id}/approve', [AdminVerificationController::class, 'approve'])->name('superadmin.verifications.approve');
    Route::post('/verifications/{id}/reject', [AdminVerificationController::class, 'reject'])->name('superadmin.verifications.reject');
});
use App\Http\Controllers\Auth\GoogleController;

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);


use App\Http\Controllers\SuperAdmin\AnnouncementController;

Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    // Rute untuk mengelola Iklan
    Route::get('/iklan', [AnnouncementController::class, 'index'])->name('iklan.index');
    Route::post('/iklan', [AnnouncementController::class, 'store'])->name('iklan.store');
    Route::post('/iklan/{id}/toggle', [AnnouncementController::class, 'toggle'])->name('iklan.toggle');
    Route::delete('/iklan/{id}', [AnnouncementController::class, 'destroy'])->name('iklan.destroy');
});


use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::middleware(['auth', 'throttle:6,1'])->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->name('verification.send');
});


use Illuminate\Support\Facades\Artisan;

Route::get('/clear-view-darurat', function () {
    // Menjalankan perintah php artisan view:clear lewat kode
    Artisan::call('view:clear');
    return 'Cache view berhasil dibersihkan! Silakan cek kembali HP Anda.';
});




    


Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);


    
    
require __DIR__.'/auth.php';
