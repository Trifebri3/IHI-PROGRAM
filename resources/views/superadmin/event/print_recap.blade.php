<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAP_LAPORAN_{{ Str::slug($event->title, '_') }}</title>
    <style>
        /* CSS Reset & Formatter Kertas A4 Standar Formal */
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        /* Kop Surat / Header Dokumen */
        .header-container {
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header-container h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header-container p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        /* Meta Data Ringkasan Event */
        .meta-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .meta-item span {
            font-weight: bold;
            color: #475569;
        }
        /* Tabel Rekap Data Hardcore */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; font-size: 11px; }
        .badge-success { color: #15803d; font-weight: bold; }
        .badge-danger { color: #b91c1c; font-style: italic; }

        /* Baris data formulir kustom gform */
        .custom-field-box {
            font-size: 11px;
            margin-bottom: 4px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .custom-field-box:last-child { border-bottom: none; }
        .field-label { color: #64748b; font-weight: bold; font-size: 9px; text-transform: uppercase; }

        /* Tanda Tangan / Otoritas Dokumen Berkas */
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-space {
            height: 70px;
        }

        /* Hapus elemen yang tidak perlu saat cetak ke printer/PDF asli */
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- KOP DOKUMEN RESMI -->
    <div class="header-container">
        <h1>Laporan Rekapitulasi Data & Kehadiran Peserta</h1>
        <p>Sistem Portal Manajemen Pengayaan Inovasi & Kegiatan Digital Kampus</p>
    </div>

    <!-- METADATA EVENT RINGKAS -->
    <div class="meta-grid">
        <div class="meta-item"><span>Nama Kegiatan:</span> {{ $event->title }}</div>
        <div class="meta-item"><span>Lokasi/Akses:</span> {{ $event->location }}</div>
        <div class="meta-item"><span>Tanggal Pelaksanaan:</span> {{ date('d F Y', strtotime($event->event_date)) }}</div>
        <div class="meta-item"><span>Total Pendaftar:</span> {{ $event->registrations_count }} / {{ $event->quota }} Slot Kuota</div>
    </div>

    <!-- TABEL UTAMA REKAP -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 26%;">Nama Anggota / Email</th>
                <th style="width: 20%;">Waktu Registrasi</th>
                <th style="width: 32%;">Hasil Jawaban Formulir Kustom</th>
                <th style="width: 18%;" class="text-center">Status Presensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recapSubmissions as $index => $sub)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>
                        @if($sub->user)
                            <strong>{{ $sub->user->name }}</strong><br>
                            <span style="font-size: 10px; color: #64748b;">{{ $sub->user->email }} (Anggota)</span>
                        @else
                            <strong>{{ $sub->guest_name }}</strong><br>
                            <span style="font-size: 10px; color: #64748b;">{{ $sub->guest_email }} (Umum/Tamu)</span>
                            @if($sub->guest_phone)
                                <div style="font-size: 9px; color: #64748b; margin-top: 1px;">📞 {{ $sub->guest_phone }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="font-mono">{{ $sub->created_at->format('d M Y - H:i') }} WIB</td>
                    <td>
                        @forelse($sub->form_values ?? [] as $val)
                            <div class="custom-field-box">
                                <div class="field-label">{{ $val['label'] }}:</div>
                                <div>{{ $val['value'] ?? '—' }}</div>
                            </div>
                        @empty
                            <span style="color: #94a3b8; font-style: italic; font-size: 11px;">Klaim Tiket Instan</span>
                        @endforelse
                    </td>
                    <td class="text-center">
                        @if($sub->attended_at)
                            <span class="badge-success">HADIR</span><br>
                            <span class="font-mono" style="font-size: 9px; color: #64748b;">{{ date('H:i', strtotime($sub->attended_at)) }} WIB</span>
                        @else
                            <span class="badge-danger">ALFA / ABSEN</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; font-style: italic; padding: 20px;">Belum ada data keikutsertaan peserta untuk kegiatan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR VALIDASI TANDA TANGAN INTEGRITAS -->
    <div class="signature-area">
        <div class="signature-box">
            <p>Bandung, {{ date('d F Y') }}<br><strong>Super Admin Pelaksana,</strong></p>
            <div class="signature-space"></div>
            <p style="border-bottom: 1px solid #334155; padding-bottom: 3px; font-weight: bold; margin-bottom: 2px;">{{ auth()->user()->name }}</p>
            <p style="margin: 0; font-size: 10px; color: #64748b; font-mono">ID Otoritas: SA-{{ auth()->id() }}</p>
        </div>
    </div>

    <!-- AUTOMATION SCRIPT: Otomatis memicu ctrl+p sesaat setelah halaman di-load sempurna -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>
