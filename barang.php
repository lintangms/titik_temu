<?php
include 'auth_check.php';
require_once 'data_helper.php';

// Pastikan hanya role Satpam / Petugas yang dapat mengakses halaman pengelolaan ini
if ($currentUser['role'] !== 'Satpam') {
    header('Location: mahasiswa.php');
    exit();
}

$pesanSukses = '';
$pesanError = '';

// Notifikasi flash dari redirect
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'simpan_sukses') {
        $pesanSukses = 'Data barang berhasil disimpan ke dalam sistem.';
    } elseif ($_GET['pesan'] === 'hapus_sukses') {
        $pesanSukses = 'Data barang berhasil dihapus dari sistem.';
    }
}

// 1. PROSES HAPUS DATA BARANG (CRUD - DELETE)
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = trim($_GET['id']);
    if (!empty($id)) {
        hapusBarangData($id);
        header('Location: barang.php?pesan=hapus_sukses');
        exit();
    }
}

$list = ambilSemuaData('barang');
$daftarKategori = ambilSemuaData('kategori');
$daftarLokasi = ambilSemuaData('lokasi');

// 2. PROSES TAMBAH & UBAH DATA BARANG (CRUD - CREATE & UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $pelapor = trim($_POST['pelapor'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');

    // DAFTAR VALIDASI (KOSONG, FORMAT SALAH, DAN NILAI TIDAK WAJAR DITOLAK)
    $listKategoriValid = array_map(fn($k) => $k['nama'], $daftarKategori);
    $listLokasiValid = array_map(fn($l) => $l['nama'], $daftarLokasi);
    $statusValid = ['Hilang', 'Ditemukan', 'Sudah Dikembalikan'];

    // 1. Validasi Kolom Wajib Kosong
    if (empty($nama) || empty($kategori) || empty($status) || empty($lokasi) || empty($tanggal) || empty($pelapor) || empty($kontak)) {
        $pesanError = 'Gagal menyimpan: Semua kolom bertanda bintang (*) wajib diisi.';
    }
    // 2. Validasi Nama Barang (minimal 3 karakter)
    elseif (mb_strlen($nama) < 3) {
        $pesanError = 'Gagal menyimpan: Nama barang minimal harus terdiri dari 3 karakter.';
    }
    // 3. Validasi Pilihan Kategori
    elseif (!in_array($kategori, $listKategoriValid)) {
        $pesanError = 'Gagal menyimpan: Kategori yang dipilih tidak valid.';
    }
    // 4. Validasi Pilihan Status
    elseif (!in_array($status, $statusValid)) {
        $pesanError = 'Gagal menyimpan: Status barang harus berupa Hilang, Ditemukan, atau Sudah Dikembalikan.';
    }
    // 5. Validasi Pilihan Lokasi
    elseif (!in_array($lokasi, $listLokasiValid)) {
        $pesanError = 'Gagal menyimpan: Lokasi yang dipilih tidak terdaftar di sistem.';
    }
    // 6. Validasi Tanggal (Format & Nilai Wajar: tidak sebelum tahun 2000 & tidak lebih dari 1 tahun ke depan)
    elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $pesanError = 'Gagal menyimpan: Format tanggal tidak valid (harus YYYY-MM-DD).';
    } else {
        $tahun = (int)substr($tanggal, 0, 4);
        $tahunSekarang = (int)date('Y');
        if ($tahun < 2000 || $tahun > ($tahunSekarang + 1)) {
            $pesanError = 'Gagal menyimpan: Tanggal kejadian tidak wajar (hanya diperbolehkan antara tahun 2000 hingga ' . ($tahunSekarang + 1) . ').';
        }
        // 7. Validasi Nama Pelapor
        elseif (mb_strlen($pelapor) < 3) {
            $pesanError = 'Gagal menyimpan: Nama pelapor minimal harus terdiri dari 3 karakter.';
        }
        // 8. Validasi Nomor Kontak (minimal 9 digit angka/karakter telepon)
        elseif (!preg_match('/^[0-9\-\+\s]{9,20}$/', $kontak)) {
            $pesanError = 'Gagal menyimpan: Nomor kontak pelapor tidak valid (minimal 9 digit angka).';
        } else {
            // Semua validasi terpenuhi, proses simpan data
            $isEdit = !empty($id);

            if (!$isEdit) {
                // Generate ID unik terstruktur: BRG-001, BRG-002, dst.
                $maxNum = 0;
                foreach ($list as $item) {
                    if (preg_match('/^BRG-(\d+)$/', $item['id'], $m)) {
                        $num = (int)$m[1];
                        if ($num > $maxNum) $maxNum = $num;
                    }
                }
                $id = sprintf('BRG-%03d', $maxNum + 1);
            }

            $itemData = compact('id', 'nama', 'kategori', 'status', 'lokasi', 'tanggal', 'deskripsi', 'pelapor', 'kontak');
            simpanBarangData($itemData, $isEdit);

            header('Location: barang.php?pesan=simpan_sukses');
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
    <title>Data Barang - TitikTemu</title>
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
                <a href="barang.php" class="btn-sidebar active">Data Barang</a>
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
                <h1>Kelola Data Barang</h1>
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

                <div class="content-card">
                    <div class="toolbar">
                        <div class="search-box">
                            <input type="text" id="input-cari" placeholder="Cari nama barang, pelapor, lokasi, kategori..." oninput="filterTabel('input-cari', 'table-barang-body')">
                        </div>
                        <button class="btn-primary" onclick="bukaModalTambah()">+ Tambah Data Barang</button>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Barang & ID</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Tanggal</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-barang-body">
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 28px; color: var(--slate-500);">
                                            Belum ada data barang tercatat.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_values($list) as $idx => $item): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small style="color: var(--slate-500); font-family: monospace;">ID: <?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= getBadgeClass($item['status']) ?>">
                                                    <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($item['lokasi'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="col-aksi">
                                                <div class="action-row">
                                                    <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetail('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['lokasi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['deskripsi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['pelapor']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['kontak']), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    
                                                    <button type="button" class="btn-action btn-action-edit" title="Ubah Data" aria-label="Ubah Data" onclick="bukaModalEdit('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['lokasi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['deskripsi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['pelapor']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['kontak']), ENT_QUOTES, 'UTF-8') ?>')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                    </button>
                                                    
                                                    <button type="button" class="btn-action btn-action-delete" title="Hapus Data" aria-label="Hapus Data" onclick="konfirmasiHapusBarang('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>')">
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

    <!-- MODAL FORM (TAMBAH / UBAH DATA BARANG) -->
    <div class="modal hidden" id="modal-form">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Tambah Data Barang</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-form')">&times;</button>
            </div>
            <form id="formBarang" method="POST" action="barang.php">
                <input type="hidden" id="form-id" name="id">

                <div class="form-group">
                    <label for="form-nama">Nama Barang *</label>
                    <input type="text" id="form-nama" name="nama" required placeholder="Contoh: Helm KYT Fullface Abu-abu">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="form-kategori">Kategori *</label>
                        <select id="form-kategori" name="kategori" required>
                            <?php foreach ($daftarKategori as $kat): ?>
                                <option value="<?= htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="form-status">Status *</label>
                        <select id="form-status" name="status" required>
                            <option value="Hilang">Hilang</option>
                            <option value="Ditemukan">Ditemukan</option>
                            <option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="form-lokasi">Lokasi Terakhir / Ditemukan *</label>
                        <select id="form-lokasi" name="lokasi" required>
                            <?php foreach ($daftarLokasi as $lok): ?>
                                <option value="<?= htmlspecialchars($lok['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($lok['nama'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($lok['area'], ENT_QUOTES, 'UTF-8') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="form-tanggal">Tanggal Kejadian / Ditemukan *</label>
                        <input type="date" id="form-tanggal" name="tanggal" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="form-deskripsi">Deskripsi Ciri Khusus</label>
                    <textarea id="form-deskripsi" name="deskripsi" placeholder="Warna, nomor seri, stiker, isi atau ciri khas khusus barang..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="form-pelapor">Nama Pelapor / Penemu *</label>
                        <input type="text" id="form-pelapor" name="pelapor" required placeholder="Nama lengkap">
                    </div>

                    <div class="form-group">
                        <label for="form-kontak">Kontak Pelapor (No. HP / WA) *</label>
                        <input type="tel" id="form-kontak" name="kontak" required placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-form')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL INFORMASI BARANG -->
    <div class="modal hidden" id="modal-detail">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Informasi Barang</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-detail')">&times;</button>
            </div>
            <div>
                <div class="profile-row">
                    <div class="profile-label">ID Barang</div>
                    <div class="profile-value" id="det-id" style="font-family: monospace; font-weight: 700;">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Nama Barang</div>
                    <div class="profile-value" id="det-nama" style="font-weight: 700; color: var(--slate-900);">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Kategori</div>
                    <div class="profile-value" id="det-kategori">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Status</div>
                    <div class="profile-value"><span id="det-status" class="badge">-</span></div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Lokasi</div>
                    <div class="profile-value" id="det-lokasi">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Tanggal</div>
                    <div class="profile-value" id="det-tanggal">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Deskripsi</div>
                    <div class="profile-value" id="det-deskripsi">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Nama Pelapor</div>
                    <div class="profile-value" id="det-pelapor">-</div>
                </div>
                <div class="profile-row" style="border-bottom: none;">
                    <div class="profile-label">Kontak Pelapor</div>
                    <div class="profile-value" id="det-kontak" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS BARANG (INTERAKTIF & JELAS) -->
    <div class="modal hidden" id="modal-hapus-barang">
        <div class="modal-content" style="max-width: 440px;">
            <div class="modal-header">
                <h3>Konfirmasi Hapus Data</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-barang')">&times;</button>
            </div>
            <div style="margin-bottom: 20px;">
                <p style="font-size: 13.5px; color: var(--slate-700); line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus data barang:
                </p>
                <div style="background: var(--slate-100); border: 1px solid var(--slate-200); padding: 10px 14px; border-radius: var(--radius-md); margin: 12px 0;">
                    <strong id="hapus-item-nama" style="color: var(--slate-900); font-size: 14px;">-</strong><br>
                    <small id="hapus-item-id" style="color: var(--slate-500); font-family: monospace;">ID: -</small>
                </div>
                <p style="font-size: 12px; color: var(--slate-500);">
                    Data yang sudah dihapus tidak dapat dipulihkan kembali.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-barang')">Batal</button>
                <a id="btn-konfirmasi-hapus" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus Data</a>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalTambah() {
            document.getElementById('modal-title').textContent = 'Tambah Data Barang';
            document.getElementById('form-id').value = '';
            document.getElementById('form-nama').value = '';
            document.getElementById('form-status').value = 'Hilang';
            document.getElementById('form-tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('form-deskripsi').value = '';
            document.getElementById('form-pelapor').value = '<?= htmlspecialchars(addslashes($currentUser['nama']), ENT_QUOTES, 'UTF-8') ?>';
            document.getElementById('form-kontak').value = '<?= htmlspecialchars(addslashes($currentUser['kontak']), ENT_QUOTES, 'UTF-8') ?>';
            bukaModal('modal-form');
        }

        function bukaModalEdit(id, nama, kat, status, lokasi, tanggal, deskripsi, pelapor, kontak) {
            document.getElementById('modal-title').textContent = 'Ubah Data Barang (' + id + ')';
            document.getElementById('form-id').value = id;
            document.getElementById('form-nama').value = nama;
            document.getElementById('form-kategori').value = kat;
            document.getElementById('form-status').value = status;
            document.getElementById('form-lokasi').value = lokasi;
            document.getElementById('form-tanggal').value = tanggal;
            document.getElementById('form-deskripsi').value = deskripsi;
            document.getElementById('form-pelapor').value = pelapor;
            document.getElementById('form-kontak').value = kontak;
            bukaModal('modal-form');
        }

        function tampilDetail(id, nama, kat, status, lokasi, tanggal, deskripsi, pelapor, kontak) {
            document.getElementById('det-id').textContent = id;
            document.getElementById('det-nama').textContent = nama;
            document.getElementById('det-kategori').textContent = kat;
            const st = document.getElementById('det-status');
            st.textContent = status;
            st.className = 'badge ' + (status === 'Hilang' ? 'badge-hilang' : (status === 'Ditemukan' ? 'badge-ditemukan' : 'badge-kembali'));
            document.getElementById('det-lokasi').textContent = lokasi;
            document.getElementById('det-tanggal').textContent = tanggal;
            document.getElementById('det-deskripsi').textContent = deskripsi || '-';
            document.getElementById('det-pelapor').textContent = pelapor;
            document.getElementById('det-kontak').textContent = kontak;
            bukaModal('modal-detail');
        }

        function konfirmasiHapusBarang(id, nama) {
            document.getElementById('hapus-item-nama').textContent = nama;
            document.getElementById('hapus-item-id').textContent = 'ID: ' + id;
            document.getElementById('btn-konfirmasi-hapus').href = 'barang.php?aksi=hapus&id=' + encodeURIComponent(id);
            bukaModal('modal-hapus-barang');
        }
    </script>
</body>

</html>
