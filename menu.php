<?php 
include "config/koneksi.php"; 

$query = mysqli_query($conn, "SELECT * FROM menu ORDER BY kategori ASC, nama_menu ASC");
$menu_data = [];
while($row = mysqli_fetch_assoc($query)) {
    $menu_data[] = $row;
}
$menu_json = json_encode($menu_data);
?>

<div class="blueprint-container">

    <div class="detail-section">
        <div class="tape-top"></div>
        
        <div class="menu-detail show" id="menu-detail">
            <div class="spec-header">
                <span class="spec-id" id="detail-id">// REF: SELECT_ITEM</span>
                <span class="spec-status text-red" id="detail-status">SYSTEM_READY</span>
            </div>
            
            <h1 class="spec-title" id="detail-title">SELECT UNIT</h1>

            <div class="image-wrapper">
                <img id="detail-img" src="assets/images/default.jpg" alt="Menu Preview">
                <div id="sold-out-stamp" class="stamp hidden">TIDAK TERSEDIA</div>
            </div>

            <div class="spec-table">
                <div class="spec-row"><span class="spec-label">Name</span><span class="spec-value" id="spec-name">-</span><div class="spec-line"></div></div>
                <div class="spec-row"><span class="spec-label">Category</span><span class="spec-value" id="spec-category">-</span><div class="spec-line"></div></div>
                <div class="spec-row"><span class="spec-label">Status</span><span class="spec-value" id="spec-stock">-</span><div class="spec-line"></div></div>
                <div class="spec-row"><span class="spec-label">Price</span><span class="spec-value text-red bold" id="spec-price">-</span><div class="spec-line"></div></div>
            </div>

            <div class="notebook-note">
                <div class="note-title">Description :</div>
                <p id="detail-desc" class="handwritten-text">Arahkan kursor dan pilih menu di samping untuk melihat detail spesifikasi.</p>
            </div>
        </div>
    </div>

    <div class="list-section">
        <div class="search-wrapper">
            <input type="text" id="menuSearch" class="search-input" placeholder="> CARI MENU...">
        </div>

        <div class="filter-tabs">
            <button class="filter-btn active" onclick="filterCategory('all', this)">ALL UNITS</button>
            <button class="filter-btn" onclick="filterCategory('Kopi', this)">COFFEE</button>
            <button class="filter-btn" onclick="filterCategory('Non-Kopi', this)">NON-COFFEE</button>
            <button class="filter-btn" onclick="filterCategory('Snack', this)">SNACK</button>
            <button class="filter-btn" onclick="filterCategory('Main Course', this)">MAIN COURSE</button>
        </div>

        <div class="menu-list-container" id="menuContainer">
            <div id="empty-state" class="empty-state">// ERROR: Menu tidak ditemukan di inventaris.</div>

            <?php foreach($menu_data as $item): 
                // INTEGRASI CRUD: sold out jika status 'Tidak Tersedia'
                $is_sold_out = (isset($item['status']) && $item['status'] == 'Tidak Tersedia');
            ?>
                <div class="menu-item <?php echo $is_sold_out ? 'sold-out' : ''; ?>" 
                     data-id="<?php echo $item['id_menu']; ?>"
                     data-category="<?php echo htmlspecialchars($item['kategori']); ?>"
                     tabindex="0" role="button">
                    
                    <?php if($is_sold_out): ?><div class="badge">EMPTY</div><?php endif; ?>

                    <div class="item-thumb">
                        <img src="assets/images/menu/<?php echo htmlspecialchars($item['foto'] ?? 'default.jpg'); ?>" 
                             onerror="this.src='assets/images/default.jpg'" loading="lazy">
                    </div>

                    <div class="item-info">
                        <h3><?php echo htmlspecialchars(strtoupper($item['nama_menu'])); ?></h3>
                    </div>
                    
                    <div class="price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a href="index.php" class="btn-return">← RETURN TO MAIN MENU</a>
    </div>

</div>

<style>
    /* Stamp TIDAK TERSEDIA */
    .stamp {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        background: rgba(234, 67, 53, 0.9);
        color: white;
        font-family: 'Special Elite', cursive;
        font-size: 1.4rem;
        font-weight: bold;
        padding: 6px 15px;
        border: 3px double white;
        white-space: nowrap;
        z-index: 20;
        text-align: center;
        box-shadow: 4px 4px 8px rgba(0,0,0,0.2);
        letter-spacing: 2px;
    }
    @media (max-width: 480px) {
        .stamp {
            font-size: 1.1rem;
            padding: 4px 10px;
        }
    }
    .stamp.hidden {
        display: none;
    }

    /* Gambar thumbnail menjadi abu-abu saat sold out */
    .menu-item.sold-out .item-thumb img {
        filter: grayscale(100%);
        opacity: 0.7;
    }

    /* Gambar besar (detail kiri) menjadi abu-abu saat sold out */
    .image-wrapper.sold-out img {
        filter: grayscale(100%) !important;
        opacity: 0.75;
    }
