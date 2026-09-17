<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerbang Keamanan Rahasia | Super Admin Console</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row border border-slate-100">

        <!-- DEKORATIF SIDEBAR KIRI -->
        <div class="bg-slate-900 p-8 text-white w-full md:w-1/3 flex flex-col justify-between relative overflow-hidden">
            <!-- Background glow accents -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500 rounded-full opacity-10 blur-xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-emerald-400 rounded-full opacity-10 blur-xl"></div>
            
            <div class="relative z-10">
                <h1 class="text-2xl font-black tracking-tighter">SECURE SHIELD</h1>
                <p class="text-[9px] text-slate-400 mt-2 uppercase tracking-widest font-extrabold">Super Admin Security Gate</p>
            </div>
            
            <div class="space-y-6 mt-12 relative z-10">
                <div class="text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Status Proteksi</div>
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                        <span class="text-xs font-bold text-emerald-400">KONSOL TERKUNCI</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Area ini berisi kendali kritis performa, server, pemeliharaan sistem, dan mode bertahan. Otorisasi ganda diperlukan sebelum melanjutkan.
                    </p>
                </div>
            </div>

            <div class="text-[9px] text-slate-500 mt-8 relative z-10">
                &copy; {{ date('Y') }} Institut Hijau Indonesia. All rights reserved.
            </div>
        </div>

        <!-- FORM PASSPHRASE ENTRY -->
        <div class="p-8 md:p-12 w-full md:w-2/3 flex flex-col justify-center">
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Otorisasi Akses Rahasia
            </h2>
            <p class="text-xs text-slate-400 mt-1 mb-8">Masukkan 6 digit kode rahasia operasional untuk memverifikasi wewenang Super Admin Anda.</p>

            @if (session('error'))
                <div class="bg-rose-50 text-rose-700 p-4 rounded-2xl text-[11px] font-bold mb-6 border border-rose-150 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('superadmin.secret-gate.verify') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Code input -->
                <div class="space-y-2">
                    <label for="secret_code" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Rahasia Operasi</label>
                    <input type="password" 
                           name="secret_code" 
                           id="secret_code"
                           maxlength="20"
                           required
                           autofocus
                           placeholder="••••••"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-base font-bold font-mono tracking-widest text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition text-center" />
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition shadow-sm">
                        Verifikasi Otoritas
                    </button>
                    <a href="/superadmin/dashboard" class="text-xs text-slate-400 hover:text-slate-650 font-bold px-4 py-2">
                        Kembali Ke Dasbor
                    </a>
                </div>
            </form>
        </div>

    </div>

</body>
</html>
