<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramAnnouncement;
use App\Models\Registration;
use App\Models\User;
use App\Mail\AnnouncementBroadcastMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuperAnnouncementController extends Controller
{
    public function index()
    {
        $programs = Program::all();
        $announcements = ProgramAnnouncement::with('program')->orderBy('created_at', 'desc')->get();

        return view('superadmin.announcement.index', compact('programs', 'announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required|string', // 'global' atau ID program murni
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,instruction,warning'
        ]);

        $isGlobal = ($request->target === 'global');

        // 1. Simpan pengumuman ke database MySQL
        $announcement = ProgramAnnouncement::create([
            'program_id' => $isGlobal ? null : $request->target,
            'title' => trim($request->title),
            'content' => $request->content,
            'type' => $request->type
        ]);

        // 2. AUTOMATION BROADCAST EMAIL HTML CUSTOM BERDASARKAN TARGET CAKUPAN
        if ($isGlobal) {
            // Target Global: Ambil semua user pendaftar di aplikasi
            $users = User::all();
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->send(new AnnouncementBroadcastMail($announcement, $user->name));
                }
            }
            $msg = 'Pengumuman GLOBAL berhasil disiarkan ke seluruh user aplikasi!';
        } else {
            // Target Program: Hanya ambil peserta yang terdaftar di program tertentu
            $participants = Registration::with('user')->where('program_id', $request->target)->get();
            foreach ($participants as $part) {
                if ($part->user && $part->user->email) {
                    Mail::to($part->user->email)->send(new AnnouncementBroadcastMail($announcement, $part->user->name));
                }
            }
            $msg = 'Pengumuman berhasil disiarkan khusus ke anggota program terpilih!';
        }

        return redirect()->route('superadmin.announcements.index')->with('success', $msg);
    }

    public function destroy($id)
    {
        ProgramAnnouncement::findOrFail($id)->delete();
        return redirect()->route('superadmin.announcements.index')->with('success', 'Arsip siaran berhasil dihapus.');
    }
}
