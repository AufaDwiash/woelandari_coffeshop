<?php include "config/koneksi.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/lokasi_style.css?v=<?php echo time(); ?>">
</head>
<body>

   <body>

    <div class="page-wrapper">
        <div class="container-split">
            
            <div class="column-left anim-fade-up">
                <div class="visual-frame float-animation">
                    <div class="tape-corner"></div>
                    <div class="main-image">
                        <img src="assets/images/gambar-mentahan/about3.jpeg" alt="Lab">
                    </div>
                </div>

                <div class="nav-controls">
                    <a href="#" class="nav-arrow hover-slide-left"><i class="bi bi-arrow-left"></i> SEBELUMNYA</a>
                    <a href="#" class="nav-arrow hover-slide-right">SELANJUTNYA <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="black-info-box anim-slide-in">
                    <div class="box-header">
                        <h3 class="typing-text">WOELANDARI COFFEE LAB</h3>
                        <p>LOWOKWARU, KOTA MALANG</p>
                    </div>
                    <div class="box-actions">
                        <a href="#" class="action-item">
                            <i class="bi bi-geo-alt bounce-icon"></i> PETUNJUK ARAH
                        </a>
                        <div class="divider"></div>
                        <a href="#" class="action-item">
                            <i class="bi bi-shop bounce-icon"></i> TOKO TERDEKAT
                        </a>
                    </div>
                </div>
            </div>

            <div class="column-right anim-fade-in-right">
                <header class="content-header">
                    <p class="doc-ref">// LOC_REF: ARCHIVE_01</p>
                    <h1 class="title">CEK LOKASI LAB KAMI</h1>
                </header>

                <div class="narrative">
                    <p class="highlight-text">"Tempat di mana aroma kopi bertemu dengan arsip cerita."</p>
                    <p class="main-desc">
                        Kami berlokasi di sudut tenang Lowokwaru. Lab kami bukan sekadar kedai, tapi ruang riset rasa bagi siapa saja yang ingin berbagi seduhan.
                    </p>
                </div>

                <div class="detail-archive">
                    <div class="detail-item stagger-1">
                        <label>ALAMAT</label>
                        <p>Jl. Contoh No. 123, Lowokwaru, Malang</p>
                    </div>
                    <div class="detail-item stagger-2">
                        <label>JAM OPERASIONAL</label>
                        <p>SETIAP HARI: 08:00 - 23:00 WIB</p>
                    </div>
                </div>

                <div class="social-footer">
                    <p>IKUTI JEJAK KAMI:</p>
                    <div class="social-links">
                        <a href="#" class="social-pop"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-pop"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="social-pop"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
    </script>
</body>