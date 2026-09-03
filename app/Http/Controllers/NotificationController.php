<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Models\Announcement;
use App\Models\ProgramAnnouncement;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Ambil daftar notifikasi pengguna lengkap (Forum + Pengumuman Resmi + Pengumuman Program)
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Notifikasi Interaksi Forum
        $forumNotifs = UserNotification::where('user_id', $user->id)
            ->with(['actor.profile', 'discussion'])
            ->latest()
            ->take(25)
            ->get();

        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();

        // 2. Pengumuman Resmi Global Platform IHI
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        // 3. Pengumuman Program Khusus (bagi program yang diikuti)
        $registeredProgramIds = $user->registrations()
            ->pluck('program_id')
            ->filter()
            ->unique();

        $programAnnouncements = ProgramAnnouncement::whereIn('program_id', $registeredProgramIds)
            ->with('program')
            ->latest()
            ->take(5)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            $formattedForum = $forumNotifs->map(function ($n) {
                $targetSlug = $n->discussion?->slug ?? $n->discussion_id;
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'category' => 'forum',
                    'actor_name' => $n->actor?->name ?? 'Pengguna',
                    'actor_avatar' => $n->actor?->profile?->profile_photo_path ? asset('storage/' . $n->actor->profile->profile_photo_path) : ($n->actor?->avatar ?? null),
                    'actor_initials' => strtoupper(substr($n->actor?->name ?? 'U', 0, 2)),
                    'message' => $n->formatted_message,
                    'discussion_id' => $n->discussion_id,
                    'url' => $targetSlug ? route('forum.public.show', $targetSlug) : '#',
                    'is_read' => !is_null($n->read_at),
                    'created_at' => $n->created_at->diffForHumans(null, true),
                    'timestamp' => $n->created_at->timestamp,
                ];
            });

            $formattedAnnouncements = $announcements->map(function ($a) {
                return [
                    'id' => 'announcement_' . $a->id,
                    'type' => 'announcement',
                    'category' => 'pengumuman',
                    'actor_name' => 'Pengumuman Resmi IHI',
                    'actor_avatar' => asset('images/logo.webp'),
                    'actor_initials' => 'IHI',
                    'message' => $a->title . ($a->description ? ' — ' . \Illuminate\Support\Str::limit(strip_tags($a->description), 75) : ''),
                    'discussion_id' => null,
                    'url' => url('/'),
                    'is_read' => false,
                    'created_at' => $a->created_at ? $a->created_at->diffForHumans(null, true) : 'Baru',
                    'timestamp' => $a->created_at ? $a->created_at->timestamp : time(),
                ];
            });

            $formattedProgAnnouncements = $programAnnouncements->map(function ($pa) {
                return [
                    'id' => 'program_announcement_' . $pa->id,
                    'type' => 'program_announcement',
                    'category' => 'pengumuman',
                    'actor_name' => 'Pengumuman: ' . ($pa->program?->name ?? 'Program'),
                    'actor_avatar' => asset('images/logo.webp'),
                    'actor_initials' => 'PRG',
                    'message' => $pa->title . ($pa->content ? ' — ' . \Illuminate\Support\Str::limit(strip_tags($pa->content), 75) : ''),
                    'discussion_id' => null,
                    'url' => route('programs.internal.dashboard', $pa->program_id),
                    'is_read' => false,
                    'created_at' => $pa->created_at ? $pa->created_at->diffForHumans(null, true) : 'Baru',
                    'timestamp' => $pa->created_at ? $pa->created_at->timestamp : time(),
                ];
            });

            // Gabungkan dan urutkan berdasarkan waktu terkini
            $all = $formattedForum->concat($formattedAnnouncements)->concat($formattedProgAnnouncements)->sortByDesc('timestamp')->values();

            return response()->json([
                'status' => 'success',
                'unread_count' => $unreadCount,
                'notifications' => $all,
            ]);
        }

        return view('notifications.index', compact('forumNotifs', 'announcements', 'programAnnouncements', 'unreadCount'));
    }

    // Tandai satu notifikasi sebagai telah dibaca
    public function markAsRead(Request $request, $id)
    {
        if (str_starts_with($id, 'announcement_') || str_starts_with($id, 'program_announcement_')) {
            return response()->json(['status' => 'success']);
        }

        $notification = UserNotification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'unread_count' => auth()->user()->unreadNotificationsCount(),
            ]);
        }

        if ($notification->discussion_id) {
            $slugOrId = $notification->discussion?->slug ?? $notification->discussion_id;
            return redirect()->route('forum.public.show', $slugOrId);
        }

        return back();
    }

    // Tandai semua notifikasi telah dibaca
    public function markAllAsRead(Request $request)
    {
        UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    // Ambil jumlah belum dibaca secara cepat (Poller)
    public function unreadCount()
    {
        return response()->json([
            'unread_count' => auth()->check() ? auth()->user()->unreadNotificationsCount() : 0,
        ]);
    }
}
