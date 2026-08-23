<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Gate untuk Super Admin (Agar otomatis punya akses penuh)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // 2. Custom Tampilan Email Verifikasi
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifikasi Akun - Institut Hijau Indonesia')
                ->view('emails.verifikasi-custom', [
                    'url' => $url,
                    'user' => $notifiable
                ]);
        });
    }
}