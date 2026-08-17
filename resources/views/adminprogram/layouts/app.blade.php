<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Program | @yield('title', 'Manajemen Portal')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('components.pwa-meta')
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 h-full overflow-hidden"
      x-data="{ mobileSidebarOpen: false }">

    @include('adminprogram.layouts.header')

    <div class="flex h-screen pt-16 overflow-hidden">

        @include('adminprogram.layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col justify-between overflow-y-auto bg-slate-50">

            <main class="w-full p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            @include('adminprogram.layouts.footer')

        </div>

    </div>

    @livewireScripts
</body>
</html>
