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
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-[10px] text-slate-500 border">
                        {{ substr($discussion->user->name, 0, 1) }}
                    </div>

                    <div class="max-w-[80%] p-3 rounded-2xl shadow-sm text-sm {{ $discussion->user_id === auth()->id() ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-white border rounded-bl-none' }}">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-[10px] font-bold opacity-75">{{ $discussion->user->name }}</span>
                            @if($discussion->user->isVerifiedAccount())
                                <span class="text-[10px] text-sky-400">✔</span>
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
