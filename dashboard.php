<?php
include 'auth_check.php';
require_once 'data_helper.php';

if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$list = ambilSemuaData('barang');
$total = count($list);
$hilang = count(array_filter($list, fn($i) => $i['status'] === 'Hilang'));
$ditemukan = count(array_filter($list, fn($i) => $i['status'] === 'Ditemukan'));
$kembali = count(array_filter($list, fn($i) => $i['status'] === 'Sudah Dikembalikan'));
$recent = array_slice($list, 0, 5);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TitikTemu</title>
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
                <a href="dashboard.php" class="btn-sidebar active">Dashboard</a>
                <a href="barang.php" class="btn-sidebar">Data Barang</a>
                <a href="kategori.php" class="btn-sidebar">Kategori Barang</a>
                <a href="lokasi.php" class="btn-sidebar">Lokasi</a>
                <a href="pengumuman.php" class="btn-sidebar">Pengumuman</a>
                <a href="serah-terima.php" class="btn-sidebar">Serah Terima</a>
                <a href="profil.php" class="btn-sidebar">Profil Saya</a>
            </div>

            <div class="sidebar-footer">
                <button class="btn-sidebar-logout" onclick="konfirmasiLogout()">Logout</button>
            </div>
        </aside>

        <div class="content-area">
            <header class="top-bar">
                <h1>Dashboard</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <div class="dashboard-banner">
                    <h3>Selamat Datang, <?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>!</h3>
                    <p>Sistem pengelolaan pelaporan, pencarian, dan pengembalian barang hilang di lingkungan kampus.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h4><?= $total ?></h4>
                        <span>Total Laporan</span>
                    </div>
                    <div class="stat-card">
                        <h4 style="color: #b91c1c;"><?= $hilang ?></h4>
                        <span>Barang Hilang</span>
                    </div>
                    <div class="stat-card">
                        <h4 style="color: #0369a1;"><?= $ditemukan ?></h4>
                        <span>Barang Ditemukan</span>
                    </div>
                    <div class="stat-card">
                        <h4 style="color: #15803d;"><?= $kembali ?></h4>
                        <span>Sudah Dikembalikan</span>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <h3>Laporan Barang Terbaru</h3>
                        <a href="barang.php" class="btn-secondary" style="font-size: 12.5px; padding: 6px 14px;">Lihat Semua Data &rarr;</a>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Tanggal</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                            Belum ada data barang terbaru.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small style="color: var(--slate-500); font-family: monospace;">ID: <?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><span class="badge <?= getBadgeClass($item['status']) ?>"><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td><?= htmlspecialchars($item['lokasi'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="col-aksi">
                                                <a href="barang.php" class="btn-action btn-action-detail" title="Kelola Data" aria-label="Kelola Data">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
