@php
    $currentUser = auth()->user();
    $reactionEmojis = [
        '👍' => 'Suka',
        '❤️' => 'Cinta',
        '🔥' => 'Keren',
        '💡' => 'Inspiratif',
        '👏' => 'Apresiasi',
        '😂' => 'Lucu'
    ];
@endphp

@extends('forum.layout')

@section('content')
<div class="space-y-4" x-data="{
    repostModalOpen: false,
    repostId: null,
    repostTitle: '',
    repostAuthor: '',
    reportModalOpen: false,
    reportId: null,
    reportReason: 'Spam atau promosi tidak relevan',
    reportNotes: ''
}">

    <!-- Peringatan jika Akun Sedang Dibatasi -->
    @if($currentUser->isForumRestricted())
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 text-xs font-semibold flex items-center gap-3 shadow-xs">
            <span class="p-2 rounded-xl bg-amber-100 text-amber-700 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </span>
            <div>
                <div class="font-bold text-amber-950">Akses Forum Dibatasi (Read-Only)</div>
                <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">Akun Anda sedang dibatasi oleh moderator sistem sehingga hanya dapat membaca dan tidak dapat membuat postingan baru atau membalas diskusi.</p>
            </div>
        </div>
    @endif

    <!-- Composer Quick Box (Green Forum Card) -->
    <div class="bg-white rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] p-4 transition-all hover:border-slate-300">
        <div class="flex items-center gap-3">
            <!-- Avatar Pengguna -->
            <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200 ring-2 ring-white shadow-xs relative">
                @if($currentUser->profile?->profile_photo_path)
                    <img src="{{ asset('storage/' . $currentUser->profile->profile_photo_path) }}"
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none;" class="w-full h-full bg-emerald-700 text-white font-black text-xs items-center justify-center">
                        {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                    </div>
                @elseif($currentUser->avatar)
                    <img src="{{ $currentUser->avatar }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-emerald-700 flex items-center justify-center font-black text-white text-xs">
                        {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <!-- Fake input trigger (Buka modal untuk posting cepat) -->
            <button type="button"
                    @click="createModalOpen = true"
                    class="flex-1 text-left py-2 px-3.5 bg-slate-50 hover:bg-slate-100/80 rounded-full text-xs text-slate-400 font-medium transition cursor-pointer border border-transparent hover:border-slate-200">
                Ada yang ingin dibahas hari ini? Mulai topik...
            </button>

            <!-- Tombol Aksi Plus / Posting dengan SVG Murni (Tanpa Emot) -->
            <button type="button"
                    @click="createModalOpen = true"
                    class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs px-4 py-2 rounded-full transition shadow-xs flex-shrink-0 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span class="hidden sm:inline">Posting</span>
            </button>
        </div>
    </div>

    <!-- Feed Daftar Threads -->
    <div class="space-y-4" id="threads-feed-container">
        @forelse($discussions as $d)
            @php
                $reactionsSummary = $d->reactionSummary();
                $totalReactions = $d->reactions->count();
                $isMine = ($d->user_id === auth()->id());
                $hasComments = ($d->comments->count() > 0);
                $isFavorited = $d->isFavoritedBy();
                $isReposted = $d->isRepostedBy();
                $isRepostItem = ($d->repost_of_id && $d->originalDiscussion);
            @endphp

            <!-- Thread Card -->
            <article class="thread-item bg-white rounded-[26px] border border-slate-200/80 shadow-[0_4px_24px_-6px_rgba(0,0,0,0.03)] p-4 sm:p-5 transition-all hover:border-slate-300 relative group"
                     data-mine="{{ $isMine ? '1' : '0' }}"
                     data-favorited="{{ $isFavorited ? '1' : '0' }}"
                     id="discussion-{{ $d->id }}">

                <!-- Jika ini adalah Posting Ulang (Repost) -->
                @if($isRepostItem)
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 mb-2.5 pl-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" />
                        </svg>
                        <span>{{ $d->user->name }} memposting ulang</span>
                    </div>
                @endif

                <!-- Main Thread Layout: Left Column (Avatar) & Right Column (Content) -->
                <div class="flex items-stretch gap-3">

                    <!-- Left Column: Avatar & Continuous Vertical Threadline -->
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 border border-slate-200 ring-2 ring-white shadow-xs relative flex-shrink-0">
                            @if($d->user->profile?->profile_photo_path)
                                <img src="{{ asset('storage/' . $d->user->profile->profile_photo_path) }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none;" class="w-full h-full bg-emerald-700 text-white font-black text-xs items-center justify-center">
                                    {{ strtoupper(substr($d->user->name, 0, 2)) }}
                                </div>
                            @elseif($d->user->avatar)
                                <img src="{{ $d->user->avatar }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-emerald-700 flex items-center justify-center font-black text-white text-xs">
                                    {{ strtoupper(substr($d->user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>

                        @if($hasComments)
                            <div class="w-[2px] bg-slate-200/90 flex-1 my-2 rounded-full"></div>
                        @endif
                    </div>

                    <!-- Right Column: Content & Actions -->
                    <div class="flex-1 min-w-0">

                        <!-- Author Header Row -->
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                <span class="font-extrabold text-slate-900 text-xs sm:text-sm truncate">
                                    {{ $d->user->name }}
                                </span>

                                @if($d->user->isVerifiedAccount())
                                    <span class="inline-flex items-center text-sky-500 flex-shrink-0" title="Akun Terverifikasi Resmi">
                                        <svg class="w-4 h-4 fill-current drop-shadow-xs" viewBox="0 0 24 24" aria-label="Terverifikasi">
                                            <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                @endif

                                @if($d->user->hasRole('Super Admin'))
                                    <span class="px-1.5 py-0.2 rounded-md bg-purple-50 text-purple-700 text-[9px] font-black border border-purple-200">Admin</span>
                                @elseif($d->user->hasRole('Admin Program'))
                                    <span class="px-1.5 py-0.2 rounded-md bg-emerald-50 text-emerald-700 text-[9px] font-black border border-emerald-200">Pengelola</span>
                                @endif

                                <span class="text-slate-400 text-[11px] font-normal">• {{ $d->created_at->diffForHumans(null, true) }}</span>
                            </div>

                            <!-- More Action Menu Dropdown (3 Dots: Salin Tautan, Laporkan, Hapus) -->
                            <div class="relative" x-data="{ menuOpen: false }">
                                <button type="button"
                                        @click="menuOpen = !menuOpen"
                                        class="p-1 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors active:scale-95"
                                        title="Opsi Diskusi">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1.5"/><circle cx="6" cy="12" r="1.5"/><circle cx="18" cy="12" r="1.5"/></svg>
                                </button>

                                <div x-show="menuOpen"
                                     @click.away="menuOpen = false"
                                     style="display: none;"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     class="absolute right-0 mt-1 w-44 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/90 p-1.5 z-30 text-xs font-semibold">

                                    <!-- Salin Tautan -->
                                    <button type="button"
                                            @click="copyThreadLink('{{ $d->id }}', '{{ $d->slug ?? $d->id }}'); menuOpen = false;"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 text-left transition">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                        <span>Salin Tautan</span>
                                    </button>

                                    <!-- Laporkan Diskusi -->
                                    <button type="button"
                                            @click="reportId = {{ $d->id }}; reportModalOpen = true; menuOpen = false;"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-amber-700 hover:bg-amber-50 text-left transition">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a1.125 1.125 0 00.864-1.091V5.518a1.125 1.125 0 00-1.378-1.096l-2.6.612a9 9 0 01-6.086-.71l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg>
                                        <span>Laporkan</span>
                                    </button>

                                    <!-- Hapus Diskusi (Hanya jika author atau admin) -->
                                    @if($d->canBeDeletedBy())
                                        <div class="my-1 border-t border-slate-100"></div>
                                        <button type="button"
                                                @click="deleteDiscussion({{ $d->id }}); menuOpen = false;"
                                                class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 text-left transition font-bold">
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            <span>Hapus Diskusi</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Thread Title & Body Text -->
                        <div class="space-y-1 mb-2">
                            <h2 class="text-xs sm:text-sm font-black text-slate-950 leading-snug tracking-tight">
                                {{ $d->title }}
                            </h2>
                            @if($d->content)
                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line font-normal">
                                    {{ $d->content }}
                                </p>
                            @endif
                        </div>

                        <!-- Embedded Box Jika Diskusi Ini Memposting Ulang Diskusi Lain -->
                        @if($isRepostItem)
                            <div class="mt-2.5 mb-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-200/90 text-xs">
                                <div class="flex items-center gap-1.5 mb-1 text-slate-700">
                                    <span class="font-bold text-[11px]">{{ $d->originalDiscussion->user->name }}</span>
                                    @if($d->originalDiscussion->user->isVerifiedAccount())
                                        <span class="text-sky-500">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @endif
                                    <span class="text-slate-400 text-[10px]">• {{ $d->originalDiscussion->created_at->diffForHumans(null, true) }}</span>
                                </div>
                                <p class="font-bold text-slate-900 text-xs mb-0.5">{{ $d->originalDiscussion->title }}</p>
                                <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed">{{ $d->originalDiscussion->content }}</p>
                            </div>
                        @endif

                        <!-- Action Bar: Reaksi, Komentar, Posting Ulang, Favorit, Share (ANGKA SELALU TAMPIL) -->
                        <div class="flex items-center gap-1 -ml-1 text-slate-700 pt-1 pb-2 flex-wrap">

                            <!-- 1. Reaction / Heart Button dengan Floating Picker (Tunggal) -->
                            <div class="relative group/reaction inline-flex items-center">
                                <button type="button"
                                        onclick="sendReaction({{ $d->id }}, '❤️')"
                                        class="p-2 rounded-full hover:bg-rose-50 hover:text-rose-600 transition-all duration-150 active:scale-90 flex items-center gap-1.5 group/btn"
                                        title="Beri Reaksi">
                                    <svg class="w-4 h-4 transition-transform group-hover/btn:scale-110 {{ $d->reactions->where('user_id', auth()->id())->count() > 0 ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current' }}"
                                         stroke-width="2"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                    <span id="reaction-count-{{ $d->id }}" class="text-[11px] font-semibold text-slate-600">{{ $totalReactions }}</span>
                                </button>

                                <div class="absolute bottom-full left-0 mb-1.5 hidden group-hover/reaction:flex items-center gap-1 bg-white/95 backdrop-blur-md px-2.5 py-1.5 rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-slate-200 z-30 animate-in fade-in zoom-in-90 duration-150 pointer-events-auto">
                                    @foreach($reactionEmojis as $emoji => $label)
                                        <button type="button"
                                                onclick="sendReaction({{ $d->id }}, '{{ $emoji }}')"
                                                class="text-lg hover:scale-135 active:scale-95 transition-transform duration-150 p-1 cursor-pointer focus:outline-none"
                                                title="{{ $label }}">
                                            {{ $emoji }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 2. Comment Button -->
                            <button type="button"
                                    onclick="focusReply({{ $d->id }})"
                                    class="p-2 rounded-full hover:bg-slate-100 hover:text-slate-900 transition-all duration-150 active:scale-90 flex items-center gap-1.5"
                                    title="Balas Diskusi">
                                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.636 1.064.508 1.67-.282 1.34-1.026 2.476-1.065 2.536a.75.75 0 00.74 1.164c1.785-.205 3.328-.857 4.382-1.472.375-.219.822-.26 1.23-.128.932.298 1.916.45 2.932.45z"/>
                                </svg>
                                <span class="text-[11px] font-semibold text-slate-600">{{ $d->comments->count() }}</span>
                            </button>

                            <!-- 3. Posting Ulang (Repost Button) -->
                            <button type="button"
                                    @click="repostId = {{ $d->id }}; repostTitle = '{{ addslashes($d->title) }}'; repostAuthor = '{{ addslashes($d->user->name) }}'; repostModalOpen = true;"
                                    class="p-2 rounded-full hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-150 active:scale-90 flex items-center gap-1.5 {{ $isReposted ? 'text-emerald-600 font-bold' : '' }}"
                                    title="Posting Ulang Diskusi">
                                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" />
                                </svg>
                                <span id="repost-count-{{ $d->id }}" class="text-[11px] font-semibold text-slate-600">{{ $d->reposts->count() }}</span>
                            </button>

                            <!-- 4. Favorit (Bookmark Button) -->
                            <button type="button"
                                    onclick="toggleFavorite({{ $d->id }})"
                                    id="fav-btn-{{ $d->id }}"
                                    class="p-2 rounded-full hover:bg-amber-50 hover:text-amber-600 transition-all duration-150 active:scale-90 flex items-center gap-1.5 {{ $isFavorited ? 'text-amber-500 font-bold' : '' }}"
                                    title="{{ $isFavorited ? 'Hapus dari Favorit' : 'Simpan ke Favorit' }}">
                                <svg id="fav-icon-{{ $d->id }}"
                                     class="w-4 h-4 {{ $isFavorited ? 'fill-amber-400 stroke-amber-500' : 'fill-none stroke-current' }}"
                                     stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                </svg>
                                <span id="fav-count-{{ $d->id }}" class="text-[11px] font-semibold text-slate-600">{{ $d->favorites->count() }}</span>
                            </button>

                            <!-- 5. Bagikan (Share Button) -->
                            <button type="button"
                                    onclick="copyThreadLink('{{ $d->id }}', '{{ $d->slug ?? $d->id }}')"
                                    class="p-2 rounded-full hover:bg-slate-100 hover:text-slate-900 transition-all duration-150 active:scale-90 flex items-center gap-1.5"
                                    title="Bagikan Tautan Publik">
                                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                <span id="share-count-{{ $d->id }}" class="text-[11px] font-semibold text-slate-600">{{ $d->shares_count ?? 0 }}</span>
                            </button>
                        </div>

                        <!-- Active Reactions Pill Container -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-3 empty:hidden" id="reactions-container-{{ $d->id }}">
                            @foreach($reactionsSummary as $r)
                                <button type="button"
                                        onclick="sendReaction({{ $d->id }}, '{{ $r['reaction'] }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.8 rounded-full text-xs transition-all duration-150 border {{ $r['has_reacted'] ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-bold shadow-3xs scale-102' : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-600' }}"
                                        title="{{ implode(', ', $r['users']) }}">
                                    <span>{{ $r['reaction'] }}</span>
                                    <span class="text-[11px] font-semibold">{{ $r['count'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <!-- Thread Replies List (Dengan Indikator Balasan & Tag Mention) -->
                        @if($hasComments)
                            <div class="space-y-3 pt-2 pb-1 border-t border-slate-100">
                                @foreach($d->comments as $c)
                                    <div class="flex items-start gap-2.5 text-xs" id="comment-{{ $c->id }}">
                                        <div class="w-6 h-6 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 mt-0.5 relative">
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

                                            <!-- Indikator Jika Komentar Ini Membalas Komentar Lain -->
                                            @if($c->parent_comment_id && $c->parent)
                                                <div class="text-[10px] font-bold text-emerald-700 flex items-center gap-1 mb-1">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                                                    <span>Membalas {{ $c->parent->user->name }}</span>
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-1 mb-0.5">
                                                <span class="font-bold text-slate-900 text-[11px]">{{ $c->user->name }}</span>
                                                @if($c->user->isVerifiedAccount())
                                                    <span class="inline-flex items-center text-sky-500" title="Terverifikasi">
                                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                            <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                @endif

                                                <span class="text-slate-400 text-[10px] ml-auto">{{ $c->created_at->diffForHumans(null, true) }}</span>

                                                <!-- Tombol Balas Komentar Langsung -->
                                                <button type="button"
                                                        onclick="replyToComment({{ $d->id }}, {{ $c->id }}, '{{ addslashes($c->user->name) }}')"
                                                        class="ml-2 text-[10px] font-bold text-slate-400 hover:text-emerald-700 flex items-center gap-0.5 transition cursor-pointer"
                                                        title="Balas komentar ini">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                                                    <span>Balas</span>
                                                </button>
                                            </div>

                                            <!-- Isi Komentar dengan Highlight Mention @Nama -->
                                            <div class="text-slate-700 text-xs leading-relaxed font-normal">
                                                {!! preg_replace('/(@[A-Za-z0-9_\.\-]+)/', '<span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded-md border border-emerald-200/60">$1</span>', e($c->content)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Quick Reply Input (Dengan Dukungan Balas Komentar Tertentu) -->
                        <form action="{{ route('forum.comment.store', $d->id) }}" method="POST" class="mt-2.5 pt-2" id="reply-form-{{ $d->id }}">
                            @csrf
                            <input type="hidden" name="parent_comment_id" id="parent-comment-id-{{ $d->id }}" value="">

                            <!-- Indikator Membalas Komentar Tertentu -->
                            <div id="reply-pill-{{ $d->id }}" class="hidden items-center justify-between bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1 rounded-full text-[11px] mb-2 animate-in fade-in">
                                <span class="flex items-center gap-1 font-semibold truncate">
                                    <svg class="w-3 h-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                                    <span>Membalas <strong id="reply-target-name-{{ $d->id }}"></strong></span>
                                </span>
                                <button type="button" onclick="cancelReply({{ $d->id }})" class="text-slate-400 hover:text-slate-700 ml-2 font-bold cursor-pointer" title="Batal membalas komentar ini">&times;</button>
                            </div>

                            <div class="relative flex items-center">
                                <input type="text"
                                       id="reply-input-{{ $d->id }}"
                                       name="content"
                                       placeholder="Balas ke {{ $d->user->name }}..."
                                       class="w-full bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200/80 rounded-full pl-3.5 pr-10 py-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-300 transition shadow-2xs"
                                       required>
                                <button type="submit"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-slate-900 hover:bg-black active:scale-90 text-white flex items-center justify-center transition shadow-3xs"
                                        title="Kirim Balasan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </article>
        @empty
            <div class="bg-white p-12 text-center rounded-[28px] border border-slate-200/80 shadow-xs">
                <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.85 5.977 5.977 0 011.523-2.614C4.168 16.14 3 14.184 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                    </svg>
                </div>
                <h3 class="font-black text-slate-900 text-base mb-1">Belum Ada Diskusi</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Mulai topik diskusi pertama di Green Forum Institut Hijau Indonesia!</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Green Forum (10 per halaman) -->
    @if($discussions->hasPages())
        <div class="pt-2 pb-10">
            <div class="bg-white p-4 rounded-[22px] border border-slate-200/80 shadow-xs">
                {{ $discussions->onEachSide(1)->links() }}
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 1: POSTING ULANG (REPOST / KUTIP) -->
    <!-- ========================================================================= -->
    <div x-show="repostModalOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @keydown.escape.window="repostModalOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">

        <div class="bg-white rounded-[28px] max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200"
             @click.away="repostModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <span class="text-xs font-black uppercase tracking-wider text-slate-900">Posting Ulang Diskusi</span>
                <button type="button" @click="repostModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Preview Card Yang Direpost -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 mb-3 text-xs">
                <div class="font-bold text-slate-900" x-text="repostAuthor"></div>
                <div class="text-slate-600 line-clamp-2 mt-0.5" x-text="repostTitle"></div>
            </div>

            <form id="repost-form" @submit.prevent="submitRepost()">
                <textarea name="comment"
                          id="repost-comment"
                          rows="3"
                          placeholder="Tambahkan pemikiran atau kutipan Anda (opsional)..."
                          class="w-full text-xs sm:text-sm text-slate-800 placeholder-slate-400 border border-slate-200 rounded-2xl p-3 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-300 outline-none resize-none leading-relaxed mb-3"></textarea>

                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            @click="repostModalOpen = false"
                            class="px-4 py-2 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs px-5 py-2.5 rounded-full transition shadow-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M4.5 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M16.5 8.25l3 3.75-3 3.75M7.5 15.75l-3-3.75 3-3.75" />
                        </svg>
                        <span>Posting Ulang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: LAPORKAN DISKUSI (REPORT) -->
    <!-- ========================================================================= -->
    <div x-show="reportModalOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @keydown.escape.window="reportModalOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">

        <div class="bg-white rounded-[28px] max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200"
             @click.away="reportModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <span class="text-xs font-black uppercase tracking-wider text-rose-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a1.125 1.125 0 00.864-1.091V5.518a1.125 1.125 0 00-1.378-1.096l-2.6.612a9 9 0 01-6.086-.71l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg>
                    Laporkan Diskusi
                </span>
                <button type="button" @click="reportModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500 mb-3">Pilih alasan mengapa diskusi ini melanggar pedoman komunitas Green Forum:</p>

            <form id="report-form" @submit.prevent="submitReport()">
                <div class="space-y-2 mb-3">
                    <template x-for="r in [
                        'Spam atau promosi tidak relevan',
                        'Konten kasar, tidak pantas, atau SARA',
                        'Pelecehan atau ujaran kebencian',
                        'Informasi menyesatkan / hoax',
                        'Lainnya'
                    ]">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-800 transition">
                            <input type="radio" name="reason" :value="r" x-model="reportReason" class="text-emerald-600 focus:ring-emerald-500">
                            <span x-text="r"></span>
                        </label>
                    </template>
                </div>

                <textarea name="notes"
                          x-model="reportNotes"
                          rows="2"
                          placeholder="Keterangan tambahan untuk moderator (opsional)..."
                          class="w-full text-xs text-slate-800 placeholder-slate-400 border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-300 outline-none resize-none mb-3"></textarea>

                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            @click="reportModalOpen = false"
                            class="px-4 py-2 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs px-5 py-2.5 rounded-full transition shadow-xs">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Green Forum Interaction Scripts -->
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. AJAX Reaction Toggle
    function sendReaction(discussionId, emoji) {
        fetch(`/forum/discussion/${discussionId}/reaction`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reaction: emoji })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderReactions(discussionId, data.reactions);

                // Update total reaction count on action bar
                const reactionCountEl = document.getElementById(`reaction-count-${discussionId}`);
                if (reactionCountEl) {
                    let total = 0;
                    Object.values(data.reactions).forEach(r => total += r.count);
                    reactionCountEl.innerText = total;
                }

                showToast(data.action === 'added' ? `Reaksi ${emoji} ditambahkan!` : `Reaksi ${emoji} dihapus.`);
            }
        })
        .catch(err => console.error(err));
    }

    function renderReactions(discussionId, reactions) {
        const container = document.getElementById(`reactions-container-${discussionId}`);
        if (!container) return;

        let html = '';
        Object.values(reactions).forEach(r => {
            const activeClass = r.has_reacted
                ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-bold shadow-3xs scale-102'
                : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-600';

            const userList = (r.users || []).join(', ');

            html += `
                <button type="button"
                        onclick="sendReaction(${discussionId}, '${r.reaction}')"
                        class="inline-flex items-center gap-1 px-2.5 py-0.8 rounded-full text-xs transition-all duration-150 border ${activeClass}"
                        title="${userList}">
                    <span>${r.reaction}</span>
                    <span class="text-[11px] font-semibold">${r.count}</span>
                </button>
            `;
        });
        container.innerHTML = html;
    }

    // 2. AJAX Favorite Toggle
    function toggleFavorite(discussionId) {
        fetch(`/forum/discussion/${discussionId}/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const btn = document.getElementById(`fav-btn-${discussionId}`);
                const icon = document.getElementById(`fav-icon-${discussionId}`);
                const count = document.getElementById(`fav-count-${discussionId}`);
                const article = document.getElementById(`discussion-${discussionId}`);

                if (data.is_favorited) {
                    btn?.classList.add('text-amber-500', 'font-bold');
                    icon?.classList.add('fill-amber-400', 'stroke-amber-500');
                    icon?.classList.remove('fill-none');
                    article?.setAttribute('data-favorited', '1');
                } else {
                    btn?.classList.remove('text-amber-500', 'font-bold');
                    icon?.classList.remove('fill-amber-400', 'stroke-amber-500');
                    icon?.classList.add('fill-none');
                    article?.setAttribute('data-favorited', '0');
                }

                if (count) {
                    count.innerText = data.total;
                }

                showToast(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    // 3. Submit Repost
    function submitRepost() {
        const comment = document.getElementById('repost-comment').value;
        const alpine = Alpine.$data(document.querySelector('[x-data]'));
        const discussionId = alpine.repostId;

        fetch(`/forum/discussion/${discussionId}/repost`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ comment: comment })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alpine.repostModalOpen = false;
                document.getElementById('repost-comment').value = '';
                showToast('Diskusi berhasil diposting ulang!');
                setTimeout(() => window.location.reload(), 800);
            }
        })
        .catch(err => console.error(err));
    }

    // 4. Submit Report
    function submitReport() {
        const alpine = Alpine.$data(document.querySelector('[x-data]'));
        const discussionId = alpine.reportId;
        const reason = alpine.reportReason;
        const notes = alpine.reportNotes;

        fetch(`/forum/discussion/${discussionId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason, notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alpine.reportModalOpen = false;
                alpine.reportNotes = '';
                showToast(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    // 5. Delete Discussion
    function deleteDiscussion(discussionId) {
        if (!confirm('Apakah Anda yakin ingin menghapus diskusi ini secara permanen?')) {
            return;
        }

        fetch(`/forum/discussion/${discussionId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const article = document.getElementById(`discussion-${discussionId}`);
                if (article) {
                    article.style.transition = 'all 0.3s ease';
                    article.style.opacity = '0';
                    article.style.transform = 'scale(0.95)';
                    setTimeout(() => article.remove(), 300);
                }
                showToast(data.message);
            } else {
                alert(data.message || 'Gagal menghapus diskusi.');
            }
        })
        .catch(err => console.error(err));
    }

    // 6. Focus reply input smoothly
    function focusReply(discussionId) {
        const input = document.getElementById(`reply-input-${discussionId}`);
        if (input) {
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // 7. Reply to specific comment & mention author
    function replyToComment(discussionId, commentId, authorName) {
        const parentIdInput = document.getElementById(`parent-comment-id-${discussionId}`);
        const replyInput = document.getElementById(`reply-input-${discussionId}`);
        const pill = document.getElementById(`reply-pill-${discussionId}`);
        const targetName = document.getElementById(`reply-target-name-${discussionId}`);

        if (parentIdInput) parentIdInput.value = commentId;
        if (targetName) targetName.innerText = `@${authorName}`;
        if (pill) {
            pill.classList.remove('hidden');
            pill.classList.add('flex');
        }

        if (replyInput) {
            replyInput.value = `@${authorName} `;
            replyInput.focus();
            replyInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function cancelReply(discussionId) {
        const parentIdInput = document.getElementById(`parent-comment-id-${discussionId}`);
        const replyInput = document.getElementById(`reply-input-${discussionId}`);
        const pill = document.getElementById(`reply-pill-${discussionId}`);

        if (parentIdInput) parentIdInput.value = '';
        if (pill) {
            pill.classList.add('hidden');
            pill.classList.remove('flex');
        }
        if (replyInput && replyInput.value.startsWith('@')) {
            replyInput.value = '';
        }
    }

    // 8. Copy Public Topic Link with Toast and Live Share Counter Increment
    function copyThreadLink(discussionId, slug) {
        const identifier = slug || discussionId;
        const url = `${window.location.origin}/forum/topic/${identifier}`;
        navigator.clipboard.writeText(url).then(() => {
            showToast('Tautan topik publik disalin!');
        }).catch(() => {
            showToast('Tautan topik siap dibagikan!');
        });

        // Increment share count in DB & UI live
        fetch(`/forum/discussion/${discussionId}/share`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const countEl = document.getElementById(`share-count-${discussionId}`);
                if (countEl) countEl.innerText = data.shares_count;
            }
        })
        .catch(e => console.error(e));
    }

    // 9. Toast helper
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

    // 10. Client filter (All / Mine / Favorites)
    function filterThreads(type) {
        const cards = document.querySelectorAll('.thread-item');

        cards.forEach(card => {
            if (type === 'mine') {
                card.style.display = (card.getAttribute('data-mine') === '1') ? 'block' : 'none';
            } else if (type === 'favorites') {
                card.style.display = (card.getAttribute('data-favorited') === '1') ? 'block' : 'none';
            } else {
                card.style.display = 'block';
            }
        });
    }

    // 11. Live Search
    function liveSearchThreads(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.thread-item');

        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            if (!q || text.includes(q)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endsection
