<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Registration;
use App\Models\PiagamParticipantGrade;

class PiagamParticipantGradeController extends Controller {
    public function index($programId) {
        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);
        
        // Find custom variables in the template layout
        $customVariables = [];
        if ($program->piagamTemplate) {
            foreach ($program->piagamTemplate->layouts as $layout) {
                foreach ($layout->elements as $element) {
                    if ($element->type === 'dynamic' && preg_match('/^\[(.*?)\]$/', $element->content, $matches)) {
                        $varName = $matches[1];
                        // Exclude standard variables
                        $standardVars = ['NAMA_PESERTA', 'NAMA_PROGRAM', 'NOMOR_KREDENSIAL_UTAMA_GLOBAL', 'PREDIKAT', 'TOTAL_SKOR', 'TANGGAL_TERBIT'];
                        if (!in_array($varName, $standardVars)) {
                            $customVariables[] = $varName;
                        }
                    }
                }
            }
        }
        
        $customVariables = array_unique($customVariables);

        // Fetch participants (Status: Lulus / Completed)
        // Usually, graduates are status 'completed' or similar, but for now we'll fetch all registrations
        $sort = request('sort', 'name_asc');
        
        $query = $program->registrations()
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('registrations.*')
            ->with('user');
            
        if ($sort === 'name_asc') {
            $query->orderBy('users.name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('users.name', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('registrations.created_at', 'desc');
        }

        $participants = $query->paginate(20)->withQueryString();
        
        // Fetch existing grades
        $participantIds = $participants->pluck('id');
        $existingGrades = PiagamParticipantGrade::whereIn('participant_id', $participantIds)->get();
        
        // Structure grades by participant and variable
        $gradesMap = [];
        foreach ($existingGrades as $grade) {
            $gradesMap[$grade->participant_id][$grade->variable_name] = $grade->value;
        }

        return view('adminprogram.piagam.grades.index', compact('program', 'customVariables', 'participants', 'gradesMap'));
    }

    public function store(Request $request, $programId) {
        $program = Program::findOrFail($programId);
        $grades = $request->input('grades', []);

        foreach ($grades as $participantId => $variables) {
            foreach ($variables as $varName => $value) {
                if ($value !== null && $value !== '') {
                    PiagamParticipantGrade::updateOrCreate(
                        [
                            'participant_id' => $participantId,
                            'variable_name' => $varName
                        ],
                        [
                            'value' => $value
                        ]
                    );
                }
            }
        }

                return redirect()->route('adminprogram.piagam.grades.index', [
            'program' => $programId,
            'page' => $request->input('page', 1),
            'sort' => $request->input('sort', 'name_asc')
        ])->with('success', 'Nilai berhasil disimpan!');
    }
}


