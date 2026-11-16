<?php
require_once __DIR__ . '/koneksi.php';

$dataDir = __DIR__ . '/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

/**
 * Mengambil semua data untuk key/tabel tertentu.
 * Jika MySQL aktif via PDO, ambil langsung dari database.
 * Jika tidak, fallback ke file JSON di folder data/.
 */
function ambilSemuaData($key) {
    global $dataDir;
    $db = getDB();

    $tableMap = [
        'barang' => 'barang',
        'users' => 'users',
        'kategori' => 'kategori',
        'lokasi' => 'lokasi',
        'pengumuman' => 'pengumuman',
        'serah_terima' => 'serah_terima'
    ];

    if ($db !== null && isset($tableMap[$key])) {
        $table = $tableMap[$key];
        try {
            $orderCol = ($table === 'barang' || $table === 'pengumuman' || $table === 'serah_terima') ? 'id DESC' : 'id ASC';
            $stmt = $db->query("SELECT * FROM `{$table}` ORDER BY {$orderCol}");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                // Simpan cache ke JSON agar sinkron
                $file = $dataDir . '/' . $key . '.json';
                file_put_contents($file, json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return $rows;
            }
        } catch (PDOException $e) {
            // Jika query gagal, lanjutkan baca dari file JSON
        }
    }

    $file = $dataDir . '/' . $key . '.json';
    if (!file_exists($file)) {
        $default = dapatkanDataAwal($key);
        simpanSemuaData($key, $default);
        return $default;
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

/**
 * Menyimpan seluruh data array ke file JSON dan menyinkronkan ke database jika MySQL aktif.
 */
function simpanSemuaData($key, $data) {
    global $dataDir;
    $file = $dataDir . '/' . $key . '.json';
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Sinkronisasi otomatis ke database MySQL jika koneksi aktif
    $db = getDB();
    if ($db !== null) {
        syncTableWithDatabase($key, $data);
    }
}

/**
 * Sinkronisasi tabel MySQL saat ada penambahan, perubahan, atau penghapusan data.
 */
function syncTableWithDatabase($key, $data) {
    $db = getDB();
    if ($db === null) return;

    try {
        if ($key === 'kategori') {
            $ids = array_column($data, 'id');
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("DELETE FROM kategori WHERE id NOT IN ($in)");
                $stmt->execute($ids);
            } else {
                $db->query("DELETE FROM kategori");
            }
            $stmt = $db->prepare("INSERT INTO kategori (id, nama, deskripsi) VALUES (:id, :nama, :deskripsi) ON DUPLICATE KEY UPDATE nama = VALUES(nama), deskripsi = VALUES(deskripsi)");
            foreach ($data as $row) {
                $stmt->execute([
                    ':id' => $row['id'],
                    ':nama' => $row['nama'],
                    ':deskripsi' => $row['deskripsi'] ?? ''
                ]);
            }
        } elseif ($key === 'lokasi') {
            $ids = array_column($data, 'id');
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("DELETE FROM lokasi WHERE id NOT IN ($in)");
                $stmt->execute($ids);
            } else {
                $db->query("DELETE FROM lokasi");
            }
            $stmt = $db->prepare("INSERT INTO lokasi (id, nama, area, keterangan) VALUES (:id, :nama, :area, :keterangan) ON DUPLICATE KEY UPDATE nama = VALUES(nama), area = VALUES(area), keterangan = VALUES(keterangan)");
            foreach ($data as $row) {
                $stmt->execute([
                    ':id' => $row['id'],
                    ':nama' => $row['nama'],
                    ':area' => $row['area'] ?? '',
                    ':keterangan' => $row['keterangan'] ?? ''
                ]);
            }
        } elseif ($key === 'pengumuman') {
            $ids = array_column($data, 'id');
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("DELETE FROM pengumuman WHERE id NOT IN ($in)");
                $stmt->execute($ids);
            } else {
                $db->query("DELETE FROM pengumuman");
            }
            $stmt = $db->prepare("INSERT INTO pengumuman (id, judul, tgl_buat, tgl_publikasi, status, penulis, isi) VALUES (:id, :judul, :tgl_buat, :tgl_publikasi, :status, :penulis, :isi) ON DUPLICATE KEY UPDATE judul = VALUES(judul), tgl_buat = VALUES(tgl_buat), tgl_publikasi = VALUES(tgl_publikasi), status = VALUES(status), penulis = VALUES(penulis), isi = VALUES(isi)");
            foreach ($data as $row) {
                $stmt->execute([
                    ':id' => $row['id'],
                    ':judul' => $row['judul'],
                    ':tgl_buat' => $row['tgl_buat'] ?? date('Y-m-d'),
                    ':tgl_publikasi' => $row['tgl_publikasi'] ?? date('Y-m-d'),
                    ':status' => in_array($row['status'] ?? '', ['Draft', 'Terbit']) ? $row['status'] : 'Draft',
                    ':penulis' => $row['penulis'] ?? 'Admin',
                    ':isi' => $row['isi'] ?? ''
                ]);
            }
        } elseif ($key === 'serah_terima') {
            $ids = array_column($data, 'id');
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("DELETE FROM serah_terima WHERE id NOT IN ($in)");
                $stmt->execute($ids);
            } else {
                $db->query("DELETE FROM serah_terima");
            }
            $stmt = $db->prepare("INSERT INTO serah_terima (id, barang_id, barang_nama, penerima, tanggal, petugas, status, catatan) VALUES (:id, :barang_id, :barang_nama, :penerima, :tanggal, :petugas, :status, :catatan) ON DUPLICATE KEY UPDATE barang_id = VALUES(barang_id), barang_nama = VALUES(barang_nama), penerima = VALUES(penerima), tanggal = VALUES(tanggal), petugas = VALUES(petugas), status = VALUES(status), catatan = VALUES(catatan)");
            foreach ($data as $row) {
                $stmt->execute([
                    ':id' => $row['id'],
                    ':barang_id' => $row['barang_id'] ?? '',
                    ':barang_nama' => $row['barang_nama'] ?? '',
                    ':penerima' => $row['penerima'] ?? '',
                    ':tanggal' => $row['tanggal'] ?? date('Y-m-d'),
                    ':petugas' => $row['petugas'] ?? 'Satpam',
                    ':status' => $row['status'] ?? 'Menunggu Penyerahan',
                    ':catatan' => $row['catatan'] ?? ''
                ]);
            }
        }
    } catch (PDOException $e) {
        // Lanjutkan tanpa interupsi
    }
}

