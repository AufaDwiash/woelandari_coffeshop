<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Woelandari Coffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap"
        rel="stylesheet">
    <!-- <link rel="stylesheet" href="C:\xampp\htdocs\woelandari_coffeshop\assets\css\rating_style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap");

        :root {
            /* Skema Warna Cream/Beige (Identik dengan Gallery) */
            --espresso: #3e2723;
            --terracotta: #d35400;
            --bg-cream: #efebe0;
            --grid-line: #dcd5c4;
            --card-surface: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Courier Prime", monospace;
            background-color: var(--bg-cream);
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--espresso);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 0%,
                    rgba(255, 255, 255, 0.4),
                    transparent 70%);
            pointer-events: none;
            z-index: -1;
        }

        .archive-wrapper {
            width: 100%;
            max-width: 1200px;
        }

        /* --- HEADER GAYA GALERI --- */
        .header-gallery {
            text-align: center;
            background: var(--card-surface);
            border: 3px solid var(--espresso);
            padding: 40px 20px;
            margin-bottom: 40px;
            box-shadow: 8px 8px 0 var(--terracotta);
            position: relative;
        }

        .header-gallery::before {
            content: "";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%) rotate(-1deg);
            width: 150px;
            height: 30px;
            background: rgba(211, 84, 0, 0.8);
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 5;
        }

        .doc-ref {
            font-weight: bold;
            font-size: 0.9rem;
            opacity: 0.7;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header-gallery h1 {
            font-family: "Special Elite", cursive;
            font-size: 3rem;
            color: var(--espresso);
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .subtitle {
            font-size: 0.9rem;
            font-weight: bold;
            max-width: 600px;
            margin: 0 auto 20px;
            border-bottom: 2px dashed var(--espresso);
            padding-bottom: 20px;
            opacity: 0.8;
        }

        .global-score-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        #avgScore {
            font-family: "Special Elite", cursive;
            font-size: 4rem;
            font-weight: bold;
            line-height: 1;
            color: var(--terracotta);
        }

        .stars-main {
            color: var(--terracotta);
            font-size: 1.2rem;
        }

        /* --- MAIN LAYOUT --- */
        .main-content-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 40px;
            align-items: start;
        }

        /* --- FORM POLAROID --- */
        .input-sidebar {
            position: sticky;
            top: 20px;
        }

        .form-polaroid {
            background: var(--card-surface);
            border: 2px solid var(--espresso);
            padding: 25px;
            box-shadow: 8px 8px 0 var(--espresso);
        }

        .form-title {
            font-family: "Special Elite", cursive;
            font-size: 1.2rem;
            margin-bottom: 25px;
            border-bottom: 1px dashed var(--espresso);
            padding-bottom: 10px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .vintage-input {
            width: 100%;
            border: none;
            border-bottom: 2px solid var(--espresso);
            background: transparent;
            padding: 10px 0;
            font-family: "Courier Prime", monospace;
            font-weight: bold;
            color: var(--espresso);
            outline: none;
            transition: 0.3s;
        }

        .vintage-input:focus {
            border-color: var(--terracotta);
            padding-left: 5px;
        }

        /* Bintang Interaktif */
        .rating-group {
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .stars-picker {
            font-size: 2rem;
            cursor: pointer;
            display: flex;
            gap: 5px;
            margin-top: 10px;
            color: var(--espresso);
        }

        .star-icon {
            transition:
                color 0.2s,
                transform 0.2s;
        }

        .star-icon.hovered,
        .star-icon.fa-solid {
            color: var(--terracotta);
            transform: scale(1.1);
        }

        @keyframes bounceAnim {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.4);
            }

            100% {
                transform: scale(1);
            }
        }

        .bounce-anim {
            animation: bounceAnim 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Tombol */
        .btn-submit,
        .btn-tab,
        .btn-back {
            width: 100%;
            padding: 12px 25px;
            background: var(--bg-cream);
            border: 2px solid var(--espresso);
            color: var(--espresso);
            font-family: "Special Elite", cursive;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-submit:hover,
        .btn-back:hover {
            background: var(--espresso);
            color: var(--card-surface);
            box-shadow: 5px 5px 0 var(--terracotta);
            transform: translateY(-2px);
        }

        #mobileFormToggle {
            display: none;
        }

        /* --- CONTROLS FEED --- */
        .feed-controls {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 2px dashed var(--espresso);
            padding-bottom: 15px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .vintage-select {
            background: var(--card-surface);
            border: 2px solid var(--espresso);
            color: var(--espresso);
            padding: 5px 10px;
            font-family: "Courier Prime", monospace;
            font-weight: bold;
            cursor: pointer;
            outline: none;
        }

        /* --- GRID POLAROID CARDS --- */
        .polaroid-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding-top: 10px;
        }

        .archive-card {
            background: var(--card-surface);
            border: 2px solid var(--espresso);
            padding: 15px 15px 25px 15px;
            /* Styling polaroid tebal di bawah */
            box-shadow: 3px 5px 15px rgba(0, 0, 0, 0.08);
            transition: 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative;
            opacity: 0;
            /* Untuk animasi awal */
        }

        /* Acak Miring */
        .archive-card:nth-child(odd) {
            transform: rotate(-2deg);
        }

        .archive-card:nth-child(even) {
            transform: rotate(2deg);
        }

        .archive-card:nth-child(3n) {
            transform: rotate(-1deg) scale(0.98);
        }

        .archive-card:hover {
            transform: rotate(0deg) scale(1.03);
            z-index: 10;
            box-shadow: 8px 8px 0 var(--terracotta);
            border-color: var(--terracotta);
        }

        @keyframes fadeInPolaroid {
            from {
                opacity: 0;
                transform: translateY(20px) rotate(0);
            }

            to {
                opacity: 1;
            }
        }

        .animate-polaroid {
            animation: fadeInPolaroid 0.6s forwards;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed var(--espresso);
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            opacity: 0.7;
        }

        .card-title {
            font-family: "Special Elite", cursive;
            font-size: 1.1rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .rev-stars {
            color: var(--terracotta);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .card-desc {
            font-size: 0.9rem;
            line-height: 1.5;
            font-style: italic;
        }

        /* --- SKELETON & EMPTY --- */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            border: 2px dashed var(--espresso);
        }

        .empty-state h3 {
            font-family: "Special Elite", cursive;
            margin-bottom: 10px;
        }

        @keyframes shimmerVintage {
            0% {
                background-color: var(--grid-line);
            }

            50% {
                background-color: #e3dbcc;
            }

            100% {
                background-color: var(--grid-line);
            }
        }

        .skeleton-box {
            border-color: var(--grid-line);
            pointer-events: none;
            opacity: 1;
        }

        .skeleton-line {
            background-color: var(--grid-line);
            animation: shimmerVintage 1.5s infinite;
            margin-bottom: 10px;
        }

        /* --- TOAST --- */
        .vintage-toast {
            position: fixed;
            bottom: 30px;
            right: -400px;
            background: var(--espresso);
            color: var(--bg-cream);
            padding: 15px 25px;
            font-family: "Courier Prime", monospace;
            font-weight: bold;
            border: 2px solid var(--terracotta);
            box-shadow: 5px 5px 0 var(--terracotta);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
        }

        .vintage-toast.show {
            right: 30px;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .main-content-layout {
                grid-template-columns: 1fr;
            }

            .header-gallery h1 {
                font-size: 2.2rem;
            }

            #mobileFormToggle {
                display: block;
            }

            .input-sidebar {
                display: none;
                position: static;
            }

            .input-sidebar.active {
                display: block;
                animation: fadeInPolaroid 0.3s forwards;
            }
        }

        @media (max-width: 600px) {
            .polaroid-grid {
                grid-template-columns: 1fr;
            }

            .archive-card {
                transform: rotate(0) !important;
            }

            /* Matikan kemiringan di HP agar rapi */
            .filter-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="archive-wrapper">
        <header class="header-gallery">
            <p class="doc-ref">WOELANDARI EXPERIENCE</p>
            <h1>CUSTOMER VOICES</h1>
            <p class="subtitle">BERKUMPUL UNTUK BERBAGI, BERKARYA UNTUK TUMBUH, DAN MENCIPTAKAN CERITA YANG BERMAKNA</p>

            <div class="global-score-badge">
                <span id="avgScore">0.0</span>
                <div class="stars-main" id="avgStarsDisplay"></div>
            </div>
        </header>

        <div class="main-content-layout">
            <button id="mobileFormToggle" class="btn-tab" style="width: 100%; margin-bottom: 20px;">TULIS ULASAN <i
                    class="fa-solid fa-pen"></i></button>

            <aside class="input-sidebar" id="sidebarForm">
                <div class="form-polaroid">
                    <h2 class="form-title">LEAVE A RECORD</h2>
                    <form id="reviewForm">
                        <div class="input-group">
                            <input type="text" id="inputName" class="vintage-input" placeholder="NAMA LENGKAP" required
                                autocomplete="off">
                        </div>

                        <div class="rating-group">
                            <p>RATING:</p>
                            <div class="stars-picker" id="starSelector">
                                <i class="fa-regular fa-star star-icon" data-val="1"></i>
                                <i class="fa-regular fa-star star-icon" data-val="2"></i>
                                <i class="fa-regular fa-star star-icon" data-val="3"></i>
                                <i class="fa-regular fa-star star-icon" data-val="4"></i>
                                <i class="fa-regular fa-star star-icon" data-val="5"></i>
                            </div>
                            <input type="hidden" id="ratingValue" required>
                        </div>

                        <div class="input-group">
                            <textarea id="inputComment" class="vintage-input" placeholder="TULIS PENGALAMAN ANDA..."
                                required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">SIMPAN ARSIP</button>
                    </form>
                </div>
            </aside>

            <section class="feed-container">
                <div class="feed-controls">
                    <span id="totalReviews" class="doc-ref" style="margin-bottom:0;">0 ARSIP</span>

                    <div class="filter-actions">
                        <select id="filterRating" class="vintage-select">
                            <option value="all">SEMUA RATING</option>
                            <option value="5">5 BINTANG</option>
                            <option value="4">4 BINTANG</option>
                            <option value="3">3 BINTANG</option>
                            <option value="2">2 BINTANG</option>
                            <option value="1">1 BINTANG</option>
                        </select>
                        <select id="sortReview" class="vintage-select">
                            <option value="newest">TERBARU</option>
                            <option value="highest">TERTINGGI</option>
                        </select>
                    </div>
                </div>

                <div id="reviewsContainer" class="polaroid-grid">
                </div>
            </section>
        </div>

        <a href="index.php" class="btn-back" style="margin: 40px auto; display: block; width: fit-content;">← RETURN TO
            MAIN MENU</a>
    </div>

    <div id="toastNotification" class="vintage-toast">
        <i class="fa-solid fa-check"></i> <span id="toastMsg">Arsip tersimpan.</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('.star-icon');
            const ratingInput = document.getElementById('ratingValue');
            const reviewForm = document.getElementById('reviewForm');
            const reviewsContainer = document.getElementById('reviewsContainer');
            const filterRating = document.getElementById('filterRating');
            const sortReview = document.getElementById('sortReview');
            const mobileToggle = document.getElementById('mobileFormToggle');
            const sidebarForm = document.getElementById('sidebarForm');
            const toast = document.getElementById('toastNotification');

            let selectedRating = 0;

            triggerRender();

            // Interaksi Bintang (Hover & Bounce)
            stars.forEach((star, index) => {
                star.addEventListener('mouseover', () => {
                    stars.forEach((s, i) => {
                        if (i <= index) {
                            s.classList.remove('fa-regular');
                            s.classList.add('fa-solid', 'hovered');
                        } else {
                            s.classList.remove('fa-solid', 'hovered');
                            s.classList.add('fa-regular');
                        }
                    });
                });

                star.addEventListener('mouseout', () => highlightStars(selectedRating));

                star.addEventListener('click', () => {
                    selectedRating = parseInt(star.dataset.val);
                    ratingInput.value = selectedRating;
                    highlightStars(selectedRating);

                    star.classList.remove('bounce-anim');
                    void star.offsetWidth;
                    star.classList.add('bounce-anim');
                });
            });

            function highlightStars(val) {
                stars.forEach(s => {
                    s.classList.remove('hovered');
                    if (parseInt(s.dataset.val) <= val) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid');
                    } else {
                        s.classList.remove('fa-solid');
                        s.classList.add('fa-regular');
                    }
                });
            }

            // Submit Form
            reviewForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (selectedRating === 0) return showToast("Mohon pilih rating bintang.", "error");

                const newReview = {
                    id: Date.now(),
                    nama: document.getElementById('inputName').value,
                    rating: parseInt(selectedRating),
                    komentar: document.getElementById('inputComment').value,
                    tanggal: new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase()
                };

                const reviews = getReviews();
                reviews.unshift(newReview);
                localStorage.setItem('woelandari_reviews', JSON.stringify(reviews));

                reviewForm.reset();
                selectedRating = 0;
                highlightStars(0);
                showToast("Arsip review berhasil ditambahkan.", "success");
                sidebarForm.classList.remove('active');

                triggerRender();
            });

            function showToast(msg, type) {
                const toastMsg = document.getElementById('toastMsg');
                toastMsg.innerText = msg;
                toast.className = `vintage-toast show ${type}`;
                setTimeout(() => { toast.classList.remove('show'); }, 3000);
            }

            filterRating.addEventListener('change', triggerRender);
            sortReview.addEventListener('change', triggerRender);

            mobileToggle.addEventListener('click', () => {
                sidebarForm.classList.toggle('active');
            });

            function getReviews() {
                return JSON.parse(localStorage.getItem('woelandari_reviews')) || [];
            }

            function triggerRender() {
                updateHeaderStats();
                showSkeleton();
                setTimeout(renderReviews, 800); // Simulasi loading
            }

            function updateHeaderStats() {
                const allReviews = getReviews();
                let avg = allReviews.length ? (allReviews.reduce((a, b) => a + b.rating, 0) / allReviews.length).toFixed(1) : "0.0";

                document.getElementById('avgScore').innerText = avg;

                let starsMain = '';
                for (let i = 1; i <= 5; i++) {
                    starsMain += i <= Math.round(avg) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                }
                document.getElementById('avgStarsDisplay').innerHTML = starsMain;
            }

            function showSkeleton() {
                reviewsContainer.innerHTML = '';
                for (let i = 0; i < 4; i++) {
                    reviewsContainer.innerHTML += `
                        <div class="archive-card skeleton-box">
                            <div class="skeleton-line" style="width: 40%; height: 15px;"></div>
                            <div class="skeleton-line" style="width: 70%; height: 25px;"></div>
                            <div class="skeleton-line" style="width: 50%; height: 20px;"></div>
                            <div class="skeleton-line" style="width: 100%; height: 60px; margin-top: 15px;"></div>
                        </div>
                    `;
                }
            }

            function renderReviews() {
                let reviews = getReviews();
                const filterVal = filterRating.value;
                if (filterVal !== 'all') reviews = reviews.filter(r => r.rating === parseInt(filterVal));

                const sortVal = sortReview.value;
                if (sortVal === 'highest') reviews.sort((a, b) => b.rating - a.rating);
                else reviews.sort((a, b) => b.id - a.id);

                document.getElementById('totalReviews').innerText = `DATA: ${reviews.length} ARSIP`;
                reviewsContainer.innerHTML = '';

                if (reviews.length === 0) {
                    reviewsContainer.innerHTML = `
                        <div class="empty-state">
                            <h3>ARSIP KOSONG</h3>
                            <p>Belum ada data review pada filter ini.</p>
                        </div>
                    `;
                    return;
                }

                reviews.forEach((rev, index) => {
                    let s = '';
                    for (let i = 1; i <= 5; i++) s += i <= rev.rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';

                    const el = document.createElement('div');
                    // Tambahkan class delay untuk animasi muncul berurutan
                    el.className = 'archive-card animate-polaroid';
                    el.style.animationDelay = `${index * 0.1}s`;

                    el.innerHTML = `
                        <div class="card-info">
                            <div class="card-meta">
                                <span>// DOC ID: ${rev.id.toString().slice(-5)}</span>
                                <span>${rev.tanggal}</span>
                            </div>
                            <h3 class="card-title">${rev.nama}</h3>
                            <div class="rev-stars">${s}</div>
                            <p class="card-desc">"${rev.komentar}"</p>
                        </div>
                    `;
                    reviewsContainer.appendChild(el);
                });
            }
        });
    </script>
</body>

</html>