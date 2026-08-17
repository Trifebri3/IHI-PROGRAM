<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PIAGAM_{{ Str::slug(auth()->user()->name, '_') }}</title>
    <style>
        /* Pengaturan Layout Kertas A4 Landscape Presisi */
        @page {
            size: A4 landscape;
            margin: 0;
        }
        body, html {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'Georgia', 'Times New Roman', Times, serif;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* CONTAINER UTAMA: Memasang base gambar PNG panitia sebagai background penuh */
        .certificate-wrapper {
            position: relative;
            width: 297mm;
            height: 210mm;
            background-image: url("{{ asset('storage/' . $event->certificate_template_path) }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            box-sizing: border-box;
        }

        /* --- LAYER OVERLAY TEKS (POSISI DIATUR ABSOLUTE PRESISI DI TENGAH) --- */
        .content-overlay {
            position: absolute;
            width: 100%;
            top: 75mm; /* Menggantung tepat di area tengah bidang kertas (bisa digeser sesuai draf PNG) */
            text-align: center;
        }

        .cert-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            color: #475569;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .participant-name {
            font-size: 34px;
            font-weight: bold;
            color: #064e3b; /* Hijau Emerald Mewah ("Mahal") */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            border-bottom: 2px solid transparent;
            display: inline-block;
            padding-bottom: 5px;
        }

        .cert-text {
            font-size: 14px;
            color: #334155;
            font-style: italic;
            margin-top: 15px;
            line-height: 1.6;
        }

        .event-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            font-style: normal;
            display: block;
            margin-top: 5px;
        }

        .event-date {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
            font-style: normal;
        }

        /* Otomatis memicu ctrl+p print saving PDF saat tab terbuka */
        @media print {
            body, html { width: 297mm; height: 210mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="certificate-wrapper">

        <div class="content-overlay">

            <div class="cert-number">No: {{ $certNumber }}</div>

            <div class="participant-name">{{ auth()->user()->name }}</div>

            <div class="cert-text">
                Diberikan atas kontribusi, dedikasi, dan partisipasi aktifnya sebagai peserta dalam kegiatan:
                <span class="event-title">"{{ $event->title }}"</span>
                <span class="event-date">Diselenggarakan pada tanggal {{ date('d F Y', strtotime($event->event_date)) }} melalui platform {{ $event->location }}</span>
            </div>

        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>
