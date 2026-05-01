<?php
require_once __DIR__ . '/auth.php';

function upload_image(string $field, string $folder, string $fallback = 'default.jpg'): string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return $fallback;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return $fallback;
    }

    $file = uniqid($folder . '_', true) . '.' . $ext;
    $target = __DIR__ . '/../assets/images/' . $folder . '/' . $file;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $target) ? $file : $fallback;
}

function delete_image(string $folder, ?string $file): void
{
    if (!$file || $file === 'default.jpg') {
        return;
    }

    $path = __DIR__ . '/../assets/images/' . $folder . '/' . $file;
    if (is_file($path)) {
        unlink($path);
    }
}

if (isset($_POST['save_event'])) {
    $id = (int) ($_POST['id_event'] ?? 0);
    $judul = mysqli_real_escape_string($conn, $_POST['judul_event'] ?? '');
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_event'] ?? date('Y-m-d'));
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_event'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status_event'] ?? 'active');
    $foto_lama = $_POST['foto_lama'] ?? 'default.jpg';
    $foto = upload_image('foto_cover', 'events', $foto_lama);

    if ($id > 0) {
        if ($foto !== $foto_lama) delete_image('events', $foto_lama);
        mysqli_query($conn, "UPDATE events SET judul_event='$judul', tanggal_event='$tanggal', deskripsi_event='$deskripsi', status_event='$status', foto_cover='$foto' WHERE id_event=$id");
    } else {
        mysqli_query($conn, "INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, foto_cover) VALUES ('$judul', '$tanggal', '$deskripsi', '$status', '$foto')");
    }

    header('Location: gallery_crud.php?tab=event&msg=saved');
    exit;
}

