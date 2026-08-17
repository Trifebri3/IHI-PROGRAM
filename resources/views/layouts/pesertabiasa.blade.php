<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peserta - @yield('title')</title>
</head>
<body>
    <nav style="display:flex;justify-content:space-between;align-items:center;padding:15px 20px;background:#f8fafc;border-bottom:1px solid #e5e7eb;">
        <div>
            <strong>Portal Peserta</strong>
        </div>

        <div style="display:flex;align-items:center;gap:15px;">
            <span>
                Halo, <strong>{{ Auth::user()->name }}</strong>
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    style="padding:8px 16px;background:#dc2626;color:white;border:none;border-radius:6px;cursor:pointer;"
                >
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main style="padding:20px;">
        @yield('content')
    </main>
</body>
</html>
