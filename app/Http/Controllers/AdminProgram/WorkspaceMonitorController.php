<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Registration;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\ProgramAnnouncement;
use App\Models\ProgramAnnouncementView;
use App\Models\RegistrationStageData;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceMonitorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        // Fetch programs managed by the user
        if ($isSuperAdmin) {
            $programs = Program::with('stages')->latest()->get();
        } else {
            $programs = $user->managedPrograms()->with('stages')->latest()->get();
        }

        $programIds = $programs->pluck('id')->toArray();

        // Calculate statistics for each program
        $programStats = [];
        $globalStats = [
            'totalApplicants' => 0,
            'checked' => 0,
            'unchecked' => 0,
            'passed' => 0,
            'failed' => 0,
            'process' => 0,
            'alumni' => 0,
            'certificates' => 0
        ];

        foreach ($programs as $program) {
            // Load checking metadata
            $checkingFile = storage_path('app/checking_metadata_' . $program->id . '.json');
            $checkingData = [];
            if (file_exists($checkingFile)) {
                $checkingData = json_decode(file_get_contents($checkingFile), true) ?? [];
            }

            $registrations = Registration::where('program_id', $program->id)->get();
            
            $checkedCount = 0;
            $uncheckedCount = 0;
            foreach ($registrations as $reg) {
                $meta = $checkingData[$reg->id] ?? null;
                if ($meta && !empty($meta['is_checked'])) {
                    $checkedCount++;
                } else {
                    $uncheckedCount++;
                }
            }

            $passed = $registrations->where('status', 'passed')->count();
            $failed = $registrations->where('status', 'failed')->count();
            $process = $registrations->where('status', 'process')->count();

            // Alumni count
            $alumniProgram = \App\Models\AlumniProgram::where('program_id', $program->id)->first();
            $alumniCount = 0;
            $certificateCount = 0;
            if ($alumniProgram) {
                $alumniCount = \App\Models\UserAlumni::where('alumni_program_id', $alumniProgram->id)->count();
                $certificateCount = \App\Models\AlumniCertificate::where('alumni_program_id', $alumniProgram->id)->count();
            }

            // Form fills count
            $formFillsCount = RegistrationStageData::whereIn('program_stage_id', $program->stages->pluck('id'))->count();

            // Announcement views count
            $announcementIds = ProgramAnnouncement::where('program_id', $program->id)->pluck('id');
            $announcementViewsCount = ProgramAnnouncementView::whereIn('program_announcement_id', $announcementIds)->count();

            $programStats[$program->id] = [
                'program' => $program,
                'total' => $registrations->count(),
                'checked' => $checkedCount,
                'unchecked' => $uncheckedCount,
                'passed' => $passed,
                'failed' => $failed,
                'process' => $process,
                'alumni' => $alumniCount,
                'certificates' => $certificateCount,
                'form_fills' => $formFillsCount,
                'announcement_views' => $announcementViewsCount
            ];

            // Accumulate global stats
            $globalStats['totalApplicants'] += $registrations->count();
            $globalStats['checked'] += $checkedCount;
            $globalStats['unchecked'] += $uncheckedCount;
            $globalStats['passed'] += $passed;
            $globalStats['failed'] += $failed;
            $globalStats['process'] += $process;
            $globalStats['alumni'] += $alumniCount;
            $globalStats['certificates'] += $certificateCount;
        }

        // Fetch recent audit logs globally across the managed programs
        $registeredUserIds = Registration::whereIn('program_id', $programIds)->pluck('user_id')->toArray();
        $recentLogs = AuditLog::with(['user', 'targetUser'])
            ->where(function($q) use ($registeredUserIds, $user) {
                $q->whereIn('user_id', $registeredUserIds)
                  ->orWhereIn('target_user_id', $registeredUserIds)
                  ->orWhere('user_id', $user->id);
            })
            ->latest()
            ->take(50)
            ->get();

        return view('adminprogram.monitor.index', compact('programs', 'programStats', 'globalStats', 'recentLogs'));
    }
}
