@extends('pesertabiasa.layouts.app')

@section('title', 'Internal Dashboard Program')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <!-- Header Panel -->
    <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-[10px] font-bold text-emerald-400 bg-slate-800 px-2.5 py-1 rounded-md uppercase font-mono tracking-wider">Internal Privilege Space</span>
            <h1 class="text-2xl font-black mt-2 tracking-tight">Selamat Datang, {{ auth()->user()->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">Anda terdaftar resmi pada program: <span class="text-white font-bold">{{ $program->name }}</span></p>
        </div>
        <a href="{{ route('programs.catalog') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition border border-slate-600">Kembali ke Daftar</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Kelulusan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-800">Informasi Pengumuman & Kelulusan</h3>
                <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/30 space-y-2">
                    <h5 class="text-sm font-bold text-slate-800">Status Tahap Aktif: {{ $registration->currentStage?->name ?? 'Siklus Kompetisi Selesai' }}</h5>
                    <p class="text-xs text-slate-600 leading-relaxed italic bg-white p-3 rounded-xl border border-slate-100">
                        @if($registration->status === 'passed')
                            {{ $registration->currentStage?->pass_announcement ?? 'Selamat! Anda resmi dinobatkan lulus final seleksi seluruh rangkaian program kerja.' }}
                        @elseif($registration->status === 'failed')
                            {{ $registration->currentStage?->fail_announcement ?? 'Mohon maaf, langkah Anda terhenti dalam rangkaian seleksi program ini.' }}
                        @else
                            Berkas pengisian Anda sedang ditinjau intensif oleh panitia penilai. Mohon pantau halaman ini secara berkala.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Transkrip Penilaian -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Transkrip Penilaian & Catatan Reviewer</h3>
                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold uppercase border-b">
                                <th class="p-3">Nama Tahapan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Catatan Reviewer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            @foreach($stageLogs as $log)
                                <tr>
                                    <td class="p-3 font-bold">{{ $log->stage->name }}</td>
                                    <td class="p-3">
                                        @if($log->status === 'passed') <span class="px-2 py-0.5 font-bold rounded bg-emerald-50 text-emerald-700 border">LOLOS</span>
                                        @elseif($log->status === 'failed') <span class="px-2 py-0.5 font-bold rounded bg-rose-50 text-rose-700 border">GUGUR</span>
                                        @else <span class="px-2 py-0.5 font-bold rounded bg-amber-50 text-amber-700 border">REVIEWING</span> @endif
                                    </td>
                                    <td class="p-3 italic">{{ $log->reviewer_notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Piagam -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm text-center space-y-4">
                <h3 class="text-sm font-bold text-slate-800 text-left border-b pb-2">Dokumen Kelulusan</h3>

                {{-- Logika Tombol: Harus PASSED dan Program Status FINISHED --}}
                @if($registration->status === 'passed' && $program->status === 'finished')
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-3">
                        <h5 class="text-xs font-black text-amber-900 uppercase">Piagam Penghargaan Digital</h5>
                        <p class="text-[11px] text-slate-500">Akun Anda terverifikasi memiliki Nomor Induk: {{ $registration->final_id_number }}.</p>
                        <a href="{{ route('programs.internal.certificate.print', $program->id) }}" target="_blank" class="block w-full py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold transition text-center uppercase tracking-wider">
                            Unduh Piagam & Raport
                        </a>
                    </div>
                @else
                    <div class="p-6 text-slate-400 italic text-xs bg-slate-50 border border-dashed rounded-2xl">
                        Dokumen Piagam Digital baru akan diterbitkan otomatis jika Anda sudah dinyatakan lolos final dan status program telah dinyatakan selesai (finished) oleh pihak administrasi.
                    </div>
                @endif
            </div>

            <!-- Pos Pelayanan GTU -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm text-center space-y-4">
                <h3 class="text-sm font-bold text-slate-800 text-left border-b pb-2">Pos Pelayanan & Konsultasi</h3>
                <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-3">
                    <div class="w-10 h-10 mx-auto bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center text-lg">
                        💬
                    </div>
                    <h5 class="text-xs font-black text-slate-800 uppercase">Pos Pelayanan GTU</h5>
                    <p class="text-[11px] text-slate-500 leading-relaxed">Punya kendala, pertanyaan, atau butuh konsultasi program? Hubungi admin resmi secara instan.</p>
                    <a href="{{ route('programs.internal.gtu.index', $program->id) }}" class="block w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition text-center uppercase tracking-wider">
                        Ajukan Pertanyaan / Konsultasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Arsip Pengumuman -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mt-6">
        <h3 class="text-sm font-bold text-slate-800 mb-3">Papan Informasi & Arsip Pengumuman</h3>
        <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
            @php $allAnnouncements = \App\Models\ProgramAnnouncement::where('program_id', $program->id)->orderBy('created_at', 'desc')->get(); @endphp
            @forelse($allAnnouncements as $ann)
                <details class="group p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs cursor-pointer">
                    <summary class="flex justify-between items-center font-bold text-slate-800">
                        <span>{{ $ann->title }}</span>
                        <span class="text-[8px] bg-white px-2 py-0.5 rounded border uppercase">{{ $ann->type }}</span>
                    </summary>
                    <div class="text-slate-600 pt-3 border-t mt-2 whitespace-pre-wrap">{!! nl2br(e($ann->content)) !!}</div>
                </details>
            @empty
                <p class="text-xs text-slate-400 italic text-center py-4">Belum ada pengumuman.</p>
            @endforelse
        </div>
    <!-- Pop-up Harapan & Motivasi (Jika Belum Terisi) -->
    @if(empty($registration->motivation))
        <div id="motivation-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-300 space-y-4">
                <div class="border-b pb-3 text-center">
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md uppercase font-mono tracking-wider">Aksi Wajib Diperlukan</span>
                    <h2 class="text-lg font-black text-slate-800 mt-2.5">Harapan &amp; Motivasi Mengikuti Program</h2>
                    <p class="text-xs text-slate-500 mt-1">Anda terdeteksi belum melengkapi Harapan &amp; Motivasi untuk program kerja ini. Silakan lengkapi terlebih dahulu.</p>
                </div>
                
                <form action="{{ route('programs.internal.motivation.update', $program->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <textarea name="motivation" placeholder="Tuliskan harapan dan motivasi Anda mengikuti program ini secara rinci (minimal 10 karakter)..." class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 transition" rows="5" required>{{ old('motivation') }}</textarea>
                        @error('motivation') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black uppercase tracking-wider text-xs rounded-xl transition-all">
                        Simpan &amp; Lanjutkan Aktivitas
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
<section class="lms-support-section">
    <div class="lms-support-header">
        <h2 class="lms-support-title">
            <svg class="lms-support-title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Pusat Bantuan & Layanan
        </h2>
        <p class="lms-support-subtitle">Punya pertanyaan atau kendala? Hubungi tim kami melalui saluran di bawah ini.</p>
    </div>

    <div class="lms-support-grid">
        <div class="lms-support-card">
            <div class="lms-support-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="lms-support-label">Seputar Program & IHI</h3>
            <p class="lms-support-text">Pertanyaan administrasi umum, informasi pendaftaran kelas, sertifikat, atau kemitraan dengan Institut Hijau Indonesia.</p>
            <a href="mailto:instituthijauindonesiaIHI@gmail.com" class="lms-support-link">instituthijauindonesiaIHI@gmail.com</a>
        </div>

        <div class="lms-support-card it-combined-card">
            <div class="lms-support-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="badge-urgent-tag">Respons Cepat</div>
            <h3 class="lms-support-label">Kendala IT & Layanan Sistem</h3>
            <p class="lms-support-text">Mengalami masalah teknis? Anda bisa kirim email atau pilih salah satu opsi tombol WhatsApp di bawah ini.</p>
            
            <div class="it-action-group">
                <a href="mailto:instituthijau.id@gmail.com" class="lms-support-link">instituthijau.id@gmail.com</a>
                
                <div class="wa-options-container">
                    <span class="wa-options-title">Hubungi via WhatsApp sesuai kendala:</span>
                    
                    <a href="https://wa.me/6285862319524?text=Halo%20Tim%20IT%20Support%20Institut%20Hijau%20Indonesia%2C%20saya%20mengalami%20*KENDALA%20AKUN*%20(seperti%20masalah%20login%2Fpassword)%20pada%20sistem%20LMS.%20Mohon%20bantuannya." target="_blank" class="wa-option-btn">
                        <svg class="wa-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.171 1.242 8.421 3.496 2.249 2.254 3.487 5.247 3.483 8.43-.004 6.571-5.329 11.895-11.896 11.895-2.004-.001-3.973-.505-5.717-1.464L0 24zm6.549-3.834l.366.217c1.517.9 3.459 1.376 5.426 1.378 5.673 0 10.288-4.614 10.291-10.287.002-2.748-1.066-5.332-3.008-7.276C17.738 2.554 15.158 1.484 12.42 1.484c-5.676 0-10.292 4.615-10.295 10.288-.001 2.016.528 3.992 1.531 5.739l.238.411-1.01 3.693 3.773-.99z"/></svg>
                        Kendala Akun / Login
                    </a>

                    <a href="https://wa.me/6285862319524?text=Halo%20Tim%20IT%20Support%20Institut%20Hijau%20Indonesia%2C%20saya%20menemukan%20*KENDALA%20SISTEM*%20(seperti%20error%20aplikasi%2C%20halaman%20blank%2C%20atau%20gagal%20submit)%20pada%20LMS.%20Mohon%20bantuannya." target="_blank" class="wa-option-btn">
                        <svg class="wa-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.171 1.242 8.421 3.496 2.249 2.254 3.487 5.247 3.483 8.43-.004 6.571-5.329 11.895-11.896 11.895-2.004-.001-3.973-.505-5.717-1.464L0 24zm6.549-3.834l.366.217c1.517.9 3.459 1.376 5.426 1.378 5.673 0 10.288-4.614 10.291-10.287.002-2.748-1.066-5.332-3.008-7.276C17.738 2.554 15.158 1.484 12.42 1.484c-5.676 0-10.292 4.615-10.295 10.288-.001 2.016.528 3.992 1.531 5.739l.238.411-1.01 3.693 3.773-.99z"/></svg>
                        Kendala Sistem / Error
                    </a>

                    <a href="https://wa.me/6285862319524?text=Halo%20Tim%20IT%20Support%20Institut%20Hijau%20Indonesia%2C%20saya%20ingin%20bertanya%20atau%20melaporkan%20*KENDALA%20TEKNIS%20LAINNYA*%20terkait%20sistem%20LMS.%20Berikut%20detailnya%20%3A" target="_blank" class="wa-option-btn">
                        <svg class="wa-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.171 1.242 8.421 3.496 2.249 2.254 3.487 5.247 3.483 8.43-.004 6.571-5.329 11.895-11.896 11.895-2.004-.001-3.973-.505-5.717-1.464L0 24zm6.549-3.834l.366.217c1.517.9 3.459 1.376 5.426 1.378 5.673 0 10.288-4.614 10.291-10.287.002-2.748-1.066-5.332-3.008-7.276C17.738 2.554 15.158 1.484 12.42 1.484c-5.676 0-10.292 4.615-10.295 10.288-.001 2.016.528 3.992 1.531 5.739l.238.411-1.01 3.693 3.773-.99z"/></svg>
                        Kendala Teknis Lainnya
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .lms-support-section {
        margin-top: 56px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .lms-support-header {
        border-bottom: 1px solid var(--border-color, #e4e4e7);
        padding-bottom: 14px;
        margin-bottom: 28px;
    }

    .lms-support-title {
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -0.3px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-main, #18181b);
    }

    .lms-support-title-icon {
        width: 22px;
        height: 22px;
        color: var(--emerald-primary, #059669);
    }

    .lms-support-subtitle {
        font-size: 12.5px;
        color: var(--text-muted, #71717a);
        margin: 6px 0 0 0;
    }

    /* Grid Layout */
    .lms-support-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 768px) {
        .lms-support-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Desain Kartu Kebersihan Tinggi */
    .lms-support-card {
        box-sizing: border-box;
        background-color: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #e4e4e7);
        border-radius: 14px;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 40px 32px 32px 32px;
        transition: all 0.25s ease;
    }

    .lms-support-card:hover {
        transform: translateY(-3px);
        border-color: var(--emerald-primary, #059669);
        box-shadow: 0 12px 24px -10px rgba(5, 150, 105, 0.15);
    }

    .lms-support-card.it-combined-card {
        border: 1px solid rgba(5, 150, 105, 0.2);
        background-color: rgba(5, 150, 105, 0.01);
    }

    .badge-urgent-tag {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        background-color: var(--emerald-primary, #059669);
        color: #ffffff;
        padding: 3px 9px;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }

    /* Aturan Icon Utama */
    .lms-support-icon {
        color: var(--emerald-primary, #059669);
        margin-bottom: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .lms-support-icon svg {
        width: 30px;
        height: 30px;
    }

    /* Tipografi Label & Paragraf */
    .lms-support-label {
        font-size: 16px;
        font-weight: 750;
        color: var(--text-main, #18181b);
        margin: 0 0 10px 0;
    }

    .lms-support-text {
        font-size: 12.5px;
        color: var(--text-muted, #71717a);
        line-height: 1.6;
        margin: 0 0 24px 0;
        flex-grow: 1;
    }

    /* Grouping untuk Konten IT */
    .it-action-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        width: 100%;
    }

    /* Desain Elegan Tautan/Email Link */
    .lms-support-link {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-main, #18181b);
        text-decoration: none;
        border-bottom: 1px solid var(--border-color, #e4e4e7);
        padding-bottom: 2px;
        transition: all 0.2s ease;
        word-break: break-all;
    }

    .lms-support-link:hover {
        color: var(--emerald-primary, #059669);
        border-color: var(--emerald-primary, #059669);
    }

    /* Container Khusus Opsi WhatsApp */
    .wa-options-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
        border-top: 1px dashed var(--border-color, #e4e4e7);
        padding-top: 16px;
    }

    .wa-options-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted, #71717a);
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    /* Desain Tombol Opsi WhatsApp Sederhana Bersih */
    .wa-option-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--emerald-primary, #059669) !important;
        background-color: transparent;
        color: var(--emerald-primary, #059669) !important;
        font-size: 12.5px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        box-sizing: border-box;
        width: 100%;
    }

    .wa-option-btn:hover {
        background-color: var(--emerald-primary, #059669);
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.15);
    }

    .wa-icon {
        width: 14px;
        height: 14px;
    }
</style>
@endsection