if (isset($_POST['save_gallery'])) {
    $id = (int) ($_POST['id_gallery'] ?? 0);
    $id_event = $_POST['id_event'] === '' ? 'NULL' : (int) $_POST['id_event'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $foto_lama = $_POST['foto_lama'] ?? 'default.jpg';
    $foto = upload_image('file_foto', 'gallery', $foto_lama);

    if ($id > 0) {
        if ($foto !== $foto_lama) delete_image('gallery', $foto_lama);
        mysqli_query($conn, "UPDATE gallery SET id_event=$id_event, judul='$judul', deskripsi='$deskripsi', file_foto='$foto' WHERE id_gallery=$id");
    } else {
        mysqli_query($conn, "INSERT INTO gallery (id_event, judul, deskripsi, file_foto) VALUES ($id_event, '$judul', '$deskripsi', '$foto')");
    }

    header('Location: gallery_crud.php?tab=gallery&msg=saved');
    exit;
}

if (isset($_GET['hapus_gallery'])) {
    $id = (int) $_GET['hapus_gallery'];
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_foto FROM gallery WHERE id_gallery=$id"));
    delete_image('gallery', $old['file_foto'] ?? null);
    mysqli_query($conn, "DELETE FROM gallery WHERE id_gallery=$id");
    header('Location: gallery_crud.php?tab=gallery&msg=deleted');
    exit;
}

if (isset($_GET['hapus_event'])) {
    $id = (int) $_GET['hapus_event'];
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto_cover FROM events WHERE id_event=$id"));
    delete_image('events', $old['foto_cover'] ?? null);
    mysqli_query($conn, "DELETE FROM events WHERE id_event=$id");
    header('Location: gallery_crud.php?tab=event&msg=deleted');
    exit;
}

$active_tab = $_GET['tab'] ?? 'gallery';
$edit_event = null;
$edit_gallery = null;

if (isset($_GET['edit_event'])) {
    $id = (int) $_GET['edit_event'];
    $edit_event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id_event=$id"));
    $active_tab = 'event';
}

if (isset($_GET['edit_gallery'])) {
    $id = (int) $_GET['edit_gallery'];
    $edit_gallery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM gallery WHERE id_gallery=$id"));
    $active_tab = 'gallery';
}

$events = mysqli_query($conn, "SELECT * FROM events ORDER BY tanggal_event DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gallery & Event</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --red:#9b2226; --navy:#001219; --paper:#e5e5e5; --side:260px; }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--paper); font-family:'Courier Prime', monospace; color:var(--navy); display:flex; }
        .sidebar { width:var(--side); background:var(--navy); color:#fff; min-height:100vh; padding:20px; position:fixed; }
        .brand { font-family:'Special Elite', cursive; font-size:1.5rem; color:var(--red); padding-bottom:20px; border-bottom:2px double #444; margin-bottom:25px; }
        .nav-item { display:block; color:#bdc3c7; text-decoration:none; padding:14px; border-left:4px solid transparent; margin-bottom:4px; }
        .nav-item:hover, .nav-item.active { color:#fff; background:rgba(255,255,255,.06); border-left-color:var(--red); }
        .main { margin-left:var(--side); width:calc(100% - var(--side)); padding:40px; }
        h1, h2 { font-family:'Special Elite', cursive; }
        .tabs { display:flex; gap:12px; margin-bottom:25px; }
        .tabs a, .btn { background:#fff; border:2px solid var(--navy); color:var(--navy); padding:10px 16px; text-decoration:none; font-weight:bold; cursor:pointer; }
        .tabs a.active, .btn.primary { background:var(--navy); color:#fff; box-shadow:4px 4px 0 var(--red); }
        .grid { display:grid; grid-template-columns:360px 1fr; gap:25px; align-items:start; }
        .card { background:#fff; border:2px solid var(--navy); padding:24px; box-shadow:8px 8px 0 rgba(0,0,0,.08); }
        label { display:block; font-weight:bold; margin-top:14px; font-size:.85rem; }
        input, select, textarea { width:100%; padding:10px; border:1px solid #aaa; font-family:inherit; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; vertical-align:top; }
        th { border-bottom:2px solid var(--navy); font-family:'Special Elite', cursive; }
        .preview { width:72px; height:72px; object-fit:cover; border:2px solid var(--navy); }
        .actions a { margin-right:10px; color:var(--navy); font-weight:bold; }
        .actions a.delete { color:var(--red); }
        .notice { padding:12px 16px; background:var(--navy); color:#fff; margin-bottom:20px; }
        @media (max-width:900px) { .sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0;width:100%;padding:20px}.grid{grid-template-columns:1fr} body{display:block} }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
    <a href="dashboard.php" class="nav-item">Dashboard</a>
    <a href="menu_crud.php" class="nav-item">Menu</a>
    <a href="gallery_crud.php" class="nav-item active">Gallery</a>
    <a href="feedback.php" class="nav-item">Feedback</a>
    <a href="user_manajemen.php" class="nav-item">Kelola User</a>
    <a href="../logout.php" class="nav-item">Logout</a>
</aside>

<main class="main">
    <h1>GALLERY & EVENT CONTROL</h1>
    <?php if (isset($_GET['msg'])): ?><div class="notice">SYSTEM_MESSAGE: ACTION_SUCCESS</div><?php endif; ?>

    <nav class="tabs">
        <a href="gallery_crud.php?tab=gallery" class="<?php echo $active_tab === 'gallery' ? 'active' : ''; ?>">Kelola Gallery</a>
        <a href="gallery_crud.php?tab=event" class="<?php echo $active_tab === 'event' ? 'active' : ''; ?>">Kelola Event</a>
    </nav>

    <?php if ($active_tab === 'event'): ?>
        <section class="grid">
            <form class="card" method="POST" enctype="multipart/form-data">
                <h2><?php echo $edit_event ? 'Edit Event' : 'Tambah Event'; ?></h2>
                <input type="hidden" name="id_event" value="<?php echo (int) ($edit_event['id_event'] ?? 0); ?>">
                <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($edit_event['foto_cover'] ?? 'default.jpg'); ?>">
                <label>Judul Event</label>
                <input type="text" name="judul_event" required value="<?php echo htmlspecialchars($edit_event['judul_event'] ?? ''); ?>">
                <label>Tanggal</label>
                <input type="date" name="tanggal_event" required value="<?php echo htmlspecialchars($edit_event['tanggal_event'] ?? date('Y-m-d')); ?>">
                <label>Status</label>
                <input type="text" name="status_event" value="<?php echo htmlspecialchars($edit_event['status_event'] ?? 'active'); ?>">
                <label>Deskripsi</label>
                <textarea name="deskripsi_event" rows="5"><?php echo htmlspecialchars($edit_event['deskripsi_event'] ?? ''); ?></textarea>
                <label>Cover</label>
                <input type="file" name="foto_cover" accept="image/*">
                <button class="btn primary" name="save_event" style="margin-top:18px;">Simpan Event</button>
            </form>

            <div class="card">
                <h2>Daftar Event</h2>
                <table>
                    <thead><tr><th>Cover</th><th>Event</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php mysqli_data_seek($events, 0); while ($row = mysqli_fetch_assoc($events)): ?>
                        <tr>
                            <td><img class="preview" src="../assets/images/events/<?php echo htmlspecialchars($row['foto_cover']); ?>" alt=""></td>
                            <td><strong><?php echo htmlspecialchars($row['judul_event']); ?></strong><br><?php echo htmlspecialchars($row['status_event']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_event'])); ?></td>
                            <td class="actions">
                                <a href="gallery_crud.php?tab=event&edit_event=<?php echo (int) $row['id_event']; ?>">EDIT</a>
                                <a class="delete" href="gallery_crud.php?tab=event&hapus_event=<?php echo (int) $row['id_event']; ?>" onclick="return confirm('Hapus event ini?')">DEL</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php else: ?>
        <section class="grid">
            <form class="card" method="POST" enctype="multipart/form-data">
                <h2><?php echo $edit_gallery ? 'Edit Gallery' : 'Tambah Gallery'; ?></h2>
                <input type="hidden" name="id_gallery" value="<?php echo (int) ($edit_gallery['id_gallery'] ?? 0); ?>">
                <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($edit_gallery['file_foto'] ?? 'default.jpg'); ?>">
                <label>Event Terkait</label>
                <select name="id_event">
                    <option value="">Tanpa event</option>
                    <?php mysqli_data_seek($events, 0); while ($ev = mysqli_fetch_assoc($events)): ?>
                        <option value="<?php echo (int) $ev['id_event']; ?>" <?php echo (isset($edit_gallery['id_event']) && (int) $edit_gallery['id_event'] === (int) $ev['id_event']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ev['judul_event']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label>Judul</label>
                <input type="text" name="judul" required value="<?php echo htmlspecialchars($edit_gallery['judul'] ?? ''); ?>">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="5"><?php echo htmlspecialchars($edit_gallery['deskripsi'] ?? ''); ?></textarea>
                <label>Foto</label>
                <input type="file" name="file_foto" accept="image/*">
                <button class="btn primary" name="save_gallery" style="margin-top:18px;">Simpan Gallery</button>
            </form>

            <div class="card">
                <h2>Daftar Gallery</h2>
                <table>
                    <thead><tr><th>Foto</th><th>Judul</th><th>Event</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php
                    $galleries = mysqli_query($conn, "SELECT g.*, e.judul_event FROM gallery g LEFT JOIN events e ON g.id_event=e.id_event ORDER BY g.id_gallery DESC");
                    while ($row = mysqli_fetch_assoc($galleries)):
                    ?>
                        <tr>
                            <td><img class="preview" src="../assets/images/gallery/<?php echo htmlspecialchars($row['file_foto']); ?>" alt=""></td>
                            <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong><br><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                            <td><?php echo htmlspecialchars($row['judul_event'] ?? '-'); ?></td>
                            <td class="actions">
                                <a href="gallery_crud.php?tab=gallery&edit_gallery=<?php echo (int) $row['id_gallery']; ?>">EDIT</a>
                                <a class="delete" href="gallery_crud.php?tab=gallery&hapus_gallery=<?php echo (int) $row['id_gallery']; ?>" onclick="return confirm('Hapus foto ini?')">DEL</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
