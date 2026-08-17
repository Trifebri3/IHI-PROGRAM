<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Program;
use App\Models\ProgramStage;
use App\Models\Registration;
use App\Models\RegistrationStageData;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithFileUploads;

    public Program $program;
    public $firstStage;
    public $formSchema = [];
    public $inputData = []; // Menyimpan jawaban form dinamis peserta

    public function mount(Program $program)
    {
        $this->program = $program;

        // Cari tahapan urutan pertama (sequence = 1) untuk pendaftaran awal
        $this->firstStage = ProgramStage::where('program_id', $this->program->id)
            ->where('sequence', 1)
            ->firstOrFail();

        $this->formSchema = $this->firstStage->form_schema ?? [];

        // Inisialisasi array input berdasarkan schema
        foreach ($this->formSchema as $index => $field) {
            $this->inputData[$index] = null;
        }
    }

    public function submitApplication()
    {
        // 1. Validasi Dinamis sesuai setting Admin Program
        $rules = [];
        $messages = [];

        foreach ($this->formSchema as $index => $field) {
            $rule = $field['required'] ? 'required' : 'nullable';
            if ($field['type'] === 'file') {
                $rule .= '|file|mimes:pdf,jpg,png|max:3072'; // Max 3MB
            } else {
                $rule .= '|string|max:1000';
            }
            $rules["inputData.{$index}"] = $rule;
            $messages["inputData.{$index}.required"] = "Dokumen/Isian '{$field['name']}' wajib dipenuhi!";
        }

        $this->validate($rules, $messages);

        // 2. Transaksi Database Aman
        DB::transaction(function() {
            // Upload berkas jika ada tipe file
            $processedValues = [];
            foreach ($this->formSchema as $index => $field) {
                $value = $this->inputData[$index];

                if ($field['type'] === 'file' && is_object($value)) {
                    $value = $value->store('program_submissions', 'public');
                }

                $processedValues[] = [
                    'field_name' => $field['name'],
                    'type' => $field['type'],
                    'value' => $value
                ];
            }

            // Buat Data Induk Registrasi
            $registration = Registration::create([
                'user_id' => auth()->id(),
                'program_id' => $this->program->id,
                'current_stage_id' => $this->firstStage->id,
                'status' => 'process'
            ]);

            // Buat Detail Data Isian Khusus Tahap 1
            RegistrationStageData::create([
                'registration_id' => $registration->id,
                'program_stage_id' => $this->firstStage->id,
                'form_values' => $processedValues,
                'status' => 'pending'
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Anda berhasil dikirim ke panitia pelaksana!');
    }
}; ?>

<div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
    <div class="border-b pb-4 mb-6">
        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase">Formulir Pendaftaran Tahap Awal</span>
        <h3 class="text-xl font-bold text-slate-800 mt-2">{{ $program->name }}</h3>
        <p class="text-xs text-slate-400 mt-1">Tahapan Aktif: <span class="font-bold text-slate-700">{{ $firstStage->name }}</span></p>
    </div>

    <form wire:submit="submitApplication" class="space-y-5">
        @forelse($formSchema as $index => $field)
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                    {{ $field['name'] }}
                    @if($field['required']) <span class="text-rose-500">*</span> @endif
                </label>

                @if($field['type'] === 'text')
                    <input type="text" wire:model="inputData.{{ $index }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" placeholder="Tuliskan isian data disini...">
                @viewelseif($field['type'] === 'file')
                    <input type="file" wire:model="inputData.{{ $index }}" class="w-full p-2 border border-slate-200 rounded-xl text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                    <span class="text-[10px] text-slate-400 block mt-1">Format yang diizinkan: PDF, JPG, PNG (Maks 3MB)</span>
                @endif

                @error("inputData.{$index}")
                    <span class="text-xs text-rose-500 mt-1 block font-medium">⚠️ {{ $message }}</span>
                @enderror
            </div>
        @empty
            <div class="p-4 bg-slate-50 text-slate-500 text-sm rounded-xl border border-dashed text-center">
                Tahap pendaftaran awal program ini tidak memerlukan dokumen unggahan tambahan. Anda bisa langsung menekan tombol submit di bawah.
            </div>
        @endforelse

        <div wire:loading wire:target="submitApplication" class="text-xs text-emerald-600 font-semibold animate-pulse">
            Mengunci data pendaftaran dan mengunggah berkas aman...
        </div>

        <div class="pt-4 border-t flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 text-white font-bold rounded-xl shadow-md shadow-emerald-100 transition-all text-sm">
                Kirim Pendaftaran Berkas
            </button>
        </div>
    </form>
</div>
