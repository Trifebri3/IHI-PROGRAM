@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-800">Generator Sertifikat</h1>
                @if(!$program->piagamTemplate)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                        Template Belum Dipasang
                    </span>
                @endif
            </div>
            <p class="text-slate-600 mt-1">Generate PDF sertifikat untuk peserta program <strong>{{ $program->name }}</strong>.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('adminprogram.piagam.grades.index', $program->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 shadow-sm flex items-center gap-2 transition-colors text-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Isi Data (Penilaian)
            </a>
            
            <a href="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 shadow-sm flex items-center gap-2 transition-colors text-sm">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Daftar Terbit ({{ $counts['generated'] }})
            </a>

            <!-- Generate Semua Yang Berhak Button -->
            <form action="{{ route('adminprogram.piagam.generator.generate', $program->id) }}" method="POST" id="formGenerateAll" class="inline">
                @csrf
                <button type="submit" onclick="return confirmGenerateAll()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm flex items-center gap-2 transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Generate Semua Yang Berhak ({{ $counts['pending'] }})
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Alerts -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-semibold text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-semibold text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 p-4 bg-sky-50 text-sky-800 rounded-xl border border-sky-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-sky-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-semibold text-sm">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('adminprogram.piagam.generator.index', array_merge(['program' => $program->id], request()->except('status'))) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $currentStatus === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <span>Semua Peserta</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'all' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-700' }}">{{ $counts['total'] }}</span>
                </a>
                
                <a href="{{ route('adminprogram.piagam.generator.index', array_merge(['program' => $program->id, 'status' => 'pending'], request()->except('status'))) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Menunggu Generate</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'pending' ? 'bg-amber-700 text-amber-100' : 'bg-amber-200 text-amber-800' }}">{{ $counts['pending'] }}</span>
                </a>

                <a href="{{ route('adminprogram.piagam.generator.index', array_merge(['program' => $program->id, 'status' => 'generated'], request()->except('status'))) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $currentStatus === 'generated' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Sudah Dibuat</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'generated' ? 'bg-emerald-700 text-emerald-100' : 'bg-emerald-200 text-emerald-800' }}">{{ $counts['generated'] }}</span>
                </a>

                <a href="{{ route('adminprogram.piagam.generator.index', array_merge(['program' => $program->id, 'status' => 'failed'], request()->except('status'))) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $currentStatus === 'failed' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>Tidak Lolos (Tidak Berhak)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'failed' ? 'bg-rose-700 text-rose-100' : 'bg-rose-200 text-rose-800' }}">{{ $counts['failed'] }}</span>
                </a>
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="flex items-center gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           id="clientSearchInput" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama atau email..." 
                           class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    @if(request('search'))
                        <a href="{{ route('adminprogram.piagam.generator.index', array_merge(['program' => $program->id], request()->except('search'))) }}" class="text-slate-400 hover:text-slate-600 absolute right-2.5 top-2 text-xs" title="Reset Pencarian">✕</a>
                    @endif
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Sticky Bulk Action Toolbar (Appears when checkboxes are selected) -->
    <div id="bulkActionBar" class="bg-indigo-900 text-white rounded-xl shadow-lg p-3.5 mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3 transition-all duration-200" style="display: none;">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500 text-white font-bold text-xs" id="selectedCountBadge">0</span>
            <div>
                <span class="font-bold text-sm text-white"><span id="selectedCountText">0</span> Peserta Dipilih</span>
                <span class="text-indigo-200 text-xs ml-1">(Pilih tindakan untuk peserta yang dicentang)</span>
            </div>
            <div class="hidden sm:flex items-center gap-1.5 ml-2 border-l border-indigo-700 pl-3">
                <button type="button" onclick="selectAllEligible()" class="text-xs text-indigo-200 hover:text-white underline transition-colors">
                    Pilih Menunggu Saja
                </button>
                <span class="text-indigo-400 text-xs">•</span>
                <button type="button" onclick="clearAllSelections()" class="text-xs text-indigo-200 hover:text-white underline transition-colors">
                    Batalkan Pilihan
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Bulk Generate Button -->
            <button type="button" onclick="executeBulkAction('generate')" class="px-3.5 py-1.5 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Generate Piagam (<span class="selected-num">0</span>)
            </button>

            <!-- Bulk Mark Tidak Lolos Button -->
            <button type="button" onclick="executeBulkAction('fail')" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                Tandai Tidak Lolos (<span class="selected-num">0</span>)
            </button>

            <!-- Bulk Pass (Restore) Button -->
            <button type="button" onclick="executeBulkAction('pass')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pulihkan Lolos (<span class="selected-num">0</span>)
            </button>

            <!-- Bulk Email Button -->
            <button type="button" onclick="executeBulkAction('email')" class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-500 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Kirim Email (<span class="selected-num">0</span>)
            </button>
        </div>
    </div>

    <!-- Hidden Bulk Action Submission Form -->
    <form id="bulkActionForm" method="POST" style="display: none;">
        @csrf
        <div id="bulkInputsContainer"></div>
    </form>

    <!-- Participants Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="participantsTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                        <th class="p-3.5 w-10 text-center border-r border-slate-200">
                            <input type="checkbox" id="masterCheckbox" onclick="toggleSelectAll(this)" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="p-3.5 w-12 text-center border-r border-slate-200">No</th>
                        <th class="p-3.5 border-r border-slate-200">Peserta</th>
                        <th class="p-3.5 border-r border-slate-200 w-48 text-center">Status Sertifikat</th>
                        <th class="p-3.5 w-52 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($participants as $index => $participant)
                        @php
                            $hasCert = $participant->piagamCertificate && $participant->piagamCertificate->file_path;
                            $isFailed = $participant->status === 'failed';
                        @endphp
                        <tr class="participant-row hover:bg-slate-50/80 transition-colors {{ $isFailed ? 'bg-rose-50/30' : '' }}" 
                            data-id="{{ $participant->id }}" 
                            data-name="{{ strtolower($participant->user->name ?? '') }}" 
                            data-email="{{ strtolower($participant->user->email ?? '') }}"
                            data-status="{{ $isFailed ? 'failed' : ($hasCert ? 'generated' : 'pending') }}">
                            
                            <!-- Checkbox -->
                            <td class="p-3.5 border-r border-slate-100 text-center">
                                <input type="checkbox" 
                                       class="participant-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" 
                                       value="{{ $participant->id }}" 
                                       data-status="{{ $isFailed ? 'failed' : ($hasCert ? 'generated' : 'pending') }}"
                                       onchange="handleRowCheckboxChange()">
                            </td>

                            <!-- No -->
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500 font-mono">
                                {{ $index + 1 }}
                            </td>

                            <!-- Peserta -->
                            <td class="p-3.5 border-r border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0 uppercase border border-slate-200">
                                        {{ substr($participant->user->name ?? 'P', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-800 flex items-center gap-2">
                                            <span>{{ $participant->user->name ?? 'N/A' }}</span>
                                            @if($isFailed)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                    Tidak Lolos / Tidak Berhak
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $participant->user->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Sertifikat -->
                            <td class="p-3.5 border-r border-slate-100 text-center">
                                @if($isFailed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Tidak Lolos
                                    </span>
                                @elseif($hasCert)
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Sudah Dibuat
                                        </span>
                                        @if($participant->piagamCertificate->email_sent_at)
                                            <span class="text-[10px] text-emerald-600 flex items-center gap-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                Email terkirim
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Menunggu Generate
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="p-3.5 text-center">
                                @if($isFailed)
                                    <!-- Jika Tidak Lolos: Opsi Pulihkan atau Tetap Generate -->
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('adminprogram.piagam.generator.passOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors border border-emerald-200" title="Pulihkan status agar memenuhi syarat piagam">
                                                Pulihkan Lolos
                                            </button>
                                        </form>
                                        <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 text-xs font-semibold rounded-lg transition-colors" title="Batalkan Tidak Lolos & Generate Sertifikat">
                                                Tetap Generate
                                            </button>
                                        </form>
                                    </div>
                                @elseif($hasCert)
                                    <!-- Jika Sudah Dibuat -->
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ asset('storage/' . $participant->piagamCertificate->file_path) }}" target="_blank" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Lihat PDF
                                        </a>
                                        <form action="{{ route('adminprogram.piagam.generator.sendEmailOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('Kirim sertifikat ini ke email peserta {{ $participant->user->email }}?')">
                                            @csrf
                                            @if($participant->piagamCertificate->email_sent_at)
                                                <button type="submit" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Kirim Ulang Email">
                                                    Kirim Ulang
                                                </button>
                                            @else
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Kirim ke Email Peserta">
                                                    Kirim Email
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                @else
                                    <!-- Jika Menunggu Generate -->
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1" title="Generate Sertifikat Peserta Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                Generate
                                            </button>
                                        </form>
                                        <form action="{{ route('adminprogram.piagam.generator.failOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('Tandai peserta {{ $participant->user->name }} sebagai TIDAK LOLOS (Tidak berhak mendapat piagam)?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition-colors border border-rose-200" title="Tandai peserta ini tidak lolos">
                                                Tidak Lolos
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-500">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Tidak ada data peserta yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Client-side quick search filtering
    document.getElementById('clientSearchInput')?.addEventListener('keyup', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.participant-row');
        
        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            if (name.includes(query) || email.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        updateSelectionState();
    });

    // Checkbox and Bulk Actions Logic
    function getVisibleRowCheckboxes() {
        return Array.from(document.querySelectorAll('.participant-row'))
            .filter(row => row.style.display !== 'none')
            .map(row => row.querySelector('.participant-checkbox'))
            .filter(Boolean);
    }

    function toggleSelectAll(master) {
        const checkboxes = getVisibleRowCheckboxes();
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
        updateSelectionState();
    }

    function selectAllEligible() {
        const checkboxes = getVisibleRowCheckboxes();
        checkboxes.forEach(cb => {
            cb.checked = (cb.getAttribute('data-status') === 'pending');
        });
        const master = document.getElementById('masterCheckbox');
        if (master) master.checked = false;
        updateSelectionState();
    }

    function clearAllSelections() {
        const allCheckboxes = document.querySelectorAll('.participant-checkbox');
        allCheckboxes.forEach(cb => cb.checked = false);
        const master = document.getElementById('masterCheckbox');
        if (master) master.checked = false;
        updateSelectionState();
    }

    function handleRowCheckboxChange() {
        updateSelectionState();
    }

    function updateSelectionState() {
        const selected = document.querySelectorAll('.participant-checkbox:checked');
        const count = selected.length;
        const bulkBar = document.getElementById('bulkActionBar');
        
        if (count > 0) {
            bulkBar.style.display = 'flex';
            document.getElementById('selectedCountBadge').innerText = count;
            document.getElementById('selectedCountText').innerText = count;
            document.querySelectorAll('.selected-num').forEach(el => el.innerText = count);
        } else {
            bulkBar.style.display = 'none';
        }

        // Update master checkbox state
        const visibleCheckboxes = getVisibleRowCheckboxes();
        const master = document.getElementById('masterCheckbox');
        if (master && visibleCheckboxes.length > 0) {
            master.checked = visibleCheckboxes.every(cb => cb.checked);
        }
    }

    // Execute Bulk Action
    function executeBulkAction(actionType) {
        const selected = Array.from(document.querySelectorAll('.participant-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Silakan pilih minimal 1 peserta terlebih dahulu.');
            return;
        }

        const form = document.getElementById('bulkActionForm');
        const container = document.getElementById('bulkInputsContainer');
        container.innerHTML = '';

        // Add hidden inputs for selected participant_ids
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'participant_ids[]';
            input.value = id;
            container.appendChild(input);
        });

        if (actionType === 'generate') {
            if (!confirm(`Generate piagam sertifikat untuk ${selected.length} peserta terpilih?`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.generate', $program->id) }}";
        } else if (actionType === 'fail') {
            if (!confirm(`PERINGATAN: Apakah Anda yakin ingin menandai ${selected.length} peserta terpilih sebagai TIDAK LOLOS?\n(Peserta yang tidak lolos tidak akan mendapatkan piagam)`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.bulkFail', $program->id) }}";
        } else if (actionType === 'pass') {
            if (!confirm(`Pulihkan status ${selected.length} peserta terpilih menjadi LOLOS / BERHAK mendapatkan piagam?`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.bulkPass', $program->id) }}";
        } else if (actionType === 'email') {
            if (!confirm(`Kirim sertifikat ke email untuk ${selected.length} peserta terpilih yang sudah memiliki file piagam?`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.bulkSendEmail', $program->id) }}";
        }

        form.submit();
    }

    function confirmGenerateAll() {
        return confirm('Apakah Anda yakin ingin memproses semua peserta yang BERHAK (yang berstatus lolos & belum memiliki sertifikat)?');
    }
</script>
@endsection
