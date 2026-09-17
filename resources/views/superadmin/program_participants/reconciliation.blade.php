@extends('superadmin.layouts.app')

@section('title', 'Komparasi Data: ' . $program->name)

@section('content')
{{-- 1. Embed Data Hasil Komparasi ke Elemen Script Aman (Bebas dari masalah quote/kutip pada HTML attribute) --}}
@if($comparisonResult)
<script type="application/json" id="reconciliation-json">
    {!! json_encode($comparisonResult, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
@endif

{{-- 2. Definisikan Logika Alpine SEBELUM Elemen HTML (Mencegah ReferenceError saat Alpine render) --}}
<script>
window.reconciliationApp = function() {
    let parsedInitial = null;
    const jsonEl = document.getElementById('reconciliation-json');
    if (jsonEl && jsonEl.textContent.trim()) {
        try {
            parsedInitial = JSON.parse(jsonEl.textContent.trim());
            if (parsedInitial && parsedInitial.items) {
                parsedInitial.items.forEach(it => {
                    it.isProcessing = false;
                });
            }
        } catch (e) {
            console.error("Gagal parse data komparasi:", e);
        }
    }

    return {
        results: parsedInitial,
        filterTab: 'missing',
        searchKeyword: '',
        isSyncingAll: false,
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 4000);
        },

        getMissingCount() {
            if (!this.results || !this.results.items) return 0;
            return this.results.items.filter(item => item.status !== 'already_passed').length;
        },

        filteredItems() {
            if (!this.results || !this.results.items) return [];

            return this.results.items.filter(item => {
                // Filter Tab
                let matchesTab = true;
                if (this.filterTab === 'missing') {
                    matchesTab = (item.status !== 'already_passed');
                } else if (this.filterTab === 'registered_not_passed') {
                    matchesTab = (item.status === 'registered_not_passed');
                } else if (this.filterTab === 'user_exists_not_in_prog') {
                    matchesTab = (item.status === 'user_exists_not_in_prog');
                } else if (this.filterTab === 'not_registered') {
                    matchesTab = (item.status === 'not_registered');
                } else if (this.filterTab === 'already_passed') {
                    matchesTab = (item.status === 'already_passed');
                }

                if (!matchesTab) return false;

                // Filter Keyword
                if (!this.searchKeyword) return true;
                const kw = this.searchKeyword.toLowerCase();
                const name = (item.name || '').toLowerCase();
                const email = (item.email || '').toLowerCase();
                const ni = (item.ni || '').toLowerCase();

                return name.includes(kw) || email.includes(kw) || ni.includes(kw);
            });
        },

        async syncSingle(item) {
            if (item.isProcessing) return;
            item.isProcessing = true;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                          || '{{ csrf_token() }}';

            try {
                const response = await fetch('{{ route('superadmin.program-participants.reconciliation.sync-single', $program->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: item.email,
                        name: item.name,
                        ni: item.ni
                    })
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    // Update counters
                    if (item.status !== 'already_passed') {
                        if (item.status === 'user_exists_not_in_prog' && this.results.stats.user_exists_not_in_prog > 0) {
                            this.results.stats.user_exists_not_in_prog--;
                        } else if (item.status === 'not_registered' && this.results.stats.not_registered > 0) {
                            this.results.stats.not_registered--;
                        } else if (item.status === 'registered_not_passed' && this.results.stats.registered_not_passed > 0) {
                            this.results.stats.registered_not_passed--;
                        }
                        this.results.stats.already_passed++;
                    }

                    // Update item state
                    item.status = 'already_passed';
                    item.status_label = 'Sudah Lolos di Program';
                    item.status_color = 'emerald';
                    item.db_ni = data.final_id_number || (item.ni && item.ni !== '-' ? item.ni : null);
                    item.ni_different = false;

                    this.showToast(data.message || 'Peserta berhasil dimasukkan ke program sebagai peserta lolos.', 'success');
                } else {
                    alert('Gagal menyinkronkan peserta: ' + (data.message || 'Terjadi kesalahan pada server.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat menyinkronkan peserta.');
            } finally {
                item.isProcessing = false;
            }
        },

        async syncAllMissing() {
            if (this.isSyncingAll) return;

            const missing = this.results.items.filter(item => item.status !== 'already_passed');
            if (missing.length === 0) {
                alert('Semua peserta sudah terdaftar sebagai anggota lolos.');
                return;
            }

            if (!confirm(`Apakah Anda yakin ingin memasukkan seluruh ${missing.length} peserta ini ke program sebagai peserta lolos?`)) {
                return;
            }

            this.isSyncingAll = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                          || '{{ csrf_token() }}';

            try {
                const response = await fetch('{{ route('superadmin.program-participants.reconciliation.sync-all', $program->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        participants: missing.map(m => ({
                            email: m.email,
                            name: m.name,
                            ni: m.ni
                        }))
                    })
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    // Update all items locally
                    missing.forEach(item => {
                        item.status = 'already_passed';
                        item.status_label = 'Sudah Lolos di Program';
                        item.status_color = 'emerald';
                        item.db_ni = (item.ni && item.ni !== '-') ? item.ni : null;
                        item.ni_different = false;
                    });
                    this.results.stats.already_passed += missing.length;
                    this.results.stats.user_exists_not_in_prog = 0;
                    this.results.stats.not_registered = 0;
                    this.results.stats.registered_not_passed = 0;
                    this.filterTab = 'all';

                    this.showToast(data.message || 'Semua peserta berhasil dimasukkan ke program.', 'success');
                } else {
                    alert('Gagal sinkronisasi massal: ' + (data.message || 'Terjadi kesalahan pada server.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat sinkronisasi massal.');
            } finally {
                this.isSyncingAll = false;
            }
        }
    };
};

