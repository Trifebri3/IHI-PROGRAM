<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationStageData;
use App\Models\ProgramBiodataSubmission;
use App\Models\Address;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ParticipantProfileController extends Controller
{
    /**
     * Tampilkan database semua peserta program dengan filter operasional canggih
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        // 1. Ambil daftar Program ID yang dikelola oleh Admin aktif
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $showAll = $request->has('show_all_applicants') && $request->show_all_applicants == '1';

        // 2. Query data registrasi dasar
        $query = Registration::with(['user.profile', 'user.address', 'user.verification', 'program'])
            ->whereIn('program_id', $managedProgramIds);

        if (!$showAll) {
            $query->where('status', 'passed');
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        // 3. Terapkan Filter Pencarian (Nama / Email / NI / ID Peserta)
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'LIKE', $search)
                       ->orWhere('email', 'LIKE', $search);
                })
                ->orWhere('final_id_number', 'LIKE', $search)
                ->orWhere('user_id', $search);
            });
        }

        // 4. Terapkan Filter Program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // 5. Terapkan Filter Batch
        if ($request->filled('batch')) {
            $query->where('batch', $request->batch);
        }

        // 6. Terapkan Filter Lokasi Kegiatan
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // 7. Terapkan Filter Wilayah / Daerah
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // 8. Terapkan Filter Status Keikutsertaan
        if ($request->filled('participant_status')) {
            $query->where('participant_status', $request->participant_status);
        }

        // 10. Terapkan Filter Status Verifikasi KYC
        if ($request->filled('verification_status')) {
            $vStatus = $request->verification_status;
            $query->whereHas('user.verification', function ($q) use ($vStatus) {
                $q->where('status', $vStatus);
            });
        }

        // 11. Terapkan Filter Status Akun Alumni
        if ($request->filled('alumni_status')) {
            $isAlumni = $request->alumni_status === 'active';
            $query->whereHas('user', function($q) use ($isAlumni, $managedProgramIds) {
                if ($isAlumni) {
                    $q->whereHas('alumniPrograms', function($qp) use ($managedProgramIds) {
                        $qp->whereIn('program_id', $managedProgramIds);
                    });
                } else {
                    $q->whereDoesntHave('alumniPrograms', function($qp) use ($managedProgramIds) {
                        $qp->whereIn('program_id', $managedProgramIds);
                    });
                }
            });
        }

        // 12. Terapkan Filter Sertifikat Terbit
        if ($request->filled('certificate_status')) {
            $hasCert = $request->certificate_status === 'issued';
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

        // 13. Terapkan Filter Rentang Tanggal Pendaftaran
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // 14. Terapkan Filter Status Blokir Akun
        if ($request->filled('blocked_status')) {
            $isBlocked = $request->blocked_status === 'blocked';
            $query->whereHas('user', function ($q) use ($isBlocked) {
                $q->where('is_blocked', $isBlocked);
            });
        }

        // 15. Terapkan Sorting
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');

        if ($sort === 'province') {
            $query->leftJoin('addresses', 'registrations.user_id', '=', 'addresses.user_id')
                ->select('registrations.*')
                ->orderBy('addresses.provinsi', $order);
        } elseif ($sort === 'name') {
            $query->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                ->select('registrations.*')
                ->orderBy('users.name', $order);
        } elseif ($sort === 'final_id_number') {
            $query->orderBy('final_id_number', $order);
        } else {
            $query->orderBy('registrations.created_at', $order);
        }

        // 16. Paginate hasil
        $registrations = $query->paginate(20)->withQueryString();

        // 17. Ambil opsi filter unik dari database untuk mempermudah Dropdown
        $passedUserIdsSubquery = Registration::whereIn('program_id', $managedProgramIds)
            ->when(!$showAll, function($q) {
                $q->where('status', 'passed');
            })
            ->select('user_id');

        $provinces = Address::whereIn('user_id', $passedUserIdsSubquery)
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->distinct()
            ->orderBy('provinsi')
            ->pluck('provinsi')
            ->toArray();

        $batchesQuery = Registration::whereIn('program_id', $managedProgramIds);
        if (!$showAll) {
            $batchesQuery->where('status', 'passed');
        }
        $batches = $batchesQuery->whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch', 'desc')
            ->pluck('batch')
            ->toArray();

        $locationsQuery = Registration::whereIn('program_id', $managedProgramIds);
        if (!$showAll) {
            $locationsQuery->where('status', 'passed');
        }
        $locations = $locationsQuery->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();

        $regionsQuery = Registration::whereIn('program_id', $managedProgramIds);
        if (!$showAll) {
            $regionsQuery->where('status', 'passed');
        }
        $regions = $regionsQuery->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region')
            ->toArray();

        $programs = $isSuperAdmin 
            ? Program::orderBy('name')->get() 
            : $user->managedPrograms()->orderBy('name')->get();

        return view('adminprogram.participants.index', compact(
            'registrations', 
            'provinces', 
            'programs', 
            'batches', 
            'locations', 
            'regions'
        ));
    }

    /**
     * Tampilkan detail profile peserta & riwayat log audit perubahan
     */
    public function show($id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        // Cari registrasi berdasarkan scope otoritas program
        $registration = Registration::with([
            'user.profile',
            'user.address',
            'user.verification',
            'user.biodataValues.biodataField',
            'program',
            'currentStage'
        ])->whereIn('program_id', $managedProgramIds)->findOrFail($id);

        // Ambil data form biodata wajib program kustom
        $biodataSubmission = ProgramBiodataSubmission::where('user_id', $registration->user_id)
            ->where('program_id', $registration->program_id)
            ->first();

        // Ambil data submisi form pada seluruh tahapan program
        $stageSubmissions = RegistrationStageData::with('stage')
            ->where('registration_id', $registration->id)
            ->get();

        // Ambil riwayat log audit perubahan yang dialami oleh user ini
        $auditLogs = AuditLog::with('user')
            ->where('target_user_id', $registration->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil list field biodata
        $allFields = \App\Models\BiodataField::orderBy('id')->get();

        return view('adminprogram.participants.show', compact('registration', 'biodataSubmission', 'stageSubmissions', 'auditLogs', 'allFields'));
    }

    /**
     * Update data administratif peserta secara menyeluruh & catat log audit
     */
    public function updateProfile(Request $request, $id)
    {
        $admin = Auth::user();
        $isSuperAdmin = $admin->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $admin->managedPrograms()->pluck('programs.id')->toArray();

        $registration = Registration::with(['user.address', 'user.verification', 'user.biodataValues'])->whereIn('program_id', $managedProgramIds)->findOrFail($id);
        $user = $registration->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'final_id_number' => 'nullable|string|max:50|unique:registrations,final_id_number,' . $registration->id,
            'batch' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'participant_status' => 'required|in:active,completed,withdrawn',
            'status' => 'required|in:process,passed,failed',
            'nik' => 'nullable|string|max:30',
            'provinsi' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'desa' => 'nullable|string|max:100',
            'detail_alamat' => 'nullable|string',
            'biodata' => 'nullable|array',
        ]);

        $changes = [];

        DB::transaction(function () use ($request, $registration, $user, &$changes) {
            // 1. Update User
            if ($user->name !== $request->name) {
                $changes[] = "Nama: '{$user->name}' -> '{$request->name}'";
                $user->name = $request->name;
            }
            if ($user->email !== $request->email) {
                $changes[] = "Email: '{$user->email}' -> '{$request->email}'";
                $user->email = $request->email;
            }
            if ($user->isDirty()) {
                $user->save();
            }

            // 2. Update Registration
            if ($registration->final_id_number !== $request->final_id_number) {
                $changes[] = "Nomor Induk: '" . ($registration->final_id_number ?? 'KOSONG') . "' -> '" . ($request->final_id_number ?? 'KOSONG') . "'";
                $registration->final_id_number = $request->final_id_number;
            }
            if ($registration->batch !== $request->batch) {
                $changes[] = "Batch: '" . ($registration->batch ?? 'KOSONG') . "' -> '" . ($request->batch ?? 'KOSONG') . "'";
                $registration->batch = $request->batch;
            }
            if ($registration->location !== $request->location) {
                $changes[] = "Lokasi: '" . ($registration->location ?? 'KOSONG') . "' -> '" . ($request->location ?? 'KOSONG') . "'";
                $registration->location = $request->location;
            }
            if ($registration->region !== $request->region) {
                $changes[] = "Wilayah: '" . ($registration->region ?? 'KOSONG') . "' -> '" . ($request->region ?? 'KOSONG') . "'";
                $registration->region = $request->region;
            }
            if ($registration->participant_status !== $request->participant_status) {
                $changes[] = "Status Peserta: '{$registration->participant_status}' -> '{$request->participant_status}'";
                $registration->participant_status = $request->participant_status;
            }
            if ($registration->status !== $request->status) {
                $changes[] = "Status Kelulusan: '{$registration->status}' -> '{$request->status}'";
                $registration->status = $request->status;
            }
            if ($registration->isDirty()) {
                $registration->save();
            }

            // 3. Update Verification (NIK)
            if ($user->verification) {
                $verification = $user->verification;
                if ($verification->nik !== $request->nik) {
                    $changes[] = "NIK: '" . ($verification->nik ?? 'KOSONG') . "' -> '" . ($request->nik ?? 'KOSONG') . "'";
                    $verification->nik = $request->nik;
                    $verification->save();
                }
            } elseif ($request->filled('nik')) {
                $changes[] = "NIK: 'KOSONG' -> '{$request->nik}'";
                $user->verification()->create([
                    'nik' => $request->nik,
                    'status' => 'pending'
                ]);
            }

            // 4. Update Address
            $address = $user->address;
            if ($address) {
                $fields = ['provinsi', 'kabupaten', 'kecamatan', 'desa', 'detail_alamat'];
                $addressChanged = false;
                foreach ($fields as $field) {
                    if ($address->$field !== $request->input($field)) {
                        $changes[] = "Alamat " . ucfirst($field) . ": '" . ($address->$field ?? 'KOSONG') . "' -> '" . ($request->input($field) ?? 'KOSONG') . "'";
                        $address->$field = $request->input($field);
                        $addressChanged = true;
                    }
                }
                if ($addressChanged) {
                    $address->save();
                }
            } else {
                $user->address()->create([
                    'negara' => 'Indonesia',
                    'provinsi' => $request->provinsi ?? '',
                    'kabupaten' => $request->kabupaten ?? '',
                    'kecamatan' => $request->kecamatan ?? '',
                    'desa' => $request->desa ?? '',
                    'kampung' => '',
                    'detail_alamat' => $request->detail_alamat ?? '',
                ]);
                $changes[] = "Membuat Data Alamat Baru";
            }

            // 5. Update Custom Biodata Values
            if ($request->filled('biodata')) {
                foreach ($request->biodata as $fieldId => $value) {
                    $existing = \App\Models\UserBiodataValue::where('user_id', $user->id)
                        ->where('biodata_field_id', $fieldId)
                        ->first();
                    
                    if ($existing) {
                        if ($existing->value !== $value) {
                            $fieldObj = \App\Models\BiodataField::find($fieldId);
                            $fieldName = $fieldObj ? $fieldObj->name : "Field #{$fieldId}";
                            $changes[] = "Biodata {$fieldName}: '" . ($existing->value ?? 'KOSONG') . "' -> '{$value}'";
                            $existing->update(['value' => $value]);
                        }
                    } else {
                        $fieldObj = \App\Models\BiodataField::find($fieldId);
                        $fieldName = $fieldObj ? $fieldObj->name : "Field #{$fieldId}";
                        $changes[] = "Biodata {$fieldName}: 'KOSONG' -> '{$value}'";
                        \App\Models\UserBiodataValue::create([
                            'user_id' => $user->id,
                            'biodata_field_id' => $fieldId,
                            'value' => $value
                        ]);
                    }
                }
            }
        });

        // 6. Pencatatan Audit Log jika terdapat perubahan
        if (!empty($changes)) {
            $changesString = implode(', ', $changes);
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'update_participant_profile',
                'target_user_id' => $user->id,
                'details' => "Mengedit profil peserta (ID Reg #{$registration->id}): " . substr($changesString, 0, 500),
                'ip_address' => $request->ip()
            ]);
        }

        return redirect()->back()->with('success', 'Profil peserta berhasil diperbarui.');
    }

    /**
     * Update Nomor Induk (NI) secara inline cepat
     */
    public function updateNi(Request $request, $id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $registration = Registration::whereIn('program_id', $managedProgramIds)->findOrFail($id);

        $request->validate([
            'final_id_number' => 'nullable|string|max:50|unique:registrations,final_id_number,' . $registration->id,
        ]);

        $registration->update([
            'final_id_number' => $request->filled('final_id_number') ? strtoupper(trim($request->final_id_number)) : null
        ]);

        return redirect()->back()->with('success', 'Nomor Induk (NI) berhasil diperbarui.');
    }

    /**
     * Blokir atau buka blokir akun user peserta
     */
    public function toggleBlock(Request $request, $userId)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        // Pastikan user target memang terdaftar di program yang dikelola oleh admin ini
        $isRegisteredInManagedProgram = Registration::where('user_id', $userId)
            ->whereIn('program_id', $managedProgramIds)
            ->exists();

        if (!$isRegisteredInManagedProgram && !$isSuperAdmin) {
            abort(403, 'Anda tidak memiliki wewenang atas pengguna ini.');
        }

        $targetUser = User::findOrFail($userId);

        if ($targetUser->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa memblokir akun Anda sendiri!');
        }

        $targetUser->is_blocked = !$targetUser->is_blocked;
        $targetUser->save();

        $actionText = $targetUser->is_blocked ? 'diblokir/dinonaktifkan' : 'diaktifkan kembali';
        return redirect()->back()->with('success', "Akun peserta '{$targetUser->name}' berhasil {$actionText}.");
    }

    /**
     * Generator Nomor Induk (NI) Massal / Semi-Otomatis dengan Formula Pola Kustom
     */
    public function bulkGenerateNi(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'sort_by' => 'required|in:province,name,created_at',
            'formula_template' => 'required|string',
            'program_code' => 'nullable|string|max:20'
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        if (!in_array($request->program_id, $managedProgramIds)) {
            abort(403, 'Aksi ditolak: Program di luar wewenang pengelolaan Anda.');
        }

        $template = $request->formula_template;

        // Ambil semua registrasi yang statusnya 'passed' (Lulus) dan belum memiliki NI
        $query = Registration::with(['user.address'])
            ->where('program_id', $request->program_id)
            ->where('status', 'passed')
            ->where(function($q) {
                $q->whereNull('final_id_number')
                  ->orWhere('final_id_number', '');
            });

        // Urutkan query sesuai preferensi penyortiran
        if ($request->sort_by === 'province') {
            $query->leftJoin('addresses', 'registrations.user_id', '=', 'addresses.user_id')
                ->select('registrations.*')
                ->orderBy('addresses.provinsi', 'asc')
                ->orderBy('registrations.created_at', 'asc');
        } elseif ($request->sort_by === 'name') {
            $query->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                ->select('registrations.*')
                ->orderBy('users.name', 'asc')
                ->orderBy('registrations.created_at', 'asc');
        } else {
            $query->orderBy('registrations.created_at', 'asc');
        }

        $registrationsToProcess = $query->get();

        if ($registrationsToProcess->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ditemukan peserta berstatus Lulus (Passed) yang belum memiliki Nomor Induk.');
        }

        // Get sequence pattern N length from formula (e.g. {SEQ:4} or {SEQ:5})
        preg_match('/\{SEQ:(\d+)\}/i', $template, $seqMatches);
        $seqLength = isset($seqMatches[1]) ? (int)$seqMatches[1] : 5;

        $successCount = 0;
        DB::transaction(function () use ($registrationsToProcess, $template, $request, $seqLength, &$successCount) {
            // Base count of registrations of this program that already have final_id_number
            $baseCount = Registration::where('program_id', $request->program_id)
                ->whereNotNull('final_id_number')
                ->where('final_id_number', '!=', '')
                ->count();

            foreach ($registrationsToProcess as $index => $reg) {
                $sequence = $baseCount + $index + 1;

                // Resolve province and regency codes
                $provName = $reg->user->address->provinsi ?? '';
                $regName = $reg->user->address->kabupaten ?? '';

                $provCode = $this->getProvinceCode($provName);
                $regencyCode = $this->getRegencyCode($provCode, $regName);

                $placeholders = [
                    '{YEAR}' => date('Y'),
                    '{MONTH}' => date('m'),
                    '{PROGRAM_CODE}' => strtoupper(trim($request->program_code ?? 'PRG')),
                    '{PROV_CODE}' => $provCode,
                    '{REGENCY_CODE}' => $regencyCode,
                ];

                // Case-insensitive replace for placeholders (except SEQ:N)
                $templateWithPlaceholders = str_ireplace(array_keys($placeholders), array_values($placeholders), $template);

                // Replace sequence with padding
                $finalIdNumber = preg_replace('/\{SEQ:\d+\}/i', str_pad($sequence, $seqLength, '0', STR_PAD_LEFT), $templateWithPlaceholders);

                // Ensure uniqueness
                while (Registration::where('final_id_number', $finalIdNumber)->exists()) {
                    $sequence++;
                    $finalIdNumber = preg_replace('/\{SEQ:\d+\}/i', str_pad($sequence, $seqLength, '0', STR_PAD_LEFT), $templateWithPlaceholders);
                }

                $reg->update([
                    'final_id_number' => strtoupper($finalIdNumber)
                ]);
                $successCount++;
            }
        });

        return redirect()->back()->with('success', "Berhasil menjana secara otomatis {$successCount} Nomor Induk (NI) dengan formula untuk peserta lulus.");
    }

    /**
     * Ekspor Template CSV khusus untuk pengisian Nomor Induk (NIP) secara massal
     */
    public function exportNiTemplate(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id'
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

        // Ambil semua registrasi yang statusnya 'passed' (Lulus) untuk program ini
        $registrations = Registration::with(['user.address'])
            ->where('program_id', $request->program_id)
            ->where('status', 'passed')
            ->get();

        $filename = "template_ni_" . str_replace(' ', '_', strtolower($program->name)) . "_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID Registrasi', 'Nama Lengkap', 'Provinsi Asal', 'Nomor Induk Baru'];

        $callback = function() use($registrations, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, "sep=,\n"); // Force Excel to recognize commas in all locales
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->id,
                    $reg->user->name,
                    $reg->user->address->provinsi ?? '—',
                    $reg->final_id_number ?? ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Impor file Excel/CSV untuk update Nomor Induk secara massal
     */
    public function importNi(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('import_file');
        $filePath = $file->getRealPath();

        $updatedCount = 0;
        $errorRows = [];

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        DB::transaction(function () use ($filePath, $managedProgramIds, &$updatedCount, &$errorRows) {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                // Skip UTF-8 BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Check if the first line is "sep=..."
                $firstLine = fgets($handle);
                $delimiter = ',';
                if ($firstLine !== false) {
                    $trimmedFirstLine = trim($firstLine);
                    if (str_starts_with($trimmedFirstLine, 'sep=')) {
                        $delimiter = str_replace('sep=', '', $trimmedFirstLine);
                    } else {
                        // Not sep= line, rewind to beginning
                        rewind($handle);
                        $bom = fread($handle, 3);
                        if ($bom !== "\xEF\xBB\xBF") {
                            rewind($handle);
                        }
                    }
                }

                // Read header row
                $header = fgetcsv($handle, 1000, $delimiter);
                if (!$header || count($header) < 2) {
                    // Fallback to auto-detect if something went wrong
                    rewind($handle);
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    
                    // Skip sep= line if present again in fallback
                    $firstLine = fgets($handle);
                    if ($firstLine !== false && str_starts_with(trim($firstLine), 'sep=')) {
                        // Keep reading
                    } else {
                        rewind($handle);
                        $bom = fread($handle, 3);
                        if ($bom !== "\xEF\xBB\xBF") {
                            rewind($handle);
                        }
                    }

                    $tempHeader = fgetcsv($handle, 1000, ',');
                    if (count($tempHeader) < 2) {
                        rewind($handle);
                        $bom = fread($handle, 3);
                        if ($bom !== "\xEF\xBB\xBF") {
                            rewind($handle);
                        }
                        // Skip sep= line
                        $firstLine = fgets($handle);
                        if ($firstLine !== false && str_starts_with(trim($firstLine), 'sep=')) {
                            // Keep reading
                        } else {
                            rewind($handle);
                            $bom = fread($handle, 3);
                            if ($bom !== "\xEF\xBB\xBF") {
                                rewind($handle);
                            }
                        }
                        $delimiter = ';';
                        $header = fgetcsv($handle, 1000, ';');
                    } else {
                        $delimiter = ',';
                        $header = $tempHeader;
                    }
                }

                $rowNum = 1;
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    $rowNum++;
                    // Check if row is empty
                    if (empty($data) || count($data) < 4) continue;

                    $registrationId = trim($data[0]);
                    $newNi = trim($data[3]);

                    if (empty($registrationId) || $registrationId === 'ID Registrasi') continue;

                    $registration = Registration::find($registrationId);
                    if (!$registration) {
                        $errorRows[] = "Baris {$rowNum}: ID Registrasi '{$registrationId}' tidak ditemukan.";
                        continue;
                    }

                    // Pastikan registrasi berada dalam program yang dikelola admin
                    if (!in_array($registration->program_id, $managedProgramIds)) {
                        $errorRows[] = "Baris {$rowNum}: Anda tidak memiliki otoritas atas pendaftar ID '{$registrationId}'.";
                        continue;
                    }

                    if (empty($newNi)) {
                        // Reset NI jika kosong
                        $registration->update(['final_id_number' => null]);
                        $updatedCount++;
                        continue;
                    }

                    // Validasi unique final_id_number
                    $exists = Registration::where('final_id_number', $newNi)
                        ->where('id', '!=', $registration->id)
                        ->exists();

                    if ($exists) {
                        $errorRows[] = "Baris {$rowNum}: Nomor Induk '{$newNi}' sudah digunakan oleh peserta lain.";
                        continue;
                    }

                    $registration->update(['final_id_number' => strtoupper($newNi)]);
                    $updatedCount++;
                }
                fclose($handle);
            }
        });

        if (!empty($errorRows)) {
            $errorMsg = implode('<br>', $errorRows);
            return redirect()->back()
                ->with('success', "Berhasil memperbarui {$updatedCount} Nomor Induk.")
                ->with('warning', "Beberapa baris gagal diimpor:<br>{$errorMsg}");
        }

        return redirect()->back()->with('success', "Berhasil mengimpor & memperbarui {$updatedCount} Nomor Induk secara massal.");
    }

    /**
     * Helper resolving nama provinsi ke kode BPS 2 digit
     */
    private function getProvinceCode($provinceName)
    {
        if (empty($provinceName)) return '00';
        $path = base_path('dataalamat/data-indonesia/provinsi.json');
        if (file_exists($path)) {
            $provinces = json_decode(file_get_contents($path), true);
            foreach ($provinces as $p) {
                if (strcasecmp(trim($p['nama']), trim($provinceName)) === 0) {
                    return str_pad($p['id'], 2, '0', STR_PAD_LEFT);
                }
            }
        }
        return '00';
    }

    /**
     * Helper resolving nama kabupaten/kota ke kode BPS 4 digit
     */
    private function getRegencyCode($provCode, $regencyName)
    {
        if (empty($provCode) || empty($regencyName)) return '0000';
        $path = base_path("dataalamat/data-indonesia/kabupaten/{$provCode}.json");
        if (file_exists($path)) {
            $regencies = json_decode(file_get_contents($path), true);
            foreach ($regencies as $r) {
                $cleanName = str_ireplace(['kabupaten', 'kota', 'kab.'], '', $regencyName);
                $cleanRegName = str_ireplace(['kabupaten', 'kota', 'kab.'], '', $r['nama']);
                if (strcasecmp(trim($cleanRegName), trim($cleanName)) === 0) {
                    return str_pad($r['id'], 4, '0', STR_PAD_LEFT);
                }
            }
        }
        return $provCode . '00';
    }

    /**
     * Eksekusi tindakan massal untuk beberapa peserta terpilih
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:registrations,id',
            'bulk_action' => 'required|in:verify_kyc,mark_passed,mark_failed,activate_alumni,deactivate_access,update_status,export_csv',
            'participant_status' => 'nullable|in:active,completed,withdrawn',
            'status' => 'nullable|in:process,passed,failed'
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $managedProgramIds = $isSuperAdmin 
            ? Program::pluck('id')->toArray() 
            : $user->managedPrograms()->pluck('programs.id')->toArray();

        $registrations = Registration::with(['user.verification', 'user.address', 'program'])
            ->whereIn('id', $request->ids)
            ->whereIn('program_id', $managedProgramIds)
            ->get();

        if ($registrations->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data peserta valid yang dipilih.');
        }

        $count = 0;
        $action = $request->bulk_action;

        // 1. Penanganan khusus Ekspor CSV massal
        if ($action === 'export_csv') {
            $filename = "export_peserta_" . date('Ymd_His') . ".csv";
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $columns = ['ID Peserta', 'Nama Lengkap', 'Email', 'Program', 'Nomor Induk (NI)', 'Batch', 'Lokasi', 'Wilayah', 'Status Seleksi', 'Status Keikutsertaan'];

            $callback = function() use($registrations, $columns) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
                fputcsv($file, $columns);

                foreach ($registrations as $reg) {
                    fputcsv($file, [
                        $reg->user_id,
                        $reg->user->name,
                        $reg->user->email,
                        $reg->program->name,
                        $reg->final_id_number ?? '—',
                        $reg->batch ?? '—',
                        $reg->location ?? '—',
                        $reg->region ?? '—',
                        strtoupper($reg->status),
                        strtoupper($reg->participant_status)
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // 2. Eksekusi database tindakan massal
        DB::transaction(function () use ($registrations, $action, $request, &$count, $user) {
            foreach ($registrations as $reg) {
                switch ($action) {
                    case 'verify_kyc':
                        if ($reg->user->verification) {
                            if ($reg->user->verification->status !== 'verified') {
                                $reg->user->verification->update(['status' => 'verified']);
                                $count++;
                            }
                        } else {
                            $reg->user->verification()->create([
                                'nik' => null,
                                'status' => 'verified'
                            ]);
                            $count++;
                        }
                        break;
                    case 'mark_passed':
                        if ($reg->status !== 'passed') {
                            $reg->update(['status' => 'passed']);
                            $count++;
                        }
                        break;
                    case 'mark_failed':
                        if ($reg->status !== 'failed') {
                            $reg->update(['status' => 'failed']);
                            $count++;
                        }
                        break;
                    case 'activate_alumni':
                        // Syarat eligible: status = passed, NIP ada, data alamat lengkap, kyc verified
                        $hasAddress = $reg->user->address()->exists();
                        $hasKyc = $reg->user->verification && $reg->user->verification->status === 'verified';
                        $hasNip = !empty($reg->final_id_number);
                        
                        $isAlreadyAlumni = \App\Models\UserAlumni::where('user_id', $reg->user_id)
                            ->whereHas('alumniProgram', function($q) use($reg) {
                                $q->where('program_id', $reg->program_id);
                            })->exists();

                        if ($reg->status === 'passed' && $hasNip && $hasAddress && $hasKyc && !$isAlreadyAlumni) {
                            app(\App\Services\AlumniService::class)->registerAutoAlumni($reg);
                            $count++;
                        }
                        break;
                    case 'deactivate_access':
                        if (!$reg->user->is_blocked && $reg->user_id !== $user->id) {
                            $reg->user->update(['is_blocked' => true]);
                            $count++;
                        }
                        break;
                    case 'update_status':
                        if ($request->filled('participant_status')) {
                            $reg->update(['participant_status' => $request->participant_status]);
                            $count++;
                        }
                        break;
                }
            }

            // Catat log audit tindakan massal
            if ($count > 0) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'bulk_action_participants',
                    'target_user_id' => null,
                    'details' => "Menjalankan bulk action '{$action}' untuk {$count} peserta program.",
                    'ip_address' => $request->ip()
                ]);
            }
        });

        $actionNames = [
            'verify_kyc' => 'Verifikasi KYC Akun',
            'mark_passed' => 'Tandai Lulus Seleksi',
            'mark_failed' => 'Tandai Gugur Seleksi',
            'activate_alumni' => 'Aktivasi Akun Alumni',
            'deactivate_access' => 'Blokir Akses Akun',
            'update_status' => 'Update Status Keikutsertaan'
        ];

        $name = $actionNames[$action] ?? $action;
        return redirect()->back()->with('success', "Bulk action '{$name}' berhasil dieksekusi untuk {$count} data.");
    }
}
