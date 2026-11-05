<!-- Mobile Backdrop Overlay -->
<div x-show="mobileSidebarOpen" 
     @click="mobileSidebarOpen = false" 
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
     style="display: none;"></div>

<aside :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen}"
       class="fixed inset-y-0 left-0 z-50 w-72 pt-4 pb-6 px-4 transition-transform duration-300 ease-in-out bg-white border-r border-slate-100 lg:translate-x-0 lg:static lg:inset-0 lg:pt-20 min-h-[calc(100vh-4rem)] flex flex-col justify-between shadow-2xl lg:shadow-none group-data-[sidebar=collapsed]:w-20"
       x-cloak>

    <!-- Mobile Drawer Header with Close Button -->
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 lg:hidden">
        <div class="flex items-center space-x-2">
            <img src="{{ asset('images/logo.webp') }}" alt="Logo PROGRAM" class="h-10 w-auto object-contain">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Admin Program</span>
        </div>
        <button type="button" @click="mobileSidebarOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="space-y-7 flex-1 overflow-y-auto pr-1">

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Main Desk
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('dashboard') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                        </svg>
                        <span>Dashboard Admin</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Delegasi Program
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('adminprogram.programs.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.programs.index') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.programs.index') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Program Kerja Saya</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('adminprogram.workspace_monitor') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.workspace_monitor') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.workspace_monitor') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m3.25-7a3 3 0 11-6 0 3 3 0 016 0zm9.75 7v-2a4 4 0 00-3-3.87m-1.12-1.13a3 3 0 116 0M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                        <span>Workspace Monitor</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Database Program
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('adminprogram.participants.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.participants.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.participants.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Database Peserta</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Modul Alumni
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('adminprogram.alumni.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.alumni.index') || request()->routeIs('adminprogram.alumni.templates') || request()->routeIs('adminprogram.alumni.verifications') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.alumni.index') || request()->routeIs('adminprogram.alumni.templates') || request()->routeIs('adminprogram.alumni.verifications') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/>
                        </svg>
                        <span>Alumni Management</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2.5">
                Piagam &amp; Sertifikasi
            </span>
            <ul class="space-y-1">
                @php
                    $managedPrograms = auth()->user()->managedPrograms ?? collect();
                @endphp
                @if($managedPrograms->count() === 1)
                    @php $singleProg = $managedPrograms->first(); @endphp
                    <li>
                        <a href="{{ route('adminprogram.piagam.generator.index', $singleProg->id) }}"
                           class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->is('adminprogram/programs/' . $singleProg->id . '/piagam*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                            <svg class="w-5 h-5 mr-3 transition-colors {{ request()->is('adminprogram/programs/' . $singleProg->id . '/piagam*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                            <span>Generator Piagam</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('adminprogram.piagam.grades.index', $singleProg->id) }}"
                           class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.piagam.grades.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                            <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.piagam.grades.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Pengisian Nilai / Formulir</span>
                        </a>
                    </li>
                @elseif($managedPrograms->count() > 1)
                    @foreach($managedPrograms as $mp)
                        <li>
                            <a href="{{ route('adminprogram.piagam.generator.index', $mp->id) }}"
                               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->is('adminprogram/programs/' . $mp->id . '/piagam*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                                <svg class="w-5 h-5 mr-3 transition-colors {{ request()->is('adminprogram/programs/' . $mp->id . '/piagam*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                <span class="truncate">{{ $mp->name }}</span>
                            </a>
                        </li>
                    @endforeach
                @else
                    <li class="px-3.5 py-2 text-slate-400 italic text-[11px] leading-relaxed bg-slate-50 rounded-xl border border-dashed border-slate-100">
                        Belum ada program kerja yang dikelola.
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="mt-auto pt-4 border-t border-slate-100">
        <div class="p-3 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200 text-center">
            <span class="block text-[10px] font-extrabold text-slate-700 tracking-wider uppercase">Otoritas Sistem</span>
            <span class="block text-[10px] font-semibold text-emerald-700 mt-0.5">Maju &bull; Admin Program</span>
        </div>
    </div>
</aside>
