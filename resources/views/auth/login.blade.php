<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="login-card">
            <div class="header">
                <h2>TitikTemu</h2>
                <p>Sistem Informasi Pencarian dan Penemuan Barang Hilang</p>
            </div>

            @if(session('sukses'))
                <div class="alert alert-success">
                    {{ session('sukses') }}
                </div>
            @endif

            @if($errors->has('login'))
                <div class="alert alert-danger">
                    {{ $errors->first('login') }}
                </div>
            @elseif($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username" value="{{ old('username') }}">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-size: 13px; color: var(--slate-600);">
                    <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" style="margin: 0; cursor: pointer; font-weight: 500;">Ingat Saya (30 Hari)</label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Masuk ke Sistem</button>
            </form>

            <div class="auth-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar Akun Baru</a>
            </div>

            <div class="info-akun">
                <p class="info-title">Akun Demo Pengujian:</p>
                <p><strong>Satpam (Petugas):</strong> satpam / satpam123</p>
                <p><strong>Mahasiswa:</strong> mahasiswa / mhs123</p>
            </div>
        </div>
    </div>
</body>
</html>
