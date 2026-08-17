@extends('adminprogram.layouts.app')

@section('title', 'Workspace Monitor Global')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">Workspace Monitor</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 font-medium">Pemantauan global, rekapan kelulusan, statistik, dan log aktivitas real-time seluruh program kerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sistem Aktif</span>
        </div>
    </div>

    <!-- TOP GLOBAL SUMMARY CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pendaftar Global -->
        <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-3xs flex flex-col justify-between hover:border-emerald-200 transition duration-200">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pendaftar Terkumpul</span>
                <span class="text-lg">👥</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($globalStats['totalApplicants']) }}</span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1">
                    <span class="text-emerald-600">{{ $globalStats['checked'] }} Diperiksa</span> • 
                    <span class="text-amber-600">{{ $globalStats['unchecked'] }} Antre</span>
                </span>
            </div>
        </div>

        <!-- Card 2: Kelulusan Seleksi Global -->
        <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-3xs flex flex-col justify-between hover:border-emerald-200 transition duration-200">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Lolos Final</span>
                <span class="text-lg">🏆</span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline space-x-1">
                    <span class="text-2xl font-black text-emerald-600 tracking-tight">{{ number_format($globalStats['passed']) }}</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase">Lolos</span>
                </div>
                <span class="text-[10px] font-bold text-slate-400 block mt-1">
                    <span class="text-rose-600">{{ $globalStats['failed'] }} Gagal</span> • 
                    <span class="text-amber-600">{{ $globalStats['process'] }} Proses</span>
                </span>
            </div>
        </div>

        <!-- Card 3: Alumni Terdaftar -->
        <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-3xs flex flex-col justify-between hover:border-emerald-200 transition duration-200">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Alumni Aktif</span>
                <span class="text-lg">🎓</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($globalStats['alumni']) }}</span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1">
                    <span class="text-emerald-700 font-extrabold">Teraktivasi di Portal</span>
                </span>
            </div>
        </div>

        <!-- Card 4: Piagam Terbit -->
        <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-3xs flex flex-col justify-between hover:border-emerald-200 transition duration-200">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Piagam &amp; Sertifikat</span>
                <span class="text-lg">📜</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($globalStats['certificates']) }}</span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1">
                    <span>Piagam Kelulusan Resmi</span>
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- KIRI & TENGAH: TABEL REKAPAN PROGRAM (2/3 Width) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 flex items-center">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                    Daftar Program Kerja &amp; Statistik
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $programs->count() }} Program Terdaftar</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-3xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="p-4">Nama Program</th>
                            <th class="p-4 text-center">Pendaftar (Cek/Antre)</th>
                            <th class="p-4 text-center">Seleksi (L/G/P)</th>
                            <th class="p-4 text-center">Alumni</th>
                            <th class="p-4 text-center">Sertifikat</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700 font-semibold">
                        @forelse($programs as $p)
                            @php
                                $stats = $programStats[$p->id];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4">
                                    <span class="font-bold text-slate-800 block text-xs truncate max-w-[200px]">{{ $p->name }}</span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">Mulai: {{ date('d M Y', strtotime($p->start_date)) }}</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-slate-800 text-xs block">{{ $stats['total'] }}</span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">
                                        <span class="text-emerald-600">{{ $stats['checked'] }}</span>/<span class="text-amber-600">{{ $stats['unchecked'] }}</span>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-xs block text-slate-850">
                                        <span class="text-emerald-600">{{ $stats['passed'] }}</span>/<span class="text-rose-600">{{ $stats['failed'] }}</span>
                                    </span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">
                                        Proses: <span class="text-amber-600 font-bold">{{ $stats['process'] }}</span>
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold text-xs text-slate-800">
                                    {{ $stats['alumni'] }}
                                </td>
                                <td class="p-4 text-center font-bold text-xs text-slate-800">
                                    {{ $stats['certificates'] }}
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('adminprogram.programs.workspace', $p->id) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-lg transition border border-emerald-100">
                                        Workspace &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400 italic text-xs rounded-b-2xl">
                                    Belum ada program kerja terdaftar atau didelegasikan kepada Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KANAN: REAL-TIME ACTIVITY LOGS TIMELINE FEED (1/3 Width) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 flex items-center">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    Log Aktivitas Global
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Live Timeline</span>
            </div>

            <div class="bg-slate-50/50 p-4 border border-slate-100 rounded-2xl max-h-[500px] overflow-y-auto space-y-4 custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="text-[11px] leading-relaxed border-b border-dashed border-slate-200/60 pb-3 last:border-b-0 last:pb-0">
                        <!-- Header log (Actor & Action tag) -->
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <div class="font-bold text-slate-800 leading-tight">
                                {{ $log->user->name ?? 'System' }}
                            </div>
                            <span class="px-2 py-0.5 text-[8px] font-black rounded uppercase border shrink-0
                                @if(str_contains($log->action, 'fail') || str_contains($log->action, 'reject'))
                                    bg-rose-50 border-rose-100 text-rose-700
                                @elseif(str_contains($log->action, 'pass') || str_contains($log->action, 'approve') || str_contains($log->action, 'verified') || str_contains($log->action, 'create'))
                                    bg-emerald-50 border-emerald-100 text-emerald-700
                                @else
                                    bg-blue-50 border-blue-100 text-blue-700
                                @endif">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </div>
                        
                        <!-- Detail content -->
                        <p class="text-slate-600 font-semibold mb-1">{{ $log->details }}</p>
                        
                        <!-- Footer log (IP and Time) -->
                        <div class="flex justify-between items-center text-[9px] text-slate-400 font-medium">
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                            <span>IP: {{ $log->ip_address ?? '—' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic text-center py-10">Belum ada rekapan aktivitas untuk pendaftar/admin saat ini.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

<style>
    /* Styling scrollbar kustom agar elegan */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endsection
