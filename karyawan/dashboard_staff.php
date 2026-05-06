<?php
ob_start();
session_start();
include "../config/koneksi.php";

// Proteksi halaman - hanya karyawan yang bisa akses
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Jika yang login adalah admin/superadmin, redirect ke admin dashboard
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
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;
$id_user = $_SESSION['id_user'];

// Query statistik untuk karyawan
$jml_menu      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$jml_event     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM events"))['total'] ?? 0;
$jml_feedback  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
$jml_gallery   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM gallery"))['total'] ?? 0;

// ========== LOGIC POPUP UNTUK PERTAMA KALI LOGIN ==========
// Cek apakah user baru saja login (menggunakan session flag)
$show_popup = false;
$popup_message = "";

// Cek apakah ini pertama kali login setelah dibuat oleh admin
// Kita cek dari kolom 'is_first_login' atau 'password_changed' jika ada
// Atau kita gunakan session flag sementara

// Method 1: Cek dari session (setelah login)
if (!isset($_SESSION['first_login_notification_shown'])) {
    // Cek dari database apakah user pernah mengubah password
    // Asumsi: kita buat kolom 'password_changed' atau kita cek password bawaan
    
    // Cara sederhana: cek apakah password masih default (misal: 'password123')
    // Atau kita cek dari tabel user ada kolom 'is_first_login'
    
    // Karena kita tidak punya kolom khusus, kita gunakan session sementara
    // Set session flag agar popup hanya muncul sekali
    $show_popup = true;
    $popup_message = "⚠️ PERHATIAN! Anda menggunakan akun bawaan dari admin. Segera ubah password dan username Anda di halaman PROFILE untuk keamanan akun Anda!";
    
    // Tandai bahwa popup sudah ditampilkan
    $_SESSION['first_login_notification_shown'] = true;
}

