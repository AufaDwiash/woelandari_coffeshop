<?php // about.php - PERBAIKAN MOBILE 
?>
<section class="section-navy">
    <div class="dossier-split">
        <div class="split-col text-col">
            <div class="content-offset">
                <div class="tech-mark anim-fade-up delay-1">
                    <p class="tech-label text-cream">Filosofi</p>
                    <h2 class="editorial-heading text-cream">MAKNA & KARYA.</h2>
                    <div class="draft-divider bg-cream"></div>
                </div>
                <p class="typewriter-body text-cream anim-fade-up delay-2">
                    Woelandari bukan sekadar tempat menyeduh kopi, melainkan ruang perantara.
                    Menjadi jembatan antara dedikasi petani lokal di hulu, dan apresiasi rasa di hilir.
                </p>
                <p class="typewriter-body text-cream anim-fade-up delay-3">
                    Visi kami jelas: Mengekstrak potensi paling maksimal dari setiap biji kopi Nusantara.
                    Membawa kultur sains yang presisi, namun tetap membebaskan seni dalam setiap tegukan.
                </p>
                <p class="tech-label text-cream" style="margin-top:20px; opacity:0.6;">visi kami</p>
            </div>
        </div>
        <div class="split-col image-col">
            <div class="pinned-photo tilt-right anim-photo-right">
                <div class="red-tape"></div>
                <img src="assets/images/gambar-mentahan/about1.jpg" alt="Lab Setup Woelandari" onerror="this.src='assets/images/default.jpg'">
                <div class="photo-meta">
                    <span>arsip</span>
                    <span>kedai</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-cream">
    <div class="dossier-split reverse"> <!-- REVERSE LAYOUT via class, bukan inline style -->
        <div class="split-col text-col">
            <div class="content-offset">
                <div class="tech-mark anim-fade-up delay-1">
                    <p class="tech-label text-navy">cerita kami</p>
                    <h2 class="editorial-heading text-navy">AWAL MULA PERJALANAN.</h2>
                    <div class="draft-divider bg-navy"></div>
                </div>
                <p class="typewriter-body text-navy anim-fade-up delay-2">
                    Berawal dari sebuah garasi sempit pada tahun 2021. Tanpa papan nama,
                    hanya bermodal mesin roasting modifikasi dan ambisi untuk membedah molekul rasa kopi lokal
                    yang sering dipandang sebelah mata.
                </p>
                <p class="typewriter-body text-navy anim-fade-up delay-3">
                    Dari ratusan kegagalan kalibrasi, perlahan tumbuh menjadi ruang diskusi bagi para pecinta kopi.
                    Hingga akhirnya, garasi penuh cerita tersebut berevolusi menjadi ruang eksperimen kopi publik yang Anda nikmati hari ini."
                </p>
                <p class="tech-label text-navy" style="margin-top:20px; opacity:0.6;">titik awal</p>
            </div>
        </div>
        <div class="split-col image-col">
            <div class="pinned-photo tilt-left anim-photo-left">
                <div class="red-tape alt-pos"></div>
                <img src="assets/images/gambar-mentahan/about2.jpg" alt="Early Garage Setup" onerror="this.src='assets/images/default.jpg'">
                <div class="photo-meta">
                    <span>arsip</span>
                    <span>produk</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });
            document.querySelectorAll(".anim-fade-up, .anim-photo-right, .anim-photo-left").forEach(el => {
                observer.observe(el);
            });
        } else {
            // Fallback
            document.querySelectorAll(".anim-fade-up, .anim-photo-right, .anim-photo-left").forEach(el => {
                el.classList.add("is-visible");
            });
        }
    });
</script>