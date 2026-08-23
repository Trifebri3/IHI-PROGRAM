<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PROGRAM | @yield('title', 'Admin Desk')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50/50" x-data="{ mobileMenuOpen: false }">

    <div class="flex flex-col min-h-screen">
        @include('superadmin.layouts.header')

        <div class="flex flex-1 w-full max-w-7xl mx-auto sm:px-6 lg:px-8">

            @include('superadmin.layouts.sidebar')

            <div class="flex-1 min-w-0 flex flex-col justify-between p-6">
                <main class="w-full">
                    @yield('content')
                </main>
            </div>

        </div>
    </div>

    @livewireScripts
</body>
</html>
