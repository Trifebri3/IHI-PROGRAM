@extends('superadmin.layouts.app')

@section('title', 'Manajemen Form Biodata')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Form Biodata</h1>
            <p class="text-sm text-gray-600">Buat dan atur field data yang wajib diisi oleh peserta sebelum mereka mendaftar program.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
            &larr; Kembali
        </a>
    </div>

    <livewire:superadmin.form-builder />

@endsection
