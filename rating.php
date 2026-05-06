<?php
require_once 'config/koneksi.php';

/* ================= PAGINATION CONFIG ================= */
$perPageOptions = [5, 10, 20, 'all'];
$perPage = isset($_GET['per_page']) && in_array($_GET['per_page'], $perPageOptions) 
    ? $_GET['per_page'] : 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($perPage === 'all') ? 0 : ($page - 1) * $perPage;

/* ================= FILTER & SIMPAN (LOGIKA TETAP) ================= */
function filterKataKotor($text) {
    $kataKotor = ['anjing', 'bangsat', 'kontol', 'memek'];
    foreach ($kataKotor as $kata) $text = preg_replace("/$kata/i", '***', $text);
    return $text;
}
function adaKataKotor($text) {
    $kataKotor = ['anjing', 'bangsat', 'kontol', 'memek'];
    foreach ($kataKotor as $kata) if (preg_match("/$kata/i", $text)) return true;
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_raw = $_POST['nama'];
    $komentar_raw = $_POST['komentar'];
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    if (adaKataKotor($nama_raw) || adaKataKotor($komentar_raw)) {
        header("Location: rating.php?status=blocked"); exit();
    }
    if ($rating >= 1 && $rating <= 5) {
        $nama = mysqli_real_escape_string($conn, filterKataKotor($nama_raw));
        $komentar = mysqli_real_escape_string($conn, filterKataKotor($komentar_raw));
        mysqli_query($conn, "INSERT INTO feedback (nama_pelanggan,rating,komentar,status_moderasi) VALUES ('$nama','$rating','$komentar','tampil')");
        header("Location: rating.php?status=success"); exit();
    }
}

/* ================= PAGINATION: COUNT TOTAL ================= */
$totalQuery = "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalReviews = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ($perPage === 'all') ? 1 : ceil($totalReviews / $perPage);

/* ================= QUERY DATA DENGAN LIMIT ================= */
$limitClause = ($perPage === 'all') ? '' : "LIMIT $perPage OFFSET $offset";
$query = "SELECT id_feedback,nama_pelanggan,rating,komentar, DATE_FORMAT(created_at,'%d %b %Y') as tanggal 
          FROM feedback WHERE status_moderasi='tampil' 
          ORDER BY created_at DESC $limitClause";
$result = mysqli_query($conn, $query);

