<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\SerahTerimaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - TitikTemu (Laravel)
|--------------------------------------------------------------------------
*/

// Root Redirect
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'Satpam'
            ? redirect()->route('dashboard')
            : redirect()->route('mahasiswa.index');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Shared Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/profil', [AuthController::class, 'profile'])->name('profil.index');
    Route::post('/profil', [AuthController::class, 'updateProfile'])->name('profil.update');
});

// Satpam / Petugas Portal (CRUD Admin)
Route::middleware(['auth', 'role:Satpam'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Utama CRUD Kelola Data Barang
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::get('/barang/{id}', [BarangController::class, 'show'])->name('barang.show');
    Route::post('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
    Route::get('/barang/hapus/{id}', [BarangController::class, 'destroy'])->name('barang.destroy.get');

    // Kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::post('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    Route::get('/kategori/hapus/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy.get');

    // Lokasi
    Route::get('/lokasi', [LokasiController::class, 'index'])->name('lokasi.index');
    Route::post('/lokasi', [LokasiController::class, 'store'])->name('lokasi.store');
    Route::post('/lokasi/{id}', [LokasiController::class, 'update'])->name('lokasi.update');
    Route::delete('/lokasi/{id}', [LokasiController::class, 'destroy'])->name('lokasi.destroy');
    Route::get('/lokasi/hapus/{id}', [LokasiController::class, 'destroy'])->name('lokasi.destroy.get');

    // Pengumuman
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::post('/pengumuman/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    Route::get('/pengumuman/hapus/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy.get');

    // Serah Terima
    Route::get('/serah-terima', [SerahTerimaController::class, 'index'])->name('serah-terima.index');
    Route::post('/serah-terima', [SerahTerimaController::class, 'store'])->name('serah-terima.store');
    Route::post('/serah-terima/{id}', [SerahTerimaController::class, 'update'])->name('serah-terima.update');
    Route::delete('/serah-terima/{id}', [SerahTerimaController::class, 'destroy'])->name('serah-terima.destroy');
    Route::get('/serah-terima/hapus/{id}', [SerahTerimaController::class, 'destroy'])->name('serah-terima.destroy.get');
});

// Mahasiswa Portal
Route::middleware(['auth', 'role:Mahasiswa'])->group(function () {
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
});

// Legacy .php URL redirects for compatibility
Route::get('/login.php', fn() => redirect()->route('login'));
Route::post('/login.php', [AuthController::class, 'login']);
Route::get('/logout.php', fn() => redirect()->route('logout'));
Route::get('/dashboard.php', fn() => redirect()->route('dashboard'));
Route::get('/barang.php', fn(Request $r) => redirect()->route('barang.index'));
Route::post('/barang.php', [BarangController::class, 'store']);
Route::get('/kategori.php', fn() => redirect()->route('kategori.index'));
Route::get('/lokasi.php', fn() => redirect()->route('lokasi.index'));
Route::get('/pengumuman.php', fn() => redirect()->route('pengumuman.index'));
Route::get('/serah-terima.php', fn() => redirect()->route('serah-terima.index'));
Route::get('/profil.php', fn() => redirect()->route('profil.index'));
Route::get('/mahasiswa.php', fn() => redirect()->route('mahasiswa.index'));
Route::post('/mahasiswa.php', [MahasiswaController::class, 'store']);
Route::get('/register.php', fn() => redirect()->route('register'));
