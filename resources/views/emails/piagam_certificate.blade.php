<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
            background-color: #fafafa;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #059669;">Sertifikat Program</h2>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $certificate->participant->user->name ?? 'Peserta' }}</strong>,</p>
            <p>Selamat! Anda telah berhasil menyelesaikan program <strong>{{ $certificate->program->name ?? 'Program' }}</strong>.</p>
            <p>Sebagai bentuk penghargaan dan bukti kelulusan, kami melampirkan sertifikat resmi Anda pada email ini.</p>
            <p>Terima kasih atas partisipasi Anda.</p>
            <p><br>Salam Hangat,<br>Tim {{ config('app.name', 'Institut Hijau Indonesia') }}</p>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
