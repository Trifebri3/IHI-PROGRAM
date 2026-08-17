<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
        'program.biodata.completed' => \App\Http\Middleware\EnsureProgramBiodataFilled::class, // <-- PASANG INI
        'announcement.read' => \App\Http\Middleware\EnsureAnnouncementsRead::class, // <-- SUNTIKKAN INI BOSS
        'check.profile' => \App\Http\Middleware\CheckProfileCompletion::class,
        'terms.accepted' => \App\Http\Middleware\EnsureTermsAccepted::class,
        'lms.api.key' => \App\Http\Middleware\EnsureLmsApiKey::class, // <-- TAMBAHKAN INI

    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
