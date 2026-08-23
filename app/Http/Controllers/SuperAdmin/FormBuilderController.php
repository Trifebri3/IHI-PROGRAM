<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BiodataField;
use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function index()
    {
        $fields = BiodataField::all();
        return view('superadmin.form-builder.index', compact('fields'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,file,select',
            'is_required' => 'nullable',
            'description' => 'nullable|string',
            'example' => 'nullable|string',
            'options' => 'nullable|string'
        ]);

        BiodataField::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_required' => $request->has('is_required'),
            'description' => $validated['description'] ?? null,
            'example' => $validated['example'] ?? null,
            'options' => $validated['type'] === 'select'
                ? array_map('trim', explode(',', $validated['options']))
                : null,
        ]);

        return redirect()->back()->with('success', 'Field berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        BiodataField::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Field berhasil dihapus!');
    }
}
