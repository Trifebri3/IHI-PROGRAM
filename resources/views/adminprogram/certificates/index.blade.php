@extends('adminprogram.layouts.app')

@section('title', 'Sertifikat & Piagam')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
                Sertifikat &amp; Piagam Kelulusan
            </h1>
            <p class="text-sm font-medium text-slate-500">
                Kelola penerbitan sertifikat digital otomatis, unggah piagam eksternal secara massal, atau hapus dan revisi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('bulkUploadModal')" class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-3xs uppercase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Bulk Upload Mapping
            </button>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs">
        <form method="GET" action="{{ route('adminprogram.certificates.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Program Kerja</label>
                <select name="program_id" class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 transition outline-none">
                    <option value="">Semua Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Angkatan / Batch</label>
                <select name="batch" class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 transition outline-none">
                    <option value="">Semua Batch</option>
                    @foreach($batches as $b)
                        <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>
                            {{ $b }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status Sertifikat</label>
                <select name="cert_status" class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 transition outline-none">
                    <option value="">Semua Status</option>
                    <option value="issued" {{ request('cert_status') == 'issued' ? 'selected' : '' }}>Sudah Diterbitkan</option>
                    <option value="none" {{ request('cert_status') == 'none' ? 'selected' : '' }}>Belum Diterbitkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pencarian Cepat</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Email / NIP..." class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl pl-9 pr-3.5 py-2.5 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 transition outline-none">
                    <svg class="absolute left-3 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            <div class="md:col-span-4 flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('adminprogram.certificates.index') }}" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-xl transition uppercase">
                    Reset Filter
                </a>
                <button type="submit" class="px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-3xs uppercase">
                    Cari &amp; Saring
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Generation and Queue Form -->
    <form id="bulkGenerateForm" method="POST" action="{{ route('adminprogram.certificates.bulk-generate') }}">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-100 shadow-3xs overflow-hidden">
            <div class="p-5 border-b flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
                <span class="text-xs font-extrabold uppercase text-slate-600 tracking-wider">
                    Daftar Antrean &amp; Sertifikat Penerbitan
                </span>
                <div id="bulkActionsPanel" class="hidden flex items-center gap-2">
                    <button type="submit" class="flex items-center gap-1.5 px-4.5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-3xs uppercase">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Jana Otomatis Terpilih
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                            <th class="p-4 w-12 text-center">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200">
                            </th>
                            <th class="p-4">Nama Lengkap &amp; NIP</th>
                            <th class="p-4">Program &amp; Batch</th>
                            <th class="p-4">No. Sertifikat</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 w-40 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs font-medium text-slate-700">
                        @forelse($registrations as $reg)
                            @php
                                $alumniProgram = $reg->user->alumniPrograms->firstWhere('program_id', $reg->program_id);
                                $certificate = $alumniProgram ? $reg->user->alumniCertificates->firstWhere('alumni_program_id', $alumniProgram->id) : null;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}" onchange="updateSelectedCount()" class="row-checkbox rounded text-emerald-600 focus:ring-emerald-500 border-slate-200">
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-slate-800">{{ $reg->user->name }}</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">{{ $reg->final_id_number ?? 'NIP: Belum Ada' }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800">{{ $reg->program->name }}</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Batch: {{ $reg->batch ?? '—' }} | Lokasi: {{ $reg->location ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $certificate->certificate_number ?? '—' }}
                                </td>
                                <td class="p-4">
                                    @if($certificate)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-full uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Terbit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-extrabold text-slate-500 bg-slate-50 border border-slate-200 rounded-full uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($certificate)
                                            <!-- Preview / Download -->
                                            <a href="{{ Storage::disk('public')->url($certificate->file_path) }}" target="_blank" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition" title="Preview/Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                            <!-- Delete / Revise -->
                                            <form method="POST" action="{{ route('adminprogram.certificates.destroy', $certificate->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus sertifikat ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus/Revisi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Upload manual button -->
                                            <button type="button" onclick="openSingleUploadModal('{{ $reg->id }}', '{{ $reg->user->name }}')" class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg transition uppercase">
                                                Upload Manual
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 font-medium">
                                    Tidak ada data peserta lulus yang cocok dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
                <div class="p-5 border-t">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Modal 1: Single Upload Manual Certificate -->
<div id="singleUploadModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-100 max-w-md w-full shadow-lg p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Unggah Sertifikat Manual</h3>
            <button onclick="toggleModal('singleUploadModal')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="singleUploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Peserta</label>
                <input type="text" id="singleUploadName" readonly class="w-full text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Sertifikat (Opsional)</label>
                <input type="text" name="certificate_number" placeholder="Contoh: CERT/2026/001" class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Berkas Sertifikat (PDF/Gambar, Max 10MB)</label>
                <input type="file" name="certificate_file" required class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2 transition outline-none">
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <button type="button" onclick="toggleModal('singleUploadModal')" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition uppercase">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-3xs uppercase">
                    Unggah &amp; Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Bulk Upload Mapping -->
<div id="bulkUploadModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-100 max-w-lg w-full shadow-lg p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Bulk Upload Mapping Sertifikat</h3>
            <button onclick="toggleModal('bulkUploadModal')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
            <p class="text-[10.5px] leading-relaxed text-amber-900 font-semibold flex items-start">
                <svg class="w-4.5 h-4.5 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Sistem akan memetakan file secara otomatis dengan mencari kesamaan nama file dengan Nomor Induk (NIP), email, atau nama lengkap peserta. Pastikan berkas dinamakan sesuai data tersebut (Contoh: "PRG202600001.pdf" atau "peserta_email@ihi.or.id.pdf").
            </p>
        </div>

        <form method="POST" action="{{ route('adminprogram.certificates.bulk-upload') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Program Sasaran</label>
                <select name="program_id" required class="w-full text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 focus:bg-white focus:border-emerald-500 transition outline-none">
                    <option value="">Pilih Program Kerja</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Berkas Sertifikat (Multi-file, Max 10MB per Berkas)</label>
                <input type="file" name="files[]" multiple required class="w-full text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2 transition outline-none">
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <button type="button" onclick="toggleModal('bulkUploadModal')" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition uppercase">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-3xs uppercase">
                    Unggah &amp; Peta Massal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openSingleUploadModal(registrationId, userName) {
        document.getElementById('singleUploadName').value = userName;
        const form = document.getElementById('singleUploadForm');
        form.action = `/adminprogram/participants/${registrationId}/upload-certificate`;
        toggleModal('singleUploadModal');
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const bulkPanel = document.getElementById('bulkActionsPanel');
        if (checked.length > 0) {
            bulkPanel.classList.remove('hidden');
        } else {
            bulkPanel.classList.add('hidden');
        }
    }
</script>
@endsection
