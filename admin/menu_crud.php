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

// ... Logika simpan, update, hapus ...
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
    <title>menu-admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 8px 8px 0 rgba(0, 43, 91, 0.15);
            --border-thick: 2px solid var(--navy);
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
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            border-bottom: 3px double var(--navy);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--red);
            text-align: center;
        }

        .nav-item {
            display: block;
            padding: 14px 18px;
            color: var(--navy);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--navy);
            color: var(--white);
            box-shadow: 4px 4px 0 var(--red);
        }

        /* --- MAIN CONTENT --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
        }

        .tape {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 35px; 
            background: rgba(234, 67, 53, 0.7);
            border: 1px dashed rgba(255,255,255,0.4);
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 25px;
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        /* --- FORM STYLING --- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--navy);
            background: transparent;
            font-family: 'Courier Prime', monospace;
            font-weight: bold;
            margin-top: 5px;
        }

        label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-submit {
            font-family: 'Special Elite', cursive;
            background: var(--navy);
            color: white;
            border: none;
            padding: 15px 30px;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: var(--red);
            transform: translate(-3px, -3px);
            box-shadow: 5px 5px 0 var(--navy);
        }

        /* --- TABLE STYLING --- */
        .aesthetic-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        }

        .img-3x4 {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border: 2px solid var(--navy);
        }

        .btn-action {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.75rem;
            padding: 6px 10px;
            border: 2px solid var(--navy);
            margin-right: 5px;
            display: inline-block;
        }

        .btn-edit { color: var(--navy); }
        .btn-edit:hover { background: var(--navy); color: white; }
        .btn-delete { color: var(--red); border-color: var(--red); }
        .btn-delete:hover { background: var(--red); color: white; }

        .crop-container {
            display: none; width: 100%; max-width: 400px; 
            margin: 15px 0; border: 2px solid var(--navy);
        }

        .blink { animation: pulse 1.5s infinite; color: var(--red); }
        @keyframes pulse { 50% { opacity: 0.3; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI STAFF</div>
    <nav class="nav-list">
  <a href="dashboard.php" class="nav-item"><span>> DASHBOARD</span></a>
<a href="menu_crud.php" class="nav-item active"><span>> KELOLA MENU</span></a> <!-- AKTIF DI SINI -->
<a href="gallery_crud.php" class="nav-item"><span>> KELOLA GALLERY & EVENT</span></a>
        <a href="feedback.php" class="nav-item"><span>> KELOLA FEEDBACK & RATING</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>> KELOLA USER</span></a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
        </div>
    </nav>
</aside>

<main class="main-wrapper">
    <header>
        <h1 class="title-main">DATA PENYIMPANAN MENU</h1>
        <p style="font-weight: bold; font-size: 0.8rem; margin-top: -15px; margin-left: 20px;">
            DATA: <span class="blink">ARSIP MENU</span>
        </p>
    </header>

    <!-- FORM SECTION -->
    <section class="paper" id="boxForm" style="<?php echo $edit_mode ? 'display: block;' : 'display: none;'; ?>">
        <div class="tape"></div>
        <h2 style="font-family: 'Special Elite'; margin-bottom: 20px;">
            <?php echo $edit_mode ? "[ MODE: EDIT_ENTRY ]" : "Tambahkan Menu Baru"; ?>
        </h2>

        <form id="formMenu" method="POST" enctype="multipart/form-data">
            <?php if($edit_mode): ?>
                <input type="hidden" name="id_menu" value="<?php echo $edit_id; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $edit_foto; ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" required value="<?php echo $edit_nama; ?>">
                </div>
                <div>
                    <label>Kategori Menu</label>
                    <select name="kategori" required>
                        <option value="Coffee" <?php echo ($edit_kategori == 'Coffee') ? 'selected' : ''; ?>>Coffee</option>
                        <option value="Non-Coffee" <?php echo ($edit_kategori == 'Non-Coffee') ? 'selected' : ''; ?>>Non-Coffee</option>
                        <option value="Snack" <?php echo ($edit_kategori == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                        <option value="Main Course" <?php echo ($edit_kategori == 'Main Course') ? 'selected' : ''; ?>>Main Course</option>
                    </select>
                </div>
                <div>
                    <label>HARGA (Rp)</label>
                    <input type="number" name="harga" required value="<?php echo $edit_harga; ?>">
                </div>
                <!-- <div>
                    <label>STOCK_LEVEL</label>
                    <input type="number" name="stok" required value="<?php echo $edit_stok; ?>">
                </div> -->
            </div>

            <div style="margin-top: 20px;">
                <label>Deskripsi Menu</label>
                <textarea name="deskripsi" rows="3" required><?php echo $edit_deskripsi; ?></textarea>
            </div>

            <div style="margin-top: 20px;">
                <label>UPLOAD FOTO (3:4 Ratio Required)</label>
                <input type="file" id="inputFoto" accept="image/*">
            </div>

            <div class="crop-container" id="cropContainerBox">
                <img id="image-to-crop" src="" style="max-width: 100%;">
            </div>
            <input type="hidden" name="foto_cropped" id="foto_cropped">

            <div style="margin-top: 30px;">
                <button type="button" class="btn-submit" id="btnSimpan">
                    <?php echo $edit_mode ? "EXECUTE_UPDATE" : "Tambahkan"; ?>
                </button>
                <button type="submit" name="<?php echo $edit_mode ? 'update' : 'simpan'; ?>" id="btnSubmitAsli" style="display:none;"></button>
                <?php if($edit_mode): ?>
                    <a href="menu_crud.php" style="margin-left: 15px; color: var(--red); font-weight: bold; text-decoration: none;">[ CANCEL_PROCESS ]</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- TABLE SECTION -->
    <section class="paper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-family: 'Special Elite';">LIST MENU YG DITAMPILKAN</h2>
            <?php if(!$edit_mode): ?>
                <button id="btnToggleForm" class="btn-action" style="padding: 10px 20px; background: var(--navy); color: white;">TAMBAHKAN MENU</button>
            <?php endif; ?>
        </div>

        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>PREVIEW</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>STOCK</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC");
                while ($row = mysqli_fetch_assoc($query)) :
                ?>
                <tr>
                    <td><img src="../assets/images/menu/<?php echo $row['foto']; ?>" class="img-3x4"></td>
                    <td><strong style="letter-spacing: 1px;"><?php echo strtoupper($row['nama_menu']); ?></strong></td>
                    <td><span style="font-size: 0.75rem; background: #eee; padding: 3px 8px; border: 1px solid var(--navy);"><?php echo $row['kategori']; ?></span></td>
                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $row['stok']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['id_menu']; ?>" class="btn-action btn-edit">EDIT</a>
                        <a href="?hapus=<?php echo $row['id_menu']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus record ini?');">DELETE</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // Toggle Form
    const btnToggle = document.getElementById('btnToggleForm');
    const boxForm = document.getElementById('boxForm');
    if(btnToggle) {
        btnToggle.addEventListener('click', () => {
            const isHidden = boxForm.style.display === 'none';
            boxForm.style.display = isHidden ? 'block' : 'none';
            btnToggle.innerHTML = isHidden ? '- Tutup Form' : '+ ADD_NEW_RECORD';
        });
    }

    // Cropper Logic
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