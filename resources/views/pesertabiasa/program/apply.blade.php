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

        <form action="{{ route('program.apply.store', $program->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if(isset($stageData) && $stageData->status === 'failed')
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl space-y-2 mb-5 shadow-3xs">
                    <div class="flex items-center space-x-2 text-rose-800 font-bold text-xs">
                        <span>⚠️</span>
                        <span>PETUNJUK PERBAIKAN DARI PANITIA:</span>
                    </div>
                    <p class="text-xs text-rose-700 leading-relaxed font-semibold">
                        Jawaban Anda sebelumnya ditolak atau butuh perbaikan. Silakan periksa kembali jawaban dan berkas Anda di bawah ini, perbaiki bagian yang salah, lalu klik kirim kembali.
                    </p>
                    @if(!empty($stageData->reviewer_notes))
                        <div class="p-3 bg-white border border-rose-100 rounded-xl text-xs text-slate-700 mt-2 shadow-3xs">
                            <span class="font-extrabold text-rose-800 block mb-0.5">Catatan Reviewer:</span>
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
                              class="w-full p-2.5 border @error('motivation') border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 transition shadow-3xs" 
                              rows="4" 
                              required>{{ old('motivation') }}</textarea>
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
                    $isFieldRequired = $field['required'] && empty($prevVal);
                @endphp

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        {{ $field['name'] }}
                        @if($field['required']) <span class="text-rose-500 font-black">*</span> @endif
                    </label>

                    @if(!empty($field['instruction']))
                        <p class="text-[10px] text-slate-400/50 italic leading-relaxed mt-0.5">{!! e($field['instruction']) !!}</p>
                    @endif

                    @if($field['type'] === 'text')
                        <input type="text"
                               name="{{ $inputName }}"
                               value="{{ old($inputName, $prevVal) }}"
                               placeholder="{{ !empty($field['placeholder']) ? $field['placeholder'] : 'Ketikkan jawaban Anda disini...' }}"
                               class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 placeholder:text-slate-300 transition shadow-3xs"
                               {{ $isFieldRequired ? 'required' : '' }}>

                    @elseif($field['type'] === 'textarea')
                        <textarea name="{{ $inputName }}"
                                  placeholder="{{ !empty($field['placeholder']) ? $field['placeholder'] : 'Ketikkan jawaban panjang Anda disini...' }}"
                                  class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 placeholder:text-slate-300 transition shadow-3xs"
                                  rows="4"
                                  {{ $isFieldRequired ? 'required' : '' }}>{{ old($inputName, $prevVal) }}</textarea>

                    @elseif($field['type'] === 'file')
                        <input type="file"
                               name="{{ $inputName }}"
                               class="w-full p-2 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition shadow-3xs"
                               {{ $isFieldRequired ? 'required' : '' }}
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        <span class="text-[10px] text-slate-400 block">Berkas sah: PDF, Word, Excel, ZIP, RAR (Maksimal: 10MB)</span>
                        @if(!empty($prevVal))
                            <div class="mt-1.5 text-[11px] text-emerald-700 font-bold bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100 flex items-center gap-1.5 shadow-3xs">
                                <span>📎 Berkas sudah diunggah:</span>
                                <a href="{{ asset('storage/' . $prevVal) }}" target="_blank" class="underline hover:text-emerald-950 transition">Lihat Berkas Lampiran</a>
                            </div>
                        @endif

                    @elseif($field['type'] === 'image')
                        <input type="file"
                               name="{{ $inputName }}"
                               id="img_input_{{ $index }}"
                               onchange="compressAndPreviewImage(this, 'img_preview_{{ $index }}')"
                               class="w-full p-2 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition shadow-3xs"
                               {{ $isFieldRequired ? 'required' : '' }}
                               accept="image/png, image/jpeg, image/jpg">
                        <span class="text-[10px] text-slate-400 block">Ekstensi gambar sah: PNG, JPG, JPEG (Otomatis dikompres &amp; resize)</span>
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
                                class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 text-slate-700 transition shadow-3xs"
                                {{ $isFieldRequired ? 'required' : '' }}>
                            <option value="">-- Pilih salah satu opsi --</option>
                            @foreach($field['options'] ?? [] as $opt)
                                <option value="{{ $opt }}" {{ old($inputName, $prevVal) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>

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
                               class="w-full p-2.5 border @error($inputName) border-rose-300 bg-rose-50/10 @else border-slate-200 @enderror rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition shadow-3xs"
                               {{ $isFieldRequired ? 'required' : '' }}>

                    @elseif($field['type'] === 'options')
                        <div class="space-y-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-100 shadow-3xs">
                            @foreach($field['options'] ?? [] as $opt)
                                <label class="flex items-center text-xs font-semibold text-slate-700 cursor-pointer select-none">
                                    <input type="radio"
                                           name="{{ $inputName }}"
                                           value="{{ $opt }}"
                                           {{ old($inputName, $prevVal) === $opt ? 'checked' : '' }}
                                           class="text-emerald-600 focus:ring-emerald-500 mr-2 w-4 h-4 border-slate-300 shadow-3xs"
                                           {{ $isFieldRequired ? 'required' : '' }}>
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    @elseif($field['type'] === 'checkbox')
                        @php
                            $oldVals = old($inputName, $prevVal ?? []);
                            if (is_string($oldVals)) {
                                $oldVals = array_map('trim', explode(', ', $oldVals));
                            }
                        @endphp
                        <div class="space-y-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-100 shadow-3xs">
                            @foreach($field['options'] ?? [] as $opt)
                                <label class="flex items-center text-xs font-semibold text-slate-700 cursor-pointer select-none">
                                    <input type="checkbox"
                                           name="{{ $inputName }}[]"
                                           value="{{ $opt }}"
                                           {{ in_array($opt, (array)$oldVals) ? 'checked' : '' }}
                                           class="rounded text-emerald-600 focus:ring-emerald-500 mr-2 w-4 h-4 border-slate-300 shadow-3xs">
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
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

            <div class="pt-5 border-t flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-bold rounded-xl shadow-md shadow-emerald-50 transition-all text-xs uppercase tracking-wider">
                    Kirim Berkas Registrasi
                </button>
            </div>
        </form>
    </div>

    <!-- Image preview & compression script -->
    <script>
        function compressAndPreviewImage(input, previewId) {
            const file = input.files[0];
            const previewContainer = document.getElementById(previewId);
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (!file) return;

            // Verify if it's an image
            if (!file.type.startsWith('image/')) {
                previewContainer.innerHTML = '<span class="text-xs text-rose-600 font-bold">Berkas wajib berupa gambar.</span>';
                return;
            }

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
            infoText.innerText = `Ukuran Asli: ${(file.size / 1024).toFixed(1)} KB`;
            previewContainer.appendChild(infoText);

            // Compress using HTML Canvas
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    // Max dimensions for compression (e.g. 1200px max width/height)
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

                        // Inject back to input files using DataTransfer
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;

                        // Update size info
                        infoText.innerText = `Ukuran Asli: ${(file.size / 1024).toFixed(1)} KB | Ukuran Kompresi: ${(compressedFile.size / 1024).toFixed(1)} KB`;
                    }, 'image/jpeg', 0.75);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    </script>
</div>
@endsection
