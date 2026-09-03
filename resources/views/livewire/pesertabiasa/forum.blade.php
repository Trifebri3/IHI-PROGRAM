<?php

use Livewire\Volt\Component;
use App\Models\Discussion;
use App\Models\DiscussionComment;

new class extends Component {
    public $title, $content;
    public $comment = []; // Jadikan array untuk menampung input tiap diskusi

public function postDiscussion()
{
    $this->validate(['title' => 'required', 'content' => 'required']);

    // Debugging: Cek apakah user ID ada
    if (!auth()->check()) {
        session()->flash('error', 'Anda harus login!');
        return;
    }

    Discussion::create([
        'user_id' => auth()->id(), // Pastikan ini tidak null
        'title'   => $this->title,
        'content' => $this->content
    ]);

    $this->reset(['title', 'content']);
}

    public function postComment($discussionId)
    {
        $this->validate(['comment.'.$discussionId => 'required']);

        DiscussionComment::create([
            'discussion_id' => $discussionId,
            'user_id' => auth()->id(),
            'content' => $this->comment[$discussionId]
        ]);

        $this->comment[$discussionId] = ''; // Reset input setelah simpan
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6 font-sans pb-24">
    <div class="space-y-6" wire:poll.3s>
        @foreach(\App\Models\Discussion::latest()->get() as $discussion)
            <div class="flex flex-col {{ $discussion->user_id === auth()->id() ? 'items-end' : 'items-start' }}">
                <div class="flex items-end gap-2 {{ $discussion->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center font-bold text-[10px] text-slate-500 border border-slate-100 shadow-3xs flex-shrink-0">
                        @if($discussion->user->profile?->profile_photo_path)
                            <img src="{{ asset('storage/' . $discussion->user->profile->profile_photo_path) }}" class="w-full h-full object-cover">
                        @elseif($discussion->user->avatar)
                            <img src="{{ $discussion->user->avatar }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($discussion->user->name, 0, 1)) }}
                        @endif
                    </div>

                    <div class="max-w-[80%] p-3 rounded-2xl shadow-sm text-sm {{ $discussion->user_id === auth()->id() ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-white border rounded-bl-none' }}">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-[10px] font-bold opacity-75">{{ $discussion->user->name }}</span>
                            @if($discussion->user->isVerifiedAccount())
                                <span class="inline-flex items-center text-sky-400" title="Terverifikasi">
                                    <svg class="w-3.5 h-3.5 fill-current drop-shadow-xs" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <p class="font-bold text-[11px] mb-1 opacity-90">{{ $discussion->title }}</p>
                        <p class="text-[12px] leading-relaxed">{{ $discussion->content }}</p>
                    </div>
                </div>

                <div class="mt-2 w-[85%] {{ $discussion->user_id === auth()->id() ? 'text-right' : 'text-left' }}">
                    @foreach($discussion->comments as $c)
                        <div class="inline-block px-3 py-1 bg-slate-100 rounded-full text-[10px] my-0.5 font-medium text-slate-600">
                            <strong>{{ $c->user->name }}</strong>: {{ $c->content }}
                        </div>
                    @endforeach

                    <div class="mt-2">
                        <input type="text"
                               wire:model="comment.{{ $discussion->id }}"
                               wire:keydown.enter="postComment({{ $discussion->id }})"
                               placeholder="Balas..."
                               class="w-full bg-slate-50 border-0 rounded-full px-4 py-1.5 text-[11px] focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t shadow-lg md:relative md:bg-transparent md:border-0 md:shadow-none">
        <form wire:submit="postDiscussion" class="flex gap-2">
            <input type="text" wire:model="title" placeholder="Judul..." class="w-1/4 p-2.5 text-xs border rounded-2xl" required>
            <input type="text" wire:model="content" placeholder="Tulis pesan..." class="flex-1 p-2.5 text-xs border rounded-2xl" required>
            <button class="bg-emerald-600 text-white px-4 rounded-2xl text-xs font-bold">Kirim</button>
        </form>
    </div>
</div>
