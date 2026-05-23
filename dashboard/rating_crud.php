<?php
// dashboard/rating_crud.php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Proses moderasi dengan notifikasi
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $pg = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $srch = isset($_GET['search']) ? trim($_GET['search']) : '';
    $msg = '';

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi='tampil' WHERE id_feedback=$id");
        $msg = " Feedback berhasil disetujui dan ditampilkan.";
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi='pending' WHERE id_feedback=$id");
        $msg = " Feedback disembunyikan (status pending).";
    } elseif ($action === 'delete') {
        mysqli_query($conn, "DELETE FROM feedback WHERE id_feedback=$id");
        $msg = " Feedback berhasil dihapus permanen.";
    }
    
    $redirectUrl = "rating_crud.php?page=$pg" . ($srch ? "&search=" . urlencode($srch) : "") . "&msg=" . urlencode($msg);
    header("Location: $redirectUrl");
    exit;
}

// Konfigurasi Search dan Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = "";
if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $whereClause = " WHERE nama_pelanggan LIKE '%$safe_search%' OR komentar LIKE '%$safe_search%'";
}

$countQuery = "SELECT COUNT(*) as total FROM feedback" . $whereClause;
$total_query = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$total_filtered = $total_query['total'] ?? 0;
$totalPages = ceil($total_filtered / $limit);

$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as tanggal FROM feedback" . $whereClause . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Statistik Global
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='pending'"))['total'] ?? 0;
$total_tampil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil'"))['total'] ?? 0;
$avg_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rate FROM feedback"));
$avg_rating = $avg_query['avg_rate'] ? number_format($avg_query['avg_rate'], 1) : '0.0';

