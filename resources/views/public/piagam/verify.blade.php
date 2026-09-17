@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
        
        <div class="bg-emerald-600 px-6 py-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-emerald-700 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="relative z-10">
                <div class="mx-auto w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Sertifikat Valid</h1>
                <p class="text-emerald-100 text-sm mt-2 font-medium">Dokumen asli diterbitkan secara resmi oleh sistem.</p>
            </div>
        </div>

        <div class="p-8 space-y-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Diberikan Kepada</label>
                <div class="text-lg font-bold text-slate-800">{{ $certificate->participant->user->name ?? 'N/A' }}</div>
            </div>

            <hr class="border-slate-100">

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Program</label>
                <div class="text-sm font-semibold text-slate-700 leading-snug">{{ $certificate->program->name ?? 'N/A' }}</div>
            </div>

            <hr class="border-slate-100">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">No. Kredensial Utama </label>
                    <div class="text-sm font-semibold text-slate-700">{{ $certificate->certificate_number ?? 'N/A' }}</div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanggal Terbit</label>
                    <div class="text-sm font-semibold text-slate-700">{{ $certificate->published_at ? \Carbon\Carbon::parse($certificate->published_at)->format('d M Y') : 'N/A' }}</div>
                </div>
            </div>

            @if($certificate->file_path)
            <div class="pt-4">
                <a href="{{ asset('storage/' . $certificate->file_path) }}" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-sm font-bold rounded-xl text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors shadow-sm gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Lihat/Unduh PDF Asli
                </a>
            </div>
            @endif
        </div>
        
    </div>
</div>
@endsection
