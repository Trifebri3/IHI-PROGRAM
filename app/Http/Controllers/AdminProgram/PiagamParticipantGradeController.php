<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Registration;
use App\Models\PiagamParticipantGrade;
use Illuminate\Support\Str;

class PiagamParticipantGradeController extends Controller {

    private function getCustomVariables($program) {
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
        return array_values(array_unique($customVariables));
    }

    public function index($programId) {
        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);
        $customVariables = $this->getCustomVariables($program);

        // Fetch participants (Status: Lulus / Completed)
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

        $publicSettings = $program->piagam_public_settings ?? [];

        return view('adminprogram.piagam.grades.index', compact('program', 'customVariables', 'participants', 'gradesMap', 'publicSettings'));
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

    public function savePublicSettings(Request $request, $programId) {
        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);

        $publicTitle = trim((string)$request->input('public_title', ''));
        if (empty($publicTitle)) {
            $publicTitle = 'Informasi Kelulusan & Nilai';
        }

        $showPublic = $request->boolean('show_public', false);
        $showPeserta = $request->boolean('show_peserta', false);

        $customVars = $this->getCustomVariables($program);

        $variableLabels = [];
        $rawLabels = $request->input('variable_labels', []);
        if (is_array($rawLabels)) {
            foreach ($rawLabels as $var => $lbl) {
                $lbl = trim((string)$lbl);
                if (!empty($lbl)) {
                    $variableLabels[$var] = $lbl;
                }
            }
        }

        $variableVisibility = [];
        $rawVisibility = $request->input('variable_visibility', []);
        foreach ($customVars as $var) {
            $variableVisibility[$var] = isset($rawVisibility[$var]) && ($rawVisibility[$var] == '1' || $rawVisibility[$var] === true);
        }

        $settings = [
            'public_title' => $publicTitle,
            'show_public' => $showPublic,
            'show_peserta' => $showPeserta,
            'variable_labels' => $variableLabels,
            'variable_visibility' => $variableVisibility,
        ];

        $program->update([
            'piagam_public_settings' => $settings
        ]);

