<?php 

include 'components/navbar.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titik Temu — Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/lokasi_full_style.css?v=<?php echo time(); ?>">
</head>
<body>

<section id="lokasi-section">
    <div class="lok-container">
        
        <div class="lok-header-matrix">
            <div class="lok-tag">LOKASI KAMI</div>
            <h2 class="lok-title-matrix">KOORDINASI TITIK TEMU.</h2>
            <div class="draft-divider"></div>
        </div>

        <div class="lokasi-grid-creative">
            
            <div class="lok-col-narrative">
                
                <div class="dossier-card narrative-card">
                    <div class="red-stamp">VERIFIED SITE</div>
                    <p class="typewriter-text">
                        Bukan sekadar deretan angka bujur dan lintang pada peta satelit. Ruang ini dibangun sebagai laboratorium interaksi organik.
                    </p>
                    <p class="typewriter-text">
                        Tempat di mana seduhan kopi mempertemukan ragam cerita, perdebatan ide, dan kolaborasi manusia. Lebih dari sekadar kedai, ini adalah titik koordinat tempat gagasan diekstraksi. Kami menantimu di titik ini.
                    </p>
                    <div class="tech-label">CATATAN EKSTRAKSI</div>
                </div>

                <div class="dossier-card photo-card">
                    <div class="img-frame">
                        <img src="assets/images/gambar-mentahan/about3.jpeg" alt="Woelandari Lab Site" onerror="this.src='assets/images/default.jpg'">
                        <div class="frame-overlay">Arsip Kedai</div>
                    </div>
                </div>

                <div class="dossier-card card-dark">
                    <div class="card-label">Jam Operasional</div>
                    <div class="hours-dossier">
                        <div class="hours-row"><span>MON - SAT</span><span>08:00 - 22:00</span></div>
                        <div class="hours-row highlight-weekend"><span>SUNDAY</span><span>08:00 - 22:00</span></div>
                    </div>
                    <div class="status-indicator">
                        <span class="pulse-dot"></span> STATUS SISTEM: ONLINE & BREWING
                    </div>
                </div>

            </div>

            <div class="lok-col-data">
                
                <div class="dossier-card maps-card">
                    <div class="card-label">// RADAR LOKASI</div>
                    <div class="map-iframe-wrapper">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.195726227181!2d112.9031263!3d-7.6611369!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7c5fc11c6d321%3A0x6b490d1bf4bbf25d!2sJl.%20Panglima%20Sudirman%20VII%20No.12%2C%20Purworejo%2C%20Kec.%20Purworejo%2C%20Kota%20Pasuruan%2C%20Jawa%20Timur%2067115!5e0!3m2!1sid!2sid!4v1710000000000!5m2!1sid!2sid" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="map-grid-overlay"></div>
                    </div>
                    <a href="https://share.google/UqJrJp2XknIhXRBhv" target="_blank" rel="noopener noreferrer" class="btn-maps-redirect">
                        MULAI NAVIGASI <i class="fa-solid fa-satellite-dish"></i>
                    </a>
                </div>

                <div class="address-contact-flex">
                    <div class="dossier-card card-cream">
                        <div class="card-label">COORDINAT LOCATION</div>
                        <h4 class="dossier-city">PASURUAN, ID</h4>
                        <p class="dossier-address">
                            Jl. Panglima Sudirman VII No. 12,<br>
                            Kec. Purworejo, Kota Pasuruan,<br>
                            Jawa Timur, Kode Pos 67115
                        </p>
                        <div class="barcode-wrapper">
                            <div class="barcode-lines"></div>
                            <div class="barcode-text">LOC_NET_9910A</div>
                        </div>
                    </div>

                    <div class="dossier-card communications-card">
                        <div class="card-label">CONTACT US</div>
                        <div class="contact-channels">
                            <a href="https://wa.me/6289677718775" target="_blank" class="wa-dossier-box">
                                <div class="wa-info"><span class="wa-title">ADMIN</span><span class="wa-phone">+62 896-7771-8775</span></div>
                                <i class="fa-brands fa-whatsapp wa-dossier-icon"></i>
                            </a>
                            <a href="https://wa.me/6289677718775" target="_blank" class="wa-dossier-box">
                                <div class="wa-info"><span class="wa-title">DEVELOPER</span><span class="wa-phone">+62 896-7771-8775</span></div>
                                <i class="fa-brands fa-whatsapp wa-dossier-icon"></i>
                            </a>
                        </div>
                        <div class="social-archive-grid">
                            <a href="https://www.instagram.com/tokokopi.woelandari?igsh=bGs2ZzZiN2Fqcnl5" target="_blank" class="soc-item"><i class="fa-brands fa-instagram"></i></a>
                            <a href="https://www.tiktok.com/@toko.kopi.woelandari?_r=1&_t=ZS-96SQUmif1nt" target="_blank" class="soc-item"><i class="fa-brands fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div> 
    </div>
</section>

</body>
</html>