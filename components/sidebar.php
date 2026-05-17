<?php
if (!isset($_SESSION['role'])) return;

$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = ($role == 'admin' || $role == 'superadmin');
?>
<aside class="sidebar" id="mainSidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="fas fa-mug-hot"></i>
        </div>
        <div class="brand-text">
            WOELANDARI<br>
            <span>COFFEE LAB.</span>
        </div>
        <div class="brand-role">
        <i class="fas fa-id-badge"></i> <?= $isAdmin ? 'ADMIN' : 'KARYAWAN' ?>
        </div>
    </div>

    <nav class="nav-list">
        <a href="../dashboard/dashboard.php" class="nav-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-user"></i> <span>DASHBOARD</span>
        </a>
        <a href="../dashboard/menu_crud.php" class="nav-item <?= ($current_page == 'menu_crud.php') ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> <span>MENU</span>
        </a>
        <a href="../dashboard/gallery_crud.php" class="nav-item <?= ($current_page == 'gallery_crud.php') ? 'active' : '' ?>">
            <i class="fas fa-images"></i> <span>GALLERY</span>
        </a>
        <a href="../dashboard/rating_crud.php" class="nav-item <?= ($current_page == 'rating_crud.php') ? 'active' : '' ?>">
            <i class="fas fa-star"></i> <span>FEEDBACK</span>
        </a>
        <a href="../dashboard/community_crud.php" class="nav-item <?= ($current_page == 'community_crud.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>COMMUNITY</span>
        </a>
        
        <?php if ($isAdmin): ?>
        <a href="../dashboard/user_manajemen.php" class="nav-item <?= ($current_page == 'user_manajemen.php') ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> <span>USER MANAJEMEN</span>
        </a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 20px; border-top: 2px dashed rgba(0,43,91,0.2);">
            <a href="../logout.php" class="nav-item" style="color: #EA4335;">
                <i class="fas fa-sign-out-alt"></i> <span>LOGOUT</span>
            </a>
        </div>
    </nav>
</aside>

<style>
/* Sidebar Utama */
.sidebar {
    width: 260px;
    background: #F8F9FA;
    border-right: 3px solid #002B5B;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    transition: left 0.3s ease;
}

/* =========================================
   STYLE BARU UNTUK BRAND / LOGO AREA
   ========================================= */
.brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    border-bottom: 4px solid #002B5B; 
    padding-bottom: 25px;
    margin-bottom: 30px;
    text-align: center;
    position: relative;
}

/* Icon Kotak ala Brutalist */
.brand-icon {
    background: #EA4335; /* Warna Merah */
    color: #F8F9FA;
    width: 55px;
    height: 55px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.8rem;
    border: 3px solid #002B5B;
    box-shadow: 4px 4px 0 #002B5B;
    margin-bottom: 15px;
    transform: rotate(-3deg); /* Sedikit miring agar artsy */
    transition: transform 0.2s ease;
}
.brand:hover .brand-icon {
    transform: rotate(0deg) scale(1.05);
}

/* Teks Woelandari */
.brand-text {
    font-family: 'Special Elite', cursive;
    font-size: 1.5rem;
    color: #002B5B;
    line-height: 1.1;
    letter-spacing: 1px;
}
.brand-text span {
    font-size: 0.8rem;
    display: block;
    font-family: 'Courier Prime', monospace;
    font-weight: bold;
    margin-top: 5px;
    letter-spacing: 3px;
}

/* Badge Role (Admin/Staff) */
.brand-role {
    margin-top: 15px;
    background: #002B5B;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.75rem;
    font-family: 'Courier Prime', monospace;
    font-weight: bold;
    border: 2px dashed #F8F9FA;
    box-shadow: 0 0 0 3px #002B5B; 
    display: flex;
    align-items: center;
    gap: 6px;
}

/* =========================================
   STYLE NAVIGASI (Tetap sama)
   ========================================= */
.nav-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}
.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #002B5B;
    text-decoration: none;
    font-family: 'Courier Prime', monospace;
    font-weight: bold;
    font-size: 0.9rem;
    border: 2px solid transparent;
    transition: all 0.2s ease;
}
.nav-item i {
    width: 25px;
    font-size: 1.1rem;
    margin-right: 10px;
}
.nav-item:hover {
    background: rgba(0, 43, 91, 0.05);
    border: 2px dashed #002B5B;
    transform: translateX(5px);
}
.nav-item.active {
    background: #002B5B;
    color: #F8F9FA;
    border: 2px solid #002B5B;
    box-shadow: 4px 4px 0 #EA4335;
}

@media (max-width: 768px) {
    .sidebar {
        left: -280px;
        width: 260px;
    }
    .sidebar.open {
        left: 0;
    }
}
</style>