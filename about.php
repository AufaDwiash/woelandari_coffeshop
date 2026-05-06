<?php // about.php - VERSI BERSIH TANPA TAG HTML/BODY ?>

<section class="section-navy">
    <div class="dossier-split">
        <div class="split-col text-col">
            <div class="content-offset">
                <div class="tech-mark anim-fade-up delay-1">
                    <p class="tech-label text-cream">// LOG_01 : PHILOSOPHY</p>
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
                <p class="tech-label text-cream" style="margin-top:20px; opacity:0.6;">FIG. 01 — VISION EXTRACT</p>
            </div>
        </div>
        <div class="split-col image-col">
            <div class="pinned-photo tilt-right anim-photo-right">
                <div class="red-tape"></div>
                <img src="assets/images/gambar-mentahan/about1.jpg" alt="Lab Setup Woelandari">
                <div class="photo-meta">
                    <span>REF: PHI_01</span>
                    <span>LOC: GARAGE LAB</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-cream">
    <div class="dossier-split" style="flex-direction: row-reverse;">
        <div class="split-col text-col">
            <div class="content-offset">
                <div class="tech-mark anim-fade-up delay-1">
                    <p class="tech-label text-navy">// LOG_02 : HISTORY RECORD</p>
                    <h2 class="editorial-heading text-navy">AWAL MULA EKSPERIMEN.</h2>
                    <div class="draft-divider bg-navy"></div>
                </div>
                <p class="typewriter-body text-navy anim-fade-up delay-2">
                    Berawal dari sebuah garasi sempit pada tahun 2021. Tanpa papan nama, 
                    hanya bermodal mesin roasting modifikasi dan ambisi untuk membedah molekul rasa kopi lokal 
                    yang sering dipandang sebelah mata.
                </p>
                <p class="typewriter-body text-navy anim-fade-up delay-3">
                    Dari ratusan kegagalan kalibrasi, perlahan tumbuh menjadi ruang diskusi bagi para pecinta kopi. 
                    Hingga akhirnya, garasi itu berevolusi menjadi fasilitas laboratorium publik yang Anda pijak hari ini.
                </p>
                <p class="tech-label text-navy" style="margin-top:20px; opacity:0.6;">FIG. 02 — THE GENESIS</p>
            </div>
        </div>
        <div class="split-col image-col">
            <div class="pinned-photo tilt-left anim-photo-left">
                <div class="red-tape alt-pos"></div>
                <img src="assets/images/gambar-mentahan/about2.jpg" alt="Early Garage Setup">
                <div class="photo-meta">
                    <span>REF: HIS_01</span>
                    <span>LOC: ORIGIN SITE</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target); // Hanya trigger 1x
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll(".anim-fade-up, .anim-photo-right, .anim-photo-left").forEach(el => {
        observer.observe(el);
    });
});
</script>