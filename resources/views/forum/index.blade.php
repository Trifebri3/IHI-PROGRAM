@php
    // Logika pemilihan layout berdasarkan role
    $layout = 'pesertabiasa.layouts.app';
    if (auth()->user()->hasRole('Super Admin')) {
        $layout = 'superadmin.layouts.app';
    } elseif (auth()->user()->hasRole('Admin Program')) {
        $layout = 'adminprogram.layouts.app';
    }
@endphp

@extends($layout)

@section('content')
<div class="max-w-2xl mx-auto p-4 py-8">
    <div class="bg-white p-6 rounded-2xl border border-emerald-50 shadow-sm mb-8">
        <h2 class="text-sm font-black text-slate-800 mb-4 uppercase tracking-wider">Mulai Diskusi Baru</h2>
        <form action="{{ route('forum.discussion.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Judul Diskusi" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm" required>
            <textarea name="content" placeholder="Apa yang ingin dibahas?" class="w-full p-2.5 border border-slate-200 rounded-xl text-sm" rows="3" required></textarea>
            <button class="w-full bg-emerald-600 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-emerald-700 transition">Kirim Diskusi</button>
        </form>
    </div>

    @foreach($discussions as $d)
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-4 hover:border-emerald-200 transition">
            <div class="flex items-center gap-2 mb-2">
                <span class="font-bold text-slate-800 text-sm">{{ $d->user->name }}</span>
                @if($d->user->isVerifiedAccount())
                    <span class="text-sky-500 text-[10px]" title="Terverifikasi">✔</span>
                @endif
            </div>

            <h3 class="font-bold text-slate-900">{{ $d->title }}</h3>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">{{ $d->content }}</p>

            <div class="space-y-1 mb-3 pl-4 border-l-2 border-slate-100">
                @foreach($d->comments as $c)
                    <div class="text-[11px] bg-slate-50 p-2 rounded-lg">
                        <strong class="text-slate-700">{{ $c->user->name }}</strong>: {{ $c->content }}
                    </div>
                @endforeach
            </div>

            <form action="{{ route('forum.comment.store', $d->id) }}" method="POST">
                @csrf
                <input type="text" name="content" placeholder="Balas diskusi ini..." class="w-full p-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-emerald-500" required>
            </form>
        </div>
    @endforeach
</div>
@endsection
