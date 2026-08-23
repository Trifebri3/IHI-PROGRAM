<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\ProgramStage;

new class extends Component {
    // Amankan state menggunakan primitive ID data murni
    public $stage_id = null;
    public $stage_name = '';
    public $form_items = [];

    // Fields Builder Input State
    public $new_field_name = '';
    public $new_field_type = 'text';
    public $new_field_required = true;

    public $debugStatus = 'Menunggu Pemilihan Stage...';
    public $debugError = '';

    #[On('stageSelected')]
    public function handleStageSelected($stageId)
    {
        $stage = ProgramStage::findOrFail($stageId);
        $this->stage_id = $stage->id;
        $this->stage_name = $stage->name;
        $this->form_items = $stage->form_schema ?? [];
        $this->debugStatus = 'Terhubung Terisolasi pada Stage ID: ' . $this->stage_id;
        $this->debugError = '';
    }

    #[On('stage-deleted')]
    public function handleStageDeleted()
    {
        $this->reset(['stage_id', 'stage_name', 'form_items']);
        $this->debugStatus = 'Stage terhapus dari panel seberang.';
    }

    public function addFieldItem()
    {
        $this->validate([
            'new_field_name' => 'required|string|max:100',
            'new_field_type' => 'required|string|in:text,file'
        ]);

        $this->debugStatus = 'Memproses suntikan JSON array baru...';

        $this->form_items[] = [
            'name' => trim($this->new_field_name),
            'type' => $this->new_field_type,
            'required' => (bool) $this->new_field_required
        ];

        try {
            // Ambil data segar langsung saat query eksekusi agar tidak bentrok hydration
            $stage = ProgramStage::findOrFail($this->stage_id);
            $stage->update([
                'form_schema' => $this->form_items
            ]);

            $this->debugStatus = 'BERHASIL MENYUNTIKKAN DATA BARU KE DATABASE!';
            $this->reset(['new_field_name', 'new_field_type', 'new_field_required']);

            // Kirim sinyal ke komponen atas untuk memperbarui counter jumlah form
            $this->dispatch('stageFormUpdated');

        } catch (\Exception $e) {
            $this->debugStatus = 'CRASH SQL JSON DATA!';
            $this->debugError = $e->getMessage();
        }
    }

    public function removeFieldItem($index)
    {
        unset($this->form_items[$index]);
        $this->form_items = array_values($this->form_items);

        try {
            $stage = ProgramStage::findOrFail($this->stage_id);
            $stage->update([
                'form_schema' => $this->form_items
            ]);
            $this->debugStatus = 'Field sukses dibersihkan dari database.';
            $this->dispatch('stageFormUpdated');
        } catch (\Exception $e) {
            $this->debugError = $e->getMessage();
        }
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mt-6 transition-all duration-300">
    @if(!$stage_id)
        <div class="p-8 text-center text-slate-400 italic bg-slate-50 rounded-2xl border border-dashed text-xs">
            💡 Silakan klik tombol "🛠️ Kelola Form" pada salah satu daftar urutan tahapan di atas untuk mulai merakit isi kuesioner berkas.
        </div>
    @else
        <div class="space-y-4">
            <div class="border-b pb-3 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase font-mono">Stage Form Builder Workspace</span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1">Form Desainer: <span class="text-emerald-700">{{ $stage_name }}</span></h4>
                </div>
                <button wire:click="$set('stage_id', null)" class="text-xs text-slate-400 hover:text-slate-600 font-semibold">✕ Sembunyikan Panel</button>
            </div>

            <div class="p-3 rounded-xl text-[11px] font-mono bg-slate-950 text-slate-200 space-y-1 shadow-lg border border-slate-800">
                <div class="text-emerald-400 font-bold tracking-wider text-[10px]">⚙️ FORM BUILDER MONITOR:</div>
                <div><span class="text-slate-400">Status DB:</span> <span class="text-sky-400 font-bold">{{ $debugStatus }}</span></div>
                @if($debugError)
                    <div class="text-rose-400 p-2 bg-rose-950/40 rounded-lg border border-rose-900/40 overflow-x-auto whitespace-pre-wrap">{{ $debugError }}</div>
                @endif
            </div>

            <div class="space-y-2">
                <span class="block text-xs font-bold uppercase text-slate-500">Atribut Formulir Pengisian Aktif:</span>
                @if(empty($form_items))
                    <p class="text-xs text-slate-400 italic p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">Tahap ini belum memiliki komponen formulir berkas.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($form_items as $index => $item)
                            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100 flex justify-between items-center shadow-3xs">
                                <div class="text-xs font-bold text-slate-700 flex items-center">
                                    <span class="text-emerald-600 mr-2">📎</span>
                                    <span>{{ $item['name'] }}</span>
                                    <span class="ml-2 text-[9px] font-black bg-white px-1.5 py-0.5 border rounded text-slate-400 uppercase tracking-wide">{{ $item['type'] }}</span>
                                    @if($item['required']) <span class="text-rose-500 ml-1 font-bold">* Required</span> @endif
                                </div>
                                <button type="button" wire:click="removeFieldItem({{ $index }})" class="text-slate-300 hover:text-rose-600 font-bold text-xs p-1">✕</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <form wire:submit.prevent="addFieldItem" class="p-4 bg-gradient-to-br from-emerald-50/30 to-white border border-emerald-100 rounded-2xl space-y-3 pt-3">
                <span class="block text-xs font-bold uppercase text-emerald-950">Pasang Atribut Input Komponen Baru</span>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Bidang Dokumen</label>
                        <input type="text" wire:model="new_field_name" placeholder="Cth: Dokumen Portofolio" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500" required>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Tampilan</label>
                        <select wire:model="new_field_type" class="w-full p-2 border border-slate-200 bg-white rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-700">
                            <option value="text">Input Deskripsi Teks</option>
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
