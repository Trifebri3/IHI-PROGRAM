<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IdentityGateController extends Controller
{
    public function showIdentityForm() {
        return view('identity.gate'); // Kita pakai folder 'identity' biar tidak tabrakan
    }

    public function storeIdentity(Request $request) {
        $request->validate([
            'negara' => 'required', 'provinsi' => 'required', 'kabupaten' => 'required',
            'kecamatan' => 'required', 'desa' => 'required', 'kampung' => 'required',
            'photo' => 'required|image|max:2048'
        ]);

        $path = $request->file('photo')->store('profiles', 'public');

        auth()->user()->profile()->updateOrCreate(['user_id' => auth()->id()], ['profile_photo_path' => $path]);
        auth()->user()->address()->updateOrCreate(['user_id' => auth()->id()], $request->except(['photo', '_token']));

        return redirect()->route('dashboard')->with('success', 'Identitas berhasil diverifikasi.');
    }
}
