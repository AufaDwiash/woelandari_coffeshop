<?php
/**
 * gallery_crud.php
 * Halaman CRUD untuk Gallery (Foto) dan Events
 * Style dipisahkan di file gallery_crud.css
 * 
 * Perbaikan: Fitur "Sematkan Event" hanya untuk event yang belum selesai.
 * Event selesai otomatis tidak bisa disematkan (checkbox disabled & is_featured = 0).
 */

session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ========== KONFIGURASI ==========
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gallery';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$today = date('Y-m-d');

// Helper redirect dengan notifikasi
function redirectWithMsg($url, $msg, $isError = false) {
    $param = $isError ? 'error' : 'msg';
    header("Location: $url&$param=" . urlencode($msg));
    exit;
}

// ========== PROSES GALLERY ==========
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
    $judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $desk = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $tipe_koneksi = $_POST['tipe_koneksi'] ?? 'biasa';
    
    if ($tipe_koneksi == 'event' && !empty($_POST['id_event'])) {
        $id_ev = (int)$_POST['id_event'];
    } else {
        $id_ev = 'NULL';
        $tipe_koneksi = 'biasa';
    }
    $tipe_file = ($tipe_koneksi == 'event') ? 'event' : 'profil';
    
    if (empty($judul)) {
        redirectWithMsg("gallery_crud.php?tab=gallery&page=$page" . ($search ? "&search=" . urlencode($search) : ""), "❌ Judul tidak boleh kosong", true);
    }

    $foto_isi = "";
    $query_foto = "";
    if (!empty($_POST['foto_cropped_gallery'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_gallery']);
        if (count($img_parts) === 2) {
            $img_base64 = base64_decode($img_parts[1], true);
            if ($img_base64 !== false) {
                $foto_isi = addslashes($img_base64);
                $query_foto = ", file_foto='$foto_isi'";
            }
        }
    } elseif (!empty($_FILES['file_foto']['tmp_name'])) {
        $file_content = file_get_contents($_FILES['file_foto']['tmp_name']);
        if ($file_content !== false) {
            $foto_isi = addslashes($file_content);
            $query_foto = ", file_foto='$foto_isi'";
        }
    }

    if (isset($_POST['simpan_gallery'])) {
        $insert = "INSERT INTO gallery (judul, deskripsi, id_event, tipe, file_foto) VALUES ('$judul', '$desk', $id_ev, '$tipe_file', '$foto_isi')";
        mysqli_query($conn, $insert) ? redirectWithMsg("gallery_crud.php?tab=gallery&page=1", "✅ Gallery berhasil ditambahkan", false) : redirectWithMsg("gallery_crud.php?tab=gallery&page=$page", "❌ Gagal menyimpan gallery", true);
    } else {
        $update = "UPDATE gallery SET judul='$judul', deskripsi='$desk', id_event=$id_ev, tipe='$tipe_file' $query_foto WHERE id_gallery=$id_gal";
        mysqli_query($conn, $update) ? redirectWithMsg("gallery_crud.php?tab=gallery&page=$page" . ($search ? "&search=" . urlencode($search) : ""), "✏️ Gallery berhasil diperbarui", false) : redirectWithMsg("gallery_crud.php?tab=gallery&page=$page", "❌ Gagal mengupdate gallery", true);
    }
}

if (isset($_GET['hapus_gallery'])) {
    $id = (int)$_GET['hapus_gallery'];
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery=$id");
    redirectWithMsg("gallery_crud.php?tab=gallery&page=$page" . ($search ? "&search=" . urlencode($search) : ""), "🗑️ Gallery berhasil dihapus", false);
}

// ========== PROSES EVENT ==========
$edit_event_mode = false;
$ev_id = $ev_judul = $ev_tanggal = $ev_deskripsi = $ev_status = $ev_foto = $ev_featured = $ev_is_expired = false;

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
        $ev_featured = $d['is_featured'] ?? 0;
        // Cek apakah event sudah selesai (status selesai atau tanggal sudah lewat)
        $ev_is_expired = ($ev_status == 'selesai') || ($ev_tanggal < $today);
        // Jika expired, pastikan featured = 0 (tidak disematkan)
        if ($ev_is_expired && $ev_featured) {
            $ev_featured = 0;
        }
    }
}

