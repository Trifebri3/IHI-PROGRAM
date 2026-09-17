@extends('superadmin.layouts.app')
@section('title', 'Upload Template Piagam')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.piagam.templates.index') }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Upload Template Baru</h1>
                <p class="text-sm text-slate-500 mt-1">Unggah PDF dasar yang akan dijadikan template piagam.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('superadmin.piagam.templates.store') }}" method="POST" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8 space-y-6">
        @csrf
        
        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nama Template</label>
            <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Contoh: Sertifikat Green Leaders 2026">
        </div>

        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 text-sm text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Penjelasan singkat kegunaan template..."></textarea>
        </div>

        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">File PDF Template</label>
            <input type="file" name="file_path" accept=".pdf" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
            <p class="text-xs text-slate-400 mt-2">Hanya menerima file berformat PDF. Maksimal 10MB.</p>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                Simpan & Lanjut ke Editor
            </button>
        </div>
    </form>
</div>
@endsection
