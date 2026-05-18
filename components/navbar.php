<!-- Navbar Woelandari - Clean, Premium, Responsive -->
<nav class="woe-navbar" id="woeNavbar">
    <div class="woe-nav-container">
        <!-- Logo sebagai tombol login tersembunyi -->
        <a href="login.php" class="woe-logo" title="Admin Access">
            <img src="assets/images/gambar-mentahan/logo.png" alt="Woelandari Coffee Lab">
        </a>

        <!-- Menu navigasi -->
        <ul class="woe-nav-menu" id="woeNavMenu">
            <li class="woe-nav-item"><a href="index.php#home">HOME</a></li>
            <li class="woe-nav-item"><a href="index.php#menu">MENU</a></li>
            
           
            
            <li class="woe-nav-item"><a href="index.php#reservasi">RESERVASI</a></li>
            <li class="woe-nav-item"><a href="index.php#rating">rating</a></li>
             <li class="woe-nav-item"><a href="gallery.php">GALLERY</a></li>
            <li class="woe-nav-item"><a href="lokasi_full.php">LOKASI</a></li>
        </ul>

        <!-- Mobile toggle button -->
        <div class="woe-menu-toggle" id="woeMenuToggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<style>
    /* ========== NAVBAR STYLE ========== */
    .woe-navbar {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 1200px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        border-radius: 60px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        padding: 8px 24px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 43, 91, 0.15);
    }

    /* Efek saat scroll (jika diperlukan, bisa ditambahkan class scrolled via JS) */
    .woe-navbar.scrolled {
        top: 0;
        border-radius: 0 0 30px 30px;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .woe-nav-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Logo */
    .woe-logo {
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: transform 0.25s ease;
    }
    .woe-logo img {
        height: 45px;
        width: auto;
        display: block;
    }
    .woe-logo:hover {
        transform: scale(1.02);
        opacity: 0.9;
    }

    /* Menu list */
    .woe-nav-menu {
        display: flex;
        gap: 2rem;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }

    .woe-nav-item a {
        text-decoration: none;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 1px;
        color: #002B5B;
        padding: 8px 0;
        position: relative;
        transition: color 0.25s ease;
        text-transform: uppercase;
    }

    /* Hover & Active underline animation */
    .woe-nav-item a::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 0;
        height: 2px;
        background: #EA4335;
        transition: width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        border-radius: 2px;
    }

    .woe-nav-item a:hover,
    .woe-nav-item.active a {
        color: #EA4335;
    }

    .woe-nav-item a:hover::after,
    .woe-nav-item.active a::after {
        width: 100%;
    }

    /* Mobile toggle button */
    .woe-menu-toggle {
        display: none;
        font-size: 1.5rem;
        color: #002B5B;
        cursor: pointer;
        transition: color 0.2s;
        background: none;
        border: none;
        padding: 8px;
    }
    .woe-menu-toggle:hover {
        color: #EA4335;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 850px) {
        .woe-navbar {
            width: 95%;
            padding: 8px 20px;
            top: 15px;
        }
        .woe-menu-toggle {
            display: block;
        }
        .woe-nav-menu {
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            flex-direction: column;
            gap: 0;
            padding: 16px 0;
            border-radius: 28px;
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 43, 91, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 999;
        }
        .woe-nav-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .woe-nav-item {
            width: 100%;
            text-align: center;
        }
        .woe-nav-item a {
            display: block;
            padding: 12px 20px;
            font-size: 0.9rem;
        }
        .woe-nav-item a::after {
            display: none;
        }
        .woe-nav-item a:hover,
        .woe-nav-item.active a {
            background: rgba(234, 67, 53, 0.08);
            color: #EA4335;
        }
    }

    @media (max-width: 480px) {
        .woe-navbar {
            width: 96%;
            padding: 6px 16px;
        }
        .woe-logo img {
            height: 38px;
        }
        .woe-nav-item a {
            font-size: 0.8rem;
            padding: 10px 16px;
        }
    }
</style>

<script>
    (function() {
        // Navbar scroll effect
        const navbar = document.getElementById('woeNavbar');
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }

        // Mobile menu toggle
        const toggleBtn = document.getElementById('woeMenuToggle');
        const menu = document.getElementById('woeNavMenu');
        if (toggleBtn && menu) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('show');
                // Change icon (optional, but keep consistency)
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    if (menu.classList.contains('show')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });
        }

        // Close mobile menu when clicking a link
        document.querySelectorAll('.woe-nav-item a').forEach(link => {
            link.addEventListener('click', function() {
                if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    const icon = toggleBtn?.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });
        });

        // Active menu highlight on scroll (optional, keep existing logic)
        function setActiveMenu() {
            const sections = document.querySelectorAll('section[id]');
            if (!sections.length) return;
            const scrollPos = window.scrollY + 120;
            let currentId = '';
            sections.forEach(section => {
                const top = section.offsetTop;
                const bottom = top + section.offsetHeight;
                if (scrollPos >= top && scrollPos < bottom) {
                    currentId = section.getAttribute('id');
                }
            });
            document.querySelectorAll('.woe-nav-item').forEach(item => {
                item.classList.remove('active');
                const link = item.querySelector('a');
                if (link && link.getAttribute('href') === `#${currentId}`) {
                    item.classList.add('active');
                }
            });
        }
        window.addEventListener('scroll', setActiveMenu);
        setActiveMenu();
    })();
</script>