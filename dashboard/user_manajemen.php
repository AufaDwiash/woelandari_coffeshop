<?php
// dashboard/user_manajemen.php - Hanya untuk admin/superadmin
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}
$role = $_SESSION['role'];
if ($role != 'admin' && $role != 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

// Cek dan tambah kolom jika belum ada (untuk kompatibilitas)
$check = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'password_default'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN password_default TEXT NULL");
}
$check2 = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'is_first_login'");
if (mysqli_num_rows($check2) == 0) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN is_first_login TINYINT(1) DEFAULT 1");
}

// Konfigurasi Search & Paginasi
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// HAPUS USER
if (isset($_GET['hapus_user'])) {
    $id = (int)$_GET['hapus_user'];
    $stmt = $conn->prepare("DELETE FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: user_manajemen.php?msg=delete");
        exit;
    }
    $stmt->close();
}

// TAMBAH USER
if (isset($_POST['simpan_user'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $role_user = mysqli_real_escape_string($conn, $_POST['role']);
    $pass = $_POST['password'];
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO user (nama_lengkap, username, role, password, password_default, is_first_login) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $nama, $user, $role_user, $hash, $hash);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: user_manajemen.php?msg=add");
        exit;
    }
    $stmt->close();
    header("Location: user_manajemen.php?msg=error");
    exit;
}

