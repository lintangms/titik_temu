<?php
// ==========================================================
// KONEKSI DATABASE (PDO - PHP DATA OBJECTS)
// Mencegah SQL Injection menggunakan Prepared Statements
// ==========================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'titiktemu';

$pdo = null;

try {
    $dsn = "mysql:host={$db_host};port=3306;dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Menggunakan prepared statement native server
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    try {
        // Coba koneksi menggunakan 127.0.0.1 jika localhost bermasalah pada resolusi IPv6 Windows
        $dsnFallback = "mysql:host=127.0.0.1;port=3306;dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsnFallback, $db_user, $db_pass, $options);
    } catch (PDOException $e2) {
        // Jika koneksi MySQL belum aktif, pdo tetap null (aplikasi beralih fallback dengan mulus)
        $pdo = null;
    }
}

function getDB() {
    global $pdo;
    return $pdo;
}

function getDbConnection() {
    return getDB();
}
