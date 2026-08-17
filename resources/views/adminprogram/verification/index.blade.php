@extends('superadmin.layouts.app')

@section('title', 'Pusat Verifikasi Akun Peserta')

@section('content')
<div class="py-12 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Validasi Dokumen Identitas
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Sistem Peninjauan KYC (Know Your Customer) untuk menyetujui atau menolak lencana Centang Biru peserta.
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        <livewire:adminprogram.verification-review />

    </div>
</div>
@endsection
