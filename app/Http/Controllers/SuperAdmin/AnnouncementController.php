<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index() {
        $announcements = Announcement::latest()->paginate(10);
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
}