</style>

<script>
    const menuData = <?php echo $menu_json; ?>;
    const items = document.querySelectorAll('.menu-item');
    const searchInput = document.getElementById('menuSearch');
    const emptyState = document.getElementById('empty-state');

    function updateLeftDetail(id) {
        const data = menuData.find(m => m.id_menu == id);
        if(!data) return;

        // INTEGRASI CRUD: sold out berdasarkan status 'Tidak Tersedia'
        const isSoldOut = (data.status === 'Tidak Tersedia');
        const detail = document.getElementById('menu-detail');
        const imgEl = document.getElementById('detail-img');
        const stamp = document.getElementById('sold-out-stamp');
        const imageWrapper = document.querySelector('.image-wrapper');

        detail.classList.remove('show');

        setTimeout(() => {
            document.getElementById('detail-id').innerText = `// REF: M-00${data.id_menu}`;
            document.getElementById('detail-title').innerText = data.nama_menu.toUpperCase();
            
            const imgFile = data.foto ? data.foto : 'default.jpg';
            const newSrc = `assets/images/menu/${imgFile}`;
            
            imgEl.style.opacity = '0';
            imgEl.classList.remove('animate-photo'); 
            
            const tempImg = new Image();
            tempImg.onload = function() {
                imgEl.src = newSrc;
                // Terapkan grayscale langsung ke elemen img
                imgEl.style.filter = isSoldOut ? 'grayscale(100%)' : 'none';
                imgEl.style.opacity = isSoldOut ? '0.75' : '1';
                void imgEl.offsetWidth; 
                imgEl.classList.add('animate-photo');
            };
            tempImg.onerror = function() {
                imgEl.src = 'assets/images/default.jpg';
                imgEl.style.filter = isSoldOut ? 'grayscale(100%)' : 'none';
                imgEl.style.opacity = isSoldOut ? '0.75' : '1';
                void imgEl.offsetWidth;
                imgEl.classList.add('animate-photo');
            };
            tempImg.src = newSrc;

            // Update class image-wrapper untuk fallback CSS
            if (isSoldOut) {
                imageWrapper.classList.add('sold-out');
            } else {
                imageWrapper.classList.remove('sold-out');
            }

            document.getElementById('spec-name').innerText = data.nama_menu;
            document.getElementById('spec-category').innerText = data.kategori;
            document.getElementById('spec-price').innerText = new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0
            }).format(data.harga);
            
            let statusText = (data.status === 'Tersedia') ? 'Tersedia' : 'Tidak Tersedia';
            document.getElementById('spec-stock').innerText = statusText;
            document.getElementById('detail-desc').innerText = data.deskripsi ? data.deskripsi : "Deskripsi tidak tersedia.";

            if(isSoldOut) {
                stamp.classList.remove('hidden');
                document.getElementById('detail-status').innerText = 'DEPLETED';
                document.getElementById('detail-status').style.color = 'var(--red)';
            } else {
                stamp.classList.add('hidden');
                document.getElementById('detail-status').innerText = 'AVAILABLE';
                document.getElementById('detail-status').style.color = 'var(--navy)';
            }

            detail.classList.add('show');
        }, 200); 
    }

    function applyFilters() {
        const activeBtn = document.querySelector('.filter-btn.active');
        const category = activeBtn ? activeBtn.innerText.toLowerCase() : 'all';
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        items.forEach(item => {
            const itemCat = item.getAttribute('data-category').toLowerCase();
            const itemName = item.querySelector('h3').innerText.toLowerCase();
            
            let matchCategory = false;
            if (activeBtn.getAttribute('onclick').includes('all')) matchCategory = true;
            else if (activeBtn.getAttribute('onclick').toLowerCase().includes(itemCat)) matchCategory = true;

            const matchSearch = itemName.includes(searchTerm);

            if (matchCategory && matchSearch) {
                item.style.display = 'flex';
                visibleCount++;
                item.animate([
                    { opacity: 0, transform: 'translateY(10px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 300, fill: 'forwards' });
            } else {
                item.style.display = 'none';
            }
        });

        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    window.filterCategory = function(cat, btnElement) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');
        applyFilters();
    };

    searchInput.addEventListener('input', function() {
        const activeBtn = document.querySelector('.filter-btn.active');
        if (this.value.trim() !== '' && activeBtn && !activeBtn.getAttribute('onclick').includes('all')) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.filter-btn[onclick*="all"]').classList.add('active');
        }
        applyFilters();
    });

    items.forEach(item => {
        item.addEventListener('click', () => {
            items.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            updateLeftDetail(item.getAttribute('data-id'));
            
            if (window.innerWidth <= 992) {
                setTimeout(() => {
                    document.querySelector('.detail-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        });
        
        item.addEventListener('keypress', function(e) { if (e.key === 'Enter') this.click(); });
    });
    
    const firstAvailable = Array.from(items).find(i => !i.classList.contains('sold-out')) || items[0];
    if(firstAvailable) firstAvailable.click();
</script>

</body>
</html>