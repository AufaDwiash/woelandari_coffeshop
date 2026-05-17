<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Woelandari Coffee Lab</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Courier+Prime:wght@400;700&family=Montserrat:wght@400;700;900&family=Special+Elite&display=swap" rel="stylesheet">
    
    <?php
    // Helper function untuk cache busting dengan filemtime
    function get_css_version($file) {
        $path = 'assets/css/' . $file;
        if (file_exists($path)) {
            return filemtime($path);
        }
        return time();
    }
    ?>
    
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo get_css_version('home_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/about_style.css?v=<?php echo get_css_version('about_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/menu_style.css?v=<?php echo get_css_version('menu_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/reservasi_style.css?v=<?php echo get_css_version('reservasi_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/community_style.css?v=<?php echo get_css_version('community_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/rating_style.css?v=<?php echo get_css_version('rating_style.css'); ?>">
    <link rel="stylesheet" href="assets/css/lokasi_style.css?v=<?php echo get_css_version('lokasi_style.css'); ?>">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<!-- ========== SECTIONS ========== -->
<section id="home" class="main-page">
    <?php if (file_exists('home.php')) include 'home.php'; else echo '<div class="coming-soon">Home section coming soon...</div>'; ?>
</section>
<section id="about" class="main-page">
    <?php if (file_exists('about.php')) include 'about.php'; else echo '<div class="coming-soon">About section coming soon...</div>'; ?>
</section>
<section id="menu" class="main-page">
    <?php if (file_exists('menu.php')) include 'menu.php'; else echo '<div class="coming-soon">Menu section coming soon...</div>'; ?>
</section>
<section id="community" class="main-page">
    <?php if (file_exists('community.php')) include 'community.php'; else echo '<div class="coming-soon">Community section coming soon...</div>'; ?>
</section>
<section id="reservasi" class="main-page">
    <?php if (file_exists('reservasi.php')) include 'reservasi.php'; else echo '<div class="coming-soon">Reservasi section coming soon...</div>'; ?>
</section>
<section id="rating" class="main-page">
    <?php if (file_exists('rating.php')) include 'rating.php'; else echo '<div class="coming-soon">Rating section coming soon...</div>'; ?>
</section>
<section id="lokasi" class="main-page">
    <?php if (file_exists('lokasi.php')) include 'lokasi.php'; else echo '<div class="coming-soon">Lokasi section coming soon...</div>'; ?>
</section>
</body>
</html>