        return back()->with('success', 'Pengaturan judul formulir dan tampilan publik berhasil disimpan!');
    }

    public function downloadTemplate($programId) {
        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);
        $customVariables = $this->getCustomVariables($program);

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

        $fileName = 'Template_Nilai_' . Str::slug($program->name) . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = array_merge(['ID Pendaftar', 'Nama Peserta', 'Email'], $customVariables);

        $callback = function() use($participants, $columns, $customVariables, $gradesMap) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');

            foreach ($participants as $reg) {
                $row = [
                    $reg->id,
                    $reg->user->name ?? '',
                    $reg->user->email ?? '',
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

    /**
     * Normalize name for comparison:
     * Lowercase, replace punctuation with spaces, collapse spaces.
     */
    private function normalizeName($name) {
        $name = mb_strtolower((string)$name, 'UTF-8');
        // Replace punctuation with spaces (so "A.Muh.Ikhwan" becomes "a muh ikhwan")
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Extract significant word tokens (length >= 2)
     */
    private function getWordTokens($name) {
        $norm = $this->normalizeName($name);
        if (empty($norm)) return [];
        $rawWords = explode(' ', $norm);
        $words = [];
        foreach ($rawWords as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 2) {
                $words[] = $w;
            }
        }
        return array_values(array_unique($words));
    }

    /**
     * Auto-detect CSV delimiter (;, ,, or tab)
     */
    private function detectDelimiter($filePath) {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        if (!$firstLine) return ';';

        // Strip UTF-8 BOM if present
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $semicolons = substr_count($firstLine, ';');
        $commas = substr_count($firstLine, ',');
        $tabs = substr_count($firstLine, "\t");

        if ($tabs > $semicolons && $tabs > $commas) return "\t";
        if ($commas > $semicolons) return ',';
        return ';';
    }

    public function uploadTemplate(Request $request, $programId) {
        $request->validate([
            'file' => 'required|file'
        ]);

        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return back()->with('error', 'File harus berformat CSV atau TXT.');
        }

        $program = Program::with('piagamTemplate.layouts.elements')->findOrFail($programId);
        $customVariables = $this->getCustomVariables($program);

        $filePath = $request->file('file')->getRealPath();
        $delimiter = $this->detectDelimiter($filePath);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membuka file yang diunggah.');
        }

        $rawHeaders = fgetcsv($handle, 2000, $delimiter);
        if (!$rawHeaders) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau format header tidak terbaca.');
        }

        // Clean headers and strip BOM
        $headers = [];
        foreach ($rawHeaders as $idx => $header) {
            $headers[$idx] = preg_replace('/^\xEF\xBB\xBF/', '', trim($header));
        }

        // Detect column positions
        $idIndex = false;
        $nameIndex = false;
        $emailIndex = false;

        foreach ($headers as $idx => $headerName) {
            $normH = strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $headerName));

            if (in_array($normH, ['idpendaftar', 'idpeserta', 'id', 'noid', 'registrationid'])) {
                $idIndex = $idx;
            } elseif (in_array($normH, ['namapeserta', 'nama', 'name', 'namalengkap', 'peserta'])) {
                $nameIndex = $idx;
            } elseif (in_array($normH, ['email', 'surel', 'emailpeserta'])) {
                $emailIndex = $idx;
            }
        }

        // Match custom variable columns
        $customVarIndexes = [];
        foreach ($headers as $idx => $headerName) {
            $cleanH = trim($headerName);
            if (empty($cleanH)) continue;
            if ($idx === $idIndex || $idx === $nameIndex || $idx === $emailIndex) continue;
            if (in_array(strtolower($cleanH), ['no', 'nomor', '#'])) continue;

            $matchedVar = null;
            foreach ($customVariables as $cv) {
                if (strcasecmp($cleanH, $cv) === 0 || 
                    strcasecmp(str_replace(' ', '_', $cleanH), $cv) === 0 ||
                    strcasecmp(str_replace('_', ' ', $cleanH), str_replace('_', ' ', $cv)) === 0) {
                    $matchedVar = $cv;
                    break;
                }
            }

            $finalVarName = $matchedVar ?: $cleanH;
            $customVarIndexes[$finalVarName] = $idx;
        }

        // Preload all program participants for fast and accurate matching
        $registrations = $program->registrations()->with('user')->get();

        $regsById = [];
        $regsByEmail = [];
        $regsByExactName = [];
        $candidateList = [];

        foreach ($registrations as $reg) {
            if (!$reg->user) continue;

            $regsById[$reg->id] = $reg;

            $userEmail = strtolower(trim($reg->user->email ?? ''));
            if (!empty($userEmail)) {
                $regsByEmail[$userEmail] = $reg;
            }

            $rawName = $reg->user->name ?? '';
            $normName = $this->normalizeName($rawName);
            if (!empty($normName)) {
                $regsByExactName[$normName] = $reg;
            }

            $candidateList[] = [
                'registration' => $reg,
                'raw_name' => $rawName,
                'norm_name' => $normName,
                'tokens' => $this->getWordTokens($rawName)
            ];
        }

        $updatedCount = 0;
        $unmatchedRows = [];
        $matchedParticipantIds = [];

        while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
            // Skip empty rows
            $hasContent = false;
            foreach ($data as $cell) {
                if (trim((string)$cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) continue;

            $matchedReg = null;

            // 1. Try matching by ID Pendaftar (if provided and valid)
            if ($idIndex !== false && isset($data[$idIndex])) {
                $rowId = trim((string)$data[$idIndex]);
                if (is_numeric($rowId) && isset($regsById[$rowId])) {
                    $matchedReg = $regsById[$rowId];
                }
            }

            // 2. Try matching by Email
            if (!$matchedReg && $emailIndex !== false && isset($data[$emailIndex])) {
                $rowEmail = strtolower(trim((string)$data[$emailIndex]));
                if (!empty($rowEmail) && isset($regsByEmail[$rowEmail])) {
                    $matchedReg = $regsByEmail[$rowEmail];
                }
            }

            // 3. Match by NAMA (Exact or Fuzzy with MINIMAL 2 SUKU KATA / KATA SAMA)
            $rowNameRaw = ($nameIndex !== false && isset($data[$nameIndex])) ? trim((string)$data[$nameIndex]) : '';
            if (!$matchedReg && !empty($rowNameRaw)) {
                $normRowName = $this->normalizeName($rowNameRaw);

                // 3a. Exact normalized match
                if (isset($regsByExactName[$normRowName])) {
                    $matchedReg = $regsByExactName[$normRowName];
                } else {
                    // 3b. Token match: minimal 2 kata sama
                    $rowTokens = $this->getWordTokens($rowNameRaw);
                    $bestCandidate = null;
                    $highestScore = 0;

                    foreach ($candidateList as $cand) {
                        $intersect = array_intersect($rowTokens, $cand['tokens']);
                        $matchCount = count($intersect);

                        // At least 2 words match
                        if ($matchCount >= 2) {
                            similar_text($normRowName, $cand['norm_name'], $percent);
                            $score = ($matchCount * 100) + $percent;
                            if ($score > $highestScore) {
                                $highestScore = $score;
                                $bestCandidate = $cand['registration'];
                            }
                        }
                    }

                    if ($bestCandidate) {
                        $matchedReg = $bestCandidate;
                    }
                }
            }

            // If participant matched, update / overwrite grades
            if ($matchedReg) {
                $matchedParticipantIds[] = $matchedReg->id;
                foreach ($customVarIndexes as $varName => $colIdx) {
                    if (isset($data[$colIdx])) {
                        $value = trim((string)$data[$colIdx]);
                        if ($value !== '') {
                            PiagamParticipantGrade::updateOrCreate(
                                [
                                    'participant_id' => $matchedReg->id,
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
            } else {
                // Record unmatched row for reporting
                $unmatchedLabel = !empty($rowNameRaw) ? $rowNameRaw : (($idIndex !== false && isset($data[$idIndex])) ? "ID " . $data[$idIndex] : "Baris data");
                $unmatchedRows[] = $unmatchedLabel;
            }
        }

        fclose($handle);

        $distinctUpdatedCount = count(array_unique($matchedParticipantIds));

        if ($updatedCount === 0 && count($unmatchedRows) > 0) {
            return back()->with('error', "Gagal mencocokkan data. Tidak ada peserta yang cocok dengan nama di file CSV (Nama: " . implode(', ', array_slice($unmatchedRows, 0, 3)) . "). Pastikan minimal 2 kata nama sama.");
        }

        $message = "Berhasil mengimpor dan memperbarui nilai untuk {$distinctUpdatedCount} data peserta.";
        if (count($unmatchedRows) > 0) {
            $unmatchedSample = implode(', ', array_slice($unmatchedRows, 0, 4));
            if (count($unmatchedRows) > 4) $unmatchedSample .= '...';
            $message .= " Catatan: " . count($unmatchedRows) . " baris tidak dapat dicocokkan ({$unmatchedSample}).";
        }

        return back()->with('success', $message);
    }
}
