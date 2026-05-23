<?php
session_start();
include "../config/koneksi.php";

// Proteksi halaman - hanya karyawan yang bisa akses
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin') {
    header("Location: ../dashboard/dashboard.php");
    exit;
}
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

    $cek_username = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username_baru' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek_username) > 0) {
        $error_msg = "Username sudah digunakan oleh user lain!";
    } else {
        $query_update = "UPDATE user SET nama_lengkap = '$nama_baru', username = '$username_baru' WHERE id_user = '$id_user'";
        if (mysqli_query($conn, $query_update)) {
            $_SESSION['username'] = $username_baru;
            $_SESSION['nama_lengkap'] = $nama_baru;

            $query_reset = "UPDATE user SET is_first_login = 0, password_default = NULL WHERE id_user = '$id_user'";
            mysqli_query($conn, $query_reset);
            unset($_SESSION['first_login_notification_shown']);

            $success_msg = "Profil berhasil diperbarui!";
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

    if (password_verify($password_lama, $data_user['password'])) {
        if ($password_baru === $konfirmasi_password) {
            if (strlen($password_baru) >= 4) {
                $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                $query_update = "UPDATE user SET password = '$password_hash' WHERE id_user = '$id_user'";
                if (mysqli_query($conn, $query_update)) {
                    $query_reset = "UPDATE user SET is_first_login = 0, password_default = NULL WHERE id_user = '$id_user'";
                    mysqli_query($conn, $query_reset);
                    unset($_SESSION['first_login_notification_shown']);
                    $success_msg = "Password berhasil diubah! Silakan login kembali dengan password baru.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Akun Saya - Karyawan | Woelandari Coffee Lab</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap"
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
            --shadow-clean: 12px 12px 0 rgba(0, 43, 91, 0.2);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
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
            overflow-x: hidden;
        }

        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0) rotate(-0.2deg);
            }
        }

        @keyframes floatTape {

            0%,
            100% {
                transform: translateX(-50%) translateY(0);
            }

            50% {
                transform: translateX(-50%) translateY(-2px);
            }
        }

        @keyframes shakeAnim {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* --- MAIN WRAPPER --- */
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
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 32px;
            background: rgba(234, 67, 53, 0.9);
            border: 1px dashed rgba(255, 255, 255, 0.5);
            z-index: 10;
            box-shadow: 2px 3px 5px rgba(0, 0, 0, 0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .spec-header {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 900;
            border-bottom: 2px solid var(--navy);
            padding-bottom: 10px;
            margin-bottom: 30px;
            text-transform: uppercase;
            flex-wrap: wrap;
            gap: 10px;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem;
            margin-bottom: 25px;
            color: var(--navy);
            border-left: 8px solid var(--red);
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
            color: var(--navy);
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--navy);
            background: white;
            font-family: 'Courier Prime', monospace;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--red);
            box-shadow: 2px 2px 0 var(--red);
        }

        .form-input:disabled {
            background: rgba(0, 43, 91, 0.05);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-submit {
            background: var(--navy);
            color: var(--white);
            border: 2px solid var(--navy);
            padding: 12px 25px;
            cursor: pointer;
            font-family: 'Special Elite', cursive;
            font-size: 0.85rem;
            transition: all 0.2s;
            margin-top: 10px;
            font-weight: bold;
            box-shadow: 4px 4px 0 var(--red);
        }

        .btn-submit:hover {
            background: var(--white);
            color: var(--navy);
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--red);
        }

        .btn-secondary {
            background: var(--white);
            border: 2px solid var(--navy);
            color: var(--navy);
            padding: 10px 20px;
            cursor: pointer;
            font-family: 'Courier Prime', monospace;
            font-weight: bold;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 3px 3px 0 var(--navy);
        }

        .btn-secondary:hover {
            background: var(--navy);
            color: var(--white);
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 var(--navy);
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid var(--red);
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .info-card {
            background: rgba(0, 43, 91, 0.04);
            border: 2px dashed var(--navy);
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .info-card i {
            font-size: 1.5rem;
            color: var(--red);
        }

        .divider {
            height: 2px;
            background: repeating-linear-gradient(90deg, var(--navy), var(--navy) 10px, transparent 10px, transparent 20px);
            margin: 30px 0 20px;
        }

        .role-badge {
            background: var(--red);
            color: white;
            padding: 4px 12px;
            font-size: 0.7rem;
            display: inline-block;
        }

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

        /* DELETE CONFIRMATION MODAL */
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 43, 91, 0.85);
            backdrop-filter: blur(8px);
            z-index: 3000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .confirm-modal-content {
            background: var(--white);
            border: 4px solid var(--navy);
            max-width: 450px;
            width: 100%;
            position: relative;
            animation: slideUpFade 0.3s ease;
            box-shadow: 16px 16px 0 var(--red);
        }

        .confirm-modal-header {
            background: var(--red);
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid var(--navy);
        }

        .confirm-modal-header i {
            font-size: 4rem;
            color: var(--white);
            text-shadow: 3px 3px 0 var(--navy);
        }

        .confirm-modal-body {
            padding: 30px;
            text-align: center;
        }

        .confirm-modal-body h3 {
            font-family: 'Special Elite', cursive;
            font-size: 1.5rem;
            color: var(--navy);
            margin-bottom: 15px;
        }

        .confirm-modal-body p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .user-name-highlight {
            background: rgba(234, 67, 53, 0.1);
            color: var(--red);
            font-weight: bold;
            padding: 5px 12px;
            display: inline-block;
            margin: 10px 0;
            border-left: 3px solid var(--red);
            font-size: 1rem;
            max-width: 100%;
            word-break: break-word;
        }

        .confirm-modal-footer {
            padding: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            border-top: 2px dashed rgba(0, 43, 91, 0.2);
        }

        .confirm-modal-footer .btn {
            min-width: 120px;
        }

        .confirm-modal-content.warning-shake {
            animation: shakeAnim 0.3s ease;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 43, 91, 0.5);
            backdrop-filter: blur(2px);
            z-index: 900;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        .mobile-header {
            display: none;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .main-wrapper {
                margin-left: 0;
                width: 100%;
                padding: 15px;
                margin-top: 70px;
                gap: 25px;
            }

            .mobile-header {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 65px;
                z-index: 800;
                background: rgba(248, 249, 250, 0.9);
                backdrop-filter: blur(8px);
                border-bottom: 3px solid var(--navy);
                padding: 0 20px;
                align-items: center;
                justify-content: space-between;
            }

            .mobile-header .logo-mobile {
                font-family: 'Special Elite', cursive;
                color: var(--navy);
                font-size: 1.2rem;
            }

            .hamburger {
                background: none;
                border: none;
                font-size: 1.6rem;
                color: var(--navy);
                cursor: pointer;
            }

            .paper {
                padding: 25px 20px;
            }

            .title-main {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }

            .spec-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .btn-submit {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .title-main {
                font-size: 1.2rem;
                padding-left: 12px;
            }

            .info-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="sidebarOverlay"></div>

    <?php include "../components/sidebar.php"; ?>

    <main class="main-wrapper">
        <!-- MOBILE HEADER + HAMBURGER -->
        <div class="mobile-header">
            <div class="logo-mobile">
                <i class="fas fa-user-circle" style="color: var(--red);"></i> WOELANDARI
            </div>
            <button class="hamburger" id="hamburgerBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <section class="paper">
            <div class="tape"></div>

            <div class="spec-header">
                <span><i class="fas fa-user-circle"></i> AKUN STAFF TOKO KOPI WOELANDARI</span>
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
                <h2 style="font-family: 'Special Elite'; font-size: 1.3rem; margin-bottom: 20px; border-left: 4px solid var(--red); padding-left: 15px;">
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
                <h2 style="font-family: 'Special Elite'; font-size: 1.3rem; margin-bottom: 20px; border-left: 4px solid var(--red); padding-left: 15px;">
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

        
        </section>
    </main>

    <!-- CUSTOM LOGOUT CONFIRMATION MODAL -->
    <div id="logoutConfirmModal" class="confirm-modal">
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="confirm-modal-body">
                <h3>KELUAR DARI SISTEM?</h3>
                <p>Apakah Anda yakin ingin keluar dari akun ini?</p>
                <div class="user-name-highlight" id="userNameDisplay"></div>
                <p style="font-size: 0.8rem; color: #999; margin-top: 15px;">
                    <i class="fas fa-info-circle"></i> Pastikan semua perubahan sudah disimpan sebelum keluar.
                </p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-secondary" id="cancelLogoutBtn" style="box-shadow: 3px 3px 0 var(--navy);">
                    <i class="fas fa-times"></i> BATAL
                </button>
                <a href="../logout.php" id="confirmLogoutBtn" class="btn-secondary" style="background: var(--red); color: white; border: none; box-shadow: 4px 4px 0 var(--navy);">
                    <i class="fas fa-sign-out-alt"></i> LOGOUT
                </a>
            </div>
        </div>
    </div>

    <script>
        // ========== MOBILE SIDEBAR TOGGLE ==========
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', toggleSidebar);
        }
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            }
        });

        // Tutup sidebar saat link diklik di mode mobile
        const navLinks = document.querySelectorAll('#mainSidebar .nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = field.nextElementSibling;
            const icon = btn.querySelector('i');

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

        // ========== LOGOUT CONFIRMATION MODAL ==========
        const logoutModal = document.getElementById('logoutConfirmModal');
        const logoutBtn = document.getElementById('logoutBtn');
        const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
        const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
        const userNameDisplay = document.getElementById('userNameDisplay');

        // Set nama user di modal
        const userName = '<?php echo htmlspecialchars($nama_lengkap); ?>';
        userNameDisplay.textContent = userName;

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.style.display = 'flex';

                const modalContent = document.querySelector('#logoutConfirmModal .confirm-modal-content');
                modalContent.classList.add('warning-shake');
                setTimeout(() => {
                    modalContent.classList.remove('warning-shake');
                }, 300);
            });
        }

        cancelLogoutBtn.addEventListener('click', function() {
            logoutModal.style.display = 'none';
        });

        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && logoutModal.style.display === 'flex') {
                logoutModal.style.display = 'none';
            }
        });
    </script>

</body>

</html>