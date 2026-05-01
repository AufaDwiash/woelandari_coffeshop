<?php
require_once __DIR__ . '/auth.php';

$current_file = basename($_SERVER['PHP_SELF']); 

// ==========================================
// PROSES CRUD USER MANAGEMENT (Logika Tetap)
// ==========================================
$edit_mode = false;
$u_id = ""; $u_nama = ""; $u_username = ""; $u_role = "";

if (isset($_GET['edit_user'])) {
    $edit_mode = true;
    $u_id = $_GET['edit_user'];
    $q_u = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$u_id'");
    $d_u = mysqli_fetch_assoc($q_u);
    $u_nama = $d_u['nama_lengkap'];
    $u_username = $d_u['username'];
    $u_role = $d_u['role'];
}

if (isset($_POST['simpan_user'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = $_POST['role'];
    $cek_user = mysqli_query($conn, "SELECT username FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.history.back();</script>";
        exit;
    }
    mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$username', '$password', '$nama', '$role')");
    echo "<script>window.location='$current_file';</script>";
    exit;
}

if (isset($_POST['update_user'])) {
    $id_user = $_POST['id_user'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = $_POST['role'];
    $cek_user = mysqli_query($conn, "SELECT username FROM users WHERE username='$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan pengguna lain!'); window.history.back();</script>";
        exit;
    }
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET username='$username', password='$password', nama_lengkap='$nama', role='$role' WHERE id_user='$id_user'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', nama_lengkap='$nama', role='$role' WHERE id_user='$id_user'");
    }
    echo "<script>window.location='$current_file';</script>";
    exit;
}

if (isset($_GET['hapus_user'])) {
    $id_hapus = $_GET['hapus_user'];
    $cek_admin = mysqli_query($conn, "SELECT count(*) as total FROM users");
    $data_admin = mysqli_fetch_assoc($cek_admin);
    if($data_admin['total'] <= 1) {
        echo "<script>alert('Minimal harus ada 1 user di sistem!'); window.location='$current_file';</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Access Control</title>
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
            margin: 0; padding: 0;
            background-color: var(--paper-bg);
            font-family: 'Courier Prime', monospace;
            color: var(--navy-ink);
            display: flex;
        }

        /* --- SIDEBAR STYLE --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--navy-ink);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
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

        /* --- MAIN CONTENT --- */
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
            margin: 0; font-size: 2rem;
        }

        .card-system {
            background: white;
            border: 2px solid var(--navy-ink);
            padding: 30px;
            position: relative;
            box-shadow: 8px 8px 0px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .tape-deco {
            position: absolute;
            width: 80px; height: 30px;
            background: rgba(0,0,0,0.05);
            top: -15px; left: 20px;
            transform: rotate(-2deg);
            border: 1px dashed rgba(0,0,0,0.1);
        }

        /* Form Styles */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.85rem; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px;
            border: 1px solid #ccc;
            font-family: 'Courier Prime', monospace;
        }

        /* Table Styles */
        .aesthetic-table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
        }
        .aesthetic-table th {
            text-align: left; padding: 12px;
            border-bottom: 2px solid var(--navy-ink);
            font-family: 'Special Elite';
        }
        .aesthetic-table td { padding: 12px; border-bottom: 1px solid #eee; }

        .btn-submit {
            font-family: 'Special Elite', cursive;
            background: var(--red-ink);
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            box-shadow: 4px 4px 0px var(--navy-ink);
        }
        .btn-submit:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0px var(--navy-ink); }

        .btn-action {
            text-decoration: none; font-weight: bold; font-size: 0.75rem;
            padding: 4px 8px; border: 1px solid; margin-right: 5px;
        }
        .btn-edit { color: var(--navy-ink); border-color: var(--navy-ink); }
        .btn-delete { color: var(--red-ink); border-color: var(--red-ink); }

        .badge-role {
            padding: 3px 8px; font-size: 10px; font-weight: bold; text-transform: uppercase;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI</div>
     <nav class="nav-list">
        <a href="dashboard.php" class="nav-item "> <span>Dashboard</span></a>
        <a href="menu_crud.php" class="nav-item"><span>Menu</span></a>
        <a href="gallery_crud.php" class="nav-item"> <span>Gallery</span></a>
        <a href="feedback.php" class="nav-item"><span>Feedback</span></a>
        <a href="user_manajemen.php" class="nav-item active"><span>Kelola User</span></a>
    </nav>
    <div style="margin-top: auto; border-top: 1px dashed #555; padding-top: 10px;">
        <a href="../logout.php" class="nav-item" style="color: #ff6b6b;">>> <span>TERMINATE</span></a>
    </div>
</aside>

<main class="main-content">
    <header class="page-header">
        <h1>USER_ACCESS_MANAGEMENT</h1>
        <div style="font-size: 0.8rem; color: var(--red-ink); font-weight: bold;">// SECURITY_LEVEL: PROTOCOL_ALPHA</div>
    </header>

    <div style="margin-bottom: 25px;">
        <?php if (!$edit_mode): ?>
            <button id="btnToggleUser" class="btn-submit">+ REGISTER_NEW_USER</button>
        <?php else: ?>
            <button onclick="window.location='<?php echo $current_file; ?>'" class="btn-submit" style="background: var(--navy-ink);">✕ ABORT_EDIT</button>
        <?php endif; ?>
    </div>

    <div class="card-system" id="boxFormUser" style="<?php echo $edit_mode ? 'display: block;' : 'display: none;'; ?>">
        <div class="tape-deco"></div>
        <h2><?php echo $edit_mode ? "MODIFY_USER_PRIVILEGES" : "USER_REGISTRATION"; ?></h2>
        
        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id_user" value="<?php echo $u_id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>FULL_NAME:</label>
                <input type="text" name="nama_lengkap" required value="<?php echo $u_nama; ?>" placeholder="...">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>USERNAME:</label>
                    <input type="text" name="username" required value="<?php echo $u_username; ?>" placeholder="...">
                </div>
                <div class="form-group">
                    <label>ACCESS_ROLE:</label>
                    <select name="role" required>
                        <option value="admin" <?php echo ($u_role == 'admin') ? 'selected' : ''; ?>>KARYAWAN_STAFF</option>
                        <option value="superadmin" <?php echo ($u_role == 'superadmin') ? 'selected' : ''; ?>>SYSTEM_ADMIN</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ENCRYPTED_PASSWORD <?php echo $edit_mode ? "<span style='color:var(--red-ink);'>[LEAVE BLANK TO KEEP OLD]</span>" : ""; ?>:</label>
                <input type="password" name="password" <?php echo $edit_mode ? "" : "required"; ?>>
            </div>

            <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'simpan_user'; ?>" class="btn-submit">
                <?php echo $edit_mode ? "UPDATE_PROTOCOL" : "EXECUTE_SAVE"; ?>
            </button>
        </form>
    </div>

    <div class="card-system">
        <div class="tape-deco"></div>
        <h2>AUTHORIZED_USER_RECORDS</h2>
        <table class="aesthetic-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAMA_LENGKAP</th>
                    <th>USERNAME</th>
                    <th>PRIVILEGE</th>
                    <th style="text-align: right;">OPERATIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $q_user = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
                while ($r_user = mysqli_fetch_assoc($q_user)):
                ?>
                <tr>
                    <td>[<?php echo $no++; ?>]</td>
                    <td><strong><?php echo strtoupper($r_user['nama_lengkap']); ?></strong></td>
                    <td><code><?php echo $r_user['username']; ?></code></td>
                    <td>
                        <?php if ($r_user['role'] == 'superadmin'): ?>
                            <span class="badge-role" style="background: var(--navy-ink); color: white;">ADMIN</span>
                        <?php else: ?>
                            <span class="badge-role" style="background: #ddd; color: #666;">STAFF</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="?edit_user=<?php echo $r_user['id_user']; ?>" class="btn-action btn-edit">EDIT</a>
                        <a href="?hapus_user=<?php echo $r_user['id_user']; ?>" class="btn-action btn-delete" onclick="return confirm('Terminate access for this user?');">DEL</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    const btnTU = document.getElementById('btnToggleUser');
    const boxU = document.getElementById('boxFormUser');
    if (btnTU) {
        btnTU.addEventListener('click', () => {
            const isHidden = boxU.style.display === 'none';
            boxU.style.display = isHidden ? 'block' : 'none';
            btnTU.innerText = isHidden ? '− CLOSE_FORM' : '+ REGISTER_NEW_USER';
        });
    }
</script>

</body>
</html>
