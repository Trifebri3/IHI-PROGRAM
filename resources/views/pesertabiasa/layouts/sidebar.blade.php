<aside class="hidden lg:flex fixed inset-y-0 left-0 z-40 w-64 pt-24 pb-6 px-4 bg-white border-r border-slate-100 lg:static lg:inset-0 min-h-[calc(100vh-4rem)] flex-col justify-between group-data-[sidebar=collapsed]:w-20"
       x-cloak>

    <div class="space-y-7 flex-1 overflow-y-auto pr-1">

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
                Main Desk
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('dashboard') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                        </svg>
                        <span>Overview Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

<div>
    <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
        Program Saya
    </span>
    <ul class="space-y-1">
        @php
            $myActivePrograms = \App\Models\Registration::with('program')
                ->where('user_id', auth()->id())
                // Menambahkan syarat: final_id_number wajib terisi (tidak null dan tidak kosong)
                ->whereNotNull('final_id_number')
                ->where('final_id_number', '!=', '')
                ->get();
        @endphp

        @forelse($myActivePrograms as $reg)
            @php
                $isCurrentActiveWorkspace = request()->is('programs/' . $reg->program_id . '/*') || (request()->route('id') == $reg->program_id && request()->routeIs('programs.internal.dashboard'));
            @endphp
            <li>
                <a href="{{ route('programs.internal.dashboard', $reg->program_id) }}"
                   class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group justify-between
                   {{ $isCurrentActiveWorkspace ? 'bg-gradient-to-r from-emerald-800 to-green-700 text-white shadow-md shadow-emerald-100' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">

                    <div class="flex items-center truncate mr-2">
                        <span class="truncate" title="{{ $reg->program->name }}">{{ $reg->program->name }}</span>
                    </div>

                    @if(!$isCurrentActiveWorkspace)
                        @if($reg->status === 'passed')
                            <span class="text-[8px] font-black tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded uppercase">MEMBER</span>
                        @elseif($reg->status === 'failed')
                            <span class="text-[8px] font-black tracking-wider bg-rose-50 text-rose-600 px-1.5 py-0.5 rounded uppercase">GUGUR</span>
                        @else
                            <span class="text-[8px] font-black tracking-wider bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded uppercase animate-pulse">SELEKSI</span>
                        @endif
                    @endif
                </a>
            </li>
        @empty
            <li class="px-3.5 py-2 text-slate-400 italic text-[11px] leading-relaxed bg-slate-50 rounded-xl border border-dashed border-slate-100">
                Belum ada riwayat keikutsertaan program kerja atau Nomor Induk belum diterbitkan.
            </li>
        @endforelse
    </ul>
</div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
                Aktivitas Saya
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('programs.catalog') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('programs.catalog') || request()->routeIs('program.apply') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('programs.catalog') || request()->routeIs('program.apply') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                        <span>Katalog Program</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('verification.create') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('verification.create') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('verification.create') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Verifikasi Berkas</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('events.catalog') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('events.catalog') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('events.catalog') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <span>Seminar & Event</span>
                    </a>
                </li>
            </ul>
        </div>

<div>
    <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
        Integrasi Sistem
    </span>

    <ul class="space-y-1">
        <li>
            <a href="https://e-learning.instituthijauindonesia.or.id/"
               target="_blank"
               class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl text-slate-700 hover:text-emerald-700 bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 transition-all duration-200 group justify-between">
                
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>E-Learning</span>
                </div>

                <span class="text-[9px] font-bold tracking-wider bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md uppercase">
                    SSO
                </span>
            </a>
        </li>
    </ul>
</div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Akun Saya
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('identitas.index') }}" 
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('identitas.index') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Identitas Lengkap</span>
                    </a>
                </li>

                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3.5 py-2.5 text-sm font-bold rounded-xl text-rose-600 hover:bg-rose-50/60 transition-all group">
                            <svg class="w-5 h-5 mr-3 text-rose-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar Sistem</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>

    <div class="mt-auto pt-4 border-t border-slate-100">
        <div class="p-3 bg-gradient-to-br from-emerald-50/40 to-slate-50 rounded-xl border border-emerald-50/50 text-center">
            <span class="block text-[10px] font-bold text-emerald-900 tracking-wider uppercase">Portal Peserta</span>
        </div>
    </div>
</aside>