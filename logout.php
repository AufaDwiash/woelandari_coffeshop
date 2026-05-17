<?php
// logout.php - letakkan di root folder (woelandari/logout.php)
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Hapus cookie remember me
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}
if (isset($_COOKIE['last_user'])) {
    setcookie('last_user', '', time() - 3600, '/');
}

// Redirect ke halaman login (di root)
header("Location: login.php");
exit;
?>