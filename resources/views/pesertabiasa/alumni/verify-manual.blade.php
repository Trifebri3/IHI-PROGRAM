@extends('pesertabiasa.layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Verifikasi Alumni Program Lama
</h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header / Back button -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('peserta.alumni.index') }}" class="p-2 bg-white border border-gray-200 text-gray-600 hover:text-gray-900 rounded-xl shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h3 class="text-lg font-black text-gray-800">Verifikasi Manual Alumni</h3>
                <p class="text-xs font-semibold text-gray-400">Ajukan bukti kelulusan untuk program kerja IHI terdahulu</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
            @if(count($programs) === 0)
                <div class="p-4 text-center text-gray-400 italic text-sm">
                    Tidak ada program lama yang tersedia untuk diverifikasi (atau Anda sudah terdaftar di semua program).
                </div>
            @else
                <form method="POST" action="{{ route('peserta.alumni.verify.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Program Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Program Yang Pernah Diikuti</label>
                        <select name="program_id" required class="w-full text-sm px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="">-- Pilih Program --</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 font-semibold mt-1.5">
                            Pilihlah salah satu dari program kerja yang terdaftar di database utama.
                        </p>
                    </div>

                    <!-- Scan Certificate File Upload -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Unggah Scan/Screenshot Piagam (Max 5MB)</label>
                        <input type="file" name="certificate_scan" accept="image/jpeg,image/png,image/jpg" required class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl p-1.5 focus:outline-none focus:border-emerald-500 transition-colors">
                        <p class="text-[10px] text-gray-400 font-semibold mt-1.5">
                            Format berkas yang didukung: JPG, JPEG, PNG. Pastikan nama lengkap Anda dan informasi program terlihat sangat jelas.
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('peserta.alumni.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm hover:bg-emerald-700 transition-colors shadow-md">
                            Ajukan Verifikasi Berkas
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
