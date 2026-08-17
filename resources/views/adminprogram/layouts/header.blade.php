<header class="fixed top-0 inset-x-0 z-50 bg-white border-b border-slate-100 shadow-sm" x-data="{ profileMenuOpen: false }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Kiri: Area Logo Utama -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center focus:outline-none">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo PROGRAM" class="h-14 w-auto object-contain md:h-16">
                </a>
            </div>

            <!-- Kanan (Desktop & Mobile): Tombol Profil Premium -->
            <div class="flex items-center">
                <div class="relative">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                            class="flex items-center space-x-2.5 text-sm font-bold text-slate-700 hover:text-emerald-700 transition-colors focus:outline-none p-1.5 rounded-xl hover:bg-slate-50">

                        <!-- Lingkaran Inisial Nama -->
                        <div class="flex items-center justify-center w-9 h-9 text-white rounded-full font-bold shadow-sm bg-gradient-to-br from-emerald-600 to-green-700 text-xs tracking-wider md:w-8 md:h-8">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>

                        <!-- Nama Hanya Muncul di Desktop -->
                        <span class="hidden sm:block max-w-[150px] truncate">{{ auth()->user()->name }}</span>

                        <!-- Panah Dropdown Hanya Muncul di Desktop -->
                        <svg class="hidden sm:block w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600': profileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- ========================================== -->
                    <!-- A. DROPDOWN UTK DESKTOP (Kanan Atas)       -->
                    <!-- ========================================== -->
                    <div x-show="profileMenuOpen"
                         @click.away="profileMenuOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                         class="hidden sm:block absolute right-0 w-48 py-1.5 mt-2 origin-top-right bg-white border border-slate-100 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5"
                         style="display: none;">

                        <div class="px-4 py-2 border-b border-slate-50">
                            <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold tracking-wider text-emerald-800 bg-emerald-50 rounded-md uppercase">
                                {{ auth()->user()->roles->first()->name ?? 'Peserta' }}
                            </span>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium">
                            Pengaturan Akun
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-rose-600 hover:bg-rose-50/50 font-bold">
                                Keluar Sistem
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- B. SLIDE-UP SHEET UTK MOBILE (Muncul Dr Bawah)-->
    <!-- ========================================== -->
    <!-- Backdrop Hitam Transparan -->
    <div x-show="profileMenuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="profileMenuOpen = false"
         class="sm:hidden fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm"
         style="display: none;"></div>

    <!-- Panel Menu Geser -->
    <div x-show="profileMenuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="sm:hidden fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl border-t border-slate-100 shadow-2xl px-4 pt-4 pb-8"
         style="display: none;">

        <!-- Handle Bar Kecil di Atas Panel -->
        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-4"></div>

        <!-- Info Singkat User -->
        <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl mb-4">
            <div class="flex items-center justify-center w-10 h-10 text-white rounded-full font-bold bg-gradient-to-br from-emerald-600 to-green-700 text-sm">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="truncate">
                <div class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <!-- Opsi Menu -->
        <div class="space-y-1">
            <span class="block px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
                Peran: {{ auth()->user()->roles->first()->name ?? 'Peserta' }}
            </span>

            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-3 py-3 text-sm font-semibold text-slate-600 rounded-xl hover:bg-slate-50 active:bg-slate-100">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Pengaturan Akun</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center space-x-3 px-3 py-3 text-sm font-bold text-rose-600 rounded-xl hover:bg-rose-50 active:bg-rose-100 text-left">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </div>
</header>
