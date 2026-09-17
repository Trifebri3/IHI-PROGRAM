<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\PiagamTemplateController;
use App\Http\Controllers\SuperAdmin\PiagamGradingFormatController;
use App\Http\Controllers\AdminProgram\PiagamParticipantGradeController;
use App\Http\Controllers\AdminProgram\PiagamGeneratorController;
use App\Http\Controllers\Peserta\PiagamController;
use App\Http\Controllers\Public\PiagamVerificationController;

// Super Admin Routes
Route::prefix('superadmin/piagam')->name('superadmin.piagam.')->middleware(['auth', 'verified'])->group(function () {
    Route::post('templates/assign', [PiagamTemplateController::class, 'assignToProgram'])->name('templates.assign');
    Route::post('templates/{template}/layout/save', [PiagamTemplateController::class, 'saveLayoutElements'])->name('templates.layout.save');
    Route::post('templates/ornament/upload', [PiagamTemplateController::class, 'uploadOrnament'])->name('templates.ornament.upload');
    Route::resource('templates', PiagamTemplateController::class);
    Route::resource('grading-formats', PiagamGradingFormatController::class);
});

// Admin Program Routes
Route::prefix('adminprogram/programs/{program}/piagam')->name('adminprogram.piagam.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('grades/download-template', [PiagamParticipantGradeController::class, 'downloadTemplate'])->name('grades.download_template');
    Route::post('grades/upload-template', [PiagamParticipantGradeController::class, 'uploadTemplate'])->name('grades.upload_template');
    Route::resource('grades', PiagamParticipantGradeController::class);
    Route::get('generator', [PiagamGeneratorController::class, 'index'])->name('generator.index');
    Route::post('generator/generate', [PiagamGeneratorController::class, 'generate'])->name('generator.generate');
      Route::post('generator/generate/{participant}', [PiagamGeneratorController::class, 'generateOne'])->name('generator.generateOne');
      Route::post('generator/fail/{participant}', [PiagamGeneratorController::class, 'failOne'])->name('generator.failOne');
      Route::post('generator/send-email/{participant}', [PiagamGeneratorController::class, 'sendEmailOne'])->name('generator.sendEmailOne');
    Route::get('published', [PiagamGeneratorController::class, 'published'])->name('generator.published');
});

// Peserta Routes
Route::prefix('peserta/piagam')->name('peserta.piagam.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PiagamController::class, 'index'])->name('index');
    Route::get('/{certificate}/download', [PiagamController::class, 'download'])->name('download');
});

// Public Verification Route
Route::get('/verify/certificate/{qr_token}', [PiagamVerificationController::class, 'verify'])->name('public.piagam.verify');





