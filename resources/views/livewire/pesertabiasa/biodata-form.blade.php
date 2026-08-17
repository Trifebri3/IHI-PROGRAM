<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\BiodataField;
use App\Models\UserBiodataValue;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public $fields;
    public $formData = []; // Array dinamis untuk menyimpan input user

    public function mount()
    {
        $this->fields = BiodataField::orderBy('id')->get();
        $user = auth()->user();

        // Load data jika user sudah pernah mengisi sebagian
        $existingData = UserBiodataValue::where('user_id', $user->id)->pluck('value', 'biodata_field_id')->toArray();

        foreach ($this->fields as $field) {
            $this->formData[$field->id] = $existingData[$field->id] ?? null;
        }
    }

    public function save()
    {
        // 1. Buat Rules Validasi Dinamis berdasarkan setting Super Admin
        $rules = [];
        $messages = [];

        foreach ($this->fields as $field) {
            $rule = $field->is_required ? 'required' : 'nullable';

            if ($field->type === 'file') {
                $rule .= '|max:2048'; // Max 2MB untuk file
            }

            $rules["formData.{$field->id}"] = $rule;
            $messages["formData.{$field->id}.required"] = "{$field->name} wajib diisi!";
        }

        $this->validate($rules, $messages);

        // 2. Simpan Data ke Database
        $user = auth()->user();

        foreach ($this->fields as $field) {
            $value = $this->formData[$field->id];

            // Handle khusus jika tipenya file upload
            if ($field->type === 'file' && is_object($value)) {
                $path = $value->store('biodata_files', 'public');
                $value = $path; // Simpan path-nya saja di database
            }

            // Update atau Create data (Upsert)
            UserBiodataValue::updateOrCreate(
                ['user_id' => $user->id, 'biodata_field_id' => $field->id],
                ['value' => $value]
            );
        }

        // 3. Jika sukses, arahkan ke Dashboard (sekarang pintu sudah terbuka)
        return redirect()->route('dashboard')->with('success', 'Biodata berhasil dilengkapi!');
    }
}; ?>

<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="mb-2 text-xl font-bold text-gray-800">Lengkapi Biodata Anda</h2>
    <p class="mb-6 text-sm text-red-600">Anda harus mengisi seluruh form dengan tanda bintang (*) sebelum dapat menggunakan sistem.</p>

    <form wire:submit="save">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach($fields as $field)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ $field->name }}
                        @if($field->is_required) <span class="text-red-500">*</span> @endif
                    </label>

                    {{-- Render Input Text / Number / Date --}}
                    @if(in_array($field->type, ['text', 'number', 'date']))
                        <input type="{{ $field->type }}"
                               wire:model="formData.{{ $field->id }}"
                               class="w-full p-2 mt-1 border rounded @error('formData.'.$field->id) border-red-500 @enderror">

                    {{-- Render Input Select --}}
                    @elseif($field->type === 'select')
                        <select wire:model="formData.{{ $field->id }}" class="w-full p-2 mt-1 border rounded @error('formData.'.$field->id) border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            @if($field->options)
                                @foreach($field->options as $option)
                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                @endforeach
                            @endif
                        </select>

                    {{-- Render Input File --}}
                    @elseif($field->type === 'file')
                        <input type="file"
                               wire:model="formData.{{ $field->id }}"
                               class="w-full p-2 mt-1 border rounded @error('formData.'.$field->id) border-red-500 @enderror">

                        <div wire:loading wire:target="formData.{{ $field->id }}" class="text-sm text-blue-500">Mengunggah...</div>

                        {{-- Tampilkan info jika file sudah pernah diupload sebelumnya --}}
                        @if(is_string($formData[$field->id]) && !empty($formData[$field->id]))
                            <p class="mt-1 text-xs text-green-600">File sudah tersimpan.</p>
                        @endif
                    @endif

                    {{-- Pesan Error Validasi --}}
                    @error('formData.'.$field->id)
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 font-bold text-white bg-blue-600 rounded hover:bg-blue-700">
                Simpan & Lanjutkan
            </button>
        </div>
    </form>
</div>
