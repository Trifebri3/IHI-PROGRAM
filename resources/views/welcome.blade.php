@extends('layouts.public')

@section('title', 'Selamat Datang di Portal Resmi PROGRAM INSTITUT HIJAU INDONESIA')

@section('content')

    <div class="bg-emerald-50/60 border-b border-emerald-100 py-3 px-4 text-center">
        <div class="max-w-5xl mx-auto flex items-center justify-center gap-2 text-emerald-950 text-xs sm:text-sm font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="leading-relaxed text-left sm:text-center">
                <span class="font-bold text-emerald-700 mr-1">[Informasi]</span> 
                Saat ini kami sedang dalam proses optimalisasi sistem dan pemindahan data secara berkala.
            </p>
        </div>
    </div>
    
    <div class="bg-white py-12 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center mb-12">
                
                <div class="lg:col-span-7 space-y-6 text-left">
                    <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
                        Portal Resmi Registrasi Program & Event
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]">
                        The sound <br>
                        of a new <br>
                        <span class="text-emerald-600">generation</span>
                    </h1>
                    
                    <div class="max-w-2xl">
                        <p class="text-base sm:text-lg font-bold text-slate-800 leading-snug">
                            Sistem integrasi pendaftaran, validasi data transparan, dan manajemen profil dinamis untuk seluruh ekosistem Institut Hijau Indonesia.
                        </p>
                    </div>

                    <!-- Quick Actions Section (Highly Optimized for Mobile) -->
                    <div class="pt-2">
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2.5">Akses Cepat Layanan:</span>
                        <div class="grid grid-cols-2 gap-3 w-full sm:max-w-xl">
                            <!-- Button 1: Daftar Akun -->
                            <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 py-3 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-md shadow-emerald-150 transition-all text-center">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 0118 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span>Daftar Akun</span>
                            </a>
                            <!-- Button 2: Masuk Portal -->
                            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 py-3 px-3 bg-white hover:bg-slate-50 active:scale-95 text-slate-800 border border-slate-200 hover:border-slate-350 font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-sm transition-all text-center">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span>Masuk</span>
                            </a>
                            <!-- Button 3: E-Learning -->
                            <a href="https://e-learning.instituthijauindonesia.or.id/" target="_blank" class="flex items-center justify-center gap-2 py-3 px-3 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-md shadow-amber-100/50 transition-all text-center">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>E-Learning</span>
                            </a>
                            <!-- Button 4: Roster Alumni -->
                            <a href="{{ route('public.program.index') }}" class="flex items-center justify-center gap-2 py-3 px-3 bg-emerald-50 hover:bg-emerald-100 active:scale-95 text-emerald-900 border border-emerald-200 font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-sm transition-all text-center">
                                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Alumni</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    <div class="w-full max-w-md lg:max-w-full rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                        <img src="{{ asset('images/banner1.png') }}" alt="Banner Institut Hijau Indonesia" class="w-full h-auto object-cover max-h-[350px] lg:max-h-[400px]">
                    </div>
                </div>

            </div>


        </div>
    </div>

    <style>
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 15px rgba(16, 185, 129, 0.15), 0 0 5px rgba(16, 185, 129, 0.05);
                border-color: rgba(16, 185, 129, 0.3);
            }
            50% {
                box-shadow: 0 0 30px rgba(16, 185, 129, 0.35), 0 0 10px rgba(16, 185, 129, 0.1);
                border-color: rgba(16, 185, 129, 0.6);
            }
        }
        .pinned-glow {
            animation: pulse-glow 3s infinite;
        }
    </style>

    <!-- Section: Event & Seminar Center -->
    @if(isset($events) && $events->isNotEmpty())
        @php
            $firstEvent = $events->first();
            $otherEvents = $events->skip(1);
        @endphp
        <div class="bg-white py-16 px-4 sm:px-6 lg:px-8 border-b border-slate-100">
            <div class="max-w-7xl mx-auto space-y-10">
                <div class="text-center space-y-2 mb-8">
                    <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-md uppercase tracking-wider font-mono">
                        Agenda & Seminar Center
                    </span>
                    <h2 class="text-3xl font-black text-slate-850 tracking-tight">Agenda Event & Seminar</h2>
                    <p class="text-xs text-slate-455 max-w-lg mx-auto leading-relaxed">
                        Ikuti berbagai seminar, webinar, dan kegiatan menarik lainnya yang diselenggarakan oleh Institut Hijau Indonesia.
                    </p>
                </div>

                @if($otherEvents->isEmpty())
                    <!-- SINGLE EVENT CARD: Centered Aligned & Clean Premium Rounded-3xl Card -->
                    <div class="max-w-xl mx-auto">
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                            <div class="space-y-4">
                                <!-- Banner Image -->
                                @if($firstEvent->banner_path)
                                    <div class="w-full h-56 sm:h-64 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 relative">
                                        <img src="{{ asset('storage/'.$firstEvent->banner_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-102">
                                    </div>
                                @else
                                    <div class="w-full h-56 sm:h-64 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex flex-col items-center justify-center text-emerald-600">
                                        <span class="text-5xl">📅</span>
                                    </div>
                                @endif

                                <div class="space-y-2 text-left">
                                    <span class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ $firstEvent->registration_type === 'external' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($firstEvent->registration_type === 'logged_in' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-250') }}">
                                        {{ $firstEvent->registration_type === 'external' ? 'External Link' : ($firstEvent->registration_type === 'logged_in' ? 'Akun Terdaftar Only' : 'Terbuka Umum') }}
                                    </span>
                                    <h3 class="text-lg font-black text-slate-800 leading-snug group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ $firstEvent->title }}</h3>
                                    <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed font-medium">{!! strip_tags($firstEvent->description) !!}</p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 mt-6 flex items-center justify-between gap-3">
                                <div class="text-[10px] text-slate-500 font-bold uppercase space-y-1 text-left">
                                    <div class="flex items-center gap-1.5">
                                        <span>📅</span>
                                        <span>{{ date('d M Y', strtotime($firstEvent->event_date)) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-emerald-650">
                                        <span>📍</span>
                                        <span>{{ Str::limit($firstEvent->location, 35) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('public.events.show', $firstEvent->id) }}" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl shadow-xs transition-all text-center cursor-pointer">
                                    Detail Event &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- MULTIPLE EVENTS GRID: Split-Screen Layout (Spotlight on Left, Stack on Right) -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
                        
                        <!-- LEFT COLUMN: Featured Spotlight Event Card (2/5 Width) -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group min-h-[480px]">
                                <div class="space-y-4">
                                    <!-- Banner Image -->
                                    @if($firstEvent->banner_path)
                                        <div class="w-full h-48 sm:h-56 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 relative">
                                            <img src="{{ asset('storage/'.$firstEvent->banner_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-102">
                                        </div>
                                    @else
                                        <div class="w-full h-48 sm:h-56 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex flex-col items-center justify-center text-emerald-600">
                                            <span class="text-4xl">📅</span>
                                        </div>
                                    @endif

                                    <div class="space-y-2 text-left">
                                        <span class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ $firstEvent->registration_type === 'external' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($firstEvent->registration_type === 'logged_in' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-250') }}">
                                            {{ $firstEvent->registration_type === 'external' ? 'External Link' : ($firstEvent->registration_type === 'logged_in' ? 'Akun Terdaftar Only' : 'Terbuka Umum') }}
                                        </span>
                                        <h3 class="text-base font-black text-slate-800 leading-snug group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ $firstEvent->title }}</h3>
                                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed font-medium">{!! strip_tags($firstEvent->description) !!}</p>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-100 mt-6 flex items-center justify-between gap-3">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase space-y-1 text-left">
                                        <div class="flex items-center gap-1.5">
                                            <span>📅</span>
                                            <span>{{ date('d M Y', strtotime($firstEvent->event_date)) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-650">
                                            <span>📍</span>
                                            <span>{{ Str::limit($firstEvent->location, 30) }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('public.events.show', $firstEvent->id) }}" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl shadow-xs transition-all text-center cursor-pointer">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Vertical Stacking List (3/5 Width) -->
                        <div class="lg:col-span-3 space-y-4">
                            @foreach($otherEvents as $item)
                                @php
                                    $day = date('d', strtotime($item->event_date));
                                    $month = strtoupper(date('M', strtotime($item->event_date)));
                                @endphp
                                <a href="{{ route('public.events.show', $item->id) }}" class="flex items-center gap-4 bg-white hover:bg-slate-50/50 p-4 rounded-2xl border border-slate-100 hover:border-emerald-500/30 transition-all duration-200 shadow-3xs hover:shadow-xs group">
                                    <!-- Custom Date Badge Box -->
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100/50 flex flex-col items-center justify-center shrink-0 select-none">
                                        <span class="text-base font-black leading-none">{{ $day }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-wider leading-none mt-1">{{ $month }}</span>
                                    </div>

                                    <!-- Middle Info -->
                                    <div class="flex-1 min-w-0 text-left space-y-1">
                                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-emerald-650 transition-colors truncate uppercase tracking-tight">
                                            {{ $item->title }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-1.5 py-0.5 rounded text-[7px] font-black uppercase {{ $item->registration_type === 'external' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($item->registration_type === 'logged_in' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-255') }}">
                                                {{ $item->registration_type === 'external' ? 'External' : ($item->registration_type === 'logged_in' ? 'Akun' : 'Umum') }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 font-semibold truncate">📍 {{ Str::limit($item->location, 35) }}</span>
                                        </div>
                                    </div>

                                    <!-- Arrow Indicator -->
                                    <div class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-emerald-50 flex items-center justify-center text-slate-400 group-hover:text-emerald-700 transition shrink-0">
                                        <svg class="w-3.5 h-3.5 stroke-current fill-none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Section 3: Kegiatan & Sorotan Media (Positioned above Programs for premium CTA spotlight) -->
    @if($highlights->isNotEmpty())
        <div class="py-16 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-100">
            <div class="max-w-7xl mx-auto space-y-10">
                <div class="text-center space-y-2">
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Social & Media Updates</span>
                    <h2 class="text-3xl font-black text-slate-850 tracking-tight">Kegiatan & Sorotan Media</h2>
                    <p class="text-xs text-slate-455 max-w-lg mx-auto leading-relaxed">
                        Ikuti perkembangan gerakan dan diskusi kelestarian ekologi langsung dari jejaring sosial kami.
                    </p>
                </div>

                <!-- Dynamic Grid container: centers dynamically if only 1 item exists -->
                <div class="{{ $highlights->count() === 1 ? 'max-w-xl mx-auto' : 'grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto' }}">
                    @foreach($highlights as $hl)
                        @if($hl->theme === 'dark')
                            <!-- Dark Theme Card (X/Twitter style Quote Card with 3D Asymmetric Borders) -->
                            <div class="bg-gradient-to-br from-[#132c23] via-[#0f241d] to-[#0c1d17] text-white p-8 rounded-[2.2rem] rounded-tl-[3.8rem] rounded-br-[3.8rem] border-b-4 border-r-4 border-emerald-950 border-t border-l border-emerald-800/20 shadow-md hover:shadow-lg transition-all flex flex-col justify-between min-h-[320px] text-left group hover:-translate-y-0.5 duration-300 relative overflow-hidden">
                                <!-- Decorative Quote Icon Background -->
                                <div class="absolute -top-4 -right-4 w-28 h-28 text-emerald-800/10 pointer-events-none transform rotate-12">
                                    <svg fill="currentColor" viewBox="0 0 24 24" class="w-full h-full"><path d="M14 17h3l2-4V7h-6v6h3zM1 13h3l2-4V7H0v6h3z"/></svg>
                                </div>
                                
                                <div class="space-y-6 relative">
                                    <div class="flex items-center gap-2 text-slate-300 text-[10px] font-extrabold uppercase tracking-widest">
                                        <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        <span>{{ $hl->title }}</span>
                                    </div>
                                    <div class="text-base sm:text-lg font-bold leading-relaxed italic text-emerald-50/95 font-sans">
                                        "{!! strip_tags($hl->content) !!}"
                                    </div>
                                </div>
                                @if($hl->link_url)
                                    <div class="pt-6 relative">
                                        <a href="{{ route('public.highlights.click', $hl->id) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-emerald-50 active:scale-95 text-[#132c23] font-extrabold text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-md cursor-pointer">
                                            <span>{{ $hl->link_text ?? 'Ikuti Diskusi' }}</span>
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Light Theme Card (Instagram style Activity Card with 3D Asymmetric Borders) -->
                            <div class="bg-white text-slate-800 p-6 rounded-[2.2rem] rounded-tr-[3.8rem] rounded-bl-[3.8rem] border-b-4 border-r-4 border-slate-200/80 border-t border-l border-slate-100/50 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-h-[340px] text-left group hover:-translate-y-0.5 duration-300">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 text-slate-450 text-[10px] font-extrabold uppercase tracking-widest">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $hl->title }}</span>
                                    </div>
                                    @if($hl->banner_path)
                                        <div class="w-full h-44 sm:h-48 rounded-2xl overflow-hidden bg-slate-50 border relative">
                                            <img src="{{ asset('storage/'.$hl->banner_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103">
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/10 via-transparent to-transparent"></div>
                                        </div>
                                    @endif
                                    <div class="text-xs text-slate-600 font-semibold leading-relaxed">
                                        {!! $hl->content !!}
                                    </div>
                                </div>
                                @if($hl->link_url)
                                    <div class="pt-5 border-t border-slate-100 mt-4 text-left">
                                        <a href="{{ route('public.highlights.click', $hl->id) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md shadow-emerald-100/50 hover:shadow-emerald-250 cursor-pointer">
                                            <span>{{ $hl->link_text ?? 'Selengkapnya' }}</span>
                                            <svg class="w-3.5 h-3.5 stroke-current fill-none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif



    <!-- Section 4: Program & Kelas Pendidikan -->
    <div class="bg-slate-50/50 py-16 px-4 sm:px-6 lg:px-8 border-b border-slate-100">
        <div class="max-w-7xl mx-auto space-y-12">
            
            <div class="text-center space-y-2">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Our Active Curriculums</span>
                <h2 class="text-3xl font-black text-slate-850 tracking-tight">Program & Kelas Pendidikan</h2>
                <p class="text-xs text-slate-455 max-w-lg mx-auto leading-relaxed">
                    Daftar program pendidikan kader pemimpin hijau yang sedang membuka pendaftaran maupun yang sedang berlangsung.
                </p>
            </div>

            <!-- 1. PINNED PROGRAM (Featured Spotlight Card) -->
            @if($pinnedProgram)
                <div class="space-y-4 text-left">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-amber-605 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                        <span>Sorotan Utama Program Kerja</span>
                    </span>
                    
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-500/30 pinned-glow shadow-md transition-all flex flex-col lg:flex-row gap-8 items-stretch group overflow-hidden">
                        <!-- Left Details Content -->
                        <div class="flex-1 flex flex-col justify-between space-y-6 text-left">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border flex items-center justify-center overflow-hidden shrink-0">
                                        @if($pinnedProgram->logo_path)
                                            <img src="{{ asset('storage/'.$pinnedProgram->logo_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="font-bold text-emerald-700 font-mono text-sm uppercase">{{ substr($pinnedProgram->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div class="space-y-0.5">
                                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover:text-emerald-600 transition-colors">{{ $pinnedProgram->name }}</h3>
                                        <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase bg-emerald-100 text-emerald-805 border border-emerald-200">SELEKSI AKTIF</span>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-550 leading-relaxed font-medium">
                                    {{ $pinnedProgram->description ?? 'Tidak ada ringkasan deskripsi program.' }}
                                </p>
                            </div>

                            <!-- Meta Grid info -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <span class="block text-[8px] font-extrabold uppercase text-slate-400">Kuota Program</span>
                                        <span class="block text-xs font-bold text-slate-700">{{ $pinnedProgram->quota ?? 'Unlimited' }} Peserta</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <span class="block text-[8px] font-extrabold uppercase text-slate-400">Jadwal Kelas</span>
                                        <span class="block text-[10px] font-bold text-slate-700 leading-tight">
                                            {{ $pinnedProgram->start_date ? $pinnedProgram->start_date->format('d M') : '' }} - {{ $pinnedProgram->end_date ? $pinnedProgram->end_date->format('d M Y') : '' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <span class="block text-[8px] font-extrabold uppercase text-slate-400">Verifikasi Profil</span>
                                        <span class="block text-xs font-bold text-slate-700">Terbuka Umum</span>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('program.apply', $pinnedProgram->id) }}" class="w-full sm:w-fit px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-100 transition-all text-center">
                                Ikuti Seleksi Utama &rarr;
                            </a>
                        </div>

                        <!-- Right Banner Image -->
                        @if($pinnedProgram->banner_path)
                            <div class="w-full lg:w-[400px] h-60 lg:h-auto rounded-2xl overflow-hidden bg-slate-50 border relative shrink-0">
                                <img src="{{ asset('storage/'.$pinnedProgram->banner_path) }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 2. CAROUSEL SECTION (Horizontal Scrolling Slider for rest of open programs) -->
            @if($openPrograms->isNotEmpty())
                <div class="space-y-4 text-left relative">
                    <div class="flex justify-between items-center pr-2">
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            {{ $pinnedProgram ? 'Daftar Kelas Pembelajaran Lainnya' : 'Program Pendaftaran Aktif' }}
                        </span>
                        
                        <!-- Carousel Nav Buttons -->
                        <div class="flex items-center gap-2">
                            <button onclick="document.getElementById('programs-carousel').scrollBy({ left: -360, behavior: 'smooth' })" class="w-8 h-8 rounded-xl bg-white border border-slate-100 shadow-3xs flex items-center justify-center text-slate-500 hover:text-emerald-600 active:scale-90 hover:border-emerald-200 transition-all cursor-pointer">
                                &larr;
                            </button>
                            <button onclick="document.getElementById('programs-carousel').scrollBy({ left: 360, behavior: 'smooth' })" class="w-8 h-8 rounded-xl bg-white border border-slate-100 shadow-3xs flex items-center justify-center text-slate-500 hover:text-emerald-600 active:scale-90 hover:border-emerald-200 transition-all cursor-pointer">
                                &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Snapping Slider Container -->
                    <div id="programs-carousel" class="flex gap-6 overflow-x-auto pb-4 scroll-smooth snap-x snap-mandatory scrollbar-none">
                        @foreach($openPrograms as $prog)
                            <div class="w-[340px] sm:w-[360px] shrink-0 snap-start bg-white rounded-3xl p-5 border border-slate-100 hover:border-emerald-500/50 shadow-3xs hover:shadow-sm transition-all flex flex-col justify-between group">
                                <div class="space-y-4">
                                    <!-- Logo and Badges -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-slate-50 border flex items-center justify-center overflow-hidden shrink-0">
                                                @if($prog->logo_path)
                                                    <img src="{{ asset('storage/'.$prog->logo_path) }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="font-bold text-emerald-700 font-mono text-xs uppercase">{{ substr($prog->name, 0, 2) }}</span>
                                                @endif
                                            </div>
                                            <div class="text-left">
                                                <h3 class="text-xs font-black text-slate-800 line-clamp-1 group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ $prog->name }}</h3>
                                                <span class="text-[8px] text-slate-400 font-semibold font-mono">Quota: {{ $prog->quota ?? 'Unlimited' }}</span>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[7px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-250">BUKA</span>
                                    </div>
                                    <!-- Banner Image -->
                                    @if($prog->banner_path)
                                        <div class="w-full h-36 rounded-2xl overflow-hidden bg-slate-50 border relative">
                                            <img src="{{ asset('storage/'.$prog->banner_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-102">
                                        </div>
                                    @endif
                                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed text-left">{{ $prog->description ?? 'Tidak ada ringkasan deskripsi program.' }}</p>
                                </div>

                                <div class="pt-4 border-t border-slate-50 mt-4 flex items-center justify-between gap-3">
                                    <span class="text-[8px] text-slate-455 font-bold uppercase tracking-wider">
                                        {{ $prog->start_date ? $prog->start_date->format('d M') : '' }} - {{ $prog->end_date ? $prog->end_date->format('d M Y') : '' }}
                                    </span>
                                    <a href="{{ route('program.apply', $prog->id) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[9px] uppercase tracking-wider rounded-xl shadow-xs transition-all text-center">
                                        Daftar &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif(!$pinnedProgram)
                <div class="p-8 text-center bg-white rounded-3xl border border-dashed border-slate-200 text-slate-400 italic text-xs">
                    Saat ini belum ada program pendaftaran yang dibuka secara umum.
                </div>
            @endif

            <!-- 3. CLOSED/ONGOING PROGRAMS -->
            @if($closedPrograms->isNotEmpty())
                <div class="space-y-4 pt-6 text-left">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Program Sedang Berjalan / Arsip:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($closedPrograms as $prog)
                            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-3xs flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-50 border flex items-center justify-center overflow-hidden shrink-0">
                                        @if($prog->logo_path)
                                            <img src="{{ asset('storage/'.$prog->logo_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="font-bold text-slate-400 font-mono text-xs uppercase">{{ substr($prog->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-extrabold text-slate-800 line-clamp-1 group-hover:text-emerald-600 transition-colors uppercase tracking-tight text-left">{{ $prog->name }}</h4>
                                        <span class="text-[8px] bg-slate-50 text-slate-400 border border-slate-200 px-1.5 py-0.5 rounded font-black uppercase inline-block mt-0.5">SELEKSI TUTUP</span>
                                    </div>
                                </div>
                                <a href="{{ route('public.program.index') }}" class="text-slate-400 hover:text-emerald-600 transition p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    <!-- Section 5: Motivasi & Harapan Pemimpin Hijau -->
    @if($featuredRegistration)
        <div class="py-16 px-4 sm:px-6 lg:px-8 bg-slate-50/30 border-b border-slate-100">
            <div class="max-w-7xl mx-auto space-y-10">
                <div class="text-center space-y-2">
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Green Leaders Voices</span>
                    <h2 class="text-3xl font-black text-slate-850 tracking-tight">Suara & Harapan Pemimpin Hijau</h2>
                    <p class="text-xs text-slate-455 max-w-lg mx-auto leading-relaxed">
                        Harapan dan motivasi dari para kader pemimpin hijau yang tergabung dalam program kelas pendidikan kami.
                    </p>
                </div>

                <!-- Twin-Box Layout Grid (Left: Profile & Photo, Right: Motivation & Share) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto items-stretch">
                    
                    <!-- BOX 1: Profile Details & Photo (Instagram-style visual card with asymmetric 3D styling) -->
                    <div class="bg-white p-6 rounded-[2.5rem] rounded-tl-[4.5rem] rounded-br-[4.5rem] border-b-4 border-r-4 border-slate-200/80 border-t border-l border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between text-left group hover:-translate-y-0.5 duration-300">
                        <div>
                            <!-- Header handle info -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2 text-slate-400 text-[10px] font-extrabold uppercase tracking-widest">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Kader Pemimpin Hijau</span>
                                </div>
                                <span id="profile-verification-badge-top">
                                    @if($featuredRegistration->user->verification?->status === 'verified')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-50 text-blue-700 border border-blue-150">
                                            <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                            <span>Verified Profile</span>
                                        </span>
                                    @endif
                                </span>
                            </div>

                            <!-- Large Photo Container -->
                            <div id="profile-photo-container" class="w-full h-48 sm:h-56 rounded-2xl overflow-hidden bg-slate-50 border relative mb-4">
                                @if($featuredRegistration->user->profile?->profile_photo_path)
                                    <img src="{{ asset('storage/'.$featuredRegistration->user->profile->profile_photo_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-700">
                                        <span class="font-black text-4xl tracking-tight font-mono uppercase">{{ substr($featuredRegistration->user->name, 0, 2) }}</span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mt-2">Institut Hijau Indonesia</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Footer details -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-50">
                            <div class="flex items-center gap-1.5">
                                <h4 id="profile-user-name" class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ $featuredRegistration->user->name }}</h4>
                                <span id="profile-verification-badge-name">
                                    @if($featuredRegistration->user->verification?->status === 'verified')
                                        <svg class="w-4.5 h-4.5 fill-current text-blue-500" viewBox="0 0 24 24">
                                            <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.99-3.818-3.99-.48 0-.936.09-1.354.254C14.775 2.5 13.51 1.5 12 1.5s-2.775 1-3.418 2.264a4.135 4.135 0 00-1.354-.254C5.128 3.51 3.418 5.29 3.418 7.5c0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.71 3.99 3.818 3.99.48 0 .936-.09 1.354-.254.643 1.264 1.908 2.264 3.418 2.264s2.775-1 3.418-2.264c.418.164.874.254 1.354.254 2.108 0 3.818-1.78 3.818-3.99 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.5 4l-4-4 1.41-1.41L10 13.67l6.59-6.59L18 8.5l-8 8z" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            <span id="profile-program-name" class="text-[9px] bg-slate-50 text-slate-500 border border-slate-200 px-2 py-0.5 rounded font-extrabold uppercase tracking-wider inline-block">
                                {{ $featuredRegistration->program->name }}
                            </span>
                        </div>
                    </div>

                    <!-- BOX 2: Motivation Quote (Dark green premium card with asymmetric 3D styling) -->
                    <div class="bg-gradient-to-br from-[#132c23] via-[#10271f] to-[#0d2019] text-white p-8 rounded-[2.5rem] rounded-tr-[4.5rem] rounded-bl-[4.5rem] border-b-4 border-r-4 border-emerald-950 border-t border-l border-emerald-800/30 shadow-md hover:shadow-lg transition-all flex flex-col justify-between min-h-[300px] text-left relative overflow-hidden group hover:-translate-y-0.5 duration-300">
                        <!-- Decorative Quote Icon Background -->
                        <div class="absolute -top-4 -right-4 w-28 h-28 text-emerald-800/10 pointer-events-none transform rotate-12">
                            <svg fill="currentColor" viewBox="0 0 24 24" class="w-full h-full"><path d="M14 17h3l2-4V7h-6v6h3zM1 13h3l2-4V7H0v6h3z"/></svg>
                        </div>

                        <div class="space-y-6">
                            <div class="text-slate-355 text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-1.5 relative">
                                <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                                <span>Harapan & Motivasi</span>
                            </div>
                            <div id="motivation-quote-text" class="text-base sm:text-lg font-bold leading-relaxed italic text-emerald-50/95 font-sans relative">
                                "{!! strip_tags($featuredRegistration->motivation) !!}"
                            </div>
                        </div>

                        <!-- Direct WhatsApp and Copy Link action triggers -->
                        <div class="pt-6 border-t border-white/10 mt-6 relative">
                            <div class="grid grid-cols-2 gap-3">
                                <a id="btn-wa-share" href="https://api.whatsapp.com/send?text={{ rawurlencode('"' . strip_tags($featuredRegistration->motivation) . '" - ' . $featuredRegistration->user->name . ' (' . $featuredRegistration->program->name . ') Selengkapnya di: ' . route('public.testimonial.share', $featuredRegistration->id)) }}" 
                                   target="_blank" 
                                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#25D366] hover:bg-[#20ba59] active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md cursor-pointer">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.968C16.59 1.97 14.12 .948 11.99 1.95c-5.44 0-9.866 4.372-9.87 9.802 0 1.81.503 3.578 1.46 5.161L2.56 21.22l4.087-1.066z"/></svg>
                                    <span>Share WA</span>
                                </a>
                                <button id="btn-share-testimonial"
                                        data-share-url="{{ route('public.testimonial.share', $featuredRegistration->id) }}"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md shadow-[#0c1c16]/50 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                    <span>Salin Link</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Participant Capsules ("Nozzles") -->
                <div class="space-y-4 pt-8 border-t border-slate-100/50 text-center">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Temukan Harapan & Motivasi Kader Lainnya:</span>
                    <div class="flex flex-wrap justify-center gap-2.5 max-w-4xl mx-auto">
                        @foreach($randomRegistrations as $reg)
                            <div class="testimonial-capsule inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:border-emerald-500/50 hover:bg-emerald-50/10 rounded-full shadow-3xs hover:shadow-2xs transition-all duration-300 cursor-pointer select-none"
                                 data-motivation="{{ strip_tags($reg->motivation) }}"
                                 data-author="{{ $reg->user->name }}"
                                 data-program="{{ $reg->program->name }}"
                                 data-photo="{{ $reg->user->profile?->profile_photo_path ? asset('storage/'.$reg->user->profile->profile_photo_path) : '' }}"
                                 data-verified="{{ $reg->user->verification?->status === 'verified' ? '1' : '0' }}"
                                 data-share-url="{{ route('public.testimonial.share', $reg->id) }}"
                                 data-whatsapp-url="https://api.whatsapp.com/send?text={{ rawurlencode('"' . strip_tags($reg->motivation) . '" - ' . $reg->user->name . ' (' . $reg->program->name . ') Selengkapnya di: ' . route('public.testimonial.share', $reg->id)) }}">
                                
                                <div class="w-5 h-5 rounded-full overflow-hidden bg-slate-50 border shrink-0 flex items-center justify-center">
                                    @if($reg->user->profile?->profile_photo_path)
                                        <img src="{{ asset('storage/'.$reg->user->profile->profile_photo_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-700 font-bold text-[8px] uppercase">
                                            {{ substr($reg->user->name, 0, 2) }}
                                        </div>
                                    @endif
                                </div>
                                <span class="text-[9.5px] font-black text-slate-700 tracking-tight">{{ $reg->user->name }}</span>
                                @if($reg->user->verification?->status === 'verified')
                                    <span class="text-blue-500 shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Toast Notification Container -->
        <div id="share-toast" class="fixed bottom-6 right-6 z-50 bg-[#132c23] border border-emerald-500/30 px-5 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-white text-xs font-semibold translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
            <svg class="w-4 h-4 text-emerald-400 fill-current" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <span>Link share motivasi berhasil disalin ke clipboard! 📋</span>
        </div>

        <script>
            // Handle Copy Share Link
            document.getElementById('btn-share-testimonial')?.addEventListener('click', function() {
                const shareUrl = this.getAttribute('data-share-url');
                
                navigator.clipboard.writeText(shareUrl).then(() => {
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
                    console.error('Failed to copy link: ', err);
                });
            });

            // Handle Testimonial Capsule Clicks (Dynamic Swapping)
            document.querySelectorAll('.testimonial-capsule').forEach(capsule => {
                capsule.addEventListener('click', function() {
                    // Update active state visual feedback
                    document.querySelectorAll('.testimonial-capsule').forEach(c => {
                        c.classList.remove('border-emerald-500/50', 'bg-emerald-50/10');
                        c.classList.add('border-slate-200');
                    });
                    this.classList.remove('border-slate-200');
                    this.classList.add('border-emerald-500/50', 'bg-emerald-50/10');

                    // Read Datasets
                    const motivation = this.getAttribute('data-motivation');
                    const author = this.getAttribute('data-author');
                    const program = this.getAttribute('data-program');
                    const photo = this.getAttribute('data-photo');
                    const verified = this.getAttribute('data-verified') === '1';
                    const shareUrl = this.getAttribute('data-share-url');
                    const whatsappUrl = this.getAttribute('data-whatsapp-url');

                    // 1. Swap Motivation Text
                    document.getElementById('motivation-quote-text').innerText = `"${motivation}"`;

                    // 2. Swap Profile Photo
                    const photoContainer = document.getElementById('profile-photo-container');
                    if (photo) {
                        photoContainer.innerHTML = `<img src="${photo}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103">`;
                    } else {
                        const initials = author.substring(0, 2).toUpperCase();
                        photoContainer.innerHTML = `<div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-700"><span class="font-black text-4xl tracking-tight font-mono uppercase">${initials}</span><span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mt-2">Institut Hijau Indonesia</span></div>`;
                    }

                    // 3. Swap Name and Program
                    document.getElementById('profile-user-name').innerText = author;
                    document.getElementById('profile-program-name').innerText = program;

                    // 4. Swap Verified Badges
                    const verificationSvg = `<svg class="w-4.5 h-4.5 fill-current text-blue-500" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.99-3.818-3.99-.48 0-.936.09-1.354.254C14.775 2.5 13.51 1.5 12 1.5s-2.775 1-3.418 2.264a4.135 4.135 0 00-1.354-.254C5.128 3.51 3.418 5.29 3.418 7.5c0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.71 3.99 3.818 3.99.48 0 .936-.09 1.354-.254.643 1.264 1.908 2.264 3.418 2.264s2.775-1 3.418-2.264c.418.164.874.254 1.354.254 2.108 0 3.818-1.78 3.818-3.99 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.5 4l-4-4 1.41-1.41L10 13.67l6.59-6.59L18 8.5l-8 8z" /></svg>`;
                    const topVerificationBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-50 text-blue-700 border border-blue-150"><svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg><span>Verified Profile</span></span>`;
                    
                    if (verified) {
                        document.getElementById('profile-verification-badge-top').innerHTML = topVerificationBadge;
                        document.getElementById('profile-verification-badge-name').innerHTML = verificationSvg;
                    } else {
                        document.getElementById('profile-verification-badge-top').innerHTML = '';
                        document.getElementById('profile-verification-badge-name').innerHTML = '';
                    }

                    // 5. Swap Share Targets
                    document.getElementById('btn-share-testimonial').setAttribute('data-share-url', shareUrl);
                    document.getElementById('btn-wa-share').setAttribute('href', whatsappUrl);
                });
            });
        </script>
    @endif
@endsection