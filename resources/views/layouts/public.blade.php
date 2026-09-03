<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Selamat Datang - PROGRAM INSTITUT HIJAU INDONESIA')</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('components.pwa-meta')
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col pt-16">

    <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-xs" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center focus:outline-none shrink-0">
                        <img src="{{ asset('images/logo.webp') }}" alt="Logo Institut Hijau Indonesia" class="h-11 w-auto object-contain">
                    </a>

                    <div class="hidden md:flex items-center gap-6">
                        <a href="/" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">Beranda</a>
                        <a href="{{ route('public.program.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">Lihat Program</a>
                        <a href="https://e-learning.instituthijauindonesia.or.id/speakers"
   class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">
    Narasumber & Tim
</a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ route('forum.index') }}" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Green Forum</span>
                        </a>

                        <!-- Lonceng Notifikasi di Halaman Utama -->
                        @include('components.notification-bell')

                        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs hover:shadow-md transition-all">
                            My Program
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-emerald-700 px-4 py-2 rounded-xl transition-colors">
                            Masuk
                        </a>
                        <a href="https://e-learning.instituthijauindonesia.or.id/" target="_blank" class="text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-2">
                            <span>E-Learning</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    @endauth
                </div>

                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!mobileMenuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="mobileMenuOpen" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-white border-b border-slate-100 px-4 pt-2 pb-4 space-y-2 shadow-lg"
             style="display: none;">

<a href="/" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-600 hover:bg-slate-50 hover:text-emerald-600">
    Beranda
</a>

<a href="{{ route('public.program.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-600 hover:bg-slate-50 hover:text-emerald-600">
    Lihat Program
</a>

<a href="https://e-learning.instituthijauindonesia.or.id/speakers" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-600 hover:bg-slate-50 hover:text-emerald-600">
    Narasumber & Tim
</a> 

<a href="{{ route('public.program.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-600 hover:bg-slate-50 hover:text-emerald-600">
    Alumni & Roster
</a> 

            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                        @include('components.notification-bell')
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('forum.index') }}" class="block text-center py-2.5 rounded-xl text-xs font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-200">
                            Green Forum
                        </a>
                        <a href="{{ route('dashboard') }}" class="block text-center py-2.5 rounded-xl text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                            My Program
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="block text-center py-2.5 rounded-xl text-sm font-extrabold text-slate-750 bg-slate-100 hover:bg-slate-200">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="block text-center py-2.5 rounded-xl text-sm font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                            Daftar
                        </a>
                    </div>
                    <a href="https://e-learning.instituthijauindonesia.or.id/" target="_blank" class="block text-center py-2.5 rounded-xl text-sm font-extrabold text-white bg-amber-500 hover:bg-amber-600 shadow-sm">
                        Akses E-Learning 🎓
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="grow">
        @yield('content')
    </main>

    <footer class="bg-slate-950 text-slate-400 pt-16 pb-8 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-900">

                <div class="md:col-span-4 space-y-5">
    <img
        src="{{ asset('images/logoihi1.png') }}"
        alt="Logo Institut Hijau Indonesia"
        class="h-16 w-auto object-contain"
    >

    <div>
        <h3 class="text-xl font-bold text-white leading-tight">
            Program Institut Hijau Indonesia
        </h3>

        <p class="mt-3 max-w-sm text-sm leading-7 text-slate-400">
            Platform integrasi data program untuk mempermudah pendaftaran,
            verifikasi, serta manajemen edukasi lingkungan secara transparan,
            terintegrasi, dan dinamis.
        </p>
    </div>
</div>

<div class="md:col-span-5">
    <h4 class="mb-5 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-white">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        Program Kami
    </h4>

    <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">01</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Green Leadership Indonesia
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">02</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Green Youth Movement
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">03</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Green Public Interest Lawyer
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">04</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Laboratorium Keadilan Sosial & Ekologis
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">05</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Jurnal Peradaban Hijau
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">06</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                Civic Education
            </span>
        </div>

        <div class="group flex items-start gap-3">
            <span class="w-6 text-xs font-semibold text-emerald-500">07</span>
            <span class="text-slate-300 transition group-hover:text-emerald-400">
                YOU-RINGS
            </span>
        </div>

    </div>
</div>

                <div class="md:col-span-3 space-y-6">
                    <div>
                        <h4 class="text-white text-sm font-bold tracking-wider uppercase mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Kontak Kami
                        </h4>
                        <ul class="space-y-3 text-sm leading-relaxed">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <a href="mailto:instituthijau.id@gmail.com" class="hover:text-emerald-400 transition-colors break-all">instituthijau.id@gmail.com</a>
                            </li>
<li class="flex items-start gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.171 1.242 8.421 3.496 2.249 2.254 3.487 5.247 3.483 8.43-.004 6.571-5.329 11.895-11.896 11.895-2.004-.001-3.973-.505-5.717-1.464L0 24zm6.549-3.834l.366.217c1.517.9 3.459 1.376 5.426 1.378 5.673 0 10.288-4.614 10.291-10.287.002-2.748-1.066-5.332-3.008-7.276C17.738 2.554 15.158 1.484 12.42 1.484c-5.676 0-10.292 4.615-10.295 10.288-.001 2.016.528 3.992 1.531 5.739l.238.411-1.01 3.693 3.773-.99z"/>
    </svg>
    <a href="https://wa.me/6285862319524" target="_blank" class="hover:text-emerald-400 transition-colors font-medium">
        Hubungi IT Support 
        <span class="text-xs text-slate-500 block">(via WhatsApp)</span>
    </a>
</li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-slate-300">Jalan Palapa XVII Nomor 3, Jakarta Selatan.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
                
                <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs font-medium">
                    <a href="{{ route('tentang-kami') }}" target="_blank" class="hover:text-emerald-400 transition-colors">Tentang Kami</a>
                    <a href="{{ route('syarat-ketentuan') }}" class="hover:text-emerald-400 transition-colors">Syarat & Ketentuan</a>
                    <a href="{{ route('kebijakan-privasi') }}" class="hover:text-emerald-400 transition-colors">Kebijakan Privasi</a>
                    <a href="{{ route('faq') }}" class="hover:text-emerald-400 transition-colors">Bantuan / FAQ</a>
                    <a href="{{ route('kontak') }}" target="_blank" class="hover:text-emerald-400 transition-colors">Hubungi Kami</a>
                </div>

                <div class="text-center md:text-right text-xs text-slate-500 space-y-1">
                    <p>&copy; {{ date('Y') }} PROGRAM INSTITUT HIJAU INDONESIA. Hak Cipta Dilindungi.</p>
                    <p class="text-[10px] text-slate-600">Platform E-learning Resmi: <a href="https://e-learning.instituthijauindonesia.or.id/" target="_blank" class="text-emerald-600/80 hover:underline">program.instituthijauindonesia.or.id</a></p>
                </div>

            </div>

        </div>
    </footer>

</body>
</html>