@extends('superadmin.layouts.app')
@section('title', 'Manajemen Event & Seminar')
@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="mb-4">
        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Event & Seminar Center</span>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Pusat Manajemen Event</h1>
        <p class="text-sm text-slate-500 mt-1">Adakan seminar, webinar, atau workshop umum. Tanpa sistem seleksi bertahap.</p>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-sm">
            ✨ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit space-y-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">Buka Event Baru</span>

            <form action="{{ route('superadmin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Nama / Judul Event</label>
                    <input type="text" name="title" placeholder="Cth: Seminar Nasional AI & IoT" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Lokasi / Link Pertemuan</label>
                    <input type="text" name="location" placeholder="Cth: Aula Utama / https://meet.google.com/abc" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Tanggal</label>
                        <input type="date" name="event_date" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Waktu Mulai</label>
                        <input type="time" name="event_time" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Kuota Peserta</label>
                    <input type="number" name="quota" value="100" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Banner Brosur (Opsional)</label>
                    <input type="file" name="banner" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-slate-50" accept="image/*">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Deskripsi / Detail Acara</label>
                    <textarea name="description" placeholder="Tuliskan detail agenda / narasumber acara disini..." class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" rows="3"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-xs rounded-xl hover:from-emerald-700 transition uppercase tracking-wider shadow-md">
                    🚀 Publikasikan Event
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">Daftar Aktif Event Kampus</span>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase border-b border-slate-100">
                            <th class="p-3">Nama Acara</th>
                            <th class="p-3">Waktu & Tempat</th>
                            <th class="p-3 text-center">Keterisian Kuota</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-medium text-slate-700">
                        @forelse($events as $ev)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3">
                                    <span class="font-bold text-slate-800 text-sm block">{{ $ev->title }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal line-clamp-1 mt-0.5">{{ $ev->description ?? 'Tidak ada deskripsi.' }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-700">📅 {{ date('d M Y', strtotime($ev->event_date)) }} - {{ $ev->event_time }}</div>
                                    <div class="text-[10px] text-emerald-700 font-bold mt-0.5 truncate max-w-[150px]">📍 {{ $ev->location }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 font-mono font-bold text-slate-700">
                                        {{ $ev->registrations_count }} / {{ $ev->quota }}
                                    </span>
                                </td>
{{-- GANTI COLLUMN ACTION DI TABEL LIST UTAMA EVENT SUPER ADMIN MENJADI INI: --}}
<td class="p-3 text-center flex items-center justify-center space-x-2">
    <a href="{{ route('superadmin.events.dashboard', $ev->id) }}" class="px-2.5 py-1 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold rounded-lg hover:from-emerald-700 transition shadow-3xs">
        ⚙️ Kelola & Rekap
    </a>

    <form action="{{ route('superadmin.events.delete', $ev->id) }}" method="POST" onsubmit="return confirm('Batalkan dan hapus event ini?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-rose-300 hover:text-rose-600 font-bold px-1.5 py-0.5 rounded transition-colors">✕</button>
    </form>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400 italic">Belum ada agenda event umum buatan Super Admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
