@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors shadow-sm" title="Kembali ke Generator">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Daftar Piagam Terbit</h1>
                    <p class="text-slate-600 mt-0.5 text-sm">Sertifikat piagam yang telah diterbitkan untuk peserta program <strong>{{ $program->name }}</strong>.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm flex items-center gap-2 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Generator Piagam
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="font-semibold text-sm">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="font-semibold text-sm">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Search Toolbar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                Total Terbit: {{ $certificates->total() }} Sertifikat
            </span>
        </div>

        <form method="GET" action="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="flex items-center gap-2">
            <div class="relative w-full sm:w-72">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nomor, nama atau email..." 
                       class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request('search'))
                    <a href="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="text-slate-400 hover:text-slate-600 absolute right-2.5 top-2 text-xs" title="Reset">✕</a>
                @endif
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                        <th class="p-3.5 w-12 text-center border-r border-slate-200">No</th>
                        <th class="p-3.5 border-r border-slate-200">Peserta</th>
                        <th class="p-3.5 border-r border-slate-200">Nomor Kredensial</th>
                        <th class="p-3.5 border-r border-slate-200 text-center">Status Email</th>
                        <th class="p-3.5 border-r border-slate-200 text-center">Tanggal Dibuat</th>
                        <th class="p-3.5 w-44 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($certificates as $index => $cert)
                        @php
                            $user = $cert->participant->user ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500 font-mono">
                                {{ $certificates->firstItem() + $index }}
                            </td>
                            <td class="p-3.5 border-r border-slate-100">
                                <div class="font-medium text-slate-800">{{ $user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3.5 border-r border-slate-100">
                                <span class="font-mono text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                    {{ $cert->certificate_number ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3.5 border-r border-slate-100 text-center">
                                @if($cert->email_sent_at)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800" title="Terkirim pada {{ $cert->email_sent_at }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Terkirim
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Belum Dikirim
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500">
                                {{ $cert->created_at ? $cert->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($cert->file_path)
                                        <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Lihat Dokumen PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            PDF
                                        </a>
                                    @endif

                                    @if($cert->participant)
                                        <form action="{{ route('adminprogram.piagam.generator.sendEmailOne', [$program->id, $cert->participant_id]) }}" method="POST" class="inline" onsubmit="return confirm('Kirim email ke {{ $user->email ?? 'peserta' }}?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 {{ $cert->email_sent_at ? 'bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} text-xs font-semibold rounded-lg transition-colors" title="{{ $cert->email_sent_at ? 'Kirim Ulang Email' : 'Kirim Email' }}">
                                                {{ $cert->email_sent_at ? 'Kirim Ulang' : 'Kirim Email' }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($cert->qr_token)
                                        <a href="{{ route('public.piagam.verify', $cert->qr_token) }}" target="_blank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-colors" title="Cek Halaman Verifikasi Publik">
                                            QR
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-500">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Belum ada piagam yang diterbitkan untuk program ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
