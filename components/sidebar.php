<?php
if (!isset($_SESSION['role'])) return;

$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = ($role == 'admin' || $role == 'superadmin');
$isKaryawan = ($role == 'karyawan');
?>
<aside class="sidebar" id="mainSidebar">
    <div class="brand">
        <div class="brand-logo-container">
            <img src="/woelandari_coffeshop/assets/images/gambar-mentahan/logo.png" alt="Woelandari Coffee Lab Logo" class="brand-logo">
        </div>
        
        <div class="brand-role <?= $isAdmin ? 'role-admin' : 'role-karyawan' ?>">
            <i class="<?= $isAdmin ? 'fas fa-user-shield' : 'fas fa-user' ?>"></i> 
            <span><?= $isAdmin ? 'Admin' : 'Karyawan' ?></span>
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
        
        <!-- Menu AKUN STAFF - Hanya untuk KARYAWAN -->
        <?php if ($isKaryawan): ?>
        <a href="../dashboard/akun_staff.php" class="nav-item <?= ($current_page == 'akun_staff.php') ? 'active' : '' ?>">
            <i class="fas fa-id-card"></i> <span>AKUN STAFF</span>
        </a>
        <?php endif; ?>
        
        <!-- Menu USER MANAJEMEN - Hanya untuk ADMIN/SUPERADMIN -->
        <?php if ($isAdmin): ?>
        <a href="../dashboard/user_manajemen.php" class="nav-item <?= ($current_page == 'user_manajemen.php') ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> <span>USER MANAJEMEN</span>
        </a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 20px; border-top: 2px dashed rgba(0,43,91,0.2);">
            <button id="logoutBtnSidebar" class="nav-item" style="color: #EA4335; background: none; width: 100%; text-align: left; cursor: pointer;">
                <i class="fas fa-sign-out-alt"></i> <span>LOGOUT</span>
            </button>
        </div>
    </nav>
</aside>

<!-- LOGOUT CONFIRMATION MODAL -->
<div id="logoutConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="confirm-modal-body">
            <h3>KELUAR DARI SISTEM?</h3>
            <p>Apakah Anda yakin ingin keluar dari akun ini?</p>
            <div class="user-name-highlight" id="userNameDisplaySidebar"></div>
            <p style="font-size: 0.8rem; color: #999; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> Pastikan semua perubahan sudah disimpan sebelum keluar.
            </p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn-secondary" id="cancelLogoutBtnSidebar" style="box-shadow: 3px 3px 0 var(--navy);">
                <i class="fas fa-times"></i> BATAL
            </button>
            <a href="../logout.php" id="confirmLogoutBtnSidebar" class="btn-secondary" style="background: var(--red); color: white; border: none; box-shadow: 4px 4px 0 var(--navy);">
                <i class="fas fa-sign-out-alt"></i> LOGOUT
            </a>
        </div>
    </div>
</div>

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
   STYLE UNTUK BRAND / LOGO AREA
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

/* Kontainer Logo */
.brand-logo-container {
    width: 100%;
    max-width: 180px;
    height: auto;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.2s ease;
}

.brand-logo {
    width: 100%;
    height: auto;
    object-fit: contain;
}

.brand:hover .brand-logo-container {
    transform: scale(1.03);
}

/* =========================================
   STYLE BARU: BADGE ROLE (SOFT & MODERN)
   ========================================= */
