<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Event - {{ $registration->event->title }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[#0c1a14] via-[#132c23] to-[#0a1510] min-h-screen text-slate-100 font-['Plus_Jakarta_Sans'] antialiased py-10 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">
    
    <div class="max-w-md mx-auto w-full space-y-6">
        
        <!-- Brand Header -->
        <div class="text-center">
            <span class="text-xs font-black tracking-widest uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-4 py-2 rounded-full">
                Institut Hijau Indonesia
            </span>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-2xl text-emerald-300 text-xs font-bold text-center">
                🎉 {{ session('success') }}
            </div>
        @endif

        <!-- Ticket Card Box -->
        <div class="bg-white text-slate-800 p-6 rounded-[2.5rem] rounded-tr-[5rem] rounded-bl-[5rem] shadow-2xl relative overflow-hidden border border-slate-100 flex flex-col justify-between space-y-6">
            
            <!-- Ticket Header Info -->
            <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                <div class="space-y-0.5 text-left">
                    <span class="block text-[8px] font-black uppercase text-slate-400 tracking-wider">Tiket Masuk Resmi</span>
                    <h2 class="text-base font-black text-emerald-800 leading-tight uppercase tracking-tight line-clamp-1">{{ $registration->event->title }}</h2>
                </div>
                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Terverifikasi</span>
            </div>

            <!-- QR Code Box -->
            <div class="flex flex-col items-center justify-center py-4 bg-slate-50 rounded-2xl border border-slate-100/50">
                <img src="{{ $qrCodeUri }}" class="w-44 h-44 border border-slate-200 p-2 rounded-xl bg-white shadow-xs">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-3">Scan Untuk Check-In Kehadiran</span>
            </div>

            <!-- Attendee Details Grid -->
            <div class="grid grid-cols-2 gap-4 text-xs border-t border-b border-slate-100 py-4 text-left">
                <div>
                    <span class="block text-[8px] font-black uppercase text-slate-400">Nama Lengkap</span>
                    <span class="block font-bold text-slate-800">{{ $registration->user ? $registration->user->name : $registration->guest_name }}</span>
                </div>
                <div>
                    <span class="block text-[8px] font-black uppercase text-slate-400">Email</span>
                    <span class="block font-bold text-slate-800 truncate">{{ $registration->user ? $registration->user->email : $registration->guest_email }}</span>
                </div>
                <div>
                    <span class="block text-[8px] font-black uppercase text-slate-400">Nomor Tiket</span>
                    <span class="block font-mono font-bold text-emerald-700 text-sm">{{ $registration->ticket_number }}</span>
                </div>
                <div>
                    <span class="block text-[8px] font-black uppercase text-slate-400">Jadwal Acara</span>
                    <span class="block font-bold text-slate-800 leading-snug">📅 {{ date('d M Y', strtotime($registration->event->event_date)) }}</span>
                </div>
            </div>

            <!-- Event Instructions & Access -->
            <div class="text-xs text-left bg-emerald-50/50 p-4 rounded-xl border border-emerald-100/50 space-y-1.5">
                <div class="font-extrabold text-emerald-800 flex items-center gap-1">
                    <span>📍</span>
                    <span>Akses Lokasi / Link:</span>
                </div>
                <p class="text-slate-600 font-semibold leading-relaxed">{{ $registration->event->location }}</p>
            </div>

            <div class="space-y-2">
                <a href="{{ route('public.events.attendance', $registration->event->id) }}" class="block w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md shadow-emerald-100">
                    ✍️ Form Absensi & Evaluasi Acara &rarr;
                </a>
                <a href="/" class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold uppercase text-[9px] tracking-wider text-center transition">
                    Kembali Ke Beranda
                </a>
            </div>

        </div>

    </div>

    <div class="text-center text-[10px] text-slate-500 pt-8">
        &copy; 2026 Institut Hijau Indonesia. All Rights Reserved.
    </div>

</body>
</html>