// Fungsi render tabel dan pagination (sama seperti sebelumnya)
function renderTableAndPagination($result, $page, $totalPages, $search) {
    ob_start();
    ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>TANGGAL</th><th>PELANGGAN</th><th style="text-align: center;">RATING</th><th>KOMENTAR</th><th style="text-align: center;">STATUS</th><th style="text-align: center;">AKSI MODERASI</th></tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $param = "?page=$page" . ($search ? "&search=".urlencode($search) : "") . "&id=".$row['id_feedback'];
                    ?>
                        <tr>
                            <td><i class="far fa-calendar-alt"></i> <?= $row['tanggal'] ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                            <td style="text-align: center;">
                                <div class="rating-stars">
                                    <?php for($i=1; $i<=5; $i++) echo '<i class="fas fa-star '.($i<=$row['rating']?'active':'inactive').'"></i>'; ?>
                                </div>
                            </td>
                            <td>"<?= htmlspecialchars(substr($row['komentar'], 0, 100)) ?><?= strlen($row['komentar'])>100?'...':'' ?>"</td>
                            <td><span class="status-badge <?= $row['status_moderasi']=='tampil'?'status-tampil':'status-pending' ?>"><?= strtoupper($row['status_moderasi']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($row['status_moderasi'] == 'pending'): ?>
                                        <a href="rating_crud.php<?= $param ?>&action=approve" class="btn-action btn-approve"><i class="fas fa-check-circle"></i> TAMPIL</a>
                                    <?php else: ?>
                                        <a href="rating_crud.php<?= $param ?>&action=reject" class="btn-action btn-hide"><i class="fas fa-eye-slash"></i> SEMBUNYI</a>
                                    <?php endif; ?>
                                    <button type="button" class="btn-action btn-delete-action delete-btn" 
                                        data-id="<?= $row['id_feedback'] ?>" data-name="<?= htmlspecialchars($row['nama_pelanggan']) ?>"
                                        data-comment="<?= htmlspecialchars(substr($row['komentar'],0,50)) ?>" data-page="<?= $page ?>" data-search="<?= htmlspecialchars($search) ?>">
                                        <i class="fas fa-trash-alt"></i> HAPUS
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:60px;">[ LOG FEEDBACK TIDAK DITEMUKAN ]</td></tr>
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

// Jika request AJAX, kirim konten tabel+pagination
if (isset($_GET['ajax'])) {
    echo renderTableAndPagination($result, $page, $totalPages, $search);
    exit;
}

$msg_display = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Feedback - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard/rating_crud.css">
    <style>
        /* Tambahan style agar alert-msg lebih menarik */
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
        .alert-msg i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<div class="overlay" id="sidebarOverlay"></div>
<?php include "../components/sidebar.php"; ?>
<main class="main-wrapper">
    <div class="mobile-header">
        <div><i class="fas fa-star" style="color: var(--red);"></i> WOELANDARI</div>
        <button id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem;"><i class="fas fa-bars"></i></button>
    </div>
    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header"><span><i class="fas fa-folder-open"></i> Kelola Feedback & Rating</span><span>DATE: <?= date('d/m/Y') ?></span></div>
        <h1 class="title-main">Feedback Pelanggan</h1>
        
        <?php if ($msg_display): ?>
            <div class="alert-msg">
                <i class="fas fa-info-circle"></i> <?= $msg_display ?>
            </div>
        <?php endif; ?>
        
        <div class="stat-grid">
            <div class="stat-card"><span class="stat-label">TOTAL FEEDBACK</span><div class="stat-value"><?= $total_feedback ?></div></div>
            <div class="stat-card"><span class="stat-label">AVG RATING</span><div class="stat-value"><?= $avg_rating ?> <i class="fas fa-star" style="font-size:1.2rem;color:#d4af37;"></i></div></div>
            <div class="stat-card"><span class="stat-label" style="color:#856404;">PENDING</span><div class="stat-value" style="color:#856404;"><?= $total_pending ?></div></div>
            <div class="stat-card"><span class="stat-label" style="color:#155724;">TAMPIL DI WEB</span><div class="stat-value" style="color:#155724;"><?= $total_tampil ?></div></div>
        </div>
        <div class="search-area">
            <div class="search-wrapper"><i class="fas fa-search"></i><input type="text" id="searchInput" class="search-input" placeholder="Cari nama pelanggan atau ulasan..." value="<?= htmlspecialchars($search) ?>"></div>
            <button class="btn btn-primary" id="searchBtn">CARI LOG</button>
            <?php if ($search): ?><a href="rating_crud.php" class="btn btn-secondary">RESET</a><?php endif; ?>
        </div>
        <div id="ajax-table-container">
            <?= renderTableAndPagination($result, $page, $totalPages, $search) ?>
        </div>
    </section>
</main>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="confirm-modal-body">
            <h3>HAPUS FEEDBACK?</h3>
            <p>Apakah Anda yakin ingin menghapus feedback berikut?</p>
            <div class="feedback-name-highlight" id="feedbackNameToDelete"></div>
            <div id="feedbackCommentToDelete" style="font-size:0.8rem; color:#999;"></div>
            <p style="font-size:0.8rem; margin-top:15px;"><i class="fas fa-info-circle"></i> Data dihapus tidak dapat dikembalikan!</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">BATAL</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">HAPUS</a>
        </div>
    </div>
</div>

<script>
    // Sidebar toggle
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btn) btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    if(overlay) overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

    // Fungsi AJAX load tabel & pagination
    function loadTableAndPagination(page, search) {
        let url = `rating_crud.php?ajax=1&page=${page}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('ajax-table-container').innerHTML = html;
                attachPaginationEvents();
                attachDeleteEvents();
                let newUrl = `rating_crud.php?page=${page}` + (search ? `&search=${encodeURIComponent(search)}` : '');
                window.history.pushState({ page, search }, '', newUrl);
            })
            .catch(err => console.error('Gagal load data:', err));
    }

    function attachPaginationEvents() {
        document.querySelectorAll('.pagi-prev').forEach(btn => {
            btn.removeEventListener('click', pagiClickHandler);
            btn.addEventListener('click', pagiClickHandler);
        });
        document.querySelectorAll('.pagi-next').forEach(btn => {
            btn.removeEventListener('click', pagiClickHandler);
            btn.addEventListener('click', pagiClickHandler);
        });
    }
    function pagiClickHandler(e) {
        let btn = e.currentTarget;
        if (btn.disabled) return;
        let page = btn.getAttribute('data-page');
        if (page) {
            let search = document.getElementById('searchInput').value;
            loadTableAndPagination(page, search);
        }
    }

    function attachDeleteEvents() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.removeEventListener('click', deleteClickHandler);
            btn.addEventListener('click', deleteClickHandler);
        });
    }
    function deleteClickHandler(e) {
        e.preventDefault();
        const id = this.dataset.id, name = this.dataset.name, comment = this.dataset.comment || '', page = this.dataset.page, search = this.dataset.search || '';
        document.getElementById('feedbackNameToDelete').innerText = name;
        let commentSpan = document.getElementById('feedbackCommentToDelete');
        if(comment) { commentSpan.innerText = `"${comment}${comment.length>=50?'...':''}"`; commentSpan.style.display = 'block'; }
        else commentSpan.style.display = 'none';
        let deleteUrl = `rating_crud.php?page=${page}&id=${id}&action=delete`;
        if(search) deleteUrl += `&search=${encodeURIComponent(search)}`;
        document.getElementById('confirmDeleteBtn').href = deleteUrl;
        document.getElementById('deleteConfirmModal').style.display = 'flex';
    }
    document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => document.getElementById('deleteConfirmModal').style.display = 'none');
    document.getElementById('deleteConfirmModal')?.addEventListener('click', (e) => { if(e.target === document.getElementById('deleteConfirmModal')) document.getElementById('deleteConfirmModal').style.display = 'none'; });

    // Search dengan AJAX
    document.getElementById('searchBtn')?.addEventListener('click', () => {
        let search = document.getElementById('searchInput').value;
        loadTableAndPagination(1, search);
    });
    document.getElementById('searchInput')?.addEventListener('keypress', (e) => { if(e.key === 'Enter') document.getElementById('searchBtn').click(); });

    attachPaginationEvents();
    attachDeleteEvents();

    window.addEventListener('popstate', (event) => {
        if (event.state) {
            loadTableAndPagination(event.state.page, event.state.search);
        } else {
            location.reload();
        }
    });
</script>
</body>
</html>