<?php
// dashboard/rating_crud.php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Proses moderasi
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $pg = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $srch = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi='tampil' WHERE id_feedback=$id");
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi='pending' WHERE id_feedback=$id");
    } elseif ($action === 'delete') {
        mysqli_query($conn, "DELETE FROM feedback WHERE id_feedback=$id");
    }
    
    // Redirect kembali ke halaman dan pencarian yang sama
    $redirectUrl = "rating_crud.php?page=$pg" . ($srch ? "&search=" . urlencode($srch) : "");
    header("Location: $redirectUrl");
    exit;
}

// Konfigurasi Search dan Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Jumlah feedback per halaman
$offset = ($page - 1) * $limit;

// Filter Pencarian
$whereClause = "";
if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $whereClause = " WHERE nama_pelanggan LIKE '%$safe_search%' OR komentar LIKE '%$safe_search%'";
}

// Hitung total data untuk pagination
$countQuery = "SELECT COUNT(*) as total FROM feedback" . $whereClause;
$total_query = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total_filtered = $total_query['total'] ?? 0;
$totalPages = ceil($total_filtered / $limit);

// Ambil data feedback dengan limit dan offset
$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as tanggal FROM feedback" . $whereClause . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Statistik Global (Tidak terpengaruh pencarian)
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='pending'"))['total'] ?? 0;
$total_tampil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil'"))['total'] ?? 0;