/**
 * Cari pengguna berdasarkan username (menggunakan PDO Prepared Statement untuk anti SQL Injection).
 */
function cariUserByUsername($username) {
    $db = getDB();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            if ($user) return $user;
        } catch (PDOException $e) {}
    }

    $users = ambilSemuaData('users');
    foreach ($users as $u) {
        if (strtolower($u['username']) === strtolower($username)) {
            return $u;
        }
    }
    return null;
}

/**
 * Tambah pengguna baru (Anti SQL Injection via Prepared Statements).
 */
function simpanUserBaru($user) {
    $db = getDB();
    if ($db !== null) {
        try {
            $hash = password_hash($user['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, password, nama, role, identitas, kontak) VALUES (:username, :password, :nama, :role, :identitas, :kontak)");
            $stmt->execute([
                ':username' => $user['username'],
                ':password' => $hash,
                ':nama' => $user['nama'],
                ':role' => $user['role'],
                ':identitas' => $user['identitas'] ?? '',
                ':kontak' => $user['kontak'] ?? ''
            ]);
        } catch (PDOException $e) {}
    }

    $users = ambilSemuaData('users');
    $users[] = $user;
    simpanSemuaData('users', $users);
}

/**
 * Update data profil pengguna.
 */
function updateUserProfil($username, $nama, $identitas, $kontak, $passwordBaru = '') {
    $db = getDB();
    if ($db !== null) {
        try {
            if (!empty($passwordBaru)) {
                $hash = password_hash($passwordBaru, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET nama = :nama, identitas = :identitas, kontak = :kontak, password = :password WHERE username = :username");
                $stmt->execute([
                    ':nama' => $nama,
                    ':identitas' => $identitas,
                    ':kontak' => $kontak,
                    ':password' => $hash,
                    ':username' => $username
                ]);
            } else {
                $stmt = $db->prepare("UPDATE users SET nama = :nama, identitas = :identitas, kontak = :kontak WHERE username = :username");
                $stmt->execute([
                    ':nama' => $nama,
                    ':identitas' => $identitas,
                    ':kontak' => $kontak,
                    ':username' => $username
                ]);
            }
        } catch (PDOException $e) {}
    }

    $users = ambilSemuaData('users');
    foreach ($users as &$u) {
        if ($u['username'] === $username) {
            $u['nama'] = $nama;
            $u['identitas'] = $identitas;
            $u['kontak'] = $kontak;
            if (!empty($passwordBaru)) {
                $u['password'] = $passwordBaru;
            }
            break;
        }
    }
    simpanSemuaData('users', $users);
}

/**
 * Hapus akun pengguna.
 */
function hapusUserAkun($username) {
    $db = getDB();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
        } catch (PDOException $e) {}
    }

    $users = ambilSemuaData('users');
    $users = array_filter($users, fn($u) => $u['username'] !== $username);
    simpanSemuaData('users', $users);
}

/**
 * Simpan data barang (Tambah / Update) dengan PDO Prepared Statement.
 */
function simpanBarangData($item, $isEdit = false) {
    $db = getDB();
    if ($db !== null) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE barang SET nama = :nama, kategori = :kategori, status = :status, lokasi = :lokasi, tanggal = :tanggal, deskripsi = :deskripsi, pelapor = :pelapor, kontak = :kontak WHERE id = :id");
                $stmt->execute([
                    ':nama' => $item['nama'],
                    ':kategori' => $item['kategori'],
                    ':status' => $item['status'],
                    ':lokasi' => $item['lokasi'],
                    ':tanggal' => $item['tanggal'],
                    ':deskripsi' => $item['deskripsi'],
                    ':pelapor' => $item['pelapor'],
                    ':kontak' => $item['kontak'],
                    ':id' => $item['id']
                ]);
            } else {
                $stmt = $db->prepare("INSERT INTO barang (id, nama, kategori, status, lokasi, tanggal, deskripsi, pelapor, kontak) VALUES (:id, :nama, :kategori, :status, :lokasi, :tanggal, :deskripsi, :pelapor, :kontak)");
                $stmt->execute([
                    ':id' => $item['id'],
                    ':nama' => $item['nama'],
                    ':kategori' => $item['kategori'],
                    ':status' => $item['status'],
                    ':lokasi' => $item['lokasi'],
                    ':tanggal' => $item['tanggal'],
                    ':deskripsi' => $item['deskripsi'],
                    ':pelapor' => $item['pelapor'],
                    ':kontak' => $item['kontak']
                ]);
            }
        } catch (PDOException $e) {}
    }

    $list = ambilSemuaData('barang');
    if ($isEdit) {
        foreach ($list as &$val) {
            if ($val['id'] === $item['id']) {
                $val = $item;
                break;
            }
        }
    } else {
        array_unshift($list, $item);
    }
    simpanSemuaData('barang', $list);
}

