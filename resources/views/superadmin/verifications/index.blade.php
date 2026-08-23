@extends('superadmin.layouts.app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Persetujuan Verifikasi Akun</h2>

    @if(session('success'))
        <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-3 border-b">Peserta</th>
                    <th class="p-3 border-b">NIK</th>
                    <th class="p-3 border-b">Dokumen</th>
                    <th class="p-3 border-b">Aksi Penolakan</th>
                    <th class="p-3 border-b">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($verifications as $item)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $item->user->name }}<br><small class="text-gray-500">{{ $item->user->email }}</small></td>
                    <td class="p-3 font-mono">{{ $item->nik }}</td>
                    <td class="p-3">
                        <a href="{{ asset('storage/'.$item->ktp_path) }}" target="_blank" class="text-blue-600 underline text-xs">KTP</a> |
                        <a href="{{ asset('storage/'.$item->photo_path) }}" target="_blank" class="text-blue-600 underline text-xs">Foto</a>
                    </td>
                    <td class="p-3">
                        <form action="{{ route('superadmin.verifications.reject', $item->id) }}" method="POST" id="reject-form-{{ $item->id }}">
                            @csrf
                            <input type="text" name="rejection_reason" placeholder="Alasan..." class="w-full p-1 text-sm border rounded" required>
                        </form>
                    </td>
                    <td class="p-3 flex gap-2">
                        <form action="{{ route('superadmin.verifications.approve', $item->id) }}" method="POST">
                            @csrf
                            <button class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded">Approve</button>
                        </form>
                        <button type="submit" form="reject-form-{{ $item->id }}" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded">Reject</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $verifications->links() }}</div>
    </div>
</div>
@endsection
