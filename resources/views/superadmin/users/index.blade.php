@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="mb-8 border-b border-slate-100 pb-5 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
            <span class="text-[10px] font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Access Control</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2.5">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola data otentikasi hak akses akun, penugasan peran (role), dan status profil pemohon.</p>
        </div>
        <div>
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm uppercase tracking-wider">
                ➕ Tambah Pengguna
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-3xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-3xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/70 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="p-4 min-w-[200px]">
                            <a href="{{ route('superadmin.users.index', ['sort' => 'name', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 hover:text-slate-800 transition-colors">
                                <span>Nama Pengguna</span>
                                <span class="text-slate-400 text-xs">
                                    {{ request('sort') === 'name' ? (request('order') === 'asc' ? '↑' : '↓') : '⇅' }}
                                </span>
                            </a>
                        </th>
                        <th class="p-4">Alamat Email</th>
                        <th class="p-4 w-44">Hak Akses Role</th>
                        <th class="p-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $user->name }}</td>
                        <td class="p-4 text-slate-500 font-mono">{{ $user->email }}</td>
                        <td class="p-4">
                            @if($user->roles->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="bg-slate-100 text-slate-700 border border-slate-200/60 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">Tidak ada role</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @php
                                // Ambil nama role pertama sebagai string aman untuk dilempar ke Javascript modal
                                $firstRoleName = $user->roles->first()->name ?? '';
                            @endphp
                            <div class="flex items-center justify-center gap-3">
                                <button type="button"
                                        onclick="openEditModal({{ json_encode($user) }}, '{{ $firstRoleName }}')"
                                        class="text-emerald-600 hover:text-emerald-800 font-extrabold tracking-wider uppercase text-[10px]">
                                    Edit
                                </button>
                                @if(Auth::id() !== $user->id)
                                    <a href="{{ route('superadmin.users.impersonate', $user->id) }}"
                                       class="text-blue-600 hover:text-blue-800 font-extrabold tracking-wider uppercase text-[10px]"
                                       onclick="return confirm('Mulai impersonasi sebagai {{ $user->name }}?')">
                                        Impersonate
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 italic bg-slate-50/30">Tidak ada data pengguna yang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $users->links() }}
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 bg-slate-950/40 hidden items-center justify-center z-50 backdrop-blur-xs p-4 animate-fade-in">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl shadow-2xl transition-all border border-slate-100">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-base font-black text-slate-800 tracking-tight">Ubah Kredensial Pengguna</h3>
            <p class="text-[11px] text-slate-400">Modifikasi informasi akun dasar dan hak otoritas hak akses.</p>
        </div>

        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Alamat Email Resmi</label>
                <input type="email" name="email" id="edit_email" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none font-mono" required>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Tingkatan Otoritas Hak Akses</label>
                <select name="role" id="edit_role" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
                    <option value="">-- Pilih Akses Grup --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="edit_password" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none font-mono" placeholder="Minimal 8 karakter">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-slate-950 hover:bg-black text-white rounded-xl transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-create" class="fixed inset-0 bg-slate-950/40 hidden items-center justify-center z-50 backdrop-blur-xs p-4 animate-fade-in">
    <div class="bg-white w-full max-w-md p-6 rounded-3xl shadow-2xl transition-all border border-slate-100">
        <div class="border-b border-slate-100 pb-3 mb-5">
            <h3 class="text-base font-black text-slate-800 tracking-tight">Tambah Pengguna Baru</h3>
            <p class="text-[11px] text-slate-400">Buat otentikasi akun baru dan tetapkan peran akses.</p>
        </div>

        <form action="{{ route('superadmin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Nama Lengkap</label>
                <input type="text" name="name" placeholder="Cth: Ahmad Dahlan" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Alamat Email Resmi</label>
                <input type="email" name="email" placeholder="Cth: ahmad@instituthijau.or.id" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none font-mono" required>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Kata Sandi (Password)</label>
                <input type="password" name="password" placeholder="Minimal 8 karakter" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase text-slate-500 tracking-wide">Tingkatan Otoritas Hak Akses</label>
                <select name="role" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700" required>
                    <option value="">-- Pilih Akses Grup --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-sm">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        const targetModal = document.getElementById('modal-create');
        if(targetModal) {
            targetModal.classList.remove('hidden');
            targetModal.classList.add('flex');
        }
    }

    function closeCreateModal() {
        const targetModal = document.getElementById('modal-create');
        if(targetModal) {
            targetModal.classList.add('hidden');
            targetModal.classList.remove('flex');
        }
    }

    function openEditModal(user, roleName) {
        // 1. Injeksi Otoritas rute URI Action Form secara dinamis
        document.getElementById('editForm').action = '/superadmin/users/' + user.id;

        // 2. Set default value dari variabel objek user
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = roleName || '';
        document.getElementById('edit_password').value = '';

        // 3. Tampilkan kontainer modal box
        const targetModal = document.getElementById('modal-edit');
        if(targetModal) {
            targetModal.classList.remove('hidden');
            targetModal.classList.add('flex');
        }
    }

    function closeEditModal() {
        const targetModal = document.getElementById('modal-edit');
        if(targetModal) {
            targetModal.classList.add('hidden');
            targetModal.classList.remove('flex');
        }
    }
</script>
@endsection
