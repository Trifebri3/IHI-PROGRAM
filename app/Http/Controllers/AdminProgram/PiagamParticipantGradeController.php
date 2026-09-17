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

    public function downloadTemplate($programId) {
        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);
        
        $customVariables = [];
        if ($program->piagamTemplate) {
            foreach ($program->piagamTemplate->layouts as $layout) {
                foreach ($layout->elements as $element) {
                    if ($element->type === 'dynamic' && preg_match('/^\[(.*?)\]$/', $element->content, $matches)) {
                        $varName = $matches[1];
                        $standardVars = ['NAMA_PESERTA', 'NAMA_PROGRAM', 'NOMOR_KREDENSIAL_UTAMA_GLOBAL', 'PREDIKAT', 'TOTAL_SKOR', 'TANGGAL_TERBIT'];
                        if (!in_array($varName, $standardVars)) {
                            $customVariables[] = $varName;
                        }
                    }
                }
            }
        }
        $customVariables = array_unique($customVariables);

        $participants = clone $program->registrations()
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('registrations.*')
            ->with('user')
            ->orderBy('users.name', 'asc')
            ->get();
            
        // Fetch existing grades
        $participantIds = $participants->pluck('id');
        $existingGrades = PiagamParticipantGrade::whereIn('participant_id', $participantIds)->get();
        $gradesMap = [];
        foreach ($existingGrades as $grade) {
            $gradesMap[$grade->participant_id][$grade->variable_name] = $grade->value;
        }

        $fileName = 'Template_Nilai_' . \Illuminate\Support\Str::slug($program->name) . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = array_merge(['ID Pendaftar', 'Nama Peserta', 'Email'], $customVariables);

        $callback = function() use($participants, $columns, $customVariables, $gradesMap) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';');

            foreach ($participants as $reg) {
                $row = [
                    $reg->id,
                    $reg->user->name,
                    $reg->user->email,
                ];
                foreach ($customVariables as $var) {
                    $row[] = $gradesMap[$reg->id][$var] ?? '';
                }
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadTemplate(Request $request, $programId) {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);
        
        $program = Program::findOrFail($programId);
        
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Auto-detect delimiter
        $firstLine = fgets($handle);
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($handle);
        
        $headers = fgetcsv($handle, 1000, $delimiter);
        if (!$headers) {
            return back()->with('error', 'Format file tidak valid.');
        }
        
        $idIndex = array_search('ID Pendaftar', $headers);
        if ($idIndex === false) {
            return back()->with('error', 'Kolom "ID Pendaftar" tidak ditemukan di dalam file. Harap gunakan template yang di-download.');
        }
        
        $customVarIndexes = [];
        foreach ($headers as $index => $headerName) {
            if (!in_array($headerName, ['ID Pendaftar', 'Nama Peserta', 'Email']) && !empty(trim($headerName))) {
                $customVarIndexes[trim($headerName)] = $index;
            }
        }
        
        $updatedCount = 0;
        
        while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if (count($data) <= $idIndex) continue;
            
            $participantId = $data[$idIndex];
            if (!is_numeric($participantId)) continue;
            
            // Validate participant
            $exists = Registration::where('id', $participantId)->where('program_id', $programId)->exists();
            if (!$exists) continue;
            
            foreach ($customVarIndexes as $varName => $index) {
                if (isset($data[$index])) {
                    $value = trim($data[$index]);
                    
                    if ($value !== '') {
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
            $updatedCount++;
        }
        
        fclose($handle);
        
        return back()->with('success', "Berhasil memproses dan mengimpor nilai untuk $updatedCount data peserta.");
    }
}


