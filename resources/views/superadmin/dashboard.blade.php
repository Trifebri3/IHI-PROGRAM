@extends('superadmin.layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="p-8 mb-8 text-white shadow-xl bg-gradient-to-br from-emerald-800 via-green-700 to-emerald-600 rounded-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 right-20 w-24 h-24 bg-emerald-300 opacity-20 rounded-full blur-xl"></div>

        <div class="relative z-10">
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Selamat Datang, Super Admin!</h1>
            <p class="mt-2 text-emerald-100 sm:text-lg max-w-2xl">
                Ini adalah pusat komando ekosistem digital. Anda memiliki otoritas penuh untuk mengelola konfigurasi sistem, program, dan identitas pengguna.
            </p>
        </div>
    </div>

    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Akses Cepat Modul Sistem</h2>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

        <a href="{{ route('superadmin.form-builder.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Manajemen Biodata</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Atur formulir dinamis dan kelengkapan data wajib bagi seluruh peserta.</p>
        </a>


        <a href="{{ route('superadmin.events.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <span class="text-xl shrink-0">🚀</span>
            </div>
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700 font-sans">Manajemen Event</h3>
                @php $eventCount = \App\Models\Event::count(); @endphp
                <span class="text-[9px] font-bold bg-slate-50 text-slate-600 px-2 py-0.5 rounded-md border border-slate-250">
                    {{ $eventCount }} Event
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 flex-1">Publikasikan agenda kegiatan, kelola registrasi peserta, dan pencatatan absensi.</p>
        </a>


        <a href="{{ route('superadmin.programs.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Master Program</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Buat program baru dan delegasikan otoritas pengelola ke Admin Program.</p>
        </a>



                <a href="{{ route('iklan.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Iklan</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Kelola iklan untuk menampilkan informasi penting kepada pengguna.</p>
        </a>





        <a href="{{ route('superadmin.verifications.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700">Verifikasi Akun</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Tinjau dokumen KYC peserta dan berikan status lencana Centang Biru.</p>
        </a>

        <a href="{{ route('superadmin.announcements.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-700 group-hover:bg-emerald-700 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Pusat Pengumuman</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Siarkan instruksi darurat global atau kelola maklumat kustom per program kerja.</p>
        </a>

        <a href="{{ route('superadmin.users.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">SSO & API Gateway</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Kelola OAuth2 Clients untuk integrasi sistem kampus (LMS, Moodle, Portal).</p>
            <span class="absolute top-4 right-4 px-2 py-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 rounded-full">Segera</span>
        </a>

        <a href="{{ route('superadmin.power-panel.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-amber-100 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <span class="text-xl">⚡</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Super Power Panel</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Generator Akun Dummy massal, Import Akun Excel/CSV, dan Pendaftaran Paksa pendaftar ke program kerja.</p>
        </a>

    </div>

    <!-- PROGRAM FILTER DROPDOWN -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 mt-10">
        <div>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">🎯 Filter Analisis & Data Registrasi</h3>
            <p class="text-xs text-slate-400 mt-0.5">Pilih program kerja di bawah untuk melihat statistik kelulusan & tabel pendaftar secara rinci.</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="programFilterSelect" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition w-full md:w-[280px]">
                <option value="all">📊 Semua Program (Global)</option>
                @foreach($programsList as $p)
                    <option value="{{ $p->id }}">🎓 {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- STATS COUNTERS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengguna</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalUsers }}</h3>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl">👤</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Program</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalPrograms }}</h3>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl">🎓</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendaftaran</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalRegistrations }}</h3>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl">📝</div>
        </div>
    </div>

    <!-- PROGRAM SPECIFIC EXTENDED STATS (Hidden on "all", visible on specific program) -->
    <div id="extendedStatsSection" class="hidden grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pendaftaran Masuk</p>
                <h3 id="ext-stat-total" class="text-2xl font-black text-slate-800 mt-1">0</h3>
            </div>
            <div class="bg-blue-50 text-blue-600 p-3 rounded-lg text-sm">📥</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lolos Seleksi (Passed)</p>
                <h3 id="ext-stat-passed" class="text-2xl font-black text-emerald-700 mt-1">0</h3>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-3 rounded-lg text-sm">✅</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Draft / Belum Kirim</p>
                <h3 id="ext-stat-draft" class="text-2xl font-black text-amber-600 mt-1">0</h3>
            </div>
            <div class="bg-amber-50 text-amber-600 p-3 rounded-lg text-sm">📋</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gugur Seleksi (Failed)</p>
                <h3 id="ext-stat-failed" class="text-2xl font-black text-rose-600 mt-1">0</h3>
            </div>
            <div class="bg-rose-50 text-rose-600 p-3 rounded-lg text-sm">❌</div>
        </div>
    </div>

    <!-- PERFORMANCE CHART & AUDIT LOGS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-8">
        
        <!-- Graph (Lg: col-span-7) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 tracking-tight">Grafik Performa Program</h3>
                <p class="text-xs text-slate-400 mt-0.5">Perbandingan jumlah pendaftar dan lulusan yang menjadi alumni.</p>
            </div>
            <div class="relative h-72">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Audit Log Trails (Lg: col-span-5) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 tracking-tight">Audit Trail / Log Aktivitas</h3>
                <p class="text-xs text-slate-400 mt-0.5">Catatan tindakan administratif terbaru pada sistem.</p>
            </div>
            <div class="grow overflow-y-auto max-h-72 space-y-3 pr-2">
                @forelse($recentLogs as $log)
                    <div class="p-3 bg-slate-50/70 border border-slate-100 rounded-xl text-xs space-y-1.5 transition-hover hover:bg-slate-50">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-700">{{ $log->user->name ?? 'System' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 font-medium">{{ $log->details }}</p>
                        <div class="flex items-center justify-between text-[9px] text-slate-400">
                            <span>Tindakan: 
                                <span class="px-1.5 py-0.5 font-bold rounded-md bg-slate-200 text-slate-700 uppercase tracking-wider text-[8px]">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </span>
                            <span class="font-mono">IP: {{ $log->ip_address }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-xs italic text-center py-10">Belum ada log aktivitas admin tercatat.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- DETAIL PENDAFTARAN PROGRAM TABLE -->
    <div id="participantTableSection" class="hidden bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mt-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 id="tableProgramTitle" class="text-base font-black text-slate-800 tracking-tight">Daftar Pendaftar Program</h3>
                <p class="text-xs text-slate-400 mt-0.5">Berikut adalah detail peserta yang terdaftar pada program kerja yang dipilih.</p>
            </div>
            <!-- Search & Filter Status -->
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" id="participantSearchInput" placeholder="Cari nama atau email..." class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none w-full sm:w-[220px]">
                <select id="statusFilterSelect" class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none w-full sm:w-[150px]">
                    <option value="all">Semua Status</option>
                    <option value="passed">Lolos (Passed)</option>
                    <option value="draft">Draf (Draft/Process)</option>
                    <option value="failed">Gugur (Failed)</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4">Nama Peserta</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Wilayah (Provinsi / Kabupaten)</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody id="participantTableBody" class="divide-y divide-slate-100 text-slate-600 font-medium">
                    <!-- Row templates filled by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination controls -->
        <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
            <span id="paginationInfo">Menampilkan 0 - 0 dari 0 data</span>
            <div class="flex gap-2">
                <button id="btnPrevPage" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg font-bold text-slate-600 disabled:opacity-50 transition" disabled>Sebelumnya</button>
                <button id="btnNextPage" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg font-bold text-slate-600 disabled:opacity-50 transition" disabled>Selanjutnya</button>
            </div>
        </div>
    </div>

    <!-- Chart JS Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            label: 'Pendaftar',
                            data: {!! json_encode($chartRegistrations) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1.5,
                            borderRadius: 6
                        },
                        {
                            label: 'Lulus (Alumni)',
                            data: {!! json_encode($chartPassed) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1.5,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });

            // ==========================================
            // LOGIC FOR INTERACTIVE PROGRAM FILTER AJAX
            // ==========================================
            const programFilterSelect = document.getElementById('programFilterSelect');
            const extendedStatsSection = document.getElementById('extendedStatsSection');
            const participantTableSection = document.getElementById('participantTableSection');
            const tableProgramTitle = document.getElementById('tableProgramTitle');
            
            const extStatTotal = document.getElementById('ext-stat-total');
            const extStatPassed = document.getElementById('ext-stat-passed');
            const extStatDraft = document.getElementById('ext-stat-draft');
            const extStatFailed = document.getElementById('ext-stat-failed');
            
            const participantTableBody = document.getElementById('participantTableBody');
            const participantSearchInput = document.getElementById('participantSearchInput');
            const statusFilterSelect = document.getElementById('statusFilterSelect');
            
            const btnPrevPage = document.getElementById('btnPrevPage');
            const btnNextPage = document.getElementById('btnNextPage');
            const paginationInfo = document.getElementById('paginationInfo');
            
            let allParticipants = [];
            let filteredParticipants = [];
            let currentPage = 1;
            const rowsPerPage = 10;
            
            programFilterSelect.addEventListener('change', function() {
                const programId = this.value;
                const programName = this.options[this.selectedIndex].text;
                
                if (programId === 'all') {
                    extendedStatsSection.classList.add('hidden');
                    participantTableSection.classList.add('hidden');
                    return;
                }
                
                // Show loading state
                participantTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">
                            <span class="animate-pulse font-bold text-xs">🔄 Memuat data analisis program...</span>
                        </td>
                    </tr>
                `;
                extendedStatsSection.classList.remove('hidden');
                participantTableSection.classList.remove('hidden');
                tableProgramTitle.innerText = `Daftar Pendaftar: ${programName}`;
                
                // Fetch data via AJAX
                fetch(`{{ url('/superadmin/dashboard/program-stats') }}/${programId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update counts
                            extStatTotal.innerText = data.total_registrations;
                            extStatPassed.innerText = data.total_passed;
                            extStatDraft.innerText = data.total_draft;
                            extStatFailed.innerText = data.total_failed;
                            
                            // Save list
                            allParticipants = data.list;
                            filteredParticipants = [...allParticipants];
                            currentPage = 1;
                            
                            // Apply filters and render
                            applyFiltersAndRender();
                        } else {
                            alert('Gagal memuat data statistik program.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan koneksi saat memuat data.');
                    });
            });
            
            function applyFiltersAndRender() {
                const searchQuery = participantSearchInput.value.toLowerCase();
                const statusFilter = statusFilterSelect.value;
                
                filteredParticipants = allParticipants.filter(p => {
                    const matchesSearch = p.name.toLowerCase().includes(searchQuery) || p.email.toLowerCase().includes(searchQuery);
                    
                    let matchesStatus = true;
                    if (statusFilter !== 'all') {
                        if (statusFilter === 'draft') {
                            matchesStatus = (p.status === 'draft' || p.status === 'process');
                        } else {
                            matchesStatus = p.status === statusFilter;
                        }
                    }
                    
                    return matchesSearch && matchesStatus;
                });
                
                currentPage = 1;
                renderTable();
            }
            
            function renderTable() {
                const totalItems = filteredParticipants.length;
                const totalPages = Math.ceil(totalItems / rowsPerPage) || 1;
                
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                
                const startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = Math.min(startIndex + rowsPerPage, totalItems);
                
                const pageItems = filteredParticipants.slice(startIndex, endIndex);
                
                participantTableBody.innerHTML = '';
                
                if (pageItems.length === 0) {
                    participantTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 italic">Tidak ada data pendaftar yang cocok.</td>
                        </tr>
                    `;
                    paginationInfo.innerText = 'Menampilkan 0 - 0 dari 0 data';
                    btnPrevPage.disabled = true;
                    btnNextPage.disabled = true;
                    return;
                }
                
                pageItems.forEach(p => {
                    let badgeClass = 'bg-slate-100 text-slate-600';
                    let badgeText = p.status;
                    
                    if (p.status === 'passed') {
                        badgeClass = 'bg-emerald-100 text-emerald-800';
                        badgeText = 'Lolos (Passed)';
                    } else if (p.status === 'draft' || p.status === 'process') {
                        badgeClass = 'bg-amber-100 text-amber-800';
                        badgeText = 'Draft / Belum Kirim';
                    } else if (p.status === 'failed') {
                        badgeClass = 'bg-rose-100 text-rose-800';
                        badgeText = 'Gugur (Failed)';
                    } else if (p.status === 'pending') {
                        badgeClass = 'bg-blue-100 text-blue-800';
                        badgeText = 'Pending Review';
                    }
                    
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-slate-50/50 transition-colors border-b border-slate-100';
                    row.innerHTML = `
                        <td class="p-4 font-bold text-slate-800">${p.name}</td>
                        <td class="p-4 text-slate-500 font-mono text-[11px]">${p.email}</td>
                        <td class="p-4">
                            <span class="block text-slate-700">${p.province}</span>
                            <span class="block text-[10px] text-slate-400 font-bold">${p.regency}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider ${badgeClass}">
                                ${badgeText}
                            </span>
                        </td>
                        <td class="p-4 text-[11px] text-slate-400 font-mono">${p.updated_at}</td>
                    `;
                    participantTableBody.appendChild(row);
                });
                
                paginationInfo.innerText = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalItems} data`;
                btnPrevPage.disabled = currentPage === 1;
                btnNextPage.disabled = currentPage === totalPages;
            }
            
            participantSearchInput.addEventListener('input', applyFiltersAndRender);
            statusFilterSelect.addEventListener('change', applyFiltersAndRender);
            
            btnPrevPage.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });
            
            btnNextPage.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredParticipants.length / rowsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });
        });
    </script>
@endsection
