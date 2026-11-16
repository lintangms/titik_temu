<?php
// ==========================================================
// AUTH CHECK & STATE KEAMANAN HALAMAN TERPROTEKSI
// 1. Mencegah akses via URL langsung tanpa login
// 2. Mencegah akses kembali setelah logout (termasuk lewat tombol back)
// ==========================================================

// Header anti-cache ketat agar browser tidak menyimpan cache halaman terproteksi di history back/forward
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Remember Me via Cookie jika sesi habis
if (!isset($_SESSION['user']) && isset($_COOKIE['titiktemu_session'])) {
    $cookieData = json_decode($_COOKIE['titiktemu_session'], true);
    if ($cookieData && isset($cookieData['username'])) {
        $_SESSION['user'] = $cookieData;
    }
}

// Jika pengguna belum terautentikasi, tolak akses dan arahkan ke halaman login
if (!isset($_SESSION['user']) || empty($_SESSION['user']['username'])) {
    header('Location: login.php?pesan=belum_login');
    exit();
}

$currentUser = $_SESSION['user'];
