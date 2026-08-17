@extends('adminprogram.layouts.app')

@section('title', 'Dashboard Otoritas')

@section('content')
<div class="space-y-8">

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
            Dashboard Penyelenggara Program
        </h1>
        <p class="text-sm font-medium text-slate-500">
            Selamat datang kembali. Kelola formulir, pantau tahapan, dan validasi kelulusan peserta di bawah kontrol Anda.
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Program Aktif</span>
            <div class="flex items-baseline space-x-1.5 mt-2">
                <span class="text-2xl font-black text-slate-800 tracking-tight">12</span>
                <span class="text-xs font-bold text-emerald-600">Delegasi</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Verifikasi Masuk</span>
            <div class="flex items-baseline space-x-1.5 mt-2">
                <span class="text-2xl font-black text-amber-600 tracking-tight">48</span>
                <span class="text-xs font-bold text-amber-500 animate-pulse">Pending</span>
            </div>
        </div>
        <div class="hidden lg:flex bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Kelulusan</span>
            <div class="flex items-baseline space-x-1.5 mt-2">
                <span class="text-2xl font-black text-slate-800 tracking-tight">1,240</span>
                <span class="text-xs font-bold text-slate-400">Peserta</span>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-4 px-1">
            Menu Operasional Utama
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <a href="{{ route('adminprogram.programs.index') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-900 transition-colors">
                        Program Kerja Saya
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Pantau daftar instansi atau program yang didelegasikan ke Anda.
                    </p>
                </div>
                <svg class="absolute right-5 w-5 h-5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('admin.verifications.index') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50 text-amber-700 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-900 transition-colors">
                        Verifikasi Berkas Masuk
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Pusat validasi data KYC, berkas identitas, dan portofolio peserta.
                    </p>
                </div>
                <svg class="absolute right-5 w-5 h-5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('adminprogram.alumni.index') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-900 transition-colors">
                        Manajemen Alumni
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola data alumni, konfigurasi template sertifikat, dan verifikasi.
                    </p>
                </div>
                <svg class="absolute right-5 w-5 h-5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-900 transition-colors">
                        Konfigurasi Profil Internal
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Perbarui kata sandi, email instansi, dan data penanggung jawab program.
                    </p>
                </div>
                <svg class="absolute right-5 w-5 h-5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </div>
    </div>

</div>
@endsection
