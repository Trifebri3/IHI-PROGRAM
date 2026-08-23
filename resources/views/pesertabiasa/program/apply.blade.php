@extends('pesertabiasa.layouts.app')

@section('title', 'Formulir Aplikasi Registrasi')

@section('content')
<div class="py-6 max-w-3xl mx-auto space-y-6 px-4 sm:px-6">

    <div>
        <a href="{{ route('programs.catalog') }}" class="inline-flex items-center text-xs bg-white text-slate-600 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition font-bold border shadow-3xs">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="border-b pb-4 mb-6">
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider font-mono">Dynamic Form Pipeline</span>
            <h3 class="text-xl font-extrabold text-slate-800 tracking-tight mt-2.5">{{ $program->name }}</h3>
            <p class="text-xs text-slate-400 mt-1">Tahapan Aktif: <span class="font-bold text-slate-700">{{ $currentStage->name }}</span></p>
        </div>

        <!-- Banner Notifikasi Draf (Tersembunyi secara default) -->
        <div id="draft-restored-banner" class="hidden mb-5 p-3.5 bg-emerald-550/10 border border-emerald-550/30 rounded-2xl text-xs text-emerald-800 font-bold flex items-center justify-between shadow-3xs">
            <span class="flex items-center gap-2">Draf jawaban otomatis Anda telah dipulihkan dari peranti ini.</span>
            <button type="button" onclick="document.getElementById('draft-restored-banner').remove()" class="text-emerald-800 hover:text-emerald-950 font-bold px-1.5 text-sm">&times;</button>
        </div>

        <form id="apply-program-form" action="{{ route('program.apply.store', $program->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if($isStageLocked)
                <div class="p-5 bg-gradient-to-br from-rose-50 to-rose-100/50 border border-rose-200 rounded-2xl space-y-2 mb-6 shadow-3xs text-rose-900 animate-in fade-in duration-300">
                    <div class="flex items-center space-x-2 font-extrabold text-xs">
                        <span>🔒</span>
                        <span>TAHAPAN BELUM DIBUKA / SEDANG DIKUNCI:</span>
                    </div>
                    <div class="text-xs leading-relaxed font-semibold">
                        Tahapan ini belum dibuka atau sedang dikunci sementara oleh panitia. Anda dapat melihat struktur formulir di bawah ini, namun **tidak diperkenankan untuk mengisi, menyimpan draf, atau mengirimkan berkas** saat ini.
                    </div>
                </div>
            @endif

            @if($currentStage->instruction)
                <div class="p-5 bg-gradient-to-br from-blue-50 to-indigo-50/50 border border-blue-100 rounded-2xl space-y-2 mb-6 shadow-3xs">
                    <div class="flex items-center space-x-2 text-blue-900 font-extrabold text-xs">
                        <span>📢</span>
                        <span>INFORMASI &amp; INSTRUKSI TAHAPAN:</span>
                    </div>
                    <div class="text-xs text-slate-700 leading-relaxed font-normal prose prose-slate max-w-none">
                        {!! $currentStage->instruction !!}
                    </div>
                </div>
            @endif

            @if(isset($stageData) && in_array($stageData->status, ['failed', 'revision']))
                <div class="p-4 rounded-2xl space-y-2 mb-5 shadow-3xs border {{ $stageData->status === 'revision' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                    <div class="flex items-center space-x-2 font-bold text-xs">
                        <span>⚠️</span>
                        <span>{{ $stageData->status === 'revision' ? 'PERMINTAAN REVISI DARI PANITIA:' : 'PETUNJUK PERBAIKAN DARI PANITIA:' }}</span>
                    </div>
                    <p class="text-xs leading-relaxed font-semibold">
                        {{ $stageData->status === 'revision'
                            ? 'Beberapa bagian berkas Anda memerlukan revisi. Silakan periksa kolom yang ditandai dengan warna kuning di bawah, perbaiki nilainya, lalu kirim kembali.'
                            : 'Jawaban Anda sebelumnya ditolak atau butuh perbaikan. Silakan periksa kembali jawaban dan berkas Anda di bawah ini, perbaiki bagian yang salah, lalu klik kirim kembali.' }}
                    </p>
                    @if(!empty($stageData->reviewer_notes))
                        <div class="p-3 bg-white border border-slate-100 rounded-xl text-xs text-slate-700 mt-2 shadow-3xs">
                            <span class="font-extrabold block mb-0.5 {{ $stageData->status === 'revision' ? 'text-amber-800' : 'text-rose-800' }}">Catatan Reviewer:</span>
                            {{ $stageData->reviewer_notes }}
                        </div>
                    @endif
                </div>
            @endif

            @if(!$registration)
                <div class="mb-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Harapan &amp; Motivasi Mengikuti Program <span class="text-rose-500 font-black">*</span>
                    </label>
                    <textarea name="motivation" 
                              placeholder="Tuliskan harapan dan motivasi Anda mengikuti program ini secara rinci..." 
                              class="w-full p-2.5 border @error('motivation') border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 transition shadow-3xs {{ $isStageLocked ? 'bg-slate-50 text-slate-500 cursor-not-allowed border-rose-200' : '' }}" 
                              rows="4" 
                              {{ $isStageLocked ? 'readonly' : 'required' }}>{{ old('motivation') }}</textarea>
                    @error('motivation')
                        <span class="text-xs text-rose-600 font-semibold mt-1.5 block flex items-center">
                            ⚠️ {{ $message }}
                        </span>
                    @enderror
                </div>
            @endif

            @forelse($currentStage->form_schema as $index => $field)
                @php 
                    $inputName = "field_" . $index; 
                    $prevVal = $previousValues->get($field['name'])['value'] ?? '';
                    
                    // Cek status revisi bidang kustom ini
                    $fieldData = $previousValues->get($field['name']);
                    $needsRevision = isset($fieldData['needs_revision']) && $fieldData['needs_revision'];
                    
                    // Bidang dapat diedit jika bukan dalam masa revisi, atau merupakan bidang yang ditunjuk untuk direvisi
                    $isEditable = ($stageData && $stageData->status === 'revision') ? $needsRevision : true;
                    if ($isStageLocked) {
                        $isEditable = false;
                    }
                    $isFieldRequired = $field['required'] && empty($prevVal) && $isEditable;
                    
                    // Deteksi warna box pembungkus
                    $boxStyle = 'border-slate-100 bg-transparent';
                    if ($stageData && $stageData->status === 'revision') {
                        if ($needsRevision) {
                            $boxStyle = 'border-amber-350 bg-amber-50/10 ring-1 ring-amber-200';
                        } else {
                            $boxStyle = 'border-emerald-300 bg-emerald-50/10 ring-1 ring-emerald-200';
                        }
                    }
                @endphp

                <div class="p-4 rounded-2xl border transition-all {{ $boxStyle }} space-y-1.5 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex justify-between items-center">
                        <span>
                            {{ $field['name'] }}
                            @if($field['required']) <span class="text-rose-500 font-black">*</span> @endif
                        </span>
                        
                        @if($stageData && $stageData->status === 'revision')
                            @if($needsRevision)
                                <span class="text-[9px] font-black text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded uppercase tracking-wider animate-pulse">⚠️ Perlu Revisi</span>
                            @else
                                <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded uppercase tracking-wider">✓ Sudah Sesuai</span>
                            @endif
                        @endif
                    </label>

                    @if($needsRevision && !empty($fieldData['revision_note']))
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 font-semibold leading-relaxed mb-1 shadow-3xs">
                            💡 <span class="font-extrabold uppercase text-[9px] tracking-wide text-amber-800">Catatan Panitia:</span> {{ $fieldData['revision_note'] }}
                        </div>
                    @endif

                    @if(!empty($field['instruction']))
                        <div class="text-xs text-slate-700 leading-relaxed mt-1 font-medium instruction-content">
                            @if(strip_tags($field['instruction']) !== $field['instruction'])
                                {!! $field['instruction'] !!}
                            @else
                                {!! preg_replace(
                                    '/(https?:\/\/[^\s\r\n\t]+(?<![.,;:]))/i',
                                    '<a href="$1" target="_blank" class="text-emerald-600 hover:text-emerald-800 hover:underline font-extrabold transition-all break-all">$1</a>',
                                    e($field['instruction'])
                                ) !!}
                            @endif
                        </div>
                    @endif

                    @if(!empty($field['placeholder']))
                        <div class="text-[11px] text-slate-500 bg-slate-50/50 p-2 rounded-lg border border-slate-100/60 mt-1 flex items-start gap-1">
                            <span class="font-bold text-slate-400 shrink-0">Contoh:</span>
                            <span class="text-slate-600 font-medium">{{ $field['placeholder'] }}</span>
                        </div>
                    @endif

                    @if($field['type'] === 'text')
                        <input type="text"
                               name="{{ $inputName }}"
                               value="{{ old($inputName, $prevVal) }}"
                               placeholder="{{ !empty($field['placeholder']) ? $field['placeholder'] : 'Ketikkan jawaban Anda disini...' }}"
                               class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 placeholder:text-slate-300 transition shadow-3xs {{ !$isEditable ? 'bg-slate-50 text-slate-550 cursor-not-allowed border-emerald-200' : '' }}"
                               {{ $isFieldRequired ? 'required' : '' }}
                               {{ !$isEditable ? 'readonly' : '' }}>

                    @elseif($field['type'] === 'url')
                        <input type="url"
                               name="{{ $inputName }}"
                               value="{{ old($inputName, $prevVal) }}"
                               placeholder="{{ !empty($field['placeholder']) ? $field['placeholder'] : 'Masukkan link URL (diawali dengan http:// atau https://)...' }}"
                               class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 placeholder:text-slate-300 transition shadow-3xs {{ !$isEditable ? 'bg-slate-50 text-slate-550 cursor-not-allowed border-emerald-200' : '' }}"
                               {{ $isFieldRequired ? 'required' : '' }}
                               {{ !$isEditable ? 'readonly' : '' }}>

                    @elseif($field['type'] === 'textarea')
                        <textarea name="{{ $inputName }}"
                                  placeholder="{{ !empty($field['placeholder']) ? $field['placeholder'] : 'Ketikkan jawaban panjang Anda disini...' }}"
                                  class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 placeholder:text-slate-300 transition shadow-3xs {{ !$isEditable ? 'bg-slate-50 text-slate-505 cursor-not-allowed border-emerald-200' : '' }}"
                                  rows="4"
                                  {{ $isFieldRequired ? 'required' : '' }}
                                  {{ !$isEditable ? 'readonly' : '' }}>{{ old($inputName, $prevVal) }}</textarea>

                    @elseif($field['type'] === 'file')
                        @if($isEditable)
                            <input type="file"
                                   name="{{ $inputName }}"
                                   class="w-full p-2 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition shadow-3xs"
                                   {{ $isFieldRequired ? 'required' : '' }}
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar"
                                   onchange="handleFileChange(this, '{{ addslashes($field['name']) }}', 5)">
                            <span class="text-[10px] text-slate-400 block">Berkas sah: PDF, Word, Excel, ZIP, RAR (Maksimal: 5MB)</span>
                        @else
                            @if($isStageLocked)
                                <div class="text-xs text-rose-700 font-semibold italic bg-rose-50/30 p-2 border border-rose-200 rounded-xl">🔒 Pengunggahan berkas dikunci sementara oleh panitia</div>
                            @else
                                <div class="text-xs text-emerald-700 font-semibold italic bg-emerald-50/30 p-2 border border-emerald-200 rounded-xl">✓ Berkas terverifikasi aman &amp; tidak perlu direvisi</div>
                            @endif
                        @endif

                        @if(!empty($prevVal))
                            <div class="mt-1.5 text-[11px] text-emerald-700 font-bold bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100 flex items-center gap-1.5 shadow-3xs">
                                <span>📎 Berkas sudah diunggah:</span>
                                <a href="{{ asset('storage/' . $prevVal) }}" target="_blank" class="underline hover:text-emerald-950 transition">Lihat Berkas Lampiran</a>
                            </div>
                        @endif

                    @elseif($field['type'] === 'image')
                        @if($isEditable)
                            <input type="file"
                                   name="{{ $inputName }}"
                                   id="img_input_{{ $index }}"
                                   onchange="compressAndPreviewImage(this, 'img_preview_{{ $index }}', '{{ addslashes($field['name']) }}', 5)"
                                   class="w-full p-2 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition shadow-3xs"
                                   {{ $isFieldRequired ? 'required' : '' }}
                                   accept="image/png, image/jpeg, image/jpg">
                            <span class="text-[10px] text-slate-400 block">Ekstensi gambar sah: PNG, JPG, JPEG (Maksimal: 5MB, otomatis dikompres &amp; resize)</span>
                        @else
                            @if($isStageLocked)
                                <div class="text-xs text-rose-700 font-semibold italic bg-rose-50/30 p-2 border border-rose-200 rounded-xl">🔒 Pengunggahan gambar dikunci sementara oleh panitia</div>
                            @else
                                <div class="text-xs text-emerald-700 font-semibold italic bg-emerald-50/30 p-2 border border-emerald-200 rounded-xl">✓ Gambar terverifikasi aman &amp; tidak perlu direvisi</div>
                            @endif
                        @endif

                        <div id="img_preview_{{ $index }}" class="mt-2 space-y-1">
                            @if(!empty($prevVal))
                                <div class="relative inline-block mt-2">
                                    <img src="{{ asset('storage/' . $prevVal) }}" class="max-w-xs max-h-40 rounded-xl border border-slate-200 shadow-sm object-cover" alt="Unggahan Sebelumnya">
                                    <span class="block text-[10px] text-emerald-700 font-bold mt-1">✓ Gambar tersimpan di server</span>
                                </div>
                            @endif
                        </div>

                    @elseif($field['type'] === 'dropdown')
                        <select name="{{ $inputName }}"
                                class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 text-slate-700 transition shadow-3xs {{ !$isEditable ? 'bg-slate-50 text-slate-400 cursor-not-allowed border-emerald-200' : '' }}"
                                {{ $isFieldRequired ? 'required' : '' }}
                                {{ !$isEditable ? 'disabled' : '' }}>
                            <option value="">-- Pilih salah satu opsi --</option>
                            @foreach($field['options'] ?? [] as $opt)
                                <option value="{{ $opt }}" {{ old($inputName, $prevVal) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @if(!$isEditable)
                            <input type="hidden" name="{{ $inputName }}" value="{{ $prevVal }}">
                        @endif

                    @elseif($field['type'] === 'datetime')
                        @php
                            $dateVal = old($inputName, $prevVal);
                            if ($dateVal && strtotime($dateVal)) {
                                $dateVal = date('Y-m-d\TH:i', strtotime($dateVal));
                            }
                        @endphp
                        <input type="datetime-local"
                               name="{{ $inputName }}"
                               value="{{ $dateVal }}"
                               class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition shadow-3xs {{ !$isEditable ? 'bg-slate-50 text-slate-450 cursor-not-allowed border-emerald-200' : '' }}"
                               {{ $isFieldRequired ? 'required' : '' }}
                               {{ !$isEditable ? 'readonly' : '' }}>

                    @elseif($field['type'] === 'options')
                        <div class="space-y-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-100 shadow-3xs {{ !$isEditable ? 'border-emerald-200' : '' }}">
                            @foreach($field['options'] ?? [] as $opt)
                                <label class="flex items-center text-xs font-semibold text-slate-700 cursor-pointer select-none">
                                    <input type="radio"
                                           name="{{ $inputName }}"
                                           value="{{ $opt }}"
                                           {{ old($inputName, $prevVal) === $opt ? 'checked' : '' }}
                                           class="text-emerald-600 focus:ring-emerald-500 mr-2 w-4 h-4 border-slate-300 shadow-3xs"
                                           {{ $isFieldRequired ? 'required' : '' }}
                                           {{ !$isEditable ? 'disabled' : '' }}>
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if(!$isEditable)
                            <input type="hidden" name="{{ $inputName }}" value="{{ $prevVal }}">
                        @endif

                    @elseif($field['type'] === 'checkbox')
                        @php
                            $oldVals = old($inputName, $prevVal ?? []);
                            if (is_string($oldVals)) {
                                $oldVals = array_map('trim', explode(', ', $oldVals));
                            }
                        @endphp
                        <div class="space-y-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-100 shadow-3xs {{ !$isEditable ? 'border-emerald-200' : '' }}">
                            @foreach($field['options'] ?? [] as $opt)
                                <label class="flex items-center text-xs font-semibold text-slate-700 cursor-pointer select-none">
                                    <input type="checkbox"
                                           name="{{ $inputName }}[]"
                                           value="{{ $opt }}"
                                           {{ in_array($opt, (array)$oldVals) ? 'checked' : '' }}
                                           class="rounded text-emerald-600 focus:ring-emerald-500 mr-2 w-4 h-4 border-slate-300 shadow-3xs"
                                           {{ !$isEditable ? 'disabled' : '' }}>
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if(!$isEditable)
                            @foreach((array)$oldVals as $oldV)
                                <input type="hidden" name="{{ $inputName }}[]" value="{{ $oldV }}">
                            @endforeach
                        @endif
                    @endif

                    @error($inputName)
                        <span class="text-xs text-rose-600 font-semibold mt-1 block flex items-center">
                            ⚠️ {{ $message }}
                        </span>
                    @enderror
                </div>
            @empty
                <div class="p-6 bg-slate-50 text-slate-500 text-xs rounded-xl border border-dashed border-slate-200 text-center font-medium">
                    Tahapan pendaftaran ini tidak membutuhkan unggahan dokumen suplemen tambahan. Anda diizinkan langsung mengirim form pengajuan.
                </div>
            @endforelse

            <div class="pt-5 border-t flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <!-- Progress & Save Status Indicator -->
                <div class="flex flex-col space-y-1">
                    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
                        <div class="relative w-28 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-150 shadow-3xs">
                            <div id="progress-bar" class="h-full bg-emerald-550 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <span id="progress-text" class="font-mono text-slate-650">0% Terisi</span>
                    </div>
                    <span id="save-time-text" class="text-[10px] text-slate-400 font-medium">Terakhir disimpan: Belum ada draf</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" id="clear-draft-btn" class="px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-700 border border-slate-200 font-extrabold rounded-xl transition-all text-xs uppercase tracking-wider {{ $isStageLocked ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" {{ $isStageLocked ? 'disabled' : '' }} title="Hapus draf & reset form">
                        Reset Form
                    </button>
                    <button type="button" id="manual-save-draft-btn" class="px-5 py-2.5 bg-white text-emerald-700 hover:bg-slate-50 border border-slate-200 font-extrabold rounded-xl transition-all text-xs uppercase tracking-wider flex items-center gap-1.5 shadow-3xs {{ $isStageLocked ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" {{ $isStageLocked ? 'disabled' : '' }}>
                        <svg id="draft-loading-icon" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-emerald-700 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Draf</span>
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r {{ $isStageLocked ? 'from-slate-400 to-slate-500 cursor-not-allowed' : 'from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 shadow-md shadow-emerald-50 cursor-pointer' }} text-white font-bold rounded-xl transition-all text-xs uppercase tracking-wider" {{ $isStageLocked ? 'disabled' : '' }}>
                        Kirim Berkas Registrasi
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($errors->any())
        <script>
            window.laravelErrors = @json($errors->all());
        </script>
    @endif

    <!-- Image preview, compression, validation, autosave & logs script -->
    <script>
        const programId = @json($program->id);
        const stageId = @json($currentStage->id);
        const draftKey = `gli_draft_${programId}_${stageId}`;
        const isStageLocked = @json($isStageLocked);
        function addLog(category, message) {
            const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
            console.log(`[${time}] [${category}] ${message}`);
        }

        // File size validation handler
        function handleFileChange(input, fieldName, maxMb = 5) {
            const file = input.files[0];
            if (!file) return;

            const maxSize = maxMb * 1024 * 1024;
            const fileSizeMb = (file.size / (1024 * 1024)).toFixed(2);

            if (file.size > maxSize) {
                addLog('Error', `Berkas "${file.name}" untuk "${fieldName}" berukuran ${fileSizeMb} MB. Melebihi batas maksimal ${maxMb} MB!`);
                alert(`Batas ukuran berkas adalah ${maxMb} MB. Berkas Anda (${fileSizeMb} MB) terlalu besar dan ditolak.`);
                input.value = ''; // Reset file input
                input.classList.remove('border-slate-200', 'border-emerald-350');
                input.classList.add('border-rose-300', 'bg-rose-50/10');
            } else {
                input.classList.remove('border-rose-300', 'bg-rose-50/10');
                input.classList.add('border-emerald-350', 'bg-emerald-50/10');
                addLog('Berkas', `Berkas "${file.name}" (${fileSizeMb} MB) lolos validasi ukuran & siap diunggah.`);
            }
        }

        // Image compression and size check
        function compressAndPreviewImage(input, previewId, fieldName, maxMb = 5) {
            const file = input.files[0];
            const previewContainer = document.getElementById(previewId);
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (!file) return;

            // Verify if it's an image
            if (!file.type.startsWith('image/')) {
                previewContainer.innerHTML = '<span class="text-xs text-rose-600 font-bold">Berkas wajib berupa gambar.</span>';
                addLog('Error', `Berkas untuk "${fieldName}" bukan berupa gambar yang valid.`);
                return;
            }

            const fileSizeMb = (file.size / (1024 * 1024)).toFixed(2);
            addLog('Berkas', `Membaca gambar "${file.name}" (${fileSizeMb} MB) untuk "${fieldName}"...`);

            // Preview container design
            const imgWrapper = document.createElement('div');
            imgWrapper.className = 'relative inline-block mt-2';
            
            const imgPreview = document.createElement('img');
            imgPreview.className = 'max-w-xs max-h-48 rounded-xl border border-slate-200 shadow-sm object-cover';
            imgPreview.src = URL.createObjectURL(file);
            
            imgWrapper.appendChild(imgPreview);
            previewContainer.appendChild(imgWrapper);

            // Size info
            const infoText = document.createElement('p');
            infoText.className = 'text-[10px] text-slate-400 font-mono mt-1';
            infoText.innerText = `Ukuran Asli: ${fileSizeMb} MB`;
            previewContainer.appendChild(infoText);

            // Compress using HTML Canvas
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    // Max dimensions for compression (1200px max width/height)
                    const MAX_SIZE = 1200;
                    if (width > height) {
                        if (width > MAX_SIZE) {
                            height *= MAX_SIZE / width;
                            width = MAX_SIZE;
                        }
                    } else {
                        if (height > MAX_SIZE) {
                            width *= MAX_SIZE / height;
                            height = MAX_SIZE;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Compress to jpeg quality 0.75
                    canvas.toBlob(function(blob) {
                        const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + "_compressed.jpg", {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        const compSizeMb = (compressedFile.size / (1024 * 1024)).toFixed(2);

                        if (compressedFile.size > maxMb * 1024 * 1024) {
                            addLog('Error', `Gambar terkompresi (${compSizeMb} MB) masih melebihi batas maksimal ${maxMb} MB!`);
                            alert(`Batas ukuran gambar terkompresi adalah ${maxMb} MB. Hasil kompresi (${compSizeMb} MB) terlalu besar dan ditolak.`);
                            input.value = ''; // Reset file input
                            previewContainer.innerHTML = '<span class="text-xs text-rose-600 font-bold">Gambar ditolak karena terlalu besar.</span>';
                            input.classList.remove('border-slate-200', 'border-emerald-350');
                            input.classList.add('border-rose-300', 'bg-rose-50/10');
                        } else {
                            // Inject back to input files using DataTransfer
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(compressedFile);
                            input.files = dataTransfer.files;

                            input.classList.remove('border-rose-300', 'bg-rose-50/10');
                            input.classList.add('border-emerald-350', 'bg-emerald-50/10');

                            // Update size info
                            infoText.innerText = `Ukuran Asli: ${fileSizeMb} MB | Ukuran Kompresi: ${compSizeMb} MB`;
                            addLog('Berkas', `Gambar terkompresi berhasil dibuat (${compSizeMb} MB) & siap diunggah.`);
                        }
                    }, 'image/jpeg', 0.75);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Autosave logic
        function saveDraft() {
            if (isStageLocked) return;
            const form = document.getElementById('apply-program-form');
            if (!form) return;
            const formData = {};
            
            // Save motivation text if exists
            const motivationInput = form.querySelector('textarea[name="motivation"]');
            if (motivationInput) {
                formData['motivation'] = motivationInput.value;
            }

            // Save dynamic fields
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                // Skip token, method, files
                if (input.name === '_token' || input.name === '_method' || input.type === 'file') return;
                
                if (input.type === 'checkbox') {
                    if (!formData[input.name]) {
                        formData[input.name] = [];
                    }
                    if (input.checked) {
                        formData[input.name].push(input.value);
                    }
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        formData[input.name] = input.value;
                    }
                } else {
                    formData[input.name] = input.value;
                }
            });

            localStorage.setItem(draftKey, JSON.stringify(formData));
            
            // Update visual save indicator
            const lastSavedTime = new Date().toLocaleTimeString('id-ID', { hour12: false });
            const saveTimeText = document.getElementById('save-time-text');
            if (saveTimeText) {
                saveTimeText.innerText = `Terakhir disimpan: ${lastSavedTime}`;
            }
            
            addLog('Autosave', 'Draf formulir otomatis berhasil diperbarui.');
        }

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        const debouncedSave = debounce(saveDraft, 1000);

        // Restore draft
        function restoreDraft() {
            const form = document.getElementById('apply-program-form');
            if (!form) return;
            const rawData = localStorage.getItem(draftKey);
            if (!rawData) {
                addLog('Sistem', 'Tidak ada draf lokal ditemukan. Memulai formulir baru.');
                return;
            }

            try {
                const formData = JSON.parse(rawData);
                let restoredCount = 0;

                // Restore motivation
                if (formData['motivation']) {
                    const motivationInput = form.querySelector('textarea[name="motivation"]');
                    if (motivationInput) {
                        motivationInput.value = formData['motivation'];
                        restoredCount++;
                    }
                }

                // Restore dynamic fields
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.name === '_token' || input.name === '_method' || input.type === 'file') return;

                    if (input.type === 'checkbox') {
                        const values = formData[input.name];
                        if (Array.isArray(values) && values.includes(input.value)) {
                            input.checked = true;
                            restoredCount++;
                        }
                    } else if (input.type === 'radio') {
                        if (formData[input.name] === input.value) {
                            input.checked = true;
                            restoredCount++;
                        }
                    } else {
                        if (formData[input.name] !== undefined) {
                            input.value = formData[input.name];
                            restoredCount++;
                        }
                    }
                });

                if (restoredCount > 0) {
                    addLog('Sistem', `Menemukan draf lokal. ${restoredCount} bidang input berhasil dipulihkan.`);
                    const banner = document.getElementById('draft-restored-banner');
                    if (banner) {
                        banner.classList.remove('hidden');
                    }
                }
            } catch (e) {
                console.error('Gagal memulihkan draf:', e);
                addLog('Error', 'Gagal memulihkan draf dari penyimpanan lokal.');
            }
        }

        // Calculate progress percentage of filled fields in the form
        function updateFormProgress() {
            const form = document.getElementById('apply-program-form');
            if (!form) return;

            const inputs = form.querySelectorAll('textarea[name="motivation"], input[name^="field_"], textarea[name^="field_"], select[name^="field_"]');
            
            // Filter unique names to handle radio groups or checkboxes correctly
            const uniqueFieldNames = new Set();
            inputs.forEach(input => {
                let name = input.name;
                if (name.endsWith('[]')) {
                    name = name.substring(0, name.length - 2);
                }
                uniqueFieldNames.add(name);
            });

            const totalFields = uniqueFieldNames.size;
            if (totalFields === 0) return;

            let filledFields = 0;
            uniqueFieldNames.forEach(fieldName => {
                const fieldGroup = form.querySelectorAll(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                let isFilled = false;
                
                fieldGroup.forEach(el => {
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.checked) isFilled = true;
                    } else if (el.type === 'file') {
                        if (el.files.length > 0) isFilled = true;
                        // Search parent or sibling for previous file upload hint
                        const parent = el.closest('.p-4');
                        if (parent && parent.querySelector('.underline')) isFilled = true;
                    } else {
                        if (el.value.trim() !== '') isFilled = true;
                    }
                });

                if (isFilled) filledFields++;
            });

            const progressPercent = Math.round((filledFields / totalFields) * 100);
            
            // Update progress elements
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            if (progressBar && progressText) {
                progressBar.style.width = `${progressPercent}%`;
                progressText.innerText = `${progressPercent}% Terisi (${filledFields}/${totalFields})`;
                
                if (progressPercent === 100) {
                    progressBar.className = 'h-full bg-emerald-600 rounded-full transition-all duration-500';
                } else {
                    progressBar.className = 'h-full bg-emerald-550 rounded-full transition-all duration-500';
                }
            }
        }

        // Initialize listeners and setup page
        document.addEventListener('DOMContentLoaded', () => {
            addLog('Sistem', 'Formulir aplikasi dimuat. Draf otomatis aktif.');
            
            // Show Laravel errors in log if present
            if (window.laravelErrors && window.laravelErrors.length > 0) {
                window.laravelErrors.forEach(err => {
                    addLog('Error', `Kegagalan Server: ${err}`);
                });
            }

            // Restore draft data
            restoreDraft();
            updateFormProgress();

            // Sesi keep-alive: ping server setiap 4 menit untuk mencegah token CSRF/sesi kedaluwarsa (error 419)
            setInterval(() => {
                fetch('/programs/catalog')
                    .then(response => {
                        if (response.ok) {
                            addLog('Sistem', 'Koneksi & token sesi diperbarui otomatis (keep-alive).');
                        } else {
                            addLog('Error', 'Gagal memperbarui token sesi keamanan. Cek koneksi Anda.');
                        }
                    })
                    .catch(error => {
                        addLog('Error', 'Terputus dari server (koneksi keep-alive gagal).');
                    });
            }, 4 * 60 * 1000); // 4 menit

            // Bind change and input listeners to form fields
            const form = document.getElementById('apply-program-form');
            if (form && !isStageLocked) {
                form.addEventListener('input', () => {
                    debouncedSave();
                    updateFormProgress();
                });
                form.addEventListener('change', () => {
                    debouncedSave();
                    updateFormProgress();
                });
            }

                // Manual Save Draft to Database handler
                const manualSaveBtn = document.getElementById('manual-save-draft-btn');
                const loadingIcon = document.getElementById('draft-loading-icon');
                
                if (manualSaveBtn && !isStageLocked) {
                    manualSaveBtn.addEventListener('click', () => {
                        manualSaveBtn.disabled = true;
                        loadingIcon.classList.remove('hidden');
                        addLog('Sistem', 'Menyimpan draf formulir ke database server...');

                        const formData = new FormData(form);
                        
                        fetch('{{ route('program.apply.draft', $program->id) }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            manualSaveBtn.disabled = false;
                            loadingIcon.classList.add('hidden');

                            if (data.success) {
                                addLog('Sukses', 'Draf berhasil disimpan di database server!');
                                
                                // Synced to local storage too
                                saveDraft();
                                
                                const lastSavedTime = data.draft_saved_at || new Date().toLocaleTimeString('id-ID', { hour12: false });
                                const saveTimeText = document.getElementById('save-time-text');
                                if (saveTimeText) {
                                    saveTimeText.innerText = `Terakhir disimpan (Server): ${lastSavedTime}`;
                                }
                                alert('Draf jawaban Anda berhasil disimpan ke database server!');
                            } else {
                                addLog('Error', 'Gagal menyimpan draf: ' + (data.message || 'Error tidak diketahui.'));
                                alert('Gagal menyimpan draf: ' + (data.message || 'Terjadi kesalahan.'));
                            }
                        })
                        .catch(error => {
                            manualSaveBtn.disabled = false;
                            loadingIcon.classList.add('hidden');
                            console.error('Error saving draft:', error);
                            addLog('Error', 'Gagal terhubung ke server untuk menyimpan draf.');
                            alert('Koneksi terganggu. Gagal terhubung ke server.');
                        });
                    });
                }

                // Submit listener
                form.addEventListener('submit', (e) => {
                    // Cek koneksi internet sebelum submit
                    if (!navigator.onLine) {
                        e.preventDefault();
                        alert('Pengiriman Gagal: Perangkat Anda sedang offline. Silakan periksa koneksi internet Anda!');
                        addLog('Error', 'Pengiriman formulir dibatalkan karena tidak ada jaringan internet.');
                        return;
                    }

                    // Tampilkan konfirmasi pengiriman
                    if (!confirm('Apakah Anda yakin ingin mengirimkan berkas registrasi ini sekarang? Setelah dikirim, jawaban Anda tidak dapat diubah kembali.')) {
                        e.preventDefault();
                        return;
                    }

                    // Check file sizes one last time
                    const fileInputs = form.querySelectorAll('input[type="file"]');
                    let sizeExceeded = false;
                    let totalSize = 0;
                    
                    fileInputs.forEach(input => {
                        const file = input.files[0];
                        if (file) {
                            totalSize += file.size;
                            if (file.size > 5 * 1024 * 1024) {
                                sizeExceeded = true;
                                addLog('Error', `Pengiriman dibatalkan: Berkas "${file.name}" melebihi batas 5 MB!`);
                            }
                        }
                    });

                    if (sizeExceeded) {
                        e.preventDefault();
                        alert('Gagal mengirim formulir. Beberapa berkas Anda melebihi batas 5 MB.');
                        return;
                    }

                    const totalSizeMb = (totalSize / (1024 * 1024)).toFixed(2);
                    addLog('Sistem', `Total ukuran berkas yang diunggah: ${totalSizeMb} MB.`);
                    if (totalSize > 15 * 1024 * 1024) {
                        addLog('Berkas', '⚠️ Ukuran total berkas cukup besar (> 15 MB). Pastikan koneksi internet stabil.');
                    }

                    // Disable button to prevent double-submit and show loading status
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim Data &amp; Berkas...
                        `;
                    }
                    addLog('Sistem', 'Mengunggah data formulir dan berkas ke server... Harap tidak menutup halaman ini.');
                });
            }

            // Clear draft handler
            const clearBtn = document.getElementById('clear-draft-btn');
            if (clearBtn && form) {
                clearBtn.addEventListener('click', () => {
                    if (confirm('Apakah Anda yakin ingin menghapus semua isian draf formulir ini?')) {
                        localStorage.removeItem(draftKey);
                        form.reset();
                        
                        // Clear image previews
                        const previews = document.querySelectorAll('[id^="img_preview_"]');
                        previews.forEach(p => p.innerHTML = '');
                        
                        // Clear validation classes
                        const fileInputs = form.querySelectorAll('input[type="file"]');
                        fileInputs.forEach(input => {
                            input.classList.remove('border-rose-300', 'bg-rose-50/10', 'border-emerald-350', 'bg-emerald-50/10');
                        });

                        addLog('Sistem', 'Draf lokal telah dihapus dan isian formulir di-reset.');
                        const saveTimeText = document.getElementById('save-time-text');
                        if (saveTimeText) {
                            saveTimeText.innerText = 'Terakhir disimpan: Belum ada draf';
                        }
                        updateFormProgress();
                    }
                });
            }
        });
    </script>

    <style>
        .instruction-content a {
            color: #059669 !important; /* emerald-600 */
            font-weight: 800 !important;
            text-decoration: underline !important;
            transition: all 0.2s;
            word-break: break-all;
        }
        .instruction-content a:hover {
            color: #065f46 !important; /* emerald-800 */
        }
        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 12px 0;
        }
        /* Custom scrollbar utility */
        .scrollbar-thin::-webkit-scrollbar {
            width: 5px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 9999px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 9999px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.6);
        }
    </style>
</div>
@endsection
