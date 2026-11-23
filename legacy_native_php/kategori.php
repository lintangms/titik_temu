<?php
include 'auth_check.php';
require_once 'data_helper.php';

if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$list = ambilSemuaData('kategori');

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $list = array_filter($list, fn($k) => $k['id'] !== $id);
    simpanSemuaData('kategori', $list);
    header('Location: kategori.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama)) {
        if (!empty($id)) {
            foreach ($list as &$item) {
                if ($item['id'] === $id) {
                    $item = compact('id', 'nama', 'deskripsi');
                    break;
                }
            }
        } else {
            $nextId = count($list) > 0 ? max(array_map(fn($k) => (int)str_replace('KAT-', '', $k['id']), $list)) + 1 : 1;
            $newId = sprintf('KAT-%03d', $nextId);
            $list[] = [
                'id' => $newId,
                'nama' => $nama,
                'deskripsi' => $deskripsi
            ];
        }

        simpanSemuaData('kategori', $list);
        header('Location: kategori.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Barang - TitikTemu</title>
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
                <a href="kategori.php" class="btn-sidebar active">Kategori Barang</a>
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
                <h1>Data Kategori Barang</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <div class="content-card">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="input-cari-kategori" placeholder="Cari nama kategori..." oninput="filterTabel('input-cari-kategori', 'table-kategori-body')">
                        </div>
                        <button class="btn-primary" onclick="bukaModalTambahKategori()">+ Tambah Kategori</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>ID Kategori</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi / Contoh Barang</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-kategori-body">
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                            Belum ada kategori yang ditambahkan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_values($list) as $idx => $item): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td><strong style="font-family: monospace;"><?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><strong><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><?= htmlspecialchars($item['deskripsi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="col-aksi">
                                                <div class="action-row">
                                                    <button type="button" class="btn-action btn-action-edit" title="Ubah Kategori" aria-label="Ubah Kategori" onclick="bukaModalEditKategori('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                    </button>
                                                    <button type="button" class="btn-action btn-action-delete" title="Hapus Kategori" aria-label="Hapus Kategori" onclick="konfirmasiHapus('kategori.php?aksi=hapus&id=<?= urlencode($item['id']) ?>', 'kategori <?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>')">
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

    <!-- Modal Form Kategori -->
    <div class="modal hidden" id="modal-kategori">
        <div class="modal-content" style="max-width: 440px;">
            <div class="modal-header">
                <h3 id="modal-kat-title">Tambah Kategori</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-kategori')">&times;</button>
            </div>
            <form method="POST" action="kategori.php">
                <input type="hidden" id="kat-id" name="id">

                <div class="form-group">
                    <label for="kat-nama">Nama Kategori *</label>
                    <input type="text" id="kat-nama" name="nama" required placeholder="Contoh: Elektronik, Dokumen, dll.">
                </div>

                <div class="form-group">
                    <label for="kat-deskripsi">Deskripsi / Contoh Barang</label>
                    <textarea id="kat-deskripsi" name="deskripsi" placeholder="Sebutkan contoh barang di dalam kategori ini..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-kategori')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalTambahKategori() {
            document.getElementById('modal-kat-title').textContent = 'Tambah Kategori';
            document.getElementById('kat-id').value = '';
            document.getElementById('kat-nama').value = '';
            document.getElementById('kat-deskripsi').value = '';
            bukaModal('modal-kategori');
        }

        function bukaModalEditKategori(id, nama, deskripsi) {
            document.getElementById('modal-kat-title').textContent = 'Ubah Kategori (' + id + ')';
            document.getElementById('kat-id').value = id;
            document.getElementById('kat-nama').value = nama;
            document.getElementById('kat-deskripsi').value = deskripsi;
            bukaModal('modal-kategori');
        }
    </script>
</body>

</html>
