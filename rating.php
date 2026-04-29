<?php
require_once 'config/koneksi.php';

// 1. LOGIKA SIMPAN DATA (Tetap sama)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $rating = (int)$_POST['rating'];
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
    
    $sql_insert = "INSERT INTO feedback (nama_pelanggan, rating, komentar, status_moderasi) 
                   VALUES ('$nama', $rating, '$komentar', 'tampil')";
    
    if (mysqli_query($conn, $sql_insert)) {
        header("Location: rating.php?status=success");
        exit();
    }
}

// 2. AMBIL DATA (Query dasar)
$query = "SELECT id_feedback, nama_pelanggan, rating, komentar, 
          DATE_FORMAT(created_at, '%d %b %Y') as tanggal 
          FROM feedback 
          WHERE status_moderasi = 'tampil' 
          ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

$reviews_db = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews_db[] = [
            'id' => $row['id_feedback'],
            'nama' => $row['nama_pelanggan'],
            'rating' => (int)$row['rating'],
            'komentar' => $row['komentar'],
            'tanggal' => strtoupper($row['tanggal'])
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Woelandari Coffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/rating_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS OVERRIDE UNTUK LAYOUT BARU */
        .main-content-layout {
            display: flex;
            flex-direction: column; /* Mengatur urutan vertikal: Form di atas, Feed di bawah */
            gap: 40px;
        }

        .input-sidebar {
            width: 100% !important; /* Memaksa form memenuhi lebar layar */
            position: static !important;
        }

        .form-polaroid {
            max-width: 100%; /* Agar full ke kanan */
            background: white;
            border: 3px solid #000;
            box-shadow: 15px 15px 0px #000;
            padding: 30px;
        }

        #reviewForm {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Membagi form menjadi 2 kolom agar tidak terlalu tinggi */
            gap: 20px;
        }

        .input-group-full { grid-column: span 2; }

        .feed-container {
            width: 100%;
            border-top: 5px double #000;
            padding-top: 40px;
        }

        /* Styling Filter Baru */
        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            #reviewForm { grid-template-columns: 1fr; }
            .input-group-full { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="archive-wrapper">
    <header class="header-gallery">
        <p class="doc-ref">WOELANDARI EXPERIENCE</p>
        <h1>CUSTOMER VOICES</h1>
        <div class="global-score-badge">
            <span id="avgScore">0.0</span>
            <div class="stars-main" id="avgStarsDisplay"></div>
        </div>
    </header>

    <div class="main-content-layout">
        <aside class="input-sidebar">
            <div class="form-polaroid">
                <h2 class="form-title">// LEAVE_A_RECORD</h2>
                <form id="reviewForm" method="POST" action="rating.php">
                    <div class="input-group">
                        <label class="doc-ref">IDENTIFICATION</label>
                        <input type="text" name="nama" class="vintage-input" placeholder="NAMA LENGKAP" required>
                    </div>
                    
                    <div class="rating-group">
                        <label class="doc-ref">EXPERIENCE_SCORE</label>
                        <div class="stars-picker" id="starSelector" style="font-size: 1.5rem; margin-top: 10px;">
                            <i class="fa-regular fa-star star-icon" data-val="1"></i>
                            <i class="fa-regular fa-star star-icon" data-val="2"></i>
                            <i class="fa-regular fa-star star-icon" data-val="3"></i>
                            <i class="fa-regular fa-star star-icon" data-val="4"></i>
                            <i class="fa-regular fa-star star-icon" data-val="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>

                    <div class="input-group input-group-full">
                        <label class="doc-ref">MESSAGE_CONTENT</label>
                        <textarea name="komentar" class="vintage-input" placeholder="TULIS PENGALAMAN ANDA..." style="height: 100px;" required></textarea>
                    </div>
                    
                    <div class="input-group-full">
                        <button type="submit" class="btn-submit" style="width: 100%;">SUBMIT TO ARCHIVE</button>
                    </div>
                </form>
            </div>
        </aside>

        <section class="feed-container">
            <div class="feed-controls">
                <span id="totalReviews" class="doc-ref">0 ARSIP</span>
                
                <div class="filter-actions">
                    <select id="limitView" class="vintage-select">
                        <option value="5">TAMPILKAN 5</option>
                        <option value="15">TAMPILKAN 15</option>
                        <option value="20">TAMPILKAN 20</option>
                        <option value="50">TAMPILKAN 50</option>
                        <option value="all">SEMUA</option>
                    </select>

                    <select id="filterRating" class="vintage-select">
                        <option value="all">SEMUA BINTANG</option>
                        <option value="5">5 BINTANG</option>
                        <option value="1">1 BINTANG</option>
                    </select>

                    <select id="sortReview" class="vintage-select">
                        <option value="newest">TERBARU</option>
                        <option value="highest">RATING TERTINGGI</option>
                    </select>
                </div>
            </div>
            
            <div id="reviewsContainer" class="polaroid-grid"></div>
        </section>
    </div>

    <a href="index.php" class="btn-back">← RETURN TO MAIN MENU</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dbReviews = <?php echo json_encode($reviews_db); ?>;
        const reviewsContainer = document.getElementById('reviewsContainer');
        const limitView = document.getElementById('limitView');
        const filterRating = document.getElementById('filterRating');
        const sortReview = document.getElementById('sortReview');
        const stars = document.querySelectorAll('.star-icon');
        const ratingInput = document.getElementById('ratingValue');

        let selectedRating = 0;

        // Bintang Logic
        stars.forEach(star => {
            star.addEventListener('click', () => {
                selectedRating = star.dataset.val;
                ratingInput.value = selectedRating;
                updateStars(selectedRating);
            });
        });

        function updateStars(val) {
            stars.forEach(s => {
                s.classList.toggle('fa-solid', s.dataset.val <= val);
                s.classList.toggle('fa-regular', s.dataset.val > val);
            });
        }

        // Render Logic
        function render() {
            let data = [...dbReviews];

            // 1. Filter Rating
            if(filterRating.value !== 'all') {
                data = data.filter(r => r.rating == filterRating.value);
            }

            // 2. Sorting
            if(sortReview.value === 'highest') {
                data.sort((a,b) => b.rating - a.rating);
            } else {
                data.sort((a,b) => b.id - a.id); // Newest
            }

            // 3. Limit (5, 15, 20, 50)
            if(limitView.value !== 'all') {
                data = data.slice(0, parseInt(limitView.value));
            }

            document.getElementById('totalReviews').innerText = `SHOWING: ${data.length} RECORDS`;
            reviewsContainer.innerHTML = data.map(rev => `
                <div class="archive-card animate-polaroid">
                    <div class="card-meta">
                        <span>#${rev.id}</span>
                        <span>${rev.tanggal}</span>
                    </div>
                    <h3 class="card-title">${rev.nama}</h3>
                    <div class="rev-stars">${'<i class="fa-solid fa-star"></i>'.repeat(rev.rating)}</div>
                    <p class="card-desc">"${rev.komentar}"</p>
                </div>
            `).join('') || '<p>Arsip tidak ditemukan.</p>';
        }

        [limitView, filterRating, sortReview].forEach(el => el.addEventListener('change', render));
        render();
    });
</script>

</body>
</html>