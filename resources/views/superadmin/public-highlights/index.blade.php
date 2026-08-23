@extends('superadmin.layouts.app')
@section('title', 'Manajemen Sorotan & Kegiatan Beranda')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="mb-4">
        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Public Homepage Portal manager</span>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Manajemen Sorotan & Kegiatan Publik</h1>
        <p class="text-sm text-slate-500 mt-1">Buat kartu kegiatan, sorotan media sosial, atau kutipan diskursus untuk ditayangkan pada halaman depan public.</p>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-sm">
            ✨ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Pembuatan Sorotan Kegiatan -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit space-y-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">Buat Sorotan / Kegiatan Baru</span>

            <form action="{{ route('superadmin.public-highlights.store') }}" method="POST" enctype="multipart/form-data" id="highlight-form" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Judul / Platform Sosial</label>
                    <input type="text" name="title" placeholder="Contoh: INSTAGRAM / @INSTITUTHIJAUINDONESIA" value="{{ old('title') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                    @error('title')
                        <p class="text-rose-500 text-[10px] mt-0.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Gaya Tema Kartu</label>
                    <select name="theme" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-750 font-bold">
                        <option value="light">☀️ Tema Putih / Bersih (Teks Gelap)</option>
                        <option value="dark">🌙 Tema Hijau Gelap (Teks Putih - Kontras)</option>
                    </select>
                    @error('theme')
                        <p class="text-rose-500 text-[10px] mt-0.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Foto Banner (Opsional)</label>
                    <input type="file" name="banner" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white">
                    <p class="text-[9px] text-slate-400 mt-1 flex items-center gap-1 bg-slate-50 p-2 rounded-lg border border-slate-100/50">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Panduan Gambar:</strong> Rekomendasi <strong>1200 x 675 piksel</strong> (aspek rasio 16:9) agar tayang presisi. JPEG/PNG maks 3MB.</span>
                    </p>
                    @error('banner')
                        <p class="text-rose-500 text-[10px] mt-0.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Teks Tombol Link</label>
                        <input type="text" name="link_text" placeholder="Contoh: Ikuti Diskusi" value="{{ old('link_text') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">URL Target Link</label>
                        <input type="text" name="link_url" placeholder="https://..." value="{{ old('link_url') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs">
                        @error('link_url')
                            <p class="text-rose-500 text-[10px] mt-0.5 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Isi Teks / Diskursus Sorotan</label>
                    <div id="highlight-quill-editor" class="h-36 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;"></div>
                    <input type="hidden" name="content" id="hidden-content-input" value="{{ old('content') }}">
                    @error('content')
                        <p class="text-rose-500 text-[10px] mt-0.5 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white font-bold text-xs rounded-xl transition uppercase tracking-wider shadow-md shadow-emerald-50">
                    💾 Simpan & Terbitkan Ke Public
                </button>
            </form>
        </div>

        <!-- Tabel Log Sorotan Kegiatan yang Eksis -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4 flex flex-col justify-between">
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2 mb-4">Daftar Sorotan Kegiatan Terbit</span>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-500">
                        <thead class="text-[10px] uppercase text-slate-450 bg-slate-50/75 border-b">
                            <tr>
                                <th class="px-4 py-3">Sorotan / Post</th>
                                <th class="px-4 py-3 text-center">Gaya Tema</th>
                                <th class="px-4 py-3 text-center">Link Aksi</th>
                                <th class="px-4 py-3 text-center">Keaktifan</th>
                                <th class="px-4 py-3 text-center">Analitik</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($highlights as $item)
                                <tr>
                                    <td class="px-4 py-3.5 space-y-1">
                                        <div class="font-extrabold text-slate-800 text-sm tracking-tight">{{ $item->title }}</div>
                                        <div class="text-[11px] text-slate-500 leading-relaxed font-medium line-clamp-2">{!! strip_tags($item->content) !!}</div>
                                        @if($item->banner_path)
                                            <a href="{{ asset('storage/'.$item->banner_path) }}" target="_blank" class="inline-block text-[9px] font-black text-emerald-600 hover:underline">🖼️ Lihat Lampiran Banner</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->theme === 'dark')
                                            <span class="px-2 py-0.5 rounded-md font-bold text-[8px] bg-slate-900 text-white border border-slate-950 uppercase">🌙 Dark Green</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md font-bold text-[8px] bg-slate-50 text-slate-700 border border-slate-200 uppercase">☀️ Light/White</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->link_url)
                                            <a href="{{ $item->link_url }}" target="_blank" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-150 text-emerald-700 font-extrabold text-[9px] uppercase tracking-wide rounded-lg transition inline-block">
                                                {{ $item->link_text ?? 'Link' }} &rarr;
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic text-[10px]">Tidak Ada Link</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('superadmin.public-highlights.toggle', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-full text-[9px] font-extrabold transition-all duration-150 cursor-pointer {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-400 hover:bg-slate-250' }}">
                                                {{ $item->is_active ? '🟢 AKTIF' : '⚪ NONAKTIF' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="text-[10px] font-bold text-slate-700">👁️ {{ $item->views_count ?? 0 }} View</div>
                                        <div class="text-[10px] font-bold text-emerald-600">🖱️ {{ $item->clicks_count ?? 0 }} Klik</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="editHighlight({{ json_encode($item) }})" class="text-emerald-600 hover:text-emerald-800 font-extrabold tracking-wider uppercase text-[10px] cursor-pointer">Edit</button>
                                            <span class="text-slate-200">|</span>
                                            <form action="{{ route('superadmin.public-highlights.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus sorotan depan ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-slate-400 italic font-medium">Belum ada kartu sorotan/kegiatan yang dibuat untuk public.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-4 border-t">
                {{ $highlights->links() }}
            </div>
        </div>

    </div>

</div>

<!-- ==================== MODAL: EDIT HIGHLIGHT ==================== -->
<div id="modal-edit" class="fixed inset-0 bg-slate-950/40 hidden items-center justify-center z-50 backdrop-blur-xs p-4 animate-fade-in">
    <div class="bg-white w-full max-w-xl p-6 sm:p-8 rounded-3xl max-h-[90vh] overflow-y-auto shadow-2xl transition-all border border-slate-100">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-lg font-black text-slate-800 tracking-tight">Perbarui Sorotan / Kegiatan</h2>
            <button onclick="toggleModal('modal-edit')" class="text-slate-400 hover:text-slate-650 font-bold text-xs uppercase tracking-wider outline-none">Tutup</button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-left">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Judul / Platform Sosial</label>
                <input type="text" name="title" id="edit_title" placeholder="Contoh: INSTAGRAM / @INSTITUTHIJAUINDONESIA" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Gaya Tema Kartu</label>
                <select name="theme" id="edit_theme" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-750 font-bold">
                    <option value="light">☀️ Tema Putih / Bersih (Teks Gelap)</option>
                    <option value="dark">🌙 Tema Hijau Gelap (Teks Putih - Kontras)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Perbarui Foto Banner (Opsional)</label>
                <input type="file" name="banner" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white">
                <p class="text-[9px] text-slate-400 mt-1 flex items-center gap-1 bg-slate-50 p-2 rounded-lg border border-slate-100/50">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Panduan Gambar:</strong> Rekomendasi <strong>1200 x 675 piksel</strong> (aspek rasio 16:9). JPEG/PNG maks 3MB.</span>
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Teks Tombol Link</label>
                    <input type="text" name="link_text" id="edit_link_text" placeholder="Contoh: Ikuti Diskusi" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">URL Target Link</label>
                    <input type="text" name="link_url" id="edit_link_url" placeholder="https://..." class="w-full p-2.5 border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Isi Teks / Diskursus Sorotan</label>
                <div id="edit-highlight-quill-editor" class="h-36 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;"></div>
                <input type="hidden" name="content" id="edit-hidden-content-input">
            </div>

            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white font-bold text-xs rounded-xl transition uppercase tracking-wider shadow-md shadow-emerald-50">
                💾 Simpan Perubahan Sorotan
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let quillEdit;

    function toggleModal(id) {
        const targetModal = document.getElementById(id);
        if (targetModal) {
            targetModal.classList.toggle('hidden');
            targetModal.classList.toggle('flex');
        }
    }

    function editHighlight(hl) {
        document.getElementById('editForm').action = '/superadmin/public-highlights/' + hl.id + '/update';
        document.getElementById('edit_title').value = hl.title || '';
        document.getElementById('edit_theme').value = hl.theme || 'light';
        document.getElementById('edit_link_text').value = hl.link_text || '';
        document.getElementById('edit_link_url').value = hl.link_url || '';
        
        // Update Quill editor content
        quillEdit.root.innerHTML = hl.content || '';
        document.getElementById('edit-hidden-content-input').value = hl.content || '';
        
        toggleModal('modal-edit');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Create Form Quill
        const quill = new Quill('#highlight-quill-editor', {
            theme: 'snow',
            placeholder: 'Tuliskan deskripsi/kutipan disini...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Edit Form Quill
        quillEdit = new Quill('#edit-highlight-quill-editor', {
            theme: 'snow',
            placeholder: 'Tuliskan deskripsi/kutipan disini...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Tampilkan data old jika ada
        const oldContent = document.getElementById('hidden-content-input').value;
        if (oldContent) {
            quill.root.innerHTML = oldContent;
        }

        // Sync Quill content ke hidden input saat submit form create
        const form = document.getElementById('highlight-form');
        form.onsubmit = function() {
            const html = quill.root.innerHTML;
            if (quill.getText().trim() === '') {
                document.getElementById('hidden-content-input').value = '';
            } else {
                document.getElementById('hidden-content-input').value = html;
            }
        };

        // Sync Quill content ke hidden input saat submit form edit
        const editForm = document.getElementById('editForm');
        editForm.onsubmit = function() {
            const html = quillEdit.root.innerHTML;
            if (quillEdit.getText().trim() === '') {
                document.getElementById('edit-hidden-content-input').value = '';
            } else {
                document.getElementById('edit-hidden-content-input').value = html;
            }
        };
    });
</script>
@endsection
