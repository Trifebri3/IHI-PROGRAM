@extends('pesertabiasa.layouts.app')

@section('title', 'Katalog Agenda Event Kampus')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <!-- Header & Filter Search/Sorting Area -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4 border-b pb-5">
        <div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Public Events Gate</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Katalog Seminar & Workshop Umum</h1>
            <p class="text-sm text-slate-500 mt-1">Ikuti berbagai kegiatan talkshow eksklusif penunjang kompetensi secara instan.</p>
        </div>

        <form action="{{ route('events.catalog') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Event..." class="p-2 border border-slate-200 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-500 w-full sm:w-44 shadow-3xs">
            <select name="sort" onchange="this.form.submit()" class="p-2 border border-slate-200 rounded-xl text-xs text-slate-700 bg-white cursor-pointer focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Urutkan --</option>
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Event Terbaru</option>
                <option value="soonest" {{ request('sort') == 'soonest' ? 'selected' : '' }}>Waktu Terdekat</option>
            </select>
            @if(request()->has('search') || request()->has('sort'))
                <a href="{{ route('events.catalog') }}" class="p-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold text-center hover:bg-slate-200">Reset</a>
            @endif
        </form>
    </div>

    <!-- Alert Sistem -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Grid Katalog Event -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($events as $event)
            @php
                $hasJoined = in_array($event->id, $myJoinedEvents);
                $isFull = $event->registrations_count >= $event->quota;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 p-5 flex flex-col justify-between space-y-4 hover:border-emerald-500 hover:shadow-sm transition-all duration-300">
                <div class="space-y-2">
                    <div class="flex justify-between items-start gap-2">
                        <h4 class="text-base font-bold text-slate-800 tracking-tight leading-snug">{{ $event->title }}</h4>
                        @if($hasJoined)
                            <span class="px-2 py-0.5 text-[9px] font-black bg-emerald-600 text-white rounded uppercase tracking-wider">Terdaftar</span>
                        @elseif($isFull)
                            <span class="px-2 py-0.5 text-[9px] font-black bg-slate-200 text-slate-500 rounded uppercase tracking-wider">Penuh</span>
                        @else
                            <span class="px-2 py-0.5 text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 rounded uppercase tracking-wider">Buka</span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $event->description ?? 'Tidak ada deskripsi detail agenda acara.' }}</p>

                    <div class="grid grid-cols-2 gap-2 pt-1 font-mono text-[10px] font-bold text-slate-600">
                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block text-[8px] uppercase">Waktu Pelaksanaan</span>
                            Jadwal: {{ date('d M Y', strtotime($event->event_date)) }} - {{ $event->event_time }} WIB
                        </div>
                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-100 truncate">
                            <span class="text-slate-400 block text-[8px] uppercase">Kuota Terisi</span>
                            Kapasitas: {{ $event->registrations_count }} / {{ $event->quota }} Slot
                        </div>
                    </div>
                </div>

                <div>
                    @if($hasJoined)
                        <!-- Sudah Bergabung -->
                        <a href="{{ route('events.dashboard', $event->id) }}" class="block w-full py-2.5 text-center bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white font-extrabold rounded-xl shadow-sm text-xs uppercase tracking-wider">
                            Masuk Ruang Utama Event &rarr;
                        </a>
                    @else
                        <!-- Belum Bergabung -->
                        @if($isFull)
                            <button type="button" class="w-full py-2 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed uppercase border" disabled>Kuota Penuh</button>
                        @else
                            <a href="{{ route('events.register.form', $event->id) }}" class="block w-full py-2.5 text-center bg-slate-950 hover:bg-black text-white font-extrabold rounded-xl shadow-sm text-xs uppercase tracking-wider transition-colors">
                                Ambil Tiket Masuk Event
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400 italic col-span-2 text-center py-6">Saat ini belum ada agenda seminar umum yang sesuai kriteria.</p>
        @endforelse
    </div>

</div>
@endsection
