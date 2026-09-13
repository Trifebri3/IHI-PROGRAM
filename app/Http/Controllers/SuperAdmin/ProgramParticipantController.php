<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationStageData;
use App\Models\ProgramBiodataSubmission;
use App\Models\Address;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProgramParticipantController extends Controller
{
    /**
     * Memastikan hanya Super Admin yang memiliki akses
     */
    protected function authorizeSuperAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Akses terbatas hanya untuk Super Admin.');
        }
    }

    /**
     * Tampilkan halaman Partisipan Program:
     * - Jika belum memilih program: Tampilkan Program Hub (Kartu semua program + metrik pendaftar)
     * - Jika memilih program: Tampilkan tabel spreadsheet partisipan program terpilih
     */
    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        // 1. Ambil semua program beserta statistik pendaftar
        $allPrograms = Program::withCount([
            'registrations as total_registrations',
            'registrations as passed_count' => function ($q) {
                $q->where('status', 'passed');
            },
            'registrations as submitted_count' => function ($q) {
                $q->whereIn('status', ['submitted', 'under_review']);
            },
            'registrations as draft_count' => function ($q) {
                $q->where('status', 'draft');
            },
            'registrations as rejected_count' => function ($q) {
                $q->where('status', 'rejected');
            },
        ])->orderBy('id', 'desc')->get();

        $selectedProgramId = $request->query('program_id');
        $selectedProgram = null;
        $registrations = null;
        $provinces = collect();
        $availableTags = collect();
        $stats = null;

        // 2. Jika program dipilih, ambil data partisipannya
        if ($selectedProgramId) {
            $selectedProgram = Program::with(['stages'])->findOrFail($selectedProgramId);

            // Metrik pendaftar untuk program yang dipilih
            $totalAllBase = Registration::where('program_id', $selectedProgramId);
            $passedBase   = Registration::where('program_id', $selectedProgramId)->where('status', 'passed');
            $stats = [
                'total_all'        => (clone $totalAllBase)->count(),
                'total_passed'     => (clone $passedBase)->count(),
                'has_ni'           => (clone $passedBase)->whereNotNull('final_id_number')->where('final_id_number', '!=', '')->where('final_id_number', '!=', '-')->count(),
                'no_ni'            => (clone $passedBase)->where(function ($q) {
                    $q->whereNull('final_id_number')->orWhere('final_id_number', '')->orWhere('final_id_number', '-');
                })->count(),
                'changed_password' => (clone $passedBase)->whereHas('user', function ($q) {
                    $q->where('must_change_password', false);
                })->count(),
                'default_password' => (clone $passedBase)->whereHas('user', function ($q) {
                    $q->where('must_change_password', true);
                })->count(),
                'unverified_email' => User::whereHas('registrations', function ($q) use ($selectedProgramId) {
                    $q->where('program_id', $selectedProgramId);
                })->whereNull('email_verified_at')->count(),
                'verified_email'   => User::whereHas('registrations', function ($q) use ($selectedProgramId) {
                    $q->where('program_id', $selectedProgramId);
                })->whereNotNull('email_verified_at')->count(),
            ];

            // Query pendaftar: HANYA MENAMPILKAN PESERTA YANG SUDAH LOLOS (STATUS: PASSED)
            $regQuery = Registration::with([
                'user.address',
                'user.profile',
                'user.verification',
                'user.biodataValues.biodataField',
                'currentStage',
                'program'
            ])->where('registrations.program_id', $selectedProgramId)
              ->where('registrations.status', 'passed');

            // Filter Pencarian (Nama, Email, NI, ID Registrasi)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $regQuery->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('registrations.final_id_number', 'like', "%{$search}%")
                    ->orWhere('registrations.id', $search);
                });
            }

            // Filter Status Nomor Induk (Sudah Ada NI / Belum Ada NI)
            if ($request->filled('ni_status') && $request->ni_status !== 'all') {
                if ($request->ni_status === 'has_ni') {
                    $regQuery->whereNotNull('registrations.final_id_number')
                             ->where('registrations.final_id_number', '!=', '')
                             ->where('registrations.final_id_number', '!=', '-');
                } elseif ($request->ni_status === 'no_ni') {
                    $regQuery->where(function ($q) {
                        $q->whereNull('registrations.final_id_number')
                          ->orWhere('registrations.final_id_number', '')
                          ->orWhere('registrations.final_id_number', '-');
                    });
                }
            }

            // Filter Status Password (sudah ganti / belum ganti)
            if ($request->filled('password_status') && $request->password_status !== 'all') {
                $mustChange = ($request->password_status === 'belum_ganti');
                $regQuery->whereHas('user', function ($q) use ($mustChange) {
                    $q->where('must_change_password', $mustChange);
                });
            }

            // Filter Status Verifikasi Email (sudah / belum)
            if ($request->filled('email_verified_status') && $request->email_verified_status !== 'all') {
                if ($request->email_verified_status === 'verified') {
                    $regQuery->whereHas('user', function ($q) {
                        $q->whereNotNull('email_verified_at');
                    });
                } elseif ($request->email_verified_status === 'unverified') {
                    $regQuery->whereHas('user', function ($q) {
                        $q->whereNull('email_verified_at');
                    });
                }
            }

            // Filter Tahapan
            if ($request->filled('stage_id') && $request->stage_id !== 'all') {
                $regQuery->where('current_stage_id', $request->stage_id);
            }

            // Filter Wilayah (Provinsi)
            if ($request->filled('provinsi') && $request->provinsi !== 'all') {
                $addressUserIds = Address::where('provinsi', $request->provinsi)->pluck('user_id');
                $regQuery->whereIn('registrations.user_id', $addressUserIds);
            }

            // Filter Tag / Kategori Peserta (misal: pokja, pesertabiasa, atau tanpa tag)
            if ($request->filled('tag') && $request->tag !== 'all') {
                if ($request->tag === 'none') {
                    $regQuery->where(function ($q) {
                        $q->whereNull('registrations.tags')
                          ->orWhere('registrations.tags', '')
                          ->orWhere('registrations.tags', '-');
                    });
                } else {
                    $tagVal = trim($request->tag);
                    $regQuery->where('registrations.tags', 'like', "%{$tagVal}%");
                }
            }

            // Pengurutan (Sorting)
            $sort = $request->query('sort', 'provinsi_nama'); // Default urut Provinsi lalu Nama
            if ($sort === 'provinsi_nama') {
                $regQuery->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                         ->leftJoin('addresses', 'users.id', '=', 'addresses.user_id')
                         ->select('registrations.*')
                         ->orderByRaw("CASE WHEN addresses.provinsi IS NULL OR addresses.provinsi = '' OR addresses.provinsi = '-' THEN 1 ELSE 0 END ASC")
                         ->orderBy('addresses.provinsi', 'ASC')
                         ->orderBy('users.name', 'ASC');
            } elseif ($sort === 'nama_asc') {
                $regQuery->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                         ->select('registrations.*')
                         ->orderBy('users.name', 'ASC');
            } elseif ($sort === 'ni_asc') {
                $regQuery->orderByRaw("COALESCE(registrations.final_id_number, 'ZZZ') ASC");
            } else {
                $regQuery->latest('registrations.id');
            }

            $registrations = $regQuery->paginate(25)->withQueryString();

            // Ambil daftar provinsi unik untuk dropdown filter (khusus peserta lolos)
            $participantUserIds = Registration::where('program_id', $selectedProgramId)
                ->where('status', 'passed')
                ->pluck('user_id');
            $provinces = Address::whereIn('user_id', $participantUserIds)
                ->whereNotNull('provinsi')
                ->where('provinsi', '!=', '')
                ->distinct()
                ->pluck('provinsi')
                ->sort()
                ->values();

            // Ambil daftar tag unik yang sudah ada di program ini
            $availableTags = Registration::where('program_id', $selectedProgramId)
                ->whereNotNull('tags')
                ->where('tags', '!=', '')
                ->where('tags', '!=', '-')
                ->pluck('tags')
                ->flatMap(function ($t) {
                    return explode(',', $t);
                })
                ->map(fn($t) => trim($t))
                ->filter()
                ->unique()
                ->values();
        }

        return view('superadmin.program_participants.index', compact(
            'allPrograms',
            'selectedProgram',
            'registrations',
            'provinces',
            'availableTags',
            'stats'
        ));
    }

    /**
     * Ekspor data partisipan program ke format spreadsheet Excel (.csv berformat BOM UTF-8)
     * Mendukung scope: 'all' (Semua pendaftar/user) atau 'passed' (Khusus lolos)
     * Kolom terstruktur: No, Nomor Induk (NI), Nama Lengkap Peserta, Email Akun, dan data lainnya.
     * Default terurut: Provinsi (A-Z) -> Nama Peserta (A-Z)
     */
    public function exportExcel($programId, Request $request)
    {
        $this->authorizeSuperAdmin();

        $program = Program::findOrFail($programId);
        $scope = $request->query('scope', 'all'); // Default 'all' untuk mengunduh semua data user

        // 1. Ambil definisi biodata fields tambahan (global) jika ada
        $biodataFields = DB::table('biodata_fields')->orderBy('id')->get();
        $standardBioKeys = [
            'whatsapp', 'telepon', 'hp',
            'jenis kelamin', 'gender',
            'tanggal lahir', 'tgl lahir', 'birth',
            'agama', 'religion',
            'pendidikan terakhir',
            'status pendidikan',
            'asal sekolah', 'perguruan tinggi', 'kampus', 'universitas',
            'jurusan', 'program studi', 'prodi',
            'kesibukan',
            'kontak darurat', 'emergency',
            'instagram', 'ig'
        ];
        $extraBioFields = $biodataFields->filter(function($f) use ($standardBioKeys) {
            $n = strtolower($f->name);
            foreach ($standardBioKeys as $sk) {
                if (str_contains($n, $sk)) return false;
            }
            return true;
        });

        // 2. Query data pendaftaran
        $regQuery = Registration::with([
            'user.address',
            'user.profile',
            'user.verification',
            'user.biodataValues.biodataField',
            'currentStage'
        ])->where('registrations.program_id', $programId);

        // Filter cakupan (scope): jika 'passed', hanya pendaftar lolos. Jika 'all', semua status.
        if ($scope === 'passed' || $request->query('status') === 'passed') {
            $regQuery->where('registrations.status', 'passed');
        } elseif ($request->filled('status') && $request->status !== 'all') {
            $regQuery->where('registrations.status', $request->status);
        }

        // Filter Tag: HANYA filter tag jika secara eksplisit diminta (filter_by_tag=1) ATAU jika scope bukan 'all'
        $shouldFilterTag = $request->boolean('filter_by_tag') || ($scope !== 'all' && $request->filled('tag') && $request->tag !== 'all');
        if ($shouldFilterTag && $request->filled('tag') && $request->tag !== 'all') {
            if ($request->tag === 'none') {
                $regQuery->where(function ($q) {
                    $q->whereNull('registrations.tags')
                      ->orWhere('registrations.tags', '')
                      ->orWhere('registrations.tags', '-');
                });
            } else {
                $regQuery->where('registrations.tags', 'like', "%{$request->tag}%");
            }
        }

        // Sorting: default urut per Provinsi lalu per Nama
        if ($request->query('sort') === 'latest') {
            $regQuery->latest('registrations.id');
        } elseif ($request->query('sort') === 'nama_asc') {
            $regQuery->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                     ->select('registrations.*')
                     ->orderBy('users.name', 'ASC');
        } elseif ($request->query('sort') === 'ni_asc') {
            $regQuery->orderByRaw("COALESCE(registrations.final_id_number, 'ZZZ') ASC");
        } else {
            $regQuery->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                     ->leftJoin('addresses', 'users.id', '=', 'addresses.user_id')
                     ->select('registrations.*')
                     ->orderByRaw("CASE WHEN addresses.provinsi IS NULL OR addresses.provinsi = '' OR addresses.provinsi = '-' THEN 1 ELSE 0 END ASC")
                     ->orderBy('addresses.provinsi', 'ASC')
                     ->orderBy('users.name', 'ASC');
        }

        $registrations = $regQuery->get();
        $regIds = $registrations->pluck('id')->toArray();
        $userIds = $registrations->pluck('user_id')->filter()->unique()->toArray();

        // 3. Kumpulkan seluruh Pertanyaan & Jawaban Formulir Tahapan Seleksi (registration_stage_data)
        $allStageFieldNames = [];
        $stageAnswersByRegId = [];
        if (!empty($regIds)) {
            $stageRows = DB::table('registration_stage_data')
                ->join('program_stages', 'registration_stage_data.program_stage_id', '=', 'program_stages.id')
                ->whereIn('registration_stage_data.registration_id', $regIds)
                ->whereNotNull('registration_stage_data.form_values')
                ->orderBy('program_stages.id', 'asc')
                ->orderBy('registration_stage_data.id', 'asc')
                ->select('registration_stage_data.registration_id', 'registration_stage_data.form_values')
                ->get();

            foreach ($stageRows as $sr) {
                $vals = json_decode($sr->form_values, true);
                if (is_array($vals)) {
                    foreach ($vals as $item) {
                        $fName = trim($item['field_name'] ?? '');
                        if ($fName !== '') {
                            if (!in_array($fName, $allStageFieldNames)) {
                                $allStageFieldNames[] = $fName;
                            }
                            $v = $item['value'] ?? '';
                            if (is_array($v)) {
                                $v = implode(', ', $v);
                            }
                            $v = trim((string)$v);
                            // Jika nilai berupa file upload submission, ubah menjadi URL lengkap
                            if (str_starts_with($v, 'program_submissions/') || str_starts_with($v, 'uploads/')) {
                                $v = asset('storage/' . $v);
                            }
                            // Bersihkan line break agar baris CSV tetap konsisten
                            $v = str_replace(["\r\n", "\r", "\n"], " ", $v);
                            $stageAnswersByRegId[$sr->registration_id][$fName] = $v;
                        }
                    }
                }
            }
        }

        // 4. Kumpulkan Jawaban Formulir Biodata Khusus Program (program_biodata_submissions) jika ada
        $allProgBioFieldNames = [];
        $progBioAnswersByUserId = [];
        if (!empty($userIds)) {
            $bioSubmissions = DB::table('program_biodata_submissions')
                ->where('program_id', $programId)
                ->whereIn('user_id', $userIds)
                ->get();

            foreach ($bioSubmissions as $bs) {
                $ans = json_decode($bs->submitted_answers, true);
                if (is_array($ans)) {
                    foreach ($ans as $k => $v) {
                        $label = trim((string)$k);
                        if ($label !== '') {
                            if (!in_array($label, $allProgBioFieldNames)) {
                                $allProgBioFieldNames[] = $label;
                            }
                            $valStr = is_array($v) ? implode(', ', $v) : trim((string)$v);
                            if (str_starts_with($valStr, 'program_submissions/') || str_starts_with($valStr, 'uploads/')) {
                                $valStr = asset('storage/' . $valStr);
                            }
                            $valStr = str_replace(["\r\n", "\r", "\n"], " ", $valStr);
                            $progBioAnswersByUserId[$bs->user_id][$label] = $valStr;
                        }
                    }
                }
            }
        }

        // 5. Susun Header Lengkap Secara Komprehensif
        $headers = [
            'No',
            'Nomor Induk (NI)',
            'Nama Lengkap Peserta',
            'Email Akun',
            'Nomor WhatsApp / Kontak',
            'Foto Profil (URL)',
            'Tag / Kategori',
            'Status Pendaftaran',
            'Tahapan Saat Ini',
            'Batch',
            'Lokasi / Wilayah Program',
            'Status Khusus Peserta',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Agama',
            'Pendidikan Terakhir',
            'Status Pendidikan Saat Ini',
            'Asal Sekolah / Perguruan Tinggi',
            'Jurusan / Program Studi',
            'Kesibukan Saat Ini',
            'Kontak Darurat',
            'Instagram',
        ];

        // Tambahkan kolom biodata tambahan (jika ada di database)
        foreach ($extraBioFields as $ebf) {
            $headers[] = $ebf->name;
        }

        // Tambahkan kolom alamat lengkap
        $headers = array_merge($headers, [
            'Negara',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Desa / Kelurahan',
            'Kampung / Dusun',
            'Detail Alamat Lengkap',
            'Motivasi Peserta',
            'Nilai Akhir / Skor',
        ]);

        // Tambahkan kolom dinamis dari seluruh Jawaban Formulir Tahapan
        foreach ($allStageFieldNames as $sfn) {
            $headers[] = '[Formulir] ' . $sfn;
        }

        // Tambahkan kolom dinamis dari Formulir Khusus Program
        foreach ($allProgBioFieldNames as $pbf) {
            $headers[] = '[Biodata Khusus] ' . $pbf;
        }

        // Tambahkan kolom status akun & audit trail
        $headers = array_merge($headers, [
            'Status Akun (KTP)',
            'Status Verifikasi Email',
            'Status Password',
            'ID Pendaftaran',
            'ID User',
            'Tanggal Daftar',
            'Terakhir Diperbarui'
        ]);

        $tagSlug = ($request->filled('tag') && $request->tag !== 'all') ? '_' . Str::slug($request->tag) : '';
        $scopeSlug = ($scope === 'passed') ? 'peserta_lolos' : 'semua_data_user';
        $filename = $scopeSlug . $tagSlug . '_' . Str::slug($program->name) . '_' . date('Ymd_His') . '.csv';

        $callback = function () use (
            $registrations, 
            $headers, 
            $extraBioFields, 
            $allStageFieldNames, 
            $stageAnswersByRegId, 
            $allProgBioFieldNames, 
            $progBioAnswersByUserId
        ) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fwrite($file, "sep=;\n");
            fputcsv($file, $headers, ';');

            $num = 1;
            foreach ($registrations as $reg) {
                $user = $reg->user;
                $address = $user?->address;

                // Index biodata values
                $bioMap = [];
                if ($user && $user->biodataValues) {
                    foreach ($user->biodataValues as $bv) {
                        $fName = strtolower(trim($bv->biodataField->name ?? ''));
                        $val = $bv->value;
                        $strVal = is_array($val) ? implode(', ', $val) : (string)$val;
                        if (!empty($strVal)) {
                            $bioMap[$bv->biodata_field_id] = $strVal;
                            $bioMap[$fName] = $strVal;
                        }
                    }
                }

                $getBio = function(...$keys) use ($bioMap) {
                    foreach ($keys as $k) {
                        if (isset($bioMap[$k])) return $bioMap[$k];
                        foreach ($bioMap as $bKey => $bVal) {
                            if (is_string($bKey) && str_contains($bKey, (string)$k)) return $bVal;
                        }
                    }
                    return '-';
                };

                $whatsapp = $this->cleanCellValue($getBio(3, 'whatsapp', 'telepon', 'hp'));
                $gender = $getBio(2, 'jenis kelamin', 'gender');
                $tglLahir = $getBio(1, 'tanggal lahir', 'tgl lahir');
                $agama = $getBio(11, 'agama', 'religion');
                $pendidikan = $getBio(5, 'pendidikan terakhir');
                $statusPendidikan = $getBio(6, 'status pendidikan');
                $asalSekolah = $getBio(7, 'asal sekolah', 'perguruan tinggi', 'kampus');
                $jurusan = $getBio(8, 'jurusan', 'program studi', 'prodi');
                $kesibukan = $getBio(4, 'kesibukan');
                $kontakDarurat = $this->cleanCellValue($getBio(9, 'kontak darurat', 'emergency'));
                $instagram = $getBio(12, 'instagram', 'ig');

                $photoUrl = '-';
                if (!empty($user?->avatar)) {
                    $photoUrl = $user->avatar;
                } elseif (!empty($user?->profile?->profile_photo_path)) {
                    $photoUrl = asset('storage/' . $user->profile->profile_photo_path);
                }

                $accountStatus = 'Reguler';
                if ($user?->verification?->status === 'verified') {
                    $accountStatus = 'Terverifikasi (Centang Biru)';
                }

                $emailStatus = $user?->email_verified_at ? 'Terverifikasi' : 'Belum Verifikasi';
                $pwdStatus = ($user && $user->must_change_password) ? 'Belum Ganti Password (Wajib Ganti)' : 'Sudah Ganti Password (Aktif)';

                $statusPendaftaranDesc = match(strtolower((string)$reg->status)) {
                    'passed'  => 'Lolos',
                    'process' => 'Dalam Proses',
                    'failed'  => 'Gugur / Tidak Lolos',
                    default   => strtoupper((string)$reg->status),
                };

                $motivationClean = str_replace(["\r\n", "\r", "\n"], " ", trim((string)$reg->motivation));
                $motivationText = $motivationClean !== '' ? $motivationClean : '-';
                $finalScoresText = trim((string)$reg->final_scores) !== '' ? trim((string)$reg->final_scores) : '-';

                $row = [
                    $num++,
                    $this->formatExcelText($reg->final_id_number ?: '-'),
                    $user?->name ?: 'Tidak Diketahui',
                    $user?->email ?: '-',
                    $this->formatExcelText($whatsapp),
                    $photoUrl,
                    $reg->tags ?: '-',
                    $statusPendaftaranDesc,
                    $reg->currentStage?->title ?: '-',
                    $reg->batch ?: '-',
                    $reg->location ?: ($reg->region ?: '-'),
                    $reg->participant_status ?: '-',
                    $gender,
                    $tglLahir,
                    $agama,
                    $pendidikan,
                    $statusPendidikan,
                    $asalSekolah,
                    $jurusan,
                    $kesibukan,
                    $this->formatExcelText($kontakDarurat),
                    $instagram,
                ];

                // Nilai untuk biodata tambahan global jika ada
                foreach ($extraBioFields as $ebf) {
                    $row[] = $bioMap[$ebf->id] ?? '-';
                }

                // Nilai alamat lengkap
                $detailAlamatClean = str_replace(["\r\n", "\r", "\n"], " ", trim((string)($address?->detail_alamat ?? '')));
                $row = array_merge($row, [
                    $address?->negara ?: 'Indonesia',
                    $address?->provinsi ?: '-',
                    $address?->kabupaten ?: '-',
                    $address?->kecamatan ?: '-',
                    $address?->desa ?: '-',
                    $address?->kampung ?: '-',
                    $detailAlamatClean !== '' ? $detailAlamatClean : '-',
                    $motivationText,
                    $finalScoresText,
                ]);

                // Nilai Jawaban Formulir Tahapan Seleksi
                $regStageAns = $stageAnswersByRegId[$reg->id] ?? [];
                foreach ($allStageFieldNames as $sfn) {
                    $ans = $regStageAns[$sfn] ?? '';
                    $row[] = $ans !== '' ? $ans : '-';
                }

                // Nilai Jawaban Formulir Khusus Program
                $userProgBioAns = $progBioAnswersByUserId[$reg->user_id] ?? [];
                foreach ($allProgBioFieldNames as $pbf) {
                    $ans = $userProgBioAns[$pbf] ?? '';
                    $row[] = $ans !== '' ? $ans : '-';
                }

                // Nilai Status Akun & Audit
                $row = array_merge($row, [
                    $accountStatus,
                    $emailStatus,
                    $pwdStatus,
                    $reg->id,
                    $user?->id ?: '-',
                    $reg->created_at ? $reg->created_at->format('Y-m-d H:i:s') : '-',
                    $reg->updated_at ? $reg->updated_at->format('Y-m-d H:i:s') : '-'
                ]);

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Reset Password Massal untuk beberapa akun yang dipilih
     */
    public function bulkResetPassword(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'user_ids'             => 'required|array|min:1',
            'user_ids.*'           => 'integer|exists:users,id',
            'new_password'         => 'required|string|min:6',
            'must_change_password' => 'nullable'
        ]);

        $userIds = $request->user_ids;
        $newPassword = $request->new_password;
        $mustChange = $request->has('must_change_password');

        $hashed = Hash::make($newPassword);

        User::whereIn('id', $userIds)->update([
            'password'             => $hashed,
            'must_change_password' => $mustChange,
            'updated_at'           => now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'bulk_reset_password',
            'details'    => "Super Admin melakukan reset massal password untuk " . count($userIds) . " akun user. Status must_change_password: " . ($mustChange ? 'Ya' : 'Tidak'),
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Berhasil mereset password untuk ' . count($userIds) . ' akun peserta terpilih.');
    }

    /**
     * Verifikasi Massal Semua Email User di Program Ini yang Belum Terverifikasi
     */
    public function verifyAllEmails(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::findOrFail($programId);

        // Ambil semua user di program ini yang email_verified_at masih NULL
        $unverifiedQuery = User::whereHas('registrations', function ($q) use ($programId) {
            $q->where('program_id', $programId);
        })->whereNull('email_verified_at');

        $count = (clone $unverifiedQuery)->count();

        if ($count === 0) {
            return back()->with('info', "Semua email user di program {$program->name} sudah terverifikasi.");
        }

        $now = now();
        $unverifiedQuery->update([
            'email_verified_at' => $now,
            'updated_at'        => $now,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'bulk_verify_program_emails',
            'details'    => "Super Admin memverifikasi {$count} email akun user di program {$program->name} secara langsung.",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Berhasil memverifikasi {$count} email akun user di program {$program->name}. Semua akun kini telah aktif & terverifikasi.");
    }

    /**
     * Verifikasi Email Satuan untuk Akun Tertentu
     */
    public function verifySingleEmail(Request $request, $userId)
    {
        $this->authorizeSuperAdmin();

        $user = User::findOrFail($userId);

        if ($user->email_verified_at) {
            return back()->with('info', "Email akun {$user->name} sudah terverifikasi.");
        }

        $user->email_verified_at = now();
        $user->save();

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'verify_single_email',
            'target_user_id' => $user->id,
            'details'        => "Super Admin memverifikasi email akun {$user->name} ({$user->email}) secara langsung.",
            'ip_address'     => $request->ip()
        ]);

        return back()->with('success', "Email akun {$user->name} ({$user->email}) berhasil diverifikasi.");
    }

    /**
     * Beri Tag Massal ke Peserta Program (Bisa via Copas Email atau Seleksi Checkbox)
     */
    public function bulkTagParticipants(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::findOrFail($programId);

        $tagAction = $request->input('tag_action', 'set'); // 'set', 'append', 'remove', 'clear'
        $rawTag = trim((string)$request->input('tag_name', ''));
        // Normalisasi format tag (contoh: 'Pokja' atau 'Peserta Biasa')
        $cleanTag = trim(preg_replace('/\s+/', ' ', $rawTag));

        if ($tagAction !== 'clear' && empty($cleanTag)) {
            return back()->with('error', 'Silakan pilih atau ketik nama tag yang ingin diterapkan.');
        }

        $targetEmails = [];
        $selectedUserIds = $request->input('selected_user_ids', []);

        // 1. Ekstrak email dari copas teks jika ada (mendukung berbagai pemisah: baris baru, koma, spasi, tab)
        if ($request->filled('pasted_emails')) {
            preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $request->input('pasted_emails'), $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $em) {
                    $targetEmails[] = strtolower(trim($em));
                }
            }
        }

        if (empty($targetEmails) && empty($selectedUserIds)) {
            return back()->with('error', 'Silakan tempel daftar email peserta atau pilih peserta dari tabel.');
        }

        // 2. Query pendaftar di program ini
        $regQuery = Registration::with(['user'])->where('program_id', $programId);
        $regQuery->where(function ($q) use ($targetEmails, $selectedUserIds) {
            if (!empty($targetEmails)) {
                $q->whereHas('user', function ($qu) use ($targetEmails) {
                    $qu->whereIn(DB::raw('LOWER(email)'), $targetEmails);
                });
            }
            if (!empty($selectedUserIds)) {
                $q->orWhereIn('user_id', $selectedUserIds);
            }
        });

        $registrations = $regQuery->get();

        if ($registrations->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftar di program ini yang cocok dengan email / user yang dimasukkan.');
        }

        $updatedCount = 0;
        $matchedEmails = [];

        foreach ($registrations as $reg) {
            $user = $reg->user;
            if ($user && !empty($user->email)) {
                $matchedEmails[] = strtolower(trim($user->email));
            }

            $existingTags = array_values(array_filter(array_map('trim', explode(',', (string)$reg->tags))));

            if ($tagAction === 'clear') {
                $reg->tags = null;
            } elseif ($tagAction === 'set') {
                $reg->tags = $cleanTag;
            } elseif ($tagAction === 'append') {
                $lowerExisting = array_map('strtolower', $existingTags);
                if (!in_array(strtolower($cleanTag), $lowerExisting)) {
                    $existingTags[] = $cleanTag;
                }
                $reg->tags = implode(', ', $existingTags);
            } elseif ($tagAction === 'remove') {
                $filtered = array_filter($existingTags, fn($t) => strtolower($t) !== strtolower($cleanTag));
                $reg->tags = !empty($filtered) ? implode(', ', $filtered) : null;
            }

            $reg->save();
            $updatedCount++;
        }

        $actionDesc = match($tagAction) {
            'clear'  => 'menghapus semua tag',
            'remove' => "menghapus tag [{$cleanTag}]",
            'append' => "menambahkan tag [{$cleanTag}]",
            default  => "menetapkan tag [{$cleanTag}]",
        };

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'bulk_tag_participants',
            'details'    => "Super Admin {$actionDesc} untuk {$updatedCount} peserta di program {$program->name}.",
            'ip_address' => $request->ip()
        ]);

        $msg = "Berhasil {$actionDesc} pada {$updatedCount} akun peserta program!";

        // Beri tahu jika ada email yang tidak cocok
        if (!empty($targetEmails)) {
            $unmatched = array_diff($targetEmails, $matchedEmails);
            if (!empty($unmatched)) {
                $unmatchedCount = count($unmatched);
                $preview = implode(', ', array_slice($unmatched, 0, 4));
                $more = $unmatchedCount > 4 ? " dan " . ($unmatchedCount - 4) . " email lainnya" : "";
                $msg .= " Catatan: {$unmatchedCount} email tidak ditemukan di program ini ({$preview}{$more}).";
            }
        }

        return back()->with('success', $msg);
    }

    /**
     * Update Tag Satuan Peserta (Bisa dipanggil via Ajax atau Form)
     */
    public function updateSingleTag(Request $request, $registrationId)
    {
        $this->authorizeSuperAdmin();

        $reg = Registration::with(['user', 'program'])->findOrFail($registrationId);
        $rawTag = $request->input('tag');
        $cleanTag = !empty($rawTag) ? trim(preg_replace('/\s+/', ' ', $rawTag)) : null;

        $reg->tags = $cleanTag;
        $reg->save();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'update_participant_tag',
            'details'    => "Super Admin memperbarui tag peserta {$reg->user?->name} menjadi: " . ($cleanTag ?? 'Tanpa Tag'),
            'ip_address' => $request->ip()
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag berhasil diperbarui.',
                'tags'    => $reg->tags,
                'tags_array' => $reg->tags_array,
            ]);
        }

        return back()->with('success', "Tag untuk peserta {$reg->user?->name} berhasil diperbarui.");
    }

    /**
     * Download Template Update Nomor Induk (Sudah terurut Provinsi A-Z -> Nama Peserta A-Z, bisa disaring per Tag)
     */
    public function downloadNiTemplate(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::findOrFail($programId);
        $tag = $request->query('tag');

        $query = Registration::with(['user.address'])
            ->where('registrations.program_id', $programId)
            ->where('registrations.status', 'passed');

        // Filter Tag jika dipilih
        if (!empty($tag) && $tag !== 'all') {
            if ($tag === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('registrations.tags')
                      ->orWhere('registrations.tags', '')
                      ->orWhere('registrations.tags', '-');
                });
            } else {
                $query->where('registrations.tags', 'like', "%{$tag}%");
            }
        }

        // Data akun lolos terurut hierarkis: Provinsi A-Z, lalu Nama A-Z
        $registrations = $query->leftJoin('users', 'registrations.user_id', '=', 'users.id')
            ->leftJoin('addresses', 'users.id', '=', 'addresses.user_id')
            ->select('registrations.*')
            ->orderByRaw("CASE WHEN addresses.provinsi IS NULL OR addresses.provinsi = '' OR addresses.provinsi = '-' THEN 1 ELSE 0 END ASC")
            ->orderBy('addresses.provinsi', 'ASC')
            ->orderBy('users.name', 'ASC')
            ->get();

        $headers = [
            'ID Registrasi',
            'Email Akun',
            'Nama Lengkap Peserta',
            'Tag / Kategori',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Kelurahan / Desa',
            'Nomor Induk Saat Ini',
            'Nomor Induk Baru (Isi Di Sini)'
        ];

        $tagSuffix = (!empty($tag) && $tag !== 'all') ? '_' . Str::slug($tag) : '';
        $filename = 'template_update_ni' . $tagSuffix . '_' . Str::slug($program->name) . '_' . date('Ymd_His') . '.csv';

        $callback = function () use ($registrations, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=;\n");
            fputcsv($file, $headers, ';');

            foreach ($registrations as $reg) {
                $user = $reg->user;
                $address = $user?->address;

                $row = [
                    $reg->id,
                    $user?->email ?: '-',
                    $user?->name ?: '-',
                    $reg->tags ?: '-',
                    $address?->provinsi ?: '-',
                    $address?->kabupaten ?: '-',
                    $address?->kecamatan ?: '-',
                    $address?->desa ?: '-',
                    $reg->final_id_number ?: '-',
                    '' // Kolom kosong untuk diisi oleh Admin
                ];
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Upload / Copas Sheet untuk Update Nomor Induk (NI)
     * Mendukung:
     * 1. Pencocokan otomatis via Email (utama), Nama Lengkap (kedua), atau ID Registrasi (ketiga)
     * 2. Input via Salin-Tempel (textarea pasted_data) atau Unggah Berkas File (.csv, .txt)
     * 3. Deteksi kolom cerdas fleksibel (Email, Nomor Induk / NI, Nama, ID)
     * 4. HANYA mengupdate nomor induk saja, seluruh data profil lainnya 100% aman
     */
    public function importNi(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        if (!$request->filled('pasted_data') && !$request->hasFile('file')) {
            return back()->with('error', 'Silakan tempel (paste) data dari spreadsheet atau pilih berkas file untuk diunggah.');
        }

        $program = Program::findOrFail($programId);

        // 1. Ambil baris data (dari copas atau file upload)
        if ($request->filled('pasted_data')) {
            $rows = $this->parsePastedText($request->input('pasted_data'));
        } else {
            $request->validate([
                'file' => 'required|file|max:10240'
            ]);
            $rows = $this->parseUploadedCsv($request->file('file'));
        }

        if (empty($rows)) {
            return back()->with('error', 'Data spreadsheet kosong atau format tidak dikenali.');
        }

        // 2. Preload seluruh pendaftar program ke memori untuk pencocokan cepat
        $registrations = Registration::with('user')
            ->where('program_id', $programId)
            ->get();

        $regByEmail = [];
        $regByName  = [];
        $regById    = [];

        foreach ($registrations as $reg) {
            $regById[(string)$reg->id] = $reg;
            if ($reg->user) {
                $email = strtolower(trim((string)$reg->user->email));
                if ($email !== '') {
                    $regByEmail[$email] = $reg;
                }
                $normName = $this->normalizeName($reg->user->name);
                if ($normName !== '') {
                    $regByName[$normName] = $reg;
                }
            }
        }

        // 3. Kamus kata kunci pendeteksi kolom
        $niKeywords = [
            'nomor induk baru', 'no induk baru', 'ni baru', 'new ni', 'nomor induk (isi di sini)',
            'nomor induk saat ini', 'nomor induk', 'no induk', 'no_induk', 'final id', 'final_id_number',
            'id number', 'nim', 'nis', 'no peserta', 'nomor peserta', 'ni', 'id final'
        ];
        $emailKeywords = ['email akun', 'alamat email', 'email', 'surel', 'e-mail', 'mail'];
        $nameKeywords  = ['nama lengkap peserta', 'nama peserta', 'nama lengkap', 'nama', 'name', 'full name', 'peserta'];
        $regIdKeywords = ['id registrasi', 'id_registrasi', 'registration id', 'id pendaftaran', 'reg id', 'id reg'];

        $firstRow = $rows[0];
        $firstRowHasEmail = false;
        foreach ($firstRow as $cell) {
            if (filter_var(trim((string)$cell), FILTER_VALIDATE_EMAIL)) {
                $firstRowHasEmail = true;
                break;
            }
        }

        $colEmail = null;
        $colNi    = null;
        $colName  = null;
        $colRegId = null;

        if (!$firstRowHasEmail) {
            // Periksa baris header
            $hasHeader = false;
            foreach ($firstRow as $cell) {
                $cnClean = trim(preg_replace('/\s+/', ' ', preg_replace('/[_\-\.\/]+/', ' ', strtolower((string)$cell))));
                foreach (array_merge($emailKeywords, $niKeywords, $nameKeywords, $regIdKeywords) as $kw) {
                    if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                        $hasHeader = true;
                        break 2;
                    }
                }
            }

            if ($hasHeader) {
                $header = array_shift($rows);
                foreach ($header as $cIdx => $rawHeader) {
                    $cnClean = trim(preg_replace('/\s+/', ' ', preg_replace('/[_\-\.\/]+/', ' ', strtolower((string)$rawHeader))));

                    // Cek Reg ID
                    if ($colRegId === null) {
                        foreach ($regIdKeywords as $kw) {
                            if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                                $colRegId = $cIdx;
                                break;
                            }
                        }
                    }

                    // Cek Email
                    if ($colEmail === null) {
                        foreach ($emailKeywords as $kw) {
                            if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                                $colEmail = $cIdx;
                                break;
                            }
                        }
                    }

                    // Cek NI (utamakan kata kunci 'baru' / 'new')
                    if (str_contains($cnClean, 'baru') || str_contains($cnClean, 'new') || str_contains($cnClean, 'isi di sini')) {
                        $colNi = $cIdx;
                    } elseif ($colNi === null) {
                        foreach ($niKeywords as $kw) {
                            if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                                $colNi = $cIdx;
                                break;
                            }
                        }
                    }

                    // Cek Nama
                    if ($colName === null) {
                        foreach ($nameKeywords as $kw) {
                            if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                                $colName = $cIdx;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Jika kolom email belum terdeteksi dari header, scan baris data
        if ($colEmail === null && !empty($rows)) {
            foreach ($rows[0] as $cIdx => $val) {
                if (filter_var(trim((string)$val), FILTER_VALIDATE_EMAIL)) {
                    $colEmail = $cIdx;
                    break;
                }
            }
        }

        // Jika kolom NI belum terdeteksi dari header, ambil kolom terakhir yang bukan email/nama
        if ($colNi === null && !empty($rows)) {
            $lastIdx = count($rows[0]) - 1;
            if ($lastIdx !== $colEmail && $lastIdx !== $colName) {
                $colNi = $lastIdx;
            }
        }

        $updatedCount = 0;
        $matchedCount = 0;
        $unmatchedEmails = [];

        foreach ($rows as $row) {
            if (empty($row)) continue;

            $emailVal = ($colEmail !== null && isset($row[$colEmail])) ? trim((string)$row[$colEmail]) : '';
            $nameVal  = ($colName !== null && isset($row[$colName])) ? trim((string)$row[$colName]) : '';
            $regIdVal = ($colRegId !== null && isset($row[$colRegId])) ? trim((string)$row[$colRegId]) : '';

            // Jika row belum terdeteksi emailnya, coba scan sel yang mengandung @
            if (empty($emailVal)) {
                foreach ($row as $k => $cVal) {
                    if (filter_var(trim((string)$cVal), FILTER_VALIDATE_EMAIL)) {
                        $emailVal = trim((string)$cVal);
                        break;
                    }
                }
            }

            // Ambil nilai Nomor Induk
            $rawNi = ($colNi !== null && isset($row[$colNi])) ? trim((string)$row[$colNi]) : '';

            // Jika row hanya punya 2 kolom dan colEmail terisi, ambil kolom lainnya sebagai NI
            if (empty($rawNi) && count($row) >= 2) {
                foreach ($row as $k => $cVal) {
                    if ($k !== $colEmail && $k !== $colName && $k !== $colRegId) {
                        $rawNi = trim((string)$cVal);
                        break;
                    }
                }
            }

            // Bersihkan format ilmiah Excel jika ada
            $cleanNi = $this->cleanCellValue($rawNi);

            // Abaikan jika NI kosong, '-', atau teks header
            if (empty($cleanNi) || $cleanNi === '-' || $cleanNi === 'Nomor Induk Baru (Isi Di Sini)') {
                continue;
            }

            // 4. Cari registrasi yang cocok (prioritas: Email -> Nama -> ID)
            $targetReg = null;
            if (!empty($emailVal) && filter_var($emailVal, FILTER_VALIDATE_EMAIL)) {
                $lowerEmail = strtolower($emailVal);
                if (isset($regByEmail[$lowerEmail])) {
                    $targetReg = $regByEmail[$lowerEmail];
                } else {
                    $unmatchedEmails[] = $emailVal;
                }
            }

            if (!$targetReg && !empty($nameVal)) {
                $normName = $this->normalizeName($nameVal);
                if (isset($regByName[$normName])) {
                    $targetReg = $regByName[$normName];
                }
            }

            if (!$targetReg && !empty($regIdVal) && isset($regById[$regIdVal])) {
                $targetReg = $regById[$regIdVal];
            }

            if ($targetReg) {
                // Bersihkan dari daftar unmatchedEmails jika ternyata cocok via Nama atau ID
                if (!empty($emailVal)) {
                    $uKey = array_search($emailVal, $unmatchedEmails);
                    if ($uKey !== false) {
                        unset($unmatchedEmails[$uKey]);
                    }
                }

                $matchedCount++;
                if ($targetReg->final_id_number !== $cleanNi) {
                    $targetReg->final_id_number = $cleanNi;
                    $targetReg->save();
                    $updatedCount++;
                }
            }
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'import_update_ni',
            'details'    => "Super Admin mengupdate {$updatedCount} Nomor Induk (NI) via pencocokan Email/Nama pada program {$program->name}.",
            'ip_address' => $request->ip()
        ]);

        $msg = "Berhasil memperbarui Nomor Induk (NI) pada {$updatedCount} akun peserta program!";
        if ($matchedCount > $updatedCount) {
            $alreadySame = $matchedCount - $updatedCount;
            $msg .= " ({$alreadySame} peserta Nomor Induknya sudah sesuai sebelumnya).";
        }

        if (!empty($unmatchedEmails)) {
            $unmatchedUnique = array_unique($unmatchedEmails);
            $countUnmatched = count($unmatchedUnique);
            $preview = implode(', ', array_slice($unmatchedUnique, 0, 3));
            $more = $countUnmatched > 3 ? " dan " . ($countUnmatched - 3) . " lainnya" : "";
            $msg .= " Catatan: {$countUnmatched} email tidak ditemukan di program ini ({$preview}{$more}).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Download Template Lengkapi Data Kosong (Bisa disaring per Tag)
     */
    public function downloadFillBlanksTemplate(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::findOrFail($programId);
        $tag = $request->query('tag');

        $query = Registration::with(['user.address', 'user.biodataValues.biodataField'])
            ->where('registrations.program_id', $programId)
            ->where('registrations.status', 'passed');

        // Filter Tag jika dipilih
        if (!empty($tag) && $tag !== 'all') {
            if ($tag === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('registrations.tags')
                      ->orWhere('registrations.tags', '')
                      ->orWhere('registrations.tags', '-');
                });
            } else {
                $query->where('registrations.tags', 'like', "%{$tag}%");
            }
        }

        $registrations = $query->leftJoin('users', 'registrations.user_id', '=', 'users.id')
            ->leftJoin('addresses', 'users.id', '=', 'addresses.user_id')
            ->select('registrations.*')
            ->orderByRaw("CASE WHEN addresses.provinsi IS NULL OR addresses.provinsi = '' OR addresses.provinsi = '-' THEN 1 ELSE 0 END ASC")
            ->orderBy('addresses.provinsi', 'ASC')
            ->orderBy('users.name', 'ASC')
            ->get();

        $headers = [
            'ID Registrasi',
            'Email Akun',
            'Nama Peserta',
            'Tag / Kategori',
            'No WhatsApp',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Kelurahan / Desa',
            'Kampung / Dusun',
            'Detail Alamat',
            'Nomor Induk'
        ];

        $tagSuffix = (!empty($tag) && $tag !== 'all') ? '_' . Str::slug($tag) : '';
        $filename = 'template_lengkapi_data' . $tagSuffix . '_' . Str::slug($program->name) . '_' . date('Ymd_His') . '.csv';

        $callback = function () use ($registrations, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=;\n");
            fputcsv($file, $headers, ';');

            foreach ($registrations as $reg) {
                $user = $reg->user;
                $address = $user?->address;

                $whatsapp = '';
                if ($user && $user->biodataValues) {
                    $phoneVal = $user->biodataValues->first(function ($v) {
                        $name = strtolower($v->biodataField->name ?? '');
                        return str_contains($name, 'whatsapp') || str_contains($name, 'telepon') || str_contains($name, 'hp') || $v->biodata_field_id == 3;
                    });
                    if ($phoneVal && !empty($phoneVal->value)) {
                        $whatsapp = is_array($phoneVal->value) ? implode(', ', $phoneVal->value) : (string) $phoneVal->value;
                    }
                }

                $row = [
                    $reg->id,
                    $user?->email ?: '',
                    $user?->name ?: '',
                    $reg->tags ?: '',
                    $whatsapp,
                    $address?->provinsi ?: '',
                    $address?->kabupaten ?: '',
                    $address?->kecamatan ?: '',
                    $address?->desa ?: '',
                    $address?->kampung ?: '',
                    $address?->detail_alamat ?: '',
                    $reg->final_id_number ?: ''
                ];

                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Upload / Copas Sheet untuk Melengkapi Data Kosong (ATURAN: HANYA mengisi data yang kosong di DB, TIDAK menimpa data yang sudah ada)
     * Mendukung:
     * 1. Copas langsung dari Google Sheets / Excel (textarea pasted_data)
     * 2. Unggah file spreadsheet (.csv, .txt)
     * 3. Deteksi kolom cerdas fleksibel (Email, Nama, Provinsi, Kab, Kec, Desa, Dusun, Alamat, WA, NI)
     * 4. Acuan pencocokan: Email atau Nama Lengkap
     */
    public function importFillBlanks(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        if (!$request->filled('pasted_data') && !$request->hasFile('file')) {
            return back()->with('error', 'Silakan tempel (paste) data dari spreadsheet atau pilih berkas file untuk diunggah.');
        }

        $program = Program::findOrFail($programId);

        // 1. Ambil baris data (dari copas atau file upload)
        if ($request->filled('pasted_data')) {
            $rows = $this->parsePastedText($request->input('pasted_data'));
        } else {
            $request->validate([
                'file' => 'required|file|max:10240'
            ]);
            $rows = $this->parseUploadedCsv($request->file('file'));
        }

        if (empty($rows)) {
            return back()->with('error', 'Data spreadsheet kosong atau format tidak dikenali.');
        }

        // 2. Kamus kata kunci pendeteksi kolom fleksibel
        $fieldKeywords = [
            'email'         => ['alamat email', 'email', 'surel', 'e-mail', 'mail'],
            'provinsi'      => ['provinsi', 'propinsi', 'province', 'prov'],
            'kabupaten'     => ['kabupaten / kota', 'kabupaten/kota', 'kab/kota', 'kabupaten', 'kota', 'kab', 'regency', 'city'],
            'kecamatan'     => ['kecamatan', 'district', 'kec.', 'kec'],
            'desa'          => ['kelurahan / desa', 'kelurahan/desa', 'desa/kelurahan', 'desa / kelurahan', 'kelurahan', 'desa', 'village', 'kel'],
            'kampung'       => ['kampung / dusun', 'kampung/dusun', 'kampung', 'dusun', 'dukuh', 'rt/rw', 'sub-village'],
            'detail_alamat' => ['detail alamat', 'detail_alamat', 'alamat domisili', 'alamat lengkap', 'alamat', 'address', 'jalan'],
            'wa'            => ['no whatsapp', 'no wa', 'nomor whatsapp', 'whatsapp', 'no hp', 'no telp', 'no telepon', 'telepon', 'phone', 'wa', 'hp', 'kontak'],
            'reg_id'        => ['id registrasi', 'id_registrasi', 'registration id', 'id pendaftaran', 'no registrasi', 'reg id', 'id reg'],
            'user_id'       => ['user id', 'user_id', 'id user', 'id_user'],
            'ni'            => ['nomor induk', 'no induk', 'no_induk', 'final id', 'final_id_number', 'id number', 'nim', 'nis', 'no peserta', 'nomor peserta', 'ni'],
            'name'          => ['nama lengkap', 'nama peserta', 'nama', 'peserta', 'name', 'full name'],
        ];

        // 3. Periksa apakah baris pertama adalah Header
        $firstRow = $rows[0];
        $firstRowHasEmail = false;
        foreach ($firstRow as $cell) {
            if (filter_var(trim((string)$cell), FILTER_VALIDATE_EMAIL)) {
                $firstRowHasEmail = true;
                break;
            }
        }

        $colMap = [];
        $hasHeader = false;

        if (!$firstRowHasEmail) {
            // Cek apakah ada cell yang memuat kata kunci header
            foreach ($firstRow as $cIdx => $cell) {
                $cn = strtolower(trim((string)$cell));
                $cnClean = trim(preg_replace('/\s+/', ' ', preg_replace('/[_\-\.\/]+/', ' ', $cn)));
                foreach ($fieldKeywords as $field => $kwList) {
                    foreach ($kwList as $kw) {
                        if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                            $hasHeader = true;
                            break 2;
                        }
                    }
                }
            }

            if ($hasHeader) {
                $header = array_shift($rows);
                foreach ($header as $cIdx => $rawHeader) {
                    $cn = strtolower(trim((string)$rawHeader));
                    $cnClean = trim(preg_replace('/\s+/', ' ', preg_replace('/[_\-\.\/]+/', ' ', $cn)));
                    foreach ($fieldKeywords as $field => $kwList) {
                        if (isset($colMap[$field])) continue;
                        foreach ($kwList as $kw) {
                            if ($cnClean === $kw || str_contains($cnClean, $kw)) {
                                $colMap[$field] = $cIdx;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Fallback jika tanpa header: cari kolom email & nama otomatis
        if (!isset($colMap['email'])) {
            foreach (array_slice($rows, 0, 15) as $sampleRow) {
                foreach ($sampleRow as $cIdx => $val) {
                    if (filter_var(trim((string)$val), FILTER_VALIDATE_EMAIL)) {
                        $colMap['email'] = $cIdx;
                        break 2;
                    }
                }
            }
        }

        if (!isset($colMap['name'])) {
            // Kolom teks non-email non-angka
            $candidateCol = null;
            if (!empty($rows)) {
                foreach (array_keys($rows[0]) as $cIdx) {
                    if (isset($colMap['email']) && $cIdx === $colMap['email']) continue;
                    $sample = trim((string)($rows[0][$cIdx] ?? ''));
                    if (!empty($sample) && !is_numeric($sample) && !filter_var($sample, FILTER_VALIDATE_EMAIL)) {
                        $colMap['name'] = $cIdx;
                        break;
                    }
                }
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada baris data untuk diproses.');
        }

        // 4. Pre-load semua pendaftar di program ini ke dalam memori untuk pencocokan instan tanpa query berulang
        $registrations = Registration::with(['user.address', 'user.biodataValues'])
            ->where('program_id', $programId)
            ->get();

        $byEmail = [];
        $byName  = [];
        $byRegId = [];
        $byNi    = [];

        foreach ($registrations as $r) {
            $u = $r->user;
            if (!$u) continue;

            if (!empty($u->email)) {
                $byEmail[strtolower(trim($u->email))] = $r;
            }
            $cleanName = strtolower(trim(preg_replace('/\s+/', ' ', $u->name)));
            if (!empty($cleanName)) {
                $byName[$cleanName] = $r;
            }
            $byRegId[(string)$r->id] = $r;
            if (!empty($r->final_id_number) && $r->final_id_number !== '-') {
                $byNi[strtolower(trim($r->final_id_number))] = $r;
            }
        }

        // 5. Eksekusi pengisian data kosong
        $filledCounts = [
            'provinsi'      => 0,
            'kabupaten'     => 0,
            'kecamatan'     => 0,
            'desa'          => 0,
            'kampung'       => 0,
            'detail_alamat' => 0,
            'wa'            => 0,
            'ni'            => 0,
            'name'          => 0,
        ];

        $affectedUsersCount = 0;
        $matchedCount = 0;
        $unmatchedRows = [];

        foreach ($rows as $rIdx => $row) {
            if (empty(array_filter($row, fn($c) => trim((string)$c) !== ''))) continue;

            $rowEmail = isset($colMap['email']) ? strtolower(trim((string)($row[$colMap['email']] ?? ''))) : '';
            $rowNameRaw = isset($colMap['name']) ? trim((string)($row[$colMap['name']] ?? '')) : '';
            $rowNameNorm = strtolower(trim(preg_replace('/\s+/', ' ', $rowNameRaw)));
            $rowRegId = isset($colMap['reg_id']) ? trim((string)($row[$colMap['reg_id']] ?? '')) : '';
            $rowNi = isset($colMap['ni']) ? strtolower(trim((string)($row[$colMap['ni']] ?? ''))) : '';

            // Tukar jika email terbalik posisinya di kolom nama
            if (!filter_var($rowEmail, FILTER_VALIDATE_EMAIL) && filter_var($rowNameNorm, FILTER_VALIDATE_EMAIL)) {
                $temp = $rowEmail;
                $rowEmail = $rowNameNorm;
                $rowNameNorm = $temp;
                $rowNameRaw = $temp;
            }

            // Identifikasi peserta (Email -> Nama -> ID Registrasi -> Nomor Induk)
            $reg = null;
            if (!empty($rowEmail) && isset($byEmail[$rowEmail])) {
                $reg = $byEmail[$rowEmail];
            } elseif (!empty($rowNameNorm) && isset($byName[$rowNameNorm])) {
                $reg = $byName[$rowNameNorm];
            } elseif (!empty($rowRegId) && isset($byRegId[$rowRegId])) {
                $reg = $byRegId[$rowRegId];
            } elseif (!empty($rowNi) && isset($byNi[$rowNi])) {
                $reg = $byNi[$rowNi];
            }

            if (!$reg || !$reg->user) {
                $unmatchedRows[] = ($rowNameRaw ?: ($rowEmail ?: "Baris #" . ($rIdx + 1)));
                continue;
            }

            $matchedCount++;
            $user = $reg->user;
            $userModified = false;
            $fieldsModifiedForThisUser = 0;

            // A. Lengkapi Nama jika di DB masih kosong
            if (isset($colMap['name'])) {
                $val = $this->cleanCellValue($row[$colMap['name']] ?? '');
                if ($this->isEmptyValue($user->name) && !empty($val)) {
                    $user->name = $val;
                    $userModified = true;
                    $filledCounts['name']++;
                    $fieldsModifiedForThisUser++;
                }
            }

            // B. Lengkapi Nomor Induk jika di DB masih kosong
            if (isset($colMap['ni'])) {
                $val = $this->cleanCellValue($row[$colMap['ni']] ?? '');
                if ($this->isEmptyValue($reg->final_id_number) && !empty($val)) {
                    $reg->final_id_number = $val;
                    $reg->save();
                    $filledCounts['ni']++;
                    $fieldsModifiedForThisUser++;
                }
            }

            // C. Lengkapi WhatsApp jika di DB masih kosong
            if (isset($colMap['wa'])) {
                $val = $this->cleanCellValue($row[$colMap['wa']] ?? '');
                if (!empty($val)) {
                    $existingWa = DB::table('user_biodata_values')
                        ->where('user_id', $user->id)
                        ->where('biodata_field_id', 3)
                        ->value('value');

                    if ($this->isEmptyValue($existingWa)) {
                        DB::table('user_biodata_values')->updateOrInsert(
                            ['user_id' => $user->id, 'biodata_field_id' => 3],
                            ['value' => $val, 'updated_at' => now(), 'created_at' => now()]
                        );
                        $filledCounts['wa']++;
                        $fieldsModifiedForThisUser++;
                    }
                }
            }

            // D. Lengkapi Alamat (Provinsi, Kabupaten, Kecamatan, Desa, Dusun, Detail Alamat)
            $address = $user->address;
            if (!$address) {
                $address = new Address([
                    'user_id'       => $user->id,
                    'negara'        => 'Indonesia',
                    'provinsi'      => '',
                    'kabupaten'     => '',
                    'kecamatan'     => '',
                    'desa'          => '',
                    'kampung'       => '',
                    'detail_alamat' => '',
                ]);
            }
            $addrModified = false;

            $addrFieldMap = [
                'provinsi'      => 'provinsi',
                'kabupaten'     => 'kabupaten',
                'kecamatan'     => 'kecamatan',
                'desa'          => 'desa',
                'kampung'       => 'kampung',
                'detail_alamat' => 'detail_alamat',
            ];

            foreach ($addrFieldMap as $mapField => $dbCol) {
                if (isset($colMap[$mapField])) {
                    $val = $this->cleanCellValue($row[$colMap[$mapField]] ?? '');
                    // ATURAN KETAT: HANYA ISI JIKA DI DATABASE KOSONG / '-' / NULL
                    if ($this->isEmptyValue($address->{$dbCol}) && !empty($val)) {
                        $address->{$dbCol} = $val;
                        $addrModified = true;
                        $filledCounts[$mapField]++;
                        $fieldsModifiedForThisUser++;
                    }
                }
            }

            if ($addrModified) {
                // Pastikan kolom NOT NULL di MySQL memiliki nilai fallback aman
                $address->negara = $address->negara ?: 'Indonesia';
                $address->provinsi = $address->provinsi ?? '';
                $address->kabupaten = $address->kabupaten ?? '';
                $address->kecamatan = $address->kecamatan ?? '';
                $address->desa = $address->desa ?? '';
                $address->kampung = $address->kampung ?? '';
                $address->save();
            }

            if ($userModified) {
                $user->save();
            }

            if ($fieldsModifiedForThisUser > 0) {
                $affectedUsersCount++;
            }
        }

        // 6. Ringkasan hasil & flash message
        $totalFilled = array_sum($filledCounts);
        $details = [];
        if ($filledCounts['provinsi'] > 0) $details[] = "{$filledCounts['provinsi']} Provinsi";
        if ($filledCounts['kabupaten'] > 0) $details[] = "{$filledCounts['kabupaten']} Kab/Kota";
        if ($filledCounts['kecamatan'] > 0) $details[] = "{$filledCounts['kecamatan']} Kecamatan";
        if ($filledCounts['desa'] > 0) $details[] = "{$filledCounts['desa']} Kelurahan/Desa";
        if ($filledCounts['kampung'] > 0) $details[] = "{$filledCounts['kampung']} Dusun/Kampung";
        if ($filledCounts['detail_alamat'] > 0) $details[] = "{$filledCounts['detail_alamat']} Detail Alamat";
        if ($filledCounts['wa'] > 0) $details[] = "{$filledCounts['wa']} No WhatsApp";
        if ($filledCounts['ni'] > 0) $details[] = "{$filledCounts['ni']} Nomor Induk";
        if ($filledCounts['name'] > 0) $details[] = "{$filledCounts['name']} Nama";

        $detailStr = !empty($details) ? implode(', ', $details) : 'tidak ada kolom kosong yang perlu diisi';

        $msg = "Sinkronisasi Berhasil! {$totalFilled} data kosong berhasil dilengkapi ({$detailStr}) pada {$affectedUsersCount} akun peserta (dari total {$matchedCount} peserta yang teridentifikasi). Data yang sudah ada di database tetap aman 100% dan tidak ditimpa.";

        if (!empty($unmatchedRows)) {
            $unmatchedCount = count($unmatchedRows);
            $preview = implode(', ', array_slice($unmatchedRows, 0, 5));
            $more = $unmatchedCount > 5 ? " dan " . ($unmatchedCount - 5) . " baris lainnya" : "";
            $msg .= " Catatan: Terdapat {$unmatchedCount} baris yang tidak ditemukan pendaftarnya di program ini ({$preview}{$more}).";
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'import_fill_blanks',
            'details'    => "Super Admin melengkapi {$totalFilled} data kosong pada program {$program->name}: {$detailStr}.",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', $msg);
    }

    /**
     * Helper: Membersihkan nilai sel dari whitespace, notasi ilmiah Excel, dan tanda strip kosong
     */
    private function cleanCellValue($val): string
    {
        if (is_null($val)) return '';
        $val = trim((string)$val);
        if ($val === '-' || strtolower($val) === 'null' || strtolower($val) === 'n/a') {
            return '';
        }
        // Penanganan notasi ilmiah Excel, contoh: 8,23E+10 atau 8.23E+10
        if (preg_match('/^(\d+)[,\.](\d+)[eE]\+(\d+)$/i', $val)) {
            $num = sprintf('%.0f', (float)str_replace(',', '.', $val));
            if (str_starts_with($num, '8')) {
                $num = '0' . $num;
            }
            return $num;
        }
        return $val;
    }

    /**
     * Download Template Tambah Peserta Baru
     */
    public function downloadImportTemplate($programId)
    {
        $this->authorizeSuperAdmin();

        $headers = [
            'Nama Lengkap',
            'Email',
            'No WhatsApp',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Kelurahan / Desa',
            'Detail Alamat',
            'Nomor Induk',
            'Status Pendaftaran (passed/lolos)'
        ];

        $sampleRow = [
            'Contoh Peserta Baru',
            'contoh.peserta@gmail.com',
            '081234567890',
            'Jawa Barat',
            'Bandung',
            'Coblong',
            'Dago',
            'Jl. Ir. H. Juanda No. 10',
            'IHI-2026-999',
            'passed'
        ];

        $filename = 'template_tambah_peserta_baru_' . date('Ymd_His') . '.csv';

        $callback = function () use ($headers, $sampleRow) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=;\n");
            fputcsv($file, $headers, ';');
            fputcsv($file, $sampleRow, ';');
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Upload File Tambah Peserta Baru ke Program Ini
     */
    public function importNewParticipants(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'file' => 'required|file|max:10240'
        ]);

        $program = Program::findOrFail($programId);
        $rows = $this->parseUploadedCsv($request->file('file'));

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        array_shift($rows); // Hapus header
        $createdCount = 0;
        $registeredCount = 0;

        foreach ($rows as $row) {
            if (count($row) < 2) continue;

            $name    = trim($row[0] ?? '');
            $email   = trim($row[1] ?? '');
            $wa      = trim($row[2] ?? '');
            $prov    = trim($row[3] ?? '');
            $kab     = trim($row[4] ?? '');
            $kec     = trim($row[5] ?? '');
            $desa    = trim($row[6] ?? '');
            $detail  = trim($row[7] ?? '');
            $ni      = trim($row[8] ?? '');
            $status  = strtolower(trim($row[9] ?? 'passed'));
            if (empty($status)) {
                $status = 'passed';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // 1. Cari atau buat User baru
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name'                 => $name ?: 'Peserta Baru',
                    'email'                => $email,
                    'password'             => Hash::make('ihi@2026'),
                    'must_change_password' => true,
                    'is_dummy'             => false
                ]);
                $this->assignParticipantRoleSafely($user);
                $createdCount++;
            }

            // 2. Simpan Alamat
            if ($user) {
                Address::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'provinsi'      => $prov ?: '-',
                        'kabupaten'     => $kab ?: '-',
                        'kecamatan'     => $kec ?: '-',
                        'desa'          => $desa ?: '-',
                        'detail_alamat' => $detail ?: '-'
                    ]
                );

                // 3. Simpan WhatsApp
                if (!empty($wa)) {
                    DB::table('user_biodata_values')->updateOrInsert(
                        ['user_id' => $user->id, 'biodata_field_id' => 3],
                        ['value' => $wa, 'updated_at' => now(), 'created_at' => now()]
                    );
                }

                // 4. Daftarkan ke Program jika belum ada
                $existingReg = Registration::where('program_id', $programId)->where('user_id', $user->id)->first();
                if (!$existingReg) {
                    Registration::create([
                        'user_id'         => $user->id,
                        'program_id'      => $programId,
                        'status'          => in_array($status, ['passed', 'submitted', 'draft', 'rejected', 'under_review']) ? $status : 'passed',
                        'final_id_number' => !empty($ni) ? $ni : null,
                    ]);
                    $registeredCount++;
                }
            }
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'import_new_participants',
            'details'    => "Super Admin menambahkan {$registeredCount} pendaftar baru ({$createdCount} akun baru dibuat) ke program {$program->name}.",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Import selesai! Berhasil mendaftarkan {$registeredCount} peserta baru ({$createdCount} akun dibuat baru) ke program ini.");
    }

    /**
     * Ambil data detail lengkap pendaftar secara AJAX (untuk modal Cek Detail)
     */
    public function applicantDetail($registrationId)
    {
        $this->authorizeSuperAdmin();

        $registration = Registration::with([
            'user.profile',
            'user.address',
            'user.verification',
            'user.biodataValues.biodataField',
            'currentStage',
            'program.stages',
            'stageData.stage'
        ])->findOrFail($registrationId);

        $biodataSubmission = ProgramBiodataSubmission::where('user_id', $registration->user_id)
            ->where('program_id', $registration->program_id)
            ->first();

        $whatsapp = '-';
        if ($registration->user && $registration->user->biodataValues) {
            $phoneVal = $registration->user->biodataValues->first(function ($v) {
                $name = strtolower($v->biodataField->name ?? '');
                return str_contains($name, 'whatsapp') 
                    || str_contains($name, 'telepon') 
                    || str_contains($name, 'hp') 
                    || $v->biodata_field_id == 3;
            });
            if ($phoneVal && !empty($phoneVal->value)) {
                $whatsapp = is_array($phoneVal->value) ? implode(', ', $phoneVal->value) : (string) $phoneVal->value;
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'registration'      => $registration,
                'user'              => $registration->user,
                'whatsapp'          => $whatsapp,
                'address'           => $registration->user?->address,
                'current_stage'     => $registration->currentStage,
                'stage_data'        => $registration->stageData,
                'biodata_submission'=> $biodataSubmission,
                'program'           => $registration->program
            ]
        ]);
    }

    /**
     * Kontrol Super Admin: Memperbarui status pendaftaran peserta
     */
    public function updateStatus(Request $request, $registrationId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'status'          => 'required|string|in:draft,submitted,under_review,passed,rejected,revision',
            'final_id_number' => 'nullable|string|max:100',
            'notes'           => 'nullable|string|max:500'
        ]);

        $registration = Registration::with(['user', 'program'])->findOrFail($registrationId);
        $oldStatus = $registration->status;

        $registration->status = $request->status;
        if ($request->has('final_id_number')) {
            $registration->final_id_number = $request->final_id_number ? trim($request->final_id_number) : null;
        }
        $registration->save();

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'update_participant_status',
            'target_user_id' => $registration->user_id,
            'details'        => "Super Admin mengubah status pendaftar {$registration->user?->name} (#{$registration->id}) di program {$registration->program?->name} dari {$oldStatus} ke {$request->status}." . ($request->notes ? " Catatan: {$request->notes}" : ''),
            'ip_address'     => $request->ip()
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pendaftaran berhasil diperbarui.'
            ]);
        }

        return back()->with('success', "Status pendaftar {$registration->user?->name} berhasil diubah menjadi " . strtoupper($request->status));
    }

    /**
     * Kontrol Super Admin: Memperbarui Nomor Induk (NI)
     */
    public function updateNi(Request $request, $registrationId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'final_id_number' => 'nullable|string|max:100'
        ]);

        $registration = Registration::with(['user', 'program'])->findOrFail($registrationId);
        $registration->final_id_number = $request->final_id_number ? trim($request->final_id_number) : null;
        $registration->save();

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'update_participant_ni',
            'target_user_id' => $registration->user_id,
            'details'        => "Super Admin menetapkan Nomor Induk (NI) [{$registration->final_id_number}] untuk pendaftar {$registration->user?->name} di program {$registration->program?->name}.",
            'ip_address'     => $request->ip()
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nomor Induk berhasil diperbarui.'
            ]);
        }

        return back()->with('success', 'Nomor Induk pendaftar berhasil diperbarui.');
    }

    /**
     * Tampilkan halaman utama alat Komparasi & Rekonsiliasi Data
     */
    public function showReconciliation($programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::withCount([
            'registrations as total_registrations',
            'registrations as passed_count' => function ($q) {
                $q->where('status', 'passed');
            }
        ])->findOrFail($programId);

        return view('superadmin.program_participants.reconciliation', [
            'program'          => $program,
            'comparisonResult' => null,
            'rawInput'         => '',
        ]);
    }

    /**
     * Proses komparasi data dari file CSV yang diunggah ATAU teks yang di-paste
     */
    public function processReconciliation(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $program = Program::withCount([
            'registrations as total_registrations',
            'registrations as passed_count' => function ($q) {
                $q->where('status', 'passed');
            }
        ])->findOrFail($programId);

        $request->validate([
            'file'        => 'nullable|file|max:10240',
            'pasted_data' => 'nullable|string|max:500000',
        ]);

        $rows = [];
        $sourceType = 'text';

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $rows = $this->parseUploadedCsv($request->file('file'));
            $sourceType = 'file';
        } elseif ($request->filled('pasted_data')) {
            $rows = $this->parsePastedText($request->pasted_data);
            $sourceType = 'paste';
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data valid yang dapat dibaca. Pastikan file CSV atau teks yang di-paste memiliki format Nama, Email, dan Nomor Induk.');
        }

        // Normalisasi kolom: deteksi posisi Nama, Email, Nomor Induk
        $normResult = $this->detectColumnsAndNormalize($rows);
        $normalizedRecords = $normResult['records'];
        $skippedRows = $normResult['skipped_rows'];

        if (empty($normalizedRecords)) {
            return back()->with('error', 'Tidak ditemukan baris data dengan alamat email yang valid. Mohon periksa kembali kolom data Anda.');
        }

        // Ekstrak semua email unik dari sheet
        $emails = array_unique(array_filter(array_column($normalizedRecords, 'email')));

        // Query database secara efisien (bulk)
        $existingUsers = User::whereIn('email', $emails)->get()->keyBy(function ($item) {
            return strtolower(trim($item->email));
        });

        $userIds = $existingUsers->pluck('id')->toArray();

        // Query registrasi pendaftar di program ini
        $existingRegistrations = Registration::where('program_id', $programId)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // Klasifikasikan setiap baris
        $comparisonItems = [];
        $stats = [
            'total_sheet'             => count($normalizedRecords),
            'already_passed'          => 0,
            'user_exists_not_in_prog' => 0,
            'registered_not_passed'   => 0,
            'not_registered'          => 0,
            'ni_different'            => 0,
        ];

        foreach ($normalizedRecords as $idx => $rec) {
            $cleanEmail = strtolower($rec['email']);
            $user = $existingUsers->get($cleanEmail);
            $reg = $user ? $existingRegistrations->get($user->id) : null;

            $status = 'not_registered';
            $statusLabel = 'Akun Belum Ada di Web';
            $statusColor = 'rose';
            $actionLabel = 'Buat Akun & Masukkan';
            $dbNi = null;
            $isNiDifferent = false;

            if ($user) {
                if ($reg) {
                    $dbNi = $reg->final_id_number;
                    if ($reg->status === 'passed') {
                        $status = 'already_passed';
                        $statusLabel = 'Sudah Lolos di Program';
                        $statusColor = 'emerald';
                        $actionLabel = 'Sudah Cocok';
                        $stats['already_passed']++;

                        if (!empty($rec['ni']) && $rec['ni'] !== '-' && $rec['ni'] !== $dbNi) {
                            $isNiDifferent = true;
                            $stats['ni_different']++;
                        }
                    } else {
                        $status = 'registered_not_passed';
                        $statusLabel = 'Terdaftar di Program (' . strtoupper($reg->status) . ')';
                        $statusColor = 'amber';
                        $actionLabel = 'Jadikan Lolos (Passed)';
                        $stats['registered_not_passed']++;
                    }
                } else {
                    $status = 'user_exists_not_in_prog';
                    $statusLabel = 'Akun Ada di Web, Belum Masuk Program';
                    $statusColor = 'indigo';
                    $actionLabel = 'Masukkan ke Program (Lolos)';
                    $stats['user_exists_not_in_prog']++;
                }
            } else {
                $stats['not_registered']++;
            }

            $comparisonItems[] = [
                'index'          => $idx + 1,
                'name'           => $rec['name'],
                'email'          => $rec['email'],
                'ni'             => $rec['ni'],
                'status'         => $status,
                'status_label'   => $statusLabel,
                'status_color'   => $statusColor,
                'action_label'   => $actionLabel,
                'user_id'        => $user?->id,
                'reg_id'         => $reg?->id,
                'db_ni'          => $dbNi,
                'ni_different'   => $isNiDifferent,
            ];
        }

        $comparisonResult = [
            'stats'        => $stats,
            'items'        => $comparisonItems,
            'skipped_rows' => $skippedRows,
            'source_type'  => $sourceType,
        ];

        return view('superadmin.program_participants.reconciliation', [
            'program'          => $program,
            'comparisonResult' => $comparisonResult,
            'rawInput'         => $request->pasted_data ?? '',
        ]);
    }

    /**
     * AJAX: Sinkronisasi 1 Peserta dari Hasil Komparasi ke Program Ini
     */
    public function syncSingleParticipant(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'email' => 'required|email',
            'name'  => 'nullable|string',
            'ni'    => 'nullable|string',
        ]);

        $program = Program::findOrFail($programId);
        $email = strtolower(trim($request->email));
        $name = trim($request->name ?: 'Peserta Baru');
        $ni = trim($request->ni ?: '');

        // 1. Cari atau buat User
        $user = User::where('email', $email)->first();
        $isNewUser = false;
        if (!$user) {
            $user = User::create([
                'name'                 => $name,
                'email'                => $email,
                'password'             => Hash::make('ihi@2026'),
                'must_change_password' => true,
                'email_verified_at'    => now(),
            ]);
            $this->assignParticipantRoleSafely($user);
            $isNewUser = true;
        }

        // 2. Cek keunikan Nomor Induk jika diberikan
        if (!empty($ni) && $ni !== '-') {
            $existingNiReg = Registration::where('final_id_number', $ni)
                ->where('user_id', '!=', $user->id)
                ->first();
            if ($existingNiReg) {
                return response()->json([
                    'success' => false,
                    'message' => "Nomor Induk '{$ni}' sudah digunakan oleh peserta {$existingNiReg->user?->name}. Mohon gunakan Nomor Induk yang berbeda.",
                ], 422);
            }
        }

        // 3. Cari atau daftarkan ke Program
        $reg = Registration::where('program_id', $programId)->where('user_id', $user->id)->first();
        if (!$reg) {
            $reg = Registration::create([
                'user_id'         => $user->id,
                'program_id'      => $programId,
                'status'          => 'passed',
                'final_id_number' => !empty($ni) && $ni !== '-' ? $ni : null,
            ]);
        } else {
            $reg->status = 'passed';
            if (!empty($ni) && $ni !== '-') {
                $reg->final_id_number = $ni;
            }
            $reg->save();
        }

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'reconciliation_sync_single',
            'target_user_id' => $user->id,
            'details'        => "Super Admin menyinkronkan peserta {$user->name} ({$user->email}) ke program {$program->name} sebagai peserta lolos (NI: {$reg->final_id_number}). " . ($isNewUser ? '[Akun Baru Dibuat]' : '[Akun Lama Dimasukkan]'),
            'ip_address'     => $request->ip()
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Peserta {$user->name} berhasil dimasukkan ke program sebagai peserta lolos.",
            'user_id'         => $user->id,
            'reg_id'          => $reg->id,
            'final_id_number' => $reg->final_id_number,
        ]);
    }

    /**
     * Bulk Action: Masukkan Semua Peserta yang Belum Terdaftar ke Program Ini Sekaligus
     */
    public function syncAllMissing(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'participants'        => 'required|array|min:1',
            'participants.*.email' => 'required|email',
            'participants.*.name'  => 'nullable|string',
            'participants.*.ni'    => 'nullable|string',
        ]);

        $program = Program::findOrFail($programId);
        $participants = $request->participants;

        $createdUsersCount = 0;
        $registeredCount = 0;
        $updatedPassedCount = 0;

        foreach ($participants as $p) {
            $email = strtolower(trim($p['email']));
            $name = trim($p['name'] ?? 'Peserta');
            $ni = trim($p['ni'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Cari atau buat User
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name'                 => $name ?: 'Peserta Baru',
                    'email'                => $email,
                    'password'             => Hash::make('ihi@2026'),
                    'must_change_password' => true,
                    'email_verified_at'    => now(),
                ]);
                $this->assignParticipantRoleSafely($user);
                $createdUsersCount++;
            }

            // Cek keunikan NI agar tidak melanggar unique constraint
            if (!empty($ni) && $ni !== '-') {
                $isDuplicateNi = Registration::where('final_id_number', $ni)
                    ->where('user_id', '!=', $user->id)
                    ->exists();
                if ($isDuplicateNi) {
                    $ni = null;
                }
            }

            // Cari atau daftarkan ke Registrasi
            $reg = Registration::where('program_id', $programId)->where('user_id', $user->id)->first();
            if (!$reg) {
                Registration::create([
                    'user_id'         => $user->id,
                    'program_id'      => $programId,
                    'status'          => 'passed',
                    'final_id_number' => (!empty($ni) && $ni !== '-') ? $ni : null,
                ]);
                $registeredCount++;
            } else {
                if ($reg->status !== 'passed' || (!empty($ni) && $ni !== '-' && $reg->final_id_number !== $ni)) {
                    $reg->status = 'passed';
                    if (!empty($ni) && $ni !== '-') {
                        $reg->final_id_number = $ni;
                    }
                    $reg->save();
                    $updatedPassedCount++;
                }
            }
        }

        $totalProcessed = $registeredCount + $updatedPassedCount;

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'reconciliation_sync_all',
            'details'    => "Super Admin melakukan sinkronisasi massal komparasi: {$totalProcessed} peserta dimasukkan ke program {$program->name} ({$createdUsersCount} akun baru dibuat).",
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Berhasil menyinkronkan {$totalProcessed} peserta ke program ({$createdUsersCount} akun baru dibuat).",
            'total_processed' => $totalProcessed,
            'created_users'   => $createdUsersCount,
        ]);
    }

    /**
     * Download Laporan Komparasi CSV
     */
    public function exportReconciliationReport(Request $request, $programId)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'report_items' => 'required|string',
        ]);

        $program = Program::findOrFail($programId);
        $items = json_decode($request->report_items, true);

        if (!is_array($items)) {
            return back()->with('error', 'Data laporan tidak valid.');
        }

        $headers = [
            'No',
            'Nama (Sheet)',
            'Email',
            'Nomor Induk (Sheet)',
            'Status di Web IHI',
            'Nomor Induk di Web',
            'Catatan / Rekomendasi'
        ];

        $filename = 'laporan_komparasi_' . Str::slug($program->name) . '_' . date('Ymd_His') . '.csv';

        $callback = function () use ($headers, $items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($file, "sep=;\n");
            fputcsv($file, $headers, ';');

            foreach ($items as $idx => $item) {
                fputcsv($file, [
                    $idx + 1,
                    $item['name'] ?? '',
                    $item['email'] ?? '',
                    $item['ni'] ?? '',
                    $item['status_label'] ?? '',
                    $item['db_ni'] ?? '-',
                    ($item['status'] === 'already_passed') ? 'Sudah Lolos' : 'Perlu Dimasukkan / Disinkronkan'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Helper: Parsing teks hasil copy-paste (dari Excel / Google Sheets)
     */
    private function parsePastedText(string $rawText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (str_contains($line, "\t")) {
                $row = explode("\t", $line);
            } elseif (str_contains($line, ';')) {
                $row = str_getcsv($line, ';');
            } else {
                $row = str_getcsv($line, ',');
            }

            $row = array_map('trim', $row);
            if (!empty($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Helper: Deteksi kolom dan normalisasi data (Nama, Email, NI)
     */
    private function detectColumnsAndNormalize(array $rows): array
    {
        if (empty($rows)) return ['records' => [], 'skipped_rows' => []];

        $firstRow = $rows[0];

        // 1. Cek apakah ada cell di baris pertama yang merupakan email valid
        // Jika ADA email valid, MAKA baris pertama PASTI DATA (bukan header!)
        $firstRowHasEmail = false;
        foreach ($firstRow as $cell) {
            $trimmed = trim((string)$cell);
            if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                $firstRowHasEmail = true;
                break;
            }
        }

        // 2. Baris pertama HANYA dianggap header jika TIDAK ada email valid
        // dan memuat kata kunci header kolom yang jelas
        $hasHeader = false;
        $emailColIdx = -1;
        $nameColIdx = -1;
        $niColIdx = -1;

        if (!$firstRowHasEmail) {
            $headerKeywords = ['nama', 'name', 'peserta', 'email', 'e-mail', 'surel', 'nomor induk', 'no induk', 'nim', 'nis', 'id peserta', 'no'];
            foreach ($firstRow as $cell) {
                $c = strtolower(trim((string)$cell));
                foreach ($headerKeywords as $kw) {
                    if ($c === $kw || str_contains($c, $kw)) {
                        $hasHeader = true;
                        break 2;
                    }
                }
            }

            if ($hasHeader) {
                $header = array_shift($rows);
                foreach ($header as $idx => $colName) {
                    $cn = strtolower(trim((string)$colName));
                    if ($emailColIdx === -1 && (str_contains($cn, 'email') || str_contains($cn, 'mail') || str_contains($cn, 'surel'))) {
                        $emailColIdx = $idx;
                    } elseif ($nameColIdx === -1 && (str_contains($cn, 'nama') || str_contains($cn, 'name') || str_contains($cn, 'peserta'))) {
                        $nameColIdx = $idx;
                    } elseif ($niColIdx === -1 && (str_contains($cn, 'induk') || str_contains($cn, 'ni') || str_contains($cn, 'nim'))) {
                        $niColIdx = $idx;
                    }
                }
            }
        }

        if (empty($rows)) return ['records' => [], 'skipped_rows' => []];

        // 3. Fallback deteksi indeks kolom email jika belum terdeteksi dari header
        if ($emailColIdx === -1) {
            $colEmailCounts = [];
            foreach (array_slice($rows, 0, 25) as $sampleRow) {
                foreach ($sampleRow as $colIdx => $val) {
                    if (filter_var(trim((string)$val), FILTER_VALIDATE_EMAIL)) {
                        $colEmailCounts[$colIdx] = ($colEmailCounts[$colIdx] ?? 0) + 1;
                    }
                }
            }
            if (!empty($colEmailCounts)) {
                arsort($colEmailCounts);
                $emailColIdx = array_key_first($colEmailCounts);
            }
        }

        // 4. Fallback deteksi kolom Nama: pilih kolom yang bukan email dan bukan nomor urut murni (1, 2, 3...)
        if ($nameColIdx === -1 && $emailColIdx !== -1) {
            $candidateScores = [];
            foreach (array_keys($rows[0]) as $cIdx) {
                if ($cIdx === $emailColIdx) continue;
                $isNumericCount = 0;
                $textLenSum = 0;
                $sampleSlice = array_slice($rows, 0, 15);
                foreach ($sampleSlice as $sRow) {
                    $val = trim((string)($sRow[$cIdx] ?? ''));
                    if (is_numeric($val)) $isNumericCount++;
                    $textLenSum += strlen($val);
                }
                // Jika mayoritas berisi angka murni, anggap kolom nomor urut (skor rendah)
                if ($isNumericCount > count($sampleSlice) / 2) {
                    $candidateScores[$cIdx] = -100;
                } else {
                    $candidateScores[$cIdx] = $textLenSum;
                }
            }
            if (!empty($candidateScores)) {
                arsort($candidateScores);
                $nameColIdx = array_key_first($candidateScores);
            }
        }

        // 5. Fallback deteksi kolom NI
        if ($niColIdx === -1 && $emailColIdx !== -1) {
            foreach (array_keys($rows[0]) as $cIdx) {
                if ($cIdx !== $emailColIdx && $cIdx !== $nameColIdx) {
                    $niColIdx = $cIdx;
                    break;
                }
            }
        }

        // Default jika tetap tidak terdeteksi
        if ($emailColIdx === -1) $emailColIdx = 1;
        if ($nameColIdx === -1) $nameColIdx = ($emailColIdx === 0) ? 1 : 0;

        $normalized = [];
        $skippedRows = [];

        foreach ($rows as $rIdx => $row) {
            $email = isset($row[$emailColIdx]) ? trim((string)$row[$emailColIdx]) : '';
            $name = isset($row[$nameColIdx]) ? trim((string)$row[$nameColIdx]) : '';
            $ni = ($niColIdx !== -1 && isset($row[$niColIdx])) ? trim((string)$row[$niColIdx]) : '';

            // Tukar jika email terisi di nama dan sebaliknya
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) && filter_var($name, FILTER_VALIDATE_EMAIL)) {
                $temp = $email;
                $email = $name;
                $name = $temp;
            }

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $normalized[] = [
                    'name'  => $name ?: 'Peserta',
                    'email' => $email,
                    'ni'    => $ni ?: '-',
                ];
            } else {
                $rawPreview = implode(' | ', array_filter($row, fn($c) => trim((string)$c) !== ''));
                if (!empty($rawPreview)) {
                    $skippedRows[] = [
                        'row_num' => $rIdx + ($hasHeader ? 2 : 1),
                        'preview' => $rawPreview,
                        'name'    => $name ?: 'Tanpa Nama',
                        'email'   => $email ?: 'Kosong',
                    ];
                }
            }
        }

        return [
            'records'      => $normalized,
            'skipped_rows' => $skippedRows,
        ];
    }

    /**
     * Helper: Cek apakah suatu nilai kosong / strip / null
     */
    private function isEmptyValue($val): bool
    {
        if (is_null($val)) return true;
        $t = trim((string)$val);
        return ($t === '' || $t === '-' || strtolower($t) === 'null');
    }

    /**
     * Helper: Parser file CSV aman dengan auto-detect delimiter dan BOM
     */
    private function parseUploadedCsv($uploadedFile): array
    {
        $filePath = $uploadedFile->getRealPath();
        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        // Skip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Deteksi delimiter
        $firstLine = fgets($handle);
        $delimiter = ';';
        if ($firstLine !== false && str_starts_with(trim($firstLine), 'sep=')) {
            $sepChar = trim(substr(trim($firstLine), 4));
            if (!empty($sepChar)) {
                $delimiter = $sepChar;
            }
        } else {
            rewind($handle);
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $sample = fgets($handle);
            if ($sample !== false) {
                $semis = substr_count($sample, ';');
                $commas = substr_count($sample, ',');
                $tabs = substr_count($sample, "\t");
                if ($commas > $semis && $commas > $tabs) {
                    $delimiter = ',';
                } elseif ($tabs > $semis && $tabs > $commas) {
                    $delimiter = "\t";
                } else {
                    $delimiter = ';';
                }
            }
            rewind($handle);
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $checkSep = fgets($handle);
            if ($checkSep === false || !str_starts_with(trim($checkSep), 'sep=')) {
                rewind($handle);
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
            }
        }

        $rows = [];
        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if (empty($row) || (count($row) === 1 && $row[0] === null)) continue;
            $rows[] = array_map('trim', $row);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Helper: Menetapkan role participant secara aman ke akun user baru
     */
    private function assignParticipantRoleSafely(User $user): void
    {
        if (method_exists($user, 'assignRole') && class_exists(\Spatie\Permission\Models\Role::class)) {
            $role = \Spatie\Permission\Models\Role::where('name', 'Participant')->first()
                ?? \Spatie\Permission\Models\Role::where('name', 'User')->first()
                ?? \Spatie\Permission\Models\Role::where('name', 'Peserta')->first();
            if ($role) {
                $user->assignRole($role);
            }
        }
    }

    /**
     * Helper: Menormalkan nama untuk pencocokan toleran (huruf kecil, tanpa tanda baca berlebih)
     */
    private function normalizeName(?string $name): string
    {
        if (empty($name)) return '';
        $name = strtolower(trim($name));
        $name = preg_replace('/[.,\-_]/', ' ', $name);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Helper: Format nilai teks numerik (seperti No HP / WA) agar tidak diubah menjadi notasi ilmiah oleh Excel
     */
    private function formatExcelText(?string $val): string
    {
        if (empty($val) || $val === '-' || $val === 'Tidak Diketahui') return '-';
        $val = trim($val);
        $numericOnly = preg_replace('/[^0-9]/', '', $val);
        // Jika angka telepon/nomor panjang (minimal 7 digit)
        if (strlen($numericOnly) >= 7 && (str_starts_with($numericOnly, '0') || str_starts_with($numericOnly, '62') || str_starts_with($numericOnly, '8'))) {
            return '="' . $numericOnly . '"';
        }
        return $val;
    }
}
