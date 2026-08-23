<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MitigationTicket;
use Illuminate\Http\Request;

class EmailMitigationController extends Controller
{
    public function submitTicket(Request $request)
    {
        $request->validate([
            'issue_type' => 'required|string',
            'description' => 'required|string|max:1000',
        ]);

        $userId = auth()->id();

        // Check if there is already a pending ticket of the same issue type
        $existing = MitigationTicket::where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda telah mengirimkan pengajuan bantuan sebelumnya. Harap menunggu verifikasi atau respon dari Admin.');
        }

        MitigationTicket::create([
            'user_id' => $userId,
            'issue_type' => $request->issue_type,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Aduan/Laporan bantuan berhasil dikirim ke Admin. Tim kami akan segera melakukan pengecekan.');
    }
}
