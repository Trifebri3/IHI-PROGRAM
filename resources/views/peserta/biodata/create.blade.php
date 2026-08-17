<form action="{{ route('biodata.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($fields as $field)
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">
                {{ $field->name }} @if($field->is_required)*@endif
            </label>

            @if($field->type === 'select')
                <select name="biodata[{{ $field->id }}]" class="w-full p-2 border rounded">
                    <option value="">-- Pilih --</option>
                    @foreach($field->options as $opt)
                        <option value="{{ $opt }}" {{ ($existingValues[$field->id] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            @elseif($field->type === 'file')
                <input type="file" name="biodata[{{ $field->id }}]" class="w-full p-2 border rounded">
                @if(isset($existingValues[$field->id]))
                    <p class="text-xs text-green-600 mt-1">File sudah ada.</p>
                @endif
            @else
                <input type="{{ $field->type }}"
                       name="biodata[{{ $field->id }}]"
                       value="{{ old('biodata.'.$field->id, $existingValues[$field->id] ?? '') }}"
                       class="w-full p-2 border rounded">
            @endif
        </div>
        @endforeach
    </div>
    <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded font-bold">Simpan</button>
</form>
