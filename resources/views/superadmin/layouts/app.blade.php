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
    @include('components.pwa-meta')
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50/50 h-screen overflow-hidden" x-data="{ mobileMenuOpen: false }">

    @include('superadmin.layouts.header')

    <div class="flex h-screen pt-16 overflow-hidden">

        @include('superadmin.layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col justify-between overflow-y-auto bg-slate-50/50">

            <main class="w-full p-6 lg:p-8">
                @yield('content')
            </main>

            @include('superadmin.layouts.footer')

        </div>

    </div>

    @livewireScripts
</body>
</html>
