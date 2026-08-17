@extends('adminprogram.layouts.app')

@section('title', 'Template Sertifikat')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('adminprogram.alumni.index') }}" class="p-2 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 rounded-xl shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
                Certificate Templates
            </h1>
            <p class="text-sm font-medium text-slate-500">
                Unggah template PDF piagam kelulusan A4 Landscape dan posisikan teks dinamis.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Upload Form -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm h-fit">
            <h2 class="text-lg font-black text-slate-800 mb-4">Upload Template</h2>
            <form method="POST" action="{{ route('adminprogram.alumni.templates.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Program Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Program Alumni</label>
                    <select name="alumni_program_id" id="alumni_program_select" required class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">-- Pilih Program --</option>
                        @foreach($programs as $p)
                            @php
                                $settings = $p->template ? json_encode($p->template->settings) : 'null';
                                $templateUrl = $p->template ? asset('storage/' . $p->template->template_path) : 'null';
                                $isImg = $p->template ? in_array(strtolower(pathinfo($p->template->template_path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']) : false;
                            @endphp
                            <option value="{{ $p->id }}" data-settings="{{ $settings }}" data-template-url="{{ $templateUrl }}" data-is-image="{{ $isImg ? 'true' : 'false' }}">{{ $p->name }} ({{ $p->year }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Template Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Berkas Template PDF / Gambar (Max 10MB)</label>
                    <input type="file" name="template_file" accept=".pdf,image/png,image/jpeg,image/jpg" required class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl p-1.5 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <h3 class="text-xs font-black uppercase text-slate-400 mb-3 tracking-widest">Koordinat Cetak (Millimeter)</h3>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-3 text-[10px] text-slate-400 font-semibold mb-2">
                            A4 Landscape berukuran lebar 297mm x tinggi 210mm. Isikan posisi X (horizontal) dan Y (vertical). Kosongkan untuk menggunakan letak default tengah.
                        </div>

                        <!-- Name Coordinates -->
                        <div class="col-span-3 border-b border-slate-50 pb-2">
                            <span class="text-xs font-bold text-slate-700">Nama Peserta</span>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <input type="number" step="0.1" name="name_x" id="input_name_x" placeholder="X (C=0)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="name_y" id="input_name_y" placeholder="Y (Def: 80)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" name="name_size" id="input_name_size" placeholder="Font Size" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- Program Coordinates -->
                        <div class="col-span-3 border-b border-slate-50 pb-2">
                            <span class="text-xs font-bold text-slate-700">Nama Program</span>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <input type="number" step="0.1" name="program_x" id="input_program_x" placeholder="X (C=0)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="program_y" id="input_program_y" placeholder="Y (Def: 100)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" name="program_size" id="input_program_size" placeholder="Font Size" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- NIA Coordinates -->
                        <div class="col-span-3 border-b border-slate-50 pb-2">
                            <span class="text-xs font-bold text-slate-700">No. Induk Alumni</span>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <input type="number" step="0.1" name="alumni_number_x" id="input_alumni_number_x" placeholder="X (C=0)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="alumni_number_y" id="input_alumni_number_y" placeholder="Y (Def: 120)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" name="alumni_number_size" id="input_alumni_number_size" placeholder="Font Size" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- Date Coordinates -->
                        <div class="col-span-3 border-b border-slate-50 pb-2">
                            <span class="text-xs font-bold text-slate-700">Tanggal Kelulusan</span>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <input type="number" step="0.1" name="date_x" id="input_date_x" placeholder="X (C=0)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="date_y" id="input_date_y" placeholder="Y (Def: 135)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" name="date_size" id="input_date_size" placeholder="Font Size" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- QR Coordinates -->
                        <div class="col-span-3">
                            <span class="text-xs font-bold text-slate-700">QR Code Otentik</span>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <input type="number" step="0.1" name="qr_x" id="input_qr_x" placeholder="X (Def: 133)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="qr_y" id="input_qr_y" placeholder="Y (Def: 155)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                                <input type="number" step="0.1" name="qr_size" id="input_qr_size" placeholder="Size (Def: 32)" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition-colors">
                    Simpan Template
                </button>
            </form>
        </div>

        <!-- Visual Layout & Template List -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Visual Layout Editor Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">Visual Layout Editor</h2>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5">Seret (drag) teks/QR secara vertikal/horizontal untuk mengatur posisi cetak</p>
                    </div>
                    <div class="text-[10px] font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-md px-2 py-1">
                        Dimensi Kanvas: 297mm x 210mm (A4 Landscape)
                    </div>
                </div>

                <!-- Canvas Workspace -->
                <div class="relative w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden shadow-inner mx-auto select-none" 
                     id="preview-canvas" style="aspect-ratio: 297/210; max-height: 480px;">
                    
                    <!-- Simulated Background Template -->
                    <div class="absolute inset-0 flex flex-col justify-between p-6 border-8 border-double border-slate-300 pointer-events-none opacity-30">
                        <div class="flex justify-between">
                            <div class="w-12 h-12 border-t-2 border-l-2 border-slate-400"></div>
                            <div class="w-12 h-12 border-t-2 border-r-2 border-slate-400"></div>
                        </div>
                        <div class="text-center font-serif text-2xl tracking-widest text-slate-400 font-bold uppercase my-auto">Sertifikat Kelulusan</div>
                        <div class="flex justify-between">
                            <div class="w-12 h-12 border-b-2 border-l-2 border-slate-400"></div>
                            <div class="w-12 h-12 border-b-2 border-r-2 border-slate-400"></div>
                        </div>
                    </div>

                    <!-- Draggable elements -->
                    <!-- Name -->
                    <div id="drag-name" class="absolute left-0 right-0 cursor-row-resize text-center py-1 hover:bg-emerald-500/10 hover:border-emerald-500 border border-transparent font-bold text-slate-850 transition-shadow group select-none" style="top: 38%;">
                        <span class="bg-white/90 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-black text-emerald-700 absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">Nama Penerima (Seret Vertikal)</span>
                        <div class="drag-handle text-xl sm:text-2xl uppercase tracking-wide font-black" id="text-name">NAMA PESERTA LENGKAP</div>
                    </div>

                    <!-- Program -->
                    <div id="drag-program" class="absolute left-0 right-0 cursor-row-resize text-center py-1 hover:bg-emerald-500/10 hover:border-emerald-500 border border-transparent font-bold text-slate-700 transition-shadow group select-none" style="top: 48%;">
                        <span class="bg-white/90 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-black text-emerald-700 absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">Nama Program (Seret Vertikal)</span>
                        <div class="drag-handle text-sm sm:text-base font-bold" id="text-program">Program YOU-RINGS (2026)</div>
                    </div>

                    <!-- NIA -->
                    <div id="drag-nia" class="absolute left-0 right-0 cursor-row-resize text-center py-1 hover:bg-emerald-500/10 hover:border-emerald-500 border border-transparent text-slate-500 transition-shadow group select-none" style="top: 57%;">
                        <span class="bg-white/90 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-black text-emerald-700 absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">Nomor NIA (Seret Vertikal)</span>
                        <div class="drag-handle text-xs font-semibold" id="text-nia">No. Induk Alumni: IHI-2026-000001</div>
                    </div>

                    <!-- Date -->
                    <div id="drag-date" class="absolute left-0 right-0 cursor-row-resize text-center py-1 hover:bg-emerald-500/10 hover:border-emerald-500 border border-transparent text-slate-500 transition-shadow group select-none" style="top: 67%;">
                        <span class="bg-white/90 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-black text-emerald-700 absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity">Tanggal Lulus (Seret Vertikal)</span>
                        <div class="drag-handle text-xs font-semibold" id="text-date">Tanggal Lulus: 30 Juni 2026</div>
                    </div>

                    <!-- QR Code (Draggable X and Y) -->
                    <div id="drag-qr" class="absolute cursor-move w-14 h-14 bg-white border border-slate-350 rounded-lg p-1.5 flex items-center justify-center shadow-md hover:border-emerald-500 group select-none" style="top: 75%; left: 45%;">
                        <span class="bg-white/95 border border-slate-200 px-1.5 py-0.5 rounded text-[9px] font-black text-emerald-700 absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">QR Code (Seret Bebas)</span>
                        <svg class="w-full h-full text-slate-800" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-3 3h3v3h-3v-3zm3 3h3v-2h-3v2zm-3-5h3v2h-3v-2zm3-3h3v2h-3v-2zm-6 6h3v3h-3v-3zm6 3h-3v2h3v-2zm-3-5h3v2h-3v-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Templates List -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h2 class="text-lg font-black text-slate-800 mb-4">Daftar Template Program</h2>
                <div class="space-y-4">
                    @forelse($programs as $p)
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border border-slate-100 rounded-2xl gap-4 hover:border-slate-200 transition-colors">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ $p->name }}</div>
                                <div class="text-xs text-slate-400 font-semibold mt-0.5">Tahun Program: {{ $p->year }}</div>
                                @if($p->template)
                                    <div class="text-[10px] font-bold text-slate-500 bg-slate-100 border border-slate-200 rounded-md px-1.5 py-0.5 mt-2 w-fit">
                                        File: {{ basename($p->template->template_path) }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                @if($p->template)
                                    <div class="flex gap-2">
                                        <a href="{{ asset('storage/' . $p->template->template_path) }}" target="_blank" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 hover:text-slate-800 text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat PDF
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-rose-500 font-bold bg-rose-50 border border-rose-100 rounded-lg px-2.5 py-1">
                                        Belum ada template
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 italic text-sm">
                            Tidak ada program alumni terdaftar di database.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to support drag and drop visual mapping -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('preview-canvas');
    const select = document.getElementById('alumni_program_select');
    
    // UI elements
    const elements = {
        name: {
            drag: document.getElementById('drag-name'),
            text: document.getElementById('text-name'),
            inputs: {
                x: document.getElementById('input_name_x'),
                y: document.getElementById('input_name_y'),
                size: document.getElementById('input_name_size')
            },
            defY: 80,
            defSize: 24,
            axis: 'y'
        },
        program: {
            drag: document.getElementById('drag-program'),
            text: document.getElementById('text-program'),
            inputs: {
                x: document.getElementById('input_program_x'),
                y: document.getElementById('input_program_y'),
                size: document.getElementById('input_program_size')
            },
            defY: 100,
            defSize: 18,
            axis: 'y'
        },
        alumni_number: {
            drag: document.getElementById('drag-nia'),
            text: document.getElementById('text-nia'),
            inputs: {
                x: document.getElementById('input_alumni_number_x'),
                y: document.getElementById('input_alumni_number_y'),
                size: document.getElementById('input_alumni_number_size')
            },
            defY: 120,
            defSize: 12,
            axis: 'y'
        },
        date: {
            drag: document.getElementById('drag-date'),
            text: document.getElementById('text-date'),
            inputs: {
                x: document.getElementById('input_date_x'),
                y: document.getElementById('input_date_y'),
                size: document.getElementById('input_date_size')
            },
            defY: 135,
            defSize: 12,
            axis: 'y'
        },
        qr: {
            drag: document.getElementById('drag-qr'),
            inputs: {
                x: document.getElementById('input_qr_x'),
                y: document.getElementById('input_qr_y'),
                size: document.getElementById('input_qr_size')
            },
            defX: 133,
            defY: 155,
            defSize: 32,
            axis: 'both'
        }
    };

    // Initialize positions on load/change
    function updateVisualsFromInputs() {
        for (const [key, el] of Object.entries(elements)) {
            // Fetch Y
            let yVal = parseFloat(el.inputs.y.value);
            if (isNaN(yVal)) yVal = el.defY;
            const yPercent = (yVal / 210) * 100;
            el.drag.style.top = yPercent + '%';

            // Fetch X if applicable
            if (el.axis === 'both') {
                let xVal = parseFloat(el.inputs.x.value);
                if (isNaN(xVal)) xVal = el.defX;
                const xPercent = (xVal / 297) * 100;
                el.drag.style.left = xPercent + '%';
            }

            // Fetch Size/Font Size
            let sizeVal = parseInt(el.inputs.size.value);
            if (isNaN(sizeVal)) sizeVal = el.defSize;
            if (el.text) {
                el.text.style.fontSize = Math.max(8, sizeVal) + 'px';
            } else {
                // QR dimensions
                const qrWPercent = (sizeVal / 297) * 100;
                el.drag.style.width = qrWPercent + '%';
                el.drag.style.height = 'auto';
            }
        }
    }

    // Populate inputs when select program option changes
    if (select) {
        select.addEventListener('change', function () {
            const option = select.options[select.selectedIndex];
            const decorations = canvas.querySelector('.border-double');
            
            if (option && option.value) {
                const settingsAttr = option.getAttribute('data-settings');
                const templateUrl = option.getAttribute('data-template-url');
                const isImage = option.getAttribute('data-is-image') === 'true';

                // Display background template image in preview canvas
                if (isImage && templateUrl && templateUrl !== 'null') {
                    canvas.style.backgroundImage = 'url(' + templateUrl + ')';
                    canvas.style.backgroundSize = '100% 100%';
                    canvas.style.backgroundPosition = 'center';
                    canvas.style.backgroundRepeat = 'no-repeat';
                    if (decorations) decorations.style.display = 'none';
                } else {
                    canvas.style.backgroundImage = 'none';
                    if (decorations) decorations.style.display = 'flex';
                }

                let settings = null;
                try {
                    settings = JSON.parse(settingsAttr);
                } catch(e) {}

                if (settings) {
                    // Populate from existing settings
                    elements.name.inputs.x.value = settings.name?.x || '';
                    elements.name.inputs.y.value = settings.name?.y || '';
                    elements.name.inputs.size.value = settings.name?.size || '';

                    elements.program.inputs.x.value = settings.program?.x || '';
                    elements.program.inputs.y.value = settings.program?.y || '';
                    elements.program.inputs.size.value = settings.program?.size || '';

                    elements.alumni_number.inputs.x.value = settings.alumni_number?.x || '';
                    elements.alumni_number.inputs.y.value = settings.alumni_number?.y || '';
                    elements.alumni_number.inputs.size.value = settings.alumni_number?.size || '';

                    elements.date.inputs.x.value = settings.date?.x || '';
                    elements.date.inputs.y.value = settings.date?.y || '';
                    elements.date.inputs.size.value = settings.date?.size || '';

                    elements.qr.inputs.x.value = settings.qr?.x || '';
                    elements.qr.inputs.y.value = settings.qr?.y || '';
                    elements.qr.inputs.size.value = settings.qr?.size || '';
                } else {
                    // Reset to empty/defaults
                    for (const el of Object.values(elements)) {
                        el.inputs.x.value = '';
                        el.inputs.y.value = '';
                        el.inputs.size.value = '';
                    }
                }
            } else {
                canvas.style.backgroundImage = 'none';
                if (decorations) decorations.style.display = 'flex';
                // Reset
                for (const el of Object.values(elements)) {
                    el.inputs.x.value = '';
                    el.inputs.y.value = '';
                    el.inputs.size.value = '';
                }
            }
            updateVisualsFromInputs();
        });
    }

    // Bind file input changes to dynamic preview canvas background
    const fileInput = document.querySelector('input[name="template_file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = fileInput.files[0];
            if (file && file.type.startsWith('image/')) {
                const url = URL.createObjectURL(file);
                canvas.style.backgroundImage = 'url(' + url + ')';
                canvas.style.backgroundSize = '100% 100%';
                canvas.style.backgroundPosition = 'center';
                canvas.style.backgroundRepeat = 'no-repeat';
                const decorations = canvas.querySelector('.border-double');
                if (decorations) decorations.style.display = 'none';
            }
        });
    }

    // Bind input changes to visual updates
    for (const el of Object.values(elements)) {
        if (el.inputs.x) el.inputs.x.addEventListener('input', updateVisualsFromInputs);
        if (el.inputs.y) el.inputs.y.addEventListener('input', updateVisualsFromInputs);
        if (el.inputs.size) el.inputs.size.addEventListener('input', updateVisualsFromInputs);
    }

    // Setup drag and drop logic
    function makeDraggable(key, el) {
        let isDragging = false;
        let startY = 0;
        let startX = 0;
        let originalTop = 0;
        let originalLeft = 0;

        el.drag.addEventListener('mousedown', dragStart);
        el.drag.addEventListener('touchstart', dragStart, { passive: true });

        function dragStart(e) {
            isDragging = true;
            const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            
            startY = clientY;
            startX = clientX;
            originalTop = el.drag.offsetTop;
            originalLeft = el.drag.offsetLeft;

            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchmove', dragMove, { passive: false });
            document.addEventListener('touchend', dragEnd);
        }

        function dragMove(e) {
            if (!isDragging) return;
            e.preventDefault();

            const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
            const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;

            const deltaY = clientY - startY;
            const deltaX = clientX - startX;

            const rect = canvas.getBoundingClientRect();

            // Calculate vertical Y
            let newTop = originalTop + deltaY;
            newTop = Math.max(0, Math.min(newTop, rect.height));
            const yPercent = (newTop / rect.height) * 100;
            el.drag.style.top = yPercent + '%';

            // Convert to mm
            const yValMm = ((newTop / rect.height) * 210).toFixed(1);
            el.inputs.y.value = yValMm;

            // Calculate horizontal X if both axes
            if (el.axis === 'both') {
                let newLeft = originalLeft + deltaX;
                newLeft = Math.max(0, Math.min(newLeft, rect.width - el.drag.offsetWidth));
                const xPercent = (newLeft / rect.width) * 100;
                el.drag.style.left = xPercent + '%';

                const xValMm = ((newLeft / rect.width) * 297).toFixed(1);
                el.inputs.x.value = xValMm;
            }
        }

        function dragEnd() {
            isDragging = false;
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
            document.removeEventListener('touchmove', dragMove);
            document.removeEventListener('touchend', dragEnd);
        }
    }

    // Initialize drag-and-drop on all elements
    for (const [key, el] of Object.entries(elements)) {
        makeDraggable(key, el);
    }

    // Initial load
    updateVisualsFromInputs();
});
</script>
@endsection
