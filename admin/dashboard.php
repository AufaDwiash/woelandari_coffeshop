<?php
require_once __DIR__ . '/auth.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'guest';
setcookie('last_user', $username, time() + 3600, '/');
$last_user = $_COOKIE['last_user'] ?? $username;

$jml_menu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu"))['total'] ?? 0;
$jml_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM penjualan"))['total'] ?? 0;
$jml_event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM events"))['total'] ?? 0;
$jml_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM feedback"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Woelandari</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
</head>
<body>
<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
    <nav class="nav-list">
        <a href="dashboard.php" class="nav-item active"><span>Dashboard</span></a>
        <a href="menu_crud.php" class="nav-item"><span>Menu</span></a>
        <a href="gallery_crud.php" class="nav-item"><span>Gallery</span></a>
        <a href="feedback.php" class="nav-item"><span>Feedback</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>Kelola User</span></a>
        <a href="../logout.php" class="nav-item"><span>Logout</span></a>
    </nav>
</aside>

<main class="main-content">
    <header class="page-header">
        <div class="header-title">
            <div class="header-sub">// LIVE_MONITORING</div>
            <h1>ADMIN DASHBOARD</h1>
        </div>
        <div class="header-meta">
            OPERATOR: <?php echo htmlspecialchars(strtoupper($username)); ?><br>
            ROLE: <?php echo htmlspecialchars(strtoupper($role)); ?><br>
            LAST USER: <?php echo htmlspecialchars($last_user); ?><br>
            DATE: <?php echo date('d/m/Y H:i'); ?>
        </div>
    </header>

    <section class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">TOTAL_MENU</div>
            <div class="stat-value"><?php echo (int) $jml_menu; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">SALES_OUTPUT</div>
            <div class="stat-value"><?php echo (int) $jml_penjualan; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">EVENT_LOGS</div>
            <div class="stat-value"><?php echo (int) $jml_event; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">USER_FEEDBACK</div>
            <div class="stat-value"><?php echo (int) $jml_feedback; ?></div>
        </div>
    </section>

    <section class="table-container">
        <h2 style="font-family:'Special Elite', cursive; margin-bottom: 20px;">SYSTEM SHORTCUTS</h2>
        <div class="action-row">
            <a class="btn-primary" href="menu_crud.php">Kelola Menu</a>
            <a class="btn-primary" href="gallery_crud.php">Kelola Gallery & Event</a>
            <a class="btn-primary" href="feedback.php">Moderasi Feedback</a>
            <a class="btn-primary" href="../karyawan/menu_kasir.php">Buka Kasir</a>
        </div>
    </section>
</main>
</body>
</html>
