<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PublicHighlight;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicHighlightController extends Controller
{
    public function index()
    {
        $highlights = PublicHighlight::latest()->paginate(10);
        return view('superadmin.public-highlights.index', compact('highlights'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image|max:3072',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:500',
            'theme' => 'required|in:light,dark'
        ], [
            'title.required' => 'Judul / Platform wajib diisi.',
            'content.required' => 'Isi sorotan wajib diisi.',
            'banner.image' => 'Banner harus berupa file gambar (jpg, png).',
            'banner.max' => 'Ukuran file banner maksimal 3MB.',
            'link_url.url' => 'Format link URL tidak valid (harus diawali http:// atau https://).'
        ]);

        unset($data['banner']);

        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('public_highlights', 'public');
        }

        $data['is_active'] = true;

        $highlight = PublicHighlight::create($data);

        // Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_highlight',
            'details' => 'Membuat sorotan kegiatan baru: ' . $highlight->title,
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('superadmin.public-highlights.index')
            ->with('success', 'Sorotan / Kegiatan baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $highlight = PublicHighlight::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image|max:3072',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:500',
            'theme' => 'required|in:light,dark'
        ], [
            'title.required' => 'Judul / Platform wajib diisi.',
            'content.required' => 'Isi sorotan wajib diisi.',
            'banner.image' => 'Banner harus berupa file gambar (jpg, png).',
            'banner.max' => 'Ukuran file banner maksimal 3MB.',
            'link_url.url' => 'Format link URL tidak valid (harus diawali http:// atau https://).'
        ]);

        unset($data['banner']);

        if ($request->hasFile('banner')) {
            if ($highlight->banner_path) {
                Storage::disk('public')->delete($highlight->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('public_highlights', 'public');
        }

        $highlight->update($data);

        // Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_highlight',
            'details' => 'Memperbarui data sorotan kegiatan: ' . $highlight->title,
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('superadmin.public-highlights.index')
            ->with('success', 'Sorotan / Kegiatan berhasil diperbarui!');
    }

    public function toggle($id)
    {
        $highlight = PublicHighlight::findOrFail($id);
        $highlight->update([
            'is_active' => !$highlight->is_active
        ]);

        // Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_highlight',
            'details' => 'Mengubah status keaktifan sorotan: ' . $highlight->title . ' menjadi ' . ($highlight->is_active ? 'Aktif' : 'Nonaktif'),
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('superadmin.public-highlights.index')
            ->with('success', 'Status keaktifan sorotan berhasil diubah!');
    }

    public function destroy($id)
    {
        $highlight = PublicHighlight::findOrFail($id);
        $title = $highlight->title;
        
        if ($highlight->banner_path) {
            Storage::disk('public')->delete($highlight->banner_path);
        }

        $highlight->delete();

        // Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_highlight',
            'details' => 'Menghapus sorotan kegiatan: ' . $title,
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('superadmin.public-highlights.index')
            ->with('success', 'Sorotan / Kegiatan berhasil dihapus!');
    }
}
