<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Konsultasi Program Baru</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fafbfc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #fafbfc; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">

                    <tr>
                        <td style="background-color: #065f46; padding: 30px 40px; text-align: left;">
                            <span style="font-size: 10px; font-weight: bold; color: #34d399; letter-spacing: 2px; text-transform: uppercase;">Pertanyaan / Konsultasi Baru</span>
                            <h1 style="font-size: 20px; font-weight: 800; color: #ffffff; margin: 10px 0 0 0;">{{ $consultation->program->name }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 14px; color: #475569; margin: 0 0 20px 0;">Halo Admin Program,</p>
                            <p style="font-size: 13px; color: #64748b; line-height: 1.6; margin: 0 0 25px 0;">Ada pertanyaan masuk dari peserta terkait program yang Anda kelola. Berikut rinciannya:</p>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-left: 4px solid #f59e0b; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="font-size: 12px; color: #64748b; margin: 0 0 5px 0;"><strong>Pengirim:</strong> {{ $consultation->user->name }} ({{ $consultation->user->email }})</p>
                                        <h3 style="font-size: 15px; font-weight: bold; color: #0f172a; margin: 10px 0 10px 0;">❓ {{ $consultation->subject }}</h3>
                                        <div style="font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-wrap;">{{ $consultation->question }}</div>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align: center; margin: 30px 0 0 0;">
                                <a href="{{ route('adminprogram.programs.workspace', $consultation->program_id) }}" style="background-color: #059669; color: #ffffff; padding: 12px 30px; font-size: 12px; font-weight: bold; text-decoration: none; border-radius: 10px; display: inline-block; box-shadow: 0 4px 6px rgba(5,150,105,0.15); text-transform: uppercase; letter-spacing: 1px;">
                                    Buka Workspace & Berikan Jawaban &rarr;
                                </a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 40px; text-align: center; border-top: 1px solid #f1f5f9;">
                            <span style="font-size: 11px; color: #94a3b8;">Surat elektronik ini dikirim otomatis oleh Sistem Tata Kelola Program IDP Kampus. Hak Cipta Dilindungi &copy; 2026.</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
