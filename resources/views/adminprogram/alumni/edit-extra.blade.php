@extends('adminprogram.layouts.app')

@section('title', 'Edit Data Akademik Alumni')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('adminprogram.alumni.index') }}" class="p-2 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 rounded-xl shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-black text-slate-800 tracking-tight sm:text-2xl">
                Edit Transkrip Akademik Alumni
            </h1>
            <p class="text-xs font-semibold text-slate-400">
                {{ $alumni->user->name }} &bull; {{ $alumni->alumniProgram->name }} ({{ $alumni->alumniProgram->year }})
            </p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('adminprogram.alumni.update-extra', $alumni->id) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Nilai Akhir -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nilai Akhir (Skor/Huruf)</label>
                    <input type="text" name="nilai_akhir" value="{{ old('nilai_akhir', $alumni->extra_info['nilai_akhir'] ?? '') }}" placeholder="Contoh: 92.5 atau A" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Predikat Kelulusan -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Predikat Kelulusan</label>
                    <input type="text" name="predikat" value="{{ old('predikat', $alumni->extra_info['predikat'] ?? '') }}" placeholder="Contoh: Dengan Pujian / Sangat Memuaskan" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Ranking -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ranking Kelas (Opsional)</label>
                    <input type="text" name="ranking" value="{{ old('ranking', $alumni->extra_info['ranking'] ?? '') }}" placeholder="Contoh: Juara 1 / Peringkat 3 dari 40" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Skor Assessment -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Skor Assessment (Opsional)</label>
                    <input type="text" name="skor_assessment" value="{{ old('skor_assessment', $alumni->extra_info['skor_assessment'] ?? '') }}" placeholder="Contoh: 850" class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Jam Pelatihan -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Durasi Jam Pelajaran (JP)</label>
                    <input type="number" name="jam_pelatihan" value="{{ old('jam_pelatihan', $alumni->extra_info['jam_pelatihan'] ?? $alumni->alumniProgram->program->total_hours ?? 32) }}" required class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Kompetensi yang Dicapai -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kompetensi yang Dicapai</label>
                    <textarea name="kompetensi" rows="3" placeholder="Tulis kompetensi yang diperoleh alumni secara ringkas..." class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">{{ old('kompetensi', $alumni->extra_info['kompetensi'] ?? '') }}</textarea>
                </div>

                <!-- Catatan Khusus -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catatan Khusus Admin</label>
                    <textarea name="catatan" rows="3" placeholder="Misal: Aktif sebagai ketua angkatan atau lulusan terbaik program..." class="w-full text-sm px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-colors">{{ old('catatan', $alumni->extra_info['catatan'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('adminprogram.alumni.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm hover:bg-emerald-700 transition-colors shadow-md">
                    Simpan & Update Piagam
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