$avg_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rate FROM feedback"));
$avg_rating = $avg_query['avg_rate'] ? number_format($avg_query['avg_rate'], 1) : '0.0';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
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

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0) rotate(-0.2deg); }
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
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .tape {
            position: absolute;
            top: -16px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 32px;
            background: rgba(234, 67, 53, 0.9);
            border: 1px dashed rgba(255,255,255,0.5);
            z-index: 10;
            box-shadow: 2px 3px 5px rgba(0,0,0,0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 30px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 25px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        /* Stat Grid */
        .stat-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px; margin-bottom: 35px;
        }
        .stat-card {
            background: rgba(0, 43, 91, 0.02); border: 2px solid var(--navy);
            padding: 20px 15px; text-align: center;
        }
        .stat-label { font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 10px; }
        .stat-value { font-family: 'Special Elite', cursive; font-size: 2.5rem; color: var(--red); line-height: 1; }

        /* Search Area */
        .search-area {
            display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; align-items: center;
            background: rgba(0, 43, 91, 0.03); padding: 15px; border: 2px solid var(--navy);
        }
        .search-wrapper { flex: 1; position: relative; min-width: 200px; height: 46px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--navy); }
        .search-input {
            width: 100%; height: 100%; padding: 10px 10px 10px 40px;
            border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime', monospace; font-weight: bold; font-size: 0.9rem; outline: none;
        }

        /* Buttons */
        .btn {
            font-family: 'Special Elite', cursive; font-size: 0.85rem; font-weight: bold;
            padding: 8px 15px; border: 2px solid var(--navy); cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; justify-content: center;
            transition: all 0.1s ease; text-decoration: none; height: 46px;
        }
        .btn-primary { background: var(--navy); color: var(--white); box-shadow: 3px 3px 0 var(--red); }
        .btn-primary:hover { background: var(--white); color: var(--navy); transform: translate(-2px, -2px); box-shadow: 5px 5px 0 var(--red); }
        
        .btn-secondary { background: var(--white); color: var(--navy); box-shadow: 3px 3px 0 var(--navy); }
        .btn-secondary:hover { background: #e0e0e0; transform: translate(-2px, -2px); box-shadow: 5px 5px 0 var(--navy); }

        .btn-danger { background: var(--white); color: var(--red); border-color: var(--red); box-shadow: 3px 3px 0 var(--red); }
        .btn-danger:hover { background: var(--red); color: var(--white); transform: translate(-2px, -2px); box-shadow: 5px 5px 0 var(--navy); }

        .btn-sm { padding: 0 12px; font-size: 0.75rem; height: 32px; box-shadow: 3px 3px 0 rgba(0,0,0,0.15); }

        /* Table */
        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white; margin-bottom: 20px;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }

        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; table-layout: fixed; }
        .data-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 130px; font-size: 0.85rem; } /* Tanggal */
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 150px; font-weight: bold; } /* Pelanggan */
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: 120px; color: #d4af37; text-align: center; } /* Rating */
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: auto; font-style: italic; } /* Komentar */
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 120px; text-align: center; } /* Status */
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 170px; text-align: center; } /* Aksi */

        .data-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .data-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }

        .action-buttons { display: inline-flex; gap: 8px; justify-content: center; width: 100%; flex-wrap: wrap;}

        .status-badge {
            padding: 4px 10px; border-radius: 2px; font-size: 0.75rem; font-weight: bold; border: 1px solid currentColor; display: inline-block;
        }
        .status-tampil { background: rgba(21, 87, 36, 0.08); color: #155724; }
        .status-pending { background: rgba(133, 100, 4, 0.08); color: #856404; }

        /* Pagination Area */
        .pagination-area {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 25px; padding-top: 15px; border-top: 2px dashed var(--navy); font-weight: bold;
        }

        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,43,91,0.5); backdrop-filter: blur(2px); z-index: 900; opacity: 0; transition: opacity 0.3s; }
        .overlay.active { display: block; opacity: 1; }
        .mobile-header { display: none; }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; width: 100%; padding: 15px; margin-top: 70px; gap: 25px;}
            .mobile-header {
                display: flex; position: fixed; top: 0; left: 0; right: 0; height: 65px; z-index: 800;
                background: rgba(248, 249, 250, 0.9); backdrop-filter: blur(8px);
                border-bottom: 3px solid var(--navy); padding: 0 20px; align-items: center; justify-content: space-between;
            }
            .paper { padding: 25px 15px; }
            .title-main { font-size: 1.6rem; }
            .tape { width: 110px; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            
            .search-area { flex-direction: column; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .btn { width: 100%; }
            
            .pagination-area { flex-direction: column; gap: 15px; text-align: center; }
            .pagination-area .btn { width: auto; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="overlay" id="sidebarOverlay"></div>

<?php include "../components/sidebar.php"; ?>

<main class="main-wrapper">
    <div class="mobile-header">
        <div class="logo-mobile" style="font-family:'Special Elite'; color:var(--navy); font-size: 1.2rem;">
            <i class="fas fa-star" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header">
            <span><i class="fas fa-folder-open"></i>Kelola Rating</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>
        
        <h1 class="title-main">Feedback</h1>

        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">TOTAL FEEDBACK</span>
                <div class="stat-value"><?= $total_feedback ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">AVG RATING</span>
                <div class="stat-value">
                    <?= $avg_rating ?> <i class="fas fa-star" style="font-size: 1.2rem; color: #d4af37;"></i>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-label" style="color: #856404;">PENDING</span>
                <div class="stat-value" style="color: #856404;"><?= $total_pending ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label" style="color: #155724;">TAMPIL DI WEB</span>
                <div class="stat-value" style="color: #155724;"><?= $total_tampil ?></div>
            </div>
        </div>

        <div class="search-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama pelanggan atau ulasan..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button class="btn btn-primary" id="searchBtn">CARI LOG</button>
            <?php if ($search): ?>
                <a href="rating_crud.php" class="btn btn-secondary">RESET</a>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>PELANGGAN</th>
                        <th style="text-align: center;">RATING</th>
                        <th>KOMENTAR</th>
                        <th style="text-align: center;">STATUS</th>
                        <th style="text-align: center;">AKSI MODERASI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $param = "?page=$page" . ($search ? "&search=".urlencode($search) : "") . "&id=".$row['id_feedback'];
                        ?>
                            <tr>
                                <td><i class="far fa-calendar-alt"></i> <?= $row['tanggal'] ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td>
                                    <?php 
                                        $rating = (int)$row['rating'];
                                        for($i=1; $i<=5; $i++) {
                                            if($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star" style="color: rgba(0,43,91,0.2);"></i>';
                                            }
                                        }
                                    ?>
                                </td>
                                <td>"<?= htmlspecialchars($row['komentar']) ?>"</td>
                                <td>
                                    <span class="status-badge <?= $row['status_moderasi'] == 'tampil' ? 'status-tampil' : 'status-pending' ?>">
                                        <?= strtoupper($row['status_moderasi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($row['status_moderasi'] == 'pending'): ?>
                                            <a href="rating_crud.php<?= $param ?>&action=approve" class="btn btn-primary btn-sm" title="Tampilkan">APP</a>
                                        <?php else: ?>
                                            <a href="rating_crud.php<?= $param ?>&action=reject" class="btn btn-secondary btn-sm" title="Sembunyikan">HIDE</a>
                                        <?php endif; ?>
                                        <a href="rating_crud.php<?= $param ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Hapus feedback ini dari arsip?');">DEL</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px; font-weight: bold; color: var(--navy);">
                                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; color: rgba(0,43,91,0.2);"></i><br>
                                [ LOG FEEDBACK TIDAK DITEMUKAN ]
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination-area">
            <button class="btn btn-secondary" id="prevBtn" <?= ($page <= 1) ? 'disabled' : '' ?> onclick="goToPage(<?= $page - 1 ?>)">← PREV</button>
            <span style="font-family:'Special Elite'; font-size: 1.1rem;">HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
            <button class="btn btn-secondary" id="nextBtn" <?= ($page >= $totalPages) ? 'disabled' : '' ?> onclick="goToPage(<?= $page + 1 ?>)">NEXT →</button>
        </div>
        <?php endif; ?>

    </section>
</main>

<script>
    // Logika Sidebar Mobile
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(btn) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }

    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Logika Pencarian & Paginasi
    document.getElementById('searchBtn')?.addEventListener('click', () => {
        let s = document.getElementById('searchInput').value;
        window.location.href = `rating_crud.php?search=${encodeURIComponent(s)}`;
    });
    
    document.getElementById('searchInput')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') document.getElementById('searchBtn').click();
    });

    function goToPage(page) {
        let s = document.getElementById('searchInput').value;
        window.location.href = `rating_crud.php?page=${page}${s ? '&search='+encodeURIComponent(s) : ''}`;
    }
</script>
</body>
</html>