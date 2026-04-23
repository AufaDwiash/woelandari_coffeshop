<?php include "config/koneksi.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Collective - Woelandari</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/community_style.css">
</head>
<body>

    <section class="tuku-style-section">
        
        <div class="header-container">
            <h2 class="main-title">BERAWAL DARI WARGA,<br>BERAKHIR JADI KELUARGA</h2>
            <p class="scroll-hint"> << GESER UNTUK LIHAT LAINNYA >> </p>
        </div>

        <div class="horizontal-scroll-wrapper">
            <div class="people-strip">
                
                <?php 
                $query = mysqli_query($conn, "SELECT * FROM human_archive WHERE status='active' ORDER BY display_order ASC");
                if(mysqli_num_rows($query) > 0):
                    while($row = mysqli_fetch_array($query)):
                ?>
                    <div class="person-card">
                        <img src="assets/images/community/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="person-img">
                        
                        <div class="person-info">
                            <span class="role-tag">// <?php echo strtoupper($row['role']); ?></span>
                            <h3 class="name-tag"><?php echo $row['name']; ?></h3>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                endif; 
                ?>

            </div>
        </div>

    </section>

</body>
</html>