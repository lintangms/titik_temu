<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'data_helper.php';

// Cek Remember Me jika ada
if (!isset($_SESSION['user']) && isset($_COOKIE['titiktemu_session'])) {
    $cookieData = json_decode($_COOKIE['titiktemu_session'], true);
    if ($cookieData && isset($cookieData['username'])) {
        $_SESSION['user'] = $cookieData;
    }
}

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'Satpam') {
        header('Location: dashboard.php');
    } else {
        header('Location: mahasiswa.php');
    }
    exit();
}

$errorMessage = '';
$successMessage = '';
$inputUsername = '';

// Tangkap notifikasi pesan dari redirect
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'logout_sukses') {
        $successMessage = 'Anda telah berhasil logout dari sistem.';
    } elseif ($_GET['pesan'] === 'belum_login') {
        $errorMessage = 'Akses ditolak! Silakan login terlebih dahulu untuk mengakses halaman.';
    } elseif ($_GET['pesan'] === 'registrasi_sukses') {
        $successMessage = 'Registrasi berhasil! Silakan login dengan akun yang baru dibuat.';
    } elseif ($_GET['pesan'] === 'akun_dihapus') {
        $successMessage = 'Akun Anda telah berhasil dihapus dari sistem.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);
    $inputUsername = $username;

    if (empty($username) || empty($password)) {
        $errorMessage = 'Username dan password wajib diisi!';
    } else {
        // Amankan dari SQL Injection menggunakan PDO Prepared Statement
        $foundUser = cariUserByUsername($username);

        // Verifikasi password (mendukung hash bcrypt & plain text untuk kompatibilitas data awal)
        if ($foundUser && (password_verify($password, $foundUser['password']) || $foundUser['password'] === $password)) {
            $_SESSION['user'] = $foundUser;

            if ($remember) {
                setcookie('titiktemu_session', json_encode($foundUser), time() + (86400 * 30), '/');
            } else {
                setcookie('titiktemu_session', json_encode($foundUser), 0, '/');
            }

            $target = ($foundUser['role'] === 'Satpam') ? 'dashboard.php' : 'mahasiswa.php';
            header("Location: $target");
            exit();
        } else {
            $errorMessage = 'Login Gagal! Username atau password yang Anda masukkan salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="login-card">
            <div class="header">
                <h2>TitikTemu</h2>
                <p>Sistem Informasi Pencarian dan Penemuan Barang Hilang</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username" value="<?= htmlspecialchars($inputUsername, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-size: 13px; color: var(--slate-600);">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember" style="margin: 0; cursor: pointer; font-weight: 500;">Ingat Saya (30 Hari)</label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Masuk ke Sistem</button>
            </form>

            <div class="auth-link">
                Belum punya akun? <a href="register.php">Daftar Akun Baru</a>
            </div>

            <div class="info-akun">
                <p class="info-title">Akun Demo Pengujian:</p>
                <p><strong>Satpam (Petugas):</strong> satpam / satpam123</p>
                <p><strong>Mahasiswa:</strong> mahasiswa / mhs123</p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
