@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="mb-8 border-b border-slate-100 pb-5 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
            <span class="text-[10px] font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Access Control</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2.5">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola data otentikasi hak akses akun, penugasan peran (role), dan status profil pemohon.</p>
        </div>
        <div class="flex flex-wrap gap-2.5 justify-end">
            @if($mitigationMode === '1')
                <form action="{{ route('superadmin.users.toggle-mitigation-global') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MENONAKTIFKAN mode mitigasi global? Keamanan email & password ketat akan diberlakukan kembali.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm uppercase tracking-wider">
                        ⚡ Mitigasi Global: AKTIF
                    </button>
                </form>
            @else
                <form action="{{ route('superadmin.users.toggle-mitigation-global') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MENGAKTIFKAN mode mitigasi global? Verifikasi email akan dilewati dan pendaftar tidak sempurna bisa masuk dengan password bebas.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-400 hover:bg-slate-500 text-white text-xs font-bold rounded-xl transition shadow-sm uppercase tracking-wider">
                        💤 Mitigasi Global: NONAKTIF
                    </button>
                </form>
            @endif

            <a href="{{ route('superadmin.users.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm uppercase tracking-wider">
                📥 Eksport Excel
            </a>
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

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold shadow-3xs">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-3xs mb-6">
        <form method="GET" action="{{ route('superadmin.users.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 w-full">
                <!-- Search input -->
                <div>
                    <label class="block text-[9px] font-extrabold uppercase text-slate-450 mb-1.5">Cari Pengguna</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 outline-none">
                </div>

                <!-- Role dropdown -->
                <div>
                    <label class="block text-[9px] font-extrabold uppercase text-slate-450 mb-1.5">Filter Role</label>
                    <select name="role" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 outline-none text-slate-700 font-semibold">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ strtoupper($r->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status dropdown -->
                <div>
                    <label class="block text-[9px] font-extrabold uppercase text-slate-450 mb-1.5">Status Akun</label>
                    <select name="status" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 outline-none text-slate-700 font-semibold">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-[9px] font-extrabold uppercase text-slate-450 mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 outline-none text-slate-700 font-semibold">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-[9px] font-extrabold uppercase text-slate-450 mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 outline-none text-slate-700 font-semibold">
                </div>
            </div>
            
            <div class="flex gap-2 w-full md:w-auto shrink-0 lg:pt-5">
                <button type="submit" class="flex-grow md:flex-grow-0 px-5 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-sm uppercase tracking-wider">
                    Saring
                </button>
                <a href="{{ route('superadmin.users.index') }}" class="flex-grow md:flex-grow-0 px-5 py-2.5 bg-slate-100 hover:bg-slate-250 text-slate-700 text-xs font-bold rounded-xl transition text-center uppercase tracking-wider border">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <form id="bulk-form" method="POST" action="{{ route('superadmin.users.bulk') }}">
        @csrf
        <input type="hidden" name="action" id="bulk-action" value="">

        <!-- Bulk Action Bar -->
        <div id="bulk-action-bar" class="hidden mb-6 p-4 bg-slate-900 text-white rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-md animate-fade-in">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-300"><span id="selected-count" class="text-emerald-400 font-extrabold text-sm">0</span> Akun Terpilih</span>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <button type="button" onclick="submitBulk('bypass_email')" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-[10px] font-black rounded-xl uppercase tracking-wider transition shadow-sm">
                    Bypass Email Massal
                </button>
                <button type="button" onclick="submitBulk('auto_password')" class="px-3.5 py-2 bg-violet-600 hover:bg-violet-750 text-white text-[10px] font-black rounded-xl uppercase tracking-wider transition shadow-sm">
                    Auto PW Massal
                </button>
                <button type="button" onclick="submitBulk('block')" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-750 text-white text-[10px] font-black rounded-xl uppercase tracking-wider transition shadow-sm">
                    Block Massal
                </button>
                <button type="button" onclick="submitBulk('delete')" class="px-3.5 py-2 bg-red-700 hover:bg-red-800 text-white text-[10px] font-black rounded-xl uppercase tracking-wider transition shadow-sm">
                    Delete Massal
                </button>
            </div>
        </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-3xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/70 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/40 text-slate-400 font-extrabold uppercase text-[9px] tracking-wider">
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" id="checkbox-all" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="p-4">
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
                        <th class="p-4 w-32 text-center">Status Akun</th>
                        <th class="p-4 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 text-center">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                        </td>
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
                            @if($user->is_blocked)
                                <span class="bg-rose-50 text-rose-700 border border-rose-100 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
                                    🔴 Diblokir
                                </span>
                            @else
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
                                    🟢 Aktif
                                </span>
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
                                    
                                    <form action="{{ route('superadmin.users.toggle-block', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_blocked ? 'mengaktifkan kembali' : 'memblokir' }} akses akun {{ $user->name }}?')">
                                        @csrf
                                        <button type="submit" class="{{ $user->is_blocked ? 'text-emerald-600 hover:text-emerald-800' : 'text-rose-600 hover:text-rose-800' }} font-extrabold tracking-wider uppercase text-[10px]">
                                            {{ $user->is_blocked ? 'Unblock' : 'Block' }}
                                        </button>
                                    </form>

                                    @if(is_null($user->email_verified_at))
                                        <form action="{{ route('superadmin.users.bypass-email', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Lewati verifikasi email untuk {{ $user->name }}?')">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:text-amber-800 font-extrabold tracking-wider uppercase text-[10px]">
                                                Bypass Email
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('superadmin.users.force-password', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Set default password & wajibkan ganti password untuk {{ $user->name }}? User dapat login menggunakan password APA PUN.')">
                                        @csrf
                                        <button type="submit" class="text-violet-600 hover:text-violet-800 font-extrabold tracking-wider uppercase text-[10px]">
                                            Auto PW
                                        </button>
                                    </form>

                                    <form action="{{ route('superadmin.users.delete', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }} secara permanen? Seluruh data profil, alamat, dan pendaftaran peserta juga akan terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-650 hover:text-rose-800 font-extrabold tracking-wider uppercase text-[10px]">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 italic bg-slate-50/30">Tidak ada data pengguna yang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>

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

    // Aksi Massal (Bulk Actions) Javascript
    const checkboxAll = document.getElementById('checkbox-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionBar = document.getElementById('bulk-action-bar');
    const selectedCount = document.getElementById('selected-count');
    const bulkActionInput = document.getElementById('bulk-action');

    if (checkboxAll) {
        checkboxAll.addEventListener('change', function() {
            userCheckboxes.forEach(cb => {
                cb.checked = checkboxAll.checked;
            });
            updateBulkActionBar();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) {
                checkboxAll.checked = false;
            } else {
                const allChecked = Array.from(userCheckboxes).every(c => c.checked);
                checkboxAll.checked = allChecked;
            }
            updateBulkActionBar();
        });
    });

    function updateBulkActionBar() {
        const checkedCount = Array.from(userCheckboxes).filter(c => c.checked).length;
        if (checkedCount > 0) {
            selectedCount.textContent = checkedCount;
            bulkActionBar.classList.remove('hidden');
            bulkActionBar.classList.add('flex');
        } else {
            bulkActionBar.classList.add('hidden');
            bulkActionBar.classList.remove('flex');
        }
    }

    function submitBulk(action) {
        let confirmMsg = 'Apakah Anda yakin ingin memproses aksi massal ini?';
        if (action === 'bypass_email') {
            confirmMsg = 'Apakah Anda yakin ingin melewati verifikasi email untuk akun terpilih?';
        } else if (action === 'auto_password') {
            confirmMsg = 'Apakah Anda yakin ingin mereset password terpilih menjadi default dan mewajibkan ganti password?';
        } else if (action === 'block') {
            confirmMsg = 'Apakah Anda yakin ingin memblokir akses akun terpilih?';
        } else if (action === 'delete') {
            confirmMsg = 'PERINGATAN KERAS! Apakah Anda yakin ingin menghapus permanen semua akun terpilih beserta seluruh data pendaftaran dan profilnya? Aksi ini tidak dapat dibatalkan.';
        }

        if (confirm(confirmMsg)) {
            bulkActionInput.value = action;
            document.getElementById('bulk-form').submit();
        }
    }
</script>
@endsection