// Daftarkan ke Alpine jika Alpine sudah siap
document.addEventListener('alpine:init', () => {
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('reconciliationApp', window.reconciliationApp);
    }
});
</script>

<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8" x-data="reconciliationApp()">

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

    {{-- Alert Notification Realtime (Toast) --}}
    <div x-show="toast.show"
         x-cloak
         x-transition
         class="p-4 rounded-2xl flex items-center justify-between shadow-md"
         :class="toast.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'">
        <div class="flex items-center gap-3">
            
            <p class="text-sm font-bold" x-text="toast.message"></p>
        </div>
        <button type="button" @click="toast.show = false" class="text-white hover:text-slate-200 font-bold text-lg">&times;</button>
    </div>

    {{-- Top Header & Navigation --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap text-xs font-bold text-slate-500">
                <a href="{{ route('superadmin.program-participants.index', ['program_id' => $program->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Tabel Partisipan
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-100">
                    {{ $program->name }}
                </span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700">Komparasi &amp; Rekonsiliasi Data Sheet</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                
                <span>Komparasi Data Peserta (Sheet vs Database)</span>
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-3xl">
                Cek dan cocokkan data spreadsheet eksternal dengan database IHI. Sistem akan mendeteksi peserta yang belum masuk, akun yang sudah ada namun belum berstatus lolos, serta menyediakan tombol 1-klik untuk langsung menjadikannya member lolos.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            <div class="px-4 py-2 bg-emerald-50 rounded-2xl border border-emerald-100 text-right">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 block">Peserta Lolos di Web</span>
                <span class="text-xl font-black text-emerald-900" x-text="results ? results.stats.already_passed + ' Orang' : '{{ number_format($program->passed_count) }} Orang'"></span>
            </div>
        </div>
    </div>

    {{-- Section Input Data (Upload CSV / Paste Google Sheets) --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4" x-data="{ inputTab: 'paste' }">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 flex-wrap gap-3">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    
                    <span>Langkah 1: Masukkan Data dari Spreadsheet</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pilih metode input yang paling praktis untuk Anda: Copy-paste langsung baris kolom, atau unggah file CSV.
                </p>
            </div>

            {{-- Tab Switcher --}}
            <div class="flex p-1 bg-slate-100 rounded-xl text-xs font-bold">
                <button type="button"
                        @click="inputTab = 'paste'"
                        :class="inputTab === 'paste' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    
                    <span>Copy-Paste dari Sheets</span>
                </button>
                <button type="button"
                        @click="inputTab = 'file'"
                        :class="inputTab === 'file' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                    
                    <span>Unggah File CSV</span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.program-participants.reconciliation.process', $program->id) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Option A: Paste Google Sheets / Excel --}}
            <div x-show="inputTab === 'paste'" class="space-y-2">
                <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-xs text-indigo-900 space-y-1">
                    <strong class="font-bold flex items-center gap-1.5">
                        
                        <span>Cara Termudah:</span>
                    </strong>
                    <p class="text-[11px] text-indigo-800">
                        Buka file Google Sheets / Excel Anda, sorot/block kolom <strong>Nama</strong>, <strong>Email</strong>, dan <strong>Nomor Induk</strong> (bisa sekaligus puluhan/ratusan baris), tekan <code>Ctrl+C</code>, lalu paste (<code>Ctrl+V</code>) langsung ke dalam kotak di bawah ini. Urutan kolom tidak masalah, sistem otomatis mendeteksi kolom Email dan Nama!
                    </p>
                </div>

                <label class="font-bold text-slate-700 text-xs block">Paste Data Tabel Spreadsheet di Sini:</label>
                <textarea name="pasted_data"
                          rows="6"
                          placeholder="Nama Peserta&#9;Email&#9;Nomor Induk&#10;Ahmad Fauzi&#9;ahmad@gmail.com&#9;GLI-2026-001&#10;Budi Santoso&#9;budi@gmail.com&#9;GLI-2026-002&#10;..."
                          class="w-full text-xs font-mono p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('pasted_data', $rawInput ?? '') }}</textarea>
            </div>

            {{-- Option B: Upload File CSV --}}
            <div x-show="inputTab === 'file'" x-cloak class="space-y-3">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-700 space-y-1">
                    <p class="text-[11px]">
                        Pastikan file berformat <strong>.csv</strong> atau <strong>.txt</strong> dengan kolom yang memuat <strong>Email</strong>, <strong>Nama</strong>, dan opsional <strong>Nomor Induk</strong>.
                    </p>
                </div>

                <div>
                    <label class="font-bold text-slate-700 text-xs block mb-1">Pilih File CSV / Excel:</label>
                    <input type="file"
                           name="file"
                           accept=".csv,.txt"
                           class="w-full text-xs py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-emerald-600 text-white text-xs font-black rounded-xl transition-all shadow-md cursor-pointer">
                    
                    <span>Jalankan Komparasi Sekarang</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Section Hasil Komparasi --}}
    <div x-show="results" x-cloak class="space-y-6">

        {{-- Alert jika ada baris spreadsheet yang dilewati karena email tidak valid --}}
        <div x-show="results && results.skipped_rows && results.skipped_rows.length > 0"
             x-cloak
             class="p-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-2xl text-xs space-y-2">
            <div class="flex items-center gap-2 font-bold text-amber-800">
                <span class="p-1.5 bg-amber-500 text-white rounded-lg text-xs">️</span>
                <span>Perhatian: Ditemukan  baris pada spreadsheet yang dilewati karena tidak memiliki format email yang valid:</span>
            </div>
            <div class="overflow-x-auto max-h-36 overflow-y-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="text-amber-700 font-bold border-b border-amber-200">
                        <tr>
                            <th class="py-1 px-2">Baris ke</th>
                            <th class="py-1 px-2">Isi Baris</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        <template x-for="sk in results.skipped_rows" :key="sk.row_num">
                            <tr>
                                <td class="py-1 px-2 font-bold" x-text="'Baris ' + sk.row_num"></td>
                                <td class="py-1 px-2 font-mono text-amber-900" x-text="sk.preview"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ringkasan Metrik Perbandingan (KPI Cards) --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Total Data Sheet --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total di Sheet</span>
                    
                </div>
                
            </div>

            {{-- Sudah Lolos di Web --}}
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Sudah Lolos</span>
                    
                </div>
                
            </div>

            {{-- Masih Tahap Proses (PROCESS / SUBMITTED) --}}
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Tahap Proses</span>
                    
                </div>
                
            </div>

            {{-- Akun Ada di Web, Belum Masuk Program --}}
            <div class="bg-white p-4 rounded-2xl border border-indigo-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Akun Ada (Di Luar)</span>
                    
                </div>
                <span class="p-3 rounded-xl bg-indigo-50 text-indigo-600 text-xl">🟡</span>
            </div>

            {{-- Akun Belum Ada Sama Sekali --}}
            <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Akun Belum Ada</span>
                    
                </div>
                
            </div>
        </div>

        {{-- Action Toolbar & Filter Tab --}}
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Tab Filter Status --}}
                <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                    <button type="button"
                            @click="filterTab = 'all'"
                            :class="filterTab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                        Semua ()
                    </button>

                    <button type="button"
                            @click="filterTab = 'missing'"
                            :class="filterTab === 'missing' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200'"
                            class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>️</span>
                        <span>Perlu Dimasukkan / Belum Lolos</span>
                        
                    </button>

                    <button type="button"
                            @click="filterTab = 'registered_not_passed'"
                            :class="filterTab === 'registered_not_passed' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200'"
                            class="px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                        Status Proses ()
                    </button>

                    <button type="button"
                            @click="filterTab = 'user_exists_not_in_prog'"
                            :class="filterTab === 'user_exists_not_in_prog' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-900 hover:bg-indigo-100 border border-indigo-200'"
                            class="px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                        Akun Ada di Web ()
                    </button>

                    <button type="button"
                            @click="filterTab = 'not_registered'"
                            :class="filterTab === 'not_registered' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-900 hover:bg-rose-100 border border-rose-200'"
                            class="px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                        Akun Belum Ada ()
                    </button>

                    <button type="button"
                            @click="filterTab = 'already_passed'"
                            :class="filterTab === 'already_passed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100 border border-emerald-200'"
                            class="px-3.5 py-2 rounded-xl transition-all cursor-pointer">
                        Sudah Lolos ()
                    </button>
                </div>

                {{-- Bulk Actions & Export --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Tombol Masukkan Semua yang Belum --}}
                    <div x-show="getMissingCount() > 0">
                        <button type="button"
                                @click.prevent.stop="syncAllMissing()"
                                :disabled="isSyncingAll"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition-all shadow-md disabled:opacity-50 cursor-pointer">
                            <span x-show="!isSyncingAll"> Masukkan Semua () ke Program</span>
                            <span x-show="isSyncingAll" class="flex items-center gap-1.5" style="display:none;">
                                <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Memproses Sinkronisasi Massal...</span>
                            </span>
                        </button>
                    </div>

                    {{-- Download Hasil Komparasi --}}
                    <form method="POST" action="{{ route('superadmin.program-participants.reconciliation.export', $program->id) }}">
                        @csrf
                        <input type="hidden" name="report_items" :value="results ? JSON.stringify(results.items) : ''">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all border border-slate-200 cursor-pointer">
                            
                            <span>Download Laporan CSV</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Search Filter Dalam Hasil --}}
            <div>
                <input type="text"
                       x-model="searchKeyword"
                       placeholder="Cari nama, email, atau nomor induk dalam hasil komparasi..."
                       class="w-full text-xs font-semibold px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>
        </div>

        {{-- Tabel Hasil Komparasi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/90 border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-600 select-none">
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-3">Nama (di Sheet)</th>
                            <th class="py-3 px-3">Email Akun</th>
                            <th class="py-3 px-3">Nomor Induk (Sheet)</th>
                            <th class="py-3 px-3">Status di Sistem IHI</th>
                            <th class="py-3 px-3">Nomor Induk di Web</th>
                            <th class="py-3 px-3 text-center">Aksi Sinkronisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, idx) in filteredItems()" :key="item.email + '_' + idx">
                            <tr class="hover:bg-slate-50 transition-colors"
                                :class="item.status === 'already_passed' ? 'bg-emerald-50/20' : (item.status === 'user_exists_not_in_prog' ? 'bg-indigo-50/20' : 'bg-amber-50/20')">
                                {{-- No --}}
                                <td class="py-2.5 px-3 text-center text-slate-400 font-bold align-middle" x-text="item.index"></td>

                                {{-- Nama di Sheet --}}
                                <td class="py-2.5 px-3 align-middle font-bold text-slate-900" x-text="item.name"></td>

                                {{-- Email --}}
                                <td class="py-2.5 px-3 align-middle font-mono text-slate-600 text-[11px]" x-text="item.email"></td>

                                {{-- NI di Sheet --}}
                                <td class="py-2.5 px-3 align-middle font-mono font-bold">
                                    
                                    <span x-show="!item.ni || item.ni === '-'" class="text-slate-300 italic">-</span>
                                </td>

                                {{-- Status di Web --}}
                                <td class="py-2.5 px-3 align-middle whitespace-nowrap">
                                    <span x-show="item.status === 'already_passed'"
                                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        
                                        Sudah Lolos di Program
                                    </span>

                                    <span x-show="item.status === 'user_exists_not_in_prog'"
                                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                        
                                        Akun Ada di Web (Belum Masuk Program)
                                    </span>

                                    

                                    <span x-show="item.status === 'not_registered'"
                                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-900 border border-rose-300">
                                        
                                        Akun Belum Ada di Web
                                    </span>
                                </td>

                                {{-- Nomor Induk di Web --}}
                                <td class="py-2.5 px-3 align-middle font-mono">
                                    
                                    <span x-show="!item.db_ni" class="text-slate-300 italic text-[11px]">Belum Ada</span>
                                </td>

                                {{-- Aksi Cepat Satuan --}}
                                <td class="py-2.5 px-3 align-middle text-center whitespace-nowrap">
                                    {{-- 1. Kondisi: Belum Lolos / Belum Masuk Program --}}
                                    <div x-show="item.status !== 'already_passed'">
                                        <button type="button"
                                                @click.prevent.stop="syncSingle(item)"
                                                :disabled="item.isProcessing"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] shadow-sm transition-all disabled:opacity-50 cursor-pointer">
                                            <span x-show="!item.isProcessing">+ Jadikan Member Lolos</span>
                                            <span x-show="item.isProcessing" class="flex items-center gap-1" style="display:none;">
                                                <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                Memproses...
                                            </span>
                                        </button>
                                    </div>

                                    {{-- 2. Kondisi: Sudah Lolos tapi NI di sheet berbeda --}}
                                    <div x-show="item.status === 'already_passed' && item.ni_different">
                                        <button type="button"
                                                @click.prevent.stop="syncSingle(item)"
                                                :disabled="item.isProcessing"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-[10px] transition-all cursor-pointer">
                                            <span> Update NI</span>
                                        </button>
                                    </div>

                                    {{-- 3. Kondisi: Sudah Lolos & NI Cocok --}}
                                    <div x-show="item.status === 'already_passed' && !item.ni_different">
                                        <span class="text-emerald-600 font-bold text-[11px] flex items-center justify-center gap-1">
                                            
                                            <span>Sesuai</span>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredItems().length === 0">
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <p class="font-bold text-slate-600 text-sm">Tidak ada data yang sesuai dengan filter ini.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
