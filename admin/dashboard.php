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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - System Overview</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-ink: #9b2226;
            --navy-ink: #001219;
            --paper-bg: #e5e5e5;
            --sidebar-width: 260px;
        }

        * {
            box-sizing: border-box; /* Kunci agar layout tidak berantakan */
        }

        body {
            margin: 0;
            padding: 0;
            display: flex; /* Membuat Sidebar & Main bersandingan */
            min-height: 100vh;
            background-color: var(--paper-bg);
            font-family: 'Courier Prime', monospace;
            color: var(--navy-ink);
        }

        /* --- SIDEBAR AREA --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--navy-ink);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed; /* Tetap di tempat saat scroll */
            height: 100vh;
            padding: 20px;
            z-index: 100;
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            color: var(--red-ink);
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px double #444;
            margin-bottom: 30px;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            display: block;
            padding: 15px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 0.9rem;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left: 4px solid var(--red-ink);
        }

        /* --- MAIN CONTENT AREA --- */
        .main-content {
            flex: 1; /* Mengambil sisa ruang kosong di kanan */
            margin-left: var(--sidebar-width); /* Agar tidak tertutup sidebar yang fixed */
            padding: 40px;
            width: 100%;
        }

        /* Header Styling */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }

        .header-title h1 {
            font-family: 'Special Elite', cursive;
            margin: 0;
            font-size: 2.2rem;
            letter-spacing: -1px;
        }

        .status-badge {
            color: var(--red-ink);
            font-weight: bold;
            font-size: 0.8rem;
        }

        /* Stats Grid (Simetri 4 Kolom) */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            border: 2px solid var(--navy-ink);
            padding: 20px;
            box-shadow: 6px 6px 0px var(--navy-ink);
            position: relative;
        }

        .stat-label {
            font-family: 'Special Elite', cursive;
            font-size: 0.75rem;
            color: var(--red-ink);
            text-transform: uppercase;
            margin-bottom: 10px;
            display: block;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: bold;
            line-height: 1;
        }

        /* Activity Chart Box */
        .activity-box {
            background: #fff;
            border: 2px solid var(--navy-ink);
            padding: 30px;
            position: relative;
        }

        .chart-container {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            height: 200px;
            margin-top: 30px;
            padding: 10px;
            border-left: 2px solid #ccc;
            border-bottom: 2px solid #ccc;
        }

        .chart-bar {
            flex: 1;
            background: var(--navy-ink);
            position: relative;
            min-width: 30px;
        }

        .chart-bar:hover { background: var(--red-ink); }

        .chart-bar::after {
            content: attr(data-val);
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
        }

        /* Tape Decoration (Khas menu_crud) */
        .tape {
            position: absolute;
            width: 80px;
            height: 30px;
            background: rgba(0,0,0,0.1);
            top: -15px;
            left: 20px;
            transform: rotate(-2deg);
            border: 1px dashed rgba(0,0,0,0.2);
        }

        .sticky-note {
            background: #fffa90;
            padding: 15px;
            transform: rotate(1deg);
            box-shadow: 3px 3px 10px rgba(0,0,0,0.1);
            display: inline-block;
            margin-bottom: 20px;
            font-family: 'Special Elite', cursive;
            font-size: 0.8rem;
            border: 1px solid #ede76d;
        }

        .blink { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }

        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 10px; }
            .brand { font-size: 0.8rem; }
            .nav-item span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
    <nav class="nav-list">
        <a href="dashboard.php" class="nav-item active"> <span>Dashboard</span></a>
        <a href="menu_crud.php" class="nav-item"><span>Menu</span></a>
        <a href="gallery_crud.php" class="nav-item"> <span>Gallery</span></a>
        <a href="#" class="nav-item"><span>Feedback</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>Kelola User</span></a>
    </nav>
    
    <div style="margin-top: auto; border-top: 1px dashed #555; padding-top: 10px;">
        <a href="logout.php" class="nav-item" style="color: #ff6b6b;">>> <span>TERMINATE_SESSION</span></a>
    </div>
</aside>

<main class="main-content">
    <div class="sticky-note">
        * SYSTEM_REPORT: OPTIMAL <br>
        * ENCRYPTION: ACTIVE <br>
        * DATE: <?php echo date('d/m/Y'); ?>
    </div>

    <header class="page-header">
        <div class="header-title">
            <div class="status-badge">// <span class="blink">●</span> LIVE_MONITORING</div>
            <h1>ADMIN DASHBOARD</h1>
        </div>
        <div style="text-align: right; font-size: 0.8rem;">
            OPERATOR: ADMIN_ROOT<br>
            TIME: <?php echo date('H:i:s'); ?>
        </div>
    </header>

    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-label">TOTAL_MENU</span>
            <div class="stat-value"><?php echo $jml_menu; ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">SALES_OUTPUT</span>
            <div class="stat-value"><?php echo $jml_penjualan; ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">EVENT_LOGS</span>
            <div class="stat-value"><?php echo $jml_event; ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">USER_FEEDBACK</span>
            <div class="stat-value"><?php echo $jml_feedback; ?></div>
        </div>
    </div>

    <div class="activity-box">
        <div class="tape"></div>
        <h3 style="font-family: 'Special Elite', cursive; margin: 0;">ACTIVITY_WEEKLY_DIAGRAM</h3>
        <div class="chart-container">
            <div class="chart-bar" style="height: 40%;" data-val="40"></div>
            <div class="chart-bar" style="height: 70%;" data-val="70"></div>
            <div class="chart-bar" style="height: 55%;" data-val="55"></div>
            <div class="chart-bar" style="height: 90%; background: var(--red-ink);" data-val="PEAK"></div>
            <div class="chart-bar" style="height: 65%;" data-val="65"></div>
            <div class="chart-bar" style="height: 35%;" data-val="35"></div>
            <div class="chart-bar" style="height: 50%;" data-val="50"></div>
        </div>
    </div>
</main>

</body>
</html>