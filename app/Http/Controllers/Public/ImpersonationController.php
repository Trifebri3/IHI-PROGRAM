<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditLog;

class ImpersonationController extends Controller
{
    /**
     * Stop impersonating and return back as the original Super Admin
     */
    public function stop(Request $request)
    {
        if (!$request->session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $adminId = $request->session()->pull('impersonator_id');
        $admin = User::find($adminId);

        if (!$admin) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun admin tidak ditemukan.');
        }

        $targetUser = Auth::user();

        // Login back as Super Admin
        Auth::login($admin);

        // Log the action
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'impersonate_stop',
            'target_user_id' => $targetUser ? $targetUser->id : null,
            'details' => 'Menghentikan impersonasi dari pengguna: ' . ($targetUser ? $targetUser->name : 'N/A'),
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Kembali masuk sebagai administrator utama.');
    }
}