$reviews_db = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reviews_db[] = [
        'id'=>$row['id_feedback'],
        'nama'=>$row['nama_pelanggan'],
        'rating'=>(int)$row['rating'],
        'komentar'=>$row['komentar'],
        'tanggal'=>strtoupper($row['tanggal'])
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rating-Woelandari</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/rating_style.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="container-xl py-4">
    
    <!-- HEADER CARD (SCORE + CHART DIGABUNG) -->
    <header class="sensory-dashboard-card">
        <span class="doc-tag">-</span>
        <div class="dashboard-grid">
            <!-- KIRI: HERO SCORE -->
            <div class="score-section">
                <h1>Rating</h1>
                
                <div class="score-display">
                    <span id="avgScore" class="hero-number">0.0</span>
                    <div id="avgStars" class="hero-stars"></div>
                    <p class="hero-sub">Berdasarkan <span id="totalReviewCount">0</span> user yang terverifikasi</p>
                </div>
            </div>
            <!-- KANAN: COMPACT CHART -->
            <div class="chart-section">
                <h3>RATING DISTRIBUTION</h3>
                <div id="ratingChart" class="chart-bars-compact"></div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT (FORM + FEED) -->
    <div class="content-grid">
        <aside class="form-panel">
            <div class="form-card">
                <h3><i class="fas fa-pen-fancy"></i> SUBMIT REVIEW</h3>
                <p class="microcopy">Ceritakan pengalaman anda di sini. Keep it real, keep it respectful.</p>
                <div id="alertBox" class="alert-box" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i> Kata-kata tidak pantas terdeteksi
                </div>
                <form id="reviewForm" method="POST" action="rating.php">
                    <div class="field">
                        <label>Nama</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nama" class="field-input" placeholder="Masukkan Nama Anda" required>
                        </div>
                    </div>
                    <div class="field">
                        <label>Beri Rating</label>
                        <div class="star-selector" id="starSelector">
                            <i class="fa-regular fa-star" data-val="1"></i>
                            <i class="fa-regular fa-star" data-val="2"></i>
                            <i class="fa-regular fa-star" data-val="3"></i>
                            <i class="fa-regular fa-star" data-val="4"></i>
                            <i class="fa-regular fa-star" data-val="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>
                    <div class="field">
                        <label>Komentar </label>
                        <div class="input-wrapper">
                            <i class="fas fa-comment-dots input-icon"></i>
                            <textarea name="komentar" class="field-textarea" placeholder="Gimana rasa, aroma, atau vibe-nya?" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">ARCHIVE ENTRY →</button>
                </form>
            </div>
        </aside>

        <section class="feed-panel">
            <div class="feed-controls">
                <div class="filter-buttons" id="filterButtons">
                    <button class="filter-btn active" data-filter="all">ALL LOGS</button>
                    <button class="filter-btn" data-filter="5">★★★★★</button>
                    <button class="filter-btn" data-filter="4">★★★★</button>
                    <button class="filter-btn" data-filter="3">★★★</button>
                    <button class="filter-btn" data-filter="2">★★</button>
                    <button class="filter-btn" data-filter="1">★</button>
                </div>
                
                <!-- PAGINATION: ITEMS PER PAGE -->
                <div class="pagination-controls">
                    <select id="perPageSelect" class="sort-select">
                        <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>SHOW 5</option>
                        <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>SHOW 10</option>
                        <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>SHOW 20</option>
                        <option value="all" <?= $perPage == 'all' ? 'selected' : '' ?>>SHOW ALL</option>
                    </select>
                </div>
                
                <select id="sortReview" class="sort-select">
                    <option value="newest">NEWEST FIRST</option>
                    <option value="highest">HIGHEST SCORE</option>
                </select>
            </div>
            
            <div id="entriesContainer" class="feed-list"></div>
            
            <!-- PAGINATION NAVIGATION -->
            <div id="paginationNav" class="pagination-nav" style="display:none;">
                <button id="prevPage" class="filter-btn">← PREV</button>
                <span id="pageInfo" class="page-info">Page 1 of 10</span>
                <button id="nextPage" class="filter-btn">NEXT →</button>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Data dari PHP
    const dbReviews = <?php echo json_encode($reviews_db); ?>;
    const totalReviews = <?= $totalReviews ?>;
    const initialPage = <?= $page ?>;
    const initialPerPage = <?= $perPage === 'all' ? '"all"' : $perPage ?>;
    const initialTotalPages = <?= $totalPages ?>;
    
    const container = document.getElementById('entriesContainer');
    const chartContainer = document.getElementById('ratingChart');
    const stars = document.querySelectorAll('.star-selector i');
    const ratingInput = document.getElementById('ratingValue');
    const form = document.getElementById('reviewForm');
    const alertBox = document.getElementById('alertBox');
    const badWords = ['anjing','bangsat','kontol','memek','cok','bajingan','tai','jancok','goblok','brengsek','tolol','asu','bego','gila','sialan','brengsek','bangke','bacot','kampret','setan','buauk','bosok'];
    const adaKataKotor = t => badWords.some(k => new RegExp(k,'i').test(t));

    // === PAGINATION VARIABLES ===
    let currentPage = initialPage;
    let perPage = initialPerPage;
    let totalPages = initialTotalPages;
    let activeFilter = 'all';
    let filteredData = [];

    // Star Picker
    stars.forEach(star => {
        star.addEventListener('click', () => {
            ratingInput.value = star.dataset.val;
            stars.forEach(s => s.classList.toggle('active', s.dataset.val <= star.dataset.val));
        });
        star.addEventListener('mouseenter', () => stars.forEach(s => s.classList.toggle('hovered', s.dataset.val <= star.dataset.val)));
    });
    document.querySelector('.star-selector').addEventListener('mouseleave', () => stars.forEach(s => s.classList.remove('hovered')));

    // Form Submit
    form.addEventListener('submit', e => {
        if (!ratingInput.value) { e.preventDefault(); alert("Harap berikan skor bintang."); return; }
        if (adaKataKotor(form.nama.value) || adaKataKotor(form.komentar.value)) {
            e.preventDefault(); alertBox.style.display = 'block'; setTimeout(() => alertBox.style.display = 'none', 3000);
        }
    });

    // Chart: Compact Play Store Style
    const renderChart = () => {
        const counts = [0,0,0,0,0,0];
        dbReviews.forEach(r => counts[r.rating]++);
        const total = counts.slice(1).reduce((a,b)=>a+b,0) || 1;
        const colors = ['#ff4d4f','#ff7a45','#ffc53d','#52c41a','#13c2c2'];

        chartContainer.innerHTML = counts.slice(1).reverse().map((count, idx) => {
            const star = 5 - idx;
            const percent = Math.round((count / total) * 100);
            return `
            <div class="chart-row-compact" data-filter="${star}">
                <div class="chart-label-compact">${star}★</div>
                <div class="chart-track-compact">
                    <div class="chart-fill-compact" data-width="${percent}" style="width: 0%; background: ${colors[idx]};"></div>
                </div>
                <div class="chart-meta-compact">${percent}% <span class="count-badge">(${count})</span></div>
            </div>`;
        }).join('');

        requestAnimationFrame(() => {
            document.querySelectorAll('.chart-fill-compact').forEach(bar => bar.style.width = bar.dataset.width + '%');
        });

        document.querySelectorAll('.chart-row-compact').forEach(row => {
            row.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                document.querySelector(`.filter-btn[data-filter="${row.dataset.filter}"]`).classList.add('active');
                activeFilter = row.dataset.filter;
                currentPage = 1;
                updateURL();
                render();
            });
        });
    };

    // === UPDATE URL PARAMETERS ===
    const updateURL = () => {
        const url = new URL(window.location);
        url.searchParams.set('page', currentPage);
        url.searchParams.set('per_page', perPage);
        if (activeFilter !== 'all') url.searchParams.set('filter', activeFilter);
        url.searchParams.set('sort', document.getElementById('sortReview').value);
        window.history.replaceState({}, '', url);
    };

    // === INIT FROM URL ===
    const initFromURL = () => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('filter')) {
            activeFilter = params.get('filter');
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.toggle('active', b.dataset.filter === activeFilter);
            });
        }
        if (params.get('sort')) {
            document.getElementById('sortReview').value = params.get('sort');
        }
    };

    // === UPDATE PAGINATION UI ===
    const updatePaginationUI = () => {
        const paginationNav = document.getElementById('paginationNav');
        const pageInfo = document.getElementById('pageInfo');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        
        const actualTotalPages = perPage === 'all' ? 1 : Math.ceil(filteredData.length / perPage);
        
        paginationNav.style.display = (actualTotalPages > 1) ? 'flex' : 'none';
        
        if (actualTotalPages > 1) {
            pageInfo.textContent = `Page ${currentPage} of ${actualTotalPages}`;
            prevBtn.disabled = (currentPage === 1);
            nextBtn.disabled = (currentPage === actualTotalPages);
        }
    };

    // === RENDER FUNCTION ===
    const render = () => {
        // Filter data berdasarkan rating
        filteredData = activeFilter === 'all' 
            ? [...dbReviews] 
            : dbReviews.filter(r => r.rating == activeFilter);
        
        // Sort data
        const sortValue = document.getElementById('sortReview').value;
        filteredData.sort((a,b) => {
            if (sortValue === 'highest') return b.rating - a.rating;
            return b.id - a.id; // newest
        });
        
        // Pagination logic
        const paginatedData = perPage === 'all' 
            ? filteredData 
            : filteredData.slice((currentPage - 1) * perPage, currentPage * perPage);
        
        // Render cards
        container.innerHTML = paginatedData.length ? paginatedData.map(rev => {
            const initials = rev.nama.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            const starsHtml = Array.from({length:5}, (_,i) => 
                i+1 <= rev.rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star" style="opacity:0.3;"></i>'
            ).join('');
            const stamp = rev.rating === 5 ? '<div class="stamp-verified">VERIFIED</div>' : '';
            return `
            <div class="review-card">
                <div class="review-avatar" title="${rev.nama}">${initials}</div>
                <div class="review-content">
                    <div class="review-header">
                        <span class="review-author">${rev.nama}</span>
                        <span class="review-date">${rev.tanggal}</span>
                    </div>
                    <div class="review-stars">${starsHtml}</div>
                    <p class="review-comment">"${rev.komentar}"</p>
                </div>
                ${stamp}
            </div>`;
        }).join('') : '<div class="empty-state">No records for this filter. Be the first to log.</div>';

        // Update stats (tetap pakai semua data dari DB)
        const allRatings = dbReviews.map(r => r.rating).filter(r => r>=1 && r<=5);
        const avg = allRatings.length ? (allRatings.reduce((a,b)=>a+b,0)/allRatings.length).toFixed(1) : '0.0';
        document.getElementById('avgScore').textContent = avg;
        document.getElementById('avgStars').innerHTML = '★'.repeat(Math.round(avg)) + '☆'.repeat(5-Math.round(avg));
        document.getElementById('totalReviewCount').textContent = totalReviews;
        
        // Update pagination UI
        updatePaginationUI();
    };

    // === EVENT LISTENERS ===
    
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            currentPage = 1;
            updateURL();
            render();
        });
    });

    // Per Page Select
    document.getElementById('perPageSelect').addEventListener('change', (e) => {
        perPage = e.target.value;
        currentPage = 1;
        updateURL();
        render();
    });

    // Sort Select
    document.getElementById('sortReview').addEventListener('change', () => {
        currentPage = 1;
        updateURL();
        render();
    });

    // Prev Page Button
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updateURL();
            render();
        }
    });

    // Next Page Button
    document.getElementById('nextPage').addEventListener('click', () => {
        const actualTotalPages = perPage === 'all' ? 1 : Math.ceil(filteredData.length / perPage);
        if (currentPage < actualTotalPages) {
            currentPage++;
            updateURL();
            render();
        }
    });

    // Initialize
    initFromURL();
    renderChart();
    render();
});
</script>
</body>
</html>