@extends('pesertabiasa.layouts.app')
@section('content')
<div x-data="{ previewOpen: false, previewUrl: '', certificateName: '' }" class="container mx-auto px-4 py-8 relative">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Sertifikat & Piagam</h1>
        <p class="text-slate-500 mt-2">Daftar sertifikat kelulusan dari program-program yang pernah Anda ikuti.</p>
    </div>

    @if($registrations->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Belum ada riwayat program</h3>
            <p class="text-slate-500 mt-1">Anda belum mendaftar di program manapun.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($registrations as $reg)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="p-6 flex-grow">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-2">Program</div>
                        <h3 class="font-bold text-slate-800 text-lg leading-tight mb-4">{{ $reg->program->name ?? 'Program Tidak Diketahui' }}</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <div class="text-[10px] uppercase text-slate-400 font-bold mb-1">Status Kelulusan</div>
                                @if($reg->status === 'failed')
                                    <span class="inline-flex px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-lg border border-red-200">Tidak Lolos</span>
                                @elseif($reg->status === 'passed' || ($reg->piagamCertificate && $reg->piagamCertificate->file_path))
                                    <span class="inline-flex px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">Lolos Bersertifikat</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-200">Proses Penilaian</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 border-t border-slate-100">
                        @if($reg->status === 'failed')
                            <p class="text-xs text-red-500 text-center font-medium">Maaf, Anda dinyatakan tidak lolos dan tidak berhak mendapatkan piagam.</p>
                        @elseif($reg->piagamCertificate && $reg->piagamCertificate->file_path)
                            <div class="flex gap-2 w-full">
                                <button type="button" @click="previewUrl = '{{ asset('storage/' . $reg->piagamCertificate->file_path) }}'; certificateName = '{{ addslashes($reg->program->name) }}'; previewOpen = true" class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Pratinjau
                                </button>
                                <a href="{{ asset('storage/' . $reg->piagamCertificate->file_path) }}" download="Sertifikat_{{ \Illuminate\Support\Str::slug(auth()->user()->name ?? 'Peserta') }}_{{ \Illuminate\Support\Str::slug($reg->program->name) }}.pdf" class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-slate-400 text-center italic">Sertifikat belum diterbitkan oleh Admin.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Preview Modal -->
    <div x-show="previewOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="previewOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="previewOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="previewOpen" x-transition class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-5xl h-[85vh] flex flex-col">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white border-b border-slate-700">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider" x-text="'Pratinjau Sertifikat: ' + certificateName"></h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a :href="previewUrl" download class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh File
                        </a>
                        <button type="button" @click="previewOpen = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="flex-1 bg-slate-100 p-2 relative">
                    <iframe :src="previewUrl" class="w-full h-full rounded border-0 shadow-inner bg-white" title="Pratinjau PDF"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

