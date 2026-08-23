<?php

namespace App\Http\Controllers;

use App\Models\AccountVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserVerificationController extends Controller
{
    public function create()
    {

    
        $verification = Auth::user()->verification;
        return view('biodata.verification', compact('verification'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $existing = $user->verification;

        $rules = [
            'nik' => 'required|numeric|digits:16',
            'ktp' => ($existing && $existing->ktp_path) ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'photo' => ($existing && $existing->photo_path) ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ];

        $validated = $request->validate($rules, [
            'nik.required' => 'NIK wajib diisi.',
            'nik.numeric' => 'NIK harus berupa angka.',
            'nik.digits' => 'NIK harus berupa 16 digit angka.',
            'ktp.required' => 'File Salinan KTP wajib diunggah.',
            'ktp.image' => 'File Salinan KTP harus berupa gambar (jpg, png).',
            'ktp.max' => 'Ukuran file Salinan KTP maksimal 2MB.',
            'photo.required' => 'File Pas Foto wajib diunggah.',
            'photo.image' => 'File Pas Foto harus berupa gambar (jpg, png).',
            'photo.max' => 'Ukuran file Pas Foto maksimal 2MB.',
        ]);

        $data = [
            'nik' => $validated['nik'],
            'status' => 'pending',
            'rejection_reason' => null,
        ];

        // Proses Upload File
        if ($request->hasFile('ktp')) {
            if ($existing && $existing->ktp_path) Storage::disk('public')->delete($existing->ktp_path);
            $data['ktp_path'] = $request->file('ktp')->store('verifications/ktp', 'public');
        }

        if ($request->hasFile('photo')) {
            if ($existing && $existing->photo_path) Storage::disk('public')->delete($existing->photo_path);
            $data['photo_path'] = $request->file('photo')->store('verifications/photo', 'public');
        }

        AccountVerification::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return redirect()->route('verification.create')->with('success', 'Pengajuan verifikasi berhasil dikirim.');
    }
}
