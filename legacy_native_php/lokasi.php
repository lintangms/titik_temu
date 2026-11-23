<?php
include 'auth_check.php';
require_once 'data_helper.php';

if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$list = ambilSemuaData('lokasi');

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $list = array_filter($list, fn($l) => $l['id'] !== $id);
    simpanSemuaData('lokasi', $list);
    header('Location: lokasi.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!empty($nama)) {
        if (!empty($id)) {
            foreach ($list as &$item) {
                if ($item['id'] === $id) {
                    $item = compact('id', 'nama', 'area', 'keterangan');
                    break;
                }
            }
        } else {
            $nextId = count($list) > 0 ? max(array_map(fn($l) => (int)str_replace('LOK-', '', $l['id']), $list)) + 1 : 1;
            $newId = sprintf('LOK-%03d', $nextId);
            $list[] = [
                'id' => $newId,
                'nama' => $nama,
                'area' => $area,
                'keterangan' => $keterangan
            ];
        }

        simpanSemuaData('lokasi', $list);
        header('Location: lokasi.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi - TitikTemu</title>
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
                <a href="dashboard.php" class="btn-sidebar">Dashboard</a>
                <a href="barang.php" class="btn-sidebar">Data Barang</a>
                <a href="kategori.php" class="btn-sidebar">Kategori Barang</a>
                <a href="lokasi.php" class="btn-sidebar active">Lokasi</a>
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
                <h1>Data Lokasi Kampus</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <div class="content-card">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="input-cari-lokasi" placeholder="Cari nama lokasi, area..." oninput="filterTabel('input-cari-lokasi', 'table-lokasi-body')">
                        </div>
                        <button class="btn-primary" onclick="bukaModalTambahLokasi()">+ Tambah Lokasi</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>ID Lokasi</th>
                                    <th>Nama Lokasi</th>
                                    <th>Zona / Area</th>
                                    <th>Keterangan</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-lokasi-body">
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                            Belum ada lokasi yang ditambahkan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_values($list) as $idx => $item): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td><strong style="font-family: monospace;"><?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><strong><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><?= htmlspecialchars($item['area'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="col-aksi">
                                                <div class="action-row">
                                                    <button type="button" class="btn-action btn-action-edit" title="Ubah Lokasi" aria-label="Ubah Lokasi" onclick="bukaModalEditLokasi('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['area'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['keterangan'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                    </button>
                                                    <button type="button" class="btn-action btn-action-delete" title="Hapus Lokasi" aria-label="Hapus Lokasi" onclick="konfirmasiHapus('lokasi.php?aksi=hapus&id=<?= urlencode($item['id']) ?>', 'lokasi <?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </button>
                                                </div>
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

    <!-- Modal Form Lokasi -->
    <div class="modal hidden" id="modal-lokasi">
        <div class="modal-content" style="max-width: 440px;">
            <div class="modal-header">
                <h3 id="modal-lok-title">Tambah Lokasi</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-lokasi')">&times;</button>
            </div>
            <form method="POST" action="lokasi.php">
                <input type="hidden" id="lok-id" name="id">

                <div class="form-group">
                    <label for="lok-nama">Nama Lokasi / Tempat *</label>
                    <input type="text" id="lok-nama" name="nama" required placeholder="Contoh: Perpustakaan Pusat, Kantin F, dll.">
                </div>

                <div class="form-group">
                    <label for="lok-area">Zona / Area Gedung</label>
                    <input type="text" id="lok-area" name="area" placeholder="Contoh: Gedung Kuliah Bersama Lt. 2">
                </div>

                <div class="form-group">
                    <label for="lok-keterangan">Keterangan Tambahan</label>
                    <textarea id="lok-keterangan" name="keterangan" placeholder="Penjelasan posisi atau akses lokasi..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-lokasi')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalTambahLokasi() {
            document.getElementById('modal-lok-title').textContent = 'Tambah Lokasi';
            document.getElementById('lok-id').value = '';
            document.getElementById('lok-nama').value = '';
            document.getElementById('lok-area').value = '';
            document.getElementById('lok-keterangan').value = '';
            bukaModal('modal-lokasi');
        }

        function bukaModalEditLokasi(id, nama, area, keterangan) {
            document.getElementById('modal-lok-title').textContent = 'Ubah Lokasi (' + id + ')';
            document.getElementById('lok-id').value = id;
            document.getElementById('lok-nama').value = nama;
            document.getElementById('lok-area').value = area;
            document.getElementById('lok-keterangan').value = keterangan;
            bukaModal('modal-lokasi');
        }
    </script>
</body>

</html>
