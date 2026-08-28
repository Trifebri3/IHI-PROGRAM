@extends('adminprogram.layouts.app')

@section('title', 'Detail Evaluasi Berkas Submisi')

@section('content')
<div class="py-6 max-w-4xl mx-auto space-y-6 px-4 sm:px-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('adminprogram.programs.workspace', $program->id) }}" class="inline-flex items-center text-xs bg-white text-slate-600 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition font-bold border shadow-3xs w-fit">
            &larr; Kembali ke Workspace
        </a>
        
        <form action="{{ route('adminprogram.programs.applicant.reset-answers', [$program->id, $registration->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus / mengosongkan seluruh jawaban berkas kuesioner dari peserta ini? Seluruh berkas lampiran fisik juga akan terhapus secara permanen dari server dan status pengisian akan di-reset menjadi kosong.')">
            @csrf
            <button type="submit" class="inline-flex items-center text-xs bg-red-50 hover:bg-red-100 text-red-700 px-3.5 py-2 rounded-xl border border-red-200 transition font-bold shadow-3xs cursor-pointer">
                🗑️ Hapus & Kosongkan Jawaban Peserta
            </button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="border-b pb-4 mb-6">
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider font-mono">KYC Audit & Grading Panel</span>
            <h3 class="text-xl font-extrabold text-slate-800 mt-2">Submisi: {{ $registration->user->name }}</h3>
            <p class="text-xs text-slate-400 mt-1">Evaluasi Isian Berkas Pada Tahap: <span class="font-bold text-slate-700">{{ $registration->currentStage->name }}</span></p>
        </div>

        <!-- Section: Data Dasar Peserta (KYC & Profil Lengkap) -->
        <div class="mb-8 bg-slate-50/60 p-5 rounded-2xl border border-slate-100 space-y-6">
            <div class="border-b pb-3 mb-3 flex items-center justify-between">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informasi Profil & KYC Dasar Peserta
                </h4>
                @if($registration->user->is_blocked)
                    <span class="px-2.5 py-0.5 text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200 rounded-md">DI-BLOKIR</span>
                @else
                    <span class="px-2.5 py-0.5 text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">AKTIF</span>
                @endif
            </div>

            <!-- Profile Summary & Photo Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Col 1: Foto Profil & Info Akun -->
                <div class="space-y-4 text-center md:text-left">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Foto Profil / Avatar</span>
                    <div class="flex flex-col items-center md:items-start space-y-3">
                        @if($registration->user->profile && $registration->user->profile->profile_photo_path)
                            <img src="{{ asset('storage/' . $registration->user->profile->profile_photo_path) }}" class="w-24 h-24 rounded-2xl object-cover border border-slate-200 shadow-sm" alt="Foto Profil">
                        @elseif($registration->user->avatar)
                            <img src="{{ $registration->user->avatar }}" class="w-24 h-24 rounded-2xl object-cover border border-slate-200 shadow-sm" alt="Avatar">
                        @else
                            <div class="w-24 h-24 rounded-2xl bg-emerald-100 flex items-center justify-center border border-emerald-200 shadow-3xs text-emerald-800 font-extrabold text-2xl">
                                {{ strtoupper(substr($registration->user->name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $registration->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $registration->user->email }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">Status Sistem: <span class="font-bold text-slate-700">{{ $registration->user->status }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Identitas Resmi (NIK & KTP) -->
                <div class="space-y-4">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Identitas Resmi (KYC)</span>
                    @if($registration->user->verification)
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-slate-400 font-medium">NIK:</span>
                                <p class="font-mono font-bold text-slate-800 text-sm tracking-wider">{{ $registration->user->verification->nik ?? 'Tidak Ada NIK' }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Status Verifikasi:</span>
                                <div>
                                    @if($registration->user->verification->status === 'verified')
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200/50">TERVERIFIKASI ✔️</span>
                                    @elseif($registration->user->verification->status === 'rejected')
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200/50">DITOLAK ❌</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200/50">PENDING ⏳</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-1 flex flex-col gap-1.5">
                                @if($registration->user->verification->ktp_path)
                                    <a href="{{ asset('storage/' . $registration->user->verification->ktp_path) }}" target="_blank" class="inline-flex items-center text-[10px] font-bold text-emerald-700 hover:underline">
                                        📄 Lihat Foto KTP/Identitas
                                    </a>
                                @endif
                                @if($registration->user->verification->photo_path)
                                    <a href="{{ asset('storage/' . $registration->user->verification->photo_path) }}" target="_blank" class="inline-flex items-center text-[10px] font-bold text-emerald-700 hover:underline">
                                        📷 Lihat Foto Selfie / Pendukung
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Belum melakukan verifikasi akun / NIK.</p>
                    @endif
                </div>

                <!-- Col 3: Alamat Lengkap -->
                <div class="space-y-4">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap</span>
                    @if($registration->user->address)
                        <div class="text-xs text-slate-800 space-y-1">
                            <p><span class="font-bold text-slate-700">Provinsi:</span> {{ $registration->user->address->provinsi }}</p>
                            <p><span class="font-bold text-slate-700">Kabupaten/Kota:</span> {{ $registration->user->address->kabupaten }}</p>
                            <p><span class="font-bold text-slate-700">Kecamatan:</span> {{ $registration->user->address->kecamatan }}</p>
                            <p><span class="font-bold text-slate-700">Desa/Kelurahan:</span> {{ $registration->user->address->desa }}</p>
                            <p><span class="font-bold text-slate-700">Kampung/Dusun:</span> {{ $registration->user->address->kampung ?? '—' }}</p>
                            <p class="mt-1 pt-1 border-t text-[11px] text-slate-500 leading-relaxed font-semibold">
                                <span class="font-bold text-slate-700 block text-[10px] uppercase text-slate-400">Detail Alamat:</span>
                                {{ $registration->user->address->detail_alamat ?? '—' }}
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Data alamat belum diisi.</p>
                    @endif
                </div>
            </div>

            <!-- Biodata Dasar Registrasi Akun -->
            @if($registration->user->biodataValues && $registration->user->biodataValues->isNotEmpty())
                <div class="pt-4 border-t">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Biodata Dasar Registrasi</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($registration->user->biodataValues as $val)
                            @if($val->biodataField)
                                <div class="bg-white p-2.5 rounded-lg border border-slate-100 shadow-3xs flex flex-col justify-between">
                                    <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">{{ $val->biodataField->name }}</span>
                                    @if($val->biodataField->type === 'file')
                                        <a href="{{ asset('storage/' . $val->value) }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline mt-1 block">📄 Unduh Dokumen</a>
                                    @else
                                        <span class="text-xs font-semibold text-slate-800 break-all">{{ $val->value ?? '—' }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Biodata Wajib Tambahan Program -->
            @if($biodataSubmission && !empty($biodataSubmission->submitted_answers))
                <div class="pt-4 border-t">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Form Biodata Wajib Program</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($biodataSubmission->submitted_answers as $fieldKey => $ansValue)
                            <div class="bg-white p-2.5 rounded-lg border border-slate-100 shadow-3xs flex flex-col justify-between">
                                <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">{{ str_replace('_', ' ', $fieldKey) }}</span>
                                @if(is_array($ansValue))
                                    <span class="text-xs font-semibold text-slate-800">{{ implode(', ', $ansValue) }}</span>
                                @elseif(is_string($ansValue) && (str_ends_with(strtolower($ansValue), '.jpg') || str_ends_with(strtolower($ansValue), '.png') || str_ends_with(strtolower($ansValue), '.jpeg') || str_ends_with(strtolower($ansValue), '.pdf')))
                                    <a href="{{ asset('storage/' . $ansValue) }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline mt-1 block">📄 Unduh Dokumen</a>
                                @else
                                    <span class="text-xs font-semibold text-slate-800 break-all">{{ $ansValue ?? '—' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-4 mb-8">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Hasil Dokumen Isian Peserta:</span>

            <!-- Harapan & Motivasi (Tahap Awal) -->
            <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/20 space-y-1.5 shadow-3xs">
                <span class="block text-xs font-bold text-emerald-800 uppercase tracking-wide">Harapan &amp; Motivasi Mengikuti Program</span>
                <p class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $registration->motivation ?? '— (Tidak diisi)' }}</p>
            </div>

            @forelse($stageData->form_values ?? [] as $form)
                @php
                    $needsRevision = isset($form['needs_revision']) && $form['needs_revision'];
                @endphp
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-1.5 shadow-3xs relative" x-data="{ requested: {{ $needsRevision ? 'true' : 'false' }} }">
                    <!-- Checkbox to mark field for revision & Hapus Satuan -->
                    <div class="absolute top-4 right-4 flex items-center gap-2 bg-white border border-slate-200 p-1.5 px-2.5 rounded-lg shadow-3xs">
                        <label class="flex items-center text-[10px] font-bold text-slate-500 cursor-pointer select-none {{ !empty($form['value']) ? 'border-r pr-2 mr-0.5' : '' }}">
                            <input type="checkbox" name="revision_fields[]" value="{{ $form['field_name'] }}" x-model="requested" form="evaluate-form" class="rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 mr-1">
                            Minta Revisi
                        </label>
                        
                        @if(!empty($form['value']))
                            <form action="{{ route('adminprogram.programs.applicant.reset-single-answer', [$program->id, $registration->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mengosongkan isian bidang ini saja? Berkas lampiran fisik (jika ada) akan dihapus permanen.')">
                                @csrf
                                <input type="hidden" name="field_name" value="{{ $form['field_name'] }}">
                                <button type="submit" class="text-rose-600 hover:text-rose-800 transition text-[10px] font-extrabold flex items-center gap-0.5 cursor-pointer">
                                    🗑️ Hapus
                                </button>
                            </form>
                        @endif
                    </div>

                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide mr-24">{{ $form['field_name'] }} <span class="text-[9px] font-normal uppercase">({{ $form['type'] }})</span></span>

                    @if($form['type'] === 'file')
                        @if(!empty($form['value']))
                            <div class="pt-1">
                                <a href="{{ asset('storage/' . $form['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                    📥 Unduh / Lihat Berkas Fisik Lampiran
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-rose-500 font-bold italic">⚠️ Berkas wajib tidak diunggah peserta!</p>
                        @endif
                    @elseif($form['type'] === 'image')
                        @if(!empty($form['value']))
                            <div class="pt-1 space-y-2">
                                <a href="{{ asset('storage/' . $form['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                    🔍 Buka Gambar Ukuran Penuh
                                </a>
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $form['value']) }}" class="max-w-md max-h-64 rounded-xl border border-slate-200 shadow-sm object-cover" alt="Lampiran Gambar">
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-rose-500 font-bold italic">⚠️ Gambar wajib tidak diunggah peserta!</p>
                        @endif
                    @elseif($form['type'] === 'url')
                        @if(!empty($form['value']))
                            <div class="pt-1">
                                <a href="{{ $form['value'] }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                    🔗 Buka Tautan Link ({{ $form['value'] }})
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-rose-500 font-bold italic">⚠️ Tautan link wajib tidak diisi peserta!</p>
                        @endif
                    @else
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $form['value'] ?? '— (Kosong)' }}</p>
                    @endif

                    <!-- Catatan Revisi per Bidang -->
                    <div class="mt-3 pt-2.5 border-t border-dashed border-slate-200" x-show="requested" x-transition>
                        <label class="block text-[10px] font-bold text-amber-800 uppercase mb-1">Catatan Koreksi Bidang Ini:</label>
                        <input type="text" name="revision_notes[{{ $form['field_name'] }}]" value="{{ $form['revision_note'] ?? '' }}" form="evaluate-form" placeholder="Tuliskan keterangan detail koreksi yang harus diperbaiki peserta..." class="w-full p-2 border border-amber-200 rounded-lg text-xs focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>
            @empty
                <div class="p-4 text-center bg-slate-50 text-slate-400 italic text-xs rounded-xl border border-dashed">
                    Tidak ada isian form kustom pada tahapan ini.
                </div>
            @endforelse
        </div>

        @php
            // Deteksi otomatis apakah ini tahapan urutan terakhir dari program
            $isLastStage = !\App\Models\ProgramStage::where('program_id', $program->id)
                ->where('sequence', $registration->currentStage->sequence + 1)
                ->exists();
        @endphp

        {{-- Form untuk Tandai Sudah Diperiksa --}}
        <form id="mark-checked-form" action="{{ route('adminprogram.workspace.update_checking', $program->id) }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="registration_ids[]" value="{{ $registration->id }}">
            <input type="hidden" name="is_checked" value="checked">
            <input type="hidden" name="redirect_url" value="{{ route('adminprogram.programs.applicant.show', [$program->id, $registration->id]) }}">
        </form>

        <form id="evaluate-form" action="{{ route('adminprogram.programs.applicant.evaluate', [$program->id, $registration->id]) }}" method="POST" class="p-5 bg-gradient-to-br from-emerald-50/20 to-white border border-emerald-100 rounded-2xl space-y-5" x-data="{ action: '{{ (isset($stageData) && $stageData->status === 'revision') ? 'revision' : 'pass' }}' }">
            @csrf
            <span class="block text-xs font-bold uppercase text-emerald-950 tracking-wider">Terbitkan Lembar Otoritas Kelulusan</span>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Keputusan Kelayakan Berkas</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-xl cursor-pointer hover:border-emerald-500 shadow-3xs select-none">
                        <span class="text-xs font-bold text-slate-700">Loloskan Ke Tahap Berikutnya 👍</span>
                        <input type="radio" name="action" value="pass" x-model="action" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-xl cursor-pointer hover:border-amber-500 shadow-3xs select-none">
                        <span class="text-xs font-bold text-slate-700">Kembalikan untuk Revisi 📝</span>
                        <input type="radio" name="action" value="revision" x-model="action" class="text-amber-600 focus:ring-amber-500 w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-xl cursor-pointer hover:border-rose-500 shadow-3xs select-none">
                        <span class="text-xs font-bold text-slate-700">Gagalkan & Gugurkan Berkas 👎</span>
                        <input type="radio" name="action" value="fail" x-model="action" class="text-rose-600 focus:ring-rose-500 w-4 h-4">
                    </label>
                </div>
            </div>

            {{-- PANEL TAMBAHAN KELULUSAN FINAL: Hanya muncul jika peserta berada di tahapan akhir program dan diloloskan --}}
            @if($isLastStage)
                <div class="p-4 bg-amber-50/50 border border-amber-200 rounded-xl space-y-3 shadow-inner" x-show="action === 'pass'" x-data="{ mode: 'auto' }">
                    <span class="block text-xs font-bold text-amber-900 uppercase tracking-wide">⚡ PENENTUAN ID INDUK PROGRAM (FINAL STAGE DETECTED)</span>

                    <div class="flex items-center space-x-4 bg-white p-2 rounded-lg border border-amber-200 w-fit">
                        <label class="flex items-center text-xs font-bold text-slate-600 cursor-pointer">
                            <input type="radio" name="generation_mode" value="auto" x-model="mode" class="text-emerald-600 mr-1"> Generator Otomatis
                        </label>
                        <label class="flex items-center text-xs font-bold text-slate-600 cursor-pointer ml-2">
                            <input type="radio" name="generation_mode" value="manual" x-model="mode" class="text-emerald-600 mr-1"> Manual Override Bypass
                        </label>
                    </div>

                    <div x-show="mode === 'manual'" class="pt-1" style="display: none;">
                        <input type="text" name="manual_id_input" placeholder="Ketikkan Nomor Induk Kustom Kampus (Cth: NIP-2026-001)" class="w-full p-2.5 border border-amber-300 rounded-xl text-xs font-mono font-bold tracking-wider bg-white">
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Catatan / Feedback Reviewer (Opsional)</label>
                <textarea name="reviewer_notes" placeholder="Tuliskan alasan penolakan atau catatan tambahan untuk peserta..." class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500" rows="3"></textarea>
            </div>

            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="submit" form="mark-checked-form" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold rounded-xl shadow-md hover:from-emerald-700 transition-all text-xs uppercase tracking-wider flex items-center gap-1.5 cursor-pointer">
                    ✓ Tandai Sudah Diperiksa
                </button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-black text-white font-bold rounded-xl shadow-md transition-all text-xs uppercase tracking-wider">
                    Eksekusi & Kirim Pengumuman
                </button>
            </div>
        </form>
    </div>
