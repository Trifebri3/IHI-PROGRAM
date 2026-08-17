@php
    // Deteksi Layout Otomatis Berdasarkan Role
    $layout = match(auth()->user()->getRoleNames()->first()) {
        'adminprogram' => 'adminprogram.layouts.app',
        'superadmin'   => 'superadmin.layouts.app',
        default        => 'pesertabiasa.layouts.app',
    };
@endphp

@extends($layout)

@section('content')
<div class="max-w-4xl mx-auto p-4 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-900">Identitas Pengguna</h1>
        <p class="text-sm text-slate-500">Kelola informasi profil, alamat, dan data pribadi Anda.</p>
    </div>

    <!-- Notifikasi -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold shadow-sm rounded-r-xl">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('identitas.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Bagian 1: Akun & Foto -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h2 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider">Informasi Akun</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 rounded-full overflow-hidden bg-slate-200 border-4 border-white shadow-md mb-3">
                        @if($profile && $profile->profile_photo_path)
                            <img src="{{ asset('storage/' . $profile->profile_photo_path) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center font-bold text-2xl text-slate-400 bg-slate-300 uppercase">{{ substr($user->name, 0, 2) }}</div>
                        @endif
                    </div>
                    <input type="file" name="profile_photo" id="profile_photo" class="hidden">
                    <label for="profile_photo" class="cursor-pointer text-[10px] font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg">Ganti Foto</label>
                </div>
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase">Nama</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="w-full p-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full p-2 border rounded-xl text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Alamat (Sinkron ke Tabel Alamat) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h2 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider">Data Alamat</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input type="text" name="negara" placeholder="Negara" value="{{ $address->negara ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <input type="text" name="provinsi" placeholder="Provinsi" value="{{ $address->provinsi ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <input type="text" name="kabupaten" placeholder="Kabupaten" value="{{ $address->kabupaten ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <input type="text" name="kecamatan" placeholder="Kecamatan" value="{{ $address->kecamatan ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <input type="text" name="desa" placeholder="Desa" value="{{ $address->desa ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <input type="text" name="kampung" placeholder="Kampung" value="{{ $address->kampung ?? '' }}" class="p-2 border rounded-xl text-sm" required>
                <textarea name="detail_alamat" placeholder="Detail Alamat" class="sm:col-span-2 p-2 border rounded-xl text-sm">{{ $address->detail_alamat ?? '' }}</textarea>
            </div>
        </div>

        <!-- Bagian 3: Biodata Dinamis -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h2 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider">Informasi Tambahan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($biodataFields as $field)
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase">{{ $field->name }}</label>
                        <input type="text" name="biodata[{{ $field->id }}]" value="{{ $field->user_value }}" class="w-full p-2 border rounded-xl text-sm">
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl text-xs hover:bg-emerald-700">Simpan Perubahan Identitas</button>
    </form>

    <!-- Bagian 4: Password (Form Terpisah) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mt-8">
        <h2 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider">Keamanan Akun</h2>
        <form action="{{ route('identitas.password') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="password" name="current_password" placeholder="Password Saat Ini" class="w-full p-2 border rounded-xl text-sm" required>
            <input type="password" name="password" placeholder="Password Baru" class="w-full p-2 border rounded-xl text-sm" required>
            <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" class="w-full p-2 border rounded-xl text-sm" required>
            <button type="submit" class="w-full py-2 bg-slate-800 text-white font-bold rounded-xl text-xs">Perbarui Password</button>
        </form>
    </div>
</div>
@endsection
