<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['status'], $_SESSION['username']) || $_SESSION['status'] !== 'login') {
    header('Location: ../login.php');
    exit;
}
?>
