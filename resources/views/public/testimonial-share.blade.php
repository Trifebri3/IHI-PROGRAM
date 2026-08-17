<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- OpenGraph Meta Tags for WhatsApp and Social Previews -->
    <meta property="og:title" content="Suara Pemimpin Hijau - {{ $registration->user->name }}" />
    <meta property="og:description" content="&ldquo;{{ strip_tags($registration->motivation) }}&rdquo; - Pendaftar {{ $registration->program->name }}" />
    <meta property="og:image" content="{{ $registration->user->profile?->profile_photo_path ? asset('storage/'.$registration->user->profile->profile_photo_path) : 'https://program.instituthijauindonesia.or.id/assets/images/logo.png' }}" />
    <meta property="og:url" content="{{ request()->url() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Institut Hijau Indonesia" />

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Suara Pemimpin Hijau - {{ $registration->user->name }}" />
    <meta name="twitter:description" content="&ldquo;{{ strip_tags($registration->motivation) }}&rdquo;" />
    <meta name="twitter:image" content="{{ $registration->user->profile?->profile_photo_path ? asset('storage/'.$registration->user->profile->profile_photo_path) : 'https://program.instituthijauindonesia.or.id/assets/images/logo.png' }}" />

    <title>Suara & Harapan Pemimpin Hijau - {{ $registration->user->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@1,600;1,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .poster-glow {
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.15), 0 0 10px rgba(16, 185, 129, 0.05);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0a1510] via-[#132c23] to-[#070f0b] min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 font-['Plus_Jakarta_Sans'] antialiased">
    
    <div class="w-full max-w-4xl mx-auto space-y-8 flex flex-col items-center">
        
        <!-- Header Logo -->
        <a href="/" class="flex items-center gap-2 text-white/90 hover:text-white transition-colors duration-300">
            <span class="text-xs font-black tracking-widest uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1.5 rounded-full">
                Institut Hijau Indonesia
            </span>
        </a>

        <!-- Poster Layout (Twin Box inside a card poster container) -->
        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch poster-glow bg-[#10241d]/40 backdrop-blur-xl border border-emerald-500/20 p-6 sm:p-8 rounded-[3rem] rounded-tl-[6rem] rounded-br-[6rem]">
            
            <!-- Box 1: Profile Details & Photo (Instagram-style visual card) -->
            <div class="bg-white p-6 rounded-3xl rounded-tl-[4rem] rounded-br-[4rem] border-b-4 border-r-4 border-slate-200/80 shadow-sm flex flex-col justify-between text-left">
                <div>
                    <!-- Header handle info -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-slate-400 text-[10px] font-extrabold uppercase tracking-widest">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Kader Pemimpin Hijau</span>
                        </div>
                        @if($registration->user->verification?->status === 'verified')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-50 text-blue-700 border border-blue-150">
                                <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                <span>Verified Profile</span>
                            </span>
                        @endif
                    </div>

                    <!-- Large Photo Container -->
                    <div class="w-full h-56 sm:h-64 rounded-2xl overflow-hidden bg-slate-50 border relative mb-4">
                        @if($registration->user->profile?->profile_photo_path)
                            <img src="{{ asset('storage/'.$registration->user->profile->profile_photo_path) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-700">
                                <span class="font-black text-5xl tracking-tight font-mono uppercase">{{ substr($registration->user->name, 0, 2) }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-2">Institut Hijau Indonesia</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer details -->
                <div class="space-y-1.5 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-1.5">
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ $registration->user->name }}</h4>
                        @if($registration->user->verification?->status === 'verified')
                            <span class="text-blue-500" title="Profil Terverifikasi">
                                <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.99-3.818-3.99-.48 0-.936.09-1.354.254C14.775 2.5 13.51 1.5 12 1.5s-2.775 1-3.418 2.264a4.135 4.135 0 00-1.354-.254C5.128 3.51 3.418 5.29 3.418 7.5c0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.71 3.99 3.818 3.99.48 0 .936-.09 1.354-.254.643 1.264 1.908 2.264 3.418 2.264s2.775-1 3.418-2.264c.418.164.874.254 1.354.254 2.108 0 3.818-1.78 3.818-3.99 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.5 4l-4-4 1.41-1.41L10 13.67l6.59-6.59L18 8.5l-8 8z" />
                                </svg>
                            </span>
                        @endif
                    </div>
                    <span class="text-[9px] bg-slate-50 text-slate-500 border border-slate-200 px-2 py-0.5 rounded font-extrabold uppercase tracking-wider inline-block">
                        {{ $registration->program->name }}
                    </span>
                </div>
            </div>

            <!-- Box 2: Motivation Quote (Dark green premium card) -->
            <div class="bg-[#132c23] text-white p-6 sm:p-8 rounded-3xl rounded-tr-[4rem] rounded-bl-[4rem] border-b-4 border-r-4 border-emerald-950 shadow-md flex flex-col justify-between min-h-[320px] relative overflow-hidden text-left">
                <!-- Decorative Quote Icon Background -->
                <div class="absolute -top-4 -right-4 w-28 h-28 text-emerald-800/10 pointer-events-none transform rotate-12">
                    <svg fill="currentColor" viewBox="0 0 24 24" class="w-full h-full"><path d="M14 17h3l2-4V7h-6v6h3zM1 13h3l2-4V7H0v6h3z"/></svg>
                </div>

                <div class="space-y-6">
                    <div class="text-slate-350 text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-1.5 relative">
                        <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        <span>Harapan & Motivasi</span>
                    </div>
                    <div class="text-lg sm:text-xl font-bold leading-relaxed italic text-emerald-50/95 font-sans relative">
                        "{{ strip_tags($registration->motivation) }}"
                    </div>
                </div>

                <!-- Footer Sharing Action Grid -->
                <div class="space-y-4 pt-6 border-t border-white/10 mt-6 relative">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="https://api.whatsapp.com/send?text={{ rawurlencode('"' . strip_tags($registration->motivation) . '" - ' . $registration->user->name . ' (' . $registration->program->name . ') Selengkapnya di: ' . request()->url()) }}" 
                           target="_blank" 
                           class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#25D366] hover:bg-[#20ba59] active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md cursor-pointer">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.968C16.59 1.97 14.12 .948 11.99 1.95c-5.44 0-9.866 4.372-9.87 9.802 0 1.81.503 3.578 1.46 5.161L2.56 21.22l4.087-1.066z"/></svg>
                            <span>Share WA</span>
                        </a>
                        <button id="btn-copy-link" 
                                class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md cursor-pointer">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                            <span>Salin Link</span>
                        </button>
                    </div>
                    <a href="/" class="block text-center text-[9px] font-bold uppercase tracking-wider text-slate-400 hover:text-emerald-300 transition-colors">
                        &larr; Kembali ke Beranda Utama
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Custom Success Toast -->
    <div id="share-toast" class="fixed bottom-6 right-6 z-50 bg-[#10241d] border border-emerald-500/30 px-5 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-white text-xs font-semibold translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        <span>Link share motivasi berhasil disalin! 📋</span>
    </div>

    <script>
        document.getElementById('btn-copy-link')?.addEventListener('click', function() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const toast = document.getElementById('share-toast');
                if (toast) {
                    toast.classList.remove('translate-y-20', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                    setTimeout(() => {
                        toast.classList.remove('translate-y-0', 'opacity-100');
                        toast.classList.add('translate-y-20', 'opacity-0');
                    }, 3000);
                }
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });
    </script>
</body>
</html>
