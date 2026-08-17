@extends('pesertabiasa.layouts.app')
@section('title', 'Form Registrasi Event')
@section('content')
<div class="py-6 max-w-2xl mx-auto px-4">

    <div class="mb-4">
        <a href="{{ route('events.catalog') }}" class="text-xs bg-white text-slate-600 px-3 py-1.5 rounded-xl border font-bold shadow-3xs hover:bg-slate-50 transition-colors">&larr; Batal</a>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="border-b pb-4 mb-6 bg-emerald-50/20 p-4 rounded-xl border-dashed border-emerald-200">
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase font-mono tracking-wider">Event Admission Form</span>
            <h3 class="text-xl font-black text-slate-800 mt-2">{{ $event->title }}</h3>
            <p class="text-xs text-slate-400 mt-1">Sila penuhi instrumen formulir kustom berikut untuk klaim tiket masuk resmi.</p>
        </div>

        <form action="{{ route('events.register.store', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- 🔥 PERBAIKAN: Looping menggunakan array mapping yang sah ($field['...']) -->
            @forelse($event->form_schema ?? [] as $index => $field)
                @php
                    $inputName = "field_" . $index;
                    $isRequired = isset($field['required']) && $field['required'];
                @endphp
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">
                        {{ $field['name'] }} @if($isRequired) <span class="text-rose-500">*</span> @endif
                    </label>

                    @if($field['type'] === 'text')
                        <input type="text" name="{{ $inputName }}" value="{{ old($inputName) }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" {{ $isRequired ? 'required' : '' }}>

                    @elseif($field['type'] === 'number')
                        <input type="number" name="{{ $inputName }}" value="{{ old($inputName) }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" {{ $isRequired ? 'required' : '' }}>

                    @elseif($field['type'] === 'file')
                        <input type="file" name="{{ $inputName }}" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold cursor-pointer" {{ $isRequired ? 'required' : '' }} accept=".pdf,.jpg,.png,.jpeg">
                        <span class="text-[10px] text-slate-400 block mt-1">Format sah: PDF, JPG, PNG (Maksimal 2MB)</span>
                    @endif

                    @error($inputName)
                        <span class="text-xs text-rose-600 font-semibold mt-1 block">⚠️ {{ $message }}</span>
                    @enderror
                </div>
            @empty
                <div class="p-6 bg-slate-50 text-slate-500 text-xs rounded-xl border border-dashed text-center font-medium">
                    Event ini tidak memerlukan isian berkas tambahan. Anda diizinkan langsung memproses klaim tiket masuk.
                </div>
            @endforelse

            <div class="pt-4 border-t flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-50 hover:from-emerald-700 transition-all">
                    🎟️ Submit & Konfirmasi Klaim Tiket
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
