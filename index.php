<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woelandari Coffee Lab</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Courier+Prime:wght@400;700&family=Montserrat:wght@400;700;900&family=Special+Elite&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/about_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/menu_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/reservasi_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/community_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/lokasi_style.css?v=<?php echo time(); ?>">


</head>
    <body>

        <section id="home" class="main-page">
            <?php include 'home.php'; ?>
        </section>
        
        <section id="about" class="main-page">
            <?php include 'about.php'; ?>
        </section>

        <section id="menu" class="main-page">
            <?php include 'menu.php'; ?>
        </section>

         <section id="community" class="main-page">
            <?php include 'community.php'; ?>
        </section>

        <section id="reservasi" class="main-page">
            <?php include 'reservasi.php'; ?>
        </section>

        <section id="lokasi" class="main-page">
            <?php include 'lokasi.php'; ?>
        </section>

    </body>
</html>