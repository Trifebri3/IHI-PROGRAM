<?php

use Livewire\Volt\Component;
use App\Models\Program;
use App\Models\Registration;
use App\Models\ProgramStage;
use App\Models\RegistrationStageData;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Program $program;
    public $applicants;

    // State untuk penanganan Nomor Induk Manual
    public $generationMode = []; // Menampung mode ('auto' atau 'manual') per peserta
    public $manualIdInput = [];   // Menampung string input manual per peserta

    public function mount(Program $program)
    {
        $this->program = $program;
        $this->loadApplicants();
    }

    public function loadApplicants()
    {
        $this->applicants = Registration::with(['user', 'currentStage'])
            ->where('program_id', $this->program->id)
            ->get();

        // Inisialisasi state default untuk setiap peserta yang sedang diproses
        foreach ($this->applicants as $app) {
            if (!isset($this->generationMode[$app->id])) {
                $this->generationMode[$app->id] = 'auto'; // Default otomatis
                $this->manualIdInput[$app->id] = '';
            }
        }
    }

    public function evaluateStage($registrationId, $action)
    {
        // Validasi jika admin memilih mode manual tapi input kosong
        if ($action === 'pass') {
            $currentStage = ProgramStage::find(Registration::find($registrationId)->current_stage_id);
            $nextStage = ProgramStage::where('program_id', $this->program->id)
                ->where('sequence', $currentStage->sequence + 1)
                ->first();

            // Cek jika ini adalah TAHAPAN TERAKHIR (akan memicu kelulusan)
            if (!$nextStage && $this->generationMode[$registrationId] === 'manual') {
                $this->validate([
                    'manualIdInput.'.$registrationId => 'required|string|min:3|max:50|unique:registrations,final_id_number'
                ], [
                    'manualIdInput.'.$registrationId.'.required' => 'Nomor induk manual wajib diisi!',
                    'manualIdInput.'.$registrationId.'.unique' => 'Nomor induk sudah terpakai peserta lain!'
                ]);
            }
        }

        DB::transaction(function () use ($registrationId, $action) {
            // Concurrency Control: Mengunci baris data di MySQL 8 agar aman dari balapan data
            $reg = Registration::where('id', $registrationId)->lockForUpdate()->first();
            $currentStage = ProgramStage::find($reg->current_stage_id);

            // 1. Update status evaluasi di tahap aktif saat ini
            RegistrationStageData::where('registration_id', $reg->id)
                ->where('program_stage_id', $currentStage->id)
                ->update(['status' => $action === 'pass' ? 'passed' : 'failed']);

            if ($action === 'fail') {
                $reg->update(['status' => 'failed']);
            } else {
                // Cari apakah ada tahapan berjenjang berikutnya?
                $nextStage = ProgramStage::where('program_id', $this->program->id)
                    ->where('sequence', $currentStage->sequence + 1)
                    ->first();

                if ($nextStage) {
                    // Masih ada tahap selanjutnya -> Naik Kelas Tahapan
                    $reg->update(['current_stage_id' => $nextStage->id]);

                    RegistrationStageData::create([
                        'registration_id' => $reg->id,
                        'program_stage_id' => $nextStage->id,
                        'status' => 'pending'
                    ]);
                } else {
                    // TIDAK ADA TAHAP LAGI = KELULUSAN FINAL & PENERBITAN NOMOR INDUK
                    $reg->status = 'passed';

                    if (empty($reg->final_id_number)) {
                        if ($this->generationMode[$registrationId] === 'auto') {
                            // --- KENDALI OTOMATIS SYSTEM ---
                            $year = date('Y');
                            $latestIncrement = Registration::whereYear('created_at', $year)
                                ->whereNotNull('final_id_number')
                                ->count() + 1;

                            $reg->final_id_number = 'PRG' . $year . str_pad($latestIncrement, 5, '0', STR_PAD_LEFT);
                        } else {
                            // --- KENDALI MANUAL OVERRIDE ---
                            $reg->final_id_number = strtoupper(trim($this->manualIdInput[$registrationId]));
                        }
                    }
                    $reg->save();
                }
            }
        });

        $this->loadApplicants();
        session()->flash('message', 'Otoritas kelulusan dan penomoran induk sukses dieksekusi!');
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
    <div class="flex items-center space-x-2 mb-6 pb-3 border-b">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
        <h3 class="text-base font-bold text-slate-800">Reviewer Panel & Kendali Otoritas Nomor Induk</h3>
    </div>

    @if(session()->has('message'))
        <div class="p-3 mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-100">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                    <th class="p-3.5">Identitas Pengaju</th>
                    <th class="p-3.5">Tahap Aktif</th>
                    <th class="p-3.5">Status Seleksi</th>
                    <th class="p-3.5">Nomor Induk Program</th>
                    <th class="p-3.5 text-center">Metode Penerbitan & Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @foreach($applicants as $app)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-3.5">
                            <div class="font-bold text-slate-800">{{ $app->user->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $app->user->email }}</div>
                        </td>

                        <td class="p-3.5">
                            <span class="font-bold text-slate-700">{{ $app->currentStage?->name ?? 'Siklus Berakhir' }}</span>
                            <div class="text-[10px] text-slate-400 font-medium mt-0.5">Sequence Urutan: {{ $app->currentStage?->sequence ?? '-' }}</div>
                        </td>

                        <td class="p-3.5">
                            @if($app->status === 'process')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">ON PROCESS</span>
                            @elseif($app->status === 'passed')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">PASSED FINAL</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">FAILED</span>
                            @endif
                        </td>

                        <td class="p-3.5 font-mono font-bold text-emerald-800 tracking-wide">
                            @if($app->final_id_number)
                                <span class="bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">{{ $app->final_id_number }}</span>
                            @else
                                <span class="text-slate-400 font-normal italic text-xs">Belum diterbitkan</span>
                            @endif
                        </td>

                        <td class="p-3.5">
                            @if($app->status === 'process' && $app->current_stage_id)
                                @php
                                    // Cek apakah ini tahap terakhir untuk program tersebut
                                    $isLastStage = !\App\Models\ProgramStage::where('program_id', $this->program->id)
                                        ->where('sequence', $app->currentStage->sequence + 1)
                                        ->exists();
                                @endphp

                                <div class="flex flex-col space-y-2 items-center justify-center">
                                    {{-- Jika di tahap akhir, tampilkan konfigurasi penomoran --}}
                                    @if($isLastStage)
                                        <div class="flex items-center space-x-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200 w-full justify-between">
                                            <label class="flex items-center text-xs font-bold text-slate-600 cursor-pointer px-2">
                                                <input type="radio" wire:model.live="generationMode.{{ $app->id }}" value="auto" class="text-emerald-600 focus:ring-emerald-500 mr-1.5 w-3.5 h-3.5"> Otomatis
                                            </label>
                                            <label class="flex items-center text-xs font-bold text-slate-600 cursor-pointer px-2">
                                                <input type="radio" wire:model.live="generationMode.{{ $app->id }}" value="manual" class="text-emerald-600 focus:ring-emerald-500 mr-1.5 w-3.5 h-3.5"> Manual Bypass
                                            </label>
                                        </div>

                                        @if(($generationMode[$app->id] ?? 'auto') === 'manual')
                                            <div class="w-full">
                                                <input type="text" wire:model="manualIdInput.{{ $app->id }}" placeholder="Ketik NIM Kustom (Cth: MHS-001)" class="w-full p-2 text-xs border border-amber-300 rounded-xl focus:ring-1 focus:ring-amber-500 bg-amber-50/20 font-mono font-bold tracking-wider">
                                                @error('manualIdInput.'.$app->id) <span class="text-[10px] text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Tombol Eksekutor --}}
                                    <div class="flex space-x-2 w-full justify-center pt-1">
                                        <button wire:click="evaluateStage({{ $app->id }}, 'pass')" class="flex-1 bg-gradient-to-r from-emerald-600 to-green-700 text-white px-3 py-1.5 text-xs font-bold rounded-xl hover:from-emerald-700 hover:to-green-800 transition shadow-sm">
                                            Loloskan {{ $isLastStage ? '& Lulus' : '' }} 👍
                                        </button>
                                        <button wire:click="evaluateStage({{ $app->id }}, 'fail')" wire:confirm="Gagalkan peserta ini dari sisa rangkaian program?" class="bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-1.5 text-xs font-bold rounded-xl transition border border-rose-200">
                                            Gagalkan 👎
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-xs text-slate-400 font-medium italic py-2">
                                    Siklus Operasional Selesai
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
