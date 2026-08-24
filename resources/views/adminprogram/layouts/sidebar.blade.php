<aside :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen}"
       class="fixed inset-y-0 left-0 z-40 w-64 pt-20 pb-6 px-4 transition-transform transform bg-white border-r border-slate-100 lg:translate-x-0 lg:static lg:inset-0 min-h-[calc(100vh-4rem)] flex flex-col justify-between group-data-[sidebar=collapsed]:w-20"
       x-cloak>

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
                <li>
                    <a href="{{ route('adminprogram.certificates.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('adminprogram.certificates.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('adminprogram.certificates.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Sertifikat &amp; Piagam</span>
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
    </div>

    <div class="mt-auto pt-4 border-t border-slate-100">
        <div class="p-3 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200 text-center">
            <span class="block text-[10px] font-extrabold text-slate-700 tracking-wider uppercase">Otoritas Sistem</span>
            <span class="block text-[10px] font-semibold text-emerald-700 mt-0.5">Maju &bull; Admin Program</span>
        </div>
    </div>
</aside>
