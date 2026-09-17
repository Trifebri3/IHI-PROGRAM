<?php
namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PiagamTemplate;
use App\Models\Program;

class PiagamTemplateController extends Controller {
    public function index() { 
        $templates = PiagamTemplate::latest()->paginate(10);
        $programs = Program::orderBy('name')->get();
        return view('superadmin.piagam.templates.index', compact('templates', 'programs')); 
    }
    
    public function create() { return view('superadmin.piagam.templates.create'); }
    
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'file_path' => 'required|mimes:pdf|max:10240',
        ]);
        
        $path = $request->file('file_path')->store('piagam_templates', 'public');
        
        // Count total pages using FPDI
        $totalPages = 1;
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $totalPages = $pdf->setSourceFile(storage_path('app/public/' . $path));
        } catch (\Exception $e) {
            // fallback if error
            $totalPages = 1;
        }

        PiagamTemplate::create([
            'name' => $request->name,
            'description' => $request->description ?? null,
            'file_path' => $path,
            'total_pages' => $totalPages
        ]);
        
        return redirect()->route('superadmin.piagam.templates.index')->with('success', 'Template berhasil diunggah.');
    }
    
    public function show($id) {}
    
    public function edit($id) { 
        $template = PiagamTemplate::findOrFail($id);
        $template->load('layouts.elements');
        return view('superadmin.piagam.templates.edit', compact('template')); 
    }
    
    public function update(Request $request, $id) {}
    
    public function destroy($id) {
        $template = PiagamTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('superadmin.piagam.templates.index')->with('success', 'Template dihapus.');
    }
    
    public function assignToProgram(Request $request) {
        $request->validate([
            'template_id' => 'required|exists:piagam_templates,id',
            'program_id' => 'required|exists:programs,id',
        ]);
        
        $program = Program::findOrFail($request->program_id);
        $program->update(['piagam_template_id' => $request->template_id]);
        
        return redirect()->route('superadmin.piagam.templates.index')->with('success', 'Template berhasil dipasangkan ke Program ' . $program->name);
    }

    public function saveLayoutElements(Request $request, $id) {
        $template = PiagamTemplate::findOrFail($id);
        
        if ($request->has('total_pages')) {
            $template->update(['total_pages' => $request->total_pages]);
        }

        $elements = $request->input('elements', []);
        
        // Group elements by page_number
        $elementsByPage = collect($elements)->groupBy('page_number');

        // Loop through all pages to ensure layouts exist and are updated
        $totalPages = $request->input('total_pages', 1);
        for ($p = 1; $p <= $totalPages; $p++) {
            $layout = \App\Models\PiagamLayout::firstOrCreate([
                'template_id' => $template->id,
                'page_number' => $p
            ], [
                'width' => $request->pdf_width ?? null,
                'height' => $request->pdf_height ?? null
            ]);
            
            // Delete old elements for this page
            $layout->elements()->delete();
            
            // Insert new elements if they exist
            if ($elementsByPage->has($p)) {
                foreach ($elementsByPage[$p] as $el) {
                    \App\Models\PiagamLayoutElement::create([
                        'layout_id' => $layout->id,
                        'type' => $el['type'],
                        'content' => $el['content'] ?? null,
                        'x_pos' => $el['x_pos'],
                        'y_pos' => $el['y_pos'],
                        'font_size' => $el['font_size'] ?? 12,
                        'font_family' => $el['font_family'] ?? 'Arial',
                        'color' => $el['color'] ?? '#000000',
                        'text_align' => $el['text_align'] ?? 'left'
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Layout berhasil disimpan']);
    }

    public function uploadOrnament(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg|max:10240',
        ]);
        
        $path = $request->file('image')->store('piagam_ornaments', 'public');
        
        return response()->json([
            'status' => 'success',
            'url' => \Illuminate\Support\Facades\Storage::url($path)
        ]);
    }
}

