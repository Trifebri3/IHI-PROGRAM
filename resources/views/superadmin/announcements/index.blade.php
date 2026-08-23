@extends('superadmin.layouts.app')

@section('content')
<!-- Load Quill editor styling and JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Manajemen Pengumuman/Banner</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola slider iklan promo, instruksi penting, dan pantau analitik tayangan real-time.</p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="px-4 py-2.5 bg-slate-950 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-xs hover:bg-black transition-all">
            + Tambah Banner
        </button>
    </div>

    {{-- Tabel Pengumuman --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-slate-50 border-b uppercase text-[10px] tracking-widest text-slate-500 font-bold">
                <tr>
                    <th class="p-4 text-left">Banner</th>
                    <th class="p-4 text-left">Info Banner</th>
                    <th class="p-4 text-center">Statistik Tayangan</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($announcements as $item)
                <tr class="hover:bg-slate-50/20 transition-colors">
                    <td class="p-4 align-middle">
                        @if($item->banner_path)
                            <img src="{{ asset('storage/'.$item->banner_path) }}" class="w-24 h-12 object-cover rounded-xl border border-slate-100 shadow-3xs">
                        @else
                            <div class="w-24 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-mono text-[9px] uppercase">No Image</div>
                        @endif
                    </td>
                    <td class="p-4 align-middle max-w-sm">
                        <div class="font-bold text-slate-800 text-sm">{{ $item->title }}</div>
                        <div class="text-slate-400 text-[10px] mt-1 line-clamp-2">
                            {{ strip_tags($item->description) }}
                        </div>
                    </td>
                    <td class="p-4 text-center align-middle">
                        @php
                            $totalViews = $item->views->sum('views_count');
                            $uniqueViews = $item->views->count();
                        @endphp
                        <div class="text-xs text-slate-700 font-bold">
                            {{ $uniqueViews }} Pembaca <span class="text-slate-300">/</span> {{ $totalViews }} Tayangan
                        </div>
                        <button type="button" 
                                onclick="openAnalyticsModal({{ $item->id }})" 
                                class="mt-1 inline-flex items-center text-blue-600 hover:text-blue-800 font-extrabold uppercase text-[10px] tracking-wider">
                            🔎 Lihat Detail Pembaca
                        </button>
                    </td>
                    <td class="p-4 text-center align-middle">
                        <form action="{{ route('iklan.toggle', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button class="px-3 py-1.5 rounded-lg text-[10px] font-extrabold tracking-wider uppercase transition {{ $item->is_active ? 'bg-emerald-55 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                {{ $item->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-4 text-center align-middle">
                        <form action="{{ route('iklan.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1.5 text-rose-650 hover:text-rose-800 font-extrabold uppercase text-[10px] tracking-wider">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 italic bg-slate-50/20">Belum ada data banner pengumuman terpasang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($announcements->hasPages())
            <div class="p-4 border-t">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modal-create" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
    <div class="bg-white p-8 rounded-3xl w-full max-w-lg shadow-2xl">
        <h2 class="text-lg font-black mb-6 text-slate-800">Tambah Banner Baru</h2>
        <form action="{{ route('iklan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Judul Banner</label>
                    <input type="text" name="title" placeholder="Masukkan judul..." class="w-full p-3 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-slate-900" required>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Berkas Banner Gambar</label>
                    <input type="file" name="banner" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50 cursor-pointer">
                    <p class="text-[9px] text-slate-450 mt-1.5 flex items-center gap-1 bg-slate-50 p-2 rounded-lg border border-slate-100/50">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Panduan Banner:</strong> Rekomendasi lanskap lebar 3:1 (contoh: <strong>1200 x 400 px</strong>). Maks 2MB.</span>
                    </p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi / Detail Informasi (Rich Text Editor)</label>
                    <div id="description-editor" class="h-32 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;"></div>
                    <input type="hidden" name="description" id="hidden-description">
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="w-full py-3 bg-slate-100 text-slate-650 hover:bg-slate-200 rounded-xl font-bold text-xs uppercase tracking-wider transition-all">Batal</button>
                    <button type="submit" class="w-full py-3 bg-slate-950 text-white hover:bg-black rounded-xl font-bold text-xs uppercase tracking-wider transition-all">Simpan Banner</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Viewer (Analytics) --}}
<div id="modal-analytics" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
    <div class="bg-white p-8 rounded-3xl w-full max-w-2xl shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                <span>📊</span> Analitik Pembaca Banner
            </h2>
            <button onclick="closeAnalyticsModal()" class="text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-widest">&times; Tutup</button>
        </div>
        
        <div class="mb-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50">
            <h3 id="analytics-title" class="text-sm font-bold text-slate-800">Judul Banner</h3>
            <div class="flex gap-4 mt-2 text-[10px] uppercase font-bold tracking-wider">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md border border-blue-100" id="analytics-total-views">Total Tayangan: 0</span>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-100" id="analytics-unique-views">Total Pembaca: 0</span>
            </div>
        </div>

        <div class="max-h-72 overflow-y-auto border border-slate-100 rounded-2xl">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b uppercase text-[9px] tracking-wider text-slate-500 font-bold">
                    <tr>
                        <th class="p-3">Nama Pembaca</th>
                        <th class="p-3">Email</th>
                        <th class="p-3 text-center">Dibuka (Kali)</th>
                        <th class="p-3 text-right">Terakhir Dibuka</th>
                    </tr>
                </thead>
                <tbody id="analytics-table-body" class="divide-y divide-slate-100">
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const announcementsData = @json($announcements->items());

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill Editor
        const quill = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Tuliskan deskripsi, info link pendaftaran, atau pengumuman detail di sini...'
        });

        // Sync Quill editor to hidden input before form submit
        const form = document.querySelector('#modal-create form');
        form.addEventListener('submit', function(e) {
            document.getElementById('hidden-description').value = quill.root.innerHTML;
        });
    });

    function openAnalyticsModal(id) {
        const announcement = announcementsData.find(item => item.id == id);
        if (!announcement) return;

        document.getElementById('analytics-title').innerText = announcement.title;
        
        let totalViews = 0;
        let uniqueViews = announcement.views ? announcement.views.length : 0;
        
        const tbody = document.getElementById('analytics-table-body');
        tbody.innerHTML = '';

        if (announcement.views && announcement.views.length > 0) {
            announcement.views.forEach(view => {
                totalViews += parseInt(view.views_count || 1);
                
                const name = view.user ? view.user.name : 'Unknown User';
                const email = view.user ? view.user.email : '-';
                const count = view.views_count || 1;
                
                const dateStr = new Date(view.last_viewed_at).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const row = `
                    <tr class="hover:bg-slate-50/50">
                        <td class="p-3 font-bold text-slate-700">${name}</td>
                        <td class="p-3 text-slate-500">${email}</td>
                        <td class="p-3 text-center font-bold text-slate-800">${count}</td>
                        <td class="p-3 text-right text-slate-450">${dateStr}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400 italic bg-slate-55/10">Belum ada pembaca yang membuka banner ini.</td>
                </tr>
            `;
        }

        document.getElementById('analytics-total-views').innerText = `Total Tayangan: ${totalViews}`;
        document.getElementById('analytics-unique-views').innerText = `Total Pembaca: ${uniqueViews}`;

        document.getElementById('modal-analytics').classList.remove('hidden');
    }

    function closeAnalyticsModal() {
        document.getElementById('modal-analytics').classList.add('hidden');
    }
</script>
@endsection
