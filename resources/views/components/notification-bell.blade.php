@auth
<div class="relative inline-flex items-center" x-data="{
    notifOpen: false,
    notifications: [],
    unreadCount: {{ auth()->user()->unreadNotificationsCount() }},
    loading: false,
    activeFilter: 'all',

    fetchNotifications() {
        this.loading = true;
        fetch('{{ route('notifications.index') }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            }
            this.loading = false;
        })
        .catch(err => {
            console.error(err);
            this.loading = false;
        });
    },

    filteredNotifications() {
        if (this.activeFilter === 'pengumuman') {
            return this.notifications.filter(n => n.category === 'pengumuman');
        }
        if (this.activeFilter === 'forum') {
            return this.notifications.filter(n => n.category === 'forum');
        }
        return this.notifications;
    },

    markAllRead() {
        fetch('{{ route('notifications.markAllRead') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                this.unreadCount = 0;
                this.notifications.forEach(n => n.is_read = true);
            }
        })
        .catch(err => console.error(err));
    },

    openNotification(notif) {
        if (!notif.is_read && notif.category === 'forum') {
            fetch(`/notifications/${notif.id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            notif.is_read = true;
            if (this.unreadCount > 0) this.unreadCount--;
        }

        if (notif.url && notif.url !== '#') {
            window.location.href = notif.url;
        }
    }
}"
x-init="
    // Polling unread count otomatis setiap 45 detik
    setInterval(() => {
        fetch('{{ route('notifications.unreadCount') }}')
            .then(r => r.json())
            .then(d => unreadCount = d.unread_count)
            .catch(() => {});
    }, 45000);
">

    <!-- Tombol Lonceng Notifikasi (Murni SVG Tanpa Emotikon) -->
    <button type="button"
            @click="notifOpen = !notifOpen; if(notifOpen) fetchNotifications();"
            class="relative p-2 rounded-full text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none cursor-pointer"
            title="Notifikasi & Pengumuman">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        <!-- Badge Counter -->
        <span x-show="unreadCount > 0"
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              style="display: none;"
              class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-black text-white bg-rose-600 rounded-full border-2 border-white shadow-xs animate-in zoom-in">
        </span>
    </button>

    <!-- Dropdown Panel Notifikasi -->
    <div x-show="notifOpen"
         @click.away="notifOpen = false"
         style="display: none;"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white/98 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 z-50 overflow-hidden text-slate-800">

        <!-- Header Panel & Filter Tabs -->
        <div class="p-3.5 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm">Notifikasi & Pengumuman</span>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount + ' baru'"
                          class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800"></span>
                </div>
                <button type="button"
                        x-show="unreadCount > 0"
                        @click="markAllRead()"
                        class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                    Tandai dibaca
                </button>
            </div>

            <!-- Filter Tabs (Semua / Pengumuman / Forum) -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-200/60 rounded-xl text-[11px] font-bold">
                <button type="button"
                        @click="activeFilter = 'all'"
                        class="flex-1 py-1 rounded-lg transition"
                        :class="activeFilter === 'all' ? 'bg-white text-slate-900 shadow-3xs' : 'text-slate-500 hover:text-slate-800'">
                    Semua
                </button>
                <button type="button"
                        @click="activeFilter = 'pengumuman'"
                        class="flex-1 py-1 rounded-lg transition"
                        :class="activeFilter === 'pengumuman' ? 'bg-white text-emerald-800 shadow-3xs' : 'text-slate-500 hover:text-slate-800'">
                    Pengumuman
                </button>
                <button type="button"
                        @click="activeFilter = 'forum'"
                        class="flex-1 py-1 rounded-lg transition"
                        :class="activeFilter === 'forum' ? 'bg-white text-emerald-800 shadow-3xs' : 'text-slate-500 hover:text-slate-800'">
                    Forum
                </button>
            </div>
        </div>

        <!-- Daftar Notifikasi -->
        <div class="max-h-84 overflow-y-auto divide-y divide-slate-100">
            <template x-if="loading && notifications.length === 0">
                <div class="p-8 text-center text-xs text-slate-400">
                    <svg class="animate-spin w-5 h-5 mx-auto mb-2 text-emerald-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Memuat notifikasi...</span>
                </div>
            </template>

            <template x-if="!loading && filteredNotifications().length === 0">
                <div class="p-8 text-center">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <div class="text-xs font-bold text-slate-700">Belum Ada Notifikasi</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pengumuman dan aktivitas terbaru akan tampil di sini.</p>
                </div>
            </template>

            <template x-for="item in filteredNotifications()" :key="item.id">
                <div @click="openNotification(item)"
                     class="p-3 hover:bg-slate-50 transition cursor-pointer flex items-start gap-2.5"
                     :class="!item.is_read ? 'bg-emerald-50/40' : ''">

                    <!-- Avatar / Logo Aktor -->
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 border border-slate-200 ring-1 ring-white shadow-2xs flex-shrink-0 mt-0.5 relative flex items-center justify-center">
                        <template x-if="item.actor_avatar">
                            <img :src="item.actor_avatar" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!item.actor_avatar">
                            <div class="w-full h-full bg-emerald-700 text-white font-black text-[10px] flex items-center justify-center" x-text="item.actor_initials"></div>
                        </template>
                    </div>

                    <!-- Isi Pesan Notifikasi -->
                    <div class="flex-1 min-w-0 text-xs">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <strong class="font-extrabold text-slate-950" x-text="item.actor_name"></strong>

                            <!-- Kategori Badge (Murni SVG) -->
                            <template x-if="item.category === 'pengumuman'">
                                <span class="px-1.5 py-0.2 rounded-md bg-amber-50 text-amber-800 text-[9px] font-black border border-amber-200 inline-flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.38-.09-2.08-.09H7.5a4.5 4.5 0 110-9h.76c.7 0 1.392-.03 2.08-.09 1.745-.152 3.513-.423 5.29-.817.568-.125 1.12.28 1.12.861v12.59c0 .58-.552.986-1.12.861-1.777-.394-3.545-.665-5.29-.817z"/></svg>
                                    <span>Pengumuman</span>
                                </span>
                            </template>
                        </div>

                        <p class="text-slate-700 leading-snug mt-0.5 font-normal" x-text="item.message"></p>
                        <span class="text-[10px] text-slate-400 mt-1 block" x-text="item.created_at"></span>
                    </div>

                    <!-- Titik Belum Dibaca -->
                    <div x-show="!item.is_read" class="w-2 h-2 rounded-full bg-emerald-600 flex-shrink-0 mt-2"></div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center flex items-center justify-around text-[11px] font-bold">
            <a href="{{ route('forum.index') }}" class="text-emerald-700 hover:text-emerald-800">
                Buka Green Forum
            </a>
            <span class="text-slate-300">•</span>
            <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">
                Dashboard Peserta
            </a>
        </div>
    </div>
</div>
@endauth
