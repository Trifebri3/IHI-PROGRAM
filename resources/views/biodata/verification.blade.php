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
            <h2 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-1.5">
                <span>Verifikasi Akun</span>
                @if($verification && $verification->status === 'verified')
                    <svg class="w-5 h-5 text-blue-500 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.99-3.818-3.99-.48 0-.936.09-1.354.254C14.775 2.5 13.51 1.5 12 1.5s-2.775 1-3.418 2.264a4.135 4.135 0 00-1.354-.254C5.128 3.51 3.418 5.29 3.418 7.5c0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.71 3.99 3.818 3.99.48 0 .936-.09 1.354-.254.643 1.264 1.908 2.264 3.418 2.264s2.775-1 3.418-2.264c.418.164.874.254 1.354.254 2.108 0 3.818-1.78 3.818-3.99 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.5 4l-4-4 1.41-1.41L10 13.67l6.59-6.59L18 8.5l-8 8z" />
                    </svg>
                @endif
            </h2>
            <p class="text-xs text-slate-500 mt-1">Status verifikasi identitas Anda.</p>
            <div class="mt-3.5 p-3.5 bg-blue-50/50 border border-blue-100 rounded-2xl text-[11px] text-blue-800 leading-relaxed font-semibold">
                ℹ️ <strong>Informasi Penting:</strong> Verifikasi Akun ini <strong>tidak wajib (opsional)</strong> dan tidak mempengaruhi keaktifan, partisipasi, maupun kelulusan Anda dalam program seleksi apa pun. Pengisian dokumen ini murni bertujuan untuk menandakan keaslian identitas akun Anda (KYC) serta menyematkan lencana centang biru resmi pada roster profil Anda.
            </div>
        </div>

        {{-- CEK APAKAH SUDAH ADA DATA VERIFIKASI --}}
        @if($verification && in_array($verification->status, ['pending', 'verified']))

            {{-- TAMPILAN STATUS (BUKAN FORM) --}}
            <div class="p-6 rounded-2xl border flex items-start gap-4 {{ $verification->status === 'verified' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
                <div class="text-2xl mt-0.5">
                    {{ $verification->status === 'verified' ? '✅' : '⏳' }}
                </div>
                <div>
                    <h4 class="font-extrabold uppercase tracking-wide flex items-center gap-1.5 {{ $verification->status === 'verified' ? 'text-emerald-950' : 'text-amber-950' }}">
                        <span>Status: {{ $verification->status === 'verified' ? 'TERVERIFIKASI' : 'PENDING / PROSES TINJAUAN' }}</span>
                        @if($verification->status === 'verified')
                            <svg class="w-4.5 h-4.5 text-blue-500 fill-current shrink-0 animate-pulse" viewBox="0 0 24 24">
                                <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.99-3.818-3.99-.48 0-.936.09-1.354.254C14.775 2.5 13.51 1.5 12 1.5s-2.775 1-3.418 2.264a4.135 4.135 0 00-1.354-.254C5.128 3.51 3.418 5.29 3.418 7.5c0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.71 3.99 3.818 3.99.48 0 .936-.09 1.354-.254.643 1.264 1.908 2.264 3.418 2.264s2.775-1 3.418-2.264c.418.164.874.254 1.354.254 2.108 0 3.818-1.78 3.818-3.99 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.5 4l-4-4 1.41-1.41L10 13.67l6.59-6.59L18 8.5l-8 8z" />
                            </svg>
                        @endif
                    </h4>
                    <p class="text-xs mt-1.5 text-slate-650 font-semibold leading-relaxed">
                        {{ $verification->status === 'verified'
                            ? 'Selamat! Akun Anda telah resmi terverifikasi identitas KYC dan mendapatkan lencana centang biru.'
                            : 'Berkas pengajuan verifikasi identitas Anda sedang dalam peninjauan oleh tim admin kami. Mohon ditunggu.' }}
                    </p>
                </div>
            </div>

        @else
            {{-- TAMPILKAN FORM JIKA BELUM ADA DATA ATAU DITOLAK (REJECTED) --}}

            @if($verification && $verification->status === 'rejected')
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold">
                    Alasan Penolakan: "{{ $verification->rejection_reason }}"
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold shadow-2xs">
                    <p class="font-extrabold uppercase tracking-wide mb-1.5">⚠️ Pengiriman Berkas Gagal:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('verification.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $verification->nik ?? '') }}" class="w-full p-3 border rounded-xl text-sm @error('nik') border-rose-500 bg-rose-50/10 @enderror" required>
                    @error('nik')
                        <p class="text-xs text-rose-655 mt-1 font-extrabold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Salinan KTP</label>
                        <input type="file" name="ktp" class="w-full p-2 border rounded-xl text-xs @error('ktp') border-rose-500 bg-rose-50/10 @enderror">
                        @if($verification?->ktp_path)
                            <p class="text-[10px] text-emerald-600 mt-1">Dokumen lama tersedia</p>
                        @endif
                        @error('ktp')
                            <p class="text-xs text-rose-655 mt-1 font-extrabold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Pas Foto</label>
                        <input type="file" name="photo" class="w-full p-2 border rounded-xl text-xs @error('photo') border-rose-500 bg-rose-50/10 @enderror">
                        @error('photo')
                            <p class="text-xs text-rose-655 mt-1 font-extrabold">{{ $message }}</p>
                        @enderror
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
