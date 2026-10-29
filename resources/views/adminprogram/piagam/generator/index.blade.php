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
            <a href="{{ route('adminprogram.piagam.grades.index', $program->id) }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 shadow-sm flex items-center gap-1.5 transition-colors text-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Isi Data (Penilaian)
            </a>
            
            <a href="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 shadow-sm flex items-center gap-1.5 transition-colors text-sm">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Daftar Terbit ({{ $counts['generated'] }})
            </a>

            <!-- Tombol Tandai dari List Nama/Email -->
            <button type="button" onclick="openMatchListModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors text-sm" title="Tempel daftar nama atau email untuk langsung menandai/memisahkan peserta">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Tempel List Nama/Email
            </button>

            <!-- Generate Semua Yang Berhak Button -->
            <form action="{{ route('adminprogram.piagam.generator.generate', $program->id) }}" method="POST" id="formGenerateAll" class="inline">
                @csrf
                <button type="submit" onclick="return confirmGenerateAll()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors text-sm">
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

    <!-- Indicator Filter List Aktif (jika ada) -->
    <div id="listFilterBanner" class="mb-4 p-3.5 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center justify-between text-indigo-900" style="display: none;">
        <div class="flex items-center gap-2 text-xs font-medium">
            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            <span>Tabel sedang difilter menampilkan <strong id="listFilterCount">0</strong> peserta hasil pencocokan list.</span>
        </div>
        <button type="button" onclick="resetListFilter()" class="px-2.5 py-1 bg-white hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors">
            Tampilkan Semua Peserta
        </button>
    </div>

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

            <!-- Bulk Regenerate Button -->
            <button type="button" onclick="executeBulkAction('regenerate')" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors" title="Terbitkan ulang PDF untuk peserta terpilih">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Terbitkan Ulang (<span class="selected-num">0</span>)
            </button>

            <!-- Bulk Cancel Piagam Button -->
            <button type="button" onclick="executeBulkAction('cancel')" class="px-3.5 py-1.5 bg-rose-700 hover:bg-rose-600 text-white font-semibold rounded-lg text-xs shadow-sm flex items-center gap-1.5 transition-colors" title="Batalkan & hapus file piagam yang sudah dibuat">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Batalkan Piagam (<span class="selected-num">0</span>)
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
                        <th class="p-3.5 min-w-[260px] text-center">Aksi</th>
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
                                       data-name="{{ strtolower($participant->user->name ?? '') }}"
                                       data-email="{{ strtolower($participant->user->email ?? '') }}"
                                       data-status="{{ $isFailed ? 'failed' : ($hasCert ? 'generated' : 'pending') }}"
                                       onchange="handleRowCheckboxChange()">
                            </td>

                            <!-- No -->
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500 font-mono row-index">
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
                                    <!-- Jika Tidak Lolos -->
                                    <div class="flex items-center justify-center flex-wrap gap-1.5">
                                        <form action="{{ route('adminprogram.piagam.generator.passOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors border border-emerald-200" title="Pulihkan status agar memenuhi syarat piagam">
                                                Pulihkan Lolos
                                            </button>
                                        </form>
                                        <button type="button" onclick="openEditPiagamModal({{ $participant->id }})" class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200" title="Edit data peserta">
                                            Edit Data
                                        </button>
                                        <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors border border-indigo-200" title="Batalkan Tidak Lolos & Generate Sertifikat">
                                                Tetap Generate
                                            </button>
                                        </form>
                                    </div>
                                @elseif($hasCert)
                                    <!-- Jika Sudah Dibuat -->
                                    <div class="flex items-center justify-center flex-wrap gap-1.5">
                                        <!-- Lihat PDF -->
                                        <a href="{{ asset('storage/' . $participant->piagamCertificate->file_path) }}" target="_blank" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Lihat Dokumen PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            PDF
                                        </a>

                                        <!-- Edit Data -->
                                        <button type="button" onclick="openEditPiagamModal({{ $participant->id }})" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors border border-amber-200 flex items-center gap-1" title="Edit nomor, nama, skor atau predikat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </button>

                                        <!-- Terbitkan Ulang (Regenerate) -->
                                        <form action="{{ route('adminprogram.piagam.generator.regenerateOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('Terbitkan ulang (regenerate) sertifikat untuk {{ $participant->user->name }}?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200 flex items-center gap-1" title="Terbitkan Ulang PDF">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                Terbitkan Ulang
                                            </button>
                                        </form>

                                        <!-- Batalkan Piagam -->
                                        <form action="{{ route('adminprogram.piagam.generator.cancelOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Batalkan dan hapus sertifikat untuk {{ $participant->user->name }}? Status akan kembali ke Menunggu Generate.')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition-colors border border-rose-200 flex items-center gap-1" title="Batalkan & hapus file sertifikat">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Batal
                                            </button>
                                        </form>

                                        <!-- Kirim Email -->
                                        <form action="{{ route('adminprogram.piagam.generator.sendEmailOne', [$program->id, $participant->id]) }}" method="POST" class="inline" onsubmit="return confirm('Kirim sertifikat ini ke email peserta {{ $participant->user->email }}?')">
                                            @csrf
                                            @if($participant->piagamCertificate->email_sent_at)
                                                <button type="submit" class="px-2 py-1 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Kirim Ulang Email">
                                                    Email Ulang
                                                </button>
                                            @else
                                                <button type="submit" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Kirim ke Email Peserta">
                                                    Kirim Email
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                @else
                                    <!-- Jika Menunggu Generate -->
                                    <div class="flex items-center justify-center flex-wrap gap-1.5">
                                        <form action="{{ route('adminprogram.piagam.generator.generateOne', [$program->id, $participant->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1" title="Generate Sertifikat Peserta Ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                Generate
                                            </button>
                                        </form>
                                        <button type="button" onclick="openEditPiagamModal({{ $participant->id }})" class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200" title="Atur nomor, nama, skor atau predikat sebelum generate">
                                            Edit Data
                                        </button>
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

