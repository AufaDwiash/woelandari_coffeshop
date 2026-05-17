<?php
// dashboard/gallery_crud.php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Konfigurasi Tab & Paginasi
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gallery';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Batas jumlah per halaman (konsisten 5 data)
$offset = ($page - 1) * $limit;

// ==========================================
// LOGIKA GALLERY
// ==========================================
$edit_gal_mode = false;
$gal_id = $gal_judul = $gal_deskripsi = $gal_event = $gal_tipe = $gal_foto = "";

if (isset($_GET['edit_gallery'])) {
    $edit_gal_mode = true;
    $id_g = (int)$_GET['edit_gallery'];
    $q = mysqli_query($conn, "SELECT * FROM gallery WHERE id_gallery = $id_g");
    if ($d = mysqli_fetch_assoc($q)) {
        $gal_id = $d['id_gallery'];
        $gal_judul = $d['judul'];
        $gal_deskripsi = $d['deskripsi'];
        $gal_event = $d['id_event'];
        $gal_tipe = $d['tipe'];
        $gal_foto = $d['file_foto'];
    }
}

if (isset($_POST['simpan_gallery']) || isset($_POST['update_gallery'])) {
    $id_gal = (int)$_POST['id_gallery'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe']);
    $id_ev = !empty($_POST['id_event']) ? "'" . (int)$_POST['id_event'] . "'" : "NULL";

    // Cek apakah ada hasil CROP BASE64
    $foto_isi = "";
    $query_foto = "";
    if (!empty($_POST['foto_cropped_gallery'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_gallery']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_isi = addslashes($img_base64); // Konversi Base64 ke format BLOB
        $query_foto = ", file_foto='$foto_isi'";
    } elseif (!empty($_FILES['file_foto']['tmp_name'])) {
        $foto_isi = addslashes(file_get_contents($_FILES['file_foto']['tmp_name']));
        $query_foto = ", file_foto='$foto_isi'";
    }

    if (isset($_POST['simpan_gallery'])) {
        mysqli_query($conn, "INSERT INTO gallery (judul, deskripsi, id_event, tipe, file_foto) VALUES ('$judul', '$desk', $id_ev, '$tipe', '$foto_isi')");
    } else {
        mysqli_query($conn, "UPDATE gallery SET judul='$judul', deskripsi='$desk', id_event=$id_ev, tipe='$tipe' $query_foto WHERE id_gallery='$id_gal'");
    }
    header("Location: gallery_crud.php?tab=gallery");
    exit;
}

if (isset($_GET['hapus_gallery'])) {
    $id = (int)$_GET['hapus_gallery'];
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery=$id");
    header("Location: gallery_crud.php?tab=gallery");
    exit;
}

// ==========================================
// LOGIKA EVENT
// ==========================================
$edit_event_mode = false;
$ev_id = $ev_judul = $ev_tanggal = $ev_deskripsi = $ev_status = $ev_foto = "";

if (isset($_GET['edit_event'])) {
    $edit_event_mode = true;
    $id_e = (int)$_GET['edit_event'];
    $q = mysqli_query($conn, "SELECT * FROM events WHERE id_event = $id_e");
    if ($d = mysqli_fetch_assoc($q)) {
        $ev_id = $d['id_event'];
        $ev_judul = $d['judul_event'];
        $ev_tanggal = $d['tanggal_event'];
        $ev_deskripsi = $d['deskripsi_event'];
        $ev_status = $d['status_event'];
        $ev_foto = $d['foto_cover'];
    }
}

if (isset($_POST['simpan_event']) || isset($_POST['update_event'])) {
    $id_ev = (int)$_POST['id_event'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul_event']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_event']);
    $desk = mysqli_real_escape_string($conn, $_POST['deskripsi_event']);
    $status = mysqli_real_escape_string($conn, $_POST['status_event']);

    // Cek apakah ada hasil CROP BASE64
    $foto_isi = "";
    $query_foto = "";
    if (!empty($_POST['foto_cropped_event'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_event']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_isi = addslashes($img_base64); // Konversi Base64 ke format BLOB
        $query_foto = ", foto_cover='$foto_isi'";
    } elseif (!empty($_FILES['foto_cover']['tmp_name'])) {
        $foto_isi = addslashes(file_get_contents($_FILES['foto_cover']['tmp_name']));
        $query_foto = ", foto_cover='$foto_isi'";
    }

    if (isset($_POST['simpan_event'])) {
        mysqli_query($conn, "INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, foto_cover) VALUES ('$judul', '$tanggal', '$desk', '$status', '$foto_isi')");
    } else {
        mysqli_query($conn, "UPDATE events SET judul_event='$judul', tanggal_event='$tanggal', deskripsi_event='$desk', status_event='$status' $query_foto WHERE id_event='$id_ev'");
    }
    header("Location: gallery_crud.php?tab=event");
    exit;
}

if (isset($_GET['hapus_event'])) {
    $id = (int)$_GET['hapus_event'];
    mysqli_query($conn, "DELETE FROM events WHERE id_event=$id");
    header("Location: gallery_crud.php?tab=event");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gallery & Event - Woelandari Coffee Lab</title>
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
            position: absolute; top: -16px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 32px;
            background: rgba(234, 67, 53, 0.9);
            border: 1px dashed rgba(255,255,255,0.5);
            z-index: 10;
            box-shadow: 2px 3px 5px rgba(0,0,0,0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 25px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 25px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        /* TABS */
        .tab-buttons {
            display: flex; gap: 10px; margin-bottom: 30px; 
            border-bottom: 4px solid var(--navy); padding-bottom: 0;
            flex-wrap: wrap;
        }
        .tab-btn {
            font-family: 'Special Elite', cursive;
            padding: 12px 25px; border: 3px solid var(--navy); border-bottom: none;
            background: rgba(0, 43, 91, 0.05); color: var(--navy); font-weight: bold; cursor: pointer; text-decoration: none;
            border-radius: 8px 8px 0 0; transition: all 0.2s ease;
            position: relative; top: 4px;
        }
        .tab-btn:hover { background: rgba(0, 43, 91, 0.1); }
        .tab-btn.active {
            background: var(--navy); color: var(--white);
            box-shadow: 4px -4px 0 var(--red);
            z-index: 2; top: 0; padding-bottom: 16px;
        }

        /* SEARCH */
        .search-area {
            display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; align-items: center;
            background: rgba(0, 43, 91, 0.03); padding: 15px; border: 2px solid var(--navy);
        }
        .search-wrapper { flex: 1; position: relative; min-width: 200px; height: 46px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--navy); }
        .search-input {
            width: 100%; height: 100%; padding: 10px 10px 10px 40px;
            border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime', monospace; font-weight: bold; font-size: 0.9rem; outline: none;
        }

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

        /* TABLE */
        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white; margin-bottom: 20px;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }
        
        .data-table { width: 100%; border-collapse: collapse; min-width: 750px; table-layout: fixed; }
        .data-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        .data-table th.col-img { width: 100px; text-align: center; }
        .data-table th.col-title { width: auto; }
        .data-table th.col-date { width: 130px; }
        .data-table th.col-status { width: 140px; text-align: center; }
        .data-table th.col-action { width: 160px; text-align: center; }

        .data-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .data-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }
        
        .thumb-img { width: 60px; height: 60px; object-fit: cover; border: 2px solid var(--navy); padding: 2px; background: white; box-shadow: 2px 2px 0 var(--navy);}
        .action-buttons { display: inline-flex; gap: 8px; justify-content: center; width: 100%; }

        .status-badge { padding: 4px 10px; border-radius: 2px; font-size: 0.75rem; font-weight: bold; border: 1px solid currentColor; display: inline-block; }
        .status-active { background: rgba(21, 87, 36, 0.08); color: #155724; }
        .status-pending { background: rgba(133, 100, 4, 0.08); color: #856404; }

        /* PAGINASI */
        .pagination-area {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 25px; padding-top: 15px; border-top: 2px dashed var(--navy); font-weight: bold;
        }

        /* MODAL */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.65); backdrop-filter: blur(4px); z-index: 2000;
            justify-content: center; align-items: center; padding: 15px;
        }
        .modal-content {
            background: var(--white); border: 4px solid var(--navy);
            width: 100%; max-width: 650px; max-height: 92vh; 
            display: flex; flex-direction: column;
            box-shadow: 14px 14px 0 var(--red); position: relative;
        }
        .modal-header-area { padding: 25px 25px 10px 25px; flex-shrink: 0; }
        .modal-body-scroll { padding: 10px 25px 25px 25px; overflow-y: auto; flex: 1; }
        .modal-body-scroll::-webkit-scrollbar { width: 6px; }
        .modal-body-scroll::-webkit-scrollbar-thumb { background: var(--navy); }

        .form-grid { display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: bold; font-size: 0.85rem; margin-bottom: 6px; color: var(--navy); text-transform: uppercase; }
        .form-input, .form-select, textarea {
            width: 100%; padding: 10px; border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime'; outline: none; box-shadow: inset 2px 2px 0 rgba(0,0,0,0.03);
        }
        .form-input:focus, .form-select:focus, textarea:focus { border-color: var(--red); }

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
            .tab-btn { width: 100%; text-align: center; border-radius: 0; top: 0; border-bottom: 3px solid var(--navy); }
            .tab-btn.active { padding-bottom: 12px; }
            .search-area { flex-direction: column; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .btn { width: 100%; }
            .pagination-area { flex-direction: column; gap: 15px; text-align: center; }
            .pagination-area .btn { width: auto; }
        }
    </style>
</head>
<body>

<div class="overlay" id="sidebarOverlay"></div>

<?php include "../components/sidebar.php"; ?>

<main class="main-wrapper">
    <div class="mobile-header">
        <div class="logo-mobile" style="font-family:'Special Elite'; color:var(--navy); font-size: 1.2rem;">
            <i class="fas fa-images" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header">
            <span><i class="fas fa-folder-open"></i> ARCHIVE_SYS // VISUAL & EVENTS</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>

        <div class="tab-buttons">
            <a href="?tab=gallery" class="tab-btn <?= $active_tab == 'gallery' ? 'active' : '' ?>">
                <i class="fas fa-image"></i> GALLERY
            </a>
            <a href="?tab=event" class="tab-btn <?= $active_tab == 'event' ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i> EVENTS
            </a>
        </div>

        <div class="search-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari judul <?= $active_tab == 'gallery' ? 'foto' : 'event' ?>..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button class="btn btn-primary" id="searchBtn">CARI DATA</button>
            <?php if ($search): ?>
                <a href="gallery_crud.php?tab=<?= $active_tab ?>" class="btn btn-secondary">RESET</a>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="openModal('<?= $active_tab == 'gallery' ? 'modalGallery' : 'modalEvent' ?>')" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);">
                <i class="fas fa-plus"></i> ADD ENTRY
            </button>
        </div>

        <?php if ($active_tab == 'gallery'): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-img">FOTO</th>
                            <th class="col-title">JUDUL & KETERANGAN</th>
                            <th class="col-status">KATEGORI EVENT</th>
                            <th class="col-action">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $safe_search = mysqli_real_escape_string($conn, $search);
                        $where = $search ? "WHERE g.judul LIKE '%$safe_search%'" : "";
                        
                        // Hitung total halaman
                        $count_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM gallery g $where"));
                        $totalPages = ceil($count_q['total'] / $limit);

                        $q = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event $where ORDER BY g.id_gallery DESC LIMIT $limit OFFSET $offset");
                        
                        if (mysqli_num_rows($q) > 0):
                            while ($row = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if($row['file_foto']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['file_foto']) ?>" class="thumb-img">
                                    <?php else: ?>
                                        <div style="width:60px; height:60px; background:#ddd; display:inline-block;"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--navy); font-size: 1.1rem;"><?= htmlspecialchars($row['judul']) ?></strong><br>
                                    <span style="font-size: 0.8rem; opacity: 0.8;"><?= htmlspecialchars(substr($row['deskripsi'],0,70)) ?>...</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="status-badge status-active">
                                        <?= $row['judul_event'] ? strtoupper(htmlspecialchars($row['judul_event'])) : 'GENERAL / UMUM' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?tab=gallery&edit_gallery=<?= $row['id_gallery'] ?><?= $search ? '&search='.urlencode($search) : '' ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">EDIT</a>
                                        <a href="?tab=gallery&hapus_gallery=<?= $row['id_gallery'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus gallery ini?')">DEL</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:40px; font-weight:bold; color:var(--red);">[ DATA GALLERY TIDAK DITEMUKAN ]</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-img">COVER</th>
                            <th class="col-title">JUDUL EVENT</th>
                            <th class="col-date">TANGGAL</th>
                            <th class="col-status">STATUS</th>
                            <th class="col-action">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $safe_search = mysqli_real_escape_string($conn, $search);
                        $where = $search ? "WHERE judul_event LIKE '%$safe_search%'" : "";
                        
                        // Hitung total halaman
                        $count_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events $where"));
                        $totalPages = ceil($count_q['total'] / $limit);

                        $q = mysqli_query($conn, "SELECT * FROM events $where ORDER BY tanggal_event DESC LIMIT $limit OFFSET $offset");
                        
                        if (mysqli_num_rows($q) > 0):
                            while ($row = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if($row['foto_cover']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['foto_cover']) ?>" class="thumb-img">
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color: var(--navy); font-size: 1.1rem;"><?= htmlspecialchars($row['judul_event']) ?></strong></td>
                                <td><i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($row['tanggal_event'])) ?></td>
                                <td style="text-align: center;">
                                    <span class="status-badge <?= $row['status_event'] == 'selesai' ? 'status-pending' : 'status-active' ?>">
                                        <?= strtoupper($row['status_event']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?tab=event&edit_event=<?= $row['id_event'] ?><?= $search ? '&search='.urlencode($search) : '' ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">EDIT</a>
                                        <a href="?tab=event&hapus_event=<?= $row['id_event'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus event ini?')">DEL</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:40px; font-weight:bold; color:var(--red);">[ DATA EVENT TIDAK DITEMUKAN ]</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="pagination-area">
            <button class="btn btn-secondary" <?= ($page <= 1) ? 'disabled' : '' ?> onclick="goToPage(<?= $page - 1 ?>)">← PREV</button>
            <span style="font-family:'Special Elite'; font-size: 1.1rem;">HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
            <button class="btn btn-secondary" <?= ($page >= $totalPages) ? 'disabled' : '' ?> onclick="goToPage(<?= $page + 1 ?>)">NEXT →</button>
        </div>
        <?php endif; ?>

    </section>
</main>

<div class="modal" id="modalGallery">
    <div class="modal-content">
        <div class="tape" style="top: -16px; width: 100px; height: 25px;"></div>
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px; border-bottom: 2px dashed var(--navy);">
                <span><?= $edit_gal_mode ? 'UPDATE ARSIP GALLERY' : 'TAMBAH FOTO GALLERY' ?></span>
            </div>
        </div>
        <div class="modal-body-scroll">
            <form id="formGallery" action="gallery_crud.php?tab=gallery" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_gallery" value="<?= $gal_id ?>">
                
                <div class="form-group">
                    <label class="form-label">JUDUL FOTO</label>
                    <input type="text" name="judul" class="form-input" value="<?= htmlspecialchars($gal_judul) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">DESKRIPSI SINGKAT</label>
                    <textarea name="deskripsi" class="form-input" rows="2"><?= htmlspecialchars($gal_deskripsi) ?></textarea>
                </div>
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">TAUTKAN KE EVENT</label>
                        <select name="id_event" class="form-select">
                            <option value="">-- TANPA EVENT (UMUM) --</option>
                            <?php
                            $evs = mysqli_query($conn, "SELECT id_event, judul_event FROM events");
                            while ($ev = mysqli_fetch_assoc($evs)): ?>
                                <option value="<?= $ev['id_event'] ?>" <?= $gal_event == $ev['id_event'] ? 'selected' : '' ?>><?= htmlspecialchars($ev['judul_event']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">TIPE FILE</label>
                        <select name="tipe" class="form-select">
                            <option value="event" <?= $gal_tipe == 'event' ? 'selected' : '' ?>>EVENT</option>
                            <option value="profil" <?= $gal_tipe == 'profil' ? 'selected' : '' ?>>PROFIL</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="background: rgba(0,43,91,0.04); padding: 15px; border: 2px dashed var(--navy);">
                    <label class="form-label" style="margin-bottom:8px;">UPLOAD FILE FOTO (RASIO 1:1)</label>
                    
                    <input type="hidden" name="foto_cropped_gallery" id="foto_cropped_gallery" value="">
                    
                    <?php if ($gal_foto): ?>
                        <div style="margin-bottom:10px;">
                            <img src="data:image/jpeg;base64,<?= base64_encode($gal_foto) ?>" style="width:120px; border:2px solid var(--navy); box-shadow: 3px 3px 0 var(--red);">
                            <p style="font-size:0.75rem; font-weight:bold; margin-top:5px;">[ FOTO SAAT INI ]</p>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="file_foto_gallery" name="file_foto" class="form-input" accept="image/*" style="font-family:'Courier Prime'; font-size:0.8rem;" <?= $edit_gal_mode ? '' : 'required' ?>>
                    
                    <div id="previewAreaGallery" style="display:none; margin-top:15px; border-top: 1px dashed var(--navy); padding-top: 15px;">
                        <p style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px;">[ PREVIEW HASIL CROP ]</p>
                        <img id="preview_gallery" style="max-width:120px; border:2px solid var(--navy); box-shadow: 4px 4px 0 rgba(0,0,0,0.15);">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; padding-bottom: 5px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalGallery')">BATAL</button>
                    <button type="submit" name="<?= $edit_gal_mode ? 'update_gallery' : 'simpan_gallery' ?>" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="modalEvent">
    <div class="modal-content">
        <div class="tape" style="top: -16px; width: 100px; height: 25px;"></div>
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px; border-bottom: 2px dashed var(--navy);">
                <span><?= $edit_event_mode ? 'UPDATE ARSIP EVENT' : 'BUAT EVENT BARU' ?></span>
            </div>
        </div>
        <div class="modal-body-scroll">
            <form id="formEvent" action="gallery_crud.php?tab=event" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_event" value="<?= $ev_id ?>">
                
                <div class="form-group">
                    <label class="form-label">JUDUL EVENT</label>
                    <input type="text" name="judul_event" class="form-input" value="<?= htmlspecialchars($ev_judul) ?>" required>
                </div>
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">TANGGAL PELAKSANAAN</label>
                        <input type="date" name="tanggal_event" class="form-input" value="<?= $ev_tanggal ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">STATUS EVENT</label>
                        <select name="status_event" class="form-select">
                            <option value="mendatang" <?= $ev_status == 'mendatang' ? 'selected' : '' ?>>MENDATANG</option>
                            <option value="selesai" <?= $ev_status == 'selesai' ? 'selected' : '' ?>>SELESAI</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">DESKRIPSI LENGKAP</label>
                    <textarea name="deskripsi_event" class="form-input" rows="3" required><?= htmlspecialchars($ev_deskripsi) ?></textarea>
                </div>
                
                <div class="form-group" style="background: rgba(0,43,91,0.04); padding: 15px; border: 2px dashed var(--navy);">
                    <label class="form-label" style="margin-bottom:8px;">UPLOAD COVER EVENT (RASIO 1:1)</label>
                    
                    <input type="hidden" name="foto_cropped_event" id="foto_cropped_event" value="">

                    <?php if ($ev_foto): ?>
                        <div style="margin-bottom:10px;">
                            <img src="data:image/jpeg;base64,<?= base64_encode($ev_foto) ?>" style="width:120px; border:2px solid var(--navy); box-shadow: 3px 3px 0 var(--red);">
                            <p style="font-size:0.75rem; font-weight:bold; margin-top:5px;">[ COVER SAAT INI ]</p>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="foto_cover_event" name="foto_cover" class="form-input" accept="image/*" style="font-family:'Courier Prime'; font-size:0.8rem;" <?= $edit_event_mode ? '' : 'required' ?>>
                    
                    <div id="previewAreaEvent" style="display:none; margin-top:15px; border-top: 1px dashed var(--navy); padding-top: 15px;">
                        <p style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px;">[ PREVIEW HASIL CROP ]</p>
                        <img id="preview_event" style="max-width:120px; border:2px solid var(--navy); box-shadow: 4px 4px 0 rgba(0,0,0,0.15);">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; padding-bottom: 5px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEvent')">BATAL</button>
                    <button type="submit" name="<?= $edit_event_mode ? 'update_event' : 'simpan_event' ?>" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cropModal" class="modal" style="z-index:2100;">
    <div class="modal-content" style="max-width:460px; padding: 20px;">
        <div class="spec-header" style="margin-bottom: 15px;" id="cropTitle">CROP GAMBAR (1:1)</div>
        <div style="border: 2px solid var(--navy); background: #000; overflow:hidden;">
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
    // Sidebar Logic
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

    // Modal Form Logic
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { 
        document.getElementById(id).style.display = 'none'; 
        window.location.href = 'gallery_crud.php?tab=<?= $active_tab ?>'; 
    }
    
    // Auto-open modal on edit
    <?php if ($edit_gal_mode) echo "window.addEventListener('DOMContentLoaded', () => openModal('modalGallery'));"; ?>
    <?php if ($edit_event_mode) echo "window.addEventListener('DOMContentLoaded', () => openModal('modalEvent'));"; ?>

    // Search Logic
    document.getElementById('searchBtn')?.addEventListener('click', () => {
        let s = document.getElementById('searchInput').value;
        window.location.href = `gallery_crud.php?tab=<?= $active_tab ?>&search=${encodeURIComponent(s)}`;
    });
    document.getElementById('searchInput')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') document.getElementById('searchBtn').click();
    });

    // Pagination Logic
    function goToPage(page) {
        let s = document.getElementById('searchInput').value;
        window.location.href = `gallery_crud.php?tab=<?= $active_tab ?>&page=${page}${s ? '&search='+encodeURIComponent(s) : ''}`;
    }

    // ==========================================
    // CROPPER LOGIC (Base64 Murni) 1:1
    // ==========================================
    let cropper;
    let targetCropType = ''; // 'gallery' atau 'event'
    
    const cropModal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const cropTitle = document.getElementById('cropTitle');
    const cropConfirmBtn = document.getElementById('cropConfirmBtn');
    const cancelCropBtn = document.getElementById('cancelCropBtn');

    // Fungsi Trigger Cropper
    function initCropper(file, targetType) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) { 
            alert('Hanya format JPG, PNG, WEBP yang diperbolehkan!'); return; 
        }

        targetCropType = targetType;
        const reader = new FileReader();
        
        reader.onload = function(e) {
            cropImage.src = e.target.result;
            cropModal.style.display = 'flex';
            
            if (cropper) cropper.destroy();
            
            // Set Aspect Ratio ke 1:1 untuk semuanya (Gallery & Event)
            let ratio = 1;
            cropTitle.innerText = targetType === 'gallery' ? 'CROP FOTO GALLERY (1:1)' : 'CROP COVER EVENT (1:1)';

            cropper = new Cropper(cropImage, { aspectRatio: ratio, viewMode: 1 });
        };
        reader.readAsDataURL(file);
    }

    // Listener Input File
    document.getElementById('file_foto_gallery').addEventListener('change', function(e) {
        if(e.target.files[0]) initCropper(e.target.files[0], 'gallery');
    });
    
    document.getElementById('foto_cover_event').addEventListener('change', function(e) {
        if(e.target.files[0]) initCropper(e.target.files[0], 'event');
    });

    // Tombol Konfirmasi Crop
    cropConfirmBtn.addEventListener('click', () => {
        if (cropper) {
            // Ukuran hasil potongan diatur menjadi bujur sangkar 600x600 px
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 });
            const base64 = canvas.toDataURL('image/jpeg', 0.9);
            
            if (targetCropType === 'gallery') {
                document.getElementById('foto_cropped_gallery').value = base64;
                document.getElementById('preview_gallery').src = base64;
                document.getElementById('previewAreaGallery').style.display = 'block';
                document.getElementById('file_foto_gallery').removeAttribute('required');
            } else {
                document.getElementById('foto_cropped_event').value = base64;
                document.getElementById('preview_event').src = base64;
                document.getElementById('previewAreaEvent').style.display = 'block';
                document.getElementById('foto_cover_event').removeAttribute('required');
            }
            
            cropModal.style.display = 'none';
            cropper.destroy();
        }
    });

    // Tombol Batal Crop
    cancelCropBtn.addEventListener('click', () => {
        cropModal.style.display = 'none';
        if (cropper) cropper.destroy();
        
        if (targetCropType === 'gallery') {
            document.getElementById('file_foto_gallery').value = '';
        } else {
            document.getElementById('foto_cover_event').value = '';
        }
    });
</script>
</body>
</html>