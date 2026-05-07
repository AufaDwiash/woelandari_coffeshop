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
    
    <!-- CSS lainnya -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/about_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/menu_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/reservasi_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/community_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/lokasi_style.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="capsule-navbar" id="mainNav">
    <div class="nav-container">
        
        <!-- Mobile Toggle -->
        <div class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Nav Menu -->
        <ul class="nav-links" id="navLinks">
            <li><a href="#home" class="nav-item">HOME</a></li>
            <li><a href="#about" class="nav-item">ABOUT</a></li>
            <li><a href="#menu" class="nav-item">MENU</a></li>
            <li><a href="#community" class="nav-item">COMMUNITY</a></li>
            <li><a href="#reservasi" class="nav-item">RESERVASI</a></li>
            <li><a href="#lokasi" class="nav-item">LOKASI</a></li>
        </ul>

        <!-- Logo (Login tersembunyi) -->
        <div class="nav-logo">
            <a href="login.php" title="Admin Login">
                <img src="assets/images/gambar-mentahan/logo.png" alt="Woelandari Coffee Lab">
            </a>
        </div>
    </div>
</nav>

<script>
(function() {
    'use strict';

    // Element references
    const navbar = document.getElementById('mainNav');
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('section.main-page, section[id]');

    // ========== 1. SCROLL EFFECT ==========
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ========== 2. SMOOTH SCROLL ==========
    document.querySelectorAll('.nav-links a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offset = 90;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu
                if (navLinks) {
                    navLinks.classList.remove('show');
                }
            }
        });
    });

    // ========== 3. MOBILE MENU TOGGLE ==========
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinks.classList.toggle('show');
        });
    }

    // ========== 4. CLOSE MENU WHEN CLICK OUTSIDE ==========
    if (navLinks && menuToggle) {
        document.addEventListener('click', function(event) {
            if (navLinks.classList.contains('show') && 
                !navLinks.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        });
    }

    // ========== 5. CLOSE MENU ON WINDOW RESIZE (if screen > 850px) ==========
    window.addEventListener('resize', function() {
        if (window.innerWidth > 850 && navLinks) {
            navLinks.classList.remove('show');
        }
    });

    // ========== 6. ACTIVE MENU DETECTION ==========
    function setActiveMenu() {
        if (!sections.length || !navItems.length) return;
        
        let current = '';
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                current = section.getAttribute('id');
            }
        });
        
        navItems.forEach(item => {
            item.classList.remove('active');
            const href = item.getAttribute('href');
            if (href && href === `#${current}`) {
                item.classList.add('active');
            }
        });
    }
    
    if (sections.length && navItems.length) {
        window.addEventListener('scroll', setActiveMenu);
        window.addEventListener('load', setActiveMenu);
        setActiveMenu();
    }

    // ========== 7. DROPDOWN MOBILE HANDLER ==========
    const dropdowns = document.querySelectorAll('.dropdown');
    if (dropdowns.length) {
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 850) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropdown.classList.toggle('open');
                    }
                });
            }
        });
    }

})();
</script>

<!-- SECTIONS -->
<section id="home" class="main-page"><?php include 'home.php'; ?></section>
<section id="about" class="main-page"><?php include 'about.php'; ?></section>
<section id="menu" class="main-page"><?php include 'menu.php'; ?></section>
<section id="community" class="main-page"><?php include 'community.php'; ?></section>
<section id="reservasi" class="main-page"><?php include 'reservasi.php'; ?></section>
<section id="lokasi" class="main-page"><?php include 'lokasi.php'; ?></section>

</body>
</html>