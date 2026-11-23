<?php
// ==========================================================
// PROSES LOGOUT (BERAKHIRNYA SESI PENGGUNA)
// Menjamin sesi sepenuhnya musnah dan tidak dapat diakses kembali
// ==========================================================

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Hapus seluruh isi sesi
$_SESSION = [];

// 2. Hapus cookie session ID dari browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi di server
session_unset();
session_destroy();

// 4. Hapus cookie remember me (titiktemu_session)
if (isset($_COOKIE['titiktemu_session'])) {
    setcookie('titiktemu_session', '', time() - 3600, '/');
}

// 5. Arahkan kembali ke halaman login dengan query parameter sukses
header('Location: login.php?pesan=logout_sukses');
exit();
