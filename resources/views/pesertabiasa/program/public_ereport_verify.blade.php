@extends('layouts.guest') {{-- Menggunakan layout umum/guest tanpa proteksi auth login --}}
@section('content')
<div class="py-12 max-w-xl mx-auto px-4 font-sans">
    <div class="bg-white rounded-3xl border border-emerald-100 shadow-xl overflow-hidden p-6 space-y-6">

        <div class="text-center space-y-2 border-b pb-4 bg-emerald-50/20 p-4 rounded-2xl border-dashed border-emerald-200">
            <span class="text-[35px]">🛡️</span>
            <h2 class="text-lg font-black text-slate-800 tracking-tight">Kredensial Dokumen Terverifikasi Sah!</h2>
            <p class="text-xs text-emerald-800 font-semibold font-mono">Token: {{ $registration->secure_verification_token }}</p>
        </div>

        <div class="space-y-3.5 text-xs">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Manifes Profil Pemilik Kredensial:</span>
            <div class="p-3 bg-slate-50 rounded-xl border flex justify-between">
                <span class="text-slate-500 font-medium">Nama Lengkap Anggota:</span>
                <strong class="text-slate-800 uppercase">{{ $registration->user->name }}</strong>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border flex justify-between">
                <span class="text-slate-500 font-medium">Judul Program Diklat:</span>
                <strong class="text-emerald-900 font-extrabold">{{ $registration->program->name }}</strong>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border flex justify-between">
                <span class="text-slate-500 font-medium">Nomor Registrasi ID:</span>
                <strong class="text-slate-800 font-mono font-black tracking-wider">{{ $registration->final_id_number }}</strong>
            </div>
        </div>

        <div class="space-y-2">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Transkrip Nilai Capaian Hasil Evaluasi ({{ $registration->program->total_hours }} JP):</span>
            @foreach($registration->final_scores ?? [] as $score)
                <div class="p-2.5 bg-white border border-slate-100 rounded-xl shadow-3xs flex justify-between items-center">
                    <span class="font-bold text-slate-700 text-xs">🔹 {{ $score['title'] }}</span>
                    <span class="font-mono font-black text-sm text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ $score['score'] }}</span>
                </div>
            @endforeach
        </div>

        <p class="text-[10px] text-center text-slate-400 font-medium leading-relaxed border-t pt-4">Layar ini memvalidasi keaslian data fisik piagam cetak yang dikeluarkan resmi oleh sistem otoritas database utama platform, dijamin 100% kredibel & anti-pemalsuan.</p>
    </div>
</div>
@endsection
