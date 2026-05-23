<?php
include "../config/koneksi.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['kategori']) && in_array($_GET['kategori'], ['Coffee', 'Non-Coffee', 'Snack', 'Main Course']) ? $_GET['kategori'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];
if ($search) {
    $where[] = "nama_menu LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}
if ($category) {
    $where[] = "kategori = '" . mysqli_real_escape_string($conn, $category) . "'";
}
$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$countQuery = "SELECT COUNT(*) as total FROM menu $whereSql";
$totalResult = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

$query = "SELECT * FROM menu $whereSql ORDER BY kategori, nama_menu LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$edit_mode = false;
$edit_id = $edit_nama = $edit_kategori = $edit_harga = $edit_deskripsi = $edit_foto = $edit_status = '';

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $edit_id = $d['id_menu'];
        $edit_nama = $d['nama_menu'];
        $edit_kategori = $d['kategori'];
        $edit_harga = $d['harga'];
        $edit_deskripsi = $d['deskripsi'];
        $edit_foto = $d['foto'];
        $edit_status = $d['status'];
    }
}

// Proses POST (Add/Update) dengan notifikasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id_menu']) ? (int)$_POST['id_menu'] : 0;
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = (int)$_POST['harga'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto_lama = isset($_POST['foto_lama']) ? $_POST['foto_lama'] : '';
    
    $foto_nama = $foto_lama;
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'menu_' . uniqid() . '.jpg';
        file_put_contents('../assets/images/menu/' . $foto_nama, $img_base64);
        if ($foto_lama && $foto_lama != 'default.jpg' && file_exists('../assets/images/menu/'.$foto_lama))
            unlink('../assets/images/menu/'.$foto_lama);
    }
    
    if ($action == 'add') {
        if (!$foto_nama) $foto_nama = 'default.jpg';
        mysqli_query($conn, "INSERT INTO menu (nama_menu, kategori, harga, status, deskripsi, foto) VALUES ('$nama', '$kategori', $harga, '$status', '$deskripsi', '$foto_nama')");
        $msg = " Menu berhasil ditambahkan!";
    } elseif ($action == 'update') {
        mysqli_query($conn, "UPDATE menu SET nama_menu='$nama', kategori='$kategori', harga=$harga, status='$status', deskripsi='$deskripsi', foto='$foto_nama' WHERE id_menu=$id");
        $msg = " Menu berhasil diperbarui!";
    }
    
    $redirect = "menu_crud.php?msg=" . urlencode($msg);
    if ($search) $redirect .= "&search=" . urlencode($search);
    if ($category) $redirect .= "&kategori=" . urlencode($category);
    if ($page) $redirect .= "&page=$page";
    header("Location: $redirect");
    exit;
}

// Proses Delete dengan notifikasi
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM menu WHERE id_menu=$id"));
    if ($q && $q['foto'] != 'default.jpg' && file_exists('../assets/images/menu/'.$q['foto']))
        unlink('../assets/images/menu/'.$q['foto']);
    mysqli_query($conn, "DELETE FROM menu WHERE id_menu=$id");
    
    $msg = " Menu berhasil dihapus permanen!";
    $redirect = "menu_crud.php?msg=" . urlencode($msg);
    if ($search) $redirect .= "&search=" . urlencode($search);
    if ($category) $redirect .= "&kategori=" . urlencode($category);
    if ($page) $redirect .= "&page=$page";
    header("Location: $redirect");
    exit;
}

$msg_display = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';

