@extends('pesertabiasa.layouts.app')

@section('title', 'Lengkapi Profil')

@section('content')
    <div class="max-w-4xl mx-auto mt-10">

        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <livewire:pesertabiasa.biodata-form />

    </div>

    
@endsection
