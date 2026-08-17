@extends('superadmin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <form action="{{ route('superadmin.programs.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border mb-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="name" placeholder="Nama Program" class="p-2 border rounded" required>
            <input type="number" name="quota" placeholder="Kuota" class="p-2 border rounded" required>
            <select name="selected_admin_id" class="p-2 border rounded" required>
                @foreach($adminPrograms as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded">Simpan Program</button>
        </div>
    </form>

    <table class="w-full bg-white border rounded">
        @foreach($programs as $program)
            <tr>
                <td class="p-3">{{ $program->name }}</td>
                <td class="p-3">
                    <form action="{{ route('superadmin.programs.destroy', $program->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
