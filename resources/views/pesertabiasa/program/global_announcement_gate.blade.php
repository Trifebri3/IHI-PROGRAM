@extends('pesertabiasa.layouts.app')
@section('title', 'Maklumat Pusat Super Admin')
@section('content')
<div class="py-12 max-w-2xl mx-auto px-4 animate-fade-in">
    <div class="bg-white rounded-3xl border border-emerald-100 shadow-2xl overflow-hidden">

        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 p-6 text-white text-left">
            <span class="text-[10px] font-mono tracking-widest uppercase font-black bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md border border-emerald-500/30">System-Wide Broadcast Gate</span>
            <h2 class="text-xl font-black tracking-tight mt-3">🌍 Maklumat Resmi Super Admin Pusat</h2>
            <p class="text-xs text-emerald-100/70 mt-1">Pemberitahuan darurat berskala global. Anda wajib membaca, memahami, dan menyetujui poin maklumat berikut sebelum diperkenankan kembali menggunakan aplikasi.</p>
        </div>

        <div class="p-6 space-y-5">
            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl shadow-inner">
                <h4 class="text-sm font-black text-slate-800">📢 {{ $announcement->title }}</h4>
                <span class="text-[9px] font-mono text-slate-400 mt-0.5 block">Disiarkan resmi pada: {{ $announcement->created_at->format('d M Y - H:i') }} WIB</span>

                <div class="text-xs text-slate-650 leading-relaxed mt-4 bg-white p-4 rounded-xl border border-slate-100 shadow-3xs font-medium">
                    {!! $announcement->content !!}
                </div>
            </div>

            <form action="{{ route('announcements.global.confirm', $announcement->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-800 to-green-700 hover:from-emerald-900 hover:to-green-800 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all">
                    🤝 Saya Mengerti & Siap Mematuhi Maklumat Pusat
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
