<?php
/**
 * rating.php
 * Halaman rating & review untuk Woelandari Coffee Lab
 * * @author Woelandari Team
 * @version 2.0 - smooth + pisah CSS
 */

require_once 'config/koneksi.php';

$perPage = 3; // jumlah review per halaman

// ============================================================
// HANDLER AJAX (submit review & load data)
// ============================================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    // ----- 1. SUBMIT REVIEW -----
    if ($_GET['ajax'] == 'submit') {
        $nama_raw     = $_POST['nama'] ?? '';
        $komentar_raw = $_POST['komentar'] ?? '';
        $rating       = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;

        if (empty($nama_raw) || empty($komentar_raw) || $rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        // Filter kata kasar (simple tapi cukup)
        $kataKotor = ['anjing', 'bangsat', 'kontol', 'memek', 'jancok', 'asu', 'goblok', 'tolol', 'bego', 'sialan', 'brengsek', 'tai', 'kampret'];
        $adaKataKotor = false;
        foreach ($kataKotor as $k) {
            if (preg_match('/\b' . preg_quote($k, '/') . '\b/i', $nama_raw) ||
                preg_match('/\b' . preg_quote($k, '/') . '\b/i', $komentar_raw)) {
                $adaKataKotor = true;
                break;
            }
        }
        if ($adaKataKotor) {
            echo json_encode(['success' => false, 'message' => 'Kata tidak pantas terdeteksi']);
            exit;
        }

        $nama     = mysqli_real_escape_string($conn, $nama_raw);
        $komentar = mysqli_real_escape_string($conn, $komentar_raw);
        $insert   = mysqli_query($conn, "INSERT INTO feedback (nama_pelanggan, rating, komentar, status_moderasi) VALUES ('$nama', '$rating', '$komentar', 'tampil')");

        if ($insert) {
            echo json_encode(['success' => true, 'message' => 'Review berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal: ' . mysqli_error($conn)]);
        }
        exit;
    }

    // ----- 2. LOAD REVIEWS (filter + pagination) -----
    if ($_GET['ajax'] == 'load') {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $page   = isset($_GET['page'])   ? max(1, (int) $_GET['page']) : 1;

        $filter_condition = ($filter !== 'all') ? "AND rating = " . intval($filter) : "";

        // Total data untuk pagination
        $totalQuery   = "SELECT COUNT(*) as total FROM feedback WHERE status_moderasi='tampil' $filter_condition";
        $totalResult  = mysqli_query($conn, $totalQuery);
        if (!$totalResult) {
            echo json_encode(['error' => true, 'message' => 'Query total error: ' . mysqli_error($conn)]);
            exit;
        }
        $totalReviews = mysqli_fetch_assoc($totalResult)['total'];
        $totalPages   = $totalReviews ? ceil($totalReviews / $perPage) : 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        // Ambil data review
        $query = "SELECT nama_pelanggan, rating, komentar, DATE_FORMAT(created_at, '%d %b %Y') as tanggal 
                  FROM feedback WHERE status_moderasi='tampil' $filter_condition
                  ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo json_encode(['error' => true, 'message' => 'Query data error: ' . mysqli_error($conn)]);
            exit;
        }

        $reviews = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = [
                'nama_pelanggan' => htmlspecialchars($row['nama_pelanggan'], ENT_QUOTES, 'UTF-8'),
                'rating'         => (int) $row['rating'],
                'komentar'       => htmlspecialchars($row['komentar'], ENT_QUOTES, 'UTF-8'),
                'tanggal'        => $row['tanggal']
            ];
        }

        echo json_encode(['reviews' => $reviews, 'totalPages' => $totalPages, 'currentPage' => $page]);
        exit;
    }
}

// ============================================================
// DATA UNTUK CHART (semua rating)
// ============================================================
$allRatings = [];
$result = mysqli_query($conn, "SELECT rating FROM feedback WHERE status_moderasi='tampil'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allRatings[] = (int) $row['rating'];
    }
}
$ratingCounts = [0,0,0,0,0,0];
foreach ($allRatings as $r) {
    if ($r >= 1 && $r <= 5) $ratingCounts[$r]++;
}
$totalRatings = count($allRatings);
$avg = $totalRatings ? round(array_sum($allRatings) / $totalRatings, 1) : 0;
$fullStars = round($avg);
?>

