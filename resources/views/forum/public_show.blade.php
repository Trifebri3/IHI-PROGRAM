@php
    $reactionsSummary = $discussion->reactionSummary();
    $totalReactions = $discussion->reactions->count();
    $isRepostItem = ($discussion->repost_of_id && $discussion->originalDiscussion);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $discussion->title }} | Green Forum - Institut Hijau Indonesia</title>

    <!-- Open Graph & Social Sharing Meta Tags -->
    <meta property="og:title" content="{{ $discussion->title }} - Green Forum IHI">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($discussion->content), 160) }}">
    <meta property="og:image" content="{{ asset('images/logo.webp') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.pwa-meta')
</head>
<body class="font-sans antialiased text-slate-900 bg-[#fbfbfb] min-h-full flex flex-col selection:bg-emerald-600 selection:text-white">

    <!-- Toast Notification -->
    <div id="thread-toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 pointer-events-none opacity-0 transition-all duration-300 transform -translate-y-2">
        <div class="bg-slate-950/95 backdrop-blur-md text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-2xl flex items-center gap-2 border border-slate-800">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            <span id="toast-message">Tautan disalin ke papan klip!</span>
        </div>
    </div>

    <!-- Header Navigasi Publik -->
    <header class="sticky top-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_1px_4px_rgba(0,0,0,0.03)]">
        <div class="max-w-4xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
            <!-- Branding IHI -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group focus:outline-none">
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

            <!-- Aksi Kanan (Masuk / Buka Forum) -->
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('forum.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-xs">
                        <span>Buka Forum</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3.5 py-2 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 rounded-full text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-xs">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Konten Diskusi Publik -->
    <main class="flex-1 w-full max-w-xl mx-auto px-3 sm:px-4 pt-6 pb-20 space-y-4">

        <!-- Banner Mode Publik View-Only -->
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 rounded-2xl p-3.5 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 text-emerald-800 font-semibold">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Pratinjau Publik (Hanya Baca)</span>
            </div>
            <button type="button"
                    onclick="copyCurrentLink()"
                    class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                <span>Salin Tautan</span>
            </button>
        </div>

        <!-- Kartu Diskusi -->
        <article class="bg-white rounded-[26px] border border-slate-200/80 shadow-[0_4px_24px_-6px_rgba(0,0,0,0.03)] p-5 relative">

            @if($isRepostItem)
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 mb-2.5 pl-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" />
                    </svg>
                    <span>{{ $discussion->user->name }} memposting ulang</span>
                </div>
            @endif

            <div class="flex items-start gap-3">
                <!-- Avatar Penulis -->
                <div class="w-11 h-11 rounded-full overflow-hidden bg-slate-100 border border-slate-200 ring-2 ring-white shadow-xs relative flex-shrink-0">
                    @if($discussion->user->profile?->profile_photo_path)
                        <img src="{{ asset('storage/' . $discussion->user->profile->profile_photo_path) }}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none;" class="w-full h-full bg-emerald-700 text-white font-black text-xs items-center justify-center">
                            {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                        </div>
                    @elseif($discussion->user->avatar)
                        <img src="{{ $discussion->user->avatar }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-emerald-700 flex items-center justify-center font-black text-white text-xs">
                            {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <!-- Detail Diskusi -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                        <span class="font-extrabold text-slate-900 text-sm">{{ $discussion->user->name }}</span>

                        @if($discussion->user->isVerifiedAccount())
                            <span class="inline-flex items-center text-sky-500 flex-shrink-0" title="Akun Terverifikasi Resmi">
                                <svg class="w-4 h-4 fill-current drop-shadow-xs" viewBox="0 0 24 24" aria-label="Terverifikasi">
                                    <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @endif

                        @if($discussion->user->hasRole('Super Admin'))
                            <span class="px-1.5 py-0.2 rounded-md bg-purple-50 text-purple-700 text-[9px] font-black border border-purple-200">Admin</span>
                        @elseif($discussion->user->hasRole('Admin Program'))
                            <span class="px-1.5 py-0.2 rounded-md bg-emerald-50 text-emerald-700 text-[9px] font-black border border-emerald-200">Pengelola</span>
                        @endif

                        <span class="text-slate-400 text-xs">• {{ $discussion->created_at->diffForHumans(null, true) }}</span>
                    </div>

                    <h1 class="text-base sm:text-lg font-black text-slate-950 leading-snug tracking-tight mb-2">
                        {{ $discussion->title }}
                    </h1>

                    @if($discussion->content)
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line font-normal mb-3">
                            {{ $discussion->content }}
                        </p>
                    @endif

                    @if($isRepostItem)
                        <div class="mt-2.5 mb-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-200/90 text-xs">
                            <div class="flex items-center gap-1.5 mb-1 text-slate-700">
                                <span class="font-bold text-[11px]">{{ $discussion->originalDiscussion->user->name }}</span>
                                @if($discussion->originalDiscussion->user->isVerifiedAccount())
                                    <span class="text-sky-500">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                                    </span>
                                @endif
                                <span class="text-slate-400 text-[10px]">• {{ $discussion->originalDiscussion->created_at->diffForHumans(null, true) }}</span>
                            </div>
                            <p class="font-bold text-slate-900 text-xs mb-0.5">{{ $discussion->originalDiscussion->title }}</p>
                            <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed">{{ $discussion->originalDiscussion->content }}</p>
                        </div>
                    @endif

                    <!-- Statistik Reaksi & Interaksi -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-2 pb-3 border-t border-slate-100">
                        @forelse($reactionsSummary as $r)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.8 rounded-full text-xs bg-slate-50 border border-slate-200 text-slate-600"
                                  title="{{ implode(', ', $r['users']) }}">
                                <span>{{ $r['reaction'] }}</span>
                                <span class="text-[11px] font-semibold">{{ $r['count'] }}</span>
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 italic">Belum ada reaksi</span>
                        @endforelse

                        <div class="ml-auto flex items-center gap-3 text-xs text-slate-500 font-semibold">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.636 1.064.508 1.67-.282 1.34-1.026 2.476-1.065 2.536a.75.75 0 00.74 1.164c1.785-.205 3.328-.857 4.382-1.472.375-.219.822-.26 1.23-.128.932.298 1.916.45 2.932.45z"/></svg>
                                <span>{{ $discussion->comments->count() }} balasan</span>
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" /></svg>
                                <span>{{ $discussion->reposts->count() }} posting ulang</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balasan Komentar -->
            @if($discussion->comments->count() > 0)
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Balasan Peserta ({{ $discussion->comments->count() }})</h3>
                    @foreach($discussion->comments as $c)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-7 h-7 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 mt-0.5 relative">
                                @if($c->user->profile?->profile_photo_path)
                                    <img src="{{ asset('storage/' . $c->user->profile->profile_photo_path) }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none;" class="w-full h-full bg-slate-700 text-white font-bold text-[8px] items-center justify-center">
                                        {{ strtoupper(substr($c->user->name, 0, 2)) }}
                                    </div>
                                @elseif($c->user->avatar)
                                    <img src="{{ $c->user->avatar }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-slate-700 text-white flex items-center justify-center font-bold text-[8px]">
                                        {{ strtoupper(substr($c->user->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 bg-slate-50/70 p-2.5 rounded-2xl border border-slate-100/90">
                                <div class="flex items-center gap-1 mb-0.5">
                                    <span class="font-bold text-slate-900 text-[11px]">{{ $c->user->name }}</span>
                                    @if($c->user->isVerifiedAccount())
                                        <span class="inline-flex items-center text-sky-500">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @endif
                                    <span class="text-slate-400 text-[10px] ml-auto">{{ $c->created_at->diffForHumans(null, true) }}</span>
                                </div>
                                <p class="text-slate-700 text-xs leading-relaxed">{{ $c->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </article>

        <!-- CTA Box Bergabung Komunitas -->
        <div class="bg-white rounded-[26px] border border-slate-200/80 shadow-xs p-5 sm:p-6 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            </div>
            <div>
                <h3 class="font-black text-slate-900 text-base">Ingin Ikut Berdiskusi?</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Bergabunglah dengan Institut Hijau Indonesia untuk membalas topik ini, memberikan reaksi, dan berbagi pemikiran dengan peserta lainnya.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-1">
                @auth
                    <a href="{{ route('forum.index') }}"
                       class="px-6 py-2.5 rounded-full text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-xs">
                        Buka di Green Forum
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-5 py-2.5 rounded-full text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        Masuk Akun
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-5 py-2.5 rounded-full text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-xs">
                        Daftar Sekarang
                    </a>
                @endauth
            </div>
        </div>

    </main>

    <script>
        function copyCurrentLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast('Tautan topik publik disalin!');
            }).catch(() => {
                showToast('Tautan siap dibagikan!');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('thread-toast');
            const text = document.getElementById('toast-message');
            if (!toast || !text) return;

            text.innerText = msg;
            toast.classList.remove('opacity-0', '-translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-2');
            }, 2200);
        }
    </script>
</body>
</html>
