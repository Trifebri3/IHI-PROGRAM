@extends('adminprogram.layouts.app')

@section('title', 'Verifikasi Program Lama')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('adminprogram.alumni.index') }}" class="p-2 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 rounded-xl shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight sm:text-3xl">
                Manual Alumni Verifications
            </h1>
            <p class="text-sm font-medium text-slate-500">
                Tinjau berkas scan piagam kelulusan dari alumni program terdahulu.
            </p>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">User</th>
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Program Terpilih</th>
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Scan Piagam</th>
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Tanggal Pengajuan</th>
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400">Status</th>
                        <th class="p-4 text-xs font-extrabold uppercase tracking-wider text-slate-400 text-right">Aksi Evaluasi</th>
                    </tr>
                </thead>
                @forelse($requests as $r)
                    <tbody x-data="{ openEvaluate: false }" class="divide-y divide-slate-100 text-sm">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $r->user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $r->user->email }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-700">{{ $r->alumniProgram->name }}</div>
                                <div class="text-xs text-slate-400">Tahun Program: {{ $r->alumniProgram->year }}</div>
                            </td>
                            <td class="p-4">
                                <a href="{{ asset('storage/' . $r->certificate_scan_path) }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Lihat Berkas Scan
                                </a>
                            </td>
                            <td class="p-4 text-slate-500">
                                {{ $r->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="p-4">
                                @if($r->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                        Menunggu Verifikasi
                                    </span>
                                @elseif($r->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Disetujui
                                    </span>
                                @elseif($r->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        Revisi Dokumen
                                    </span>
                                @endif
                                
                                @if($r->admin_notes)
                                    <div class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate" title="{{ $r->admin_notes }}">
                                        Catatan: {{ $r->admin_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                @if($r->status === 'pending' || $r->status === 'revision')
                                    <button @click="openEvaluate = !openEvaluate" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors shadow-sm">
                                        Evaluasi Berkas
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Evaluasi Selesai</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Inline Evaluation Form (Sibling Row inside the same TBODY) -->
                        <tr x-show="openEvaluate" x-cloak class="bg-slate-50/50">
                            <td colspan="6" class="p-6 border-b border-slate-100">
                                <form method="POST" action="{{ route('adminprogram.alumni.verifications.process', $r->id) }}" class="space-y-4 text-left max-w-xl mx-auto bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                                    @csrf
                                    <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Form Evaluasi Piagam Alumni</h3>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Action Choice -->
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Keputusan</label>
                                            <select name="action" required class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500">
                                                <option value="approve">Setujui & Terbitkan Alumni</option>
                                                <option value="revision">Minta Revisi Dokumen</option>
                                                <option value="reject">Tolak Permohonan</option>
                                            </select>
                                        </div>

                                        <!-- Score fields only needed if approving -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nilai Akhir (Opsional)</label>
                                            <input type="text" name="nilai_akhir" placeholder="Skor / Huruf" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Predikat (Opsional)</label>
                                            <input type="text" name="predikat" placeholder="Dengan Pujian / dll" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ranking (Opsional)</label>
                                            <input type="text" name="ranking" placeholder="Peringkat" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jam Pelatihan (Opsional)</label>
                                            <input type="number" name="jam_pelatihan" placeholder="Durasi JP (Def: 32)" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none">
                                        </div>

                                        <!-- Notes -->
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Catatan / Alasan (Wajib jika Revisi/Tolak)</label>
                                            <textarea name="admin_notes" rows="2" placeholder="Tulis alasan jika menolak atau instruksi revisi jika meminta perbaikan berkas..." class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none"></textarea>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                                        <button type="button" @click="openEvaluate = false" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors">
                                            Batal
                                        </button>
                                        <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors shadow-sm">
                                            Simpan Keputusan
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody class="text-sm">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                Belum ada permohonan verifikasi alumni yang diajukan.
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
