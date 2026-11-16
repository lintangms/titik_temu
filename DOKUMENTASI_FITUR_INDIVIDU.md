# DOKUMENTASI FITUR INDIVIDU & PANDUAN PENGUJIAN
## Sistem Informasi Pencarian dan Penemuan Barang Hilang (TitikTemu)
**Mata Kuliah:** Pemrograman Web  
**Fitur Individu:** Modul CRUD Kelola Data Barang (`barang.php`)  
**Penulis:** Mahasiswa Pengembang Fitur Individu  

---

## 1. Peran Fitur dalam Aplikasi Secara Keseluruhan (Bobot: 2 Poin)

Modul **Kelola Data Barang (`barang.php`)** merupakan **jantung dan modul inti (*core engine*)** dari Sistem Informasi TitikTemu. 

### Peran dan Keterhubungan dengan Fitur Lain:
1. **Pusat Aliran Data (*Data Hub*):**
   - Menampung seluruh laporan kehilangan dan barang temuan dari civitas akademika (Mahasiswa) yang masuk melalui `mahasiswa.php`.
   - Mengelola siklus hidup (*lifecycle*) barang dari status **Hilang** &rarr; **Ditemukan** &rarr; hingga **Sudah Dikembalikan**.
2. **Integrasi dengan Modul Dashboard (`dashboard.php`):**
   - Menyuplai metrik data statistik secara langsung (*real-time*): Total Laporan, Barang Hilang, Barang Ditemukan, dan Barang Sudah Dikembalikan.
   - Menampilkan daftar 5 barang terbaru di ringkasan dashboard.
3. **Integrasi dengan Master Data Kategori (`kategori.php`) & Lokasi (`lokasi.php`):**
   - Menggunakan relasi data referensi kategori dan lokasi agar pencatatan barang terstandarisasi di lingkungan kampus.
4. **Integrasi dengan Modul Serah Terima (`serah-terima.php`):**
   - Menjadi sumber referensi saat petugas membuat berita acara serah terima pengembalian barang kepada pemilik aslinya.

---

## 2. Alur Fitur: Tampilan &rarr; Proses Server &rarr; Database (Bobot: 3 Poin)

Fitur ini mengimplementasikan siklus lengkap **CRUD (Create, Read, Update, Delete)** dengan arsitektur bersih:

```
[ Antarmuka Browser (View) ]
           │
           ▼ (HTTP POST / GET Request)
[ Server PHP Engine (Controller & Validation) ]
  ├── 1. Validasi Autentikasi & State Sesi (auth_check.php)
  ├── 2. Sanitasi & Validasi Input (Format, Nilai Wajar, Kelengkapan)
  └── 3. Pembuatan Parameterized Query (PDO Prepared Statements)
           │
           ▼
[ Lapisan Data (Model / Database: MySQL & JSON Fallback) ]
  ├── Eksekusi Query Aman (INSERT, SELECT, UPDATE, DELETE)
  └── Sinkronisasi Cache Penyimpanan
           │
           ▼
[ Response Browser ]
  └── Render Data dengan Sanitasi Anti-XSS (htmlspecialchars) & Notifikasi
```

### A. Alur Tambah Data (Create)
1. **Tampilan (View):** Pengguna mengklik tombol `+ Tambah Data Barang`, membuka modal dialog interaktif `#modal-form`.
2. **Proses Server (PHP):**
   - Memeriksa otentikasi sesi melalui `auth_check.php`.
   - Memvalidasi seluruh field bertanda bintang (*).
   - Memastikan nama barang minimal 3 karakter.
   - Memastikan kategori dan lokasi cocok dengan data master yang valid.
   - Memastikan format tanggal `YYYY-MM-DD` dan berada pada rentang tahun wajar (tahun 2000 hingga sekarang/tahun depan).
   - Memastikan kontak valid format nomor telepon (minimal 9 digit angka).
   - Menghasilkan ID unik terstruktur otomatis (`BRG-001`, `BRG-002`, dst.).
