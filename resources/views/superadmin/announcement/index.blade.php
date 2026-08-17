@extends('superadmin.layouts.app') {{-- Sesuaikan dengan nama layout master superadmin Anda --}}
@section('title', 'Pusat Penyiaran Maklumat')
@section('content')
<!-- Load Quill editor styling and JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="mb-4">
        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Central Broadcasting Center</span>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Pusat Pengumuman Super Admin</h1>
        <p class="text-sm text-slate-500 mt-1">Siarkan instruksi darurat secara global ke seluruh sistem atau filter per program kerja.</p>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold shadow-sm">
            ✨ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit space-y-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">Rakit Siaran Maklumat</span>            <form id="broadcast-form" action="{{ route('superadmin.announcements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Cakupan Ruang Target</label>
                    <select name="target" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 font-bold">
                        <option value="global" class="text-emerald-700 font-bold">🌍 GLOBAL BROADCAST (Semua User Aplikasi)</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}">📦 PROGRAM: {{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Judul Pengumuman</label>
                        <input type="text" name="title" placeholder="Ketik judul maklumat..." class="w-full p-2.5 border border-slate-200 rounded-xl text-xs" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500">Derajat Sifat</label>
                        <select name="type" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-700">
                            <option value="info">Informasi Umum</option>
                            <option value="instruction">Instruksi Wajib Baca (Blocker Gate)</option>
                            <option value="warning">Peringatan Darurat Darurat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500">Isi Maklumat Instruksi</label>
                    <div id="broadcast-content-editor" class="h-36 bg-slate-50/50 rounded-xl text-xs" style="font-size: 11px;"></div>
                    <input type="hidden" name="content" id="hidden-broadcast-content">
                </div>

                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-xs rounded-xl hover:from-emerald-700 transition uppercase tracking-wider shadow-md shadow-emerald-50">
                    📣 Siarkan &amp; Eksekusi Email
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">Log Arsip Siaran Historis</span>

            <div class="space-y-2.5 max-h-[450px] overflow-y-auto pr-1">
                @forelse($announcements as $ann)
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex justify-between items-start text-xs shadow-3xs">
                        <div class="space-y-1.5 flex-1 pr-4">
                            <div class="flex items-center space-x-2 flex-wrap gap-1">
                                <span class="font-bold text-slate-800 text-sm">📣 {{ $ann->title }}</span>
                                @if($ann->program_id)
                                    <span class="text-[8px] bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded font-black uppercase">📦 PROGRAM: {{ $ann->program->name }}</span>
                                @else
                                    <span class="text-[8px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-1.5 py-0.5 rounded font-black uppercase">🌍 GLOBAL ALL USER</span>
                                @endif
                                <span class="text-[8px] bg-white text-slate-400 border px-1.5 py-0.5 rounded font-bold uppercase">{{ $ann->type }}</span>
                            </div>
                            <div class="text-slate-500 font-medium leading-relaxed">{!! $ann->content !!}</div>
                        </div>
                        <form action="{{ route('superadmin.announcements.delete', $ann->id) }}" method="POST" onsubmit="return confirm('Hapus arsip siaran ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 font-bold hover:bg-rose-50 px-2 py-1 rounded transition-colors text-xs">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic text-center py-6">Belum ada historis pengumuman yang disiarkan Super Admin.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill
        const quill = new Quill('#broadcast-content-editor', {
            theme: 'snow',
            placeholder: 'Tulis instruksi mendalam disini...'
        });

        // Sync Quill editor text on submit
        const form = document.getElementById('broadcast-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                document.getElementById('hidden-broadcast-content').value = quill.root.innerHTML;
            });
        }
    });
</script>
@endsection
