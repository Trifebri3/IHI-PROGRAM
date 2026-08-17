@extends('pesertabiasa.layouts.app')

@section('title', 'Dashboard Portal Event')

@section('content')
<div class="py-6 max-w-5xl mx-auto space-y-6 px-4 sm:px-6">

    <!-- Top Info Card -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-6 rounded-3xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-[9px] font-mono tracking-widest uppercase font-black bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md border border-emerald-500/30">Privileged Event Room</span>
            <h1 class="text-2xl font-black mt-2.5 tracking-tight">{{ $event->title }}</h1>
            <p class="text-xs text-slate-400 mt-1">Sesi Pelaksanaan: Jadwal {{ date('d M Y', strtotime($event->event_date)) }} Pukul {{ $event->event_time }} WIB</p>
        </div>
        <a href="{{ route('events.catalog') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs rounded-xl border border-slate-600 transition-colors">&larr; Kembali ke Katalog</a>
    </div>

    <!-- Alert Sistem -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-3xs">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold shadow-3xs">
            {{ session('error') }}
        </div>
    @endif

    <!-- Layout Utama Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- KOLOM KIRI: GERBANG PRESENSI & SERTIFIKAT -->
        <div class="md:col-span-1 space-y-6">

            <!-- Blok Presensi Kehadiran -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 flex flex-col justify-between space-y-4 shadow-2xs">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 border-b pb-2">Gerbang Presensi Kehadiran</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">Saat panitia atau narasumber membagikan kode unik absensi di layar seminar, masukkan kodenya ke kotak di bawah untuk mengunci lembar absensi Anda.</p>
                </div>

                @if($myRegistration->attended_at)
                    <!-- Kondisi Sudah Absen -->
                    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-center space-y-1.5 shadow-inner">
                        <span class="block text-xs font-black uppercase tracking-wider text-emerald-800">Kehadiran Terverifikasi</span>
                        <span class="block text-[10px] text-slate-400 font-mono">Timestamp: {{ date('H:i', strtotime($myRegistration->attended_at)) }} WIB</span>
                    </div>
                @else
                    <!-- Kondisi Belum Absen -->
                    @if($event->is_attendance_open)
                        <form action="{{ route('events.attendance.verify', $event->id) }}" method="POST" class="space-y-3 bg-amber-50/50 p-4 rounded-xl border border-amber-200">
                            @csrf
                            <label class="block text-[10px] font-black uppercase tracking-wide text-amber-900">Sesi Absensi Sedang Dibuka</label>
                            <input type="text" name="token_input" placeholder="Ketik Kode Token (Cth: EVT-X21)" class="w-full p-2.5 border border-amber-300 rounded-xl text-xs uppercase font-mono font-bold text-center bg-white focus:ring-1 focus:ring-emerald-500 shadow-3xs" required>
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-sm">
                                Kunci Kehadiran Sekarang
                            </button>
                        </form>
                    @else
                        <div class="p-4 bg-slate-50 border rounded-xl text-center text-xs text-slate-400 font-medium italic">
                            Sesi pengisian token absensi belum diaktifkan oleh operator pusat Super Admin. Link Pertemuan: <span class="font-bold text-slate-700 underline block mt-1 select-all">{{ $event->location }}</span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Blok E-Certificate -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-slate-800 border-b pb-2">E-Certificate Penghargaan</h3>

                @if($myRegistration->attended_at)
                    @if($event->certificate_template_path)
                        <!-- Hadir + Template Tersedia -->
                        <div class="p-4 bg-gradient-to-br from-emerald-50 to-white border border-emerald-200 rounded-2xl space-y-3 text-center">
                            <h5 class="text-xs font-black text-emerald-950 uppercase tracking-wider">Piagam Digital Anda Siap</h5>
                            <p class="text-[11px] text-slate-500 leading-normal text-left">Kehadiran Anda tervalidasi resmi oleh sistem pusat. Silakan klik tombol di bawah untuk mencetak piagam penghargaan atas nama Anda.</p>
                            <a href="{{ route('events.certificate.print', $event->id) }}" target="_blank" class="block w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold rounded-xl text-xs uppercase tracking-wider shadow-md hover:from-emerald-700 transition-all text-center">
                                Unduh Piagam Resmi (.PDF)
                            </a>
                        </div>
                    @else
                        <!-- Hadir tapi Template Belum Ada -->
                        <div class="p-4 bg-amber-50/40 border border-amber-200 rounded-xl text-xs text-amber-800 text-left font-medium leading-relaxed shadow-inner">
                            Kehadiran Anda sukses diverifikasi sah. Namun, panitia pelaksana pusat belum melampirkan draf base gambar piagam penghargaan untuk kegiatan ini. Mohon pantau berkala.
                        </div>
                    @endif
                @else
                    <!-- Belum Absen -->
                    <div class="p-5 text-slate-400 italic text-xs bg-slate-50 border border-dashed rounded-2xl text-center">
                        Dokumen sertifikat penghargaan digital hanya akan diterbitkan otomatis jika Anda sudah mengisi token absensi kehadiran yang sah pada sesi acara berlangsung.
                    </div>
                @endif
            </div>

        </div>

        <!-- KOLOM KANAN: DETAIL ACARA & RESUME JAWABAN -->
        <div class="md:col-span-2 space-y-6">

            <!-- Deskripsi Info Acara -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-2xs space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-b pb-2">Deskripsi Silabus Kegiatan</h3>
                <div class="text-xs text-slate-650 leading-relaxed font-medium pt-1">{!! $event->description ?? 'Tidak ada ringkasan deskripsi detail.' !!}</div>
            </div>

            <!-- Resume Jawaban Registrasi -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-slate-800 border-b pb-1">Resume Lembar Jawaban Registrasi Anda</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                    @forelse($myRegistration->form_values ?? [] as $val)
                        <div class="p-3 bg-slate-50/70 border border-slate-100 rounded-xl text-xs">
                            <span class="text-slate-400 font-bold block text-[9px] uppercase tracking-wide mb-0.5">{{ $val['label'] }}</span>
                            @if($val['type'] === 'file')
                                <a href="{{ asset('storage/' . $val['value']) }}" target="_blank" class="text-emerald-700 font-bold underline hover:text-emerald-900 flex items-center mt-1">Unduh Lampiran Berkas Anda</a>
                            @else
                                <span class="text-slate-800 font-extrabold text-sm">{{ $val['value'] ?? '—' }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic col-span-2 py-1">Anda terdaftar via klaim instan sekali klik (tanpa isian form tambahan).</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
