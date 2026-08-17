@extends('layouts.public')

@section('title', 'Verifikasi Sertifikat Alumni - Institut Hijau Indonesia')

@section('content')
<div class="flex-1 flex items-center justify-center p-4 py-12 sm:py-20 bg-slate-50">
    <div class="w-full max-w-xl">
        
        @if($isValid)
            <!-- Valid Certificate Card -->
            <div class="bg-white rounded-3xl border border-emerald-100 shadow-xl overflow-hidden">
                <!-- Status Banner -->
                <div class="bg-emerald-600 p-6 text-center text-white space-y-2">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/20 text-white border border-white/20 animate-pulse">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-black uppercase tracking-wider">Dokumen Terverifikasi</h1>
                    <p class="text-xs font-semibold text-emerald-100">Sertifikat ini sah dan terdaftar resmi di database alumni Institut Hijau Indonesia</p>
                </div>

                <!-- Alumni Details -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Penerima Sertifikat</h2>
                        <p class="text-xl font-black text-slate-800 mt-1">{{ strtoupper($alumni->user->name) }}</p>
                        @if($alumni->user->alumniProfile && $alumni->user->alumniProfile->alumni_number)
                            <p class="text-xs font-mono font-bold text-emerald-700 mt-0.5">NIA: {{ $alumni->user->alumniProfile->alumni_number }}</p>
                        @else
                            <p class="text-xs font-mono font-semibold text-slate-400 mt-0.5">ID Internal: {{ $alumni->uuid }}</p>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Detail Kelulusan Program</h2>
                        <div class="mt-2 space-y-1">
                            <h3 class="text-base font-black text-slate-800">{{ $alumni->alumniProgram->name }}</h3>
                            <p class="text-xs text-slate-500 font-bold">Tahun Penyelenggaraan: {{ $alumni->alumniProgram->year }}</p>
                            <p class="text-xs text-slate-400 font-semibold">Tanggal Kelulusan: {{ $alumni->created_at->format('d F Y') }}</p>
                        </div>
                    </div>

                    <!-- Academic Transkrip / Extra Info Section -->
                    @php
                        $extra = $alumni->extra_info;
                    @endphp
                    @if($extra)
                        <div class="border-t border-slate-100 pt-5 space-y-4">
                            <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-2">Transkrip & Kredensial Tambahan</h2>
                            
                            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs">
                                <div>
                                    <span class="block text-slate-400 font-semibold">Total Beban Jam:</span>
                                    <strong class="text-slate-800 text-sm">{{ $extra['jam_pelatihan'] ?? 32 }} JP (Jam Pelajaran)</strong>
                                </div>
                                
                                @if(!empty($extra['nilai_akhir']))
                                    <div>
                                        <span class="block text-slate-400 font-semibold">Nilai Akhir:</span>
                                        <strong class="text-slate-800 text-sm">{{ $extra['nilai_akhir'] }}</strong>
                                    </div>
                                @endif

                                @if(!empty($extra['predikat']))
                                    <div>
                                        <span class="block text-slate-400 font-semibold">Predikat Kelulusan:</span>
                                        <strong class="text-slate-800 text-sm">{{ $extra['predikat'] }}</strong>
                                    </div>
                                @endif

                                @if(!empty($extra['ranking']))
                                    <div>
                                        <span class="block text-slate-400 font-semibold">Ranking / Peringkat:</span>
                                        <strong class="text-slate-800 text-sm">{{ $extra['ranking'] }}</strong>
                                    </div>
                                @endif

                                @if(!empty($extra['skor_assessment']))
                                    <div>
                                        <span class="block text-slate-400 font-semibold">Assessment Score:</span>
                                        <strong class="text-slate-800 text-sm">{{ $extra['skor_assessment'] }}</strong>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($extra['kompetensi']))
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs">
                                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Kompetensi yang Dicapai:</span>
                                    <p class="text-slate-600 leading-relaxed font-medium">{{ $extra['kompetensi'] }}</p>
                                </div>
                            @endif

                            @if(!empty($extra['catatan']))
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs italic text-slate-500">
                                    <strong>Catatan:</strong> {{ $extra['catatan'] }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Download/View PDF Button for verification -->
                    @if($certificate)
                        <div class="border-t border-slate-100 pt-5">
                            <a href="{{ route('public.alumni.certificate.download', $alumni->uuid) }}" 
                               class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold transition-all shadow-md hover:shadow-emerald-500/20 w-full justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Lihat / Unduh Piagam Asli (PDF)
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Footer branding -->
                <div class="bg-slate-50 border-t border-slate-100 p-4 text-center">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo IHI" class="h-8 mx-auto object-contain">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">&copy; {{ date('Y') }} Institut Hijau Indonesia. All rights reserved.</p>
                </div>
            </div>
        @else
            <!-- Invalid Certificate Card -->
            <div class="bg-white rounded-3xl border border-rose-100 shadow-xl overflow-hidden">
                <!-- Status Banner -->
                <div class="bg-rose-600 p-6 text-center text-white space-y-2">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/20 text-white border border-white/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-black uppercase tracking-wider">Dokumen Tidak Valid</h1>
                    <p class="text-xs font-semibold text-rose-100">ID Verifikasi tidak ditemukan atau dokumen telah kedaluwarsa</p>
                </div>

                <!-- Error Message Detail -->
                <div class="p-8 text-center space-y-4">
                    <h2 class="text-lg font-black text-slate-800">Dokumen Tidak Terdaftar!</h2>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-sm mx-auto">
                        Sertifikat dengan ID tanda tangan digital ini tidak terdaftar dalam database resmi alumni Institut Hijau Indonesia. Mohon periksa kembali keaslian QR Code pada dokumen fisik atau digital Anda.
                    </p>
                    <div class="pt-4">
                        <a href="/" class="inline-flex px-5 py-2.5 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 transition-colors shadow-md">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
