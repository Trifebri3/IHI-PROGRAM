<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\DiscussionReaction;
use App\Models\DiscussionReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperForumController extends Controller
{
    public function index(Request $request)
    {
        // 1. KPI Metrik Forum
        $totalDiscussions = Discussion::count();
        $totalComments = DiscussionComment::count();
        $totalReactions = DiscussionReaction::count();
        $totalShares = (int) Discussion::sum('shares_count');
        $pendingReports = DiscussionReport::where('status', 'pending')->count();
        $restrictedUsersCount = User::where('is_forum_restricted', true)->count();
        $blockedUsersCount = User::where('is_blocked', true)->count();

        // 2. Trending Topics (Top 5 berdasarkan engagement)
        $trendingDiscussions = Discussion::with(['user.profile', 'user.verification'])
            ->withCount(['reactions', 'comments', 'reposts'])
            ->get()
            ->map(function ($d) {
                // Formula Skor Tren Sosial
                $d->engagement_score = ($d->reactions_count * 2)
                    + ($d->comments_count * 3)
                    + ($d->reposts_count * 3)
                    + ((int) $d->shares_count * 2);
                return $d;
            })
            ->sortByDesc('engagement_score')
            ->take(5);

        // 3. Laporan Pengguna (dengan filter status)
        $reportStatus = $request->get('report_status', 'all');
        $reportsQuery = DiscussionReport::with(['user.profile', 'discussion.user.profile', 'actionAdmin'])->latest();

        if ($reportStatus !== 'all') {
            $reportsQuery->where('status', $reportStatus);
        }

        $reports = $reportsQuery->paginate(8, ['*'], 'reports_page')->withQueryString();

        // 4. Daftar Semua Diskusi (Searchable)
        $searchDiscussion = $request->get('search_discussion');
        $discussionsQuery = Discussion::with(['user.profile', 'user.verification'])
            ->withCount(['comments', 'reactions', 'reports', 'reposts'])
            ->latest();

        if ($searchDiscussion) {
            $discussionsQuery->where(function ($q) use ($searchDiscussion) {
                $q->where('title', 'like', "%{$searchDiscussion}%")
                  ->orWhere('content', 'like', "%{$searchDiscussion}%")
                  ->orWhereHas('user', function ($u) use ($searchDiscussion) {
                      $u->where('name', 'like', "%{$searchDiscussion}%")
                        ->orWhere('email', 'like', "%{$searchDiscussion}%");
                  });
            });
        }

        $discussions = $discussionsQuery->paginate(8, ['*'], 'discussions_page')->withQueryString();

        // 5. Komentar Terkini (Bisa di-takedown)
        $recentComments = DiscussionComment::with(['user.profile', 'discussion'])
            ->latest()
            ->take(8)
            ->get();

        // 6. Akun Terbatas & Diblokir
        $moderatedUsers = User::where('is_forum_restricted', true)
            ->orWhere('is_blocked', true)
            ->with('profile')
            ->latest()
            ->take(10)
            ->get();

        return view('superadmin.forum.index', compact(
            'totalDiscussions',
            'totalComments',
            'totalReactions',
            'totalShares',
            'pendingReports',
            'restrictedUsersCount',
            'blockedUsersCount',
            'trendingDiscussions',
            'reports',
            'reportStatus',
            'discussions',
            'searchDiscussion',
            'recentComments',
            'moderatedUsers'
        ));
    }

    // Takedown Diskusi
    public function takedownDiscussion(Request $request, $id)
    {
        $discussion = Discussion::findOrFail($id);
        $title = $discussion->title;

        // Tandai laporan terkait jika ada
        DiscussionReport::where('discussion_id', $discussion->id)->update([
            'status' => 'action_taken',
            'action_taken' => 'Diskusi di-takedown oleh admin',
            'action_taken_by' => auth()->id(),
        ]);

        $discussion->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Diskusi '{$title}' berhasil di-takedown.",
            ]);
        }

        return back()->with('success', "Diskusi '{$title}' berhasil di-takedown.");
    }

    // Takedown Komentar
    public function takedownComment(Request $request, $id)
    {
        $comment = DiscussionComment::findOrFail($id);
        $comment->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Komentar berhasil di-takedown.',
            ]);
        }

        return back()->with('success', 'Komentar berhasil di-takedown.');
    }

    // Batasi / Lepas Batasan Akun di Forum
    public function toggleRestrictUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_forum_restricted = !$user->is_forum_restricted;
        $user->save();

        $action = $user->is_forum_restricted ? 'dibatasi akses forumnya (hanya baca)' : 'dipulihkan akses forumnya';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'is_forum_restricted' => $user->is_forum_restricted,
                'message' => "Akun {$user->name} berhasil {$action}.",
            ]);
        }

        return back()->with('success', "Akun {$user->name} berhasil {$action}.");
    }

    // Blokir / Buka Blokir Akun Total
    public function toggleBlockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $action = $user->is_blocked ? 'diblokir dari seluruh sistem' : 'dibuka blokirnya';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'is_blocked' => $user->is_blocked,
                'message' => "Akun {$user->name} berhasil {$action}.",
            ]);
        }

        return back()->with('success', "Akun {$user->name} berhasil {$action}.");
    }

    // Tangani / Abaikan Laporan
    public function resolveReport(Request $request, $id)
    {
        $report = DiscussionReport::findOrFail($id);
        $status = $request->input('status', 'resolved');
        $actionTaken = $request->input('action_taken', 'Laporan ditinjau oleh admin');

        $report->update([
            'status' => $status,
            'action_taken' => $actionTaken,
            'action_taken_by' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Laporan berhasil diperbarui.');
    }
}
