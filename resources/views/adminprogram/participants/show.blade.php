@extends('adminprogram.layouts.app')

@section('title', 'Detail Profil & Edit Peserta')

@section('content')
<div class="py-6 max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'details' }">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('adminprogram.participants.index') }}" class="inline-flex items-center text-xs bg-white text-slate-600 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition font-bold border shadow-3xs">
            &larr; Kembali ke Database Peserta
        </a>

        <!-- Tab Switches -->
        <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border">
            <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'bg-white text-slate-800 shadow-3xs' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-1.5 text-xs font-bold rounded-lg transition">
                Detail &amp; Riwayat Form
            </button>
            <button @click="activeTab = 'edit'" :class="activeTab === 'edit' ? 'bg-white text-slate-800 shadow-3xs' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-1.5 text-xs font-bold rounded-lg transition">
                Edit Data &amp; Log Audit
            </button>
        </div>
    </div>

    <!-- Header / Overview Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center space-x-4">
            @if($registration->user->profile && $registration->user->profile->profile_photo_path)
                <img class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-100 shadow-sm" src="{{ asset('storage/' . $registration->user->profile->profile_photo_path) }}" alt="Foto">
            @elseif($registration->user->avatar)
                <img class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-100 shadow-sm" src="{{ $registration->user->avatar }}" alt="Avatar">
            @else
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 border-2 border-emerald-100 flex items-center justify-center text-emerald-800 font-extrabold text-lg shadow-3xs">
                    {{ strtoupper(substr($registration->user->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 leading-tight">{{ $registration->user->name }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $registration->user->email }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-600 border">Role: {{ $registration->user->status }}</span>
                    @if($registration->status === 'passed')
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Lulus Seleksi</span>
                    @elseif($registration->status === 'failed')
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200">Gugur Seleksi</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">Proses Seleksi</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Block Status & Quick Actions -->
        <div class="flex items-center space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100 md:self-stretch">
            <div class="text-xs space-y-1">
                <span class="block text-[9px] font-extrabold uppercase text-slate-400">Status Akun Sistem</span>
                <div class="flex items-center space-x-1.5">
                    @if($registration->user->is_blocked)
                        <span class="font-bold text-rose-800">🔴 DI-BLOKIR</span>
                    @else
                        <span class="font-bold text-emerald-800">🟢 AKTIF / AMAN</span>
                    @endif
                </div>
            </div>
            <div class="border-l border-slate-200 pl-4">
                <form action="{{ route('adminprogram.participants.toggle-block', $registration->user_id) }}" method="POST">
                    @csrf
                    @if($registration->user->is_blocked)
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-3xs uppercase tracking-wider">
                            Aktifkan Akun
                        </button>
                    @else
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-3xs uppercase tracking-wider" onclick="return confirm('Apakah Anda yakin ingin memblokir akses akun peserta ini?')">
                            Blokir Akun
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 1: READ ONLY DETAILS -->
    <div x-show="activeTab === 'details'" class="grid grid-cols-1 md:grid-cols-3 gap-6" x-transition>
        <!-- Column Left: NIP Management, KYC Identitas, & Alamat (1 Col) -->
        <div class="md:col-span-1 space-y-6">
            <!-- Card: Nomor Induk (NI) Management -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Nomor Induk Program</h3>
                </div>
                <div class="space-y-1">
                    <span class="block text-[10px] font-bold uppercase text-slate-450">Nomor Induk (NI)</span>
                    <p class="font-mono font-bold text-slate-800 tracking-wider text-sm bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        {{ $registration->final_id_number ?? 'Belum ada Nomor Induk' }}
                    </p>
                </div>
            </div>

            <!-- Card: Identitas Resmi (KYC) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Identitas Resmi (KYC)</h3>
                </div>
                @if($registration->user->verification)
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[9px]">NIK terenkripsi:</span>
                            <p class="font-mono font-bold text-slate-800 tracking-wider text-sm mt-0.5">{{ $registration->user->verification->nik ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[9px]">Status Verifikasi NIK:</span>
                            <div class="mt-0.5">
                                @if($registration->user->verification->status === 'verified')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Terverifikasi</span>
                                @elseif($registration->user->verification->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200">Ditolak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="pt-2 border-t flex flex-col gap-2 font-bold">
                            @if($registration->user->verification->ktp_path)
                                <a href="{{ asset('storage/' . $registration->user->verification->ktp_path) }}" target="_blank" class="inline-flex items-center text-[10px] text-emerald-700 bg-emerald-50/50 border border-emerald-100 px-3 py-1.5 rounded-lg hover:bg-emerald-100/50 transition">
                                    📥 Dokumen KTP Identitas
                                </a>
                            @endif
                            @if($registration->user->verification->photo_path)
                                <a href="{{ asset('storage/' . $registration->user->verification->photo_path) }}" target="_blank" class="inline-flex items-center text-[10px] text-emerald-700 bg-emerald-50/50 border border-emerald-100 px-3 py-1.5 rounded-lg hover:bg-emerald-100/50 transition">
                                    📷 Foto Selfie Peserta
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Belum melakukan verifikasi KYC.</p>
                @endif
            </div>

            <!-- Card: Alamat Demografi -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Alamat Demografi</h3>
                </div>
                @if($registration->user->address)
                    <div class="text-xs space-y-2 text-slate-800">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Provinsi</span>
                                <p class="font-bold">{{ $registration->user->address->provinsi }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Kabupaten/Kota</span>
                                <p class="font-bold">{{ $registration->user->address->kabupaten }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Kecamatan</span>
                                <p class="font-bold">{{ $registration->user->address->kecamatan }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Desa/Kelurahan</span>
                                <p class="font-bold">{{ $registration->user->address->desa }}</p>
                            </div>
                        </div>
                        <div class="pt-2 border-t">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">Detail Alamat</span>
                            <p class="font-medium text-slate-600 mt-0.5 leading-relaxed">{{ $registration->user->address->detail_alamat ?? '—' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Data alamat belum diisi.</p>
                @endif
            </div>
        </div>

        <!-- Column Right: Biodata Dasar, Biodata Program, & Riwayat Tahapan -->
        <div class="md:col-span-2 space-y-6">
            <!-- Card: Biodata Dasar Pendaftaran Akun -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Biodata Registrasi Akun</h3>
                </div>
                @if($registration->user->biodataValues && $registration->user->biodataValues->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($registration->user->biodataValues as $val)
                            @if($val->biodataField)
                                <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 flex flex-col justify-between">
                                    <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">{{ $val->biodataField->name }}</span>
                                    @if($val->biodataField->type === 'file')
                                        <a href="{{ asset('storage/' . $val->value) }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline mt-1 block">📄 Unduh Dokumen</a>
                                    @else
                                        <span class="text-xs font-semibold text-slate-800 break-all leading-normal">{{ $val->value ?? '—' }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada pengisian biodata registrasi dasar.</p>
                @endif
            </div>

            <!-- Card: Form Biodata Wajib Program (Form Tambahan) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Biodata Wajib Program (Form Tambahan)</h3>
                </div>
                @if($biodataSubmission && !empty($biodataSubmission->submitted_answers))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($biodataSubmission->submitted_answers as $fieldKey => $ansValue)
                            <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 flex flex-col justify-between">
                                <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">{{ str_replace('_', ' ', $fieldKey) }}</span>
                                @if(is_array($ansValue))
                                    <span class="text-xs font-semibold text-slate-800 leading-normal">{{ implode(', ', $ansValue) }}</span>
                                @elseif(is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf')))
                                    <a href="{{ asset('storage/' . $ansValue) }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline mt-1 block font-mono">📄 Unduh Lampiran</a>
                                @else
                                    <span class="text-xs font-semibold text-slate-800 break-all leading-normal">{{ $ansValue ?? '—' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Peserta belum mengisi form biodata wajib program.</p>
                @endif
            </div>

            <!-- Card: Riwayat Tahapan Program & Formulir Tambahan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Status &amp; Hasil Seluruh Tahapan Program</h3>
                </div>
                <div class="space-y-4">
                    <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/10 space-y-1">
                        <span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wide">Motivasi &amp; Harapan Awal Mengikuti Program</span>
                        <p class="text-xs font-semibold text-slate-700 leading-relaxed">{{ $registration->motivation ?? '— (Tidak diisi)' }}</p>
                    </div>

                    @forelse($stageSubmissions as $sub)
                        <div class="p-4 rounded-xl border border-slate-150 bg-slate-50/20 space-y-3">
                            <div class="flex items-center justify-between border-b pb-2">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">{{ $sub->stage->name }}</span>
                                    <span class="text-[9px] text-slate-400 block">Sequence/Urutan: {{ $sub->stage->sequence }}</span>
                                </div>
                                <div>
                                    @if($sub->status === 'passed')
                                        <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">LOLOS</span>
                                    @elseif($sub->status === 'failed')
                                        <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200">GUGUR</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">PENDING / PROSES</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2 text-xs">
                                @forelse($sub->form_values ?? [] as $fv)
                                    <div class="grid grid-cols-3 gap-2">
                                        <span class="font-bold text-slate-400">{{ $fv['field_name'] ?? 'Field' }}:</span>
                                        <span class="col-span-2 text-slate-800">
                                            @if(($fv['type'] ?? '') === 'file' || ($fv['type'] ?? '') === 'image')
                                                @if(!empty($fv['value']))
                                                    <a href="{{ asset('storage/' . $fv['value']) }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline">
                                                        📄 Lihat Dokumen ({{ strtoupper($fv['type']) }})
                                                    </a>
                                                @else
                                                    <span class="text-rose-500 italic">Tidak diunggah</span>
                                                @endif
                                            @else
                                                {{ $fv['value'] ?? '—' }}
                                            @endif
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-slate-400 italic text-[11px]">Tidak ada isian formulir kustom pada tahap ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Peserta belum memulai tahapan program apa pun.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: ACTIVE EDIT FORM & AUDIT LOGS -->
    <div x-show="activeTab === 'edit'" class="space-y-6" x-transition style="display: none;">
        
        <!-- FORM EDIT DATA ADMINISTRATIF -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="border-b pb-3 mb-6">
                <h3 class="text-sm font-extrabold text-slate-700 uppercase tracking-wider">Form Perubahan Data Administratif</h3>
                <p class="text-xs text-slate-450">Setiap perubahan data sensitif (Nama, Email, NIK, NI, Status) akan dicatat ke dalam log audit sistem.</p>
            </div>

            <form method="POST" action="{{ route('adminprogram.participants.update-profile', $registration->id) }}" class="space-y-6">
                @csrf

                <!-- Section: Data Utama Akun -->
                <div>
                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">1. Data Utama Akun</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-450 uppercase mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $registration->user->name) }}" required class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-450 uppercase mb-1">Email Resmi</label>
                            <input type="email" name="email" value="{{ old('email', $registration->user->email) }}" required class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-450 uppercase mb-1">NIK KTP (KYC)</label>
                            <input type="text" name="nik" value="{{ old('nik', $registration->user->verification->nik ?? '') }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none" placeholder="Masukkan 16 digit NIK">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-450 uppercase mb-1">Nomor Induk Program (NI)</label>
                            <input type="text" name="final_id_number" value="{{ old('final_id_number', $registration->final_id_number) }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none font-mono" placeholder="Cth: PRG202600001">
                        </div>
                    </div>
                </div>

                <!-- Section: Informasi Program Kerja -->
                <div class="border-t pt-4">
                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">2. Status Program &amp; Administrasi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Batch / Angkatan</label>
                            <input type="text" name="batch" value="{{ old('batch', $registration->batch) }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none" placeholder="Cth: Batch 2 / Angkatan 2026">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Lokasi Pelaksanaan</label>
                            <input type="text" name="location" value="{{ old('location', $registration->location) }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none" placeholder="Cth: Cikole / Bandung">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Daerah / Wilayah Kegiatan</label>
                            <input type="text" name="region" value="{{ old('region', $registration->region) }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none" placeholder="Cth: Jawa Barat">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Status Keikutsertaan</label>
                            <select name="participant_status" class="w-full text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                                <option value="active" {{ old('participant_status', $registration->participant_status) === 'active' ? 'selected' : '' }}>Aktif Mengikuti</option>
                                <option value="completed" {{ old('participant_status', $registration->participant_status) === 'completed' ? 'selected' : '' }}>Selesai Program</option>
                                <option value="withdrawn" {{ old('participant_status', $registration->participant_status) === 'withdrawn' ? 'selected' : '' }}>Mengundurkan Diri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Status Kelulusan Seleksi</label>
                            <select name="status" class="w-full text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                                <option value="process" {{ old('status', $registration->status) === 'process' ? 'selected' : '' }}>Proses Seleksi</option>
                                <option value="passed" {{ old('status', $registration->status) === 'passed' ? 'selected' : '' }}>Lulus (Passed)</option>
                                <option value="failed" {{ old('status', $registration->status) === 'failed' ? 'selected' : '' }}>Gugur (Failed)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section: Alamat Demografi -->
                <div class="border-t pt-4">
                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">3. Alamat Demografi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Provinsi</label>
                            <input type="text" name="provinsi" value="{{ old('provinsi', $registration->user->address->provinsi ?? '') }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten" value="{{ old('kabupaten', $registration->user->address->kabupaten ?? '') }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan', $registration->user->address->kecamatan ?? '') }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Desa / Kelurahan</label>
                            <input type="text" name="desa" value="{{ old('desa', $registration->user->address->desa ?? '') }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">Detail Alamat / Patokan Jalan</label>
                            <textarea name="detail_alamat" rows="2" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">{{ old('detail_alamat', $registration->user->address->detail_alamat ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Dynamic Custom Biodata Fields -->
                @if($allFields->isNotEmpty())
                    <div class="border-t pt-4">
                        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">4. Biodata Registrasi Akun</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($allFields as $field)
                                @php
                                    $existingVal = $registration->user->biodataValues->firstWhere('biodata_field_id', $field->id);
                                    $currentVal = $existingVal ? $existingVal->value : '';
                                @endphp
                                @if($field->type !== 'file')
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-455 uppercase mb-1">{{ $field->name }}</label>
                                        <input type="text" name="biodata[{{ $field->id }}]" value="{{ old('biodata.' . $field->id, $currentVal) }}" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-150 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none" placeholder="Masukkan {{ strtolower($field->name) }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-2 border-t pt-4">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-3xs uppercase tracking-wider">
                        Simpan Perubahan &amp; Buat Log
                    </button>
                </div>
            </form>
        </div>

        <!-- RIWAYAT LOG AUDIT PERUBAHAN -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="border-b pb-3 mb-4">
                <h3 class="text-sm font-extrabold text-slate-700 uppercase tracking-wider">Timeline Log Audit Perubahan</h3>
                <p class="text-xs text-slate-450 font-medium">Melacak riwayat pengeditan data sensitif yang dilakukan oleh administrator terhadap profil peserta ini.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                            <th class="p-3">Tanggal &amp; Waktu</th>
                            <th class="p-3">Oleh (Admin)</th>
                            <th class="p-3">Detail Perubahan</th>
                            <th class="p-3 w-32">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-medium text-slate-750">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-3 text-slate-500 font-mono">
                                    {{ $log->created_at->format('d M Y H:i:s') }}
                                </td>
                                <td class="p-3 font-bold text-slate-800">
                                    {{ $log->user->name ?? 'System' }}
                                    <span class="block text-[9px] text-slate-400 font-normal">ID: #{{ $log->user_id }}</span>
                                </td>
                                <td class="p-3 leading-relaxed break-words">
                                    {{ $log->details }}
                                </td>
                                <td class="p-3 text-slate-500 font-mono">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400 italic">
                                    Belum ada log perubahan data yang tercatat untuk peserta ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
