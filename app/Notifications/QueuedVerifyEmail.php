<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

// 1. Extend ke BaseVerifyEmail bawaan Laravel
// 2. Implement ShouldQueue agar masuk ke background job
class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    // Kita tidak perlu menulis ulang logika pengiriman email,
    // karena sudah ditangani oleh BaseVerifyEmail.
    // Class ini hanya berfungsi untuk menambahkan sifat "ShouldQueue".
}