if (isset($_POST['simpan_event']) || isset($_POST['update_event'])) {
    $id_ev = (int)$_POST['id_event'];
    $judul = mysqli_real_escape_string($conn, trim($_POST['judul_event']));
    $tanggal = mysqli_real_escape_string($conn, trim($_POST['tanggal_event']));
    $desk = mysqli_real_escape_string($conn, trim($_POST['deskripsi_event']));
    $status = in_array($_POST['status_event'], ['mendatang', 'selesai']) ? $_POST['status_event'] : 'mendatang';
    
    // Cek apakah event selesai (status atau tanggal)
    $isExpired = ($status == 'selesai') || ($tanggal < $today);
    // Jika expired, featured dipaksa 0, tidak peduli input checkbox
    if ($isExpired) {
        $featured = 0;
    } else {
        $featured = isset($_POST['is_featured']) ? 1 : 0;
    }
    
    if (empty($judul) || empty($tanggal)) {
        redirectWithMsg("gallery_crud.php?tab=event&page=$page", "❌ Judul dan tanggal tidak boleh kosong", true);
    }

    $foto_isi = "";
    $query_foto = "";
    if (!empty($_POST['foto_cropped_event'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped_event']);
        if (count($img_parts) === 2) {
            $img_base64 = base64_decode($img_parts[1], true);
            if ($img_base64 !== false) {
                $foto_isi = addslashes($img_base64);
                $query_foto = ", foto_cover='$foto_isi'";
            }
        }
    } elseif (!empty($_FILES['foto_cover']['tmp_name'])) {
        $file_content = file_get_contents($_FILES['foto_cover']['tmp_name']);
        if ($file_content !== false) {
            $foto_isi = addslashes($file_content);
            $query_foto = ", foto_cover='$foto_isi'";
        }
    }

    if (isset($_POST['simpan_event'])) {
        $insert = "INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, is_featured, foto_cover) VALUES ('$judul', '$tanggal', '$desk', '$status', '$featured', '$foto_isi')";
        mysqli_query($conn, $insert) ? redirectWithMsg("gallery_crud.php?tab=event&page=1", "✅ Event berhasil ditambahkan", false) : redirectWithMsg("gallery_crud.php?tab=event&page=$page", "❌ Gagal menyimpan event", true);
    } else {
        $update = "UPDATE events SET judul_event='$judul', tanggal_event='$tanggal', deskripsi_event='$desk', status_event='$status', is_featured='$featured' $query_foto WHERE id_event=$id_ev";
        mysqli_query($conn, $update) ? redirectWithMsg("gallery_crud.php?tab=event&page=$page" . ($search ? "&search=" . urlencode($search) : ""), "✏️ Event berhasil diperbarui", false) : redirectWithMsg("gallery_crud.php?tab=event&page=$page", "❌ Gagal mengupdate event", true);
    }
}

if (isset($_GET['hapus_event'])) {
    $id = (int)$_GET['hapus_event'];
    mysqli_query($conn, "DELETE FROM events WHERE id_event=$id");
    redirectWithMsg("gallery_crud.php?tab=event&page=$page" . ($search ? "&search=" . urlencode($search) : ""), "🗑️ Event berhasil dihapus", false);
}

$msg_display = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$error_display = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// ========== FUNGSI RENDER TABEL (AJAX) ==========
function renderGalleryTable($conn, $search, $page, $limit, $offset) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where = $search ? "WHERE g.judul LIKE '%$safe_search%'" : "";
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM gallery g $where"));
    $totalPages = ceil($count['total'] / $limit);
    $q = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event = e.id_event $where ORDER BY g.id_gallery DESC LIMIT $limit OFFSET $offset");
    ob_start(); ?>
    <div class="table-container"><table class="data-table"><thead><tr><th class="col-img">FOTO</th><th class="col-title">JUDUL & DESKRIPSI</th><th class="col-status">KATEGORI</th><th class="col-action">AKSI</th></tr></thead><tbody>
    <?php if(mysqli_num_rows($q)>0): while($r=mysqli_fetch_assoc($q)): ?>
        <tr><td style="text-align:center"><?php if($r['file_foto']): ?><img src="data:image/jpeg;base64,<?= base64_encode($r['file_foto']) ?>" class="thumb-img"><?php else: ?><div class="no-img"></div><?php endif; ?></td>
        <td><strong><?= htmlspecialchars($r['judul']) ?></strong><br><small><?= htmlspecialchars(substr($r['deskripsi'],0,60)) ?>...</small></td>
        <td style="text-align:center"><span class="status-badge <?= $r['id_event'] ? 'status-active' : 'status-pending' ?>"><?= $r['judul_event'] ? strtoupper(htmlspecialchars($r['judul_event'])) : 'FOTO BIASA' ?></span></td>
        <td><div class="action-buttons"><a href="?tab=gallery&edit_gallery=<?= $r['id_gallery'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>" class="btn-action btn-edit-action"><i class="fas fa-pencil-alt"></i> EDIT</a>
        <button type="button" class="btn-action btn-delete-action delete-btn" data-id="<?= $r['id_gallery'] ?>" data-name="<?= htmlspecialchars($r['judul']) ?>" data-type="gallery" data-search="<?= htmlspecialchars($search) ?>" data-page="<?= $page ?>"><i class="fas fa-trash-alt"></i> HAPUS</button></div></td></tr>
    <?php endwhile; else: ?>
        <tr><td colspan="4" style="text-align:center; padding:40px;"><i class="fas fa-image"></i> [ DATA GALLERY KOSONG ]</td></tr>
    <?php endif; ?>
    </tbody></table></div>
    <?php if($totalPages>1): ?>
    <div class="pagination-area"><button class="btn btn-secondary pagi-prev" data-page="<?= $page-1 ?>" <?= $page<=1?'disabled':'' ?>><i class="fas fa-chevron-left"></i> PREV</button><span>HALAMAN <?= $page ?> DARI <?= $totalPages ?></span><button class="btn btn-secondary pagi-next" data-page="<?= $page+1 ?>" <?= $page>=$totalPages?'disabled':'' ?>>NEXT <i class="fas fa-chevron-right"></i></button></div>
    <?php endif; return ob_get_clean();
}

function renderEventTable($conn, $search, $page, $limit, $offset) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where = $search ? "WHERE judul_event LIKE '%$safe_search%'" : "";
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events $where"));
    $totalPages = ceil($count['total'] / $limit);
    // Urutan: featured dulu, kemudian mendatang, lalu selesai, terakhir tanggal
    $q = mysqli_query($conn, "SELECT * FROM events $where ORDER BY is_featured DESC, FIELD(status_event, 'mendatang', 'selesai'), tanggal_event DESC LIMIT $limit OFFSET $offset");
    ob_start(); ?>
    <div class="table-container"><table class="data-table"><thead><tr><th class="col-img">COVER</th><th class="col-title">JUDUL EVENT</th><th class="col-date">TANGGAL</th><th class="col-status">STATUS</th><th class="col-action">AKSI</th></tr></thead><tbody>
    <?php if(mysqli_num_rows($q)>0): while($r=mysqli_fetch_assoc($q)): ?>
        <tr><td style="text-align:center"><?php if($r['foto_cover']): ?><img src="data:image/jpeg;base64,<?= base64_encode($r['foto_cover']) ?>" class="thumb-img"><?php else: ?><div class="no-img"></div><?php endif; ?></td>
        <td><strong><?= htmlspecialchars($r['judul_event']) ?></strong> <?php if($r['is_featured']): ?><span class="featured-star"><i class="fas fa-star"></i></span><?php endif; ?></td>
        <td><i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($r['tanggal_event'])) ?></td>
        <td style="text-align:center"><span class="status-badge <?= $r['status_event']=='selesai'?'status-pending':'status-active' ?>"><?= strtoupper($r['status_event']) ?></span></td>
        <td><div class="action-buttons"><a href="?tab=event&edit_event=<?= $r['id_event'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>" class="btn-action btn-edit-action"><i class="fas fa-pencil-alt"></i> EDIT</a>
        <button type="button" class="btn-action btn-delete-action delete-btn" data-id="<?= $r['id_event'] ?>" data-name="<?= htmlspecialchars($r['judul_event']) ?>" data-type="event" data-search="<?= htmlspecialchars($search) ?>" data-page="<?= $page ?>"><i class="fas fa-trash-alt"></i> HAPUS</button></div></td>
    </tr>
    <?php endwhile; else: ?>
        <tr><td colspan="5" style="text-align:center; padding:40px;"><i class="fas fa-calendar-alt"></i> [ DATA EVENT KOSONG ]</td></tr>
    <?php endif; ?>
    </tbody></table></div>
    <?php if($totalPages>1): ?>
    <div class="pagination-area"><button class="btn btn-secondary pagi-prev" data-page="<?= $page-1 ?>" <?= $page<=1?'disabled':'' ?>><i class="fas fa-chevron-left"></i> PREV</button><span>HALAMAN <?= $page ?> DARI <?= $totalPages ?></span><button class="btn btn-secondary pagi-next" data-page="<?= $page+1 ?>" <?= $page>=$totalPages?'disabled':'' ?>>NEXT <i class="fas fa-chevron-right"></i></button></div>
    <?php endif; return ob_get_clean();
}

