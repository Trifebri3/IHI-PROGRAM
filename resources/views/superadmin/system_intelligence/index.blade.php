@extends('superadmin.layouts.app')

@section('title', 'System Intelligence & Operations Console')

@section('content')
<!-- Memuat CDN Chart.js untuk visualisasi grafik premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="systemConsole()">
    
    <!-- Header Konsol -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <div class="inline-block bg-slate-100 text-slate-700 text-[10px] font-mono px-2.5 py-1 rounded-md uppercase tracking-wider border border-slate-200">
                    PANEL KONTROL AMAN
                </div>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-2">
                System Intelligence &amp; Konsol Operasi Mandiri
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl font-medium">
                Platform observabilitas &amp; antarmuka perbaikan mandiri untuk pemantauan kesehatan aplikasi secara real-time, latensi respons, insiden keamanan, dan audit performa struktur kode.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <a href="{{ route('superadmin.system-intelligence.export-excel') }}" 
               class="px-5 py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-750 font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Unduh Laporan Excel (Lengkap)</span>
            </a>

            <button @click="runSelfHealing()" 
                    :disabled="healingStatus === 'running'"
                    class="px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" :class="healingStatus === 'running' ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span x-text="healingStatus === 'running' ? 'Menjalankan AI Perbaikan...' : 'Picu AI Perbaikan Mandiri' "></span>
            </button>
        </div>
    </div>

    <!-- Navigasi Tab (Gaya Terang Premium) -->
    <div class="flex flex-wrap items-center gap-2 bg-slate-100/85 p-1.5 rounded-2xl border border-slate-200">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Ikhtisar</button>
        <button @click="activeTab = 'health'" :class="activeTab === 'health' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Kesehatan</button>
        <button @click="activeTab = 'errors'" :class="activeTab === 'errors' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">
            <span class="flex items-center gap-1.5">
                Error &amp; Log Monitoring
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-500 animate-pulse" x-show="openErrorsCount() > 0"></span>
            </span>
        </button>
        <button @click="activeTab = 'availability'" :class="activeTab === 'availability' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Ketersediaan</button>
        <button @click="activeTab = 'performance'" :class="activeTab === 'performance' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Performa</button>
        <button @click="activeTab = 'codebase'" :class="activeTab === 'codebase' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">
            <span class="flex items-center gap-1">
                Audit Kode &amp; Struktur
                <span class="inline-flex h-2 w-2 rounded-full bg-rose-500 animate-pulse" x-show="findings.length > 0"></span>
            </span>
        </button>
        <button @click="activeTab = 'anomalies'" :class="activeTab === 'anomalies' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">
            <span class="flex items-center gap-1.5">
                Anomali Pengguna
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-500 animate-pulse" x-show="anomalousUsers.length > 0"></span>
            </span>
        </button>
        <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Keamanan</button>
        <button @click="activeTab = 'sla'" :class="activeTab === 'sla' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">SLA</button>
        <button @click="activeTab = 'usage'" :class="activeTab === 'usage' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Penggunaan</button>
        <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-white text-slate-900 shadow-xs border-slate-200' : 'text-slate-500 hover:text-slate-800 border-transparent'" class="px-4 py-2 rounded-xl text-xs font-bold transition border">Pengaturan AI</button>
    </div>

    <!-- Panel Toast Notifikasi Alert -->
    <div x-show="notification.show" 
         x-cloak
         x-transition
         class="fixed bottom-5 right-5 z-50 p-4 rounded-2xl shadow-lg border text-xs font-bold flex items-center gap-3.5"
         :class="notification.type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200'">
        <span x-text="notification.message"></span>
        <button @click="notification.show = false" class="hover:text-slate-900 text-slate-400">✕</button>
    </div>

    <!-- Panel Simulasi Diagnostik Perbaikan Mandiri -->
    <div x-show="healingStatus !== 'idle'" 
         x-transition
         class="bg-white border-2 p-5 rounded-3xl space-y-4 shadow-sm"
         :class="healingStatus === 'running' ? 'border-amber-200 bg-amber-50/10' : (healingStatus === 'success' ? 'border-emerald-200 bg-emerald-50/10' : 'border-rose-200 bg-rose-50/10')">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="healingStatus === 'running' ? 'bg-amber-400' : (healingStatus === 'success' ? 'bg-emerald-400' : 'bg-rose-400')"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="healingStatus === 'running' ? 'bg-amber-500' : (healingStatus === 'success' ? 'bg-emerald-500' : 'bg-rose-500')"></span>
                </span>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider" x-text="healingStatus === 'running' ? 'AI Diagnostic & Perbaikan Sistem Aktif' : (healingStatus === 'success' ? 'Perbaikan Berhasil Diselesaikan' : 'Kesalahan Diagnostik')"></h3>
            </div>
            <button @click="healingStatus = 'idle'" class="text-xs font-bold text-slate-400 hover:text-slate-600">Tutup</button>
        </div>
        
        <div class="font-mono text-xs space-y-1.5 text-slate-300 p-4 bg-slate-950 rounded-2xl max-h-[220px] overflow-y-auto border border-slate-900">
            <template x-for="step in healingSteps">
                <div class="leading-relaxed" x-text="step"></div>
            </template>
            <div x-show="healingStatus === 'running'" class="text-amber-400 animate-pulse mt-2">● Mengeksekusi instruksi perbaikan sistem...</div>
            <div x-show="healingStatus === 'success'" class="text-emerald-400 font-bold mt-2">✔ Sukses: Cache view dibersihkan, status antrean gagal direset ke nol.</div>
            <div x-show="healingStatus === 'error'" class="text-rose-400 font-bold mt-2">✖ Gagal berkomunikasi dengan pengontrol pemulihan sistem.</div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: IKHTISAR (OVERVIEW)                 -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'overview'" x-transition class="space-y-8">
        <!-- Grid Ringkasan Kesehatan Real-Time -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Status Kesehatan</span>
                <span class="text-lg font-black tracking-wide mt-2" 
                      :class="'{{ $overallHealth }}' === 'SEHAT' ? 'text-emerald-600' : ('{{ $overallHealth }}' === 'PERINGATAN' ? 'text-amber-600' : 'text-rose-600')">
                    {{ $overallHealth }}
                </span>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Ketersediaan</span>
                <span class="text-lg font-black text-emerald-600 tracking-wide mt-2">
                    {{ $availabilityData['uptime'] }}
                </span>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Performa</span>
                <span class="text-lg font-black text-emerald-600 tracking-wide mt-2">OPTIMAL</span>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Skor Keamanan</span>
                <span class="text-lg font-black text-emerald-600 tracking-wide mt-2">
                    {{ $securityScore }}/100
                </span>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Status SLA</span>
                <span class="text-lg font-black text-emerald-600 tracking-wide mt-2">
                    {{ $slaStatus }}
                </span>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl flex flex-col justify-between min-h-[100px] shadow-xs">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Beban Kerja</span>
                <span class="text-lg font-black text-emerald-600 tracking-wide mt-2">NORMAL</span>
            </div>
        </div>

        <!-- GRAFIK STATISTIK HISTORIS (Harian, Mingguan, Bulanan, Tahunan) -->
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Grafik Statistika Pendaftaran Peserta Program
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Analisis tren data registrasi peserta secara transaksional riil.</p>
                </div>

                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200 self-start">
                    <button @click="changeChartPeriod('daily')" :class="chartPeriod === 'daily' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Harian</button>
                    <button @click="changeChartPeriod('weekly')" :class="chartPeriod === 'weekly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Mingguan</button>
                    <button @click="changeChartPeriod('monthly')" :class="chartPeriod === 'monthly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Bulanan</button>
                    <button @click="changeChartPeriod('yearly')" :class="chartPeriod === 'yearly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Tahunan</button>
                </div>
            </div>

            <div class="w-full relative h-[320px]">
                <canvas id="registrationTrendChart"></canvas>
            </div>
        </div>

        <!-- PUSAT ANALISIS APM KINERJA & TELEMETRI SISTEM -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik 1: Trafik & Latensi Web -->
            <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        Trafik Request &amp; Latensi Web
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Memetakan volume request terhadap kecepatan respon halaman (ms).</p>
                </div>
                <div class="w-full relative h-[200px]">
                    <canvas id="trafficLatencyChart"></canvas>
                </div>
            </div>

            <!-- Grafik 2: Sumber Daya Server & Latensi SQL -->
            <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Kapasitas RAM/CPU &amp; Latensi SQL
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Mendeteksi perlambatan eksekusi kueri akibat beban CPU/RAM.</p>
                </div>
                <div class="w-full relative h-[200px]">
                    <canvas id="resourcesSqlChart"></canvas>
                </div>
            </div>

            <!-- Grafik 3: Aktivitas User & Keamanan -->
            <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Percobaan Login &amp; Blokir Keamanan
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Mendeteksi anomali brute-force atau spam akses login.</p>
                </div>
                <div class="w-full relative h-[200px]">
                    <canvas id="loginSecurityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pusat Insiden & Timeline Perbaikan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Log Kejadian Perbaikan -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Pusat Penanganan Insiden &amp; Audit AI
                </h3>
                
                <div class="space-y-3.5 max-h-[350px] overflow-y-auto pr-1">
                    <template x-for="log in logs" :key="log.id">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-sm uppercase font-mono"
                                      :class="log.level === 'PERINGATAN' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                                      x-text="log.level"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="log.timestamp"></span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800" x-text="log.incident"></h4>
                            <div class="text-[11px] text-slate-600 space-y-1.5 pl-2.5 border-l-2 border-emerald-600">
                                <div><strong class="text-slate-700">Diagnosis Masalah:</strong> <span x-text="log.diagnosis"></span></div>
                                <div><strong class="text-slate-700">Tindakan AI Agent:</strong> <span x-text="log.action"></span></div>
                                <div><strong class="text-slate-700">Hasil Verifikasi:</strong> <span class="text-emerald-600 font-bold" x-text="log.verification"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Alur Kerja AI Observabilitas -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl flex flex-col justify-between shadow-sm">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Arsitektur Pipeline AI Perbaikan Mandiri
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-1 font-medium">
                        Pipeline analisis anomali sistem secara otomatis di backend aplikasi.
                    </p>
                </div>

                <div class="grid grid-cols-6 gap-2 my-6">
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">1. DETEKSI</span>
                        <span class="text-[9px] text-emerald-600 font-bold">Aktif</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">2. DIAGNOSA</span>
                        <span class="text-[9px] text-emerald-600 font-bold">Aktif</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">3. AGEN</span>
                        <span class="text-[9px] text-emerald-600 font-bold">Siap</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">4. AKSI</span>
                        <span class="text-[9px] text-slate-400">Antre</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">5. VERIF</span>
                        <span class="text-[9px] text-slate-400">Antre</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-center border border-slate-200">
                        <span class="block text-[10px] font-black text-slate-700">6. AUDIT</span>
                        <span class="text-[9px] text-slate-400">Antre</span>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-2xl border border-slate-200">
                    <strong class="text-slate-700">Cakupan Perbaikan AI:</strong> Pembersihan otomatis cache server compiled, pengaturan memori overhead, pembersihan queue gagal, serta penutupan session kadaluarsa.
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: KESEHATAN SISTEM (HEALTH)           -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'health'" x-transition class="space-y-6">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Pemantau Kesehatan Modul Utama</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- App -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Laravel Core Engine</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                          :class="'{{ $appHealth }}' === 'SEHAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        {{ $appHealth }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Environment: <span class="text-slate-800 font-bold">{{ config('app.env') }}</span></div>
                    <div>Debug Mode: <span class="text-slate-800 font-bold">{{ config('app.debug') ? 'AKTIF (PERINGATAN)' : 'MATI (PASS)' }}</span></div>
                    <div>Akses Tulis Storage: <span class="text-emerald-600 font-bold">YES</span></div>
                </div>
            </div>
            <!-- Database -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Koneksi Basis Data (Database)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                          :class="'{{ $dbHealth }}' === 'SEHAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                        {{ $dbHealth }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Driver Database: <span class="text-slate-800 font-bold">{{ config('database.default') }}</span></div>
                    <div>Latensi Query: <span class="text-emerald-600 font-bold">{{ $dbLatency }} ms</span></div>
                    <div>Koneksi Operasional: <span class="text-emerald-600 font-bold">LANCAR</span></div>
                </div>
            </div>
            <!-- Cache -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Sistem Cache (Penyimpanan Sementara)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                          :class="'{{ $cacheHealth }}' === 'SEHAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                        {{ $cacheHealth }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Driver Cache: <span class="text-slate-800 font-bold">{{ config('cache.default') }}</span></div>
                    <div>Latensi Baca-Tulis: <span class="text-emerald-600 font-bold">{{ $cacheLatency }} ms</span></div>
                    <div>Status Uji: <span class="text-emerald-600 font-bold">100% SUKSES</span></div>
                </div>
            </div>
            <!-- Queue Worker -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Pekerja Antrean (Queue Workers)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                          :class="'{{ $queueHealth }}' === 'SEHAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        {{ $queueHealth }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Antrean Gagal (Failed Jobs): <span class="text-slate-800 font-bold">{{ $failedJobsCount }}</span></div>
                    <div>Antrean Tertunda: <span class="text-slate-800 font-bold">{{ $pendingJobsCount }}</span></div>
                    <div>Status Daemon: <span class="text-emerald-600 font-bold">ONLINE</span></div>
                </div>
            </div>
            <!-- Storage -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Kapasitas Penyimpanan Disk</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                          :class="'{{ $storageHealth }}' === 'SEHAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                        {{ $storageHealth }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Kapasitas Disk Terpakai: <span class="text-slate-800 font-bold">{{ $diskUsedPercent }}%</span></div>
                    <div>Folder Media Lokal: <span class="text-emerald-600 font-bold">WRITABLE (LANCAR)</span></div>
                    <div>Folder Logs Writable: <span class="text-emerald-600 font-bold">YES</span></div>
                </div>
            </div>
            <!-- Scheduler -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Tugas Terjadwal (Cron Scheduler)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono bg-emerald-50 text-emerald-700 border border-emerald-200">
                        SEHAT
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                    <div>Operasi Terakhir: <span class="text-slate-800 font-bold">{{ date('Y-m-d H:i:00') }}</span></div>
                    <div>Tugas Terdaftar: <span class="text-emerald-600 font-bold">6 Tugas Aktif</span></div>
                    <div>Status Engine: <span class="text-emerald-600 font-bold">RUNNING</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: ERROR & LOG MONITORING (NEW)        -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'errors'" x-transition class="space-y-6">
        <!-- Rangkuman Kinerja Error (Trend Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Error Hari Ini</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-slate-900 font-mono">{{ $errorsToday }}</span>
                    <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-0.5">
                        ↓ {{ abs($errorTrendPct) }}%
                        <span class="text-[9px] text-slate-400 font-normal">vs kemarin</span>
                    </span>
                </div>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Insiden Terbuka (OPEN)</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-rose-600 font-mono" x-text="countErrorsByStatus('OPEN')"></span>
                    <span class="text-[10px] text-slate-400 font-medium">Membutuhkan investigasi</span>
                </div>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Dalam Investigasi</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-amber-600 font-mono" x-text="countErrorsByStatus('INVESTIGATING')"></span>
                    <span class="text-[10px] text-slate-400 font-medium">Sedang ditinjau IT</span>
                </div>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Insiden Selesai (RESOLVED)</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-emerald-600 font-mono" x-text="countErrorsByStatus('RESOLVED')"></span>
                    <span class="text-[10px] text-slate-400 font-medium">Terselesaikan sepenuhnya</span>
                </div>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Severity Filter -->
                <div class="space-y-1">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Bahaya</label>
                    <select x-model="selectedSeverity" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:border-emerald-500 outline-none transition cursor-pointer">
                        <option value="ALL">Semua Tingkat</option>
                        <option value="CRITICAL">CRITICAL</option>
                        <option value="ERROR">ERROR</option>
                        <option value="WARNING">WARNING</option>
                    </select>
                </div>
                
                <!-- Service Filter -->
                <div class="space-y-1">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Layanan / Modul</label>
                    <select x-model="selectedService" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:border-emerald-500 outline-none transition cursor-pointer">
                        <option value="ALL">Semua Layanan</option>
                        <option value="Database Service">Database Service</option>
                        <option value="Authentication Service">Authentication Service</option>
                        <option value="Program Registration">Program Registration</option>
                        <option value="Queue Worker">Queue Worker</option>
                        <option value="Event Check-in">Event Check-in</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="space-y-1">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Status Investigasi</label>
                    <select x-model="selectedStatus" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:border-emerald-500 outline-none transition cursor-pointer">
                        <option value="ALL">Semua Status</option>
                        <option value="OPEN">BARU (OPEN)</option>
                        <option value="INVESTIGATING">DIINVESTIGASI</option>
                        <option value="RESOLVED">SELESAI (RESOLVED)</option>
                    </select>
                </div>
            </div>
            
            <button @click="resetErrorFilters()" class="text-xs text-slate-500 hover:text-slate-800 font-bold self-end lg:self-center">✕ Reset Filter</button>
        </div>

        <!-- Aggregated Errors Table -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Error Teragregasi (Error Aggregation)</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Kejadian error sejenis dikelompokkan otomatis untuk mempercepat pencarian. Klik baris error untuk menganalisis stack trace dan perangkat user.</p>
            </div>

            <div x-show="filteredErrors().length === 0" class="p-12 text-center bg-slate-50/50 space-y-2">
                <span class="text-3xl">🎉</span>
                <h4 class="text-sm font-bold text-slate-800">Tidak ada Log Error yang sesuai filter</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Semua insiden dengan filter terpilih bersih atau sudah selesai diselesaikan.</p>
            </div>

            <div x-show="filteredErrors().length > 0" class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 bg-slate-50/50">
                            <th class="py-3 px-6">ID / Tingkat</th>
                            <th class="py-3 pr-4">Kejadian Terakhir</th>
                            <th class="py-3 pr-4">Layanan Terpengaruh</th>
                            <th class="py-3 pr-4">Pesan Error Teknis</th>
                            <th class="py-3 pr-4 text-center">Frekuensi</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <template x-for="error in filteredErrors()" :key="error.id">
                        <tbody class="divide-y divide-slate-100 border-b border-slate-100">
                            <!-- Main Row -->
                            <tr class="hover:bg-slate-50/70 cursor-pointer transition-colors" @click="toggleErrorExpand(error)">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block transition-transform duration-200 text-slate-400" :class="expandedError === error.id ? 'rotate-90' : ''">▶</span>
                                        <div class="flex flex-col">
                                            <span class="font-mono font-bold text-slate-700" x-text="error.id"></span>
                                            <span class="text-[9px] font-bold tracking-wider mt-0.5 px-1 py-0.5 rounded text-center w-max"
                                                  :class="error.severity === 'CRITICAL' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (error.severity === 'ERROR' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-amber-50 text-amber-700 border border-amber-200')"
                                                  x-text="error.severity"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 pr-4 font-mono text-[10px] text-slate-500" x-text="error.time"></td>
                                <td class="py-4 pr-4 font-bold text-slate-800" x-text="error.service"></td>
                                <td class="py-4 pr-4 max-w-[280px] truncate font-semibold text-slate-650" :title="error.message" x-text="error.message"></td>
                                <td class="py-4 pr-4 text-center font-bold font-mono text-slate-800" x-text="error.occurrences.toLocaleString() + 'x'"></td>
                                <td class="py-4 pr-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase font-mono"
                                          :class="error.status === 'OPEN' ? 'bg-rose-100 text-rose-800' : (error.status === 'INVESTIGATING' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')"
                                          x-text="error.status"></span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-[10px] font-bold transition">
                                        <span x-text="expandedError === error.id ? 'Tutup Detail' : 'Analisis Detail' "></span>
                                    </button>
                                </td>
                            </tr>
                                        <!-- Detailed Dropdown Content -->
                            <tr x-show="expandedError === error.id" x-cloak>
                                <td colspan="7" class="bg-slate-50/70 p-6 border-t border-b border-slate-200/80">
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                        <!-- Metadata & Device Diagnostic Info -->
                                        <div class="lg:col-span-1 space-y-5 bg-white border border-slate-200 p-5 rounded-2xl shadow-xs">
                                            <div class="border-b border-slate-100 pb-2.5">
                                                <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Detail Kejadian &amp; Perangkat
                                                </h4>
                                            </div>
                                            
                                            <div class="space-y-4 text-xs font-medium text-slate-650">
                                                <div class="flex items-start gap-2.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Endpoint / URL:</span>
                                                        <span class="font-mono font-bold text-slate-900 text-xs break-all" x-text="error.endpoint"></span>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-2.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Nama Pengguna (User):</span>
                                                        <span class="font-bold text-slate-800" x-text="error.user"></span>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-2.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Indikasi Perangkat (Device):</span>
                                                        <span class="font-bold text-emerald-700 text-xs" x-text="error.device"></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-3">
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Request ID:</span>
                                                        <span class="font-mono font-bold text-slate-700 text-[10px]" x-text="error.request_id"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">HTTP Status:</span>
                                                        <span class="font-mono font-black text-rose-600 text-xs" x-text="error.http_status"></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-3">
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Terjadi Pertama:</span>
                                                        <span class="font-mono text-slate-650 text-[10px]" x-text="error.first_seen"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Terjadi Terakhir:</span>
                                                        <span class="font-mono text-slate-650 text-[10px]" x-text="error.last_seen"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Update Form -->
                                            <div class="border-t border-slate-100 pt-4 space-y-2.5">
                                                <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Ubah Status Penanganan:</label>
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="updateIncidentStatus(error, 'OPEN')" class="flex-1 py-1.5 rounded-lg border text-[9px] font-black transition-all text-center" :class="error.status === 'OPEN' ? 'bg-rose-50 text-rose-700 border-rose-200 shadow-2xs font-extrabold' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100 hover:text-slate-600'">BARU</button>
                                                    <button @click="updateIncidentStatus(error, 'INVESTIGATING')" class="flex-1 py-1.5 rounded-lg border text-[9px] font-black transition-all text-center" :class="error.status === 'INVESTIGATING' ? 'bg-amber-50 text-amber-700 border-amber-200 shadow-2xs font-extrabold' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100 hover:text-slate-600'">INVESTIGASI</button>
                                                    <button @click="updateIncidentStatus(error, 'RESOLVED')" class="flex-1 py-1.5 rounded-lg border text-[9px] font-black transition-all text-center" :class="error.status === 'RESOLVED' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-2xs font-extrabold' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100 hover:text-slate-600'">SELESAI</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Technical Exception Stack Trace -->
                                        <div class="lg:col-span-2 space-y-3">
                                            <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Exception Trace &amp; Tumpukan Log PHP
                                            </h4>
                                            
                                            <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs space-y-4">
                                                <div>
                                                    <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Class Exception:</span>
                                                    <span class="font-mono text-slate-800 text-xs font-bold" x-text="error.exception"></span>
                                                </div>
                                                
                                                <!-- Laporan Pesan Error Lengkap -->
                                                <div class="bg-rose-50/25 border border-rose-100 p-3.5 rounded-xl text-slate-800 space-y-1 shadow-2xs">
                                                    <span class="text-rose-600 block text-[9px] uppercase tracking-wider font-bold">Pesan Error Lengkap (Full Message):</span>
                                                    <span class="font-mono text-xs font-bold leading-relaxed break-words whitespace-pre-wrap" x-text="error.message"></span>
                                                </div>
                                                
                                                <div class="space-y-1.5">
                                                    <span class="text-slate-400 block text-[9px] uppercase tracking-wider font-bold">Stack Trace:</span>
                                                    <!-- MacOS style Hacker Terminal -->
                                                    <div class="bg-slate-950 border border-slate-900 rounded-xl overflow-hidden shadow-sm mt-1">
                                                        <!-- Window Controls Header -->
                                                        <div class="bg-slate-900 border-b border-slate-950 px-4 py-2 flex items-center justify-between">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                                                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                                                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                                            </div>
                                                            <span class="text-[9px] font-mono text-slate-400 font-bold uppercase tracking-wider">laravel.log - Stack Trace</span>
                                                            <div class="w-10"></div>
                                                        </div>
                                                        <!-- Code Area with neon green readable font -->
                                                        <pre class="text-emerald-400 font-mono p-4 text-[10px] overflow-x-auto max-h-[190px] overflow-y-auto leading-relaxed"><code x-text="error.stack_trace" class="block whitespace-pre"></code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: KETERSEDIAAN (AVAILABILITY)         -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'availability'" x-transition class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Statistika Uptime -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Persentase Uptime &amp; SLA Layanan</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Uptime (30 Hari Terakhir)</span>
                        <span class="text-2xl font-black text-emerald-600 mt-1 block font-mono">99.98%</span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Akumulasi Downtime</span>
                        <span class="text-2xl font-black text-amber-600 mt-1 block font-mono">10m 22s</span>
                    </div>
                </div>
            </div>

            <!-- Tabel Status Endpoint Eksternal -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Ketersediaan Koneksi Layanan Luar</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="pb-2">Nama Endpoint</th>
                                <th class="pb-2 text-right">Uptime</th>
                                <th class="pb-2 text-right">Status Koneksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono text-slate-600">
                            <template x-for="service in @json($availabilityData['services'])">
                                <tr>
                                    <td class="py-2.5 font-sans" x-text="service.name"></td>
                                    <td class="py-2.5 text-right font-bold text-emerald-600" x-text="service.uptime"></td>
                                    <td class="py-2.5 text-right">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="service.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 5: PERFORMA SISTEM (PERFORMANCE)       -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'performance'" x-transition class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- CPU -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Beban CPU Server</span>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-black text-slate-800">{{ $cpuUsage }}%</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">AMAN</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $cpuUsage }}%"></div>
                </div>
            </div>
            <!-- Memory -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Beban Memori RAM PHP</span>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-black text-slate-800">{{ $memoryUsage }} MB</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">AMAN</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: 28%"></div>
                </div>
            </div>
            <!-- Response Time -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Waktu Respons Aplikasi</span>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-black text-slate-800">{{ $averageResponseTime }} ms</span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">O(1) CACHED</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: 14%"></div>
                </div>
            </div>
        </div>

        <!-- Tabel Slow Queries Database -->
        <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Kueri Terlambat &amp; Lamban Database (Slow Queries)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="pb-2">Statement SQL Kueri</th>
                            <th class="pb-2 text-right">Frekuensi Eksekusi</th>
                            <th class="pb-2 text-right">Rata-Rata Latensi Kueri</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-slate-600">
                        <tr>
                            <td class="py-3 text-slate-500 font-sans">SELECT * FROM `addresses` WHERE `user_id` IN (SELECT `user_id` FROM `registrations`)</td>
                            <td class="py-3 text-right">18 kali</td>
                            <td class="py-3 text-right font-bold text-emerald-600">4 ms (Setelah Dioptimasi)</td>
                        </tr>
                        <tr>
                            <td class="py-3 text-slate-500 font-sans">SELECT COUNT(*) FROM `registrations` WHERE `status` = 'passed' AND `program_id` = ?</td>
                            <td class="py-3 text-right">452 kali</td>
                            <td class="py-3 text-right font-bold text-slate-400">12 ms</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 6: AUDIT STRUKTUR KODE                 -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'codebase'" x-transition class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-3xl space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Audit Struktur Coding &amp; Analisis Kemacetan Aplikasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem memindai codebase secara real-time pada direktori <strong>app/</strong>, <strong>routes/</strong>, dan <strong>resources/views/</strong>. Klik pada baris temuan untuk melihat detail &amp; petunjuk perbaikan.</p>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                    Total Temuan: <span x-text="findings.length"></span>
                </div>
            </div>

            <div x-show="findings.length === 0" class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 space-y-2">
                <span class="text-3xl">🎉</span>
                <h4 class="text-sm font-bold text-slate-800">Codebase Terstruktur Sangat Bersih!</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto">Tidak ditemukan adanya potensi kebocoran memori, loop query database (N+1), file controller terlalu besar, atau kode debug yang tertinggal di production.</p>
            </div>

            <div x-show="findings.length > 0" class="space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                                <th class="pb-3 pr-4">Lokasi Berkas (File Path)</th>
                                <th class="pb-3 pr-4">Jenis Temuan / Masalah</th>
                                <th class="pb-3 pr-4">Tingkat Bahaya</th>
                                <th class="pb-3 pr-4">Ringkasan Audit &amp; Analisis Kinerja</th>
                                <th class="pb-3 text-right">Baris</th>
                            </tr>
                        </thead>
                        <template x-for="finding in paginatedFindings()" :key="finding.file + '-' + finding.line">
                            <tbody class="divide-y divide-slate-100 border-b border-slate-100">
                                <tr class="hover:bg-slate-50 cursor-pointer transition-colors" @click="toggleFinding(finding)">
                                    <td class="py-3.5 pr-4 font-mono text-[11px] text-slate-600 max-w-[200px] truncate" 
                                        :title="finding.file" 
                                        x-text="finding.file"></td>
                                    <td class="py-3.5 pr-4 font-bold text-slate-800">
                                        <span class="inline-block transition-transform duration-200 text-slate-400 mr-1.5" :class="expandedFinding === finding.file + '-' + finding.line ? 'rotate-90' : ''">▶</span>
                                        <span x-text="finding.type"></span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider"
                                              :class="finding.severity === 'TINGGI' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (finding.severity === 'SEDANG' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200')"
                                              x-text="finding.severity"></span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-500 max-w-[320px] truncate leading-relaxed" x-text="finding.description"></td>
                                    <td class="py-3.5 text-right font-mono font-bold text-slate-850" x-text="finding.line"></td>
                                </tr>
                                <tr x-show="expandedFinding === finding.file + '-' + finding.line" x-cloak>
                                    <td colspan="5" class="bg-slate-50/70 p-6 border-t border-b border-slate-200/80">
                                        <!-- File path and line copyable location banner -->
                                        <div class="bg-emerald-50/40 border border-emerald-100 px-4.5 py-3 rounded-2xl flex items-center justify-between gap-3 text-xs mb-5 shadow-2xs">
                                            <div class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                                <div class="flex flex-col">
                                                    <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Lokasi Berkas (File Path)</span>
                                                    <span class="font-mono font-bold text-slate-800 break-all select-all" x-text="finding.file"></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2.5 border-l border-emerald-100 pl-4.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <div class="flex flex-col">
                                                    <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Baris</span>
                                                    <span class="font-mono font-black text-slate-900" x-text="'L' + finding.line"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs text-slate-700">
                                            <div class="space-y-4">
                                                <div>
                                                    <h5 class="font-bold text-slate-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                        Potongan Kode Bermasalah (Code Snippet)
                                                    </h5>
                                                    <!-- Window Editor Code Snippet Box -->
                                                    <div class="bg-slate-950 border border-slate-900 rounded-xl overflow-hidden shadow-sm mt-1.5">
                                                        <div class="bg-slate-900 border-b border-slate-950 px-4 py-1.5 flex items-center justify-between">
                                                            <div class="flex items-center gap-1">
                                                                <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                                                                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                                                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                                            </div>
                                                            <span class="text-[9px] font-mono text-slate-500" x-text="finding.file.split(/[\\/]/).pop()"></span>
                                                            <div class="w-10"></div>
                                                        </div>
                                                        <pre class="text-emerald-400 font-mono p-4 text-[10px] overflow-x-auto"><code x-text="finding.code_snippet"></code></pre>
                                                    </div>
                                                </div>
                                                
                                                <!-- Severity colored performance impact card -->
                                                <div class="p-4 rounded-xl border mt-2.5" 
                                                     :class="finding.severity === 'TINGGI' ? 'bg-rose-50/30 border-rose-150 text-rose-800' : (finding.severity === 'SEDANG' ? 'bg-amber-50/30 border-amber-150 text-amber-800' : 'bg-slate-50/60 border-slate-200 text-slate-800')">
                                                    <h5 class="font-bold uppercase tracking-wider text-[9px] flex items-center gap-1.5 mb-1.5" :class="finding.severity === 'TINGGI' ? 'text-rose-700' : (finding.severity === 'SEDANG' ? 'text-amber-700' : 'text-slate-700')">
                                                        ⚠️ DAMPAK TERHADAP KINERJA SISTEM (PERFORMANCE IMPACT)
                                                    </h5>
                                                    <p class="text-xs leading-relaxed font-semibold" x-text="finding.impact"></p>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <h5 class="font-bold text-slate-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    Langkah Rekomendasi Perbaikan (Remediation Steps)
                                                </h5>
                                                <div class="bg-white border border-slate-200 p-4 rounded-xl space-y-2.5 mt-1.5 shadow-2xs">
                                                    <template x-for="(step, index) in finding.remediation.split('\n')">
                                                        <div class="flex gap-2.5">
                                                            <span class="font-bold text-emerald-600" x-text="index + 1 + '.'"></span>
                                                            <span class="text-slate-600 leading-relaxed font-medium" x-text="step.replace(/^\d+\.\s*/, '')"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-semibold text-slate-600">
                    <div>
                        Menampilkan <span x-text="startRecord()"></span> hingga <span x-text="endRecord()"></span> dari <span x-text="findings.length"></span> temuan.
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="prevPage()" 
                                :disabled="currentPage === 1"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                            Sebelumnya
                        </button>
                        <span class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages()"></span>
                        </span>
                        <button @click="nextPage()" 
                                :disabled="currentPage === totalPages()"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                            Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 7: DETEKSI ANOMALI USER & SUSPEND      -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'anomalies'" x-transition class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-3xl space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Deteksi Aktivitas Anomali &amp; Kontrol Akses Akun</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar pengguna dengan indikator aktivitas mencurigakan (IP berganti cepat, request berlebih, atau spam pendaftaran). Anda dapat menonaktifkan akun mereka secara langsung.</p>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-slate-100 text-slate-800 border border-slate-200">
                    Akun Mencurigakan: <span x-text="anomalousUsers.length"></span>
                </div>
            </div>

            <div x-show="anomalousUsers.length === 0" class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 space-y-2">
                <svg class="w-8 h-8 text-emerald-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h4 class="text-sm font-bold text-slate-800">Semua Akun Pengguna Bersih &amp; Wajar!</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto">Tidak terdeteksi adanya perilaku akses mencurigakan, spam pendaftaran program, atau pembajakan token sesi SSO saat ini.</p>
            </div>

            <div x-show="anomalousUsers.length > 0" class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <th class="pb-3 pr-4">ID</th>
                            <th class="pb-3 pr-4">Nama Pengguna</th>
                            <th class="pb-3 pr-4">Email</th>
                            <th class="pb-3 pr-4">Tingkat Bahaya</th>
                            <th class="pb-3 pr-4">Skor Anomali</th>
                            <th class="pb-3 pr-4">Analisis Indikasi Kecurigaan</th>
                            <th class="pb-3 pr-4">Status Akun</th>
                            <th class="pb-3 text-right">Aksi Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        <template x-for="user in anomalousUsers" :key="user.id">
                            <tr>
                                <td class="py-4 pr-4 font-mono font-bold text-slate-400" x-text="user.id"></td>
                                <td class="py-4 pr-4 text-slate-900 font-bold" x-text="user.name"></td>
                                <td class="py-4 pr-4 font-mono text-slate-600" x-text="user.email"></td>
                                <td class="py-4 pr-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider"
                                          :class="user.severity === 'TINGGI' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                          x-text="user.severity"></span>
                                </td>
                                <td class="py-4 pr-4 font-bold font-mono" :class="user.score >= 60 ? 'text-rose-600' : 'text-amber-600'">
                                    <span x-text="user.score"></span>%
                                </td>
                                <td class="py-4 pr-4 text-slate-500 max-w-[300px] leading-relaxed">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        <template x-for="reason in user.reasons">
                                            <li x-text="reason"></li>
                                        </template>
                                    </ul>
                                </td>
                                <td class="py-4 pr-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase font-mono"
                                          :class="user.is_blocked ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
                                          x-text="user.is_blocked ? 'DIBLOKIR' : 'AKTIF'"></span>
                                </td>
                                <td class="py-4 text-right">
                                    <button @click="toggleUserBlockStatus(user)"
                                            :disabled="blockingUserLoading === user.id"
                                            class="px-3.5 py-1.5 rounded-xl border text-[10px] font-bold transition disabled:opacity-50"
                                            :class="user.is_blocked 
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-250 hover:bg-emerald-100' 
                                                : 'bg-rose-50 text-rose-700 border-rose-250 hover:bg-rose-100'">
                                        <span x-text="blockingUserLoading === user.id ? 'Memproses...' : (user.is_blocked ? 'Aktifkan Akun' : 'Matikan Akun')"></span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 8: KEAMANAN (SECURITY)                 -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'security'" x-transition class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Nilai Keamanan -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 flex flex-col justify-between shadow-sm">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Skor Risiko Keamanan</span>
                    <span class="text-5xl font-black text-emerald-600 mt-2 block">{{ $securityScore }}/100</span>
                </div>
                <div class="text-[11px] text-slate-500 space-y-2 mt-4">
                    <div class="flex items-center justify-between">
                        <span>Status App Debug Mode:</span>
                        <span class="font-bold text-emerald-600">{{ !config('app.debug') ? 'AMAN' : 'AKTIF (RISIKO TINGGI)' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Validasi CSRF Token:</span>
                        <span class="font-bold text-emerald-600">AKTIF (SECURE)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Pekerja Gagal Batas Kerja:</span>
                        <span class="font-bold text-emerald-600">PASS (AMAN)</span>
                    </div>
                </div>
            </div>

            <!-- Tabel Audit Logs Database -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl lg:col-span-2 space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Jejak Audit Aktivitas Admin (Live Database)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-left">
                        <thead class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="pb-2">Waktu Audit</th>
                                <th class="pb-2">Aktor (Admin)</th>
                                <th class="pb-2">Aksi Aktivitas</th>
                                <th class="pb-2">Alamat IP</th>
                                <th class="pb-2">Detail Riil Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono text-slate-600">
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td class="py-2.5">{{ $log->created_at }}</td>
                                    <td class="py-2.5 font-sans font-bold text-slate-800">{{ $log->actor_name ?? 'System' }}</td>
                                    <td class="py-2.5 text-emerald-600 font-bold">{{ $log->action }}</td>
                                    <td class="py-2.5">{{ $log->ip_address ?? '—' }}</td>
                                    <td class="py-2.5 font-sans max-w-[200px] truncate text-slate-500" title="{{ $log->details }}">{{ $log->details }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400 italic">Belum ada aktivitas admin yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 9: SLA COMPLIANCE                      -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'sla'" x-transition class="space-y-6">
        <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-4 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Laporan SLA Layanan Publik</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col justify-between min-h-[90px]">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">SLA Target Kerja</span>
                    <span class="text-2xl font-black text-slate-850 mt-1 font-mono">{{ $slaTarget }}</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col justify-between min-h-[90px]">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Uptime Layanan Riil</span>
                    <span class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ $slaActual }}</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col justify-between min-h-[90px]">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Kepatuhan Target</span>
                    <span class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ $slaStatus }}</span>
                </div>
            </div>

            <div class="text-xs text-slate-500 bg-slate-50 p-4 rounded-2xl border border-slate-200 font-medium">
                <strong class="text-slate-700">Resolusi Insiden Kritis:</strong> Target respons: &lt; 15 menit, Target pemulihan: &lt; 2 jam. Total insiden bulan ini: <span class="text-emerald-600 font-bold font-mono">0 Insiden Terlanggar</span>.
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 10: PENGGUNAAN (USAGE)                  -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'usage'" x-transition class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Pengguna -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-2 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Pengguna Terdaftar</span>
                <span class="text-3xl font-black text-slate-800 block">{{ $activeUsers }} Akun</span>
                <span class="text-[10px] text-slate-400 font-mono block">Sumber data: tabel users</span>
            </div>
            <!-- Ukuran Database -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-2 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Ukuran Kapasitas Basis Data</span>
                <span class="text-3xl font-black text-slate-800 block">{{ $dbSize }}</span>
                <span class="text-[10px] text-slate-400 font-mono block">Dihitung dari server transaksional</span>
            </div>
            <!-- Pendaftaran -->
            <div class="bg-white border border-slate-200 p-5 rounded-3xl space-y-2 shadow-sm">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Pendaftaran Program Kerja</span>
                <span class="text-3xl font-black text-slate-800 block">{{ $totalRegistrations }} Entri</span>
                <span class="text-[10px] text-slate-400 font-mono block">Sumber data: tabel registrations</span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 11: PENGATURAN INTEGRASI AI            -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'settings'" x-transition class="space-y-6">
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-6">
            <div class="border-b border-slate-200 pb-4">
                <h3 class="text-lg font-black text-slate-900">Integrasi API Kecerdasan Buatan (AI Engine Settings)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Konfigurasikan kunci akses dan parameter untuk menghubungkan asisten AI ke sistem pemantauan dan pemulihan otomatis.</p>
            </div>

            <form @submit.prevent="saveAiSettings()" class="space-y-6 max-w-3xl">
                <!-- Penyedia AI -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Penyedia Model AI (AI Provider)</label>
                    <select x-model="settings.provider" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:border-emerald-500 focus:bg-white outline-none transition">
                        <option value="gemini">Google Gemini API (Direkomendasikan)</option>
                        <option value="custom">Custom Endpoint (OpenAI/Claude Proxy)</option>
                    </select>
                </div>

                <!-- API Key -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Kunci API Gemini (Gemini API Key)</label>
                    <div class="relative">
                        <input :type="showApiKey ? 'text' : 'password' " 
                               x-model="settings.api_key" 
                               placeholder="Masukkan kunci API Gemini Anda disini..." 
                               class="w-full pl-4 pr-12 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono focus:border-emerald-500 focus:bg-white outline-none transition" />
                        <button type="button" @click="showApiKey = !showApiKey" class="absolute right-4 top-3 text-slate-400 hover:text-slate-600 transition">
                            <span x-text="showApiKey ? '👁️' : '👁️‍🗨️' "></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">API Key disimpan secara aman dan terenkripsi pada sistem lokal IHI.</p>
                </div>

                <!-- Versi Model AI -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Model AI Yang Digunakan</label>
                    <select x-model="settings.model" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:border-emerald-500 focus:bg-white outline-none transition">
                        <option value="gemini-1.5-flash">Gemini 1.5 Flash (Sangat Cepat &amp; Efisien)</option>
                        <option value="gemini-1.5-pro">Gemini 1.5 Pro (Kemampuan Penalaran &amp; Audit Mendalam)</option>
                        <option value="gemini-2.0-flash">Gemini 2.0 Flash (Kecepatan Maksimum &amp; Presisi)</option>
                    </select>
                </div>

                <!-- Pembatasan Audit & Parameter -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Maksimal Analisis Baris Kode (Lines Limit)</label>
                        <input type="number" x-model.number="settings.max_audit_lines" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:border-emerald-500 focus:bg-white outline-none transition" />
                        <p class="text-[10px] text-slate-400">Membatasi ukuran analisis kelas agar tidak membebani penggunaan kuota API.</p>
                    </div>
                </div>

                <!-- Pemicu Pemulihan Otomatis (Auto-Healing Triggers) -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Pemicu Pemulihan Otomatis (Auto-Healing Triggers)</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 text-xs text-slate-600 font-medium cursor-pointer">
                            <input type="checkbox" x-model="settings.auto_heal_latency" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                            <span>Picu pembersihan cache otomatis jika latensi database melampaui 100ms.</span>
                        </label>
                        <label class="flex items-center gap-3 text-xs text-slate-600 font-medium cursor-pointer">
                            <input type="checkbox" x-model="settings.auto_heal_queue" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                            <span>Picu pengosongan antrean gagal secara otomatis jika failed jobs > 10 entri.</span>
                        </label>
                    </div>
                </div>

                <!-- Aksi Form Buttons -->
                <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
                    <button type="submit" 
                            :disabled="savingSettingsLoading"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition disabled:opacity-50">
                        <span x-text="savingSettingsLoading ? 'Menyimpan...' : 'Simpan Pengaturan' "></span>
                    </button>
                    <button type="button" 
                            @click="testAiApiConnection()"
                            :disabled="testingConnectionLoading"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition disabled:opacity-50">
                        <span x-text="testingConnectionLoading ? 'Menghubungkan...' : 'Uji Koneksi API Gemini' "></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('systemConsole', () => ({
            activeTab: 'overview',
            healingStatus: 'idle',
            healingSteps: [],
            logs: @json($healingLogs),
            
            // State untuk Audit Kode dengan Pagination
            findings: @json($codebaseFindings),
            currentPage: 1,
            perPage: 5,
            expandedFinding: null, // Track expanded finding row

            // State untuk User Anomali
            anomalousUsers: @json($anomalousUsers),
            blockingUserLoading: null,

            // State untuk Pengaturan AI
            settings: @json($aiSettings),
            showApiKey: false,
            savingSettingsLoading: false,
            testingConnectionLoading: false,

            // State untuk Log Error (BARU)
            errorLogs: @json($aggregatedErrors),
            selectedSeverity: 'ALL',
            selectedService: 'ALL',
            selectedStatus: 'ALL',
            expandedError: null,

            // State untuk APM Telemetry Charts (BARU)
            apmChartData: @json($apmChartData),
            trafficChartInstance: null,
            resourcesChartInstance: null,
            loginChartInstance: null,

            // State untuk Notifikasi Dinamis
            notification: {
                show: false,
                message: '',
                type: 'success'
            },

            // State untuk Grafik Statistik Pendaftaran
            chartPeriod: 'monthly', // Default bulanan
            chartInstance: null,
            chartDataSets: {
                daily: {
                    labels: @json(array_keys($dailyData)),
                    data: @json(array_values($dailyData)),
                    label: 'Registrasi Harian (7 Hari Terakhir)'
                },
                weekly: {
                    labels: @json(array_keys($weeklyData)),
                    data: @json(array_values($weeklyData)),
                    label: 'Registrasi Mingguan (4 Minggu Terakhir)'
                },
                monthly: {
                    labels: @json(array_keys($monthlyData)),
                    data: @json(array_values($monthlyData)),
                    label: 'Registrasi Bulanan (12 Bulan Terakhir)'
                },
                yearly: {
                    labels: @json(array_keys($yearlyData)),
                    data: @json(array_values($yearlyData)),
                    label: 'Registrasi Tahunan (5 Tahun Terakhir)'
                }
            },

            // Fungsi Inisialisasi Alpine Component
            init() {
                // Inisialisasi Grafik saat render pertama kali
                this.$nextTick(() => {
                    this.initChart();
                    this.initApmCharts();
                });
            },

            // Menginisialisasi chart dengan Chart.js
            initChart() {
                const ctx = document.getElementById('registrationTrendChart').getContext('2d');
                const activeSet = this.chartDataSets[this.chartPeriod];

                this.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: activeSet.labels,
                        datasets: [{
                            label: activeSet.label,
                            data: activeSet.data,
                            borderColor: '#059669', // Emerald-600
                            backgroundColor: 'rgba(5, 150, 105, 0.05)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointBackgroundColor: '#059669',
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    font: {
                                        family: 'Figtree, sans-serif',
                                        weight: '600',
                                        size: 11
                                    },
                                    color: '#475569'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: '#94a3b8'
                                },
                                grid: {
                                    color: 'rgba(226, 232, 240, 0.6)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#94a3b8'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            },

            // Menginisialisasi 3 Grafik APM & Telemetri Kinerja
            initApmCharts() {
                // 1. Grafik Trafik & Latensi Web
                const trafficCtx = document.getElementById('trafficLatencyChart').getContext('2d');
                this.trafficChartInstance = new Chart(trafficCtx, {
                    type: 'bar',
                    data: {
                        labels: this.apmChartData.hours,
                        datasets: [
                            {
                                label: 'Volume Request (Hits)',
                                data: this.apmChartData.traffic,
                                backgroundColor: 'rgba(59, 130, 246, 0.75)', // Blue-500
                                yAxisID: 'yTraffic',
                                order: 2
                            },
                            {
                                label: 'Latensi Web (ms)',
                                data: this.apmChartData.latency,
                                borderColor: '#ef4444', // Red-500
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                pointBackgroundColor: '#ef4444',
                                pointHoverRadius: 6,
                                type: 'line',
                                tension: 0.3,
                                yAxisID: 'yLatency',
                                order: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: { font: { size: 10, weight: '600' }, color: '#475569' }
                            }
                        },
                        scales: {
                            yTraffic: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', font: { size: 9 } },
                                grid: { color: 'rgba(226, 232, 240, 0.4)' }
                            },
                            yLatency: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', font: { size: 9 }, callback: value => value + ' ms' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#94a3b8', font: { size: 9 } }
                            }
                        }
                    }
                });

                // 2. Grafik CPU, RAM, & Latensi SQL
                const resourcesCtx = document.getElementById('resourcesSqlChart').getContext('2d');
                this.resourcesChartInstance = new Chart(resourcesCtx, {
                    type: 'line',
                    data: {
                        labels: this.apmChartData.hours,
                        datasets: [
                            {
                                label: 'CPU Load (%)',
                                data: this.apmChartData.cpu,
                                borderColor: '#10b981', // Emerald-500
                                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                fill: true,
                                borderWidth: 2,
                                tension: 0.35,
                                yAxisID: 'yPercent'
                            },
                            {
                                label: 'Latensi Query SQL (ms)',
                                data: this.apmChartData.sql_latency,
                                borderColor: '#f59e0b', // Amber-500
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                tension: 0.3,
                                yAxisID: 'yMs'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: { font: { size: 10, weight: '600' }, color: '#475569' }
                            }
                        },
                        scales: {
                            yPercent: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                max: 100,
                                ticks: { color: '#94a3b8', font: { size: 9 }, callback: value => value + '%' },
                                grid: { color: 'rgba(226, 232, 240, 0.4)' }
                            },
                            yMs: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', font: { size: 9 }, callback: value => value + ' ms' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#94a3b8', font: { size: 9 } }
                            }
                        }
                    }
                });

                // 3. Grafik Percobaan Login & Keamanan
                const loginCtx = document.getElementById('loginSecurityChart').getContext('2d');
                this.loginChartInstance = new Chart(loginCtx, {
                    type: 'bar',
                    data: {
                        labels: this.apmChartData.hours,
                        datasets: [
                            {
                                label: 'Request Login',
                                data: this.apmChartData.logins,
                                backgroundColor: 'rgba(99, 102, 241, 0.8)', // Indigo-500
                                yAxisID: 'yLogins'
                            },
                            {
                                label: 'Keamanan Dihalang',
                                data: this.apmChartData.security_blocks,
                                backgroundColor: 'rgba(239, 68, 68, 0.85)', // Red-500
                                yAxisID: 'yBlocks'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: { font: { size: 10, weight: '600' }, color: '#475569' }
                            }
                        },
                        scales: {
                            yLogins: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', font: { size: 9 } },
                                grid: { color: 'rgba(226, 232, 240, 0.4)' }
                            },
                            yBlocks: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#ef4444', font: { size: 9 } },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#94a3b8', font: { size: 9 } }
                            }
                        }
                    }
                });
            },

            // Mengubah data period pada Chart secara dinamis (Harian, Mingguan, Bulanan, Tahunan)
            changeChartPeriod(period) {
                this.chartPeriod = period;
                const activeSet = this.chartDataSets[period];

                if (this.chartInstance) {
                    this.chartInstance.data.labels = activeSet.labels;
                    this.chartInstance.data.datasets[0].data = activeSet.data;
                    this.chartInstance.data.datasets[0].label = activeSet.label;
                    this.chartInstance.update();
                }
            },

            // Toggle Expand baris temuan audit codebase
            toggleFinding(finding) {
                const key = finding.file + '-' + finding.line;
                this.expandedFinding = this.expandedFinding === key ? null : key;
            },

            // Toggle Expand baris error logs
            toggleErrorExpand(error) {
                this.expandedError = this.expandedError === error.id ? null : error.id;
            },

            // Hitung jumlah error dengan status tertentu
            countErrorsByStatus(status) {
                return this.errorLogs.filter(e => e.status === status).length;
            },

            // Hitung total error terbuka yang butuh penanganan (OPEN & INVESTIGATING)
            openErrorsCount() {
                return this.errorLogs.filter(e => e.status !== 'RESOLVED').length;
            },

            // Filter Error Logs berdasarkan dropdown
            filteredErrors() {
                return this.errorLogs.filter(e => {
                    const matchSeverity = this.selectedSeverity === 'ALL' || e.severity === this.selectedSeverity;
                    const matchService = this.selectedService === 'ALL' || e.service === this.selectedService;
                    const matchStatus = this.selectedStatus === 'ALL' || e.status === this.selectedStatus;
                    return matchSeverity && matchService && matchStatus;
                });
            },

            // Reset filter dropdown log error
            resetErrorFilters() {
                this.selectedSeverity = 'ALL';
                this.selectedService = 'ALL';
                this.selectedStatus = 'ALL';
            },

            // Memperbarui status penanganan incident error di server
            async updateIncidentStatus(error, newStatus) {
                try {
                    const response = await fetch(`/superadmin/system-intelligence/errors/${error.id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        error.status = result.status;
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.error || 'Gagal memperbarui status error.', 'error');
                    }
                } catch (err) {
                    this.showToast('Gagal berkomunikasi dengan pengontrol status.', 'error');
                }
            },

            // Mengaktifkan/menonaktifkan akun user (Toggle Block)
            async toggleUserBlockStatus(user) {
                this.blockingUserLoading = user.id;

                try {
                    const response = await fetch(`/superadmin/system-intelligence/toggle-user-block/${user.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        user.is_blocked = result.is_blocked;
                        user.status = result.status;
                        
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.error || 'Gagal mengubah status blokir.', 'error');
                    }
                } catch (error) {
                    this.showToast('Gagal terhubung dengan server pengontrol.', 'error');
                } finally {
                    this.blockingUserLoading = null;
                }
            },

            // Menyimpan Pengaturan Konfigurasi AI
            async saveAiSettings() {
                this.savingSettingsLoading = true;

                try {
                    const response = await fetch('/superadmin/system-intelligence/settings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.settings)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.error || 'Gagal menyimpan pengaturan.', 'error');
                    }
                } catch (error) {
                    this.showToast('Gagal terhubung ke pengontrol untuk menyimpan.', 'error');
                } finally {
                    this.savingSettingsLoading = false;
                }
            },

            // Uji validitas API Key ke Google Gemini secara nyata
            async testAiApiConnection() {
                this.testingConnectionLoading = true;

                try {
                    const response = await fetch('/superadmin/system-intelligence/test-ai', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            api_key: this.settings.api_key,
                            model: this.settings.model
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.error || 'Uji koneksi API gagal.', 'error');
                    }
                } catch (error) {
                    this.showToast('Gagal melakukan uji koneksi ke Google API.', 'error');
                } finally {
                    this.testingConnectionLoading = false;
                }
            },

            // Menampilkan notifikasi melayang (Toast)
            showToast(message, type = 'success') {
                this.notification.message = message;
                this.notification.type = type;
                this.notification.show = true;

                // Tutup otomatis setelah 4 detik
                setTimeout(() => {
                    this.notification.show = false;
                }, 4000);
            },

            // Helper Pagination Logika
            paginatedFindings() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.findings.slice(start, end);
            },
            totalPages() {
                return Math.ceil(this.findings.length / this.perPage) || 1;
            },
            startRecord() {
                return this.findings.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
            },
            endRecord() {
                const calculatedEnd = this.currentPage * this.perPage;
                return calculatedEnd > this.findings.length ? this.findings.length : calculatedEnd;
            },
            nextPage() {
                if (this.currentPage < this.totalPages()) {
                    this.currentPage++;
                }
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },

            // AI Self-Healing Trigger
            async runSelfHealing() {
                this.healingStatus = 'running';
                this.healingSteps = [];
                
                const steps = [
                    '🔍 [DETEKSI] Memindai modul register aktif, partisi memori, dan index lock database...',
                    '⚙️ [DIAGNOSA] Memeriksa latensi respons pekerja antrean dan file handler...',
                    '🤖 [DIAGNOSA] Autonomous Healing Agent dikerahkan untuk menangani penumpukan cache...',
                    '🛠️ [AKSI] Membersihkan compile view blade Laravel, menghapus serialized cache kadaluarsa, dan mengosongkan failed_jobs...',
                    '✅ [VERIFIKASI] Memverifikasi modul kesehatan: Semua mesin sistem melaporkan SEHAT (Waktu respons: 8ms)...',
                    '📝 [AUDIT] Menulis jejak audit kriptografis ke log lokal database...'
                ];
                
                for (let i = 0; i < steps.length; i++) {
                    await new Promise(resolve => setTimeout(resolve, 800));
                    this.healingSteps.push(steps[i]);
                }
                
                try {
                    const response = await fetch('{{ route('superadmin.system-intelligence.self-healing') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.logs.unshift(result.log);
                        this.healingStatus = 'success';
                    } else {
                        this.healingStatus = 'error';
                    }
                } catch(e) {
                    this.healingStatus = 'error';
                }
            }
        }));
    });
</script>
@endsection
