<?php
include 'auth_check.php';
require_once 'data_helper.php';

if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$list = ambilSemuaData('pengumuman');

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $list = array_filter($list, fn($p) => $p['id'] !== $id);
    simpanSemuaData('pengumuman', $list);
    header('Location: pengumuman.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $tgl_buat = trim($_POST['tgl_buat'] ?? date('Y-m-d'));
    $tgl_publikasi = trim($_POST['tgl_publikasi'] ?? date('Y-m-d'));
    $status = trim($_POST['status'] ?? 'Draft');
    $penulis = trim($_POST['penulis'] ?? $currentUser['nama']);
    $isi = trim($_POST['isi'] ?? '');

    if (!empty($judul)) {
        if (!empty($id)) {
            foreach ($list as &$item) {
                if ($item['id'] === $id) {
                    $item = compact('id', 'judul', 'tgl_buat', 'tgl_publikasi', 'status', 'penulis', 'isi');
                    break;
                }
            }
        } else {
            $nextId = count($list) > 0 ? max(array_map(fn($p) => (int)str_replace('PGM-', '', $p['id']), $list)) + 1 : 1;
            $newId = sprintf('PGM-%03d', $nextId);
            array_unshift($list, [
                'id' => $newId,
                'judul' => $judul,
                'tgl_buat' => $tgl_buat,
                'tgl_publikasi' => $tgl_publikasi,
                'status' => $status,
                'penulis' => $penulis,
                'isi' => $isi
            ]);
        }

        simpanSemuaData('pengumuman', $list);
        header('Location: pengumuman.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman - TitikTemu</title>
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
                <a href="lokasi.php" class="btn-sidebar">Lokasi</a>
                <a href="pengumuman.php" class="btn-sidebar active">Pengumuman</a>
                <a href="serah-terima.php" class="btn-sidebar">Serah Terima</a>
                <a href="profil.php" class="btn-sidebar">Profil Saya</a>
            </div>

            <div class="sidebar-footer">
                <button class="btn-sidebar-logout" onclick="konfirmasiLogout()">Logout</button>
            </div>
        </aside>

        <div class="content-area">
            <header class="top-bar">
                <h1>Data Pengumuman</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <div class="content-card">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="input-cari-pengumuman" placeholder="Cari judul pengumuman, penulis..." oninput="filterTabel('input-cari-pengumuman', 'table-pengumuman-body')">
                        </div>
                        <button class="btn-primary" onclick="bukaModalTambahPengumuman()">+ Buat Pengumuman</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Judul Pengumuman</th>
                                    <th>Status</th>
                                    <th>Penulis</th>
                                    <th>Tgl Publikasi</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-pengumuman-body">
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                            Belum ada pengumuman yang dibuat.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_values($list) as $idx => $item): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small style="color: var(--slate-500); font-family: monospace;">ID: <?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge <?= $item['status'] === 'Terbit' ? 'badge-kembali' : 'badge-draft' ?>">
                                                    <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($item['penulis'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['tgl_publikasi'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="col-aksi">
                                                <div class="action-row">
                                                    <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetailPengumuman('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['judul']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['penulis']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tgl_publikasi'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['isi'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    
                                                    <button type="button" class="btn-action btn-action-edit" title="Ubah Pengumuman" aria-label="Ubah Pengumuman" onclick="bukaModalEditPengumuman('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['judul']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tgl_publikasi'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['isi'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                    </button>
                                                    
                                                    <button type="button" class="btn-action btn-action-delete" title="Hapus Pengumuman" aria-label="Hapus Pengumuman" onclick="konfirmasiHapus('pengumuman.php?aksi=hapus&id=<?= urlencode($item['id']) ?>', 'pengumuman <?= htmlspecialchars(addslashes($item['judul']), ENT_QUOTES, 'UTF-8') ?>')">
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

    <!-- Modal Form Pengumuman -->
    <div class="modal hidden" id="modal-pengumuman">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-pgm-title">Buat Pengumuman</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-pengumuman')">&times;</button>
            </div>
            <form method="POST" action="pengumuman.php">
                <input type="hidden" id="pgm-id" name="id">

                <div class="form-group">
                    <label for="pgm-judul">Judul Pengumuman *</label>
                    <input type="text" id="pgm-judul" name="judul" required placeholder="Contoh: Penemuan Kunci Motor di Parkiran F">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pgm-status">Status Publikasi *</label>
                        <select id="pgm-status" name="status" required>
                            <option value="Terbit">Terbit (Publik)</option>
                            <option value="Draft">Draft (Disimpan)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pgm-tgl-publikasi">Tanggal Publikasi *</label>
                        <input type="date" id="pgm-tgl-publikasi" name="tgl_publikasi" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="pgm-isi">Isi Pesan Pengumuman *</label>
                    <textarea id="pgm-isi" name="isi" style="min-height: 120px;" required placeholder="Tuliskan isi pengumuman secara rinci..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-pengumuman')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Pengumuman -->
    <div class="modal hidden" id="modal-detail-pgm">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pengumuman</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-detail-pgm')">&times;</button>
            </div>
            <div>
                <h2 id="det-pgm-judul" style="font-size: 17px; font-weight: 700; color: var(--slate-900); margin-bottom: 8px;">-</h2>
                <p style="font-size: 12px; color: var(--slate-500); margin-bottom: 16px;">
                    Oleh: <span id="det-pgm-penulis">-</span> &bull; 
                    Tanggal: <span id="det-pgm-tgl">-</span> &bull; 
                    Status: <span id="det-pgm-status" class="badge">-</span>
                </p>
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); padding: 14px; border-radius: var(--radius-md); font-size: 13.5px; line-height: 1.6; color: var(--slate-800);" id="det-pgm-isi">
                    -
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail-pgm')">Tutup</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalTambahPengumuman() {
            document.getElementById('modal-pgm-title').textContent = 'Buat Pengumuman';
            document.getElementById('pgm-id').value = '';
            document.getElementById('pgm-judul').value = '';
            document.getElementById('pgm-status').value = 'Terbit';
            document.getElementById('pgm-tgl-publikasi').value = new Date().toISOString().split('T')[0];
            document.getElementById('pgm-isi').value = '';
            bukaModal('modal-pengumuman');
        }

        function bukaModalEditPengumuman(id, judul, status, tgl, isi) {
            document.getElementById('modal-pgm-title').textContent = 'Ubah Pengumuman (' + id + ')';
            document.getElementById('pgm-id').value = id;
            document.getElementById('pgm-judul').value = judul;
            document.getElementById('pgm-status').value = status;
            document.getElementById('pgm-tgl-publikasi').value = tgl;
            document.getElementById('pgm-isi').value = isi;
            bukaModal('modal-pengumuman');
        }

        function tampilDetailPengumuman(id, judul, status, penulis, tgl, isi) {
            document.getElementById('det-pgm-judul').textContent = judul;
            document.getElementById('det-pgm-penulis').textContent = penulis;
            document.getElementById('det-pgm-tgl').textContent = tgl;
            const st = document.getElementById('det-pgm-status');
            st.textContent = status;
            st.className = 'badge ' + (status === 'Terbit' ? 'badge-kembali' : 'badge-draft');
            document.getElementById('det-pgm-isi').textContent = isi;
            bukaModal('modal-detail-pgm');
        }
    </script>
</body>

</html>
