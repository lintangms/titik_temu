<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Barang;
use App\Models\Pengumuman;
use App\Models\SerahTerima;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Default
        User::updateOrCreate(
            ['username' => 'satpam'],
            [
                'password' => Hash::make('satpam123'),
                'nama' => 'Bambang Sutrisno',
                'role' => 'Satpam',
                'identitas' => 'NIP: 198207152008121002',
                'kontak' => '0856-7890-1234',
            ]
        );

        User::updateOrCreate(
            ['username' => 'mahasiswa'],
            [
                'password' => Hash::make('mhs123'),
                'nama' => 'Lintang Arya Nugraha',
                'role' => 'Mahasiswa',
                'identitas' => 'NIM: 24050123001',
                'kontak' => '0812-3456-7890',
            ]
        );

        // 2. Kategori Default
        $kategori = [
            ['id' => 'KAT-001', 'nama' => 'Elektronik', 'deskripsi' => 'Laptop, HP, charger, flashdisk, mouse, powerbank, earphone'],
            ['id' => 'KAT-002', 'nama' => 'Dokumen', 'deskripsi' => 'KTM, KTP, SIM, STNK, buku tabungan, berkas ijazah/sertifikat'],
            ['id' => 'KAT-003', 'nama' => 'Aksesoris', 'deskripsi' => 'Jam tangan, kacamata, helm, perhiasan, topi, payung'],
            ['id' => 'KAT-004', 'nama' => 'Pakaian', 'deskripsi' => 'Jaket, jas almamater, sweater, hoodie, syal, sepatu'],
            ['id' => 'KAT-005', 'nama' => 'Lainnya', 'deskripsi' => 'Kunci motor/mobil, tas ransel, botol minum/tumbler, binder'],
        ];
        foreach ($kategori as $k) {
            Kategori::updateOrCreate(['id' => $k['id']], $k);
        }

        // 3. Lokasi Default
        $lokasi = [
            ['id' => 'LOK-001', 'nama' => 'Perpustakaan Pusat', 'area' => 'Gedung Perpustakaan', 'keterangan' => 'Area ruang baca utama dan loker penitipan barang'],
            ['id' => 'LOK-002', 'nama' => 'Gedung Kuliah Bersama (GKB)', 'area' => 'Gedung GKB Lt. 1-4', 'keterangan' => 'Ruang kelas perkuliahan dan koridor gedung'],
            ['id' => 'LOK-003', 'nama' => 'Lab Komputer Terpadu', 'area' => 'Gedung F Lt. 2', 'keterangan' => 'Ruang praktikum komputer 1 sampai 4'],
            ['id' => 'LOK-004', 'nama' => 'Kantin Utama Kampus', 'area' => 'Area Foodcourt', 'keterangan' => 'Area meja makan dan deretan tenant makanan'],
            ['id' => 'LOK-005', 'nama' => 'Parkiran Motor Gedung F', 'area' => 'Area Parkir Barat', 'keterangan' => 'Parkiran kendaraan roda dua dan pos jaga satpam'],
        ];
        foreach ($lokasi as $l) {
            Lokasi::updateOrCreate(['id' => $l['id']], $l);
        }

        // 4. Barang Default
        $barang = [
            [
                'id' => 'BRG-001',
                'nama' => 'KTM & Dompet Hitam Kulit',
                'kategori' => 'Dokumen',
                'status' => 'Hilang',
                'lokasi' => 'Gedung Kuliah Bersama (GKB)',
                'tanggal' => '2026-09-27',
                'deskripsi' => 'Dompet lipat warna hitam merk Baellerry berisi KTM atas nama Lintang Arya.',
                'pelapor' => 'Lintang Arya',
                'kontak' => '081234567890',
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
                'kontak' => '085678901234',
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
                'kontak' => '082345678901',
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
                'kontak' => '087890123456',
            ],
        ];
        foreach ($barang as $b) {
            Barang::updateOrCreate(['id' => $b['id']], $b);
        }

        // 5. Pengumuman Default
        $pengumuman = [
            [
                'id' => 'PGM-001',
                'judul' => 'Penemuan Kunci Motor Vario di Parkiran F',
                'tgl_buat' => '2026-09-28',
                'tgl_publikasi' => '2026-09-28',
                'status' => 'Terbit',
                'penulis' => 'Bambang (Satpam)',
                'isi' => 'Ditemukan 1 set kunci motor Honda Vario dengan gantungan boneka biru di parkiran motor barat. Pemilik dapat mengambilnya di Pos Satpam Utama dengan membawa bukti STNK.',
            ],
            [
                'id' => 'PGM-002',
                'judul' => 'Prosedur Pengambilan Barang Temuan di Pos Satpam',
                'tgl_buat' => '2026-09-24',
                'tgl_publikasi' => '2026-09-25',
                'status' => 'Terbit',
                'penulis' => 'Pos Satpam Pusat',
                'isi' => 'Pengambilan barang temuan dilayani setiap hari Senin - Jumat pukul 08.00 - 16.00 WIB. Pemilik wajib membawa kartu identitas/KTM serta dapat menjelaskan ciri khusus barang.',
            ],
            [
                'id' => 'PGM-003',
                'judul' => 'Himbauan Menjaga Barang Bawaan di Area Perpustakaan',
                'tgl_buat' => '2026-09-20',
                'tgl_publikasi' => '2026-09-22',
                'status' => 'Draft',
                'penulis' => 'Satpam Kampus',
                'isi' => 'Dihimbau kepada seluruh civitas akademika agar tidak meninggalkan laptop, dompet, atau gadget di atas meja belajar perpustakaan tanpa pengawasan.',
            ],
        ];
        foreach ($pengumuman as $p) {
            Pengumuman::updateOrCreate(['id' => $p['id']], $p);
        }

        // 6. Serah Terima Default
        $serahTerima = [
            [
                'id' => 'STR-001',
                'barang_id' => 'BRG-004',
                'barang_nama' => 'Jaket Hoodie Hitam Polos (BRG-004)',
                'penerima' => 'Dika Pratama',
                'tanggal' => '2026-09-25',
                'petugas' => 'Bambang Sutrisno',
                'status' => 'Selesai',
                'catatan' => 'Penerima menunjukkan foto bukti kepemilikan dan kartu tanda mahasiswa.',
            ],
            [
                'id' => 'STR-002',
                'barang_id' => 'BRG-002',
                'barang_nama' => 'Helm KYT Fullface Abu-abu (BRG-002)',
                'penerima' => 'Anisa Rahma',
                'tanggal' => '2026-09-28',
                'petugas' => 'Bambang Sutrisno',
                'status' => 'Menunggu Penyerahan',
                'catatan' => 'Pemilik telah mengonfirmasi via WA, menunggu jadwal pengambilan di pos satpam.',
            ],
        ];
        foreach ($serahTerima as $st) {
            SerahTerima::updateOrCreate(['id' => $st['id']], $st);
        }
    }
}
