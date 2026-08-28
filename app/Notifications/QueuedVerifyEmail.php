<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

// Extend ke BaseVerifyEmail bawaan Laravel secara asinkron dengan ShouldQueue
// agar email verifikasi dikirim via queue di background & registrasi tidak lambat/error 500
class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    // Kita tidak perlu menulis ulang logika pengiriman email,
    // karena sudah ditangani oleh BaseVerifyEmail secara default.
}
