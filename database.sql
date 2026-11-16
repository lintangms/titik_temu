-- ==========================================================
-- DATABASE SISTEM INFORMASI BARANG HILANG "TITIKTEMU"
-- Universitas / Proyek Pemrograman Web
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `titiktemu` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `titiktemu`;

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `role` ENUM('Satpam', 'Mahasiswa') NOT NULL DEFAULT 'Mahasiswa',
    `identitas` VARCHAR(100) NULL,
    `kontak` VARCHAR(30) NULL,
    `foto` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Kategori
CREATE TABLE IF NOT EXISTS `kategori` (
    `id` VARCHAR(20) PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `deskripsi` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Lokasi
CREATE TABLE IF NOT EXISTS `lokasi` (
    `id` VARCHAR(20) PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `area` VARCHAR(100) NOT NULL,
    `keterangan` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Barang (Fitur CRUD Utama Individu)
CREATE TABLE IF NOT EXISTS `barang` (
    `id` VARCHAR(20) PRIMARY KEY,
    `nama` VARCHAR(150) NOT NULL,
    `kategori` VARCHAR(100) NOT NULL,
    `status` ENUM('Hilang', 'Ditemukan', 'Sudah Dikembalikan') NOT NULL DEFAULT 'Hilang',
    `lokasi` VARCHAR(150) NOT NULL,
    `tanggal` DATE NOT NULL,
    `deskripsi` TEXT NULL,
    `pelapor` VARCHAR(100) NOT NULL,
    `kontak` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pengumuman
CREATE TABLE IF NOT EXISTS `pengumuman` (
    `id` VARCHAR(20) PRIMARY KEY,
    `judul` VARCHAR(200) NOT NULL,
    `tgl_buat` DATE NOT NULL,
    `tgl_publikasi` DATE NOT NULL,
    `status` ENUM('Draft', 'Terbit') NOT NULL DEFAULT 'Draft',
    `penulis` VARCHAR(100) NOT NULL,
    `isi` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Serah Terima
CREATE TABLE IF NOT EXISTS `serah_terima` (
    `id` VARCHAR(20) PRIMARY KEY,
    `barang_id` VARCHAR(20) NOT NULL,
    `barang_nama` VARCHAR(150) NOT NULL,
    `penerima` VARCHAR(100) NOT NULL,
    `tanggal` DATE NOT NULL,
    `petugas` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Menunggu Penyerahan',
    `catatan` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- DATA AWAL / SEEDER
-- ==========================================================

-- Data Akun Default (Password: satpam123 dan mhs123)
-- Hash bcrypt menggunakan PASSWORD_BCRYPT
INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `identitas`, `kontak`) VALUES
(1, 'satpam', '$2y$10$mO7gYWZFBB1vMlxMArjn2efTNmPaY8m0Y2UoUOt7svO3v.9iI4l6a', 'Bambang Sutrisno', 'Satpam', 'NIP: 198207152008121002', '0856-7890-1234'),
(2, 'mahasiswa', '$2y$10$PhStJ8jNnnx5VQ6E91FD8eMggROvWsiNEYNxLNtPJaFsqoB1Hndnu', 'Lintang Arya Nugraha', 'Mahasiswa', 'NIM: 24050123001', '0812-3456-7890')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Data Kategori Default
INSERT INTO `kategori` (`id`, `nama`, `deskripsi`) VALUES
('KAT-001', 'Elektronik', 'Laptop, HP, charger, flashdisk, mouse, powerbank, earphone'),
('KAT-002', 'Dokumen', 'KTM, KTP, SIM, STNK, buku tabungan, berkas ijazah/sertifikat'),
('KAT-003', 'Aksesoris', 'Jam tangan, kacamata, helm, perhiasan, topi, payung'),
('KAT-004', 'Pakaian', 'Jaket, jas almamater, sweater, hoodie, syal, sepatu'),
('KAT-005', 'Lainnya', 'Kunci motor/mobil, tas ransel, botol minum/tumbler, binder')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Data Lokasi Default
INSERT INTO `lokasi` (`id`, `nama`, `area`, `keterangan`) VALUES
('LOK-001', 'Perpustakaan Pusat', 'Gedung Perpustakaan', 'Area ruang baca utama dan loker penitipan barang'),
('LOK-002', 'Gedung Kuliah Bersama (GKB)', 'Gedung GKB Lt. 1-4', 'Ruang kelas perkuliahan dan koridor gedung'),
('LOK-003', 'Lab Komputer Terpadu', 'Gedung F Lt. 2', 'Ruang praktikum komputer 1 sampai 4'),
('LOK-004', 'Kantin Utama Kampus', 'Area Foodcourt', 'Area meja makan dan deretan tenant makanan'),
('LOK-005', 'Parkiran Motor Gedung F', 'Area Parkir Barat', 'Parkiran kendaraan roda dua dan pos jaga satpam')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Data Barang Default
INSERT INTO `barang` (`id`, `nama`, `kategori`, `status`, `lokasi`, `tanggal`, `deskripsi`, `pelapor`, `kontak`) VALUES
('BRG-001', 'KTM & Dompet Hitam Kulit', 'Dokumen', 'Hilang', 'Gedung Kuliah Bersama (GKB)', '2026-09-27', 'Dompet lipat warna hitam merk Baellerry berisi KTM atas nama Lintang Arya.', 'Lintang Arya', '081234567890'),
('BRG-002', 'Helm KYT Fullface Abu-abu', 'Aksesoris', 'Ditemukan', 'Parkiran Motor Gedung F', '2026-09-28', 'Ditemukan di atas spion motor Beat, diamankan di pos satpam.', 'Pak Bambang (Satpam)', '085678901234'),
('BRG-003', 'Kalkulator Casio fx-991EX', 'Elektronik', 'Hilang', 'Lab Komputer Terpadu', '2026-09-26', 'Kalkulator tertinggal setelah jam praktikum, ada stiker biru di cover belakang.', 'Rina Sasmita', '082345678901'),
('BRG-004', 'Jaket Hoodie Hitam Polos', 'Pakaian', 'Sudah Dikembalikan', 'Perpustakaan Pusat', '2026-09-25', 'Tertinggal di kursi baca perpus, sudah diambil kembali oleh pemiliknya.', 'Petugas Perpus', '087890123456')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Data Pengumuman Default
INSERT INTO `pengumuman` (`id`, `judul`, `tgl_buat`, `tgl_publikasi`, `status`, `penulis`, `isi`) VALUES
('PGM-001', 'Penemuan Kunci Motor Vario di Parkiran F', '2026-09-28', '2026-09-28', 'Terbit', 'Bambang (Satpam)', 'Ditemukan 1 set kunci motor Honda Vario dengan gantungan boneka biru di parkiran motor barat. Pemilik dapat mengambilnya di Pos Satpam Utama dengan membawa bukti STNK.'),
('PGM-002', 'Prosedur Pengambilan Barang Temuan di Pos Satpam', '2026-09-24', '2026-09-25', 'Terbit', 'Pos Satpam Pusat', 'Pengambilan barang temuan dilayani setiap hari Senin - Jumat pukul 08.00 - 16.00 WIB. Pemilik wajib membawa kartu identitas/KTM serta dapat menjelaskan ciri khusus barang.'),
('PGM-003', 'Himbauan Menjaga Barang Bawaan di Area Perpustakaan', '2026-09-20', '2026-09-22', 'Draft', 'Satpam Kampus', 'Dihimbau kepada seluruh civitas akademika agar tidak meninggalkan laptop, dompet, atau gadget di atas meja belajar perpustakaan tanpa pengawasan.')
ON DUPLICATE KEY UPDATE `judul` = VALUES(`judul`);

-- Data Serah Terima Default
INSERT INTO `serah_terima` (`id`, `barang_id`, `barang_nama`, `penerima`, `tanggal`, `petugas`, `status`, `catatan`) VALUES
('STR-001', 'BRG-004', 'Jaket Hoodie Hitam Polos (BRG-004)', 'Dika Pratama', '2026-09-25', 'Bambang Sutrisno', 'Selesai', 'Penerima menunjukkan foto bukti kepemilikan dan kartu tanda mahasiswa.'),
('STR-002', 'BRG-002', 'Helm KYT Fullface Abu-abu (BRG-002)', 'Anisa Rahma', '2026-09-28', 'Bambang Sutrisno', 'Menunggu Penyerahan', 'Pemilik telah mengonfirmasi via WA, menunggu jadwal pengambilan di pos satpam.')
ON DUPLICATE KEY UPDATE `penerima` = VALUES(`penerima`);
