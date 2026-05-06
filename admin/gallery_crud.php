<?php
ob_start();
session_start();
include "../config/koneksi.php";

// Proteksi halaman admin
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
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
    $id_g = $_lGET['edit_gallery'];
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
        header("location:gallery_crud.php?tab=event");
        exit;
    }
}

if (isset($_POST['simpan_gallery']) || isset($_POST['update_gallery'])) {
    $id_gal = $_POST['id_gallery'];
    $judul  = $_POST['judul'];
    $desk   = $_POST['deskripsi'];
    $tipe   = $_POST['tipe'];

    // Jika id_event kosong, set menjadi NULL
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
        header("location:gallery_crud.php?tab=gallery");
        exit;
    } else {
        die("Error: " . mysqli_error($conn));
    }
}

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus_event'])) {
    mysqli_query($conn, "DELETE FROM events WHERE id_event='" . $_GET['hapus_event'] . "'");
    header("location:gallery_crud.php?tab=event");
    exit;
}
if (isset($_GET['hapus_gallery'])) {
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery='" . $_GET['hapus_gallery'] . "'");
    header("location:gallery_crud.php?tab=gallery");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gallery-admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/gallery_crud.css">
</head>

<body>

    <aside class="sidebar">
        <div class="brand">WOELANDARI STAFF</div>
        <nav class="nav-list">
            <a href="dashboard.php" class="nav-item"><span>> DASHBOARD</span></a>
            <a href="menu_crud.php" class="nav-item"><span>> KELOLA MENU</span></a>
            <a href="gallery_crud.php" class="nav-item active"><span>> KELOLA GALLERY & EVENT</span></a>
            <a href="feedback.php" class="nav-item"><span>> KELOLA FEEDBACK & RATING</span></a>
            <a href="user_manajemen.php" class="nav-item"><span>> KELOLA USER</span></a>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
            </div>
        </nav>
    </aside>

    <main class="main-wrapper">
        <section class="paper paper-style-1" style="padding: 20px 40px;">
            <div class="tape"></div>
            <div class="spec-header">
                <span></span> <span>DATE: <?php echo date('d/m/Y'); ?></span>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="?tab=gallery" class="nav-item <?php echo ($active_tab == 'gallery') ? 'active' : ''; ?>" style="margin-bottom:0; flex: 1; text-align: center;">Gallery</a>
                <a href="?tab=event" class="nav-item <?php echo ($active_tab == 'event') ? 'active' : ''; ?>" style="margin-bottom:0; flex: 1; text-align: center;">Events</a>
            </div>
        </section>

        <section class="paper paper-style-2">
            <div class="sticky-note">
                <p>USER: <?php echo $username; ?></p>
                <p>STATUS: <span class="blink">ONLINE</span></p>
            </div>

            <div class="spec-header">
                <span>MODULE: <?php echo ($active_tab == 'gallery') ? 'VISUAL_ARCHIVE' : 'SCHEDULE_ARCHIVE'; ?></span>
                <span>REF: WLDRI-<?php echo ($active_tab == 'gallery') ? 'GAL' : 'EVT'; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 class="title-main" style="margin-bottom:0;">DATABASE_LIST</h1>
                <button class="nav-item active btn-trigger" style="cursor: pointer;" onclick="openModal('<?php echo ($active_tab == 'gallery') ? 'modalGallery' : 'modalEvent'; ?>')">+ ADD_NEW_RECORD</button>
            </div>

            <div style="overflow-x: auto;">
                <table class="brutalist-table">
                    <thead>
                        <?php if ($active_tab == 'gallery'): ?>
                            <tr>
                                <th>PREVIEW</th>
                                <th>IDENTIFICATION</th>
                                <th>LINK_EVENT</th>
                                <th style="text-align: right;">OP_CMD</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>COVER</th>
                                <th>EVENT_NAME</th>
                                <th>TIMESTAMP</th>
                                <th style="text-align: right;">OP_CMD</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php
                        if ($active_tab == 'gallery'):
                            $q = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event ORDER BY g.id_gallery DESC");
                            while ($r = mysqli_fetch_assoc($q)): ?>
                                <tr>
                                    <td><img src="data:image/jpeg;base64,<?= base64_encode($r['file_foto']) ?>" class="img-preview"></td>
                                    <td><strong><?php echo strtoupper($r['judul']); ?></strong></td>
                                    <td><span class="tag"><?php echo $r['judul_event'] ? strtoupper($r['judul_event']) : 'GENERAL'; ?></span></td>
                                    <td style="text-align: right;">
                                        <a href="?tab=gallery&edit_gallery=<?php echo $r['id_gallery']; ?>" class="btn-action">EDIT</a>
                                        <a href="?tab=gallery&hapus_gallery=<?php echo $r['id_gallery']; ?>" class="btn-action btn-del" onclick="return confirm('Hapus?');">DEL</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else:
                            $q = mysqli_query($conn, "SELECT * FROM events ORDER BY tanggal_event DESC");
                            while ($r = mysqli_fetch_assoc($q)): ?>
                                <tr>
                                    <td><img src="data:image/jpeg;base64,<?= base64_encode($r['foto_cover']) ?>" class="img-preview"></td>
                                    <td><strong><?php echo strtoupper($r['judul_event']); ?></strong></td>
                                    <td><?php echo date('d.m.Y', strtotime($r['tanggal_event'])); ?></td>
                                    <td style="text-align: right;">
                                        <a href="?tab=event&edit_event=<?php echo $r['id_event']; ?>" class="btn-action">EDIT</a>
                                        <a href="?tab=event&hapus_event=<?php echo $r['id_event']; ?>" class="btn-action btn-del" onclick="return confirm('Hapus?');">DEL</a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal-overlay" id="modalGallery">
        <div class="paper" style="max-width: 500px; width: 100%; transform: rotate(0deg);">
            <div class="spec-header"><span>ACTION: <?= $edit_gal_mode ? 'UPDATE_GALLERY' : 'ADD_NEW_GALLERY' ?></span></div>
            <form action="gallery_crud.php?tab=gallery" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_gallery" value="<?= $gal_id ?>">

                <label class="form-label">JUDUL</label>
                <input type="text" name="judul" class="brutalist-input" value="<?= $gal_judul ?>" required>

                <label class="form-label">DESKRIPSI</label>
                <textarea name="deskripsi" class="brutalist-input"><?= $gal_deskripsi ?></textarea>

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
                <input type="file" name="file_foto" class="brutalist-input" <?= $edit_gal_mode ? '' : 'required' ?>>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="<?= $edit_gal_mode ? 'update_gallery' : 'simpan_gallery' ?>" class="nav-item active" style="flex:1; border:none; cursor:pointer;">SAVE</button>
                    <button type="button" class="nav-item btn-del" style="flex:1; background:none; cursor:pointer;" onclick="closeModal('modalGallery'); window.location='gallery_crud.php?tab=gallery';">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEvent">
        <div class="paper" style="max-width: 500px; width: 100%; transform: rotate(0deg);">
            <div class="spec-header"><span>ACTION: <?= $edit_event_mode ? 'UPDATE_EVENT' : 'ADD_NEW_EVENT' ?></span></div>
            <form action="gallery_crud.php?tab=event" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_event" value="<?= $ev_id ?>">

                <label class="form-label">JUDUL EVENT</label>
                <input type="text" name="judul_event" class="brutalist-input" value="<?= $ev_judul ?>" required>

                <label class="form-label">TANGGAL</label>
                <input type="date" name="tanggal_event" class="brutalist-input" value="<?= $ev_tanggal ?>" required>

                <label class="form-label">DESKRIPSI</label>
                <textarea name="deskripsi_event" class="brutalist-input" required><?= $ev_deskripsi ?></textarea>

                <label class="form-label">STATUS</label>
                <select name="status_event" class="brutalist-input">
                    <option value="mendatang" <?= $ev_status == 'mendatang' ? 'selected' : '' ?>>MENDATANG</option>
                    <option value="selesai" <?= $ev_status == 'selesai' ? 'selected' : '' ?>>SELESAI</option>
                </select>

                <label class="form-label">COVER FOTO</label>
                <?php if ($ev_foto): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($ev_foto) ?>" style="width:100px; display:block; margin-top:10px; margin-bottom:10px; border:2px solid var(--navy);">
                <?php endif; ?>
                <input type="file" name="foto_cover" class="brutalist-input" <?= $edit_event_mode ? '' : 'required' ?>>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="<?= $edit_event_mode ? 'update_event' : 'simpan_event' ?>" class="nav-item active" style="flex:1; border:none; cursor:pointer;">SAVE</button>
                    <button type="button" class="nav-item btn-del" style="flex:1; background:none; cursor:pointer;" onclick="closeModal('modalEvent'); window.location='gallery_crud.php?tab=event';">CANCEL</button>
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

        // Auto-open modal untuk Mode Edit
        <?php if ($edit_gal_mode) echo "openModal('modalGallery');"; ?>
        <?php if ($edit_event_mode) echo "openModal('modalEvent');"; ?>
    </script>

</body>

</html>
<?php ob_end_flush(); ?>