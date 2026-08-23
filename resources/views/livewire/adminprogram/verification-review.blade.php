<?php

use Livewire\Volt\Component;
use App\Models\AccountVerification;

new class extends Component {
    public $lists;
    public $reason = []; // Array penampung alasan reject per baris

    public function mount()
    {
        $this->loadPending();
    }

    public function loadPending()
    {
        $this->lists = AccountVerification::with('user')->where('status', 'pending')->get();
    }

    public function approve($id)
    {
        $verify = AccountVerification::find($id);
        $verify->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now()
        ]);

        $this->loadPending();
        session()->flash('message', 'Akun peserta berhasil diverifikasi (Centang Biru aktif!).');
    }

    public function reject($id)
    {
        $this->validate([
            'reason.'.$id => 'required|string|min:5'
        ], [
            'reason.'.$id.'.required' => 'Alasan penolakan wajib diisi!'
        ]);

        $verify = AccountVerification::find($id);
        $verify->update([
            'status' => 'rejected',
            'rejection_reason' => $this->reason[$id],
            'verified_by' => auth()->id(),
            'verified_at' => now()
        ]);

        $this->loadPending();
        session()->flash('message', 'Pengajuan akun berhasil ditolak.');
    }
}; ?>

<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Persetujuan Verifikasi Akun</h2>

    @if (session()->has('message'))
        <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ session('message') }}</div>
    @endif

    @if($lists->isEmpty())
        <p class="text-gray-500">Tidak ada pengajuan verifikasi baru saat ini.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-3 border-b">Peserta</th>
                        <th class="p-3 border-b">NIK (Decrypted)</th>
                        <th class="p-3 border-b">Dokumen</th>
                        <th class="p-3 border-b" width="30%">Aksi Penolakan</th>
                        <th class="p-3 border-b">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lists as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">
                                <div class="font-bold">{{ $item->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->user->email }}</div>
                            </td>
                            <td class="p-3 font-mono text-sm text-blue-700">
                                {{ $item->nik }} </td>
                            <td class="p-3 space-x-2">
                                <a href="{{ asset('storage/'.$item->ktp_path) }}" target="_blank" class="text-xs text-blue-600 underline font-semibold">Lihat KTP</a>
                                <a href="{{ asset('storage/'.$item->photo_path) }}" target="_blank" class="text-xs text-blue-600 underline font-semibold">Lihat Foto</a>
                            </td>
                            <td class="p-3">
                                <input type="text" wire:model="reason.{{ $item->id }}" placeholder="Tulis alasan jika menolak..." class="w-full p-1 text-sm border rounded">
                                @error('reason.'.$item->id) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </td>
                            <td class="p-3 space-x-2 whitespace-nowrap">
                                <button wire:click="approve({{ $item->id }})" class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded hover:bg-green-700">
                                    Approve ✅
                                </button>
                                <button wire:click="reject({{ $item->id }})" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">
                                    Reject ❌
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
