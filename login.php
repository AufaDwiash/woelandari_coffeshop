<?php
session_start();
include "config/koneksi.php";

if (isset($_SESSION['status']) && $_SESSION['status'] === "login") {
    header("Location: admin/dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' LIMIT 1");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $stored_password = $data['password'];
        $password_valid = password_verify($password_input, $stored_password) || md5($password_input) === $stored_password;

        if ($password_valid) {
            if (md5($password_input) === $stored_password) {
                $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
                $id_user = (int) $data['id_user'];
                mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id_user=$id_user");
            }

            $_SESSION['id_user']  = $data['id_user'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'] ?? 'admin';
            $_SESSION['status']   = "login";

            setcookie('last_user', $data['username'], time() + 3600, '/');
            header("Location: admin/dashboard.php");
            exit;
        }
    }

    $error = "ACCESS_DENIED: Kredensial tidak valid.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login_style.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="tape-top"></div>
        
        <div class="spec-header">
            <span class="spec-id">// AUTH_PANEL_WOELANDARI</span>
            <span class="spec-status text-red blink">SYSTEM_LOCKED</span>
        </div>
        
        <h1 class="login-title">AUTHORIZED<br>ACCESS ONLY</h1>

        <?php if ($error !== ''): ?>
            <div class="error-box">
                &gt; ERROR: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="form-login">
            <div class="input-group">
                <label for="username">USERNAME</label>
                <input type="text" id="username" name="username" required autocomplete="off" placeholder="Masukkan ID...">
                <div class="input-line"></div>
            </div>

            <div class="input-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                <div class="input-line"></div>
            </div>

            <button type="submit" name="login" class="btn-login">
                INITIALIZE LOGIN <span class="arrow">→</span>
            </button>
        </form>

        <div class="notebook-note">
            <div class="note-title">Security Note:</div>
            <p class="handwritten-text">Hanya untuk staf Woelandari Lab. Jangan berikan akses ke pihak luar!</p>
        </div>
    </div>
</div>

<script>
    const inputs = document.querySelectorAll('input');
    const statusBox = document.querySelector('.spec-status');

    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            statusBox.innerText = 'VERIFYING_INPUT...';
            statusBox.style.color = 'var(--navy)';
            statusBox.classList.remove('blink');
        });
        
        input.addEventListener('blur', () => {
            if (document.getElementById('username').value === '' && document.getElementById('password').value === '') {
                statusBox.innerText = 'SYSTEM_LOCKED';
                statusBox.style.color = 'var(--red)';
                statusBox.classList.add('blink');
            }
        });
    });
</script>

</body>
</html>
