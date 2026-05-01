<?php 
include "config/koneksi.php"; 

$query = mysqli_query($conn, "SELECT * FROM menu ORDER BY kategori ASC, nama_menu ASC");
$menu_data = [];
while($row = mysqli_fetch_assoc($query)) {
    $menu_data[] = $row;
}
$menu_json = json_encode($menu_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/public_nav.css">
    <link rel="stylesheet" href="assets/css/menu_style.css">
</head>
<body>
<nav class="public-nav">
    <a href="index.php">Beranda</a>
    <a href="gallery.php">Gallery</a>
    <a href="rating.php">Rating</a>
</nav>

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
                <div id="sold-out-stamp" class="stamp hidden">SOLD OUT</div>
            </div>

            <div class="spec-table">
                <div class="spec-row">
                    <span class="spec-label">Name</span>
                    <span class="spec-value" id="spec-name">-</span>
                    <div class="spec-line"></div>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Category</span>
                    <span class="spec-value" id="spec-category">-</span>
                    <div class="spec-line"></div>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Stock</span>
                    <span class="spec-value" id="spec-stock">-</span>
                    <div class="spec-line"></div>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Price</span>
                    <span class="spec-value text-red bold" id="spec-price">-</span>
                    <div class="spec-line"></div>
                </div>
            </div>

            <div class="notebook-note">
                <div class="note-title">Description :</div>
                <p id="detail-desc" class="handwritten-text">Arahkan kursor dan pilih menu di samping untuk melihat detail spesifikasi.</p>
            </div>
        </div>
    </div>

    <div class="list-section">
        
        <div class="filter-tabs">
            <span class="filter-label">LIST MENU</span>
            <button class="filter-btn active" onclick="filterCategory('all', this)">ALL UNITS</button>
            <button class="filter-btn" onclick="filterCategory('Coffee', this)">COFFEE</button>
            <button class="filter-btn" onclick="filterCategory('Non-Coffee', this)">NON-COFFEE</button>
            <button class="filter-btn" onclick="filterCategory('Snack', this)">SNACKS</button>
            <button class="filter-btn" onclick="filterCategory('Main Course', this)">MAIN COURSE</button>
        </div>

        <div class="menu-list-container">
            <?php foreach($menu_data as $index => $item): 
                $is_sold_out = ($item['stok'] <= 0);
            ?>
                <div class="menu-item <?php echo $is_sold_out ? 'sold-out' : ''; ?>" 
                     data-index="<?php echo $index; ?>"
                     data-category="<?php echo htmlspecialchars($item['kategori']); ?>">
                    
                    <div class="item-thumb">
                        <img src="assets/images/menu/<?php echo htmlspecialchars($item['foto']); ?>" 
                             onerror="this.src='assets/images/default.jpg'" 
                             alt="<?php echo htmlspecialchars($item['nama_menu']); ?>">
                    </div>

                    <div class="item-info">
                        <h3><?php echo htmlspecialchars(strtoupper($item['nama_menu'])); ?></h3>
                        <div class="item-meta">
                            <span class="item-cat">[<?php echo htmlspecialchars($item['kategori']); ?>]</span>
                            <?php if($is_sold_out): ?>
                                <span class="item-stock text-red">QTY: 0</span>
                            <?php else: ?>
                                <span class="item-stock">QTY: <?php echo htmlspecialchars($item['stok']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="price">IDR <?php echo number_format($item['harga']/1000, 0); ?>K</div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a href="index.php" class="btn-back">← RETURN TO MAIN MENU</a>
    </div>

</div>

<script>
    const menuData = <?php echo $menu_json; ?>;
    const items = document.querySelectorAll('.menu-item');
    const detail = document.getElementById('menu-detail');

    const detailId = document.getElementById('detail-id');
    const detailStatus = document.getElementById('detail-status');
    const detailTitle = document.getElementById('detail-title');
    const detailImg = document.getElementById('detail-img');
    const specName = document.getElementById('spec-name');
    const specCat = document.getElementById('spec-category');
    const specPrice = document.getElementById('spec-price');
    const specStock = document.getElementById('spec-stock');
    const detailDesc = document.getElementById('detail-desc');
    const soldOutStamp = document.getElementById('sold-out-stamp');

    function updateLeftDetail(index) {
        const data = menuData[index];
        const isSoldOut = parseInt(data.stok) <= 0;

        detail.classList.remove('show');

        setTimeout(() => {
            detailId.innerText = `// REF: M-00${data.id_menu}`;
            detailTitle.innerText = data.nama_menu.toUpperCase();
            
            detailImg.src = `assets/images/menu/${data.foto}`;
            detailImg.onerror = function() { this.src = 'assets/images/default.jpg'; };

            specName.innerText = data.nama_menu;
            specCat.innerText = data.kategori;
            specPrice.innerText = `Rp ${parseInt(data.harga).toLocaleString('id-ID')}`;
            specStock.innerText = isSoldOut ? '0 (Empty)' : `${data.stok} Units`;
            
            detailDesc.innerText = data.deskripsi ? data.deskripsi : "Deskripsi tidak tersedia.";

            // LOGIKA HITAM PUTIH (GRAYSCALE) UNTUK GAMBAR DETAIL UTAMA
            if(isSoldOut) {
                soldOutStamp.classList.remove('hidden');
                setTimeout(() => { soldOutStamp.style.opacity = '1'; }, 50); 
                detailStatus.innerText = 'DEPLETED';
                detailStatus.style.color = 'var(--red)';
                
                detailImg.style.filter = 'grayscale(100%)';
                detailImg.style.opacity = '0.8';
            } else {
                soldOutStamp.classList.add('hidden');
                soldOutStamp.style.opacity = '0';
                detailStatus.innerText = 'AVAILABLE';
                detailStatus.style.color = 'var(--navy)';
                
                detailImg.style.filter = 'none';
                detailImg.style.opacity = '1';
            }

            detail.classList.add('show');
        }, 300); 
    }

    function filterCategory(category, btnElement) {
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        items.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            
            if (category === 'all' || itemCat === category) {
                item.style.display = 'flex';
                item.animate([
                    { opacity: 0, transform: 'scale(0.95)' },
                    { opacity: 1, transform: 'scale(1)' }
                ], { duration: 300, fill: 'forwards', easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)' });
            } else {
                item.style.display = 'none';
            }
        });
    }

    items.forEach(item => {
        item.addEventListener('click', () => {
            items.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const index = item.getAttribute('data-index');
            updateLeftDetail(index);
            
            if (window.innerWidth <= 992) {
                document.querySelector('.detail-section').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        });
    });
    
    if(items.length > 0) {
        items[0].click();
    }
</script>

</body>
</html>
