<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Kategori
        Schema::create('kategori', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Lokasi
        Schema::create('lokasi', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama', 100);
            $table->string('area', 100);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Barang (Modul Inti CRUD)
        Schema::create('barang', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama', 150);
            $table->string('kategori', 100);
            $table->enum('status', ['Hilang', 'Ditemukan', 'Sudah Dikembalikan'])->default('Hilang');
            $table->string('lokasi', 150);
            $table->date('tanggal');
            $table->text('deskripsi')->nullable();
            $table->string('pelapor', 100);
            $table->string('kontak', 50);
            $table->timestamps();
        });

        // 4. Tabel Pengumuman
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('judul', 200);
            $table->date('tgl_buat');
            $table->date('tgl_publikasi');
            $table->enum('status', ['Draft', 'Terbit'])->default('Draft');
            $table->string('penulis', 100);
            $table->text('isi')->nullable();
            $table->timestamps();
        });

        // 5. Tabel Serah Terima
        Schema::create('serah_terima', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('barang_id', 20);
            $table->string('barang_nama', 150);
            $table->string('penerima', 100);
            $table->date('tanggal');
            $table->string('petugas', 100);
            $table->string('status', 50)->default('Menunggu Penyerahan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serah_terima');
        Schema::dropIfExists('pengumuman');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('lokasi');
        Schema::dropIfExists('kategori');
    }
};
