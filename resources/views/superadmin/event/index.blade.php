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
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold shadow-sm animate-fade-in">
            ✨ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Buka Event Baru -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-100 h-fit space-y-4">
            <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 border-b pb-2">Buka Event Baru</span>

            <form id="event-form" action="{{ route('superadmin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama / Judul Event</label>
                    <input type="text" name="title" placeholder="Cth: Seminar Nasional AI & IoT" class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Lokasi / Link Pertemuan</label>
                    <input type="text" name="location" placeholder="Cth: Aula Utama / https://meet.google.com/abc" class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tanggal</label>
                        <input type="date" name="event_date" class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Waktu Mulai</label>
                        <input type="time" name="event_time" class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Kuota Peserta</label>
                        <input type="number" name="quota" value="100" min="1" class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tipe Registrasi</label>
                        <select name="registration_type" id="registration_type" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-white text-slate-750 font-bold focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none" onchange="toggleExternalLinkField(this.value)">
                            <option value="public">☀️ Terbuka Umum</option>
                            <option value="external">🔗 Link Eksternal</option>
                            <option value="logged_in">🔑 Wajib Login</option>
                        </select>
                    </div>
                </div>

                <div id="external_link_container" class="hidden">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Link Pendaftaran Eksternal (Mitra)</label>
                    <input type="url" name="external_link" id="external_link" placeholder="https://forms.gle/... atau https://wa.me/..." class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Banner Brosur (Opsional)</label>
                    <input type="file" name="banner" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer" accept="image/*">
                    <!-- Load Quill editor styling and JS -->
                    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
                    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Deskripsi / Detail Acara</label>
                    <div id="event-description-editor" class="h-32 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;"></div>
                    <input type="hidden" name="description" id="hidden-event-description">
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-xs rounded-xl hover:from-emerald-700 transition uppercase tracking-wider shadow-sm shadow-emerald-100">
                    🚀 Publikasikan Event
                </button>
            </form>
        </div>

        <!-- Tabel Daftar Event -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 border-b pb-2">Daftar Aktif Event Kampus</span>

            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 text-slate-500 font-bold uppercase border-b border-slate-100">
                            <th class="p-3">Nama Acara</th>
                            <th class="p-3">Waktu & Tempat</th>
                            <th class="p-3 text-center">Registrasi</th>
                            <th class="p-3 text-center">Kuota</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($events as $ev)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="p-3 space-y-1">
                                    <span class="font-extrabold text-slate-800 text-sm block tracking-tight">{{ $ev->title }}</span>
                                    @if($ev->banner_path)
                                        <a href="{{ asset('storage/'.$ev->banner_path) }}" target="_blank" class="inline-block text-[9px] font-black text-emerald-650 hover:underline">🖼️ Lihat Pamflet</a>
                                    @endif
                                </td>
                                <td class="p-3 space-y-1">
                                    <div class="font-bold text-slate-800">📅 {{ date('d M Y', strtotime($ev->event_date)) }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold">⏱️ {{ $ev->event_time }} WIB</div>
                                    <div class="text-[10px] text-emerald-700 font-bold max-w-[150px] truncate">📍 {{ $ev->location }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    @if($ev->registration_type === 'external')
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">🔗 Link Eksternal</span>
                                    @elseif($ev->registration_type === 'logged_in')
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-amber-50 text-amber-700 border border-amber-200">🔑 Wajib Login</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-250">☀️ Terbuka Umum</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono font-bold text-slate-700 border border-slate-200">
                                        {{ $ev->registrations_count }} / {{ $ev->quota }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('superadmin.events.dashboard', $ev->id) }}" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-150 text-emerald-700 font-extrabold text-[9px] uppercase tracking-wide rounded-lg transition shrink-0">
                                            ⚙️ Kelola & Rekap
                                        </a>
                                        <form action="{{ route('superadmin.events.delete', $ev->id) }}" method="POST" onsubmit="return confirm('Batalkan dan hapus event ini?')" class="inline">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">✕</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-400 italic">Belum ada agenda event umum buatan Super Admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleExternalLinkField(value) {
        const container = document.getElementById('external_link_container');
        const input = document.getElementById('external_link');
        if (value === 'external') {
            container.classList.remove('hidden');
            input.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            input.removeAttribute('required');
            input.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#event-description-editor', {
            theme: 'snow',
            placeholder: 'Tuliskan detail agenda / narasumber acara disini...'
        });

        const form = document.getElementById('event-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                document.getElementById('hidden-event-description').value = quill.root.innerHTML;
            });
        }
    });
</script>
@endsection
