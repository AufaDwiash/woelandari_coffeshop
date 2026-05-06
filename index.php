<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woelandari Coffee Lab</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Courier+Prime:wght@400;700&family=Montserrat:wght@400;700;900&family=Special+Elite&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/about_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/menu_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/community_style.css?v=<?php echo time(); ?>">

    <style>
    html { scroll-behavior: smooth; }

    /* Hanya targetkan pembungkus halaman utama */
    .main-page {
        border-bottom: 4px solid #000000; /* Garis hitam tegas */
        position: relative;
        display: block;
        width: 100%;
    }

    /* Hilangkan garis di section paling bawah (Menu) */
    .main-page:last-of-type {
        border-bottom: none;
    }
</style>

</head>
    <body>

        <section id="home" class="main-page">
            <?php include 'home.php'; ?>
        </section>
        
        <section id="about" class="main-page">
            <?php include 'about.php'; ?>
        </section>

        <section id="community" class="main-page">
            <?php include 'community.php'; ?>
        </section>

        <section id="menu" class="main-page">
            <?php include 'menu.php'; ?>
        </section>

    </body>
</html>