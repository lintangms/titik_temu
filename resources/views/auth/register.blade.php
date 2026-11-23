<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="login-card" style="max-width: 440px;">
            <div class="header">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun TitikTemu untuk mengakses layanan pelaporan barang hilang</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="reg-nama">Nama Lengkap</label>
                    <input type="text" id="reg-nama" name="nama" placeholder="Contoh: Lintang Arya Nugraha" required value="{{ old('nama') }}">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-identitas">No. Identitas (NIM)</label>
                        <input type="text" id="reg-identitas" name="identitas" placeholder="NIM: 24050123001" value="{{ old('identitas') }}">
                    </div>

                    <div class="form-group">
                        <label for="reg-kontak">Nomor HP / WhatsApp</label>
                        <input type="tel" id="reg-kontak" name="kontak" placeholder="08xxxxxxxxxx" required value="{{ old('kontak') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-username">Username Baru</label>
                    <input type="text" id="reg-username" name="username" placeholder="Minimal 4 karakter" required autocomplete="username" value="{{ old('username') }}">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" id="reg-password" name="password" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="reg-konfirmasi">Ulangi Password</label>
                        <input type="password" id="reg-konfirmasi" name="password_confirmation" placeholder="Ulangi password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Daftar Sekarang</button>
            </form>

            <div class="auth-link">
                Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