if (isset($_GET['ajax'])) {
    if ($active_tab == 'gallery') echo renderGalleryTable($conn, $search, $page, $limit, $offset);
    else echo renderEventTable($conn, $search, $page, $limit, $offset);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gallery & Events - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard/gallery_crud.css">
</head>
<body>
<div class="overlay" id="sidebarOverlay"></div>
<?php include "../components/sidebar.php"; ?>
<main class="main-wrapper">
    <div class="mobile-header"><div><i class="fas fa-images" style="color: var(--red);"></i> WOELANDARI</div><button id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem;"><i class="fas fa-bars"></i></button></div>
    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header"><span><i class="fas fa-folder-open"></i> Kelola Gallery & Events</span><span>DATE: <?= date('d/m/Y') ?></span></div>
        <?php if ($msg_display): ?><div class="alert-msg"><i class="fas fa-check-circle"></i> <?= $msg_display ?></div><?php endif; ?>
        <?php if ($error_display): ?><div class="alert-msg error"><i class="fas fa-exclamation-circle"></i> <?= $error_display ?></div><?php endif; ?>
        
        <div class="tab-buttons">
            <a href="?tab=gallery&page=1<?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= $active_tab=='gallery'?'active':'' ?>"><i class="fas fa-image"></i> GALLERY</a>
            <a href="?tab=event&page=1<?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= $active_tab=='event'?'active':'' ?>"><i class="fas fa-calendar-alt"></i> EVENTS</a>
        </div>

        <div class="search-area">
            <div class="search-wrapper"><i class="fas fa-search"></i><input type="text" id="searchInput" class="search-input" placeholder="Cari judul <?= $active_tab=='gallery'?'foto':'event' ?>..." value="<?= htmlspecialchars($search) ?>"></div>
            <button class="btn btn-primary" id="searchBtn">CARI DATA</button>
            <?php if ($search): ?><a href="gallery_crud.php?tab=<?= $active_tab ?>&page=1" class="btn btn-secondary">RESET</a><?php endif; ?>
            <button class="btn btn-primary add-entry-btn" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);"><i class="fas fa-plus"></i> ADD ENTRY</button>
        </div>
        <div id="ajax-table-container">
            <?php if ($active_tab == 'gallery') echo renderGalleryTable($conn, $search, $page, $limit, $offset);
            else echo renderEventTable($conn, $search, $page, $limit, $offset); ?>
        </div>
    </section>
