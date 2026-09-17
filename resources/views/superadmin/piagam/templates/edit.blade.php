@extends('superadmin.layouts.app')
@section('title', 'Layout Editor - ' . $template->name)

@section('content')
<div class="h-full flex flex-col -m-6 lg:-m-8 bg-slate-100" x-data="editorApp()">
    <!-- Topbar -->
    <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('superadmin.piagam.templates.index') }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-lg font-black text-slate-800">Visual Layout Editor</h1>
                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">{{ $template->name }}</p>
            </div>
        </div>
        
        <!-- Page Switcher -->
        <div class="flex items-center bg-slate-100 rounded-lg p-1" x-show="totalPages > 1" x-cloak>
            <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1.5 rounded-md disabled:opacity-50 hover:bg-white hover:shadow-sm transition-all text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <div class="px-3 text-xs font-bold text-slate-700">
                Halaman  / 
            </div>
            <button @click="nextPage()" :disabled="currentPage >= totalPages" class="p-1.5 rounded-md disabled:opacity-50 hover:bg-white hover:shadow-sm transition-all text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <div class="flex items-center gap-3">
            
            <button @click="saveLayout()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Simpan Layout
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Sidebar Elements -->
        <div class="w-64 bg-white border-r border-slate-200 shrink-0 overflow-y-auto flex flex-col">
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400 mb-3">Teks Statis</h3>
                <button @click="addText('Teks Bebas', 'static')" class="w-full px-4 py-2.5 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 text-sm font-medium border border-slate-200 hover:border-emerald-200 rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Tambah Teks
                </button>
            </div>

            <div class="p-4 border-b border-slate-100 flex-1">
                <h3 class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400 mb-3">Variabel Dinamis</h3>
                <div class="space-y-2">
                    <template x-for="v in variables" :key="v.code">
                        <button @click="addText(v.code, 'variable')" class="w-full px-3 py-2 text-left bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-xs font-semibold border border-slate-200 hover:border-emerald-200 rounded-lg transition-colors flex items-center justify-between group">
                            
                            <svg class="w-3 h-3 text-slate-300 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </template>
                </div>
            </div>

            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Variabel Dinamis Kustom</h3>
                <div class="space-y-2">
                    <input type="text" x-model="customVarName" placeholder="Cth: Nilai Sikap" @keydown.enter="addCustomVariable" class="w-full text-sm rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    <button @click="addCustomVariable" :disabled="!customVarName" class="w-full px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-100 disabled:opacity-50 transition-colors">
                        Buat & Tambahkan
                    </button>
                    <p class="text-[10px] text-slate-400 mt-1 leading-tight">Variabel ini otomatis akan memunculkan form pengisian nilai bagi Admin Program nanti.</p>
                </div>
            </div>

            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Variabel Dinamis Kustom</h3>
                <div class="space-y-2">
                    <input type="text" x-model="customVarName" placeholder="Cth: Nilai Sikap" @keydown.enter="addCustomVariable" class="w-full text-sm rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    <button @click="addCustomVariable" :disabled="!customVarName" class="w-full px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-100 disabled:opacity-50 transition-colors">
                        Buat & Tambahkan
                    </button>
                    <p class="text-[10px] text-slate-400 mt-1 leading-tight">Variabel ini otomatis akan memunculkan form pengisian nilai bagi Admin Program nanti.</p>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                <button @click="$refs.ornamentInput.click()" class="w-full px-4 py-3 bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-sm font-bold border border-slate-200 hover:border-indigo-200 rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Unggah Ornamen
                </button>
                <input type="file" x-ref="ornamentInput" @change="uploadOrnament" accept="image/png, image/jpeg, image/jpg" class="hidden">

                <button @click="addQrCode()" class="w-full px-4 py-3 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 text-sm font-bold border border-slate-200 hover:border-emerald-200 rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    QR Code Verifikasi
                </button>
            </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-auto bg-slate-100 p-8" id="canvas-container">
            <!-- Wrapper for proper centering and shadow -->
            <div id="canvas-wrapper-id" class="relative bg-white shadow-2xl ring-1 ring-slate-900/5 mx-auto" style="min-height: 500px;" x-ref="canvasWrapper">
                <!-- PDF.js background canvas -->
                <canvas id="pdf-canvas" style="display:none;"></canvas>
                <!-- Fabric.js interactive canvas -->
                <canvas id="editor-canvas" class="relative z-10"></canvas>
            </div>
        </div>

        <!-- Right Toolbar (Properties) -->
        <div class="w-72 bg-white border-l border-slate-200 shrink-0 overflow-y-auto" x-show="activeObject" x-transition>
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800">Properti Elemen</h3>
            </div>
            
            <div class="p-4 space-y-5">
                <template x-if="activeObject && activeObject.type === 'i-text'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">Teks</label>
                            <textarea x-model="objText" @input="updateObject('text', objText)" rows="2" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">Font Family</label>
                            <select x-model="objFontFamily" @change="updateObject('fontFamily', objFontFamily)" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="Arial">Arial</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier">Courier</option>
                                <option value="Helvetica">Helvetica</option>
                            </select>
                        </div>

                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">Ukuran</label>
                                <input type="number" x-model="objFontSize" @input="updateObject('fontSize', parseInt(objFontSize))" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">Warna</label>
                                <input type="color" x-model="objColor" @input="updateObject('fill', objColor)" class="w-full h-[38px] p-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">Perataan Teks (Align)</label>
                            <div class="flex rounded-lg shadow-sm">
                                <button @click="objTextAlign = 'left'; updateObject('textAlign', 'left')" :class="{'bg-emerald-50 text-emerald-700 border-emerald-200': objTextAlign === 'left', 'bg-white text-slate-500 border-slate-200': objTextAlign !== 'left'}" class="flex-1 px-4 py-2 border rounded-l-lg hover:bg-slate-50 focus:z-10 focus:ring-1 focus:ring-emerald-500 text-sm font-medium">Kiri</button>
                                <button @click="objTextAlign = 'center'; updateObject('textAlign', 'center')" :class="{'bg-emerald-50 text-emerald-700 border-emerald-200': objTextAlign === 'center', 'bg-white text-slate-500 border-slate-200': objTextAlign !== 'center'}" class="flex-1 px-4 py-2 border-t border-b hover:bg-slate-50 focus:z-10 focus:ring-1 focus:ring-emerald-500 text-sm font-medium">Tengah</button>
                                <button @click="objTextAlign = 'right'; updateObject('textAlign', 'right')" :class="{'bg-emerald-50 text-emerald-700 border-emerald-200': objTextAlign === 'right', 'bg-white text-slate-500 border-slate-200': objTextAlign !== 'right'}" class="flex-1 px-4 py-2 border rounded-r-lg hover:bg-slate-50 focus:z-10 focus:ring-1 focus:ring-emerald-500 text-sm font-medium">Kanan</button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="activeObject && activeObject.type === 'image'">
                    <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-800 text-sm">
                        Ini adalah gambar ornamen / QR Code. Anda bisa menarik sudut kotak pembatasnya untuk mengubah ukurannya.
                    </div>
                </template>

                <div class="pt-6 mt-6 border-t border-slate-100">
                    <button @click="deleteObject()" class="w-full px-4 py-2 bg-white text-rose-600 hover:bg-rose-50 hover:border-rose-200 text-sm font-bold border border-slate-200 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Elemen
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Empty State Property -->
        <div class="w-72 bg-white border-l border-slate-200 shrink-0 flex items-center justify-center p-6" x-show="!activeObject">
            <div class="text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                <p class="text-sm font-medium">Klik elemen di canvas untuk mengatur propertinya.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    // Setup PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    document.addEventListener('alpine:init', () => {
        Alpine.data('editorApp', () => ({
            templateUrl: "{{ Storage::url($template->file_path) }}",
            templateId: "{{ $template->id }}",
            pdfDoc: null,
            currentPage: 1,
            totalPages: 1,
            pdfWidthMM: 0,
            pdfHeightMM: 0,
            scaleFactor: 1,
            canvas: null,
            activeObject: null,
            statusText: 'Memuat PDF...',
            pageLayouts: {}, // Store elements per page

            
            // Properties
            objText: '',
            customVarName: '',
            savedLayouts: {!! $template->layouts ? $template->layouts->toJson() : '[]' !!},
            objFontFamily: 'Arial',
            objFontSize: 12,
            objColor: '#000000',
            objTextAlign: 'left',

            variables: [
                { label: 'Nama Peserta', code: '[NAMA_PESERTA]' },
                { label: 'Nama Program', code: '[NAMA_PROGRAM]' },
                { label: 'Nomor Kredensial Utama Global', code: '[NOMOR_KREDENSIAL_UTAMA_GLOBAL]' },
                { label: 'Predikat', code: '[PREDIKAT]' },
                { label: 'Total Skor', code: '[TOTAL_SKOR]' },
                { label: 'Tanggal Terbit', code: '[TANGGAL_TERBIT]' }
            ],

            init() {
                // Pre-fill pageLayouts from database
                this.savedLayouts.forEach(layout => {
                    this.pageLayouts[layout.page_number] = layout.elements.map(el => ({
                        page_number: layout.page_number,
                        type: el.type,
                        content: el.content,
                        x_pos: parseFloat(el.x_pos),
                        y_pos: parseFloat(el.y_pos),
                        font_size: parseFloat(el.font_size),
                        font_family: el.font_family,
                        color: el.color,
                        text_align: el.text_align
                    }));
                });

                this.loadPdf();
            },

            async loadPdf() {
                try {
                    const loadingTask = pdfjsLib.getDocument(this.templateUrl);
                    this.pdfDoc = await loadingTask.promise;
                    this.totalPages = Alpine.raw(this.pdfDoc).numPages;
                    
                    await this.renderPage(this.currentPage);
                    this.statusText = 'Siap';
                } catch (error) {
                    console.error(error);
                    this.statusText = 'Gagal: ' + error.message;
                }
            },

            async renderPage(pageNum) {
                this.statusText = 'Memuat halaman ' + pageNum + '...';
                if(this.canvas) {
                    Alpine.raw(this.canvas).clear();
                }

                const doc = Alpine.raw(this.pdfDoc);
                const page = await doc.getPage(pageNum);
                
                // Render dalam ukuran yang proporsional
                const originalViewport = page.getViewport({ scale: 1.0 });
                // Gunakan scale tetap yang cukup bagus untuk editing (misalnya 1.25)
                // CSS overflow-auto akan menangani scrollbar jika terlalu besar
                const viewport = page.getViewport({ scale: 1.25 });
                
                this.pdfWidthMM = (originalViewport.width / 72) * 25.4;
                this.pdfHeightMM = (originalViewport.height / 72) * 25.4;

                const pdfCanvas = document.getElementById('pdf-canvas');
                const ctx = pdfCanvas.getContext('2d');
                pdfCanvas.width = viewport.width;
                pdfCanvas.height = viewport.height;

                // Set ukuran div wrapper menggunakan ID agar lebih tangguh (jika refs gagal)
                const wrapper = document.getElementById('canvas-wrapper-id');
                if(wrapper) {
                    wrapper.style.width = viewport.width + 'px';
                    wrapper.style.height = viewport.height + 'px';
                }

                await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                if(!this.canvas) {
                    this.initFabric(viewport.width, viewport.height);
                } else {
                    const c = Alpine.raw(this.canvas);
                    c.setWidth(viewport.width);
                    c.setHeight(viewport.height);
                    this.scaleFactor = this.pdfWidthMM / viewport.width;
                    const wrapper = document.getElementById('canvas-wrapper-id');
                    if(wrapper) {
                        wrapper.style.width = viewport.width + 'px';
                        wrapper.style.height = viewport.height + 'px';
                    }
                }
                
                // Set PDF sebagai background Fabric
                const bgImage = new fabric.Image(pdfCanvas);
                const c = Alpine.raw(this.canvas);
                c.setBackgroundImage(bgImage, c.renderAll.bind(c));
                
                this.statusText = 'Siap';
                this.restorePageFromMemory(pageNum);
            },

            prevPage() {
                if(this.currentPage > 1) {
                    this.savePageToMemory();
                    this.currentPage--;
                    this.renderPage(this.currentPage);
                }
            },
            
            nextPage() {
                if(this.currentPage < this.totalPages) {
                    this.savePageToMemory();
                    this.currentPage++;
                    this.renderPage(this.currentPage);
                }
            },

            initFabric(width, height) {
                const editorCanvasEl = document.getElementById('editor-canvas');
                editorCanvasEl.width = width;
                editorCanvasEl.height = height;

                this.canvas = new fabric.Canvas('editor-canvas', {
                    width: width,
                    height: height,
                    selection: false
                });

                // Scale factor dari Pixel layar ke MM asli PDF
                this.scaleFactor = this.pdfWidthMM / width;

                // Event listener untuk properti panel
                this.canvas.on('selection:created', this.onObjectSelected.bind(this));
                this.canvas.on('selection:updated', this.onObjectSelected.bind(this));
                this.canvas.on('selection:cleared', () => {
                    this.activeObject = null;
                });
                
                // Fetch saved elements
                this.loadSavedElements();
            },

            loadSavedElements() {
                // Di sistem riil, panggil via AJAX ke layout_elements. 
                // Untuk tahap ini, kita abaikan karena view ini digunakan dari awal kosong.
                // Jika butuh mengedit yang sudah ada, tinggal me-loop elemen di blade dan menambahkannya ke Fabric.
            },

            savePageToMemory() {
                if(!this.canvas) return;
                this.pageLayouts[this.currentPage] = this.extractElements();
            },

            restorePageFromMemory(pageNum) {
                if(!this.pageLayouts[pageNum]) return;
                
                const elements = this.pageLayouts[pageNum];
                const c = Alpine.raw(this.canvas);
                
                elements.forEach(el => {
                    let leftPx = el.x_pos / this.scaleFactor;
                    let topPx = el.y_pos / this.scaleFactor;
                    
                    if (el.type === 'image' || el.type === 'qr_code') {
                        let url = el.type === 'qr_code' ? 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Placeholder' : el.content;
                        fabric.Image.fromURL(url, (img, isError) => {
                            if (!isError && img) {
                                let widthPx = el.font_size / this.scaleFactor;
                                img.scaleToWidth(widthPx);
                                img.set({
                                    left: leftPx,
                                    top: topPx
                                });
                                img.set('piagamType', el.type);
                                if(el.type === 'image') img.set('imageUrl', el.content);
                                c.add(img);
                            }
                        });
                    } else {
                        // Text element
                        let fontPx = el.font_size; 
                        let textObj = new fabric.IText(el.content || '', {
                            left: leftPx,
                            top: topPx,
                            fontFamily: el.font_family || 'Arial',
                            fontSize: fontPx,
                            fill: el.color || '#000000',
                            originX: 'left',
                            originY: 'top',
                            textAlign: el.text_align || 'left'
                        });
                        textObj.set('piagamType', el.type);
                        c.add(textObj);
                    }
                });
                
                c.renderAll();
            },

            extractElements() {
                const c = Alpine.raw(this.canvas);
                const objects = c.getObjects();
                return objects.map(obj => {
                    let x_mm = obj.left * this.scaleFactor;
                    let y_mm = obj.top * this.scaleFactor;
                    
                    let font_pt = obj.fontSize || 12; 
                    if(obj.scaleX && obj.type === 'i-text') { 
                        font_pt = font_pt * obj.scaleX; 
                    }
                    // For images, store the scaled width (in mm) in font_size column
                    if (obj.type === 'image') {
                        let width_mm = (obj.width * obj.scaleX) * this.scaleFactor;
                        font_pt = width_mm;
                    }
                    
                    let content = obj.type === 'i-text' ? obj.text : null;
                    if (obj.type === 'image' && obj.get('piagamType') === 'image') {
                        content = obj.get('imageUrl'); // Save the URL of the ornament
                    }
                    
                    return {
                        page_number: this.currentPage,
                        type: obj.get('piagamType') || 'static',
                        content: content,
                        x_pos: x_mm,
                        y_pos: y_mm,
                        font_size: font_pt,
                        font_family: obj.fontFamily || 'Arial',
                        color: obj.fill || '#000000',
                        text_align: obj.textAlign || 'left'
                    };
                });
            },

            addText(text, typeName) {
                const textObj = new fabric.IText(text, {
                    left: 50,
                    top: 50,
                    fontFamily: 'Arial',
                    fontSize: 24, // pixel
                    fill: '#000000',
                    originX: 'left',
                    originY: 'top',
                    textAlign: 'left'
                });
                
                // Simpan metadata
                textObj.set('piagamType', typeName);
                
                const c = Alpine.raw(this.canvas);
                c.add(textObj);
                c.setActiveObject(textObj);
                c.renderAll();
            },

            addQrCode() {
                // Kita gunakan placeholder gambar atau rect. 
                fabric.Image.fromURL('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Placeholder', (img) => {
                    img.set({
                        left: 50,
                        top: 50,
                        scaleX: 1,
                        scaleY: 1
                    });
                    img.set('piagamType', 'qr_code');
                    const c = Alpine.raw(this.canvas);
                    c.add(img);
                    c.setActiveObject(img);
                    c.renderAll();
                });
            },
            
            async uploadOrnament(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                this.statusText = 'Mengunggah gambar...';
                const formData = new FormData();
                formData.append('image', file);
                
                try {
                    const response = await axios.post("{{ route('superadmin.piagam.templates.ornament.upload') }}", formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    
                    if (response.data.status === 'success') {
                        const url = response.data.url;
                        this.statusText = 'Menambahkan ke kanvas...';
                        
                        fabric.Image.fromURL(url, (img, isError) => {
                            if (isError || !img) {
                                this.statusText = 'Gagal menampilkan ornamen di kanvas';
                                alert('Gagal memuat gambar: ' + url);
                                return;
                            }
                            // Scale down if too large
                            if (img.width > 300) {
                                img.scaleToWidth(300);
                            }
                            img.set({
                                left: 50,
                                top: 50
                            });
                            img.set('piagamType', 'image');
                            img.set('imageUrl', url);
                            
                            const c = Alpine.raw(this.canvas);
                            c.add(img);
                            c.setActiveObject(img);
                            c.renderAll();
                            this.statusText = 'Siap';
                        });
                    }
                } catch(error) {
                    console.error(error);
                    let errMsg = 'Gagal mengunggah gambar: ' + error.message;
                    if (error.response && error.response.data && error.response.data.message) {
                        errMsg = error.response.data.message;
                    }
                    this.statusText = errMsg;
                    alert(errMsg);
                }
                
                // Reset input
                this.$refs.ornamentInput.value = null;
            },

            addCustomVariable() {
                if (!this.customVarName.trim()) return;
                
                // Format: "Nilai Kehadiran" -> "[NILAI_KEHADIRAN]"
                let formatted = this.customVarName.trim().toUpperCase().replace(/[^A-Z0-9]/g, '_');
                // Avoid double underscores
                formatted = formatted.replace(/_+/g, '_');
                formatted = `[${formatted}]`;
                
                this.addText(formatted, 'dynamic');
                this.customVarName = '';
            },


            addCustomVariable() {
                if (!this.customVarName.trim()) return;
                
                // Format: "Nilai Kehadiran" -> "[NILAI_KEHADIRAN]"
                let formatted = this.customVarName.trim().toUpperCase().replace(/[^A-Z0-9]/g, "_");
                // Avoid double underscores
                formatted = formatted.replace(/_+/g, "_");
                formatted = "[" + formatted + "]";
                
                this.addText(formatted, "dynamic");
                this.customVarName = "";
            },

            onObjectSelected(e) {

                const obj = e.selected[0];
                this.activeObject = obj;
                
                if (obj.type === 'i-text') {
                    this.objText = obj.text;
                    this.objFontFamily = obj.fontFamily;
                    this.objFontSize = obj.fontSize;
                    this.objColor = obj.fill;
                    this.objTextAlign = obj.textAlign;
                }
            },

            updateObject(key, value) {
                if (!this.activeObject) return;
                const obj = Alpine.raw(this.activeObject);
                const c = Alpine.raw(this.canvas);
                obj.set(key, value);
                c.renderAll();
            },

            deleteObject() {
                if (!this.activeObject) return;
                const obj = Alpine.raw(this.activeObject);
                const c = Alpine.raw(this.canvas);
                c.remove(obj);
                this.activeObject = null;
            },

            async saveLayout() {
                this.statusText = 'Menyimpan...';
                
                // Save current page elements to memory
                this.savePageToMemory();
                
                // Aggregate all elements from all pages
                let allElements = [];
                for (const pageNum in this.pageLayouts) {
                    allElements = allElements.concat(this.pageLayouts[pageNum]);
                }
                
                // If they never switched pages, ensure at least current page is caught
                if(allElements.length === 0) {
                    allElements = this.extractElements();
                }

                try {
                    const response = await axios.post("{{ route('superadmin.piagam.templates.layout.save', $template->id) }}", {
                        pdf_width: this.pdfWidthMM,
                        pdf_height: this.pdfHeightMM,
                        elements: allElements,
                        total_pages: this.totalPages
                    });
                    
                    this.statusText = 'Berhasil disimpan!';
                    setTimeout(() => { this.statusText = 'Siap'; }, 3000);
                } catch (error) {
                    console.error(error);
                    this.statusText = 'Gagal menyimpan!';
                }
            }
        }));
    });
</script>
<style>
    /* Prevent text selection while dragging on canvas */
    .canvas-container {
        margin: 0 auto;
    }
</style>
@endsection














