@extends('superadmin.layouts.app')

@section('title', 'Event QR Scanner')

@section('content')
<div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md uppercase tracking-wide font-mono">Realtime Check-in Station</span>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mt-1.5">Kamera Absensi & Scan Tiket</h1>
            <p class="text-xs text-slate-400 mt-1">
                Arahkan QR Code tiket pendaftaran peserta ke kamera untuk melakukan absensi otomatis.
            </p>
        </div>
        <div>
            <a href="{{ route('superadmin.events.dashboard', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-800 text-white hover:bg-black text-xs font-bold rounded-xl transition border shadow-sm">
                &larr; Kembali ke Dashboard Event
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Scanner Camera view -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-ping"></span>
                    Kamera Aktif
                </h3>
                <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-500 font-bold uppercase tracking-wider">Status: Ready</span>
            </div>

            <!-- HTML5 QR Reader Element -->
            <div class="relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 shadow-inner flex flex-col justify-center items-center p-4">
                <div id="interactive-reader" class="w-full max-w-md mx-auto aspect-square overflow-hidden rounded-xl bg-black border border-slate-800"></div>
                
                <!-- Laser Scanning animation overlay -->
                <div id="scanning-laser" class="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-emerald-500 to-transparent shadow-lg shadow-emerald-500/50 animate-bounce" style="top: 25%; pointer-events: none;"></div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 items-center justify-between text-xs pt-2">
                <div class="text-slate-500 font-medium">
                    Jika kamera tidak terbuka, silakan izinkan akses kamera pada peramban Anda.
                </div>
                <button onclick="restartScanner()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition border text-xs">
                    🔄 Restart Scanner
                </button>
            </div>
        </div>

        <!-- Scanned Log and Alert -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">📋 Hasil Pindaian Terakhir</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Log absensi check-in langsung pada halaman ini.</p>
                </div>

                <!-- Alert Overlay for Dynamic Feedback -->
                <div id="scan-feedback" class="hidden p-4 rounded-xl border flex flex-col items-center justify-center text-center space-y-1.5 transition-all duration-300">
                    <span id="feedback-icon" class="text-2xl"></span>
                    <span id="feedback-status" class="text-xs font-bold uppercase tracking-wider"></span>
                    <p id="feedback-message" class="text-sm font-bold text-slate-700"></p>
                </div>

                <!-- Scanned list log -->
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1" id="scanned-logs">
                    <p class="text-xs text-slate-450 italic text-center py-8" id="log-empty-state">
                        Belum ada tiket yang dipindai saat sesi ini terbuka.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider text-center">
                    🎵 Efek Suara Presensi Aktif (Web Audio API)
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load html5-qrcode Library from CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;
    let lastScannedCode = null;
    let scanTimeout = null;
    
    // Initialize Web Audio Context for Beep Sound
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    let audioCtx = null;

    function playBeep(success) {
        try {
            if (!audioCtx) {
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            if (success) {
                // Double chime (success)
                osc.frequency.value = 880; // A5
                gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.005, audioCtx.currentTime + 0.1);
                osc.stop(audioCtx.currentTime + 0.1);
                
                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.frequency.value = 1109; // C#6
                    gain2.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    osc2.start();
                    gain2.gain.exponentialRampToValueAtTime(0.005, audioCtx.currentTime + 0.15);
                    osc2.stop(audioCtx.currentTime + 0.15);
                }, 80);
            } else {
                // Low buzzer (error / warning)
                osc.type = 'sawtooth';
                osc.frequency.value = 160;
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.005, audioCtx.currentTime + 0.35);
                osc.stop(audioCtx.currentTime + 0.35);
            }
        } catch(e) {
            console.error('Audio beep failed', e);
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Prevent instant multi-scan of the same code
        if (decodedText === lastScannedCode) {
            return;
        }
        
        lastScannedCode = decodedText;
        clearTimeout(scanTimeout);
        
        // Reset lastScannedCode after 3.5 seconds to allow scanning again
        scanTimeout = setTimeout(() => {
            lastScannedCode = null;
        }, 3500);

        // Standard event checkin url check
        // QR Code could contain: http://localhost:8000/superadmin/events/scan-checkin/IHI-EVT-XXXXXX
        // We extract the ticket code from the URL or text directly
        let ticketNumber = decodedText;
        if (decodedText.includes('/scan-checkin/')) {
            const parts = decodedText.split('/scan-checkin/');
            ticketNumber = parts[parts.length - 1];
        }

        // Clean up ticket input
        ticketNumber = ticketNumber.trim();
        
        // Perform AJAX check-in request
        const verifyUrl = `/superadmin/events/scan-checkin/${ticketNumber}`;
        
        fetch(verifyUrl, {
            method: 'GET', // Using GET which matches the scan-checkin route
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showFeedback(data);
        })
        .catch(err => {
            console.error(err);
            showFeedback({
                success: false,
                message: 'Gagal menghubungi server atau format tiket tidak dikenal.'
            });
        });
    }

    function showFeedback(data) {
        const fbElement = document.getElementById('scan-feedback');
        const iconElement = document.getElementById('feedback-icon');
        const statusElement = document.getElementById('feedback-status');
        const msgElement = document.getElementById('feedback-message');
        const emptyElement = document.getElementById('log-empty-state');
        const logsContainer = document.getElementById('scanned-logs');

        fbElement.classList.remove('hidden');
        
        if (data.success) {
            if (data.already_attended) {
                // Warning / Already checked in
                playBeep(false);
                fbElement.className = "p-4 rounded-xl border bg-amber-50 border-amber-250 text-amber-800 flex flex-col items-center justify-center text-center space-y-1.5";
                iconElement.innerText = "⚠️";
                statusElement.innerText = "Sudah Absen";
                msgElement.innerText = data.message;
            } else {
                // Perfect success
                playBeep(true);
                fbElement.className = "p-4 rounded-xl border bg-emerald-50 border-emerald-250 text-emerald-800 flex flex-col items-center justify-center text-center space-y-1.5";
                iconElement.innerText = "✅";
                statusElement.innerText = "Hadir";
                msgElement.innerText = data.message;
            }
            
            if (emptyElement) {
                emptyElement.remove();
            }

            // Append scan log
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            
            const logItem = document.createElement('div');
            logItem.className = `p-3 rounded-xl border text-xs flex justify-between items-start transition-all duration-300 ${
                data.already_attended ? 'bg-amber-50/50 border-amber-100' : 'bg-emerald-50/50 border-emerald-100'
            }`;
            
            logItem.innerHTML = `
                <div>
                    <div class="font-bold text-slate-800">${data.name}</div>
                    <div class="font-mono text-[10px] text-slate-400 mt-0.5">${data.ticket}</div>
                </div>
                <div class="text-right">
                    <span class="px-1.5 py-0.5 rounded font-black text-[8px] uppercase ${
                        data.already_attended ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'
                    }">${data.already_attended ? 'WARNING' : 'CHECK-IN'}</span>
                    <div class="text-[9px] text-slate-400 mt-1">${timeString}</div>
                </div>
            `;
            
            // Insert log at the beginning of the container
            logsContainer.insertBefore(logItem, logsContainer.firstChild);
        } else {
            // Failure
            playBeep(false);
            fbElement.className = "p-4 rounded-xl border bg-rose-50 border-rose-250 text-rose-800 flex flex-col items-center justify-center text-center space-y-1.5";
            iconElement.innerText = "❌";
            statusElement.innerText = "Gagal";
            msgElement.innerText = data.message;
        }
    }

    function initScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "interactive-reader", 
            { 
                fps: 12, 
                qrbox: function(width, height) {
                    // Make qrbox responsive
                    const min = Math.min(width, height);
                    return {
                        width: Math.round(min * 0.7),
                        height: Math.round(min * 0.7)
                    };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                showZoomSliderIfSupported: true,
            },
            /* verbose= */ false
        );

        html5QrcodeScanner.render(onScanSuccess);
    }

    function restartScanner() {
        initScanner();
        // Reset feedback
        document.getElementById('scan-feedback').classList.add('hidden');
        lastScannedCode = null;
    }

    // Auto-initialize when page loads
    window.addEventListener('DOMContentLoaded', (event) => {
        initScanner();
        
        // Touch or Click interaction to unlock AudioContext on mobile devices
        document.body.addEventListener('click', function() {
            if (!audioCtx) {
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }, { once: true });
    });
</script>
@endsection