</main>

<!-- MODAL GALLERY (tidak berubah) -->
<div class="modal" id="modalGallery">
    <div class="modal-content">
        <div class="tape" style="top:-16px; width:100px; height:25px;"></div>
        <div class="modal-header-area"><div class="spec-header" style="margin-bottom:10px;"><span id="galleryModalTitle">TAMBAH FOTO GALLERY</span></div></div>
        <div class="modal-body-scroll">
            <form id="formGallery" action="gallery_crud.php?tab=gallery&page=<?= $page ?><?= $search ? '&search='.urlencode($search) : '' ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_gallery" id="gal_id" value="<?= $gal_id ?>">
                <input type="hidden" name="foto_cropped_gallery" id="foto_cropped_gallery" value="">
                <div class="form-group"><label class="form-label">JUDUL FOTO</label><input type="text" name="judul" id="gal_judul" class="form-input" value="<?= htmlspecialchars($gal_judul) ?>" required></div>
                <div class="form-group"><label class="form-label">DESKRIPSI SINGKAT</label><textarea name="deskripsi" id="gal_deskripsi" class="form-input" rows="2"><?= htmlspecialchars($gal_deskripsi) ?></textarea></div>
                <div class="form-group">
                    <label class="form-label">TIPE KONTEN</label>
                    <select name="tipe_koneksi" id="tipe_koneksi" class="form-select">
                        <option value="biasa" <?= (!$gal_event || $gal_event == 'NULL') ? 'selected' : '' ?>>Foto Gallery Biasa</option>
                        <option value="event" <?= ($gal_event && $gal_event != 'NULL') ? 'selected' : '' ?>>Terhubung dengan Event</option>
                    </select>
                    <div id="eventSelectWrapper" style="margin-top:10px; <?= ($gal_event && $gal_event != 'NULL') ? '' : 'display:none;' ?>">
                        <label class="form-label">PILIH EVENT</label>
                        <select name="id_event" id="gal_id_event" class="form-select">
                            <option value="">-- Pilih Event --</option>
                            <?php $events = mysqli_query($conn, "SELECT id_event, judul_event FROM events ORDER BY is_featured DESC, tanggal_event DESC");
                            while($ev = mysqli_fetch_assoc($events)): ?>
                                <option value="<?= $ev['id_event'] ?>" <?= ($gal_event == $ev['id_event']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['judul_event']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="upload-box-safe">
                    <label class="form-label">UPLOAD FOTO (RASIO 1:1)</label>
                    <?php if ($gal_foto): ?>
                        <div style="margin-bottom:10px;"><img src="data:image/jpeg;base64,<?= base64_encode($gal_foto) ?>" style="width:80px; border:2px solid var(--navy);"><p style="font-size:0.75rem;">[ FOTO SAAT INI ]</p></div>
                    <?php endif; ?>
                    <input type="file" id="file_foto_gallery" name="file_foto" accept="image/*" <?= $edit_gal_mode ? '' : 'required' ?>>
                    <div id="previewAreaGallery" style="display:none; margin-top:15px;"><img id="preview_gallery" style="max-width:100px; border:2px solid var(--navy);"><p style="color:green;">✓ CROP SELESAI</p></div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:25px;"><button type="button" class="btn btn-secondary close-modal-btn">BATAL</button><button type="submit" name="<?= $edit_gal_mode ? 'update_gallery' : 'simpan_gallery' ?>" class="btn btn-primary">SIMPAN DATA</button></div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EVENT (DIPERBAIKI: checkbox disabled jika event expired) -->
<div class="modal" id="modalEvent">
    <div class="modal-content">
        <div class="tape" style="top:-16px; width:100px; height:25px;"></div>
        <div class="modal-header-area"><div class="spec-header" style="margin-bottom:10px;"><span id="eventModalTitle"><?= $edit_event_mode ? 'UPDATE EVENT' : 'BUAT EVENT BARU' ?></span></div></div>
        <div class="modal-body-scroll">
            <form id="formEvent" action="gallery_crud.php?tab=event&page=<?= $page ?><?= $search ? '&search='.urlencode($search) : '' ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_event" id="ev_id" value="<?= $ev_id ?>">
                <input type="hidden" name="foto_cropped_event" id="foto_cropped_event" value="">
                <div class="form-group"><label class="form-label">JUDUL EVENT</label><input type="text" name="judul_event" id="ev_judul" class="form-input" value="<?= htmlspecialchars($ev_judul) ?>" required></div>
                <div class="form-grid two-cols">
                    <div class="form-group"><label class="form-label">TANGGAL PELAKSANAAN</label><input type="date" name="tanggal_event" id="ev_tanggal" class="form-input" value="<?= $ev_tanggal ?>" required></div>
                    <div class="form-group"><label class="form-label">STATUS EVENT</label>
                        <select name="status_event" id="ev_status" class="form-select">
                            <option value="mendatang" <?= ($ev_status=='mendatang') ? 'selected' : '' ?>>MENDATANG</option>
                            <option value="selesai" <?= ($ev_status=='selesai') ? 'selected' : '' ?>>SELESAI</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label class="form-label">DESKRIPSI LENGKAP</label><textarea name="deskripsi_event" id="ev_deskripsi" class="form-input" rows="3" required><?= htmlspecialchars($ev_deskripsi) ?></textarea></div>
                
                <div class="form-group">
                    <label class="form-label">FITUR TAMPIL</label>
                    <div class="checkbox-group">
                        <?php 
                        // Tentukan apakah checkbox disabled (jika event sudah selesai)
                        $is_checkbox_disabled = false;
                        $checkbox_title = "";
                        if ($edit_event_mode && $ev_is_expired) {
                            $is_checkbox_disabled = true;
                            $checkbox_title = "Event sudah selesai, tidak dapat disematkan.";
                        }
                        ?>
                        <label class="custom-checkbox" title="<?= $checkbox_title ?>">
                            <input type="checkbox" name="is_featured" value="1" <?= ($ev_featured && !$is_checkbox_disabled) ? 'checked' : '' ?> <?= $is_checkbox_disabled ? 'disabled' : '' ?>>
                            <span class="checkmark"></span>
                            <span class="checkbox-text">Sematkan Event (ditampilkan lebih dulu)</span>
                        </label>
                        <?php if($is_checkbox_disabled): ?>
                            <p style="font-size:0.7rem; color:var(--red); margin-top:5px;"><i class="fas fa-info-circle"></i> Event sudah selesai, tidak bisa disematkan.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="upload-box-safe">
                    <label class="form-label">UPLOAD COVER (RASIO 1:1)</label>
                    <?php if ($ev_foto): ?>
                        <div style="margin-bottom:10px;"><img src="data:image/jpeg;base64,<?= base64_encode($ev_foto) ?>" style="width:80px; border:2px solid var(--navy);"><p style="font-size:0.75rem;">[ COVER SAAT INI ]</p></div>
                    <?php endif; ?>
                    <input type="file" id="foto_cover_event" name="foto_cover" accept="image/*" <?= $edit_event_mode ? '' : 'required' ?>>
                    <div id="previewAreaEvent" style="display:none; margin-top:15px;"><img id="preview_event" style="max-width:100px; border:2px solid var(--navy);"><p style="color:green;">✓ CROP SELESAI</p></div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:25px;"><button type="button" class="btn btn-secondary close-modal-btn">BATAL</button><button type="submit" name="<?= $edit_event_mode ? 'update_event' : 'simpan_event' ?>" class="btn btn-primary">SIMPAN DATA</button></div>
            </form>
        </div>
    </div>
</div>

<!-- CROP MODAL (tidak berubah) -->
<div id="cropModal" class="modal" style="z-index:2100;"><div class="modal-content" style="max-width:460px; padding:20px;"><div class="spec-header" style="margin-bottom:15px;" id="cropTitle">CROP GAMBAR (1:1)</div><div style="border:2px solid var(--navy); background:#000;"><img id="cropImage" src="" style="max-width:100%;"></div><div style="display:flex; justify-content:flex-end; gap:15px; margin-top:20px;"><button class="btn btn-secondary" id="cancelCropBtn">BATAL</button><button class="btn btn-primary" id="cropConfirmBtn" style="background:var(--red);">CROP & SET</button></div></div></div>

<!-- DELETE CONFIRM MODAL -->
<div id="deleteConfirmModal" class="confirm-modal"><div class="confirm-modal-content"><div class="confirm-modal-header"><i class="fas fa-exclamation-triangle"></i></div><div class="confirm-modal-body"><h3>HAPUS DATA?</h3><p>Apakah Anda yakin ingin menghapus data berikut?</p><div class="item-name-highlight" id="itemNameToDelete"></div><p style="font-size:0.8rem;"><i class="fas fa-info-circle"></i> Data dihapus tidak dapat dikembalikan!</p></div><div class="confirm-modal-footer"><button class="btn btn-secondary" id="cancelDeleteBtn">BATAL</button><a href="#" id="confirmDeleteBtn" class="btn btn-danger">HAPUS</a></div></div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
// Sidebar
const btn = document.getElementById('hamburgerBtn'), sidebar = document.getElementById('mainSidebar'), overlay = document.getElementById('sidebarOverlay');
if(btn) btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
if(overlay) overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

let currentTab = '<?= $active_tab ?>', currentSearch = '<?= addslashes($search) ?>', currentPage = <?= $page ?>;
const container = document.getElementById('ajax-table-container');
function loadTable(page, search, replaceState = false) {
    container.innerHTML = '<div class="loading-overlay"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
    let url = `gallery_crud.php?ajax=1&tab=${currentTab}&page=${page}`;
    if(search) url += `&search=${encodeURIComponent(search)}`;
    fetch(url).then(res => res.text()).then(html => {
        container.innerHTML = html;
        let newUrl = `gallery_crud.php?tab=${currentTab}&page=${page}` + (search ? `&search=${encodeURIComponent(search)}` : '');
        if(replaceState) window.history.replaceState({tab:currentTab,page,search}, '', newUrl);
        else window.history.pushState({tab:currentTab,page,search}, '', newUrl);
        currentPage = page; currentSearch = search;
    }).catch(() => container.innerHTML = '<div class="loading-overlay" style="color:red;">Gagal memuat data</div>');
}
container.addEventListener('click', e => {
    const pagi = e.target.closest('.pagi-prev, .pagi-next');
    if(pagi && !pagi.disabled) { e.preventDefault(); loadTable(pagi.dataset.page, document.getElementById('searchInput').value); return; }
    const del = e.target.closest('.delete-btn');
    if(del) {
        e.preventDefault();
        document.getElementById('itemNameToDelete').innerText = del.dataset.name;
        let url = `?tab=${currentTab}&hapus_${del.dataset.type}=${del.dataset.id}&search=${encodeURIComponent(del.dataset.search||'')}&page=${del.dataset.page||'1'}`;
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteConfirmModal').style.display = 'flex';
    }
});
document.getElementById('searchBtn')?.addEventListener('click', () => loadTable(1, document.getElementById('searchInput').value));
document.getElementById('searchInput')?.addEventListener('keypress', e => { if(e.key === 'Enter') document.getElementById('searchBtn').click(); });
document.querySelectorAll('.tab-btn').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        const newTab = new URL(this.href).searchParams.get('tab');
        if(newTab && newTab !== currentTab) {
            currentTab = newTab;
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            loadTable(1, document.getElementById('searchInput').value, false);
        }
    });
});
function resetAndOpenModal(modalId) {
    const modal = document.getElementById(modalId);
    const form = modal.querySelector('form');
    if(form) form.reset();
    modal.querySelectorAll('[id^="previewArea"]').forEach(div => div.style.display = 'none');
    modal.querySelectorAll('input[type="hidden"]').forEach(h => { if(h.id.includes('cropped')) h.value = ''; });
    modal.style.display = 'flex';
}
document.querySelector('.add-entry-btn')?.addEventListener('click', () => resetAndOpenModal(currentTab === 'gallery' ? 'modalGallery' : 'modalEvent'));
document.querySelectorAll('.close-modal-btn').forEach(btn => btn.addEventListener('click', function() { this.closest('.modal').style.display = 'none'; }));
<?php if ($edit_gal_mode): ?>resetAndOpenModal('modalGallery'); document.getElementById('gal_id').value='<?= $gal_id ?>'; document.getElementById('gal_judul').value='<?= addslashes($gal_judul) ?>'; document.getElementById('gal_deskripsi').value='<?= addslashes($gal_deskripsi) ?>'; if(<?= $gal_event ? 'true' : 'false' ?>) { document.getElementById('tipe_koneksi').value='event'; document.getElementById('eventSelectWrapper').style.display='block'; document.getElementById('gal_id_event').value='<?= $gal_event ?>'; } <?php endif; ?>
<?php if ($edit_event_mode): ?>resetAndOpenModal('modalEvent'); document.getElementById('ev_id').value='<?= $ev_id ?>'; document.getElementById('ev_judul').value='<?= addslashes($ev_judul) ?>'; document.getElementById('ev_tanggal').value='<?= $ev_tanggal ?>'; document.getElementById('ev_status').value='<?= $ev_status ?>'; document.getElementById('ev_deskripsi').value='<?= addslashes($ev_deskripsi) ?>'; <?php if(!$ev_is_expired && $ev_featured): ?>document.querySelector('input[name="is_featured"]').checked=true;<?php endif; ?> <?php endif; ?>

