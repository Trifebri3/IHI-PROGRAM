@extends('superadmin.layouts.app')

@section('title', 'Backup Database & Auto-Backup')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ 
    manualModal: false, 
    filterType: 'all', 
    searchKeyword: '',
    isCreatingBackup: false 
}">

    {{-- 1. Header Banner & Quick Actions --}}
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-gradient-to-br from-emerald-100/40 to-teal-100/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold mb-3">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Database Resilience & Security</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight">
                    Backup Database &amp; Auto-Backup
                </h1>
                <p class="text-slate-500 text-sm mt-1 max-w-2xl leading-relaxed">
                    Kelola pencadangan basis data MySQL secara manual maupun terjadwal otomatis (Auto-Backup). Unduh arsip database sewaktu-waktu ke perangkat lokal Anda dengan aman.
                </p>
            </div>

            {{-- Quick Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="POST" action="{{ route('superadmin.database-backups.run-auto') }}" 
                      onsubmit="return confirm('Apakah Anda ingin memicu eksekusi Auto-Backup sekarang?')">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                        
                        <span>Tes Auto-Backup</span>
                    </button>
                </form>

                <button type="button" 
                        @click="manualModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black rounded-xl transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                    
                    <span>Buat Backup Baru</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Notifikasi Flash --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">×</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-sm font-bold">×</button>
        </div>
    @endif

    {{-- 2. Metrik Status Database (4 KPI Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Database Aktif --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">Database Aktif</span>
                <span class="text-base font-black text-slate-800 block mt-0.5 truncate max-w-[170px]" title="{{ $dbName }}">{{ $dbName }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">{{ $mysqlVersion }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                ️
            </div>
        </div>

        {{-- Card 2: Kapasitas & Tabel --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">Ukuran &amp; Tabel</span>
                <span class="text-xl font-black text-slate-800 block mt-0.5">{{ $dbSizeFormatted }}</span>
                <span class="text-[11px] text-slate-500 font-semibold block mt-0.5">{{ $totalTables }} Tabel Terdeteksi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                
            </div>
        </div>

        {{-- Card 3: Status Auto-Backup --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">Auto-Backup</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($autoSettings['enabled'])
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-base font-black text-emerald-700">AKTIF</span>
                    @else
                        <span class="inline-block w-2 h-2 rounded-full bg-slate-400"></span>
                        <span class="text-base font-black text-slate-500">NONAKTIF</span>
                    @endif
                </div>
                <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">
                    @if($autoSettings['last_run'])
                        Terakhir: {{ \Carbon\Carbon::parse($autoSettings['last_run'])->diffForHumans() }}
                    @else
                        Belum pernah berjalan
                    @endif
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $autoSettings['enabled'] ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-xl flex-shrink-0">
                
            </div>
        </div>

        {{-- Card 4: Total File Backup Tersimpan --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">Arsip Backup</span>
                <span class="text-xl font-black text-slate-800 block mt-0.5">{{ count($backups) }} File</span>
                <span class="text-[11px] text-slate-500 font-semibold block mt-0.5">Total: {{ $totalBackupSizeFormatted }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                
            </div>
        </div>
    </div>

    {{-- 3. Konfigurasi Auto-Backup & Pembuatan Manual --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2 Kolom): Pengaturan Auto-Backup --}}
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 lg:p-7 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <span>️</span>
                            <span>Konfigurasi Auto-Backup Database</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Atur jadwal pencadangan otomatis tanpa perlu campur tangan manual.</p>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $autoSettings['enabled'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $autoSettings['enabled'] ? 'Jadwal Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('superadmin.database-backups.settings') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Status Switch --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Auto-Backup</label>
                            <select name="auto_backup_enabled" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2.5">
                                <option value="1" {{ $autoSettings['enabled'] ? 'selected' : '' }}>🟢 Aktifkan Auto-Backup Terjadwal</option>
                                <option value="0" {{ !$autoSettings['enabled'] ? 'selected' : '' }}> Nonaktifkan Auto-Backup</option>
                            </select>
                        </div>

                        {{-- Frekuensi --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Frekuensi Jadwal</label>
                            <select name="auto_backup_frequency" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2.5">
                                <option value="every_12_hours" {{ $autoSettings['frequency'] === 'every_12_hours' ? 'selected' : '' }}>Setiap 12 Jam (Dua Kali Sehari)</option>
                                <option value="daily" {{ $autoSettings['frequency'] === 'daily' ? 'selected' : '' }}>Setiap Hari (Pukul 02:00 Dini Hari) - Direkomendasikan</option>
                                <option value="weekly" {{ $autoSettings['frequency'] === 'weekly' ? 'selected' : '' }}>Setiap Minggu (1x Seminggu)</option>
                            </select>
                        </div>

                        {{-- Batas Retensi File --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Maksimal Arsip Tersimpan (Retensi)</label>
                            <select name="auto_backup_max_files" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2.5">
                                <option value="5" {{ $autoSettings['max_files'] === 5 ? 'selected' : '' }}>Simpan 5 File Terakhir</option>
                                <option value="10" {{ $autoSettings['max_files'] === 10 ? 'selected' : '' }}>Simpan 10 File Terakhir (Standar)</option>
                                <option value="20" {{ $autoSettings['max_files'] === 20 ? 'selected' : '' }}>Simpan 20 File Terakhir</option>
                                <option value="30" {{ $autoSettings['max_files'] === 30 ? 'selected' : '' }}>Simpan 30 File Terakhir (1 Bulan)</option>
                            </select>
                            <span class="text-[10px] text-slate-400 mt-1 block">File backup paling lama akan otomatis dihapus saat batas tercapai.</span>
                        </div>

                        {{-- Format Kompresi --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Berkas Hasil Backup</label>
                            <select name="auto_backup_format" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2.5">
                                <option value="zip" {{ $autoSettings['format'] === 'zip' ? 'selected' : '' }}>.ZIP (Kompresi Ringan &amp; Hemat Disk ~85%)</option>
                                <option value="sql" {{ $autoSettings['format'] === 'sql' ? 'selected' : '' }}>.SQL (Teks Mentah Standar MySQL)</option>
                            </select>
                            <span class="text-[10px] text-slate-400 mt-1 block">Format ZIP lebih cepat diunduh dan sangat hemat penyimpanan server.</span>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-between">
                        <div class="text-xs text-slate-500 font-medium">
                            @if($nextScheduled)
                                <span> Jadwal perkiraan berikutnya: <strong class="text-slate-700">{{ $nextScheduled->format('d M Y, H:i') }} WIB</strong></span>
                            @else
                                <span> Jadwal berikutnya: <span class="text-slate-400">Menunggu eksekusi pertama</span></span>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kolom Kanan (1 Kolom): Manual Instant Backup --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-6 lg:p-7 text-white shadow-xl flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl"></div>

            <div>
                <div class="flex items-center gap-2 mb-3">
                    
                    <div>
                        <h3 class="text-base font-black text-white tracking-tight">Manual Instant Backup</h3>
                        <p class="text-[11px] text-slate-400">Buat salinan instan database sekarang</p>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed mt-3">
                    Fitur ini mendumpling seluruh skema tabel, relasi, indeks, dan baris data secara instan dan aman.
                </p>

                <form method="POST" action="{{ route('superadmin.database-backups.create') }}" class="mt-5 space-y-4" @submit="isCreatingBackup = true">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Format Arsip</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-700 bg-slate-800/50 cursor-pointer hover:border-emerald-500 transition">
                                <input type="radio" name="format" value="zip" checked class="text-emerald-500 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-slate-200">.ZIP (Hemat)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-700 bg-slate-800/50 cursor-pointer hover:border-emerald-500 transition">
                                <input type="radio" name="format" value="sql" class="text-emerald-500 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-slate-200">.SQL (Mentah)</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="download_now" value="1" checked class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                            <span>Langsung unduh ke komputer saya</span>
                        </label>
                    </div>

                    <div class="pt-3">
                        <button type="submit" 
                                :disabled="isCreatingBackup"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-xs font-black rounded-xl transition-all shadow-lg hover:shadow-emerald-500/20 disabled:opacity-50">
                            <span x-show="!isCreatingBackup"> Mulai Backup Sekarang</span>
                            <span x-show="isCreatingBackup" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses Backup...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. Tabel Riwayat File Backup Database --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Toolbar Riwayat --}}
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    
                    <span>Daftar Berkas Backup Tersimpan ({{ count($backups) }})</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Seluruh file backup yang dapat diunduh langsung ke komputer Anda atau dihapus.</p>
            </div>

            {{-- Filter & Search --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative">
                    <input type="text" 
                           x-model="searchKeyword" 
                           placeholder="Cari nama file..." 
                           class="text-xs rounded-xl border-slate-200 pl-8 pr-3 py-2 w-48 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                    
                </div>

                <select x-model="filterType" class="text-xs rounded-xl border-slate-200 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 font-bold text-slate-700">
                    <option value="all">Semua Tipe</option>
                    <option value="auto">🟢 Otomatis</option>
                    <option value="manual"> Manual</option>
                </select>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-5">No</th>
                        <th class="py-3.5 px-5">Nama Berkas</th>
                        <th class="py-3.5 px-5">Tipe</th>
                        <th class="py-3.5 px-5">Format</th>
                        <th class="py-3.5 px-5">Ukuran</th>
                        <th class="py-3.5 px-5">Tanggal &amp; Waktu Pembuatan</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                    @forelse($backups as $index => $file)
                        <tr class="hover:bg-slate-50/80 transition"
                            x-show="(filterType === 'all' || filterType === '{{ $file['type'] }}') && 
                                    (searchKeyword === '' || '{{ strtolower($file['filename']) }}'.includes(searchKeyword.toLowerCase()))">
                            <td class="py-3.5 px-5 text-slate-400 font-mono">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $file['extension'] === 'zip' ? '️' : '' }}</span>
                                    <span class="font-mono font-bold text-slate-800">{{ $file['filename'] }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                @if($file['type'] === 'auto')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Otomatis</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        <span>Manual</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-black uppercase tracking-wider {{ $file['extension'] === 'zip' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ strtoupper($file['extension']) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                {{ $file['size_formatted'] }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-600">
                                <span class="font-bold text-slate-800">{{ $file['created_at']->format('d M Y') }}</span>
                                <span class="text-slate-400 text-[11px] block">{{ $file['created_at']->format('H:i:s') }} WIB ({{ $file['created_at']->diffForHumans() }})</span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Tombol Download --}}
                                    <a href="{{ route('superadmin.database-backups.download', $file['filename']) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Download</span>
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    <form method="POST" action="{{ route('superadmin.database-backups.destroy', $file['filename']) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen file backup {{ $file['filename'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition border border-rose-200">
                                            <span>️</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-2xl mx-auto mb-3">
                                    
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum Ada File Backup Tersimpan</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Backup Baru" di atas untuk mencadangkan database Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. Card Informasi & Panduan Restorasi Database --}}
    <div class="bg-white rounded-3xl p-6 lg:p-7 border border-slate-100 shadow-sm">
        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
            
            <span>Panduan Restorasi Database (Restore Guide)</span>
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600 leading-relaxed">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="font-bold text-slate-900 block mb-1">1. Ekstraksi Berkas</span>
                Jika Anda mengunduh berkas dengan format <code>.zip</code>, ekstrak terlebih dahulu file arsip tersebut untuk mendapatkan berkas skrip SQL mentah (<code>.sql</code>).
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="font-bold text-slate-900 block mb-1">2. Via phpMyAdmin / GUI</span>
                Buka phpMyAdmin, pilih nama database target, masuk ke tab <strong>Import</strong>, unggah berkas <code>.sql</code>, lalu klik tombol <strong>Go / Kirim</strong>.
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="font-bold text-slate-900 block mb-1">3. Via Terminal / CLI</span>
                Gunakan perintah standar terminal:<br>
                <code class="bg-slate-200/80 px-1.5 py-0.5 rounded text-[11px] block mt-1 font-mono text-slate-800">mysql -u root -p {{ $dbName }} &lt; backup.sql</code>
            </div>
        </div>
    </div>

    {{-- Modal Popup: Buat Backup Manual Baru --}}
    <div x-show="manualModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 lg:p-7 shadow-2xl border border-slate-100 relative animate-scale-up"
             @click.away="manualModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    
                    <span>Buat Backup Database Baru</span>
                </h3>
                <button type="button" @click="manualModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">×</button>
            </div>

            <form method="POST" action="{{ route('superadmin.database-backups.create') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Format Berkas</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border-2 border-slate-200 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 cursor-pointer transition">
                            <input type="radio" name="format" value="zip" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">.ZIP Archive</span>
                                <span class="text-[10px] text-slate-400 block">Terkompresi (~85%)</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border-2 border-slate-200 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 cursor-pointer transition">
                            <input type="radio" name="format" value="sql" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">.SQL Script</span>
                                <span class="text-[10px] text-slate-400 block">Teks mentah</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <label class="flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="download_now" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Langsung unduh file ke komputer saya setelah selesai</span>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" @click="manualModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition shadow-md">
                        Mulai Backup Database
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
