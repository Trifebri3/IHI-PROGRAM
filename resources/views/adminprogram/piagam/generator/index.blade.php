@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Generator Sertifikat</h1>
            <p class="text-slate-600 mt-1">Generate PDF sertifikat untuk peserta program <strong>{{ $program->name }}</strong>.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('adminprogram.piagam.grades.index', $program->id) }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 shadow-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Isi Data (Penilaian)
            </a>
            <form action="{{ route('adminprogram.piagam.generator.generate', $program->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses semua sertifikat yang belum di-generate?')" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 shadow-sm flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Generate Massal
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-700 rounded-xl border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-sm font-semibold text-slate-600">
                        <th class="p-4 border-r border-slate-200 w-12 text-center">No</th>
                        <th class="p-4 border-r border-slate-200">Peserta</th>
                        <th class="p-4 border-r border-slate-200">Status Sertifikat</th>
                        <th class="p-4 w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($participants as $index => $participant)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 border-r border-slate-100 text-center text-sm text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-4 border-r border-slate-100">
                                <div class="font-medium text-slate-800">{{ $participant->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $participant->user->email }}</div>
                            </td>
                            <td class="p-4 border-r border-slate-100">
                                @if($participant->piagamCertificate && $participant->piagamCertificate->file_path)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Sudah Dibuat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        Menunggu Generate
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($participant->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-700 text-[10px] font-bold uppercase rounded-lg border border-red-200">
                                        Tidak Lolos
                                    </span>
                                    <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline mt-2 block">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-slate-400 hover:text-indigo-600 underline" title="Batalkan Tidak Lolos dan Generate">Tetap Generate</button>
                                    </form>
                                @elseif($participant->piagamCertificate && $participant->piagamCertificate->file_path)
                                    <a href="{{ asset('storage/' . $participant->piagamCertificate->file_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Lihat PDF</a>
                                @else
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors" title="Generate Sertifikat">Generate</button>
                                        </form>
                                        <form action="{{ route('adminprogram.piagam.generator.failOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('Tandai peserta ini sebagai Tidak Lolos?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-lg transition-colors" title="Peserta Tidak Lolos">Tidak Lolos</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500">
                                Belum ada peserta terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection



