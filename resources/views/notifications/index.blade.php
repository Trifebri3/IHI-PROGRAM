@extends('forum.layout')

@section('content')
<div class="space-y-4 max-w-xl mx-auto">
    <!-- Header Notifikasi -->
    <div class="bg-white rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] p-4 sm:p-5 flex items-center justify-between">
        <div>
            <h1 class="text-base sm:text-lg font-black text-slate-900">Notifikasi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Semua interaksi dan balasan pada topik Anda</p>
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 px-3 py-1.5 rounded-full bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <!-- List Notifikasi -->
    <div class="bg-white rounded-[26px] border border-slate-200/80 shadow-[0_4px_24px_-6px_rgba(0,0,0,0.03)] divide-y divide-slate-100 overflow-hidden">
        @forelse($notifications as $n)
            <div class="p-4 sm:p-4.5 hover:bg-slate-50 transition flex items-start gap-3 {{ is_null($n->read_at) ? 'bg-emerald-50/40' : '' }}">
                <!-- Avatar Aktor -->
                <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 border border-slate-200 ring-2 ring-white shadow-2xs flex-shrink-0 mt-0.5 relative">
                    @if($n->actor?->profile?->profile_photo_path)
                        <img src="{{ asset('storage/' . $n->actor->profile->profile_photo_path) }}" class="w-full h-full object-cover">
                    @elseif($n->actor?->avatar)
                        <img src="{{ $n->actor->avatar }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-emerald-700 text-white font-black text-xs flex items-center justify-center">
                            {{ strtoupper(substr($n->actor?->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                </div>

                <!-- Isi Pesan -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm text-slate-800 leading-snug">
                        <strong class="font-extrabold text-slate-950">{{ $n->actor?->name ?? 'Pengguna' }}</strong>
                        <span class="text-slate-600 font-normal"> {{ $n->formatted_message }}</span>
                    </p>
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ $n->created_at->diffForHumans(null, true) }}</span>
                </div>

                <!-- Aksi Buka -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if(is_null($n->read_at))
                        <div class="w-2 h-2 rounded-full bg-emerald-600" title="Belum Dibaca"></div>
                    @endif

                    @if($n->discussion_id)
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 transition" title="Buka Topik">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </div>
                <h3 class="font-black text-slate-900 text-sm mb-1">Belum Ada Notifikasi</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Interaksi, balasan diskusi, dan sebutan akun Anda akan muncul di sini secara langsung.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
