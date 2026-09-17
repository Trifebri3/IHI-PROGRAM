@extends('superadmin.layouts.app')

@section('title', $selectedProgram ? 'Partisipan: ' . $selectedProgram->name : 'Partisipan Program')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8" x-data="participantHub()">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-rose-500 text-white rounded-xl text-sm">!</span>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-sm font-bold">&times;</button>
        </div>
    @endif

    {{-- KONDISI 1: JIKA BELUM MEMILIH PROGRAM (PROGRAM HUB) --}}
    @if(!$selectedProgram)
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-100 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-full mb-2">
                         INDUK PROGRAM DAHULU
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Partisipan Program</h1>
                    <p class="text-slate-500 text-sm mt-1 max-w-2xl">
                        Pilih salah satu program di bawah ini untuk menginspeksi akun peserta, status password, kontrol status pendaftaran, update nomor induk, serta sinkronisasi via Excel.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">
                        Total {{ $allPrograms->count() }} Program Tersedia
                    </span>
                </div>
            </div>

            {{-- Grid Kartu Program --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($allPrograms as $prog)
                    <div class="group bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div>
                            {{-- Header Banner / Gradient --}}
                            <div class="h-28 bg-gradient-to-br from-emerald-800 via-teal-700 to-emerald-600 p-4 relative flex flex-col justify-between overflow-hidden">
                                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    @if($prog->is_open)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-emerald-400 text-emerald-950 shadow-sm">
                                            ● Pendaftaran Buka
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-slate-800/80 text-white shadow-sm">
                                            Ditutup
                                        </span>
                                    @endif

                                    @if($prog->quota)
                                        <span class="text-[11px] font-bold text-emerald-100 bg-black/20 px-2 py-0.5 rounded-md">
                                            Kuota: {{ number_format($prog->quota) }}
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-white font-black text-lg tracking-tight line-clamp-1 relative z-10" title="{{ $prog->name }}">
                                    {{ $prog->name }}
                                </h3>
                            </div>

                            {{-- Hanya Menampilkan Peserta Yang Sudah Lolos --}}
                            <div class="p-5">
                                <div class="bg-gradient-to-br from-emerald-50 to-teal-50/70 p-4 rounded-2xl border border-emerald-200/80 text-center space-y-1">
                                    <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider block">Peserta Lolos Program</span>
                                    <div class="flex items-baseline justify-center gap-1.5">
                                        <span class="text-3xl font-black text-emerald-900 tracking-tight">{{ number_format($prog->passed_count) }}</span>
                                        <span class="text-xs font-bold text-emerald-700">Orang Lolos</span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 rounded-full mt-1 border border-emerald-300">
                                        
                                        Status: PASSED / Diterima
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Action Masuk --}}
                        <div class="p-5 pt-0">
                            <a href="{{ route('superadmin.program-participants.index', ['program_id' => $prog->id]) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-sm group-hover:shadow-emerald-500/20">
                                <span>Lihat &amp; Kelola Partisipan</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center bg-slate-50 rounded-2xl border border-slate-200">
                        
                        <h4 class="font-bold text-slate-800 text-base mt-2">Belum Ada Program</h4>
                        <p class="text-sm text-slate-500 mt-1">Belum ada data program yang terdaftar di sistem.</p>
                    </div>
                @endforelse
            </div>
        </div>

    {{-- KONDISI 2: JIKA PROGRAM SUDAH DIPILIH (PARTISIPAN WORKSPACE SPREADSHEET MODE) --}}
    @else
        <div class="space-y-5">

            {{-- Top Action Bar & Tools --}}
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <a href="{{ route('superadmin.program-participants.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Ganti Program
                        </a>
                        <span class="text-slate-300">/</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-100">
                            {{ $selectedProgram->is_open ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}
                        </span>
                        <span class="text-xs font-black text-emerald-800 bg-emerald-100/90 px-3 py-1 rounded-md border border-emerald-300 flex items-center gap-1.5">
                            
                            Khusus Peserta Lolos (Status: PASSED)
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $selectedProgram->name }}
                    </h1>
                </div>

                {{-- Toolbar Excel Actions --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    {{-- 1. Tombol Download SEMUA Data User (No, NI, Nama, Email, Biodata, Alamat) - PASTI 100% SEMUA PENDAFTAR --}}
                    <a href="{{ route('superadmin.program-participants.export-excel', ['programId' => $selectedProgram->id, 'scope' => 'all', 'tag' => 'all', 'sort' => request('sort', 'provinsi_nama')]) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black rounded-xl transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5"
                       title="Download semua data pendaftar/user di program ini tanpa terfilter (total {{ number_format($stats['total_all'] ?? 0) }} pendaftar)">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
                            <path d="M8.8 11.2l1.9 2.8-1.9 2.8h1.6l1.1-1.8 1.1 1.8h1.6l-1.9-2.8 1.9-2.8h-1.6l-1.1 1.8-1.1-1.8H8.8z"/>
                        </svg>
                        <span> Download Semua Data User ({{ number_format($stats['total_all'] ?? 0) }})</span>
                    </a>

                    {{-- 2. Tombol Download Khusus Peserta Lolos --}}
                    <a href="{{ route('superadmin.program-participants.export-excel', ['programId' => $selectedProgram->id, 'scope' => 'passed', 'tag' => 'all', 'sort' => request('sort', 'provinsi_nama')]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl transition-all"
                       title="Download data khusus peserta yang berstatus lolos (total {{ number_format($stats['total_passed'] ?? 0) }} peserta)">
                        
                        <span>Khusus Lolos ({{ number_format($stats['total_passed'] ?? 0) }})</span>
                    </a>

                    {{-- 3. Tombol Download Sesuai Filter Tag Aktif (Muncul jika admin sedang memfilter tag tertentu) --}}
                    @if(request('tag') && request('tag') !== 'all')
                        <a href="{{ route('superadmin.program-participants.export-excel', ['programId' => $selectedProgram->id, 'scope' => 'all', 'tag' => request('tag'), 'filter_by_tag' => 1, 'sort' => request('sort', 'provinsi_nama')]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-bold rounded-xl transition-all shadow-sm"
                           title="Download khusus pendaftar yang memiliki tag {{ request('tag') }}">
                            <span>️</span>
                            <span>Khusus Tag "{{ request('tag') }}"</span>
                        </a>
                    @endif

                    {{-- 2. Tombol Khusus Update Nomor Induk --}}
                    <button type="button"
                            @click="niModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl transition-all">
                        
                        <span>Update Nomor Induk</span>
                    </button>

                    {{-- 3. Tombol Lengkapi Data (Sinkronisasi Data Kosong Saja) --}}
                    <button type="button"
                            @click="fillBlanksModalOpen = true; fillBlanksTab = 'paste'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        
                        <span>Lengkapi Data / Alamat Kosong</span>
                    </button>

                    {{-- 4. Tombol Tambah Peserta Baru via Excel --}}
                    <button type="button"
                            @click="importUsersModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all">
                        
                        <span>Tambah Peserta</span>
                    </button>

                    {{-- 5. Tombol Khusus Komparasi Data Sheet vs Database --}}
                    <a href="{{ route('superadmin.program-participants.reconciliation', $selectedProgram->id) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-emerald-600 text-white text-xs font-black rounded-xl transition-all shadow-md">
                        
                        <span>Komparasi Data Sheet</span>
                    </a>

                    {{-- 8. Tombol Pintas Backup Database & Auto-Backup --}}
                    <a href="{{ route('superadmin.database-backups.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition-all shadow-sm"
                       title="Buka panel backup database & kelola auto-backup">
                        
                        <span>Backup DB</span>
                    </a>

                    {{-- 6. Tombol Kelola / Tambah Tag Peserta (Pokja, Peserta Biasa, dll.) --}}
                    <button type="button"
                            @click="openTagModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                        <span>️</span>
                        <span>Kelola Tag Peserta</span>
                    </button>

                    {{-- 7. Tombol Verifikasi Massal Semua Email User di Program Ini --}}
                    @if(($stats['unverified_email'] ?? 0) > 0)
                        <form method="POST" action="{{ route('superadmin.program-participants.verify-all-emails', $selectedProgram->id) }}"
                              onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi langsung {{ $stats['unverified_email'] }} email user di program {{ $selectedProgram->name }} yang belum terverifikasi? Akun-akun ini akan langsung aktif & terverifikasi.')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs font-black rounded-xl transition-all shadow-md cursor-pointer animate-pulse">
                                
                                <span>Verifikasi Semua Email ({{ $stats['unverified_email'] }})</span>
                            </button>
                        </form>
                    @else
                        <div class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl cursor-default">
                            
                            <span>Semua Email Terverifikasi</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 5 KPI Ringkasan Metrik Peserta Lolos & Email --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Total Peserta Lolos</span>
                        <span class="text-xl font-black text-emerald-800">{{ number_format($stats['total_passed'] ?? 0) }}</span>
                        <span class="text-[10px] text-slate-400 font-bold block">dari {{ number_format($stats['total_all'] ?? 0) }} pendaftar</span>
                    </div>
                    
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Sudah Ada NI</span>
                        <span class="text-xl font-black text-indigo-700">{{ number_format($stats['has_ni'] ?? 0) }}</span>
                    </div>
                    
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Belum Ada NI</span>
                        <span class="text-xl font-black text-amber-700">{{ number_format($stats['no_ni'] ?? 0) }}</span>
                    </div>
                    
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Status Password</span>
                        <span class="text-xs font-black text-slate-800 block mt-0.5">
                            <span class="text-emerald-600">{{ number_format($stats['changed_password'] ?? 0) }} Aktif</span> /
                            <span class="text-amber-600">{{ number_format($stats['default_password'] ?? 0) }} Wajib Ganti</span>
                        </span>
                    </div>
                    
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-teal-600 uppercase tracking-wider block">Verifikasi Email</span>
                        <span class="text-xs font-black text-slate-800 block mt-0.5">
                            <span class="text-emerald-600">{{ number_format($stats['verified_email'] ?? 0) }} Aktif</span> /
                            <span class="{{ ($stats['unverified_email'] ?? 0) > 0 ? 'text-rose-600 font-extrabold' : 'text-slate-400' }}">{{ number_format($stats['unverified_email'] ?? 0) }} Belum</span>
                        </span>
                    </div>
                    <span class="p-2.5 rounded-xl bg-teal-50 text-teal-700 text-lg">️</span>
                </div>
            </div>

            {{-- Filter Bar & Pengurutan Hierarkis (Provinsi -> Nama) --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <form method="GET" action="{{ route('superadmin.program-participants.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">

                    {{-- Search --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Cari Peserta / NI</label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nama, email, NI..."
                                   class="w-full text-xs font-semibold pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Pengurutan (Sorting Hierarkis) --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Urutan Data</label>
                        <select name="sort" class="w-full text-xs font-semibold py-2 px-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="provinsi_nama" {{ request('sort', 'provinsi_nama') === 'provinsi_nama' ? 'selected' : '' }}> Provinsi (A-Z) → Nama</option>
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}> Pendaftar Terbaru</option>
                            <option value="nama_asc" {{ request('sort') === 'nama_asc' ? 'selected' : '' }}> Nama Peserta (A-Z)</option>
                            <option value="ni_asc" {{ request('sort') === 'ni_asc' ? 'selected' : '' }}> Nomor Induk (A-Z)</option>
                        </select>
                    </div>

                    {{-- Tag / Kategori Filter --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-purple-600 block mb-1">Tag / Kategori</label>
                        <select name="tag" class="w-full text-xs font-semibold py-2 px-2.5 bg-slate-50 border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white transition-all">
                            <option value="all">Semua Tag</option>
                            <option value="none" {{ request('tag') === 'none' ? 'selected' : '' }}>Tanpa Tag</option>
                            @foreach($availableTags as $avTag)
                                <option value="{{ $avTag }}" {{ request('tag') === $avTag ? 'selected' : '' }}>
                                    ️ {{ $avTag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Password --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Status Password</label>
                        <select name="password_status" class="w-full text-xs font-semibold py-2 px-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="all">Semua Password</option>
                            <option value="sudah_ganti" {{ request('password_status') === 'sudah_ganti' ? 'selected' : '' }}>🟢 Sudah Ganti (Aktif)</option>
                            <option value="belum_ganti" {{ request('password_status') === 'belum_ganti' ? 'selected' : '' }}>🟡 Belum Ganti (Wajib)</option>
                        </select>
                    </div>

                    {{-- Status Nomor Induk (NI) --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Status Nomor Induk</label>
                        <select name="ni_status" class="w-full text-xs font-semibold py-2 px-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="all">Semua Peserta Lolos</option>
                            <option value="has_ni" {{ request('ni_status') === 'has_ni' ? 'selected' : '' }}> Sudah Ada NI</option>
                            <option value="no_ni" {{ request('ni_status') === 'no_ni' ? 'selected' : '' }}>️ Belum Ada NI</option>
                        </select>
                    </div>

                    {{-- Status Verifikasi Email --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Status Verifikasi Email</label>
                        <select name="email_verified_status" class="w-full text-xs font-semibold py-2 px-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="all">Semua Email</option>
                            <option value="verified" {{ request('email_verified_status') === 'verified' ? 'selected' : '' }}>🟢 Terverifikasi</option>
                            <option value="unverified" {{ request('email_verified_status') === 'unverified' ? 'selected' : '' }}>️ Belum Verifikasi</option>
                        </select>
                    </div>

                    {{-- Provinsi Filter & Action Button --}}
                    <div class="flex items-end gap-1.5">
                        <div class="flex-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Provinsi</label>
                            <select name="provinsi" class="w-full text-xs font-semibold py-2 px-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                                <option value="all">Semua</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov }}" {{ request('provinsi') === $prov ? 'selected' : '' }}>
                                        {{ $prov }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-3 py-2 bg-slate-900 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'ni_status', 'password_status', 'provinsi', 'sort', 'tag', 'email_verified_status']))
                            <a href="{{ route('superadmin.program-participants.index', ['program_id' => $selectedProgram->id]) }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold" title="Reset Filter">
                                
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- TABEL SPREADSHEET PARTISIPAN PROGRAM --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/90 border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-600 select-none">
                                <th class="py-3 px-3 text-center w-10">
                                    <input type="checkbox"
                                           @click="toggleSelectAll()"
                                           :checked="isAllSelected()"
                                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                </th>
                                <th class="py-3 px-2 text-center w-12">No</th>
                                <th class="py-3 px-3">Nomor Induk (NI)</th>
                                <th class="py-3 px-3">Nama Peserta</th>
                                <th class="py-3 px-3">Email Akun</th>
                                <th class="py-3 px-3 text-center">Status Password</th>
                                <th class="py-3 px-3 text-center">Tag / Kategori</th>
                                <th class="py-3 px-3">Provinsi &amp; Kota/Kab</th>
                                <th class="py-3 px-3">Kecamatan &amp; Kelurahan/Desa</th>
                                <th class="py-3 px-3 text-center">Status Pendaftaran</th>
                                <th class="py-3 px-3 text-center">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($registrations as $index => $reg)
                                @php
                                    $user = $reg->user;
                                    $address = $user?->address;
                                    
                                    $wa = '-';
                                    if ($user && $user->biodataValues) {
                                        $phoneVal = $user->biodataValues->first(function($v) {
                                            $name = strtolower($v->biodataField->name ?? '');
                                            return str_contains($name, 'whatsapp') || str_contains($name, 'telepon') || str_contains($name, 'hp') || $v->biodata_field_id == 3;
                                        });
                                        if ($phoneVal && !empty($phoneVal->value)) {
                                            $wa = is_array($phoneVal->value) ? implode(', ', $phoneVal->value) : (string)$phoneVal->value;
                                        }
                                    }

                                    $hasChangedPassword = ($user && !$user->must_change_password);
                                @endphp
                                <tr class="hover:bg-emerald-50/40 transition-colors" :class="isSelected({{ $user?->id ?? 0 }}) ? 'bg-emerald-50/60' : ''">
                                    {{-- Checkbox --}}
                                    <td class="py-2.5 px-3 text-center align-middle">
                                        @if($user)
                                            <input type="checkbox"
                                                   value="{{ $user->id }}"
                                                   x-model="selectedUserIds"
                                                   class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                        @endif
                                    </td>

                                    {{-- No --}}
                                    <td class="py-2.5 px-2 text-center text-slate-400 font-bold align-middle">
                                        {{ $registrations->firstItem() + $index }}
                                    </td>

                                    {{-- Nomor Induk (NI) --}}
                                    <td class="py-2.5 px-3 align-middle font-mono font-bold">
                                        @if($reg->final_id_number)
                                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-900 border border-slate-300 rounded font-mono text-[11px]">
                                                {{ $reg->final_id_number }}
                                            </span>
                                        @else
                                            <span class="text-[11px] text-slate-300 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- Nama Peserta --}}
                                    <td class="py-2.5 px-3 align-middle">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-[10px] uppercase flex-shrink-0 border border-slate-200">
                                                {{ substr($user?->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-slate-900 whitespace-nowrap">{{ $user?->name ?? 'Akun Terhapus' }}</span>
                                                @if($user?->verification?->status === 'verified')
                                                    
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                     {{-- Email --}}
                                     <td class="py-2.5 px-3 align-middle font-mono text-[11px]">
                                         <div class="text-slate-800 font-semibold">{{ $user?->email ?? '-' }}</div>
                                         @if($user)
                                             @if($user->email_verified_at)
                                                 <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 mt-0.5" title="Email terverifikasi pada {{ $user->email_verified_at->format('d/m/Y H:i') }}">
                                                     
                                                     <span>Terverifikasi</span>
                                                 </span>
                                             @else
                                                 <form method="POST" action="{{ route('superadmin.program-participants.verify-single-email', $user->id) }}" class="inline-block mt-0.5" onsubmit="return confirm('Verifikasi email untuk {{ $user->name }} sekarang?')">
                                                     @csrf
                                                     <button type="submit" title="Klik untuk verifikasi akun ini sekarang" class="inline-flex items-center gap-1 text-[9px] font-black text-amber-800 bg-amber-100 hover:bg-amber-200 px-1.5 py-0.5 rounded border border-amber-300 transition-colors cursor-pointer">
                                                         
                                                         <span>Belum Verif (Klik Verif)</span>
                                                     </button>
                                                 </form>
                                             @endif
                                         @endif
                                     </td>

                                    {{-- Status Password (Sudah Ganti / Belum Ganti) --}}
                                    <td class="py-2.5 px-3 align-middle text-center whitespace-nowrap">
                                        @if($hasChangedPassword)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300" title="Pengguna telah mengganti password atau menggunakan password permanen">
                                                
                                                Sudah Ganti
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300" title="Password default/sementara dari admin, wajib diganti saat login">
                                                
                                                Belum Ganti
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Tag / Kategori --}}
                                    <td class="py-2.5 px-3 align-middle text-center whitespace-nowrap">
                                        @if(!empty($reg->tags))
                                            <div class="inline-flex flex-wrap gap-1 items-center justify-center max-w-[130px]">
                                                @foreach($reg->tags_array as $t)
                                                    @php
                                                        $tLower = strtolower($t);
                                                        $tagBadge = str_contains($tLower, 'pokja')
                                                            ? 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-200'
                                                            : (str_contains($tLower, 'biasa')
                                                                ? 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200'
                                                                : 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100');
                                                    @endphp
                                                    <button type="button"
                                                            @click="openSingleTagModal({{ $reg->id }}, '{{ addslashes($reg->tags) }}', '{{ addslashes($user?->name ?? '') }}')"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold border transition-all {{ $tagBadge }}"
                                                            title="Klik untuk ubah tag">
                                                        <span>️</span>
                                                        <span>{{ $t }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @else
                                            <button type="button"
                                                    @click="openSingleTagModal({{ $reg->id }}, '', '{{ addslashes($user?->name ?? '') }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-medium text-slate-400 hover:text-purple-700 hover:bg-purple-50 border border-dashed border-slate-200 transition-all"
                                                    title="Beri Tag ke Peserta">
                                                <span>+</span>
                                                <span>Tag</span>
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Provinsi & Kota/Kab --}}
                                    <td class="py-2.5 px-3 align-middle">
                                        <div class="font-bold text-slate-900 whitespace-nowrap">{{ $address?->provinsi ?: '-' }}</div>
                                        <div class="text-[10px] text-slate-500 whitespace-nowrap">{{ $address?->kabupaten ?: '-' }}</div>
                                    </td>

                                    {{-- Kecamatan & Kelurahan/Desa (Jelas!) --}}
                                    <td class="py-2.5 px-3 align-middle">
                                        <div class="text-[11px] text-slate-800">
                                            Kec: <strong>{{ $address?->kecamatan ?: '-' }}</strong>
                                        </div>
                                        <div class="inline-block px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold text-[10px]">
                                            Kel/Desa: {{ $address?->desa ?: '-' }}
                                        </div>
                                    </td>

                                    {{-- Status Pendaftaran (Khusus Lolos) --}}
                                    <td class="py-2.5 px-3 align-middle text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">
                                            
                                            Lolos (Passed)
                                        </span>
                                    </td>

                                    {{-- Aksi Cepat --}}
                                    <td class="py-2.5 px-3 align-middle text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button"
                                                    @click="openDetail({{ $reg->id }})"
                                                    class="p-1 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded"
                                                    title="Cek Berkas &amp; Biodata Lengkap">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            <button type="button"
                                                    @click="openStatusModal({{ $reg->id }}, '{{ $reg->status }}', '{{ $reg->final_id_number }}', '{{ addslashes($user?->name ?? '') }}')"
                                                    class="p-1 text-slate-600 hover:text-indigo-700 hover:bg-indigo-50 rounded"
                                                    title="Ubah Status / Nomor Induk Satuan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-12 text-center text-slate-400">
                                        <div class="space-y-1">
                                            
                                            <p class="font-bold text-slate-700">Tidak ada partisipan yang sesuai filter</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if($registrations->hasPages())
                    <div class="p-3 border-t border-slate-200 bg-slate-50/80">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>

            {{-- FLOATING ACTION BAR SAAT SELEKSI AKUN AKTIF --}}
            <div x-show="selectedUserIds.length > 0"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-8 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-8 opacity-0"
                 class="fixed bottom-6 inset-x-0 mx-auto max-w-xl z-40 bg-slate-900/95 backdrop-blur-md text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-bold">
                    
                    <span>Akun Peserta Dipilih</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="openTagModalWithSelected()"
                            class="px-3.5 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-600 hover:to-indigo-600 text-white font-black text-xs rounded-xl transition-all shadow-md flex items-center gap-1.5">
                        <span>️</span>
                        <span>Beri Tag</span>
                    </button>
                    <button type="button"
                            @click="bulkResetModalOpen = true"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs rounded-xl transition-all shadow-md flex items-center gap-1.5">
                        
                        <span>Reset Password</span>
                    </button>
                    <button type="button"
                            @click="selectedUserIds = []"
                            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition-colors">
                        Batal
                    </button>
                </div>
            </div>

        </div>
    @endif

    {{-- MODAL A: RESET PASSWORD MASSAL --}}
    <div x-show="bulkResetModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
             @click.outside="bulkResetModalOpen = false">

            <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Aksi Massal Akun</span>
                    <h3 class="text-lg font-black text-white">Reset Password Massal</h3>
                </div>
                <button type="button" @click="bulkResetModalOpen = false" class="text-white hover:text-slate-300 font-bold text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('superadmin.program-participants.bulk-reset-password') }}" class="p-6 space-y-4 text-xs">
                @csrf

                <template x-for="id in selectedUserIds" :key="id">
                    <input type="hidden" name="user_ids[]" :value="id">
                </template>

                <div class="p-3 bg-emerald-50 text-emerald-900 rounded-xl border border-emerald-100 flex items-center gap-2">
                    
                    <p>Password baru akan diterapkan serempak ke <strong x-text="selectedUserIds.length"></strong> akun terpilih.</p>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Password Baru:</label>
                    <div class="flex gap-2">
                        <input type="text"
                               name="new_password"
                               x-model="bulkPasswordInput"
                               required
                               placeholder="Ketik password baru..."
                               class="flex-1 text-xs font-mono font-bold py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <button type="button"
                                @click="bulkPasswordInput = generateRandomPassword()"
                                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-[11px] whitespace-nowrap">
                            Generate Acak
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-2 pt-1">
                    <input type="checkbox"
                           name="must_change_password"
                           id="mustChangePwd"
                           value="1"
                           checked
                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                    <label for="mustChangePwd" class="text-slate-600 font-medium cursor-pointer">
                        <strong>Wajibkan ganti password saat login berikutnya</strong>
                        <span class="block text-[10px] text-slate-400">Pengguna akan diarahkan ke form pembuatan password baru begitu mereka masuk.</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="bulkResetModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black shadow-md">
                        Terapkan Password Serempak
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL B: UPDATE NOMOR INDUK (NI) VIA EXCEL / COPAS EMAIL --}}
    @if($selectedProgram)
    <div x-show="niModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-8"
             @click.outside="niModalOpen = false">

            <div class="p-5 bg-gradient-to-r from-indigo-900 to-indigo-700 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-300 block"> Update Nomor Induk Cepat</span>
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        
                        <span>Update Nomor Induk (NI) Peserta</span>
                    </h3>
                </div>
                <button type="button" @click="niModalOpen = false" class="text-white hover:text-slate-300 font-bold text-xl">&times;</button>
            </div>

            <div class="p-6 space-y-5 text-xs">
                {{-- Banner Penjelasan Cerdas Pencocokan Email --}}
                <div class="p-4 bg-indigo-50/70 rounded-2xl border border-indigo-200 text-indigo-950 space-y-2">
                    <div class="flex items-start gap-2">
                        
                        <div>
                            <strong class="font-bold block text-xs">Otomatis Cocokkan Berdasarkan Email di Database:</strong>
                            <p class="text-[11px] text-indigo-900 mt-0.5 leading-relaxed">
                                Anda <strong>tidak perlu repot menyamakan format kolom</strong> atau mencari ID registrasi! Cukup sertakan kolom <strong>Email</strong> dan <strong>Nomor Induk</strong> (atau Nama jika ada) dari file Excel Anda. Sistem otomatis mencari akun peserta berdasarkan email di database dan memperbarui Nomor Induknya. Data profil lainnya <strong>100% aman dan tidak tersentuh</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Langkah 1: Download Template (Opsional, Bisa Sesuai Tag) --}}
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-slate-900 text-[11px]">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px]">1</span>
                            <span>Butuh File Template Akun Terurut? (Opsional)</span>
                        </div>
                        <span class="text-[10px] text-slate-400">Terurut Provinsi &rarr; Nama</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-0.5">
                        <select x-model="niDownloadTag" class="flex-1 text-xs py-2 px-3 bg-white border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-indigo-500">
                            <option value="all"> Semua Peserta Lolos (Semua Tag)</option>
                            <option value="none"> Tanpa Tag / Belum Ditandai</option>
                            @if(isset($availableTags) && $availableTags->isNotEmpty())
                                <optgroup label="Tag yang Terdaftar:">
                                    @foreach($availableTags as $avTag)
                                        <option value="{{ $avTag }}">️ Khusus Tag: {{ $avTag }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if(!isset($availableTags) || !$availableTags->contains('Pokja'))
                                <option value="Pokja">️ Khusus Tag: Pokja</option>
                            @endif
                            @if(!isset($availableTags) || !$availableTags->contains('Peserta Biasa'))
                                <option value="Peserta Biasa">️ Khusus Tag: Peserta Biasa</option>
                            @endif
                        </select>
                        <a :href="'{{ route('superadmin.program-participants.template-ni', $selectedProgram->id) }}?tag=' + encodeURIComponent(niDownloadTag)"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition-all shadow-sm whitespace-nowrap">
                            
                            <span>Download CSV</span>
                        </a>
                    </div>
                </div>

                {{-- Langkah 2: Tab Navigasi Masukan (Salin & Tempel vs Unggah File) --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-[11px]">
                        <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Masukkan Data Nomor Induk:</span>
                    </div>

                    <div class="flex border-b border-slate-200">
                        <button type="button"
                                @click="niModalTab = 'paste'"
                                :class="niModalTab === 'paste' ? 'border-indigo-600 text-indigo-700 font-black bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                                class="py-2.5 px-4 text-xs border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                            
                            <span>Salin &amp; Tempel (Copas dari Sheet)</span>
                            <span class="px-1.5 py-0.5 text-[9px] bg-indigo-100 text-indigo-800 rounded-full font-bold">Paling Cepat</span>
                        </button>
                        <button type="button"
                                @click="niModalTab = 'upload'"
                                :class="niModalTab === 'upload' ? 'border-indigo-600 text-indigo-700 font-black bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                                class="py-2.5 px-4 text-xs border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                            
                            <span>Unggah File Spreadsheet (.csv / .txt)</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.program-participants.import-ni', $selectedProgram->id) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- TAB 1: SALIN & TEMPEL LANGSUNG DARI EXCEL --}}
                        <div x-show="niModalTab === 'paste'" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-900 block">
                                    Tempelkan Kolom Sheet di Sini:
                                </label>
                                <span class="text-[10px] text-slate-400 font-medium">Bisa langsung dari Excel / Google Sheets</span>
                            </div>
                            <textarea name="pasted_data"
                                      rows="7"
                                      placeholder="Contoh: Blok kolom di Excel lalu Copy-Paste ke sini:&#10;Email	Nomor Induk&#10;peserta1@gmail.com	PRG202601001&#10;peserta2@gmail.com	PRG202601002&#10;peserta3@gmail.com	PRG202601003"
                                      class="w-full font-mono text-[11px] p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"></textarea>
                            <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                
                                <span><strong>Tips:</strong> Cukup blok kolom <em>Email</em> dan <em>Nomor Induk</em> (boleh juga ada kolom Nama) di Excel, tekan <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded text-[9px] font-bold">Ctrl+C</kbd>, lalu paste di kotak ini.</span>
                            </p>
                        </div>

                        {{-- TAB 2: UNGGAH BERKAS CSV / TXT --}}
                        <div x-show="niModalTab === 'upload'" class="space-y-3">
                            <label class="font-bold text-slate-900 block">
                                Pilih File CSV / TXT Hasil Olahan Excel:
                            </label>
                            <input type="file"
                                   name="file"
                                   accept=".csv,.txt"
                                   class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <p class="text-[10px] text-slate-400">
                                Pastikan file memiliki baris judul (header) yang memuat nama kolom <code>Email</code> dan <code>Nomor Induk</code>.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-medium">Hanya Nomor Induk yang diperbarui. Profil lain aman.</span>
                            <div class="flex gap-2">
                                <button type="button" @click="niModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black shadow-md flex items-center gap-1.5">
                                    
                                    <span>Perbarui Nomor Induk</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL C: LENGKAPI DATA KOSONG (FILL-BLANKS ONLY - COPAS / FILE) --}}
    <div x-show="fillBlanksModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
             @click.outside="fillBlanksModalOpen = false">

            {{-- Modal Header --}}
            <div class="p-5 bg-gradient-to-r from-amber-800 to-amber-600 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-200 block">️ Sinkronisasi Aman (Fill-Blanks Only)</span>
                    <h3 class="text-lg font-black text-white">Lengkapi Data / Alamat Kosong</h3>
                </div>
                <button type="button" @click="fillBlanksModalOpen = false" class="text-white hover:text-slate-300 font-bold text-xl">&times;</button>
            </div>

            <div class="p-6 space-y-5 text-xs">
                {{-- Penjelasan Ketentuan Fitur --}}
                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-950 space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-base">️</span>
                        <div>
                            <strong class="font-bold block text-xs">Data Lama di Database Dijamin 100% Aman:</strong>
                            <p class="text-[11px] text-amber-900 mt-0.5">
                                Fitur ini <strong>HANYA mengisi kolom yang saat ini masih kosong / NULL / '-' di database</strong> (misal peserta yang belum ada Provinsi, Kabupaten, Kecamatan, Desa, Detail Alamat, No WA, atau NI). Data yang sudah ada <strong>TIDAK AKAN PERNAH dirubah atau ditimpa</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-amber-200/60 text-[11px]">
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-700 font-bold"> Acuan Pencocokan:</span>
                            <span class="font-semibold text-slate-800">Email atau Nama Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-700 font-bold"> Kolom Bebas:</span>
                            <span class="font-semibold text-slate-800">Tidak wajib lengkap (bisa Nama + Email + Provinsi saja)</span>
                        </div>
                    </div>
                </div>

                {{-- Tab Navigasi: Salin-Tempel (Copas) vs Unggah File --}}
                <div class="flex border-b border-slate-200">
                    <button type="button"
                            @click="fillBlanksTab = 'paste'"
                            :class="fillBlanksTab === 'paste' ? 'border-amber-600 text-amber-700 font-black bg-amber-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                            class="py-2.5 px-4 text-xs border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                        
                        <span>Salin & Tempel (Copas dari Sheet)</span>
                        <span class="px-1.5 py-0.5 text-[9px] bg-amber-100 text-amber-800 rounded-full font-bold">Paling Mudah</span>
                    </button>
                    <button type="button"
                            @click="fillBlanksTab = 'upload'"
                            :class="fillBlanksTab === 'upload' ? 'border-amber-600 text-amber-700 font-black bg-amber-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
                            class="py-2.5 px-4 text-xs border-b-2 transition-all flex items-center gap-2 rounded-t-xl">
                        
                        <span>Unggah File Spreadsheet (.csv)</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.program-participants.import-fill-blanks', $selectedProgram->id) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    {{-- TAB 1: SALIN & TEMPEL (COPAS) LANGSUNG --}}
                    <div x-show="fillBlanksTab === 'paste'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-900 block">
                                Tempelkan Kolom Sheet di Sini:
                            </label>
                            <span class="text-[10px] text-slate-400 font-medium">Bisa dari Google Sheets / Excel</span>
                        </div>
                        <textarea name="pasted_data"
                                  rows="8"
                                  placeholder="Contoh: Blok kolom di Google Sheets lalu Copy-Paste ke sini:&#10;Nama	Email	Provinsi	Kabupaten&#10;Warih Handono	warihhan21.11@gmail.com	Jawa Barat	Bandung&#10;Nabila Azuwa	nabilaazuwa708@gmail.com	Aceh	Banda Aceh&#10;Muhammad Danil	muhamaddanil032002@gmail.com	Aceh	Aceh Besar"
                                  class="w-full font-mono text-[11px] p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"></textarea>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1">
                            
                            <span><strong>Tips:</strong> Di spreadsheet Anda, cukup blok kolom yang ingin dilengkapi (sertakan baris judulnya seperti <em>Nama</em>, <em>Email</em>, <em>Provinsi</em>, dll.), tekan <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded text-[9px] font-bold">Ctrl+C</kbd>, lalu paste di kotak atas.</span>
                        </p>

                        {{-- Quick Download Helper in Tab 1 --}}
                        <div class="p-2.5 bg-amber-50/70 rounded-xl border border-amber-200/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-[11px]">
                            <span class="text-amber-950 font-medium">Butuh referensi data peserta yang ingin dilengkapi?</span>
                            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                                <select x-model="fillBlanksDownloadTag" class="text-xs py-1 px-2 bg-white border border-amber-300 rounded-lg font-semibold focus:ring-2 focus:ring-amber-500">
                                    <option value="all">Semua Tag</option>
                                    <option value="none">Tanpa Tag</option>
                                    @if(isset($availableTags) && $availableTags->isNotEmpty())
                                        @foreach($availableTags as $avTag)
                                            <option value="{{ $avTag }}">{{ $avTag }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <a :href="'{{ route('superadmin.program-participants.template-fill-blanks', $selectedProgram->id) }}?tag=' + encodeURIComponent(fillBlanksDownloadTag)"
                                   class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-[10px] transition-all whitespace-nowrap shadow-sm">
                                    <span> Unduh CSV</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: UNGGAH FILE CSV --}}
                    <div x-show="fillBlanksTab === 'upload'" class="space-y-4">
                        <div class="space-y-2">
                            <label class="font-bold text-slate-900 block">
                                Pilih File CSV / TXT yang Telah Dilengkapi:
                            </label>
                            <input type="file"
                                   name="file"
                                   accept=".csv,.txt"
                                   class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500">
                        </div>

                        {{-- Download Template with Tag Filter in Tab 2 --}}
                        <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200 space-y-2">
                            <div>
                                <span class="font-bold text-amber-950 block text-[11px] flex items-center gap-1.5">
                                    
                                    <span>Download Template Sesuai Tag / Kategori:</span>
                                </span>
                                <span class="text-[10px] text-amber-800">Unduh data peserta saat ini dalam format CSV (bisa disaring berdasarkan tag)</span>
                            </div>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-1">
                                <select x-model="fillBlanksDownloadTag" class="flex-1 text-xs py-2 px-3 bg-white border border-amber-300 rounded-xl font-semibold focus:ring-2 focus:ring-amber-500">
                                    <option value="all"> Semua Peserta Lolos (Semua Tag)</option>
                                    <option value="none"> Tanpa Tag / Belum Ditandai</option>
                                    @if(isset($availableTags) && $availableTags->isNotEmpty())
                                        <optgroup label="Tag yang Terdaftar:">
                                            @foreach($availableTags as $avTag)
                                                <option value="{{ $avTag }}">️ Khusus Tag: {{ $avTag }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(!isset($availableTags) || !$availableTags->contains('Pokja'))
                                        <option value="Pokja">️ Khusus Tag: Pokja</option>
                                    @endif
                                    @if(!isset($availableTags) || !$availableTags->contains('Peserta Biasa'))
                                        <option value="Peserta Biasa">️ Khusus Tag: Peserta Biasa</option>
                                    @endif
                                </select>
                                <a :href="'{{ route('superadmin.program-participants.template-fill-blanks', $selectedProgram->id) }}?tag=' + encodeURIComponent(fillBlanksDownloadTag)"
                                   class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm whitespace-nowrap">
                                    
                                    <span>Download CSV</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-medium">Hanya baris yang cocok dan kolom kosong yang diperbarui.</span>
                        <div class="flex gap-2">
                            <button type="button" @click="fillBlanksModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-xl font-black shadow-md flex items-center gap-1.5">
                                
                                <span>Sinkronkan Data Kosong</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL D: TAMBAH PESERTA BARU VIA EXCEL --}}
    <div x-show="importUsersModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
             @click.outside="importUsersModalOpen = false">

            <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Import Data</span>
                    <h3 class="text-lg font-black text-white">Tambah Peserta Baru via Excel</h3>
                </div>
                <button type="button" @click="importUsersModalOpen = false" class="text-white hover:text-slate-300 font-bold text-lg">&times;</button>
            </div>

            <div class="p-6 space-y-5 text-xs">
                <div class="space-y-2">
                    <span class="font-bold text-slate-900 block">1. Download Template Tambah Peserta:</span>
                    <a href="{{ route('superadmin.program-participants.template-import-users', $selectedProgram->id) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-all">
                        
                        <span>Download Format CSV (.csv)</span>
                    </a>
                </div>

                <form method="POST" action="{{ route('superadmin.program-participants.import-new-users', $selectedProgram->id) }}" enctype="multipart/form-data" class="space-y-4 pt-2 border-t border-slate-100">
                    @csrf
                    <div class="space-y-2">
                        <span class="font-bold text-slate-900 block">2. Unggah File Data Peserta Baru:</span>
                        <input type="file"
                               name="file"
                               accept=".csv,.txt"
                               required
                               class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 block">Akun yang belum ada otomatis dibuatkan dengan password awal <code>ihi@2026</code> dan status wajib ganti password.</span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="importUsersModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black shadow-md">
                            Import &amp; Daftarkan Peserta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL E: DETAIL AKUN & BERKAS PENDAFTAR --}}
    <div x-show="detailModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-8"
             @click.outside="detailModalOpen = false">

            <div class="p-6 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Inspeksi Akun &amp; Berkas</span>
                    <h3 class="text-xl font-black text-white" x-text="detailData?.user?.name || 'Detail Peserta'"></h3>
                </div>
                <button type="button" @click="detailModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    &times;
                </button>
            </div>

            <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
                <div x-show="loadingDetail" class="py-12 text-center space-y-3">
                    <div class="w-8 h-8 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs font-bold text-slate-500">Mengambil data pendaftar &amp; berkas...</p>
                </div>

                <div x-show="!loadingDetail && detailData" class="space-y-6 text-xs">
                    {{-- Data Akun --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3">
                        <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400 block">Data Akun &amp; Kontak</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <span class="text-slate-500 block">Nama Lengkap:</span>
                                <strong class="text-slate-800 text-sm" x-text="detailData?.user?.name"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Email:</span>
                                <strong class="text-slate-800 text-sm" x-text="detailData?.user?.email"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">WhatsApp / Telepon:</span>
                                <strong class="text-emerald-700 font-bold" x-text="detailData?.whatsapp || '-'"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Nomor Induk (NI):</span>
                                
                            </div>
                            <div>
                                <span class="text-slate-500 block">Status Ganti Password:</span>
                                
                            </div>
                        </div>
                    </div>

                    {{-- Data Wilayah Lengkap Sampai Kelurahan --}}
                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 space-y-3">
                        <span class="font-bold uppercase tracking-wider text-[10px] text-emerald-800 block"> Data Wilayah &amp; Alamat Lengkap</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-700">
                            <div>
                                <span class="text-slate-500 block">Provinsi:</span>
                                <strong class="text-slate-900" x-text="detailData?.address?.provinsi || '-'"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Kabupaten / Kota:</span>
                                <strong class="text-slate-900" x-text="detailData?.address?.kabupaten || '-'"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Kecamatan:</span>
                                <strong class="text-slate-900" x-text="detailData?.address?.kecamatan || '-'"></strong>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-emerald-200">
                                <span class="text-emerald-800 font-bold block text-[10px] uppercase">Desa / Kelurahan:</span>
                                <strong class="text-emerald-950 text-sm" x-text="detailData?.address?.desa || '-'"></strong>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-slate-500 block">Kampung / Dusun &amp; Keterangan Alamat:</span>
                                <p class="text-slate-800 font-medium bg-white p-2.5 rounded-xl border border-slate-200 mt-1"
                                   x-text="(detailData?.address?.kampung ? detailData.address.kampung + ' - ' : '') + (detailData?.address?.detail_alamat || '-')"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat Formulir --}}
                    <div class="space-y-3">
                        <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400 block">Riwayat Isian Tahapan Formulir</span>
                        <template x-if="detailData?.stage_data && detailData.stage_data.length > 0">
                            <div class="space-y-3">
                                <template x-for="sData in detailData.stage_data" :key="sData.id">
                                    <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                            <strong class="text-slate-800" x-text="sData.stage?.title || 'Tahapan Program'"></strong>
                                            
                                        </div>
                                        <div class="space-y-2 pt-1">
                                            <template x-if="sData.form_values">
                                                <div class="grid grid-cols-1 gap-2">
                                                    <template x-for="(val, key) in sData.form_values" :key="key">
                                                        <div class="bg-slate-50 p-2.5 rounded-xl">
                                                            
                                                            
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!detailData?.stage_data || detailData.stage_data.length === 0">
                            <p class="text-slate-400 italic bg-slate-50 p-3 rounded-xl">Belum ada data isian tahapan yang dikirimkan.</p>
                        </template>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="detailModalOpen = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL F: UBAH STATUS PENDAFTARAN SATUAN --}}
    <div x-show="statusModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-8"
             @click.outside="statusModalOpen = false">

            <div class="p-6 bg-gradient-to-r from-emerald-800 to-teal-700 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300 block">Kontrol Super Admin</span>
                    <h3 class="text-lg font-black text-white" x-text="'Ubah: ' + statusForm.userName"></h3>
                </div>
                <button type="button" @click="statusModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    &times;
                </button>
            </div>

            <form :action="'{{ url('/superadmin/program-participants') }}/' + statusForm.regId + '/update-status'" method="POST" class="p-6 space-y-4 text-xs">
                @csrf

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Status Pendaftaran:</label>
                    <select name="status" x-model="statusForm.status" class="w-full text-xs font-bold py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="passed">🟢 Lolos (Passed)</option>
                        <option value="submitted">🟡 In Review / Submitted</option>
                        <option value="revision"> Revisi</option>
                        <option value="draft"> Draft</option>
                        <option value="rejected"> Ditolak (Rejected)</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Nomor Induk (NI / Final ID):</label>
                    <input type="text"
                           name="final_id_number"
                           x-model="statusForm.finalId"
                           placeholder="Contoh: IHI-2026-001"
                           class="w-full text-xs font-mono font-bold py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Kosongkan jika belum ingin memberikan Nomor Induk resmi.</span>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Catatan Super Admin (Opsional):</label>
                    <textarea name="notes"
                              rows="2"
                              placeholder="Alasan perubahan status pendaftar..."
                              class="w-full text-xs font-medium py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="statusModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-all shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL G: KELOLA & TAMBAH TAG MASSAL PESERTA --}}
    @if($selectedProgram)
    <div x-show="tagModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
             @click.outside="tagModalOpen = false">

            <div class="p-5 bg-gradient-to-r from-purple-900 via-indigo-900 to-purple-800 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-300 block">Kategori &amp; Pengelompokan Peserta</span>
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        <span>️</span>
                        <span>Kelola &amp; Tambah Tag Peserta</span>
                    </h3>
                </div>
                <button type="button" @click="tagModalOpen = false" class="text-white hover:text-slate-300 font-bold text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('superadmin.program-participants.bulk-tag', $selectedProgram->id) }}" class="p-6 space-y-5 text-xs">
                @csrf

                {{-- Hidden input for selected users from checkboxes if any --}}
                <template x-for="uid in selectedUserIds" :key="uid">
                    <input type="hidden" name="selected_user_ids[]" :value="uid">
                </template>

                {{-- Info Box --}}
                <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200 text-purple-950 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-purple-900 text-xs">
                        
                        <span>Cara Cepat Menandai Peserta:</span>
                    </div>
                    <p class="text-[11px] text-purple-900 leading-relaxed">
                        Cukup <strong>copy daftar email peserta</strong> dari Google Sheets / Excel lalu tempelkan ke kotak di bawah. Sistem otomatis mendeteksi email-email tersebut dan menandainya dengan tag yang Anda pilih (misal: <strong>pokja</strong>, <strong>pesertabiasa</strong>, dll.).
                    </p>
                </div>

                {{-- Jika ada akun dicentang dari tabel --}}
                <div x-show="selectedUserIds.length > 0" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        
                        <span class="font-bold text-[11px]">Akun peserta sedang dicentang dari tabel</span>
                    </div>
                    <span class="text-[10px] text-emerald-700 font-medium">Akan ikut ditandai bersama daftar email</span>
                </div>

                {{-- Kotak Copas Email --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-900 block">
                            Tempelkan Daftar Email Peserta:
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Bisa dipisah baris / spasi / koma</span>
                    </div>
                    <textarea name="pasted_emails"
                              x-model="tagPastedEmails"
                              rows="5"
                              placeholder="Contoh:&#10;peserta1@gmail.com&#10;peserta2@gmail.com&#10;peserta3@gmail.com"
                              class="w-full font-mono text-[11px] p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:bg-white transition-all"></textarea>
                </div>

                {{-- Pilihan Tag --}}
                <div class="space-y-2">
                    <label class="font-bold text-slate-900 block">Pilih atau Ketik Nama Tag:</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                @click="selectedTagInput = 'Pokja'"
                                :class="selectedTagInput === 'Pokja' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-purple-50 text-purple-800 border-purple-200 hover:bg-purple-100'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>️</span>
                            <span>Pokja</span>
                        </button>
                        <button type="button"
                                @click="selectedTagInput = 'Peserta Biasa'"
                                :class="selectedTagInput === 'Peserta Biasa' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>️</span>
                            <span>Peserta Biasa</span>
                        </button>
                        <button type="button"
                                @click="selectedTagInput = 'Fasilitator'"
                                :class="selectedTagInput === 'Fasilitator' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>️</span>
                            <span>Fasilitator</span>
                        </button>
                        <button type="button"
                                @click="selectedTagInput = 'Panitia'"
                                :class="selectedTagInput === 'Panitia' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>️</span>
                            <span>Panitia</span>
                        </button>
                    </div>
                    <div class="pt-1">
                        <input type="text"
                               name="tag_name"
                               x-model="selectedTagInput"
                               placeholder="Atau ketik nama tag kustom (misal: Pokja Aceh, Alumni, dll.)..."
                               class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white transition-all font-semibold">
                    </div>
                </div>

                {{-- Mode Aksi Tag --}}
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block text-[11px]">Mode Penerapan:</label>
                    <div class="grid grid-cols-3 gap-2 text-[11px]">
                        <label class="p-2.5 rounded-xl border cursor-pointer transition-all flex flex-col items-center text-center gap-1"
                               :class="tagActionChoice === 'set' ? 'border-purple-500 bg-purple-50 text-purple-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="tag_action" value="set" x-model="tagActionChoice" class="sr-only">
                            <span> Ganti / Tetapkan</span>
                            <span class="text-[9px] text-slate-400 font-normal">Ganti tag jadi ini</span>
                        </label>
                        <label class="p-2.5 rounded-xl border cursor-pointer transition-all flex flex-col items-center text-center gap-1"
                               :class="tagActionChoice === 'append' ? 'border-purple-500 bg-purple-50 text-purple-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="tag_action" value="append" x-model="tagActionChoice" class="sr-only">
                            <span> Tambahkan</span>
                            <span class="text-[9px] text-slate-400 font-normal">Gabung ke tag lama</span>
                        </label>
                        <label class="p-2.5 rounded-xl border cursor-pointer transition-all flex flex-col items-center text-center gap-1"
                               :class="tagActionChoice === 'remove' ? 'border-rose-500 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="tag_action" value="remove" x-model="tagActionChoice" class="sr-only">
                            <span>️ Hapus Tag Ini</span>
                            <span class="text-[9px] text-slate-400 font-normal">Hapus dari akun</span>
                        </label>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="tagModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-black shadow-md flex items-center gap-1.5">
                        <span>️</span>
                        <span>Terapkan Tag</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL H: EDIT TAG SATUAN PESERTA --}}
    <div x-show="singleTagModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
             @click.outside="singleTagModalOpen = false">

            <div class="p-4 bg-gradient-to-r from-purple-800 to-indigo-700 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-200 block">Edit Tag Peserta</span>
                    <h3 class="text-sm font-black text-white" x-text="singleTagUserName"></h3>
                </div>
                <button type="button" @click="singleTagModalOpen = false" class="text-white hover:text-slate-300 font-bold text-lg">&times;</button>
            </div>

            <form method="POST" :action="'{{ url('/superadmin/program-participants') }}/' + singleTagRegId + '/update-tag'" class="p-5 space-y-4 text-xs">
                @csrf
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-800 block">Tag / Kategori:</label>
                    <div class="flex flex-wrap gap-1.5 pb-2">
                        <button type="button" @click="singleTagInput = 'Pokja'" class="px-2.5 py-1 bg-purple-50 text-purple-800 border border-purple-200 rounded-lg text-[10px] font-bold hover:bg-purple-100">Pokja</button>
                        <button type="button" @click="singleTagInput = 'Peserta Biasa'" class="px-2.5 py-1 bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-[10px] font-bold hover:bg-slate-100">Peserta Biasa</button>
                        <button type="button" @click="singleTagInput = ''" class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-bold hover:bg-rose-100">Hapus Tag</button>
                    </div>
                    <input type="text"
                           name="tag"
                           x-model="singleTagInput"
                           placeholder="Contoh: Pokja, Peserta Biasa..."
                           class="w-full text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 font-semibold">
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="singleTagModalOpen = false" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black shadow-sm">
                        Simpan Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
function participantHub() {
    return {
        detailModalOpen: false,
        loadingDetail: false,
        detailData: null,

        statusModalOpen: false,
        statusForm: {
            regId: '',
            status: '',
            finalId: '',
            userName: ''
        },

        // Fitur Seleksi Massal
        selectedUserIds: [],
        bulkResetModalOpen: false,
        bulkPasswordInput: 'ihi@2026',

        // Fitur Modal Tambahan Excel
        niModalOpen: false,
        niModalTab: 'paste',
        niDownloadTag: '{{ request("tag", "all") }}',
        fillBlanksModalOpen: false,
        fillBlanksDownloadTag: '{{ request("tag", "all") }}',
        fillBlanksTab: 'paste',
        importUsersModalOpen: false,

        // Fitur Tag Peserta
        tagModalOpen: false,
        tagPastedEmails: '',
        selectedTagInput: 'Pokja',
        tagActionChoice: 'set',

        singleTagModalOpen: false,
        singleTagRegId: '',
        singleTagInput: '',
        singleTagUserName: '',

        // ID semua user di halaman aktif
        currentPageUserIds: @json($registrations ? $registrations->pluck('user_id')->filter()->values() : []),

        isSelected(userId) {
            return this.selectedUserIds.includes(userId);
        },

        isAllSelected() {
            if (!this.currentPageUserIds.length) return false;
            return this.currentPageUserIds.every(id => this.selectedUserIds.includes(id));
        },

        toggleSelectAll() {
            if (this.isAllSelected()) {
                this.selectedUserIds = this.selectedUserIds.filter(id => !this.currentPageUserIds.includes(id));
            } else {
                this.selectedUserIds = Array.from(new Set([...this.selectedUserIds, ...this.currentPageUserIds]));
            }
        },

        generateRandomPassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
            let res = '';
            for (let i = 0; i < 10; i++) {
                res += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return res;
        },

        openDetail(regId) {
            this.detailModalOpen = true;
            this.loadingDetail = true;
            this.detailData = null;

            fetch('{{ url('/superadmin/program-participants') }}/' + regId + '/detail')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.detailData = data.data;
                    } else {
                        alert('Gagal mengambil data detail pendaftar.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan jaringan.');
                })
                .finally(() => {
                    this.loadingDetail = false;
                });
        },

        openStatusModal(regId, currentStatus, currentNi, userName) {
            this.statusForm.regId = regId;
            this.statusForm.status = currentStatus;
            this.statusForm.finalId = (currentNi && currentNi !== 'null' && currentNi !== '-') ? currentNi : '';
            this.statusForm.userName = userName;
            this.statusModalOpen = true;
        },

        openTagModal() {
            this.tagModalOpen = true;
        },

        openTagModalWithSelected() {
            this.tagModalOpen = true;
        },

        openSingleTagModal(regId, currentTag, userName) {
            this.singleTagRegId = regId;
            this.singleTagInput = currentTag || '';
            this.singleTagUserName = userName;
            this.singleTagModalOpen = true;
        }
    }
}
</script>
@endsection
