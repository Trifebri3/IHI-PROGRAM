<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\Registration;
use App\Models\AlumniProgram;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\CertificateTemplate;
use App\Services\AlumniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CertificateManagementController extends Controller
{
    protected $alumniService;

    public function __construct(AlumniService $alumniService)
    {
        $this->alumniService = $alumniService;
    }

    /**
     * Tampilkan daftar sertifikat & queue peserta lulus
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        // Query pendaftaran yang berstatus Lulus (passed)
        $query = Registration::with(['user.address', 'user.verification', 'program', 'user.alumniCertificates'])
            ->whereIn('program_id', $managedProgramIds)
            ->where('status', 'passed');

        // Filter Program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter Batch
        if ($request->filled('batch')) {
            $query->where('batch', $request->batch);
        }

        // Filter Lokasi
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Filter status sertifikat (Terbit / Belum)
        if ($request->filled('cert_status')) {
            $hasCert = $request->cert_status === 'issued';
            $query->whereHas('user', function($q) use ($hasCert, $managedProgramIds) {
                if ($hasCert) {
                    $q->whereHas('alumniCertificates', function($qp) use ($managedProgramIds) {
                        $qp->whereHas('alumniProgram', function($qpp) use ($managedProgramIds) {
                            $qpp->whereIn('program_id', $managedProgramIds);
                        });
                    });
                } else {
                    $q->whereDoesntHave('alumniCertificates', function($qp) use ($managedProgramIds) {
                        $qp->whereHas('alumniProgram', function($qpp) use ($managedProgramIds) {
                            $qpp->whereIn('program_id', $managedProgramIds);
                        });
                    });
                }
            });
        }

        // Pencarian Nama / Email / NIP
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'LIKE', $search)
                      ->orWhere('email', 'LIKE', $search);
                })
                ->orWhere('final_id_number', 'LIKE', $search);
            });
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();

        // Data opsi dropdown
        $programs = $isSuperAdmin 
            ? Program::orderBy('name')->get() 
            : $user->managedPrograms()->orderBy('name')->get();

        $batches = Registration::whereIn('program_id', $managedProgramIds)
            ->whereNotNull('batch')
            ->distinct()
            ->pluck('batch')
            ->toArray();

        $locations = Registration::whereIn('program_id', $managedProgramIds)
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->toArray();

        return view('adminprogram.certificates.index', compact('registrations', 'programs', 'batches', 'locations'));
    }

    /**
     * Generate otomatis sertifikat dari template untuk pilihan massal
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $registrations = Registration::whereIn('id', $request->registration_ids)
            ->whereIn('program_id', $managedProgramIds)
            ->where('status', 'passed')
            ->get();

        if ($registrations->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data peserta lulus valid yang dipilih.');
        }

        $successCount = 0;
        $failedCount = 0;
        $noTemplateCount = 0;

        foreach ($registrations as $reg) {
            // Cek apakah program ini memiliki template
            $alumniProgram = AlumniProgram::firstOrCreate(
                ['program_id' => $reg->program_id],
                [
                    'name' => $reg->program->name,
                    'year' => $reg->program->start_date ? date('Y', strtotime($reg->program->start_date)) : date('Y'),
                ]
            );

            $template = CertificateTemplate::where('alumni_program_id', $alumniProgram->id)->first();
            if (!$template) {
                $noTemplateCount++;
                continue;
            }

            try {
                // Aktifkan alumni terlebih dahulu jika belum terdaftar
                $userAlumni = UserAlumni::where('user_id', $reg->user_id)
                    ->where('alumni_program_id', $alumniProgram->id)
                    ->first();

                if (!$userAlumni) {
                    $this->alumniService->registerAutoAlumni($reg);
                } else {
                    $this->alumniService->generateCertificate($reg->user, $alumniProgram, $userAlumni);
                }
                $successCount++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Certificate generation failed: ' . $e->getMessage());
                $failedCount++;
            }
        }

        $msg = "Berhasil menjana otomatis {$successCount} sertifikat.";
        if ($noTemplateCount > 0) {
            $msg .= " {$noTemplateCount} program tidak memiliki template sertifikat aktif.";
        }
        if ($failedCount > 0) {
            $msg .= " {$failedCount} gagal karena kesalahan sistem.";
        }

        return redirect()->back()->with($successCount > 0 ? 'success' : 'error', $msg);
    }

    /**
     * Upload manual file sertifikat per individu
     */
    public function singleUpload(Request $request, $id)
    {
        $request->validate([
            'certificate_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'certificate_number' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $reg = Registration::whereIn('program_id', $managedProgramIds)
            ->where('status', 'passed')
            ->findOrFail($id);

        // Pastikan record alumni terdaftar
        $alumniProgram = AlumniProgram::firstOrCreate(
            ['program_id' => $reg->program_id],
            [
                'name' => $reg->program->name,
                'year' => $reg->program->start_date ? date('Y', strtotime($reg->program->start_date)) : date('Y'),
            ]
        );

        $userAlumni = UserAlumni::where('user_id', $reg->user_id)
            ->where('alumni_program_id', $alumniProgram->id)
            ->first();

        if (!$userAlumni) {
            // Aktifkan alumni secara aman
            $this->alumniService->registerAutoAlumni($reg);
            $userAlumni = UserAlumni::where('user_id', $reg->user_id)
                ->where('alumni_program_id', $alumniProgram->id)
                ->first();
        }

        // Simpan File
        $certDirectory = 'alumni_certificates/' . $alumniProgram->id;
        $path = $request->file('certificate_file')->store($certDirectory, 'public');

        $certNumber = $request->filled('certificate_number') 
            ? $request->certificate_number 
            : ($reg->final_id_number ?? 'ALM-' . $userAlumni->uuid);

        // Simpan data sertifikat
        AlumniCertificate::updateOrCreate(
            [
                'user_id' => $reg->user_id,
                'alumni_program_id' => $alumniProgram->id,
            ],
            [
                'certificate_number' => $certNumber,
                'file_path' => $path,
                'uuid' => $userAlumni->uuid,
                'extra_info' => $userAlumni->extra_info,
            ]
        );

        return redirect()->back()->with('success', 'Sertifikat manual berhasil diunggah.');
    }

    /**
     * Upload massal (Bulk Upload) & mencocokkan otomatis berdasarkan NIP / Email dari nama file
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,png,jpg,jpeg|max:10240',
            'program_id' => 'required|exists:programs,id',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        if (!in_array($request->program_id, $managedProgramIds)) {
            abort(403, 'Aksi ditolak: Program di luar wewenang pengelolaan Anda.');
        }

        $program = Program::findOrFail($request->program_id);

        $alumniProgram = AlumniProgram::firstOrCreate(
            ['program_id' => $program->id],
            [
                'name' => $program->name,
                'year' => $program->start_date ? date('Y', strtotime($program->start_date)) : date('Y'),
            ]
        );

        // Ambil seluruh peserta lulus pada program ini
        $registrations = Registration::with('user')
            ->where('program_id', $program->id)
            ->where('status', 'passed')
            ->get();

        $successCount = 0;
        $skippedFiles = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $normalizedName = strtoupper(trim($filenameWithoutExt));

            // Cari peserta yang cocok berdasarkan NIP (final_id_number) atau Email
            $matchedReg = $registrations->first(function($reg) use ($normalizedName) {
                // Kecocokan NIP
                if (!empty($reg->final_id_number) && str_contains(strtoupper($reg->final_id_number), $normalizedName)) {
                    return true;
                }
                // Kecocokan Email
                if (str_contains(strtoupper($reg->user->email), $normalizedName)) {
                    return true;
                }
                // Kecocokan Nama Lengkap
                if (str_contains(strtoupper($reg->user->name), $normalizedName)) {
                    return true;
                }
                return false;
            });

            if (!$matchedReg) {
                $skippedFiles[] = $originalName;
                continue;
            }

            // Aktifkan alumni jika belum terdaftar
            $userAlumni = UserAlumni::where('user_id', $matchedReg->user_id)
                ->where('alumni_program_id', $alumniProgram->id)
                ->first();

            if (!$userAlumni) {
                $this->alumniService->registerAutoAlumni($matchedReg);
                $userAlumni = UserAlumni::where('user_id', $matchedReg->user_id)
                    ->where('alumni_program_id', $alumniProgram->id)
                    ->first();
            }

            // Simpan File
            $certDirectory = 'alumni_certificates/' . $alumniProgram->id;
            $path = $file->store($certDirectory, 'public');

            $certNumber = $matchedReg->final_id_number ?? 'ALM-' . $userAlumni->uuid;

            // Simpan data sertifikat
            AlumniCertificate::updateOrCreate(
                [
                    'user_id' => $matchedReg->user_id,
                    'alumni_program_id' => $alumniProgram->id,
                ],
                [
                    'certificate_number' => $certNumber,
                    'file_path' => $path,
                    'uuid' => $userAlumni->uuid,
                    'extra_info' => $userAlumni->extra_info,
                ]
            );

            $successCount++;
        }

        $msg = "Berhasil mengunggah dan memetakan {$successCount} file sertifikat.";
        if (!empty($skippedFiles)) {
            $msg .= " " . count($skippedFiles) . " file dilewati karena nama file tidak cocok dengan data NIP/Email/Nama peserta: (" . implode(', ', $skippedFiles) . ").";
        }

        return redirect()->back()->with($successCount > 0 ? 'success' : 'error', $msg);
    }

    /**
     * Hapus / revisi data sertifikat peserta
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $certificate = AlumniCertificate::whereHas('alumniProgram', function($q) use ($managedProgramIds) {
            $q->whereIn('program_id', $managedProgramIds);
        })->findOrFail($id);

        // Hapus file dari storage
        if (Storage::disk('public')->exists($certificate->file_path)) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return redirect()->back()->with('success', 'Sertifikat berhasil dihapus/dibatalkan.');
    }
}
