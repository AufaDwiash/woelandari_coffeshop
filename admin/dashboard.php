<?php
ob_start();
session_start();
// Tetap menggunakan path asli Anda
include "../config/koneksi.php";

// Proteksi halaman tetap sama
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// Query statistik tetap sesuai database Anda
$jml_menu      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$jml_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'] ?? 0;
$jml_event     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events"))['total'] ?? 0;
$jml_feedback  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard-Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 8px 8px 0 rgba(0, 43, 91, 0.15);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            border-right: 3px solid var(--navy);
            height: 100vh;
            position: fixed;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            border-bottom: 3px double var(--navy);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--red);
            text-align: center;
        }

        .nav-item {
            display: block;
            padding: 14px 18px;
            color: var(--navy);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--navy);
            color: var(--white);
            transform: translateX(5px);
            box-shadow: 4px 4px 0 var(--red);
        }

        /* --- MAIN WRAPPER --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: var(--gap-section);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: var(--gap-section);
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
            width: 100%;
            overflow: hidden;
        }

        .paper-style-1 { transform: rotate(-0.3deg); }
        .paper-style-2 { transform: rotate(0.3deg); }

        .tape {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 35px; 
            background: rgba(234, 67, 53, 0.7);
            border: 1px dashed rgba(255,255,255,0.4);
            z-index: 2;
        }

        .sticky-note {
            position: absolute; top: 25px; right: 25px;
            background: #fff9c4;
            padding: 12px 18px;
            width: 170px;
            transform: rotate(2deg);
            box-shadow: 4px 4px 10px rgba(0,0,0,0.08);
            font-family: 'Caveat', cursive;
            font-size: 1.15rem;
            border: 1px solid #f0e68c;
            z-index: 5;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 35px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 30px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        /* --- STAT GRID --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: rgba(0, 43, 91, 0.02);
            border: 2px solid var(--navy);
            padding: 25px 15px;
            text-align: center;
            transition: 0.3s;
        }

        .stat-card:hover {
            background: white;
            box-shadow: 6px 6px 0 var(--navy);
            transform: translateY(-5px);
        }

        .stat-label { font-size: 0.75rem; font-weight: 800; display: block; margin-bottom: 12px; opacity: 0.7; }
        .stat-value { font-family: 'Special Elite', cursive; font-size: 3.5rem; color: var(--red); line-height: 1; }

        /* --- CHART --- */
        .chart-frame {
            margin-top: 20px;
            border: 1px solid #e0e0e0;
            background: linear-gradient(to bottom, #f9f9f9 1px, transparent 1px);
            background-size: 100% 40px;
            padding: 20px 20px 0 20px;
        }

        .chart-box {
            height: 220px;
            display: flex;
            align-items: flex-end;
            gap: 20px;
            border-bottom: 3px solid var(--navy);
            border-left: 3px solid var(--navy);
            padding: 0 15px;
        }

        .bar-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; }
        .bar-element { width: 100%; max-width: 60px; background: var(--navy); transition: 0.4s; }
        .bar-element:hover { background: var(--red); }
        .bar-tag { font-size: 11px; font-weight: bold; margin-top: 10px; padding-bottom: 10px; }

        .blink { animation: pulse 1.5s infinite; color: var(--red); }
        @keyframes pulse { 50% { opacity: 0.3; } }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar .brand, .nav-item span { display: none; }
            .main-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI STAFF</div>
    <nav class="nav-list">
        <a href="dashboard.php" class="nav-item active"><span>> DASHBOARD</span></a>
        <a href="menu_crud.php" class="nav-item"><span>> KELOLA MENU</span></a>
        <a href="gallery_crud.php" class="nav-item"><span>> KELOLA GALLERY & EVENT</span></a>
        <a href="feedback.php" class="nav-item"><span>> KELOLA FEEDBACK & RATING</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>> KELOLA USER</span></a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
        </div>
    </nav>
</aside>

<main class="main-wrapper">
    <!-- SECTION 1 -->
    <section class="paper paper-style-1">
        <div class="tape"></div>
        <div class="sticky-note">
            <p>USER: <?php echo $username; ?></p>
            <p>STATUS: <span class="blink">ONLINE</span></p>
        </div>
        
        <div class="spec-header">
           
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">Ringkasan Data</h1>
        
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">Total Menu</span>
                <div class="stat-value"><?php echo $jml_menu; ?></div>
            </div>
        
            <div class="stat-card">
                <span class="stat-label">Event</span>
                <div class="stat-value"><?php echo $jml_event; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Rating</span>
                <div class="stat-value"><?php echo $jml_feedback; ?></div>
            </div>
        </div>
    </section>

    <!-- SECTION 2 -->
    <section class="paper paper-style-2">
        <div class="spec-header">
            <!-- <span>MODULE: TRAFFIC_LOG</span>
            <span>REF: WLDRI-001</span> -->
        </div>

        <h2 class="title-main" style="font-size: 1.8rem;">ACTIVITY_GRAPH</h2>
        
        <div class="chart-frame">
            <div class="chart-box">
                <div class="bar-wrapper"><div class="bar-element" style="height: 40%;"></div><span class="bar-tag">MON</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 70%;"></div><span class="bar-tag">TUE</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 55%;"></div><span class="bar-tag">WED</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 90%; background: var(--red);"></div><span class="bar-tag">THU</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 65%;"></div><span class="bar-tag">FRI</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 80%;"></div><span class="bar-tag">SAT</span></div>
                <div class="bar-wrapper"><div class="bar-element" style="height: 45%;"></div><span class="bar-tag">SUN</span></div>
            </div>
        </div>
    </section>
</main>

</body>
</html>
<?php ob_end_flush(); ?>