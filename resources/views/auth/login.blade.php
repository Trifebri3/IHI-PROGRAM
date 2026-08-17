{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    

    
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-700 bg-green-50 p-3 rounded-xl border-l-4 border-green-600">
            {{ session('status') }}
        </div>
    @endif

    @if (session('verification_sent'))
        <div class="mb-4 text-sm font-medium text-blue-700 bg-blue-50 p-3 rounded-xl border-l-4 border-blue-500">
            {{ session('verification_sent') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="input-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="input-field" placeholder="nama@instituthijau.id">
            @error('email')
                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                   class="input-field" placeholder="masukkan kata sandi">
            @error('password')
                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-3 mb-6">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="remember" class="checkbox-custom">
                <span class="text-sm text-gray-600 ml-2">Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link-green text-sm">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="btn-green">Masuk ke Akun</button>

        <div class="text-center mt-6 pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="link-green font-semibold">Daftar sebagai anggota</a>
            </p>
        </div>
    </form>

    <div class="info-badge mt-5">
        <div class="font-semibold text-sm mb-1">Akses Ekosistem Hijau</div>
        <div class="text-xs leading-relaxed">
            Setelah login, Anda dapat mengakses materi pelatihan, sertifikasi ramah lingkungan, forum diskusi, dan peluang kolaborasi dengan mitra hijau Institut Hijau Indonesia.
        </div>
    </div>

    <div class="mt-3 text-center">
        <p class="text-[11px] text-gray-400">
            Belum menerima email verifikasi? <a href="{{ route('verification.send') }}" class="link-green">Kirim ulang</a>
        </p>
    </div>

    <div class="mt-6">
    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
        <div class="relative flex justify-center text-sm"><span class="bg-white px-4 text-gray-400">Atau masuk dengan</span></div>
    </div>

<div
   class="flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-gray-100 p-3 font-semibold text-gray-500 opacity-70">
    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" width="20" alt="Google Logo">
    <span>Google Account</span>
</div>
</div>

</x-guest-layout>
