<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\AuditLog;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Cek jika dia Super Admin
        if ($user->hasRole('Super Admin')) {
            $totalUsers = User::count();
            $totalPrograms = Program::count();
            $totalRegistrations = \App\Models\Registration::count();

            // Get registration and graduation counts per program
            $programStats = Program::withCount([
                'registrations',
                'registrations as passed_count' => function ($query) {
                    $query->where('status', 'passed');
                }
            ])->get();

            // Prepare chart data Arrays
            $chartLabels = $programStats->pluck('name')->toArray();
            $chartRegistrations = $programStats->pluck('registrations_count')->toArray();
            $chartPassed = $programStats->pluck('passed_count')->toArray();

            // Fetch latest 10 Audit Logs
            $recentLogs = AuditLog::with(['user', 'targetUser'])
                ->latest()
                ->take(10)
                ->get();

            return view('superadmin.dashboard', compact(
                'totalUsers',
                'totalPrograms',
                'totalRegistrations',
                'chartLabels',
                'chartRegistrations',
                'chartPassed',
                'recentLogs'
            ));
        }

        // 2. Cek jika dia Admin Program
        if ($user->hasRole('Admin Program')) {
            $managedProgramIds = $user->managedPrograms()->pluck('programs.id')->toArray();

            // METRICS CALCULATIONS
            $totalPeserta = \App\Models\Registration::whereIn('program_id', $managedProgramIds)->count();

            $pesertaSelesai = \App\Models\Registration::whereIn('program_id', $managedProgramIds)
                ->where('status', '!=', 'process')
                ->count();

            $lulusCount = \App\Models\Registration::whereIn('program_id', $managedProgramIds)
                ->where('status', 'passed')
                ->count();

            $alumniAktif = \App\Models\UserAlumni::whereHas('alumniProgram', function($q) use ($managedProgramIds) {
                $q->whereIn('program_id', $managedProgramIds);
            })->where('verification_status', 'approved')->count();

            // Menunggu Aktivasi (Lulus, ada NIP, data KYC lengkap, alamat lengkap, tapi belum terdaftar di user_alumni)
            $menungguAktivasi = \App\Models\Registration::whereIn('program_id', $managedProgramIds)
                ->where('status', 'passed')
                ->whereNotNull('final_id_number')
                ->where('final_id_number', '!=', '')
                ->whereHas('user.address')
                ->whereHas('user.verification', function($q) {
                    $q->where('status', 'verified');
                })
                ->whereDoesntHave('user.alumniPrograms', function($q) use ($managedProgramIds) {
                    $q->whereIn('program_id', $managedProgramIds);
                })
                ->count();

            // Sertifikat Belum Terbit (Passed but no certificate record)
            $sertifikatBelumTerbit = \App\Models\Registration::whereIn('program_id', $managedProgramIds)
                ->where('status', 'passed')
                ->whereDoesntHave('user.alumniCertificates', function($q) use ($managedProgramIds) {
                    $q->whereHas('alumniProgram', function($qp) use ($managedProgramIds) {
                        $qp->whereIn('program_id', $managedProgramIds);
                    });
                })
                ->count();

            // Pengajuan Verifikasi mandiri alumni
            $pengajuanVerifikasi = \App\Models\AlumniVerificationRequest::whereHas('alumniProgram', function($q) use ($managedProgramIds) {
                $q->whereIn('program_id', $managedProgramIds);
            })->where('status', 'pending')->count();

            // Data Peserta Belum Lengkap (Missing address, kyc verification, or photo path)
            $dataBelumLengkap = \App\Models\Registration::whereIn('program_id', $managedProgramIds)
                ->where(function($q) {
                    $q->whereDoesntHave('user.address')
                      ->orWhereDoesntHave('user.verification')
                      ->orWhereDoesntHave('user.profile');
                })
                ->count();

            return view('adminprogram.dashboard', compact(
                'totalPeserta',
                'pesertaSelesai',
                'lulusCount',
                'alumniAktif',
                'menungguAktivasi',
                'sertifikatBelumTerbit',
                'pengajuanVerifikasi',
                'dataBelumLengkap'
            ));
        }

        // 3. Cek jika dia Reviewer (opsional, jika Anda ingin view khusus reviewer nanti)
        if ($user->hasRole('Reviewer')) {
            // Untuk sementara kita arahkan ke view admin program atau view khusus reviewer
            return view('audit.dashboard');
        }

        // 4. Default / Fallback: Lemparkan ke view Peserta Biasa
        return view('pesertabiasa.dashboard');
    }
}
