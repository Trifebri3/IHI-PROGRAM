<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Models\UserBiodataValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

class PublicEventController extends Controller
{
    public function show($id)
    {
        $event = Event::findOrFail($id);
        
        $registeredCount = EventRegistration::where('event_id', $id)->count();
        $isFull = $registeredCount >= $event->quota;
        
        $alreadyRegistered = false;
        if (auth()->check()) {
            $alreadyRegistered = EventRegistration::where('event_id', $id)
                ->where('user_id', auth()->id())
                ->exists();
        }

        return view('public.event-show', compact('event', 'registeredCount', 'isFull', 'alreadyRegistered'));
    }

    public function register(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $registeredCount = EventRegistration::where('event_id', $id)->count();
        
        if ($registeredCount >= $event->quota) {
            return redirect()->back()->with('error', 'Mohon maaf, kuota peserta untuk event ini sudah penuh!');
        }

        // Validate basic fields
        $rules = [];
        if (!auth()->check()) {
            if ($event->registration_type === 'logged_in') {
                return redirect()->route('login')->with('error', 'Anda harus masuk akun terlebih dahulu untuk mendaftar event ini.');
            }
            if ($event->registration_type === 'external') {
                return redirect()->away($event->external_link);
            }
            
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_phone'] = 'required|string|max:20';
        } else {
            if ($event->registration_type === 'external') {
                return redirect()->away($event->external_link);
            }
        }

        // Validate custom form schema fields if any
        $schema = $event->form_schema ?? [];
        $formValues = [];
        
        foreach ($schema as $idx => $field) {
            $fieldName = 'field_' . $idx;
            $fieldLabel = $field['name'];
            $fieldType = $field['type'];
            $isRequired = isset($field['required']) && $field['required'];

            if ($fieldType === 'file') {
                if ($isRequired) {
                    $rules[$fieldName] = 'required|file|max:5120'; // max 5MB
                } else {
                    $rules[$fieldName] = 'nullable|file|max:5120';
                }
            } else {
                $validationType = $fieldType === 'number' ? 'numeric' : 'string';
                if ($isRequired) {
                    $rules[$fieldName] = 'required|' . $validationType;
                } else {
                    $rules[$fieldName] = 'nullable|' . $validationType;
                }
            }
        }

        $validated = $request->validate($rules);

        // Check if already registered
        if (auth()->check()) {
            $exists = EventRegistration::where('event_id', $id)
                ->where('user_id', auth()->id())
                ->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Anda sudah terdaftar dalam event ini.');
            }
        } else {
            $exists = EventRegistration::where('event_id', $id)
                ->where('guest_email', $request->guest_email)
                ->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Email ini sudah terdaftar dalam event ini.');
            }
        }

        // Process custom form inputs
        foreach ($schema as $idx => $field) {
            $fieldName = 'field_' . $idx;
            $fieldLabel = $field['name'];
            $fieldType = $field['type'];

            $val = null;
            if ($request->hasFile($fieldName)) {
                $val = $request->file($fieldName)->store('event_submissions/' . $id, 'public');
            } else {
                $val = $request->input($fieldName);
            }

            $formValues[] = [
                'label' => $fieldLabel,
                'type' => $fieldType,
                'value' => $val
            ];
        }

        // Generate unique ticket number
        do {
            $ticketNumber = 'IHI-EVT-' . strtoupper(Str::random(8));
        } while (EventRegistration::where('ticket_number', $ticketNumber)->exists());

        // Save Registration
        $registration = EventRegistration::create([
            'event_id' => $id,
            'user_id' => auth()->check() ? auth()->id() : null,
            'guest_name' => auth()->check() ? null : trim($request->guest_name),
            'guest_email' => auth()->check() ? null : trim($request->guest_email),
            'guest_phone' => auth()->check() ? null : trim($request->guest_phone),
            'ticket_number' => $ticketNumber,
            'form_values' => $formValues
        ]);

        // Send Notification Email (Simulated + Actual if SMTP ready)
        $this->sendEventTicketEmail($registration);

        return redirect()->route('public.events.ticket', $ticketNumber)
            ->with('success', 'Pendaftaran event berhasil! Harap simpan Nomor Tiket dan QR Code Anda.');
    }

    public function registerFast(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $registeredCount = EventRegistration::where('event_id', $id)->count();
        
        if ($registeredCount >= $event->quota) {
            return redirect()->back()->with('error', 'Mohon maaf, kuota peserta untuk event ini sudah penuh!');
        }

        $request->validate([
            'nomor_induk' => 'required|string|max:100',
            'nomor_hp' => 'required|string|max:50'
        ]);

        $nomorInduk = trim($request->nomor_induk);
        $nomorHp = trim($request->nomor_hp);

        // Search user through program final_id_number registration
        $progReg = \App\Models\Registration::where('final_id_number', $nomorInduk)->first();
        if (!$progReg) {
            return redirect()->back()->with('error', 'Data Nomor Induk Anda tidak terdaftar di platform kami.');
        }

        $user = $progReg->user;

        // Verify Phone number across user biodata values
        $phoneMatches = UserBiodataValue::where('user_id', $user->id)
            ->whereHas('field', function($query) {
                $query->where('name', 'like', '%hp%')
                    ->orWhere('name', 'like', '%whatsapp%')
                    ->orWhere('name', 'like', '%telepon%')
                    ->orWhere('name', 'like', '%phone%');
            })
            ->where('value', 'like', '%' . $nomorHp . '%')
            ->exists();

        if (!$phoneMatches) {
            return redirect()->back()->with('error', 'Nomor HP tidak cocok dengan data profil terdaftar.');
        }

        // Verify event double registration
        $exists = EventRegistration::where('event_id', $id)
            ->where('user_id', $user->id)
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Akun Anda sudah terdaftar dalam event ini.');
        }

        // Generate unique ticket number
        do {
            $ticketNumber = 'IHI-EVT-' . strtoupper(Str::random(8));
        } while (EventRegistration::where('ticket_number', $ticketNumber)->exists());

        // Save Registration
        $registration = EventRegistration::create([
            'event_id' => $id,
            'user_id' => $user->id,
            'ticket_number' => $ticketNumber,
            'form_values' => [] // fast registration has empty initial custom fields
        ]);

        // Send Email
        $this->sendEventTicketEmail($registration);

        return redirect()->route('public.events.ticket', $ticketNumber)
            ->with('success', 'Pendaftaran cepat berhasil! Akun Anda terdeteksi.');
    }

    public function showTicket($ticketNumber)
    {
        $registration = EventRegistration::with('event')->where('ticket_number', $ticketNumber)->firstOrFail();
        
        // Generate QR Code inline for display
        $options = new QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
            'scale' => 5,
            'imageBase64' => true,
        ]);
        $qrcode = new QRCode($options);
        // The QR code contains the scan check-in link for admins!
        $scanUrl = route('superadmin.events.scan-checkin', $registration->ticket_number);
        $qrCodeUri = $qrcode->render($scanUrl);

        return view('public.event-ticket', compact('registration', 'qrCodeUri'));
    }

    public function showAttendance($id)
    {
        $event = Event::findOrFail($id);
        return view('public.event-attendance', compact('event'));
    }

    public function verifyTicketForAttendance(Request $request, $id)
    {
        $request->validate(['ticket_number' => 'required|string']);
        $ticket = trim($request->ticket_number);

        $registration = EventRegistration::where('event_id', $id)
            ->where('ticket_number', $ticket)
            ->first();

        if (!$registration) {
            return response()->json(['success' => false, 'message' => 'Nomor tiket tidak terdaftar untuk event ini.']);
        }

        $name = $registration->user ? $registration->user->name : $registration->guest_name;
        $email = $registration->user ? $registration->user->email : $registration->guest_email;

        return response()->json([
            'success' => true,
            'name' => $name,
            'email' => $email,
            'ticket_number' => $ticket,
            'already_attended' => !is_null($registration->attended_at)
        ]);
    }

    public function submitAttendance(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        
        $rules = [];
        $registration = null;
        $ticket = null;

        if ($event->attendance_require_ticket) {
            $request->validate(['ticket_number' => 'required|string']);
            $ticket = trim($request->ticket_number);
            $registration = EventRegistration::where('event_id', $id)
                ->where('ticket_number', $ticket)
                ->first();
            if (!$registration) {
                return redirect()->back()->with('error', 'Nomor tiket tidak terdaftar untuk event ini.')->withInput();
            }
        } else {
            // Open self-attendance (Google Form Style)
            if (!auth()->check()) {
                $rules['guest_name'] = 'required|string|max:255';
                $rules['guest_email'] = 'required|email|max:255';
                $rules['guest_phone'] = 'required|string|max:20';
            }
        }

        // Validate custom attendance schema fields if any
        $schema = $event->attendance_form_schema ?? [];

        foreach ($schema as $idx => $field) {
            $fieldName = 'field_' . $idx;
            $fieldLabel = $field['name'];
            $fieldType = $field['type'];
            $isRequired = isset($field['required']) && $field['required'];

            if ($fieldType === 'file') {
                if ($isRequired) {
                    $rules[$fieldName] = 'required|file|max:5120';
                } else {
                    $rules[$fieldName] = 'nullable|file|max:5120';
                }
            } else {
                $validationType = $fieldType === 'number' ? 'numeric' : 'string';
                if ($isRequired) {
                    $rules[$fieldName] = 'required|' . $validationType;
                } else {
                    $rules[$fieldName] = 'nullable|' . $validationType;
                }
            }
        }

        $validated = $request->validate($rules);

        // Process answers
        $formValues = [];
        foreach ($schema as $idx => $field) {
            $fieldName = 'field_' . $idx;
            $fieldLabel = $field['name'];
            $fieldType = $field['type'];

            $val = null;
            if ($request->hasFile($fieldName)) {
                $val = $request->file($fieldName)->store('event_attendance_files/' . $id, 'public');
            } else {
                $val = $request->input($fieldName);
            }

            $formValues[] = [
                'label' => $fieldLabel,
                'type' => $fieldType,
                'value' => $val
            ];
        }

        if ($event->attendance_require_ticket) {
            // Save attendance
            $registration->update([
                'attended_at' => now(),
                'attendance_form_values' => $formValues
            ]);
            $ticket = $registration->ticket_number;
        } else {
            // Create a registration + checkin on the fly
            do {
                $ticket = 'IHI-EVT-SELF-' . strtoupper(Str::random(6));
            } while (EventRegistration::where('ticket_number', $ticket)->exists());

            $registration = EventRegistration::create([
                'event_id' => $id,
                'user_id' => auth()->check() ? auth()->id() : null,
                'guest_name' => auth()->check() ? null : trim($request->guest_name),
                'guest_email' => auth()->check() ? null : trim($request->guest_email),
                'guest_phone' => auth()->check() ? null : trim($request->guest_phone),
                'ticket_number' => $ticket,
                'attended_at' => now(),
                'form_values' => [],
                'attendance_form_values' => $formValues
            ]);
        }

        $name = $registration->user ? $registration->user->name : $registration->guest_name;

        return redirect()->back()->with([
            'success' => 'Absensi & evaluasi Anda berhasil terekam! Terima kasih.',
            'claimed' => true,
            'ticket_number' => $ticket,
            'attendee_name' => $name
        ]);
    }

    private function sendEventTicketEmail($registration)
    {
        try {
            $email = $registration->user ? $registration->user->email : $registration->guest_email;
            $name = $registration->user ? $registration->user->name : $registration->guest_name;
            
            // Build inline base64 QR Code
            $options = new QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
                'scale' => 5,
                'imageBase64' => true,
            ]);
            $qrcode = new QRCode($options);
            $scanUrl = route('superadmin.events.scan-checkin', $registration->ticket_number);
            $qrDataUri = $qrcode->render($scanUrl);

            // Log entry
            Log::info("=== EVENT REGISTRATION SUCCESS EMAIL ===");
            Log::info("Recipient: {$email} ({$name})");
            Log::info("Event: {$registration->event->title}");
            Log::info("Ticket: {$registration->ticket_number}");
            Log::info("=========================================");

            if (config('mail.mailers.smtp.host')) {
                Mail::send([], [], function ($message) use ($email, $name, $registration, $qrDataUri) {
                    $message->to($email, $name)
                        ->subject("Tiket Pendaftaran Event: " . $registration->event->title)
                        ->html("
                            <div style='font-family: sans-serif; padding: 25px; max-width: 600px; margin: auto; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff;'>
                                <div style='text-align: center; margin-bottom: 20px;'>
                                    <span style='font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #047857; background-color: #ecfdf5; padding: 6px 16px; border-radius: 9999px;'>Institut Hijau Indonesia</span>
                                </div>
                                <h2 style='color: #1f2937; font-size: 20px; font-weight: 800; text-align: center; margin-top: 10px; margin-bottom: 5px;'>Pendaftaran Event Berhasil!</h2>
                                <p style='color: #4b5563; font-size: 14px; text-align: center;'>Halo <strong>{$name}</strong>, Anda resmi terdaftar dalam kegiatan:</p>
                                
                                <div style='border: 1px solid #e5e7eb; border-radius: 12px; padding: 15px; margin: 20px 0; background-color: #f9fafb;'>
                                    <h3 style='margin: 0 0 10px 0; color: #047857; font-size: 15px; font-weight: 800;'>{$registration->event->title}</h3>
                                    <p style='margin: 4px 0; font-size: 12px; color: #4b5563;'><strong>Nomor Tiket:</strong> <span style='font-family: monospace; font-weight: bold; color: #047857;'>{$registration->ticket_number}</span></p>
                                    <p style='margin: 4px 0; font-size: 12px; color: #4b5563;'><strong>Tanggal:</strong> " . date('d M Y', strtotime($registration->event->event_date)) . " - {$registration->event->event_time} WIB</p>
                                    <p style='margin: 4px 0; font-size: 12px; color: #4b5563;'><strong>Tempat:</strong> {$registration->event->location}</p>
                                </div>

                                <p style='color: #4b5563; font-size: 13px; text-align: center; margin-bottom: 10px;'>Gunakan QR Code di bawah ini untuk ditunjukkan kepada petugas saat absensi check-in:</p>
                                <div style='text-align: center; margin: 15px 0;'>
                                    <img src='{$qrDataUri}' style='border: 1px solid #e5e7eb; padding: 8px; border-radius: 12px; width: 180px; height: 180px;' />
                                </div>
                                
                                <p style='color: #4b5563; font-size: 12px; text-align: center;'>Jika Anda mengikuti kelas online, tautan pertemuan dapat diakses di bagian tempat di atas.</p>
                            </div>
                        ");
                });
            }
        } catch (\Exception $e) {
            Log::error("Failed to send event ticket email: " . $e->getMessage());
        }
    }
}
