@extends('pesertabiasa.layouts.app')

@section('title', 'Pengajuan Verifikasi Akun')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        @if (session()->has('success'))
            <div class="p-4 mb-6 text-sm text-green-800 bg-green-100 rounded-lg border border-green-200" role="alert">
                <span class="font-bold">Sukses!</span> {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
            <span>&rarr;</span>
            <span class="text-gray-800 font-semibold">Verifikasi Akun</span>
        </div>

        <livewire:pesertabiasa.verification-request />

    </div>
</div>
@endsection
