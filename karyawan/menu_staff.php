<?php
session_start();
include "../config/koneksi.php";

// Proteksi halaman - hanya karyawan yang bisa akses
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Jika yang login adalah admin/superadmin, redirect ke admin
if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin') {
    header("Location: ../admin/menu_crud.php");
    exit;
}

// Pastikan role adalah karyawan
if ($_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;

// --- LOGIKA CRUD MENU ---
$edit_mode = false;
$edit_id = ""; 
$edit_nama = ""; 
$edit_kategori = ""; 
$edit_harga = ""; 
$edit_status = "Tersedia"; // Ganti stok dengan status
$edit_deskripsi = ""; 
$edit_foto = "";

// Ambil data untuk edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $query_edit = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$edit_id'");
    $data_edit = mysqli_fetch_assoc($query_edit);
    if ($data_edit) {
        $edit_nama = $data_edit['nama_menu'];
        $edit_kategori = $data_edit['kategori'];
        $edit_harga = $data_edit['harga'];
        $edit_status = $data_edit['status'] ?? 'Tersedia'; // Ambil status dari DB
        $edit_deskripsi = $data_edit['deskripsi'];
        $edit_foto = $data_edit['foto'];
    }
}

// SIMPAN MENU BARU
if (isset($_POST['simpan'])) {
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = $_POST['harga'];
    $status = $_POST['status']; // Ambil status
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Handle upload foto
    $foto_nama = "default.jpg";
    if (!empty($_FILES['foto']['tmp_name'])) {
        $target_dir = "../assets/images/menu/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_nama);
    }
    
    $query = "INSERT INTO menu (nama_menu, kategori, harga, status, deskripsi, foto) 
              VALUES ('$nama_menu', '$kategori', '$harga', '$status', '$deskripsi', '$foto_nama')";
    mysqli_query($conn, $query);
    echo "<script>alert('Menu baru berhasil ditambahkan!'); window.location='menu_staff.php';</script>";
    exit;
}

// UPDATE MENU
if (isset($_POST['update'])) {
    $id_menu = $_POST['id_menu'];
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = $_POST['harga'];
    $status = $_POST['status']; // Ambil status
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = $_POST['foto_lama'];
    
    $foto_nama = $foto_lama;
    if (!empty($_FILES['foto']['tmp_name'])) {
        $target_dir = "../assets/images/menu/";
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_nama);
        
        // Hapus foto lama jika bukan default
        if ($foto_lama != 'default.jpg' && file_exists($target_dir . $foto_lama)) {
            unlink($target_dir . $foto_lama);
        }
    }
    
    $query = "UPDATE menu SET nama_menu='$nama_menu', kategori='$kategori', harga='$harga', 
              status='$status', deskripsi='$deskripsi', foto='$foto_nama' WHERE id_menu='$id_menu'";
    mysqli_query($conn, $query);
    echo "<script>alert('Data menu berhasil diperbarui!'); window.location='menu_staff.php';</script>";
    exit;
}

// HAPUS MENU
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $query_foto = mysqli_query($conn, "SELECT foto FROM menu WHERE id_menu='$id_hapus'");
    $data_foto = mysqli_fetch_assoc($query_foto);
    
    if ($data_foto['foto'] != 'default.jpg' && file_exists("../assets/images/menu/" . $data_foto['foto'])) {
        unlink("../assets/images/menu/" . $data_foto['foto']);
    }
    
    mysqli_query($conn, "DELETE FROM menu WHERE id_menu='$id_hapus'");
    echo "<script>alert('Menu berhasil dihapus!'); window.location='menu_staff.php';</script>";
    exit;
}

