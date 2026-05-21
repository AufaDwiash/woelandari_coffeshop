<?php 
include "config/koneksi.php"; 
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual Archives - Woelandari Coffee Lab</title>
    <link rel="stylesheet" href="assets/css/gallery_style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="header-gallery">
        <p class="doc-ref">WOELANDARI EXPERIENCE</p>
        <h1>VISUAL ARCHIVES</h1>
        <p class="subtitle">BERKUMPUL UNTUK BERBAGI, BERKARYA UNTUK TUMBUH, DAN MENCIPTAKAN CERITA YANG BERMAKNA</p>
        <div class="tab-controls">
            <button class="btn-tab active" onclick="switchTab(event, 'tab-gallery')">COLLECTION</button>
            <button class="btn-tab" onclick="switchTab(event, 'tab-event')">BIG EVENTS</button>
        </div>
    </div>

    <div id="tab-gallery" class="tab-content active">
        <?php
        $query_gal = mysqli_query($conn, "SELECT gallery.*, events.judul_event FROM gallery LEFT JOIN events ON gallery.id_event = events.id_event ORDER BY gallery.id_gallery DESC");
        if(mysqli_num_rows($query_gal) > 0):
            while($g = mysqli_fetch_assoc($query_gal)):
                $img_src = !empty($g['file_foto']) ? 'data:image/jpeg;base64,' . base64_encode($g['file_foto']) : 'assets/images/default.jpg';
        ?>
            <div class="archive-card">
                <div class="card-img-wrapper">
                    <img src="<?php echo $img_src; ?>" alt="Collection" onerror="this.src='assets/images/default.jpg'">
                </div>
                <div class="card-info">
                    <div class="card-meta">
                        <span>// <?php echo !empty($g['judul_event']) ? $g['judul_event'] : 'GALLERY'; ?></span>
                    </div>
                    <h3 class="card-title"><?php echo !empty($g['judul']) ? $g['judul'] : "TANPA JUDUL"; ?></h3>
                    <p class="card-desc"><?php echo !empty($g['deskripsi']) ? $g['deskripsi'] : "Dokumentasi arsip."; ?></p>
                </div>
            </div>
        <?php 
            endwhile; 
        else:
            echo "<p style='grid-column: 1/-1; text-align:center;'>Belum ada koleksi foto.</p>";
        endif; 
        ?>
    </div>

    <div id="tab-event" class="tab-content">
        <?php
        // Urutan: featured (1) dulu, kemudian status mendatang, lalu selesai, dan tanggal terbaru
        $query_ev = mysqli_query($conn, "SELECT * FROM events ORDER BY is_featured DESC, FIELD(status_event, 'mendatang', 'selesai'), tanggal_event DESC");
        if(mysqli_num_rows($query_ev) > 0):
            while($ev = mysqli_fetch_assoc($query_ev)):
                $tgl_event = $ev['tanggal_event'] ?? $today;
                $is_featured = $ev['is_featured'] ?? 0;
                if ($tgl_event < $today) {
                    $status_class = "status-selesai";
                    $status_text = "SELESAI";
                    $card_class = "event-selesai";
                } else {
                    $status_class = "status-mendatang";
                    $status_text = "AKAN DATANG";
                    $card_class = "";
                }
                $img_ev_src = !empty($ev['foto_cover']) ? 'data:image/jpeg;base64,' . base64_encode($ev['foto_cover']) : 'assets/images/default.jpg';
        ?>
            <div class="archive-card <?php echo $card_class; ?>">
                <div class="card-img-wrapper">
                    <span class="event-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    <img src="<?php echo $img_ev_src; ?>" alt="Event" onerror="this.src='assets/images/default.jpg'">
                </div>
                <div class="card-info">
                    <div class="card-meta">
                        <span>// DATE LOG :</span>
                        <span><?php echo date('d M Y', strtotime($tgl_event)); ?></span>
                        <?php if($is_featured): ?>
                            <span style="margin-left: 8px; color: gold; font-size:0.7rem;">★ FEATURED</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="card-title"><?php echo $ev['judul_event'] ?? 'Tanpa Judul'; ?></h3>
                    <p class="card-desc"><?php echo $ev['deskripsi_event'] ?? ''; ?></p>
                </div>
            </div>
        <?php 
            endwhile; 
        else:
            echo "<p style='grid-column: 1/-1; text-align:center;'>Belum ada acara besar.</p>";
        endif; 
        ?>
    </div>

    <a href="index.php" class="btn-back">← RETURN TO MAIN MENU</a>

    <script>
        function switchTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("btn-tab");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>