/**
 * Hapus data barang dengan PDO Prepared Statement.
 */
function hapusBarangData($id) {
    $db = getDB();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("DELETE FROM barang WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {}
    }

    $list = ambilSemuaData('barang');
    $list = array_filter($list, fn($b) => $b['id'] !== $id);
    simpanSemuaData('barang', $list);
}

/**
 * Helper class badge - Desain modern, jelas, dan berwarna.
 */
function getBadgeClass($status) {
    if ($status === 'Hilang') return 'badge-hilang';
    if ($status === 'Ditemukan') return 'badge-ditemukan';
    if ($status === 'Sudah Dikembalikan' || $status === 'Selesai' || $status === 'Terbit') return 'badge-kembali';
    if ($status === 'Draft') return 'badge-draft';
    if ($status === 'Satpam') return 'badge-satpam';
    if ($status === 'Mahasiswa') return 'badge-mahasiswa';
    return 'badge-proses';
}

function dapatkanDataAwal($key) {
    if ($key === 'barang') {
        return [
            [
                'id' => 'BRG-001',
                'nama' => 'KTM & Dompet Hitam Kulit',
                'kategori' => 'Dokumen',
                'status' => 'Hilang',
                'lokasi' => 'Gedung Kuliah Bersama (GKB)',
                'tanggal' => '2026-09-27',
                'deskripsi' => 'Dompet lipat warna hitam merk Baellerry berisi KTM atas nama Lintang Arya.',
                'pelapor' => 'Lintang Arya',
                'kontak' => '081234567890'
            ],
            [
                'id' => 'BRG-002',
                'nama' => 'Helm KYT Fullface Abu-abu',
                'kategori' => 'Aksesoris',
                'status' => 'Ditemukan',
                'lokasi' => 'Parkiran Motor Gedung F',
                'tanggal' => '2026-09-28',
                'deskripsi' => 'Ditemukan di atas spion motor Beat, diamankan di pos satpam.',
                'pelapor' => 'Pak Bambang (Satpam)',
                'kontak' => '085678901234'
            ],
            [
                'id' => 'BRG-003',
                'nama' => 'Kalkulator Casio fx-991EX',
                'kategori' => 'Elektronik',
                'status' => 'Hilang',
                'lokasi' => 'Lab Komputer Terpadu',
                'tanggal' => '2026-09-26',
                'deskripsi' => 'Kalkulator tertinggal setelah jam praktikum, ada stiker biru di cover belakang.',
                'pelapor' => 'Rina Sasmita',
                'kontak' => '082345678901'
            ],
            [
                'id' => 'BRG-004',
                'nama' => 'Jaket Hoodie Hitam Polos',
                'kategori' => 'Pakaian',
                'status' => 'Sudah Dikembalikan',
                'lokasi' => 'Perpustakaan Pusat',
                'tanggal' => '2026-09-25',
                'deskripsi' => 'Tertinggal di kursi baca perpus, sudah diambil kembali oleh pemiliknya.',
                'pelapor' => 'Petugas Perpus',
                'kontak' => '087890123456'
            ]
        ];
    } elseif ($key === 'users') {
        return [
            [
                'id' => 1,
                'username' => 'satpam',
                'password' => 'satpam123',
                'nama' => 'Bambang Sutrisno',
                'role' => 'Satpam',
                'identitas' => 'NIP: 198207152008121002',
                'kontak' => '0856-7890-1234'
            ],
            [
                'id' => 2,
                'username' => 'mahasiswa',
                'password' => 'mhs123',
                'nama' => 'Lintang Arya Nugraha',
                'role' => 'Mahasiswa',
                'identitas' => 'NIM: 24050123001',
                'kontak' => '0812-3456-7890'
            ]
        ];
    } elseif ($key === 'kategori') {
        return [
            ['id' => 'KAT-001', 'nama' => 'Elektronik', 'deskripsi' => 'Laptop, HP, charger, flashdisk, mouse, powerbank, earphone'],
            ['id' => 'KAT-002', 'nama' => 'Dokumen', 'deskripsi' => 'KTM, KTP, SIM, STNK, buku tabungan, berkas ijazah/sertifikat'],
            ['id' => 'KAT-003', 'nama' => 'Aksesoris', 'deskripsi' => 'Jam tangan, kacamata, helm, perhiasan, topi, payung'],
            ['id' => 'KAT-004', 'nama' => 'Pakaian', 'deskripsi' => 'Jaket, jas almamater, sweater, hoodie, syal, sepatu'],
            ['id' => 'KAT-005', 'nama' => 'Lainnya', 'deskripsi' => 'Kunci motor/mobil, tas ransel, botol minum/tumbler, binder']
        ];
    } elseif ($key === 'lokasi') {
        return [
            ['id' => 'LOK-001', 'nama' => 'Perpustakaan Pusat', 'area' => 'Gedung Perpustakaan', 'keterangan' => 'Area ruang baca utama dan loker penitipan barang'],
            ['id' => 'LOK-002', 'nama' => 'Gedung Kuliah Bersama (GKB)', 'area' => 'Gedung GKB Lt. 1-4', 'keterangan' => 'Ruang kelas perkuliahan dan koridor gedung'],
            ['id' => 'LOK-003', 'nama' => 'Lab Komputer Terpadu', 'area' => 'Gedung F Lt. 2', 'keterangan' => 'Ruang praktikum komputer 1 sampai 4'],
            ['id' => 'LOK-004', 'nama' => 'Kantin Utama Kampus', 'area' => 'Area Foodcourt', 'keterangan' => 'Area meja makan dan deretan tenant makanan'],
            ['id' => 'LOK-005', 'nama' => 'Parkiran Motor Gedung F', 'area' => 'Area Parkir Barat', 'keterangan' => 'Parkiran kendaraan roda dua dan pos jaga satpam']
        ];
    } elseif ($key === 'pengumuman') {
        return [
            ['id' => 'PGM-001', 'judul' => 'Penemuan Kunci Motor Vario di Parkiran F', 'tgl_buat' => '2026-09-28', 'tgl_publikasi' => '2026-09-28', 'status' => 'Terbit', 'penulis' => 'Bambang (Satpam)', 'isi' => 'Ditemukan 1 set kunci motor Honda Vario dengan gantungan boneka biru di parkiran motor barat. Pemilik dapat mengambilnya di Pos Satpam Utama dengan membawa bukti STNK.'],
            ['id' => 'PGM-002', 'judul' => 'Prosedur Pengambilan Barang Temuan di Pos Satpam', 'tgl_buat' => '2026-09-24', 'tgl_publikasi' => '2026-09-25', 'status' => 'Terbit', 'penulis' => 'Pos Satpam Pusat', 'isi' => 'Pengambilan barang temuan dilayani setiap hari Senin - Jumat pukul 08.00 - 16.00 WIB. Pemilik wajib membawa kartu identitas/KTM serta dapat menjelaskan ciri khusus barang.'],
            ['id' => 'PGM-003', 'judul' => 'Himbauan Menjaga Barang Bawaan di Area Perpustakaan', 'tgl_buat' => '2026-09-20', 'tgl_publikasi' => '2026-09-22', 'status' => 'Draft', 'penulis' => 'Satpam Kampus', 'isi' => 'Dihimbau kepada seluruh civitas akademika agar tidak meninggalkan laptop, dompet, atau gadget di atas meja belajar perpustakaan tanpa pengawasan.']
        ];
    } elseif ($key === 'serah_terima') {
        return [
            ['id' => 'STR-001', 'barang_id' => 'BRG-004', 'barang_nama' => 'Jaket Hoodie Hitam Polos (BRG-004)', 'penerima' => 'Dika Pratama', 'tanggal' => '2026-09-25', 'petugas' => 'Bambang Sutrisno', 'status' => 'Selesai', 'catatan' => 'Penerima menunjukkan foto bukti kepemilikan dan kartu tanda mahasiswa.'],
            ['id' => 'STR-002', 'barang_id' => 'BRG-002', 'barang_nama' => 'Helm KYT Fullface Abu-abu (BRG-002)', 'penerima' => 'Anisa Rahma', 'tanggal' => '2026-09-28', 'petugas' => 'Bambang Sutrisno', 'status' => 'Menunggu Penyerahan', 'catatan' => 'Pemilik telah mengonfirmasi via WA, menunggu jadwal pengambilan di pos satpam.']
        ];
    }
    return [];
}
