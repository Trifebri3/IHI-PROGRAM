<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AccountVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVerificationController extends Controller
{
    public function index()
    {
        // Mengambil daftar pending dengan user terkait
        $verifications = AccountVerification::with('user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('superadmin.verifications.index', compact('verifications'));
    }

    public function approve($id)
    {
        $verify = AccountVerification::findOrFail($id);

        $verify->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Akun peserta berhasil diverifikasi!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);

        $verify = AccountVerification::findOrFail($id);

        $verify->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan akun telah ditolak.');
    }
}
