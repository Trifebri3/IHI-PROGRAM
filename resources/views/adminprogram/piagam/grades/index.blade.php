@extends('adminprogram.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengisian Nilai Sertifikat</h1>
            <p class="text-slate-600 mt-1">Isi variabel kustom sertifikat untuk setiap peserta program <strong>{{ $program->name }}</strong>.</p>
        </div>
        <div class="flex flex-wrap gap-2 sm:gap-3 items-center">
            <form action="{{ route('adminprogram.piagam.grades.index', $program->id) }}" method="GET" class="flex gap-2">
                <select name="sort" onchange="this.form.submit()" class="text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 py-2">
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </form>
            <form action="{{ route('adminprogram.piagam.grades.upload_template', $program->id) }}" method="POST" enctype="multipart/form-data" class="hidden" id="uploadForm">
                @csrf
                <input type="file" name="file" id="fileInput" accept=".csv,.txt" onchange="document.getElementById('uploadForm').submit()">
            </form>
            <button type="button" onclick="document.getElementById('fileInput').click()" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-amber-600 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-amber-700 shadow-sm flex items-center gap-1.5 transition-colors" title="Upload Template CSV">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload CSV
            </button>
            <a href="{{ route('adminprogram.piagam.grades.download_template', $program->id) }}" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-sky-600 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-sky-700 shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                Download CSV
            </a>
            <button type="button" onclick="document.getElementById('modalPublicSettings').classList.remove('hidden')" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-purple-600 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-purple-700 shadow-sm flex items-center gap-1.5 transition-colors" title="Pengaturan Judul Formulir & Tampilan Publik">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Atur Tampilan Publik
            </button>
            <button type="button" onclick="document.getElementById('gradesForm').submit()" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-emerald-700 shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan
            </button>
            <a href="{{ route('adminprogram.piagam.generator.index', $program->id) }}" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 text-white text-xs sm:text-sm font-medium rounded-xl hover:bg-indigo-700 shadow-sm flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                Generator Piagam
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-700 rounded-xl border border-rose-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('error') }}</div>
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
                                    @php
                                        $customLabel = $publicSettings['variable_labels'][$varName] ?? null;
                                        $isVisible = $publicSettings['variable_visibility'][$varName] ?? true;
                                    @endphp
                                    <th class="p-4 border-r border-slate-200">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-700">{{ $customLabel ?: str_replace('_', ' ', $varName) }}</span>
                                            @if(!$isVisible)
                                                <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 rounded text-[10px] font-medium border border-rose-200" title="Disembunyikan dari Publik & Peserta">Hidden</span>
                                            @endif
                                        </div>
                                        @if($customLabel)
                                            <div class="text-[10px] text-slate-400 font-mono font-normal">[{{ $varName }}]</div>
                                        @endif
                                    </th>
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

    <!-- Modal Pengaturan Tampilan & Judul Formulir Publik -->
    <div id="modalPublicSettings" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="px-6 py-5 bg-gradient-to-r from-purple-700 to-indigo-700 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                        ⚙️
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight">Pengaturan Formulir Publik & Peserta</h3>
                        <p class="text-xs text-purple-200 mt-0.5">Atur judul dan label variabel resmi yang tampil ke publik/peserta</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalPublicSettings').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors text-2xl leading-none">&times;</button>
            </div>
            
            <form action="{{ route('adminprogram.piagam.grades.public_settings', $program->id) }}" method="POST" class="p-6 space-y-5">
                @csrf

                <!-- Judul Formulir Publik -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Judul Formulir / Bagian Nilai
                    </label>
                    <input type="text" name="public_title" 
                           value="{{ $publicSettings['public_title'] ?? 'Informasi Kelulusan & Nilai' }}" 
                           class="w-full text-sm border-slate-300 rounded-xl focus:ring-purple-500 focus:border-purple-500 py-2.5 px-3.5 shadow-sm"
                           placeholder="Contoh: Transkrip Capaian & Nilai / Evaluasi Belajar" required>
                    <p class="text-xs text-slate-500 mt-1">Judul ini akan menjadi kepala bagian rincian saat publik scan QR sertifikat dan di dashboard peserta.</p>
                </div>

                <!-- Opsi Tampilan Visibilitas Global -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3">
                        <input type="checkbox" id="show_public" name="show_public" value="1" 
                               {{ ($publicSettings['show_public'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500 mt-0.5 cursor-pointer">
                        <label for="show_public" class="cursor-pointer select-none">
                            <div class="text-xs font-bold text-slate-800">Tampil di Scan QR Publik</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Bisa dilihat siapapun saat verifikasi sertifikat.</div>
                        </label>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3">
                        <input type="checkbox" id="show_peserta" name="show_peserta" value="1" 
                               {{ ($publicSettings['show_peserta'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500 mt-0.5 cursor-pointer">
                        <label for="show_peserta" class="cursor-pointer select-none">
                            <div class="text-xs font-bold text-slate-800">Tampil di Akun Peserta</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Bisa dilihat oleh peserta di daftar sertifikat mereka.</div>
                        </label>
                    </div>
                </div>

                <!-- Custom Labels per Variable -->
                @if(count($customVariables) > 0)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Nama Label Resmi (Proper) per Variabel
                        </label>
                        <span class="text-[11px] text-slate-400">Centang untuk tampilkan</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">Ubah singkatan kode template (misal <code>[JAM]</code> atau <code>[ATT]</code>) menjadi nama yang layak dibaca publik (misal: <em>Jam Pelatihan</em> atau <em>Kehadiran</em>).</p>
                    
                    <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                        @foreach($customVariables as $var)
                            @php
                                $currentLabel = $publicSettings['variable_labels'][$var] ?? '';
                                $isVarVisible = $publicSettings['variable_visibility'][$var] ?? true;
                            @endphp
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3">
                                <input type="checkbox" name="variable_visibility[{{ $var }}]" value="1" 
                                       {{ $isVarVisible ? 'checked' : '' }}
                                       title="Tampilkan variabel ini"
                                       class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500 cursor-pointer shrink-0">
                                <span class="px-2 py-1 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-mono font-bold shrink-0 min-w-[85px] text-center shadow-xs">
                                    [{{ $var }}]
                                </span>
                                <input type="text" name="variable_labels[{{ $var }}]" 
                                       value="{{ $currentLabel }}" 
                                       placeholder="Default: {{ ucwords(str_replace('_', ' ', strtolower($var))) }}"
                                       class="flex-1 text-xs border-slate-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-1.5 px-3 bg-white">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('modalPublicSettings').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