.brand-role {
    margin-top: 15px;
    padding: 6px 14px;
    font-size: 0.8rem;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-weight: 600;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

/* Khusus Style Admin */
.brand-role.role-admin {
    background: rgba(0, 45, 134, 0.06);
    color: #002950;
    border: 1px solid rgba(0, 45, 134, 0.11);
}

/* Khusus Style Karyawan */
.brand-role.role-karyawan {
    background: rgba(0, 45, 134, 0.06);
    color: #002950;
    border: 1px solid rgba(0, 45, 134, 0.11);
}

/* =========================================
   STYLE NAVIGASI
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

/* =========================================
   HOVER & ACTIVE UNTUK ICON KHUSUS
   ========================================= */
.nav-item.active i {
    color: #F8F9FA;
}

.nav-item:hover i {
    transform: scale(1.05);
}

/* =========================================
   STYLE UNTUK MODAL KONFIRMASI LOGOUT
   ========================================= */
.confirm-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 43, 91, 0.85);
    backdrop-filter: blur(8px);
    z-index: 3000;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.confirm-modal-content {
    background: var(--white, #F8F9FA);
    border: 4px solid #002B5B;
    max-width: 450px;
    width: 100%;
    position: relative;
    animation: slideUpFade 0.3s ease;
    box-shadow: 16px 16px 0 #EA4335;
}

@keyframes slideUpFade {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes shakeAnim {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.confirm-modal-header {
    background: #EA4335;
    padding: 20px;
    text-align: center;
    border-bottom: 2px solid #002B5B;
}

.confirm-modal-header i {
    font-size: 4rem;
    color: #F8F9FA;
    text-shadow: 3px 3px 0 #002B5B;
}

.confirm-modal-body {
    padding: 30px;
    text-align: center;
}

.confirm-modal-body h3 {
    font-family: 'Special Elite', cursive;
    font-size: 1.5rem;
    color: #002B5B;
    margin-bottom: 15px;
}

.confirm-modal-body p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
}

.user-name-highlight {
    background: rgba(234, 67, 53, 0.1);
    color: #EA4335;
    font-weight: bold;
    padding: 5px 12px;
    display: inline-block;
    margin: 10px 0;
    border-left: 3px solid #EA4335;
    font-size: 1rem;
    max-width: 100%;
    word-break: break-word;
}

.confirm-modal-footer {
    padding: 20px;
    display: flex;
    gap: 15px;
    justify-content: center;
    border-top: 2px dashed rgba(0,43,91,0.2);
}

.confirm-modal-footer .btn-secondary {
    min-width: 120px;
    background: transparent;
    border: 2px solid #002B5B;
    color: #002B5B;
    padding: 10px 20px;
    cursor: pointer;
    font-family: 'Courier Prime', monospace;
    font-weight: bold;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
    font-size: 0.85rem;
}

.confirm-modal-footer .btn-secondary:hover {
    background: #002B5B;
    color: #F8F9FA;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0 #002B5B;
}

.confirm-modal-content.warning-shake {
    animation: shakeAnim 0.3s ease;
}

@media (max-width: 768px) {
    .sidebar {
        left: -280px;
        width: 260px;
    }
    .sidebar.open {
        left: 0;
    }
    .confirm-modal-footer .btn-secondary {
        min-width: 100px;
        padding: 8px 16px;
    }
}
</style>

<script>
    // Ambil nama user dari session (akan diisi PHP)
    const sessionUserName = '<?php echo isset($_SESSION['nama_lengkap']) ? htmlspecialchars($_SESSION['nama_lengkap']) : (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'); ?>';
    
    // ========== LOGOUT CONFIRMATION MODAL ==========
    const logoutModal = document.getElementById('logoutConfirmModal');
    const logoutBtnSidebar = document.getElementById('logoutBtnSidebar');
    const confirmLogoutBtnSidebar = document.getElementById('confirmLogoutBtnSidebar');
    const cancelLogoutBtnSidebar = document.getElementById('cancelLogoutBtnSidebar');
    const userNameDisplaySidebar = document.getElementById('userNameDisplaySidebar');

    // Set nama user di modal
    if (userNameDisplaySidebar) {
        userNameDisplaySidebar.textContent = sessionUserName;
    }

    if (logoutBtnSidebar) {
        logoutBtnSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            if (logoutModal) {
                logoutModal.style.display = 'flex';
                
                const modalContent = logoutModal.querySelector('.confirm-modal-content');
                if (modalContent) {
                    modalContent.classList.add('warning-shake');
                    setTimeout(() => {
                        modalContent.classList.remove('warning-shake');
                    }, 300);
                }
            }
        });
    }

    if (cancelLogoutBtnSidebar) {
        cancelLogoutBtnSidebar.addEventListener('click', function() {
            if (logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && logoutModal && logoutModal.style.display === 'flex') {
            logoutModal.style.display = 'none';
        }
    });
</script>