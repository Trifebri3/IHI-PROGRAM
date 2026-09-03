<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\DiscussionReaction;
use App\Models\DiscussionFavorite;
use App\Models\DiscussionReport;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index()
    {
        $discussions = Discussion::with([
            'user.profile',
            'user.verification',
            'originalDiscussion.user.profile',
            'originalDiscussion.user.verification',
            'comments.user.profile',
            'comments.user.verification',
            'comments.parent.user',
            'reactions.user',
            'favorites',
            'reposts'
        ])->latest()->paginate(10);

        return view('forum.index', compact('discussions'));
    }

    public function storeDiscussion(Request $request)
    {
        if (auth()->user()->isForumRestricted() || auth()->user()->is_blocked) {
            return back()->with('error', 'Akun Anda sedang dibatasi dari membuat topik baru di Green Forum.');
        }

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
        if (auth()->user()->isForumRestricted() || auth()->user()->is_blocked) {
            return back()->with('error', 'Akun Anda sedang dibatasi dari mengirim komentar di Green Forum.');
        }

        $validated = $request->validate([
            'content' => 'required',
            'parent_comment_id' => 'nullable|exists:discussion_comments,id',
        ]);

        $discussion = Discussion::findOrFail($discussionId);

        $comment = DiscussionComment::create([
            'discussion_id' => $discussionId,
            'parent_comment_id' => $request->input('parent_comment_id'),
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        // 1. Kirim notifikasi ke pembuat diskusi
        if ($discussion->user_id !== auth()->id()) {
            UserNotification::create([
                'user_id' => $discussion->user_id,
                'actor_id' => auth()->id(),
                'discussion_id' => $discussion->id,
                'comment_id' => $comment->id,
                'type' => 'comment',
                'data' => ['snippet' => \Illuminate\Support\Str::limit($validated['content'], 60)],
            ]);
        }

        // 2. Kirim notifikasi jika membalas komentar tertentu
        if ($comment->parent_comment_id && $comment->parent) {
            $parentUserId = $comment->parent->user_id;
            if ($parentUserId !== auth()->id() && $parentUserId !== $discussion->user_id) {
                UserNotification::create([
                    'user_id' => $parentUserId,
                    'actor_id' => auth()->id(),
                    'discussion_id' => $discussion->id,
                    'comment_id' => $comment->id,
                    'type' => 'reply',
                    'data' => ['snippet' => \Illuminate\Support\Str::limit($validated['content'], 60)],
                ]);
            }
        }

        // 3. Kirim notifikasi jika ada sebutan @Nama
        preg_match_all('/@([A-Za-z0-9_\.\-]+)/', $validated['content'], $matches);
        if (!empty($matches[1])) {
            $mentionedNames = array_unique($matches[1]);
            foreach ($mentionedNames as $mName) {
                $mUser = User::where('name', 'like', "%{$mName}%")->first();
                if ($mUser && $mUser->id !== auth()->id() && $mUser->id !== $discussion->user_id) {
                    UserNotification::create([
                        'user_id' => $mUser->id,
                        'actor_id' => auth()->id(),
                        'discussion_id' => $discussion->id,
                        'comment_id' => $comment->id,
                        'type' => 'mention',
                        'data' => ['snippet' => \Illuminate\Support\Str::limit($validated['content'], 60)],
                    ]);
                }
            }
        }

        return back()->with('success', 'Komentar berhasil dikirim!');
    }

    public function toggleReaction(Request $request, $discussionId)
    {
        $validated = $request->validate([
            'reaction' => 'required|string|max:10',
        ]);

        $userId = auth()->id();
        $reaction = $validated['reaction'];
        $discussion = Discussion::findOrFail($discussionId);

        $existing = DiscussionReaction::where('discussion_id', $discussionId)
            ->where('user_id', $userId)
            ->where('reaction', $reaction)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            DiscussionReaction::create([
                'discussion_id' => $discussionId,
                'user_id' => $userId,
                'reaction' => $reaction,
            ]);
            $action = 'added';

            // Kirim notifikasi ke pemilik diskusi
            if ($discussion->user_id !== $userId) {
                UserNotification::create([
                    'user_id' => $discussion->user_id,
                    'actor_id' => $userId,
                    'discussion_id' => $discussion->id,
                    'type' => 'reaction',
                    'data' => ['emoji' => $reaction],
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            $discussion = Discussion::with('reactions.user')->findOrFail($discussionId);
            return response()->json([
                'status' => 'success',
                'action' => $action,
                'reactions' => $discussion->reactionSummary(),
                'total' => $discussion->reactions()->count(),
            ]);
        }

        return back();
    }

    // 1. Posting Ulang (Repost / Kutip)
    public function repostDiscussion(Request $request, $id)
    {
        $original = Discussion::findOrFail($id);
        $user = auth()->user();

        if ($user->isForumRestricted() || $user->is_blocked) {
            return back()->with('error', 'Akun Anda sedang dibatasi.');
        }

        $validated = $request->validate([
            'comment' => 'nullable|string|max:2000',
        ]);

        $comment = $validated['comment'] ?? null;
        $title = $comment ? 'Kutipan: ' . $original->title : 'Memposting ulang: ' . $original->title;
        $content = $comment ?? '';

        $repost = Discussion::create([
            'user_id' => $user->id,
            'repost_of_id' => $original->id,
            'title' => $title,
            'content' => $content,
        ]);

        // Kirim notifikasi ke pemilik diskusi asli
        if ($original->user_id !== $user->id) {
            UserNotification::create([
                'user_id' => $original->user_id,
                'actor_id' => $user->id,
                'discussion_id' => $original->id,
                'type' => 'repost',
                'data' => ['snippet' => \Illuminate\Support\Str::limit($title, 60)],
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Diskusi berhasil diposting ulang!',
                'repost_id' => $repost->id,
            ]);
        }

        return back()->with('success', 'Diskusi berhasil diposting ulang!');
    }

    // 2. Favorit (Bookmark)
    public function toggleFavorite(Request $request, $id)
    {
        $userId = auth()->id();
        Discussion::findOrFail($id); // pastikan ada

        $existing = DiscussionFavorite::where('discussion_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
            $message = 'Dihapus dari favorit';
        } else {
            DiscussionFavorite::create([
                'discussion_id' => $id,
                'user_id' => $userId,
            ]);
            $isFavorited = true;
            $message = 'Disimpan ke favorit!';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'is_favorited' => $isFavorited,
                'message' => $message,
                'total' => DiscussionFavorite::where('discussion_id', $id)->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    // 3. Laporkan Diskusi (Report)
    public function reportDiscussion(Request $request, $id)
    {
        Discussion::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DiscussionReport::create([
            'discussion_id' => $id,
            'user_id' => auth()->id(),
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan Anda telah dikirimkan ke moderator Green Forum. Terima kasih!',
            ]);
        }

        return back()->with('success', 'Laporan Anda telah dikirimkan ke tim moderasi.');
    }

    // 4. Hapus Diskusi (Delete)
    public function destroyDiscussion(Request $request, $id)
    {
        $discussion = Discussion::findOrFail($id);

        if (!$discussion->canBeDeletedBy(auth()->user())) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk menghapus diskusi ini.'], 403);
            }
            abort(403, 'Akses tidak diizinkan.');
        }

        $discussion->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Diskusi berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Diskusi berhasil dihapus.');
    }

    // 5. Tampilkan Topik Publik (Mendukung Slug dan ID dengan auto-redirect ke Slug)
    public function showPublicTopic($slugOrId)
    {
        $discussion = Discussion::with([
            'user.profile',
            'user.verification',
            'originalDiscussion.user.profile',
            'originalDiscussion.user.verification',
            'comments.user.profile',
            'comments.user.verification',
            'reactions.user',
            'reposts',
            'favorites'
        ])
        ->where('slug', $slugOrId)
        ->orWhere('id', is_numeric($slugOrId) ? (int)$slugOrId : 0)
        ->firstOrFail();

        // Jika dibuka via angka ID biasa, alihkan secara mulus ke URL Slug yang SEO & human friendly
        if (is_numeric($slugOrId) && !empty($discussion->slug)) {
            return redirect()->route('forum.public.show', $discussion->slug, 301);
        }

        return view('forum.public_show', compact('discussion'));
    }

    // 6. Catat Jumlah Share / Bagikan
    public function recordShare($id)
    {
        $discussion = Discussion::findOrFail($id);
        $discussion->increment('shares_count');

        return response()->json([
            'status' => 'success',
            'shares_count' => $discussion->shares_count,
        ]);
    }
}
