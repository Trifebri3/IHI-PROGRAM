<header class="fixed top-0 inset-x-0 z-50 bg-white border-b border-slate-100 shadow-sm" x-data="{ profileMenuOpen: false }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex items-center">
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="lg:hidden p-2 -ml-2 mr-2 text-slate-600 hover:text-emerald-700 hover:bg-slate-100 rounded-xl transition-colors focus:outline-none"
                        aria-label="Buka Menu Navigasi">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center focus:outline-none">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo PROGRAM" class="h-14 w-auto object-contain md:h-16">
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @include('components.notification-bell')

                <div class="flex items-center">
                    <div class="relative">
                        <button @click="profileMenuOpen = !profileMenuOpen"
                                @click.away="profileMenuOpen = false"
                                class="flex items-center space-x-2 text-sm font-bold text-slate-700 hover:text-emerald-700 transition-colors focus:outline-none p-1.5 rounded-xl hover:bg-slate-50">

                            <div class="flex items-center justify-center w-8 h-8 text-white rounded-full font-bold shadow-sm bg-gradient-to-br from-emerald-600 to-green-700 text-xs tracking-wider">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>

                            <span class="hidden sm:block max-w-[150px] truncate">{{ auth()->user()->name }}</span>

                            <svg class="hidden sm:block w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600': profileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="profileMenuOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                             class="absolute right-0 w-48 py-1.5 mt-2 origin-top-right bg-white border border-slate-100 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 z-50"
                             style="display: none;">

                            <div class="px-4 py-2 border-b border-slate-50">
                                <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold tracking-wider text-emerald-800 bg-emerald-50 rounded-md uppercase">
                                    {{ auth()->user()->roles->first()->name ?? 'User' }}
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
    </div>
</header>
