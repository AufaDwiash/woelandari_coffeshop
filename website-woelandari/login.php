<?php
session_start();
include "config/koneksi.php"; 

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Asumsi password di database menggunakan enkripsi MD5

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['status']   = "login";
        
        // Arahkan ke halaman admin/dashboard setelah berhasil login
        header("Location: admin/dashboard.php"); 
        exit();
    } else {
        $error = "ACCESS_DENIED: Kredensial tidak valid.";
    }
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

        <?php if($error != ''): ?>
            <div class="error-box">
                > ERROR: <?php echo $error; ?>
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
            if(document.getElementById('username').value === '' && document.getElementById('password').value === '') {
                statusBox.innerText = 'SYSTEM_LOCKED';
                statusBox.style.color = 'var(--red)';
                statusBox.classList.add('blink');
            }
        });
    });
</script>

</body>
</html>