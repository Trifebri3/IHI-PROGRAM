<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Cek jika sudah terverifikasi
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            // 2. SOLUSI "HARDCORE": Gunakan sendNow() 
            // Ini akan memaksa Laravel mengirim email saat ini juga, 
            // mengabaikan settingan QUEUE_CONNECTION=database, 
            // sehingga tidak butuh worker/artisan.
            Notification::sendNow($user, new VerifyEmail());
            
        } catch (\Exception $e) {
            // Log error jika pengiriman gagal
            \Log::error("Gagal kirim verifikasi: " . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email verifikasi. Silakan hubungi admin.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}