<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use App\Models\Program;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

$programId = 11;
$regQuery = Registration::with([
    'user.address',
    'user.profile',
    'user.verification',
    'user.biodataValues.biodataField',
    'currentStage'
])->where('registrations.program_id', $programId);

$registrations = $regQuery->get();
$regIds = $registrations->pluck('id')->toArray();
$userIds = $registrations->pluck('user_id')->filter()->unique()->toArray();

echo "Total Registrations: " . count($registrations) . PHP_EOL;

// 1. Gather all stage fields
$stageRows = DB::table('registration_stage_data')
    ->join('program_stages', 'registration_stage_data.program_stage_id', '=', 'program_stages.id')
    ->whereIn('registration_stage_data.registration_id', $regIds)
    ->whereNotNull('registration_stage_data.form_values')
    ->orderBy('program_stages.id', 'asc')
    ->orderBy('registration_stage_data.id', 'asc')
    ->select('registration_stage_data.registration_id', 'registration_stage_data.form_values')
    ->get();

$allStageFieldNames = [];
$stageAnswersByRegId = [];

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
                if (str_starts_with($v, 'program_submissions/') || str_starts_with($v, 'uploads/')) {
                    $v = asset('storage/' . $v);
                }
                $v = str_replace(["\r\n", "\r", "\n"], " ", $v);
                $stageAnswersByRegId[$sr->registration_id][$fName] = $v;
            }
        }
    }
}

// 2. Program Biodata Submissions
$bioSubmissions = DB::table('program_biodata_submissions')
    ->where('program_id', $programId)
    ->whereIn('user_id', $userIds)
    ->get();

$progBioAnswersByUserId = [];
$allProgBioFieldNames = [];
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

echo "Stage fields: " . count($allStageFieldNames) . PHP_EOL;
echo "Program Biodata fields: " . count($allProgBioFieldNames) . PHP_EOL;

// 3. Extra biodata fields
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

// Build headers
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
foreach ($extraBioFields as $ebf) {
    $headers[] = $ebf->name;
}
$headers = array_merge($headers, [
    'Negara',
    'Provinsi',
    'Kabupaten / Kota',
    'Kecamatan',
    'Desa / Kelurahan',
    'Kampung / Dusun',
    'Detail Alamat Lengkap',
    'Motivasi Peserta',
    'Nilai Akhir / Skor'
]);
foreach ($allStageFieldNames as $fn) {
    $headers[] = '[Formulir] ' . $fn;
}
foreach ($allProgBioFieldNames as $pbf) {
    $headers[] = '[Biodata Khusus] ' . $pbf;
}
$headers = array_merge($headers, [
    'Status Akun (KTP)',
    'Status Verifikasi Email',
    'Status Password',
    'ID Pendaftaran',
    'ID User',
    'Tanggal Daftar',
    'Terakhir Diperbarui'
]);

echo "Total Headers: " . count($headers) . PHP_EOL;

// Test first row
$firstReg = $registrations->first();
$user = $firstReg->user;
$stageAns = $stageAnswersByRegId[$firstReg->id] ?? [];
echo "Sample Row for {$user->name}:\n";
echo "NI: {$firstReg->final_id_number}\n";
echo "Email: {$user->email}\n";
echo "Motivasi: " . substr($firstReg->motivation, 0, 40) . "...\n";
echo "CV: " . ($stageAns['CV atau Daftar Riwayat Hidup'] ?? 'N/A') . "\n";
echo "Motivation Letter: " . ($stageAns['Motivation Letter'] ?? 'N/A') . "\n";
