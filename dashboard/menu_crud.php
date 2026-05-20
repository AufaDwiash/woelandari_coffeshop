<?php
include "../config/koneksi.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Semua role (admin, superadmin, karyawan) memiliki akses penuh
// Hanya perlu memastikan user sudah login

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM menu";
if ($search) $countQuery .= " WHERE nama_menu LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
$totalResult = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

$query = "SELECT * FROM menu";
if ($search) $query .= " WHERE nama_menu LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
$query .= " ORDER BY kategori, nama_menu LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$edit_mode = false;
$edit_id = $edit_nama = $edit_kategori = $edit_harga = $edit_deskripsi = $edit_foto = $edit_status = '';

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $edit_id = $d['id_menu'];
        $edit_nama = $d['nama_menu'];
        $edit_kategori = $d['kategori'];
        $edit_harga = $d['harga'];
        $edit_deskripsi = $d['deskripsi'];
        $edit_foto = $d['foto'];
        $edit_status = $d['status'];
    }
}

// Proses POST untuk semua role yang sudah login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id_menu']) ? (int)$_POST['id_menu'] : 0;
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = (int)$_POST['harga'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = isset($_POST['foto_lama']) ? $_POST['foto_lama'] : '';
    
    $foto_nama = $foto_lama;
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/menu/' . $foto_nama, $img_base64);
        if ($foto_lama && $foto_lama != 'default.jpg' && file_exists('../assets/images/menu/'.$foto_lama))
            unlink('../assets/images/menu/'.$foto_lama);
    }
    
    if ($action == 'add') {
        if (!$foto_nama) $foto_nama = 'default.jpg';
        mysqli_query($conn, "INSERT INTO menu (nama_menu, kategori, harga, status, deskripsi, foto) VALUES ('$nama', '$kategori', $harga, '$status', '$deskripsi', '$foto_nama')");
        $msg = "Menu berhasil ditambahkan!";
    } elseif ($action == 'update') {
        mysqli_query($conn, "UPDATE menu SET nama_menu='$nama', kategori='$kategori', harga=$harga, status='$status', deskripsi='$deskripsi', foto='$foto_nama' WHERE id_menu=$id");
        $msg = "Menu berhasil diperbarui!";
    }
    header("Location: menu_crud.php?msg=" . urlencode($msg) . ($search ? "&search=" . urlencode($search) : "") . ($page ? "&page=$page" : ""));
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM menu WHERE id_menu=$id"));
    if ($q && $q['foto'] != 'default.jpg' && file_exists('../assets/images/menu/'.$q['foto']))
        unlink('../assets/images/menu/'.$q['foto']);
    mysqli_query($conn, "DELETE FROM menu WHERE id_menu=$id");
    header("Location: menu_crud.php?msg=Menu dihapus!" . ($search ? "&search=" . urlencode($search) : ""));
    exit;
}

