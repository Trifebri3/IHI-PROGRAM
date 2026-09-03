@extends('superadmin.layouts.app')

@section('title', 'Green Forum Moderation & Analytics Desk')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8" x-data="{
    reportDetailModal: false,
    selectedReport: null
}">

    <!-- Top Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide">Community Governance</span>
                @if($pendingReports > 0)
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-md uppercase tracking-wide animate-pulse">
                        🚨 {{ $pendingReports }} Laporan Menunggu
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-2">Green Forum Desk</h1>
            <p class="text-xs text-slate-500 mt-1">Pusat analitik interaksi, moderasi laporan komunitas, pemantauan topik trending, dan kendali penindakan akun.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('forum.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                <span>Buka Green Forum</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-200 shadow-3xs">
                &larr; Dasbor
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Topik</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalDiscussions) }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold mt-0.5 block">Diskusi dibuat</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Komentar</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalComments) }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold mt-0.5 block">Balasan aktif</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Reaksi</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ number_format($totalReactions) }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-0.5 block">Emotikon terkirim</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Share</span>
            <div class="text-xl font-black text-sky-600 mt-1">{{ number_format($totalShares) }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-0.5 block">Tautan dibagikan</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm {{ $pendingReports > 0 ? 'ring-2 ring-rose-300' : '' }}">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Laporan Pending</span>
            <div class="text-xl font-black {{ $pendingReports > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ number_format($pendingReports) }}</div>
            <span class="text-[10px] {{ $pendingReports > 0 ? 'text-rose-500 font-bold' : 'text-slate-400' }} mt-0.5 block">Perlu review</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Akun Dibatasi</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ number_format($restrictedUsersCount + $blockedUsersCount) }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-0.5 block">{{ $restrictedUsersCount }} forum, {{ $blockedUsersCount }} total</span>
        </div>
    </div>

    <!-- Section: Top Trending Discussions (Skor Engagement) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>🔥 Top 5 Topik Paling Hangat (Trending)</span>
                </h2>
                <p class="text-xs text-slate-400">Peringkat keterlibatan dihitung berdasarkan volume reaksi, komentar, posting ulang, dan share.</p>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($trendingDiscussions as $idx => $trend)
                <div class="p-3.5 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-black text-xs {{ $idx === 0 ? 'bg-amber-400 text-amber-950 shadow-sm' : ($idx === 1 ? 'bg-slate-300 text-slate-800' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-600')) }} flex-shrink-0">
                            #{{ $idx + 1 }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-900">{{ $trend->user->name }}</span>
                                @if($trend->user->isVerifiedAccount())
                                    <span class="text-sky-500">✓</span>
                                @endif
                                <span class="text-slate-400 text-[11px]">• {{ $trend->created_at->diffForHumans(null, true) }}</span>
                            </div>
                            <a href="{{ route('forum.public.show', $trend->id) }}" target="_blank" class="font-extrabold text-slate-950 text-sm hover:text-emerald-700 hover:underline block truncate mt-0.5">
                                {{ $trend->title }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <!-- Metrik Pills (Murni SVG Tanpa Emotikon) -->
                        <div class="flex items-center gap-2.5 text-[11px] font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-full border border-slate-200/80 shadow-2xs">
                            <span class="inline-flex items-center gap-1" title="Reaksi">
                                <svg class="w-3.5 h-3.5 text-rose-500 fill-rose-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                <span>{{ $trend->reactions_count }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1" title="Komentar">
                                <svg class="w-3.5 h-3.5 text-slate-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.636 1.064.508 1.67-.282 1.34-1.026 2.476-1.065 2.536a.75.75 0 00.74 1.164c1.785-.205 3.328-.857 4.382-1.472.375-.219.822-.26 1.23-.128.932.298 1.916.45 2.932.45z"/></svg>
                                <span>{{ $trend->comments_count }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1" title="Posting Ulang">
                                <svg class="w-3.5 h-3.5 text-emerald-600 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" /></svg>
                                <span>{{ $trend->reposts_count }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1" title="Dibagikan">
                                <svg class="w-3.5 h-3.5 text-sky-500 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                <span>{{ $trend->shares_count ?? 0 }}</span>
                            </span>
                        </div>

                        <!-- Tombol Takedown Diskusi -->
                        <form action="{{ route('superadmin.forum.discussion.takedown', $trend->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin men-takedown diskusi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl transition text-[11px] border border-rose-200">
                                Takedown
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Belum ada data topik yang cukup untuk analisis tren.</div>
            @endforelse
        </div>
    </div>

    <!-- Section: Laporan Komunitas (Community Reports) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" id="reports-section">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>🚩 Laporan Masuk dari Komunitas</span>
                </h2>
                <p class="text-xs text-slate-400">Tinjau laporan pelanggaran, lakukan takedown konten, batasi atau blokir akun pelaku pelanggaran.</p>
            </div>

            <!-- Filter Status Laporan -->
            <div class="flex items-center gap-1.5 text-xs bg-slate-100 p-1 rounded-xl">
                <a href="{{ route('superadmin.forum.index', ['report_status' => 'all']) }}#reports-section"
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $reportStatus === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('superadmin.forum.index', ['report_status' => 'pending']) }}#reports-section"
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $reportStatus === 'pending' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Pending ({{ $pendingReports }})
                </a>
                <a href="{{ route('superadmin.forum.index', ['report_status' => 'action_taken']) }}#reports-section"
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $reportStatus === 'action_taken' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Ditindak
                </a>
                <a href="{{ route('superadmin.forum.index', ['report_status' => 'dismissed']) }}#reports-section"
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $reportStatus === 'dismissed' ? 'bg-white text-slate-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Diabaikan
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">Pelapor</th>
                        <th class="py-2.5 px-3">Topik / Pelanggar</th>
                        <th class="py-2.5 px-3">Alasan & Catatan</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Tindakan Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $rep->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rep->created_at->diffForHumans() }}</div>
                            </td>

                            <td class="py-3 px-3 max-w-xs">
                                @if($rep->discussion)
                                    <a href="{{ route('forum.public.show', $rep->discussion->id) }}" target="_blank" class="font-extrabold text-slate-900 hover:text-emerald-700 block truncate">
                                        {{ $rep->discussion->title }}
                                    </a>
                                    <div class="text-[10px] text-slate-500">
                                        Penulis: <span class="font-bold">{{ $rep->discussion->user->name }}</span>
                                        @if($rep->discussion->user->is_forum_restricted)
                                            <span class="text-amber-600 font-bold ml-1">(Dibatasi)</span>
                                        @endif
                                        @if($rep->discussion->user->is_blocked)
                                            <span class="text-rose-600 font-bold ml-1">(Diblokir)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Diskusi telah dihapus</span>
                                @endif
                            </td>

                            <td class="py-3 px-3 max-w-xs">
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-[10px] border border-rose-200 block w-fit mb-1">
                                    {{ $rep->reason }}
                                </span>
                                @if($rep->notes)
                                    <p class="text-slate-600 text-[11px] line-clamp-2 italic">"{{ $rep->notes }}"</p>
                                @endif
                            </td>

                            <td class="py-3 px-3">
                                @if($rep->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Pending
                                    </span>
                                @elseif($rep->status === 'action_taken')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Ditindak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Diabaikan
                                    </span>
                                @endif

                                @if($rep->action_taken)
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $rep->action_taken }}</div>
                                @endif
                            </td>

                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @if($rep->discussion)
                                        <!-- Takedown Diskusi -->
                                        <form action="{{ route('superadmin.forum.discussion.takedown', $rep->discussion->id) }}" method="POST" onsubmit="return confirm('Takedown diskusi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition" title="Takedown Diskusi">
                                                Takedown
                                            </button>
                                        </form>

                                        <!-- Batasi Forum User -->
                                        <form action="{{ route('superadmin.forum.user.restrict', $rep->discussion->user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $rep->discussion->user->is_forum_restricted ? 'bg-amber-600 text-white' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200' }} transition" title="Batasi Akun Penulis">
                                                {{ $rep->discussion->user->is_forum_restricted ? 'Buka Batasan' : 'Batasi Akun' }}
                                            </button>
                                        </form>

                                        <!-- Blokir Total User -->
                                        <form action="{{ route('superadmin.forum.user.block', $rep->discussion->user->id) }}" method="POST" onsubmit="return confirm('Blokir akun pengguna ini secara total?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $rep->discussion->user->is_blocked ? 'bg-slate-900 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' }} transition" title="Blokir Akun Total">
                                                {{ $rep->discussion->user->is_blocked ? 'Buka Blokir' : 'Blokir Total' }}
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Abaikan Laporan jika status pending -->
                                    @if($rep->status === 'pending')
                                        <form action="{{ route('superadmin.forum.report.resolve', $rep->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="dismissed">
                                            <input type="hidden" name="action_taken" value="Laporan diabaikan / tidak melanggar ketentuan">
                                            <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 transition" title="Abaikan Laporan">
                                                Abaikan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada laporan pengguna yang perlu ditinjau.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <!-- Section: Kontrol Moderasi Semua Diskusi & Komentar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Semua Diskusi (Searchable & Takedown) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Semua Diskusi Green Forum</h2>
                    <p class="text-xs text-slate-400">Kelola dan lakukan moderasi langsung terhadap topik yang beredar.</p>
                </div>

                <form method="GET" action="{{ route('superadmin.forum.index') }}" class="relative">
                    <input type="text"
                           name="search_discussion"
                           value="{{ $searchDiscussion }}"
                           placeholder="Cari topik / penulis..."
                           class="w-56 text-xs bg-slate-50 border border-slate-200 rounded-full pl-3 pr-8 py-1.5 outline-none focus:border-emerald-500">
                    <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-2 px-2">Penulis</th>
                            <th class="py-2 px-2">Judul & Isi</th>
                            <th class="py-2 px-2">Engagement</th>
                            <th class="py-2 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($discussions as $disc)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-2">
                                    <div class="font-bold text-slate-900">{{ $disc->user->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $disc->created_at->diffForHumans(null, true) }}</div>
                                </td>
                                <td class="py-2.5 px-2 max-w-sm">
                                    <a href="{{ route('forum.public.show', $disc->id) }}" target="_blank" class="font-extrabold text-slate-950 hover:text-emerald-700 block truncate">
                                        {{ $disc->title }}
                                    </a>
                                    <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $disc->content }}</p>
                                </td>
                                <td class="py-2.5 px-2">
                                    <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-600">
                                        <span class="inline-flex items-center gap-0.5" title="Reaksi">
                                            <svg class="w-3 h-3 text-rose-500 fill-rose-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                            <span>{{ $disc->reactions_count }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-0.5" title="Komentar">
                                            <svg class="w-3 h-3 text-slate-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.636 1.064.508 1.67-.282 1.34-1.026 2.476-1.065 2.536a.75.75 0 00.74 1.164c1.785-.205 3.328-.857 4.382-1.472.375-.219.822-.26 1.23-.128.932.298 1.916.45 2.932.45z"/></svg>
                                            <span>{{ $disc->comments_count }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-0.5" title="Posting Ulang">
                                            <svg class="w-3 h-3 text-emerald-600 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" /></svg>
                                            <span>{{ $disc->reposts_count }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-0.5" title="Dibagikan">
                                            <svg class="w-3 h-3 text-sky-500 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                            <span>{{ $disc->shares_count ?? 0 }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 text-right">
                                    <form action="{{ route('superadmin.forum.discussion.takedown', $disc->id) }}" method="POST" onsubmit="return confirm('Takedown diskusi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition">
                                            Takedown
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada diskusi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($discussions->hasPages())
                <div class="pt-2 border-t border-slate-100">
                    {{ $discussions->links() }}
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Komentar Terkini & Akun Dibatasi -->
        <div class="space-y-6">

            <!-- Card Komentar Terkini -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-extrabold text-slate-900 mb-1">Komentar Terkini</h3>
                <p class="text-[11px] text-slate-400 mb-3">Takedown komentar tidak pantas langsung.</p>

                <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                    @forelse($recentComments as $comm)
                        <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition text-xs flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 text-[11px] flex items-center gap-1">
                                    <span>{{ $comm->user->name }}</span>
                                    <span class="text-slate-400 text-[10px]">• {{ $comm->created_at->diffForHumans(null, true) }}</span>
                                </div>
                                <p class="text-slate-700 text-xs mt-0.5 line-clamp-2">{{ $comm->content }}</p>
                                <div class="text-[10px] text-slate-400 mt-1 truncate">
                                    Di: <span class="italic">{{ $comm->discussion?->title }}</span>
                                </div>
                            </div>
                            <form action="{{ route('superadmin.forum.comment.takedown', $comm->id) }}" method="POST" onsubmit="return confirm('Takedown komentar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-md text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" title="Takedown Komentar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-4">Belum ada komentar.</div>
                    @endforelse
                </div>
            </div>

            <!-- Card Akun Terbatas / Diblokir -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-extrabold text-slate-900 mb-1">Akun Dibatasi / Diblokir</h3>
                <p class="text-[11px] text-slate-400 mb-3">Daftar pengguna dengan sanksi moderasi aktif.</p>

                <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                    @forelse($moderatedUsers as $mUser)
                        <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 text-xs truncate">{{ $mUser->name }}</div>
                                <div class="text-[10px] text-slate-400 truncate">{{ $mUser->email }}</div>
                                <div class="flex items-center gap-1 mt-1">
                                    @if($mUser->is_forum_restricted)
                                        <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[9px] font-bold border border-amber-200">Forum Read-Only</span>
                                    @endif
                                    @if($mUser->is_blocked)
                                        <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 text-[9px] font-bold border border-rose-200">Diblokir Total</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if($mUser->is_forum_restricted)
                                    <form action="{{ route('superadmin.forum.user.restrict', $mUser->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold rounded-lg text-[10px] transition" title="Pulihkan Akses Forum">
                                            Buka Batasan
                                        </button>
                                    </form>
                                @endif
                                @if($mUser->is_blocked)
                                    <form action="{{ route('superadmin.forum.user.block', $mUser->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-lg text-[10px] transition" title="Buka Blokir Akun">
                                            Buka Blokir
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-4">Tidak ada akun yang sedang dibatasi atau diblokir.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
