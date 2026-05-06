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

// --- LOGIKA CRUD ---

// 1. HAPUS USER
if (isset($_GET['hapus_user'])) {
    $id_hapus = (int)$_GET['hapus_user'];
     
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
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO user (nama_lengkap, username, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $user, $role, $pass);
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
        $stmt = $conn->prepare("UPDATE user SET nama_lengkap=?, username=?, role=?, password=? WHERE id_user=?");
        $stmt->bind_param("ssssi", $nama, $user, $role, $hash, $id);
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
$u_id = ""; $u_nama = ""; $u_username = ""; $u_role = "";

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
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI STAFF</div>
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
            <span>OPERATOR: <?php echo $username_logged; ?></span>
        </div>

        <h1 class="title-main">USER_MANAGEMENT</h1>

        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id_user" value="<?php echo $u_id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>FULL_NAME / IDENTIFIER:</label>
                <input type="text" name="nama_lengkap" class="brutalist-input" required value="<?php echo $u_nama; ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>USERNAME / UID:</label>
                    <input type="text" name="username" class="brutalist-input" required value="<?php echo $u_username; ?>">
                </div>
                <div class="form-group">
                    <label>ACCESS_LEVEL:</label>
                    <select name="role" class="brutalist-input" required>
                        <option value="admin" selected>ADMIN</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>PASSWORD <?php echo $edit_mode ? "(BLANK_TO_KEEP)" : ""; ?>:</label>
                <input type="password" name="password" class="brutalist-input" <?php echo $edit_mode ? "" : "required"; ?>>
            </div>

            <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'simpan_user'; ?>" class="btn-action">
                <?php echo $edit_mode ? "UPDATE_DATA" : "REGISTER_USER"; ?>
            </button>
        </form>
    </section>

    <section class="paper">
        <h2 class="title-main" style="font-size: 1.5rem;">PERSONNEL_LOG</h2>
        <div style="overflow-x: auto;">
            <table class="aesthetic-table">
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>NAME</th>
                        <th>ROLE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM user ORDER BY id_user DESC");
                    while ($r = mysqli_fetch_assoc($q)):
                    ?>
                    <tr>
                        <td><code>@<?php echo $r['username']; ?></code></td>
                        <td><?php echo strtoupper($r['nama_lengkap']); ?></td>
                        <td><span class="badge"><?php echo strtoupper($r['role']); ?></span></td>
                        <td>
                            <a href="?edit_user=<?php echo $r['id_user']; ?>" style="color:var(--navy); font-weight:bold;">EDIT</a> | 
                            <a href="?hapus_user=<?php echo $r['id_user']; ?>" style="color:var(--red); font-weight:bold;" onclick="return confirm('DELETE?');">DROP</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

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
    }
</style>

</body>
</html>
<?php ob_end_flush(); ?>