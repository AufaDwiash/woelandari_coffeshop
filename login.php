<?php
ob_start(); 
session_start();
include "config/koneksi.php"; 

// Jika sudah login, redirect berdasarkan role
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login') {
    if ($_SESSION['role'] == 'karyawan') {
        header("Location: karyawan/dashboard_staff.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;
}

// Cek cookie remember me
if (!isset($_SESSION['status']) && isset($_COOKIE['remember_user']) && !isset($_GET['logout'])) {
    $user_cookie = mysqli_real_escape_string($conn, $_COOKIE['remember_user']);
    $query_cookie = mysqli_query($conn, "SELECT * FROM user WHERE username='$user_cookie'");
    $data_cookie = mysqli_fetch_assoc($query_cookie);

    if ($data_cookie) {
        $_SESSION['id_user']  = $data_cookie['id_user'];
        $_SESSION['username'] = $data_cookie['username'];
        $_SESSION['role']     = $data_cookie['role'];
        $_SESSION['nama_lengkap'] = $data_cookie['nama_lengkap'];
        $_SESSION['status']   = "login";
        
        // Redirect berdasarkan role
        if ($data_cookie['role'] == 'karyawan') {
            header("Location: karyawan/dashboard_staff.php");
        } else {
            header("Location: admin/dashboard.php");
        }
        exit;
    }
}

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    
    // Debug: cek apakah username ada
    $query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
    
    if (mysqli_num_rows($query) == 0) {
        $error = "ERR_404: USERNAME TIDAK DITEMUKAN.";
    } else {
        $data = mysqli_fetch_assoc($query);
        
        // Cek password
        if (password_verify($password, $data['password'])) {
            // SET SESSION LENGKAP
            $_SESSION['id_user']       = $data['id_user'];
            $_SESSION['username']      = $data['username'];
            $_SESSION['role']          = $data['role'];
            $_SESSION['nama_lengkap']  = $data['nama_lengkap'];
            $_SESSION['status']        = "login";
            
            // Set cookie remember me
            if (isset($_POST['remember'])) {
                $duration = time() + (3600 * 24 * 30); 
                setcookie("remember_user", $data['username'], $duration, "/");
            }
            setcookie('last_user', $data['username'], time() + 3600, "/");
            
            ob_clean();
            
            // LOGIC REDIRECT BERDASARKAN ROLE
            // PASTIKAN ROLE YANG TERSIMPAN DI SESSION
            if ($data['role'] == 'karyawan') {
                header("Location: karyawan/dashboard_staff.php");
            } else {
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            $error = "ERR_403: PASSWORD SALAH.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login - Woelandari Coffee Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/login_style.css">
    
    <style>
        /* Additional debug styles */
        .debug-info {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: #0f0;
            font-family: monospace;
            font-size: 10px;
            padding: 5px;
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center h-100">
    <div class="login-card">
        <div class="tape-red"></div>
        
        <div class="spec-header d-flex justify-content-between">
            <span id="sys-title">// LOG_01</span>
            <span class="spec-status text-danger">SYSTEM_LOCKED</span>
        </div>
        
        <h1>System Access</h1>

        <?php if($error != ''): ?>
            <div class="error-box"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group-custom mb-3">
                <label for="username" class="form-label">IDENTIFIER</label>
                <input type="text" id="username" name="username" class="form-control" required autocomplete="off" placeholder="Input ID...">
            </div>

            <div class="input-group-custom mb-2">
                <label for="password" class="form-label">ACCESS_CODE</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                    <button type="button" class="eye-icon-btn" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-check remember-me">
                <input type="checkbox" class="form-check-input" name="remember" id="remember" value="1"> 
                <label class="form-check-label ms-1" for="remember">
                    RETAIN SESSION
                </label>
            </div>

            <button type="submit" name="login" class="btn-login">
                INITIALIZE <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="notebook-note">
            <div class="mb-1" style="font-weight:bold; font-size:0.75rem;">NOTE:</div>
            <p class="handwritten-text">Jaga kerahasiaan akses lab!</p>
        </div>
        
        <!-- Debug informasi (hapus setelah testing) -->
        <div class="debug-info" id="debugInfo"></div>
    </div>
</div>

<script>
    // Toggle Password
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });

    // Interaksi Status Teks Terminal
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
    const statusBox = document.querySelector('.spec-status');

    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            statusBox.innerText = 'VERIFYING...';
            statusBox.classList.remove('text-danger');
            statusBox.style.color = 'var(--ink-color)';
        });
        
        input.addEventListener('blur', () => {
            let userVal = document.getElementById('username').value;
            let passVal = document.getElementById('password').value;
            
            if(userVal === '' && passVal === '') {
                statusBox.innerText = 'SYSTEM_LOCKED';
                statusBox.style.color = '';
                statusBox.classList.add('text-danger');
            } else {
                statusBox.innerText = 'STANDBY';
                statusBox.style.color = '#b35a5a';
            }
        });
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>