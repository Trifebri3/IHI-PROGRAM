@extends('pesertabiasa.layouts.app')
@section('title', 'Lengkapi Biodata Wajib Program')
@section('content')
<div class="py-12 max-w-2xl mx-auto px-4">
    <div class="bg-white p-6 rounded-2xl shadow-md border border-amber-200">
        <div class="border-b pb-4 mb-6 bg-amber-50/50 p-4 rounded-xl border-dashed border-amber-200">
            <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-md uppercase font-mono">🔒 Mandatory Security Gate</span>
            <h3 class="text-xl font-extrabold text-slate-800 mt-2">Formulir Pengisian Biodata Program</h3>
            <p class="text-xs text-slate-500 mt-1">Anda diwajibkan melengkapi berkas instrumen data buatan panitia berikut sebelum diperkenankan memasuki ekosistem dashboard {{ $program->name }}.</p>
        </div>

        <form action="{{ route('programs.internal.biodata.store', $program->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @foreach($schemas as $sch)
                @php $inputName = "schema_" . $sch->id; @endphp
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">
                        {{ $sch->field_name }} @if($sch->is_required) <span class="text-rose-500">*</span> @endif
                    </label>

                    @if($sch->field_type === 'text')
                        <input type="text" name="{{ $inputName }}" class="w-full p-2.5 border rounded-xl text-sm" {{ $sch->is_required ? 'required' : '' }}>
                    @elseif($sch->field_type === 'number')
                        <input type="number" name="{{ $inputName }}" class="w-full p-2.5 border rounded-xl text-sm" {{ $sch->is_required ? 'required' : '' }}>
                    @elseif($sch->field_type === 'file')
                        <input type="file" name="{{ $inputName }}" class="w-full p-2 border rounded-xl text-xs bg-slate-50" {{ $sch->is_required ? 'required' : '' }} accept=".pdf,.jpg,.png">
                    @endif
                </div>
            @endforeach

            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md mt-4">
                Verifikasi & Unlock Dashboard Program
            </button>
        </form>
    </div>
</div>
@endsection
