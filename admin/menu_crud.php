<?php
include "../config/koneksi.php";

// --- LOGIKA PHP (Tetap Sama Persis) ---
$edit_mode = false;
$edit_id = ""; $edit_nama = ""; $edit_kategori = ""; $edit_harga = ""; $edit_stok = ""; $edit_deskripsi = ""; $edit_foto = "";

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $query_edit = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$edit_id'");
    $data_edit = mysqli_fetch_assoc($query_edit);
    $edit_nama = $data_edit['nama_menu']; $edit_kategori = $data_edit['kategori']; $edit_harga = $data_edit['harga'];
    $edit_stok = $data_edit['stok']; $edit_deskripsi = $data_edit['deskripsi']; $edit_foto = $data_edit['foto'];
}

// ... (Logika simpan, update, hapus tetap sama seperti code Anda) ...
if (isset($_POST['simpan'])) {
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = $_POST['harga']; $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_nama = "default.jpg"; 
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/menu/' . $foto_nama, $img_base64);
    }
    $query = "INSERT INTO menu (nama_menu, kategori, harga, stok, deskripsi, foto) VALUES ('$nama_menu', '$kategori', '$harga', '$stok', '$deskripsi', '$foto_nama')";
    mysqli_query($conn, $query);
    echo "<script>alert('Menu baru berhasil ditambahkan!'); window.location='menu_crud.php';</script>";
}
if (isset($_POST['update'])) {
    $id_menu = $_POST['id_menu'];
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = $_POST['harga']; $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = $_POST['foto_lama'];
    $foto_nama = $foto_lama; 
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/menu/' . $foto_nama, $img_base64);
        if (file_exists('../assets/images/menu/' . $foto_lama) && $foto_lama != 'default.jpg') {
            unlink('../assets/images/menu/' . $foto_lama);
        }
    }
    $query = "UPDATE menu SET nama_menu='$nama_menu', kategori='$kategori', harga='$harga', stok='$stok', deskripsi='$deskripsi', foto='$foto_nama' WHERE id_menu='$id_menu'";
    mysqli_query($conn, $query);
    echo "<script>alert('Data menu berhasil diperbarui!'); window.location='menu_crud.php';</script>";
}
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus']; 
    $query_foto = mysqli_query($conn, "SELECT foto FROM menu WHERE id_menu='$id_hapus'");
    $data_foto = mysqli_fetch_assoc($query_foto);
    if (file_exists('../assets/images/menu/' . $data_foto['foto']) && $data_foto['foto'] != 'default.jpg') {
        unlink('../assets/images/menu/' . $data_foto['foto']);
    }
    mysqli_query($conn, "DELETE FROM menu WHERE id_menu='$id_hapus'");
    echo "<script>alert('Menu berhasil dihapus!'); window.location='menu_crud.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Database Menu</title>
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
            display: flex; /* Gabungkan sidebar dan main */
        }

        /* --- SIDEBAR STYLE (Sama Persis Dashboard) --- */
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
            transition: 0.3s;
            margin-bottom: 5px;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left: 4px solid var(--red-ink);
        }

        /* --- MAIN CONTENT ADJUSTMENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
        }

        .page-header h1 {
            font-family: 'Special Elite', cursive;
            margin: 0;
            font-size: 2rem;
        }

        /* Form & Table Container Penyesuaian */
        .form-container, .table-container {
            background: white;
            border: 2px solid var(--navy-ink);
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 8px 8px 0px rgba(0,0,0,0.1);
        }

        .tape-table {
            position: absolute;
            width: 80px; height: 30px;
            background: rgba(0,0,0,0.05);
            top: -15px; left: 20px;
            transform: rotate(-2deg);
            border: 1px dashed rgba(0,0,0,0.1);
        }

        h2 { font-family: 'Special Elite', cursive; margin-top: 0; }

        /* Tombol-tombol */
        .btn-submit {
            font-family: 'Special Elite', cursive;
            background: var(--red-ink);
            color: white;
            border: 2px solid var(--red-ink);
            padding: 12px 20px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover { background: var(--navy-ink); border-color: var(--navy-ink); }

        .btn-action {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.8rem;
            padding: 5px 10px;
            border: 1px solid;
            margin-right: 5px;
        }
        .btn-edit { color: var(--navy-ink); border-color: var(--navy-ink); }
        .btn-delete { color: var(--red-ink); border-color: var(--red-ink); }

        /* Table Aesthetics */
        .aesthetic-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .aesthetic-table th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid var(--navy-ink);
            font-family: 'Special Elite';
        }
        .aesthetic-table td { padding: 12px; border-bottom: 1px solid #eee; }

        .img-preview { border: 2px solid var(--navy-ink); object-fit: cover; }
        .img-3x4 { width: 60px; height: 80px; }

        /* Modal / Crop container */
        .crop-container {
            display: none; width: 100%; max-width: 400px; 
            margin: 15px 0; border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
    <nav class="nav-list">
        <a href="dashboard.php" class="nav-item "> <span>Dashboard</span></a>
        <a href="menu_crud.php" class="nav-item active"><span>Menu</span></a>
        <a href="gallery_crud.php" class="nav-item"> <span>Gallery</span></a>
        <a href="#" class="nav-item"><span>Feedback</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>Kelola User</span></a>
    </nav>
    <div style="margin-top: auto; border-top: 1px dashed #555; padding-top: 10px;">
        <a href="logout.php" class="nav-item" style="color: #ff6b6b;">>> <span>TERMINATE</span></a>
    </div>
</aside>

<main class="main-content">
    <header class="page-header">
        <h1>INVENTORY_MANAGEMENT</h1>
        <div style="font-size: 0.8rem; color: var(--red-ink); font-weight: bold;">// MODULE: MENU_DATABASE_ACCESS</div>
    </header>

    <div style="margin-bottom: 25px;">
        <?php if(!$edit_mode): ?>
            <button id="btnToggleForm" class="btn-submit">+ TAMBAH MENU BARU</button>
        <?php else: ?>
            <h3 style="font-family: 'Special Elite', cursive; color: var(--red-ink);">* MODE_EDIT: ON_PROGRESS</h3>
        <?php endif; ?>
    </div>

    <div class="form-container" id="boxForm" style="<?php echo $edit_mode ? 'display: block;' : 'display: none;'; ?>">
        <h2><?php echo $edit_mode ? "UPDATE_ENTRY" : "NEW_ENTRY"; ?></h2>
        <form id="formMenu" method="POST" enctype="multipart/form-data">
            <?php if($edit_mode): ?>
                <input type="hidden" name="id_menu" value="<?php echo $edit_id; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $edit_foto; ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">NAMA MENU:</label>
                    <input type="text" name="nama_menu" required style="width:100%; padding:10px;" value="<?php echo $edit_nama; ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">KATEGORI:</label>
                    <select name="kategori" required style="width:100%; padding:10px;">
                        <option value="Coffee" <?php echo ($edit_kategori == 'Coffee') ? 'selected' : ''; ?>>Coffee</option>
                        <option value="Non-Coffee" <?php echo ($edit_kategori == 'Non-Coffee') ? 'selected' : ''; ?>>Non-Coffee</option>
                        <option value="Snack" <?php echo ($edit_kategori == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                        <option value="Main Course" <?php echo ($edit_kategori == 'Main Course') ? 'selected' : ''; ?>>Main Course</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:15px;">
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">HARGA (Rp):</label>
                    <input type="number" name="harga" required style="width:100%; padding:10px;" value="<?php echo $edit_harga; ?>">
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">STOK:</label>
                    <input type="number" name="stok" required style="width:100%; padding:10px;" value="<?php echo $edit_stok; ?>">
                </div>
            </div>

            <div style="margin-top:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">DESKRIPSI:</label>
                <textarea name="deskripsi" rows="3" required style="width:100%; padding:10px;"><?php echo $edit_deskripsi; ?></textarea>
            </div>

            <div style="margin-top:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">FOTO MENU (3:4):</label>
                <input type="file" id="inputFoto" accept="image/*">
            </div>

            <div class="crop-container" id="cropContainerBox">
                <img id="image-to-crop" src="" style="max-width: 100%;">
            </div>
            <input type="hidden" name="foto_cropped" id="foto_cropped">

            <div style="margin-top:20px;">
                <button type="button" class="btn-submit" id="btnSimpan">
                    <?php echo $edit_mode ? "COMMIT_CHANGES" : "SAVE_RECORD"; ?>
                </button>
                <button type="submit" name="<?php echo $edit_mode ? 'update' : 'simpan'; ?>" id="btnSubmitAsli" style="display:none;"></button>
                <?php if($edit_mode): ?>
                    <a href="menu_crud.php" style="margin-left:10px; color:var(--red-ink);">[CANCEL]</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="tape-table"></div>
        <h2>List Menu</h2>
        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>FOTO</th>
                    <th>NAMA MENU</th>
                    <th>KATEGORI</th>
                    <th>HARGA</th>
                    <th>STOK</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC");
                while ($row = mysqli_fetch_assoc($query)) :
                ?>
                <tr>
                    <td><img src="../assets/images/menu/<?php echo $row['foto']; ?>" class="img-preview img-3x4"></td>
                    <td><strong><?php echo strtoupper($row['nama_menu']); ?></strong></td>
                    <td><?php echo $row['kategori']; ?></td>
                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $row['stok']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['id_menu']; ?>" class="btn-action btn-edit">EDIT</a>
                        <a href="?hapus=<?php echo $row['id_menu']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus menu ini?');">DELETE</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // Script Toggle Form
    const btnToggle = document.getElementById('btnToggleForm');
    const boxForm = document.getElementById('boxForm');
    if(btnToggle) {
        btnToggle.addEventListener('click', () => {
            const isHidden = boxForm.style.display === 'none';
            boxForm.style.display = isHidden ? 'block' : 'none';
            btnToggle.innerHTML = isHidden ? '− BATAL / TUTUP' : '+ TAMBAH MENU BARU';
        });
    }

    // Script Cropper
    let cropper;
    const inputFoto = document.getElementById('inputFoto');
    const imageToCrop = document.getElementById('image-to-crop');
    const cropContainerBox = document.getElementById('cropContainerBox');
    const fotoCropped = document.getElementById('foto_cropped');
    const btnSimpan = document.getElementById('btnSimpan');

    inputFoto.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                imageToCrop.src = event.target.result;
                cropContainerBox.style.display = 'block';
                if (cropper) cropper.destroy();
                cropper = new Cropper(imageToCrop, { aspectRatio: 3 / 4, viewMode: 1 });
            };
            reader.readAsDataURL(file);
        }
    });

    btnSimpan.addEventListener('click', function () {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 800 });
            fotoCropped.value = canvas.toDataURL('image/jpeg', 0.9);
        }
        document.getElementById('btnSubmitAsli').click();
    });
</script>
</body>
</html>