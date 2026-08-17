<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi & Evaluasi - {{ $event->title }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[#0c1a14] via-[#132c23] to-[#0a1510] min-h-screen text-slate-100 font-['Plus_Jakarta_Sans'] antialiased py-10 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">
    
    <div class="max-w-md mx-auto w-full space-y-6">
        
        <!-- Brand Header -->
        <div class="text-center">
            <span class="text-xs font-black tracking-widest uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-4 py-2 rounded-full">
                Institut Hijau Indonesia
            </span>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-2xl text-emerald-300 text-xs font-bold text-center">
                🎉 {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="p-4 bg-rose-500/20 border border-rose-500/50 rounded-2xl text-rose-300 text-xs font-bold text-left space-y-1">
                @if(session('error'))
                    <div>❌ {{ session('error') }}</div>
                @endif
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white text-slate-800 p-6 sm:p-8 rounded-[2.5rem] rounded-tr-[5rem] rounded-bl-[5rem] shadow-2xl relative border border-slate-100 text-left space-y-6">
            
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[8px] font-black uppercase text-slate-400 tracking-wider">Lembar Kehadiran & Penilaian</span>
                <h2 class="text-base font-black text-emerald-800 leading-tight uppercase tracking-tight line-clamp-1 mt-0.5">{{ $event->title }}</h2>
            </div>

            @if($event->attendance_method !== 'form')
                <!-- Warning when Form Attendance is not active -->
                <div class="py-4 text-center space-y-4">
                    <span class="text-4xl block">⚠️</span>
                    <h3 class="text-sm font-extrabold text-amber-800 uppercase tracking-wider">Absensi Mandiri Ditutup</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-semibold">
                        Sesi absensi mandiri melalui formulir tidak diaktifkan untuk kegiatan ini.
                        @if($event->attendance_method === 'scan')
                            <br>Silakan tunjukkan QR Code tiket pendaftaran Anda kepada petugas panitia di lokasi acara.
                        @elseif($event->attendance_method === 'token')
                            <br>Silakan masukkan Token Presensi melalui Ruang Utama Dashboard Peserta Anda.
                        @endif
                    </p>
                    <a href="/" class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold uppercase text-[9px] tracking-wider text-center transition">
                        Kembali ke Beranda
                    </a>
                </div>
            @else
                <!-- STEP 1: TICKET VERIFICATION FORM -->
                @if($event->attendance_require_ticket)
                    <div id="step-verification" class="space-y-4 {{ session('claimed') ? 'hidden' : '' }}">
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">Harap masukkan Nomor Tiket yang dikirimkan ke email Anda untuk memvalidasi identitas:</p>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-slate-400 mb-1">Nomor Tiket Pendaftaran</label>
                                <input type="text" id="verify_ticket_input" placeholder="Cth: IHI-EVT-REG-XXXXXX" class="w-full p-3 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none uppercase" required>
                            </div>
                            <button type="button" onclick="verifyTicket()" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md shadow-emerald-100 cursor-pointer">
                                🔍 Verifikasi Tiket Kehadiran
                            </button>
                            <a href="/" class="block w-full py-2 bg-slate-100 hover:bg-slate-250 text-slate-500 rounded-xl font-bold uppercase text-[9px] tracking-wider text-center transition">
                                Batal
                            </a>
                        </div>
                    </div>
                @endif

                <!-- STEP 2: FILL ATTENDANCE FORM -->
                <div id="step-form" class="{{ ($event->attendance_require_ticket || session('claimed')) ? 'hidden' : '' }} space-y-4">
                    <form action="{{ route('public.events.attendance.submit', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="ticket_number" id="form_ticket_number">

                        <!-- Attendee Profile Info / Guest Form Fields -->
                        @if($event->attendance_require_ticket)
                            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-slate-700 space-y-1">
                                <span class="block text-[8px] font-black uppercase text-slate-400">Peserta Terdaftar</span>
                                <div class="font-extrabold text-slate-800 text-sm" id="form_attendee_name"></div>
                                <div class="text-slate-500 text-[10px]" id="form_attendee_email"></div>
                            </div>
                        @else
                            @auth
                                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-slate-700 space-y-1">
                                    <span class="block text-[8px] font-black uppercase text-slate-400">Akun Teridentifikasi</span>
                                    <div class="font-extrabold text-slate-800 text-sm">{{ auth()->user()->name }}</div>
                                    <div class="text-slate-500 text-[10px]">{{ auth()->user()->email }}</div>
                                </div>
                            @else
                                <div class="space-y-3 p-3 bg-slate-50 border border-slate-100 rounded-2xl">
                                    <span class="block text-[9px] font-black uppercase text-slate-450 tracking-wider mb-1">Biodata Kehadiran Peserta:</span>
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-500 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                        <input type="text" name="guest_name" value="{{ old('guest_name') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 outline-none bg-white" placeholder="Nama lengkap Anda..." required>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-500 mb-1">Email Aktif <span class="text-rose-500">*</span></label>
                                        <input type="email" name="guest_email" value="{{ old('guest_email') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 outline-none bg-white" placeholder="alamat@email.com" required>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-500 mb-1">No. HP / WhatsApp <span class="text-rose-500">*</span></label>
                                        <input type="tel" name="guest_phone" value="{{ old('guest_phone') }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 outline-none bg-white" placeholder="Cth: 08123456789" required>
                                    </div>
                                </div>
                            @endauth
                        @endif

                        <!-- Custom Attendance Schema Fields -->
                        @if($event->attendance_form_schema && count($event->attendance_form_schema) > 0)
                            <div class="pt-2 border-t border-slate-100 space-y-3">
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-wider">Kuesioner Evaluasi & Absensi:</span>
                                
                                @foreach($event->attendance_form_schema as $idx => $field)
                                    @php 
                                        $fieldName = 'field_' . $idx; 
                                        $isRequired = isset($field['required']) && $field['required'];
                                    @endphp
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-500 mb-1">
                                            {{ $field['name'] }} @if($isRequired)<span class="text-rose-500">*</span>@endif
                                        </label>
                                        
                                        @if($field['type'] === 'file')
                                            <input type="file" name="{{ $fieldName }}" class="w-full p-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-500" @if($isRequired) required @endif>
                                        @elseif($field['type'] === 'number')
                                            <input type="number" name="{{ $fieldName }}" value="{{ old($fieldName) }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 outline-none" placeholder="Masukkan angka..." @if($isRequired) required @endif>
                                        @else
                                            <input type="text" name="{{ $fieldName }}" value="{{ old($fieldName) }}" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 outline-none" placeholder="Jawaban Anda..." @if($isRequired) required @endif>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md shadow-emerald-100 cursor-pointer">
                            💾 Kirim Absen & Evaluasi
                        </button>
                    </form>
                </div>

                <!-- STEP 3: CLAIM CERTIFICATE / SUCCESS COMPLETED -->
                <div id="step-completed" class="space-y-4 {{ session('claimed') ? '' : 'hidden' }}">
                    <div class="text-center space-y-2">
                        <span class="text-3xl">🎓</span>
                        <h3 class="text-base font-extrabold text-slate-800">Absensi Terkonfirmasi!</h3>
                        <p class="text-xs text-slate-450 leading-relaxed font-semibold">Terima kasih atas partisipasi dan evaluasi Anda dalam kegiatan ini.</p>
                    </div>

                    @if($event->certificate_template_path)
                        <!-- Client-side HTML5 Canvas Generator -->
                        <button type="button" onclick="downloadCertificate()" class="block w-full py-3 bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md cursor-pointer">
                            📥 Download Sertifikat PNG
                        </button>
                        <canvas id="certificateCanvas" style="display: none;"></canvas>
                    @endif

                    @if($event->certificate_link)
                        <a href="{{ $event->certificate_link }}" target="_blank" class="block w-full py-3 bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider text-center transition shadow-md cursor-pointer">
                            📥 Klaim Piagam Digital Eksternal &rarr;
                        </a>
                    @endif

                    <a href="/" class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-650 rounded-xl font-bold uppercase text-[9px] tracking-wider text-center transition">
                        Kembali ke Beranda
                    </a>
                </div>
            @endif

        </div>

    </div>

    <div class="text-center text-[10px] text-slate-500 pt-8">
        &copy; 2026 Institut Hijau Indonesia. All Rights Reserved.
    </div>

    <!-- Script verification & certificate overlays -->
    <script>
        let attendeeName = "";

        function verifyTicket() {
            const ticket = document.getElementById('verify_ticket_input').value.trim();
            if (!ticket) {
                alert('Silakan masukkan nomor tiket.');
                return;
            }

            fetch("{{ route('public.events.attendance.verify', $event->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ticket_number: ticket })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    attendeeName = data.name;
                    localStorage.setItem('cached_attendee_name', data.name);
                    
                    if (data.already_attended) {
                        // Already checked in previously - show certificate claim directly
                        document.getElementById('step-verification').classList.add('hidden');
                        document.getElementById('step-completed').classList.remove('hidden');
                    } else {
                        // Not checked in yet - show evaluation inputs
                        document.getElementById('step-verification').classList.add('hidden');
                        document.getElementById('form_ticket_number').value = data.ticket_number;
                        document.getElementById('form_attendee_name').innerText = data.name;
                        document.getElementById('form_attendee_email').innerText = data.email;
                        document.getElementById('step-form').classList.remove('hidden');
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menghubungkan ke server.');
            });
        }

        function downloadCertificate() {
            const name = attendeeName || localStorage.getItem('cached_attendee_name') || "Peserta Event";
            const canvas = document.getElementById('certificateCanvas');
            const ctx = canvas.getContext('2d');
            
            const img = new Image();
            img.crossOrigin = "anonymous";
            // Ensure proper storage URL resolution
            img.src = "{{ $event->certificate_template_path ? asset('storage/' . $event->certificate_template_path) : '' }}";
            
            img.onload = function() {
                canvas.width = img.width;
                canvas.height = img.height;
                
                // Draw base certificate
                ctx.drawImage(img, 0, 0);
                
                // Overlay text settings
                ctx.font = "bold " + Math.round(img.width * 0.04) + "px 'Plus Jakarta Sans', sans-serif";
                ctx.fillStyle = "#111827"; // Slate 900 dark contrast
                ctx.textAlign = "center";
                
                // Place name in center (around 52% height)
                ctx.fillText(name, img.width / 2, img.height * 0.52);
                
                // Trigger download
                const link = document.createElement('a');
                link.download = 'Sertifikat_Kehadiran_' + name.replace(/\s+/g, '_') + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            };
        }
    </script>

</body>
</html>
