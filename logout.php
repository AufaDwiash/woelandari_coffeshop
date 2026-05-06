<?php
session_start();
session_unset();
session_destroy();

// Karena sudah di luar folder admin, langsung panggil login.php
header("Location: login.php");
exit;
?>