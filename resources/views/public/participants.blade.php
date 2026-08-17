@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    
    <div class="max-w-3xl space-y-2">
        <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
            Direktori Publik Terverifikasi
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Daftar Peserta Resmi</h1>
        <p class="text-sm sm:text-base text-slate-500">Mencakup seluruh profil anggota aktif dalam program <strong class="text-emerald-600 font-bold">{{ $program->name }}</strong>.</p>
    </div>

    <div class="bg-white p-4 rounded-2xl border-2 border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-0.5">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Saring Wilayah</span>
            <p class="text-[11px] text-slate-400">Tampilkan data berdasarkan domisili provinsi peserta.</p>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <select name="provinsi" onchange="this.form.submit()" class="w-full sm:w-64 text-xs font-medium p-3 bg-white border border-slate-200 rounded-xl focus:outline-hidden focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition-colors">
                <option value="">Semua Provinsi</option>
                @foreach($provinces as $p) 
                    <option value="{{ $p }}" {{ request('provinsi') == $p ? 'selected' : '' }}>{{ strtoupper($p) }}</option> 
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($participants as $p)
            <div class="bg-white p-5 rounded-2xl border-2 border-slate-100 hover:border-emerald-600 shadow-xs hover:shadow-md transition-all text-center flex flex-col justify-between items-center group min-h-[220px]">
                
                <div class="w-20 h-20 rounded-full overflow-hidden bg-slate-50 border border-slate-100 mb-4 shrink-0 flex items-center justify-center relative">
                    @if($p->user->profile?->profile_photo_path)
                        <img src="{{ asset('storage/'.$p->user->profile->profile_photo_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-emerald-50 font-mono font-black text-xl text-emerald-700 uppercase">
                            {{ substr($p->user->name, 0, 2) }}
                        </div>
                    @endif
                    
                    <span class="absolute bottom-0 right-0 bg-emerald-600 text-white p-1 rounded-full border-2 border-white shadow-xs">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </div>
                
                <div class="space-y-1 w-full">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-600 transition-colors line-clamp-1 uppercase tracking-tight px-1">
                        {{ $p->user->name }}
                    </h4>
                    <p class="text-[10px] text-slate-400 font-mono tracking-wider bg-slate-50 py-0.5 px-2 rounded-sm inline-block">
                        {{ $p->final_id_number ?? 'ID-PENDING' }}
                    </p>
                    @if(!empty($p->motivation))
                        <p class="text-[10px] text-slate-500 italic mt-1 line-clamp-2 px-1 text-center cursor-pointer hover:text-emerald-700 transition" 
                           data-name="{{ $p->user->name }}"
                           data-id="{{ $p->final_id_number ?? 'ID-PENDING' }}"
                           data-motivation="{{ $p->motivation }}"
                           onclick="openMotivation(this)"
                           title="Klik untuk membaca selengkapnya">
                            &ldquo;{{ $p->motivation }}&rdquo;
                        </p>
                    @endif
                </div>

                <div class="w-full pt-3 mt-3 border-t border-slate-50 text-[11px] font-bold text-emerald-700 flex items-center justify-center gap-1 uppercase tracking-tight">
                    <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">{{ $p->user->address?->kabupaten ?? 'WILAYAH N/A' }}</span>
                </div>
                
            </div>
        @empty
            <div class="col-span-full bg-white border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center text-slate-400 space-y-2">
                <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-xs font-semibold">Tidak ditemukan partisipan resmi untuk wilayah provinsi ini.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10 custom-pagination">
        {{ $participants->links() }}
    </div>
</div>

<style>
    /* Sinkronisasi warna pagination bawaan Laravel agar tetap Hijau-Putih */
    .custom-pagination nav svg {
        display: inline-block;
    }
    .custom-pagination nav p {
        font-size: 11px;
        color: #64748b;
    }
    .custom-pagination nav span[current="true"],
    .custom-pagination nav a:hover {
        background-color: #059669 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
    }
</style>

<!-- Modal Detail Motivation -->
<div id="public-motivation-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-200 space-y-4 border border-slate-100">
        <div class="flex justify-between items-start border-b pb-3">
            <div>
                <h3 id="modal-participant-name" class="text-base font-black text-slate-800 uppercase tracking-tight">Nama Peserta</h3>
                <span id="modal-participant-id" class="text-[10px] text-slate-400 font-mono tracking-wider bg-slate-50 py-0.5 px-2 rounded-sm inline-block mt-1">ID-PENDING</span>
            </div>
            <button onclick="closeMotivationModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg p-1 transition-colors select-none">&times;</button>
        </div>
        
        <div class="space-y-2">
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Harapan &amp; Motivasi Mengikuti Program</span>
            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl max-h-60 overflow-y-auto">
                <p id="modal-participant-motivation" class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap font-medium">&mdash;</p>
            </div>
        </div>

        <button onclick="closeMotivationModal()" class="w-full py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm">
            Tutup
        </button>
    </div>
</div>

<script>
    function openMotivation(element) {
        const name = element.getAttribute('data-name');
        const id = element.getAttribute('data-id');
        const motivation = element.getAttribute('data-motivation');
        
        document.getElementById('modal-participant-name').innerText = name;
        document.getElementById('modal-participant-id').innerText = id;
        document.getElementById('modal-participant-motivation').innerText = motivation;
        
        const modal = document.getElementById('public-motivation-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeMotivationModal() {
        const modal = document.getElementById('public-motivation-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal if user clicks outside of the modal dialog
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('public-motivation-modal');
        if (event.target === modal) {
            closeMotivationModal();
        }
    });
</script>
@endsection