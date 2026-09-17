<?php

namespace App\Services\Piagam;

use App\Models\PiagamCertificate;
use setasign\Fpdi\Fpdi;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateGeneratorService {

    public function generateForParticipant($participantId) {
        $registration = \App\Models\Registration::findOrFail($participantId);
        $program = $registration->program;
        
        if (!$program->piagam_template_id) {
            throw new \Exception("Program tidak memiliki template piagam.");
        }
        
        $certificate = \App\Models\PiagamCertificate::firstOrCreate([
            'participant_id' => $participantId,
        ], [
            'program_id' => $program->id,
            'template_id' => $program->piagam_template_id,
            'certificate_number' => 'IHI-' . date('Y') . '-' . str_pad($participantId, 4, '0', STR_PAD_LEFT),
            'status' => 'draft',
            'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'published_at' => now()
        ]);

        return $this->generate($certificate);
    }
    public function generate(PiagamCertificate $certificate) {
        $template = $certificate->template;
        if (!$template) {
            throw new \Exception("Certificate does not have a template assigned.");
        }
        
        $layouts = $template->layouts()->with('elements')->get();
        $participant = $certificate->participant;

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $templatePath = storage_path('app/public/' . $template->file_path);
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found at: " . $templatePath);
        }

        $pageCount = $pdf->setSourceFile($templatePath);

        // Generate QR code image temporary
        $qrToken = $certificate->qr_token ?? (string) Str::uuid();
        if (!$certificate->qr_token) {
            $certificate->update(['qr_token' => $qrToken]);
        }
        $qrUrl = route('public.piagam.verify', ['qr_token' => $qrToken]);
        
        $qrOptions = new QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
            'scale' => 5,
        ]);
        $qrData = (new QRCode($qrOptions))->render($qrUrl);
        $qrData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $qrData));
        $tmpQrPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        file_put_contents($tmpQrPath, $qrData);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

            // Find layout for this page
            $layout = $layouts->where('page_number', $pageNo)->first();
            if ($layout) {
                foreach ($layout->elements as $element) {
                    if ($element->type === 'qr_code') {
                        // For QR Code, font_size is used as dimension
                        $pdf->Image($tmpQrPath, $element->x_pos, $element->y_pos, $element->font_size, $element->font_size);
                    } else if ($element->type === 'image') {
                        // For Custom Images, font_size is used as width, height is auto (0)
                        $imgPath = public_path($element->content);
                        if(file_exists($imgPath)) {
                            $pdf->Image($imgPath, $element->x_pos, $element->y_pos, $element->font_size, 0);
                        }
                    } else {
                        // resolve content
                        $text = $this->resolveText($element->content, $certificate);
                        
                        // map color hex to rgb
                        $color = $element->color ?? '#000000';
                        list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
                        $pdf->SetTextColor($r, $g, $b);
                        $pdf->SetXY($element->x_pos, $element->y_pos);
                        
                        $font = strtolower($element->font_family) ?: 'helvetica';
                        if (!in_array($font, ['courier', 'helvetica', 'arial', 'times'])) {
                            $font = 'helvetica';
                        }
                        
                        $pdf->SetFont($font, '', $element->font_size);
                        $align = strtoupper(substr($element->text_align ?? 'left', 0, 1));
                        if(!in_array($align, ['L', 'C', 'R'])) $align = 'L';
                        
                        $pdf->Cell(0, 0, utf8_decode($text), 0, 0, $align);
                    }
                }
            }
        }
        
        @unlink($tmpQrPath);

        // Save PDF
        $fileName = 'certificates/' . $certificate->program_id . '/' . $certificate->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->Output('S'));

        $certificate->update([
            'file_path' => $fileName,
            'status' => 'generated'
        ]);

        return $fileName;
    }

    private function resolveText($content, $certificate) {
        if (!$content) return '';
        $text = $content;
        $participant = $certificate->participant; 
        
        $variables = [
            '[NAMA_PESERTA]' => $certificate->participant->user->name ?? 'N/A',
            '[NAMA_PROGRAM]' => $certificate->program->name ?? 'N/A',
            '[NOMOR_KREDENSIAL_UTAMA_GLOBAL]' => $certificate->certificate_number ?? 'N/A',
            '[PREDIKAT]' => $certificate->predikat ?? '', // need to add predikat if exists
            '[TOTAL_SKOR]' => $certificate->final_grade ?? 'N/A',
            '[TANGGAL_TERBIT]' => date('d F Y'),
        ];
        
        // Fetch Custom Grades
        $customGrades = \App\Models\PiagamParticipantGrade::where('participant_id', $certificate->participant_id)->get();
        foreach ($customGrades as $grade) {
            $variables['[' . $grade->variable_name . ']'] = $grade->value;
        }
        
        foreach ($variables as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }
}








