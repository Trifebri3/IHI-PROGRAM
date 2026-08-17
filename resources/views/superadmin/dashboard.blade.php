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

        <a href="{{ route('superadmin.biodata.index') }}" class="relative flex flex-col p-6 transition-all duration-300 bg-white border border-emerald-50 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-emerald-300 group">
            <div class="flex items-center justify-center w-14 h-14 mb-4 rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700">Manajemen Biodata</h3>
            <p class="mt-2 text-sm text-gray-500 flex-1">Atur formulir dinamis dan kelengkapan data wajib bagi seluruh peserta.</p>
        </a>


        <li>
    <a href="{{ route('superadmin.events.index') }}"
       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group justify-between
       {{ request()->routeIs('superadmin.events.index') ? 'bg-gradient-to-r from-emerald-800 to-green-700 text-white shadow-md shadow-emerald-100/50' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">

        <div class="flex items-center truncate">
            <span class="mr-3 text-lg flex-shrink-0 {{ request()->routeIs('superadmin.events.index') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-600' }}">
                🚀
            </span>
            <span class="font-bold">Manajemen Event</span>
        </div>

        {{-- Mini Badge Total Event sebagai pemanis --}}
        @if(!request()->routeIs('superadmin.events.index'))
            @php $eventCount = \App\Models\Event::count(); @endphp
            <span class="text-[9px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md group-hover:bg-emerald-50 group-hover:text-emerald-700 border transition-colors">
                {{ $eventCount }}
            </span>
        @endif
    </a>
</li>


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

    </div>

    <!-- STATS COUNTERS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
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
        });
    </script>
@endsection
