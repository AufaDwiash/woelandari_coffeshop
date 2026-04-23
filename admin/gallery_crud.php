<?php
include "../config/koneksi.php";

// --- LOGIKA PHP (Tetap Sama Seperti Sebelumnya) ---
$edit_event_mode = false;
$ev_id = ""; $ev_judul = ""; $ev_tanggal = ""; $ev_deskripsi = ""; $ev_status = ""; $ev_foto = "";
if (isset($_GET['edit_event'])) {
    $edit_event_mode = true;
    $ev_id = $_GET['edit_event'];
    $q_ev = mysqli_query($conn, "SELECT * FROM events WHERE id_event='$ev_id'");
    $d_ev = mysqli_fetch_assoc($q_ev);
    $ev_judul = $d_ev['judul_event'];
    $ev_tanggal = $d_ev['tanggal_event'];
    $ev_deskripsi = $d_ev['deskripsi_event'];
    $ev_status = $d_ev['status_event'];
    $ev_foto = $d_ev['foto_cover'];
}

$edit_gal_mode = false;
$gal_id = ""; $gal_event = ""; $gal_foto = ""; $gal_judul = ""; $gal_deskripsi = "";
if (isset($_GET['edit_gallery'])) {
    $edit_gal_mode = true;
    $gal_id = $_GET['edit_gallery'];
    $q_gal = mysqli_query($conn, "SELECT * FROM gallery WHERE id_gallery='$gal_id'");
    $d_gal = mysqli_fetch_assoc($q_gal);
    $gal_event = $d_gal['id_event'];
    $gal_foto = $d_gal['file_foto'];
    $gal_judul = $d_gal['judul'];
    $gal_deskripsi = $d_gal['deskripsi'];
}
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gallery';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gallery & Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-ink: #9b2226;
            --navy-ink: #001219;
            --paper-bg: #e5e5e5;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--paper-bg);
            font-family: 'Courier Prime', monospace;
            color: var(--navy-ink);
            display: flex; /* Memungkinkan sidebar dan main berdampingan */
        }

        /* --- SIDEBAR (Sama Persis Dashboard) --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--navy-ink);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            z-index: 1000;
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

        .nav-list { list-style: none; padding: 0; margin: 0; }

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

        /* --- MAIN CONTENT (Penyesuaian Lebar) --- */
        .main-content {
            margin-left: var(--sidebar-width); /* KUNCI SIMETRI: Dorong konten ke kanan */
            width: calc(100% - var(--sidebar-width)); /* Isi sisa layar */
            padding: 40px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 40px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }

        .page-header h1 {
            font-family: 'Special Elite', cursive;
            margin: 0;
            font-size: 2.2rem;
            letter-spacing: -1px;
        }

        /* Tab Control */
        .tab-wrapper {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .tab-btn {
            font-family: 'Special Elite', cursive;
            padding: 12px 25px;
            border: 2px solid var(--navy-ink);
            background: white;
            cursor: pointer;
            transition: 0.2s;
        }

        .tab-btn.active {
            background: var(--navy-ink);
            color: white;
            box-shadow: 5px 5px 0px var(--red-ink);
        }

        /* Container Table */
        .card-system {
            background: #fff;
            border: 2px solid var(--navy-ink);
            padding: 30px;
            position: relative;
            box-shadow: 10px 10px 0px rgba(0,0,0,0.05);
        }

        .tape-deco {
            position: absolute;
            width: 80px;
            height: 30px;
            background: rgba(0,0,0,0.1);
            top: -15px;
            left: 20px;
            transform: rotate(-2deg);
            border: 1px dashed rgba(0,0,0,0.2);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #f8f8f8;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--navy-ink);
            font-family: 'Special Elite', cursive;
            font-size: 0.9rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .img-preview {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border: 2px solid var(--navy-ink);
        }

        .btn-add {
            background: var(--red-ink);
            color: white;
            border: none;
            padding: 10px 20px;
            font-family: 'Special Elite', cursive;
            cursor: pointer;
            box-shadow: 3px 3px 0px var(--navy-ink);
        }

        .action-link {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .edit { color: var(--navy-ink); }
        .delete { color: var(--red-ink); }

        /* Modal Simple Style */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,18,25,0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .modal-box {
            background: white;
            width: 100%;
            max-width: 700px;
            padding: 30px;
            border: 5px solid var(--red-ink);
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
     <nav class="nav-list">
        <a href="dashboard.php" class="nav-item "> <span>Dashboard</span></a>
        <a href="menu_crud.php" class="nav-item"><span>Menu</span></a>
        <a href="gallery_crud.php" class="nav-item active"> <span>Gallery</span></a>
        <a href="#" class="nav-item"><span>Feedback</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>Kelola User</span></a>
    </nav>
    <div style="margin-top: auto; border-top: 1px dashed #555; padding-top: 10px;">
        <a href="logout.php" class="nav-item" style="color: #ff6b6b;">>> <span>TERMINATE</span></a>
    </div>
</aside>

<main class="main-content">
    <header class="page-header">
        <h1>Kelola Gallery dan Event</h1>

    </header>

    <div class="tab-wrapper">
        <button class="tab-btn <?php echo ($active_tab == 'gallery') ? 'active' : ''; ?>" 
                onclick="window.location='gallery_crud.php?tab=gallery'">Kelola Gallery </button>
        <button class="tab-btn <?php echo ($active_tab == 'event') ? 'active' : ''; ?>" 
                onclick="window.location='gallery_crud.php?tab=event'">Kelola Event </button>
    </div>

    <div class="card-system">
        <div class="tape-deco"></div>
        
        <?php if ($active_tab == 'gallery'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: 'Special Elite';">Kelola Gallery </h2>
                <button class="btn-add" onclick="bukaModal('modalGallery')"> + Tambah Foto Baru</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Judul</th>
                        <th>Keterangan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_g = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event ORDER BY g.id_gallery DESC");
                    while ($r_g = mysqli_fetch_assoc($q_g)):
                    ?>
                    <tr>
                        <td><img src="../assets/images/gallery/<?php echo $r_g['file_foto']; ?>" class="img-preview"></td>
                        <td><strong><?php echo strtoupper($r_g['judul']); ?></strong></td>
                        <td><?php echo $r_g['judul_event'] ? strtoupper($r_g['judul_event']) : '<span style="color:#ccc;">(NULL)</span>'; ?></td>
                        <td style="text-align: right;">
                            <a href="?tab=gallery&edit_gallery=<?php echo $r_g['id_gallery']; ?>" class="action-link edit">[EDIT]</a>
                            <a href="?tab=gallery&hapus_gallery=<?php echo $r_g['id_gallery']; ?>" class="action-link delete" onclick="return confirm('Hapus data?');">[DEL]</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: 'Special Elite';">EVENT_SCHEDULER_LOG</h2>
                <button class="btn-add" onclick="bukaModal('modalEvent')">+ CREATE_NEW_EVENT</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>COVER</th>
                        <th>EVENT_NAME</th>
                        <th>DATE_STAMP</th>
                        <th style="text-align: right;">OPERATIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_eve = mysqli_query($conn, "SELECT * FROM events ORDER BY tanggal_event DESC");
                    while ($r_eve = mysqli_fetch_assoc($q_eve)):
                    ?>
                    <tr>
                        <td><img src="../assets/images/events/<?php echo $r_eve['foto_cover']; ?>" class="img-preview"></td>
                        <td><strong><?php echo strtoupper($r_eve['judul_event']); ?></strong></td>
                        <td><?php echo date('d-m-Y', strtotime($r_eve['tanggal_event'])); ?></td>
                        <td style="text-align: right;">
                            <a href="?tab=event&edit_event=<?php echo $r_eve['id_event']; ?>" class="action-link edit">[EDIT]</a>
                            <a href="?tab=event&hapus_event=<?php echo $r_eve['id_event']; ?>" class="action-link delete" onclick="return confirm('Hapus data?');">[DEL]</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="modalGallery">
    <div class="modal-box">
        <h2 style="font-family: 'Special Elite'; border-bottom: 2px solid var(--red-ink); padding-bottom: 10px;">FORM_GALLERY_UPLOAD</h2>
        <button class="btn-add" onclick="tutupModal('modalGallery')" style="background:#555;">CANCEL</button>
    </div>
</div>

<div class="modal-overlay" id="modalEvent">
    <div class="modal-box">
        <h2 style="font-family: 'Special Elite'; border-bottom: 2px solid var(--red-ink); padding-bottom: 10px;">FORM_EVENT_ENTRY</h2>
        <button class="btn-add" onclick="tutupModal('modalEvent')" style="background:#555;">CANCEL</button>
    </div>
</div>

<script>
    function bukaModal(id) { document.getElementById(id).style.display = 'flex'; }
    function tutupModal(id) { document.getElementById(id).style.display = 'none'; }
    
    // Auto open modal jika mode edit
    <?php if ($edit_gal_mode): ?> bukaModal('modalGallery'); <?php endif; ?>
    <?php if ($edit_event_mode): ?> bukaModal('modalEvent'); <?php endif; ?>
</script>

</body>
</html>