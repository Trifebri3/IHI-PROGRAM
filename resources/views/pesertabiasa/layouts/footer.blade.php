<footer class="hidden bg-white border-t border-slate-100 mt-auto md:block pb-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">

            <div class="flex justify-center mb-4 space-x-6 md:order-2 md:mb-0">
                <a href="{{ route('faq') }}" class="text-sm font-medium text-gray-500 transition-colors hover:text-emerald-600">
                    Pusat Bantuan
                </a>
                <a href="{{ route('kebijakan-privasi') }}" class="text-sm font-medium text-gray-500 transition-colors hover:text-emerald-600">
                    Kebijakan Privasi
                </a>
                <a href="{{ route('syarat-ketentuan') }}" class="text-sm font-medium text-gray-500 transition-colors hover:text-emerald-600">
                    Syarat & Ketentuan
                </a>
            </div>

            <div class="md:order-1">
                <p class="text-sm text-center text-gray-500 md:text-left">
                    &copy; {{ date('Y') }} <span class="font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-800 to-green-600">PROGRAM</span><span class="font-light text-emerald-600">Pusat</span>. Hak Cipta Dilindungi.
                </p>
            </div>

            <div class="hidden md:flex md:order-3 md:justify-end">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Sistem v1.0.0 (Peserta)
                </span>
            </div>

        </div>
    </div>
</footer>


