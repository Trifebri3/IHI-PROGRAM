<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Models\AccountVerification;
use App\Models\Program;
use App\Models\Registration;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\MitigationTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperPowerController extends Controller
{
    public function index()
    {
        $programs = Program::all();
        
        // Count dummy users
        $dummyUsersCount = User::where('is_dummy', true)->count();

        // Mitigation settings
        $mitigationMode = SystemSetting::getVal('mitigation_mode', '0');
        $pendingTickets = MitigationTicket::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();
        
        return view('superadmin.power_panel.index', compact('programs', 'dummyUsersCount', 'mitigationMode', 'pendingTickets'));
    }

    public function generateDummyUsers(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:500',
            'password' => 'required|string|min:4'
        ]);

        $count = (int) $request->count;
        $passwordHash = Hash::make($request->password);
        $adminId = auth()->id();

        DB::transaction(function () use ($count, $passwordHash, $adminId) {
            for ($i = 1; $i <= $count; $i++) {
                $unique = Str::random(6) . '_' . time() . '_' . $i;
                $name = "Peserta Uji Coba " . $i;
                $email = "dummy_" . $unique . "@ihidummy.com";

                // 1. Create user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'is_dummy' => true,
                    'password' => $passwordHash,
                ]);
                $user->email_verified_at = now();
                $user->save();

                // 2. Assign role
                $user->assignRole('Participant');

                // 3. Create profile & address
                UserProfile::create([
                    'user_id' => $user->id,
                    'biodata_lengkap' => 'Akun Uji Coba Dummy Sistem'
                ]);

                Address::create([
                    'user_id' => $user->id,
                    'negara' => 'Indonesia',
                    'provinsi' => '-',
                    'kabupaten' => '-',
                    'kecamatan' => '-',
                    'desa' => '-',
                    'kampung' => '-',
                ]);

                // 4. Create verified AccountVerification
                AccountVerification::create([
                    'user_id' => $user->id,
                    'nik' => '1234567890123456',
                    'ktp_path' => 'default_ktp.jpg',
                    'photo_path' => 'default_photo.jpg',
                    'status' => 'verified',
                    'verified_by' => $adminId,
                    'verified_at' => now()
                ]);
            }

            AuditLog::create([
                'user_id' => $adminId,
                'action' => 'generate_dummy_users',
                'details' => "Membuat {$count} akun dummy untuk uji coba",
                'ip_address' => request()->ip()
            ]);
        });

        return redirect()->to(route('superadmin.power-panel.index'))
            ->with('success', "Berhasil men-generate {$count} akun dummy baru!");
    }

    public function deleteAllDummyUsers()
    {
        $adminId = auth()->id();
        $dummyCount = User::where('is_dummy', true)->count();

        if ($dummyCount === 0) {
            return redirect()->to(route('superadmin.power-panel.index'))
                ->with('error', "Tidak ada akun dummy untuk dihapus.");
        }

        DB::transaction(function () use ($dummyCount, $adminId) {
            // Delete users (will cascade delete registrations, profiles, addresses, verifications)
            User::where('is_dummy', true)->delete();

            AuditLog::create([
                'user_id' => $adminId,
                'action' => 'delete_all_dummy_users',
                'details' => "Menghapus seluruh ({$dummyCount}) akun dummy dari sistem",
                'ip_address' => request()->ip()
            ]);
        });

        return redirect()->to(route('superadmin.power-panel.index'))
            ->with('success', "Berhasil menghapus seluruh {$dummyCount} akun dummy dari sistem!");
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'csv_file' => 'nullable|file|mimes:csv,txt|max:5120',
            'raw_text' => 'nullable|string'
        ]);

        $parsedRows = [];

        // Try reading CSV file first
        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            if (($handle = fopen($file->getRealPath(), "r")) !== false) {
                // Read first line to detect delimiter and handle BOM
                $firstLine = fgets($handle);
                $firstLineClean = str_replace("\xEF\xBB\xBF", "", $firstLine);
                $delimiter = (strpos($firstLineClean, ';') !== false) ? ';' : ',';

                // Rewind and skip BOM if present
                rewind($handle);
                $bomCheck = fread($handle, 3);
                if ($bomCheck !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Skip header row
                fgetcsv($handle, 1000, $delimiter);
                
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    if (count($data) >= 3) {
                        $parsedRows[] = [
                            'name' => trim($data[0]),
                            'email' => trim($data[1]),
                            'password' => trim($data[2]),
                        ];
                    }
                }
                fclose($handle);
            }
        } 
        // fallback to raw copy-pasted text
        elseif ($request->filled('raw_text')) {
            $lines = explode("\n", $request->raw_text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Detect separator (Tab if copy-pasted from Excel, semicolon, or comma)
                $separator = ",";
                if (strpos($line, "\t") !== false) {
                    $separator = "\t";
                } elseif (strpos($line, ";") !== false) {
                    $separator = ";";
                }
                $data = explode($separator, $line);
                
                if (count($data) >= 3) {
                    $parsedRows[] = [
                        'name' => trim($data[0]),
                        'email' => trim($data[1]),
                        'password' => trim($data[2]),
                    ];
                }
            }
        }

        if (empty($parsedRows)) {
            return redirect()->to(route('superadmin.power-panel.index'))
                ->with('error', "Format data kosong atau tidak dikenali. Pastikan memiliki minimal 3 kolom (Nama, Email, Password).");
        }

        $successCount = 0;
        $failedCount = 0;
        $adminId = auth()->id();

        DB::transaction(function () use ($parsedRows, &$successCount, &$failedCount, $adminId) {
            foreach ($parsedRows as $row) {
                $name = $row['name'];
                $email = $row['email'];
                $password = $row['password'];

                // Skip header strings
                if (strtolower($name) === 'nama' || strtolower($name) === 'name') {
                    continue;
                }

                // Validate email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $failedCount++;
                    continue;
                }

                // Check duplicate email
                if (User::where('email', $email)->exists()) {
                    $failedCount++;
                    continue;
                }

                // Create user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
                $user->email_verified_at = now();
                $user->save();

                // Assign role
                $user->assignRole('Participant');

                // Create profile & address
                UserProfile::create([
                    'user_id' => $user->id,
                ]);

                Address::create([
                    'user_id' => $user->id,
                    'negara' => 'Indonesia',
                    'provinsi' => '-',
                    'kabupaten' => '-',
                    'kecamatan' => '-',
                    'desa' => '-',
                    'kampung' => '-',
                ]);

                // Create verified AccountVerification (bypassed since added via admin)
                AccountVerification::create([
                    'user_id' => $user->id,
                    'nik' => '1234567890123456',
                    'ktp_path' => 'default_ktp.jpg',
                    'photo_path' => 'default_photo.jpg',
                    'status' => 'verified',
                    'verified_by' => $adminId,
                    'verified_at' => now()
                ]);

                $successCount++;
            }

            AuditLog::create([
                'user_id' => $adminId,
                'action' => 'import_users_massal',
                'details' => "Mengimpor massal {$successCount} akun peserta (Gagal: {$failedCount})",
                'ip_address' => request()->ip()
            ]);
        });

        return redirect()->to(route('superadmin.power-panel.index'))
            ->with('success', "Proses Impor Selesai! Berhasil terdaftar: {$successCount} akun. Gagal/Terlewati: {$failedCount} akun.");
    }

    public function downloadCsvTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_import_akun_ihi.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it in correct columns
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Nama Lengkap', 'Email Akun', 'Password Default'], ';');
            fputcsv($file, ['Budi Santoso', 'budis@gmail.com', 'budis12345'], ';');
            fputcsv($file, ['Siti Aminah', 'sitia@gmail.com', 'sitia12345'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function forceRegisterUsers(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'emails_text' => 'required|string'
        ]);

        $program = Program::findOrFail($request->program_id);
        $stages = $program->stages()->orderBy('sequence', 'asc')->get();
        $lastStage = $stages->last();
        $emails = explode("\n", $request->emails_text);
        
        $registeredCount = 0;
        $createdCount = 0;
        $skippedCount = 0;
        $adminId = auth()->id();

        $year = date('Y');
        $baseCount = Registration::whereYear('created_at', $year)->whereNotNull('final_id_number')->count();

        DB::transaction(function () use ($emails, $program, $stages, $lastStage, $year, &$baseCount, &$registeredCount, &$createdCount, &$skippedCount, $adminId) {
            foreach ($emails as $emailLine) {
                $email = trim($emailLine);
                if (empty($email)) continue;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedCount++;
                    continue;
                }

                // 1. Find or create user
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $prefixName = explode('@', $email)[0];
                    $name = ucwords(str_replace(['.', '_', '-'], ' ', $prefixName));

                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password123'),
                    ]);
                    $user->email_verified_at = now();
                    $user->save();

                    // Assign role
                    $user->assignRole('Participant');

                    // Create profile & address
                    UserProfile::create([
                        'user_id' => $user->id,
                    ]);

                    Address::create([
                        'user_id' => $user->id,
                        'negara' => 'Indonesia',
                        'provinsi' => '-',
                        'kabupaten' => '-',
                        'kecamatan' => '-',
                        'desa' => '-',
                        'kampung' => '-',
                    ]);

                    // Create verified AccountVerification
                    AccountVerification::create([
                        'user_id' => $user->id,
                        'nik' => '1234567890123456',
                        'ktp_path' => 'default_ktp.jpg',
                        'photo_path' => 'default_photo.jpg',
                        'status' => 'verified',
                        'verified_by' => $adminId,
                        'verified_at' => now()
                    ]);

                    $createdCount++;
                }

                // 2. Register to program
                if (Registration::where('user_id', $user->id)->where('program_id', $program->id)->exists()) {
                    $skippedCount++;
                    continue;
                }

                // Auto generate final ID number
                $baseCount++;
                $finalIdNumber = 'PRG' . $year . str_pad($baseCount, 5, '0', STR_PAD_LEFT);

                $registration = Registration::create([
                    'user_id' => $user->id,
                    'program_id' => $program->id,
                    'current_stage_id' => $lastStage?->id,
                    'status' => 'passed',
                    'final_id_number' => $finalIdNumber
                ]);

                // Auto fill all stage forms with Lorem
                foreach ($stages as $stage) {
                    $formValues = [];
                    $schema = $stage->form_schema ?? [];
                    foreach ($schema as $idx => $field) {
                        $fieldType = $field['type'] ?? 'text';
                        $fieldName = $field['name'] ?? ('Field ' . ($idx + 1));
                        
                        $dummyVal = 'Lorem Ipsum';
                        if ($fieldType === 'number') {
                            $dummyVal = '123';
                        } elseif ($fieldType === 'date' || $fieldType === 'datetime') {
                            $dummyVal = date('Y-m-d');
                        } elseif ($fieldType === 'url') {
                            $dummyVal = 'https://instituthijauindonesia.or.id';
                        } elseif ($fieldType === 'file' || $fieldType === 'image') {
                            $dummyVal = 'program_submissions/dummy_lorem_file.pdf';
                        } elseif ($fieldType === 'checkbox' || $fieldType === 'radio' || $fieldType === 'select') {
                            $options = $field['options'] ?? [];
                            if (!empty($options)) {
                                $dummyVal = is_array($options) ? $options[0] : $options;
                            } else {
                                $dummyVal = 'Lorem Option';
                            }
                        }
                        
                        $formValues[] = [
                            'field_name' => $fieldName,
                            'type' => $fieldType,
                            'value' => $dummyVal
                        ];
                    }

                    \App\Models\RegistrationStageData::create([
                        'registration_id' => $registration->id,
                        'program_stage_id' => $stage->id,
                        'form_values' => $formValues,
                        'status' => 'passed',
                        'reviewer_notes' => 'Pendaftaran otomatis oleh Super Admin'
                    ]);
                }

                $registeredCount++;
            }

            AuditLog::create([
                'user_id' => $adminId,
                'action' => 'force_register_program',
                'details' => "Mendaftarkan paksa {$registeredCount} email ke program ID {$program->id} (Baru dibuat: {$createdCount})",
                'ip_address' => request()->ip()
            ]);
        });

        return redirect()->to(route('superadmin.power-panel.index'))
            ->with('success', "Proses pendaftaran selesai! Mendaftarkan {$registeredCount} peserta (Akun baru dibentuk: {$createdCount}, Terlewati/Duplikat: {$skippedCount}) ke program '{$program->name}'.");
    }

    public function toggleMitigation(Request $request)
    {
        $current = SystemSetting::getVal('mitigation_mode', '0');
        $newVal = ($current === '1') ? '0' : '1';
        SystemSetting::setVal('mitigation_mode', $newVal);

        $statusText = ($newVal === '1') ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_mitigation_mode',
            'details' => "Mengubah status tombol mitigasi menjadi: {$statusText}",
            'ip_address' => $request->ip()
        ]);

        return redirect()->to(route('superadmin.power-panel.index'))
            ->with('success', "Tombol mitigasi berhasil {$statusText}!");
    }

    public function resolveTicket(Request $request, $id)
    {
        $ticket = MitigationTicket::findOrFail($id);
        $action = $request->input('action'); // 'bypass' or 'resolve'

        DB::transaction(function () use ($ticket, $action) {
            if ($action === 'bypass') {
                $user = $ticket->user;
                $user->email_verified_at = now();
                $user->save();
            }

            $ticket->status = 'resolved';
            $ticket->resolved_by = auth()->id();
            $ticket->resolved_at = now();
            $ticket->save();
        });

        $msg = ($action === 'bypass') 
            ? "Berhasil memverifikasi email secara manual untuk {$ticket->user->name} dan menyelesaikan tiket!" 
            : "Tiket bantuan berhasil ditandai selesai!";

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'resolve_mitigation_ticket',
            'details' => "Menyelesaikan tiket bantuan #{$ticket->id} untuk user {$ticket->user->name} dengan aksi: {$action}",
            'ip_address' => $request->ip()
        ]);

        return redirect()->to(route('superadmin.power-panel.index'))->with('success', $msg);
    }
}
