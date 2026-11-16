<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) && isset($_COOKIE['titiktemu_session'])) {
    $cookieData = json_decode($_COOKIE['titiktemu_session'], true);
    if ($cookieData && isset($cookieData['username'])) {
        $_SESSION['user'] = $cookieData;
    }
}

if (isset($_SESSION['user']) && !empty($_SESSION['user']['username'])) {
    if ($_SESSION['user']['role'] === 'Satpam') {
        header('Location: dashboard.php');
    } else {
        header('Location: mahasiswa.php');
    }
    exit();
} else {
    header('Location: login.php');
    exit();
}
