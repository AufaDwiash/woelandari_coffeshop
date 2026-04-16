<?php
include "../config/koneksi.php";

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
    <link rel="stylesheet" href="../assets/css/admin/admin_crud.css">
</head>
<body>

<div style="width: 100%; max-width: 1000px; text-align: left; margin-bottom: 20px;">
    <?php if(!$edit_mode): ?>
        <button id="btnToggleForm" class="btn-submit" style="width: auto; background: var(--red-ink); border-color: var(--red-ink); padding: 12px 25px;">+ TAMBAH MENU BARU</button>
    <?php else: ?>
        <h3 style="font-family: 'Special Elite', cursive; color: var(--red-ink);">* MODE EDIT AKTIF</h3>
    <?php endif; ?>
</div>

<div class="form-container" id="boxForm" style="<?php echo $edit_mode ? 'display: block;' : 'display: none;'; ?>">
    <h2><?php echo $edit_mode ? "UPDATE DATA MENU" : "INPUT MENU BARU"; ?></h2>
    <div class="subtitle">DATABASE INVENTORY - RATIO 3:4</div>
    
    <form id="formMenu" method="POST" enctype="multipart/form-data">
        <?php if($edit_mode): ?>
            <input type="hidden" name="id_menu" value="<?php echo $edit_id; ?>">
            <input type="hidden" name="foto_lama" value="<?php echo $edit_foto; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>NAMA MENU:</label>
            <input type="text" name="nama_menu" required placeholder="Contoh: Kopi Susu" value="<?php echo $edit_nama; ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>KATEGORI:</label>
                <select name="kategori" required>
                    <option value="Coffee" <?php echo ($edit_kategori == 'Coffee') ? 'selected' : ''; ?>>Coffee</option>
                    <option value="Non-Coffee" <?php echo ($edit_kategori == 'Non-Coffee') ? 'selected' : ''; ?>>Non-Coffee</option>
                    <option value="Snack" <?php echo ($edit_kategori == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                    <option value="Main Course" <?php echo ($edit_kategori == 'Main Course') ? 'selected' : ''; ?>>Main Course</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>STOK:</label>
                <input type="number" name="stok" required placeholder="0" value="<?php echo $edit_stok; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>HARGA (Rp):</label>
            <input type="number" name="harga" required placeholder="Contoh: 20000" value="<?php echo $edit_harga; ?>">
        </div>

        <div class="form-group">
            <label>DESKRIPSI:</label>
            <textarea name="deskripsi" rows="3" required><?php echo $edit_deskripsi; ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="foto-label">UNGGAH FOTO <?php echo $edit_mode ? "(Opsional)" : "(Otomatis Crop 3:4)"; ?>:</label>
            <input type="file" id="inputFoto" accept="image/*" <?php echo $edit_mode ? "" : "required"; ?>>
        </div>

        <div class="crop-container" id="cropContainerBox">
            <img id="image-to-crop" src="">
        </div>

        <input type="hidden" name="foto_cropped" id="foto_cropped">
        
        <?php if($edit_mode): ?>
            <button type="button" class="btn-submit" id="btnSimpan" style="background-color: var(--navy-ink);">UPDATE MENU</button>
            <button type="submit" name="update" id="btnSubmitAsli" style="display:none;"></button>
            <a href="menu_crud.php" class="btn-cancel">BATAL EDIT</a>
        <?php else: ?>
            <button type="button" class="btn-submit" id="btnSimpan">SIMPAN MENU</button>
            <button type="submit" name="simpan" id="btnSubmitAsli" style="display:none;"></button>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <div class="tape-table"></div>
    <h2>MENU RECORDS</h2>
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
                <td>
                    <img src="../assets/images/menu/<?php echo $row['foto']; ?>" class="img-preview img-3x4" alt="Foto">
                </td>
                <td class="text-left"><strong><?php echo strtoupper($row['nama_menu']); ?></strong></td>
                <td><?php echo $row['kategori']; ?></td>
                <td class="harga-text">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // SCRIPT BUKA-TUTUP FORM (TOGGLE)
    const btnToggle = document.getElementById('btnToggleForm');
    const boxForm = document.getElementById('boxForm');

    if(btnToggle) {
        btnToggle.addEventListener('click', function() {
            if (boxForm.style.display === 'none') {
                boxForm.style.display = 'block';
                btnToggle.innerHTML = '− BATAL / TUTUP FORM';
                btnToggle.style.background = 'var(--navy-ink)';
                btnToggle.style.borderColor = 'var(--navy-ink)';
            } else {
                boxForm.style.display = 'none';
                btnToggle.innerHTML = '+ TAMBAH MENU BARU';
                btnToggle.style.background = 'var(--red-ink)';
                btnToggle.style.borderColor = 'var(--red-ink)';
            }
        });
    }

    // SCRIPT CROPPER
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
                cropContainerBox.style.display = 'flex';
                if (cropper) { cropper.destroy(); }
                // INILAH KUNCI RASIO 3:4 (Portrait)
                cropper = new Cropper(imageToCrop, { aspectRatio: 3 / 4, viewMode: 1 });
            };
            reader.readAsDataURL(file);
        }
    });

    btnSimpan.addEventListener('click', function () {
        <?php if(!$edit_mode): ?>
            if (!cropper) { alert("Pilih foto terlebih dahulu!"); return; }
        <?php else: ?>
            if (!cropper) {
                document.getElementById('btnSubmitAsli').click();
                return;
            }
        <?php endif; ?>

        if (cropper) {
            // Pemotongan Output Disetel ke 600x800 pixel (Sesuai rasio 3:4)
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 800 });
            fotoCropped.value = canvas.toDataURL('image/jpeg', 0.9);
        }
        document.getElementById('btnSubmitAsli').click();
    });
</script>

</body>
</html>