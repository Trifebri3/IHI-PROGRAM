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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'quota' => 'required|integer|min:1',
            'registration_type' => 'required|in:public,external,logged_in',
            'external_link' => 'nullable|required_if:registration_type,external|url|max:500',
            'banner' => 'nullable|image|max:3072'
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('event_banners', 'public');
        }

        Event::create([
            'title' => trim($request->title),
            'description' => $request->description,
            'location' => trim($request->location),
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'quota' => $request->quota,
            'registration_type' => $request->registration_type,
            'external_link' => $request->registration_type === 'external' ? $request->external_link : null,
            'banner_path' => $bannerPath,
            'form_schema' => []
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
        'certificate_template' => 'nullable|image|mimes:png|max:3072',
        'certificate_link' => 'nullable|url|max:500'
    ]);

    $event = Event::findOrFail($id);

    $data = [
        'certificate_link' => $request->certificate_link
    ];

    if ($request->hasFile('certificate_template')) {
        $path = $request->file('certificate_template')->store('event_certificate_templates', 'public');
        $data['certificate_template_path'] = $path;
    }

    $event->update($data);

    return redirect()->route('superadmin.events.dashboard', $id)
        ->with('success', 'Pengaturan sertifikat digital berhasil diperbarui!');
}

public function storeAttendanceFormSchema(Request $request, $id)
{
    $request->validate(['field_name' => 'required|string|max:100', 'field_type' => 'required|in:text,number,file']);
    $event = Event::findOrFail($id);

    $schema = $event->attendance_form_schema ?? [];
    $schema[] = [
        'name' => trim($request->field_name),
        'type' => $request->field_type,
        'required' => $request->has('is_required')
    ];

    $event->update(['attendance_form_schema' => $schema]);
    return redirect()->route('superadmin.events.dashboard', $id)->with('success', 'Atribut Formulir Absensi sukses dipasang!');
}

public function deleteAttendanceFormSchema($id, $index)
{
    $event = Event::findOrFail($id);
    $schema = $event->attendance_form_schema ?? [];

    if (isset($schema[$index])) {
        unset($schema[$index]);
    }

    $event->update(['attendance_form_schema' => array_values($schema)]);
    return redirect()->route('superadmin.events.dashboard', $id)->with('success', 'Atribut Formulir Absensi berhasil dicabut.');
}

    public function scanCheckin(Request $request, $ticketNumber)
    {
        $registration = EventRegistration::with('event')->where('ticket_number', $ticketNumber)->first();
        
        if (!$registration) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan untuk event ini.']);
            }
            abort(404, 'Tiket tidak ditemukan.');
        }

        $name = $registration->user ? $registration->user->name : $registration->guest_name;

        if ($registration->attended_at) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'already_attended' => true,
                    'name' => $name,
                    'ticket' => $ticketNumber,
                    'message' => "Tiket {$ticketNumber} atas nama {$name} sudah melakukan absensi sebelumnya."
                ]);
            }
        } else {
            $registration->update(['attended_at' => now()]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'already_attended' => false,
                'name' => $name,
                'ticket' => $ticketNumber,
                'message' => "Absensi {$name} ({$ticketNumber}) berhasil dicatat!"
            ]);
        }

        return view('superadmin.event.scan-success', [
            'registration' => $registration,
            'name' => $name,
            'event' => $registration->event
        ]);
    }

    public function updateAttendanceSettings(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $request->validate([
            'attendance_method' => 'required|in:scan,token,form',
        ]);

        $event->update([
            'attendance_method' => $request->attendance_method,
            'attendance_require_ticket' => $request->has('attendance_require_ticket') ? (bool)$request->attendance_require_ticket : false
        ]);

        return redirect()->route('superadmin.events.dashboard', $id)->with('success', 'Metode & Kebijakan Absensi berhasil diperbarui!');
    }

    public function showScanner($id)
    {
        $event = Event::findOrFail($id);
        return view('superadmin.event.scanner', compact('event'));
    }
}
