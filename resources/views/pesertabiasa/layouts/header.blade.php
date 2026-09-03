<header class="fixed top-0 inset-x-0 z-50 bg-white border-b border-slate-100 shadow-sm" x-data="{ profileMenuOpen: false }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center focus:outline-none">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo PROGRAM" class="h-14 w-auto object-contain md:h-16">
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Lonceng Notifikasi & Pengumuman Lengkap -->
                @include('components.notification-bell')

                <div class="relative">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                            class="flex items-center space-x-2.5 text-sm font-bold text-slate-700 hover:text-emerald-700 transition-colors focus:outline-none p-1.5 rounded-xl hover:bg-slate-50">

                        <div class="w-9 h-9 rounded-full overflow-hidden border border-slate-200 bg-gradient-to-br from-emerald-600 to-green-700 flex items-center justify-center text-white font-bold shadow-sm text-xs tracking-wider md:w-8 md:h-8">
                            @if(auth()->user()->userProfile && auth()->user()->userProfile->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->userProfile->profile_photo_path) }}" alt="Foto Profil" class="w-full h-full object-cover">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>

                        <span class="hidden sm:block max-w-[150px] truncate">{{ auth()->user()->name }}</span>

                        <svg class="hidden sm:block w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600': profileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

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

                        <a href="{{ route('identitas.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium">
                            Identitas Lengkap
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
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

    <div x-show="profileMenuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="sm:hidden fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl border-t border-slate-100 shadow-2xl px-4 pt-4 pb-8"
         style="display: none;">

        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-4"></div>

        <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl mb-4">
            <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-gradient-to-br from-emerald-600 to-green-700 flex items-center justify-center text-white font-bold text-sm">
                @if(auth()->user()->userProfile && auth()->user()->userProfile->profile_photo_path)
                    <img src="{{ asset('storage/' . auth()->user()->userProfile->profile_photo_path) }}" alt="Foto Profil" class="w-full h-full object-cover">
                @else
                    {{ substr(auth()->user()->name, 0, 1) }}
                @endif
            </div>
            <div class="truncate">
                <div class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <div class="space-y-1">
            <span class="block px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
                Peran: {{ auth()->user()->roles->first()->name ?? 'Peserta' }}
            </span>

            <a href="{{ route('identitas.index') }}" class="flex items-center space-x-3 px-3 py-3 text-sm font-semibold text-slate-600 rounded-xl hover:bg-slate-50 active:bg-slate-100">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Identitas Lengkap</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
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