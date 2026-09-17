@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengisian Nilai Sertifikat</h1>
            <p class="text-slate-600 mt-1">Isi variabel kustom sertifikat untuk setiap peserta program <strong>{{ $program->name }}</strong>.</p>
        </div>
        <div class="flex gap-3 items-center">
            <form action="{{ route('adminprogram.piagam.grades.index', $program->id) }}" method="GET" class="flex gap-2">
                <select name="sort" onchange="this.form.submit()" class="text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-2">
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </form>
            <form action="{{ route('adminprogram.piagam.grades.upload_template', $program->id) }}" method="POST" enctype="multipart/form-data" class="hidden" id="uploadForm">
                @csrf
                <input type="file" name="file" id="fileInput" accept=".csv" onchange="document.getElementById('uploadForm').submit()">
            </form>
            <button type="button" onclick="document.getElementById('fileInput').click()" class="px-5 py-2.5 bg-amber-600 text-white font-medium rounded-xl hover:bg-amber-700 shadow-sm flex items-center gap-2 transition-colors" title="Upload Template CSV">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload CSV
            </button>
            <a href="{{ route('adminprogram.piagam.grades.download_template', $program->id) }}" class="px-5 py-2.5 bg-sky-600 text-white font-medium rounded-xl hover:bg-sky-700 shadow-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                Download Template CSV
            </a>
            <button type="button" onclick="document.getElementById('gradesForm').submit()" class="px-5 py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 shadow-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan Halaman Ini
            </button>
            <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 shadow-sm flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                Generator Piagam
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(count($customVariables) === 0)
        <div class="p-8 bg-white rounded-xl shadow-sm border border-slate-200 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Tidak Ada Variabel Kustom</h3>
            <p class="text-slate-600">Template sertifikat untuk program ini tidak mendefinisikan variabel nilai kustom. Anda bisa langsung menuju ke Generator Piagam.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <form id="gradesForm" action="{{ route('adminprogram.piagam.grades.store', $program->id) }}" method="POST">
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <input type="hidden" name="sort" value="{{ request('sort', 'name_asc') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm font-semibold text-slate-600">
                                <th class="p-4 border-r border-slate-200 w-12 text-center">No</th>
                                <th class="p-4 border-r border-slate-200">Nama Peserta</th>
                                @foreach($customVariables as $varName)
                                    <th class="p-4 border-r border-slate-200">{{ str_replace('_', ' ', $varName) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($participants as $index => $participant)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 border-r border-slate-100 text-center text-sm text-slate-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="p-4 border-r border-slate-100">
                                        <div class="font-medium text-slate-800">{{ $participant->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $participant->user->email }}</div>
                                    </td>
                                    @foreach($customVariables as $varName)
                                        <td class="p-4 border-r border-slate-100">
                                            @php
                                                $val = $gradesMap[$participant->id][$varName] ?? '';
                                            @endphp
                                            <input type="text" name="grades[{{ $participant->id }}][{{ $varName }}]" value="{{ $val }}" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Isi nilai...">
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($customVariables) + 2 }}" class="p-8 text-center text-slate-500">
                                        Belum ada peserta yang terdaftar di program ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(count($participants) > 0)
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="w-full sm:w-auto overflow-x-auto">
                        {{ $participants->links() }}
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Halaman Ini
                    </button>
                </div>
                @endif
            </form>
        </div>
    @endif
</div>
@endsection



