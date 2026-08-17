<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapan Jawaban Peserta: {{ $registration->user->name }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Rekapan Jawaban Pendaftaran</h1>
                <p class="text-sm font-semibold text-emerald-700 uppercase tracking-wide">{{ $program->name }}</p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                    🖨️ Cetak / Simpan PDF
                </button>
            </div>
        </div>

        <!-- Participant Profile -->
        <div class="bg-white p-6 rounded-2xl border shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</span>
                <p class="text-sm font-bold text-slate-800">{{ $registration->user->name }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Address</span>
                <p class="text-sm font-bold text-slate-800 font-mono">{{ $registration->user->email }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Registrasi</span>
                <span class="inline-block mt-0.5 px-2.5 py-0.5 text-xs font-bold rounded bg-emerald-50 text-emerald-700 border uppercase">
                    {{ $registration->status }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">NIA / ID Induk</span>
                <p class="text-sm font-bold font-mono text-emerald-800">{{ $registration->final_id_number ?? '-' }}</p>
            </div>
            <div class="md:col-span-2 border-t pt-4 mt-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Harapan &amp; Motivasi Mengikuti Program</span>
                <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-xl border whitespace-pre-wrap font-medium">{{ $registration->motivation ?? '-' }}</p>
            </div>
        </div>

        <!-- Answers by Stage -->
        <div class="space-y-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Rincian Berkas dan Kuesioner Tahapan:</h3>
            @foreach($stageSubmissions as $index => $item)
                @php
                    $stage = $item['stage'];
                    $data = $item['data'];
                    $formValues = $data ? ($data->form_values ?? []) : [];
                @endphp
                
                <div class="bg-white p-5 rounded-2xl border shadow-sm space-y-4">
                    <div class="flex justify-between items-center border-b pb-2">
                        <div class="flex items-center space-x-2">
                            <span class="h-5 w-5 rounded-full bg-emerald-50 text-emerald-700 border flex items-center justify-center font-bold text-xs">{{ $stage->sequence }}</span>
                            <h4 class="text-sm font-bold text-slate-800">{{ $stage->name }}</h4>
                        </div>
                        @if($data)
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase border 
                                @if($data->status === 'passed') bg-emerald-50 text-emerald-800 border-emerald-200
                                @elseif($data->status === 'failed') bg-rose-50 text-rose-800 border-rose-200
                                @else bg-amber-50 text-amber-800 border-amber-200 @endif">
                                Status: {{ $data->status }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase bg-slate-50 text-slate-400 border">Belum Aktif</span>
                        @endif
                    </div>

                    @if(empty($formValues))
                        <p class="text-xs text-slate-400 italic">Tidak ada berkas yang diunggah pada tahapan ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($formValues as $val)
                                <div class="bg-slate-50/50 p-3 rounded-xl border space-y-1">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $val['field_name'] }}</span>
                                    @if(empty($val['value']))
                                        <p class="text-xs text-slate-400 italic">Kosong / Tidak diisi</p>
                                    @elseif($val['type'] === 'file')
                                        <a href="{{ asset('storage/' . $val['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg hover:bg-emerald-100 transition shadow-3xs mt-1">
                                            📥 Download Lampiran Berkas
                                        </a>
                                    @elseif($val['type'] === 'image')
                                        <div class="space-y-1.5 pt-1">
                                            <a href="{{ asset('storage/' . $val['value']) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg hover:bg-emerald-100 transition shadow-3xs">
                                                🔍 Lihat Gambar Ukuran Penuh
                                            </a>
                                            <img src="{{ asset('storage/' . $val['value']) }}" class="max-w-xs max-h-32 rounded-lg border shadow-3xs object-cover mt-1" alt="preview">
                                        </div>
                                    @else
                                        <p class="text-xs font-bold text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $val['value'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($data && $data->reviewer_notes)
                            <div class="p-3 bg-emerald-50/20 border border-emerald-100 rounded-xl text-xs mt-2">
                                <span class="font-bold text-emerald-950 block">Catatan Reviewer:</span>
                                <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $data->reviewer_notes }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
