@extends('adminprogram.layouts.app')

@section('title', 'Detail Evaluasi Berkas Submisi')

@section('content')
<div class="py-6 max-w-4xl mx-auto space-y-6 px-4 sm:px-6">

    <div>
        <a href="{{ route('adminprogram.programs.workspace', $program->id) }}" class="inline-flex items-center text-xs bg-white text-slate-600 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition font-bold border shadow-3xs">
            &larr; Kembali ke Workspace
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="border-b pb-4 mb-6">
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider font-mono">KYC Audit & Grading Panel</span>
            <h3 class="text-xl font-extrabold text-slate-800 mt-2">Submisi: {{ $registration->user->name }}</h3>
            <p class="text-xs text-slate-400 mt-1">Evaluasi Isian Berkas Pada Tahap: <span class="font-bold text-slate-700">{{ $registration->currentStage->name }}</span></p>
        </div>

        <div class="space-y-4 mb-8">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Hasil Dokumen Isian Peserta:</span>

            <!-- Harapan & Motivasi (Tahap Awal) -->
            <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/20 space-y-1.5 shadow-3xs">
                <span class="block text-xs font-bold text-emerald-800 uppercase tracking-wide">Harapan &amp; Motivasi Mengikuti Program</span>
                <p class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $registration->motivation ?? '— (Tidak diisi)' }}</p>
            </div>

            @forelse($stageData->form_values ?? [] as $form)
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-1.5 shadow-3xs">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">{{ $form['field_name'] }} <span class="text-[9px] font-normal uppercase">({{ $form['type'] }})</span></span>

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
                    @else
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $form['value'] ?? '— (Kosong)' }}</p>
                    @endif
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

        <form action="{{ route('adminprogram.programs.applicant.evaluate', [$program->id, $registration->id]) }}" method="POST" class="p-5 bg-gradient-to-br from-emerald-50/20 to-white border border-emerald-100 rounded-2xl space-y-5">
            @csrf
            <span class="block text-xs font-bold uppercase text-emerald-950 tracking-wider">Terbitkan Lembar Otoritas Kelulusan</span>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Keputusan Kelayakan Berkas</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-xl cursor-pointer hover:border-emerald-500 shadow-3xs select-none">
                        <span class="text-xs font-bold text-slate-700">Loloskan Ke Tahap Berikutnya 👍</span>
                        <input type="radio" name="action" value="pass" checked class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-xl cursor-pointer hover:border-rose-500 shadow-3xs select-none">
                        <span class="text-xs font-bold text-slate-700">Gagalkan & Gugurkan Berkas 👎</span>
                        <input type="radio" name="action" value="fail" class="text-rose-600 focus:ring-rose-500 w-4 h-4">
                    </label>
                </div>
            </div>

            {{-- PANEL TAMBAHAN KELULUSAN FINAL: Hanya muncul jika peserta berada di tahapan akhir program --}}
            @if($isLastStage)
                <div class="p-4 bg-amber-50/50 border border-amber-200 rounded-xl space-y-3 shadow-inner" x-data="{ mode: 'auto' }">
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

            <div class="pt-4 border-t flex justify-end">
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
