@extends('adminprogram.layouts.app')

@section('title', 'Database & Demografi Peserta')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8" x-data="{ openBulkGenerator: false }">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Database &amp; Demografi Peserta</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data dasar, verifikasi identitas, nomor induk, status alumni, dan lakukan aksi massal.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openBulkGenerator = !openBulkGenerator" 
                    class="inline-flex items-center text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-4 py-2.5 rounded-xl transition shadow-3xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Generator Nomor Induk Massal</span>
            </button>
        </div>
    </div>

    <!-- Bulk NI Generator Form (Expandable Panel) -->
    <div x-show="openBulkGenerator" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="bg-gradient-to-br from-emerald-50/50 to-white p-5 rounded-2xl border border-emerald-200 shadow-sm space-y-4"
         style="display: none;">
        <div class="border-b border-emerald-100 pb-2 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-emerald-950 uppercase tracking-wider flex items-center">
                ⚡ Pusat Generator Nomor Induk (NI) Semi-Otomatis
            </h3>
            <button @click="openBulkGenerator = false" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold">Tutup</button>
        </div>
        <p class="text-xs text-slate-500">Membantu pembuatan nomor induk berurutan secara otomatis untuk semua peserta berstatus **LULUS (Passed)** yang belum memiliki NI.</p>
        
        <form action="{{ route('adminprogram.participants.bulk-ni') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Pilih Program Kerja</label>
                <select name="program_id" required class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-800">
                    <option value="">-- Pilih Program --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Metode Urutan Nomor Induk</label>
                <select name="sort_by" required class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-800">
                    <option value="province" selected>Urut per Provinsi (Rekomendasi)</option>
                    <option value="name">Urut per Nama Peserta</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Prefix Kode NI (Opsional)</label>
                <input type="text" name="prefix" placeholder="Cth: IHI{{ date('Y') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 bg-white">
            </div>
            <div>
                <button type="submit" class="w-full p-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition uppercase tracking-wider">
                    Jana &amp; Generate Massal
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Card & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs">
        <form action="{{ route('adminprogram.participants.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Search bar -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Cari Peserta</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Email / NIP / ID..." class="w-full p-2.5 pl-9 border border-slate-100 rounded-xl text-xs bg-slate-50/50 text-slate-800 focus:bg-white transition outline-none">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Program filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Program</label>
                    <select name="program_id" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Program</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Batch filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Batch / Angkatan</label>
                    <select name="batch" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Batch</option>
                        @foreach($batches as $b)
                            <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lokasi filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Lokasi Kegiatan</label>
                    <select name="location" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $l)
                            <option value="{{ $l }}" {{ request('location') == $l ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Wilayah filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Wilayah / Daerah</label>
                    <select name="region" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Wilayah</option>
                        @foreach($regions as $r)
                            <option value="{{ $r }}" {{ request('region') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Keikutsertaan filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Status Peserta</label>
                    <select name="participant_status" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('participant_status') === 'active' ? 'selected' : '' }}>Aktif Mengikuti</option>
                        <option value="completed" {{ request('participant_status') === 'completed' ? 'selected' : '' }}>Selesai Program</option>
                        <option value="withdrawn" {{ request('participant_status') === 'withdrawn' ? 'selected' : '' }}>Mengundurkan Diri</option>
                    </select>
                </div>

                <!-- Status Kelulusan filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Status Kelulusan</label>
                    <select name="status" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="process" {{ request('status') === 'process' ? 'selected' : '' }}>Proses Seleksi</option>
                        <option value="passed" {{ request('status') === 'passed' ? 'selected' : '' }}>Lulus (Passed)</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gugur (Failed)</option>
                    </select>
                </div>

                <!-- Status KYC filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Status Verifikasi KYC</label>
                    <select name="verification_status" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified (Selesai)</option>
                        <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Status Alumni filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Akun Portal Alumni</label>
                    <select name="alumni_status" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('alumni_status') === 'active' ? 'selected' : '' }}>Teraktivasi (Alumni)</option>
                        <option value="inactive" {{ request('alumni_status') === 'inactive' ? 'selected' : '' }}>Belum Aktif (Peserta)</option>
                    </select>
                </div>

                <!-- Status Sertifikat filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Sertifikat / Piagam</label>
                    <select name="certificate_status" class="w-full p-2.5 border border-slate-150 rounded-xl text-xs bg-white text-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="issued" {{ request('certificate_status') === 'issued' ? 'selected' : '' }}>Sudah Diterbitkan</option>
                        <option value="none" {{ request('certificate_status') === 'none' ? 'selected' : '' }}>Belum Diterbitkan</option>
                    </select>
                </div>

                <!-- Rentang Tanggal pendaftaran -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Rentang Tanggal Registrasi</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full p-2.5 border border-slate-100 rounded-xl text-xs bg-slate-50/50 text-slate-800 focus:bg-white outline-none">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full p-2.5 border border-slate-100 rounded-xl text-xs bg-slate-50/50 text-slate-800 focus:bg-white outline-none">
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between border-t pt-4 gap-4">
                <!-- Additional filters: Block status -->
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-[10px] font-bold uppercase text-slate-400">Blokir Akses:</span>
                        <label class="inline-flex items-center text-xs text-slate-600 font-semibold cursor-pointer">
                            <input type="radio" name="blocked_status" value="" {{ !request('blocked_status') ? 'checked' : '' }} class="text-emerald-600 mr-1 focus:ring-0 border-slate-350"> Semua
                        </label>
                        <label class="inline-flex items-center text-xs text-slate-600 font-semibold cursor-pointer ml-3">
                            <input type="radio" name="blocked_status" value="active" {{ request('blocked_status') === 'active' ? 'checked' : '' }} class="text-emerald-600 mr-1 focus:ring-0 border-slate-350"> Aktif (Normal)
                        </label>
                        <label class="inline-flex items-center text-xs text-slate-600 font-semibold cursor-pointer ml-3">
                            <input type="radio" name="blocked_status" value="blocked" {{ request('blocked_status') === 'blocked' ? 'checked' : '' }} class="text-emerald-600 mr-1 focus:ring-0 border-slate-350"> Diblokir (Suspended)
                        </label>
                    </div>
                </div>

                <div class="flex space-x-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('adminprogram.participants.index') }}" class="px-4 py-2.5 border rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition uppercase">
                        Reset
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-3xs uppercase tracking-wider">
                        Saring Database
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Wrapper Form -->
    <form id="bulkActionForm" method="POST" action="{{ route('adminprogram.participants.bulk-action') }}">
        @csrf
        <input type="hidden" name="bulk_action" id="bulkActionInput">
        <input type="hidden" name="participant_status" id="bulkPartStatusInput">

        <!-- Selected Count Panel & Bulk Action Buttons -->
        <div id="bulkActionsPanel" class="hidden bg-slate-800 text-white p-4 rounded-2xl flex flex-wrap items-center justify-between gap-4 shadow-md transition-all duration-200">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-bold"><span id="selectedCount">0</span> Peserta Dipilih</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="submitBulk('verify_kyc')" class="px-3 py-1.5 text-[10px] font-bold text-slate-800 bg-white hover:bg-slate-50 border rounded-lg uppercase">
                    Verifikasi KYC
                </button>
                <button type="button" onclick="submitBulk('mark_passed')" class="px-3 py-1.5 text-[10px] font-bold text-slate-800 bg-white hover:bg-slate-50 border rounded-lg uppercase">
                    Tandai Lulus
                </button>
                <button type="button" onclick="submitBulk('mark_failed')" class="px-3 py-1.5 text-[10px] font-bold text-slate-800 bg-white hover:bg-slate-50 border rounded-lg uppercase">
                    Tandai Gugur
                </button>
                <button type="button" onclick="submitBulk('activate_alumni')" class="px-3 py-1.5 text-[10px] font-bold text-slate-800 bg-emerald-400 hover:bg-emerald-350 border border-emerald-500 rounded-lg uppercase">
                    Aktifkan Alumni
                </button>
                <button type="button" onclick="submitBulk('deactivate_access')" class="px-3 py-1.5 text-[10px] font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-lg uppercase" onclick="return confirm('Apakah Anda yakin ingin memblokir akses pengguna terpilih?')">
                    Blokir Akses
                </button>
                
                <!-- Status selection -->
                <div class="flex items-center gap-1.5 bg-slate-700/80 px-2 py-1 rounded-lg border border-slate-600">
                    <select id="bulkStatusDropdown" class="bg-transparent border-none text-[10px] text-white font-bold uppercase outline-none focus:ring-0 py-0.5 cursor-pointer">
                        <option value="active" class="bg-slate-800">Set Keikutsertaan: Aktif</option>
                        <option value="completed" class="bg-slate-800">Set Keikutsertaan: Selesai</option>
                        <option value="withdrawn" class="bg-slate-800">Set Keikutsertaan: Mundur</option>
                    </select>
                    <button type="button" onclick="submitBulkStatus()" class="px-2 py-1 text-[9px] font-bold text-slate-800 bg-white hover:bg-slate-50 rounded uppercase">
                        Set
                    </button>
                </div>

                <button type="button" onclick="submitBulk('export_csv')" class="px-3 py-1.5 text-[10px] font-bold text-slate-300 hover:text-white border border-slate-600 rounded-lg uppercase">
                    Export CSV
                </button>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-4">
            <!-- Table Selection Controls -->
            <div class="p-4 bg-slate-50/50 border-b flex flex-wrap gap-2 items-center justify-between text-xs">
                <div class="flex gap-2">
                    <button type="button" onclick="selectN(5)" class="px-2.5 py-1 text-[10px] font-bold bg-white text-slate-600 hover:bg-slate-100 border rounded-lg transition uppercase">Pilih 5</button>
                    <button type="button" onclick="selectN(20)" class="px-2.5 py-1 text-[10px] font-bold bg-white text-slate-600 hover:bg-slate-100 border rounded-lg transition uppercase">Pilih 20</button>
                    <button type="button" onclick="selectFiltered(true)" class="px-2.5 py-1 text-[10px] font-bold bg-white text-slate-600 hover:bg-slate-100 border rounded-lg transition uppercase">Pilih Semua Halaman</button>
                    <button type="button" onclick="selectFiltered(false)" class="px-2.5 py-1 text-[10px] font-bold bg-white text-slate-600 hover:bg-slate-100 border rounded-lg transition uppercase">Batal Semua</button>
                </div>
                <span class="text-slate-400 font-medium">Tampil {{ $registrations->firstItem() ?? 0 }}-{{ $registrations->lastItem() ?? 0 }} dari {{ $registrations->total() }} Peserta</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-4 py-4 w-12 text-center">
                                <input type="checkbox" id="selectAllHeaderCheckbox" onchange="toggleAllRowCheckboxes(this)" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200">
                            </th>
                            <th class="px-6 py-4">Foto &amp; Akun</th>
                            <th class="px-6 py-4">Program &amp; Batch</th>
                            <th class="px-6 py-4">
                                @php
                                    $nextOrder = request('sort') === 'province' && request('order') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'province', 'order' => $nextOrder]) }}" class="inline-flex items-center hover:text-slate-700 transition">
                                    <span>Provinsi &amp; Wilayah</span>
                                    <svg class="w-3.5 h-3.5 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4">
                                @php
                                    $nextOrderNI = request('sort') === 'final_id_number' && request('order') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'final_id_number', 'order' => $nextOrderNI]) }}" class="inline-flex items-center hover:text-slate-700 transition">
                                    <span>Nomor Induk (NI)</span>
                                    <svg class="w-3.5 h-3.5 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4">Seleksi &amp; Peserta</th>
                            <th class="px-6 py-4">Aktivasi Alumni</th>
                            <th class="px-6 py-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                        @forelse($registrations as $reg)
                            @php
                                $alumniProgram = $reg->user->alumniPrograms->firstWhere('program_id', $reg->program_id);
                                $isAlumniActive = $alumniProgram && $reg->user->alumniPrograms()->wherePivot('verification_status', 'approved')->exists();
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <!-- Row Checkbox -->
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $reg->id }}" onchange="updateSelection()" class="row-selector rounded text-emerald-600 focus:ring-emerald-500 border-slate-200">
                                </td>

                                <!-- User Account Details -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        @if($reg->user->profile && $reg->user->profile->profile_photo_path)
                                            <img class="w-9 h-9 rounded-xl object-cover border" src="{{ asset('storage/' . $reg->user->profile->profile_photo_path) }}" alt="Foto">
                                        @elseif($reg->user->avatar)
                                            <img class="w-9 h-9 rounded-xl object-cover border" src="{{ $reg->user->avatar }}" alt="Avatar">
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border flex items-center justify-center text-emerald-800 font-extrabold text-xs">
                                                {{ strtoupper(substr($reg->user->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm leading-tight hover:underline">
                                                <a href="{{ route('adminprogram.participants.show', $reg->id) }}">{{ $reg->user->name }}</a>
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $reg->user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Program Name & Batch -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 text-xs truncate max-w-[180px]" title="{{ $reg->program->name }}">
                                            {{ $reg->program->name }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Batch: {{ $reg->batch ?? '—' }} | Lokasi: {{ $reg->location ?? '—' }}</span>
                                    </div>
                                </td>

                                <!-- Province / Region -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-slate-700 font-semibold">{{ $reg->user->address->provinsi ?? 'Provinsi: —' }}</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Daerah: {{ $reg->region ?? ($reg->user->address->provinsi ?? '—') }}</span>
                                    </div>
                                </td>

                                <!-- Nomor Induk (NI) + Inline Quick Update Form -->
                                <td class="px-6 py-4" x-data="{ editing: false, tempNi: '{{ $reg->final_id_number }}' }">
                                    <div x-show="!editing" class="flex items-center space-x-2">
                                        @if($reg->final_id_number)
                                            <span class="font-mono font-bold text-slate-800 tracking-wider text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $reg->final_id_number }}</span>
                                        @else
                                            <span class="text-xs text-slate-400 italic font-normal">Belum di-generate</span>
                                        @endif
                                        <button type="button" @click="editing = true" class="text-slate-400 hover:text-emerald-700 p-0.5" title="Cepat Edit Nomor Induk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="editing" class="flex items-center space-x-1.5" style="display: none;">
                                        <input type="text" x-model="tempNi" placeholder="Nomor Induk" class="w-28 p-1.5 border border-slate-300 rounded font-mono font-bold text-[11px] bg-white text-slate-700 outline-none">
                                        <button type="button" @click="
                                            fetch(`/adminprogram/participants/{{ $reg->id }}/ni`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ final_id_number: tempNi })
                                            }).then(() => { editing = false; window.location.reload(); });
                                        " class="p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded" title="Simpan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="editing = false; tempNi = '{{ $reg->final_id_number }}'" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded" title="Batal">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Status Seleksi & Keikutsertaan -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <!-- Kelulusan -->
                                        @if($reg->status === 'passed')
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Passed (Lulus)</span>
                                        @elseif($reg->status === 'failed')
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-100">Failed (Gugur)</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-100">Seleksi</span>
                                        @endif

                                        <!-- Keikutsertaan -->
                                        @if($reg->participant_status === 'completed')
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-100 text-slate-700">Selesai Program</span>
                                        @elseif($reg->participant_status === 'withdrawn')
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-200 text-slate-500">Mundur</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-50 text-emerald-800">Aktif Mengikuti</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Alumni Status -->
                                <td class="px-6 py-4">
                                    @if($isAlumniActive)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-full uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                            Alumni Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-extrabold text-slate-500 bg-slate-50 border border-slate-200 rounded-full uppercase">
                                            Belum Aktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('adminprogram.participants.show', $reg->id) }}" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-100 transition shadow-3xs">
                                            <span>Buka</span>
                                        </a>
                                        
                                        <form action="{{ route('adminprogram.participants.toggle-block', $reg->user_id) }}" method="POST" class="inline">
                                            @csrf
                                            @if($reg->user->is_blocked)
                                                <button type="submit" class="inline-flex items-center text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl border border-slate-200 transition shadow-3xs">
                                                    Unblock
                                                </button>
                                            @else
                                                <button type="submit" class="inline-flex items-center text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl border border-rose-100 transition shadow-3xs" onclick="return confirm('Apakah Anda yakin ingin memblokir akses akun peserta {{ $reg->user->name }}?')">
                                                    Block
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-slate-400 italic">
                                    Tidak ada data peserta yang cocok dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            @if($registrations->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    function toggleAllRowCheckboxes(headerCheckbox) {
        const rowSelectors = document.querySelectorAll('.row-selector');
        rowSelectors.forEach(cb => {
            cb.checked = headerCheckbox.checked;
        });
        updateSelection();
    }

    function selectN(n) {
        selectFiltered(false); // reset first
        const rowSelectors = document.querySelectorAll('.row-selector');
        for(let i=0; i<Math.min(n, rowSelectors.length); i++) {
            rowSelectors[i].checked = true;
        }
        updateSelection();
    }

    function selectFiltered(status) {
        const rowSelectors = document.querySelectorAll('.row-selector');
        rowSelectors.forEach(cb => cb.checked = status);
        document.getElementById('selectAllHeaderCheckbox').checked = status;
        updateSelection();
    }

    function updateSelection() {
        const checked = document.querySelectorAll('.row-selector:checked');
        const count = checked.length;
        document.getElementById('selectedCount').innerText = count;
        
        const panel = document.getElementById('bulkActionsPanel');
        if (count > 0) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    }

    function submitBulk(action) {
        if (action === 'export_csv') {
            document.getElementById('bulkActionInput').value = action;
            document.getElementById('bulkActionForm').submit();
            return;
        }
        
        let confirmMsg = `Apakah Anda yakin ingin menjalankan aksi massal untuk peserta terpilih?`;
        if (action === 'deactivate_access') {
            confirmMsg = `Apakah Anda yakin ingin memblokir akses ke seluruh akun peserta terpilih?`;
        } else if (action === 'activate_alumni') {
            confirmMsg = `Aktivasi Alumni akan mendaftarkan nomor alumni (NIA) permanen dan menerbitkan piagam. Lanjutkan?`;
        }

        if (confirm(confirmMsg)) {
            document.getElementById('bulkActionInput').value = action;
            document.getElementById('bulkActionForm').submit();
        }
    }

    function submitBulkStatus() {
        const partStatus = document.getElementById('bulkStatusDropdown').value;
        if (confirm(`Apakah Anda yakin ingin memperbarui status keikutsertaan peserta terpilih menjadi ${partStatus.toUpperCase()}?`)) {
            document.getElementById('bulkActionInput').value = 'update_status';
            document.getElementById('bulkPartStatusInput').value = partStatus;
            document.getElementById('bulkActionForm').submit();
        }
    }
</script>
@endsection
