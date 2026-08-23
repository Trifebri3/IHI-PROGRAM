@extends('adminprogram.layouts.app')

@section('title', 'Manajemen Alumni')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
                Alumni Management
            </h1>
            <p class="text-sm font-medium text-slate-500">
                Kelola data alumni, konfigurasi template sertifikat, dan verifikasi kelulusan program lama.
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('adminprogram.alumni.templates') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-all">
                Sertifikat Template
            </a>
            <a href="{{ route('adminprogram.alumni.verifications') }}" class="px-4 py-2.5 bg-emerald-600 rounded-xl text-sm font-bold text-white hover:bg-emerald-700 shadow-md transition-all flex items-center gap-2">
                Permohonan Verifikasi
                @php
                    $pendingCount = \App\Models\AlumniVerificationRequest::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="bg-white text-emerald-800 text-xs px-2 py-0.5 rounded-full font-black animate-pulse">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('adminprogram.alumni.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="sm:col-span-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Cari Alumni</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, Email, NIA..." class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <!-- Program -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Program</label>
                <select name="program_id" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                    <option value="">Semua Program</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tahun -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tahun</label>
                <select name="year" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Verifikasi -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Revision</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-900 transition-colors shadow-sm text-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs Switcher -->
    <div class="flex border-b border-slate-200">
        <button id="tab-alumni-btn" onclick="switchTab('alumni')" class="px-5 py-3 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 focus:outline-none transition-all">
            🎓 Alumni Terdaftar ({{ $alumni->total() }})
        </button>
        <button id="tab-candidates-btn" onclick="switchTab('candidates')" class="px-5 py-3 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none transition-all">
            📝 Peserta Aktif / Calon Alumni ({{ $candidates->total() }})
        </button>
    </div>

    <!-- Alumni Tab Panel -->
    <div id="panel-alumni" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Nama Alumni</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">No. Induk Alumni</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Program & Tahun</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Tanggal Lulus</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Informasi Tambahan</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($alumni as $a)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $a->user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $a->user->email }}</div>
                                </td>
                                <td class="p-4 font-mono font-bold text-slate-600">
                                    {{ $a->user->alumniProfile->alumni_number ?? 'Bukan Program Baru' }}
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-700">{{ $a->alumniProgram->name }}</div>
                                    <div class="text-xs text-slate-400">Tahun {{ $a->alumniProgram->year }}</div>
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $a->created_at->format('d M Y') }}
                                </td>
                                <td class="p-4">
                                    @if($a->verification_status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            Active / Verified
                                        </span>
                                    @elseif($a->verification_status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700">
                                            Pending Review
                                        </span>
                                    @elseif($a->verification_status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                            Revision Requested
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if(!empty($a->extra_info['nilai_akhir']) || !empty($a->extra_info['predikat']))
                                        <div class="text-xs text-slate-600">
                                            @if(!empty($a->extra_info['nilai_akhir']))
                                                <div>Nilai: <strong>{{ $a->extra_info['nilai_akhir'] }}</strong></div>
                                            @endif
                                            @if(!empty($a->extra_info['predikat']))
                                                <div>Predikat: <strong>{{ $a->extra_info['predikat'] }}</strong></div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('adminprogram.alumni.edit-extra', $a->id) }}" class="p-2 bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors" title="Edit Nilai / Transkrip">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        
                                        @php
                                            $cert = \App\Models\AlumniCertificate::where('user_id', $a->user_id)
                                                ->where('alumni_program_id', $a->alumni_program_id)
                                                ->first();
                                        @endphp
                                        @if($cert)
                                            <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" class="p-2 bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors" title="Lihat Sertifikat PDF">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('public.alumni.verify', $cert->uuid) }}" target="_blank" class="p-2 bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors" title="Lihat Halaman QR Verifikasi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                    Tidak ada data alumni yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alumni->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $alumni->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Candidates Tab Panel -->
    <div id="panel-candidates" class="hidden space-y-4">
        <!-- Data Registrasi Aktif JSON -->
        <script id="active-registrations-data" type="application/json">
            @json($activeRegistrationsData)
        </script>

        <!-- Quick Register and Pass Form -->
        <div class="bg-gradient-to-br from-emerald-50/20 to-white p-5 rounded-2xl border border-emerald-100 shadow-2xs mb-4">
            <h4 class="text-xs font-black text-emerald-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                ⚡ Loloskan Peserta Baru ke Program (Pilih dari User Terdaftar)
            </h4>
            <form action="{{ route('adminprogram.alumni.register-and-pass') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Program</label>
                    <select name="program_id" id="manual_program_select" required class="w-full text-xs px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 bg-white shadow-3xs">
                        <option value="">-- Pilih Program --</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih User Peserta</label>
                    <select name="user_id" id="manual_user_select" required class="w-full text-xs px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 bg-white shadow-3xs">
                        <option value="">-- Pilih User Peserta --</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-extrabold rounded-xl transition shadow-md text-xs uppercase tracking-wider">
                        ⚡ Loloskan & Terbit Piagam
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Nama Peserta / Email</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Nama Program</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Tahun Program</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Tahap Berjalan</th>
                            <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($candidates as $c)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $c->user->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-400">{{ $c->user->email ?? 'N/A' }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-700">{{ $c->program->name ?? 'N/A' }}</div>
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $c->program->start_date ? date('Y', strtotime($c->program->start_date)) : date('Y') }}
                                </td>
                                <td class="p-4 font-semibold text-slate-600">
                                    {{ $c->currentStage->name ?? 'N/A' }}
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">Sequence Tahap: {{ $c->currentStage->sequence ?? '-' }}</div>
                                </td>
                                <td class="p-4 text-right">
                                    <form action="{{ route('adminprogram.programs.applicant.instant-pass', [$c->program_id, $c->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MELOLOSKAN INSTAN peserta {{ $c->user->name ?? '' }}? Proses ini akan langsung mengubah status menjadi lulus, memberikan NIA, dan menerbitkan piagam kelulusan otomatis.')" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all uppercase tracking-wider whitespace-nowrap">
                                            ✅ Loloskan Instan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 italic">
                                    Tidak ada data peserta aktif yang cocok atau sedang diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($candidates->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $candidates->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Switch Tab Script -->
    <script>
        function switchTab(tab) {
            const panelAlumni = document.getElementById('panel-alumni');
            const panelCandidates = document.getElementById('panel-candidates');
            const btnAlumni = document.getElementById('tab-alumni-btn');
            const btnCandidates = document.getElementById('tab-candidates-btn');

            if (tab === 'alumni') {
                panelAlumni.classList.remove('hidden');
                panelCandidates.classList.add('hidden');
                btnAlumni.className = "px-5 py-3 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 focus:outline-none transition-all";
                btnCandidates.className = "px-5 py-3 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none transition-all";
            } else {
                panelAlumni.classList.add('hidden');
                panelCandidates.classList.remove('hidden');
                btnAlumni.className = "px-5 py-3 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none transition-all";
                btnCandidates.className = "px-5 py-3 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 focus:outline-none transition-all";
            }
        }

        // Auto-switch tab if URL contains candidate_page pagination parameter
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('candidate_page')) {
                switchTab('candidates');
            }

            // Filter candidate users based on selected program (dynamically rebuild option list to support all browsers)
            const manualProgramSelect = document.getElementById('manual_program_select');
            const manualUserSelect = document.getElementById('manual_user_select');
            const dataScript = document.getElementById('active-registrations-data');
            
            if (manualProgramSelect && manualUserSelect && dataScript) {
                let registrations = [];
                try {
                    registrations = JSON.parse(dataScript.textContent);
                } catch (e) {
                    console.error("Error parsing registrations JSON", e);
                }

                manualProgramSelect.addEventListener('change', function() {
                    const selectedProgramId = parseInt(this.value, 10);
                    
                    // Reset dropdown
                    manualUserSelect.innerHTML = '<option value="">-- Pilih User Peserta --</option>';
                    
                    if (!isNaN(selectedProgramId)) {
                        // Filter matching registrations
                        const filtered = registrations.filter(function(reg) {
                            return parseInt(reg.alumni_program_id, 10) === selectedProgramId;
                        });

                        // Append filtered options
                        filtered.forEach(function(reg) {
                            const opt = document.createElement('option');
                            opt.value = reg.user_id;
                            opt.textContent = reg.user_name + ' (' + reg.user_email + ')';
                            manualUserSelect.appendChild(opt);
                        });
                    }
                });
            }
        });
    </script>
</div>
@endsection
