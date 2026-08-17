@extends('adminprogram.layouts.app')

@section('title', 'Dashboard Otoritas')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Welcome Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
            Dashboard Penyelenggara Program
        </h1>
        <p class="text-sm font-medium text-slate-500">
            Selamat datang kembali. Kelola formulir, pantau tahapan, terbitkan sertifikat, dan aktivasi alumni di bawah kontrol Anda.
        </p>
    </div>

    <!-- KPI Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Peserta -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:border-emerald-250 transition">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Peserta</span>
            <div class="flex items-baseline space-x-1.5 mt-3">
                <span class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($totalPeserta) }}</span>
                <span class="text-xs font-bold text-slate-400">Terdaftar</span>
            </div>
        </div>

        <!-- Card 2: Peserta Selesai -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:border-emerald-250 transition">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Peserta Selesai</span>
            <div class="flex items-baseline space-x-1.5 mt-3">
                <span class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($pesertaSelesai) }}</span>
                <span class="text-xs font-bold text-emerald-600">Selesai</span>
            </div>
        </div>

        <!-- Card 3: Peserta Lulus -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:border-emerald-250 transition">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Lulus Seleksi</span>
            <div class="flex items-baseline space-x-1.5 mt-3">
                <span class="text-3xl font-black text-emerald-600 tracking-tight">{{ number_format($lulusCount) }}</span>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Passed</span>
            </div>
        </div>

        <!-- Card 4: Alumni Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:border-emerald-250 transition">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Alumni Aktif</span>
            <div class="flex items-baseline space-x-1.5 mt-3">
                <span class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($alumniAktif) }}</span>
                <span class="text-xs font-bold text-slate-400">Teraktivasi</span>
            </div>
        </div>
    </div>

    <!-- Action Center (Pusat Kendali Operasional) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div class="border-b pb-3">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 flex items-center">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500 mr-2 animate-pulse"></span>
                Action Center (Butuh Tindakan Admin)
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Alert 1: Pengajuan Verifikasi Alumni Mandiri -->
            <div class="p-4 rounded-xl border border-rose-100 bg-rose-50/20 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-rose-100 text-rose-800 rounded uppercase">Prioritas Tinggi</span>
                    <h3 class="text-sm font-extrabold text-slate-800">{{ $pengajuanVerifikasi }} Pengajuan Verifikasi Mandiri</h3>
                    <p class="text-xs text-slate-500 leading-normal">Ada alumni lama atau peserta mandiri yang mengajukan klaim status verifikasi alumni.</p>
                </div>
                <a href="{{ route('adminprogram.alumni.verifications') }}" class="shrink-0 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-xl border border-rose-200 transition shadow-3xs uppercase">
                    Periksa &rarr;
                </a>
            </div>

            <!-- Alert 2: Peserta Eligible Alumni Belum Aktif -->
            <div class="p-4 rounded-xl border border-amber-100 bg-amber-50/20 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded uppercase">Butuh Konfirmasi</span>
                    <h3 class="text-sm font-extrabold text-slate-800">{{ $menungguAktivasi }} Eligible Alumni Belum Aktif</h3>
                    <p class="text-xs text-slate-500 leading-normal">Peserta telah Lulus & NIP lengkap, namun akun portal alumni belum diaktifkan.</p>
                </div>
                <a href="{{ route('adminprogram.participants.index', ['status' => 'passed', 'blocked_status' => 'active']) }}" class="shrink-0 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-2 rounded-xl border border-amber-200 transition shadow-3xs uppercase">
                    Aktivasi &rarr;
                </a>
            </div>

            <!-- Alert 3: Sertifikat Belum Terbit -->
            <div class="p-4 rounded-xl border border-amber-100 bg-amber-50/20 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded uppercase">Penerbitan Sertifikat</span>
                    <h3 class="text-sm font-extrabold text-slate-800">{{ $sertifikatBelumTerbit }} Sertifikat Belum Terbit</h3>
                    <p class="text-xs text-slate-500 leading-normal">Peserta lulus belum memiliki piagam kelulusan atau sertifikat digital resmi.</p>
                </div>
                <a href="{{ route('adminprogram.participants.index', ['status' => 'passed']) }}" class="shrink-0 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-2 rounded-xl border border-amber-200 transition shadow-3xs uppercase">
                    Terbitkan &rarr;
                </a>
            </div>

            <!-- Alert 4: Biodata Belum Lengkap -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-slate-200 text-slate-700 rounded uppercase">Data Administratif</span>
                    <h3 class="text-sm font-extrabold text-slate-800">{{ $dataBelumLengkap }} Peserta Biodata Belum Lengkap</h3>
                    <p class="text-xs text-slate-500 leading-normal">Peserta belum mengunggah dokumen NIK/KTP atau melengkapi data alamat demografi.</p>
                </div>
                <a href="{{ route('adminprogram.participants.index') }}" class="shrink-0 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-250 px-3 py-2 rounded-xl border border-slate-200 transition shadow-3xs uppercase">
                    Lihat Data &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Operational Main Menus -->
    <div>
        <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-4 px-1">
            Menu Operasional Utama
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Card Menu 1: Program Kerja -->
            <a href="{{ route('adminprogram.programs.index') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-950 transition-colors">
                        Program Kerja Saya
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Pantau daftar instansi/program yang didelegasikan ke Anda.
                    </p>
                </div>
            </a>

            <!-- Card Menu 2: Database Peserta -->
            <a href="{{ route('adminprogram.participants.index') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-950 transition-colors">
                        Database Peserta
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Penyaringan demografi wilayah, edit data NIP, dan tindakan massal.
                    </p>
                </div>
            </a>


            <!-- Card Menu 4: Manajemen Alumni -->
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
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-950 transition-colors">
                        Manajemen Alumni
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Konfigurasi template sertifikat PDF, dan persetujuan klaim verifikasi.
                    </p>
                </div>
            </a>

            <!-- Card Menu 5: Konfigurasi Profil -->
            <a href="{{ route('profile.edit') }}"
               class="group relative flex items-center p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-emerald-500 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4 pr-6">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-950 transition-colors">
                        Profil &amp; Kata Sandi
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Perbarui kata sandi, email instansi, dan data penanggung jawab.
                    </p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
