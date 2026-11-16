<?php
include 'auth_check.php';
require_once 'data_helper.php';

// Ambil data profil terbaru dari database / storage
$latestUser = cariUserByUsername($currentUser['username']);
if ($latestUser) {
    $currentUser = $latestUser;
    $_SESSION['user'] = $latestUser;
}

$isSatpam = ($currentUser['role'] === 'Satpam');
$pesanSukses = '';
$pesanError = '';

// 1. PROSES UBAH DATA PROFIL (CRUD - UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ubah_profil') {
    $nama = trim($_POST['nama'] ?? '');
    $identitas = trim($_POST['identitas'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');
    $password_lama = trim($_POST['password_lama'] ?? '');
    $password_baru = trim($_POST['password_baru'] ?? '');
    $konfirmasi_baru = trim($_POST['konfirmasi_baru'] ?? '');

    // Validasi input
    if (empty($nama) || empty($identitas) || empty($kontak)) {
        $pesanError = 'Nama lengkap, nomor identitas, dan kontak wajib diisi.';
    } elseif (mb_strlen($nama) < 3) {
        $pesanError = 'Nama lengkap minimal harus terdiri dari 3 karakter.';
    } elseif (!preg_match('/^[0-9\-\+\s]{9,20}$/', $kontak)) {
        $pesanError = 'Format nomor kontak tidak valid (harus berupa nomor telepon yang valid).';
    } else {
        // Jika pengguna juga ingin mengubah password
        $ubahPassword = false;
        if (!empty($password_baru)) {
            if (empty($password_lama)) {
                $pesanError = 'Untuk mengganti password, Anda wajib memasukkan password lama.';
            } elseif (!password_verify($password_lama, $currentUser['password']) && $currentUser['password'] !== $password_lama) {
                $pesanError = 'Password lama yang Anda masukkan tidak sesuai.';
            } elseif (strlen($password_baru) < 6) {
                $pesanError = 'Password baru minimal harus terdiri dari 6 karakter.';
            } elseif ($password_baru !== $konfirmasi_baru) {
                $pesanError = 'Konfirmasi password baru tidak cocok.';
            } else {
                $ubahPassword = true;
            }
        }

        if (empty($pesanError)) {
            // Simpan perubahan ke Database & JSON
            updateUserProfil($currentUser['username'], $nama, $identitas, $kontak, $ubahPassword ? $password_baru : '');

            // Perbarui sesi aktif
            $currentUser['nama'] = $nama;
            $currentUser['identitas'] = $identitas;
            $currentUser['kontak'] = $kontak;
            if ($ubahPassword) {
                $currentUser['password'] = password_hash($password_baru, PASSWORD_BCRYPT);
            }
            $_SESSION['user'] = $currentUser;

            if (isset($_COOKIE['titiktemu_session'])) {
                setcookie('titiktemu_session', json_encode($currentUser), time() + (86400 * 30), '/');
            }

            $pesanSukses = 'Data profil Anda berhasil diperbarui!';
        }
    }
}

// 2. PROSES HAPUS DATA AKUN (CRUD - DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'hapus_akun') {
    $konfirmasiHapus = trim($_POST['konfirmasi_teks'] ?? '');

    if ($konfirmasiHapus === 'HAPUS') {
        hapusUserAkun($currentUser['username']);

        // Musnahkan sesi & cookie
        $_SESSION = [];
        session_unset();
        session_destroy();
        if (isset($_COOKIE['titiktemu_session'])) {
            setcookie('titiktemu_session', '', time() - 3600, '/');
        }

        header('Location: login.php?pesan=akun_dihapus');
        exit();
    } else {
        $pesanError = 'Konfirmasi penghapusan akun salah. Silakan ketik kata HAPUS dengan huruf kapital untuk melanjutkan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="app-fullscreen">

        <!-- Mobile Header Bar -->
        <div class="mobile-nav-bar">
            <button class="btn-hamburger" onclick="toggleSidebar()" aria-label="Buka Menu">&#9776;</button>
            <span class="mobile-title">TitikTemu</span>
            <div style="width: 32px;"></div>
        </div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>TitikTemu</h2>
                <p>Sistem Informasi Barang Hilang</p>
            </div>

            <div class="sidebar-menu">
                <?php if ($isSatpam): ?>
                    <a href="dashboard.php" class="btn-sidebar">Dashboard</a>
                    <a href="barang.php" class="btn-sidebar">Data Barang</a>
                    <a href="kategori.php" class="btn-sidebar">Kategori Barang</a>
                    <a href="lokasi.php" class="btn-sidebar">Lokasi</a>
                    <a href="pengumuman.php" class="btn-sidebar">Pengumuman</a>
                    <a href="serah-terima.php" class="btn-sidebar">Serah Terima</a>
                    <a href="profil.php" class="btn-sidebar active">Profil Saya</a>
                <?php else: ?>
                    <a href="mahasiswa.php" class="btn-sidebar">Portal Mahasiswa</a>
                    <a href="profil.php" class="btn-sidebar active">Profil Saya</a>
                <?php endif; ?>
            </div>

            <div class="sidebar-footer">
                <button class="btn-sidebar-logout" onclick="konfirmasiLogout()">Logout</button>
            </div>
        </aside>

        <div class="content-area">
            <header class="top-bar">
                <h1>Profil Saya</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <?php if (!empty($pesanSukses)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($pesanSukses, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesanError)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($pesanError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="profile-layout-grid">
                    <!-- 1. LIHAT DATA PROFIL (READ) -->
                    <div class="content-card">
                        <div class="profile-card-header">
                            <div class="avatar-circle">
                                <?= htmlspecialchars(strtoupper(mb_substr($currentUser['nama'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div style="flex: 1;">
                                <h2 style="font-size: 18px; font-weight: 700; color: var(--slate-900);">
                                    <?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>
                                </h2>
                                <p style="color: var(--slate-500); font-size: 13px;">
                                    @<?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?> &bull; 
                                    <span class="badge <?= $currentUser['role'] === 'Satpam' ? 'badge-satpam' : 'badge-mahasiswa' ?>"><?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="profile-info-list" style="margin-top: 16px;">
                            <div class="profile-row">
                                <div class="profile-label">Nama Lengkap</div>
                                <div class="profile-value"><strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-label">Username</div>
                                <div class="profile-value" style="font-family: monospace;"><?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-label">Nomor Identitas (NIM / NIP)</div>
                                <div class="profile-value"><?= htmlspecialchars($currentUser['identitas'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <div class="profile-row">
                                <div class="profile-label">Peran Pengguna (Role)</div>
                                <div class="profile-value"><span class="badge <?= $currentUser['role'] === 'Satpam' ? 'badge-satpam' : 'badge-mahasiswa' ?>"><?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?></span></div>
                            </div>
                            <div class="profile-row" style="border-bottom: none;">
                                <div class="profile-label">Kontak / No. WhatsApp</div>
                                <div class="profile-value"><?= htmlspecialchars($currentUser['kontak'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. UBAH DATA PROFIL (UPDATE) -->
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Perbarui Informasi Profil</h3>
                        </div>

                        <form method="POST" action="profil.php">
                            <input type="hidden" name="aksi" value="ubah_profil">

                            <div class="form-group">
                                <label for="edit-nama">Nama Lengkap</label>
                                <input type="text" id="edit-nama" name="nama" value="<?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-identitas">Nomor Identitas (NIM / NIP)</label>
                                    <input type="text" id="edit-identitas" name="identitas" value="<?= htmlspecialchars($currentUser['identitas'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit-kontak">Kontak (HP / WhatsApp)</label>
                                    <input type="tel" id="edit-kontak" name="kontak" value="<?= htmlspecialchars($currentUser['kontak'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                            </div>

                            <div style="border-top: 1px solid var(--slate-200); margin: 20px 0 16px 0; padding-top: 14px;">
                                <p style="font-size: 13px; font-weight: 600; color: var(--slate-700); margin-bottom: 8px;">Ganti Password (Opsional)</p>
                                <p style="font-size: 12px; color: var(--slate-500); margin-bottom: 14px;">Biarkan kolom password kosong jika Anda tidak ingin mengubah password akun.</p>

                                <div class="form-group">
                                    <label for="edit-pass-lama">Password Lama</label>
                                    <input type="password" id="edit-pass-lama" name="password_lama" placeholder="Masukkan password lama Anda">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit-pass-baru">Password Baru</label>
                                        <input type="password" id="edit-pass-baru" name="password_baru" placeholder="Minimal 6 karakter">
                                    </div>

                                    <div class="form-group">
                                        <label for="edit-konfirmasi">Konfirmasi Password Baru</label>
                                        <input type="password" id="edit-konfirmasi" name="konfirmasi_baru" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <!-- 3. HAPUS DATA AKUN (DELETE) -->
                <div class="content-card" style="margin-top: 20px; border-color: #fecaca;">
                    <div class="content-card-header">
                        <h3 style="color: var(--slate-900);">Zona Bahaya: Hapus Akun</h3>
                    </div>
                    <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 14px;">
                        Tindakan ini akan menghapus data akun profil Anda secara permanen dari sistem. Anda akan otomatis logout dan tidak dapat mengakses sistem dengan akun ini lagi.
                    </p>
                    <button type="button" class="btn-danger" onclick="bukaModal('modal-hapus-akun')">Hapus Akun Saya</button>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Akun -->
    <div class="modal hidden" id="modal-hapus-akun">
        <div class="modal-content" style="max-width: 460px;">
            <div class="modal-header">
                <h3>Konfirmasi Hapus Akun</h3>
                <button class="btn-close" onclick="tutupModal('modal-hapus-akun')">&times;</button>
            </div>
            <form method="POST" action="profil.php">
                <input type="hidden" name="aksi" value="hapus_akun">
                <p style="font-size: 13.5px; color: var(--slate-700); margin-bottom: 14px;">
                    Apakah Anda yakin ingin menghapus akun <strong><?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?></strong>?
                </p>
                <div class="alert alert-danger" style="font-size: 12.5px; margin-bottom: 14px;">
                    Untuk mencegah tindakan tidak sengaja, silakan ketik kata <strong>HAPUS</strong> di bawah ini untuk konfirmasi:
                </div>
                <div class="form-group">
                    <input type="text" name="konfirmasi_teks" placeholder="Ketik HAPUS" required autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-akun')">Batal</button>
                    <button type="submit" class="btn-danger">Hapus Akun Permanen</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
