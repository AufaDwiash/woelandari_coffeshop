<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive: About - Woelandari Coffee Lab</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/public_nav.css">
    <link rel="stylesheet" href="assets/css/about_style.css">
</head>
<body>
    <nav class="public-nav">
        <a href="index.php">Beranda</a>
        <a href="menu.php">Menu</a>
        <a href="gallery.php">Gallery</a>
    </nav>
    <main class="blueprint-canvas">
        
        <section class="dossier-split section-navy">
            <div class="split-col text-col">
                <div class="content-offset">
                    <div class="tech-mark anim-fade-up delay-1">
                        <span class="tech-label text-cream">// LOG_01 : PHILOSOPHY</span>
                    </div>
                    
                    <h1 class="editorial-heading text-cream anim-fade-up delay-2">MAKNA<br>ANGAN<br>KARYA.</h1>
                    <div class="draft-divider bg-cream anim-fade-up delay-3"></div>
                    
                    <p class="typewriter-body text-cream anim-fade-up delay-4">
                        Woelandari bukan sekadar tempat menyeduh kopi, melainkan ruang perantara. Menjadi jembatan antara dedikasi petani lokal di hulu, dan apresiasi rasa di hilir. 
                    </p>
                    <p class="typewriter-body text-cream anim-fade-up delay-4">
                        Visi kami jelas: Mengekstrak potensi paling maksimal dari setiap biji kopi Nusantara. Membawa kultur sains yang presisi, namun tetap membebaskan seni dalam setiap tegukan.
                    </p>
                </div>
            </div>

            <div class="split-col image-col">
                <div class="pinned-photo tilt-right anim-photo-right">
                    <div class="red-tape"></div>
                    <img src="assets/images/gambar-mentahan/about1.jpg" alt="Filosofi Kopi">
                    <div class="photo-meta">
                        <span>FIG. 01 — VISION EXTRACT</span>
                        <span class="barcode">||| |||| || |||</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="dossier-split section-cream">
            <div class="split-col image-col">
                <div class="pinned-photo tilt-left anim-photo-left">
                    <div class="red-tape alt-pos"></div>
                    <img src="assets/images/gambar-mentahan/about2.jpg" alt="Sejarah Awal">
                    <div class="photo-meta text-navy">
                        <span>FIG. 02 — THE GENESIS</span>
                        <span class="barcode">|| ||||| || |||</span>
                    </div>
                </div>
            </div>

            <div class="split-col text-col">
                <div class="content-offset">
                    <div class="tech-mark anim-fade-up delay-1">
                        <span class="tech-label text-navy">// LOG_02 : HISTORY RECORD</span>
                    </div>
                    
                    <h2 class="editorial-heading text-navy anim-fade-up delay-2">AWAL MULA<br>EKSPERIMEN.</h2>
                    <div class="draft-divider bg-navy anim-fade-up delay-3"></div>
                    
                    <p class="typewriter-body text-navy anim-fade-up delay-4">
                        Berawal dari sebuah garasi sempit pada tahun 2021. Tanpa papan nama, hanya bermodal mesin <i>roasting</i> modifikasi dan ambisi untuk membedah molekul rasa kopi lokal yang sering dipandang sebelah mata.
                    </p>
                    <p class="typewriter-body text-navy anim-fade-up delay-4">
                        Dari ratusan kegagalan kalibrasi, perlahan tumbuh menjadi ruang diskusi bagi para pecinta kopi. Hingga akhirnya, garasi itu berevolusi menjadi fasilitas laboratorium publik yang Anda pijak hari ini.
                    </p>
                </div>
            </div>
        </section>

    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Animasi hanya berjalan jika lebar layar lebih dari 992px (Desktop/Laptop)
            if (window.innerWidth > 992) {
                const observerOptions = {
                    threshold: 0.2 // Animasi terpicu saat elemen terlihat 20% di layar
                };

                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target); // Hanya animasi 1 kali
                        }
                    });
                }, observerOptions);

                // Pantau semua elemen yang punya class animasi ini
                const animatedElements = document.querySelectorAll('.anim-fade-up, .anim-photo-right, .anim-photo-left');
                animatedElements.forEach(el => observer.observe(el));
            }
        });
    </script>
</body>
</html>
