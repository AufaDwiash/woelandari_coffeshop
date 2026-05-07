<?php
ob_start();
session_start();
include "../config/koneksi.php";

// Proteksi halaman admin
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username_logged = $_SESSION['username'];

// Cek dan tambah kolom jika belum ada (untuk kompatibilitas)
$check_column_password_default = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'password_default'");
if (mysqli_num_rows($check_column_password_default) == 0) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN password_default TEXT NULL");
}

$check_column_is_first_login = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'is_first_login'");
if (mysqli_num_rows($check_column_is_first_login) == 0) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN is_first_login TINYINT(1) DEFAULT 1");
}

// --- LOGIKA CRUD ---

// 1. HAPUS USER
if (isset($_GET['hapus_user'])) {
    $id_hapus = (int)$_GET['hapus_user'];
    
    $stmt = $conn->prepare("DELETE FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id_hapus);
    $stmt->execute();
    header("Location: user_manajemen.php");
    exit;
}

// 2. SIMPAN USER BARU
if (isset($_POST['simpan_user'])) {
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $role = $_POST['role'];
    $pass = $_POST['password'];
    $pass_hash = password_hash($pass, PASSWORD_BCRYPT);
    
    // Simpan password default juga (untuk cek nanti)
    $stmt = $conn->prepare("INSERT INTO user (nama_lengkap, username, role, password, password_default, is_first_login) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $nama, $user, $role, $pass_hash, $pass_hash);
    $stmt->execute();
    header("Location: user_manajemen.php");
    exit;
}

// 3. UPDATE USER
if (isset($_POST['update_user'])) {
    $id = $_POST['id_user'];
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $role = $_POST['role'];
    $pass = $_POST['password'];

    if (!empty($pass)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        // Jika admin mengganti password, reset juga password_default dan is_first_login
        $stmt = $conn->prepare("UPDATE user SET nama_lengkap=?, username=?, role=?, password=?, password_default=?, is_first_login=1 WHERE id_user=?");
        $stmt->bind_param("sssssi", $nama, $user, $role, $hash, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama_lengkap=?, username=?, role=? WHERE id_user=?");
        $stmt->bind_param("sssi", $nama, $user, $role, $id);
    }
    $stmt->execute();
    header("Location: user_manajemen.php");
    exit;
}

// --- LOGIKA EDIT ---
$edit_mode = false;
$u_id = ""; 
$u_nama = ""; 
$u_username = ""; 
$u_role = "";

if (isset($_GET['edit_user'])) {
    $edit_mode = true;
    $id_edit = (int)$_GET['edit_user'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $d_u = $stmt->get_result()->fetch_assoc();
    if ($d_u) {
        $u_id = $d_u['id_user'];
        $u_nama = $d_u['nama_lengkap'];
        $u_username = $d_u['username'];
        $u_role = $d_u['role'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kelolaUser-admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/admin_crud.css">
    <style>
        .brutalist-input {
            width: 100%;
            padding: 10px;
            border: 3px solid #000;
            font-family: 'Courier Prime', monospace;
            margin-bottom: 10px;
            outline: none;
            background: #fff;
        }
        .badge {
            background: #000;
            color: #fff;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: bold;
            border-radius: 2px;
        }
        .badge-superadmin {
            background: #8B0000;
            color: #fff;
        }
        .badge-admin {
            background: #1a1a2e;
            color: #fff;
        }
        .badge-karyawan {
            background: #2d6a4f;
            color: #fff;
        }
        .role-select {
            cursor: pointer;
        }
        .info-tooltip {
            font-size: 0.7rem;
            color: #666;
            margin-top: -8px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI ADMIN</div>
    <nav class="nav-list">
        <a href="dashboard.php" class="nav-item"><span>> DASHBOARD</span></a>
        <a href="menu_crud.php" class="nav-item"><span>> KELOLA MENU</span></a>
        <a href="gallery_crud.php" class="nav-item"><span>> KELOLA GALLERY & EVENT</span></a>
        <a href="feedback.php" class="nav-item"><span>> KELOLA FEEDBACK & RATING</span></a>
        <a href="user_manajemen.php" class="nav-item active"><span>> KELOLA USER</span></a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
        </div>
    </nav>
</aside>

<main class="main-wrapper">
    <section class="paper paper-style-1">
        <div class="spec-header">
            <span>OPERATOR: <?php echo htmlspecialchars($username_logged); ?></span>
        </div>

        <h1 class="title-main">USER_MANAGEMENT</h1>

        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id_user" value="<?php echo $u_id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>FULL_NAME / IDENTIFIER:</label>
                <input type="text" name="nama_lengkap" class="brutalist-input" required value="<?php echo htmlspecialchars($u_nama); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>USERNAME / UID:</label>
                    <input type="text" name="username" class="brutalist-input" required value="<?php echo htmlspecialchars($u_username); ?>">
                </div>
                <div class="form-group">
                    <label>ACCESS_LEVEL:</label>
                    <select name="role" class="brutalist-input role-select" required>
                        <option value="admin" <?php echo ($u_role == 'admin') ? 'selected' : ''; ?>>ADMIN</option>
                        <option value="karyawan" <?php echo ($u_role == 'karyawan') ? 'selected' : ''; ?>>KARYAWAN</option>
                    </select>
                    <small class="info-tooltip">
                      
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label>PASSWORD <?php echo $edit_mode ? "(BIARKAN KOSONG JIKA TIDAK DIUBAH)" : ""; ?>:</label>
                <input type="password" name="password" class="brutalist-input" <?php echo $edit_mode ? "" : "required"; ?>>
                <?php if($edit_mode): ?>
                    <small class="info-tooltip">🔒 Isi password baru jika ingin mengganti</small>
                <?php endif; ?>
            </div>

            <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'simpan_user'; ?>" class="btn-action">
                <?php echo $edit_mode ? "⟳ UPDATE_DATA" : "+ REGISTER_USER"; ?>
            </button>
            
            <?php if ($edit_mode): ?>
                <a href="user_manajemen.php" style="display: inline-block; margin-left: 10px; color: #666; text-decoration: underline;">↺ BATAL</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="paper">
        <h2 class="title-main" style="font-size: 1.5rem;">PERSONNEL_LOG</h2>
        <div style="overflow-x: auto;">
            <table class="aesthetic-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>USERNAME</th>
                        <th>NAMA LENGKAP</th>
                        <th>ROLE</th>
                        <th>CREATED_AT</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM user ORDER BY 
                        CASE role 
                            WHEN 'superadmin' THEN 1 
                            WHEN 'admin' THEN 2 
                            WHEN 'karyawan' THEN 3 
                        END, id_user DESC");
                    
                    if (mysqli_num_rows($q) > 0):
                        while ($r = mysqli_fetch_assoc($q)):
                            $role_class = '';
                            $role_display = '';
                            switch($r['role']) {
                                case 'superadmin':
                                    $role_class = 'badge-superadmin';
                                    $role_display = 'SUPERADMIN';
                                    break;
                                case 'admin':
                                    $role_class = 'badge-admin';
                                    $role_display = 'ADMIN';
                                    break;
                                case 'karyawan':
                                    $role_class = 'badge-karyawan';
                                    $role_display = 'KARYAWAN';
                                    break;
                                default:
                                    $role_class = '';
                                    $role_display = strtoupper($r['role']);
                            }
                    ?>
                    <tr>
                        <td><code>#<?php echo $r['id_user']; ?></code></td>
                        <td><strong>@<?php echo htmlspecialchars($r['username']); ?></strong></td>
                        <td><?php echo strtoupper(htmlspecialchars($r['nama_lengkap'])); ?></td>
                        <td><span class="badge <?php echo $role_class; ?>"><?php echo $role_display; ?></span></td>
                        <td><small><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></small></td>
                        <td>
                            <a href="?edit_user=<?php echo $r['id_user']; ?>" style="color:var(--navy); font-weight:bold; margin-right: 10px;">✎ EDIT</a> | 
                            <a href="?hapus_user=<?php echo $r['id_user']; ?>" style="color:var(--red); font-weight:bold; margin-left: 10px;" onclick="return confirm('⚠️ Yakin ingin menghapus user @<?php echo $r['username']; ?>?');">🗑 DROP</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">📭 Belum ada user terdaftar</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      
    </section>
</main>

</body>
</html>
<?php ob_end_flush(); ?>