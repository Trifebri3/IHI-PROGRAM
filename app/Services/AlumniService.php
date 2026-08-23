<?php

namespace App\Services;

use App\Models\User;
use App\Models\Program;
use App\Models\AlumniProfile;
use App\Models\AlumniProgram;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\CertificateTemplate;
use App\Models\AlumniVerificationRequest;
use App\Models\Registration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use setasign\Fpdi\Fpdi;

class AlumniService
{
    /**
     * Generate unique permanent Alumni Number (NIA)
     */
    public function generateAlumniNumber(): string
    {
        $year = date('Y');
        
        // Find highest suffix for this year
        $latest = AlumniProfile::where('alumni_number', 'LIKE', "ALM{$year}%")
            ->orderBy('alumni_number', 'desc')
            ->first();

        if ($latest && preg_match('/ALM' . $year . '(\d+)/', $latest->alumni_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'ALM' . $year . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Auto register passed registration into Alumni
     */
    public function registerAutoAlumni(Registration $registration)
    {
        DB::transaction(function () use ($registration) {
            $user = $registration->user;
            $program = $registration->program;

            if (!$user || !$program) {
                return;
            }

            // 1. Update user status to Alumni
            $user->status = 'Alumni';
            $user->save();

            // 2. Ensure AlumniProfile exists, generate NIA
            $profile = AlumniProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'alumni_number' => $this->generateAlumniNumber(),
                ]
            );

            // If profile exists but NIA is null, assign one
            if (empty($profile->alumni_number)) {
                $profile->alumni_number = $this->generateAlumniNumber();
                $profile->save();
            }

            // 3. Ensure AlumniProgram exists
            $year = $program->start_date ? date('Y', strtotime($program->start_date)) : date('Y');
            $alumniProgram = AlumniProgram::firstOrCreate(
                ['program_id' => $program->id],
                [
                    'name' => $program->name,
                    'year' => $year,
                ]
            );

            // 4. Create Pivot Record in user_alumni
            $extraInfo = [
                'nilai_akhir' => null,
                'predikat' => null,
                'ranking' => null,
                'skor_assessment' => null,
                'jam_pelatihan' => $program->total_hours ?? 32,
                'kompetensi' => null,
                'catatan' => null
            ];

            // If registrations has final scores, try to map them or use it
            if ($registration->final_scores) {
                $extraInfo['final_scores'] = $registration->final_scores;
            }

            $userAlumni = UserAlumni::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'alumni_program_id' => $alumniProgram->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'verification_status' => 'approved',
                    'extra_info' => $extraInfo,
                ]
            );

            // 5. Generate Certificate PDF
            $this->generateCertificate($user, $alumniProgram, $userAlumni);
        });
    }

    /**
     * Approve manual verification request
     */
    public function approveManualVerification(AlumniVerificationRequest $request, array $extraInfo = [])
    {
        DB::transaction(function () use ($request, $extraInfo) {
            $user = $request->user;
            $alumniProgram = $request->alumniProgram;

            // 1. Update user status to Alumni
            $user->status = 'Alumni';
            $user->save();

            // 2. Ensure AlumniProfile exists (without official NIA, just internal UUID)
            $profile = AlumniProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'alumni_number' => null, // Manual doesn't get official permanent NIA
                ]
            );

            // 3. Create UserAlumni pivot with random UUID and approved status
            $userAlumni = UserAlumni::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'alumni_program_id' => $alumniProgram->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'verification_status' => 'approved',
                    'extra_info' => array_merge([
                        'nilai_akhir' => $extraInfo['nilai_akhir'] ?? null,
                        'predikat' => $extraInfo['predikat'] ?? null,
                        'ranking' => $extraInfo['ranking'] ?? null,
                        'skor_assessment' => $extraInfo['skor_assessment'] ?? null,
                        'jam_pelatihan' => $extraInfo['jam_pelatihan'] ?? 32,
                        'kompetensi' => $extraInfo['kompetensi'] ?? null,
                        'catatan' => $extraInfo['catatan'] ?? null,
                        'manual_verification_request_id' => $request->id,
                    ], $extraInfo),
                ]
            );

            // 4. Generate certificate based on the manual request's uploaded file or template
            // We can generate a certificate if template exists, or just copy the uploaded screenshot as the certificate file
            $template = CertificateTemplate::where('alumni_program_id', $alumniProgram->id)->first();
            if ($template) {
                $this->generateCertificate($user, $alumniProgram, $userAlumni);
            } else {
                // Get registration to check for final_id_number (Nomor Induk Program)
                $registration = Registration::where('user_id', $user->id)
                    ->where('program_id', $alumniProgram->program_id)
                    ->first();
                
                $certNumber = $registration ? $registration->final_id_number : null;

                // Fallback to permanent NIA if no final_id_number
                if (empty($certNumber)) {
                    $profile = AlumniProfile::where('user_id', $user->id)->first();
                    $certNumber = $profile ? ($profile->alumni_number ?? 'ALM-' . $userAlumni->uuid) : 'ALM-' . $userAlumni->uuid;
                }

                // If no template, we link the uploaded scan as the certificate file
                AlumniCertificate::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'alumni_program_id' => $alumniProgram->id,
                    ],
                    [
                        'certificate_number' => $certNumber,
                        'file_path' => $request->certificate_scan_path,
                        'uuid' => $userAlumni->uuid,
                        'extra_info' => $userAlumni->extra_info,
                    ]
                );
            }

            // 5. Update request status
            $request->status = 'approved';
            $request->save();
        });
    }

    /**
     * Generate Certificate PDF with FPDI and FPDF
     */
    public function generateCertificate(User $user, AlumniProgram $alumniProgram, UserAlumni $userAlumni)
    {
        $template = CertificateTemplate::where('alumni_program_id', $alumniProgram->id)->first();
        if (!$template) {
            return;
        }

        $templatePath = Storage::disk('public')->path($template->template_path);
        if (!file_exists($templatePath)) {
            return;
        }

        // Get registration to check for final_id_number (Nomor Induk Program)
        $registration = \App\Models\Registration::where('user_id', $user->id)
            ->where('program_id', $alumniProgram->program_id)
            ->first();
        
        $certNumber = $registration ? $registration->final_id_number : null;

        // Fallback to permanent NIA if no final_id_number
        if (empty($certNumber)) {
            $profile = AlumniProfile::where('user_id', $user->id)->first();
            $certNumber = $profile ? ($profile->alumni_number ?? 'ALM-' . $userAlumni->uuid) : 'ALM-' . $userAlumni->uuid;
        }

        // Prepare text overlays
        $name = strtoupper($user->name);
        $programName = $alumniProgram->name;
        $year = $alumniProgram->year;
        $date = date('d F Y', strtotime($userAlumni->created_at));

        // Get coordinates settings
        $settings = $template->settings ?? $this->getDefaultSettings();

        // 1. Generate QR Code PNG file
        $verificationUrl = route('public.alumni.verify', $userAlumni->uuid);
        $qrTempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        
$options = new QROptions([
    'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
    'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
    'scale' => 5,
    'imageBase64' => false,
]);
        
        $qrcode = new QRCode($options);
        $qrcode->render($verificationUrl, $qrTempFile);

        // 2. Load PDF with FPDI or Image with standard FPDF
        $pdf = new Fpdi('L', 'mm', 'A4'); // A4 Landscape
        $pdf->AddPage();
        
        $extension = pathinfo($templatePath, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['png', 'jpg', 'jpeg']);

        if ($isImage) {
            $pdf->Image($templatePath, 0, 0, 297, 210);
        } else {
            $pdf->setSourceFile($templatePath);
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 297, 210); // A4 Landscape is 297mm x 210mm
        }

        // Write Name
        if (isset($settings['name'])) {
            $s = $settings['name'];
            $pdf->SetFont('Arial', 'B', $s['size'] ?? 24);
            $color = $s['color'] ?? [30, 41, 59];
            $pdf->SetTextColor($color[0], $color[1], $color[2]);
            $pdf->SetXY($s['x'] ?? 0, $s['y'] ?? 85);
            $pdf->Cell(0, 10, $name, 0, 0, $s['align'] ?? 'C');
        }

        // Write Program
        if (isset($settings['program'])) {
            $s = $settings['program'];
            $pdf->SetFont('Arial', 'B', $s['size'] ?? 18);
            $color = $s['color'] ?? [15, 23, 42];
            $pdf->SetTextColor($color[0], $color[1], $color[2]);
            $pdf->SetXY($s['x'] ?? 0, $s['y'] ?? 105);
            $pdf->Cell(0, 10, $programName, 0, 0, $s['align'] ?? 'C');
        }

        // Write NIA / Nomor Induk
        if (isset($settings['alumni_number'])) {
            $s = $settings['alumni_number'];
            $pdf->SetFont('Arial', '', $s['size'] ?? 12);
            $color = $s['color'] ?? [100, 116, 139];
            $pdf->SetTextColor($color[0], $color[1], $color[2]);
            $pdf->SetXY($s['x'] ?? 0, $s['y'] ?? 125);
            $pdf->Cell(0, 10, "No. Induk: " . $certNumber, 0, 0, $s['align'] ?? 'C');
        }

        // Write Date
        if (isset($settings['date'])) {
            $s = $settings['date'];
            $pdf->SetFont('Arial', '', $s['size'] ?? 12);
            $color = $s['color'] ?? [100, 116, 139];
            $pdf->SetTextColor($color[0], $color[1], $color[2]);
            $pdf->SetXY($s['x'] ?? 0, $s['y'] ?? 145);
            $pdf->Cell(0, 10, "Tanggal Lulus: " . $date, 0, 0, $s['align'] ?? 'C');
        }

        // Embed QR Code
        if (isset($settings['qr']) && file_exists($qrTempFile)) {
            $s = $settings['qr'];
            $pdf->Image($qrTempFile, $s['x'] ?? 133, $s['y'] ?? 160, $s['size'] ?? 30, $s['size'] ?? 30);
        }

        // Extra dynamic text overlays (like Predikat / Nilai Akhir) if present in extra_info and mapped in settings
        $extra = $userAlumni->extra_info;
        if ($extra && is_array($extra)) {
            if (!empty($extra['nilai_akhir']) && isset($settings['nilai_akhir'])) {
                $s = $settings['nilai_akhir'];
                $pdf->SetFont('Arial', 'B', $s['size'] ?? 12);
                $pdf->SetTextColor(30, 41, 59);
                $pdf->SetXY($s['x'] ?? 20, $s['y'] ?? 180);
                $pdf->Cell(0, 10, "Nilai Akhir: " . $extra['nilai_akhir'], 0, 0, $s['align'] ?? 'L');
            }
            if (!empty($extra['predikat']) && isset($settings['predikat'])) {
                $s = $settings['predikat'];
                $pdf->SetFont('Arial', 'B', $s['size'] ?? 12);
                $pdf->SetTextColor(30, 41, 59);
                $pdf->SetXY($s['x'] ?? 20, $s['y'] ?? 190);
                $pdf->Cell(0, 10, "Predikat: " . $extra['predikat'], 0, 0, $s['align'] ?? 'L');
            }
        }

        // 3. Save Output PDF
        $certDirectory = 'alumni_certificates/' . $alumniProgram->id;
        Storage::disk('public')->makeDirectory($certDirectory);
        
        $outputFilename = $certDirectory . '/' . $userAlumni->uuid . '.pdf';
        $outputPath = Storage::disk('public')->path($outputFilename);
        
        $pdf->Output('F', $outputPath);

        // Delete temporary QR Code file
        if (file_exists($qrTempFile)) {
            unlink($qrTempFile);
        }

        // 4. Save to alumni_certificates table
        return AlumniCertificate::updateOrCreate(
            [
                'user_id' => $user->id,
                'alumni_program_id' => $alumniProgram->id,
            ],
            [
                'certificate_number' => $certNumber,
                'file_path' => $outputFilename,
                'uuid' => $userAlumni->uuid,
                'extra_info' => $userAlumni->extra_info,
            ]
        );
    }

    /**
     * Default certificate placement coordinates
     */
    public function getDefaultSettings(): array
    {
        return [
            'name' => ['x' => 0, 'y' => 80, 'size' => 28, 'color' => [30, 41, 59], 'align' => 'C'],
            'program' => ['x' => 0, 'y' => 100, 'size' => 20, 'color' => [15, 23, 42], 'align' => 'C'],
            'alumni_number' => ['x' => 0, 'y' => 120, 'size' => 12, 'color' => [100, 116, 139], 'align' => 'C'],
            'date' => ['x' => 0, 'y' => 135, 'size' => 12, 'color' => [100, 116, 139], 'align' => 'C'],
            'qr' => ['x' => 133, 'y' => 155, 'size' => 32],
            'nilai_akhir' => ['x' => 20, 'y' => 180, 'size' => 11, 'align' => 'L'],
            'predikat' => ['x' => 20, 'y' => 187, 'size' => 11, 'align' => 'L'],
        ];
    }
}
