@extends('superadmin.layouts.app')

@section('title', 'Event Command Dashboard')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Event Command Studio</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-1.5">{{ $event->title }}</h1>
            <p class="text-xs text-slate-400 mt-1">
                ⏱️ Waktu: {{ date('d M Y', strtotime($event->event_date)) }} — Jam {{ $event->event_time }} | 📍 Ruang: {{ $event->location }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.events.scanner', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl transition border shadow-sm gap-1.5">
                📷 Buka Kamera Scanner
            </a>
            <a href="{{ route('superadmin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold rounded-xl transition border shadow-3xs">
                &larr; Kembali ke Daftar Event
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold shadow-2xs flex items-center">
            <span>✨ {{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-5 h-fit">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                    Rakit Formulir Kustom Pendaftaran (Ala Google Form)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pasang kolom isian wajib suplemen tambahan yang wajib dijawab peserta saat mengklaim tiket masuk acara.</p>
            </div>

            <form action="{{ route('superadmin.events.form.store', $event->id) }}" method="POST" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Nama Atribut / Kolom Isian</label>
                    <input type="text" name="field_name" placeholder="Cth: NIK / ID Instagram / Alamat Instansi" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-500" required>
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Tipe Tampilan</label>
                    <select name="field_type" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 font-medium">
                        <option value="text">Ketikkan Deskripsi Teks</option>
                        <option value="number">Input Nilai Angka</option>
                        <option value="file">Upload Berkas File (PDF/Image)</option>
                    </select>
                </div>
                <div class="sm:col-span-3 flex justify-between items-center border-t pt-2 mt-1 border-slate-200/60">
                    <label class="flex items-center text-xs text-slate-500 font-bold cursor-pointer select-none">
                        <input type="checkbox" name="is_required" value="1" class="rounded text-emerald-600 focus:ring-emerald-500 mr-1.5 w-3.5 h-3.5 shadow-3xs" checked> Wajib Diisi Peserta
                    </label>
                    <button type="submit" class="bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold text-xs px-4 py-2 rounded-xl hover:from-emerald-700 shadow-sm transition-all uppercase tracking-wider">
                        + Pasang Bidang
                    </button>
                </div>
            </form>

            <div class="space-y-2">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Atribut Formulir Kustom Aktif saat ini:</span>

                @forelse($event->form_schema ?? [] as $index => $sch)
                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex justify-between items-center shadow-3xs hover:bg-slate-50/50 transition">
                        <div class="text-xs font-bold text-slate-700 flex items-center">
                            <span class="text-emerald-600 mr-2 text-sm">📌</span>
                            <span>{{ $sch['name'] }}</span>
                            <span class="ml-2 text-[8px] font-black bg-slate-100 border px-1.5 py-0.5 rounded text-slate-400 uppercase tracking-wider">{{ $sch['type'] }}</span>
                            @if(isset($sch['required']) && $sch['required'])
                                <span class="text-[8px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-1.5 py-0.5 rounded ml-1">* Required</span>
                            @endif
                        </div>

                        <form action="{{ route('superadmin.events.form.delete', [$event->id, $index]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-rose-600 font-bold text-xs p-1 transition-colors">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        Formulir saat ini bersifat Instan Sekali Klik (Belum ada komponen kuesioner kustom).
                    </p>
                @endforelse
            </div>
        </div>

        <!-- Rakit Formulir Absensi & Evaluasi Kustom -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-5 h-fit mt-6">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2"></span>
                    Rakit Formulir Absensi & Evaluasi Acara (Kustom)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pasang kolom isian kuesioner, evaluasi, atau feedback yang wajib dijawab peserta saat melakukan absensi seminar/event.</p>
            </div>

            <form action="{{ route('superadmin.events.attendance_form.store', $event->id) }}" method="POST" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Nama Atribut / Pertanyaan Evaluasi</label>
                    <input type="text" name="field_name" placeholder="Cth: Feedback Materi / Rating Pemateri / Kategori" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white focus:ring-1 focus:ring-amber-500 outline-none" required>
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Tipe Tampilan</label>
                    <select name="field_type" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 font-medium outline-none">
                        <option value="text">Ketikkan Deskripsi Teks</option>
                        <option value="number">Input Nilai Angka / Skala</option>
                        <option value="file">Upload Berkas File (PDF/Image)</option>
                    </select>
                </div>
                <div class="sm:col-span-3 flex justify-between items-center border-t pt-2 mt-1 border-slate-200/60">
                    <label class="flex items-center text-xs text-slate-500 font-bold cursor-pointer select-none">
                        <input type="checkbox" name="is_required" value="1" class="rounded text-amber-600 focus:ring-amber-500 mr-1.5 w-3.5 h-3.5 shadow-3xs" checked> Wajib Diisi Peserta
                    </label>
                    <button type="submit" class="bg-gradient-to-r from-amber-600 to-amber-700 text-white font-extrabold text-xs px-4 py-2 rounded-xl hover:from-amber-750 shadow-sm transition-all uppercase tracking-wider">
                        + Pasang Bidang Absen
                    </button>
                </div>
            </form>

            <div class="space-y-2">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Atribut Formulir Absensi Aktif saat ini:</span>

                @forelse($event->attendance_form_schema ?? [] as $index => $sch)
                    <div class="p-3 bg-white rounded-xl border border-slate-100 flex justify-between items-center shadow-3xs hover:bg-slate-50/50 transition">
                        <div class="text-xs font-bold text-slate-700 flex items-center">
                            <span class="text-amber-650 mr-2 text-sm">📌</span>
                            <span>{{ $sch['name'] }}</span>
                            <span class="ml-2 text-[8px] font-black bg-slate-100 border px-1.5 py-0.5 rounded text-slate-400 uppercase tracking-wider">{{ $sch['type'] }}</span>
                            @if(isset($sch['required']) && $sch['required'])
                                <span class="text-[8px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-1.5 py-0.5 rounded ml-1">* Required</span>
                            @endif
                        </div>

                        <form action="{{ route('superadmin.events.attendance_form.delete', [$event->id, $index]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-rose-600 font-bold text-xs p-1 transition-colors">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        Formulir absensi bersifat standar (Belum ada komponen kuesioner/evaluasi kustom).
                    </p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6 flex flex-col justify-start">

            <!-- Metode & Kebijakan Absensi -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 border-b pb-2">⚙️ Metode & Kebijakan Absensi</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">Tentukan bagaimana peserta dapat mencatatkan kehadiran mereka pada event ini.</p>
                </div>

                <form action="{{ route('superadmin.events.attendance.settings', $event->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition">
                            <input type="radio" name="attendance_method" value="scan" class="text-emerald-600 focus:ring-emerald-500 mt-0.5" {{ $event->attendance_method === 'scan' ? 'checked' : '' }} onchange="toggleFormRequirement(this.value)">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">📷 Scan QR Tiket (Panitia)</span>
                                <span class="text-[10px] text-slate-400">Absensi dipindai oleh petugas panitia menggunakan kamera scanner.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition">
                            <input type="radio" name="attendance_method" value="token" class="text-emerald-600 focus:ring-emerald-500 mt-0.5" {{ $event->attendance_method === 'token' ? 'checked' : '' }} onchange="toggleFormRequirement(this.value)">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">🔑 Token Mandiri (Peserta)</span>
                                <span class="text-[10px] text-slate-400">Peserta memasukkan kode token acak melalui halaman dashboard mereka.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition">
                            <input type="radio" name="attendance_method" value="form" class="text-emerald-600 focus:ring-emerald-500 mt-0.5" {{ $event->attendance_method === 'form' ? 'checked' : '' }} onchange="toggleFormRequirement(this.value)">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">📝 Formulir Mandiri (Google Form)</span>
                                <span class="text-[10px] text-slate-400">Peserta mengisi kehadiran + evaluasi secara mandiri lewat link publik.</span>
                            </div>
                        </label>
                    </div>

                    <div id="require-ticket-container" class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1.5 {{ $event->attendance_method === 'form' ? '' : 'hidden' }}">
                        <label class="flex items-center text-xs text-slate-650 font-bold cursor-pointer select-none">
                            <input type="checkbox" name="attendance_require_ticket" value="1" class="rounded text-emerald-600 focus:ring-emerald-500 mr-2 w-3.5 h-3.5" {{ $event->attendance_require_ticket ? 'checked' : '' }}> 
                            Wajib Tiket Terdaftar (Sinkron)
                        </label>
                        <p class="text-[9.5px] text-slate-400 pl-5 leading-normal">
                            Jika dinonaktifkan, siapa saja dapat mengisi absensi secara langsung (seperti Google Form biasa) tanpa perlu mendaftar tiket terlebih dahulu.
                        </p>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl uppercase tracking-wider transition-colors shadow-sm">
                        💾 Terapkan Kebijakan
                    </button>
                </form>

                @if($event->attendance_method === 'form')
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">🔗 Link Absensi Mandiri:</label>
                        <div class="flex items-center gap-1.5">
                            <input type="text" readonly id="public-attendance-link" value="{{ route('public.events.attendance', $event->id) }}" class="flex-1 p-2 bg-slate-50 border border-slate-200 rounded-xl text-[10px] font-mono text-slate-600 select-all outline-none">
                            <button onclick="copyAttendanceLink()" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs border font-bold transition">
                                Salin
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 border-b pb-2">🔑 Otentikasi Token Presensi Kehadiran</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">Nyalakan saklar kehadiran saat sesi seminar berlangsung. Sistem akan mengacak Token unik berkunci keamanan tinggi yang wajib dimasukkan peserta.</p>
                </div>

                <div class="p-4 rounded-xl border text-center space-y-2 {{ $event->is_attendance_open ? 'bg-emerald-50/50 border-emerald-200 ring-1 ring-emerald-300' : 'bg-slate-50 border-slate-200' }}">
                    <span class="text-[9px] font-bold uppercase tracking-widest block {{ $event->is_attendance_open ? 'text-emerald-800' : 'text-slate-400' }}">Gubernansi Ruang Absensi</span>

                    @if($event->is_attendance_open)
                        <span class="text-2xl font-mono font-black text-emerald-900 tracking-widest block select-all bg-white py-2 px-6 rounded-xl border border-emerald-300 w-fit mx-auto shadow-sm">
                            {{ $event->attendance_token }}
                        </span>
                        <span class="text-[10px] text-emerald-600 font-bold block mt-1 animate-pulse">📢 Tayangkan Token di atas di LCD Aula / Layar Zoom!</span>
                    @else
                        <span class="text-xl font-extrabold text-slate-400 block py-3 bg-white rounded-xl border border-dashed tracking-wide uppercase font-mono">Sesi Tertutup</span>
                    @endif
                </div>

                <form action="{{ route('superadmin.events.attendance.toggle', $event->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 text-xs font-bold text-white rounded-xl uppercase tracking-wider shadow-md transition-all duration-200
                        {{ $event->is_attendance_open ? 'bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 shadow-rose-50' : 'bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 shadow-emerald-50' }}">
                        {{ $event->is_attendance_open ? '🛑 Tutup Gerbang Absen & Hancurkan Token' : '⚡ Buka Gerbang Absen & Acak Token Baru' }}
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 border-b pb-2">📜 Template & Tautan Piagam Digital</h3>
                    <p class="text-xs text-slate-450 mt-2 leading-relaxed">Unggah draf base gambar piagam berformat **PNG Polos** untuk dinamis auto-generate, atau sematkan tautan sertifikat eksternal.</p>
                </div>

                <div class="p-3 rounded-xl border text-center bg-slate-50 border-slate-200">
                    @if($event->certificate_template_path)
                        <div class="flex items-center justify-center space-x-2 text-emerald-700 font-bold text-xs">
                            <span>✅ TEMPLATE PIAGAM AKTIF</span>
                        </div>
                        <a href="{{ asset('storage/' . $event->certificate_template_path) }}" target="_blank" class="text-[10px] text-slate-400 underline block mt-1 hover:text-slate-600">Lihat Gambar Base Template</a>
                    @else
                        <span class="text-xs font-semibold text-rose-500 block py-1.5 uppercase font-mono bg-rose-50 rounded border border-rose-100">Belum Ada Template Gambar</span>
                    @endif

                    @if($event->certificate_link)
                        <div class="text-[10px] text-emerald-750 font-bold mt-2">
                            🔗 Tautan Aktif: <a href="{{ $event->certificate_link }}" target="_blank" class="underline text-emerald-600 hover:text-emerald-800">{{ Str::limit($event->certificate_link, 30) }}</a>
                        </div>
                    @endif
                </div>

                <form action="{{ route('superadmin.events.certificate.upload', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Unggah Base PNG (Opsional)</label>
                        <input type="file" name="certificate_template" class="w-full p-1.5 border border-slate-200 rounded-xl text-xs bg-white cursor-pointer file:text-xs file:font-bold file:bg-slate-100 file:border-0 file:rounded-md file:px-2" accept=".png">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Link Sertifikat Eksternal (Opsional)</label>
                        <input type="url" name="certificate_link" value="{{ $event->certificate_link }}" placeholder="https://drive.google.com/..." class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 focus:ring-1 focus:ring-emerald-500 outline-none">
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow-xs transition-colors">
                        📤 Sinkronkan Gambar & Link Sertifikat
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 shadow-3xs">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4 pb-3 border-b gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                    Live Rekapitulasi Dokumen & Manifes Kehadiran ({{ count($recapSubmissions) }} Peserta)
                </h3>
            </div>

            <a href="{{ route('superadmin.events.recap.print', $event->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-black text-white font-bold rounded-lg text-xs transition shadow-3xs inline-flex items-center tracking-wide uppercase">
                🖨️ Cetak / Rekap PDF
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold uppercase border-b border-slate-100 tracking-wider">
                        <th class="p-3.5">Identitas Anggota / Email</th>
                        <th class="p-3.5">Waktu Klaim Tiket</th>
                        <th class="p-3.5">Data Rekap Formulir Kustom</th>
                        <th class="p-3.5 text-center">Status Absensi Presensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($recapSubmissions as $sub)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3.5">
                                @if($sub->user)
                                    <div class="font-bold text-slate-900 text-sm leading-tight">{{ $sub->user->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $sub->user->email }}</div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[7px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-widest mt-1">Anggota</span>
                                @else
                                    <div class="font-bold text-slate-900 text-sm leading-tight">{{ $sub->guest_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $sub->guest_email }}</div>
                                    <div class="text-[9px] text-slate-400 font-normal">📞 {{ $sub->guest_phone }}</div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[7px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-widest mt-1">Umum / Tamu</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-500 font-mono text-[11px]">
                                {{ $sub->created_at->format('d M Y') }}
                                <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $sub->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="p-3.5 space-y-1.5 max-w-xs">
                                @forelse($sub->form_values ?? [] as $val)
                                    <div class="p-2 bg-slate-50 rounded-xl border border-slate-100 text-[11px] leading-normal shadow-3xs">
                                        <span class="text-slate-400 font-extrabold block text-[8px] uppercase tracking-wide">{{ $val['label'] }}:</span>
                                        @if($val['type'] === 'file')
                                            @if(!empty($val['value']))
                                                <a href="{{ asset('storage/' . $val['value']) }}" target="_blank" class="text-emerald-700 font-bold underline hover:text-emerald-900 flex items-center mt-0.5">
                                                    📥 Unduh Dokumen Lampiran
                                                </a>
                                            @else
                                                <span class="text-rose-500 italic font-bold">File Kosong</span>
                                            @endif
                                        @else
                                            <span class="text-slate-800 font-bold">{{ $val['value'] ?? '—' }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-slate-400 italic font-normal text-[11px]">Tidak memerlukan isian kolom tambahan.</span>
                                @endforelse
                            </td>
                            <td class="p-3.5 text-center">
                                @if($sub->attended_at)
                                    <span class="px-2.5 py-0.5 font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px]">
                                        ✅ HADIR ({{ date('H:i', strtotime($sub->attended_at)) }} WIB)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 font-bold rounded-full bg-slate-100 text-slate-400 border border-slate-200 text-[11px]">
                                        ❌ BELUM PRESENSI
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 italic font-normal">
                                Belum ada daftar peserta yang mengklaim tiket masuk untuk event ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function toggleFormRequirement(method) {
        const container = document.getElementById('require-ticket-container');
        if (method === 'form') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function copyAttendanceLink() {
        const copyText = document.getElementById("public-attendance-link");
        if (copyText) {
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value)
                .then(() => {
                    alert("Link absensi berhasil disalin!");
                })
                .catch(err => {
                    console.error("Gagal menyalin: ", err);
                });
        }
    }
</script>
@endsection
