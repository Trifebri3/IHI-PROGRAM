<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermsController extends Controller
{
    /**
     * Menampilkan halaman persetujuan
     */
    public function show()
    {
        return view('auth.terms-agreement');
    }

    /**
     * Memproses persetujuan user
     */
public function store(Request $request)
{
    $request->validate(['agree' => 'required|accepted']);

    $user = Auth::user();
    $user->terms_accepted_at = now();
    $user->save(); // Gunakan save() agar lebih pasti

    return redirect()->route('dashboard');
}
}
