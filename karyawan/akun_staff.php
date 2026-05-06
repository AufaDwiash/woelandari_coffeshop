<?php
session_start();
include "../config/koneksi.php";

// Proteksi halaman - hanya karyawan yang bisa akses
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Jika yang login adalah admin/superadmin, redirect ke admin
if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin') {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Pastikan role adalah karyawan
if ($_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$id_user = $_SESSION['id_user'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;
$role = $_SESSION['role'];

$success_msg = '';
$error_msg = '';

// Ambil data user saat ini
$query_user = mysqli_query($conn, "SELECT * FROM user WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);

// Proses update profil
if (isset($_POST['update_profil'])) {
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username_baru = mysqli_real_escape_string($conn, $_POST['username']);

    // Cek apakah username sudah digunakan oleh user lain
    $cek_username = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username_baru' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek_username) > 0) {
        $error_msg = "Username sudah digunakan oleh user lain!";
    } else {
        $query_update = "UPDATE user SET nama_lengkap = '$nama_baru', username = '$username_baru' WHERE id_user = '$id_user'";
        if (mysqli_query($conn, $query_update)) {
            $_SESSION['username'] = $username_baru;
            $_SESSION['nama_lengkap'] = $nama_baru;
            
            // Reset flag first login dan hapus password default
            $query_reset = "UPDATE user SET is_first_login = 0, password_default = NULL WHERE id_user = '$id_user'";
            mysqli_query($conn, $query_reset);
            
            // Hapus session notification flag
            unset($_SESSION['first_login_notification_shown']);
            
            $success_msg = "Profil berhasil diperbarui!";
            // Refresh data
            $query_user = mysqli_query($conn, "SELECT * FROM user WHERE id_user = '$id_user'");
            $data_user = mysqli_fetch_assoc($query_user);
        } else {
            $error_msg = "Gagal memperbarui profil: " . mysqli_error($conn);
        }
    }
}

// Proses ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Verifikasi password lama
    if (password_verify($password_lama, $data_user['password'])) {
        if ($password_baru === $konfirmasi_password) {
            if (strlen($password_baru) >= 4) {
                $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                $query_update = "UPDATE user SET password = '$password_hash' WHERE id_user = '$id_user'";
                if (mysqli_query($conn, $query_update)) {
                    // Reset flag first login dan hapus password default
                    $query_reset = "UPDATE user SET is_first_login = 0, password_default = NULL WHERE id_user = '$id_user'";
                    mysqli_query($conn, $query_reset);
                    
                    // Hapus session notification flag
                    unset($_SESSION['first_login_notification_shown']);
                    
                    $success_msg = "Password berhasil diubah! Silakan login kembali dengan password baru.";
                    
                    // Optional: redirect ke logout agar login ulang
                    // header("Location: ../logout.php");
                    // exit;
                } else {
                    $error_msg = "Gagal mengubah password: " . mysqli_error($conn);
                }
            } else {
                $error_msg = "Password baru minimal 4 karakter!";
            }
        } else {
            $error_msg = "Konfirmasi password tidak sesuai!";
        }
    } else {
        $error_msg = "Password lama salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Akun Saya - Karyawan | Woelandari Coffee Lab</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap"
        rel="stylesheet">
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
            --sidebar-width-mobile: 70px;
            --shadow-clean: 8px 8px 0 rgba(0, 43, 91, 0.15);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
            --gap-section-mobile: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
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
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            border-bottom: 3px double var(--navy);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--green);
            text-align: center;
        }

        .brand small {
            font-size: 0.7rem;
            display: block;
            color: var(--red);
        }

        .nav-item {
            display: block;
            padding: 14px 18px;
            color: var(--navy);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
        }

        .nav-item:hover,
        .nav-item.active {
            background: var(--navy);
            color: var(--white);
            transform: translateX(5px);
            box-shadow: 4px 4px 0 var(--green);
        }

        /* --- MAIN WRAPPER --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: var(--gap-section);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: var(--gap-section);
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
            width: 100%;
            overflow: hidden;
        }

        .paper-style-1 {
            transform: rotate(-0.3deg);
        }

        .paper-style-2 {
            transform: rotate(0.3deg);
        }

        .tape {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 35px;
            background: rgba(234, 67, 53, 0.7);
            border: 1px dashed rgba(255, 255, 255, 0.4);
            z-index: 2;
        }

        .sticky-note {
            position: absolute;
            top: 25px;
            right: 25px;
            background: #fff9c4;
            padding: 12px 18px;
            width: 220px;
            transform: rotate(2deg);
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.08);
            font-family: 'Caveat', cursive;
            font-size: 1rem;
            border: 1px solid #f0e68c;
            z-index: 5;
        }

        .sticky-note p {
            margin: 5px 0;
        }

        .spec-header {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 900;
            border-bottom: 2px solid var(--navy);
            padding-bottom: 10px;
            margin-bottom: 35px;
            text-transform: uppercase;
            flex-wrap: wrap;
            gap: 10px;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem;
            margin-bottom: 30px;
            color: var(--navy);
            border-left: 8px solid var(--green);
            padding-left: 20px;
        }

        /* --- FORM STYLING --- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--navy);
            background: transparent;
            font-family: 'Courier Prime', monospace;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 2px 2px 0 var(--green);
        }

        .form-input:disabled {
            background: rgba(0, 0, 0, 0.05);
            cursor: not-allowed;
        }

        .btn-submit {
            background: var(--green);
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            font-family: 'Special Elite', cursive;
            font-size: 0.85rem;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--navy);
            transform: translateY(-2px);
            box-shadow: 3px 3px 0 var(--green);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid var(--navy);
            color: var(--navy);
            padding: 10px 20px;
            cursor: pointer;
            font-family: 'Courier Prime', monospace;
            font-weight: bold;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: var(--navy);
            color: white;
        }

        /* Alert Messages */
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 0.85rem;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--red);
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 0.85rem;
        }

        .info-card {
            background: rgba(45, 106, 79, 0.08);
            border: 1px dashed var(--green);
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .info-card i {
            font-size: 1.5rem;
            color: var(--green);
        }

        .divider {
            height: 2px;
            background: repeating-linear-gradient(90deg, var(--navy), var(--navy) 10px, transparent 10px, transparent 20px);
            margin: 30px 0 20px;
        }

        .role-badge {
            background: var(--green);
            color: white;
            padding: 4px 12px;
            font-size: 0.7rem;
            border-radius: 2px;
            display: inline-block;
        }

        .blink {
            animation: pulse 1.5s infinite;
            color: var(--green);
        }

        @keyframes pulse {
            50% {
                opacity: 0.3;
            }
        }

        /* Password wrapper */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--navy);
            font-size: 1rem;
        }

        .toggle-password:focus {
            outline: none;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: var(--sidebar-width-mobile);
                padding: 20px 10px;
            }

            .brand span,
            .nav-item span {
                display: none;
            }

            .brand {
                font-size: 1.2rem;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }

            .brand small {
                display: none;
            }

            .nav-item {
                text-align: center;
                padding: 12px 8px;
            }

            .nav-item i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width-mobile);
                width: calc(100% - var(--sidebar-width-mobile));
                padding: var(--gap-section-mobile);
            }
        }

        @media (max-width: 768px) {
            .paper {
                padding: 25px 20px;
            }

            .title-main {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }

            .sticky-note {
                position: static;
                margin-bottom: 20px;
                width: 100%;
                transform: rotate(0deg);
            }

            .spec-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .title-main {
                font-size: 1.2rem;
                padding-left: 12px;
            }

            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand">
            WOELANDARI
            <small>Staff</small>
        </div>
        <nav class="nav-list">
            <a href="dashboard_staff.php" class="nav-item">
                <i class="fas fa-chalkboard-user"></i> <span>DASHBOARD</span>
            </a>
            <a href="menu_staff.php" class="nav-item">
                <i class="fas fa-utensils"></i> <span>MENU</span>
            </a>
            <a href="gallery_staff.php" class="nav-item">
                <i class="fas fa-images"></i> <span>GALLERY</span>
            </a>
            <a href="feedback_staff.php" class="nav-item">
                <i class="fas fa-star"></i> <span>FEEDBACK</span>
            </a>
            <a href="akun_staff.php" class="nav-item active">
                <i class="fas fa-user-circle"></i> <span>AKUN</span>
            </a>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="nav-item" style="color: var(--red);">
                    <i class="fas fa-sign-out-alt"></i> <span>KELUAR</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-wrapper">
        <section class="paper paper-style-1">
            <div class="tape"></div>
            
            <!-- Sticky Note -->
            <div class="sticky-note">
                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($nama_lengkap); ?></p>
                <p><i class="fas fa-badge"></i> ROLE: <span class="role-badge"><?php echo strtoupper($role); ?></span></p>
                <p><i class="fas fa-clock"></i> STATUS: <span class="blink">ONLINE</span></p>
            </div>

            <div class="spec-header">
                <span><i class="fas fa-user-circle"></i> WOELANDARI COFFEE LAB // ACCOUNT MANAGEMENT</span>
                <span>DATE: <?php echo date('d/m/Y'); ?></span>
            </div>

            <h1 class="title-main">PENGATURAN AKUN</h1>

            <!-- Alert Messages -->
            <?php if ($success_msg): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Info Card -->
            <div class="info-card">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Informasi Keamanan</strong><br>
                    <small>Jaga kerahasiaan akun Anda. Jangan berikan password kepada siapapun.</small>
                </div>
            </div>

            <!-- ========== FORM EDIT PROFIL ========== -->
            <form method="POST" action="">
                <h2 style="font-family: 'Special Elite'; font-size: 1.3rem; margin-bottom: 20px;">
                    <i class="fas fa-id-card"></i> DATA PROFIL
                </h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">ID USER</label>
                        <input type="text" class="form-input" value="#<?php echo $data_user['id_user']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ROLE / AKSES</label>
                        <input type="text" class="form-input" value="<?php echo strtoupper($data_user['role']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NAMA LENGKAP</label>
                        <input type="text" name="nama_lengkap" class="form-input"
                            value="<?php echo htmlspecialchars($data_user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">USERNAME</label>
                        <input type="text" name="username" class="form-input"
                            value="<?php echo htmlspecialchars($data_user['username']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">TANGGAL BERGABUNG</label>
                    <input type="text" class="form-input"
                        value="<?php echo date('d F Y H:i', strtotime($data_user['created_at'])); ?>" disabled>
                </div>

                <button type="submit" name="update_profil" class="btn-submit">
                    <i class="fas fa-save"></i> UPDATE PROFIL
                </button>
            </form>

            <div class="divider"></div>

            <!-- ========== FORM GANTI PASSWORD ========== -->
            <form method="POST" action="">
                <h2 style="font-family: 'Special Elite'; font-size: 1.3rem; margin-bottom: 20px;">
                    <i class="fas fa-key"></i> GANTI PASSWORD
                </h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">PASSWORD LAMA</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_lama" class="form-input" id="password_lama" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_lama')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PASSWORD BARU</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_baru" class="form-input" id="password_baru" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_baru')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small style="font-size: 0.65rem; color: #666;">Minimal 4 karakter</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">KONFIRMASI PASSWORD BARU</label>
                        <div class="password-wrapper">
                            <input type="password" name="konfirmasi_password" class="form-input" id="konfirmasi_password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('konfirmasi_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="info-card" style="margin-top: 10px;">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <small>Password minimal 4 karakter. Gunakan kombinasi huruf dan angka untuk keamanan yang lebih baik.</small>
                    </div>
                </div>

                <button type="submit" name="ganti_password" class="btn-submit">
                    <i class="fas fa-lock"></i> GANTI PASSWORD
                </button>
            </form>

            <!-- Logout Warning -->
            <div style="margin-top: 30px; padding: 15px; background: rgba(234, 67, 53, 0.1); border: 1px dashed var(--red);">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; justify-content: space-between;">
                    <div>
                        <i class="fas fa-sign-out-alt" style="color: var(--red);"></i>
                        <strong style="margin-left: 5px;">Keluar dari Sistem?</strong>
                        <small style="display: block; margin-top: 5px;">Pastikan Anda telah menyimpan semua perubahan sebelum keluar.</small>
                    </div>
                    <a href="../logout.php" class="btn-secondary"
                        style="background: var(--red); color: white; border: none; text-decoration: none;"
                        onclick="return confirm('Yakin ingin keluar dari sistem?')">
                        <i class="fas fa-sign-out-alt"></i> LOGOUT
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>