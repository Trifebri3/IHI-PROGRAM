@extends('pesertabiasa.layouts.app')

@section('title', 'Portal Peserta')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-16">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-8 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Riwayat Aktivitas & Partisipasi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pemantauan global real-time terhadap seluruh keikutsertaan Anda.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-mono font-black text-slate-400 uppercase tracking-widest">Global Tracking Active</span>
            </div>
        </div>

        @php
            $myPrograms = auth()->user()->registrations()->with('program')->latest()->get();
            $myEvents = auth()->user()->eventRegistrations()->with('event')->latest()->get();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-1 w-3 bg-emerald-600 rounded-full"></span>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Program Kerja Intern</h3>
                </div>
                @forelse($myPrograms as $reg)
                    <div class="group bg-white p-4 rounded-2xl border border-slate-100 shadow-3xs flex items-center justify-between gap-4 {{ $reg->status !== 'passed' ? 'bg-slate-50/55 opacity-65' : 'hover:border-emerald-500/30' }} transition-all">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-slate-50 border border-slate-100 {{ $reg->status !== 'passed' ? 'grayscale' : '' }}">
                                @if($reg->program->banner_path)
                                    <img src="{{ asset('storage/' . $reg->program->banner_path) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-600 to-teal-800 flex items-center justify-center text-white/20 font-black text-xs">PRG</div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate">{{ $reg->program->name }}</h4>
                                <div class="mt-1.5">
                                    @if($reg->status === 'passed')
                                        <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wide bg-emerald-50 text-emerald-700">Lulus Seleksi</span>
                                    @elseif($reg->status === 'failed')
                                        <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wide bg-rose-50 text-rose-700">Langkah Terhenti / Gugur</span>
                                    @else
                                        <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wide bg-amber-50 text-amber-700">Proses Seleksi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($reg->status === 'passed')
                            <a href="{{ route('programs.internal.dashboard', $reg->program_id) }}" class="p-2 rounded-xl bg-slate-50 text-slate-400 hover:bg-emerald-600 hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <button type="button" class="p-2 rounded-xl bg-slate-100 text-slate-300 cursor-not-allowed border" title="Akses Dashboard Terkunci" disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed text-xs italic">Tidak ada program.</div>
                @endforelse
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-1 w-3 bg-slate-700 rounded-full"></span>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Seminar & Pengembangan</h3>
                </div>
                @forelse($myEvents as $join)
                    <div class="group bg-white p-4 rounded-2xl border border-slate-100 shadow-3xs flex items-center justify-between gap-4 hover:border-slate-400/30 transition-all">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-slate-50 border border-slate-100">
                                @if($join->event->banner_path)
                                    <img src="{{ asset('storage/' . $join->event->banner_path) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-slate-800 flex items-center justify-center text-white/20 font-black text-xs">EVT</div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate">{{ $join->event->title }}</h4>
                                <span class="text-[10px] font-mono text-slate-400 block mt-0.5">{{ date('d M Y', strtotime($join->event->event_date)) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('events.dashboard', $join->event->id) }}" class="p-2 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed text-xs italic">Tidak ada riwayat event.</div>
                @endforelse
            </div>
        </div>
    </div>

<div class="mb-6 flex justify-end gap-3">
    <a href="{{ route('peserta.alumni.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-2xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-lg hover:shadow-emerald-500/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
        </svg>
        Buka Portal Alumni
    </a>

    <a href="{{ route('forum.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-2xl text-xs font-bold hover:bg-emerald-600 transition-all shadow-lg hover:shadow-emerald-500/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Buka Forum Diskusi
    </a>
</div>
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