// Fungsi render tabel dan pagination
function renderMenuTable($result, $page, $totalPages, $search, $category) {
    ob_start();
    ?>
    <div class="table-container">
        <table class="menu-table">
            <thead>
                <tr><th>FOTO</th><th>NAMA ITEM</th><th>KATEGORI</th><th>HARGA</th><th>STATUS</th><th style="text-align: center;">AKSI</th></tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td style="text-align: center;"><img src="../assets/images/menu/<?= $row['foto'] ?>" class="thumb-img" onerror="this.src='../assets/images/menu/default.jpg'"></td>
                    <td style="font-weight: bold; color: var(--navy);"><?= htmlspecialchars($row['nama_menu']) ?></td>
                    <td>
                        <?php
                        $kategori = htmlspecialchars($row['kategori']);
                        $badgeClass = '';
                        switch ($kategori) {
                            case 'Coffee': $badgeClass = 'badge-coffee'; break;
                            case 'Non-Coffee': $badgeClass = 'badge-noncoffee'; break;
                            case 'Snack': $badgeClass = 'badge-snack'; break;
                            case 'Main Course': $badgeClass = 'badge-main'; break;
                            default: $badgeClass = 'badge-default';
                        }
                        ?>
                        <span class="category-badge <?= $badgeClass ?>"><?= strtoupper($kategori) ?></span>
                    </td>
                    <td style="font-weight: bold;">Rp <?= number_format($row['harga'],0,',','.') ?></td>
                    <td><span class="status-badge <?= strtolower($row['status']) == 'tersedia' ? 'status-tersedia' : 'status-tidak' ?>"><?= strtoupper($row['status']) ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?edit=<?= $row['id_menu'] ?>&search=<?= urlencode($search) ?>&kategori=<?= urlencode($category) ?>&page=<?= $page ?>" class="btn-action btn-edit-action"><i class="fas fa-pencil-alt"></i> EDIT</a>
                            <button type="button" class="btn-action btn-delete-action delete-btn" data-id="<?= $row['id_menu'] ?>" data-name="<?= htmlspecialchars($row['nama_menu']) ?>" data-search="<?= htmlspecialchars($search) ?>" data-category="<?= htmlspecialchars($category) ?>" data-page="<?= $page ?>"><i class="fas fa-trash-alt"></i> HAPUS</button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:40px;"><i class="fas fa-database"></i> [ DATA TIDAK DITEMUKAN ]</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-area">
        <button class="btn btn-secondary pagi-prev" data-page="<?= $page-1 ?>" <?= $page<=1?'disabled':'' ?>><i class="fas fa-chevron-left"></i> PREV</button>
        <span>HALAMAN <?= $page ?> DARI <?= $totalPages ?></span>
        <button class="btn btn-secondary pagi-next" data-page="<?= $page+1 ?>" <?= $page>=$totalPages?'disabled':'' ?>>NEXT <i class="fas fa-chevron-right"></i></button>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    echo renderMenuTable($result, $page, $totalPages, $search, $category);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Menu - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard/menu_crud.css">
    <style>
        /* Badge kategori */
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 2px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid currentColor;
        }
        .badge-coffee { background: rgba(110,58,28,0.1); color: #6e3a1c; }
        .badge-noncoffee { background: rgba(0,102,102,0.1); color: #006666; }
        .badge-snack { background: rgba(217,119,6,0.1); color: #d97706; }
        .badge-main { background: rgba(0,70,128,0.1); color: #004680; }
        .badge-default { background: rgba(0,43,91,0.1); color: var(--navy); }
        
        /* Pagination seperti rating */
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
       
        /* Alert notifikasi */
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
        .alert-msg i { font-size: 1.2rem; }
    </style>
</head>
<body>
<div class="overlay" id="sidebarOverlay"></div>
<?php include "../components/sidebar.php"; ?>
<main class="main-wrapper">
    <div class="mobile-header">
        <div><i class="fas fa-mug-hot" style="color: var(--red);"></i> WOELANDARI</div>
        <button id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem;"><i class="fas fa-bars"></i></button>
    </div>
    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header"><span><i class="fas fa-folder-open"></i> Kelola Menu</span><span>DATE: <?= date('d/m/Y') ?></span></div>
        <h1 class="title-main">MENU</h1>
        
        <?php if ($msg_display): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg_display ?></div>
        <?php endif; ?>
        
        <div class="filter-area">
            <div class="search-wrapper"><i class="fas fa-search"></i><input type="text" id="searchInput" class="search-input" placeholder="Cari menu..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="category-wrapper">
                <select id="categoryFilter" class="category-select">
                    <option value="">SEMUA KATEGORI</option>
                    <option value="Coffee" <?= $category=='Coffee'?'selected':'' ?>>Coffee</option>
                    <option value="Non-Coffee" <?= $category=='Non-Coffee'?'selected':'' ?>>Non-Coffee</option>
                    <option value="Snack" <?= $category=='Snack'?'selected':'' ?>>Snack</option>
                    <option value="Main Course" <?= $category=='Main Course'?'selected':'' ?>>Main Course</option>
                </select>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" id="filterBtn"><i class="fas fa-filter"></i> FILTER</button>
                <?php if ($search || $category): ?><a href="menu_crud.php" class="btn btn-secondary"><i class="fas fa-undo-alt"></i> RESET</a><?php endif; ?>
                <button class="btn btn-primary" id="showModalBtn" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy);"><i class="fas fa-plus"></i> ADD ENTRY</button>
            </div>
        </div>
        <div id="menuContent">
            <?= renderMenuTable($result, $page, $totalPages, $search, $category) ?>
        </div>
    </section>
</main>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="confirm-modal-body">
            <h3>HAPUS MENU?</h3>
            <p>Apakah Anda yakin ingin menghapus menu berikut?</p>
            <div class="menu-name-highlight" id="menuNameToDelete"></div>
            <p style="font-size:0.8rem; margin-top:15px;"><i class="fas fa-info-circle"></i> Data dihapus tidak dapat dikembalikan!</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">BATAL</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">HAPUS</a>
        </div>
    </div>
</div>

<!-- Modal Add/Edit -->
<div id="crudModal" class="modal">
    <div class="modal-content">
        <div class="tape" style="top:-16px; width:100px; height:25px;"></div>
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px;"><span id="modalTitle">TAMBAH MENU BARU</span></div>
        </div>
        <div class="modal-body-scroll">
            <form id="menuForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_menu" id="editId" value="">
                <input type="hidden" name="foto_lama" id="fotoLama" value="">
                <input type="hidden" name="foto_cropped" id="fotoCropped" value="">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">NAMA MENU</label><input type="text" name="nama_menu" id="nama_menu" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">KATEGORI</label><select name="kategori" id="kategori" class="form-select"><option value="Coffee">Coffee</option><option value="Non-Coffee">Non-Coffee</option><option value="Snack">Snack</option><option value="Main Course">Main Course</option></select></div>
                    <div class="form-group"><label class="form-label">HARGA (Rp)</label><input type="number" name="harga" id="harga" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">STATUS</label><select name="status" id="status" class="form-select"><option value="Tersedia">Tersedia</option><option value="Tidak Tersedia">Tidak Tersedia</option></select></div>
                </div>
                <div class="form-group"><label class="form-label">DESKRIPSI (OPSIONAL)</label><textarea name="deskripsi" id="deskripsi" rows="2" class="form-input"></textarea></div>
                <div class="upload-box-safe">
                    <label class="form-label">UPLOAD FOTO (RASIO 6:5)</label>
                    <input type="file" id="fileInput" accept="image/*">
                    <div id="previewArea" style="margin-top:12px;"><img id="previewImg" style="max-width:110px; border:2px solid var(--navy); display:none;"></div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:25px;">
                    <button type="button" class="btn btn-secondary" id="cancelModalBtn">BATAL</button>
                    <button type="submit" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cropModal" class="modal" style="z-index:2100;">
    <div class="modal-content" style="max-width:460px; padding:20px;">
        <div class="spec-header" style="margin-bottom:15px;">CROP GAMBAR (6:5)</div>
        <div class="crop-container" style="border:2px solid var(--navy); background:#000;"><img id="cropImage" src="" style="max-width:100%;"></div>
        <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:20px;">
            <button class="btn btn-secondary" id="cancelCropBtn">BATAL</button>
            <button class="btn btn-primary" id="cropConfirmBtn" style="background:var(--red);">CROP & SET</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let currentSearch = '<?= addslashes($search) ?>';
    let currentCategory = '<?= addslashes($category) ?>';
    let currentPage = <?= $page ?>;

    // Sidebar
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btn) btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

    function loadMenuData(page, search, category) {
        document.getElementById('menuContent').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
        let url = `menu_crud.php?ajax=1&page=${page}`;
        if(search) url += `&search=${encodeURIComponent(search)}`;
        if(category) url += `&kategori=${encodeURIComponent(category)}`;
        fetch(url).then(res => res.text()).then(html => {
            document.getElementById('menuContent').innerHTML = html;
            attachDeleteEvents(); attachPaginationEvents();
            currentSearch = search; currentCategory = category; currentPage = page;
            let newUrl = `menu_crud.php?page=${page}` + (search?`&search=${encodeURIComponent(search)}`:'') + (category?`&kategori=${encodeURIComponent(category)}`:'');
            window.history.pushState({}, '', newUrl);
        }).catch(err => { console.error(err); document.getElementById('menuContent').innerHTML = '<div style="text-align:center;padding:40px;color:red;">Gagal memuat data.</div>'; });
    }

    document.getElementById('filterBtn')?.addEventListener('click', () => loadMenuData(1, document.getElementById('searchInput').value, document.getElementById('categoryFilter').value));
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
        if(page) loadMenuData(parseInt(page), currentSearch, currentCategory);
    }

    function attachDeleteEvents() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.removeEventListener('click', deleteHandler);
            btn.addEventListener('click', deleteHandler);
        });
    }
    function deleteHandler(e) {
        e.preventDefault();
        let id = this.dataset.id, name = this.dataset.name, search = this.dataset.search||'', category = this.dataset.category||'', page = this.dataset.page||'1';
        document.getElementById('menuNameToDelete').innerText = name;
        let url = `?hapus=${id}&search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(category)}&page=${page}`;
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteConfirmModal').style.display = 'flex';
    }
    document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => document.getElementById('deleteConfirmModal').style.display = 'none');
    document.getElementById('deleteConfirmModal')?.addEventListener('click', e => { if(e.target === document.getElementById('deleteConfirmModal')) document.getElementById('deleteConfirmModal').style.display = 'none'; });

    // Modal Add/Edit
    const modal = document.getElementById('crudModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const editId = document.getElementById('editId');
    const fotoLama = document.getElementById('fotoLama');
    const namaInput = document.getElementById('nama_menu');
    const kategoriSelect = document.getElementById('kategori');
    const hargaInput = document.getElementById('harga');
    const statusSelect = document.getElementById('status');
    const deskripsiText = document.getElementById('deskripsi');
    const previewImg = document.getElementById('previewImg');
    const fileInput = document.getElementById('fileInput');
    const fotoCropped = document.getElementById('fotoCropped');
    const showModalBtn = document.getElementById('showModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');

    <?php if ($edit_mode): ?>
    window.addEventListener('DOMContentLoaded', () => {
        modalTitle.innerText = 'UPDATE ARSIP MENU';
        formAction.value = 'update';
        editId.value = '<?= $edit_id ?>';
        fotoLama.value = '<?= addslashes($edit_foto) ?>';
        namaInput.value = '<?= addslashes($edit_nama) ?>';
        kategoriSelect.value = '<?= addslashes($edit_kategori) ?>';
        hargaInput.value = '<?= $edit_harga ?>';
        statusSelect.value = '<?= addslashes($edit_status) ?>';
        deskripsiText.value = `<?= addslashes($edit_deskripsi) ?>`;
        if ('<?= $edit_foto ?>' && '<?= $edit_foto ?>' != 'default.jpg') { previewImg.src = '../assets/images/menu/<?= $edit_foto ?>'; previewImg.style.display = 'block'; }
        modal.style.display = 'flex';
    });
    <?php endif; ?>
    if(showModalBtn) showModalBtn.onclick = () => { modalTitle.innerText = 'TAMBAH ENTRI BARU'; formAction.value='add'; editId.value=''; fotoLama.value=''; namaInput.value=''; kategoriSelect.value='Coffee'; hargaInput.value=''; statusSelect.value='Tersedia'; deskripsiText.value=''; previewImg.style.display='none'; fileInput.value=''; fotoCropped.value=''; modal.style.display='flex'; };
    if(cancelModalBtn) cancelModalBtn.onclick = () => modal.style.display = 'none';

    // Cropper
    let cropper;
    const cropModal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const cropConfirm = document.getElementById('cropConfirmBtn');
    const cancelCrop = document.getElementById('cancelCropBtn');
    fileInput.addEventListener('change', e => {
        let file = e.target.files[0];
        if(!file) return;
        let reader = new FileReader();
        reader.onload = ev => { cropImage.src = ev.target.result; cropModal.style.display = 'flex'; if(cropper) cropper.destroy(); cropper = new Cropper(cropImage, { aspectRatio:6/5, viewMode:1 }); };
        reader.readAsDataURL(file);
    });
    cropConfirm.addEventListener('click', () => {
        if(cropper) {
            let canvas = cropper.getCroppedCanvas({ width:600, height:500 });
            fotoCropped.value = canvas.toDataURL('image/jpeg', 0.9);
            previewImg.src = fotoCropped.value;
            previewImg.style.display = 'block';
            cropModal.style.display = 'none';
            cropper.destroy();
        }
    });
    cancelCrop.addEventListener('click', () => { cropModal.style.display = 'none'; if(cropper) cropper.destroy(); fileInput.value=''; });
    document.getElementById('menuForm')?.addEventListener('submit', e => { if(fileInput.files[0] && !fotoCropped.value) { e.preventDefault(); alert('Wajib crop foto sebelum menyimpan!'); } });

    attachPaginationEvents();
    attachDeleteEvents();
    window.addEventListener('popstate', () => loadMenuData(currentPage, currentSearch, currentCategory));
</script>
</body>
</html>