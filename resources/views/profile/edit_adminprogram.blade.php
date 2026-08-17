@extends('adminprogram.layouts.app')

@section('title', 'Edit Profil Admin Program')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h1 class="text-xl font-extrabold text-slate-800 uppercase tracking-wider">Pengaturan Profil</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola data informasi akun dan kata sandi keamanan Anda.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Info -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-800 border-b pb-2">Informasi Profil</h3>
            @include('profile.partials.update-profile-information-form')
        </div>

        <!-- Password -->
        <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-800 border-b pb-2">Perbarui Kata Sandi</h3>
            @include('profile.partials.update-password-form')
        </div>
    </div>
</div>
@endsection
