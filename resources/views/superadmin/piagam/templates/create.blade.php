@extends('superadmin.layouts.app')
@section('title', 'Upload Template Piagam')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="createTemplateApp()">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.piagam.templates.index') }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Upload Template Baru</h1>
                <p class="text-sm text-slate-500 mt-1">Unggah PDF dasar yang akan dijadikan template piagam dan atur syarat unduh.</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <div class="font-bold mb-1">Terjadi kesalahan:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.piagam.templates.store') }}" method="POST" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8 space-y-6">
        @csrf
        
        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nama Template</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Contoh: Sertifikat Green Leaders 2026">
        </div>

        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Penjelasan singkat kegunaan template...">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">File PDF Template</label>
            <input type="file" name="file_path" accept=".pdf" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
            <p class="text-xs text-slate-400 mt-2">Hanya menerima file berformat PDF. Maksimal 10MB.</p>
        </div>

        <!-- PENGATURAN SYARAT UNDUH PIAGAM -->
        <div class="pt-6 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-800">Syarat Wajib Sebelum Unduh Sertifikat</label>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Opsional</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Peserta wajib mengklik link ini (misal: Follow IG, Subscribe YT, Join Grup WA) sebelum tombol unduh piagam terbuka.</p>
                </div>
            </div>

            <!-- Quick Template Buttons -->
            <div class="flex flex-wrap gap-2 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="text-xs font-bold text-slate-500 self-center mr-1">Tambah Cepat:</span>
                <button type="button" @click="addInstagram()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-pink-50 text-slate-700 hover:text-pink-600 border border-slate-200 hover:border-pink-200 text-xs font-bold rounded-lg transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    + Follow Instagram
                </button>
                <button type="button" @click="addYoutube()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-red-50 text-slate-700 hover:text-red-600 border border-slate-200 hover:border-red-200 text-xs font-bold rounded-lg transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    + Subscribe YouTube
                </button>
                <button type="button" @click="addWhatsApp()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-600 border border-slate-200 hover:border-emerald-200 text-xs font-bold rounded-lg transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    + Join WhatsApp
                </button>
                <button type="button" @click="addCustom()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + Syarat Lainnya
                </button>
            </div>

            <!-- List of Requirements -->
            <div class="space-y-3" x-show="requirements.length > 0">
                <template x-for="(req, index) in requirements" :key="index">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 relative group hover:border-emerald-300 transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black flex items-center justify-center" x-text="index + 1"></span>
                                <span x-text="'Syarat #' + (index + 1)"></span>
                            </span>
                            <button type="button" @click="removeRequirement(index)" class="text-slate-400 hover:text-rose-600 p-1 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus syarat ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-6">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Judul Syarat (Ditampilkan ke Peserta)</label>
                                <input type="text" :name="'requirements[' + index + '][title]'" x-model="req.title" required class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:outline-none" placeholder="Cth: Wajib Follow Instagram @instituthijauindonesia">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Link Tujuan (URL)</label>
                                <input type="url" :name="'requirements[' + index + '][url]'" x-model="req.url" required class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:outline-none" placeholder="https://instagram.com/instituthijauindonesia">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Teks Tombol</label>
                                <input type="text" :name="'requirements[' + index + '][button_text]'" x-model="req.button_text" class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:outline-none" placeholder="Follow IG">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State for Requirements -->
            <div x-show="requirements.length === 0" class="p-6 border border-dashed border-slate-200 rounded-xl text-center bg-slate-50/50">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <p class="text-xs text-slate-500 font-medium">Belum ada syarat unduh ditambahkan.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Jika kosong, peserta dapat langsung mengunduh piagam tanpa harus klik tautan tertentu.</p>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan & Lanjut ke Editor
            </button>
        </div>
    </form>
</div>

<script>
function createTemplateApp() {
    return {
        requirements: [],
        addInstagram() {
            this.requirements.push({
                title: 'Wajib Follow Instagram Resmi Institut Hijau Indonesia',
                url: 'https://instagram.com/instituthijauindonesia',
                button_text: 'Follow Instagram'
            });
        },
        addYoutube() {
            this.requirements.push({
                title: 'Wajib Subscribe Channel YouTube Institut Hijau Indonesia',
                url: 'https://www.youtube.com/@instituthijauindonesia',
                button_text: 'Subscribe YouTube'
            });
        },
        addWhatsApp() {
            this.requirements.push({
                title: 'Wajib Gabung Grup / Saluran Resmi WhatsApp',
                url: 'https://chat.whatsapp.com/',
                button_text: 'Gabung WhatsApp'
            });
        },
        addCustom() {
            this.requirements.push({
                title: '',
                url: '',
                button_text: 'Buka Tautan'
            });
        },
        removeRequirement(index) {
            this.requirements.splice(index, 1);
        }
    }
}
</script>
@endsection
