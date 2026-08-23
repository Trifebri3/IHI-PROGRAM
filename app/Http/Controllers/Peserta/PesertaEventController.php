<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class PesertaEventController extends Controller
{
    public function index(Request $request)
    {
        // Engine Filter & Sorting Native
        $query = Event::withCount('registrations');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->sort === 'soonest') {
            $query->orderBy('event_date', 'asc'); // Acara terdekat
        } elseif ($request->sort === 'latest') {
            $query->orderBy('created_at', 'desc'); // Event baru dibuat
        } else {
            $query->orderBy('id', 'desc'); // Default terbaru
        }

        $events = $query->get();

        // Ambil data registrasi lengkap milik user
        $myRegistrations = EventRegistration::where('user_id', auth()->id())->get()->keyBy('event_id');

        // SINKRONISASI KUNCI: Buat array ID event yang sudah diikuti agar tidak memicu error Undefined variable lagi
        $myJoinedEvents = $myRegistrations->keys()->toArray();

        return view('pesertabiasa.event.index', compact('events', 'myRegistrations', 'myJoinedEvents'));
    }

    public function showRegisterForm($id)
    {
        $event = Event::findOrFail($id);
        if (EventRegistration::where('user_id', auth()->id())->where('event_id', $id)->exists()) {
            return redirect()->route('events.catalog')->with('error', 'Anda sudah terdaftar di event ini!');
        }
        return view('pesertabiasa.event.register_form', compact('event'));
    }

    public function submitRegistration(Request $request, $id)
    {
        $event = Event::withCount('registrations')->findOrFail($id);
        if ($event->registrations_count >= $event->quota) {
            return redirect()->route('events.catalog')->with('error', 'Kuota pendaftaran event sudah penuh!');
        }

        $rules = [];
        foreach ($event->form_schema ?? [] as $index => $field) {
            $inputName = "field_" . $index;
            $rules[$inputName] = $field['required'] ? 'required' : 'nullable';
            $rules[$inputName] .= ($field['type'] === 'file') ? '|file|mimes:pdf,jpg,png|max:2048' : (($field['type'] === 'number') ? '|numeric' : '|string|max:500');
        }
        $request->validate($rules);

        $processedValues = [];
        foreach ($event->form_schema ?? [] as $index => $field) {
            $inputName = "field_" . $index;
            $value = $request->input($inputName);

            if ($field['type'] === 'file' && $request->hasFile($inputName)) {
                $value = $request->file($inputName)->store('event_submissions', 'public');
            }

            $processedValues[] = ['label' => $field['name'], 'type' => $field['type'], 'value' => $value];
        }

        EventRegistration::create([
            'user_id' => auth()->id(),
            'event_id' => $id,
            'form_values' => $processedValues
        ]);

        return redirect()->route('events.dashboard', $id)->with('success', 'Klaim tiket berhasil! Selamat datang di Ruang Utama Event.');
    }

    // --- 🚀 METHOD BARU: RUANG UTAMA DEDICATED EVENT DASHBOARD PESERTA ---
    public function showDashboard($id)
    {
        $event = Event::findOrFail($id);

        // Pastikan dia memang terdaftar sah, jika tidak punya tiket tidak boleh intip halaman ini
        $myRegistration = EventRegistration::where('user_id', auth()->id())
            ->where('event_id', $id)
            ->firstOrFail();

        return view('pesertabiasa.event.dashboard', compact('event', 'myRegistration'));
    }

    public function submitAttendance(Request $request, $id)
    {
        $request->validate(['token_input' => 'required|string']);
        $event = Event::findOrFail($id);

        if (!$event->is_attendance_open || empty($event->attendance_token)) {
            return redirect()->route('events.dashboard', $id)->with('error', 'Sesi pengisian token absensi belum dibuka oleh panitia!');
        }

        if (strtoupper(trim($request->token_input)) !== strtoupper($event->attendance_token)) {
            return redirect()->route('events.dashboard', $id)->with('error', 'Kode Token salah! Presensi gagal diverifikasi.');
        }

        $registration = EventRegistration::where('user_id', auth()->id())->where('event_id', $id)->firstOrFail();
        $registration->update(['attended_at' => now()]);

        return redirect()->route('events.dashboard', $id)->with('success', 'Absensi Berhasil! Kehadiran Anda sah tersimpan di sistem.');
    }


    // Tambahkan method ini di dalam class PesertaEventController Anda:

public function printCertificate($id)
{
    $event = Event::findOrFail($id);

    // Pastikan user terdaftar dan STATUSNYA WAJIB HADIR (attended_at tidak null)
    $myRegistration = EventRegistration::where('user_id', auth()->id())
        ->where('event_id', $id)
        ->whereNotNull('attended_at')
        ->firstOrFail();

    // Jika admin belum upload template piagam, tendang balik
    if (!$event->certificate_template_path) {
        return redirect()->route('events.dashboard', $id)->with('error', 'Panitia belum menerbitkan dokumen berkas piagam penghargaan.');
    }

    // --- LOGIC GENERATOR NOMOR SERTIFIKAT UNIK ENTERPRISE ---
    // Pola: CERT / ID REGISTRASI / ID EVENT / TAHUN (Cth: CERT-0021-03-2026)
    $certNumber = 'CERT-' . str_pad($myRegistration->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($event->id, 2, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($event->event_date));

    return view('pesertabiasa.event.certificate_print', compact('event', 'myRegistration', 'certNumber'));
}

}
