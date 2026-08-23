<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapan Jawaban Tahap: {{ $stage->name }}</title>
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
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">{{ $program->name }}</h1>
                <h2 class="text-sm font-bold text-emerald-700 uppercase tracking-wide">Rekapan Jawaban Tahap: {{ $stage->name }}</h2>
                <p class="text-xs text-slate-400 mt-1">Dicetak pada: {{ date('d F Y H:i') }}</p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                    🖨️ Cetak / Simpan PDF
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase border-b">
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Tanggal Submit</th>
                        <th class="p-3">Status</th>
                        @foreach($schema as $field)
                            <th class="p-3">{{ $field['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y text-slate-600">
                    @forelse($submissions as $sub)
                        @if(!$sub->registration || !$sub->registration->user) @continue @endif
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3 font-bold text-slate-800">{{ $sub->registration->user->name }}</td>
                            <td class="p-3 font-mono">{{ $sub->registration->user->email }}</td>
                            <td class="p-3">{{ $sub->updated_at ? $sub->updated_at->format('d M Y H:i') : '-' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($sub->status === 'passed') bg-emerald-100 text-emerald-800
                                    @elseif($sub->status === 'failed') bg-rose-100 text-rose-800
                                    @else bg-amber-100 text-amber-800 @endif border">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            @php
                                $values = collect($sub->form_values)->keyBy('field_name');
                            @endphp
                            @foreach($schema as $field)
                                @php
                                    $fieldName = $field['name'];
                                    $val = isset($values[$fieldName]) ? $values[$fieldName]['value'] : null;
                                @endphp
                                <td class="p-3">
                                    @if(empty($val))
                                        <span class="text-slate-400 italic">Kosong</span>
                                    @elseif($field['type'] === 'file' || $field['type'] === 'image')
                                        <a href="{{ asset('storage/' . $val) }}" target="_blank" class="text-emerald-600 hover:underline font-bold">
                                            Unduh Lampiran
                                        </a>
                                        @if($field['type'] === 'image')
                                            <div class="mt-1">
                                                <img src="{{ asset('storage/' . $val) }}" class="w-12 h-12 rounded object-cover shadow-3xs border" alt="preview">
                                            </div>
                                        @endif
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + count($schema) }}" class="p-8 text-center text-slate-400 italic">
                                Belum ada berkas pendaftaran yang masuk/diisi untuk tahap ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
