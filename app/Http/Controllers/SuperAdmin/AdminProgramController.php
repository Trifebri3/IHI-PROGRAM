<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('managers')->latest()->get();
        $adminPrograms = User::role('Admin Program')->get();
        return view('superadmin.programs.index', compact('programs', 'adminPrograms'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($request, $validated) {
            $data = $validated;
            if ($request->hasFile('logo')) $data['logo_path'] = $request->file('logo')->store('programs/logos', 'public');
            if ($request->hasFile('banner')) $data['banner_path'] = $request->file('banner')->store('programs/banners', 'public');

            $program = Program::create($data);
            $program->managers()->attach($request->selected_admin_id);
        });

        return redirect()->back()->with('success', 'Program berhasil diterbitkan!');
    }

    public function update(Request $request, $id)
{
    $program = Program::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'quota' => 'required|numeric|min:1',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'status' => 'required|in:draft,published',
        'logo' => 'nullable|image|max:2048',
        'banner' => 'nullable|image|max:2048',
        'selected_admin_id' => 'required|exists:users,id',
    ]);

    DB::transaction(function () use ($request, $program, $validated) {
        $data = $validated;

        // Handle File Update
        if ($request->hasFile('logo')) {
            if ($program->logo_path) Storage::disk('public')->delete($program->logo_path);
            $data['logo_path'] = $request->file('logo')->store('programs/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($program->banner_path) Storage::disk('public')->delete($program->banner_path);
            $data['banner_path'] = $request->file('banner')->store('programs/banners', 'public');
        }

        $program->update($data);
        $program->managers()->sync([$validated['selected_admin_id']]);
    });

    return redirect()->route('superadmin.programs.index')->with('success', 'Program berhasil diperbarui.');
}

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        if ($program->banner_path) Storage::disk('public')->delete($program->banner_path);
        if ($program->logo_path) Storage::disk('public')->delete($program->logo_path);
        $program->delete();

        return redirect()->back()->with('success', 'Program berhasil dihapus.');
    }

    private function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,published',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'selected_admin_id' => 'required|exists:users,id',
        ]);
    }
}
