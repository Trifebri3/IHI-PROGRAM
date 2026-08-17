@extends('pesertabiasa.layouts.app')

@section('title', 'Katalog Pendaftaran Program')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4 border-b pb-5">
        <div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Admission Workspace</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Katalog Program Pendaftaran</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau lini masa tahapan alur kompetisi dan pengisian berkas Anda secara transparan.</p>
        </div>

        <form action="{{ route('programs.catalog') }}" method="GET" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Program..." class="p-2 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 w-full sm:w-48 bg-white shadow-3xs">
            <select name="sort" onchange="this.form.submit()" class="p-2 border border-slate-200 rounded-xl text-xs text-slate-700 bg-white shadow-3xs cursor-pointer focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Urutkan Data --</option>
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Program Terbaru</option>
                <option value="soonest" {{ request('sort') == 'soonest' ? 'selected' : '' }}>Paling Cepat Tutup</option>
            </select>
            @if(request()->has('search') || request()->has('sort'))
                <a href="{{ route('programs.catalog') }}" class="p-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold text-center hover:bg-slate-200">Reset</a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold flex items-center shadow-2xs">
            <span>🎉 {{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold flex items-center shadow-2xs">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($activePrograms as $program)
            @php
                // Hubungkan korelasi pendaftaran user untuk program ini
                $reg = $userRegistrations->get($program->id);
                
                // VALIDASI BARU: Program dianggap tutup jika kolom is_open bernilai false (0)
                $isClosed = !$program->is_open;
            @endphp

            <div class="bg-white rounded-2xl border transition-all duration-300 flex flex-col justify-between overflow-hidden shadow-2xs
                {{ ($reg && $reg->status === 'failed') || $isClosed ? 'border-slate-200 bg-slate-50/50 opacity-85' : 'border-slate-100 hover:border-emerald-500 hover:shadow-md' }}">

                <div class="relative h-36 bg-slate-100">
                    @if($program->banner_path)
                        <img src="{{ asset('storage/' . $program->banner_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-green-700"></div>
                    @endif

                    <div class="absolute -bottom-5 left-5 w-12 h-12 bg-white p-0.5 rounded-full shadow border flex items-center justify-center overflow-hidden">
                        @if($program->logo_path)
                            <img src="{{ asset('storage/' . $program->logo_path) }}" class="w-full h-full object-cover rounded-full">
                        @else
                            <div class="w-full h-full bg-emerald-600 rounded-full flex items-center justify-center text-white font-extrabold text-sm uppercase">
                                {{ substr($program->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-5 pt-7 flex-1 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex justify-between items-start gap-2">
                            <h4 class="text-base font-bold text-slate-800 tracking-tight leading-snug">{{ $program->name }}</h4>

                            {{-- Global Status Badges --}}
                            @if($isClosed)
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-slate-200 text-slate-600 uppercase">Closed</span>
                            @elseif($reg)
                                @if($reg->status === 'passed')
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-600 text-white uppercase animate-bounce">Lolos Final</span>
                                @elseif($reg->status === 'failed')
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-rose-600 text-white uppercase">Gugur</span>
                                @else
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-amber-500 text-white uppercase">Active Seleksi</span>
                                @endif
                            @else
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Buka</span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $program->description ?? 'Tidak ada ringkasan deskripsi objektif.' }}</p>

                        @if($reg)
                            <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-3">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tracking Alur Tahapan Anda:</span>

                                @php
                                    $allProgramStages = \App\Models\ProgramStage::where('program_id', $program->id)->orderBy('sequence')->get();
                                    $currentStageLog = $reg->stageData->keyBy('program_stage_id');
                                @endphp

                                <div class="flex items-center w-full text-center text-[10px] font-bold">
                                    @foreach($allProgramStages as $index => $stage)
                                        @php
                                            $log = $currentStageLog->get($stage->id);
                                            $circleColor = 'bg-slate-200 text-slate-400';
                                            if ($log) {
                                                if ($log->status === 'passed') $circleColor = 'bg-emerald-600 text-white';
                                                elseif ($log->status === 'failed') $circleColor = 'bg-rose-600 text-white';
                                                else $circleColor = 'bg-amber-400 text-slate-900 animate-pulse';
                                            }
                                        @endphp

                                        <div class="flex items-center flex-1 last:flex-none">
                                            <div class="flex flex-col items-center relative">
                                                <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-3xs {{ $circleColor }}" title="{{ $stage->name }}">
                                                    {{ $stage->sequence }}
                                                </div>
                                                <span class="text-[8px] text-slate-500 font-medium truncate max-w-[60px] mt-1 block">{{ $stage->name }}</span>
                                            </div>
                                            @if(!$loop->last)
                                                <div class="flex-1 h-0.5 mx-1 {{ $log && $log->status === 'passed' ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-1">
                        @if($reg)
                            @if($reg->status === 'passed')
                                {{-- SELEBRASI SELESAI & PENERBITAN NOMOR INDUK RESMI --}}
                                <div class="bg-gradient-to-br from-emerald-600 to-green-700 text-white p-3.5 rounded-xl space-y-1.5 shadow-sm">
                                    <div class="text-xs font-black uppercase tracking-wider">🎉 SELAMAT! ANDA DITERIMA RESMI</div>
                                    <div class="text-[11px] text-emerald-100 leading-snug">Anda dinyatakan lulus final seleksi. Berikut Kode Identitas Nomor Induk Program Anda:</div>
                                    <div class="text-sm font-mono font-bold tracking-widest bg-emerald-900/40 p-1.5 rounded-lg text-center border border-emerald-500/30">
                                        {{ $reg->final_id_number ?? 'KODE_PROG_GENERATING' }}
                                    </div>
                                </div>
                            @elseif($reg->status === 'failed')
                                {{-- JIKA GUGUR DI TENGAH JALAN --}}
                                <button type="button" class="w-full py-2 bg-slate-200 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed uppercase tracking-wider border" disabled>
                                    ❌ Langkah Seleksi Anda Terhenti
                                </button>
                            @else
                                {{-- JIKA SEDANG SELEKSI NAMUN ADMIN MENUTUP AKSES TENGAH JALAN --}}
                                @if($isClosed)
                                    <button type="button" class="w-full py-2 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed uppercase border" disabled>
                                        🔒 Tahapan Dihentikan / Ditutup
                                    </button>
                                @else
                                    {{-- JIKA SEDANG ON PROCESS SELEKSI BERJENJANG --}}
                                    @php
                                        $activeStageLog = $reg->stageData->where('program_stage_id', $reg->current_stage_id)->first();
                                        $hasSubmittedActiveStage = ($activeStageLog && !empty($activeStageLog->form_values));
                                    @endphp

                                    @if($hasSubmittedActiveStage)
                                        <button type="button" class="w-full py-2 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold rounded-xl cursor-not-allowed tracking-wider uppercase font-mono" disabled>
                                            ⏳ MENUNGGU REVIEW: {{ $reg->currentStage->name }}
                                        </button>
                                    @else
                                        <a href="{{ route('program.apply', $program->id) }}"
                                           class="block w-full py-2.5 text-center bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold rounded-xl shadow-md shadow-amber-100 transition-all text-xs uppercase tracking-wider animate-pulse">
                                            📝 ISI FORMULIR: {{ $reg->currentStage->name }} &rarr;
                                        </a>
                                    @endif
                                @endif
                            @endif
                        @else
                            {{-- BELUM DAFTAR SAMA SEKALI --}}
                            @if($isClosed)
                                <button type="button" class="w-full py-2 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed uppercase border" disabled>
                                    🔒 Registrasi Ditutup
                                </button>
                            @else
                                <a href="{{ route('program.apply', $program->id) }}"
                                   class="block w-full py-2.5 text-center bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-bold rounded-xl shadow-md shadow-emerald-50 transition-all text-xs uppercase tracking-wider">
                                    Ikuti Program Kerja
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-2 p-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400 italic text-xs shadow-3xs">
                Tidak ditemukan program pendaftaran aktif yang sesuai dengan kriteria pencarian Anda.
            </div>
        @endforelse
    </div>

</div>
@endsection