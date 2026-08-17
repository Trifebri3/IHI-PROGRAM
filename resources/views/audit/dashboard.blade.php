<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100">

    <!-- Navbar -->
    <nav class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">
                Audit & Review Panel
            </h1>
        </div>

        <div class="flex items-center gap-4">
            <span>
                {{ Auth::user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700"
                >
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r p-6">

            <h2 class="font-bold text-gray-700 mb-4">
                Reviewer Menu
            </h2>

            <ul class="space-y-3">

                <li>
                    <a href="{{ route('dashboard') }}">
                        📊 Dashboard Review
                    </a>
                </li>

                <li>
                    <a href="#">
                        📄 Daftar Pengajuan
                    </a>
                </li>

                <li>
                    <a href="#">
                        ✅ Verifikasi Dokumen
                    </a>
                </li>

                <li>
                    <a href="#">
                        🔍 Audit Program
                    </a>
                </li>

                <li>
                    <a href="#">
                        📝 Catatan Review
                    </a>
                </li>

                <li>
                    <a href="#">
                        📈 Laporan Monitoring
                    </a>
                </li>

                <li>
                    <a href="#">
                        📥 Export Hasil Audit
                    </a>
                </li>

            </ul>

        </aside>

        <!-- Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>

</body>
</html>
