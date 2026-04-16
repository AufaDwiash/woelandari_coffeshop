<?php
include "../config/koneksi.php";

// ==========================================
// 1. PROSES CRUD EVENT
// ==========================================
$edit_event_mode = false;
$ev_id = "";
$ev_judul = "";
$ev_tanggal = "";
$ev_deskripsi = "";
$ev_status = "";
$ev_foto = "";

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

if (isset($_POST['simpan_event'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul_event']);
    $tanggal = $_POST['tanggal_event'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_event']);
    $status = $_POST['status_event'];
    $foto_nama = "default_event.jpg";

    if (!empty($_POST['foto_cropped_event'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_event']);
        if (count($img_parts) >= 2) {
            $img_base64 = base64_decode(str_replace(' ', '+', $img_parts[1]));
            $foto_nama = 'event_' . uniqid() . '.jpg';
            file_put_contents('../assets/images/events/' . $foto_nama, $img_base64);
        }
    }

    mysqli_query($conn, "INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, foto_cover) VALUES ('$judul', '$tanggal', '$deskripsi', '$status', '$foto_nama')");
    echo "<script>window.location='gallery_crud.php?tab=event';</script>";
}

if (isset($_POST['update_event'])) {
    $id_event = $_POST['id_event'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul_event']);
    $tanggal = $_POST['tanggal_event'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_event']);
    $status = $_POST['status_event'];
    $foto_lama = $_POST['foto_lama'];
    $foto_nama = $foto_lama;

    if (!empty($_POST['foto_cropped_event'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_event']);
        if (count($img_parts) >= 2) {
            $img_base64 = base64_decode(str_replace(' ', '+', $img_parts[1]));
            $foto_nama = 'event_' . uniqid() . '.jpg';
            file_put_contents('../assets/images/events/' . $foto_nama, $img_base64);

            if (file_exists('../assets/images/events/' . $foto_lama) && $foto_lama != 'default_event.jpg') {
                unlink('../assets/images/events/' . $foto_lama);
            }
        }
    }

    mysqli_query($conn, "UPDATE events SET judul_event='$judul', tanggal_event='$tanggal', deskripsi_event='$deskripsi', status_event='$status', foto_cover='$foto_nama' WHERE id_event='$id_event'");
    echo "<script>window.location='gallery_crud.php?tab=event';</script>";
}

if (isset($_GET['hapus_event'])) {
    $id_hapus = $_GET['hapus_event'];
    $q_foto = mysqli_query($conn, "SELECT foto_cover FROM events WHERE id_event='$id_hapus'");
    $d_foto = mysqli_fetch_assoc($q_foto);
    if (file_exists('../assets/images/events/' . $d_foto['foto_cover']) && $d_foto['foto_cover'] != 'default_event.jpg') {
        unlink('../assets/images/events/' . $d_foto['foto_cover']);
    }
    mysqli_query($conn, "DELETE FROM events WHERE id_event='$id_hapus'");
    echo "<script>window.location='gallery_crud.php?tab=event';</script>";
}

// ==========================================
// 2. PROSES CRUD GALLERY
// ==========================================
$edit_gal_mode = false;
$gal_id = "";
$gal_event = "";
$gal_foto = "";
$gal_judul = "";
$gal_deskripsi = "";

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

if (isset($_POST['simpan_gallery'])) {
    $id_ev_in = $_POST['id_event'];
    $id_event = ($id_ev_in == "0" || $id_ev_in == "") ? "NULL" : "'$id_ev_in'";
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_nama = "default_gallery.jpg";

    if (!empty($_POST['foto_cropped_gallery'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_gallery']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'gallery_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/gallery/' . $foto_nama, $img_base64);
    }
    mysqli_query($conn, "INSERT INTO gallery (id_event, judul, deskripsi, file_foto) VALUES ($id_event, '$judul', '$deskripsi', '$foto_nama')");
    echo "<script>window.location='gallery_crud.php?tab=gallery';</script>";
}

if (isset($_POST['update_gallery'])) {
    $id_gallery = $_POST['id_gallery'];
    $id_ev_in = $_POST['id_event'];
    $id_event = ($id_ev_in == "0" || $id_ev_in == "") ? "NULL" : "'$id_ev_in'";
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = $_POST['foto_lama'];
    $foto_nama = $foto_lama;

    if (!empty($_POST['foto_cropped_gallery'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_gallery']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'gallery_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/gallery/' . $foto_nama, $img_base64);
        if (file_exists('../assets/images/gallery/' . $foto_lama) && $foto_lama != 'default_gallery.jpg') {
            unlink('../assets/images/gallery/' . $foto_lama);
        }
    }
    mysqli_query($conn, "UPDATE gallery SET id_event=$id_event, judul='$judul', deskripsi='$deskripsi', file_foto='$foto_nama' WHERE id_gallery='$id_gallery'");
    echo "<script>window.location='gallery_crud.php?tab=gallery';</script>";
}

if (isset($_GET['hapus_gallery'])) {
    $id_hapus = $_GET['hapus_gallery'];
    $q_foto = mysqli_query($conn, "SELECT file_foto FROM gallery WHERE id_gallery='$id_hapus'");
    $d_foto = mysqli_fetch_assoc($q_foto);
    if (file_exists('../assets/images/gallery/' . $d_foto['file_foto']) && $d_foto['file_foto'] != 'default_gallery.jpg') {
        unlink('../assets/images/gallery/' . $d_foto['file_foto']);
    }
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery='$id_hapus'");
    echo "<script>window.location='gallery_crud.php?tab=gallery';</script>";
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gallery';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gallery & Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/gallery_crud.css">
</head>
<body>

<div class="tab-container">
    <button class="tab-btn <?php echo ($active_tab == 'gallery') ? 'active' : ''; ?>"
        onclick="window.location='gallery_crud.php?tab=gallery'">KELOLA GALLERY</button>
    <button class="tab-btn <?php echo ($active_tab == 'event') ? 'active' : ''; ?>"
        onclick="window.location='gallery_crud.php?tab=event'">KELOLA EVENT</button>
</div>

<div id="tab-gallery" class="tab-content <?php echo ($active_tab == 'gallery') ? 'active' : ''; ?>">
    <div class="table-container">
        <div class="tape-table"></div>
        <div class="header-flex">
            <h2>DAFTAR GALLERY SISTEM</h2>
            <button class="btn-add-top" onclick="bukaModal('modalGallery')">+ TAMBAH FOTO</button>
        </div>
        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>FOTO</th>
                    <th>INFO JUDUL</th>
                    <th>EVENT</th>
                    <th style="text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_g = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event ORDER BY g.id_gallery DESC");
                while ($r_g = mysqli_fetch_assoc($q_g)):
                ?>
                <tr>
                    <td><img src="../assets/images/gallery/<?php echo $r_g['file_foto']; ?>" class="img-preview" style="width:75px; height:75px; object-fit:cover; border: 2px solid var(--navy-ink);"></td>
                    <td class="text-left"><strong><?php echo strtoupper($r_g['judul']); ?></strong></td>
                    <td><?php echo $r_g['judul_event'] ? strtoupper($r_g['judul_event']) : '-'; ?></td>
                    <td class="action-cell">
                        <div class="action-flex">
                            <a href="?tab=gallery&edit_gallery=<?php echo $r_g['id_gallery']; ?>" class="btn-action btn-edit">Edit</a>
                            <a href="?tab=gallery&hapus_gallery=<?php echo $r_g['id_gallery']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus foto ini?');">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="tab-event" class="tab-content <?php echo ($active_tab == 'event') ? 'active' : ''; ?>">
    <div class="table-container">
        <div class="tape-table"></div>
        <div class="header-flex">
            <h2>JADWAL EVENT SISTEM</h2>
            <button class="btn-add-top" onclick="bukaModal('modalEvent')">+ BUAT EVENT</button>
        </div>
        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>COVER</th>
                    <th>JUDUL EVENT</th>
                    <th>TANGGAL</th>
                    <th style="text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_eve = mysqli_query($conn, "SELECT * FROM events ORDER BY tanggal_event DESC");
                while ($r_eve = mysqli_fetch_assoc($q_eve)):
                ?>
                <tr>
                    <td><img src="../assets/images/events/<?php echo $r_eve['foto_cover']; ?>" class="img-preview" style="width:75px; height:75px; object-fit:cover; border: 2px solid var(--navy-ink);"></td>
                    <td><strong><?php echo strtoupper($r_eve['judul_event']); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($r_eve['tanggal_event'])); ?></td>
                    <td class="action-cell">
                        <div class="action-flex">
                            <a href="?tab=event&edit_event=<?php echo $r_eve['id_event']; ?>" class="btn-action btn-edit">Edit</a>
                            <a href="?tab=event&hapus_event=<?php echo $r_eve['id_event']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus event ini?');">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL GALLERY -->
<div class="modal-overlay" id="modalGallery">
    <div class="modal-content">
        <?php if ($edit_gal_mode): ?>
            <a href="gallery_crud.php?tab=gallery" class="close-modal">✕</a>
        <?php else: ?>
            <span class="close-modal" onclick="tutupModal('modalGallery')">✕</span>
        <?php endif; ?>
        <h2><?php echo $edit_gal_mode ? "UPDATE DATA GALLERY" : "TAMBAH GALLERY BARU"; ?></h2>
        <form method="POST">
            <?php if ($edit_gal_mode): ?>
                <input type="hidden" name="id_gallery" value="<?php echo $gal_id; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $gal_foto; ?>">
            <?php endif; ?>
            <div class="flex-row">
                <div class="form-group">
                    <label>Judul Foto:</label>
                    <input type="text" name="judul" required value="<?php echo $gal_judul; ?>" placeholder="Masukkan judul foto...">
                </div>
                <div class="form-group">
                    <label>Pilih Event Terkait:</label>
                    <select name="id_event">
                        <option value="0">-- Tanpa Event --</option>
                        <?php
                        $q_ev_list = mysqli_query($conn, "SELECT id_event, judul_event FROM events ORDER BY id_event DESC");
                        while ($ev = mysqli_fetch_assoc($q_ev_list)) {
                            $sel = ($gal_event == $ev['id_event']) ? 'selected' : '';
                            echo "<option value='{$ev['id_event']}' $sel>{$ev['judul_event']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Deskripsi Singkat:</label>
                <textarea name="deskripsi" rows="3" required placeholder="Tulis deskripsi foto..."><?php echo $gal_deskripsi; ?></textarea>
            </div>
            <div class="form-group">
                <label>Unggah Foto (Rasio 1:1):</label>
                <input type="file" id="inputFotoGallery" accept="image/*">
            </div>
            <div class="crop-wrapper" id="cropContainerBoxGallery" style="display:none;">
                <img id="image-to-crop-gallery">
            </div>
            <input type="hidden" name="foto_cropped_gallery" id="foto_cropped_gallery">
            <button type="button" class="btn-submit-full" id="btnSimpanGallery"><?php echo $edit_gal_mode ? "UPDATE GALLERY" : "SIMPAN GALLERY"; ?></button>
            <button type="submit" name="<?php echo $edit_gal_mode ? 'update_gallery' : 'simpan_gallery'; ?>" id="btnSubmitGalleryAsli" style="display:none;"></button>
        </form>
    </div>
</div>

<!-- MODAL EVENT -->
<div class="modal-overlay" id="modalEvent">
    <div class="modal-content">
        <?php if ($edit_event_mode): ?>
            <a href="gallery_crud.php?tab=event" class="close-modal">✕</a>
        <?php else: ?>
            <span class="close-modal" onclick="tutupModal('modalEvent')">✕</span>
        <?php endif; ?>
        <h2><?php echo $edit_event_mode ? "UPDATE DATA EVENT" : "BUAT EVENT BARU"; ?></h2>
        <form method="POST">
            <?php if ($edit_event_mode): ?>
                <input type="hidden" name="id_event" value="<?php echo $ev_id; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $ev_foto; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Judul Event:</label>
                <input type="text" name="judul_event" required value="<?php echo $ev_judul; ?>" placeholder="Masukkan nama event...">
            </div>
            <div class="flex-row">
                <div class="form-group">
                    <label>Tanggal Event:</label>
                    <input type="date" name="tanggal_event" required value="<?php echo $ev_tanggal; ?>">
                </div>
                <div class="form-group">
                    <label>Status Event:</label>
                    <select name="status_event">
                        <option value="mendatang" <?php echo ($ev_status == 'mendatang') ? 'selected' : ''; ?>>Mendatang</option>
                        <option value="selesai" <?php echo ($ev_status == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Deskripsi Event:</label>
                <textarea name="deskripsi_event" rows="3" required placeholder="Jelaskan detail event..."><?php echo $ev_deskripsi; ?></textarea>
            </div>
            <div class="form-group">
                <label>Unggah Cover Event (Rasio 1:1):</label>
                <input type="file" id="inputFotoEvent" accept="image/*">
            </div>
            <div class="crop-wrapper" id="cropContainerBoxEvent" style="display:none;">
                <img id="image-to-crop-event">
            </div>
            <input type="hidden" name="foto_cropped_event" id="foto_cropped_event">
            <button type="button" class="btn-submit-full" id="btnSimpanEvent"><?php echo $edit_event_mode ? "UPDATE EVENT" : "SIMPAN EVENT"; ?></button>
            <button type="submit" name="<?php echo $edit_event_mode ? 'update_event' : 'simpan_event'; ?>" id="btnSubmitEventAsli" style="display:none;"></button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    function bukaModal(idModal) {
        document.getElementById(idModal).style.display = 'flex';
    }
    function tutupModal(idModal) {
        document.getElementById(idModal).style.display = 'none';
    }

    <?php if ($edit_gal_mode): ?>
        bukaModal('modalGallery');
    <?php endif; ?>
    <?php if ($edit_event_mode): ?>
        bukaModal('modalEvent');
    <?php endif; ?>

    let cropperG;
    document.getElementById('inputFotoGallery')?.addEventListener('change', function (e) {
        const reader = new FileReader();
        reader.onload = (event) => {
            document.getElementById('cropContainerBoxGallery').style.display = 'block';
            const img = document.getElementById('image-to-crop-gallery');
            img.src = event.target.result;
            if (cropperG) cropperG.destroy();
            cropperG = new Cropper(img, { aspectRatio: 1 / 1, viewMode: 1 });
        };
        reader.readAsDataURL(e.target.files[0]);
    });
    
    document.getElementById('btnSimpanGallery')?.addEventListener('click', () => {
        if (cropperG) document.getElementById('foto_cropped_gallery').value = cropperG.getCroppedCanvas({ width: 800, height: 800 }).toDataURL('image/jpeg', 0.9);
        document.getElementById('btnSubmitGalleryAsli').click();
    });

    let cropperE;
    document.getElementById('inputFotoEvent')?.addEventListener('change', function (e) {
        const reader = new FileReader();
        reader.onload = (event) => {
            document.getElementById('cropContainerBoxEvent').style.display = 'block';
            const img = document.getElementById('image-to-crop-event');
            img.src = event.target.result;
            if (cropperE) cropperE.destroy();
            cropperE = new Cropper(img, { aspectRatio: 1 / 1, viewMode: 1 });
        };
        reader.readAsDataURL(e.target.files[0]);
    });

    document.getElementById('btnSimpanEvent')?.addEventListener('click', () => {
        if (cropperE) {
            document.getElementById('foto_cropped_event').value = cropperE.getCroppedCanvas({ width: 800, height: 800 }).toDataURL('image/jpeg', 0.8);
        }
        document.getElementById('btnSubmitEventAsli').click();
    });
</script>
</body>
</html>