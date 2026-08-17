<?php

use Livewire\Volt\Component;

new class extends Component {
    public $myPrograms;

    public function mount()
    {
        // KUNCI UTAMA ENTERPRISE SCOPING: Hanya ambil program milik user yang sedang login!
        $this->myPrograms = auth()->user()->managedPrograms()->with('managers')->get();
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50">
    <div class="flex items-center space-x-2 pb-4 mb-6 border-b border-slate-100">
        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Program Yang Anda Kelola</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar otoritas program kerja ekosistem di mana Anda ditunjuk sebagai direktur pelaksana.</p>
        </div>
    </div>

    @if($myPrograms->isEmpty())
        <div class="p-4 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-sm font-medium flex items-center">
            <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Anda belum ditugaskan untuk mengelola program apapun oleh Super Admin Pusat.</span>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($myPrograms as $program)
                <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-emerald-500 transition-all duration-300 flex flex-col justify-between">

                    <div class="relative h-32 bg-slate-100">
                        @if($program->banner_path)
                            <img src="{{ asset('storage/' . $program->banner_path) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-green-700"></div>
                        @endif

                        <div class="absolute top-3 right-3">
                            @if($program->status === 'published')
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500 text-white shadow-sm">PUBLISHED</span>
                            @else
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-400 text-white shadow-sm">DRAFT</span>
                            @endif
                        </div>

                        <div class="absolute -bottom-4 left-4 w-12 h-12 rounded-full bg-white p-0.5 shadow border flex items-center justify-center overflow-hidden">
                            @if($program->logo_path)
                                <img src="{{ asset('storage/' . $program->logo_path) }}" class="w-full h-full object-cover rounded-full">
                            @else
                                <div class="w-full h-full bg-emerald-600 rounded-full flex items-center justify-center text-[10px] text-white font-extrabold uppercase">
                                    {{ substr($program->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 pt-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-base text-slate-800 tracking-tight leading-snug line-clamp-1" title="{{ $program->name }}">
                                {{ $program->name }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                {{ $program->description ?? 'Tidak ada deskripsi objektif program.' }}
                            </p>

                            <div class="mt-4 grid grid-cols-2 gap-2 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100 text-[11px] font-medium text-slate-600">
                                <div class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>{{ $program->quota }} Quota Slot</span>
                                </div>
                                <div class="flex items-center justify-end font-semibold text-slate-500">
                                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ date('d M', strtotime($program->start_date)) }} - {{ date('d M Y', strtotime($program->end_date)) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-slate-50 flex justify-end">
                            <a href="{{ route('adminprogram.programs.workspace', $program->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-200 group">
                                <span>Kelola Isi Program</span>
                                <svg class="w-3.5 h-3.5 ml-1.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>
