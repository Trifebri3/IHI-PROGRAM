<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - @yield('title')</title>
</head>
<body>

    <nav style="display:flex;justify-content:space-between;align-items:center;padding:15px 20px;background:#111827;color:white;">
        <div>
            <strong>Panel Super Admin</strong>
        </div>

        <div style="display:flex;align-items:center;gap:15px;">
            <span>
                {{ Auth::user()->name }}
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

    <div style="display:flex;min-height:calc(100vh - 60px);">
        <aside style="width:250px;background:#1f2937;color:white;padding:20px;">
            <h3>Menu Super Admin</h3>

            <ul style="list-style:none;padding:0;">
                <li><a href="{{ route('dashboard') }}" style="color:white;">Dashboard</a></li>
                <li><a href="#" style="color:white;">Manajemen User</a></li>
                <li><a href="#" style="color:white;">Manajemen Role</a></li>
                <li><a href="#" style="color:white;">Manajemen Program</a></li>
                <li><a href="#" style="color:white;">Pengaturan Sistem</a></li>
            </ul>
        </aside>

        <main style="flex:1;padding:20px;">
            @yield('content')
        </main>
    </div>

</body>
</html>
