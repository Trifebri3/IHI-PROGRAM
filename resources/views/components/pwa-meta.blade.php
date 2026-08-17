<!-- PWA Manifest & Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#059669">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="IHI Portal">
<link rel="apple-touch-icon" href="/images/logo.webp">

<!-- Register Service Worker -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((reg) => {
                    console.log('Service Worker registered successfully:', reg.scope);
                })
                .catch((err) => {
                    console.error('Service Worker registration failed:', err);
                });
        });
    }
</script>

{{-- Impersonation Alert Banner --}}
@if(session()->has('impersonator_id'))
    <div style="position: fixed; top: 0; left: 0; right: 0; height: 40px; background-color: #f59e0b; color: #0f172a; z-index: 999999; display: flex; align-items: center; justify-content: center; font-family: system-ui, -apple-system, sans-serif; font-size: 13px; font-weight: bold; border-bottom: 2px solid #d97706; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <span>Anda sedang masuk sebagai <span style="text-decoration: underline;">{{ Auth::user()->name }}</span> (Impersonasi)</span>
        <form method="POST" action="{{ route('impersonate.stop') }}" style="margin-left: 20px; display: inline;">
            @csrf
            <button type="submit" style="background-color: #0f172a; color: #ffffff; border: none; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; transition: background-color 0.2s;">
                Kembali ke Admin
            </button>
        </form>
    </div>
    <style>
        /* Push body down when impersonation banner is active */
        body {
            padding-top: 40px !important;
        }
    </style>
@endif
