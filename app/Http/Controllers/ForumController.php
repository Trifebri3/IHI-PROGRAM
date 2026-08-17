<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index()
    {
        $discussions = Discussion::with(['user', 'comments.user'])->latest()->get();
        return view('forum.index', compact('discussions'));
    }

    public function storeDiscussion(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        Discussion::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Diskusi berhasil dikirim!');
    }

    public function storeComment(Request $request, $discussionId)
    {
        $validated = $request->validate([
            'content' => 'required',
        ]);

        DiscussionComment::create([
            'discussion_id' => $discussionId,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Komentar berhasil dikirim!');
    }
}