// Dropdown tipe konten gallery
const tipeKoneksi = document.getElementById('tipe_koneksi');
const eventWrapper = document.getElementById('eventSelectWrapper');
if(tipeKoneksi) {
    tipeKoneksi.addEventListener('change', function() {
        eventWrapper.style.display = this.value === 'event' ? 'block' : 'none';
    });
}

// Cropper logic
let cropper, targetCropType = '';
const cropModal = document.getElementById('cropModal'), cropImage = document.getElementById('cropImage'), cropConfirm = document.getElementById('cropConfirmBtn'), cancelCrop = document.getElementById('cancelCropBtn');
function initCropper(file, type) { if(!file.type.match('image.*')) { alert('Hanya gambar'); return; } targetCropType = type; const reader = new FileReader(); reader.onload = e => { cropImage.src = e.target.result; cropModal.style.display = 'flex'; if(cropper) cropper.destroy(); cropper = new Cropper(cropImage, { aspectRatio: 1, viewMode: 1 }); }; reader.readAsDataURL(file); }
document.getElementById('file_foto_gallery')?.addEventListener('change', e => { if(e.target.files[0]) initCropper(e.target.files[0], 'gallery'); });
document.getElementById('foto_cover_event')?.addEventListener('change', e => { if(e.target.files[0]) initCropper(e.target.files[0], 'event'); });
cropConfirm.addEventListener('click', () => { if(cropper) { const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 }); const base64 = canvas.toDataURL('image/jpeg', 0.85); if(targetCropType === 'gallery') { document.getElementById('foto_cropped_gallery').value = base64; document.getElementById('preview_gallery').src = base64; document.getElementById('previewAreaGallery').style.display = 'block'; document.getElementById('file_foto_gallery').removeAttribute('required'); } else { document.getElementById('foto_cropped_event').value = base64; document.getElementById('preview_event').src = base64; document.getElementById('previewAreaEvent').style.display = 'block'; document.getElementById('foto_cover_event').removeAttribute('required'); } cropModal.style.display = 'none'; cropper.destroy(); } });
cancelCrop.addEventListener('click', () => { cropModal.style.display = 'none'; if(cropper) cropper.destroy(); });
document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => document.getElementById('deleteConfirmModal').style.display = 'none');
document.getElementById('deleteConfirmModal')?.addEventListener('click', e => { if(e.target === document.getElementById('deleteConfirmModal')) e.target.style.display = 'none'; });
window.addEventListener('popstate', e => { if(e.state) { currentTab = e.state.tab; loadTable(e.state.page, e.state.search, true); } else location.reload(); });
window.history.replaceState({tab: currentTab, page: currentPage, search: currentSearch}, '', window.location.href);
</script>
</body>
</html>