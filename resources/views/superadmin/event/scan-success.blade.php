@extends('superadmin.layouts.app')
@section('title', 'Check-In Sukses')
@section('content')
<div class="py-12 max-w-md mx-auto px-4 sm:px-6 flex flex-col items-center">
    
    <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] rounded-tr-[5rem] rounded-bl-[5rem] shadow-2xl border border-slate-150 text-center w-full space-y-6">
        
        <!-- Status Icon -->
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto text-3xl shadow-sm border border-emerald-200">
            ✅
        </div>

        <div class="space-y-1">
            <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider font-mono">Kehadiran Terverifikasi</span>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight mt-3">Check-In Berhasil!</h1>
            <p class="text-xs text-slate-450 leading-relaxed font-semibold">Status absensi peserta telah diperbarui menjadi **HADIR** di database.</p>
        </div>

        <!-- Participant details -->
        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl text-left text-xs space-y-2.5">
            <div>
                <span class="block text-[8px] font-black text-slate-400 uppercase">Nama Lengkap</span>
                <strong class="text-slate-800 text-sm font-extrabold">{{ $name }}</strong>
            </div>
            <div>
                <span class="block text-[8px] font-black text-slate-400 uppercase">Email / Kontak</span>
                <span class="text-slate-600 font-semibold">{{ $registration->user ? $registration->user->email : $registration->guest_email }}</span>
                @if($registration->guest_phone)
                    <div class="text-slate-500 mt-0.5">📞 {{ $registration->guest_phone }}</div>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                <div>
                    <span class="block text-[8px] font-black text-slate-400 uppercase">Nomor Tiket</span>
                    <span class="font-mono text-emerald-700 font-bold">{{ $registration->ticket_number }}</span>
                </div>
                <div>
                    <span class="block text-[8px] font-black text-slate-400 uppercase">Waktu Absen</span>
                    <span class="text-slate-600 font-semibold">{{ $registration->attended_at->format('H:i') }} WIB</span>
                </div>
            </div>
        </div>

        <!-- Event info -->
        <div class="text-xs text-left bg-emerald-50/50 p-4 rounded-xl border border-emerald-100/50 space-y-1">
            <span class="block text-[8px] font-black text-emerald-700 uppercase">Agenda Kegiatan</span>
            <span class="font-bold text-emerald-900 block leading-snug">{{ $event->title }}</span>
            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">📅 {{ date('d M Y', strtotime($event->event_date)) }}</span>
        </div>

        <div class="space-y-2 pt-2">
            <a href="{{ route('superadmin.events.dashboard', $event->id) }}" class="block w-full py-3 bg-slate-900 hover:bg-black text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md">
                ⚙️ Kembali ke Dashboard Event
            </a>
            <a href="{{ route('superadmin.events.index') }}" class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold uppercase text-[9px] tracking-wider text-center transition">
                Daftar Semua Event
            </a>
        </div>

    </div>

</div>
@endsection
