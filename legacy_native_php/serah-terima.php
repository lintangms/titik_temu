<?php
include 'auth_check.php';
require_once 'data_helper.php';

if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$list = ambilSemuaData('serah_terima');
$daftarBarang = ambilSemuaData('barang');

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $list = array_filter($list, fn($s) => $s['id'] !== $id);
    simpanSemuaData('serah_terima', $list);
    header('Location: serah-terima.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $barang_id = trim($_POST['barang_id'] ?? '');
    $penerima = trim($_POST['penerima'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $petugas = trim($_POST['petugas'] ?? $currentUser['nama']);
    $status = trim($_POST['status'] ?? 'Selesai');
    $catatan = trim($_POST['catatan'] ?? '');

    $barang_nama = $barang_id;
    foreach ($daftarBarang as $b) {
        if ($b['id'] === $barang_id) {
            $barang_nama = $b['nama'] . ' (' . $b['id'] . ')';
            break;
        }
    }

    if (!empty($barang_id) && !empty($penerima)) {
        if (!empty($id)) {
            foreach ($list as &$item) {
                if ($item['id'] === $id) {
                    $item = compact('id', 'barang_id', 'barang_nama', 'penerima', 'tanggal', 'petugas', 'status', 'catatan');
                    break;
                }
            }
        } else {
            $nextId = count($list) > 0 ? max(array_map(fn($s) => (int)str_replace('STR-', '', $s['id']), $list)) + 1 : 1;
            $newId = sprintf('STR-%03d', $nextId);
            array_unshift($list, [
                'id' => $newId,
                'barang_id' => $barang_id,
                'barang_nama' => $barang_nama,
                'penerima' => $penerima,
                'tanggal' => $tanggal,
                'petugas' => $petugas,
                'status' => $status,
                'catatan' => $catatan
            ]);

            // Jika serah terima selesai, update status barang menjadi 'Sudah Dikembalikan'
            if ($status === 'Selesai') {
                foreach ($daftarBarang as &$brg) {
                    if ($brg['id'] === $barang_id) {
                        $brg['status'] = 'Sudah Dikembalikan';
                        break;
                    }
                }
                simpanSemuaData('barang', $daftarBarang);
            }
        }

        simpanSemuaData('serah_terima', $list);
        header('Location: serah-terima.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serah Terima - TitikTemu</title>
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
                <a href="pengumuman.php" class="btn-sidebar">Pengumuman</a>
                <a href="serah-terima.php" class="btn-sidebar active">Serah Terima</a>
                <a href="profil.php" class="btn-sidebar">Profil Saya</a>
            </div>

            <div class="sidebar-footer">
                <button class="btn-sidebar-logout" onclick="konfirmasiLogout()">Logout</button>
            </div>
        </aside>

        <div class="content-area">
            <header class="top-bar">
                <h1>Log Serah Terima Barang</h1>
                <div class="top-bar-user">Login sebagai: <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</div>
            </header>

            <main class="main-view">
                <div class="content-card">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="input-cari-serah" placeholder="Cari nama barang, penerima..." oninput="filterTabel('input-cari-serah', 'table-serah-body')">
                        </div>
                        <button class="btn-primary" onclick="bukaModalTambahSerah()">+ Catat Serah Terima</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>ID Log</th>
                                    <th>Barang</th>
                                    <th>Penerima</th>
                                    <th>Tanggal</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-serah-body">
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                            Belum ada log serah terima barang tercatat.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_values($list) as $idx => $item): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td><strong style="font-family: monospace;"><?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><strong><?= htmlspecialchars($item['barang_nama'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><?= htmlspecialchars($item['penerima'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['petugas'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $item['status'] === 'Selesai' ? 'badge-kembali' : 'badge-proses' ?>">
                                                    <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td class="col-aksi">
                                                <div class="action-row">
                                                    <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetailSerah('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['barang_nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['penerima']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['petugas']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['catatan'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    
                                                    <button type="button" class="btn-action btn-action-delete" title="Hapus Log" aria-label="Hapus Log" onclick="konfirmasiHapus('serah-terima.php?aksi=hapus&id=<?= urlencode($item['id']) ?>', 'log serah terima <?= htmlspecialchars(addslashes($item['id']), ENT_QUOTES, 'UTF-8') ?>')">
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

    <!-- Modal Form Serah Terima -->
    <div class="modal hidden" id="modal-serah">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-str-title">Catat Serah Terima Barang</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-serah')">&times;</button>
            </div>
            <form method="POST" action="serah-terima.php">
                <input type="hidden" id="str-id" name="id">

                <div class="form-group">
                    <label for="str-barang">Pilih Barang Temuan *</label>
                    <select id="str-barang" name="barang_id" required>
                        <?php foreach ($daftarBarang as $b): ?>
                            <option value="<?= htmlspecialchars($b['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($b['id'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($b['nama'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($b['status'], ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="str-penerima">Nama Pengambil / Pemilik *</label>
                        <input type="text" id="str-penerima" name="penerima" required placeholder="Nama lengkap pemilik">
                    </div>

                    <div class="form-group">
                        <label for="str-tanggal">Tanggal Penyerahan *</label>
                        <input type="date" id="str-tanggal" name="tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="str-petugas">Petugas yang Menyerahkan *</label>
                        <input type="text" id="str-petugas" name="petugas" required value="<?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="str-status">Status Serah Terima *</label>
                        <select id="str-status" name="status" required>
                            <option value="Selesai">Selesai (Sudah Diserahkan)</option>
                            <option value="Menunggu Penyerahan">Menunggu Penyerahan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="str-catatan">Catatan / Bukti Kepemilikan</label>
                    <textarea id="str-catatan" name="catatan" placeholder="Bukti verifikasi (KTM, foto tanda bukti, ciri rahasia barang)..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-serah')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Serah Terima -->
    <div class="modal hidden" id="modal-detail-str">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Log Serah Terima</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-detail-str')">&times;</button>
            </div>
            <div>
                <div class="profile-row">
                    <div class="profile-label">ID Log</div>
                    <div class="profile-value" id="det-str-id" style="font-family: monospace; font-weight: 700;">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Barang</div>
                    <div class="profile-value" id="det-str-barang" style="font-weight: 700; color: var(--slate-900);">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Penerima / Pemilik</div>
                    <div class="profile-value" id="det-str-penerima">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Tanggal Serah Terima</div>
                    <div class="profile-value" id="det-str-tanggal">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Petugas Keamanan</div>
                    <div class="profile-value" id="det-str-petugas">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Status</div>
                    <div class="profile-value"><span id="det-str-status" class="badge">-</span></div>
                </div>
                <div class="profile-row" style="border-bottom: none;">
                    <div class="profile-label">Catatan Verifikasi</div>
                    <div class="profile-value" id="det-str-catatan">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail-str')">Tutup</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalTambahSerah() {
            document.getElementById('modal-str-title').textContent = 'Catat Serah Terima Barang';
            document.getElementById('str-id').value = '';
            document.getElementById('str-penerima').value = '';
            document.getElementById('str-tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('str-catatan').value = '';
            bukaModal('modal-serah');
        }

        function tampilDetailSerah(id, barang, penerima, tanggal, petugas, status, catatan) {
            document.getElementById('det-str-id').textContent = id;
            document.getElementById('det-str-barang').textContent = barang;
            document.getElementById('det-str-penerima').textContent = penerima;
            document.getElementById('det-str-tanggal').textContent = tanggal;
            document.getElementById('det-str-petugas').textContent = petugas;
            const st = document.getElementById('det-str-status');
            st.textContent = status;
            st.className = 'badge ' + (status === 'Selesai' ? 'badge-kembali' : 'badge-proses');
            document.getElementById('det-str-catatan').textContent = catatan || '-';
            bukaModal('modal-detail-str');
        }
    </script>
</body>

</html>
