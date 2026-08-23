@extends('pesertabiasa.layouts.app')

@section('title', 'Pos Pelayanan GTU & Konsultasi')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <!-- Top Header Panel -->
    <div class="bg-gradient-to-r from-emerald-900 to-green-800 text-white p-6 rounded-2xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-[10px] font-bold text-emerald-300 bg-emerald-950/60 px-2.5 py-1 rounded-md uppercase font-mono tracking-wider">Official GTU Helpdesk</span>
            <h1 class="text-2xl font-black mt-2 tracking-tight">Pos Pelayanan GTU & Konsultasi</h1>
            <p class="text-xs text-emerald-100 mt-1">Konsultasikan kendala atau ajukan pertanyaan resmi terkait program: <span class="font-bold text-white">{{ $program->name }}</span></p>
        </div>
        <a href="{{ route('programs.internal.dashboard', $program->id) }}" class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950/80 text-white font-bold text-xs rounded-xl transition border border-emerald-700/30">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold flex items-center shadow-2xs">
            <span>🎉 {{ session('success') }}</span>
        </div>
    @endif

    @if(!$program->gtu_email)
        <!-- Gtu Service Inactive State -->
        <div class="bg-white p-8 text-center rounded-2xl border border-slate-100 shadow-sm space-y-4 max-w-2xl mx-auto py-12">
            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-3xl mx-auto border border-slate-100 shadow-3xs">
                🔒
            </div>
            <h3 class="text-base font-bold text-slate-800">Pos Pelayanan Belum Aktif</h3>
            <p class="text-xs text-slate-500 leading-relaxed">Pihak admin untuk program kerja ini belum mengonfigurasi email resmi Pos Pelayanan GTU. Silakan hubungi panitia melalui kanal informasi resmi lainnya.</p>
            <a href="{{ route('programs.internal.dashboard', $program->id) }}" class="inline-flex px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-sm">
                Kembali ke Dashboard
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Submit Consultation Form -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4 h-fit">
                <div class="flex items-center space-x-2 pb-3 border-b mb-1">
                    <span class="text-sm font-bold text-slate-800">Kirim Pertanyaan Baru</span>
                </div>

                <form action="{{ route('programs.internal.gtu.store', $program->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Subjek / Judul Pertanyaan</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Cth: Masalah Integrasi LMS / Pertanyaan Tugas Akhir" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition bg-slate-50/50" required>
                        @error('subject') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Detail Konsultasi / Pertanyaan</label>
                        <textarea name="question" placeholder="Deskripsikan secara lengkap kendala atau pertanyaan Anda..." class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition bg-slate-50/50" rows="6" required>{{ old('question') }}</textarea>
                        @error('question') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-50 transition-all uppercase tracking-wider">
                        Kirim Pertanyaan &rarr;
                    </button>
                </form>
            </div>

            <!-- Right Column: Consultation History & Answers -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 pb-3 border-b mb-1">
                    <span class="text-sm font-bold text-slate-800">Riwayat Konsultasi Anda</span>
                </div>

                @if($consultations->isEmpty())
                    <div class="text-center py-16 bg-slate-50/50 border border-dashed rounded-2xl p-6 text-slate-400 italic text-xs">
                        Belum ada riwayat pertanyaan atau konsultasi yang Anda kirimkan untuk program ini.
                    </div>
                @else
                    <div class="space-y-4 max-h-[550px] overflow-y-auto pr-1">
                        @foreach($consultations as $cons)
                            <div class="p-4 bg-slate-50/60 border border-slate-100 rounded-2xl space-y-3 hover:bg-slate-50 transition duration-200 shadow-3xs">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $cons->subject }}</h4>
                                        <span class="text-[9px] text-slate-400 font-semibold block mt-0.5">Dikirim pada: {{ $cons->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    @if($cons->status === 'pending')
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider font-mono animate-pulse">Mencari Solusi</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider font-mono">Dijawab</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed bg-white p-3.5 rounded-xl border border-slate-100/80 whitespace-pre-wrap">{{ $cons->question }}</p>

                                @if($cons->reply)
                                    <div class="bg-gradient-to-br from-emerald-50/40 to-green-50/20 p-4 rounded-xl border border-emerald-100 text-xs shadow-3xs space-y-1.5">
                                        <div class="flex justify-between items-center text-emerald-950 font-black uppercase tracking-wider text-[10px]">
                                            <span>💬 Jawaban Resmi Admin Program:</span>
                                            <span class="text-emerald-700 font-extrabold font-mono">{{ $cons->answered_at ? $cons->answered_at->format('d M Y H:i') : '' }}</span>
                                        </div>
                                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap font-medium">{{ $cons->reply }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection
