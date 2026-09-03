<aside :class="{'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen}"
       class="fixed inset-y-0 left-0 z-40 w-64 pt-24 pb-6 px-4 transition-transform transform bg-white border-r border-slate-100 lg:translate-x-0 lg:static lg:inset-0 min-h-[calc(100vh-4rem)] flex flex-col justify-between group-data-[sidebar=collapsed]:w-20"
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
                Governance
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('superadmin.programs.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.programs.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.programs.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Master Program</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.form-builder.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.form-builder.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.form-builder.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Form Biodata Pusat</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.public-highlights.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.public-highlights.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.public-highlights.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v4M19 20a2 2 0 002-2v-5a2 2 0 00-2-2h-3m3 9v-9m0 0l-3-3m3 3l3-3"/>
                        </svg>
                        <span>Sorotan & Kegiatan</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.power-panel.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.power-panel.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <span class="text-sm mr-3 flex-shrink-0">⚡</span>
                        <span>Super Power Panel</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.system-intelligence.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.system-intelligence.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.system-intelligence.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="flex items-center gap-1.5">
                            System Intelligence
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.forum.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.forum.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.forum.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>Green Forum Desk</span>
                        </div>
                        @php
                            $pendingReportsCount = \App\Models\DiscussionReport::where('status', 'pending')->count();
                        @endphp
                        @if($pendingReportsCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-black bg-rose-500 text-white rounded-full animate-pulse">
                                {{ $pendingReportsCount }}
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
                Optimisasi &amp; Pertahanan
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('superadmin.optimization.index') }}"
                       class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all group {{ request()->routeIs('superadmin.optimization.*') ? 'bg-gradient-to-r from-emerald-50 to-emerald-100/30 text-emerald-900 border-l-4 border-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-800' }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ request()->routeIs('superadmin.optimization.*') ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Optimisasi Admin</span>
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <span class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-emerald-800/50 block mb-2.5">
                Access Gate
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="#" class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl text-slate-400 bg-slate-50/50 border border-slate-100 cursor-not-allowed group justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <span>SSO Clients</span>
                        </div>
                        <span class="text-[9px] font-bold tracking-wider bg-slate-200/70 text-slate-600 px-2 py-0.5 rounded-md uppercase">OAuth2</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <div class="mt-auto pt-4 border-t border-slate-100">
        <div class="p-3 bg-gradient-to-br from-emerald-50/40 to-slate-50 rounded-xl border border-emerald-50/50 text-center">
            <span class="block text-[10px] font-bold text-emerald-900 tracking-wider uppercase">Identity Provider</span>
            <span class="block text-[10px] font-medium text-slate-400 mt-0.5">Engine v1.0 &bull; Secure Cast</span>
        </div>
    </div>
</aside>
