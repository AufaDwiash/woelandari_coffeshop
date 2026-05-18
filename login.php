<?php
ob_start();
session_start();
include "config/koneksi.php";

// --- 1. LOGIKA COOKIE (Remember Me) ---
// Jika tidak ada session tapi ada cookie, buatkan session-nya
if (!isset($_SESSION['status']) && isset($_COOKIE['remember_user']) && !isset($_GET['logout'])) {
    $user_cookie = mysqli_real_escape_string($conn, $_COOKIE['remember_user']);
    $query_cookie = mysqli_query($conn, "SELECT * FROM user WHERE username='$user_cookie'");
    $data_cookie = mysqli_fetch_assoc($query_cookie);

    if ($data_cookie) {
        $_SESSION['id_user']      = $data_cookie['id_user'];
        $_SESSION['username']     = $data_cookie['username'];
        $_SESSION['role']         = $data_cookie['role'];
        $_SESSION['nama_lengkap'] = $data_cookie['nama_lengkap'];
        $_SESSION['status']       = "login";
    }
}

// --- 2. LOGIKA REDIRECT OTOMATIS ---
// Jika sudah dalam keadaan login (baik dari session atau cookie tadi), langsung lempar ke dashboard
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login') {
    // Sesuaikan path ini dengan folder dashboard Anda
    header("Location: dashboard/dashboard.php");
    exit;
}

// --- 3. PROSES LOGIN MANUAL (POST) ---
$error = "";
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {
        // Verifikasi password (menggunakan password_verify karena di user_manajemen menggunakan password_hash)
        if (password_verify($password, $data['password'])) {
            // Set Session
            $_SESSION['id_user']      = $data['id_user'];
            $_SESSION['username']     = $data['username'];
            $_SESSION['role']         = $data['role'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['status']       = "login";

            // Set Cookie jika Remember Me dicentang
            if (isset($_POST['remember'])) {
                setcookie('remember_user', $username, time() + (7 * 24 * 60 * 60), "/"); // Berlaku 7 hari
            }

            header("Location: dashboard/dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login | Woelandari Coffee Lab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login_style.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="cursor-glow"></div>

    <div class="login-container">
        <div class="paper-stack">
            <div class="card-blueprint">
                <div class="tape-red"></div>

                <div class="spec-header">
                    <span>Hai Gimana Harimu?</span>
                    <span class="spec-status">STANDBY</span>
                </div>

                <h1>Login</h1>
                <p class="subtitle">WOELANDARI COFFEESHOP   SYSTEM</p>

                <?php if($error != ''): ?>
                    <div class="error-box">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password..." required>
                            <span class="password-toggle" id="togglePassword">
                                <i class="fa-regular fa-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-footer">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Remember Me</label>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" name="login" class="btn-login">
                                <i class="fa-solid fa-power-off"></i> LOGIN
                            </button>
                            <a href="index.php" class="btn-return">
                                <i class="fa-solid fa-rotate-left"></i> KEMBALI KE HALAMAN UTAMA
                            </a>
                        </div>
                    </div>

                    <div class="terminal-note">
                        <p><strong>NOTE:</strong> AKSES TERBATAS. SISTEM MENCATAT SETIAP PERCOBAAN LOGIN SECARA OTOMATIS.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Toggle Password
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');
            const eyeIcon = document.querySelector('#eyeIcon');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    if (eyeIcon) {
                        eyeIcon.classList.toggle('fa-eye');
                        eyeIcon.classList.toggle('fa-eye-slash');
                    }
                });
            }

            // Cursor Glow Effect
            const glow = document.querySelector('.cursor-glow');
            if (glow) {
                document.addEventListener('mousemove', (e) => {
                    glow.style.left = e.pageX + 'px';
                    glow.style.top = e.pageY + 'px';
                });
            }

            // Terminal Status Interaction
            const inputs = document.querySelectorAll('.form-control');
            const statusBox = document.querySelector('.spec-status');
            if (inputs.length && statusBox) {
                inputs.forEach(input => {
                    input.addEventListener('focus', () => {
                        statusBox.innerText = 'VERIFYING...';
                        statusBox.style.color = '#C55A5A';
                    });
                    input.addEventListener('blur', () => {
                        statusBox.innerText = 'STANDBY';
                        statusBox.style.color = '';
                    });
                });
            }
        })();
    </script>
</body>
</html>