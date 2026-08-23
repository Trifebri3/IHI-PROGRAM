<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Program - @yield('title')</title>
</head>
<body>
    <nav style="display:flex;justify-content:space-between;padding:10px;background:#f5f5f5;">
        <span>Navbar Admin Program</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">
                Logout
            </button>
        </form>
    </nav>

    <aside>
        Sidebar Admin Program (Manajemen Program Saja)
    </aside>

    <main>
        @yield('content')
    </main>
</body>
</html>
