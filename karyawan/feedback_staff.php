<?php
ob_start();
session_start();
include "../config/koneksi.php";

// Proteksi halaman - hanya karyawan yang bisa akses
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Jika yang login adalah admin/superadmin, redirect ke admin
if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin') {
    header("Location: ../admin/feedback.php");
    exit;
}

// Pastikan role adalah karyawan
if ($_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;

// LOGIKA MODERASI
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $query = "UPDATE feedback SET status_moderasi='tampil' WHERE id_feedback=$id";
    } elseif ($action === 'reject') {
        $query = "UPDATE feedback SET status_moderasi='pending' WHERE id_feedback=$id";
    } elseif ($action === 'delete') {
        $query = "DELETE FROM feedback WHERE id_feedback=$id";
    }

    if (isset($query) && mysqli_query($conn, $query)) {
        header("Location: feedback_staff.php?msg=success");
        exit();
    }
}

// Ambil data feedback pelanggan
$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as tanggal FROM feedback ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='pending'"))['total'];
$total_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil'"))['total'];
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM feedback WHERE status_moderasi='tampil'"))['avg'];
$avg_rating = round($avg_rating, 1) ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Feedback - Karyawan | Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --green: #2d6a4f;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --sidebar-width-mobile: 70px;
            --shadow-clean: 8px 8px 0 rgba(0, 43, 91, 0.15);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
            --gap-section-mobile: 20px;
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
            transition: all 0.3s ease;
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            border-bottom: 3px double var(--navy);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--green);
            text-align: center;
        }

        .brand small {
            font-size: 0.7rem;
            display: block;
            color: var(--red);
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
            white-space: nowrap;
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--navy);
            color: var(--white);
            transform: translateX(5px);
            box-shadow: 4px 4px 0 var(--green);
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
            width: 200px;
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
            flex-wrap: wrap;
            gap: 10px;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 30px;
            color: var(--navy);
            border-left: 8px solid var(--green);
            padding-left: 20px;
        }

        /* --- STAT GRID --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(0, 43, 91, 0.02);
            border: 2px solid var(--navy);
            padding: 20px 15px;
            text-align: center;
            transition: 0.3s;
        }

        .stat-card:hover {
            background: white;
            box-shadow: 6px 6px 0 var(--navy);
            transform: translateY(-5px);
        }

        .stat-label { 
            font-size: 0.7rem; 
            font-weight: 800; 
            display: block; 
            margin-bottom: 12px; 
            opacity: 0.7;
            text-transform: uppercase;
        }
        
        .stat-value { 
            font-family: 'Special Elite', cursive; 
            font-size: 2.5rem; 
            color: var(--green); 
            line-height: 1; 
        }

        /* --- TABLE STYLING RESPONSIVE --- */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .feedback-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .feedback-table th {
            background: var(--navy);
            color: white;
            padding: 15px;
            text-align: left;
            font-family: 'Special Elite', cursive;
            font-size: 0.85rem;
        }

        .feedback-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 43, 91, 0.1);
            font-size: 0.85rem;
            vertical-align: top;
        }

        .feedback-table tr:hover {
            background: rgba(0, 43, 91, 0.03);
        }

        .rating-stars {
            color: var(--green);
            font-size: 1rem;
            letter-spacing: 2px;
            white-space: nowrap;
        }

        .timestamp {
            font-size: 0.7rem;
            color: #888;
            display: block;
            margin-top: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-tampil {
            background: var(--green);
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: var(--navy);
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .op-btn {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.7rem;
            padding: 6px 12px;
            border: 2px solid var(--green);
            color: var(--green);
            display: inline-block;
            transition: all 0.2s;
            cursor: pointer;
            text-align: center;
        }

        .op-btn:hover {
            background: var(--green);
            color: white;
        }

        .op-btn.del {
            border-color: var(--red);
            color: var(--red);
        }

        .op-btn.del:hover {
            background: var(--red);
            color: white;
        }

        .blink { animation: pulse 1.5s infinite; color: var(--green); }
        @keyframes pulse { 50% { opacity: 0.3; } }

        .role-badge {
            background: var(--green);
            color: white;
            padding: 2px 8px;
            font-size: 0.6rem;
            border-radius: 2px;
            margin-left: 8px;
        }

        .msg-success {
            background: rgba(45, 106, 79, 0.1);
            border-left: 4px solid var(--green);
            padding: 12px 20px;
            margin-bottom: 20px;
            font-size: 0.8rem;
        }

        /* --- RESPONSIVE STYLES --- */
        @media (max-width: 1024px) {
            .sidebar {
                width: var(--sidebar-width-mobile);
                padding: 20px 10px;
            }
            
            .brand span, .nav-item span {
                display: none;
            }
            
            .brand {
                font-size: 1.2rem;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            
            .brand small {
                display: none;
            }
            
            .nav-item {
                text-align: center;
                padding: 12px 8px;
            }
            
            .nav-item i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-wrapper {
                margin-left: var(--sidebar-width-mobile);
                width: calc(100% - var(--sidebar-width-mobile));
                padding: var(--gap-section-mobile);
            }
        }

        @media (max-width: 768px) {
            .paper {
                padding: 25px 20px;
            }
            
            .title-main {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }
            
            .sticky-note {
                position: static;
                margin-bottom: 20px;
                width: 100%;
                transform: rotate(0deg);
            }
            
            .spec-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
            
            .stat-label {
                font-size: 0.6rem;
            }
            
            .feedback-table th, 
            .feedback-table td {
                padding: 10px;
                font-size: 0.75rem;
            }
            
            .rating-stars {
                font-size: 0.8rem;
                white-space: nowrap;
            }
            
            .op-btn {
                padding: 4px 8px;
                font-size: 0.65rem;
            }
            
            .action-group {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
            
            .feedback-table th:nth-child(1),
            .feedback-table td:nth-child(1) {
                display: none;
            }
            
            .title-main {
                font-size: 1.2rem;
                padding-left: 12px;
            }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        WOELANDARI
        <small>Staff</small>
    </div>
    <nav class="nav-list">
        <a href="dashboard_staff.php" class="nav-item">
            <i class="fas fa-chalkboard-user"></i> <span>DASHBOARD</span>
        </a>
        <a href="menu_staff.php" class="nav-item">
            <i class="fas fa-utensils"></i> <span>MENU</span>
        </a>
        <a href="gallery_staff.php" class="nav-item">
            <i class="fas fa-images"></i> <span>GALLERY</span>
        </a>
        <a href="feedback_staff.php" class="nav-item active">
            <i class="fas fa-star"></i> <span>FEEDBACK</span>
        </a>
        <a href="akun_staff.php" class="nav-item ">
            <i class="fas fa-user-circle"></i> <span>AKUN</span>
        </a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);">
                <i class="fas fa-sign-out-alt"></i> <span>KELUAR</span>
            </a>
        </div>
    </nav>
</aside>

<main class="main-wrapper">
    <section class="paper paper-style-1">
        <div class="tape"></div>
       
        
        <div class="spec-header">
            <span><i class="fas fa-star"></i> WOELANDARI COFFEE LAB // FEEDBACK MODERATION</span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">FEEDBACK SYSTEM</h1>

        <!-- Pesan sukses -->
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="msg-success">
            <i class="fas fa-check-circle"></i> Operasi berhasil dilakukan!
        </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-comments"></i> TOTAL FEEDBACK</span>
                <div class="stat-value"><?php echo $total_feedback; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-clock"></i> PENDING</span>
                <div class="stat-value"><?php echo $total_pending; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-check-circle"></i> APPROVED</span>
                <div class="stat-value"><?php echo $total_approved; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-star"></i> AVG RATING</span>
                <div class="stat-value"><?php echo $avg_rating; ?></div>
            </div>
        </div>

        <div class="table-container">
            <table class="feedback-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CLIENT_NAME</th>
                        <th>RATING</th>
                        <th>LOG_MESSAGE</th>
                        <th style="text-align: center;">STATUS</th>
                        <th style="text-align: center;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id_feedback']; ?></td>
                            <td class="bold"><?php echo strtoupper(htmlspecialchars($row['nama_pelanggan'])); ?></td>
                            <td class="rating-stars">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <?php if($i <= $row['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </td>
                            <td>
                                <div style="margin-bottom: 8px;">"<?php echo htmlspecialchars($row['komentar']); ?>"</div>
                                <span class="timestamp"><i class="far fa-calendar-alt"></i> <?php echo $row['tanggal']; ?></span>
                             </td>
                            <td align="center">
                                <span class="status-badge status-<?php echo $row['status_moderasi']; ?>">
                                    <?php echo strtoupper($row['status_moderasi']); ?>
                                </span>
                             </td>
                            <td align="center">
                                <div class="action-group">
                                    <?php if($row['status_moderasi'] == 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $row['id_feedback']; ?>" class="op-btn">
                                            <i class="fas fa-check"></i> APPROVE
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=reject&id=<?php echo $row['id_feedback']; ?>" class="op-btn">
                                            <i class="fas fa-eye-slash"></i> HIDE
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $row['id_feedback']; ?>" 
                                       class="op-btn del" 
                                       onclick="return confirm('⚠️ Yakin ingin menghapus feedback ini?')">
                                        <i class="fas fa-trash"></i> DELETE
                                    </a>
                                </div>
                             </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px;">
                                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.5;"></i><br>
                                Belum ada feedback dari pelanggan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
<?php ob_end_flush(); ?>