<?php
include 'auth_check.php';
require_once 'data_helper.php';

$list = ambilSemuaData('barang');
$daftarKategori = ambilSemuaData('kategori');
$daftarLokasi = ambilSemuaData('lokasi');

$pesanSukses = '';
$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $status = trim($_POST['status'] ?? 'Hilang');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $pelapor = trim($_POST['pelapor'] ?? $currentUser['nama']);
    $kontak = trim($_POST['kontak'] ?? $currentUser['kontak']);

    // Validasi
    if (empty($nama) || empty($kategori) || empty($lokasi) || empty($tanggal)) {
        $pesanError = 'Nama barang, kategori, lokasi, dan tanggal wajib diisi.';
    } elseif (mb_strlen($nama) < 3) {
        $pesanError = 'Nama barang minimal 3 karakter.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $pesanError = 'Format tanggal tidak valid.';
    } else {
        // Generate new ID
        $maxNum = 0;
        foreach ($list as $item) {
            if (preg_match('/^BRG-(\d+)$/', $item['id'], $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) $maxNum = $num;
            }
        }
        $newId = sprintf('BRG-%03d', $maxNum + 1);

        $newItem = [
            'id' => $newId,
            'nama' => $nama,
            'kategori' => $kategori,
            'status' => in_array($status, ['Hilang', 'Ditemukan']) ? $status : 'Hilang',
            'lokasi' => $lokasi,
            'tanggal' => $tanggal,
            'deskripsi' => $deskripsi,
            'pelapor' => $pelapor,
            'kontak' => $kontak
        ];

        simpanBarangData($newItem, false);
        $pesanSukses = 'Laporan Anda (' . $newId . ') berhasil disimpan ke sistem!';
        $list = ambilSemuaData('barang');
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Mahasiswa - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mahasiswa.css">
</head>

<body>

    <div class="mahasiswa-container">

        <header class="mhs-navbar">
            <div class="mhs-logo">
                <h2>TitikTemu</h2>
                <span>Portal Layanan Mahasiswa</span>
            </div>
            <div class="mhs-user-area">
                <span class="mhs-greeting">Halo, <strong><?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)</span>
                <a href="profil.php" class="btn-mhs-profile">Profil Saya</a>
                <button class="btn-mhs-logout" onclick="konfirmasiLogout()">Logout</button>
            </div>
        </header>

        <main class="mhs-content">

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

            <div class="mhs-hero">
                <h3>Pusat Informasi Barang Hilang & Temuan Kampus</h3>
                <p>Selamat datang, <?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>. Cari barang Anda yang tercecer atau laporkan barang temuan Anda agar dapat diamankan oleh petugas.</p>
            </div>

            <div class="toolbar">
                <div class="search-box">
                    <input type="text" id="input-cari" placeholder="Cari nama barang, pelapor, lokasi..." oninput="filterTabel('input-cari', 'table-barang-body')">
                </div>
                <button class="btn-primary" onclick="bukaModalLapor()">+ Lapor Barang Hilang / Temuan</button>
            </div>

            <div class="content-card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Barang & ID</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Lokasi Terakhir</th>
                                <th>Tanggal</th>
                                <th class="col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-barang-body">
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                        Data barang tidak ditemukan.
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
                                            <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetailMhs('<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['nama']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['lokasi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($item['tanggal'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['deskripsi']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['pelapor']), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($item['kontak']), ENT_QUOTES, 'UTF-8') ?>')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </button>
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

    <!-- Modal Lapor Barang -->
    <div class="modal hidden" id="modal-lapor">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Buat Laporan Barang</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-lapor')">&times;</button>
            </div>
            <form method="POST" action="mahasiswa.php">
                <div class="form-group">
                    <label for="lapor-nama">Nama Barang *</label>
                    <input type="text" id="lapor-nama" name="nama" required placeholder="Contoh: Flashdisk Sandisk 32GB Merah">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lapor-kategori">Kategori *</label>
                        <select id="lapor-kategori" name="kategori" required>
                            <?php foreach ($daftarKategori as $kat): ?>
                                <option value="<?= htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lapor-status">Jenis Laporan *</label>
                        <select id="lapor-status" name="status" required>
                            <option value="Hilang">Saya Kehilangan Barang</option>
                            <option value="Ditemukan">Saya Menemukan Barang</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lapor-lokasi">Lokasi Terkait *</label>
                        <select id="lapor-lokasi" name="lokasi" required>
                            <?php foreach ($daftarLokasi as $lok): ?>
                                <option value="<?= htmlspecialchars($lok['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($lok['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lapor-tanggal">Tanggal *</label>
                        <input type="date" id="lapor-tanggal" name="tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="lapor-deskripsi">Deskripsi Ciri Khusus</label>
                    <textarea id="lapor-deskripsi" name="deskripsi" placeholder="Tuliskan ciri khas, stiker, warna, atau tanda pengenal barang..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lapor-pelapor">Nama Pelapor *</label>
                        <input type="text" id="lapor-pelapor" name="pelapor" required value="<?= htmlspecialchars($currentUser['nama'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="lapor-kontak">No. HP / WA Aktif *</label>
                        <input type="tel" id="lapor-kontak" name="kontak" required value="<?= htmlspecialchars($currentUser['kontak'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-lapor')">Batal</button>
                    <button type="submit" class="btn-primary">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Barang -->
    <div class="modal hidden" id="modal-detail-mhs">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Informasi Barang</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-detail-mhs')">&times;</button>
            </div>
            <div>
                <div class="profile-row">
                    <div class="profile-label">ID Barang</div>
                    <div class="profile-value" id="mhs-det-id" style="font-family: monospace; font-weight: 700;">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Nama Barang</div>
                    <div class="profile-value" id="mhs-det-nama" style="font-weight: 700; color: var(--slate-900);">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Kategori</div>
                    <div class="profile-value" id="mhs-det-kategori">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Status</div>
                    <div class="profile-value"><span id="mhs-det-status" class="badge">-</span></div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Lokasi</div>
                    <div class="profile-value" id="mhs-det-lokasi">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Tanggal</div>
                    <div class="profile-value" id="mhs-det-tanggal">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Deskripsi</div>
                    <div class="profile-value" id="mhs-det-deskripsi">-</div>
                </div>
                <div class="profile-row">
                    <div class="profile-label">Pelapor</div>
                    <div class="profile-value" id="mhs-det-pelapor">-</div>
                </div>
                <div class="profile-row" style="border-bottom: none;">
                    <div class="profile-label">Kontak</div>
                    <div class="profile-value" id="mhs-det-kontak" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail-mhs')">Tutup</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function bukaModalLapor() {
            bukaModal('modal-lapor');
        }

        function tampilDetailMhs(id, nama, kat, status, lokasi, tanggal, deskripsi, pelapor, kontak) {
            document.getElementById('mhs-det-id').textContent = id;
            document.getElementById('mhs-det-nama').textContent = nama;
            document.getElementById('mhs-det-kategori').textContent = kat;
            const st = document.getElementById('mhs-det-status');
            st.textContent = status;
            st.className = 'badge ' + (status === 'Hilang' ? 'badge-hilang' : (status === 'Ditemukan' ? 'badge-ditemukan' : 'badge-kembali'));
            document.getElementById('mhs-det-lokasi').textContent = lokasi;
            document.getElementById('mhs-det-tanggal').textContent = tanggal;
            document.getElementById('mhs-det-deskripsi').textContent = deskripsi || '-';
            document.getElementById('mhs-det-pelapor').textContent = pelapor;
            document.getElementById('mhs-det-kontak').textContent = kontak;
            bukaModal('modal-detail-mhs');
        }
    </script>
</body>

</html>