<!-- MODAL: TEMPEL DAFTAR NAMA / EMAIL (PISAHKAN & TANDAI) -->
<div id="modalMatchList" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeMatchListModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tandai / Pisahkan Peserta dari Daftar</h3>
                        <p class="text-xs text-slate-500">Tempel nama atau email peserta (langsung dari Excel, Google Sheets, atau chat)</p>
                    </div>
                </div>
                <button type="button" onclick="closeMatchListModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('adminprogram.piagam.generator.matchList', $program->id) }}" method="POST" id="matchListForm">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Daftar Nama atau Email Peserta (1 baris per orang):
                        </label>
                        <textarea id="rawListInput" 
                                  name="raw_list" 
                                  rows="7" 
                                  placeholder="Contoh:&#10;Muhammad Ridho Fauzan&#10;07251058@student.itk.ac.id&#10;Muthia Arrusyda&#10;1001arrusyda@gmail.com&#10;..." 
                                  class="w-full p-3.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all resize-y"
                                  oninput="analyzePastedList()"></textarea>
                        <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sistem otomatis mengabaikan nomor baris, titik, atau bullet dari copy-paste.
                        </p>
                    </div>

                    <!-- Hasil Analisis Realtime -->
                    <div id="matchAnalysisBox" class="p-3 bg-slate-100 rounded-xl border border-slate-200 text-xs" style="display: none;">
                        <div class="font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Hasil Pencocokan Instan:</span>
                            <span id="matchAnalysisSummary" class="text-emerald-700 font-semibold"></span>
                        </div>
                        <div class="text-slate-600 space-y-1">
                            <div>✅ <strong id="matchedCountText">0</strong> peserta berhasil ditemukan di tabel program ini.</div>
                            <div id="unmatchedNotice" class="text-rose-600" style="display: none;">⚠️ <strong id="unmatchedCountText">0</strong> data tidak ditemukan di tabel.</div>
                        </div>
                    </div>

                    <!-- Tindakan Cepat Instan di Halaman (Tanpa Simpan) -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <span class="block text-xs font-bold text-slate-700 mb-2">Tindakan Cepat di Tabel Halaman Ini:</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="applyCheckFromList()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Centang Peserta di Tabel
                            </button>
                            <button type="button" onclick="applyFilterFromList()" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 shadow-sm flex items-center gap-1.5 transition-colors">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Pisahkan & Tampilkan Hanya List Ini
                            </button>
                        </div>
                    </div>

                    <!-- Tindakan Simpan Status ke Database -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Atau Terapkan & Simpan Status ke Database:
                        </label>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-start gap-2.5 p-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="list_action" value="mark_passed_others_failed" checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-slate-800">Tandai Yang di List Sebagai LOLOS, Lainnya TIDAK LOLOS</span>
                                    <p class="text-slate-500 text-[11px] mt-0.5">Sangat direkomendasikan jika dari 500+ peserta hanya puluhan yang lolos. Semua di luar list otomatis Tidak Lolos.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-2.5 p-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="list_action" value="mark_passed_only" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="font-bold text-slate-800">Tandai Yang di List Sebagai LOLOS Saja</span>
                                    <p class="text-slate-500 text-[11px] mt-0.5">Hanya mengubah status peserta yang di list menjadi lolos, peserta lain tidak diubah.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-2.5 p-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="list_action" value="mark_failed" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                <div>
                                    <span class="font-bold text-slate-800">Tandai Yang di List Sebagai TIDAK LOLOS</span>
                                    <p class="text-slate-500 text-[11px] mt-0.5">Gunakan jika daftar yang Anda tempel adalah daftar peserta yang gagal / gugur.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-2.5 p-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" name="list_action" value="generate_matched" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="font-bold text-slate-800">Langsung Generate Piagam untuk Peserta di List</span>
                                    <p class="text-slate-500 text-[11px] mt-0.5">Membuat sertifikat PDF langsung untuk semua peserta yang cocok di list ini.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <button type="button" onclick="closeMatchListModal()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                        Tutup
                    </button>
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerapkan perubahan status ke database untuk peserta ini?')" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Perubahan ke Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDIT DATA PIAGAM SERTIFIKAT -->
<div id="modalEditPiagam" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditPiagamModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Data Piagam Sertifikat</h3>
                        <p class="text-xs text-slate-500">Perbarui nama, nomor sertifikat, nilai, atau predikat</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditPiagamModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    ✕
                </button>
            </div>

            <!-- Loading Spinner -->
            <div id="editModalLoading" class="p-10 text-center text-slate-500">
                <div class="inline-block w-8 h-8 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-xs font-semibold">Memuat data sertifikat...</p>
            </div>

            <!-- Form -->
            <form id="editPiagamForm" method="POST" style="display: none;">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Nama Peserta -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Peserta (Dicetak pada Piagam):
                        </label>
                        <input type="text" id="editName" name="name" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold text-slate-800">
                        <p class="text-[11px] text-slate-500 mt-1">Perubahan nama akan otomatis tersimpan ke data akun peserta.</p>
                    </div>

                    <!-- Email Peserta (Read-only) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                            Email Peserta:
                        </label>
                        <input type="text" id="editEmail" readonly class="w-full px-3.5 py-2 text-xs bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor Kredensial / Sertifikat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Piagam:
                            </label>
                            <input type="text" id="editCertificateNumber" name="certificate_number" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-mono">
                        </div>

                        <!-- Tanggal Terbit -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Terbit:
                            </label>
                            <input type="date" id="editPublishedAt" name="published_at" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Predikat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Predikat / Keterangan:
                            </label>
                            <input type="text" id="editPredikat" name="predikat" list="predikatSuggestions" placeholder="Contoh: Sangat Memuaskan" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                            <datalist id="predikatSuggestions">
                                <option value="Sangat Memuaskan"></option>
                                <option value="Memuaskan"></option>
                                <option value="Baik"></option>
                                <option value="Cukup"></option>
                                <option value="Lulus"></option>
                                <option value="Peserta Terbaik"></option>
                            </datalist>
                        </div>

                        <!-- Nilai Akhir / Skor -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nilai Akhir / Skor Total:
                            </label>
                            <input type="text" id="editFinalGrade" name="final_grade" placeholder="Contoh: 88.5 atau A" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <!-- Dynamic Custom Grades container if any -->
                    <div id="dynamicCustomGradesContainer" class="space-y-2" style="display: none;">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Komponen Nilai Tambahan:
                        </label>
                        <div id="dynamicCustomGradesInputs" class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200"></div>
                    </div>

                    <!-- Regenerate Now Checkbox -->
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" id="editRegenerateNow" name="regenerate_now" value="1" checked class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-xs text-amber-900">Langsung Terbitkan Ulang (Regenerate PDF)</span>
                                <p class="text-[11px] text-amber-700 mt-0.5">Otomatis memperbarui dokumen PDF sertifikat dengan perubahan nama/nomor/nilai di atas.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <button type="button" onclick="closeEditPiagamModal()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
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

    // Modal Tandai dari List
    function openMatchListModal() {
        document.getElementById('modalMatchList').style.display = 'block';
        document.getElementById('rawListInput').focus();
    }

    function closeMatchListModal() {
        document.getElementById('modalMatchList').style.display = 'none';
    }

    // Helper: Parse pasted text to array of clean lines
    function getPastedItems() {
        const text = document.getElementById('rawListInput').value || '';
        const lines = text.split(/[\r\n,;]+/);
        const items = [];
        lines.forEach(l => {
            // Strip leading bullet or numbers like "1. ", "2) ", "- "
            let clean = l.replace(/^\s*\d+[\.\)\-]\s*/, '').trim().toLowerCase();
            if (clean.length > 0) {
                items.push(clean);
            }
        });
        return Array.from(new Set(items));
    }

    // Live analysis of pasted list vs table rows
    function analyzePastedList() {
        const items = getPastedItems();
        const box = document.getElementById('matchAnalysisBox');
        if (items.length === 0) {
            box.style.display = 'none';
            return;
        }

        const rows = Array.from(document.querySelectorAll('.participant-row'));
        let matchedRows = [];
        let unmatchedItems = [];

        items.forEach(item => {
            const isEmail = item.includes('@');
            const foundRow = rows.find(r => {
                if (isEmail) {
                    return (r.getAttribute('data-email') || '') === item;
                } else {
                    const rowName = r.getAttribute('data-name') || '';
                    return rowName === item || rowName.includes(item) || item.includes(rowName);
                }
            });

            if (foundRow) {
                if (!matchedRows.includes(foundRow)) matchedRows.push(foundRow);
            } else {
                unmatchedItems.push(item);
            }
        });

        box.style.display = 'block';
        document.getElementById('matchedCountText').innerText = matchedRows.length;
        document.getElementById('matchAnalysisSummary').innerText = `${matchedRows.length} dari ${items.length} cocok`;

        const unmatchedEl = document.getElementById('unmatchedNotice');
        if (unmatchedItems.length > 0) {
            unmatchedEl.style.display = 'block';
            document.getElementById('unmatchedCountText').innerText = unmatchedItems.length;
        } else {
            unmatchedEl.style.display = 'none';
        }
    }

    // Centang otomatis baris peserta yang cocok dengan list
    function applyCheckFromList() {
        const items = getPastedItems();
        if (items.length === 0) {
            alert('Silakan tempel daftar nama atau email terlebih dahulu.');
            return;
        }

        const rows = Array.from(document.querySelectorAll('.participant-row'));
        let checkedCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const cb = row.querySelector('.participant-checkbox');

            const isMatched = items.some(item => {
                if (item.includes('@')) {
                    return email === item;
                } else {
                    return name === item || name.includes(item) || item.includes(name);
                }
            });

            if (isMatched && cb) {
                cb.checked = true;
                checkedCount++;
            }
        });

        updateSelectionState();
        closeMatchListModal();

        alert(`Berhasil mencentang ${checkedCount} peserta di tabel yang cocok dengan daftar Anda! Anda sekarang dapat memilih tindakan pada bilah biru.`);
    }

    // Filter tabel untuk menampilkan hanya peserta yang cocok dengan list
    function applyFilterFromList() {
        const items = getPastedItems();
        if (items.length === 0) {
            alert('Silakan tempel daftar nama atau email terlebih dahulu.');
            return;
        }

        const rows = Array.from(document.querySelectorAll('.participant-row'));
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';

            const isMatched = items.some(item => {
                if (item.includes('@')) {
                    return email === item;
                } else {
                    return name === item || name.includes(item) || item.includes(name);
                }
            });

            if (isMatched) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('listFilterBanner').style.display = 'flex';
        document.getElementById('listFilterCount').innerText = visibleCount;
        updateSelectionState();
        closeMatchListModal();
    }

    // Reset filter list
    function resetListFilter() {
        document.querySelectorAll('.participant-row').forEach(row => row.style.display = '');
        document.getElementById('listFilterBanner').style.display = 'none';
        updateSelectionState();
    }

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
        } else if (actionType === 'regenerate') {
            if (!confirm(`Terbitkan ulang (regenerate) dokumen PDF piagam untuk ${selected.length} peserta terpilih?`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.bulkRegenerate', $program->id) }}";
        } else if (actionType === 'cancel') {
            if (!confirm(`PERINGATAN: Apakah Anda yakin ingin MEMBATALKAN & MENGHAPUS file piagam untuk ${selected.length} peserta terpilih?\nStatus mereka akan kembali menjadi 'Menunggu Generate'.`)) {
                return;
            }
            form.action = "{{ route('adminprogram.piagam.generator.bulkCancel', $program->id) }}";
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

    // Modal Edit Data Piagam
    function openEditPiagamModal(participantId) {
        const modal = document.getElementById('modalEditPiagam');
        const loading = document.getElementById('editModalLoading');
        const form = document.getElementById('editPiagamForm');

        modal.style.display = 'block';
        loading.style.display = 'block';
        form.style.display = 'none';

        const dataUrl = "{{ url('adminprogram/programs/' . $program->id . '/piagam/generator/certificate') }}/" + participantId + "/data";
        const updateUrl = "{{ url('adminprogram/programs/' . $program->id . '/piagam/generator/certificate') }}/" + participantId + "/update";

        form.action = updateUrl;

        fetch(dataUrl)
            .then(res => {
                if (!res.ok) throw new Error('Status server ' + res.status);
                return res.json();
            })
            .then(data => {
                document.getElementById('editName').value = data.name || '';
                document.getElementById('editEmail').value = data.email || '';
                document.getElementById('editCertificateNumber').value = data.certificate_number || '';
                document.getElementById('editPublishedAt').value = data.published_at || '';
                document.getElementById('editPredikat').value = data.predikat || '';
                document.getElementById('editFinalGrade').value = data.final_grade || '';

                const customContainer = document.getElementById('dynamicCustomGradesContainer');
                const customInputs = document.getElementById('dynamicCustomGradesInputs');
                customInputs.innerHTML = '';

                if (data.grades && data.grades.length > 0) {
                    customContainer.style.display = 'block';
                    data.grades.forEach(g => {
                        const col = document.createElement('div');
                        col.innerHTML = `
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">${g.variable_name}:</label>
                            <input type="text" name="custom_grades[${g.variable_name}]" value="${g.value || ''}" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500">
                        `;
                        customInputs.appendChild(col);
                    });
                } else {
                    customContainer.style.display = 'none';
                }

                loading.style.display = 'none';
                form.style.display = 'block';
            })
            .catch(err => {
                alert('Gagal mengambil data sertifikat: ' + err.message);
                closeEditPiagamModal();
            });
    }

    function closeEditPiagamModal() {
        document.getElementById('modalEditPiagam').style.display = 'none';
    }
</script>
@endsection
