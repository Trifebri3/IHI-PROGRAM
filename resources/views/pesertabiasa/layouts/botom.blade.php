<style>
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        z-index: 50;
        width: 100%;
        height: 64px; /* h-16 */
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid #d1fae5; /* border-emerald-100 */
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
        padding-bottom: env(safe-area-inset-bottom); /* pb-safe untuk iPhone modern */
    }

    .nav-grid-container {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr)); /* grid-cols-5 */
        height: 100%;
        max-width: 512px; /* max-w-lg */
        margin-left: auto;
        margin-right: auto;
        font-weight: 500;
    }

    .nav-item-link, .nav-item-button {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding-left: 8px;
        padding-right: 8px;
        text-decoration: none;
        background: none;
        border: none;
        width: 100%;
        height: 100%;
        cursor: pointer;
        font-family: inherit;
    }

    .nav-item-link span, .nav-item-button span {
        font-size: 10px;
        letter-spacing: 0.025em;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        margin-top: 2px;
    }

    /* Warna Aktif & Default (Menggantikan text-emerald-600 & text-gray-400) */
    .nav-active { color: #059669 !important; }
    .nav-inactive { color: #9ca3af !important; }
    .nav-logout { color: #f43f5e !important; }

    /* Responsivitas: Otomatis sembunyi di layar desktop (md:hidden) */
    @media (min-width: 768px) {
        .mobile-bottom-nav {
            display: none !important;
        }
    }
</style>

<div class="mobile-bottom-nav">
    <div class="nav-grid-container">

        <a href="{{ route('dashboard') }}" 
           class="nav-item-link {{ request()->routeIs('dashboard') ? 'nav-active' : 'nav-inactive' }}">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
            </svg>
            <span>Beranda</span>
        </a>

        <a href="{{ route('programs.catalog') }}" 
           class="nav-item-link {{ request()->routeIs('programs.catalog') || request()->routeIs('program.apply') ? 'nav-active' : 'nav-inactive' }}">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
            <span>Program</span>
        </a>

        <a href="{{ route('peserta.piagam.index') }}" 
           class="nav-item-link {{ request()->routeIs('peserta.piagam.*') ? 'nav-active' : 'nav-inactive' }}">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <span>Piagam</span>
        </a>

        <a href="{{ route('events.catalog') }}" 
           class="nav-item-link {{ request()->routeIs('events.catalog') ? 'nav-active' : 'nav-inactive' }}">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            <span>Event</span>
        </a>

        <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="nav-item-button nav-inactive">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span>Menu</span>
        </button>

    </div>
</div>