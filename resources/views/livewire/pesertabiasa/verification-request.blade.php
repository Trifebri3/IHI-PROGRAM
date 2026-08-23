<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\AccountVerification;

new class extends Component {
    use WithFileUploads;

    public $nik = '';
    public $ktp;
    public $photo;
    public $currentStatus = null;
    public $rejectionReason = '';

    public function mount()
    {
        $verification = auth()->user()->verification;
        if ($verification) {
            $this->currentStatus = $verification->status;
            $this->rejectionReason = $verification->rejection_reason;

            if($verification->status === 'rejected') {
                $this->nik = $verification->nik;
            }
        }
    }

    public function submitRequest()
    {
        $this->validate([
            'nik' => 'required|numeric|digits:16',
            'ktp' => 'required|image|max:2048',
            'photo' => 'required|image|max:2048',
        ]);

        $ktpPath = $this->ktp->store('verifications/ktp', 'public');
        $photoPath = $this->photo->store('verifications/photo', 'public');

        AccountVerification::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'nik' => $this->nik,
                'ktp_path' => $ktpPath,
                'photo_path' => $photoPath,
                'status' => 'pending',
                'rejection_reason' => null
            ]
        );

        $this->currentStatus = 'pending';
        session()->flash('success', 'Pengajuan verifikasi akun berhasil dikirim. Mohon tunggu konfirmasi dari tim verifikator.');
    }
}; ?>

<div class="max-w-4xl mx-auto my-6">
    @if(session()->has('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-2xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-3xs transition-all">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <span class="text-[10px] font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wider">Identity Service</span>
            <h2 class="text-xl font-black text-slate-800 tracking-tight mt-2.5">Verifikasi Profil Akun</h2>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Ajukan validasi identitas resmi Anda untuk memperoleh lencana keanggotaan terverifikasi serta membuka akses penuh ke program eksklusif.</p>
        </div>

        @if($currentStatus === 'verified')
            <div class="p-5 bg-gradient-to-r from-emerald-50 to-teal-50/30 border border-emerald-200 text-emerald-900 rounded-2xl flex items-start gap-4 shadow-3xs">
                <div class="p-2 bg-emerald-600 rounded-xl text-white shadow-sm flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-900">Identitas Terverifikasi Resmi</h4>
                    <p class="text-xs text-emerald-700/90 mt-1 leading-relaxed">Selamat, akun Anda telah divalidasi oleh sistem. Anda memiliki otorisasi penuh untuk berpartisipasi dalam agenda bersertifikat.</p>
                </div>
            </div>

        @elseif($currentStatus === 'pending')
            <div class="p-5 bg-amber-50/60 border border-amber-200 text-amber-900 rounded-2xl flex items-start gap-4 shadow-3xs animate-pulse">
                <div class="p-2 bg-amber-500 rounded-xl text-white shadow-sm flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Berkas Sedang Ditinjau</h4>
                    <p class="text-xs text-amber-700 mt-1 leading-relaxed">Dokumen Anda sedang diproses oleh tim verifikator. Tahapan peninjauan berkas ini memakan waktu kurang lebih 1 hingga 3 hari kerja.</p>
                </div>
            </div>

        @else
            @if($currentStatus === 'rejected')
                <div class="p-4 mb-6 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl flex items-start gap-3 shadow-3xs">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-xs">
                        <span class="font-bold text-rose-900 block">Pengajuan Sebelumnya Ditolak</span>
                        <p class="text-rose-700 mt-0.5 font-medium leading-relaxed">Alasan penolakan: <span class="italic font-bold text-rose-800">"{{ $rejectionReason }}"</span>. Mohon periksa kembali dan unggah ulang berkas dokumen yang sah.</p>
                    </div>
                </div>
            @endif

            <form wire:submit="submitRequest" class="space-y-6">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" wire:model="nik"
                           class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none font-mono placeholder:font-sans"
                           placeholder="Masukkan 16 digit angka NIK sesuai KTP">
                    @error('nik') <span class="text-[11px] font-medium text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Unggah Salinan KTP</label>
                        <div class="relative group border border-slate-200 bg-slate-50/30 rounded-2xl p-4 transition hover:bg-slate-50">
                            <input type="file" wire:model="ktp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-950 file:text-white hover:file:bg-black file:cursor-pointer cursor-pointer">

                            @if ($ktp)
                                <div class="mt-3 relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-white">
                                    <img src="{{ $ktp->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </div>
                        @error('ktp') <span class="text-[11px] font-medium text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Pas Foto Formal</label>
                        <div class="relative group border border-slate-200 bg-slate-50/30 rounded-2xl p-4 transition hover:bg-slate-50">
                            <input type="file" wire:model="photo" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-950 file:text-white hover:file:bg-black file:cursor-pointer cursor-pointer">

                            @if ($photo)
                                <div class="mt-3 relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-white">
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </div>
                        @error('photo') <span class="text-[11px] font-medium text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-4">
                    <span wire:loading wire:target="ktp, photo" class="text-[11px] text-amber-600 font-medium animate-pulse">
                        Sedang memproses pratinjau dokumen...
                    </span>
                    <div class="ml-auto">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-950 hover:bg-black text-white font-extrabold rounded-xl shadow-sm text-xs uppercase tracking-wider transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submitRequest">Kirim Dokumen Verifikasi</span>
                            <span wire:loading wire:target="submitRequest" class="animate-pulse">Mengirim Data...</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
