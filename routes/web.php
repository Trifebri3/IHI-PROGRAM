<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\BiodataController;
use App\Http\Controllers\SuperAdmin\SuperAnnouncementController;
use App\Http\Controllers\SuperAdmin\SuperEventController;
use App\Http\Controllers\AdminProgram\ProgramWorkspaceController;
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
    return view('welcome');
});





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
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
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
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/admin/verifications', function() {
        return view('adminprogram.verification.index');
    })->name('admin.verifications.index');
});

Route::prefix('adminprogram')
    ->name('adminprogram.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/my-programs', function() {
            return view('adminprogram.program.index');
        })->name('programs.index');

        // CRUD Alur Tahapan Kompetisi Workspace (Native Controller)
        Route::get('/programs/{id}/workspace', [ProgramWorkspaceController::class, 'show'])->name('programs.workspace');
        Route::post('/programs/{id}/workspace/stage/store', [ProgramWorkspaceController::class, 'storeStage'])->name('workspace.stage.store');
        Route::patch('/programs/{id}/workspace/stage/{stageId}/update', [ProgramWorkspaceController::class, 'updateStage'])->name('workspace.stage.update');
        Route::delete('/programs/{id}/workspace/stage/{stageId}/delete', [ProgramWorkspaceController::class, 'deleteStage'])->name('workspace.stage.delete');

// SAKLAR HARDCORE: Sekarang masuk ke grup dan menggunakan ProgramWorkspaceController yang benar
        Route::post('/programs/{id}/toggle-registration', [ProgramWorkspaceController::class, 'toggleRegistration'])
            ->name('programs.toggle');
    
    
        // Form Builder - Perakitan Field Kustom Pendaftaran
        Route::post('/programs/{id}/workspace/stage/{stageId}/field/store', [ProgramWorkspaceController::class, 'storeFormField'])->name('workspace.field.store');
        Route::delete('/programs/{id}/workspace/stage/{stageId}/field/{index}/delete', [ProgramWorkspaceController::class, 'deleteFormField'])->name('workspace.field.delete');

        // Form Builder - Perakitan Google Form Biodata Wajib Tambahan Program
        Route::post('/programs/{id}/workspace/biodata/store', [ProgramWorkspaceController::class, 'storeBiodataSchema'])->name('workspace.biodata.store');
        Route::delete('/programs/{id}/workspace/biodata/{schemaId}/delete', [ProgramWorkspaceController::class, 'deleteBiodataSchema'])->name('workspace.biodata.delete');

        // Broadcasting - Pusat Penyiaran Berkas & Pesan Kustom Admin
        Route::post('/programs/{id}/workspace/announcement/store', [ProgramWorkspaceController::class, 'storeAnnouncement'])->name('workspace.announcement.store');

        Route::get('/programs/{id}/applicants/{registrationId}', [ProgramWorkspaceController::class, 'showApplicantSubmission'])->name('programs.applicant.show');
        Route::post('/programs/{id}/applicants/{registrationId}/evaluate', [ProgramWorkspaceController::class, 'evaluateApplicant'])->name('programs.applicant.evaluate');
        Route::post('/programs/{id}/applicants/{registrationId}/instant-pass', [ProgramWorkspaceController::class, 'instantPass'])->name('programs.applicant.instant-pass');

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
        Route::post('/programs/{id}/workspace/update-checking', [ProgramWorkspaceController::class, 'updateCheckingMetadata'])->name('workspace.update_checking');
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

        // Manajemen Master Pembuatan Program & Distribusi Delegasi Otoritas
        Route::get('/programs', function() {
            return view('superadmin.program.index');
        })->name('programs.index');

        // Penyiaran Maklumat Darurat Berskala Global Seluruh Sistem / Per Program
        Route::get('/announcements', [SuperAnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements/store', [SuperAnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{id}/delete', [SuperAnnouncementController::class, 'destroy'])->name('announcements.delete');

        // Event Management
        Route::get('/events', [SuperEventController::class, 'index'])->name('events.index');
        Route::post('/events/store', [SuperEventController::class, 'store'])->name('events.store');
        Route::delete('/events/{id}/delete', [SuperEventController::class, 'destroy'])->name('events.delete');
        Route::get('/events/{id}/dashboard', [SuperEventController::class, 'showDashboard'])->name('events.dashboard');
        Route::post('/events/{id}/form-builder/store', [SuperEventController::class, 'storeFormSchema'])->name('events.form.store');
        Route::delete('/events/{id}/form-builder/{index}/delete', [SuperEventController::class, 'deleteFormSchema'])->name('events.form.delete');
        Route::post('/events/{id}/attendance/toggle', [SuperEventController::class, 'toggleAttendance'])->name('events.attendance.toggle');
        Route::get('/events/{id}/recap/print', [SuperEventController::class, 'printRecap'])->name('events.recap.print');
        Route::post('/events/{id}/certificate/upload', [SuperEventController::class, 'uploadCertificateTemplate'])->name('events.certificate.upload');



    Route::get('/form-builder', [FormBuilderController::class, 'index'])->name('form-builder.index');
    Route::post('/form-builder', [FormBuilderController::class, 'store'])->name('form-builder.store');
    Route::delete('/form-builder/{id}', [FormBuilderController::class, 'destroy'])->name('form-builder.destroy');







    });









    use App\Http\Controllers\SuperAdmin\AdminProgramController;

Route::middleware(['auth', 'verified'])->prefix('superadmin')->group(function () {
    Route::get('/programs', [AdminProgramController::class, 'index'])->name('superadmin.programs.index');
    Route::post('/programs', [AdminProgramController::class, 'store'])->name('superadmin.programs.store');
    Route::put('/programs/{id}', [AdminProgramController::class, 'update'])->name('superadmin.programs.update');
    Route::delete('/programs/{id}', [AdminProgramController::class, 'destroy'])->name('superadmin.programs.destroy');
    

});

/*
|--------------------------------------------------------------------------
| 8. FORUM ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum/discussion', [ForumController::class, 'storeDiscussion'])->name('forum.discussion.store');
    Route::post('/forum/comment/{id}', [ForumController::class, 'storeComment'])->name('forum.comment.store');
});

/*
|--------------------------------------------------------------------------
| 9. IDENTITY GATE ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/identity-gate', [IdentityGateController::class, 'showIdentityForm'])->name('identity.gate');
Route::post('/identity-gate/store', [IdentityGateController::class, 'storeIdentity'])->name('identity.store');

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

Route::middleware(['auth'])->group(function () {
    Route::get('/biodata', [UserBiodataController::class, 'create'])->name('biodata.create');
    Route::post('/biodata', [UserBiodataController::class, 'store'])->name('biodata.store');
});

use App\Http\Controllers\SuperAdmin\UserController;

Route::middleware(['auth'])->prefix('superadmin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('superadmin.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('superadmin.users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('superadmin.users.update');
    Route::match(['get', 'post'], '/users/{id}/impersonate', [UserController::class, 'impersonate'])->name('superadmin.users.impersonate');
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
