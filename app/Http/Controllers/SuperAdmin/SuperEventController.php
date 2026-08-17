<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class SuperEventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('registrations')->orderBy('event_date', 'desc')->get();
        return view('superadmin.event.index', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255', 'location' => 'required|string|max:255', 'event_date' => 'required|date', 'event_time' => 'required', 'quota' => 'required|integer|min:1']);
        Event::create([
            'title' => trim($request->title), 'description' => $request->description, 'location' => trim($request->location),
            'event_date' => $request->event_date, 'event_time' => $request->event_time, 'quota' => $request->quota, 'form_schema' => []
        ]);
        return redirect()->route('superadmin.events.index')->with('success', 'Event baru berhasil dipublikasikan!');
    }

    // --- 🚀 METHOD BARU: DEDICATED EVENT DASHBOARD & LIVE RECAP ---
    public function showDashboard($id)
    {
        $event = Event::findOrFail($id);
        // Tarik semua data pendaftar lengkap beserta jawaban kustom JSON & status absennya
        $recapSubmissions = EventRegistration::with('user')->where('event_id', $id)->orderBy('created_at', 'desc')->get();

        return view('superadmin.event.dashboard', compact('event', 'recapSubmissions'));
    }

    public function storeFormSchema(Request $request, $id)
    {
        $request->validate(['field_name' => 'required|string|max:100', 'field_type' => 'required|in:text,number,file']);
        $event = Event::findOrFail($id);

        $schema = $event->form_schema ?? [];
        $schema[] = [
            'name' => trim($request->field_name),
            'type' => $request->field_type,
            'required' => $request->has('is_required')
        ];

        $event->update(['form_schema' => $schema]);
        return redirect()->route('superadmin.events.dashboard', $id)->with('success', 'Atribut Formulir GForm sukses dipasang!');
    }

    public function deleteFormSchema($id, $index)
    {
        $event = Event::findOrFail($id);
        $schema = $event->form_schema ?? [];

        if (isset($schema[$index])) {
            unset($schema[$index]);
        }

        $event->update(['form_schema' => array_values($schema)]);
        return redirect()->route('superadmin.events.dashboard', $id)->with('success', 'Atribut Formulir berhasil dicabut.');
    }

    public function toggleAttendance(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($event->is_attendance_open) {
            // Tutup Absen & Hapus Token
            $event->update(['is_attendance_open' => false, 'attendance_token' => null]);
            $msg = 'Sesi absensi event resmi ditutup.';
        } else {
            // Buka Absen & Bangun Token Acak Unik (Cth: EVT-48A1)
            $token = 'EVT-' . strtoupper(substr(md5(uniqid()), 0, 4));
            $event->update(['is_attendance_open' => true, 'attendance_token' => $token]);
            $msg = 'Sesi absensi berhasil dibuka dengan KODE TOKEN: ' . $token;
        }

        return redirect()->route('superadmin.events.dashboard', $id)->with('success', $msg);
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();
        return redirect()->route('superadmin.events.index')->with('success', 'Data event berhasil dihapus.');
    }


    // Tambahkan method ini di dalam class SuperEventController Anda:

public function printRecap($id)
{
    // Tarik data induk event
    $event = Event::withCount('registrations')->findOrFail($id);

    // Tarik data seluruh pendaftar, data isian formulir kustom, dan jam absensi mereka
    $recapSubmissions = EventRegistration::with('user')
        ->where('event_id', $id)
        ->orderBy('attended_at', 'desc') // Utamakan yang hadir di atas
        ->orderBy('created_at', 'asc')
        ->get();

    return view('superadmin.event.print_recap', compact('event', 'recapSubmissions'));
}

// Tambahkan method ini di dalam class SuperEventController Anda:

public function uploadCertificateTemplate(Request $request, $id)
{
    $request->validate([
        'certificate_template' => 'required|image|mimes:png|max:3072' // Wajib format PNG, Maksimal 3MB
    ]);

    $event = Event::findOrFail($id);

    if ($request->hasFile('certificate_template')) {
        // Simpan file template ke folder public disk server
        $path = $request->file('certificate_template')->store('event_certificate_templates', 'public');

        // Update data path ke database MySQL
        $event->update([
            'certificate_template_path' => $path
        ]);
    }

    return redirect()->route('superadmin.events.dashboard', $id)
        ->with('success', 'Template gambar PNG piagam penghargaan resmi berhasil diunggah!');
}
}
