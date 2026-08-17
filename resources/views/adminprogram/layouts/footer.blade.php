<footer class="hidden bg-white border-t border-slate-100 mt-auto md:block pb-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">

            <div class="flex justify-center mb-4 space-x-6 md:order-2 md:mb-0">
                <a href="#" class="text-sm font-medium text-slate-500 transition-colors hover:text-emerald-600">
                    Pusat Bantuan
                </a>
                <a href="#" class="text-sm font-medium text-slate-500 transition-colors hover:text-emerald-600">
                    Kebijakan Privasi
                </a>
                <a href="#" class="text-sm font-medium text-slate-500 transition-colors hover:text-emerald-600">
                    Syarat & Ketentuan
                </a>
            </div>

            <div class="md:order-1">
                <p class="text-sm text-center text-slate-500 md:text-left">
                    &copy; {{ date('Y') }} <span class="font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-800 to-green-600">PROGRAM</span><span class="font-light text-emerald-600">Pusat</span>. Hak Cipta Dilindungi.
                </p>
            </div>

            <div class="hidden md:flex md:order-3 md:justify-end">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-700 border border-slate-200">
                    Sistem v1.0.0 (Admin Program)
                </span>
            </div>

        </div>
    </div>
</footer>

<div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white/90 backdrop-blur-md border-t border-slate-100 md:hidden pb-safe">
    <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">

        <a href="{{ route('dashboard') }}" class="inline-flex flex-col items-center justify-center px-5 group {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600' }}">
            <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span class="text-[11px] tracking-wide">Dashboard</span>
        </a>

        <a href="{{ route('adminprogram.programs.index') }}" class="inline-flex flex-col items-center justify-center px-5 group {{ request()->routeIs('adminprogram.programs.*') ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600' }}">
            <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="text-[11px] tracking-wide">Program</span>
        </a>



        <a href="{{ route('profile.edit') }}" class="inline-flex flex-col items-center justify-center px-5 group {{ request()->routeIs('profile.edit') ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600' }}">
            <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-[11px] tracking-wide">Profil</span>
        </a>

    </div>
</div>
