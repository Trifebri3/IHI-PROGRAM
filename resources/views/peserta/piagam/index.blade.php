@extends('pesertabiasa.layouts.app')

@section('content')
<div x-data="pesertaPiagamApp()" class="container mx-auto px-4 py-8 relative">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Sertifikat & Piagam</h1>
        <p class="text-slate-500 mt-2">Daftar sertifikat kelulusan dari program-program yang pernah Anda ikuti.</p>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

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
                @php
                    $cert = $reg->piagamCertificate;
                    $tpl = $cert ? ($cert->template ?? $reg->program->piagamTemplate) : null;
                    $rawReqs = $tpl ? ($tpl->download_requirements ?? []) : [];
                    if (is_string($rawReqs)) {
                        $rawReqs = json_decode($rawReqs, true) ?: [];
                    }
                    $hasRequirements = !empty($rawReqs);
                    $isCompletedInitially = $cert && ($cert->requirements_completed_at !== null || !$hasRequirements);
                    $fileUrl = $cert && $cert->file_path ? asset('storage/' . $cert->file_path) : '';
                    $downloadUrl = $cert ? route('peserta.piagam.download', $cert->id) : '';
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="p-6 flex-grow">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Program</span>
                            @if($cert && $cert->file_path)
                                @if($hasRequirements)
                                    <template x-if="isCertUnlocked({{ $cert->id }}, {{ $isCompletedInitially ? 'true' : 'false' }})">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Syarat Selesai
                                        </span>
                                    </template>
                                    <template x-if="!isCertUnlocked({{ $cert->id }}, {{ $isCompletedInitially ? 'true' : 'false' }})">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-full border border-amber-200">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            {{ count($rawReqs) }} Syarat Wajib
                                        </span>
                                    </template>
                                @endif
                            @endif
                        </div>

                        <h3 class="font-bold text-slate-800 text-lg leading-tight mb-4">{{ $reg->program->name ?? 'Program Tidak Diketahui' }}</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <div class="text-[10px] uppercase text-slate-400 font-bold mb-1">Status Kelulusan</div>
                                @if($reg->status === 'failed')
                                    <span class="inline-flex px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-lg border border-red-200">Tidak Lolos</span>
                                @elseif($reg->status === 'passed' || ($cert && $cert->file_path))
                                    <span class="inline-flex px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">Lolos Bersertifikat</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-200">Proses Penilaian</span>
                                @endif
                            </div>

                            @if($cert && $cert->certificate_number)
                                <div>
                                    <div class="text-[10px] uppercase text-slate-400 font-bold mb-0.5">No. Sertifikat</div>
                                    <div class="text-xs font-semibold text-slate-600 font-mono">{{ $cert->certificate_number }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 border-t border-slate-100">
                        @if($reg->status === 'failed')
                            <p class="text-xs text-red-500 text-center font-medium">Maaf, Anda dinyatakan tidak lolos dan tidak berhak mendapatkan piagam.</p>
                        @elseif($cert && $cert->file_path)
                            <div class="flex gap-2 w-full">
                                <button type="button" 
                                    @click="openAction({{ $cert->id }}, '{{ addslashes($reg->program->name) }}', '{{ $fileUrl }}', '{{ $downloadUrl }}', {{ json_encode($rawReqs) }}, {{ $isCompletedInitially ? 'true' : 'false' }}, 'preview')" 
                                    class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Pratinjau
                                </button>
                                
                                <button type="button" 
                                    @click="openAction({{ $cert->id }}, '{{ addslashes($reg->program->name) }}', '{{ $fileUrl }}', '{{ $downloadUrl }}', {{ json_encode($rawReqs) }}, {{ $isCompletedInitially ? 'true' : 'false' }}, 'download')" 
                                    class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh
                                </button>
                            </div>
                        @else
                            <p class="text-xs text-slate-400 text-center italic">Sertifikat belum diterbitkan oleh Admin.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Requirement Modal (Pop-up Syarat Sebelum Unduh) -->
    <div x-show="requirementModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-requirements-title" role="dialog" aria-modal="true" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="requirementModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="requirementModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="requirementModalOpen" x-transition class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 px-6 py-6 text-white relative">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shrink-0 shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black leading-tight" id="modal-requirements-title">Syarat Pengambilan Piagam</h3>
                                <p class="text-xs text-emerald-100 mt-1" x-text="currentProgramName"></p>
                            </div>
                        </div>
                        <button type="button" @click="requirementModalOpen = false" class="text-emerald-100 hover:text-white transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="leading-relaxed">
                            Untuk membuka tombol unduhan piagam resmi, mohon klik dan selesaikan tautan syarat di bawah ini terlebih dahulu:
                        </p>
                    </div>

                    <!-- Progress counter -->
                    <div class="flex items-center justify-between text-xs font-bold text-slate-600 px-1">
                        <span>Status Penyelesaian:</span>
                        <span class="text-emerald-600" x-text="completedCount() + ' dari ' + requirements.length + ' syarat diklik'"></span>
                    </div>

                    <!-- List Requirements -->
                    <div class="space-y-3">
                        <template x-for="(req, idx) in requirements" :key="idx">
                            <div class="p-4 rounded-2xl border transition-all flex items-center justify-between gap-3"
                                :class="req.clicked ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                                
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                        :class="req.clicked ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                                        <template x-if="req.clicked">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                        <template x-if="!req.clicked">
                                            <span class="text-xs font-black" x-text="idx + 1"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 leading-snug" x-text="req.title"></div>
                                        <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="req.url"></div>
                                    </div>
                                </div>

                                <button type="button" @click="clickRequirement(idx)" 
                                    class="shrink-0 px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs"
                                    :class="req.clicked 
                                        ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300' 
                                        : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm'">
                                    
                                    <!-- Dynamic Platform Icon -->
                                    <template x-if="req.url.includes('instagram.com')">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </template>
                                    <template x-if="req.url.includes('youtube.com') || req.url.includes('youtu.be')">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </template>
                                    <template x-if="req.url.includes('wa.me') || req.url.includes('whatsapp.com')">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </template>
                                    <template x-if="!req.url.includes('instagram.com') && !req.url.includes('youtube.com') && !req.url.includes('youtu.be') && !req.url.includes('wa.me') && !req.url.includes('whatsapp.com')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </template>

                                    <span x-text="req.clicked ? 'Sudah Diklik' : (req.button_text || 'Buka Link')"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="button" @click="requirementModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 rounded-xl hover:bg-slate-200/50 transition-colors">
                        Tutup
                    </button>
                    
                    <button type="button" 
                        @click="confirmUnlock()" 
                        :disabled="!allRequirementsClicked() || isSubmitting" 
                        class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-sm"
                        :class="allRequirementsClicked() && !isSubmitting 
                            ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-emerald-600/20' 
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60'">
                        
                        <template x-if="isSubmitting">
                            <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <template x-if="!isSubmitting">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                        </template>

                        <span x-text="allRequirementsClicked() ? (currentAction === 'preview' ? 'Buka Pratinjau Sekarang' : 'Buka Unduhan Sekarang') : 'Selesaikan Semua Syarat di Atas'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="previewOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-preview-title" role="dialog" aria-modal="true" x-cloak>
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

<script>
function pesertaPiagamApp() {
    return {
        previewOpen: false,
        previewUrl: '',
        certificateName: '',

        requirementModalOpen: false,
        currentCertId: null,
        currentProgramName: '',
        currentDownloadUrl: '',
        currentPreviewUrl: '',
        currentAction: 'download',
        requirements: [],
        isSubmitting: false,

        unlockedCerts: {},

        isCertUnlocked(certId, initialValue) {
            if (this.unlockedCerts[certId] !== undefined) {
                return this.unlockedCerts[certId];
            }
            return initialValue;
        },

        openAction(certId, programName, fileUrl, downloadUrl, reqs, isCompletedInitially, actionType) {
            const isUnlocked = this.isCertUnlocked(certId, isCompletedInitially);

            // If already completed or no requirements, proceed directly
            if (isUnlocked || !reqs || reqs.length === 0) {
                if (actionType === 'preview') {
                    this.previewUrl = fileUrl;
                    this.certificateName = programName;
                    this.previewOpen = true;
                } else {
                    window.location.href = downloadUrl;
                }
                return;
            }

            // Otherwise, open Requirement Modal
            this.currentCertId = certId;
            this.currentProgramName = programName;
            this.currentDownloadUrl = downloadUrl;
            this.currentPreviewUrl = fileUrl;
            this.currentAction = actionType;
            this.requirements = reqs.map(r => ({ ...r, clicked: false }));
            this.requirementModalOpen = true;
        },

        clickRequirement(index) {
            const req = this.requirements[index];
            if (!req) return;
            req.clicked = true;
            window.open(req.url, '_blank');
        },

        allRequirementsClicked() {
            return this.requirements.length > 0 && this.requirements.every(r => r.clicked);
        },

        completedCount() {
            return this.requirements.filter(r => r.clicked).length;
        },

        async confirmUnlock() {
            if (!this.allRequirementsClicked() || this.isSubmitting) return;

            this.isSubmitting = true;
            try {
                const response = await fetch(`{{ url('peserta/piagam') }}/${this.currentCertId}/complete-requirements`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    this.unlockedCerts[this.currentCertId] = true;
                    this.requirementModalOpen = false;

                    // Trigger the desired action automatically
                    if (this.currentAction === 'preview') {
                        this.previewUrl = this.currentPreviewUrl;
                        this.certificateName = this.currentProgramName;
                        this.previewOpen = true;
                    } else {
                        window.location.href = this.currentDownloadUrl;
                    }
                } else {
                    alert(data.message || 'Gagal memverifikasi syarat. Silakan coba lagi.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kendala saat memverifikasi syarat.');
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
