@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <!-- HEADER UTAMA -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Master Program</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola konfigurasi data master program, delegasi admin pelaksana, dan visual aset.</p>
        </div>
        <button onclick="toggleModal('modal-create')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-200/50 transition-all self-start sm:self-center">
            + Program Baru
        </button>
    </div>

    <!-- NOTIFIKASI SUKSES -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-3xs">
            {{ session('success') }}
        </div>
    @endif

    <!-- TABEL DATA MASTER -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-3xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/70 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="p-4 w-20 text-center">Visual</th>
                        <th class="p-4">Program</th>
                        <th class="p-4">Admin Pelaksana (PJ)</th>
                        <th class="p-4 w-28 text-center">Status</th>
                        <th class="p-4 w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                    @forelse($programs as $program)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 text-center">
                            @if($program->logo_path)
                                <img src="{{ asset('storage/'.$program->logo_path) }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 mx-auto shadow-3xs">
                            @else
                                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-[10px] font-black text-slate-400 flex items-center justify-center mx-auto">N/A</div>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $program->name }}</td>
                        <td class="p-4 text-slate-500 font-medium">
                            @if($program->managers->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($program->managers as $m)
                                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-[11px]">{{ $m->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide {{ $program->status == 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50' : 'bg-slate-100 text-slate-600' }}">
                                {{ $program->status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-3">
                                <button onclick="editProgram({{ json_encode($program) }})" class="text-emerald-600 hover:text-emerald-800 font-extrabold tracking-wider uppercase text-[10px]">Edit</button>
                                <span class="text-slate-200">|</span>
                                <form action="{{ route('superadmin.programs.destroy', $program->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-extrabold tracking-wider uppercase text-[10px]" onclick="return confirm('Apakah Anda yakin ingin menghapus master data program ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 italic bg-slate-50/30">Belum ada data master program yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODAL: TAMBAH PROGRAM ==================== -->
<div id="modal-create" class="fixed inset-0 bg-slate-950/40 hidden items-center justify-center z-50 backdrop-blur-xs p-4 animate-fade-in">
    <div class="bg-white w-full max-w-2xl p-6 sm:p-8 rounded-3xl max-h-[90vh] overflow-y-auto shadow-2xl transition-all border border-slate-100">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-lg font-black text-slate-800 tracking-tight">Tambah Master Program Baru</h2>
            <button onclick="toggleModal('modal-create')" class="text-slate-400 hover:text-slate-600 font-bold text-xs uppercase tracking-wider outline-none">Tutup</button>
        </div>

        <form action="{{ route('superadmin.programs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Nama Program</label>
                    <input type="text" name="name" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" placeholder="Contoh: Magang Bakti Pelaksana" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Admin Pelaksana (PJ)</label>
                    <select name="selected_admin_id" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                        <option value="">-- Pilih Penanggung Jawab --</option>
                        @foreach($adminPrograms as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Deskripsi Lengkap</label>
                <textarea name="description" rows="4" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none resize-none" placeholder="Jelaskan detail cakupan ruang lingkup program kerja..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Kuota Peserta</label>
                    <input type="number" name="quota" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" placeholder="50" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Icon / Logo Berkas</label>
                    <input type="file" name="logo" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Banner Halaman</label>
                    <input type="file" name="banner" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all cursor-pointer">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Status Awal Publikasi</label>
                <select name="status" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                    <option value="draft">Draft (Sembunyikan dari Publik)</option>
                    <option value="published">Published (Tampilkan di Katalog Pemohon)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors shadow-sm shadow-emerald-200">
                    Simpan & Terbitkan Program
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL: EDIT PROGRAM ==================== -->
<div id="modal-edit" class="fixed inset-0 bg-slate-950/40 hidden items-center justify-center z-50 backdrop-blur-xs p-4 animate-fade-in">
    <div class="bg-white w-full max-w-2xl p-6 sm:p-8 rounded-3xl max-h-[90vh] overflow-y-auto shadow-2xl transition-all border border-slate-100">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-lg font-black text-slate-800 tracking-tight">Perbarui Master Data Program</h2>
            <button onclick="toggleModal('modal-edit')" class="text-slate-400 hover:text-slate-600 font-bold text-xs uppercase tracking-wider outline-none">Tutup</button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Nama Program</label>
                    <input type="text" name="name" id="edit_name" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Admin Pelaksana (PJ)</label>
                    <select name="selected_admin_id" id="edit_admin" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                        @foreach($adminPrograms as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Deskripsi Program</label>
                <textarea name="description" id="edit_description" rows="4" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Kuota Instansi</label>
                    <input type="number" name="quota" id="edit_quota" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="edit_start_date" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="edit_end_date" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Status Sinkronisasi</label>
                <select name="status" id="edit_status" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors shadow-sm shadow-emerald-200">
                    Simpan Perubahan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JAVASCRIPT LOGIC CONTROLLER -->
<script>
    function toggleModal(id) {
        const targetModal = document.getElementById(id);
        if (targetModal) {
            targetModal.classList.toggle('hidden');
            targetModal.classList.toggle('flex');
        }
    }

    function editProgram(p) {
        document.getElementById('editForm').action = '/superadmin/programs/' + p.id;
        document.getElementById('edit_name').value = p.name || '';
        document.getElementById('edit_quota').value = p.quota || 0;
        document.getElementById('edit_description').value = p.description || '';
        document.getElementById('edit_status').value = p.status || 'draft';

        // Format tanggal standar database Y-m-d ke input form HTML
        if(p.start_date) {
            document.getElementById('edit_start_date').value = p.start_date.split('T')[0];
        }
        if(p.end_date) {
            document.getElementById('edit_end_date').value = p.end_date.split('T')[0];
        }

        // Mapping relasi ID admin pelaksana
        if (p.managers && p.managers.length > 0) {
            document.getElementById('edit_admin').value = p.managers[0].id;
        } else {
            document.getElementById('edit_admin').value = '';
        }

        toggleModal('modal-edit');
    }
</script>
@endsection
