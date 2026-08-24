<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // <-- INI YANG KURANG!
use App\Models\Program;
use App\Models\Registration;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class PublicStatisticController extends Controller
{

public function index()
{
    $programs = Program::where('status', 'published')->get();
    return view('public.index', compact('programs'));
}


    public function showProgramStats($programId)
    {
        $program = Program::findOrFail($programId);

        // Mengambil jumlah peserta per provinsi untuk grafik
        $stats = Address::whereIn('user_id', function($query) use ($programId) {
            $query->select('user_id')->from('registrations')->where('program_id', $programId);
        })
        ->select('provinsi', DB::raw('count(*) as total'))
        ->groupBy('provinsi')
        ->get();

        // Mengambil data lengkap alamat untuk pemetaan kabupaten kustom
        $participantsData = Address::whereIn('user_id', function($query) use ($programId) {
            $query->select('user_id')->from('registrations')->where('program_id', $programId);
        })
        ->select('provinsi', 'kabupaten')
        ->get();

        // Data statistik umum
        $totalPeserta = Registration::where('program_id', $programId)->count();

        return view('public.statistics', compact('program', 'stats', 'participantsData', 'totalPeserta'));
    }

    public function mapData($programId)
    {
        // Mengambil data koordinat (termasuk provinsi untuk agregasi peta choropleth)
        $participants = Address::whereIn('user_id', function($query) use ($programId) {
            $query->select('user_id')
                ->from('registrations')
                ->where('program_id', $programId)
                ->whereNotNull('final_id_number'); // Hanya peserta resmi terverifikasi
        })->select('provinsi', 'kabupaten')->get();

        // Data ini akan dikirim ke Leaflet.js
        return response()->json($participants);
    }
public function participants(Request $request, $programId)
{
    $program = Program::findOrFail($programId);

    // Query peserta yang sudah lolos (final_id_number tidak null)
    $query = Registration::with(['user.profile', 'user.address', 'user.verification'])
        ->where('program_id', $programId)
        ->whereNotNull('final_id_number')
        ->whereHas('user.address', function($q) use ($request) {
            if ($request->filled('provinsi')) $q->where('provinsi', $request->provinsi);
            if ($request->filled('kabupaten')) $q->where('kabupaten', $request->kabupaten);
        });

    $participants = $query->paginate(12);

    // List unik untuk filter
    $provinces = Address::distinct()->pluck('provinsi');
    $regencies = $request->provinsi ? Address::where('provinsi', $request->provinsi)->distinct()->pluck('kabupaten') : [];

    return view('public.participants', compact('program', 'participants', 'provinces', 'regencies'));
}








}