// Query untuk mendapatkan data menu
$query = mysqli_query($conn, "SELECT * FROM menu ORDER BY kategori, nama_menu");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Menu - Karyawan | Woelandari Coffee Lab</title>
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

        /* --- BUTTON STYLE --- */
        .btn-trigger {
            background: var(--green);
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-family: 'Special Elite', cursive;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .btn-trigger:hover {
            background: var(--navy);
            transform: translateY(-2px);
            box-shadow: 3px 3px 0 var(--green);
        }

        /* --- TABLE STYLING --- */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .aesthetic-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .aesthetic-table th {
            background: var(--navy);
            color: white;
            padding: 15px;
            text-align: left;
            font-family: 'Special Elite', cursive;
            font-size: 0.9rem;
        }

        .aesthetic-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 43, 91, 0.1);
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .aesthetic-table tr:hover {
            background: rgba(0, 43, 91, 0.03);
        }

        .img-3x4 {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border: 2px solid var(--navy);
        }

        .category-badge {
            font-size: 0.7rem;
            background: rgba(0, 43, 91, 0.1);
            padding: 4px 10px;
            border: 1px solid var(--navy);
            display: inline-block;
        }

        .price-tag {
            font-weight: bold;
            color: var(--green);
        }

        /* Badge Status */
        .status-badge {
            font-size: 0.7rem;
            padding: 6px 12px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }

        .status-tersedia {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .status-tidak-tersedia {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid var(--red);
        }

        .btn-action {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.7rem;
            padding: 6px 12px;
            border: 2px solid var(--green);
            margin: 0 3px;
            display: inline-block;
            color: var(--green);
        }

        .btn-action:hover {
            background: var(--green);
            color: white;
        }

        .btn-del {
            border-color: var(--red);
            color: var(--red);
        }

        .btn-del:hover {
            background: var(--red);
            color: white;
        }

        /* --- FORM MODAL STYLE --- */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--navy);
            background: transparent;
            font-family: 'Courier Prime', monospace;
            margin-bottom: 15px;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
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

        /* --- RESPONSIVE --- */
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
            
            .status-badge {
                min-width: 80px;
                font-size: 0.65rem;
                padding: 4px 8px;
            }
        }

        @media (max-width: 480px) {
            .title-main {
                font-size: 1.2rem;
                padding-left: 12px;
            }
            
            .btn-trigger {
                padding: 8px 12px;
                font-size: 0.7rem;
            }
            
            .aesthetic-table th, 
            .aesthetic-table td {
                padding: 10px;
                font-size: 0.75rem;
            }
            
            .img-3x4 {
                width: 50px;
                height: 65px;
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
        <a href="menu_staff.php" class="nav-item active">
            <i class="fas fa-utensils"></i> <span>MENU</span>
        </a>
        <a href="gallery_staff.php" class="nav-item">
            <i class="fas fa-images"></i> <span>GALLERY</span>
        </a>
        <a href="feedback_staff.php" class="nav-item">
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
            <span><i class="fas fa-coffee"></i> WOELANDARI COFFEE LAB // MENU MANAGEMENT</span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h1 class="title-main" style="margin-bottom:0;">DAFTAR MENU</h1>
            <button class="btn-trigger" onclick="openModal('modalMenu')">
                <i class="fas fa-plus"></i> TAMBAH MENU
            </button>
        </div>

        <div class="table-container">
            <table class="aesthetic-table">
                <thead>
                    <tr>
                        <th>PREVIEW</th>
                        <th>NAMA PRODUK</th>
                        <th>KATEGORI</th>
                        <th>HARGA</th>
                        <th>STATUS</th>
                        <th>DESKRIPSI</th>
                        <th style="text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                        <tr>
                            <td>
                                <img src="../assets/images/menu/<?php echo $row['foto']; ?>" 
                                     class="img-3x4" 
                                     onerror="this.src='../assets/images/menu/default.jpg'">
                            </td>
                            <td>
                                <strong style="letter-spacing: 1px;"><?php echo strtoupper(htmlspecialchars($row['nama_menu'])); ?></strong>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="price-tag">
                                    <i class="fas fa-money-bill-wave"></i> Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $status_class = ($row['status'] == 'Tersedia') ? 'status-tersedia' : 'status-tidak-tersedia';
                                $status_icon = ($row['status'] == 'Tersedia') ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_icon; ?> <?php echo strtoupper($row['status']); ?>
                                </span>
                             </div>
                            <td>
                                <small><?php echo nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 80))); ?></small>
                                <?php if(strlen($row['deskripsi']) > 80): ?>...<?php endif; ?>
                               </div>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                    <a href="?edit=<?php echo $row['id_menu']; ?>" class="btn-action">
                                        <i class="fas fa-edit"></i> EDIT
                                    </a>
                                    <a href="?hapus=<?php echo $row['id_menu']; ?>" class="btn-action btn-del" onclick="return confirm('Yakin ingin menghapus menu ini?');">
                                        <i class="fas fa-trash"></i> HAPUS
                                    </a>
                                </div>
                             </div>
                         </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 60px;">
                                <i class="fas fa-box-open" style="font-size: 3rem; opacity: 0.5;"></i><br>
                                Belum ada menu yang tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Statistik ringkasan menu -->
        <div style="margin-top: 30px; padding: 15px; background: rgba(0, 43, 91, 0.05); border: 1px dashed var(--navy);">
            <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: space-between;">
                <?php
                $total_menu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu"))['total'];
                $total_coffee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori='Coffee'"))['total'];
                $total_noncoffee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori='Non-Coffee'"))['total'];
                $total_snack = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori='Snack'"))['total'];
                $total_main = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori='Main Course'"))['total'];
                $total_tersedia = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE status='Tersedia'"))['total'];
                ?>
                <div><i class="fas fa-utensils"></i> <strong>Total Menu:</strong> <?php echo $total_menu; ?></div>
                <div><i class="fas fa-mug-hot"></i> <strong>Coffee:</strong> <?php echo $total_coffee; ?></div>
                <div><i class="fas fa-leaf"></i> <strong>Non-Coffee:</strong> <?php echo $total_noncoffee; ?></div>
                <div><i class="fas fa-bread-slice"></i> <strong>Snack:</strong> <?php echo $total_snack; ?></div>
                <div><i class="fas fa-utensil-spoon"></i> <strong>Main Course:</strong> <?php echo $total_main; ?></div>
                <div><i class="fas fa-check-circle"></i> <strong>Tersedia:</strong> <?php echo $total_tersedia; ?></div>
            </div>
        </div>
    </section>
