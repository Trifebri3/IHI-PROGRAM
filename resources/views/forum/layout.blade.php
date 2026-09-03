<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Green Forum | Institut Hijau Indonesia</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('components.pwa-meta')

    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-[#fbfbfb] min-h-full flex flex-col selection:bg-emerald-600 selection:text-white"
      x-data="{
          createModalOpen: false,
          searchModalOpen: false,
          searchQuery: '',
          activeTab: 'all',
          profileMenuOpen: false
      }">

    @php
        $authUser = auth()->user();
    @endphp

    <!-- Toast Notification (Floating Clean Toast dengan SVG Icon) -->
    <div id="thread-toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 pointer-events-none opacity-0 transition-all duration-300 transform -translate-y-2">
        <div class="bg-slate-950/95 backdrop-blur-md text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-2xl flex items-center gap-2 border border-slate-800">
            <span id="toast-icon-container" class="text-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </span>
            <span id="toast-message">Tautan disalin ke papan klip!</span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. TOP NAVIGATION BAR (Green Forum - Branding Resmi IHI) -->
    <!-- ========================================================================= -->
    <header class="sticky top-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_1px_4px_rgba(0,0,0,0.03)] transition-all">
        <div class="max-w-4xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">

            <!-- Sisi Kiri: Branding Green Forum + Logo IHI Resmi -->
            <div class="flex items-center gap-3">
                <a href="{{ route('forum.index') }}" class="flex items-center gap-2.5 group focus:outline-none" title="Green Forum - Institut Hijau Indonesia">
                    <img src="{{ asset('images/logo.webp') }}"
                         alt="Logo Institut Hijau Indonesia"
                         class="h-9 sm:h-10 w-auto object-contain transition-transform duration-200 group-hover:scale-105"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none;" class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-black text-xs items-center justify-center">
                        IHI
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm sm:text-base font-black tracking-tight text-slate-900 block leading-none">Green Forum</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 block mt-0.5 tracking-wide">Institut Hijau Indonesia</span>
                    </div>
                </a>

                <!-- Pill Tombol Kembali ke My Program / Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-600 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-transparent transition-all shadow-3xs active:scale-95 ml-1"
                   title="Kembali ke My Program">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    <span class="hidden sm:inline">My Program</span>
                    <span class="sm:hidden">My Program</span>
                </a>
            </div>

            <!-- Bagian Tengah: Navigasi Ikon Sosial Media (Desktop - SVG Murni Tanpa Emot) -->
            <nav class="hidden sm:flex items-center justify-center gap-1 sm:gap-2">

                <!-- 1. Home / Feed -->
                <button type="button"
                        @click="activeTab = 'all'; filterThreads('all')"
                        class="p-2.5 sm:px-4 sm:py-2 rounded-2xl transition-all duration-150 flex items-center gap-2"
                        :class="activeTab === 'all' ? 'bg-emerald-50 text-emerald-800 font-black shadow-3xs' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'"
                        title="Semua Diskusi">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                        <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
                    </svg>
                </button>

                <!-- 2. Search / Cari -->
                <button type="button"
                        @click="searchModalOpen = true; $nextTick(() => $refs.searchInput.focus())"
                        class="p-2.5 sm:px-4 sm:py-2 rounded-2xl text-slate-400 hover:text-slate-800 hover:bg-slate-50 transition-all duration-150 flex items-center gap-2"
                        title="Pencarian Diskusi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </button>

                <!-- 3. Buat Thread Baru (Tombol Plus Tengah) -->
                <button type="button"
                        @click="createModalOpen = true"
                        class="p-2.5 sm:px-4 sm:py-2 rounded-2xl text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-150 flex items-center gap-2"
                        title="Buat Topik Diskusi Baru">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </button>

                <!-- 4. Diskusi Saya -->
                <button type="button"
                        @click="activeTab = 'mine'; filterThreads('mine')"
                        class="p-2.5 sm:px-4 sm:py-2 rounded-2xl transition-all duration-150 flex items-center gap-2"
                        :class="activeTab === 'mine' ? 'bg-emerald-50 text-emerald-800 font-black shadow-3xs' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'"
                        title="Diskusi Saya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>

                <!-- 5. Favorit Saya -->
                <button type="button"
                        @click="activeTab = 'favorites'; filterThreads('favorites')"
                        class="p-2.5 sm:px-4 sm:py-2 rounded-2xl transition-all duration-150 flex items-center gap-2"
                        :class="activeTab === 'favorites' ? 'bg-emerald-50 text-emerald-800 font-black shadow-3xs' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'"
                        title="Favorit Saya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                    </svg>
                </button>

            </nav>

            <!-- Sisi Kanan: Notifikasi & Foto Profil Pengguna -->
            <div class="flex items-center gap-1.5">
                <!-- Lonceng Notifikasi Green Forum -->
                @include('components.notification-bell')

                <!-- Foto Profil & Dropdown Pengguna -->
                <div class="relative" x-data="{ userMenu: false }">
                <button type="button"
                        @click="userMenu = !userMenu"
                        class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition focus:outline-none"
                        title="Menu Akun">
                    <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 border border-slate-200 ring-2 ring-white shadow-xs relative">
                        @if($authUser->profile?->profile_photo_path)
                            <img src="{{ asset('storage/' . $authUser->profile->profile_photo_path) }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none;" class="w-full h-full bg-emerald-700 text-white font-black text-xs items-center justify-center">
                                {{ strtoupper(substr($authUser->name, 0, 2)) }}
                            </div>
                        @elseif($authUser->avatar)
                            <img src="{{ $authUser->avatar }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-emerald-700 flex items-center justify-center font-black text-white text-xs">
                                {{ strtoupper(substr($authUser->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                </button>

                <!-- Dropdown Menu (Menggunakan Ikon SVG Murni) -->
                <div x-show="userMenu"
                     @click.away="userMenu = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute right-0 mt-2 w-56 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/80 p-2 z-50 text-xs"
                     style="display: none;">

                    <div class="px-3 py-2 border-b border-slate-100">
                        <div class="font-extrabold text-slate-900 truncate">{{ $authUser->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $authUser->email }}</div>
                    </div>

                    <div class="py-1">
                        <a href="{{ route('identitas.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 font-bold transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <span>Profil & Keamanan</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-bold transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                            <span>Kembali ke My Program</span>
                        </a>
                    </div>

                    <div class="pt-1 border-t border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-left transition">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                                <span>Keluar Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            </div>

        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. KONTEN UTAMA HALAMAN FORUM -->
    <!-- ========================================================================= -->
    <main class="flex-1 w-full max-w-xl mx-auto px-3 sm:px-4 pt-6 pb-24 sm:pb-12">
        @yield('content')
    </main>

    <!-- ========================================================================= -->
    <!-- 3. MOBILE BOTTOM NAVIGATION (Bilah Ponsel Bawah - SVG Murni Tanpa Emot) -->
    <!-- ========================================================================= -->
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/80 h-14 flex items-center justify-around px-4 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] pb-safe">

        <!-- 1. Home Feed -->
        <button type="button"
                @click="activeTab = 'all'; filterThreads('all'); window.scrollTo({top: 0, behavior: 'smooth'})"
                class="p-2 transition active:scale-90"
                :class="activeTab === 'all' ? 'text-emerald-700 font-bold' : 'text-slate-400'"
                title="Semua Diskusi">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
            </svg>
        </button>

        <!-- 2. Search -->
        <button type="button"
                @click="searchModalOpen = true; $nextTick(() => $refs.searchInput.focus())"
                class="p-2 text-slate-400 hover:text-slate-800 transition active:scale-90"
                title="Cari Diskusi">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
        </button>

        <!-- 3. Center Floating Action Button (Buat Diskusi Baru) -->
        <button type="button"
                @click="createModalOpen = true"
                class="w-10 h-10 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-md active:scale-90 transition-all"
                title="Buat Topik Baru">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </button>

        <!-- 4. Diskusi Saya -->
        <button type="button"
                @click="activeTab = 'mine'; filterThreads('mine')"
                class="p-2 transition active:scale-90"
                :class="activeTab === 'mine' ? 'text-emerald-700 font-bold' : 'text-slate-400'"
                title="Diskusi Saya">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>

        <!-- 5. Favorit Saya -->
        <button type="button"
                @click="activeTab = 'favorites'; filterThreads('favorites')"
                class="p-2 transition active:scale-90"
                :class="activeTab === 'favorites' ? 'text-emerald-700 font-bold' : 'text-slate-400'"
                title="Favorit Saya">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
            </svg>
        </button>

        <!-- 6. Kembali ke My Program -->
        <a href="{{ route('dashboard') }}" class="p-2 text-slate-400 hover:text-emerald-700 transition active:scale-90" title="Kembali ke My Program">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
        </a>

    </nav>

    <!-- ========================================================================= -->
    <!-- 4. MODAL POP-UP: BUAT TOPIK BARU (SVG Murni) -->
    <!-- ========================================================================= -->
    <div x-show="createModalOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @keydown.escape.window="createModalOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-[28px] max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 animate-in zoom-in-95 duration-200"
             @click.away="createModalOpen = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <button type="button" @click="createModalOpen = false" class="text-xs font-bold text-slate-500 hover:text-slate-900 transition">
                    Batal
                </button>
                <span class="text-xs font-black uppercase tracking-wider text-slate-900">Diskusi Baru</span>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form action="{{ route('forum.discussion.store') }}" method="POST">
                @csrf
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 mt-0.5">
                        @if($authUser->profile?->profile_photo_path)
                            <img src="{{ asset('storage/' . $authUser->profile->profile_photo_path) }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none;" class="w-full h-full bg-emerald-700 text-white font-black text-xs items-center justify-center">
                                {{ strtoupper(substr($authUser->name, 0, 2)) }}
                            </div>
                        @elseif($authUser->avatar)
                            <img src="{{ $authUser->avatar }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-emerald-700 flex items-center justify-center font-black text-white text-xs">
                                {{ strtoupper(substr($authUser->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 space-y-2">
                        <div class="font-extrabold text-xs text-slate-900">{{ $authUser->name }}</div>
                        <input type="text"
                               name="title"
                               placeholder="Topik pembahasan..."
                               class="w-full font-bold text-sm text-slate-900 placeholder-slate-400 border-0 p-0 focus:ring-0 outline-none"
                               required>
                        <textarea name="content"
                                  rows="4"
                                  placeholder="Mulai sebuah topik diskusi..."
                                  class="w-full text-xs sm:text-sm text-slate-700 placeholder-slate-400 border-0 p-0 focus:ring-0 outline-none resize-none leading-relaxed"
                                  required></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-4 mt-2 border-t border-slate-100">
                    <span class="text-[11px] text-slate-400 font-medium">Siapa pun dapat melihat & membalas</span>
                    <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs px-5 py-2.5 rounded-full transition shadow-xs flex items-center gap-1.5">
                        <span>Posting Diskusi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. MODAL POP-UP: PENCARIAN DISKUSI (SVG Murni) -->
    <!-- ========================================================================= -->
    <div x-show="searchModalOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4 bg-black/50 backdrop-blur-sm"
         @keydown.escape.window="searchModalOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-[26px] max-w-lg w-full p-4 shadow-2xl border border-slate-200"
             @click.away="searchModalOpen = false">

            <div class="relative flex items-center">
                <span class="absolute left-3.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </span>
                <input type="text"
                       x-ref="searchInput"
                       x-model="searchQuery"
                       @input="liveSearchThreads(searchQuery)"
                       placeholder="Cari topik, kata kunci, atau nama penulis..."
                       class="w-full bg-slate-100/90 focus:bg-white pl-10 pr-10 py-2.5 rounded-2xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 outline-none border border-transparent focus:border-emerald-500 transition">
                <button type="button"
                        @click="searchModalOpen = false"
                        class="absolute right-3 p-1 rounded-full text-slate-400 hover:text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-3 text-[11px] text-slate-400 px-1 flex items-center justify-between">
                <span>Ketik untuk memfilter feed secara instan</span>
                <button type="button" @click="searchQuery = ''; liveSearchThreads(''); searchModalOpen = false" class="text-emerald-700 font-bold hover:underline">
                    Bersihkan
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
