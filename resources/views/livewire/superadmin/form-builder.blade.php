<?php

use Livewire\Volt\Component;
use App\Models\BiodataField;

new class extends Component {
    public $fields;

    public $name = '';
    public $type = 'text';
    public $is_required = true;
    public $options = ''; // Default string dari input text html

    public function mount()
    {
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->fields = BiodataField::all();
    }

    public function save()
    {
        // 1. Validasi input
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,file,select',
        ]);

        // 🔥 FIX UTAMA: Kita paksa baca value dari state component saat ini.
        // Hilangkan kondisi ribet, jika tipenya 'select' dan string $this->options tidak kosong, langsung convert!
        $formattedOptions = null;

        if (trim($this->type) === 'select') {
            if (!empty($this->options)) {
                // Pecah teks string "L,P" menjadi array ['L', 'P']
                $formattedOptions = array_map('trim', explode(',', $this->options));
            } else {
                // Kasih validasi manual jika user milih select tapi lupa ngisi opsinya
                $this->addError('options', 'Opsi pilihan dropdown tidak boleh kosong!');
                return;
            }
        }

        // 🛠️ DEBUGER LANGSUNG:
        // Kalau masih gak nyimpan, hapus tanda ulasan (//) dd di bawah ini untuk mengintip isi data sebelum masuk DB
        // dd($this->name, $this->type, $this->is_required, $formattedOptions);

        // 2. Simpan ke Database
        BiodataField::create([
            'name' => $this->name,
            'type' => $this->type,
            'is_required' => (bool) $this->is_required,
            'options' => $formattedOptions, // Laravel Model akan otomatis convert array ini ke JSON teks di DB
        ]);

        // 3. Reset Form ke Default
        $this->name = '';
        $this->type = 'text';
        $this->is_required = true;
        $this->options = '';

        $this->loadFields();
        session()->flash('message', 'Field berhasil ditambahkan beserta opsinya!');
    }

    public function delete($id)
    {
        BiodataField::find($id)?->delete();
        $this->loadFields();
    }
}; ?>

<div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100">

    <div class="flex items-center space-x-2 pb-4 mb-5 border-b border-gray-100">
        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Arsitektur Form Biodata Dinamis</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tambahkan field kustom khusus yang wajib diisi oleh peserta saat mendaftar.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-3 mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center shadow-xs">
            <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="p-5 mb-6 bg-slate-50/60 rounded-2xl border border-slate-100 space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 items-start">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nama Field / Label</label>
                <input type="text" wire:model="name" placeholder="Cth: Ukuran Almamater" class="w-full p-2.5 mt-1.5 border border-slate-200 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm shadow-xs" required>
                @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Tipe Komponen Input</label>
                <select wire:model.live="type" class="w-full p-2.5 mt-1.5 border border-slate-200 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm shadow-xs cursor-pointer">
                    <option value="text">Teks Singkat (Text Input)</option>
                    <option value="number">Angka / Nominal (Number)</option>
                    <option value="date">Kalender / Tanggal (Date)</option>
                    <option value="file">File / Dokumen (Upload PDF/Img)</option>
                    <option value="select">Dropdown Pilihan (Select Options)</option>
                </select>
                @error('type') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if($type === 'select')
            <div x-data x-transition>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Pilihan Opsi (Pisah Koma)</label>
                <input type="text" wire:model="options" placeholder="Cth: S, M, L, XL, XXL" class="w-full p-2.5 mt-1.5 border border-slate-200 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition text-sm shadow-xs">
                @error('options') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="flex items-center gap-3 md:mt-9 mt-2 p-1 bg-white md:bg-transparent rounded-xl md:border-0 border border-slate-100 px-3 md:px-0 py-2 md:py-0">
                <input type="checkbox" id="is_required" wire:model="is_required" class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 cursor-pointer">
                <label for="is_required" class="text-sm font-semibold text-slate-700 cursor-pointer select-none">Wajib Diisi oleh User</label>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 font-bold text-white bg-gradient-to-r from-emerald-600 to-green-700 rounded-xl hover:from-emerald-700 hover:to-green-800 transition shadow-md shadow-emerald-100 flex items-center space-x-1 text-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Suntik Dynamic Field</span>
            </button>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                    <th class="p-3.5">Nama Atribut Form</th>
                    <th class="p-3.5">Tipe Skema Kontrol</th>
                    <th class="p-3.5">Opsi Dropdown (Jika Ada)</th>
                    <th class="p-3.5 text-center">Sifat Validasi</th>
                    <th class="p-3.5 text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
                @forelse($fields as $field)
                <tr wire:key="field-{{ $field->id }}" class="hover:bg-slate-50/50 transition">
                    <td class="p-3.5 font-bold text-slate-800">{{ $field->name }}</td>
                    <td class="p-3.5">
                        <span class="px-2.5 py-1 text-[11px] font-bold tracking-wide rounded-md border bg-slate-100 text-slate-600 uppercase">
                            {{ $field->type }}
                        </span>
                    </td>
                    <td class="p-3.5 text-xs font-medium text-slate-500">
                        @if($field->options)
                            <div class="flex flex-wrap gap-1">
                                @foreach($field->options as $opt)
                                    <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded border border-emerald-100 font-semibold">{{ $opt }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-400 italic">- Tidak memerlukan opsi -</span>
                        @endif
                    </td>
                    <td class="p-3.5 text-center">
                        @if($field->is_required)
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-50 text-rose-700 border border-rose-100">Wajib Diisi</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-500 border border-slate-200">Opsional</span>
                        @endif
                    </td>
                    <td class="p-3.5 text-center">
                        <button wire:click="delete({{ $field->id }})" wire:confirm="Apakah Anda yakin ingin mematikan field '{{ $field->name }}' ini dari form pengisian user? Data yang telah diisi peserta pada kolom ini akan ikut terhapus." class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="Hapus Atribut">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 italic">
                        Belum ada atribut kustom biodata dinamis yang dibuat. Gunakan form di atas untuk menambahkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
