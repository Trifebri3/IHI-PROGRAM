<?php

use Livewire\Volt\Component;
use App\Models\ProgramStage;
use Illuminate\Support\Facades\Validator;

new class extends Component {
    // Tangkap ID Program murni berupa integer primitif
    public $program_id;
    public $stages;

    // --- STATE 1: KENDALI TAHAPAN (STAGE) ---
    public $stageId;
    public $name = '';
    public $start_date = '';
    public $end_date = '';
    public $pass_announcement = '';
    public $fail_announcement = '';
    public $isEditMode = false;

    // --- STATE 2: KENDALI FORM BUILDER INTERNAL ---
    public $activeStageForForm = null; // Menyimpan objek stage yang sedang diisi form-nya
    public $form_items = [];
    public $new_field_name = '';
    public $new_field_type = 'text';
    public $new_field_required = true;

    // --- MONITOR ENGINE BARU ---
    public $debugStatus = 'Engine Siap Menerima Data (Idle)';
    public $debugError = '';

    public function mount($program_id)
    {
        $this->program_id = $program_id;
        $this->loadStages();
    }

    public function loadStages()
    {
        $this->stages = ProgramStage::where('program_id', $this->program_id)->orderBy('sequence')->get();

        // Segarkan data form builder jika sedang membuka salah satu stage
        if ($this->activeStageForForm) {
            $refreshStage = ProgramStage::find($this->activeStageForForm->id);
            if ($refreshStage) {
                $this->activeStageForForm = $refreshStage;
                $this->form_items = $refreshStage->form_schema ?? [];
            } else {
                $this->activeStageForForm = null;
                $this->form_items = [];
            }
        }
    }

    // =========================================================================
    // CRUD TAHAPAN ENGINE
    // =========================================================================
    public function saveStage()
    {
        $this->debugStatus = 'Mencoba eksekusi tombol Simpan Tahapan...';
        $this->debugError = '';

        try {
            $validator = Validator::make([
                'name' => $this->name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ], [
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                throw new \Exception('Gagal Validasi Input: ' . implode(', ', $validator->errors()->all()));
            }

            if ($this->isEditMode) {
                $stage = ProgramStage::findOrFail($this->stageId);
                $stage->update([
                    'name' => $this->name,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'pass_announcement' => $this->pass_announcement,
                    'fail_announcement' => $this->fail_announcement,
                ]);
                $this->debugStatus = 'SUKSES UPDATE DATABASE PADA STAGE ID: ' . $stage->id;
                session()->flash('message', 'Tahapan berhasil diperbarui!');
            } else {
                $nextSequence = ProgramStage::where('program_id', $this->program_id)->count() + 1;

                $newStage = ProgramStage::create([
                    'program_id' => $this->program_id,
                    'name' => $this->name,
                    'sequence' => $nextSequence,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'form_schema' => [],
                    'pass_announcement' => $this->pass_announcement,
                    'fail_announcement' => $this->fail_announcement,
                ]);
                $this->debugStatus = 'SUKSES INSERT DATA BARU KE MYSQL DENGAN ID: ' . $newStage->id;
                session()->flash('message', 'Tahapan baru berhasil disimpan!');
            }

            $this->resetStageForm();
            $this->loadStages();

        } catch (\Exception $e) {
            $this->debugStatus = 'PROSES SIMPAN GAGAL / CRASH!';
            $this->debugError = $e->getMessage();
        }
    }

    public function editStage($id)
    {
        $stage = ProgramStage::findOrFail($id);
        $this->stageId = $stage->id;
        $this->name = $stage->name;
        $this->start_date = $stage->start_date;
        $this->end_date = $stage->end_date;
        $this->pass_announcement = $stage->pass_announcement;
        $this->fail_announcement = $stage->fail_announcement;
        $this->isEditMode = true;
        $this->debugStatus = 'Sedang mengubah data untuk Stage ID: ' . $id;
    }

    public function deleteStage($id)
    {
        ProgramStage::findOrFail($id)->delete();

        $allStages = ProgramStage::where('program_id', $this->program_id)->orderBy('sequence')->get();
        foreach ($allStages as $index => $stg) {
            $stg->update(['sequence' => $index + 1]);
        }

        $this->loadStages();
        $this->debugStatus = 'Berhasil menghapus Stage ID: ' . $id;
    }

    public function resetStageForm()
    {
        $this->reset(['stageId', 'name', 'start_date', 'end_date', 'pass_announcement', 'fail_announcement', 'isEditMode']);
    }

    // =========================================================================
    // INTERNAL FORM BUILDER ENGINE (ANTI-LAG & INSTANT SAVE)
    // =========================================================================
    public function selectStageForForm($id)
    {
        $this->activeStageForForm = ProgramStage::findOrFail($id);
        $this->form_items = $this->activeStageForForm->form_schema ?? [];
        $this->debugStatus = 'Workspace Form Terhubung ke Stage: ' . $this->activeStageForForm->name;
    }

    public function addFieldItem()
    {
        $this->validate([
            'new_field_name' => 'required|string|max:100',
            'new_field_type' => 'required|string|in:text,file'
        ]);

        $this->form_items[] = [
            'name' => trim($this->new_field_name),
            'type' => $this->new_field_type,
            'required' => (bool) $this->new_field_required
        ];

        // Langsung suntik simpan ke database MySQL detik ini juga!
        $this->activeStageForForm->update([
            'form_schema' => $this->form_items
        ]);

        $this->debugStatus = 'Berhasil menyimpan kolom form baru ke database!';
        $this->reset(['new_field_name', 'new_field_type', 'new_field_required']);
        $this->loadStages();
    }

    public function removeFieldItem($index)
    {
        unset($this->form_items[$index]);
        $this->form_items = array_values($this->form_items);

        $this->activeStageForForm->update([
            'form_schema' => $this->form_items
        ]);

        $this->debugStatus = 'Kolom form berhasil dihapus dari database.';
        $this->loadStages();
    }
}; ?>

<div class="space-y-6">
    <div class="p-4 rounded-xl text-[11px] font-mono bg-slate-950 text-slate-200 space-y-1.5 shadow-lg border border-slate-800">
        <div class="text-amber-400 font-bold tracking-wider flex items-center text-[10px]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
            BRUTAL CORE MONITOR ENGINE:
        </div>
        <div><span class="text-slate-500">Status Alur Kerja:</span> <span class="text-sky-400 font-bold">{{ $debugStatus }}</span></div>
        @if($debugError)
            <div class="text-rose-400 p-2 bg-rose-950/40 rounded-lg border border-rose-900/40 overflow-x-auto whitespace-pre-wrap font-bold"><span class="text-slate-400 block mb-0.5">Muntahan Error SQL/Validasi:</span>{{ $debugError }}</div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50 space-y-4 h-fit">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b pb-2">{{ $isEditMode ? 'Ubah Data Tahapan' : 'Buat Tahapan Baru' }}</h3>

            @if (session()->has('message'))
                <div class="p-2.5 bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-xl text-xs font-semibold">
                    ✨ {{ session('message') }}
                </div>
            @endif

            <form wire:submit.prevent="saveStage" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-500">Nama Tahapan</label>
                    <input type="text" wire:model="name" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Cth: Seleksi Administrasi" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500">Tgl Mulai</label>
                        <input type="date" wire:model="start_date" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500">Tgl Selesai</label>
                        <input type="date" wire:model="end_date" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-emerald-800">Pesan Kelolosan</label>
                    <textarea wire:model="pass_announcement" class="w-full p-2 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500" rows="2" placeholder="Selamat Anda lolos..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-rose-800">Pesan Kegagalan</label>
                    <textarea wire:model="fail_announcement" class="w-full p-2 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500" rows="2" placeholder="Mohon maaf..."></textarea>
                </div>

                <div class="flex space-x-2 pt-1">
                    @if($isEditMode)
                        <button type="button" wire:click="resetStageForm" class="flex-1 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl border">Batal</button>
                    @endif
                    <button type="submit" class="flex-grow py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-xs rounded-xl hover:from-emerald-700 shadow-md">
                        {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Tahapan' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 space-y-4">
            <h4 class="text-sm font-bold text-slate-800 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                Struktur Urutan Rangkaian Program
            </h4>

            @if($stages->isEmpty())
                <p class="text-xs text-slate-400 italic text-center py-8 bg-slate-50 rounded-xl border border-dashed">Belum ada tahapan alur yang dibentuk.</p>
            @else
                <div class="relative border-l-2 border-emerald-100 pl-6 ml-3 space-y-3">
                    @foreach($stages as $stg)
                        <div class="relative bg-slate-50/70 p-4 rounded-xl border border-slate-100 flex justify-between items-center hover:bg-slate-50 transition shadow-3xs">
                            <span class="absolute -left-9 top-4 flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-extrabold text-emerald-700 border border-emerald-200">
                                {{ $stg->sequence }}
                            </span>

                            <div>
                                <h5 class="font-bold text-slate-800 text-sm leading-tight">{{ $stg->name }}</h5>
                                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded mt-1 inline-block">
                                    ⏱️ {{ date('d M Y', strtotime($stg->start_date)) }} s/d {{ date('d M Y', strtotime($stg->end_date)) }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-1.5">
                                <button type="button" wire:click="selectStageForForm({{ $stg->id }})" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition shadow-xs">
                                    🛠️ Kelola Form ({{ count($stg->form_schema ?? []) }})
                                </button>
                                <button type="button" wire:click="editStage({{ $stg->id }})" class="p-1.5 text-slate-400 hover:text-emerald-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button type="button" wire:click="deleteStage({{ $stg->id }})" class="p-1.5 text-slate-300 hover:text-rose-600 transition" wire:confirm="Hapus tahapan ini?">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($activeStageForForm)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 transition-all duration-300 animate-fade-in">
            <div class="border-b pb-3 flex justify-between items-center mb-4">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Stage Form Builder Slot</span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1">Perakitan Form: <span class="text-emerald-700 font-extrabold">{{ $activeStageForForm->name }}</span></h4>
                </div>
                <button type="button" wire:click="$set('activeStageForForm', null)" class="text-xs text-slate-400 hover:text-slate-600 font-semibold">✕ Tutup Panel</button>
            </div>

            <div class="space-y-2 mb-4">
                <span class="block text-xs font-bold uppercase text-slate-500">Daftar Atribut Kuesioner Aktif:</span>
                @if(empty($form_items))
                    <p class="text-xs text-slate-400 italic p-4 bg-slate-50 rounded-xl border border-dashed">Belum ada kolom isian berkas untuk tahapan ini.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($form_items as $index => $item)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                                <div class="text-xs font-bold text-slate-700 flex items-center">
                                    <span class="text-emerald-600 mr-2">📎</span>
                                    <span>{{ $item['name'] }}</span>
                                    <span class="ml-2 text-[9px] font-black bg-white px-1.5 py-0.5 border rounded text-slate-400 uppercase tracking-wide">{{ $item['type'] }}</span>
                                    @if($item['required']) <span class="text-rose-500 ml-1 font-bold">* Wajib</span> @endif
                                </div>
                                <button type="button" wire:click="removeFieldItem({{ $index }})" class="text-slate-300 hover:text-rose-600 font-bold p-1">✕</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <form wire:submit.prevent="addFieldItem" class="p-4 bg-gradient-to-br from-emerald-50/20 to-white border border-emerald-100 rounded-2xl space-y-3 pt-3">
                <span class="block text-xs font-bold uppercase text-emerald-950">Pasang Atribut Form Baru</span>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Atribut Dokumen</label>
                        <input type="text" wire:model="new_field_name" placeholder="Cth: Lembar CV / Berkas Esai" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Input Tampilan</label>
                        <select wire:model="new_field_type" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs text-slate-700">
                            <option value="text">Teks Deskripsi / Isian Singkat</option>
                            <option value="file">Upload Berkas Berkas (PDF/Gambar)</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between bg-white p-2 border border-slate-200 rounded-xl h-[34px]">
                        <label class="flex items-center text-[11px] font-bold text-slate-600 cursor-pointer pl-1">
                            <input type="checkbox" wire:model="new_field_required" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-200 mr-1.5 w-3.5 h-3.5"> Wajib Diisi
                        </label>
                        <button type="submit" class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-4 py-1 rounded-lg text-xs font-extrabold shadow-sm transition">
                            + Pasang Field
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
