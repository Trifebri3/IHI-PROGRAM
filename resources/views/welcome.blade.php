@extends('layouts.public')

@section('title', 'Selamat Datang di Portal Resmi PROGRAM INSTITUT HIJAU INDONESIA')

@section('content')

    <div class="bg-emerald-50/60 border-b border-emerald-100 py-3 px-4 text-center">
        <div class="max-w-5xl mx-auto flex items-center justify-center gap-2 text-emerald-950 text-xs sm:text-sm font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="leading-relaxed text-left sm:text-center">
                <span class="font-bold text-emerald-700 mr-1">[Informasi]</span> 
                Saat ini kami sedang dalam proses optimalisasi sistem dan pemindahan data secara berkala.
            </p>
        </div>
    </div>
    
    <div class="bg-white py-12 lg:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center mb-12">
                
                <div class="lg:col-span-7 space-y-6 text-left">
                    <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
                        Portal Resmi Registrasi Program & Event
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]">
                        The sound <br>
                        of a new <br>
                        <span class="text-emerald-600">generation</span>
                    </h1>
                    
                    <div class="max-w-2xl">
                        <p class="text-base sm:text-lg font-bold text-slate-800 leading-snug">
                            Sistem integrasi pendaftaran, validasi data transparan, dan manajemen profil dinamis untuk seluruh ekosistem Institut Hijau Indonesia.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    <div class="w-full max-w-md lg:max-w-full rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                        <img src="{{ asset('images/banner1.png') }}" alt="Banner Institut Hijau Indonesia" class="w-full h-auto object-cover max-h-[350px] lg:max-h-[400px]">
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4">
                
                <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white p-6 rounded-2xl shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-h-[140px] group">
                    <div class="flex justify-between items-start w-full">
                        <div class="p-2.5 bg-white/10 rounded-xl text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 0118 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 opacity-40 group-hover:opacity-100 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-emerald-100 font-medium block">Pendaftaran Akun</span>
                        <h3 class="text-base font-bold mt-1">Mulai Daftar Sekarang</h3>
                    </div>
                </a>

                <a href="https://e-learning.instituthijauindonesia.or.id/" target="_blank" class="bg-white border-2 border-slate-100 hover:border-emerald-600 text-slate-800 p-6 rounded-2xl shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-h-[140px] group">
                    <div class="flex justify-between items-start w-full">
                        <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Ruang Belajar</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1 group-hover:text-emerald-600 transition-colors">Akses E-Learning</h3>
                    </div>
                </a>

                <a href="https://program.instituthijauindonesia.or.id/public/program" class="bg-white border-2 border-slate-100 hover:border-emerald-600 text-slate-800 p-6 rounded-2xl shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-h-[140px] group">
                    <div class="flex justify-between items-start w-full">
                        <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Data Demografi</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1 group-hover:text-emerald-600 transition-colors">Statistik & Persebaran</h3>
                    </div>
                </a>

                <a href="https://program.instituthijauindonesia.or.id/public/program" class="bg-white border-2 border-slate-100 hover:border-emerald-600 text-slate-800 p-6 rounded-2xl shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-h-[140px] group">
                    <div class="flex justify-between items-start w-full">
                        <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Direktori Valid</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1 group-hover:text-emerald-600 transition-colors">Peserta Resmi Program</h3>
                    </div>
                </a>

            </div>

        </div>
    </div>


@endsection