3. **Database:** Menjalankan perintah SQL melalui PDO Prepared Statement:
   ```sql
   INSERT INTO barang (id, nama, kategori, status, lokasi, tanggal, deskripsi, pelapor, kontak) 
   VALUES (:id, :nama, :kategori, :status, :lokasi, :tanggal, :deskripsi, :pelapor, :kontak);
   ```
4. **Redirect & Feedback:** Mengarahkan kembali ke `barang.php?pesan=simpan_sukses` dan menampilkan alert sukses berwarna tenang.

### B. Alur Lihat Data (Read)
1. **Proses Server (PHP):** Menjalankan query `SELECT * FROM barang ORDER BY id DESC`.
2. **Tampilan (View):** 
   - Merender data ke dalam tabel responsif.
   - Semua string dibungkus fungsi `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` untuk proteksi XSS.
   - Dilengkapi fitur pencarian langsung (*instant search*) menggunakan JavaScript `filterTabel()`.
   - Tombol **Detail** memicu `#modal-detail` untuk menampilkan seluruh atribut lengkap barang.

### C. Alur Ubah Data (Update)
1. **Tampilan (View):** Pengguna mengklik tombol **Edit** pada baris barang. Fungsi `bukaModalEdit()` secara instan mengisi data lama barang ke dalam form modal.
2. **Proses Server (PHP):** Menerima data via metode `POST`, melakukan validasi kelayakan data yang sama seperti alur Tambah Data.
3. **Database:** Menjalankan query pembaruan:
   ```sql
   UPDATE barang 
   SET nama = :nama, kategori = :kategori, status = :status, lokasi = :lokasi, 
       tanggal = :tanggal, deskripsi = :deskripsi, pelapor = :pelapor, kontak = :kontak 
   WHERE id = :id;
   ```
4. **Redirect & Feedback:** Kembali ke halaman barang dengan alert informasi berhasil diperbarui.

### D. Alur Hapus Data (Delete)
1. **Tampilan (View):** Pengguna mengklik tombol **Hapus**. Sistem memunculkan **Modal Konfirmasi Interaktif** (`#modal-hapus-barang`) yang secara spesifik menampilkan nama dan ID barang yang akan dihapus.
2. **Proses Server (PHP):** Menerima parameter `aksi=hapus&id=BRG-xxx` via `GET`.
3. **Database:** Menjalankan query penghapusan:
   ```sql
   DELETE FROM barang WHERE id = :id;
   ```
4. **Redirect & Feedback:** Dialihkan ke `barang.php?pesan=hapus_sukses`.

---

## 3. Aspek State dan Keamanan (Bobot: 18 Poin)

