<?php
// dashboard/community_crud.php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ========== KONFIGURASI PAGINATION & FILTER ==========
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) && in_array($_GET['status'], ['active', 'hidden']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];
if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where[] = "(name LIKE '%$safe_search%' OR role LIKE '%$safe_search%')";
}
if ($status_filter) {
    $where[] = "status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}
$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Hitung total data
$countQuery = "SELECT COUNT(*) as total FROM human_archive $whereSql";
$totalResult = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

// Ambil data dengan pagination
$query = "SELECT * FROM human_archive $whereSql ORDER BY display_order ASC, id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// ========== PROSES TAMBAH DATA ==========
if (isset($_POST['add_human'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = (int)$_POST['display_order'];
    $status = 'active';

    $foto_nama = '';
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.png';
        file_put_contents('../assets/images/community/' . $foto_nama, $img_base64);
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/community/' . $foto_nama);
    } else {
        $foto_nama = 'default.jpg';
    }

    mysqli_query($conn, "INSERT INTO human_archive (name, role, quote, image, display_order, status) 
                          VALUES ('$name', '$role', '$quote', '$foto_nama', '$order', '$status')");
    
    $msg = "✅ Anggota Komunitas berhasil ditambahkan!";
    $redirect = "community_crud.php?msg=" . urlencode($msg) . "&page=$page" . ($search ? "&search=" . urlencode($search) : "") . ($status_filter ? "&status=" . urlencode($status_filter) : "");
    header("Location: $redirect");
    exit;
}

// ========== UPDATE DATA ==========
if (isset($_POST['update_human'])) {
    $id = (int)$_POST['id_human'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = (int)$_POST['display_order'];
    $foto_lama = $_POST['foto_lama'] ?? '';
    
    $foto_nama = $foto_lama;
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.png';
        file_put_contents('../assets/images/community/' . $foto_nama, $img_base64);
        if ($foto_lama && $foto_lama != 'default.jpg' && file_exists('../assets/images/community/' . $foto_lama)) {
            unlink('../assets/images/community/' . $foto_lama);
        }
    }

    mysqli_query($conn, "UPDATE human_archive SET name='$name', role='$role', quote='$quote', display_order='$order', image='$foto_nama' WHERE id=$id");
    
    $msg = " Data Anggota berhasil diperbarui!";
    $redirect = "community_crud.php?msg=" . urlencode($msg) . "&page=$page" . ($search ? "&search=" . urlencode($search) : "") . ($status_filter ? "&status=" . urlencode($status_filter) : "");
    header("Location: $redirect");
    exit;
}

// ========== UPDATE URUTAN (via form biasa, tanpa AJAX) ==========
if (isset($_POST['update_order'])) {
    if (!empty($_POST['order'])) {
        foreach ($_POST['order'] as $id => $order_val) {
            $id = (int)$id;
            $order_val = (int)$order_val;
            mysqli_query($conn, "UPDATE human_archive SET display_order='$order_val' WHERE id='$id'");
        }
        $msg = " Urutan tampil berhasil diperbarui!";
    }
    $redirect = "community_crud.php?msg=" . urlencode($msg) . "&page=$page" . ($search ? "&search=" . urlencode($search) : "") . ($status_filter ? "&status=" . urlencode($status_filter) : "");
    header("Location: $redirect");
    exit;
}

// ========== TOGGLE STATUS ==========
if (isset($_GET['toggle']) && isset($_GET['current'])) {
    $id = (int)$_GET['toggle'];
    $current = $_GET['current'];
    $new = ($current == 'active') ? 'hidden' : 'active';
    mysqli_query($conn, "UPDATE human_archive SET status='$new' WHERE id='$id'");
    $msg = ($new == 'active') ? "👁️ Anggota sekarang ditampilkan!" : "🙈 Anggota disembunyikan!";
    $redirect = "community_crud.php?msg=" . urlencode($msg) . "&page=$page" . ($search ? "&search=" . urlencode($search) : "") . ($status_filter ? "&status=" . urlencode($status_filter) : "");
    header("Location: $redirect");
    exit;
}

// ========== HAPUS DATA ==========
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $f = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM human_archive WHERE id=$id"));
    if ($f && $f['image'] != 'default.jpg' && file_exists('../assets/images/community/' . $f['image'])) {
        unlink('../assets/images/community/' . $f['image']);
    }
    mysqli_query($conn, "DELETE FROM human_archive WHERE id=$id");
    $msg = " Data Anggota berhasil dihapus dari arsip!";
    $redirect = "community_crud.php?msg=" . urlencode($msg) . "&page=$page" . ($search ? "&search=" . urlencode($search) : "") . ($status_filter ? "&status=" . urlencode($status_filter) : "");
    header("Location: $redirect");
    exit;
}

// ========== AMBIL DATA EDIT ==========
$edit_mode = false;
$edit_id = $edit_name = $edit_role = $edit_quote = $edit_order = $edit_status = $edit_image = "";
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM human_archive WHERE id=$id");
    $d = mysqli_fetch_assoc($q);
    if ($d) {
        $edit_mode = true;
        $edit_id = $d['id'];
        $edit_name = $d['name'];
        $edit_role = $d['role'];
        $edit_quote = $d['quote'];
        $edit_order = $d['display_order'];
        $edit_status = $d['status'];
        $edit_image = $d['image'];
    }
}

// Notifikasi flash message
$msg_display = '';
if (isset($_GET['msg'])) {
    $msg_display = htmlspecialchars(urldecode($_GET['msg']));
}

// Fungsi render tabel dan pagination (untuk AJAX)
function renderCommunityTable($result, $page, $totalPages, $search, $status_filter) {
    ob_start();
    ?>
    <div class="table-container">
        <form action="" method="POST" id="orderForm" class="order-form-inline">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align: center;">FOTO</th>
                        <th>NAMA & PERAN</th>
                        <th style="text-align: center;">URUTAN</th>
                        <th style="text-align: center;">STATUS</th>
                        <th style="text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): while($row = mysqli_fetch_assoc($result)): 
                        $status_class = ($row['status'] == 'active') ? 'status-active' : 'status-hidden';
                        $toggle_icon = ($row['status'] == 'active') ? 'fa-eye' : 'fa-eye-slash';
                        $toggle_title = ($row['status'] == 'active') ? 'Sembunyikan' : 'Tampilkan';
                        $toggle_text = ($row['status'] == 'active') ? 'SEMBUNYI' : 'TAMPIL';
                    ?>
                        <tr>
                            <td style="text-align: center;">
                                <img src="../assets/images/community/<?= $row['image'] ?>" class="thumb-img" onerror="this.src='../assets/images/menu/default.jpg'">
                            </td>
                            <td>
                                <strong style="color: var(--navy); font-size: 1rem;"><?= htmlspecialchars($row['name']) ?></strong><br>
                                <span style="color: var(--red); font-size: 0.75rem; font-weight: bold;">[ <?= strtoupper(htmlspecialchars($row['role'])) ?> ]</span>
                                <?php if (!empty($row['quote'])): ?>
                                    <br><small style="opacity: 0.8;">"<?= htmlspecialchars(substr($row['quote'],0,50)) ?>"</small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" name="order[<?= $row['id'] ?>]" value="<?= $row['display_order'] ?>" class="order-input">
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?= $status_class ?>"><?= strtoupper($row['status']) ?></span>
                            </td>
                            <td style="text-align: center;">
                                <div class="action-buttons">
                                    <a href="?edit=<?= $row['id'] ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $page ?>" class="btn-action btn-edit-action">
                                        <i class="fas fa-pencil-alt"></i> EDIT
                                    </a>
                                    <a href="?toggle=<?= $row['id'] ?>&current=<?= $row['status'] ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $page ?>" class="btn-action btn-toggle-action">
                                        <i class="fas <?= $toggle_icon ?>"></i> <?= $toggle_text ?>
                                    </a>
                                    <button type="button" class="btn-action btn-delete-action delete-btn" 
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-role="<?= htmlspecialchars($row['role']) ?>"
                                        data-search="<?= htmlspecialchars($search) ?>"
                                        data-status="<?= htmlspecialchars($status_filter) ?>"
                                        data-page="<?= $page ?>">
                                        <i class="fas fa-trash-alt"></i> HAPUS
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:40px;"><i class="fas fa-users-slash"></i> [ DATA ANGGOTA KOSONG ]</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="text-align: left; margin-top: 15px;">
                <button type="submit" name="update_order" class="btn btn-secondary" style="width: auto;">
                    <i class="fas fa-save"></i> SIMPAN URUTAN TAMPIL
                </button>
            </div>
        </form>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination-area">
        <button class="btn btn-secondary pagi-prev" data-page="<?= $page-1 ?>" <?= $page<=1?'disabled':'' ?>>
            <i class="fas fa-chevron-left"></i> PREV
        </button>
        <span>HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
        <button class="btn btn-secondary pagi-next" data-page="<?= $page+1 ?>" <?= $page>=$totalPages?'disabled':'' ?>>
            NEXT <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// Jika AJAX request, kirim hanya tabel + pagination
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    echo renderCommunityTable($result, $page, $totalPages, $search, $status_filter);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Komunitas - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard/community_crud.css">
    <style>
        /* Tambahan untuk konsistensi pagination dan alert */
        .pagination-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px dashed var(--navy);
            font-weight: bold;
            gap: 15px;
        }
        .pagination-area .btn { min-width: 110px; }
        @media (max-width: 768px) {
            .pagination-area { flex-wrap: wrap; justify-content: center; gap: 10px; }
            .pagination-area .btn { min-width: 90px; font-size: 0.8rem; }
            .pagination-area span { order: -1; width: 100%; text-align: center; }
        }
        .alert-msg {
            background: #fff9c4;
            border: 2px dashed #e0d68c;
            padding: 10px 15px;
            margin-bottom: 25px;
            font-weight: bold;
            border-left: 5px solid var(--red);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .order-form-inline .btn-secondary {
            margin-top: 15px;
        }
        .filter-area {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
            background: rgba(0, 43, 91, 0.03);
            padding: 15px;
            border: 2px solid var(--navy);
        }
        .search-wrapper { flex: 2; position: relative; min-width: 180px; height: 46px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--navy); }
        .search-input {
            width: 100%; height: 100%; padding: 10px 10px 10px 40px;
            border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime', monospace; font-weight: bold; font-size: 0.9rem; outline: none;
        }
        .status-wrapper { flex: 1; min-width: 150px; height: 46px; }
        .status-select {
            width: 100%; height: 100%; padding: 0 12px;
            border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime', monospace; font-weight: bold; font-size: 0.9rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="overlay" id="sidebarOverlay"></div>
<?php include "../components/sidebar.php"; ?>
<main class="main-wrapper">
    <div class="mobile-header">
        <div><i class="fas fa-users" style="color: var(--red);"></i> WOELANDARI</div>
        <button id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem;"><i class="fas fa-bars"></i></button>
    </div>
    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header"><span><i class="fas fa-folder-open"></i> Kelola Komunitas</span><span>DATE: <?= date('d/m/Y') ?></span></div>
        
        <?php if ($msg_display): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg_display ?></div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h1 class="title-main" style="margin-bottom:0;">ARSIP ANGGOTA</h1>
            <button class="btn btn-primary" id="showModalBtn" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);"><i class="fas fa-plus"></i> ADD MEMBER</button>
        </div>

        <!-- Filter Area -->
        <div class="filter-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau role..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="status-wrapper">
                <select id="statusFilter" class="status-select">
                    <option value="">SEMUA STATUS</option>
                    <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>ACTIVE</option>
                    <option value="hidden" <?= $status_filter == 'hidden' ? 'selected' : '' ?>>HIDDEN</option>
                </select>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" id="filterBtn"><i class="fas fa-filter"></i> FILTER</button>
                <?php if ($search || $status_filter): ?>
                    <a href="community_crud.php" class="btn btn-secondary"><i class="fas fa-undo-alt"></i> RESET</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tempat tabel & pagination -->
        <div id="communityContent">
            <?= renderCommunityTable($result, $page, $totalPages, $search, $status_filter) ?>
        </div>
    </section>
</main>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="confirm-modal-body">
            <h3>HAPUS ANGGOTA?</h3>
            <p>Apakah Anda yakin ingin menghapus anggota berikut?</p>
            <div class="member-name-highlight" id="memberNameToDelete"></div>
            <div id="memberRoleToDelete" style="font-size:0.8rem; color:#999;"></div>
            <p style="font-size:0.8rem; margin-top:15px;"><i class="fas fa-info-circle"></i> Data dihapus tidak dapat dikembalikan!</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">BATAL</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">HAPUS</a>
        </div>
    </div>
</div>

<!-- Modal Add/Edit -->
<div id="modalCommunity" class="modal">
    <div class="modal-content">
        <div class="tape" style="top:-16px; width:100px; height:25px;"></div>
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px;"><span id="modalTitle"><?= $edit_mode ? 'UPDATE ARSIP ANGGOTA' : 'TAMBAH ANGGOTA BARU' ?></span></div>
        </div>
        <div class="modal-body-scroll">
            <form id="mainForm" action="" method="POST" enctype="multipart/form-data">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id_human" value="<?= $edit_id ?>">
                <?php endif; ?>
                <input type="hidden" name="foto_lama" id="fotoLama" value="<?= htmlspecialchars($edit_image) ?>">
                <input type="hidden" name="foto_cropped" id="fotoCropped" value="">
                
                <div class="form-group">
                    <label class="form-label">NAMA LENGKAP</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($edit_name) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ROLE / JABATAN</label>
                    <input type="text" name="role" class="form-input" value="<?= htmlspecialchars($edit_role) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">QUOTE (OPSIONAL)</label>
                    <textarea name="quote" class="form-input" rows="3"><?= htmlspecialchars($edit_quote) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">URUTAN TAMPIL (ANGKA)</label>
                    <input type="number" name="display_order" class="form-input" value="<?= $edit_order ?: 1 ?>" required>
                </div>
                <div class="upload-box-safe">
                    <label class="form-label">UPLOAD FOTO (RASIO 1:1)</label>
                    <?php if ($edit_mode && $edit_image && $edit_image != 'default.jpg'): ?>
                        <div style="margin-bottom:15px;">
                            <img src="../assets/images/community/<?= $edit_image ?>" style="width:80px; height:80px; object-fit:cover; border:2px solid var(--navy);">
                            <p style="font-size:0.75rem;">[ FOTO SAAT INI ]</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imageInput" name="image" accept="image/*">
                    <p style="font-size:0.75rem; margin-top:10px; color:var(--red);">* Pilih gambar, crop otomatis muncul</p>
                    <div id="previewArea" style="display:none; margin-top:15px;">
                        <img id="previewImage" style="max-width:120px; border:2px solid var(--navy);">
                        <p style="color:green; font-weight:bold;">✓ GAMBAR SIAP</p>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:25px;">
                    <button type="button" class="btn btn-secondary" id="cancelModalBtn">BATAL</button>
                    <button type="submit" name="<?= $edit_mode ? 'update_human' : 'add_human' ?>" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cropModal" class="modal" style="z-index:2100;">
    <div class="modal-content" style="max-width:460px; padding:20px;">
        <div class="spec-header" style="margin-bottom:15px;">CROP GAMBAR (1:1)</div>
        <div style="border:2px solid var(--navy); background:#000;"><img id="cropImage" src="" style="max-width:100%;"></div>
        <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:20px;">
            <button class="btn btn-secondary" id="cancelCropBtn">BATAL</button>
            <button class="btn btn-primary" id="cropBtn" style="background:var(--red);">CROP & SET</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let currentSearch = '<?= addslashes($search) ?>';
    let currentStatus = '<?= addslashes($status_filter) ?>';
    let currentPage = <?= $page ?>;

    // Sidebar
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btn) btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

    // AJAX load data
    function loadCommunityData(page, search, status) {
        document.getElementById('communityContent').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
        let url = `community_crud.php?ajax=1&page=${page}`;
        if(search) url += `&search=${encodeURIComponent(search)}`;
        if(status) url += `&status=${encodeURIComponent(status)}`;
        fetch(url).then(res => res.text()).then(html => {
            document.getElementById('communityContent').innerHTML = html;
            attachDeleteEvents(); attachPaginationEvents();
            currentSearch = search; currentStatus = status; currentPage = page;
            let newUrl = `community_crud.php?page=${page}` + (search?`&search=${encodeURIComponent(search)}`:'') + (status?`&status=${encodeURIComponent(status)}`:'');
            window.history.pushState({}, '', newUrl);
        }).catch(err => { console.error(err); document.getElementById('communityContent').innerHTML = '<div style="text-align:center;padding:40px;color:red;">Gagal memuat data.</div>'; });
    }

    // Filter events
    document.getElementById('filterBtn')?.addEventListener('click', () => {
        loadCommunityData(1, document.getElementById('searchInput').value, document.getElementById('statusFilter').value);
    });
    document.getElementById('searchInput')?.addEventListener('keypress', e => { if(e.key==='Enter') document.getElementById('filterBtn').click(); });

    function attachPaginationEvents() {
        document.querySelectorAll('.pagi-prev, .pagi-next').forEach(btn => {
            btn.removeEventListener('click', pagiHandler);
            btn.addEventListener('click', pagiHandler);
        });
    }
    function pagiHandler(e) {
        let btn = e.currentTarget;
        if(btn.disabled) return;
        let page = btn.dataset.page;
        if(page) loadCommunityData(parseInt(page), currentSearch, currentStatus);
    }

    // Delete modal
    function attachDeleteEvents() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.removeEventListener('click', deleteHandler);
            btn.addEventListener('click', deleteHandler);
        });
    }
    function deleteHandler(e) {
        e.preventDefault();
        let id = this.dataset.id, name = this.dataset.name, role = this.dataset.role || '', search = this.dataset.search||'', status = this.dataset.status||'', page = this.dataset.page||'1';
        document.getElementById('memberNameToDelete').innerText = name;
        let roleSpan = document.getElementById('memberRoleToDelete');
        if(role) { roleSpan.innerText = `[${role}]`; roleSpan.style.display = 'block'; } else roleSpan.style.display = 'none';
        let url = `?delete=${id}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&page=${page}`;
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteConfirmModal').style.display = 'flex';
    }
    document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => document.getElementById('deleteConfirmModal').style.display = 'none');
    document.getElementById('deleteConfirmModal')?.addEventListener('click', e => { if(e.target === document.getElementById('deleteConfirmModal')) document.getElementById('deleteConfirmModal').style.display = 'none'; });

    // Modal Add/Edit
    const modal = document.getElementById('modalCommunity');
    const showModalBtn = document.getElementById('showModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    if(showModalBtn) showModalBtn.onclick = () => { modal.style.display = 'flex'; };
    if(cancelModalBtn) cancelModalBtn.onclick = () => { modal.style.display = 'none'; };
    <?php if ($edit_mode) echo "modal.style.display = 'flex';"; ?>

    // Cropper (rasio 1:1)
    let cropper;
    const imageInput = document.getElementById('imageInput');
    const cropModalEl = document.getElementById('cropModal');
    const cropImageEl = document.getElementById('cropImage');
    const cropBtnEl = document.getElementById('cropBtn');
    const cancelCropBtnEl = document.getElementById('cancelCropBtn');
    const previewArea = document.getElementById('previewArea');
    const previewImage = document.getElementById('previewImage');
    const fotoCropped = document.getElementById('fotoCropped');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            cropImageEl.src = ev.target.result;
            cropModalEl.style.display = 'flex';
            if(cropper) cropper.destroy();
            cropper = new Cropper(cropImageEl, { aspectRatio: 1, viewMode: 1 });
        };
        reader.readAsDataURL(file);
    });
    cropBtnEl.addEventListener('click', () => {
        if(cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 500 });
            const base64 = canvas.toDataURL('image/png', 0.9);
            fotoCropped.value = base64;
            previewImage.src = base64;
            previewArea.style.display = 'block';
            cropModalEl.style.display = 'none';
            cropper.destroy();
        }
    });
    cancelCropBtnEl.addEventListener('click', () => {
        cropModalEl.style.display = 'none';
        if(cropper) cropper.destroy();
        imageInput.value = '';
    });
    document.getElementById('mainForm')?.addEventListener('submit', function(e) {
        if(imageInput.files[0] && !fotoCropped.value) { e.preventDefault(); alert('Harap crop foto terlebih dahulu!'); }
    });

    attachPaginationEvents();
    attachDeleteEvents();
    window.addEventListener('popstate', () => loadCommunityData(currentPage, currentSearch, currentStatus));
</script>
</body>
</html>