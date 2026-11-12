<?php
session_start();

// Hapus semua data session yang tersimpan
$_SESSION = [];
session_unset();
session_destroy();

// Tendang kembali ke halaman login setelah sukses logout
header("Location: login.php");
exit;