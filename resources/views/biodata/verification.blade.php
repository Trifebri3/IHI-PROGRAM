@extends('pesertabiasa.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto my-6">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold shadow-2xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-3xs">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Verifikasi Profil Akun</h2>
            <p class="text-xs text-slate-500 mt-1">Status verifikasi identitas Anda.</p>
        </div>

        {{-- CEK APAKAH SUDAH ADA DATA VERIFIKASI --}}
        @if($verification && in_array($verification->status, ['pending', 'verified']))

            {{-- TAMPILAN STATUS (BUKAN FORM) --}}
            <div class="p-6 rounded-2xl border {{ $verification->status === 'verified' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
                <h4 class="font-bold {{ $verification->status === 'verified' ? 'text-emerald-900' : 'text-amber-900' }}">
                    Status: {{ strtoupper($verification->status) }}
                </h4>
                <p class="text-xs mt-2 text-slate-600">
                    {{ $verification->status === 'verified'
                        ? 'Selamat, akun Anda sudah terverifikasi.'
                        : 'Pengajuan Anda sedang dalam peninjauan oleh tim kami. Mohon tunggu.' }}
                </p>
            </div>

        @else
            {{-- TAMPILKAN FORM JIKA BELUM ADA DATA ATAU DITOLAK (REJECTED) --}}

            @if($verification && $verification->status === 'rejected')
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold">
                    Alasan Penolakan: "{{ $verification->rejection_reason }}"
                </div>
            @endif

            <form action="{{ route('verification.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $verification->nik ?? '') }}" class="w-full p-3 border rounded-xl text-sm" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Salinan KTP</label>
                        <input type="file" name="ktp" class="w-full p-2 border rounded-xl text-xs">
                        @if($verification?->ktp_path)
                            <p class="text-[10px] text-emerald-600 mt-1">Dokumen lama tersedia</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Pas Foto</label>
                        <input type="file" name="photo" class="w-full p-2 border rounded-xl text-xs">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-slate-950 text-white font-black rounded-2xl text-sm uppercase">
                    Kirim Dokumen
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