<section id="rating-section" class="main-page">
    <div class="rating-container">

        <div class="rating-header-card">
            <div class="rating-header-flex">
                <div class="rating-left">
                    <span class="doc-tag">Sistem Rating</span>
                    <h2>Rating</h2>
                    <div class="title-line"></div>
                    <p class="rating-subtitle">Suara pelanggan, cerminan rasa</p>
                </div>
                <div class="rating-right">
                    <div class="score-section">
                        <div class="big-number"><?= $avg ?></div>
                        <div class="big-stars"><?= str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars) ?></div>
                        <div class="total-reviews"><?= $totalRatings ?> review</div>
                    </div>
                    <div class="chart-section" id="ratingChartCompact"></div>
                </div>
            </div>
        </div>

        <div class="rating-two-columns">

            <div class="form-card-panel">
                <div class="card-label">Form Pengisian Rating</div>
                <div class="form-header">
                    <i class="fas fa-mug-hot"></i>
                    <h3>Tulis Pengalamanmu</h3>
                </div>
                <div id="formAlert" class="alert-box" style="display:none;"></div>
                <form id="ratingForm">
                    <div class="field">
                        <label>Nama </label>
                        <input type="text" id="formNama" placeholder="Misal: Andi Wijaya" required>
                    </div>
                    <div class="field">
                        <label>Rating</label>
                        <div class="star-selector" id="starSelector">
                            <i class="fa-regular fa-star" data-val="1"></i>
                            <i class="fa-regular fa-star" data-val="2"></i>
                            <i class="fa-regular fa-star" data-val="3"></i>
                            <i class="fa-regular fa-star" data-val="4"></i>
                            <i class="fa-regular fa-star" data-val="5"></i>
                        </div>
                        <input type="hidden" id="formRatingValue" required>
                    </div>
                    <div class="field">
                        <label>Komentar</label>
                        <textarea id="formKomentar" rows="3" placeholder="Bagaimana rasa, aroma, suasana, atau pelayanan kami?" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit-form">KIRIM REVIEW →</button>
                </form>
            </div>

            <div class="feed-card-panel">
                <div class="card-label">Deretan Ulasan</div>
                <div class="feed-controls">
                    <div class="filter-buttons" id="filterButtons">
                        <button data-filter="all" class="filter-btn active">SEMUA</button>
                        <button data-filter="5" class="filter-btn">5★</button>
                        <button data-filter="4" class="filter-btn">4★</button>
                        <button data-filter="3" class="filter-btn">3★</button>
                        <button data-filter="2" class="filter-btn">2★</button>
                        <button data-filter="1" class="filter-btn">1★</button>
                    </div>
                </div>
                <div id="feedList" class="feed-list">
                    <div class="loader">Memuat ulasan...</div>
                </div>
                <div id="paginationNav" class="pagination-nav" style="display:none;">
                    <button id="prevPage" class="filter-btn">← Sebelumnya</button>
                    <span id="pageInfo" class="page-info"></span>
                    <button id="nextPage" class="filter-btn">Selanjutnya →</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// -------------------------------------------------------------
// Data dari server
// -------------------------------------------------------------
const ratingCounts = <?= json_encode($ratingCounts) ?>;
const totalRatings = <?= $totalRatings ?>;

let currentFilter = 'all';
let currentPage   = 1;
let totalPages     = 1;

// -------------------------------------------------------------
// Render chart distribusi rating (interaktif)
// -------------------------------------------------------------
function renderChart() {
    const container = document.getElementById('ratingChartCompact');
    if (!container) return;

    const colors = ['#E74C3C', '#E67E22', '#F1C40F', '#2ECC71', '#1ABC9C'];
    let html = '';
    for (let star = 5; star >= 1; star--) {
        let count = ratingCounts[star];
        let percent = totalRatings ? Math.round((count / totalRatings) * 100) : 0;
        html += `<div class="chart-row" data-filter="${star}">
                    <div class="chart-label">${star}★</div>
                    <div class="chart-track"><div class="chart-fill" data-width="${percent}" style="width:0%; background: ${colors[5-star]};"></div></div>
                    <div class="chart-meta">${percent}% <span>(${count})</span></div>
                </div>`;
    }
    container.innerHTML = html;

    // animasi bar
    setTimeout(() => {
        document.querySelectorAll('.chart-fill').forEach(bar => bar.style.width = bar.dataset.width + '%');
    }, 100);

    // klik bar → filter
    document.querySelectorAll('.chart-row').forEach(row => {
        row.addEventListener('click', () => {
            const filterVal = row.dataset.filter;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            const target = document.querySelector(`.filter-btn[data-filter="${filterVal}"]`);
            if (target) target.classList.add('active');
            currentFilter = filterVal;
            currentPage = 1;
            loadReviews(currentFilter, 1);
        });
    });
}

