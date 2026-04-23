<?php 
include "../config/koneksi.php"; 

// A. PROSES TAMBAH DATA (UPLOAD)
if(isset($_POST['add_human'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = $_POST['display_order'];
    $status = 'active';

    // Proses Gambar
    $img_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
    
    $new_img_name = 'human_' . time() . '.' . $ext;
    
    // PERBAIKAN: Menyesuaikan dengan nama foldermu yaitu "community"
    $upload_path = '../assets/images/community/' . $new_img_name;

    if(move_uploaded_file($tmp_name, $upload_path)) {
        mysqli_query($conn, "INSERT INTO human_archive (name, role, quote, image, display_order, status) 
                                VALUES ('$name', '$role', '$quote', '$new_img_name', '$order', '$status')");
        echo "<script>alert('Data berhasil ditambahkan!'); window.location='community_crud.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupload gambar! Pastikan folder tersedia.'); window.location='community_crud.php';</script>";
    }
}

// B. PROSES UPDATE URUTAN TAMPIL
if(isset($_POST['update_order'])) {
    if(!empty($_POST['order'])) {
        foreach($_POST['order'] as $id => $order_val) {
            $id = intval($id);
            $order_val = intval($order_val);
            mysqli_query($conn, "UPDATE human_archive SET display_order='$order_val' WHERE id='$id'");
        }
        echo "<script>alert('Urutan berhasil diperbarui!'); window.location='community_crud.php';</script>";
    }
}

// C. PROSES HIDE/SHOW (Ubah Status)
if(isset($_GET['toggle']) && isset($_GET['current'])) {
    $id = intval($_GET['toggle']);
    $current_status = $_GET['current'];
    
    $new_status = ($current_status == 'active') ? 'hidden' : 'active';
    mysqli_query($conn, "UPDATE human_archive SET status='$new_status' WHERE id='$id'");
    
    echo "<script>window.location='community_crud.php';</script>";
}

// D. PROSES HAPUS PERMANEN
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Cari dan hapus file fisik foto
    $cari_foto = mysqli_query($conn, "SELECT image FROM human_archive WHERE id='$id'");
    if($data_foto = mysqli_fetch_assoc($cari_foto)) {
        // PERBAIKAN: Menyesuaikan folder untuk proses hapus file
        if(file_exists('../assets/images/community/' . $data_foto['image'])) {
            unlink('../assets/images/community/' . $data_foto['image']);
        }
    }
    
    // Hapus data dari database
    mysqli_query($conn, "DELETE FROM human_archive WHERE id='$id'");
    echo "<script>alert('Data dihapus permanen!'); window.location='community_crud.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel - Human Archive</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background: #EFEBE0; color: #2A1B18; }
        .admin-header { margin-bottom: 30px; border-bottom: 2px solid #2A1B18; padding-bottom: 10px; }
        .admin-box { background: #fff; padding: 25px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid rgba(42, 27, 24, 0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; }
        input[type="text"], input[type="number"], select, textarea, input[type="file"] { 
            width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-family: inherit;
        }
        button { padding: 12px 20px; background: #2A1B18; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        button:hover { background: #D35400; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
        th { background: #2A1B18; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; color: white; }
        .status-active { background: #27AE60; }
        .status-hidden { background: #7f8c8d; }
        
        .action-links a { text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; color: white; margin-right: 5px; display: inline-block; }
        .btn-toggle { background: #f39c12; }
        .btn-del { background: #e74c3c; }
        .btn-toggle:hover, .btn-del:hover { opacity: 0.8; }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>// SYSTEM_PANEL : HUMAN ARCHIVE</h1>
        <p>Kelola data anggota, peran, dan urutan tampil pada Slider Homepage.</p>
    </div>

    <div class="admin-box">
        <h2>+ Tambah Entri Baru</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Nama Subjek</label>
                    <input type="text" name="name" required placeholder="Cth: Satria">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Peran (Role)</label>
                    <input type="text" name="role" required placeholder="Cth: HEAD ROASTER">
                </div>
            </div>

            <div class="form-group">
                <label>Kutipan / Quote Pendek (Opsional)</label>
                <textarea name="quote" rows="2" placeholder="Cth: Merawat mesin, merawat rasa."></textarea>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Urutan Tampil (Paling kecil tampil duluan)</label>
                    <input type="number" name="display_order" value="1" required>
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Foto (Wajib PNG Transparan / Cut-Out)</label>
                    <input type="file" name="image" accept=".png, .jpg, .jpeg" required>
                </div>
            </div>

            <button type="submit" name="add_human">+ Simpan & Unggah</button>
        </form>
    </div>

    <div class="admin-box">
        <h2>Daftar Arsip & Pengaturan Urutan</h2>
        
        <form action="" method="POST">
            <table>
                <tr>
                    <th width="80">Preview</th>
                    <th>Nama & Peran</th>
                    <th width="120">Urutan (Sort)</th>
                    <th width="100">Status</th>
                    <th width="200">Aksi</th>
                </tr>
                
                <?php 
                $q = mysqli_query($conn, "SELECT * FROM human_archive ORDER BY display_order ASC, id DESC");
                if(mysqli_num_rows($q) > 0):
                    while($d = mysqli_fetch_array($q)):
                        $status_class = ($d['status'] == 'active') ? 'status-active' : 'status-hidden';
                ?>
                <tr>
                    <td>
                        <img src="../assets/images/community/<?php echo $d['image']; ?>" height="70" style="object-fit: contain; background: #eee; border-radius: 4px;">
                    </td>
                    <td>
                        <strong style="font-size: 1.1rem;"><?php echo $d['name']; ?></strong><br>
                        <span style="color: #D35400; font-family: monospace; font-weight: bold;">// <?php echo $d['role']; ?></span>
                    </td>
                    <td>
                        <input type="number" name="order[<?php echo $d['id']; ?>]" value="<?php echo $d['display_order']; ?>" style="width: 80px; text-align: center;">
                    </td>
                    <td>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo strtoupper($d['status']); ?></span>
                    </td>
                    <td class="action-links">
                        <a href="?toggle=<?php echo $d['id']; ?>&current=<?php echo $d['status']; ?>" class="btn-toggle">
                            <?php echo ($d['status'] == 'active') ? 'Hide' : 'Show'; ?>
                        </a>
                        <a href="?delete=<?php echo $d['id']; ?>" class="btn-del" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">Delete</a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr><td colspan="5" style="text-align: center; color: #777;">Belum ada data arsip.</td></tr>
                <?php endif; ?>
            </table>
            
            <div style="margin-top: 15px;">
                <button type="submit" name="update_order" style="background: #27AE60;">Simpan Perubahan Urutan</button>
            </div>
        </form>
    </div>

</body>
</html>