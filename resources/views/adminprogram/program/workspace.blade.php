@extends('adminprogram.layouts.app')

@section('title', 'Workspace Pengelolaan Program')

@section('content')
<!-- Load Quill editor styling and JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <!-- Top Header Panel -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide">Operational Workspace (Native Engine)</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">{{ $program->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">Gubernansi alur bertingkat dan pembuatan formulir dinamis murni Controller.</p>
        </div>
        <div>
            <a href="{{ route('adminprogram.programs.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold rounded-xl transition border">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </div>


<form action="{{ route('adminprogram.programs.toggle', $program->id) }}" method="POST" style="display: inline-block;">
    @csrf
    @if($program->is_open)
        <button type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin MENUTUP pendaftaran program ini secara paksa?')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-all uppercase tracking-wider">
            Tutup Pendaftaran
        </button>
    @else
        <button type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin MEMBUKA KEMBALI pendaftaran program ini?')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-all uppercase tracking-wider">
            Buka Pendaftaran
        </button>
    @endif
</form>

    <!-- Master Session Alert Handle -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold shadow-sm flex items-center">
            <span>✨ {{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-medium space-y-1 shadow-sm">
            <span class="font-bold block mb-1">⚠️ Terjadi Kendala Input Data:</span>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <!-- ROW GRID 1: MANAGEMENT TAHAPAN STAGES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- PANEL KIRI: Form Create ATAU Edit Stage -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50 h-fit">
            <div class="flex items-center space-x-2 pb-3 border-b mb-4">
                <span class="text-sm font-bold text-slate-800">{{ $editingStage ? 'Ubah Atribut Tahapan' : 'Buat Tahapan Baru' }}</span>
            </div>

            <form id="form-stage" action="{{ $editingStage ? route('adminprogram.workspace.stage.update', [$program->id, $editingStage->id]) : route('adminprogram.workspace.stage.store', $program->id) }}" method="POST" class="space-y-4">
                @csrf
                @if($editingStage) @method('PATCH') @endif

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Nama Tahapan</label>
                    <input type="text" name="name" value="{{ old('name', $editingStage ? $editingStage->name : '') }}" placeholder="Cth: Seleksi Berkas Administrasi" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Tgl Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $editingStage ? $editingStage->start_date : '') }}" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Tgl Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $editingStage ? $editingStage->end_date : '') }}" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 text-emerald-800">Template Pesan Lolos</label>
                    <div id="pass-announcement-editor" class="h-32 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;">{!! old('pass_announcement', $editingStage ? $editingStage->pass_announcement : '') !!}</div>
                    <input type="hidden" name="pass_announcement" id="hidden-pass-announcement">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 text-rose-800">Template Pesan Gagal</label>
                    <div id="fail-announcement-editor" class="h-32 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;">{!! old('fail_announcement', $editingStage ? $editingStage->fail_announcement : '') !!}</div>
                    <input type="hidden" name="fail_announcement" id="hidden-fail-announcement">
                </div>

                <div class="flex space-x-2 pt-1">
                    @if($editingStage)
                        <a href="{{ route('adminprogram.programs.workspace', $program->id) }}" class="flex-1 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl border text-center flex items-center justify-center">Batal</a>
                    @endif
                    <button type="submit" class="flex-grow py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-xs rounded-xl hover:from-emerald-700 shadow-md">
                        {{ $editingStage ? 'Simpan Perubahan' : 'Simpan Tahapan' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL KANAN: Daftar Rangkaian Alur / Timeline -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <h4 class="text-sm font-bold text-slate-800 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                Struktur Urutan Rangkaian Program
            </h4>

            @if($stages->isEmpty())
                <p class="text-xs text-slate-400 italic text-center py-10 bg-slate-50 rounded-xl border border-dashed">Belum ada rangkaian tahapan alur yang dibentuk.</p>
            @else
                <div class="relative border-l-2 border-emerald-100 pl-6 ml-3 space-y-4">
                    @foreach($stages as $stg)
                        <div class="relative bg-slate-50/70 p-4 rounded-xl border {{ ($managingStage && $managingStage->id === $stg->id) ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/10' : 'border-slate-100' }} flex justify-between items-center hover:bg-slate-50 transition shadow-2xs">
                            <span class="absolute -left-9 top-4 flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-extrabold text-emerald-700 border border-emerald-200 shadow-3xs">
                                {{ $stg->sequence }}
                            </span>

                            <div>
                                <h5 class="font-bold text-slate-800 text-sm leading-tight flex items-center gap-1.5">
                                    {{ $stg->name }}
                                    @if($stg->is_locked)
                                        <span class="px-1.5 py-0.5 text-[8px] font-bold bg-rose-50 text-rose-600 border border-rose-200 rounded uppercase tracking-wider flex items-center gap-0.5">
                                            🔒 Terkunci
                                        </span>
                                    @endif
                                </h5>
                                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded mt-1.5 inline-block shadow-3xs">
                                    ⏱️ {{ date('d M Y', strtotime($stg->start_date)) }} s/d {{ date('d M Y', strtotime($stg->end_date)) }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-1.5">
                                <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'manage_stage_id' => $stg->id]) }}#form-builder-workspace" class="px-3 py-1.5 bg-gradient-to-r {{ ($managingStage && $managingStage->id === $stg->id) ? 'from-slate-700 to-slate-800' : 'from-emerald-600 to-emerald-700' }} text-white text-xs font-bold rounded-xl transition flex items-center shadow-xs">
                                    🛠️ Kelola Form ({{ count($stg->form_schema ?? []) }})
                                </a>

                                <form action="{{ route('adminprogram.workspace.stage.toggle_lock', [$program->id, $stg->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 transition-all {{ $stg->is_locked ? 'text-rose-600 hover:text-rose-800' : 'text-slate-400 hover:text-emerald-700' }}" title="{{ $stg->is_locked ? 'Buka Kunci Akses Tahap' : 'Kunci Akses Tahap' }}">
                                        @if($stg->is_locked)
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'edit_stage_id' => $stg->id]) }}" class="p-1.5 text-slate-400 hover:text-emerald-700 transition" title="Edit Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>

                                <form action="{{ route('adminprogram.workspace.stage.delete', [$program->id, $stg->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuang tahapan ini dari rangkaian program?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-300 hover:text-rose-600 transition" title="Hapus Tahapan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- PANEL TENGAH: DAFTAR REVIEW DOKUMEN & KELULUSAN PESERTA (DENGAN PAGINATION) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-2 border-b">
            <div class="flex items-center space-x-2">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Review Dokumen & Kelulusan Peserta</h3>
            </div>
            
            <!-- Tabs -->
            <div class="flex bg-slate-100 p-1 rounded-xl w-fit">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'pending', 'page' => null]) }}" 
                   class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all uppercase tracking-wider {{ request('tab', 'pending') === 'pending' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-400 hover:text-slate-650' }}">
                   ⏳ Sedang Proses ({{ $pendingCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'draft', 'page' => null]) }}" 
                   class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all uppercase tracking-wider {{ request('tab') === 'draft' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-400 hover:text-slate-650' }}">
                   ✍️ Draf Pengisian ({{ $draftCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'reviewed', 'page' => null]) }}" 
                   class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all uppercase tracking-wider {{ request('tab') === 'reviewed' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-400 hover:text-slate-650' }}">
                   ✅ Sudah Di-Review ({{ $reviewedCount }})
                </a>
            </div>
        </div>

        <!-- Filters Form -->
        <form action="{{ route('adminprogram.programs.workspace', $program->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-6 items-end bg-slate-50/50 p-4 border rounded-2xl">
            <input type="hidden" name="tab" value="{{ request('tab', 'pending') }}">
            @if(request('edit_stage_id')) <input type="hidden" name="edit_stage_id" value="{{ request('edit_stage_id') }}"> @endif
            @if(request('manage_stage_id')) <input type="hidden" name="manage_stage_id" value="{{ request('manage_stage_id') }}"> @endif
            @if(request('view_stage_id')) <input type="hidden" name="view_stage_id" value="{{ request('view_stage_id') }}"> @endif
            @if(request('view_submission_id')) <input type="hidden" name="view_submission_id" value="{{ request('view_submission_id') }}"> @endif

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Cari Nama / Email</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peserta..." class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white shadow-3xs focus:ring-1 focus:ring-emerald-500 text-slate-800">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Provinsi</label>
                <select name="province" class="w-full p-2 border border-slate-200 rounded-xl text-xs text-slate-700 bg-white shadow-3xs focus:ring-1 focus:ring-emerald-500 cursor-pointer">
                    <option value="">Semua Provinsi</option>
                    @foreach($provinces as $prov)
                        <option value="{{ $prov }}" {{ request('province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Mulai Daftar</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white shadow-3xs focus:ring-1 focus:ring-emerald-500 text-slate-850">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Sampai Daftar</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white shadow-3xs focus:ring-1 focus:ring-emerald-500 text-slate-850">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-xs transition uppercase tracking-wider h-[34px]">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'province', 'start_date', 'end_date']))
                    <a href="{{ route('adminprogram.programs.workspace', array_filter([$program->id, 'tab' => request('tab', 'pending'), 'edit_stage_id' => request('edit_stage_id'), 'manage_stage_id' => request('manage_stage_id'), 'view_stage_id' => request('view_stage_id'), 'view_submission_id' => request('view_submission_id')])) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl border text-center flex items-center justify-center transition uppercase tracking-wider h-[34px]">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="p-3.5">Nama Peserta / Email</th>
                        <th class="p-3.5">Tahapan Berjalan</th>
                        <th class="p-3.5">Status Seleksi</th>
                        <th class="p-3.5">ID Induk Program</th>
                        <th class="p-3.5 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @forelse($applicants as $app)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800">{{ $app->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $app->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3.5 font-semibold text-slate-700">
                                {{ $app->currentStage?->name ?? 'Siklus Berakhir' }}
                                <div class="text-[10px] text-slate-400 font-medium mt-0.5">Sequence Tahap: {{ $app->currentStage?->sequence ?? '-' }}</div>
                            </td>
                            <td class="p-3.5">
                                <div class="flex flex-col gap-1.5">
                                    {{-- Registration Status --}}
                                    @if(request('tab') === 'draft')
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-center w-fit">DRAF AKTIF</span>
                                    @elseif($app->status === 'process')
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-center w-fit">ON PROCESS</span>
                                    @elseif($app->status === 'passed')
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-center w-fit">PASSED FINAL</span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-center w-fit">FAILED</span>
                                    @endif
                                    
                                    {{-- Checking Status --}}
                                    @php
                                        $regMeta = $checkingData[$app->id] ?? null;
                                        $regStatus = $regMeta['status'] ?? (($regMeta && !empty($regMeta['is_checked'])) ? 'checked' : 'unopened');
                                        
                                        $regStatusConfig = [
                                            'unopened' => ['text' => 'Belum Dibuka', 'bg' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                            'opened' => ['text' => 'Sudah Dibuka', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                            'checked' => ['text' => 'Sudah Diperiksa', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-250'],
                                            'passed' => ['text' => 'Lolos Tahap', 'bg' => 'bg-green-600 text-white border-transparent'],
                                            'failed' => ['text' => 'Gugur', 'bg' => 'bg-rose-600 text-white border-transparent'],
                                            'revision' => ['text' => 'Butuh Revisi', 'bg' => 'bg-amber-500 text-white border-transparent']
                                        ];
                                        $regCfg = $regStatusConfig[$regStatus] ?? $regStatusConfig['unopened'];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-black rounded border {{ $regCfg['bg'] }} uppercase tracking-wide w-fit">
                                        {{ $regCfg['text'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-800 tracking-wide">
                                {{ $app->final_id_number ?? '-' }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if(request('tab') === 'draft')
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('adminprogram.programs.applicant.show', [$program->id, $app->id]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border whitespace-nowrap">
                                            👁️ Lihat Draf Sementara
                                        </a>
                                        <span class="text-xs text-amber-600 font-bold bg-amber-50 px-2.5 py-1 rounded border border-amber-200 uppercase tracking-wide">Belum Dikirim</span>
                                    </div>
                                @elseif($app->status === 'process' && $app->current_stage_id)
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('adminprogram.programs.applicant.show', [$program->id, $app->id]) }}" class="px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white text-xs font-bold rounded-xl hover:from-emerald-700 shadow-xs whitespace-nowrap">
                                            🔍 Periksa Berkas
                                        </a>
                                        <form action="{{ route('adminprogram.programs.applicant.instant-pass', [$program->id, $app->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MELOLOSKAN INSTAN peserta {{ $app->user->name ?? '' }}? Proses ini akan langsung mengubah status menjadi lulus, memberikan NIA, dan menerbitkan piagam kelulusan otomatis.')" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-xs whitespace-nowrap">
                                                ✅ Loloskan Instan
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic font-medium">Review Ditutup</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 italic text-xs">
                                @if(request('tab') === 'draft')
                                    Tidak ada data draf pengisian sementara pada tahapan berjalan.
                                @else
                                    Belum ada berkas pendaftaran masuk dari peserta untuk program ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- LINK PAGINATION LINK (Efisien & Ringan) -->
        <div class="mt-4">
            {{ $applicants->links() }}
        </div>
    </div>

    <!-- ROW GRID 2: INTERNAL STAGE FORM BUILDER (WORKSPACE FORMULIR KUSTOM) -->
    <div id="form-builder-workspace" class="pt-2">
        @if(!$managingStage)
            <div class="bg-white p-8 text-center text-slate-400 italic rounded-2xl border border-dashed border-slate-200 text-xs shadow-2xs">
                💡 Silakan klik tombol <span class="font-bold text-emerald-700">"🛠️ Kelola Form"</span> pada salah satu susunan rangkaian alur di atas untuk mulai merakit isi kuesioner kustom pendaftaran.
            </div>
        @else
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-6" x-data="{
                editOpen: false, 
                editIndex: null,
                editName: '',
                editType: '',
                editRequired: false,
                editInstruction: '',
                editPlaceholder: '',
                editOptions: '',
                openEditModal(index, item) {
                    this.editIndex = index;
                    this.editName = item.name;
                    this.editType = item.type;
                    this.editRequired = item.required;
                    this.editInstruction = item.instruction || '';
                    this.editPlaceholder = item.placeholder || '';
                    this.editOptions = (item.options || []).join(', ');
                    this.editOpen = true;
                    this.toggleEditOptions(item.type);
                    
                    // Sync value to Quill editor
                    setTimeout(() => {
                        if (window.quillEditInstruction) {
                            window.quillEditInstruction.root.innerHTML = this.editInstruction;
                        }
                    }, 50);
                },
                showEditOptions: false,
                toggleEditOptions(type) {
                    this.showEditOptions = ['dropdown', 'options', 'checkbox'].includes(type);
                }
            }">
                <!-- Workspace Form Header -->
                <div class="border-b pb-3 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Stage Form Builder Workspace</span>
                        <h4 class="text-sm font-bold text-slate-800 mt-1">Form Desainer Tahap: <span class="text-emerald-700 font-extrabold">{{ $managingStage->name }}</span></h4>
                    </div>
                    <a href="{{ route('adminprogram.programs.workspace', $program->id) }}" class="text-xs text-slate-400 hover:text-slate-600 font-bold">✕ Sembunyikan Workspace</a>
                </div>

                <!-- List Field Terpasang -->
                <div class="space-y-2.5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Input Aktif Pengisian Berkas:</span>
                    @if(empty($managingStage->form_schema))
                        <p class="text-xs text-slate-400 italic p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">Tahap kompetisi ini belum memiliki kolom pengisian berkas. Pasang kuesioner kustom baru di bawah.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($managingStage->form_schema as $index => $item)
                                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-col justify-between shadow-3xs">
                                    <div class="flex justify-between items-center w-full">
                                        <div class="text-xs font-bold text-slate-700 flex items-center min-w-0">
                                            <span class="bg-emerald-50 text-emerald-700 text-[10px] font-extrabold w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0 border border-emerald-200 shadow-3xs">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="truncate">{{ $item['name'] }}</span>
                                            <span class="ml-2 text-[8px] font-black bg-white px-1.5 py-0.5 border rounded text-slate-400 uppercase tracking-wider flex-shrink-0">{{ $item['type'] }}</span>
                                            @if($item['required']) <span class="text-rose-500 ml-1.5 font-bold flex-shrink-0">* Wajib</span> @endif
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0 ml-4">
                                            <!-- Move Up (Disabled if first) -->
                                            @if($index > 0)
                                                <form action="{{ route('adminprogram.workspace.field.move_up', [$program->id, $managingStage->id, $index]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-slate-400 hover:text-slate-800 text-xs p-1 font-bold transition-colors" title="Pindahkan Ke Atas">▲</button>
                                                </form>
                                            @endif

                                            <!-- Move Down (Disabled if last) -->
                                            @if($index < count($managingStage->form_schema) - 1)
                                                <form action="{{ route('adminprogram.workspace.field.move_down', [$program->id, $managingStage->id, $index]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-slate-400 hover:text-slate-800 text-xs p-1 font-bold transition-colors" title="Pindahkan Ke Bawah">▼</button>
                                                </form>
                                            @endif

                                            <!-- Edit Button -->
                                            <button type="button" @click="openEditModal({{ $index }}, {{ json_encode($item) }})" class="text-slate-450 hover:text-emerald-600 font-bold text-xs p-1 transition-colors" title="Ubah Bidang">
                                                ✏️
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('adminprogram.workspace.field.delete', [$program->id, $managingStage->id, $index]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mencabut atribut formulir ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-350 hover:text-rose-600 font-bold text-xs p-1 transition-colors">✕</button>
                                            </form>
                                        </div>
                                    </div>
                                    @if(!empty($item['instruction']) || !empty($item['placeholder']) || !empty($item['options']))
                                        <div class="mt-2 text-[10px] text-slate-400 border-t border-dashed border-slate-200/60 pt-1.5 space-y-0.5">
                                            @if(!empty($item['instruction']))
                                                <div><span class="font-bold text-slate-500">Deskripsi:</span> {{ $item['instruction'] }}</div>
                                            @endif
                                            @if(!empty($item['placeholder']))
                                                <div><span class="font-bold text-slate-500">Contoh:</span> {{ $item['placeholder'] }}</div>
                                            @endif
                                            @if(!empty($item['options']))
                                                <div><span class="font-bold text-slate-500">Opsi:</span> {{ implode(', ', $item['options']) }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Modal Edit Field Kustom -->
                <div x-show="editOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-50 flex justify-center items-start p-4" style="display: none;">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-xl my-8 overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.away="editOpen = false">
                        <!-- Header -->
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Edit Field Component</span>
                                <h3 class="text-sm font-bold text-slate-800 mt-1">Ubah Kolom Kuesioner</h3>
                            </div>
                            <button type="button" @click="editOpen = false" class="text-slate-450 hover:text-slate-650 text-xs font-bold bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition">✕ Tutup</button>
                        </div>

                        <!-- Form -->
                        <form :action="'{{ route('adminprogram.workspace.field.update', [$program->id, $managingStage->id, ':index']) }}'.replace(':index', editIndex)" method="POST" id="edit-field-form" class="p-6 space-y-4">
                            @csrf
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Bidang / Pertanyaan</label>
                                    <input type="text" name="field_name" x-model="editName" class="w-full p-2.5 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs" required>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Input / Jawaban</label>
                                    <select name="field_type" x-model="editType" @change="toggleEditOptions($event.target.value)" class="w-full p-2.5 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs">
                                        <option value="text">Teks Pendek (Short Text)</option>
                                        <option value="textarea">Teks Panjang (Paragraph)</option>
                                        <option value="file">Upload Berkas (PDF/Dokumen)</option>
                                        <option value="image">Upload Gambar (PNG/JPG dengan Preview &amp; Kompresi)</option>
                                        <option value="dropdown">Pilihan Dropdown</option>
                                        <option value="datetime">Tanggal &amp; Waktu (Datetime)</option>
                                        <option value="options">Pilihan Ganda / Radio Button</option>
                                        <option value="checkbox">Pilihan Kotak Centang (Pilih Lebih Dari 1)</option>
                                        <option value="url">Tautan / Link URL (Validasi Link Otomatis)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Deskripsi / Petunjuk Pengisian</label>
                                    <div class="bg-white rounded-xl border border-slate-200 shadow-3xs overflow-hidden">
                                        <div id="edit-instruction-editor" style="min-height: 100px; height: 100px;"></div>
                                    </div>
                                    <input type="hidden" name="field_instruction" id="hidden-edit-instruction" x-model="editInstruction">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Contoh Jawaban / Placeholder</label>
                                    <input type="text" name="field_placeholder" x-model="editPlaceholder" placeholder="Cth: Masukkan alamat lengkap" class="w-full p-2.5 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs text-slate-800">
                                </div>
                            </div>

                            <!-- Options (Shown only for dropdown, options, checkbox) -->
                            <div x-show="showEditOptions" x-transition class="bg-amber-50/20 border border-amber-100 p-4 rounded-xl space-y-1">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Daftar Pilihan Opsi (Pisahkan dengan koma)</label>
                                <input type="text" name="field_options" x-model="editOptions" placeholder="Cth: Pria, Wanita, Lainnya" class="w-full p-2.5 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs text-slate-800">
                                <span class="text-[9px] text-slate-400 block pt-1">Gunakan tanda koma (,) untuk memisahkan pilihan opsi masukan kuesioner.</span>
                            </div>

                            <!-- Wajib Diisi -->
                            <div class="flex items-center">
                                <label class="flex items-center text-xs font-bold text-slate-700 cursor-pointer select-none">
                                    <input type="checkbox" name="field_required" value="1" x-model="editRequired" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200 mr-2 w-4 h-4 shadow-3xs"> 
                                    Tandai sebagai Bidang Wajib Diisi (Required Field)
                                </label>
                            </div>

                            <!-- Footer -->
                            <div class="pt-4 border-t flex justify-end gap-2.5">
                                <button type="button" @click="editOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold text-xs rounded-xl shadow-xs hover:from-emerald-700 transition uppercase tracking-wider">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Form Pembuat/Pemasang Field Baru (Google Form Builder Style) -->
                <form action="{{ route('adminprogram.workspace.field.store', [$program->id, $managingStage->id]) }}" method="POST" id="create-field-form" class="p-5 bg-gradient-to-br from-emerald-50/20 to-white border border-emerald-100 rounded-2xl space-y-4 pt-4">
                    @csrf
                    <span class="block text-xs font-bold uppercase text-emerald-950">🛠️ Pasang Atribut Input Komponen Baru (Google Form Style)</span>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Bidang / Pertanyaan</label>
                            <input type="text" name="new_field_name" placeholder="Cth: Ukuran Seragam / Surat Bebas Narkoba" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs" required>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Input / Jawaban</label>
                            <select name="new_field_type" id="new_field_type_select" onchange="toggleOptionsInput(this.value)" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs">
                                <option value="text">Teks Pendek (Short Text)</option>
                                <option value="textarea">Teks Panjang (Paragraph)</option>
                                <option value="file">Upload Berkas (PDF/Dokumen)</option>
                                <option value="image">Upload Gambar (PNG/JPG dengan Preview &amp; Kompresi)</option>
                                <option value="dropdown">Pilihan Dropdown</option>
                                <option value="datetime">Tanggal &amp; Waktu (Datetime)</option>
                                <option value="options">Pilihan Ganda / Radio Button</option>
                                <option value="checkbox">Pilihan Kotak Centang (Pilih Lebih Dari 1)</option>
                                <option value="url">Tautan / Link URL (Validasi Link Otomatis)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between bg-white p-2 border border-slate-200 rounded-xl h-[34px] shadow-3xs mt-auto">
                            <label class="flex items-center text-[11px] font-bold text-slate-600 cursor-pointer pl-1 select-none">
                                <input type="checkbox" name="new_field_required" value="1" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200 mr-1.5 w-3.5 h-3.5 shadow-3xs" checked> Wajib Diisi
                            </label>
                            <button type="submit" class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-4 py-1 rounded-lg text-xs font-extrabold shadow-sm hover:from-emerald-700 transition">
                                + Pasang Field
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Deskripsi / Petunjuk Pengisian (Opsional)</label>
                            <div class="bg-white rounded-xl border border-slate-200 shadow-3xs overflow-hidden">
                                <div id="new-instruction-editor" style="min-height: 80px; height: 80px;"></div>
                            </div>
                            <input type="hidden" name="new_field_instruction" id="hidden-new-field-instruction">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contoh Jawaban / Placeholder (Opsional)</label>
                            <input type="text" name="new_field_placeholder" placeholder="Cth: L, XL, atau XXL" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs">
                        </div>
                    </div>

                    <div id="options_choices_wrapper" class="hidden">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Daftar Pilihan / Opsi <span class="text-rose-500">*</span> <span class="text-[9px] text-slate-400 font-normal">(Pisahkan setiap opsi dengan tanda koma, cth: Opsi A, Opsi B, Opsi C)</span></label>
                        <input type="text" name="new_field_options" id="new_field_options_input" placeholder="Cth: Pria, Wanita" class="w-full p-2.5 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs">
                    </div>
                </form>

                <script>
                    function toggleOptionsInput(value) {
                        const wrapper = document.getElementById('options_choices_wrapper');
                        const input = document.getElementById('new_field_options_input');
                        if (value === 'dropdown' || value === 'options' || value === 'checkbox') {
                            wrapper.classList.remove('hidden');
                            input.setAttribute('required', 'required');
                        } else {
                            wrapper.classList.add('hidden');
                            input.removeAttribute('required');
                        }
                    }
                </script>
            </div>
        @endif
    </div>

    <!-- MENU KONTROL & KONFIGURASI PROGRAM -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mt-6">
        <div class="flex items-center space-x-2 mb-4 pb-2 border-b">
            <span class="text-emerald-600 text-lg">⚙️</span>
            <h3 class="text-sm font-bold text-slate-800">Menu Kontrol &amp; Konfigurasi Program</h3>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- Card 1: Gatekeeper -->
            <button type="button" id="btn-gatekeeper" onclick="toggleProgramPanel('gatekeeper')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">🛡️</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Form Biodata Wajib</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Konfigurasi biodata &amp; gatekeeper peserta.</p>
                </div>
            </button>

            <!-- Card 2: Academic Transcripts -->
            <button type="button" id="btn-academic" onclick="toggleProgramPanel('academic')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">🎓</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Rancangan E-Raport</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Beban JP &amp; kriteria penilaian raport.</p>
                </div>
            </button>

            <!-- Card 3: Broadcasting Engine -->
            <button type="button" id="btn-broadcasting" onclick="toggleProgramPanel('broadcasting')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">📣</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Broadcasting Engine</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Siarkan pengumuman &amp; broadcast email.</p>
                </div>
            </button>

            <!-- Card 4: Pos Pelayanan GTU -->
            <button type="button" id="btn-gtu" onclick="toggleProgramPanel('gtu')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">📞</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Pos Pelayanan GTU</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Kelola pertanyaan &amp; email GTU.</p>
                </div>
            </button>

            <!-- Card 5: Rekapan & Ekspor -->
            <button type="button" id="btn-recap" onclick="toggleProgramPanel('recap')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">📊</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Rekapan &amp; Ekspor</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Rekap tahap/peserta, cetak PDF &amp; Excel.</p>
                </div>
            </button>

            <!-- Card 6: Pemeriksaan & Kelompok -->
            <button type="button" id="btn-checking" onclick="toggleProgramPanel('checking')" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-200 transition text-left flex items-start space-x-3 group cursor-pointer focus:outline-none w-full">
                <span class="text-2xl p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition">📋</span>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Pemeriksaan &amp; Kelompok</h5>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Batch pemeriksaan, kelompok peserta, &amp; summary.</p>
                </div>
            </button>
        </div>
    </div>

    <!-- GATEKEEPER CONFIGURATION -->
    <div id="panel-gatekeeper" class="hidden mt-6 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Gatekeeper Configuration</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Form Biodata Wajib Program</h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Rakit formulir kustom (Angka, File, Teks) yang wajib diisi peserta sebelum mereka bisa mengakses Dashboard Internal Program Kerja.</p>
            </div>

            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                <form action="{{ route('adminprogram.workspace.biodata.store', $program->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase">Nama Atribut</label>
                        <input type="text" name="field_name" placeholder="Cth: Ukuran Baju / Upload Surat Pernyataan" class="w-full p-2 border rounded-xl text-xs bg-white" required>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Tipe Data</label>
                            <select name="field_type" class="w-full p-2 border rounded-xl text-xs bg-white text-slate-700">
                                <option value="text">Teks Deskriptif</option>
                                <option value="number">Nilai Angka / Nomor</option>
                                <option value="file">Unggahan File (PDF/Image)</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-center pt-4">
                            <label class="text-xs font-bold text-slate-600 flex items-center cursor-pointer">
                                <input type="checkbox" name="is_required" value="1" class="rounded text-emerald-600 mr-1" checked> Wajib Isi
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        + Pasang Kolom Wajib
                    </button>
                </form>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                <span class="block text-[11px] font-bold text-slate-400 uppercase">Formulir Teraktif saat ini:</span>
                @forelse($biodataSchemas as $schema)
                    <div class="p-2 bg-white rounded-lg border border-slate-100 flex justify-between items-center text-xs shadow-3xs">
                        <div class="font-semibold text-slate-700 truncate max-w-[150px]">
                            📌 {{ $schema->field_name }} <span class="text-[9px] text-slate-400 uppercase">({{ $schema->field_type }})</span>
                        </div>
                        <form action="{{ route('adminprogram.workspace.biodata.delete', [$program->id, $schema->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-500 font-bold hover:bg-rose-50 px-1.5 py-0.5 rounded">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada form kustom biodata tambahan. Dashboard program saat ini terbuka bebas untuk semua pendaftar.</p>
                @endforelse
            </div>
        </div>
    </div>


    <!-- ACADEMIC TRANSCRIPTS CONFIGURATION -->
    <div id="panel-academic" class="hidden mt-6 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Academic Transcripts Configuration</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Rancangan E-Raport &amp; JP</h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Tentukan beban Jam Pelajaran (JP) dan tuliskan kriteria kompetensi penilaian secara bebas (dipisah tanda koma). Judul nilai ini akan otomatis menjadi kolom raport pengisian nilai peserta.</p>
            </div>

            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 md:col-span-2 space-y-4">
                <form action="{{ route('adminprogram.workspace.academic.schema', $program->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Beban Durasi (JP)</label>
                            <input type="number" name="total_hours" value="{{ $program->total_hours ?? 32 }}" class="w-full p-2 border rounded-xl text-xs bg-white font-bold text-slate-800" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Daftar Judul Kriteria Nilai <span class="text-emerald-700">(Pisah dengan koma)</span></label>
                            <input type="text" name="raw_criteria" value="{{ !empty($program->score_schema) ? implode(', ', $program->score_schema) : '' }}" placeholder="Cth: Logika Dasar, UI/UX Design, Ketepatan Tugas, Ujian Akhir" class="w-full p-2 border rounded-xl text-xs bg-white text-slate-800 font-semibold" required>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-t pt-2 border-slate-200/60">
                        <div class="text-[10px] text-slate-400 font-medium">Judul Aktif: <span class="text-slate-700 font-bold">{{ !empty($program->score_schema) ? count($program->score_schema) : 0 }} Kriteria</span></div>
                        <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow-sm">
                            🔒 Kunci Format Raport
                        </button>
                    </div>
                </form>

                <form action="{{ route('adminprogram.workspace.certificate.upload', $program->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-dashed grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    @csrf
                    <div class="text-xs font-bold text-slate-700">🖼️ Base PNG Piagam Program:</div>
                    <input type="file" name="program_certificate" class="p-1 border rounded-lg text-xs bg-white cursor-pointer w-full sm:col-span-1" accept="image/png" required>
                    <button type="submit" class="py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg uppercase tracking-wide">Upload PNG</button>
                </form>
            </div>
        </div>
    </div>

    
    <!-- BROADCASTING ENGINE -->
    <div id="panel-broadcasting" class="hidden mt-6 transition-all duration-300 space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Broadcasting Engine</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Siarkan Instruksi Baru</h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Terbitkan maklumat atau intruksi darurat. Pengumuman otomatis dikirimkan ke email peserta serta mengunci paksa tampilan dashboard mereka sampai dibaca sah!</p>
            </div>

            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 md:col-span-2 space-y-3">
                <form action="{{ route('adminprogram.workspace.announcement.store', $program->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="sm:col-span-2">
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Judul Pengumuman</label>
                            <input type="text" name="title" placeholder="Cth: Link Zoom Pembukaan Kelas / Perubahan Aturan" class="w-full p-2 border rounded-xl text-xs bg-white" required>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Derajat Sifat</label>
                            <select name="type" class="w-full p-2 border rounded-xl text-xs bg-white text-slate-700">
                                <option value="info">Info Standar</option>
                                <option value="instruction">Instruksi Wajib Baca (Blocker)</option>
                                <option value="warning">Peringatan Darurat</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase">Isi Pesan Siaran</label>
                        <div id="broadcast-content-editor" class="h-32 bg-slate-50/50 rounded-xl text-xs bg-white" style="font-size: 11px;"></div>
                        <input type="hidden" name="content" id="hidden-broadcast-content">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl transition shadow-sm uppercase tracking-wider">
                            📣 Kirim &amp; Broadcast Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grid 2: List Pengumuman Aktif (CRUD) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4" x-data="{
            editAnnOpen: false,
            editAnnId: null,
            editAnnTitle: '',
            editAnnType: 'info',
            editAnnContent: '',
            openEditAnnModal(ann) {
                this.editAnnId = ann.id;
                this.editAnnTitle = ann.title;
                this.editAnnType = ann.type;
                this.editAnnContent = ann.content || '';
                this.editAnnOpen = true;
                
                // Sync to edit Quill editor
                setTimeout(() => {
                    if (window.quillEditBroadcast) {
                        window.quillEditBroadcast.root.innerHTML = this.editAnnContent;
                    }
                }, 50);
            }
        }">
            <div class="border-b pb-3 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Broadcast History</span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1">Riwayat Pengumuman &amp; Instruksi</h4>
                </div>
            </div>

            @if($announcements->isEmpty())
                <p class="text-xs text-slate-400 italic p-6 bg-slate-50 rounded-2xl border border-dashed text-center">Belum ada pengumuman yang disiarkan untuk program ini.</p>
            @else
                <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-3xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 w-32">Tanggal Siar</th>
                                <th class="p-4 w-64">Judul Pengumuman</th>
                                <th class="p-4 w-36">Derajat Sifat</th>
                                <th class="p-4">Isi Pesan</th>
                                <th class="p-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-semibold">
                            @foreach($announcements as $ann)
                                <tr>
                                    <td class="p-4 text-[10px] text-slate-500 font-mono">
                                        {{ $ann->created_at ? $ann->created_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="p-4 text-xs font-bold text-slate-800">
                                        {{ $ann->title }}
                                    </td>
                                    <td class="p-4">
                                        @if($ann->type === 'info')
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-100 uppercase">Info Standar</span>
                                        @elseif($ann->type === 'instruction')
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-100 uppercase">Instruksi Wajib</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-rose-50 text-rose-700 border border-rose-100 uppercase">Darurat</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-xs text-slate-500 max-w-xs truncate font-normal">
                                        {!! strip_tags($ann->content) !!}
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Tombol Edit --}}
                                            <button type="button" @click="openEditAnnModal({ id: {{ $ann->id }}, title: '{{ addslashes($ann->title) }}', type: '{{ $ann->type }}', content: `{{ addslashes($ann->content) }}` })" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px] transition cursor-pointer">
                                                ✏️ Edit
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('adminprogram.workspace.announcement.delete', [$program->id, $ann->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini secara permanen?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-[10px] transition cursor-pointer">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Modal Edit Pengumuman (Alpine) -->
            <div x-show="editAnnOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-50 flex justify-center items-start p-4" style="display: none;">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-xl my-8 overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.away="editAnnOpen = false">
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <div>
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Edit Broadcast Announcement</span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Ubah Pengumuman / Instruksi</h3>
                        </div>
                        <button type="button" @click="editAnnOpen = false" class="text-slate-450 hover:text-slate-650 text-xs font-bold bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition">✕ Tutup</button>
                    </div>

                    <!-- Form -->
                    <form :action="'{{ route('adminprogram.workspace.announcement.update', [$program->id, ':annId']) }}'.replace(':annId', editAnnId)" method="POST" id="edit-ann-form" class="p-6 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase">Judul Pengumuman</label>
                                <input type="text" name="title" x-model="editAnnTitle" class="w-full p-2 border rounded-xl text-xs bg-white text-slate-800 font-bold" required>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-500 uppercase">Derajat Sifat</label>
                                <select name="type" x-model="editAnnType" class="w-full p-2 border rounded-xl text-xs bg-white text-slate-700">
                                    <option value="info">Info Standar</option>
                                    <option value="instruction">Instruksi Wajib Baca (Blocker)</option>
                                    <option value="warning">Peringatan Darurat</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Isi Pesan Siaran</label>
                            <div class="bg-white rounded-xl border border-slate-200 shadow-3xs overflow-hidden">
                                <div id="edit-broadcast-content-editor" style="min-height: 120px; height: 120px;"></div>
                            </div>
                            <input type="hidden" name="content" id="hidden-edit-broadcast-content">
                        </div>
                        <div class="pt-4 border-t flex justify-end gap-2.5">
                            <button type="button" @click="editAnnOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold text-xs rounded-xl shadow-xs hover:from-emerald-700 transition uppercase tracking-wider">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- POS PELAYANAN GTU CONFIGURATION & LIST -->
    <div id="panel-gtu" class="hidden mt-6 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Pos Pelayanan GTU Engine</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Pos Pelayanan GTU &amp; Konsultasi</h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Admin program wajib memasukkan email resmi. Ketika ada peserta yang mengajukan konsultasi atau pertanyaan, notifikasi akan dikirim langsung ke email ini, dan Anda bisa menjawabnya di sebelah kanan.</p>
                
                <form action="{{ route('adminprogram.workspace.gtu.email', $program->id) }}" method="POST" class="mt-4 space-y-3 p-4 bg-slate-50 border rounded-2xl">
                    @csrf
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Email Pos Pelayanan (Wajib)</label>
                        <input type="email" name="gtu_email" value="{{ $program->gtu_email }}" placeholder="Cth: admin.gtu@instituthijau.or.id" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 shadow-3xs" required>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-sm uppercase tracking-wider">
                        💾 Simpan Email GTU
                    </button>
                </form>
            </div>

            <div class="md:col-span-2 space-y-4">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Konsultasi Masuk dari Peserta:</span>
                
                @if($consultations->isEmpty())
                    <p class="text-xs text-slate-400 italic p-6 bg-slate-50 rounded-2xl border border-dashed text-center">Belum ada pertanyaan atau konsultasi masuk dari peserta.</p>
                @else
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-1">
                        @foreach($consultations as $cons)
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-3 shadow-3xs">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-500">{{ $cons->user->name }} ({{ $cons->user->email }})</span>
                                        <h5 class="text-xs font-bold text-slate-800 mt-0.5">Subjek: {{ $cons->subject }}</h5>
                                    </div>
                                    @if($cons->status === 'pending')
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-amber-100 text-amber-800 border uppercase">Mencari Solusi</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-100 text-emerald-800 border uppercase">Dijawab</span>
                                    @endif
                                </div>
                                
                                <p class="text-xs text-slate-600 leading-relaxed bg-white p-3 rounded-xl border border-slate-100 whitespace-pre-wrap">{{ $cons->question }}</p>
                                
                                @if($cons->reply)
                                    <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100 text-xs">
                                        <span class="font-bold text-emerald-950 block mb-1">Jawaban Admin:</span>
                                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $cons->reply }}</p>
                                        <span class="text-[9px] text-slate-400 mt-1 block">Dijawab pada: {{ $cons->answered_at ? $cons->answered_at->format('d M Y H:i') : '-' }}</span>
                                    </div>
                                @endif

                                {{-- Form to Reply or Edit Reply --}}
                                <details class="group">
                                    <summary class="text-xs text-emerald-700 hover:text-emerald-800 font-bold cursor-pointer list-none flex items-center gap-1 select-none">
                                        <span>{{ $cons->reply ? '📝 Ubah Jawaban' : '💬 Berikan Jawaban' }}</span>
                                        <svg class="w-3.5 h-3.5 transform group-open:rotate-180 transition text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </summary>
                                    <form action="{{ route('adminprogram.workspace.gtu.reply', [$program->id, $cons->id]) }}" method="POST" class="mt-3 space-y-2 pt-2 border-t border-slate-200/50">
                                        @csrf
                                        <textarea name="reply" placeholder="Tuliskan jawaban solusi untuk peserta..." class="w-full p-2.5 border rounded-xl text-xs bg-white text-slate-800 focus:ring-1 focus:ring-emerald-500" rows="3" required>{{ $cons->reply }}</textarea>
                                        <div class="flex justify-end">
                                            <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-lg uppercase tracking-wider">
                                                Kirim Jawaban
                                            </button>
                                        </div>
                                    </form>
                                </details>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- REKAPAN JAWABAN & EKSPOR -->
    <div id="panel-recap" class="hidden mt-6 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Kiri: Rekapan Per Tahap (Semua Peserta) -->
            <div class="space-y-4 border-r border-slate-100 pr-0 md:pr-6">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Stage-wide Response Recap</span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1">Rekapan Jawaban per Tahap (Semua Peserta)</h4>
                    <p class="text-xs text-slate-400 mt-1">Pilih salah satu tahapan program di bawah ini untuk menampilkan jawaban di layar, mengunduh Excel (CSV), atau mencetak PDF.</p>
                </div>
                
                <div class="space-y-3 pt-2">
                    <label class="block text-xs font-bold text-slate-600">Pilih Tahap Program:</label>
                    <select id="recap_stage_select" class="w-full p-2.5 border rounded-xl text-xs bg-white text-slate-700 focus:ring-1 focus:ring-emerald-500">
                        @foreach($stages as $stg)
                            <option value="{{ $stg->id }}" {{ isset($viewStage) && $viewStage->id == $stg->id ? 'selected' : '' }}>Tahap {{ $stg->sequence }}: {{ $stg->name }}</option>
                        @endforeach
                    </select>
                    
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="button" onclick="viewStageOnScreen()" class="w-full py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>👁️ Tampilkan Jawaban di Layar (10 Per Halaman)</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" onclick="exportStage('excel')" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                                <span>📥 Ekspor Excel (CSV)</span>
                            </button>
                            <button type="button" onclick="exportStage('pdf')" class="flex-1 py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                                <span>📄 Cetak / PDF</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Rekapan Per Peserta (Semua Tahap) -->
            <div class="space-y-4">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Individual Participant Recap</span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1">Rekapan Jawaban per Peserta (Semua Tahapan)</h4>
                    <p class="text-xs text-slate-400 mt-1">Pilih peserta terdaftar di bawah ini untuk merekap seluruh jawaban yang diisi dari Tahap 1 hingga tahap terakhir dalam format Excel (CSV) atau cetak PDF.</p>
                </div>
                
                <div class="space-y-3 pt-2">
                    <label class="block text-xs font-bold text-slate-600">Pilih Peserta:</label>
                    <select id="recap_user_select" class="w-full p-2.5 border rounded-xl text-xs bg-white text-slate-700 focus:ring-1 focus:ring-emerald-500">
                        @forelse($allApplicants as $app)
                            @if($app->user)
                                <option value="{{ $app->id }}">{{ $app->user->name }} ({{ $app->user->email }})</option>
                            @endif
                        @empty
                            <option value="">-- Belum ada peserta terdaftar --</option>
                        @endforelse
                    </select>
                    
                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="exportUser('excel')" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5" @if($allApplicants->isEmpty()) disabled @endif>
                            <span>📥 Ekspor Excel (CSV)</span>
                        </button>
                        <button type="button" onclick="exportUser('pdf')" class="flex-1 py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5" @if($allApplicants->isEmpty()) disabled @endif>
                            <span>📄 Cetak / PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danger Zone: Reset Pendaftar Program -->
            <div class="md:col-span-2 mt-4 p-6 bg-rose-50/40 rounded-2xl border border-rose-100 space-y-4 shadow-3xs">
                <div class="flex items-center space-x-2 text-rose-800 font-black text-xs uppercase tracking-wider pb-2 border-b border-rose-100">
                    <span>🚨</span>
                    <span>DANGER ZONE: PEMBERSIHAN DATA PENDAFTAR</span>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                    <!-- Sisi Kiri: Reset Peserta Tertentu -->
                    <div class="space-y-3">
                        <div>
                            <h5 class="text-xs font-bold text-slate-800">1. Reset Akun / Peserta Pilihan</h5>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed">Pilih salah satu peserta untuk menghapus pendaftarannya beserta jawaban kuesioner dan file unggahannya.</p>
                        </div>
                        
                        <form action="{{ route('adminprogram.workspace.reset_applicant', $program->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta terpilih beserta seluruh berkas jawabannya secara permanen?');" class="flex items-end gap-2">
                            @csrf
                            <div class="flex-1">
                                <select name="registration_id" class="w-full p-2 border border-rose-200 rounded-xl text-xs bg-white text-slate-700 focus:ring-rose-500 focus:border-rose-500 shadow-3xs cursor-pointer" required>
                                    <option value="">-- Pilih Peserta --</option>
                                    @foreach($allApplicants as $app)
                                        @if($app->user)
                                            <option value="{{ $app->id }}">{{ $app->user->name }} ({{ $app->user->email }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all uppercase tracking-wider whitespace-nowrap">
                                🗑️ Hapus Peserta
                            </button>
                        </form>
                    </div>

                    <!-- Sisi Kanan: Reset Semua Pendaftar -->
                    <div class="space-y-3 border-t lg:border-t-0 lg:border-l border-rose-100 pt-4 lg:pt-0 lg:pl-6 flex flex-col justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-800">2. Reset Seluruh Pendaftar Program</h5>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed">Menghapus secara permanen seluruh peserta terdaftar, isian berkas kuesioner dinamis, serta berkas lampiran yang diunggah.</p>
                        </div>
                        
                        <form action="{{ route('adminprogram.workspace.reset_all_applicants', $program->id) }}" method="POST" onsubmit="return confirm('PERINGATAN KERAS! Apakah Anda yakin ingin menghapus SELURUH pendaftar program ini? Semua data pendaftaran, biodata wajib, berkas jawaban, kelulusan, dan file fisik lampiran akan dihilangkan secara permanen dari server dan tidak dapat dikembalikan.');">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all uppercase tracking-wider text-center">
                                🔥 Reset Semua Pendaftar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tampilan Preview Tabel Data Kuesioner (Bila Aktif) -->
            @if(isset($viewStage) && isset($stageSubmissions))
                <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-4 space-y-5">
                    
                    <!-- Judul Section -->
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Stage Filter Workspace</span>
                            <h4 class="text-sm font-bold text-slate-800 mt-1">Daftar Kiriman Jawaban Peserta per Tahapan</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Pilih tahapan di bawah ini untuk menyaring kiriman secara otomatis.</p>
                        </div>
                        <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'active_panel' => 'recap']) }}" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-xl transition">✕ Bersihkan Saringan</a>
                    </div>

                    <!-- Tombol Filter Otomatis Per Tahapan -->
                    <div class="flex flex-wrap gap-2.5 pb-2">
                        @foreach($stages as $stg)
                            <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'view_stage_id' => $stg->id, 'active_panel' => 'recap']) }}" 
                               class="px-4 py-2 text-xs font-bold rounded-xl transition-all border flex items-center gap-1.5 shadow-3xs
                               {{ $viewStage->id == $stg->id 
                                  ? 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700' 
                                  : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <span>📍</span>
                                <span>Tahap {{ $stg->sequence }}: {{ $stg->name }}</span>
                            </a>
                        @endforeach
                    </div>

                    <!-- List Data Jawaban Peserta -->
                    <div class="space-y-3">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Peserta Terdaftar Tahap [{{ $viewStage->name }}] (10 Per Halaman):</span>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            @forelse($stageSubmissions as $sub)
                                @if($sub->registration && $sub->registration->user)
                                    <div class="p-4 bg-slate-50/60 hover:bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center transition shadow-3xs">
                                        <div class="space-y-1 pr-4">
                                            <span class="text-[10px] text-slate-400 font-mono block">Diisi pada: {{ $sub->updated_at ? $sub->updated_at->format('d M Y H:i') : '-' }}</span>
                                            <h5 class="text-xs font-black text-slate-700">{{ $sub->registration->user->name }}</h5>
                                            <p class="text-[10px] text-slate-400">{{ $sub->registration->user->email }}</p>
                                            
                                            <div class="pt-1">
                                                @if($sub->status === 'passed')
                                                    <span class="px-2 py-0.5 text-[8px] font-black rounded bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">Lulus</span>
                                                @elseif($sub->status === 'failed')
                                                    <span class="px-2 py-0.5 text-[8px] font-black rounded bg-rose-50 text-rose-700 border border-rose-100 uppercase">Gagal</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[8px] font-black rounded bg-amber-50 text-amber-700 border border-amber-100 uppercase">Proses</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'view_stage_id' => $viewStage->id, 'view_submission_id' => $sub->id, 'active_panel' => 'recap']) }}" 
                                           class="px-3.5 py-2 bg-slate-800 hover:bg-black text-white font-extrabold text-[11px] rounded-xl shadow-xs transition-all flex items-center gap-1">
                                            <span>👁️</span> Lihat Jawaban
                                        </a>
                                    </div>
                                @endif
                            @empty
                                <div class="md:col-span-2 p-8 text-center bg-slate-50 text-slate-400 italic text-xs rounded-2xl border border-dashed border-slate-200">
                                    Belum ada peserta yang mengumpulkan berkas/data jawaban untuk tahapan "{{ $viewStage->name }}".
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                        <div class="text-[10px] text-slate-400 font-mono">
                            Menampilkan {{ $stageSubmissions->firstItem() ?? 0 }} - {{ $stageSubmissions->lastItem() ?? 0 }} dari {{ $stageSubmissions->total() }} data
                        </div>
                        <div class="flex items-center justify-end space-x-1 custom-pagination">
                            {{ $stageSubmissions->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Viewer Detail Jawaban Dinamis Peserta -->
            @if(isset($viewSubmission) && $viewSubmission->registration && $viewSubmission->registration->user)
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-50 flex justify-center items-start p-4 transition-opacity duration-300">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-2xl my-8 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        <!-- Header -->
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Submission Details</span>
                                <h3 class="text-sm font-bold text-slate-800 mt-1">Jawaban Kuesioner Tahap: <span class="text-emerald-700 font-extrabold">{{ $viewStage->name }}</span></h3>
                            </div>
                            <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'view_stage_id' => $viewStage->id, 'active_panel' => 'recap']) }}" class="text-slate-400 hover:text-slate-600 text-xs font-bold bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition">✕ Tutup</a>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-5">
                            <!-- Profile -->
                            <div class="flex items-center space-x-3 p-3.5 bg-slate-50/60 rounded-xl border border-slate-100">
                                <span class="text-2xl">👤</span>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">{{ $viewSubmission->registration->user->name }}</h4>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $viewSubmission->registration->user->email }} | Dikirim pada: {{ $viewSubmission->updated_at ? $viewSubmission->updated_at->format('d M Y H:i') : '-' }}</p>
                                </div>
                            </div>

                            <!-- Q&A Lists -->
                            <div class="space-y-4">
                                @forelse($viewSubmission->form_values ?? [] as $form)
                                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/20 space-y-1.5 shadow-3xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $form['field_name'] }} <span class="text-[8px] font-normal uppercase font-mono">({{ $form['type'] }})</span></span>
                                        
                                        @if($form['type'] === 'file')
                                            @if(!empty($form['value']))
                                                <div class="pt-1">
                                                    <a href="{{ asset('storage/' . $form['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                                        📥 Unduh Berkas Lampiran
                                                    </a>
                                                </div>
                                            @else
                                                <p class="text-xs text-rose-500 font-bold italic">⚠️ Berkas wajib tidak diunggah peserta!</p>
                                            @endif
                                        @elseif($form['type'] === 'image')
                                            @if(!empty($form['value']))
                                                <div class="pt-1 space-y-2">
                                                    <a href="{{ asset('storage/' . $form['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                                        🔍 Buka Gambar Ukuran Penuh
                                                    </a>
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . $form['value']) }}" class="max-w-md max-h-60 rounded-xl border border-slate-200 shadow-sm object-cover" alt="Lampiran Gambar">
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-xs text-rose-500 font-bold italic">⚠️ Gambar wajib tidak diunggah peserta!</p>
                                            @endif
                                        @elseif($form['type'] === 'url')
                                            @if(!empty($form['value']))
                                                <div class="pt-1">
                                                    <a href="{{ $form['value'] }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                                        🔗 Buka Tautan Link ({{ $form['value'] }})
                                                    </a>
                                                </div>
                                            @else
                                                <p class="text-xs text-rose-500 font-bold italic">⚠️ Tautan link wajib tidak diisi peserta!</p>
                                            @endif
                                        @else
                                            <p class="text-xs font-semibold text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $form['value'] ?? '— (Kosong)' }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic text-center py-4 bg-slate-50 border border-dashed rounded-xl">Tidak ada isian jawaban kuesioner.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <form action="{{ route('adminprogram.workspace.submission.reset', [$program->id, $viewSubmission->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset seluruh jawaban peserta ini? Semua jawaban dan file berkas yang diunggah untuk tahapan ini akan dihapus permanen.');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl transition shadow-sm">
                                    ⚠️ Reset Jawaban (Nol Kembali)
                                </button>
                            </form>

                            <a href="{{ route('adminprogram.programs.workspace', [$program->id, 'view_stage_id' => $viewStage->id, 'active_panel' => 'recap']) }}" class="px-4 py-2 bg-slate-800 hover:bg-black text-white font-bold text-xs rounded-xl transition">
                                Tutup Viewer
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- PEMERIKSAAN & KELOMPOK PANEL -->
    <div id="panel-checking" class="hidden mt-6 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 space-y-6">
            <!-- Judul & Deskripsi -->
            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Administrative Verification Panel</span>
                    <h3 class="text-base font-black text-slate-800 mt-1">Pemeriksaan Berkas &amp; Pengelompokan Peserta</h3>
                    <p class="text-xs text-slate-400 mt-0.5 font-medium">Pantau status verifikasi kelengkapan administrasi peserta, tandai status periksa secara massal, serta kelompokkan peserta ke dalam batch tertentu.</p>
                </div>
            </div>

            @php
                $checkedCount = 0;
                $uncheckedCount = 0;
                $batches = [];
                
                foreach ($allApplicants as $app) {
                    $regId = $app->id;
                    $meta = $checkingData[$regId] ?? null;
                    if ($meta && !empty($meta['is_checked'])) {
                        $checkedCount++;
                        $bName = $meta['batch_name'] ?? 'Tanpa Kelompok';
                        $batches[$bName] = ($batches[$bName] ?? 0) + 1;
                    } else {
                        $uncheckedCount++;
                        $bName = ($meta['batch_name'] ?? '') ?: 'Tanpa Kelompok';
                        $batches[$bName] = ($batches[$bName] ?? 0) + 1;
                    }
                }
            @endphp

            <!-- Summary Cards Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Card 1: Total Pendaftar -->
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex items-center space-x-3.5 shadow-3xs">
                    <span class="text-2xl">👥</span>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Pendaftar</span>
                        <span class="text-sm font-black text-slate-800">{{ count($allApplicants) }} orang</span>
                    </div>
                </div>

                <!-- Card 2: Sudah Diperiksa -->
                <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl flex items-center space-x-3.5 shadow-3xs">
                    <span class="text-2xl">✅</span>
                    <div>
                        <span class="block text-[10px] text-emerald-700 font-bold uppercase tracking-wider">Sudah Diperiksa</span>
                        <span class="text-sm font-black text-emerald-800">{{ $checkedCount }} orang</span>
                    </div>
                </div>

                <!-- Card 3: Belum Diperiksa -->
                <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-xl flex items-center space-x-3.5 shadow-3xs">
                    <span class="text-2xl">⏳</span>
                    <div>
                        <span class="block text-[10px] text-amber-700 font-bold uppercase tracking-wider">Belum Diperiksa</span>
                        <span class="text-sm font-black text-emerald-800">{{ $uncheckedCount }} orang</span>
                    </div>
                </div>

                <!-- Card 4: Distribusi Kelompok/Batch -->
                <div class="p-4 bg-indigo-50/30 border border-indigo-100 rounded-xl flex items-center space-x-3.5 shadow-3xs">
                    <span class="text-2xl">📦</span>
                    <div class="overflow-hidden">
                        <span class="block text-[10px] text-indigo-700 font-bold uppercase tracking-wider">Daftar Kelompok</span>
                        <div class="text-[9px] font-bold text-slate-600 mt-1 max-h-12 overflow-y-auto space-y-0.5 leading-tight">
                            @forelse($batches as $bName => $bCount)
                                <span class="inline-block bg-white border px-1.5 py-0.5 rounded-md mr-1 mt-0.5">
                                    {{ $bName }}: <span class="text-emerald-750 font-black">{{ $bCount }}</span>
                                </span>
                            @empty
                                <span class="italic text-slate-400 font-medium">Belum ada kelompok dibuat.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter, Cari & Aksi Massal Toolbar -->
            <div class="p-5 bg-slate-50 border border-slate-100 rounded-2xl space-y-4 shadow-3xs">
                <!-- Baris 1: Live Filter & Sorting -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Cari Nama/Email:</label>
                        <input type="text" id="chk_search" onkeyup="filterCheckingTable()" placeholder="Ketik nama atau email..." class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs">
                    </div>

                    <!-- Checked Status Filter -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Periksa:</label>
                        <select id="chk_filter_status" onchange="filterCheckingTable()" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs">
                            <option value="all">Semua</option>
                            <option value="unopened">⏳ Belum Dibuka</option>
                            <option value="opened">📖 Sudah Dibuka</option>
                            <option value="checked">✅ Sudah Diperiksa</option>
                            <option value="passed">🎉 Lolos Tahap</option>
                            <option value="failed">❌ Gugur</option>
                            <option value="revision">⚠️ Butuh Revisi</option>
                        </select>
                    </div>

                    <!-- Group/Batch Filter -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kelompok/Batch:</label>
                        <select id="chk_filter_batch" onchange="filterCheckingTable()" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs">
                            <option value="all">Semua Kelompok</option>
                            <option value="none">Tanpa Kelompok</option>
                            @foreach(array_keys($batches) as $bName)
                                @if($bName !== 'Tanpa Kelompok')
                                    <option value="{{ $bName }}">{{ $bName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Sort -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Urutan Tanggal Daftar:</label>
                        <select id="chk_sort_date" onchange="sortCheckingTable()" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs">
                            <option value="desc">Terbaru Pertama</option>
                            <option value="asc">Terlama Pertama</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-200/60">

                <!-- Baris 2: Formulir Aksi Massal (Bulk Action) -->
                <form id="bulk_checking_form" action="{{ route('adminprogram.workspace.update_checking', $program->id) }}" method="POST" onsubmit="return validateBulkForm();" class="space-y-3.5">
                    @csrf
                    <div class="flex items-center space-x-2 text-slate-700 font-extrabold text-[11px] uppercase tracking-wide">
                        <span>⚡</span>
                        <span>Aksi Massal Untuk Peserta Terpilih (Centang Kolom Tabel):</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        <!-- Checked Status Action -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tandai Status:</label>
                            <select name="is_checked" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700 shadow-3xs" required>
                                <option value="checked">✅ Sudah Diperiksa</option>
                                <option value="unopened">⏳ Belum Dibuka</option>
                                <option value="opened">📖 Sudah Dibuka</option>
                                <option value="passed">🎉 Lolos Tahap</option>
                                <option value="failed">❌ Gugur</option>
                                <option value="revision">⚠️ Butuh Revisi</option>
                            </select>
                        </div>

                        <!-- Batch Name -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Masukkan Kelompok/Batch:</label>
                            <input type="text" name="batch_name" placeholder="Cth: Kelompok A / Batch 1" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs">
                        </div>

                        <!-- Checker Name -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Pemeriksa:</label>
                            <input type="text" name="checked_by" value="{{ auth()->user()->name ?? 'Admin' }}" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 shadow-3xs">
                        </div>

                        <!-- Apply Button -->
                        <div>
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all uppercase tracking-wider flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🚀 Terapkan ke (<span id="checked_count_badge">0</span>) Peserta</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Data Peserta -->
            <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-3xs">
                <table class="w-full text-left border-collapse" id="checking_table">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4 w-12 text-center">
                                <input type="checkbox" id="check_all_applicants" onchange="toggleSelectAllApplicants(this)" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="p-4">Peserta &amp; Profil</th>
                            <th class="p-4">Tanggal Daftar</th>
                            <th class="p-4">Status Periksa</th>
                            <th class="p-4">Keterangan Pemeriksaan</th>
                            <th class="p-4 text-center">Kelompok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-semibold" id="checking_tbody">
                        @forelse($allApplicants as $app)
                            @php
                                $meta = $checkingData[$app->id] ?? null;
                                $status = $meta['status'] ?? (($meta && !empty($meta['is_checked'])) ? 'checked' : 'unopened');
                                $batchName = ($meta['batch_name'] ?? '') ?: 'Tanpa Kelompok';
                                
                                $statusConfig = [
                                    'unopened' => ['text' => 'Belum Dibuka', 'bg' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                    'opened' => ['text' => 'Sudah Dibuka', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'checked' => ['text' => 'Sudah Diperiksa', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    'passed' => ['text' => 'Lolos Tahap', 'bg' => 'bg-green-600 text-white border-transparent'],
                                    'failed' => ['text' => 'Gugur', 'bg' => 'bg-rose-600 text-white border-transparent'],
                                    'revision' => ['text' => 'Butuh Revisi', 'bg' => 'bg-amber-500 text-white border-transparent']
                                ];
                                $cfg = $statusConfig[$status] ?? $statusConfig['unopened'];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition checking-row" 
                                data-id="{{ $app->id }}"
                                data-name="{{ strtolower($app->user->name ?? '') }}"
                                data-email="{{ strtolower($app->user->email ?? '') }}"
                                data-checked="{{ $status }}"
                                data-batch="{{ strtolower($batchName) }}"
                                data-timestamp="{{ strtotime($app->created_at) }}">
                                
                                <td class="p-4 text-center w-12">
                                    <input type="checkbox" name="registration_ids[]" form="bulk_checking_form" value="{{ $app->id }}" onchange="updateSelectedCount()" class="applicant-checkbox rounded text-emerald-600 focus:ring-emerald-500 border-slate-200 w-4 h-4 cursor-pointer">
                                </td>
                                
                                <td class="p-4">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-lg">👤</span>
                                        <div>
                                            <span class="font-bold text-slate-800 block text-xs">{{ $app->user->name ?? '—' }}</span>
                                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $app->user->email ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="p-4 text-[10px] text-slate-500 font-mono">
                                    {{ $app->created_at ? $app->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[9px] font-black rounded border {{ $cfg['bg'] }} uppercase tracking-wide">
                                        {{ $cfg['text'] }}
                                    </span>
                                </td>
                                
                                <td class="p-4 text-[11px] leading-relaxed">
                                    @if($status !== 'unopened')
                                        <div class="space-y-0.5 text-slate-600">
                                            <div><span class="font-bold text-slate-400">Oleh:</span> {{ $meta['checked_by'] ?? 'Admin' }}</div>
                                            <div class="text-[9px] font-mono text-slate-400"><span class="font-bold text-slate-400">Waktu:</span> {{ $meta['checked_at'] ?? '-' }}</div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic font-medium">Belum dibuka oleh panitia.</span>
                                    @endif
                                </td>
                                
                                <td class="p-4 text-center">
                                    <span class="inline-block px-2.5 py-1 text-[10px] font-extrabold rounded-xl border {{ $batchName !== 'Tanpa Kelompok' ? 'bg-indigo-50 border-indigo-100 text-indigo-700' : 'bg-slate-100 border-slate-200 text-slate-500' }}">
                                        {{ $batchName }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center bg-slate-50 text-slate-400 italic text-xs rounded-b-2xl">
                                    Belum ada peserta yang mendaftar pada program ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Javascript helper for dynamic checking panel -->
            <script>
                function toggleSelectAllApplicants(checkbox) {
                    const checkboxes = document.querySelectorAll('.applicant-checkbox');
                    checkboxes.forEach(cb => {
                        const row = cb.closest('tr');
                        if (row && !row.classList.contains('hidden')) {
                            cb.checked = checkbox.checked;
                        }
                    });
                    updateSelectedCount();
                }

                function updateSelectedCount() {
                    const selected = document.querySelectorAll('.applicant-checkbox:checked').length;
                    document.getElementById('checked_count_badge').innerText = selected;
                }

                function validateBulkForm() {
                    const selected = document.querySelectorAll('.applicant-checkbox:checked').length;
                    if (selected === 0) {
                        alert('Silakan pilih minimal 1 peserta terlebih dahulu dengan mencentang kolom tabel!');
                        return false;
                    }
                    return true;
                }

                function filterCheckingTable() {
                    const searchVal = document.getElementById('chk_search').value.toLowerCase();
                    const statusVal = document.getElementById('chk_filter_status').value;
                    const batchVal = document.getElementById('chk_filter_batch').value.toLowerCase();
                    
                    const rows = document.querySelectorAll('.checking-row');
                    
                    rows.forEach(row => {
                        const name = row.getAttribute('data-name');
                        const email = row.getAttribute('data-email');
                        const checked = row.getAttribute('data-checked');
                        const batch = row.getAttribute('data-batch');
                        
                        let matchesSearch = !searchVal || name.includes(searchVal) || email.includes(searchVal);
                        let matchesStatus = statusVal === 'all' || checked === statusVal;
                        
                        let matchesBatch = true;
                        if (batchVal !== 'all') {
                            if (batchVal === 'none') {
                                matchesBatch = batch === 'tanpa kelompok';
                            } else {
                                matchesBatch = batch === batchVal;
                            }
                        }
                        
                        if (matchesSearch && matchesStatus && matchesBatch) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                            const cb = row.querySelector('.applicant-checkbox');
                            if (cb) cb.checked = false;
                        }
                    });
                    
                    updateSelectedCount();
                }

                function sortCheckingTable() {
                    const sortOrder = document.getElementById('chk_sort_date').value;
                    const tbody = document.getElementById('checking_tbody');
                    const rows = Array.from(tbody.querySelectorAll('.checking-row'));
                    
                    rows.sort((a, b) => {
                        const timeA = parseInt(a.getAttribute('data-timestamp'));
                        const timeB = parseInt(b.getAttribute('data-timestamp'));
                        
                        return sortOrder === 'asc' ? timeA - timeB : timeB - timeA;
                    });
                    
                    rows.forEach(row => tbody.appendChild(row));
                }
            </script>
        </div>
    </div>

    <!-- Toggle scripts -->
    <script>
        function viewStageOnScreen() {
            const stageId = document.getElementById('recap_stage_select').value;
            if (!stageId) {
                alert('Silakan pilih tahapan terlebih dahulu.');
                return;
            }
            window.location.href = "{{ route('adminprogram.programs.workspace', $program->id) }}?view_stage_id=" + stageId;
        }

        function exportStage(format) {
            const stageId = document.getElementById('recap_stage_select').value;
            if (!stageId) {
                alert('Silakan pilih tahapan terlebih dahulu.');
                return;
            }
            let url = '';
            if (format === 'excel') {
                url = "{{ route('adminprogram.workspace.export.stage.excel', [$program->id, ':stageId']) }}";
            } else {
                url = "{{ route('adminprogram.workspace.export.stage.pdf', [$program->id, ':stageId']) }}";
            }
            url = url.replace(':stageId', stageId);
            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }

        function exportUser(format) {
            const regId = document.getElementById('recap_user_select').value;
            if (!regId) {
                alert('Silakan pilih peserta terlebih dahulu.');
                return;
            }
            let url = '';
            if (format === 'excel') {
                url = "{{ route('adminprogram.workspace.export.user.excel', [$program->id, ':regId']) }}";
            } else {
                url = "{{ route('adminprogram.workspace.export.user.pdf', [$program->id, ':regId']) }}";
            }
            url = url.replace(':regId', regId);
            if (format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }

        function toggleProgramPanel(panelName) {
            const panels = {
                'gatekeeper': document.getElementById('panel-gatekeeper'),
                'academic': document.getElementById('panel-academic'),
                'broadcasting': document.getElementById('panel-broadcasting'),
                'gtu': document.getElementById('panel-gtu'),
                'recap': document.getElementById('panel-recap'),
                'checking': document.getElementById('panel-checking')
            };
            
            const buttons = {
                'gatekeeper': document.getElementById('btn-gatekeeper'),
                'academic': document.getElementById('btn-academic'),
                'broadcasting': document.getElementById('btn-broadcasting'),
                'gtu': document.getElementById('btn-gtu'),
                'recap': document.getElementById('btn-recap'),
                'checking': document.getElementById('btn-checking')
            };

            const targetPanel = panels[panelName];
            const isCurrentlyOpen = targetPanel && !targetPanel.classList.contains('hidden');

            // Hide all panels and reset button styles
            for (const key in panels) {
                if (panels[key]) {
                    panels[key].classList.add('hidden');
                }
                if (buttons[key]) {
                    buttons[key].classList.remove('border-emerald-500', 'bg-emerald-50/30', 'ring-1', 'ring-emerald-500');
                    buttons[key].classList.add('border-slate-100', 'bg-slate-50/50');
                }
            }

            // If it wasn't open, open it
            if (!isCurrentlyOpen && targetPanel) {
                targetPanel.classList.remove('hidden');
                if (buttons[panelName]) {
                    buttons[panelName].classList.remove('border-slate-100', 'bg-slate-50/50');
                    buttons[panelName].classList.add('border-emerald-500', 'bg-emerald-50/30', 'ring-1', 'ring-emerald-500');
                }
                localStorage.setItem('active_program_panel_{{ $program->id }}', panelName);
                
                // Scroll to the active panel smoothly
                setTimeout(() => {
                    targetPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            } else {
                localStorage.removeItem('active_program_panel_{{ $program->id }}');
            }
        }

        // Auto-restore active panel on load
        document.addEventListener('DOMContentLoaded', function() {
            let activePanel = localStorage.getItem('active_program_panel_{{ $program->id }}');
            
            @if(request()->has('view_stage_id'))
                activePanel = 'recap';
            @elseif(request()->query('active_panel') === 'checking')
                activePanel = 'checking';
            @endif

            if (activePanel) {
                const btn = document.getElementById('btn-' + activePanel);
                if (btn) {
                    const panels = {
                        'gatekeeper': document.getElementById('panel-gatekeeper'),
                        'academic': document.getElementById('panel-academic'),
                        'broadcasting': document.getElementById('panel-broadcasting'),
                        'gtu': document.getElementById('panel-gtu'),
                        'recap': document.getElementById('panel-recap'),
                        'checking': document.getElementById('panel-checking')
                    };
                    const buttons = {
                        'gatekeeper': document.getElementById('btn-gatekeeper'),
                        'academic': document.getElementById('btn-academic'),
                        'broadcasting': document.getElementById('btn-broadcasting'),
                        'gtu': document.getElementById('btn-gtu'),
                        'recap': document.getElementById('btn-recap'),
                        'checking': document.getElementById('btn-checking')
                    };
                    
                    if (panels[activePanel]) {
                        panels[activePanel].classList.remove('hidden');
                    }
                    if (buttons[activePanel]) {
                        buttons[activePanel].classList.remove('border-slate-100', 'bg-slate-50/50');
                        buttons[activePanel].classList.add('border-emerald-500', 'bg-emerald-50/30', 'ring-1', 'ring-emerald-500');
                    }
                }
            }

            // Initialize Quill for Field Edit
            if (document.getElementById('edit-instruction-editor')) {
                window.quillEditInstruction = new Quill('#edit-instruction-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan deskripsi atau petunjuk pendaftaran di sini...'
                });

                // Sync edit field content on submit
                const editFieldForm = document.getElementById('edit-field-form');
                if (editFieldForm) {
                    editFieldForm.addEventListener('submit', function() {
                        // Check if Quill editor has actual text inside (Quill leaves <p><br></p> when empty)
                        const text = window.quillEditInstruction.getText().trim();
                        if (text === '') {
                            document.getElementById('hidden-edit-instruction').value = '';
                        } else {
                            document.getElementById('hidden-edit-instruction').value = window.quillEditInstruction.root.innerHTML;
                        }
                    });
                }
            }

            // Initialize Quill for New Field
            if (document.getElementById('new-instruction-editor')) {
                window.quillNewInstruction = new Quill('#new-instruction-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan deskripsi atau petunjuk pendaftaran di sini...'
                });

                // Sync new field content on submit
                const createFieldForm = document.getElementById('create-field-form');
                if (createFieldForm) {
                    createFieldForm.addEventListener('submit', function() {
                        const text = window.quillNewInstruction.getText().trim();
                        if (text === '') {
                            document.getElementById('hidden-new-field-instruction').value = '';
                        } else {
                            document.getElementById('hidden-new-field-instruction').value = window.quillNewInstruction.root.innerHTML;
                        }
                    });
                }
            }

            // Rich Toolbar Options for announcements, rules, and messages
            const richToolbarOptions = [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ];

            // Initialize Quill for Stage Create/Edit Form
            let quillPass, quillFail;
            if (document.getElementById('pass-announcement-editor')) {
                quillPass = new Quill('#pass-announcement-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan draf isi pengumuman kelolosan...',
                    modules: { toolbar: richToolbarOptions }
                });
            }
            if (document.getElementById('fail-announcement-editor')) {
                quillFail = new Quill('#fail-announcement-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan draf isi penolakan berkas...',
                    modules: { toolbar: richToolbarOptions }
                });
            }

            // Form stage sync
            const formStage = document.getElementById('form-stage');
            if (formStage) {
                formStage.addEventListener('submit', function() {
                    if (quillPass) {
                        document.getElementById('hidden-pass-announcement').value = quillPass.root.innerHTML;
                    }
                    if (quillFail) {
                        document.getElementById('hidden-fail-announcement').value = quillFail.root.innerHTML;
                    }
                });
            }

            // Initialize Quill for Broadcasting Engine
            let quillBroadcast;
            if (document.getElementById('broadcast-content-editor')) {
                quillBroadcast = new Quill('#broadcast-content-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan petunjuk operasional di sini secara jelas...',
                    modules: { toolbar: richToolbarOptions }
                });

                // Form broadcast sync
                const formBroadcast = document.querySelector('#panel-broadcasting form');
                if (formBroadcast) {
                    formBroadcast.addEventListener('submit', function() {
                        document.getElementById('hidden-broadcast-content').value = quillBroadcast.root.innerHTML;
                    });
                }
            }

            // Initialize Quill for Edit Broadcasting Engine
            if (document.getElementById('edit-broadcast-content-editor')) {
                window.quillEditBroadcast = new Quill('#edit-broadcast-content-editor', {
                    theme: 'snow',
                    placeholder: 'Tuliskan petunjuk operasional di sini secara jelas...',
                    modules: { toolbar: richToolbarOptions }
                });

                // Form edit broadcast sync
                const editAnnForm = document.getElementById('edit-ann-form');
                if (editAnnForm) {
                    editAnnForm.addEventListener('submit', function() {
                        document.getElementById('hidden-edit-broadcast-content').value = window.quillEditBroadcast.root.innerHTML;
                    });
                }
            }
        });
    </script>
</div>


@endsection
