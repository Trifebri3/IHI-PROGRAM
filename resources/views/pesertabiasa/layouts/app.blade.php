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

    @include('pesertabiasa.layouts.header')

    <div class="flex h-screen pt-16 overflow-hidden">

        @include('pesertabiasa.layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col justify-between overflow-y-auto bg-slate-50/50 pb-16 md:pb-0">

            <main class="w-full p-6 lg:p-8">
                @yield('content')
            </main>

            @include('pesertabiasa.layouts.footer')
            
        </div>

    </div>

    @include('pesertabiasa.layouts.botom')

    {{-- MODAL IKLAN GLOBAL - MUNCUL DI TENGAH --}}
    @php
        $activeIklan = \App\Models\Announcement::where('is_active', true)->latest()->first();
    @endphp

    @if($activeIklan)
        <div id="iklan-modal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-300">
                @if($activeIklan->banner_path)
                    <img src="{{ asset('storage/'.$activeIklan->banner_path) }}" class="rounded-2xl w-full mb-4 shadow-sm object-cover">
                @endif
                <h2 class="text-xl font-black text-slate-800">{{ $activeIklan->title }}</h2>
                <div class="text-sm text-slate-650 mt-2 leading-relaxed">{!! $activeIklan->description !!}</div>

                <button onclick="closeIklanModal('{{ $activeIklan->id }}')"
                        class="mt-6 w-full py-3 bg-slate-950 text-white font-black uppercase tracking-wider text-xs rounded-xl hover:bg-black transition-all">
                    Mengerti & Tutup
                </button>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const iklanId = '{{ $activeIklan->id }}';
                if (localStorage.getItem('iklan_closed_' + iklanId) !== 'true') {
                    document.getElementById('iklan-modal').style.display = 'flex';
                    
                    // Track view analytics
                    fetch("{{ route('iklan.track-view', $activeIklan->id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).catch(err => console.log('Analytics tracking error:', err));
                }
            });

            function closeIklanModal(iklanId) {
                localStorage.setItem('iklan_closed_' + iklanId, 'true');
                document.getElementById('iklan-modal').remove();
            }
        </script>
    @endif

    @livewireScripts
</body>
</html>