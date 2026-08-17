<?php

use Livewire\Volt\Component;
use App\Models\Program;
use App\Models\Registration;

new class extends Component {
    public $activePrograms;
    public $userRegistrations = [];

    public function mount()
    {
        // Ambil program yang statusnya sudah dipublikasikan
        $this->activePrograms = Program::where('status', 'published')
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        // Ambil riwayat pendaftaran user saat ini agar tidak daftar ganda
        $this->userRegistrations = Registration::where('user_id', auth()->id())
            ->pluck('status', 'program_id')
            ->toArray();
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($activePrograms as $program)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col justify-between hover:border-emerald-500 transition-all duration-300">
            <div class="relative h-40 bg-slate-100">
                @if($program->banner_path)
                    <img src="{{ asset('storage/' . $program->banner_path) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-green-700 flex items-center justify-center text-white font-bold">
                        {{ $program->name }}
                    </div>
                @endif

                <div class="absolute -bottom-5 left-5 w-14 h-14 bg-white p-1 rounded-full shadow border overflow-hidden flex items-center justify-center">
                    @if($program->logo_path)
                        <img src="{{ asset('storage/' . $program->logo_path) }}" class="w-full h-full object-cover rounded-full">
                    @else
                        <div class="w-full h-full bg-emerald-600 rounded-full flex items-center justify-center text-white font-black text-lg">
                            {{ substr($program->name, 0, 1) }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-6 pt-8 flex-1 flex flex-col justify-between">
                <div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">{{ $program->name }}</h4>
                    <p class="text-sm text-slate-500 line-clamp-3 mb-4">{{ $program->description ?? 'Tidak ada deskripsi.' }}</p>

                    <div class="flex items-center space-x-4 text-xs font-semibold text-slate-600 mb-6 bg-slate-50 p-3 rounded-xl">
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase">Kuota</span>
                            <span class="text-emerald-700 font-bold">{{ $program->quota }} Peserta</span>
                        </div>
                        <div class="border-l border-slate-200 h-6"></div>
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase">Batas Pendaftaran</span>
                            <span class="text-slate-700">{{ date('d M Y', strtotime($program->end_date)) }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    @if(array_key_exists($program->id, $userRegistrations))
                        <div class="w-full py-2.5 text-center rounded-xl text-sm font-bold bg-slate-100 text-slate-500 border">
                            @if($userRegistrations[$program->id] === 'process')
                                ⏳ Sedang Mengikuti Seleksi
                            @elseif($userRegistrations[$program->id] === 'passed')
                                🎉 Anda Lulus Program
                            @else
                                ❌ Belum Lolos Seleksi
                            @endif
                        </div>
                    @else
                        <a href="{{ route('program.apply', $program->id) }}"
                           class="block w-full py-2.5 text-center bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-bold rounded-xl shadow-md shadow-emerald-100 transition-all text-sm">
                            Daftar Program Sekarang
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-2 p-12 text-center bg-white rounded-2xl border border-dashed border-slate-200 text-slate-400 italic">
            Saat ini belum ada program pendaftaran yang dibuka untuk publik.
        </div>
    @endforelse
</div>