</main>

<!-- MODAL TAMBAH/EDIT MENU -->
<div class="modal-overlay" id="modalMenu">
    <div class="paper" style="max-width: 500px; width: 100%; transform: rotate(0deg);">
        <div class="spec-header">
            <span>ACTION: <?= $edit_mode ? 'UPDATE MENU' : 'ADD NEW MENU' ?></span>
        </div>
        <form action="menu_staff.php" method="POST" enctype="multipart/form-data">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id_menu" value="<?= $edit_id ?>">
                <input type="hidden" name="foto_lama" value="<?= $edit_foto ?>">
            <?php endif; ?>

            <label class="form-label">NAMA MENU</label>
            <input type="text" name="nama_menu" class="form-input" value="<?= htmlspecialchars($edit_nama) ?>" required>

            <label class="form-label">KATEGORI</label>
            <select name="kategori" class="form-input" required>
                <option value="Coffee" <?= $edit_kategori == 'Coffee' ? 'selected' : '' ?>>Coffee</option>
                <option value="Non-Coffee" <?= $edit_kategori == 'Non-Coffee' ? 'selected' : '' ?>>Non-Coffee</option>
                <option value="Snack" <?= $edit_kategori == 'Snack' ? 'selected' : '' ?>>Snack</option>
                <option value="Main Course" <?= $edit_kategori == 'Main Course' ? 'selected' : '' ?>>Main Course</option>
            </select>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label class="form-label">HARGA (Rp)</label>
                    <input type="number" name="harga" class="form-input" value="<?= $edit_harga ?>" required>
                </div>
                <div>
                    <label class="form-label">STATUS KETERSEDIAAN</label>
                    <select name="status" class="form-input" required>
                        <option value="Tersedia" <?= $edit_status == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Tidak Tersedia" <?= $edit_status == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                    </select>
                </div>
            </div>

            <label class="form-label">DESKRIPSI</label>
            <textarea name="deskripsi" class="form-input" rows="3" required><?= htmlspecialchars($edit_deskripsi) ?></textarea>

            <label class="form-label">FOTO MENU</label>
            <?php if ($edit_mode && $edit_foto && $edit_foto != 'default.jpg'): ?>
                <img src="../assets/images/menu/<?= $edit_foto ?>" style="width:100px; display:block; margin-bottom:10px; border:2px solid var(--navy);">
            <?php endif; ?>
            <input type="file" name="foto" class="form-input" accept="image/*" <?= $edit_mode ? '' : 'required' ?>>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="<?= $edit_mode ? 'update' : 'simpan' ?>" class="btn-trigger" style="flex:1;">
                    <i class="fas fa-save"></i> SIMPAN
                </button>
                <button type="button" class="btn-action btn-del" style="flex:1; text-align:center; cursor:pointer;" onclick="closeModal('modalMenu');">
                    <i class="fas fa-times"></i> BATAL
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
        window.location.href = 'menu_staff.php';
    }

    <?php if ($edit_mode): ?>
        openModal('modalMenu');
    <?php endif; ?>
</script>

</body>
</html>