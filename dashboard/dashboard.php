<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;
$role = $_SESSION['role'];
$isAdmin = ($role == 'admin' || $role == 'superadmin');

// Statistik
$jml_menu      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$jml_event     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events"))['total'] ?? 0;
$jml_feedback  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
$jml_community = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM human_archive"))['total'] ?? 0;
$jml_user      = $isAdmin ? (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM user"))['total'] ?? 0) : 0;

// Recent Activity
$recent = [];
$q1 = mysqli_query($conn, "SELECT 'feedback' as type, nama_pelanggan as name, komentar as detail, created_at as date FROM feedback ORDER BY date DESC LIMIT 3");
while ($r = mysqli_fetch_assoc($q1)) $recent[] = $r;
$q2 = mysqli_query($conn, "SELECT 'event' as type, judul_event as name, deskripsi_event as detail, created_at as date FROM events ORDER BY date DESC LIMIT 2");
while ($r = mysqli_fetch_assoc($q2)) $recent[] = $r;
$q3 = mysqli_query($conn, "SELECT 'komunitas' as type, name as name, role as detail, created_at as date FROM human_archive ORDER BY date DESC LIMIT 2");
while ($r = mysqli_fetch_assoc($q3)) $recent[] = $r;
usort($recent, fn($a,$b) => strtotime($b['date']) - strtotime($a['date']));
$recent = array_slice($recent, 0, 4);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 12px 12px 0 rgba(0, 43, 91, 0.2);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* --- KEYFRAMES ANIMASI --- */
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(40px) rotate(0deg); }
            100% { opacity: 1; transform: translateY(0) rotate(-0.3deg); }
        }
        @keyframes floatTape {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-2px); }
        }
        
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: var(--gap-section);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: var(--gap-section);
            transition: all 0.3s ease;
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
            width: 100%;
            opacity: 0; 
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .paper:nth-child(2) { animation-delay: 0.1s; }
        .paper:nth-child(3) { animation-delay: 0.3s; }

        .paper:hover {
            transform: translateY(-5px) rotate(-0.3deg);
            box-shadow: 16px 16px 0 rgba(0, 43, 91, 0.25);
        }
        
        .tape {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 32px;
            background: rgba(234, 67, 53, 0.85);
            border: 1px dashed rgba(255,255,255,0.6);
            z-index: 10;
            box-shadow: 2px 4px 5px rgba(0,0,0,0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .sticky-note {
            position: absolute;
            top: 25px;
            right: 25px;
            background: #fff9c4;
            padding: 10px 16px;
            width: 200px;
            transform: rotate(3deg);
            box-shadow: 6px 6px 0 rgba(0,0,0,0.08);
            font-family: 'Caveat', cursive;
            border: 1px solid #e0d68c;
            z-index: 5;
            transition: transform 0.3s ease;
        }
        .sticky-note:hover {
            transform: rotate(0deg) scale(1.05);
            z-index: 20;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 35px;
            text-transform: uppercase; gap: 10px;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 30px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: rgba(0, 43, 91, 0.02);
            border: 2px solid var(--navy);
            padding: 25px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
        }

        .stat-card:hover {
            background: var(--white);
            transform: translateY(-8px);
            box-shadow: 6px 6px 0 var(--red);
            border-color: var(--red);
        }

        .stat-label { font-size: 0.9rem; font-weight: bold; }
        .stat-value { font-family: 'Special Elite', cursive; font-size: 3.5rem; color: var(--red); line-height: 1.2; }

        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 2px solid rgba(0, 43, 91, 0.1);
        }
        
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }

        .menu-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .menu-table th { background: var(--navy); color: white; padding: 12px; text-align: left; }
        .menu-table td { padding: 12px; border-bottom: 1px dashed rgba(0,43,91,0.2); transition: background 0.2s; }
        .menu-table tbody tr:hover td { background: rgba(0, 43, 91, 0.05); }

        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.6); 
            backdrop-filter: blur(3px);
            z-index: 900;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .overlay.active { display: block; opacity: 1; }

        .mobile-header { display: none; }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 1024px) {
            .main-wrapper { padding: 25px; }
        }

        @media (max-width: 768px) {
            /* BUG FIX: Kode class .sidebar dihapus sepenuhnya dari sini */
            
            .main-wrapper { 
                margin-left: 0; width: 100%; padding: 15px; margin-top: 70px; gap: 25px;
            }
            
            .mobile-header {
                display: flex;
                position: fixed; top: 0; left: 0; right: 0;
                height: 65px; z-index: 800;
                background: rgba(248, 249, 250, 0.85); 
                backdrop-filter: blur(8px); 
                border-bottom: 3px solid var(--navy);
                padding: 0 20px; align-items: center; justify-content: space-between;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }

            .paper { padding: 30px 15px; }
            .sticky-note { 
                position: relative; top: 0; right: 0; 
                width: 100%; transform: rotate(0); 
                margin-bottom: 25px; 
                text-align: center;
            }
            .title-main { font-size: 1.6rem; border-left-width: 5px; }
            .stat-value { font-size: 2.5rem; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .tape { width: 110px; }
        }

        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
            .spec-header { font-size: 9px; flex-direction: column; align-items: center; gap: 5px;}
        }
    </style>
