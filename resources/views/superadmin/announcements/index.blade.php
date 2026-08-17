@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-black text-slate-800">Manajemen Pengumuman/Banner</h1>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="px-4 py-2 bg-slate-950 text-white rounded-xl text-xs font-bold uppercase">
            + Tambah Banner
        </button>
    </div>

    {{-- Tabel Pengumuman --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b uppercase text-[10px] tracking-widest text-slate-500">
                <tr>
                    <th class="p-4 text-left">Banner</th>
                    <th class="p-4 text-left">Judul</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($announcements as $item)
                <tr class="border-b">
                    <td class="p-4">
                        @if($item->banner_path)
                            <img src="{{ asset('storage/'.$item->banner_path) }}" class="w-16 h-10 object-cover rounded">
                        @else
                            <span class="text-slate-400 text-[10px]">No Image</span>
                        @endif
                    </td>
                    <td class="p-4 font-bold text-slate-700">{{ $item->title }}</td>
                    <td class="p-4 text-center">
                        <form action="{{ route('iklan.toggle', $item->id) }}" method="POST">
                            @csrf
                            <button class="px-2 py-1 rounded text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $item->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-4 text-center">
                        {{-- Tambah tombol hapus jika perlu --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t">{{ $announcements->links() }}</div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modal-create" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
    <div class="bg-white p-8 rounded-3xl w-full max-w-lg">
        <h2 class="text-lg font-black mb-6">Tambah Banner Baru</h2>
        <form action="{{ route('iklan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <input type="text" name="title" placeholder="Judul Pengumuman" class="w-full p-3 border rounded-xl text-sm" required>
                <textarea name="description" placeholder="Keterangan..." class="w-full p-3 border rounded-xl text-sm"></textarea>
                <input type="file" name="banner" class="w-full p-2 border rounded-xl text-sm">
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="w-full py-3 bg-slate-100 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="w-full py-3 bg-slate-950 text-white rounded-xl font-bold">Simpan Banner</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
