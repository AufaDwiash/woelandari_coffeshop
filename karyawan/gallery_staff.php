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
    header("Location: ../admin/gallery_crud.php");
    exit;
}

// Pastikan role adalah karyawan
if ($_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gallery';

// --- INISIALISASI VARIABEL ---
$edit_event_mode = false;
$edit_gal_mode = false;

// Variabel Penampung Data Modal (Event)
$ev_id = $ev_judul = $ev_tanggal = $ev_deskripsi = $ev_status = $ev_foto = "";
// Variabel Penampung Data Modal (Gallery)
$gal_id = $gal_judul = $gal_deskripsi = $gal_event = $gal_tipe = $gal_foto = "";

// --- LOGIKA EDIT (AMBIL DATA) ---
if (isset($_GET['edit_event'])) {
    $edit_event_mode = true;
    $id_e = $_GET['edit_event'];
    $q_edit = mysqli_query($conn, "SELECT * FROM events WHERE id_event = '$id_e'");
    $d_e = mysqli_fetch_assoc($q_edit);
    if ($d_e) {
        $ev_id = $d_e['id_event'];
        $ev_judul = $d_e['judul_event'];
        $ev_tanggal = $d_e['tanggal_event'];
        $ev_deskripsi = $d_e['deskripsi_event'];
        $ev_status = $d_e['status_event'];
        $ev_foto = $d_e['foto_cover'];
    }
}

if (isset($_GET['edit_gallery'])) {
    $edit_gal_mode = true;
    $id_g = $_GET['edit_gallery'];
    $q_edit = mysqli_query($conn, "SELECT * FROM gallery WHERE id_gallery = '$id_g'");
    $d_g = mysqli_fetch_assoc($q_edit);
    if ($d_g) {
        $gal_id = $d_g['id_gallery'];
        $gal_judul = $d_g['judul'];
        $gal_deskripsi = $d_g['deskripsi'];
        $gal_event = $d_g['id_event'];
        $gal_tipe = $d_g['tipe'];
        $gal_foto = $d_g['file_foto'];
    }
}

// --- LOGIKA SIMPAN/UPDATE ---
if (isset($_POST['simpan_event']) || isset($_POST['update_event'])) {
    $id_event = $_POST['id_event'];
    $judul = $_POST['judul_event'];
    $tanggal = $_POST['tanggal_event'];
    $desk = $_POST['deskripsi_event'];
    $status = $_POST['status_event'];

    if (!empty($_FILES['foto_cover']['tmp_name'])) {
        $foto_isi = addslashes(file_get_contents($_FILES['foto_cover']['tmp_name']));
        $query_foto = ", foto_cover='$foto_isi'";
    } else {
        $query_foto = "";
    }

    if (isset($_POST['simpan_event'])) {
        $foto_isi = isset($foto_isi) ? $foto_isi : "";
        $q = "INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, foto_cover) 
              VALUES ('$judul', '$tanggal', '$desk', '$status', '$foto_isi')";
    } else {
        $q = "UPDATE events SET judul_event='$judul', tanggal_event='$tanggal', 
              deskripsi_event='$desk', status_event='$status' $query_foto WHERE id_event='$id_event'";
    }
    if (mysqli_query($conn, $q)) {
        header("location:gallery_staff.php?tab=event");
        exit;
    }
}

if (isset($_POST['simpan_gallery']) || isset($_POST['update_gallery'])) {
    $id_gal = $_POST['id_gallery'];
    $judul  = $_POST['judul'];
    $desk   = $_POST['deskripsi'];
    $tipe   = $_POST['tipe'];

    $id_ev  = !empty($_POST['id_event']) ? "'" . $_POST['id_event'] . "'" : "NULL";

    if (!empty($_FILES['file_foto']['tmp_name'])) {
        $foto_isi = addslashes(file_get_contents($_FILES['file_foto']['tmp_name']));
        $query_foto = ", file_foto='$foto_isi'";
    } else {
        $query_foto = "";
    }

    if (isset($_POST['simpan_gallery'])) {
        $foto_isi = isset($foto_isi) ? $foto_isi : "";
        $q = "INSERT INTO gallery (judul, deskripsi, id_event, tipe, file_foto) 
              VALUES ('$judul', '$desk', $id_ev, '$tipe', '$foto_isi')";
    } else {
        $q = "UPDATE gallery SET judul='$judul', deskripsi='$desk', id_event=$id_ev, tipe='$tipe' $query_foto 
              WHERE id_gallery='$id_gal'";
    }

    if (mysqli_query($conn, $q)) {
        header("location:gallery_staff.php?tab=gallery");
        exit;
    } else {
        die("Error: " . mysqli_error($conn));
    }
}

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus_event'])) {
    mysqli_query($conn, "DELETE FROM events WHERE id_event='" . $_GET['hapus_event'] . "'");
    header("location:gallery_staff.php?tab=event");
    exit;
}
if (isset($_GET['hapus_gallery'])) {
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery='" . $_GET['hapus_gallery'] . "'");
    header("location:gallery_staff.php?tab=gallery");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Gallery & Event - Karyawan | Woelandari Coffee Lab</title>
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

        .brutalist-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .brutalist-table th {
            background: var(--navy);
            color: white;
            padding: 15px;
            text-align: left;
            font-family: 'Special Elite', cursive;
            font-size: 0.9rem;
        }

        .brutalist-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 43, 91, 0.1);
            font-size: 0.9rem;
        }

        .brutalist-table tr:hover {
            background: rgba(0, 43, 91, 0.03);
        }

        .img-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid var(--navy);
        }

        .tag {
            background: rgba(0, 43, 91, 0.1);
            padding: 4px 8px;
            font-size: 0.7rem;
            border: 1px solid var(--navy);
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

        /* --- MODAL STYLE --- */
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

        .brutalist-input {
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

        .action-group {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            flex-wrap: wrap;
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
            
            .action-group {
                justify-content: center;
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
        <a href="gallery_staff.php" class="nav-item active">
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
    <section class="paper paper-style-1" style="padding: 20px 40px;">
        <div class="tape"></div>
       
        
        <div class="spec-header">
            <span><i class="fas fa-coffee"></i> WOELANDARI COFFEE LAB // <?php echo ($active_tab == 'gallery') ? 'VISUAL ARCHIVE' : 'EVENT SCHEDULE'; ?></span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="?tab=gallery" class="nav-item <?php echo ($active_tab == 'gallery') ? 'active' : ''; ?>" style="margin-bottom:0; flex: 1; text-align: center;">
                <i class="fas fa-images"></i> GALLERY
            </a>
            <a href="?tab=event" class="nav-item <?php echo ($active_tab == 'event') ? 'active' : ''; ?>" style="margin-bottom:0; flex: 1; text-align: center;">
                <i class="fas fa-calendar-alt"></i> EVENTS
            </a>
        </div>
    </section>

    <section class="paper paper-style-2">
        <div class="spec-header">
            <span>MODULE: <?php echo ($active_tab == 'gallery') ? 'VISUAL_ARCHIVE' : 'SCHEDULE_ARCHIVE'; ?></span>
            <span>REF: WLDRI-<?php echo ($active_tab == 'gallery') ? 'GAL' : 'EVT'; ?></span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h1 class="title-main" style="margin-bottom:0;">DATABASE_LIST</h1>
            <button class="btn-trigger" onclick="openModal('<?php echo ($active_tab == 'gallery') ? 'modalGallery' : 'modalEvent'; ?>')">
                <i class="fas fa-plus"></i> ADD NEW RECORD
            </button>
        </div>

        <div class="table-container">
            <table class="brutalist-table">
                <thead>
                    <?php if ($active_tab == 'gallery'): ?>
                        <tr>
                            <th>PREVIEW</th>
                            <th>IDENTIFICATION</th>
                            <th>LINK EVENT</th>
                            <th style="text-align: right;">ACTION</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>COVER</th>
                            <th>EVENT NAME</th>
                            <th>DATE</th>
                            <th>STATUS</th>
                            <th style="text-align: right;">ACTION</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php
                    if ($active_tab == 'gallery'):
                        $q = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event ORDER BY g.id_gallery DESC");
                        if (mysqli_num_rows($q) > 0):
                            while ($r = mysqli_fetch_assoc($q)): ?>
                                <tr>
                                    <td><img src="data:image/jpeg;base64,<?= base64_encode($r['file_foto']) ?>" class="img-preview"></td>
                                    <td><strong><?php echo strtoupper(htmlspecialchars($r['judul'])); ?></strong><br><small><?php echo htmlspecialchars(substr($r['deskripsi'], 0, 50)); ?>...</small></td>
                                    <td><span class="tag"><?php echo $r['judul_event'] ? strtoupper($r['judul_event']) : 'GENERAL'; ?></span></td>
                                    <td style="text-align: right;">
                                        <div class="action-group">
                                            <a href="?tab=gallery&edit_gallery=<?php echo $r['id_gallery']; ?>" class="btn-action"><i class="fas fa-edit"></i> EDIT</a>
                                            <a href="?tab=gallery&hapus_gallery=<?php echo $r['id_gallery']; ?>" class="btn-action btn-del" onclick="return confirm('Yakin ingin menghapus data ini?');"><i class="fas fa-trash"></i> DELETE</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="4" style="text-align: center; padding: 60px;"><i class="fas fa-box-open" style="font-size: 3rem;"></i><br>Belum ada data gallery</td></tr>
                        <?php endif;
                    else:
                        $q = mysqli_query($conn, "SELECT * FROM events ORDER BY tanggal_event DESC");
                        if (mysqli_num_rows($q) > 0):
                            while ($r = mysqli_fetch_assoc($q)): ?>
                                <tr>
                                    <td><img src="data:image/jpeg;base64,<?= base64_encode($r['foto_cover']) ?>" class="img-preview"></td>
                                    <td><strong><?php echo strtoupper(htmlspecialchars($r['judul_event'])); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($r['tanggal_event'])); ?></td>
                                    <td><span class="tag <?php echo ($r['status_event'] == 'mendatang') ? '' : 'btn-del'; ?>"><?php echo strtoupper($r['status_event']); ?></span></td>
                                    <td style="text-align: right;">
                                        <div class="action-group">
                                            <a href="?tab=event&edit_event=<?php echo $r['id_event']; ?>" class="btn-action"><i class="fas fa-edit"></i> EDIT</a>
                                            <a href="?tab=event&hapus_event=<?php echo $r['id_event']; ?>" class="btn-action btn-del" onclick="return confirm('Yakin ingin menghapus event ini?');"><i class="fas fa-trash"></i> DELETE</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 60px;"><i class="fas fa-calendar-times" style="font-size: 3rem;"></i><br>Belum ada event</td></tr>
                        <?php endif;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- MODAL GALLERY -->
<div class="modal-overlay" id="modalGallery">
    <div class="paper" style="max-width: 500px; width: 100%; transform: rotate(0deg);">
        <div class="spec-header"><span>ACTION: <?= $edit_gal_mode ? 'UPDATE_GALLERY' : 'ADD NEW GALLERY' ?></span></div>
        <form action="gallery_staff.php?tab=gallery" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_gallery" value="<?= $gal_id ?>">

            <label class="form-label">JUDUL</label>
            <input type="text" name="judul" class="brutalist-input" value="<?= htmlspecialchars($gal_judul) ?>" required>

            <label class="form-label">DESKRIPSI</label>
            <textarea name="deskripsi" class="brutalist-input" rows="3"><?= htmlspecialchars($gal_deskripsi) ?></textarea>

            <label class="form-label">LINK EVENT</label>
            <select name="id_event" class="brutalist-input">
                <option value="">-- TANPA EVENT --</option>
                <?php
                $ev_list = mysqli_query($conn, "SELECT id_event, judul_event FROM events");
                while ($ev = mysqli_fetch_assoc($ev_list)): ?>
                    <option value="<?= $ev['id_event'] ?>" <?= $gal_event == $ev['id_event'] ? 'selected' : '' ?>><?= strtoupper($ev['judul_event']) ?></option>
                <?php endwhile; ?>
            </select>

            <label class="form-label">TIPE</label>
            <select name="tipe" class="brutalist-input">
                <option value="event" <?= $gal_tipe == 'event' ? 'selected' : '' ?>>EVENT</option>
                <option value="profil" <?= $gal_tipe == 'profil' ? 'selected' : '' ?>>PROFIL</option>
            </select>

            <label class="form-label">FOTO</label>
            <?php if ($gal_foto): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($gal_foto) ?>" style="width:100px; display:block; margin-top:10px; margin-bottom:10px; border:2px solid var(--navy);">
            <?php endif; ?>
            <input type="file" name="file_foto" class="brutalist-input" <?= $edit_gal_mode ? '' : 'required' ?> accept="image/*">

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="<?= $edit_gal_mode ? 'update_gallery' : 'simpan_gallery' ?>" class="btn-trigger" style="flex:1;">SAVE</button>
                <button type="button" class="btn-action btn-del" style="flex:1; text-align:center; cursor:pointer;" onclick="closeModal('modalGallery'); window.location='gallery_staff.php?tab=gallery';">CANCEL</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EVENT -->
<div class="modal-overlay" id="modalEvent">
    <div class="paper" style="max-width: 500px; width: 100%; transform: rotate(0deg);">
        <div class="spec-header"><span>ACTION: <?= $edit_event_mode ? 'UPDATE_EVENT' : 'ADD NEW EVENT' ?></span></div>
        <form action="gallery_staff.php?tab=event" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_event" value="<?= $ev_id ?>">

            <label class="form-label">JUDUL EVENT</label>
            <input type="text" name="judul_event" class="brutalist-input" value="<?= htmlspecialchars($ev_judul) ?>" required>

            <label class="form-label">TANGGAL</label>
            <input type="date" name="tanggal_event" class="brutalist-input" value="<?= $ev_tanggal ?>" required>

            <label class="form-label">DESKRIPSI</label>
            <textarea name="deskripsi_event" class="brutalist-input" rows="3" required><?= htmlspecialchars($ev_deskripsi) ?></textarea>

            <label class="form-label">STATUS</label>
            <select name="status_event" class="brutalist-input">
                <option value="mendatang" <?= $ev_status == 'mendatang' ? 'selected' : '' ?>>MENDATANG</option>
                <option value="selesai" <?= $ev_status == 'selesai' ? 'selected' : '' ?>>SELESAI</option>
            </select>

            <label class="form-label">COVER FOTO</label>
            <?php if ($ev_foto): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($ev_foto) ?>" style="width:100px; display:block; margin-top:10px; margin-bottom:10px; border:2px solid var(--navy);">
            <?php endif; ?>
            <input type="file" name="foto_cover" class="brutalist-input" <?= $edit_event_mode ? '' : 'required' ?> accept="image/*">

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="<?= $edit_event_mode ? 'update_event' : 'simpan_event' ?>" class="btn-trigger" style="flex:1;">SAVE</button>
                <button type="button" class="btn-action btn-del" style="flex:1; text-align:center; cursor:pointer;" onclick="closeModal('modalEvent'); window.location='gallery_staff.php?tab=event';">CANCEL</button>
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
    }

    <?php if ($edit_gal_mode) echo "openModal('modalGallery');"; ?>
    <?php if ($edit_event_mode) echo "openModal('modalEvent');"; ?>
</script>

</body>
</html>
<?php ob_end_flush(); ?>