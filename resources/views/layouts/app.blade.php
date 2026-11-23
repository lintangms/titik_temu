<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-fullscreen">
        <!-- Mobile Header Bar -->
        <div class="mobile-nav-bar">
            <button class="btn-hamburger" onclick="toggleSidebar()" aria-label="Buka Menu">&#9776;</button>
            <span class="mobile-title">TitikTemu</span>
            <div style="width: 32px;"></div>
        </div>

        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>TitikTemu</h2>
                <p>Sistem Informasi Barang Hilang</p>
            </div>

            <div class="sidebar-menu">
                @if(Auth::check() && Auth::user()->isSatpam())
                    <a href="{{ route('dashboard') }}" class="btn-sidebar {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('barang.index') }}" class="btn-sidebar {{ request()->routeIs('barang.*') ? 'active' : '' }}">Data Barang</a>
                    <a href="{{ route('kategori.index') }}" class="btn-sidebar {{ request()->routeIs('kategori.*') ? 'active' : '' }}">Kategori Barang</a>
                    <a href="{{ route('lokasi.index') }}" class="btn-sidebar {{ request()->routeIs('lokasi.*') ? 'active' : '' }}">Lokasi</a>
                    <a href="{{ route('pengumuman.index') }}" class="btn-sidebar {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">Pengumuman</a>
                    <a href="{{ route('serah-terima.index') }}" class="btn-sidebar {{ request()->routeIs('serah-terima.*') ? 'active' : '' }}">Serah Terima</a>
                @else
                    <a href="{{ route('mahasiswa.index') }}" class="btn-sidebar {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">Portal Barang Hilang</a>
                @endif
                <a href="{{ route('profil.index') }}" class="btn-sidebar {{ request()->routeIs('profil.*') ? 'active' : '' }}">Profil Saya</a>
            </div>

            <div class="sidebar-footer">
                <button class="btn-sidebar-logout" onclick="konfirmasiLogout()">Logout</button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </aside>

        <!-- Main Content View Area -->
        <div class="content-area">
            <header class="top-bar">
                <h1>@yield('title', 'Dashboard')</h1>
                @if(Auth::check())
                    <div class="top-bar-user">
                        Login sebagai: <strong>{{ Auth::user()->nama }}</strong> ({{ Auth::user()->role }})
                    </div>
                @endif
            </header>

            <main class="main-view">
                <!-- Flash Alerts -->
                @if(session('sukses'))
                    <div class="alert alert-success">
                        {{ session('sukses') }}
                    </div>
                @endif

                @if(session('pesan') === 'simpan_sukses')
                    <div class="alert alert-success">
                        Data berhasil disimpan ke dalam sistem.
                    </div>
                @elseif(session('pesan') === 'hapus_sukses')
                    <div class="alert alert-success">
                        Data berhasil dihapus dari sistem.
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        function toggleSidebar() {
            var sb = document.getElementById('sidebar');
            if (sb) sb.classList.toggle('active');
        }

        function konfirmasiLogout() {
            if (confirm('Apakah Anda yakin ingin keluar dari sistem TitikTemu?')) {
                document.getElementById('logout-form').submit();
            }
        }

        function bukaModal(id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }

        function tutupModal(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function filterTabel(inputId, bodyId) {
            var filter = document.getElementById(inputId).value.toLowerCase();
            var rows = document.getElementById(bodyId).getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                var text = rows[i].textContent || rows[i].innerText;
                rows[i].style.display = text.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
