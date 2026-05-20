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
    
    $redirectUrl = "rating_crud.php?page=$pg" . ($srch ? "&search=" . urlencode($srch) : "");
    header("Location: $redirectUrl");
    exit;
}

// Konfigurasi Search dan Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "";
if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $whereClause = " WHERE nama_pelanggan LIKE '%$safe_search%' OR komentar LIKE '%$safe_search%'";
}

$countQuery = "SELECT COUNT(*) as total FROM feedback" . $whereClause;
$total_query = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total_filtered = $total_query['total'] ?? 0;
$totalPages = ceil($total_filtered / $limit);

$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as tanggal FROM feedback" . $whereClause . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Statistik Global
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='pending'"))['total'] ?? 0;
$total_tampil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil'"))['total'] ?? 0;

$avg_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rate FROM feedback"));
$avg_rating = $avg_query['avg_rate'] ? number_format($avg_query['avg_rate'], 1) : '0.0';

$msg_display = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Feedback - Woelandari Coffee Lab</title>
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
        @keyframes shakeAnim {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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

        .alert-msg { background: #fff9c4; border: 2px dashed #e0d68c; padding: 10px 15px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid var(--red); }

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

        /* ========== PERBAIKAN TOMBOL ACTION ========== */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 0.7rem;
            font-family: 'Special Elite', cursive;
            font-weight: bold;
            border: 2px solid var(--navy);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            white-space: nowrap;
            border-radius: 0;
        }
        
        .btn-action i {
            font-size: 0.75rem;
        }
        
        .btn-approve {
            background: var(--navy);
            color: var(--white);
            box-shadow: 2px 2px 0 var(--red);
        }
        
        .btn-approve:hover {
            background: var(--white);
            color: var(--navy);
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0 var(--red);
        }
        
        .btn-hide {
            background: var(--white);
            color: #856404;
            border-color: #856404;
            box-shadow: 2px 2px 0 #856404;
        }
        
        .btn-hide:hover {
            background: #856404;
            color: var(--white);
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0 var(--navy);
        }
        
        .btn-delete-action {
            background: var(--white);
            color: var(--red);
            border-color: var(--red);
            box-shadow: 2px 2px 0 var(--red);
        }
        
        .btn-delete-action:hover {
            background: var(--red);
            color: var(--white);
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0 var(--navy);
        }
        
        .btn-action:active {
            transform: translate(1px, 1px);
            box-shadow: 1px 1px 0 var(--red);
        }

        /* Table */
        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white; margin-bottom: 20px;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }

        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .data-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 130px; font-size: 0.85rem; }
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 140px; font-weight: bold; }
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: 110px; color: #d4af37; text-align: center; }
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: auto; font-style: italic; }
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 110px; text-align: center; }
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 200px; text-align: center; }

        .data-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .data-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }

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

        /* DELETE CONFIRMATION MODAL */
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 43, 91, 0.85);
            backdrop-filter: blur(8px);
            z-index: 3000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .confirm-modal-content {
            background: var(--white);
            border: 4px solid var(--navy);
            max-width: 450px;
            width: 100%;
            position: relative;
            animation: slideUpFade 0.3s ease;
            box-shadow: 16px 16px 0 var(--red);
        }
        
        .confirm-modal-header {
            background: var(--red);
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid var(--navy);
        }
        
        .confirm-modal-header i {
            font-size: 4rem;
            color: var(--white);
            text-shadow: 3px 3px 0 var(--navy);
        }
        
        .confirm-modal-body {
            padding: 30px;
            text-align: center;
        }
        
        .confirm-modal-body h3 {
            font-family: 'Special Elite', cursive;
            font-size: 1.5rem;
            color: var(--navy);
            margin-bottom: 15px;
        }
        
        .confirm-modal-body p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .feedback-name-highlight {
            background: rgba(234, 67, 53, 0.1);
            color: var(--red);
            font-weight: bold;
            padding: 5px 12px;
            display: inline-block;
            margin: 10px 0;
            border-left: 3px solid var(--red);
            font-size: 1rem;
            max-width: 100%;
            word-break: break-word;
        }
        
        .confirm-modal-footer {
            padding: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            border-top: 2px dashed rgba(0,43,91,0.2);
        }
        
        .confirm-modal-footer .btn {
            min-width: 120px;
        }
        
        .confirm-modal-content.warning-shake {
            animation: shakeAnim 0.3s ease;
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
            
            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }
            
            .btn-action {
                width: 100%;
                padding: 5px 10px;
                font-size: 0.65rem;
            }
            
            .data-table th:nth-child(6), 
            .data-table td:nth-child(6) { 
                width: 110px; 
            }
        }
        
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
            
            .btn-action {
                padding: 4px 8px;
                font-size: 0.6rem;
            }
            
            .btn-action i {
                font-size: 0.65rem;
            }
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
            <span><i class="fas fa-folder-open"></i> Kelola Feedback & Rating</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>
        
        <h1 class="title-main">Feedback Pelanggan</h1>

        <?php if ($msg_display): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg_display ?></div>
        <?php endif; ?>

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
                                                echo '<i class="fas fa-star" style="color: #d4af37;"></i>';
                                            } else {
                                                echo '<i class="far fa-star" style="color: rgba(0,43,91,0.2);"></i>';
                                            }
                                        }
                                    ?>
                                </td>
                                <td>"<?= htmlspecialchars(substr($row['komentar'], 0, 100)) ?><?= strlen($row['komentar']) > 100 ? '...' : '' ?>"</td>
                                <td style="text-align: center;">
                                    <span class="status-badge <?= $row['status_moderasi'] == 'tampil' ? 'status-tampil' : 'status-pending' ?>">
                                        <?= strtoupper($row['status_moderasi']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-buttons">
                                        <?php if ($row['status_moderasi'] == 'pending'): ?>
                                            <a href="rating_crud.php<?= $param ?>&action=approve" 
                                               class="btn-action btn-approve" 
                                               title="Tampilkan di website">
                                                <i class="fas fa-check-circle"></i> TAMPIL
                                            </a>
                                        <?php else: ?>
                                            <a href="rating_crud.php<?= $param ?>&action=reject" 
                                               class="btn-action btn-hide" 
                                               title="Sembunyikan dari website">
                                                <i class="fas fa-eye-slash"></i> SEMBUNYI
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn-action btn-delete-action delete-btn" 
                                                data-id="<?= $row['id_feedback'] ?>"
                                                data-name="<?= htmlspecialchars($row['nama_pelanggan']) ?>"
                                                data-comment="<?= htmlspecialchars(substr($row['komentar'], 0, 50)) ?>"
                                                data-page="<?= $page ?>"
                                                data-search="<?= htmlspecialchars($search) ?>"
                                                title="Hapus feedback">
                                            <i class="fas fa-trash-alt"></i> HAPUS
                                        </button>
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

<!-- CUSTOM DELETE CONFIRMATION MODAL -->
<div id="deleteConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="confirm-modal-body">
            <h3>HAPUS FEEDBACK?</h3>
            <p>Apakah Anda yakin ingin menghapus feedback berikut dari sistem?</p>
            <div class="feedback-name-highlight" id="feedbackNameToDelete"></div>
            <div style="font-size: 0.8rem; color: #999; margin-top: 5px;" id="feedbackCommentToDelete"></div>
            <p style="font-size: 0.8rem; color: #999; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> Data yang dihapus tidak dapat dikembalikan!
            </p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">
                <i class="fas fa-times"></i> BATAL
            </button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> HAPUS
            </a>
        </div>
    </div>
</div>

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

    // ========== DELETE CONFIRMATION MODAL ==========
    const deleteModal = document.getElementById('deleteConfirmModal');
    const feedbackNameSpan = document.getElementById('feedbackNameToDelete');
    const feedbackCommentSpan = document.getElementById('feedbackCommentToDelete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    let currentDeleteUrl = '';

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const feedbackId = this.dataset.id;
            const feedbackName = this.dataset.name;
            const feedbackComment = this.dataset.comment || '';
            const page = this.dataset.page || '1';
            const search = this.dataset.search || '';
            
            feedbackNameSpan.textContent = feedbackName;
            if (feedbackComment) {
                feedbackCommentSpan.textContent = `"${feedbackComment}${feedbackComment.length >= 50 ? '...' : ''}"`;
                feedbackCommentSpan.style.display = 'block';
            } else {
                feedbackCommentSpan.style.display = 'none';
            }
            
            let deleteUrl = `rating_crud.php?page=${page}&id=${feedbackId}&action=delete`;
            if (search) deleteUrl += `&search=${encodeURIComponent(search)}`;
            
            currentDeleteUrl = deleteUrl;
            confirmDeleteBtn.href = currentDeleteUrl;
            
            deleteModal.style.display = 'flex';
            
            const modalContent = document.querySelector('.confirm-modal-content');
            modalContent.classList.add('warning-shake');
            setTimeout(() => {
                modalContent.classList.remove('warning-shake');
            }, 300);
        });
    });
    
    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });
    
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && deleteModal.style.display === 'flex') {
            deleteModal.style.display = 'none';
        }
    });
</script>
</body>
</html>