$msg_display = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Menu - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
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
            font-size: 2.2rem; margin-bottom: 20px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        .alert-msg { background: #fff9c4; border: 2px dashed #e0d68c; padding: 10px 15px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid var(--red); }

        /* Search & Action Area */
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

        /* Button Retro Brutalist */
        .btn {
            font-family: 'Special Elite', cursive; font-size: 0.9rem; font-weight: bold;
            padding: 11px 20px; border: 2px solid var(--navy); cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; justify-content: center;
            transition: all 0.1s ease; text-decoration: none; height: 46px;
        }
        .btn-primary { background: var(--navy); color: var(--white); box-shadow: 4px 4px 0 var(--red); }
        .btn-primary:hover { background: var(--white); color: var(--navy); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--red); }
        
        .btn-secondary { background: var(--white); color: var(--navy); box-shadow: 4px 4px 0 var(--navy); }
        .btn-secondary:hover { background: #e0e0e0; transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }

        .btn-danger { background: var(--white); color: var(--red); border-color: var(--red); box-shadow: 4px 4px 0 var(--red); }
        .btn-danger:hover { background: var(--red); color: var(--white); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }

        .btn-sm { padding: 0 12px; font-size: 0.75rem; box-shadow: 3px 3px 0 rgba(0,0,0,0.15); height: 32px; }

        /* ========== PERBAIKAN TOMBOL EDIT & DELETE ========== */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
        }
        
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: 0.75rem;
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
            font-size: 0.85rem;
        }
        
        .btn-edit-action {
            background: var(--navy);
            color: var(--white);
            box-shadow: 3px 3px 0 var(--red);
        }
        
        .btn-edit-action:hover {
            background: var(--white);
            color: var(--navy);
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 var(--red);
        }
        
        .btn-delete-action {
            background: var(--white);
            color: var(--red);
            border-color: var(--red);
            box-shadow: 3px 3px 0 var(--red);
        }
        
        .btn-delete-action:hover {
            background: var(--red);
            color: var(--white);
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 var(--navy);
        }
        
        .btn-action:active {
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0 var(--red);
        }

        /* Custom Table */
        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }

        .menu-table { width: 100%; border-collapse: collapse; min-width: 750px; }
        .menu-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        /* Definisi Lebar Kolom yang Konsisten */
        .menu-table th:nth-child(1), .menu-table td:nth-child(1) { width: 90px; text-align: center; }
        .menu-table th:nth-child(2), .menu-table td:nth-child(2) { width: auto; }
        .menu-table th:nth-child(3), .menu-table td:nth-child(3) { width: 130px; }
        .menu-table th:nth-child(4), .menu-table td:nth-child(4) { width: 130px; }
        .menu-table th:nth-child(5), .menu-table td:nth-child(5) { width: 130px; }
        .menu-table th:nth-child(6), .menu-table td:nth-child(6) { width: 200px; text-align: center; }

        .menu-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .menu-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }

        .thumb-img { width: 60px; height: 50px; object-fit: cover; border: 2px solid var(--navy); padding: 1px; background: white; box-shadow: 2px 2px 0 var(--navy);}

        .status-badge {
            padding: 4px 10px; border-radius: 2px; font-size: 0.75rem; font-weight: bold; border: 1px solid currentColor; display: inline-block;
        }
        .status-tersedia { background: rgba(21, 87, 36, 0.08); color: #155724; }
        .status-tidak { background: rgba(114, 28, 36, 0.08); color: #721c24; }

        /* Pagination */
        .pagination-area {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 25px; padding-top: 15px; border-top: 2px dashed var(--navy); font-weight: bold;
        }

        /* MODAL FIX - SCROLLABLE COMPREHENSIVE */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.65); backdrop-filter: blur(4px); z-index: 2000;
            justify-content: center; align-items: center; padding: 15px;
        }
        .modal-content {
            background: var(--white); border: 4px solid var(--navy);
            width: 100%; max-width: 650px; 
            max-height: 92vh; 
            display: flex; flex-direction: column;
            box-shadow: 14px 14px 0 var(--red); position: relative;
        }
        .modal-header-area { padding: 25px 25px 10px 25px; flex-shrink: 0; }
        
        .modal-body-scroll {
            padding: 10px 25px 25px 25px;
            overflow-y: auto;
            flex: 1;
        }
        .modal-body-scroll::-webkit-scrollbar { width: 6px; }
        .modal-body-scroll::-webkit-scrollbar-thumb { background: var(--navy); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: bold; font-size: 0.85rem; margin-bottom: 6px; color: var(--navy); text-transform: uppercase; }
        .form-input, .form-select, textarea {
            width: 100%; padding: 10px; border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime'; outline: none; box-shadow: inset 2px 2px 0 rgba(0,0,0,0.03);
        }
        .form-input:focus, .form-select:focus, textarea:focus { border-color: var(--red); }

        .upload-box-safe {
            background: rgba(0,43,91,0.04); padding: 15px; 
            border: 2px dashed var(--navy); margin-top: 10px; margin-bottom: 5px;
        }

        .overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.5); backdrop-filter: blur(2px); z-index: 900; opacity: 0; transition: opacity 0.3s;
        }
        .overlay.active { display: block; opacity: 1; }
        .mobile-header { display: none; }

        /* ========== CUSTOM DELETE CONFIRMATION MODAL ========== */
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
        
        .menu-name-highlight {
            background: rgba(234, 67, 53, 0.1);
            color: var(--red);
            font-weight: bold;
            padding: 5px 12px;
            display: inline-block;
            margin: 10px 0;
            border-left: 3px solid var(--red);
            font-size: 1.1rem;
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
        
        @keyframes shakeAnim {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .confirm-modal-content.warning-shake {
            animation: shakeAnim 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; width: 100%; padding: 15px; margin-top: 70px; gap: 25px;}
            .mobile-header {
                display: flex; position: fixed; top: 0; left: 0; right: 0; height: 65px; z-index: 800;
                background: rgba(248, 249, 250, 0.9); backdrop-filter: blur(8px);
                border-bottom: 3px solid var(--navy); padding: 0 20px; align-items: center; justify-content: space-between;
            }
            .paper { padding: 25px 15px; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .search-area { flex-direction: column; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .btn { width: 100%; }
            .title-main { font-size: 1.6rem; }
            .tape { width: 110px; }
            .pagination-area { flex-direction: column; gap: 15px; }
            .pagination-area .btn { width: auto; }
            .confirm-modal-footer .btn { width: auto; min-width: 100px; }
            
            /* Responsive untuk action buttons */
            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .btn-action {
                width: 100%;
                padding: 6px 12px;
                font-size: 0.7rem;
            }
            
            .menu-table th:nth-child(6), 
            .menu-table td:nth-child(6) { 
                width: 110px; 
            }
        }
        
        @media (max-width: 480px) {
            .btn-action {
                padding: 5px 10px;
                font-size: 0.65rem;
            }
            
            .btn-action i {
                font-size: 0.7rem;
            }
            
            .menu-table th:nth-child(6), 
            .menu-table td:nth-child(6) { 
                width: 90px; 
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
            <i class="fas fa-mug-hot" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header">
            <span><i class="fas fa-folder-open"></i> Kelola Menu</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>
        
        <h1 class="title-main">MENU</h1>
        
        <?php if ($msg_display): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg_display ?></div>
        <?php endif; ?>

        <div class="search-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari entri menu..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button class="btn btn-primary" id="searchBtn">CARI DATA</button>
            <?php if ($search): ?>
                <a href="menu_crud.php" class="btn btn-secondary">RESET</a>
            <?php endif; ?>
            <button class="btn btn-primary" id="showModalBtn" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);">
                <i class="fas fa-plus"></i> ADD ENTRY
            </button>
        </div>

        <div class="table-container">
            <table class="menu-table">
                <thead>
                    <tr>
                        <th>FOTO</th>
                        <th>NAMA ITEM</th>
                        <th>KATEGORI</th>
                        <th>HARGA</th>
                        <th>STATUS</th>
                        <th style="text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): while($row=mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td style="text-align: center;">
                            <img src="../assets/images/menu/<?= $row['foto'] ?>" class="thumb-img" onerror="this.src='../assets/images/menu/default.jpg'">
                        </td>
                        <td style="font-weight: bold; color: var(--navy);"><?= htmlspecialchars($row['nama_menu']) ?></td>
                        <td>[ <?= strtoupper(htmlspecialchars($row['kategori'])) ?> ]</td>
                        <td style="font-weight: bold;">Rp <?= number_format($row['harga'],0,',','.') ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($row['status']) == 'tersedia' ? 'status-tersedia' : 'status-tidak' ?>">
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit=<?= $row['id_menu'] ?><?= $search ? '&search='.urlencode($search).'&page='.$page : '' ?>" 
                                   class="btn-action btn-edit-action" 
                                   title="Edit menu">
                                    <i class="fas fa-pencil-alt"></i> EDIT
                                </a>
                                <button type="button" 
                                        class="btn-action btn-delete-action delete-btn" 
                                        data-id="<?= $row['id_menu'] ?>"
                                        data-name="<?= htmlspecialchars($row['nama_menu']) ?>"
                                        data-search="<?= htmlspecialchars($search) ?>"
                                        data-page="<?= $page ?>"
                                        title="Hapus menu">
                                    <i class="fas fa-trash-alt"></i> HAPUS
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; font-weight:bold; color:var(--red);">
                            <i class="fas fa-database"></i> [ DATA TIDAK DITEMUKAN DALAM ARSIP ]
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination-area">
            <button class="btn btn-secondary" id="prevBtn" <?= ($page<=1)?'disabled':'' ?> onclick="goToPage(<?= $page-1 ?>)">← PREV</button>
            <span style="font-family:'Special Elite'; font-size: 1.1rem;">HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
            <button class="btn btn-secondary" id="nextBtn" <?= ($page>=$totalPages)?'disabled':'' ?> onclick="goToPage(<?= $page+1 ?>)">NEXT →</button>
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
            <h3>HAPUS MENU?</h3>
            <p>Apakah Anda yakin ingin menghapus menu berikut dari sistem?</p>
            <div class="menu-name-highlight" id="menuNameToDelete"></div>
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

<div id="crudModal" class="modal">
    <div class="modal-content">
        <div class="tape" style="top: -16px; width: 100px; height: 25px;"></div>
        
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px; border-bottom: 2px dashed var(--navy);">
                <span id="modalTitle">TAMBAH MENU BARU</span>
            </div>
        </div>
        
        <div class="modal-body-scroll">
            <form id="menuForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_menu" id="editId" value="">
                <input type="hidden" name="foto_lama" id="fotoLama" value="">
                <input type="hidden" name="foto_cropped" id="fotoCropped" value="">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">NAMA MENU</label>
                        <input type="text" name="nama_menu" id="nama_menu" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">KATEGORI</label>
                        <select name="kategori" id="kategori" class="form-select">
                            <option value="Coffee">Coffee</option>
                            <option value="Non-Coffee">Non-Coffee</option>
                            <option value="Snack">Snack</option>
                            <option value="Main Course">Main Course</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">HARGA (Rp)</label>
                        <input type="number" name="harga" id="harga" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">STATUS KETERSEDIAAN</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Tidak Tersedia">Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">DESKRIPSI (OPSIONAL)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="2" class="form-input"></textarea>
                </div>
                
                <div class="upload-box-safe">
                    <label class="form-label" style="margin-bottom:8px;">UPLOAD FOTO ARSIP (RASIO 6:5)</label>
                    <input type="file" id="fileInput" accept="image/*" style="font-family:'Courier Prime'; font-size:0.8rem; width:100%;">
                    <div id="previewArea" style="margin-top:12px;">
                        <img id="previewImg" style="max-width:110px; border:2px solid var(--navy); display:none; box-shadow: 3px 3px 0 rgba(0,0,0,0.15);">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; padding-bottom: 5px;">
                    <button type="button" class="btn btn-secondary" id="cancelModalBtn">BATAL</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cropModal" class="modal" style="z-index:2100;">
    <div class="modal-content" style="max-width:460px; padding: 20px;">
        <div class="spec-header" style="margin-bottom: 15px;">CROP GAMBAR (6:5)</div>
        <div class="crop-container" style="border: 2px solid var(--navy); background: #000; overflow:hidden;">
            <img id="cropImage" src="" style="max-width:100%; display:block;">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
            <button class="btn btn-secondary" id="cancelCropBtn">BATAL</button>
            <button class="btn btn-primary" id="cropConfirmBtn" style="background:var(--red);">CROP & SET</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // Toggle Sidebar Mobile
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

    // Search Logic
    document.getElementById('searchBtn')?.addEventListener('click', () => {
        let s = document.getElementById('searchInput').value;
        window.location.href = `menu_crud.php?search=${encodeURIComponent(s)}`;
    });
    document.getElementById('searchInput')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') document.getElementById('searchBtn').click();
    });

    function goToPage(page) {
        let s = document.getElementById('searchInput').value;
        window.location.href = `menu_crud.php?page=${page}${s ? '&search='+encodeURIComponent(s) : ''}`;
    }

    // ========== DELETE CONFIRMATION MODAL ==========
    const deleteModal = document.getElementById('deleteConfirmModal');
    const menuNameSpan = document.getElementById('menuNameToDelete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    let currentDeleteUrl = '';

    // Event listener untuk semua tombol delete
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const menuId = this.dataset.id;
            const menuName = this.dataset.name;
            const search = this.dataset.search || '';
            const page = this.dataset.page || '1';
            
            // Set nama menu yang akan dihapus
            menuNameSpan.textContent = menuName;
            
            // Buat URL untuk delete
            let deleteUrl = `?hapus=${menuId}`;
            if (search) deleteUrl += `&search=${encodeURIComponent(search)}`;
            if (page) deleteUrl += `&page=${page}`;
            
            currentDeleteUrl = deleteUrl;
            confirmDeleteBtn.href = currentDeleteUrl;
            
            // Tampilkan modal dengan animasi
            deleteModal.style.display = 'flex';
            
            // Tambah efek shake
            const modalContent = document.querySelector('.confirm-modal-content');
            modalContent.classList.add('warning-shake');
            setTimeout(() => {
                modalContent.classList.remove('warning-shake');
            }, 300);
        });
    });
    
    // Cancel delete
    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });
    
    // Close modal klik di luar
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });
    
    // Escape key untuk close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && deleteModal.style.display === 'flex') {
            deleteModal.style.display = 'none';
        }
    });

    // Modal Control
    const modal = document.getElementById('crudModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const editId = document.getElementById('editId');
    const fotoLama = document.getElementById('fotoLama');
    const namaInput = document.getElementById('nama_menu');
    const kategoriSelect = document.getElementById('kategori');
    const hargaInput = document.getElementById('harga');
    const statusSelect = document.getElementById('status');
    const deskripsiText = document.getElementById('deskripsi');
    const previewImg = document.getElementById('previewImg');
    const fileInput = document.getElementById('fileInput');
    const fotoCropped = document.getElementById('fotoCropped');
    const showModalBtn = document.getElementById('showModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');

    <?php if ($edit_mode): ?>
    window.addEventListener('DOMContentLoaded', () => {
        modalTitle.innerText = 'UPDATE ARSIP MENU';
        formAction.value = 'update';
        editId.value = '<?= $edit_id ?>';
        fotoLama.value = '<?= addslashes($edit_foto) ?>';
        namaInput.value = '<?= addslashes($edit_nama) ?>';
        kategoriSelect.value = '<?= addslashes($edit_kategori) ?>';
        hargaInput.value = '<?= $edit_harga ?>';
        statusSelect.value = '<?= addslashes($edit_status) ?>';
        deskripsiText.value = `<?= addslashes($edit_deskripsi) ?>`;
        if ('<?= $edit_foto ?>' && '<?= $edit_foto ?>' != 'default.jpg') {
            previewImg.src = '../assets/images/menu/<?= $edit_foto ?>';
            previewImg.style.display = 'block';
        }
        modal.style.display = 'flex';
    });
    <?php endif; ?>

    if (showModalBtn) showModalBtn.onclick = () => {
        modalTitle.innerText = 'TAMBAH ENTRI BARU';
        formAction.value = 'add';
        editId.value = ''; fotoLama.value = ''; namaInput.value = '';
        kategoriSelect.value = 'Coffee'; hargaInput.value = '';
        statusSelect.value = 'Tersedia'; deskripsiText.value = '';
        previewImg.style.display = 'none'; fileInput.value = ''; fotoCropped.value = '';
        modal.style.display = 'flex';
    };
    if (cancelModalBtn) cancelModalBtn.onclick = () => modal.style.display = 'none';

    // Cropper Logic
    let cropper;
    const cropModal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const cropConfirm = document.getElementById('cropConfirmBtn');
    const cancelCrop = document.getElementById('cancelCropBtn');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(ev) {
            cropImage.src = ev.target.result;
            cropModal.style.display = 'flex';
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, { aspectRatio: 6/5, viewMode: 1, background: false });
        };
        reader.readAsDataURL(file);
    });

    cropConfirm.addEventListener('click', () => {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 500 });
            const croppedBase64 = canvas.toDataURL('image/jpeg', 0.9);
            fotoCropped.value = croppedBase64;
            previewImg.src = croppedBase64;
            previewImg.style.display = 'block';
            cropModal.style.display = 'none';
            cropper.destroy();
        }
    });

    cancelCrop.addEventListener('click', () => {
        cropModal.style.display = 'none';
        if (cropper) cropper.destroy();
        fileInput.value = '';
    });

    document.getElementById('menuForm')?.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        if (file && !fotoCropped.value) {
            e.preventDefault();
            alert('Wajib memotong (crop) foto sebelum menyimpan!');
        }
    });
</script>
</body>
</html>