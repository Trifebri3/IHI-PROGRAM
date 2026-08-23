<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AlumniProfile;
use App\Models\AlumniProgram;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\CertificateTemplate;
use App\Models\AlumniVerificationRequest;
use App\Services\AlumniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AlumniManagementController extends Controller
{
    protected $alumniService;

    public function __construct(AlumniService $alumniService)
    {
        $this->alumniService = $alumniService;
    }

    /**
     * Auto-sync/create AlumniProgram entries on the fly from Program table
     */
    private function syncAlumniPrograms($user)
    {
        $isSuperAdmin = $user->hasRole('Super Admin');
        if ($isSuperAdmin) {
            $programIds = \App\Models\Program::pluck('id')->toArray();
        } else {
            $programIds = $user->managedPrograms()->pluck('programs.id')->toArray();
        }

        foreach ($programIds as $pid) {
            $p = \App\Models\Program::find($pid);
            if ($p) {
                $year = $p->start_date ? date('Y', strtotime($p->start_date)) : date('Y');
                AlumniProgram::firstOrCreate(
                    ['program_id' => $pid],
                    [
                        'name' => $p->name,
                        'year' => $year,
                    ]
                );
            }
        }
    }

    /**
     * List all alumni with filtering
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $this->syncAlumniPrograms($user);

        $isSuperAdmin = $user->hasRole('Super Admin');
        
        $query = UserAlumni::with(['user', 'alumniProgram.program']);

        // Limit to managed programs for non-superadmins
        if (!$isSuperAdmin) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $query->whereHas('alumniProgram', function($q) use ($managedProgramIds) {
                $q->whereIn('program_id', $managedProgramIds);
            });
        }

        // Filter Program
        if ($request->filled('program_id')) {
            $query->where('alumni_program_id', $request->program_id);
        }

        // Filter Year
        if ($request->filled('year')) {
            $query->whereHas('alumniProgram', function($q) use ($request) {
                $q->where('year', $request->year);
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        // Search Name/Email/NIA
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($qu) use ($search) {
                    $qu->where('name', 'LIKE', $search)
                       ->orWhere('email', 'LIKE', $search);
                })->orWhereHas('user.alumniProfile', function($qp) use ($search) {
                    $qp->where('alumni_number', 'LIKE', $search);
                });
            });
        }

        $alumni = $query->latest()->paginate(20)->withQueryString();

        // Get filter options restricted to managed programs
        if ($isSuperAdmin) {
            $programs = AlumniProgram::orderBy('name')->get();
            $years = AlumniProgram::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        } else {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $programs = AlumniProgram::whereIn('program_id', $managedProgramIds)->orderBy('name')->get();
            $years = AlumniProgram::whereIn('program_id', $managedProgramIds)->select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        }

        // Ambil data Peserta Aktif (Calon Alumni) yang belum memiliki data di user_alumni
        $candidateQuery = \App\Models\Registration::with(['user', 'program'])
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('user_alumni')
                    ->join('alumni_programs', 'user_alumni.alumni_program_id', '=', 'alumni_programs.id')
                    ->whereColumn('user_alumni.user_id', 'registrations.user_id')
                    ->whereColumn('alumni_programs.program_id', 'registrations.program_id');
            });

        if (!$isSuperAdmin) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $candidateQuery->whereIn('program_id', $managedProgramIds);
        }

        if ($request->filled('program_id')) {
            // Mapping dari alumni_program_id ke program_id utama
            $ap = AlumniProgram::find($request->program_id);
            if ($ap) {
                $candidateQuery->where('program_id', $ap->program_id);
            }
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $candidateQuery->whereHas('user', function($qu) use ($search) {
                $qu->where('name', 'LIKE', $search)
                   ->orWhere('email', 'LIKE', $search);
            });
        }

        $candidates = $candidateQuery->latest()->paginate(20, ['*'], 'candidate_page')->withQueryString();

        // Ambil semua registrasi aktif (yang belum masuk user_alumni) untuk dropdown manual
        $activeRegistrationsQuery = \App\Models\Registration::with(['user', 'program'])
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('user_alumni')
                    ->join('alumni_programs', 'user_alumni.alumni_program_id', '=', 'alumni_programs.id')
                    ->whereColumn('user_alumni.user_id', 'registrations.user_id')
                    ->whereColumn('alumni_programs.program_id', 'registrations.program_id');
            });

        if (!$isSuperAdmin) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $activeRegistrationsQuery->whereIn('program_id', $managedProgramIds);
        }

        $activeRegistrations = $activeRegistrationsQuery->get();

        $activeRegistrationsData = $activeRegistrations->map(function($reg) use ($programs) {
            $ap = $programs->where('program_id', $reg->program_id)->first();
            return [
                'user_id' => $reg->user_id,
                'user_name' => $reg->user->name ?? 'N/A',
                'user_email' => $reg->user->email ?? 'N/A',
                'alumni_program_id' => $ap ? $ap->id : null
            ];
        })->filter(fn($x) => !is_null($x['alumni_program_id']))->values();

        return view('adminprogram.alumni.index', compact('alumni', 'programs', 'years', 'candidates', 'activeRegistrationsData'));
    }

    /**
     * Edit extra info form
     */
    public function editExtraInfo($id)
    {
        $user = auth()->user();
        $alumni = UserAlumni::with(['user', 'alumniProgram'])->findOrFail($id);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            if (!in_array($alumni->alumniProgram->program_id, $managedProgramIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return view('adminprogram.alumni.edit-extra', compact('alumni'));
    }

    /**
     * Update extra info and re-generate certificate
     */
    public function updateExtraInfo(Request $request, $id)
    {
        $request->validate([
            'nilai_akhir' => 'nullable|string|max:50',
            'predikat' => 'nullable|string|max:100',
            'ranking' => 'nullable|string|max:50',
            'skor_assessment' => 'nullable|string|max:50',
            'jam_pelatihan' => 'required|integer|min:1',
            'kompetensi' => 'nullable|string|max:1000',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $alumni = UserAlumni::findOrFail($id);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            if (!in_array($alumni->alumniProgram->program_id, $managedProgramIds)) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        $extraInfo = $alumni->extra_info ?? [];
        $extraInfo['nilai_akhir'] = $request->nilai_akhir;
        $extraInfo['predikat'] = $request->predikat;
        $extraInfo['ranking'] = $request->ranking;
        $extraInfo['skor_assessment'] = $request->skor_assessment;
        $extraInfo['jam_pelatihan'] = $request->jam_pelatihan;
        $extraInfo['kompetensi'] = $request->kompetensi;
        $extraInfo['catatan'] = $request->catatan;

        $alumni->extra_info = $extraInfo;
        $alumni->save();

        // Re-generate certificate if template exists
        $userModel = User::findOrFail($alumni->user_id);
        $alumniProgram = AlumniProgram::findOrFail($alumni->alumni_program_id);
        
        $this->alumniService->generateCertificate($userModel, $alumniProgram, $alumni);

        return redirect()->route('adminprogram.alumni.index')
            ->with('success', 'Informasi akademik dan piagam digital berhasil diperbarui!');
    }

    /**
     * Show certificate templates page
     */
    public function showTemplates()
    {
        $user = auth()->user();
        $this->syncAlumniPrograms($user);

        $isSuperAdmin = $user->hasRole('Super Admin');

        if ($isSuperAdmin) {
            $programs = AlumniProgram::with('template')->get();
        } else {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $programs = AlumniProgram::with('template')->whereIn('program_id', $managedProgramIds)->get();
        }

        return view('adminprogram.alumni.templates', compact('programs'));
    }

    /**
     * Store certificate template PDF and settings
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'alumni_program_id' => 'required|exists:alumni_programs,id',
            'template_file' => 'required|file|mimes:pdf,png,jpeg,jpg|max:10240', // 10MB
            // Settings options (X, Y coordinates)
            'name_x' => 'nullable|numeric',
            'name_y' => 'nullable|numeric',
            'name_size' => 'nullable|integer',
            'program_x' => 'nullable|numeric',
            'program_y' => 'nullable|numeric',
            'program_size' => 'nullable|integer',
            'alumni_number_x' => 'nullable|numeric',
            'alumni_number_y' => 'nullable|numeric',
            'alumni_number_size' => 'nullable|integer',
            'date_x' => 'nullable|numeric',
            'date_y' => 'nullable|numeric',
            'date_size' => 'nullable|integer',
            'qr_x' => 'nullable|numeric',
            'qr_y' => 'nullable|numeric',
            'qr_size' => 'nullable|numeric',
        ]);

        $user = auth()->user();
        $program = AlumniProgram::findOrFail($request->alumni_program_id);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            if (!in_array($program->program_id, $managedProgramIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Store PDF
        $path = $request->file('template_file')->store('certificate_templates', 'public');

        // Parse custom coordinate settings
        $settings = $this->alumniService->getDefaultSettings();
        
        if ($request->filled('name_x')) $settings['name']['x'] = floatval($request->name_x);
        if ($request->filled('name_y')) $settings['name']['y'] = floatval($request->name_y);
        if ($request->filled('name_size')) $settings['name']['size'] = intval($request->name_size);
        
        if ($request->filled('program_x')) $settings['program']['x'] = floatval($request->program_x);
        if ($request->filled('program_y')) $settings['program']['y'] = floatval($request->program_y);
        if ($request->filled('program_size')) $settings['program']['size'] = intval($request->program_size);
        
        if ($request->filled('alumni_number_x')) $settings['alumni_number']['x'] = floatval($request->alumni_number_x);
        if ($request->filled('alumni_number_y')) $settings['alumni_number']['y'] = floatval($request->alumni_number_y);
        if ($request->filled('alumni_number_size')) $settings['alumni_number']['size'] = intval($request->alumni_number_size);
        
        if ($request->filled('date_x')) $settings['date']['x'] = floatval($request->date_x);
        if ($request->filled('date_y')) $settings['date']['y'] = floatval($request->date_y);
        if ($request->filled('date_size')) $settings['date']['size'] = intval($request->date_size);
        
        if ($request->filled('qr_x')) $settings['qr']['x'] = floatval($request->qr_x);
        if ($request->filled('qr_y')) $settings['qr']['y'] = floatval($request->qr_y);
        if ($request->filled('qr_size')) $settings['qr']['size'] = floatval($request->qr_size);

        CertificateTemplate::updateOrCreate(
            ['alumni_program_id' => $program->id],
            [
                'template_path' => $path,
                'settings' => $settings,
            ]
        );

        // Regenerate certificates for all verified alumni in this program
        $verifiedAlumni = UserAlumni::where('alumni_program_id', $program->id)
            ->where('verification_status', 'approved')
            ->get();

        foreach ($verifiedAlumni as $alumniPivot) {
            $userModel = User::find($alumniPivot->user_id);
            if ($userModel) {
                $this->alumniService->generateCertificate($userModel, $program, $alumniPivot);
            }
        }

        return redirect()->route('adminprogram.alumni.templates')
            ->with('success', 'Template sertifikat PDF berhasil dikunci dan piagam alumni diperbarui!');
    }

    /**
     * Show manual verification requests list
     */
    public function showVerificationRequests()
    {
        $user = auth()->user();
        $this->syncAlumniPrograms($user);

        $query = AlumniVerificationRequest::with(['user', 'alumniProgram']);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            $query->whereHas('alumniProgram', function($q) use ($managedProgramIds) {
                $q->whereIn('program_id', $managedProgramIds);
            });
        }

        $requests = $query->latest()->paginate(20);

        return view('adminprogram.alumni.verifications', compact('requests'));
    }

    /**
     * Process verification request (Approve, Reject, Revision)
     */
    public function processVerification(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'admin_notes' => 'nullable|string|max:1000',
            // Academic details for approval
            'nilai_akhir' => 'nullable|string|max:50',
            'predikat' => 'nullable|string|max:100',
            'ranking' => 'nullable|string|max:50',
            'skor_assessment' => 'nullable|string|max:50',
            'jam_pelatihan' => 'nullable|integer|min:1',
            'kompetensi' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $verificationRequest = AlumniVerificationRequest::with('alumniProgram')->findOrFail($id);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            if (!in_array($verificationRequest->alumniProgram->program_id, $managedProgramIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($request->action === 'approve') {
            $extraInfo = [
                'nilai_akhir' => $request->nilai_akhir,
                'predikat' => $request->predikat,
                'ranking' => $request->ranking,
                'skor_assessment' => $request->skor_assessment,
                'jam_pelatihan' => $request->jam_pelatihan ?? 32,
                'kompetensi' => $request->kompetensi,
                'catatan' => $request->admin_notes,
            ];

            $this->alumniService->approveManualVerification($verificationRequest, $extraInfo);
            $message = 'Permohonan verifikasi alumni berhasil disetujui!';
        } else {
            $verificationRequest->status = $request->action === 'reject' ? 'rejected' : 'revision';
            $verificationRequest->admin_notes = $request->admin_notes;
            $verificationRequest->save();
            $message = 'Status permohonan verifikasi berhasil diupdate menjadi: ' . strtoupper($verificationRequest->status);
        }

        return redirect()->route('adminprogram.alumni.verifications')
            ->with('success', $message);
    }

    /**
     * Daftarkan dan Loloskan Peserta Baru secara instan
     */
    public function registerAndPass(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:alumni_programs,id',
        ]);

        $user = auth()->user();
        $alumniProgram = AlumniProgram::findOrFail($request->program_id);

        if (!$user->hasRole('Super Admin')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();
            if (!in_array($alumniProgram->program_id, $managedProgramIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Check if user is already registered for this program
        $existing = \App\Models\Registration::where('user_id', $request->user_id)
            ->where('program_id', $alumniProgram->program_id)
            ->first();

        if ($existing) {
            if ($existing->status === 'passed') {
                return redirect()->back()->with('error', 'User tersebut sudah lulus di program ini!');
            }
            $registration = $existing;
        } else {
            // Create a new registration
            $program = \App\Models\Program::findOrFail($alumniProgram->program_id);
            $stage = $program->stages()->orderBy('sequence')->first();
            
            $registration = \App\Models\Registration::create([
                'user_id' => $request->user_id,
                'program_id' => $alumniProgram->program_id,
                'current_stage_id' => $stage ? $stage->id : null,
                'status' => 'pending',
            ]);
        }

        \DB::transaction(function () use ($registration) {
            // Mark all stages as passed
            $stages = \App\Models\ProgramStage::where('program_id', $registration->program_id)->orderBy('sequence')->get();
            foreach ($stages as $stage) {
                \App\Models\RegistrationStageData::updateOrCreate(
                    [
                        'registration_id' => $registration->id,
                        'program_stage_id' => $stage->id,
                    ],
                    [
                        'status' => 'passed',
                        'reviewer_notes' => 'Lulus Instan oleh Admin Program'
                    ]
                );
            }

            $registration->status = 'passed';
            if (empty($registration->final_id_number)) {
                $year = date('Y');
                $count = \App\Models\Registration::whereYear('created_at', $year)->whereNotNull('final_id_number')->count() + 1;
                $registration->final_id_number = 'PRG' . $year . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
            $registration->save();
        });

        return redirect()->back()->with('success', 'User berhasil didaftarkan ke program dan langsung diluluskan menjadi Alumni!');
    }
}
