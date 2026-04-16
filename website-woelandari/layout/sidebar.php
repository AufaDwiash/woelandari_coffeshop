<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<aside class="sidebar">
    <div class="sidebar-brand">
        WOELANDARI<span>.LAB</span>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                DASHBOARD
            </a>
        </li>
        <li>
            <a href="menu_crud.php" class="<?= ($current_page == 'menu_crud.php') ? 'active' : ''; ?>">
                KELOLA MENU
            </a>
        </li>
        <li>
            <a href="gallery_crud.php" class="<?= ($current_page == 'gallery_crud.php') ? 'active' : ''; ?>">
                GALLERY ARCHIVE
            </a>
        </li>
        <li>
            <a href="user_manajemen.php" class="<?= ($current_page == 'user_manajemen.php') ? 'active' : ''; ?>">
                USER MANAJEMEN
            </a>
        </li>
    </ul>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="../logout.php" style="color: var(--accent-red); text-decoration: none; font-family: 'Courier Prime'; font-size: 0.75rem; font-weight: bold;">
            >> TERMINATE_SESSION
        </a>
    </div>
</aside>