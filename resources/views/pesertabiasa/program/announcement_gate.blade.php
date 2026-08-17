@extends('pesertabiasa.layouts.app')
@section('title', 'Instruksi Wajib Terbuka')
@section('content')
<div class="py-12 max-w-2xl mx-auto px-4 animate-fade-in">

    <div class="bg-white rounded-3xl border border-rose-100 shadow-xl overflow-hidden">

        <div class="bg-gradient-to-r from-rose-700 to-amber-700 p-6 text-white text-left">
            <span class="text-[10px] font-mono tracking-widest uppercase font-black bg-rose-950/40 px-2.5 py-1 rounded-md">Urgent Mandate Gate</span>
            <h2 class="text-xl font-black tracking-tight mt-3">⚠️ Maklumat & Instruksi Wajib Anggota</h2>
            <p class="text-xs text-rose-100 mt-1">Demi kelancaran operasional tata tertib program, Anda wajib membuka, membaca, dan menyetujui poin instruksi panitia berikut ini.</p>
        </div>

        <div class="p-6 space-y-5">
            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                <h4 class="text-sm font-extrabold text-slate-800 flex items-center">
                    <span class="mr-2">📢</span> {{ $announcement->title }}
                </h4>
                <span class="text-[9px] font-mono text-slate-400 mt-0.5 block">Disiarkan pada: {{ $announcement->created_at->format('d M Y - H:i') }} WIB</span>

                <div class="text-xs text-slate-600 leading-relaxed mt-4 whitespace-pre-wrap bg-white p-4 rounded-xl border border-slate-100 shadow-3xs font-medium">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>

            <form action="{{ route('programs.internal.announcement.confirm', [$program->id, $announcement->id]) }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-rose-700 to-amber-700 hover:from-rose-800 hover:to-amber-800 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all">
                    ✍️ Saya Sudah Membaca, Memahami, & Siap Mematuhi Instruksi
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