// -------------------------------------------------------------
// Load review via AJAX (SMOOTH VERSION)
// -------------------------------------------------------------
async function loadReviews(filter, page) {
    const feedList = document.getElementById('feedList');
    
    // Beri efek transisi CSS agar memudar halus tanpa merusak tinggi halaman
    feedList.style.transition = 'opacity 0.3s ease';
    feedList.style.opacity = '0.4'; 

    try {
        const url = `rating.php?ajax=load&filter=${filter}&page=${page}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.error) {
            feedList.innerHTML = `<div class="empty-state">Error: ${data.message}</div>`;
            feedList.style.opacity = '1';
            document.getElementById('paginationNav').style.display = 'none';
            return;
        }

        if (data.reviews !== undefined) {
            totalPages = data.totalPages;
            currentPage = data.currentPage;
            if (data.reviews.length === 0) {
                feedList.innerHTML = '<div class="empty-state">Belum ada ulasan. Jadilah yang pertama!</div>';
                feedList.style.opacity = '1';
                document.getElementById('paginationNav').style.display = 'none';
            } else {
                renderFeed(data.reviews);
                updatePagination(filter);
                // Kembalikan opacity ke normal
                feedList.style.opacity = '1';
            }
        } else {
            feedList.innerHTML = '<div class="empty-state">Data tidak valid</div>';
            feedList.style.opacity = '1';
        }
    } catch (err) {
        console.error(err);
        feedList.innerHTML = '<div class="empty-state">Gagal memuat ulasan. Coba lagi nanti.</div>';
        feedList.style.opacity = '1';
    }
}

// -------------------------------------------------------------
// Tampilkan daftar review di feed dengan ANIMASI SLIDE UP
// -------------------------------------------------------------
function renderFeed(reviews) {
    const feedList = document.getElementById('feedList');
    if (!reviews.length) {
        feedList.innerHTML = '<div class="empty-state">Belum ada ulasan.</div>';
        return;
    }

    // build HTML (tambahkan opacity: 0 di awal untuk persiapan animasi)
    let html = '';
    reviews.forEach(rev => {
        const initials = rev.nama_pelanggan.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        const stars = '★'.repeat(rev.rating) + '☆'.repeat(5 - rev.rating);
        const colorMap = {5:'#1ABC9C',4:'#2ECC71',3:'#F1C40F',2:'#E67E22',1:'#E74C3C'};
        const color = colorMap[rev.rating] || '#888';

        html += `<div class="review-item" style="border-left-color: ${color}; opacity: 0;">
                    <div class="review-avatar" style="background: ${color};">${escapeHtml(initials)}</div>
                    <div class="review-body">
                        <div class="review-header">
                            <span class="review-name">${escapeHtml(rev.nama_pelanggan)}</span>
                            <span class="review-date">${rev.tanggal}</span>
                        </div>
                        <div class="review-stars">${stars}</div>
                        <p class="review-text"><i class="fas fa-quote-left"></i> ${escapeHtml(rev.komentar)}</p>
                    </div>
                </div>`;
    });
    feedList.innerHTML = html;

    // Terapkan animasi meluncur ke atas satu per satu (Staggered Animation)
    const newItems = feedList.querySelectorAll('.review-item');
    newItems.forEach((item, index) => {
        item.animate([
            { opacity: 0, transform: 'translateY(20px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], { 
            duration: 400, 
            delay: index * 80, // Delay bertingkat agar munculnya bergiliran
            fill: 'forwards',
            easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)'
        });
    });
}

// -------------------------------------------------------------
// Update tampilan pagination (tombol prev/next, info halaman)
// -------------------------------------------------------------
function updatePagination(filter) {
    const paginationDiv = document.getElementById('paginationNav');
    if (totalPages <= 1) {
        paginationDiv.style.display = 'none';
        return;
    }
    paginationDiv.style.display = 'flex';
    document.getElementById('pageInfo').innerText = `Halaman ${currentPage} dari ${totalPages}`;

    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    // disable jika di ujung
    prevBtn.disabled = (currentPage === 1);
    nextBtn.disabled = (currentPage === totalPages);

    // lepaskan event lama lalu pasang ulang (biar tidak double)
    const newPrev = prevBtn.cloneNode(true);
    const newNext = nextBtn.cloneNode(true);
    prevBtn.parentNode.replaceChild(newPrev, prevBtn);
    nextBtn.parentNode.replaceChild(newNext, nextBtn);

    newPrev.onclick = () => { if (currentPage > 1) loadReviews(filter, currentPage - 1); };
    newNext.onclick = () => { if (currentPage < totalPages) loadReviews(filter, currentPage + 1); };
}

// -------------------------------------------------------------
// Kirim review via AJAX
// -------------------------------------------------------------
document.getElementById('ratingForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const nama = document.getElementById('formNama').value.trim();
    const rating = document.getElementById('formRatingValue').value;
    const komentar = document.getElementById('formKomentar').value.trim();
    const alertBox = document.getElementById('formAlert');

    if (!nama || !rating || !komentar) {
        alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Harap lengkapi semua data!';
        alertBox.style.display = 'flex';
        setTimeout(() => alertBox.style.display = 'none', 3000);
        return;
    }

    const formData = new FormData();
    formData.append('nama', nama);
    formData.append('rating', rating);
    formData.append('komentar', komentar);

    try {
        const response = await fetch('rating.php?ajax=submit', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.success) {
            alertBox.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
            alertBox.style.backgroundColor = '#d4edda';
            alertBox.style.color = '#155724';
            alertBox.style.display = 'flex';

            // reset form
            document.getElementById('ratingForm').reset();
            document.querySelectorAll('.star-selector i').forEach(s => {
                s.classList.remove('active', 'fa-solid');
                s.classList.add('fa-regular');
            });
            document.getElementById('formRatingValue').value = '';

            // muat ulang data review (biar muncul yang baru)
            loadReviews(currentFilter, 1);
            // reload halaman sebentar biar chart update (opsional, tapi biar konsisten)
            setTimeout(() => location.reload(), 1500);
        } else {
            alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + result.message;
            alertBox.style.backgroundColor = '#fff3cd';
            alertBox.style.color = '#856404';
            alertBox.style.display = 'flex';
            setTimeout(() => alertBox.style.display = 'none', 3000);
        }
    } catch (err) {
        alertBox.innerHTML = 'Terjadi kesalahan, coba lagi.';
        alertBox.style.display = 'flex';
        setTimeout(() => alertBox.style.display = 'none', 3000);
    }
});

// -------------------------------------------------------------
// Helper escape HTML (mencegah XSS)
// -------------------------------------------------------------
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// -------------------------------------------------------------
// Event listener untuk tombol filter bintang
// -------------------------------------------------------------
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (btn.classList.contains('disabled')) return;

        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        currentPage = 1;
        loadReviews(currentFilter, 1);
    });
});

// -------------------------------------------------------------
// Interaksi bintang pada form rating
// -------------------------------------------------------------
const starIcons = document.querySelectorAll('#rating-section .star-selector i');
const ratingInput = document.getElementById('formRatingValue');
if (starIcons.length) {
    starIcons.forEach(star => {
        star.addEventListener('click', () => {
            const val = parseInt(star.dataset.val);
            ratingInput.value = val;
            starIcons.forEach(s => {
                if (parseInt(s.dataset.val) <= val) {
                    s.classList.add('active');
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid');
                } else {
                    s.classList.remove('active');
                    s.classList.remove('fa-solid');
                    s.classList.add('fa-regular');
                }
            });
        });

        star.addEventListener('mouseenter', () => {
            const val = parseInt(star.dataset.val);
            starIcons.forEach(s => {
                if (parseInt(s.dataset.val) <= val) s.classList.add('hovered');
                else s.classList.remove('hovered');
            });
        });
    });

    document.querySelector('#rating-section .star-selector')?.addEventListener('mouseleave', () => {
        starIcons.forEach(s => s.classList.remove('hovered'));
    });
}

renderChart();
loadReviews('all', 1);
</script>