<?php
if (defined('SEKOLAH_KONEKSI_LOADED')) {
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        $conn = $GLOBALS['conn'];
    }
    return;
}
define('SEKOLAH_KONEKSI_LOADED', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_sekolah';

if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof mysqli)) {
    $GLOBALS['conn'] = mysqli_connect($host, $user, $pass, $db);
    if (!$GLOBALS['conn']) {
        die('Koneksi ke database gagal: ' . mysqli_connect_error());
    }
}
$conn = $GLOBALS['conn'];

if (!defined('BASE_PATH')) {
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    define('BASE_PATH', $base === '/' ? '' : rtrim($base, '/'));
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        return (BASE_PATH === '' ? '' : BASE_PATH) . '/' . $path;
    }
}

if (!function_exists('db')) {
    /** Koneksi mysqli (aman dipanggil dari halaman yang di-include router). */
    function db(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = $GLOBALS['conn'] ?? null;
        }
        if (!$conn instanceof mysqli) {
            throw new RuntimeException('Koneksi database belum tersedia.');
        }
        return $conn;
    }
}
