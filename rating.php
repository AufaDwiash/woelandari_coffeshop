<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Woelandari Coffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/rating_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <button id="mobileFormToggle" class="btn-tab" style="width: 100%; margin-bottom: 20px;">TULIS ULASAN <i class="fa-solid fa-pen"></i></button>

            <aside class="input-sidebar" id="sidebarForm">
                <div class="form-polaroid">
                    <h2 class="form-title">LEAVE A RECORD</h2>
                    <form id="reviewForm">
                        <div class="input-group">
                            <input type="text" id="inputName" class="vintage-input" placeholder="NAMA LENGKAP" required autocomplete="off">
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
                            <textarea id="inputComment" class="vintage-input" placeholder="TULIS PENGALAMAN ANDA..." required></textarea>
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

        <a href="index.php" class="btn-back" style="margin: 40px auto; display: block; width: fit-content;">← RETURN TO MAIN MENU</a>
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
                        if(i <= index) {
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
                    if(parseInt(s.dataset.val) <= val) {
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
                for(let i=1; i<=5; i++) {
                    starsMain += i <= Math.round(avg) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                }
                document.getElementById('avgStarsDisplay').innerHTML = starsMain;
            }

            function showSkeleton() {
                reviewsContainer.innerHTML = '';
                for(let i=0; i<4; i++) {
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
                if(filterVal !== 'all') reviews = reviews.filter(r => r.rating === parseInt(filterVal));

                const sortVal = sortReview.value;
                if(sortVal === 'highest') reviews.sort((a, b) => b.rating - a.rating);
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
                    for(let i=1; i<=5; i++) s += i <= rev.rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                    
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