<?php
include "config/koneksi.php";
$menu_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu"))['total'] ?? 0;
$event_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM events"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Courier+Prime:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main_style.css">
    <style>
        body { margin: 0; font-family: 'Courier Prime', monospace; background: #efebe0; color: #1a1a1a; }
        .topnav { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; border-bottom: 4px solid #000; background: #fff; }
        .brand { font-family: 'Special Elite', cursive; font-size: 1.4rem; color: #9b2226; }
        .navlinks { display: flex; gap: 16px; flex-wrap: wrap; }
        .navlinks a { color: #000; text-decoration: none; font-weight: 700; }
        .frontpage-hero { min-height: calc(100vh - 86px); justify-content: center; }
        .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; margin-top: 30px; }
        .hero-actions a { background: #000; color: #fff; border: 3px solid #000; padding: 13px 20px; text-decoration: none; font-family: 'Special Elite', cursive; box-shadow: 6px 6px 0 #9b2226; }
        .hero-actions a.secondary { background: #fff; color: #000; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; padding: 40px 5%; border-bottom: 4px solid #000; }
        .stat { background: #fff; border: 3px solid #000; padding: 20px; box-shadow: 8px 8px 0 #000; }
        .stat strong { display: block; font-size: 2.2rem; font-family: 'Special Elite', cursive; }
        @media (max-width: 800px) { .hero-headline { font-size: 4rem; letter-spacing: 0; } .topnav { align-items: flex-start; flex-direction: column; gap: 15px; } }
    </style>
</head>
<body>
<nav class="topnav">
    <div class="brand">WOELANDARI COFFEE LAB</div>
    <div class="navlinks">
        <a href="about.php">About</a>
        <a href="menu.php">Menu</a>
        <a href="gallery.php">Gallery</a>
        <a href="community.php">Community</a>
        <a href="lokasi.php">Lokasi</a>
        <a href="rating.php">Rating</a>
        <a href="login.php">Login</a>
    </div>
</nav>

<section class="frontpage-hero">
    <p class="hero-kicker">// MAKNA ANGAN KARYA</p>
    <h1 class="hero-headline">WOELANDARI<br><span class="outline-text">COFFEE LAB</span></h1>
    <div class="hero-visual">
        <img src="assets/images/gambar-mentahan/about1.jpg" class="hero-img" alt="Woelandari Coffee">
    </div>
    <div class="hero-actions">
        <a href="menu.php">Lihat Menu</a>
        <a href="gallery.php" class="secondary">Visual Archives</a>
        <a href="rating.php" class="secondary">Customer Voices</a>
    </div>
</section>

<div class="marquee-container">
    <div class="marquee-content">
        <span>COFFEE</span><span>COMMUNITY</span><span>EVENT</span><span>LOCAL BEANS</span><span>WOELANDARI</span>
        <span>COFFEE</span><span>COMMUNITY</span><span>EVENT</span><span>LOCAL BEANS</span><span>WOELANDARI</span>
    </div>
</div>

<section class="stats">
    <div class="stat"><strong><?php echo (int) $menu_count; ?></strong>Menu tersimpan</div>
    <div class="stat"><strong><?php echo (int) $event_count; ?></strong>Event tercatat</div>
    <div class="stat"><strong>2021</strong>Awal eksperimen</div>
</section>
</body>
</html>
