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
            return view('adminprogram.dashboard');
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