// Method alternatif: Cek dari database (lebih akurat)
// Jalankan query ini sekali untuk menambahkan kolom jika belum ada
// ALTER TABLE user ADD COLUMN is_first_login TINYINT(1) DEFAULT 1;
/*
$check_user = mysqli_query($conn, "SELECT is_first_login FROM user WHERE id_user = '$id_user'");
$user_data = mysqli_fetch_assoc($check_user);
if ($user_data && $user_data['is_first_login'] == 1) {
    $show_popup = true;
    $popup_message = "⚠️ PERHATIAN! Ini adalah pertama kali Anda login. Segera ubah password dan username Anda di halaman PROFILE untuk keamanan akun Anda!";
}
*/
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
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
            --shadow-clean: 8px 8px 0 rgba(0, 43, 91, 0.15);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
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
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
        }

        .nav-item:hover, .nav-item.active {
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

        .paper-style-1 { transform: rotate(-0.3deg); }
        .paper-style-2 { transform: rotate(0.3deg); }

        .tape {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 35px; 
            background: rgba(234, 67, 53, 0.7);
            border: 1px dashed rgba(255,255,255,0.4);
            z-index: 2;
        }

        .sticky-note {
            position: absolute; top: 25px; right: 25px;
            background: #fff9c4;
            padding: 12px 18px;
            width: 200px;
            transform: rotate(2deg);
            box-shadow: 4px 4px 10px rgba(0,0,0,0.08);
            font-family: 'Caveat', cursive;
            font-size: 1.15rem;
            border: 1px solid #f0e68c;
            z-index: 5;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 35px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 30px;
            color: var(--navy);
            border-left: 8px solid var(--green);
            padding-left: 20px;
        }

        /* --- STAT GRID --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: rgba(0, 43, 91, 0.02);
            border: 2px solid var(--navy);
            padding: 25px 15px;
            text-align: center;
            transition: 0.3s;
        }

        .stat-card:hover {
            background: white;
            box-shadow: 6px 6px 0 var(--navy);
            transform: translateY(-5px);
        }

        .stat-label { font-size: 0.75rem; font-weight: 800; display: block; margin-bottom: 12px; opacity: 0.7; }
        .stat-value { font-family: 'Special Elite', cursive; font-size: 3.5rem; color: var(--green); line-height: 1; }

        .blink { animation: pulse 1.5s infinite; color: var(--green); }
        @keyframes pulse { 50% { opacity: 0.3; } }

        .role-badge {
            background: var(--green);
            color: white;
            padding: 2px 8px;
            font-size: 0.6rem;
            border-radius: 2px;
            margin-left: 8px;
        }

        /* --- POPUP MODAL STYLE --- */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        .popup-card {
            background: var(--white);
            border: 3px solid var(--navy);
            padding: 0;
            max-width: 500px;
            width: 90%;
            position: relative;
            box-shadow: 10px 10px 0 rgba(0, 0, 0, 0.2);
            animation: popupSlideIn 0.3s ease-out;
        }

        @keyframes popupSlideIn {
            from {
                transform: scale(0.8) translateY(-50px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .popup-header {
            background: var(--red);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid var(--navy);
        }

        .popup-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .popup-header h3 {
            font-family: 'Special Elite', cursive;
            font-size: 1.5rem;
            letter-spacing: 2px;
        }

        .popup-body {
            padding: 25px;
            text-align: center;
        }

        .popup-body p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .popup-body .warning-icon {
            font-size: 2rem;
            color: var(--red);
            margin-bottom: 15px;
        }

        .popup-footer {
            padding: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .popup-btn {
            padding: 10px 25px;
            font-family: 'Courier Prime', monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .popup-btn-primary {
            background: var(--green);
            color: white;
            border: 2px solid var(--green);
        }

        .popup-btn-primary:hover {
            background: var(--navy);
            border-color: var(--navy);
            transform: translateY(-2px);
            box-shadow: 3px 3px 0 rgba(0,0,0,0.1);
        }

        .popup-btn-secondary {
            background: transparent;
            color: var(--navy);
            border: 2px solid var(--navy);
        }

        .popup-btn-secondary:hover {
            background: var(--navy);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar .brand span, .nav-item span { display: none; }
            .main-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        }

        @media (max-width: 768px) {
            .popup-header h3 { font-size: 1.2rem; }
            .popup-body p { font-size: 0.85rem; }
            .popup-btn { padding: 8px 20px; font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        WOELANDARI
        <small>staff</small>
    </div>
    <nav class="nav-list">
        <a href="dashboard_staff.php" class="nav-item active">
            <i class="fas fa-chalkboard-user"></i> <span>DASHBOARD</span>
        </a>
        <a href="menu_staff.php" class="nav-item">
            <i class="fas fa-utensils"></i> <span>MENU</span>
        </a>
        <a href="gallery_staff.php" class="nav-item">
            <i class="fas fa-images"></i> <span> GALLERY</span>
        </a>
        <a href="feedback_staff.php" class="nav-item">
            <i class="fas fa-star"></i> <span> FEEDBACK</span>
        </a>
        <a href="akun_staff.php" class="nav-item">
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
    <!-- SECTION 1 - GREETING & STATS -->
    <section class="paper paper-style-1">
        <div class="tape"></div>
        <div class="sticky-note">
            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($nama_lengkap); ?></p>
            <p><i class="fas fa-badge"></i> ROLE: KARYAWAN <span class="role-badge">STAFF</span></p>
            <p><i class="fas fa-clock"></i> STATUS: <span class="blink">ONLINE</span></p>
        </div>
        
        <div class="spec-header">
            <span><i class="fas fa-coffee"></i> WOELANDARI COFFEE LAB // STAFF PORTAL</span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">Ringkasan Data</h1>
        
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-utensils"></i> Total Menu</span>
                <div class="stat-value"><?php echo $jml_menu; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-images"></i> Gallery</span>
                <div class="stat-value"><?php echo $jml_gallery; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-calendar-alt"></i> Event</span>
                <div class="stat-value"><?php echo $jml_event; ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-label"><i class="fas fa-star"></i> Rating & Feedback</span>
                <div class="stat-value"><?php echo $jml_feedback; ?></div>
            </div>
        </div>
    </section>

    <!-- SECTION 2 - QUICK ACCESS MENU -->
    <section class="paper paper-style-1">
        <div class="spec-header">
            <span><i class="fas fa-bolt"></i> QUICK ACCESS</span>
        </div>
        
        <h2 class="title-main" style="font-size: 1.5rem;">Akses Cepat</h2>
        
        <div class="stat-grid">
            <a href="menu_staff.php" style="text-decoration: none;">
                <div class="stat-card">
                    <i class="fas fa-utensils" style="font-size: 2rem; color: var(--green);"></i>
                    <div class="stat-value" style="font-size: 1.2rem; margin-top: 10px;">Menu</div>
                    <span class="stat-label">Kelola Daftar Menu</span>
                </div>
            </a>
            <a href="gallery_staff.php" style="text-decoration: none;">
                <div class="stat-card">
                    <i class="fas fa-images" style="font-size: 2rem; color: var(--navy);"></i>
                    <div class="stat-value" style="font-size: 1.2rem; margin-top: 10px;">Gallery</div>
                    <span class="stat-label">Kelola Foto & Event</span>
                </div>
            </a>
            <a href="feedback_staff.php" style="text-decoration: none;">
                <div class="stat-card">
                    <i class="fas fa-star" style="font-size: 2rem; color: var(--red);"></i>
                    <div class="stat-value" style="font-size: 1.2rem; margin-top: 10px;">Feedback</div>
                    <span class="stat-label">Moderasi Feedback</span>
                </div>
            </a>
            <a href="akun_staff.php" style="text-decoration: none;">
                <div class="stat-card">
                    <i class="fas fa-user-circle" style="font-size: 2rem; color: var(--green);"></i>
                    <div class="stat-value" style="font-size: 1.2rem; margin-top: 10px;">Akun</div>
                    <span class="stat-label">Pengaturan Profil</span>
                </div>
            </a>
        </div>
    </section>
</main>

<!-- POPUP NOTIFICATION UNTUK PERTAMA KALI LOGIN -->
<?php if ($show_popup): ?>
<div class="popup-overlay" id="firstLoginPopup">
    <div class="popup-card">
        <div class="popup-header">
            <i class="fas fa-shield-alt"></i>
            <h3>PERHATIAN!</h3>
        </div>
        <div class="popup-body">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p><?php echo $popup_message; ?></p>
            <p style="font-size: 0.85rem; color: #666; margin-top: 10px;">
                <i class="fas fa-info-circle"></i> Demi keamanan akun Anda, disarankan untuk segera mengganti informasi login default.
            </p>
        </div>
        <div class="popup-footer">
            <a href="akun_staff.php" class="popup-btn popup-btn-primary">
                <i class="fas fa-user-edit"></i> KE HALAMAN AKUN
            </a>
            <button onclick="closePopup()" class="popup-btn popup-btn-secondary">
                <i class="fas fa-times"></i> TUTUP
            </button>
        </div>
    </div>
</div>

<script>
    // Tampilkan popup saat halaman load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('firstLoginPopup').style.display = 'flex';
    });
    
    function closePopup() {
        document.getElementById('firstLoginPopup').style.display = 'none';
    }
    
    // Tutup popup jika klik di luar area
    document.getElementById('firstLoginPopup').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>
<?php endif; ?>

</body>
</html>
<?php ob_end_flush(); ?>