// EDIT USER (ambil data)
$edit_mode = false;
$edit_id = $edit_nama = $edit_username = $edit_role = "";
if (isset($_GET['edit_user'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit_user'];
    $q = mysqli_query($conn, "SELECT * FROM user WHERE id_user = $id");
    if ($q && mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $edit_id = $d['id_user'];
        $edit_nama = $d['nama_lengkap'];
        $edit_username = $d['username'];
        $edit_role = $d['role'];
    }
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = (int)$_POST['id_user'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $role_user = mysqli_real_escape_string($conn, $_POST['role']);
    $pass = $_POST['password'];

    if (!empty($pass)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE user SET nama_lengkap=?, username=?, role=?, password=?, password_default=?, is_first_login=1 WHERE id_user=?");
        $stmt->bind_param("sssssi", $nama, $user, $role_user, $hash, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama_lengkap=?, username=?, role=? WHERE id_user=?");
        $stmt->bind_param("sssi", $nama, $user, $role_user, $id);
    }
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: user_manajemen.php?msg=update");
        exit;
    }
    $stmt->close();
    header("Location: user_manajemen.php?msg=error");
    exit;
}

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'add') $msg = "User berhasil ditambahkan ke arsip!";
    elseif ($_GET['msg'] == 'update') $msg = "Data User berhasil diperbarui!";
    elseif ($_GET['msg'] == 'delete') $msg = "User berhasil dihapus!";
    elseif ($_GET['msg'] == 'error') $msg = "Terjadi kesalahan sistem, coba lagi.";
}

// Query untuk list user
$safe_search = mysqli_real_escape_string($conn, $search);
$whereClause = $search ? "WHERE nama_lengkap LIKE '%$safe_search%' OR username LIKE '%$safe_search%'" : "";

$countQuery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM user $whereClause"));
$totalPages = ceil($countQuery['total'] / $limit);

$query_users = mysqli_query($conn, "SELECT * FROM user $whereClause ORDER BY 
                CASE role 
                    WHEN 'superadmin' THEN 1 
                    WHEN 'admin' THEN 2 
                    WHEN 'karyawan' THEN 3 
                END, id_user DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola User - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --green: #2d6a4f;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 12px 12px 0 rgba(0, 43, 91, 0.2);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0) rotate(-0.2deg); }
        }
        @keyframes floatTape {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-2px); }
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: var(--gap-section);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: var(--gap-section);
            transition: all 0.3s ease;
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
            width: 100%;
            opacity: 0; 
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .tape {
            position: absolute; top: -16px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 32px;
            background: rgba(234, 67, 53, 0.9);
            border: 1px dashed rgba(255,255,255,0.5);
            z-index: 10;
            box-shadow: 2px 3px 5px rgba(0,0,0,0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 25px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 25px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        .alert-msg { background: #fff9c4; border: 2px dashed #e0d68c; padding: 10px 15px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid var(--green); color: var(--navy); }

        /* SEARCH & BUTTONS */
        .search-area {
            display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; align-items: center;
            background: rgba(0, 43, 91, 0.03); padding: 15px; border: 2px solid var(--navy);
        }
        .search-wrapper { flex: 1; position: relative; min-width: 200px; height: 46px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--navy); }
        .search-input {
            width: 100%; height: 100%; padding: 10px 10px 10px 40px;
            border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime', monospace; font-weight: bold; font-size: 0.9rem; outline: none;
        }

        .btn {
            font-family: 'Special Elite', cursive; font-size: 0.9rem; font-weight: bold;
            padding: 11px 20px; border: 2px solid var(--navy); cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; justify-content: center;
            transition: all 0.1s ease; text-decoration: none; height: 46px;
        }
        .btn-primary { background: var(--navy); color: var(--white); box-shadow: 4px 4px 0 var(--red); }
        .btn-primary:hover { background: var(--white); color: var(--navy); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--red); }
        
        .btn-secondary { background: var(--white); color: var(--navy); box-shadow: 4px 4px 0 var(--navy); }
        .btn-secondary:hover { background: #e0e0e0; transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }
        
        .btn-danger { background: var(--white); color: var(--red); border-color: var(--red); box-shadow: 4px 4px 0 var(--red); }
        .btn-danger:hover { background: var(--red); color: var(--white); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }
        
        .btn-sm { padding: 0 12px; font-size: 0.75rem; box-shadow: 3px 3px 0 rgba(0,0,0,0.15); height: 32px; }

        /* TABLE */
        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white; margin-bottom: 20px;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }
        
        .data-table { width: 100%; border-collapse: collapse; min-width: 750px; table-layout: fixed; }
        .data-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 70px; text-align: center; } /* ID */
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 150px; font-weight: bold; } /* Username */
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: auto; } /* Nama Lengkap */
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: 150px; text-align: center; } /* Role */
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 140px; text-align: center; font-size: 0.8rem; } /* Tanggal */
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 160px; text-align: center; } /* Aksi */

        .data-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .data-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }
        
        .action-buttons { display: inline-flex; gap: 8px; justify-content: center; width: 100%; }

        .badge { padding: 4px 10px; border-radius: 2px; font-size: 0.75rem; font-weight: bold; display: inline-block; border: 1px solid currentColor;}
        .badge-superadmin { background: rgba(139, 0, 0, 0.1); color: #8B0000; }
        .badge-admin { background: rgba(0, 43, 91, 0.1); color: var(--navy); }
        .badge-karyawan { background: rgba(45, 106, 79, 0.1); color: var(--green); }

        /* PAGINASI */
        .pagination-area {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 25px; padding-top: 15px; border-top: 2px dashed var(--navy); font-weight: bold;
        }

        /* MODAL */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.65); backdrop-filter: blur(4px); z-index: 2000;
            justify-content: center; align-items: center; padding: 15px;
        }
        .modal-content {
            background: var(--white); border: 4px solid var(--navy);
            width: 100%; max-width: 600px; max-height: 92vh; 
            display: flex; flex-direction: column;
            box-shadow: 14px 14px 0 var(--red); position: relative;
        }
        .modal-header-area { padding: 25px 25px 10px 25px; flex-shrink: 0; }
        .modal-body-scroll { padding: 10px 25px 25px 25px; overflow-y: auto; flex: 1; }
        .modal-body-scroll::-webkit-scrollbar { width: 6px; }
        .modal-body-scroll::-webkit-scrollbar-thumb { background: var(--navy); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: bold; font-size: 0.85rem; margin-bottom: 6px; color: var(--navy); text-transform: uppercase; }
        .form-input, .form-select {
            width: 100%; padding: 10px; border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime'; outline: none; box-shadow: inset 2px 2px 0 rgba(0,0,0,0.03);
        }
        .form-input:focus, .form-select:focus { border-color: var(--red); }

        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,43,91,0.5); backdrop-filter: blur(2px); z-index: 900; opacity: 0; transition: opacity 0.3s; }
        .overlay.active { display: block; opacity: 1; }
        .mobile-header { display: none; }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; width: 100%; padding: 15px; margin-top: 70px; gap: 25px;}
            .mobile-header {
                display: flex; position: fixed; top: 0; left: 0; right: 0; height: 65px; z-index: 800;
                background: rgba(248, 249, 250, 0.9); backdrop-filter: blur(8px);
                border-bottom: 3px solid var(--navy); padding: 0 20px; align-items: center; justify-content: space-between;
            }
            .paper { padding: 25px 15px; }
            .title-main { font-size: 1.6rem; }
            .tape { width: 110px; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .search-area { flex-direction: column; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .btn { width: 100%; }
            .pagination-area { flex-direction: column; gap: 15px; text-align: center; }
            .pagination-area .btn { width: auto; }
        }
    </style>
</head>
<body>

<div class="overlay" id="sidebarOverlay"></div>

<?php include "../components/sidebar.php"; ?>

<main class="main-wrapper">
    <div class="mobile-header">
        <div class="logo-mobile" style="font-family:'Special Elite'; color:var(--navy); font-size: 1.2rem;">
            <i class="fas fa-users-cog" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header">
            <span><i class="fas fa-shield-alt"></i> KELOLA USER</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>
        
        <h1 class="title-main">USER MANAGEMENT</h1>

        <?php if ($msg): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg ?></div>
        <?php endif; ?>

        <div class="search-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau username..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button class="btn btn-primary" id="searchBtn">CARI USER</button>
            <?php if ($search): ?>
                <a href="user_manajemen.php" class="btn btn-secondary">RESET</a>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="openModal()" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);">
                <i class="fas fa-user-plus"></i> ADD USER
            </button>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-user">USERNAME</th>
                        <th class="col-nama">NAMA LENGKAP</th>
                        <th class="col-role">SEBAGAI</th>
                        <th class="col-date">DIBUAT PADA</th>
                        <th class="col-action">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query_users) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_users)):
                            $role_class = '';
                            $role_display = '';
                            switch ($row['role']) {
                                case 'superadmin': $role_class = 'badge-superadmin'; $role_display = 'SUPERADMIN'; break;
                                case 'admin': $role_class = 'badge-admin'; $role_display = 'ADMINISTRATOR'; break;
                                case 'karyawan': $role_class = 'badge-karyawan'; $role_display = 'STAFF/KARYAWAN'; break;
                                default: $role_class = 'badge-admin'; $role_display = strtoupper($row['role']);
                            }
                        ?>
                            <tr>
                                <td style="text-align: center; color: var(--red); font-weight:bold;">#<?= $row['id_user'] ?></td>
                                <td style="color: var(--navy);">@<?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge <?= $role_class ?>"><?= $role_display ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <i class="far fa-clock"></i> <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit_user=<?= $row['id_user'] ?><?= $search ? '&search='.urlencode($search) : '' ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">EDIT</a>
                                        
                                        <?php if($row['username'] !== $_SESSION['username']): ?>
                                            <a href="?hapus_user=<?= $row['id_user'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('APAKAH ANDA YAKIN MENGHAPUS USER INI DARI SISTEM?')">DEL</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:40px; font-weight:bold; color:var(--red);">[ DATA USER TIDAK DITEMUKAN ]</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="pagination-area">
            <button class="btn btn-secondary" <?= ($page <= 1) ? 'disabled' : '' ?> onclick="goToPage(<?= $page - 1 ?>)">← PREV</button>
            <span style="font-family:'Special Elite'; font-size: 1.1rem;">HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
            <button class="btn btn-secondary" <?= ($page >= $totalPages) ? 'disabled' : '' ?> onclick="goToPage(<?= $page + 1 ?>)">NEXT →</button>
        </div>
        <?php endif; ?>

    </section>
