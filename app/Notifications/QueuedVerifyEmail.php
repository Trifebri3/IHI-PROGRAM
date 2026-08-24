<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

// Extend ke BaseVerifyEmail bawaan Laravel secara sinkron tanpa ShouldQueue
// agar email verifikasi dikirim langsung saat registrasi & kendala SMTP langsung terdeteksi
class QueuedVerifyEmail extends BaseVerifyEmail
{
    // Kita tidak perlu menulis ulang logika pengiriman email,
    // karena sudah ditangani oleh BaseVerifyEmail secara default.
}
