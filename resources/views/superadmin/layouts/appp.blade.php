<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PROGRAM | @yield('title', 'Pusat Ekosistem')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">

    <div class="flex flex-col min-h-screen">

        @include('superadmin.layouts.header')

        <div class="flex flex-1 w-full max-w-[100vw] overflow-hidden">
            @yield('wrapper')
        </div>

        @include('superadmin.layouts.footer')

    </div>

    @livewireScripts
</body>
</html>
