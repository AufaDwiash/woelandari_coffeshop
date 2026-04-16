<?php
include "../config/koneksi.php";

// Deteksi otomatis nama file saat ini agar terhindar dari error 404
$current_file = basename($_SERVER['PHP_SELF']); 

// ==========================================
// PROSES CRUD USER MANAGEMENT
// ==========================================
$edit_mode = false;
$u_id = "";
$u_nama = "";
$u_username = "";
$u_role = "";

// Cek jika sedang mode EDIT
if (isset($_GET['edit_user'])) {
    $edit_mode = true;
    $u_id = $_GET['edit_user'];
    $q_u = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$u_id'");
    $d_u = mysqli_fetch_assoc($q_u);
    
    $u_nama = $d_u['nama_lengkap'];
    $u_username = $d_u['username'];
    $u_role = $d_u['role'];
}

// PROSES SIMPAN DATA BARU
if (isset($_POST['simpan_user'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); 
    $role = $_POST['role'];

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($conn, "SELECT username FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan! Silakan pilih yang lain.'); window.history.back();</script>";
        exit;
    }

    mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$username', '$password', '$nama', '$role')");
    echo "<script>window.location='$current_file';</script>";
    exit;
}

// PROSES UPDATE DATA
if (isset($_POST['update_user'])) {
    $id_user = $_POST['id_user'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = $_POST['role'];
    
    // Cek apakah username dipakai oleh ID lain
    $cek_user = mysqli_query($conn, "SELECT username FROM users WHERE username='$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan pengguna lain!'); window.history.back();</script>";
        exit;
    }

    // Jika password diisi, update passwordnya. Jika kosong, biarkan password lama.
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        mysqli_query($conn, "UPDATE users SET username='$username', password='$password', nama_lengkap='$nama', role='$role' WHERE id_user='$id_user'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', nama_lengkap='$nama', role='$role' WHERE id_user='$id_user'");
    }

    echo "<script>window.location='$current_file';</script>";
    exit;
}

// PROSES HAPUS DATA
if (isset($_GET['hapus_user'])) {
    $id_hapus = $_GET['hapus_user'];
    
    // Cegah menghapus akun admin/superadmin terakhir
    $cek_admin = mysqli_query($conn, "SELECT count(*) as total FROM users");
    $data_admin = mysqli_fetch_assoc($cek_admin);
    
    if($data_admin['total'] <= 1) {
        echo "<script>alert('Tidak dapat menghapus! Harus ada minimal 1 admin di sistem.'); window.location='$current_file';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id_user='$id_hapus'");
        echo "<script>window.location='$current_file';</script>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - KELOLA AKUN </title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/admin_crud.css">
</head>
<body>

    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-family: 'Special Elite', cursive; color: var(--navy-ink); font-size: 32px; border-bottom: 2px dashed var(--navy-ink); display: inline-block; padding-bottom: 10px;">KELOLA AKUN</h1>
    </div>

    <div style="width: 100%; max-width: 900px; margin: 0 auto 20px auto; text-align: left;">
        <?php if (!$edit_mode): ?>
            <button id="btnToggleUser" class="btn-submit" style="width: auto; background: var(--red-ink); border-color: var(--red-ink); padding: 10px 20px;">+ TAMBAH PENGGUNA BARU</button>
        <?php else: ?>
            <button onclick="window.location='<?php echo $current_file; ?>'" class="btn-submit" style="width: auto; background: var(--red-ink); border-color: var(--red-ink); padding: 10px 20px; cursor: pointer;">✕ BATAL EDIT</button>
        <?php endif; ?>
    </div>

    <div class="form-container" id="boxFormUser" style="<?php echo $edit_mode ? 'display: block;' : 'display: none;'; ?> max-width: 900px; margin: 0 auto 30px auto;">
        <h2><?php echo $edit_mode ? "UPDATE DATA PENGGUNA" : "TAMBAH PENGGUNA BARU"; ?></h2>
        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id_user" value="<?php echo $u_id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>NAMA LENGKAP:</label>
                <input type="text" name="nama_lengkap" required value="<?php echo $u_nama; ?>" placeholder="Masukkan nama lengkap...">
            </div>

            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1;">
                    <label>USERNAME:</label>
                    <input type="text" name="username" required value="<?php echo $u_username; ?>" placeholder="Untuk keperluan login">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>HAK AKSES (ROLE):</label>
                    <select name="role" required>
                        <option value="admin" <?php echo ($u_role == 'admin') ? 'selected' : ''; ?>>Karyawan (Biasa)</option>
                        <option value="superadmin" <?php echo ($u_role == 'superadmin') ? 'selected' : ''; ?>>admin (Penuh)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>PASSWORD <?php echo $edit_mode ? "<span style='font-size:12px; font-weight:normal; color:#d9534f;'>(Kosongkan jika tidak ingin mengubah password)</span>" : ""; ?>:</label>
                <input type="password" name="password" <?php echo $edit_mode ? "" : "required"; ?> placeholder="Masukkan password yang kuat...">
            </div>

            <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'simpan_user'; ?>" class="btn-submit" style="margin-top: 10px;"><?php echo $edit_mode ? "SIMPAN PERUBAHAN" : "SIMPAN PENGGUNA"; ?></button>
        </form>
    </div>

    <div class="table-container" style="max-width: 900px; margin: 0 auto;">
        <div class="tape-table"></div>
        <h2>DAFTAR PENGGUNA SISTEM</h2>
        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAMA LENGKAP</th>
                    <th>USERNAME</th>
                    <th>ROLE</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $q_user = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
                while ($r_user = mysqli_fetch_assoc($q_user)):
                    ?>
                    <tr>
                        <td><strong><?php echo $no++; ?></strong></td>
                        <td class="text-left"><strong><?php echo strtoupper($r_user['nama_lengkap']); ?></strong></td>
                        <td><?php echo $r_user['username']; ?></td>
                        <td>
                            <?php if ($r_user['role'] == 'superadmin'): ?>
                                <span style="background: var(--navy-ink); color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">ADMIN</span>
                            <?php else: ?>
                                <span style="background: #ccc; color: #333; padding: 4px 10px; border-radius: 4px; font-size: 12px;">KARYAWAN</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?edit_user=<?php echo $r_user['id_user']; ?>" class="btn-action btn-edit">EDIT</a>
                            <a href="?hapus_user=<?php echo $r_user['id_user']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus pengguna ini?');">DEL</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Toggle Form
        const btnTU = document.getElementById('btnToggleUser');
        const boxU = document.getElementById('boxFormUser');
        if (btnTU) {
            btnTU.addEventListener('click', () => {
                boxU.style.display = (boxU.style.display === 'none') ? 'block' : 'none';
                btnTU.innerText = (boxU.style.display === 'none') ? '+ TAMBAH PENGGUNA BARU' : '− TUTUP FORM';
            });
        }
    </script>
</body>
</html>