<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'data_helper.php';

$errorMessage = '';
$old = [
    'nama' => '',
    'role' => 'Mahasiswa',
    'identitas' => '',
    'kontak' => '',
    'username' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $role = $_POST['role'] ?? 'Mahasiswa';
    $identitas = trim($_POST['identitas'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi'] ?? '');

    $old = compact('nama', 'role', 'identitas', 'kontak', 'username');

    // 1. Validasi Kelengkapan Input
    if (empty($nama) || empty($identitas) || empty($kontak) || empty($username) || empty($password)) {
        $errorMessage = 'Semua kolom wajib diisi dengan lengkap!';
    }
    // 2. Validasi Format Nama Lengkap
    elseif (mb_strlen($nama) < 3) {
        $errorMessage = 'Nama lengkap minimal harus terdiri dari 3 karakter.';
    }
    // 3. Validasi Format Username (hanya huruf, angka, underscore, 3-30 karakter)
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errorMessage = 'Username hanya boleh berupa huruf, angka, atau underscore (_) dengan panjang 3-30 karakter.';
    }
    // 4. Validasi Kontak / Nomor Telepon (minimal 9 digit angka)
    elseif (!preg_match('/^[0-9\-\+\s]{9,20}$/', $kontak)) {
        $errorMessage = 'Nomor HP/WhatsApp tidak valid. Masukkan nomor telepon yang valid (minimal 9 digit).';
    }
    // 5. Validasi Panjang Password
    elseif (strlen($password) < 6) {
        $errorMessage = 'Password minimal harus terdiri dari 6 karakter demi keamanan akun Anda.';
    }
    // 6. Validasi Konfirmasi Password
    elseif ($password !== $konfirmasi) {
        $errorMessage = 'Konfirmasi password tidak cocok dengan password yang dimasukkan.';
    }
    // 7. Validasi Duplikasi Username
    else {
        $existing = cariUserByUsername($username);
        if ($existing) {
            $errorMessage = 'Username "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" sudah digunakan. Silakan pilih username yang lain.';
        } else {
            // Data valid dan unik, simpan ke sistem (Database & JSON)
            $newUser = [
                'username' => $username,
                'password' => $password,
                'nama' => $nama,
                'role' => in_array($role, ['Mahasiswa', 'Satpam']) ? $role : 'Mahasiswa',
                'identitas' => $identitas,
                'kontak' => $kontak
            ];

            simpanUserBaru($newUser);

            // Arahkan ke halaman login dengan pesan sukses
            header("Location: login.php?pesan=registrasi_sukses");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="login-card" style="max-width: 440px;">
            <div class="header">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun TitikTemu untuk mengakses layanan pelaporan barang hilang</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="reg-nama">Nama Lengkap</label>
                    <input type="text" id="reg-nama" name="nama" placeholder="Contoh: Lintang Arya Nugraha" required value="<?= htmlspecialchars($old['nama'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-role">Peran (Role)</label>
                        <select id="reg-role" name="role" required>
                            <option value="Mahasiswa" <?= $old['role'] === 'Mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                            <option value="Satpam" <?= $old['role'] === 'Satpam' ? 'selected' : '' ?>>Satpam / Petugas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reg-identitas">No. Identitas (NIM / NIP)</label>
                        <input type="text" id="reg-identitas" name="identitas" placeholder="NIM / NIP" required value="<?= htmlspecialchars($old['identitas'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-kontak">Nomor HP / WhatsApp</label>
                    <input type="tel" id="reg-kontak" name="kontak" placeholder="Contoh: 081234567890" required value="<?= htmlspecialchars($old['kontak'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" placeholder="Huruf, angka, underscore (min. 3 kar.)" required autocomplete="username" value="<?= htmlspecialchars($old['username'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" id="reg-password" name="password" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="reg-konfirmasi">Ulangi Password</label>
                        <input type="password" id="reg-konfirmasi" name="konfirmasi" placeholder="Ketik ulang password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 8px;">Daftar Sekarang</button>
            </form>

            <div class="auth-link">
                Sudah memiliki akun? <a href="login.php">Masuk ke Halaman Login</a>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
