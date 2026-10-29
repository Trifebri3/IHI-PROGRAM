@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors shadow-sm" title="Kembali ke Generator">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Daftar Piagam Terbit</h1>
                    <p class="text-slate-600 mt-0.5 text-sm">Sertifikat piagam yang telah diterbitkan untuk peserta program <strong>{{ $program->name }}</strong>.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm flex items-center gap-2 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Generator Piagam
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="font-semibold text-sm">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="font-semibold text-sm">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Search Toolbar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                Total Terbit: {{ $certificates->total() }} Sertifikat
            </span>
        </div>

        <form method="GET" action="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="flex items-center gap-2">
            <div class="relative w-full sm:w-72">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nomor, nama atau email..." 
                       class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request('search'))
                    <a href="{{ route('adminprogram.piagam.generator.published', $program->id) }}" class="text-slate-400 hover:text-slate-600 absolute right-2.5 top-2 text-xs" title="Reset">✕</a>
                @endif
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                        <th class="p-3.5 w-12 text-center border-r border-slate-200">No</th>
                        <th class="p-3.5 border-r border-slate-200">Peserta</th>
                        <th class="p-3.5 border-r border-slate-200">Nomor Kredensial</th>
                        <th class="p-3.5 border-r border-slate-200 text-center">Status Email</th>
                        <th class="p-3.5 border-r border-slate-200 text-center">Tanggal Dibuat</th>
                        <th class="p-3.5 min-w-[280px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($certificates as $index => $cert)
                        @php
                            $user = $cert->participant->user ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500 font-mono">
                                {{ $certificates->firstItem() + $index }}
                            </td>
                            <td class="p-3.5 border-r border-slate-100">
                                <div class="font-medium text-slate-800">{{ $user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3.5 border-r border-slate-100">
                                <span class="font-mono text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                    {{ $cert->certificate_number ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3.5 border-r border-slate-100 text-center">
                                @if($cert->email_sent_at)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800" title="Terkirim pada {{ $cert->email_sent_at }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Terkirim
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Belum Dikirim
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 border-r border-slate-100 text-center text-xs text-slate-500">
                                {{ $cert->created_at ? $cert->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center flex-wrap gap-1.5">
                                    @if($cert->file_path)
                                        <!-- PDF View -->
                                        <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1" title="Lihat Dokumen PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            PDF
                                        </a>
                                    @endif

                                    <!-- Edit Data -->
                                    <button type="button" onclick="openEditPiagamModal({{ $cert->participant_id }})" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors border border-amber-200 flex items-center gap-1" title="Edit nomor, nama, skor atau predikat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit
                                    </button>

                                    <!-- Terbitkan Ulang (Regenerate) -->
                                    <form action="{{ route('adminprogram.piagam.generator.regenerateOne', [$program->id, $cert->participant_id]) }}" method="POST" class="inline" onsubmit="return confirm('Terbitkan ulang (regenerate) sertifikat untuk {{ $user->name ?? 'peserta' }}?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200 flex items-center gap-1" title="Terbitkan Ulang Dokumen PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Terbitkan Ulang
                                        </button>
                                    </form>

                                    <!-- Batalkan Piagam -->
                                    <form action="{{ route('adminprogram.piagam.generator.cancelOne', [$program->id, $cert->participant_id]) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Batalkan dan hapus sertifikat untuk {{ $user->name ?? 'peserta' }}? Data piagam akan dihapus.')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition-colors border border-rose-200 flex items-center gap-1" title="Batalkan & hapus piagam">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Batal
                                        </button>
                                    </form>

                                    @if($cert->participant)
                                        <form action="{{ route('adminprogram.piagam.generator.sendEmailOne', [$program->id, $cert->participant_id]) }}" method="POST" class="inline" onsubmit="return confirm('Kirim email ke {{ $user->email ?? 'peserta' }}?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 {{ $cert->email_sent_at ? 'bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} text-xs font-semibold rounded-lg transition-colors" title="{{ $cert->email_sent_at ? 'Kirim Ulang Email' : 'Kirim Email' }}">
                                                {{ $cert->email_sent_at ? 'Email Ulang' : 'Email' }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($cert->qr_token)
                                        <a href="{{ route('public.piagam.verify', $cert->qr_token) }}" target="_blank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-colors" title="Cek Halaman Verifikasi Publik">
                                            QR
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-500">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Belum ada piagam yang diterbitkan untuk program ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL: EDIT DATA PIAGAM SERTIFIKAT -->
<div id="modalEditPiagam" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditPiagamModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Data Piagam Sertifikat</h3>
                        <p class="text-xs text-slate-500">Perbarui nama, nomor sertifikat, nilai, atau predikat</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditPiagamModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    ✕
                </button>
            </div>

            <!-- Loading Spinner -->
            <div id="editModalLoading" class="p-10 text-center text-slate-500">
                <div class="inline-block w-8 h-8 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-xs font-semibold">Memuat data sertifikat...</p>
            </div>

            <!-- Form -->
            <form id="editPiagamForm" method="POST" style="display: none;">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Nama Peserta -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Peserta (Dicetak pada Piagam):
                        </label>
                        <input type="text" id="editName" name="name" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold text-slate-800">
                        <p class="text-[11px] text-slate-500 mt-1">Perubahan nama akan otomatis tersimpan ke data akun peserta.</p>
                    </div>

                    <!-- Email Peserta (Read-only) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                            Email Peserta:
                        </label>
                        <input type="text" id="editEmail" readonly class="w-full px-3.5 py-2 text-xs bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor Kredensial / Sertifikat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Piagam:
                            </label>
                            <input type="text" id="editCertificateNumber" name="certificate_number" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-mono">
                        </div>

                        <!-- Tanggal Terbit -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Terbit:
                            </label>
                            <input type="date" id="editPublishedAt" name="published_at" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Predikat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Predikat / Keterangan:
                            </label>
                            <input type="text" id="editPredikat" name="predikat" list="predikatSuggestions" placeholder="Contoh: Sangat Memuaskan" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                            <datalist id="predikatSuggestions">
                                <option value="Sangat Memuaskan"></option>
                                <option value="Memuaskan"></option>
                                <option value="Baik"></option>
                                <option value="Cukup"></option>
                                <option value="Lulus"></option>
                                <option value="Peserta Terbaik"></option>
                            </datalist>
                        </div>

                        <!-- Nilai Akhir / Skor -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nilai Akhir / Skor Total:
                            </label>
                            <input type="text" id="editFinalGrade" name="final_grade" placeholder="Contoh: 88.5 atau A" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <!-- Dynamic Custom Grades container if any -->
                    <div id="dynamicCustomGradesContainer" class="space-y-2" style="display: none;">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Komponen Nilai Tambahan:
                        </label>
                        <div id="dynamicCustomGradesInputs" class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200"></div>
                    </div>

                    <!-- Regenerate Now Checkbox -->
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" id="editRegenerateNow" name="regenerate_now" value="1" checked class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-xs text-amber-900">Langsung Terbitkan Ulang (Regenerate PDF)</span>
                                <p class="text-[11px] text-amber-700 mt-0.5">Otomatis memperbarui dokumen PDF sertifikat dengan perubahan nama/nomor/nilai di atas.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <button type="button" onclick="closeEditPiagamModal()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditPiagamModal(participantId) {
        const modal = document.getElementById('modalEditPiagam');
        const loading = document.getElementById('editModalLoading');
        const form = document.getElementById('editPiagamForm');

        modal.style.display = 'block';
        loading.style.display = 'block';
        form.style.display = 'none';

        const dataUrl = "{{ url('adminprogram/programs/' . $program->id . '/piagam/generator/certificate') }}/" + participantId + "/data";
        const updateUrl = "{{ url('adminprogram/programs/' . $program->id . '/piagam/generator/certificate') }}/" + participantId + "/update";

        form.action = updateUrl;

        fetch(dataUrl)
            .then(res => {
                if (!res.ok) throw new Error('Status server ' + res.status);
                return res.json();
            })
            .then(data => {
                document.getElementById('editName').value = data.name || '';
                document.getElementById('editEmail').value = data.email || '';
                document.getElementById('editCertificateNumber').value = data.certificate_number || '';
                document.getElementById('editPublishedAt').value = data.published_at || '';
                document.getElementById('editPredikat').value = data.predikat || '';
                document.getElementById('editFinalGrade').value = data.final_grade || '';

                const customContainer = document.getElementById('dynamicCustomGradesContainer');
                const customInputs = document.getElementById('dynamicCustomGradesInputs');
                customInputs.innerHTML = '';

                if (data.grades && data.grades.length > 0) {
                    customContainer.style.display = 'block';
                    data.grades.forEach(g => {
                        const col = document.createElement('div');
                        col.innerHTML = `
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">${g.variable_name}:</label>
                            <input type="text" name="custom_grades[${g.variable_name}]" value="${g.value || ''}" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500">
                        `;
                        customInputs.appendChild(col);
                    });
                } else {
                    customContainer.style.display = 'none';
                }

                loading.style.display = 'none';
                form.style.display = 'block';
            })
            .catch(err => {
                alert('Gagal mengambil data sertifikat: ' + err.message);
                closeEditPiagamModal();
            });
    }

    function closeEditPiagamModal() {
        document.getElementById('modalEditPiagam').style.display = 'none';
    }
</script>
@endsection