</main>

<div class="modal" id="modalUser">
    <div class="modal-content">
        <div class="tape" style="top: -16px; width: 100px; height: 25px;"></div>
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px; border-bottom: 2px dashed var(--navy);">
                <span><?= $edit_mode ? 'UPDATE DATA USER' : 'REGISTRASI USER BARU' ?></span>
            </div>
        </div>
        
        <div class="modal-body-scroll">
            <form action="" method="POST">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id_user" value="<?= $edit_id ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">NAMA LENGKAP</label>
                    <input type="text" name="nama_lengkap" class="form-input" value="<?= htmlspecialchars($edit_nama) ?>" required>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">USERNAME (TANPA SPASI)</label>
                        <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($edit_username) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">OTORITAS AKSES (ROLE)</label>
                        <select name="role" class="form-select" required>
                            <option value="karyawan" <?= $edit_role == 'karyawan' ? 'selected' : '' ?>>STAFF / KARYAWAN</option>
                            <option value="admin" <?= $edit_role == 'admin' ? 'selected' : '' ?>>ADMINISTRATOR</option>
                            <?php if ($role == 'superadmin'): ?>
                                <option value="superadmin" <?= $edit_role == 'superadmin' ? 'selected' : '' ?>>SUPERADMIN</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">PASSWORD <?= $edit_mode ? "(KOSONGKAN JIKA TIDAK DIUBAH)" : "" ?></label>
                    <input type="password" name="password" class="form-input" <?= $edit_mode ? '' : 'required' ?>>
                    <?php if ($edit_mode): ?>
                        <p style="font-size:0.75rem; font-weight:bold; color:var(--red); margin-top:5px;">* Hanya isi jika ingin mereset password pengguna.</p>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; padding-bottom: 5px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">BATAL</button>
                    <button type="submit" name="<?= $edit_mode ? 'update_user' : 'simpan_user' ?>" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Sidebar Logic
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(btn) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }
    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Modal Logic
    function openModal() { document.getElementById('modalUser').style.display = 'flex'; }
    function closeModal() { document.getElementById('modalUser').style.display = 'none'; window.location.href = 'user_manajemen.php'; }
    <?php if ($edit_mode) echo "window.addEventListener('DOMContentLoaded', openModal);"; ?>

    // Search Logic
    document.getElementById('searchBtn')?.addEventListener('click', () => {
        let s = document.getElementById('searchInput').value;
        window.location.href = `user_manajemen.php?search=${encodeURIComponent(s)}`;
    });
    document.getElementById('searchInput')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') document.getElementById('searchBtn').click();
    });

    // Pagination Logic
    function goToPage(page) {
        let s = document.getElementById('searchInput').value;
        window.location.href = `user_manajemen.php?page=${page}${s ? '&search='+encodeURIComponent(s) : ''}`;
    }
</script>
</body>
</html>