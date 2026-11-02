@extends('superadmin.layouts.app')
@section('title', 'Manajemen Template Piagam')

@section('content')
<div class="space-y-6" x-data="templateIndexApp()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen Template Piagam</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola template PDF dasar dan syarat unduh piagam.</p>
        </div>
        <a href="{{ route('superadmin.piagam.templates.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Upload Template Baru
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider w-1/3">Nama Template</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider w-1/3">Dipakai Oleh Program</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($templates as $template)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800">{{ $template->name }}</div>
                                <div class="text-[10px] font-medium text-slate-400 mt-0.5">PDF &bull; {{ $template->total_pages }} Halaman</div>
                                @if($template->description)
                                    <div class="text-xs text-slate-500 mt-1 italic">{{ $template->description }}</div>
                                @endif

                                @if(!empty($template->download_requirements))
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                            {{ count($template->download_requirements) }} Syarat Unduh Aktif
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-1">
                                        <span class="text-[10px] text-slate-400 italic">Tanpa syarat unduh</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($template->programs->count() > 0)
                                    <div class="flex flex-col gap-1">
                                        @foreach($template->programs as $program)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-emerald-100 text-emerald-800 w-fit">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                {{ $program->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum dipasangkan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEditModal({{ json_encode($template) }})" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Syarat & Info Template">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" @click="assignModalOpen = true; selectedTemplate = '{{ $template->id }}'" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Pasangkan ke Program">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    </button>
                                    <a href="{{ route('superadmin.piagam.templates.edit', $template->id) }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Visual Layout Editor">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="{{ route('superadmin.piagam.templates.destroy', $template->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus template ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="text-sm font-medium">Belum ada template.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $templates->links() }}
        </div>
    </div>

    <!-- Edit Template & Syarat Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen" @click="editModalOpen = false" class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="editModalOpen" class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                <form :action="'{{ url('superadmin/piagam/templates') }}/' + editForm.id" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-black text-slate-800">Edit Template & Syarat Unduh</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Atur nama template dan tautan syarat yang wajib diklik peserta.</p>
                            </div>
                            <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-1">Nama Template</label>
                                <input type="text" name="name" x-model="editForm.name" required class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-1">Deskripsi (Opsional)</label>
                                <textarea name="description" x-model="editForm.description" rows="2" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                            </div>

                            <!-- SYARAT UNDUH REPEATER IN EDIT MODAL -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <label class="text-xs font-black uppercase tracking-wider text-slate-800">Syarat Wajib Sebelum Unduh Piagam</label>
                                        <p class="text-[11px] text-slate-400">Peserta wajib membuka link ini sebelum tombol unduh piagam terbuka.</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mb-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <span class="text-[11px] font-bold text-slate-500 self-center mr-1">Tambah Cepat:</span>
                                    <button type="button" @click="addEditInstagram()" class="px-2.5 py-1 bg-white hover:bg-pink-50 text-pink-600 border border-slate-200 text-xs font-bold rounded-lg shadow-2xs">
                                        + Follow IG
                                    </button>
                                    <button type="button" @click="addEditYoutube()" class="px-2.5 py-1 bg-white hover:bg-red-50 text-red-600 border border-slate-200 text-xs font-bold rounded-lg shadow-2xs">
                                        + Subscribe YT
                                    </button>
                                    <button type="button" @click="addEditWhatsApp()" class="px-2.5 py-1 bg-white hover:bg-emerald-50 text-emerald-600 border border-slate-200 text-xs font-bold rounded-lg shadow-2xs">
                                        + Join WA
                                    </button>
                                    <button type="button" @click="addEditCustom()" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg shadow-2xs">
                                        + Lainnya
                                    </button>
                                </div>

                                <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                                    <template x-for="(req, index) in editForm.requirements" :key="index">
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-slate-700" x-text="'Syarat #' + (index + 1)"></span>
                                                <button type="button" @click="removeEditRequirement(index)" class="text-slate-400 hover:text-rose-600 text-xs font-bold">
                                                    Hapus
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                                <div class="sm:col-span-6">
                                                    <input type="text" :name="'requirements[' + index + '][title]'" x-model="req.title" required class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="Judul Syarat (cth: Follow Instagram @...)">
                                                </div>
                                                <div class="sm:col-span-4">
                                                    <input type="url" :name="'requirements[' + index + '][url]'" x-model="req.url" required class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="URL Link">
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input type="text" :name="'requirements[' + index + '][button_text]'" x-model="req.button_text" class="w-full bg-white border border-slate-200 text-xs text-slate-800 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="Teks Tombol">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="editForm.requirements.length === 0" class="p-4 border border-dashed border-slate-200 rounded-xl text-center text-xs text-slate-400">
                                    Tidak ada syarat unduh. Peserta dapat langsung unduh tanpa klik link.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div x-show="assignModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="assignModalOpen" @click="assignModalOpen = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="assignModalOpen" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('superadmin.piagam.templates.assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template_id" x-bind:value="selectedTemplate">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">
                                    Pasangkan Template ke Program
                                </h3>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Program</label>
                                    <select name="program_id" required class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        <option value="">-- Pilih Program --</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Pasangan
                        </button>
                        <button type="button" @click="assignModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function templateIndexApp() {
    return {
        assignModalOpen: false,
        selectedTemplate: null,
        editModalOpen: false,
        editForm: {
            id: null,
            name: '',
            description: '',
            requirements: []
        },
        openEditModal(tpl) {
            this.editForm.id = tpl.id;
            this.editForm.name = tpl.name;
            this.editForm.description = tpl.description || '';
            let reqs = tpl.download_requirements;
            if (typeof reqs === 'string') {
                try { reqs = JSON.parse(reqs); } catch(e) { reqs = []; }
            }
            this.editForm.requirements = Array.isArray(reqs) ? JSON.parse(JSON.stringify(reqs)) : [];
            this.editModalOpen = true;
        },
        addEditInstagram() {
            this.editForm.requirements.push({
                title: 'Wajib Follow Instagram Resmi Institut Hijau Indonesia',
                url: 'https://instagram.com/instituthijauindonesia',
                button_text: 'Follow Instagram'
            });
        },
        addEditYoutube() {
            this.editForm.requirements.push({
                title: 'Wajib Subscribe Channel YouTube Institut Hijau Indonesia',
                url: 'https://www.youtube.com/@instituthijauindonesia',
                button_text: 'Subscribe YouTube'
            });
        },
        addEditWhatsApp() {
            this.editForm.requirements.push({
                title: 'Wajib Gabung Grup / Saluran Resmi WhatsApp',
                url: 'https://chat.whatsapp.com/',
                button_text: 'Gabung WhatsApp'
            });
        },
        addEditCustom() {
            this.editForm.requirements.push({
                title: '',
                url: '',
                button_text: 'Buka Tautan'
            });
        },
        removeEditRequirement(idx) {
            this.editForm.requirements.splice(idx, 1);
        }
    };
}
</script>
@endsection
