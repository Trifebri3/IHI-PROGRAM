<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PIAGAM_PROGRAM_{{ Str::slug($registration->final_id_number, '_') }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body, html { margin: 0; padding: 0; width: 297mm; height: 210mm; font-family: 'Georgia', serif; background-color: #ffffff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* HALAMAN SATU: PIAGAM UTAMA */
        .page-front { position: relative; width: 297mm; height: 210mm; background-image: url("{{ asset('storage/' . $program->program_certificate_template) }}"); background-size: cover; background-position: center; background-repeat: no-repeat; page-break-after: always; }
        .overlay-front { position: absolute; width: 100%; top: 75mm; text-align: center; }
        .cert-id { font-family: monospace; font-size: 13px; font-weight: bold; color: #475569; letter-spacing: 1px; margin-bottom: 25px; }
        .user-name { font-size: 34px; font-weight: bold; color: #064e3b; text-transform: uppercase; margin-bottom: 10px; }
        .cert-desc { font-size: 13px; color: #334155; font-style: italic; line-height: 1.6; max-width: 700px; margin: 15px auto 0 auto; }

        /* HALAMAN DUA: TRANSKRIP AKADEMIK E-RAPORT */
        .page-back { width: 297mm; height: 210mm; padding: 25mm 30mm; box-sizing: border-box; background-color: #ffffff; position: relative; page-break-after: avoid; }
        .raport-title { text-align: center; text-transform: uppercase; font-size: 18px; font-weight: bold; color: #0f172a; margin-bottom: 5px; letter-spacing: 0.5px; }
        .raport-sub { text-align: center; font-size: 11px; color: #64748b; margin: 0 0 25px 0; font-family: sans-serif; }

        .student-info { display: flex; justify-content: space-between; font-size: 11px; font-family: sans-serif; font-weight: bold; color: #475569; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 12px; margin-top: 10px; }
        th, td { border: 1px solid #94a3b8; padding: 10px 12px; text-align: left; }
        th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }

        /* DYNAMIC QR CODE CONTAINER DI POJOK BAWAH RAPORT */
        .footer-raport { position: absolute; bottom: 25mm; left: 30mm; right: 30mm; display: flex; justify-content: space-between; align-items: flex-end; font-family: sans-serif; }
        .qr-box { text-align: center; font-size: 9px; color: #64748b; font-weight: bold; }
        .qr-image { width: 75px; height: 75px; border: 1px solid #e2e8f0; padding: 4px; background: white; border-radius: 6px; margin-bottom: 4px; }
        .sig-box { text-align: center; font-size: 12px; width: 200px; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="page-front">
        <div class="overlay-front">
            <div class="cert-id">No. Registrasi Kelulusan: {{ $registration->final_id_number }}</div>
            <div class="user-name">{{ $registration->user->name }}</div>
            <div class="cert-desc">
                Dinyatakan LULUS dan memenuhi syarat kualifikasi kompetensi pada seluruh program inkubasi kerja bertajuk <strong style="font-style: normal; color:#0f172a;">"{{ $program->name }}"</strong> dengan pencapaian hasil transkrip akademik terlampir di balik dokumen ini.
            </div>
        </div>
    </div>

    <div class="page-back">
        <div class="raport-title">Transkrip Nilai Pencapaian Akademik</div>
        <div class="raport-sub">Lampiran Sertifikat Resmi Tata Kelola Hasil Evaluasi Berkas Anggota</div>

        <div class="student-info">
            <div>Nama Peserta: {{ $registration->user->name }}</div>
            <div>ID Induk: {{ $registration->final_id_number }}</div>
            <div>Beban Pelaksanaan: {{ $program->total_hours ?? 32 }} JP (Jam Pelajaran)</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">No</th>
                    <th style="width: 67%;">Komponen Kriteria Penilaian Materi Kompetensi</th>
                    <th style="width: 25%;" class="text-center">Capaian Nilai Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registration->final_scores ?? [] as $index => $row)
                    <tr>
                        <td class="text-center" style="font-family: monospace;">{{ $index + 1 }}</td>
                        <td style="font-weight: bold; color:#334155;">{{ $row['title'] }}</td>
                        <td class="text-center" style="font-family: monospace; font-weight: bold; font-size: 13px; color:#065f46;">{{ $row['score'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center" style="color:#94a3b8; font-style:italic;">Belum ada komponen nilai akhir yang disuntikkan panitia pelaksana.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-raport">
            <div class="qr-box">
                <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode($qrVerificationUrl) }}&choe=UTF-8" class="qr-image">
                <span class="block font-mono" style="font-size: 8px;">SCAN UNTUK VERIFIKASI SAH</span>
            </div>

            <div class="sig-box">
                <p style="margin:0 0 45px 0;">Bandung, {{ date('d F Y', strtotime($program->end_date)) }}<br><strong>Direktur Eksekutif Program,</strong></p>
                <p style="font-weight:bold; border-bottom: 1px solid #334155; margin:0; padding-bottom:2px;">Tri Febriansah</p>
                <p style="margin:2px 0 0 0; font-size:9px; color:#94a3b8; font-family:sans-serif;">ID Otoritas Pelaksana Utama</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>

</body>
</html>