<div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs mb-4">
            <div class="border-b pb-2 mb-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">📝 Pengisian Transkrip Nilai E-Raport Anggota (Beban {{ $program->total_hours ?? 32 }} JP)</h4>
            </div>

            @if(empty($program->score_schema))
                <p class="text-xs text-slate-400 italic">Format kriteria nilai belum dirancang oleh Anda di workspace utama. Silakan isi skema judul kriteria nilai terlebih dahulu.</p>
            @else
                <form action="{{ route('adminprogram.programs.applicant.scores.save', [$program->id, $registration->id]) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50/60 p-4 rounded-xl border">
                        @php
                            // Ambil riwayat isi nilai jika sudah pernah disimpan sebelumnya
                            $existingScores = collect($registration->final_scores)->keyBy('title');
                        @endphp
                        @foreach($program->score_schema as $index => $criteriaName)
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1 truncate" title="{{ $criteriaName }}">⭐ {{ $criteriaName }}</label>
                                <input type="text"
                                       name="criterion_{{ $index }}"
                                       value="{{ $existingScores->has($criteriaName) ? $existingScores->get($criteriaName)['score'] : '' }}"
                                       placeholder="Cth: 85 / A"
                                       class="w-full p-2 border rounded-xl text-xs bg-white text-slate-800 font-mono font-bold tracking-wide" required>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl uppercase tracking-wider shadow-xs">
                            💾 Simpan & Update Transkrip Raport
                        </button>
                    </div>
                </form>
            @endif
        </div>
</div>
@endsection