| Aspek Keamanan | Implementasi Kode | Bukti & Mekanisme Pengujian |
| :--- | :--- | :--- |
| **Proteksi URL Langsung** | `auth_check.php` di baris teratas setiap halaman terproteksi. | Jika URL `barang.php` dibuka langsung di tab baru (Incognito) tanpa login, server langsung menolak dan me-redirect ke `login.php?pesan=belum_login`. |
| **Proteksi Tombol Back Setelah Logout** | Header HTTP `Cache-Control: no-store, no-cache, must-revalidate` + listener event JS `pageshow`. | Setelah menekan Logout, menekan tombol Back di browser akan memicu reload otomatis ke server. Sesi yang telah kosong membuat sistem melempar browser ke login. |
| **Anti SQL Injection** | PDO Prepared Statements dengan parameter binding (`:nama`, `:id`, dll). | Input injeksi seperti `' OR '1'='1` atau `'; DROP TABLE barang; --` diperlakukan murni sebagai teks biasa, tanpa pernah mengeksekusi struktur sintaks SQL. |
| **Anti XSS (Cross-Site Scripting)** | Seluruh data output dirender menggunakan `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. | Input script seperti `<script>alert('XSS')</script>` akan diubah menjadi entitas HTML `&lt;script&gt;...` dan ditampilkan sebagai teks biasa di tabel maupun modal, tidak pernah dieksekusi. |
| **Validasi & Error Handling** | Validasi di sisi server (PHP) dan client-side HTML5. | Input kosong, nama < 3 karakter, format tanggal salah, atau nomor HP tidak valid ditolak dan memunculkan pesan kesalahan yang spesifik. |

---

## 4. Desain & Antarmuka Responsif (Bobot: 14 Poin)
- **Tema Visual:** Minimalis, rapi, dan elegan sesuai instruksi. Menggunakan warna dasar netral (*slate gray*) tanpa warna-warna neon yang mencolok/ngejreng.
- **Label / Badges:** Didesain dengan warna lembut dan tenang (`.badge-hilang`, `.badge-ditemukan`, `.badge-kembali`) tanpa gradien menyilaukan.
- **Responsivitas:** Mendukung layar desktop, tablet, hingga smartphone melalui CSS media query (`@media (max-width: 768px)`), dilengkapi tombol menu hamburger untuk perangkat mobile.

---

## 5. CHEAT SHEET MODIFIKASI LANGSUNG SAAT UJIAN / DEMO DOSEN (Bobot: 11 Poin)

Bagian ini disiapkan khusus agar mahasiswa dapat menjawab dan memodifikasi kode secara langsung di depan Dosen Penguji dengan cepat, tepat, dan tanpa merusak fitur lainnya.

### Skenario A: Dosen meminta menambah kolom baru, misalnya "Nomor Seri / IMEI / Ciri Khusus Baru"
1. **Buka file `barang.php`:**
   - Tambahkan input pada formulir modal tambah/edit (sekitar baris 190):
     ```html
     <div class="form-group">
         <label for="form-seri">Nomor Seri / Kode Unik</label>
         <input type="text" id="form-seri" name="nomor_seri" placeholder="Contoh: SN-998234">
     </div>
     ```
   - Tambahkan tangkapan POST di PHP (sekitar baris 35):
     ```php
     $nomor_seri = trim($_POST['nomor_seri'] ?? '');
     ```
   - Masukkan `$nomor_seri` ke dalam array `$itemData = compact(..., 'nomor_seri');`.
   - Tambahkan kolom `<th>Nomor Seri</th>` dan `<td><?= htmlspecialchars($item['nomor_seri'] ?? '-') ?></td>` pada tabel data barang.

### Skenario B: Dosen meminta mengubah batasan validasi (misal nama barang minimal 5 karakter)
1. **Buka file `barang.php` pada baris validasi (sekitar baris 48):**
   ```php
   // Ubah dari 3 menjadi 5 karakter:
   elseif (mb_strlen($nama) < 5) {
       $pesanError = 'Gagal menyimpan: Nama barang minimal harus terdiri dari 5 karakter.';
   }
   ```

### Skenario C: Dosen meminta menambah status baru (misal status "Dalam Investigasi")
1. **Buka file `barang.php`:**
   - Di daftar validasi (sekitar baris 40), tambahkan status baru:
     ```php
     $statusValid = ['Hilang', 'Ditemukan', 'Sudah Dikembalikan', 'Dalam Investigasi'];
     ```
   - Di elemen `<select id="form-status" name="status">`, tambahkan:
     ```html
     <option value="Dalam Investigasi">Dalam Investigasi</option>
     ```

### Skenario D: Dosen meminta mengubah tombol aksi atau dialog konfirmasi hapus
1. **Buka file `barang.php` pada bagian JavaScript di baris paling bawah:**
   - Fungsi `konfirmasiHapusBarang(id, nama)` mengatur isi teks judul, deskripsi, dan URL tujuan penghapusan.
   - Semua penanganan modal bersifat modular sehingga sangat aman untuk diubah tanpa mengganggu proses server lainnya.
