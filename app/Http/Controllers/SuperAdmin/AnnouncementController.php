<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index() {
        $announcements = Announcement::with('views.user')->latest()->paginate(10);
        return view('superadmin.announcements.index', compact('announcements'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'banner' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('announcements', 'public');
        }

        Announcement::create($data);
        return back()->with('success', 'Banner berhasil dipasang!');
    }

    public function toggle($id) {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_active' => !$announcement->is_active]);
        return back()->with('success', 'Status diubah!');
    }

    public function destroy($id) {
        $announcement = Announcement::findOrFail($id);
        if ($announcement->banner_path) {
            Storage::disk('public')->delete($announcement->banner_path);
        }
        $announcement->delete();
        return back()->with('success', 'Banner berhasil dihapus!');
    }

    public function trackView($id) {
        $announcement = Announcement::findOrFail($id);
        $userId = auth()->id();

        if ($userId) {
            $view = \App\Models\AnnouncementView::firstOrNew([
                'announcement_id' => $announcement->id,
                'user_id' => $userId,
            ]);
            $view->views_count = $view->exists ? ($view->views_count + 1) : 1;
            $view->last_viewed_at = now();
            $view->save();
        }

        return response()->json(['success' => true]);
    }
}
