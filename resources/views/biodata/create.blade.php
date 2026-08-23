@extends('pesertabiasa.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto my-6">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-3xs animate-fade-in">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-3xs">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <span class="text-[10px] font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Formulir Peserta</span>
            <h2 class="text-xl font-black text-slate-800 tracking-tight mt-2.5">Lengkapi Biodata</h2>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Mohon lengkapi seluruh komponen data di bawah ini dengan informasi yang valid untuk keperluan verifikasi berkas pendaftaran.</p>
        </div>

        <form action="{{ route('biodata.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                @foreach($fields as $field)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            {{ $field->name }}
                            @if($field->is_required)
                                <span class="text-rose-500 ml-0.5" title="Wajib diisi">*</span>
                            @endif
                        </label>

                        @if($field->type === 'select')
                            <select name="biodata[{{ $field->id }}]"
                                    class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                                <option value="">-- Pilih Sesuai Opsi --</option>

                                @php
                                    // Antisipasi jika field->options bertipe array (Eloquent Cast) atau raw string teks biasa
                                    $computedOptions = is_array($field->options) ? $field->options : explode(',', $field->options);
                                @endphp

                                @foreach($computedOptions as $opt)
                                    @php $trimmedOpt = trim($opt); @endphp
                                    <option value="{{ $trimmedOpt }}" {{ old('biodata.'.$field->id, $existingValues[$field->id] ?? '') == $trimmedOpt ? 'selected' : '' }}>
                                        {{ $trimmedOpt }}
                                    </option>
                                @endforeach
                            </select>

                        @elseif($field->type === 'file')
                            <div class="relative group border border-slate-200 bg-slate-50/30 rounded-xl p-3 transition hover:bg-slate-50">
                                <input type="file" name="biodata[{{ $field->id }}]"
                                       class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-950 file:text-white hover:file:bg-black transition-all cursor-pointer">

                                @if(isset($existingValues[$field->id]) && $existingValues[$field->id])
                                    <div class="mt-2.5 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50/60 border border-emerald-100 rounded-lg p-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div class="flex items-center gap-1">
                                            <span>Arsip dokumen lama aman disimpan.</span>
                                            <a href="{{ asset('storage/' . $existingValues[$field->id]) }}" target="_blank" class="underline hover:text-emerald-900 font-bold">Unduh Lihat &rarr;</a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @else
                            <input type="{{ $field->type }}"
                                   name="biodata[{{ $field->id }}]"
                                   value="{{ old('biodata.'.$field->id, $existingValues[$field->id] ?? '') }}"
                                   class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none {{ $field->type === 'date' ? 'font-mono' : '' }}"
                                   placeholder="Masukkan data {{ strtolower($field->name) }}...">
                        @endif

                        @error('biodata.'.$field->id)
                            <span class="text-[11px] font-medium text-rose-600 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-slate-950 hover:bg-black text-white font-extrabold rounded-xl shadow-sm text-xs uppercase tracking-wider transition-colors">
                    Simpan Perubahan Biodata
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
