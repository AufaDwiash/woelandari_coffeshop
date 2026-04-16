<?php
session_start();
include "../config/koneksi.php";

// Query Statistik
$jml_menu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$jml_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'] ?? 0;
$jml_event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events"))['total'] ?? 0;
$jml_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Technical Blueprint</title>
    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
</head>
<body>

<aside class="sidebar">
    <div class="brand">WLD_LAB.</div>
    <ul class="nav-list">
        <li><a href="dashboard.php" class="nav-item active">[01] DASHBOARD</a></li>
        <li><a href="menu_admin.php" class="nav-item">[02] DATABASE MENU</a></li>
        <li><a href="#" class="nav-item">[03] TRANSAKSI</a></li>
        <li><a href="#" class="nav-item">[04] EVENT LOGS</a></li>
        <li><a href="#" class="nav-item">[05] FEEDBACK</a></li>
    </ul>
    
    <div style="margin-top: auto;">
        <a href="logout.php" class="nav-item text-red">>> TERMINATE_SESSION</a>
    </div>
</aside>

<main class="main-content">
    <div class="notebook-note">
        <p class="handwritten">System is running optimally...</p>
    </div>

    <header class="page-header">
        <div class="header-title">
            <div class="header-sub">// SYSTEM_OVERVIEW : ROOT</div>
            <h1>ADMIN DASHBOARD</h1>
        </div>
        <div class="header-meta">
            <span class="text-red blink">● LIVE</span><br>
            <?php echo date('d M Y | H:i'); ?>
        </div>
    </header>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">TOTAL_KOMPONEN [MENU]</div>
            <div class="stat-value"><?php echo $jml_menu; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">TOTAL_OUTPUT [SALES]</div>
            <div class="stat-value"><?php echo $jml_penjualan; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">LOG_AGENDA [EVENTS]</div>
            <div class="stat-value"><?php echo $jml_event; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">USER_REPORT [FEEDBACK]</div>
            <div class="stat-value"><?php echo $jml_feedback; ?></div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 40px;">
        <div class="stat-label" style="font-size: 1rem; border:none; margin-bottom: 0;">// GRAFIK_AKTIVITAS_MINGGUAN (SIMULATION)</div>
        <div class="chart-container">
            <div class="chart-bar" style="height: 40%;" data-val="40"></div>
            <div class="chart-bar" style="height: 70%;" data-val="70"></div>
            <div class="chart-bar" style="height: 50%;" data-val="50"></div>
            <div class="chart-bar" style="height: 90%; background: var(--red);" data-val="90 (PEAK)"></div>
            <div class="chart-bar" style="height: 60%;" data-val="60"></div>
            <div class="chart-bar" style="height: 30%;" data-val="30"></div>
        </div>
    </div>
</main>

</body>
</html>