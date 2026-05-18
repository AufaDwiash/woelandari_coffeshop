<?php 
include "config/koneksi.php"; 
?>

<section id="community-section" class="tuku-style-section">
    
    <div class="header-overlay">
        <h2 class="main-title">KELUARGA <br>WOELANDARI</h2>
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
        </div>
    </div>
    
    <div class="custom-scrollbar">
        <div class="scroll-thumb"></div>
    </div>

</section>

<style>
/* Pastikan section utama bisa menjadi patokan posisi scrollbar */
#community-section {
    position: relative; 
}

#community-section .horizontal-scroll-wrapper {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
    width: 100%;
    
    /* REVISI DI SINI: 
       Ubah angka 24px menjadi 12px (setinggi scrollbar normal) atau 16px (setinggi scrollbar saat di-hover).
       Ini akan membuat scrollbar menempel rapat di bawah foto tanpa jeda putih. */
    padding-bottom: 12px !important; 
    
    margin-bottom: 0 !important;
}

#community-section .horizontal-scroll-wrapper::-webkit-scrollbar {
    display: none; /* Chrome/Safari */
}

/* Container utama scrollbar (Track) - FULL MENTOK KANAN KIRI */
.custom-scrollbar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%; /* Memaksa rata kiri-kanan */
    height: 12px;
    background: rgba(0, 43, 91, 0.08);
    cursor: pointer;
    z-index: 10;
    transition: height 0.2s ease-in-out;
}

/* Handle / Batang geser scrollbar (Thumb) */
.scroll-thumb {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    background: #EA4335;
    border-radius: 4px; 
    cursor: grab;
    width: 30%; /* Diatur dinamis oleh JS */
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
    will-change: transform; /* Optimasi pergerakan dari JS */
}

.scroll-thumb:active {
    cursor: grabbing;
    background: #EA4335CC; /* Warna lebih gelap saat di-drag */
}

/* Efek Hover */
.custom-scrollbar:hover {
    height: 16px;
    background: rgba(0, 43, 91, 0.15);
}

.custom-scrollbar:hover .scroll-thumb {
    background: #EA4335CC;
    border-radius: 6px;
}

/* Mode Mobile / Smartphone */
@media (max-width: 768px) {
    .custom-scrollbar {
        height: 8px;
    }
    .custom-scrollbar:hover {
        height: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.horizontal-scroll-wrapper');
    const scrollbar = document.querySelector('.custom-scrollbar');
    const thumb = document.querySelector('.scroll-thumb');
    
    if (!wrapper || !scrollbar || !thumb) return;
    
    let isDragging = false;
    let startX = 0;
    let startScrollLeft = 0;

    // 1. Fungsi Mengupdate Lebar Batang Thumb
    function updateThumbSize() {
        const scrollWidth = wrapper.scrollWidth;
        const clientWidth = wrapper.clientWidth;
        
        // Sembunyikan scrollbar jika konten tidak cukup panjang
        if (scrollWidth <= clientWidth) {
            scrollbar.style.display = 'none';
            return;
        }
        
        scrollbar.style.display = 'block';
        const thumbWidthPercent = (clientWidth / scrollWidth) * 100;
        // Minimal lebar thumb adalah 10% agar tetap mudah di-klik
        thumb.style.width = `${Math.max(10, thumbWidthPercent)}%`;
        
        updateThumbPosition();
    }
    
    // 2. Fungsi Mengupdate Posisi Batang Thumb Saat Wrapper Di-scroll
    function updateThumbPosition() {
        const scrollWidth = wrapper.scrollWidth;
        const clientWidth = wrapper.clientWidth;
        
        const maxScrollLeft = scrollWidth - clientWidth;
        if (maxScrollLeft <= 0) return;
        
        const scrollPercent = wrapper.scrollLeft / maxScrollLeft;
        
        // Menghitung batas pergerakan thumb di dalam track
        const trackWidth = scrollbar.clientWidth;
        const thumbWidth = thumb.offsetWidth;
        const maxThumbLeft = trackWidth - thumbWidth;
        
        // Menggunakan translate3d untuk optimasi hardware (Smooth Scrolling)
        const moveX = scrollPercent * maxThumbLeft;
        thumb.style.transform = `translate3d(${moveX}px, 0, 0)`;
    }
    
    // 3. Event Listener untuk Dragging Thumb
    thumb.addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX;
        startScrollLeft = wrapper.scrollLeft;
        
        // Mencegah blok teks tersorot biru saat dragging
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        // Hitung pergeseran mouse
        const deltaX = e.clientX - startX;
        
        // Hitung rasio (seberapa jauh konten harus bergeser relatif terhadap pergeseran mouse)
        const trackWidth = scrollbar.clientWidth;
        const thumbWidth = thumb.offsetWidth;
        const maxThumbLeft = trackWidth - thumbWidth;
        const maxScrollLeft = wrapper.scrollWidth - wrapper.clientWidth;
        
        const scrollRatio = maxScrollLeft / maxThumbLeft;
        
        // Terapkan scroll ke wrapper. Event 'scroll' di bawah otomatis menggerakkan thumb.
        wrapper.scrollLeft = startScrollLeft + (deltaX * scrollRatio);
    });
    
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            document.body.style.userSelect = '';
        }
    });

    // 4. Klik langsung di area Track untuk melompat
    scrollbar.addEventListener('mousedown', function(e) {
        if (e.target === thumb) return; // Abaikan jika yang diklik adalah thumb
        
        const trackRect = scrollbar.getBoundingClientRect();
        const clickX = e.clientX - trackRect.left;
        const thumbWidth = thumb.offsetWidth;
        
        // Posisikan tengah-tengah thumb di titik kursor
        const newThumbLeft = clickX - (thumbWidth / 2);
        const maxThumbLeft = trackRect.width - thumbWidth;
        
        let movePercent = newThumbLeft / maxThumbLeft;
        movePercent = Math.max(0, Math.min(movePercent, 1)); // Batasi antara 0 dan 1
        
        const maxScrollLeft = wrapper.scrollWidth - wrapper.clientWidth;
        wrapper.scrollLeft = movePercent * maxScrollLeft;
    });
    
    // Listeners Utama
    wrapper.addEventListener('scroll', updateThumbPosition);
    window.addEventListener('resize', updateThumbSize);
    
    // Update saat load pertama kali
    updateThumbSize();
    
    // Gunakan ResizeObserver untuk memantau jika tiba-tiba ada gambar baru ter-load
    const observer = new ResizeObserver(() => updateThumbSize());
    observer.observe(wrapper);
});
</script>