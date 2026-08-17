@extends('superadmin.layouts.app')

@section('title', 'Super Power Panel')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <!-- Top Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md uppercase tracking-wide">Emergency &amp; Testing Override Desk</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-2">Super Power Panel</h1>
            <p class="text-xs text-slate-400 mt-1">Gubernansi akses hardcore: generator akun dummy massal, impor data spreadsheet langsung, dan override integrasi program.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('superadmin.power-panel.toggle-mitigation') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-extrabold rounded-xl transition border shadow-3xs uppercase tracking-wider {{ $mitigationMode === '1' ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600' : 'bg-slate-50 text-slate-650 hover:bg-slate-100' }}">
                    🚨 Tombol Mitigasi: {{ $mitigationMode === '1' ? 'AKTIF' : 'NON-AKTIF' }}
                </button>
            </form>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-slate-105 text-slate-700 hover:bg-slate-200 text-xs font-bold rounded-xl transition border shadow-3xs">
                &larr; Kembali ke Dasbor
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold shadow-3xs flex items-center">
            <span>✨ {{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold shadow-3xs flex items-center">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-medium space-y-1 shadow-3xs">
            <span class="font-bold block mb-1">⚠️ Terjadi kendala input data:</span>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <!-- Three Column Tools Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- CARD 1: GENERATOR DUMMY -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between space-y-5">
            <div class="space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                        <span class="text-lg mr-2">⚡</span> Generator Akun Dummy
                    </h3>
                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 uppercase">
                        {{ $dummyUsersCount }} Dummy Active
                    </span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Buat akun dummy pendaftar secara instan untuk simulasi alur tahapan. Seluruh akun yang dihasilkan otomatis berstatus email terverifikasi dan KYC lolos centang biru.</p>
                
                <form action="{{ route('superadmin.power-panel.generate-dummy') }}" method="POST" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase">Jumlah Akun Dummy</label>
                        <select name="count" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs bg-white text-slate-800 focus:ring-1 focus:ring-emerald-500">
                            <option value="10">10 Akun</option>
                            <option value="50" selected>50 Akun</option>
                            <option value="100">100 Akun</option>
                            <option value="200">200 Akun</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase">Password Default Akun</label>
                        <input type="text" name="password" value="password123" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 text-slate-800" required>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-amber-500 to-orange-650 text-white font-extrabold text-xs rounded-xl shadow-xs hover:from-amber-600 transition uppercase tracking-wider">
                        ⚡ Generate Akun Dummy
                    </button>
                </form>
            </div>

            <!-- Danger zone to clean dummy users -->
            <div class="pt-4 border-t border-dashed border-slate-100">
                <form action="{{ route('superadmin.power-panel.delete-dummy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh akun dummy dari database? Aksi ini akan membersihkan data pendaftaran dan file lampiran milik akun dummy secara permanen.');">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 hover:text-rose-800 font-extrabold text-xs rounded-xl border border-rose-200 transition uppercase tracking-wider">
                        ✕ Hapus Semua Akun Dummy
                    </button>
                </form>
            </div>
        </div>

        <!-- CARD 2: IMPOR AKUN MASSAL -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                    <span class="text-lg mr-2">📥</span> Impor Akun Spreadsheet
                </h3>
                <a href="{{ route('superadmin.power-panel.download-template') }}" class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100 hover:bg-emerald-100 transition shadow-3xs uppercase">
                    📥 Template CSV
                </a>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed font-medium">Impor akun dalam jumlah banyak sekaligus. Bypass verifikasi email secara otomatis agar akun langsung aktif.</p>

            <form action="{{ route('superadmin.power-panel.import-users') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-1">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Pilihan 1: Upload File CSV</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="w-full mt-1.5 p-2 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-600 focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Pilihan 2: Paste Baris Data Excel/Spreadsheet</label>
                    <textarea name="raw_text" placeholder="Format: Nama,Email,Password (Contoh)&#10;Budi Santoso,budi@gmail.com,rahasia123&#10;Siti Aminah,siti@gmail.com,amanah456" class="w-full mt-1.5 p-2.5 border border-slate-200 rounded-xl text-xs font-mono focus:ring-1 focus:ring-emerald-500 text-slate-800" rows="5"></textarea>
                    <span class="text-[9px] text-slate-400 block pt-1 leading-normal">Salin kolom Nama, Email, Password di Excel lalu langsung tempelkan di sini (Pemisah TAB atau Koma dideteksi otomatis).</span>
                </div>

                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-700 text-white font-extrabold text-xs rounded-xl shadow-xs hover:from-emerald-700 transition uppercase tracking-wider">
                    📥 Mulai Impor Data Akun
                </button>
            </form>
        </div>

        <!-- CARD 3: PENDAFTARAN PAKSA -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                    <span class="text-lg mr-2">🔗</span> Pendaftaran Paksa Program
                </h3>
                <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">
                    Force Register
                </span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed font-medium">Daftarkan paksa email pendaftar ke program tertentu secara bypass. Jika email belum terdaftar di sistem, akun peserta akan dibuat otomatis secara otomatis.</p>

            <form action="{{ route('superadmin.power-panel.force-register') }}" method="POST" class="space-y-4 pt-1">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Pilih Program Kerja Sasaran</label>
                    <select name="program_id" class="w-full p-2.5 mt-1 border border-slate-200 rounded-xl text-xs bg-white text-slate-800 focus:ring-1 focus:ring-emerald-500 cursor-pointer" required>
                        <option value="">-- Pilih Program --</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Daftar Email Sasaran (Satu per baris)</label>
                    <textarea name="emails_text" placeholder="alamat1@email.com&#10;alamat2@email.com&#10;alamat3@email.com" class="w-full mt-1.5 p-2.5 border border-slate-200 rounded-xl text-xs font-mono focus:ring-1 focus:ring-emerald-500 text-slate-800" rows="5" required></textarea>
                    <span class="text-[9px] text-slate-400 block pt-1 leading-normal">Email yang diisi akan otomatis didaftarkan ke tahap ke-1 program tersebut. Password default akun baru adalah <span class="font-extrabold text-blue-750">password123</span>.</span>
                </div>

                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-xs hover:from-blue-750 transition uppercase tracking-wider">
                    🔗 Hubungkan &amp; Daftarkan Paksa
                </button>
            </form>
        </div>

    </div>

    <!-- Mitigation Tickets Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-50 mb-4">
            <h2 class="text-base font-extrabold text-slate-800 flex items-center">
                <span class="text-lg mr-2">🆘</span> Tiket Bantuan &amp; Aduan Peserta
            </h2>
            <span class="text-xs font-bold text-slate-400">
                {{ $pendingTickets->count() }} Tiket Tertunda
            </span>
        </div>

        @if($pendingTickets->isEmpty())
            <div class="p-8 text-center text-slate-400 italic bg-slate-50/20 rounded-xl border border-dashed border-slate-200">
                Tidak ada tiket bantuan/aduan tertunda saat ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-100">
                            <th class="p-4">Nama Peserta</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Jenis Kendala</th>
                            <th class="p-4">Rincian / Keluhan</th>
                            <th class="p-4">Waktu Kirim</th>
                            <th class="p-4 text-center">Aksi Mitigasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($pendingTickets as $ticket)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="p-4 font-bold text-slate-850">{{ $ticket->user->name }}</td>
                                <td class="p-4 text-slate-500 font-mono">{{ $ticket->user->email }}</td>
                                <td class="p-4">
                                    @if($ticket->issue_type === 'no_email')
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md font-bold border border-amber-100">Tidak Menerima Email</span>
                                    @elseif($ticket->issue_type === 'password')
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md font-bold border border-blue-100">Masalah Password</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md font-bold border border-slate-150">Lainnya</span>
                                    @endif
                                </td>
                                <td class="p-4 text-slate-600 max-w-xs truncate" title="{{ $ticket->description }}">{{ $ticket->description }}</td>
                                <td class="p-4 text-slate-400">{{ $ticket->created_at->diffForHumans() }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($ticket->issue_type === 'no_email')
                                            <form action="{{ route('superadmin.power-panel.resolve-ticket', $ticket->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="bypass">
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded-lg shadow-3xs uppercase tracking-wider transition">
                                                    Bypass &amp; Verifikasi Email
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('superadmin.power-panel.resolve-ticket', $ticket->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="resolve">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-500 hover:bg-slate-600 text-white font-bold text-[10px] rounded-lg shadow-3xs uppercase tracking-wider transition">
                                                Tandai Selesai
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
