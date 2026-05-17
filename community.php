<?php 
include "config/koneksi.php"; 
?>

<section id="community-section" class="tuku-style-section">
    
    <div class="header-overlay">
        <h2 class="main-title">TEAM<br>DEVELOPER</h2>
        <p class="scroll-hint"><< GESER UNTUK LIHAT LAINNYA >></p>
    </div>

    <div class="horizontal-scroll-wrapper">
        <div class="people-strip">
            
            <?php 
            $query = mysqli_query($conn, "SELECT * FROM human_archive WHERE status='active' ORDER BY display_order ASC");
            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_array($query)):
            ?>
                <div class="person-card">
                    <img src="assets/images/community/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="person-img">
                    
                    <div class="person-info">
                        <div class="brutalist-badge">
                            <span class="role"><?php echo strtoupper(htmlspecialchars($row['role'])); ?></span>
                            <span class="name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <?php if(!empty($row['quote'])): ?>
                                <span class="quote">“<?php echo htmlspecialchars($row['quote']); ?>”</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
                // fallback jika belum ada data
                echo '<div style="position:absolute; bottom:20%; left:5%; color:red;">Belum ada data tim. Silakan login sebagai admin untuk menambahkan.</div>';
            endif; 
            ?>
            
            <div class="scroll-spacer"></div>
        </div>
    </div>
</section>