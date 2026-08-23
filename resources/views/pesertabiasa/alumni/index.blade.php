@extends('pesertabiasa.layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Portal Alumni
</h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Flash Message -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl shadow-sm text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Profile Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-2xl border border-emerald-100">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-400 font-semibold">{{ $user->email }}</p>
                        @if($user->alumniProfile && $user->alumniProfile->alumni_number)
                            <div class="inline-flex items-center gap-1.5 mt-2 bg-emerald-50 text-emerald-800 text-xs font-black px-2.5 py-1 rounded-lg border border-emerald-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                NIA: {{ $user->alumniProfile->alumni_number }}
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 mt-2 bg-slate-50 text-slate-500 text-xs font-bold px-2.5 py-1 rounded-lg border border-slate-200">
                                Anggota Terdaftar (Status: {{ $user->status }})
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <a href="{{ route('peserta.alumni.verify.form') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 border border-transparent rounded-xl text-sm font-bold text-white hover:bg-emerald-700 shadow-md hover:shadow-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Verifikasi Alumni Program Lama
                    </a>
                </div>
            </div>
        </div>

        <!-- Graduated Programs & Certificates -->
        <div class="space-y-4">
            <h3 class="text-base font-extrabold text-gray-700 tracking-tight">Riwayat Program & Piagam Digital</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($graduatedPrograms as $gp)
                    @php
                        $cert = \App\Models\AlumniCertificate::where('user_id', $user->id)
                            ->where('alumni_program_id', $gp->alumniProgram->id)
                            ->first();
                        $extra = $gp->extra_info;
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between hover:border-gray-200 transition-colors">
                        <!-- Top Info -->
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                        Lulus Program
                                    </span>
                                    <h4 class="text-base font-black text-gray-800 mt-2">{{ $gp->alumniProgram->name }}</h4>
                                    <p class="text-xs text-gray-400 font-semibold">Tahun Pelaksanaan: {{ $gp->alumniProgram->year }}</p>
                                </div>
                                <span class="text-xs font-mono font-semibold text-gray-400 bg-gray-50 border border-gray-100 px-2 py-1 rounded-lg">
                                    UUID: {{ substr($gp->uuid, 0, 8) }}...
                                </span>
                            </div>

                            <!-- Academic transkrip overlay -->
                            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 text-xs text-slate-600 space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>Jam Pelatihan: <strong>{{ $extra['jam_pelatihan'] ?? 32 }} JP</strong></div>
                                    @if(!empty($extra['nilai_akhir']))
                                        <div>Nilai Akhir: <strong class="text-slate-800">{{ $extra['nilai_akhir'] }}</strong></div>
                                    @endif
                                    @if(!empty($extra['predikat']))
                                        <div>Predikat: <strong class="text-slate-800">{{ $extra['predikat'] }}</strong></div>
                                    @endif
                                    @if(!empty($extra['ranking']))
                                        <div>Ranking: <strong class="text-slate-800">{{ $extra['ranking'] }}</strong></div>
                                    @endif
                                    @if(!empty($extra['skor_assessment']))
                                        <div>Skor Assessment: <strong class="text-slate-800">{{ $extra['skor_assessment'] }}</strong></div>
                                    @endif
                                </div>
                                @if(!empty($extra['kompetensi']))
                                    <div class="border-t border-slate-200/60 pt-2">
                                        <div class="font-bold text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Kompetensi Dicapai:</div>
                                        <p class="text-slate-500 leading-relaxed">{{ $extra['kompetensi'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Footer -->
                        <div class="bg-gray-50 border-t border-gray-100 p-4 flex justify-between items-center">
                            @if($cert)
                                <a href="{{ route('public.alumni.verify', $cert->uuid) }}" target="_blank" class="text-xs font-bold text-slate-500 hover:text-emerald-700 hover:underline flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Validasi QR Verifikasi
                                </a>
                                
                                <a href="{{ route('peserta.alumni.certificate.download', $cert->uuid) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Unduh Piagam (PDF)
                                </a>
                            @else
                                <span class="text-xs text-amber-600 font-bold bg-amber-50 px-2.5 py-1 rounded-md border border-amber-100">
                                    Piagam Sedang Diproses
                                </span>
                                <span></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 italic">
                        Belum ada riwayat kelulusan program yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Manual Verifications -->
        @if(count($pendingRequests) > 0)
            <div class="space-y-4">
                <h3 class="text-base font-extrabold text-gray-700 tracking-tight">Status Pengajuan Verifikasi Program Lama</h3>
                <div class="space-y-3">
                    @foreach($pendingRequests as $pr)
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">{{ $pr->alumniProgram->name }} ({{ $pr->alumniProgram->year }})</h4>
                                <p class="text-xs text-gray-400 font-semibold mt-0.5">Diajukan pada: {{ $pr->created_at->format('d M Y H:i') }}</p>
                                
                                @if($pr->status === 'revision' && $pr->admin_notes)
                                    <div class="mt-2 bg-rose-50 border border-rose-100 rounded-xl p-2.5 text-xs text-rose-800">
                                        <strong>Catatan Revisi Admin:</strong> {{ $pr->admin_notes }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                @if($pr->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100 animate-pulse">
                                        Menunggu Review Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        Butuh Revisi Berkas
                                    </span>
                                    <a href="{{ route('peserta.alumni.verify.form') }}" class="px-3 py-1.5 bg-slate-800 text-white font-bold text-xs rounded-lg hover:bg-slate-900 transition-colors">
                                        Ajukan Ulang
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
