@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="mb-8 border-b border-slate-100 pb-5">
        <span class="text-[10px] font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Dynamic Form Builder</span>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight mt-2.5">Manajemen Field Biodata</h2>
        <p class="text-xs text-slate-500 mt-1">Konfigurasi skema formulir pendaftaran yang wajib diisi oleh calon peserta program.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-3xs animate-fade-in">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('superadmin.form-builder.store') }}" method="POST" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-3xs mb-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
            <div class="space-y-1.5 md:col-span-4">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Nama Field</label>
                <input type="text" name="name" placeholder="Contoh: Ukuran Jaket" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" required>
            </div>

            <div class="space-y-1.5 md:col-span-3">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Tipe Komponen</label>
                <select name="type" id="typeSelector" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" onchange="toggleOptions(this.value)">
                    <option value="text">Teks Singkat</option>
                    <option value="number">Angka</option>
                    <option value="date">Tanggal</option>
                    <option value="file">File / Dokumen</option>
                    <option value="select">Dropdown (Pilihan)</option>
                </select>
            </div>

            <div id="optionsWrapper" class="space-y-1.5 md:col-span-3 hidden">
                <label class="block text-xs font-bold uppercase text-slate-500 tracking-wide">Opsi Pilihan</label>
                <input type="text" name="options" id="optionsInput" placeholder="S, M, L, XL" class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
            </div>

            <div class="md:col-span-2 flex items-center h-11 pb-1">
                <label class="flex items-center gap-2.5 cursor-pointer group select-none">
                    <input type="checkbox" name="is_required" value="1" checked class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500/30 cursor-pointer">
                    <span class="text-xs font-bold text-slate-600 group-hover:text-slate-800 transition-colors">Wajib Diisi</span>
                </label>
            </div>

            <div class="md:col-span-12 lg:col-span-2 lg:ml-auto w-full pt-2 lg:pt-0">
                <button type="submit" class="w-full py-3 bg-slate-950 hover:bg-black text-white font-extrabold rounded-xl shadow-sm text-xs uppercase tracking-wider transition-colors">
                    Tambah
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-3xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/70 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="p-4">Nama Properti Input</th>
                        <th class="p-4 w-40">Tipe Input</th>
                        <th class="p-4 w-32 text-center">Validasi</th>
                        <th class="p-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                    @forelse($fields as $field)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 font-bold text-slate-800">{{ $field->name }}</td>
                        <td class="p-4">
                            <span class="font-mono bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ strtoupper($field->type) }}
                            </span>
@if($field->type === 'select' && $field->options)
    @php
        // Memastikan $options berbentuk string untuk ditampilkan
        $optionsString = is_array($field->options) ? implode(', ', $field->options) : $field->options;
    @endphp
    <span class="block text-[10px] text-slate-400 mt-1 truncate max-w-xs" title="{{ $optionsString }}">
        Pilihan: {{ $optionsString }}
    </span>
@endif
                        </td>
                        <td class="p-4 text-center">
                            @if($field->is_required)
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-wide">Required</span>
                            @else
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-slate-100 text-slate-400 uppercase tracking-wide">Optional</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <form action="{{ route('superadmin.form-builder.destroy', $field->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-extrabold tracking-wider uppercase text-[10px]" onclick="return confirm('Apakah Anda yakin ingin menghapus properti kolom input ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 italic bg-slate-50/30">Belum ada field tambahan yang dibuat untuk formulir biodata.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleOptions(val) {
    const wrapper = document.getElementById('optionsWrapper');
    const input = document.getElementById('optionsInput');

    if (val === 'select') {
        wrapper.classList.remove('hidden');
        input.setAttribute('required', 'required');
    } else {
        wrapper.classList.add('hidden');
        input.removeAttribute('required');
        input.value = ''; // Reset nilai jika tipe diubah kembali
    }
}

// Sinkronisasi ulang tampilan saat halaman pertama kali dimuat (antisipasi jika select menyimpan state lama)
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('typeSelector');
    if(selector) toggleOptions(selector.value);
});
</script>
@endsection