</head>
<body>

<div class="overlay" id="sidebarOverlay"></div>

<?php include "../components/sidebar.php"; ?>

<main class="main-wrapper">
    <div class="mobile-header">
        <div class="logo-mobile" style="font-family:'Special Elite'; color:var(--navy); font-size: 1.2rem;">
            <i class="fas fa-mug-hot" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer; transition: transform 0.2s;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper paper-style-1">
        <div class="tape"></div>
        
        <div class="sticky-note">
            <p><i class="fas fa-user-circle"></i> <strong><?php echo htmlspecialchars($username); ?></strong></p>
            <p><i class="fas fa-shield-alt"></i> <span style="color:var(--red); font-weight:bold;"><?php echo strtoupper($role); ?></span></p>
        </div>
        
        <div class="spec-header">
            <span><i class="fas fa-coffee"></i> WOELANDARI LAB_SYS</span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">RINGKASAN DATA</h1>
        
        <div class="stat-grid">
            <div class="stat-card" data-count="<?= $jml_menu ?>">
                <span class="stat-label">MENU ENTRIES</span>
                <div class="stat-value counter">0</div>
            </div>
            <div class="stat-card" data-count="<?= $jml_event ?>">
                <span class="stat-label">ACTIVE EVENTS</span>
                <div class="stat-value counter">0</div>
            </div>
            <div class="stat-card" data-count="<?= $jml_feedback ?>">
                <span class="stat-label">CLIENT FEEDBACK</span>
                <div class="stat-value counter">0</div>
            </div>
            <div class="stat-card" data-count="<?= $jml_community ?>">
                <span class="stat-label">COMMUNITY BASE</span>
                <div class="stat-value counter">0</div>
            </div>
        </div>

        <div class="log-section" style="margin-top:35px; border-top:2px dashed var(--navy); padding-top:20px;">
            <p style="font-weight:bold; margin-bottom:15px; font-size:0.8rem; color:var(--red);">// RECENT SYSTEM ACTIVITY LOGS</p>
            <?php foreach ($recent as $log): ?>
            <div style="font-size:0.85rem; padding:8px 0; border-bottom:1px solid rgba(0,43,91,0.1); display: flex; gap: 10px;">
                <span style="opacity:0.6; min-width: 45px;">[<?= date('H:i', strtotime($log['date'])) ?>]</span> 
                <span>
                    <strong><?= strtoupper($log['type']) ?></strong>: 
                    <?= htmlspecialchars(substr($log['name'],0,35)) ?><?= strlen($log['name']) > 35 ? '...' : '' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="paper paper-style-1">
        <div class="tape"></div>
        <div class="spec-header"><span>// LATEST MENU DATABASE ENTRIES</span></div>
        
        <div class="table-container">
            <table class="menu-table">
                <thead>
                    <tr><th>SYS_ID</th><th>NAMA ITEM</th><th>KATEGORI</th><th>HARGA</th><th>STATUS</th></tr>
                </thead>
                <tbody>
                    <?php
                    $qmenu = mysqli_query($conn, "SELECT id_menu, nama_menu, kategori, harga, status FROM menu ORDER BY id_menu DESC LIMIT 5");
                    while ($m = mysqli_fetch_assoc($qmenu)):
                    ?>
                    <tr>
                        <td style="font-weight: bold; color: var(--red);">#<?= $m['id_menu'] ?></td>
                        <td><?= htmlspecialchars($m['nama_menu']) ?></td>
                        <td><?= $m['kategori'] ?></td>
                        <td>Rp <?= number_format($m['harga'],0,',','.') ?></td>
                        <td>
                            <span style="background: <?= $m['status'] == 'tersedia' ? '#d4edda' : '#f8d7da' ?>; color: <?= $m['status'] == 'tersedia' ? '#155724' : '#721c24' ?>; padding: 3px 8px; border-radius: 3px; font-size: 0.8rem; font-weight: bold;">
                                <?= strtoupper($m['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    // --- ANIMASI TOMBOL HAMBURGER & SIDEBAR ---
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar'); 
    const overlay = document.getElementById('sidebarOverlay');

    if(btn) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            btn.style.transform = sidebar.classList.contains('open') ? 'rotate(90deg)' : 'rotate(0deg)';
        });
    }

    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            btn.style.transform = 'rotate(0deg)';
        });
    }

    // --- ANIMASI COUNTDOWN ANGKA HALUS ---
    const counters = document.querySelectorAll('.counter');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = +entry.target.closest('.stat-card').dataset.count;
                let count = 0;
                const inc = target / 40; 
                
                const update = () => {
                    count += inc;
                    if(count < target) {
                        entry.target.innerText = Math.ceil(count);
                        requestAnimationFrame(update);
                    } else {
                        entry.target.innerText = target;
                    }
                };
                update();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(c => observer.observe(c));
</script>
</body>
</html>