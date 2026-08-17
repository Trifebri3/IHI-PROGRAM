@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <div class="max-w-3xl space-y-3 text-left mb-16">
        <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
            Direktori Data Transparan
        </div>
        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
            Pilih Program untuk <span class="text-emerald-600">Melihat Data</span>
        </h2>
        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
            Silakan pilih salah satu program aktif di bawah ini untuk meninjau persebaran demografi wilayah atau memeriksa daftar nama partisipan resmi.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($programs as $index => $prog)
            <div class="bg-white rounded-2xl border-2 border-slate-100 hover:border-emerald-600 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group overflow-hidden relative">
                
                <div class="relative h-48 w-full bg-slate-100 overflow-hidden shrink-0">
                    @if($prog->banner_path)
                        <img src="{{ asset('storage/' . $prog->banner_path) }}" alt="Banner {{ $prog->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-emerald-950 flex items-center justify-center p-4">
                            <span class="text-emerald-500/10 font-black text-5xl tracking-widest select-none uppercase font-mono">
                                ACTIVE
                            </span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-linear-to-t from-slate-950/30 via-transparent to-transparent"></div>

                    <div class="absolute top-4 left-4 w-14 h-14 rounded-xl bg-white/95 backdrop-blur-xs p-1.5 shadow-md border border-white/20 flex items-center justify-center overflow-hidden transition-transform group-hover:scale-105 duration-300">
                        @if($prog->logo_path)
                            <img src="{{ asset('storage/' . $prog->logo_path) }}" alt="Logo {{ $prog->name }}" class="w-full h-full object-contain rounded-lg">
                        @else
                            <div class="w-full h-full bg-emerald-50 text-emerald-700 flex items-center justify-center font-black font-mono text-base rounded-lg uppercase">
                                {{ substr($prog->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col justify-between bg-white">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-emerald-600 transition-colors mb-2 leading-snug uppercase tracking-tight">
                            {{ $prog->name }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed line-clamp-3">
                            {{ $prog->description }}
                        </p>
                    </div>

                    <div class="space-y-2.5 pt-4 border-t border-slate-100">
                        
                        <a href="{{ route('public.program.stats', $prog->id) }}"
                           class="flex items-center justify-center gap-2 w-full text-center py-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            <span>Statistik & Persebaran</span>
                        </a>

                        <a href="{{ route('public.program.participants', $prog->id) }}"
                           class="flex items-center justify-center gap-2 w-full text-center py-3 bg-white border border-slate-200 text-slate-700 hover:border-emerald-600 hover:text-emerald-600 text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Peserta Resmi Program</span>
                        </a>
                        
                    </div>
                </div>

            </div>
        @endforeach
    </div>
</div>
@endsection