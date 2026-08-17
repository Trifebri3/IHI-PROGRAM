<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{!! strip_tags($event->title) !!} - Institut Hijau Indonesia</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50/50 min-h-screen text-slate-800 font-['Plus_Jakarta_Sans'] antialiased py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- Back Navigation & Brand -->
        <div class="flex justify-between items-center pb-4 border-b border-slate-200">
            <a href="/" class="inline-flex items-center gap-2 text-slate-600 hover:text-emerald-700 font-extrabold text-xs uppercase tracking-widest transition-all">
                &larr; Kembali ke Beranda
            </a>
            <span class="text-xs font-black tracking-widest uppercase bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-full">
                Institut Hijau Indonesia
            </span>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-bold animate-fade-in shadow-xs">
                🎉 {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold animate-fade-in shadow-xs">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white border border-slate-100 p-6 sm:p-8 rounded-[2.5rem] shadow-sm space-y-8">
            
            <!-- Event Banner -->
            @if($event->banner_path)
                <div class="w-full h-64 sm:h-80 rounded-3xl overflow-hidden bg-slate-50 border border-slate-100 relative shadow-3xs">
                    <img src="{{ asset('storage/'.$event->banner_path) }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-start">
                
                <!-- Details (3 Columns) -->
                <div class="md:col-span-3 space-y-6 text-left">
                    <div class="space-y-2">
                        <span class="inline-block px-3 py-1 rounded-full text-[9px] font-extrabold uppercase {{ $event->registration_type === 'external' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($event->registration_type === 'logged_in' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                            {{ $event->registration_type === 'external' ? 'External Registration' : ($event->registration_type === 'logged_in' ? 'Khusus Akun Terdaftar' : 'Pendaftaran Terbuka') }}
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight uppercase tracking-tight">{{ $event->title }}</h1>
                    </div>

                    <!-- Meta Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-5 rounded-2xl border border-slate-150 text-xs shadow-3xs">
                        <div class="space-y-1">
                            <span class="block text-[8px] font-black uppercase text-emerald-700 tracking-widest">Waktu & Tanggal</span>
                            <span class="block font-bold text-slate-800 text-sm">📅 {{ date('d M Y', strtotime($event->event_date)) }}</span>
                            <span class="block text-[10px] text-slate-500 font-semibold mt-0.5">⏱️ {{ $event->event_time }} WIB</span>
                        </div>
                        <div class="space-y-1">
                            <span class="block text-[8px] font-black uppercase text-emerald-700 tracking-widest">Tempat / Lokasi</span>
                            <span class="block font-bold text-slate-800 text-sm leading-snug">📍 {{ $event->location }}</span>
                        </div>
                        <div class="space-y-1 sm:col-span-2 pt-3 border-t border-slate-200">
                            <span class="block text-[8px] font-black uppercase text-emerald-700 tracking-widest">Ketersediaan Kuota</span>
                            <span class="block font-black text-emerald-800 text-sm">👥 Terisi {{ $registeredCount }} / {{ $event->quota }} Kuota Peserta</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-450 border-b border-slate-100 pb-1.5">Deskripsi / Detail Acara</h2>
                        <div class="text-xs sm:text-sm text-slate-650 leading-relaxed space-y-4 font-semibold">
                            {!! $event->description !!}
                        </div>
                    </div>
                </div>

                <!-- Registration Panel (2 Columns) -->
                <div class="md:col-span-2 bg-white border border-slate-200/80 p-6 rounded-3xl text-left space-y-5 shadow-xs">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-800 pb-2 border-b border-slate-100">Form Pendaftaran</h3>

                    @if($isFull && !$alreadyRegistered)
                        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold text-center">
                            🚫 Mohon maaf, pendaftaran ditutup karena kuota penuh.
                        </div>
                    @elseif($alreadyRegistered)
                        <div class="p-4 bg-emerald-50 border border-emerald-250 text-emerald-850 rounded-xl text-xs font-bold text-center">
                            <span>✅ Anda sudah terdaftar dalam event ini!</span>
                        </div>
                    @else
                        
                        @if($event->registration_type === 'external')
                            <!-- External Link Registration -->
                            <div class="space-y-3">
                                <p class="text-[11px] text-slate-500 leading-relaxed font-semibold">Pendaftaran acara ini dikelola secara eksternal melalui platform mitra kami.</p>
                                <a href="{{ $event->external_link }}" target="_blank" class="block w-full py-3.5 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition-all shadow-xs hover:shadow-sm">
                                    Daftar Via Link Eksternal &rarr;
                                </a>
                            </div>
                        @elseif($event->registration_type === 'logged_in' && !auth()->check())
                            <!-- Logged In Only - Guest Mode -->
                            <div class="space-y-3">
                                <p class="text-[11px] text-slate-500 leading-relaxed font-semibold">Pendaftaran acara ini hanya diperuntukkan bagi kader/peserta yang terdaftar di platform kami.</p>
                                <a href="{{ route('login') }}" class="block w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition-all shadow-xs">
                                    Login Untuk Mendaftar &rarr;
                                </a>
                            </div>
                        @else
                            @if(!auth()->check())
                                <!-- Guest Registration Toggle Options -->
                                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200/50">
                                    <button type="button" id="tab-manual-btn" onclick="switchRegMode('manual')" class="py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer shadow-xs">
                                        Daftar Manual
                                    </button>
                                    <button type="button" id="tab-fast-btn" onclick="switchRegMode('fast')" class="py-2.5 text-slate-650 hover:text-slate-800 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer">
                                        Saya Punya Akun
                                    </button>
                                </div>

                                <!-- Manual Registration Form (Guest Mode) -->
                                <div id="manual-reg-form" class="space-y-4">
                                    <form action="{{ route('public.events.register', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">Nama Lengkap</label>
                                                <input type="text" name="guest_name" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Cth: Ahmad Fauzi" required>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">Email</label>
                                                <input type="email" name="guest_email" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Cth: ahmad@domain.com" required>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">No. WhatsApp / Telepon</label>
                                                <input type="text" name="guest_phone" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Cth: 08123456789" required>
                                            </div>
                                        </div>

                                        <!-- Custom Schema Fields -->
                                        @if($event->form_schema && count($event->form_schema) > 0)
                                            <div class="pt-3 border-t border-slate-100 space-y-3">
                                                <span class="block text-[9px] font-black uppercase text-emerald-800 tracking-wider">Atribut Kuesioner Acara:</span>
                                                @foreach($event->form_schema as $idx => $field)
                                                    @php 
                                                        $fieldName = 'field_' . $idx; 
                                                        $isRequired = isset($field['required']) && $field['required'];
                                                    @endphp
                                                    <div>
                                                        <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">
                                                            {{ $field['name'] }} @if($isRequired)<span class="text-rose-500">*</span>@endif
                                                        </label>
                                                        @if($field['type'] === 'file')
                                                            <input type="file" name="{{ $fieldName }}" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 outline-none focus:bg-white focus:border-emerald-600 transition-all" @if($isRequired) required @endif>
                                                        @elseif($field['type'] === 'number')
                                                            <input type="number" name="{{ $fieldName }}" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Masukkan angka..." @if($isRequired) required @endif>
                                                        @else
                                                            <input type="text" name="{{ $fieldName }}" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Jawaban Anda..." @if($isRequired) required @endif>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <button type="submit" class="w-full py-3.5 bg-emerald-650 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-xs hover:shadow-sm transition-all text-center cursor-pointer">
                                            Daftar Event &rarr;
                                        </button>
                                    </form>
                                </div>

                                <!-- Fast Registration Form (Account verification check) -->
                                <div id="fast-reg-form" class="space-y-4 hidden">
                                    <form action="{{ route('public.events.register_fast', $event->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <p class="text-[10px] text-slate-500 leading-relaxed font-semibold">Tarik data keanggotaan Anda otomatis menggunakan Nomor Induk (NI) & Nomor HP yang terdaftar di platform kami:</p>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">Nomor Induk Anggota (NI)</label>
                                                <input type="text" name="nomor_induk" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Cth: IHI-REG-XXXXXX" required>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">Nomor Handphone Terdaftar</label>
                                                <input type="text" name="nomor_hp" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Cth: 08123456789" required>
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition-all shadow-xs cursor-pointer">
                                            ⚡ Cari Akun & Daftar Instan
                                        </button>
                                    </form>
                                </div>
                            @else
                                <!-- Registered Logged-in Auth Member -->
                                <form action="{{ route('public.events.register', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-800 font-bold flex items-center gap-2">
                                        <span>👤 Mendaftar sebagai: <strong>{{ auth()->user()->name }}</strong></span>
                                    </div>

                                    <!-- Custom Schema Fields -->
                                    @if($event->form_schema && count($event->form_schema) > 0)
                                        <div class="pt-3 border-t border-slate-100 space-y-3">
                                            <span class="block text-[9px] font-black uppercase text-emerald-800 tracking-wider">Atribut Kuesioner Acara:</span>
                                            @foreach($event->form_schema as $idx => $field)
                                                @php 
                                                    $fieldName = 'field_' . $idx; 
                                                    $isRequired = isset($field['required']) && $field['required'];
                                                @endphp
                                                <div>
                                                    <label class="block text-[10px] font-extrabold uppercase text-slate-700 tracking-wider mb-1.5">
                                                        {{ $field['name'] }} @if($isRequired)<span class="text-rose-500">*</span>@endif
                                                    </label>
                                                    @if($field['type'] === 'file')
                                                        <input type="file" name="{{ $fieldName }}" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-650 outline-none focus:bg-white focus:border-emerald-600 transition-all" @if($isRequired) required @endif>
                                                    @elseif($field['type'] === 'number')
                                                        <input type="number" name="{{ $fieldName }}" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Masukkan angka..." @if($isRequired) required @endif>
                                                    @else
                                                        <input type="text" name="{{ $fieldName }}" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-all" placeholder="Jawaban Anda..." @if($isRequired) required @endif>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <button type="submit" class="w-full py-3.5 bg-emerald-650 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-[11px] uppercase tracking-wider rounded-xl shadow-xs hover:shadow-sm transition-all text-center cursor-pointer">
                                        Daftar Event &rarr;
                                    </button>
                                </form>
                            @endif
                        @endif

                    @endif

                </div>

            </div>

        </div>

        <div class="text-center text-[10px] text-slate-400">
            &copy; 2026 Institut Hijau Indonesia. All Rights Reserved.
        </div>

    </div>

    <!-- Toggle scripts for fast registration modes -->
    @if(!auth()->check())
        <script>
            function switchRegMode(mode) {
                const manualBtn = document.getElementById('tab-manual-btn');
                const fastBtn = document.getElementById('tab-fast-btn');
                const manualForm = document.getElementById('manual-reg-form');
                const fastForm = document.getElementById('fast-reg-form');

                if (mode === 'manual') {
                    manualBtn.className = "py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer shadow-xs";
                    fastBtn.className = "py-2.5 text-slate-650 hover:text-slate-800 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer";
                    manualForm.classList.remove('hidden');
                    fastForm.classList.add('hidden');
                } else {
                    fastBtn.className = "py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer shadow-xs";
                    manualBtn.className = "py-2.5 text-slate-650 hover:text-slate-800 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition-all cursor-pointer";
                    manualForm.classList.add('hidden');
                    fastForm.classList.remove('hidden');
                }
            }
        </script>
    @endif
</body>
</html>
