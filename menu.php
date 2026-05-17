<?php 
// Gunakan include_once agar koneksi tidak bentrok jika section lain juga memanggilnya
include_once "config/koneksi.php"; 

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
                <div class="spec-row"><span class="spec-label">Name</span><span class="spec-value" id="spec-name">-</span></div>
                <div class="spec-row"><span class="spec-label">Category</span><span class="spec-value" id="spec-category">-</span></div>
                <div class="spec-row"><span class="spec-label">Status</span><span class="spec-value" id="spec-stock">-</span></div>
                <div class="spec-row"><span class="spec-label">Price</span><span class="spec-value text-red bold" id="spec-price">-</span></div>
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
            <button class="filter-btn active" data-filter="all">ALL UNITS</button>
            <button class="filter-btn" data-filter="coffee">COFFEE</button>
            <button class="filter-btn" data-filter="non-coffee">NON-COFFEE</button>
            <button class="filter-btn" data-filter="snack">SNACK</button>
            <button class="filter-btn" data-filter="main course">MAIN COURSE</button>
        </div>
       

        <div class="menu-list-container" id="menuContainer">
            <div id="empty-state" class="empty-state">// ERROR: Menu tidak ditemukan di inventaris.</div>

            <?php foreach($menu_data as $item): 
                $is_sold_out = (isset($item['status']) && $item['status'] == 'Tidak Tersedia');
            ?>
                <div class="menu-item <?php echo $is_sold_out ? 'sold-out' : ''; ?>" 
                     data-id="<?php echo $item['id_menu']; ?>"
                     data-category="<?php echo htmlspecialchars(strtolower($item['kategori'])); ?>"
                     data-name="<?php echo htmlspecialchars(strtolower($item['nama_menu'])); ?>"
                     tabindex="0" role="button">
                    
                    <?php if($is_sold_out): ?><div class="badge">EMPTY</div><?php endif; ?>

                    <div class="item-thumb">
                        <img src="assets/images/menu/<?php echo htmlspecialchars($item['foto'] ?? 'default.jpg'); ?>" 
                             onerror="this.src='assets/images/default.jpg'" loading="lazy">
                    </div>

                    <div class="item-info">
                        <h3><?php echo htmlspecialchars(strtoupper($item['nama_menu'])); ?></h3>
                        <div class="price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="pagination-container">
            <button id="prevMenuBtn" class="btn-pagination" disabled>← PREVIOUS</button>
            <span id="pageMenuInfo" class="page-info">PAGE 1 / 1</span>
            <button id="nextMenuBtn" class="btn-pagination" disabled>NEXT →</button>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const MenuApp = (function() {
        const menuData = <?php echo $menu_json; ?>;
        const items = Array.from(document.querySelectorAll('#menu .menu-item'));
        const searchInput = document.getElementById('menuSearch');
        const emptyState = document.getElementById('empty-state');
        const filterBtns = document.querySelectorAll('#menu .filter-btn');
        
        const prevBtn = document.getElementById('prevMenuBtn');
        const nextBtn = document.getElementById('nextMenuBtn');
        const pageInfo = document.getElementById('pageMenuInfo');

        let currentPage = 1;
        const itemsPerPage = 8; 
        let filteredItems = [...items];
        let activeItemId = null;

        function updateLeftDetail(id) {
            const data = menuData.find(m => m.id_menu == id);
            if(!data) return;

            const isSoldOut = (data.status === 'Tidak Tersedia');
            const detail = document.getElementById('menu-detail');
            const imgEl = document.getElementById('detail-img');
            const stamp = document.getElementById('sold-out-stamp');
            const imageWrapper = document.querySelector('.image-wrapper');

            detail.classList.remove('show');

            setTimeout(() => {
                document.getElementById('detail-id').innerText = `// REF: M-${String(data.id_menu).padStart(3, '0')}`;
                document.getElementById('detail-title').innerText = data.nama_menu.toUpperCase();
                
                const imgFile = data.foto ? data.foto : 'default.jpg';
                const newSrc = `assets/images/menu/${imgFile}`;
                
                imgEl.style.opacity = '0';
                imgEl.classList.remove('animate-photo'); 
                
                const tempImg = new Image();
                tempImg.onload = function() {
                    imgEl.src = newSrc;
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

                if (isSoldOut) imageWrapper.classList.add('sold-out');
                else imageWrapper.classList.remove('sold-out');

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

        function renderPagination() {
            const totalPages = Math.ceil(filteredItems.length / itemsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = startIdx + itemsPerPage;

            items.forEach(item => item.style.display = 'none');

            let visibleCount = 0;
            const pageItems = filteredItems.slice(startIdx, endIdx);
            
            pageItems.forEach(item => {
                item.style.display = 'flex';
                visibleCount++;
                item.animate([
                    { opacity: 0, transform: 'translateY(10px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 300, fill: 'forwards' });
            });

            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';

            pageInfo.innerText = `PAGE ${currentPage} / ${totalPages}`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;

            if (pageItems.length > 0) {
                const isActiveVisible = pageItems.some(i => i.classList.contains('active'));
                if (!isActiveVisible) {
                    const firstAvail = pageItems.find(i => !i.classList.contains('sold-out')) || pageItems[0];
                    if(firstAvail) firstAvail.click();
                }
            }
        }

        function applyFilters() {
            const activeBtn = document.querySelector('#menu .filter-btn.active');
            const categoryFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
            const searchTerm = searchInput.value.toLowerCase();

            filteredItems = items.filter(item => {
                const itemCat = item.getAttribute('data-category');
                const itemName = item.getAttribute('data-name');
                
                const matchCategory = (categoryFilter === 'all') || itemCat.includes(categoryFilter);
                const matchSearch = itemName.includes(searchTerm);

                return matchCategory && matchSearch;
            });

            currentPage = 1;
            renderPagination();
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                applyFilters();
            });
        });

        searchInput.addEventListener('input', function() {
            const activeBtn = document.querySelector('#menu .filter-btn.active');
            if (this.value.trim() !== '' && activeBtn && activeBtn.getAttribute('data-filter') !== 'all') {
                filterBtns.forEach(b => b.classList.remove('active'));
                document.querySelector('#menu .filter-btn[data-filter="all"]').classList.add('active');
            }
            applyFilters();
        });

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) { currentPage--; renderPagination(); }
        });

        nextBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
            if (currentPage < totalPages) { currentPage++; renderPagination(); }
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
        
        applyFilters();
    })();
});
</script>