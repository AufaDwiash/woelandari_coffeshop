<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Kasir (POS)</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/karyawan/menu_kasir.css"> </head>
<body style="background-color: #f4f4f4; padding: 20px;">

    <div class="pos-wrapper">
        <div class="menu-container">
            <h2 style="font-family: 'Special Elite', cursive; text-align:center; color: var(--navy-ink, #002b5e);">DAFTAR MENU</h2>
            <div class="menu-grid">
                
                <?php
                // Ambil data dari tabel menu (Sesuaikan nama tabel dan kolom database kamu)
                $q_menu = mysqli_query($conn, "SELECT * FROM menu ORDER BY nama_menu ASC");
                while ($row = mysqli_fetch_assoc($q_menu)) {
                    $id_menu = $row['id_menu'];
                    $nama_menu = addslashes($row['nama_menu']); 
                    $harga = $row['harga'];
                    // Asumsi nama gambar disimpan di kolom 'foto'
                    $foto = $row['foto'] ? $row['foto'] : 'default.jpg'; 
                    ?>
                    
                    <div class="menu-card" onclick="tambahKeKeranjang(<?php echo $id_menu; ?>, '<?php echo $nama_menu; ?>', <?php echo $harga; ?>)">
                        <img src="../assets/images/menu/<?php echo $foto; ?>" alt="<?php echo $row['nama_menu']; ?>" onerror="this.src='https://via.placeholder.com/100'">
                        <strong><?php echo strtoupper($row['nama_menu']); ?></strong><br>
                        Rp <?php echo number_format($harga, 0, ',', '.'); ?>
                    </div>

                <?php } ?>

            </div>
        </div>

        <div class="cart-container">
            <h2>KERANJANG PESANAN</h2>
            
            <div class="cart-items">
                <table style="width: 100%; text-align: left; font-family: 'Courier Prime', monospace; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px dashed #ccc;">
                            <th style="padding-bottom: 5px;">Item</th>
                            <th style="padding-bottom: 5px;">Qty</th>
                            <th style="padding-bottom: 5px;">Sub</th>
                            <th style="padding-bottom: 5px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        </tbody>
                </table>
            </div>

            <div class="cart-total">
                TOTAL: Rp <span id="total-harga">0</span>
            </div>

            <form method="POST" action="proses_checkout.php" id="formCheckout">
                <input type="hidden" name="data_transaksi" id="data_transaksi">
                <input type="hidden" name="total_belanja_hidden" id="total_belanja_hidden">
                <input type="hidden" name="kembalian_hidden" id="kembalian_hidden">

                <div class="form-group-kasir">
                    <label>Metode Pembayaran:</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" onchange="toggleCashInput()">
                        <option value="CASH">CASH (Tunai)</option>
                        <option value="QRIS">QRIS / TRANSFER</option>
                    </select>
                </div>

                <div id="area_cash" class="form-group-kasir">
                    <label>Uang Diterima (Rp):</label>
                    <input type="number" id="uang_diterima" name="uang_diterima" onkeyup="hitungKembalian()" placeholder="Contoh: 50000">
                    <div style="margin-top: 10px; font-family: 'Courier Prime', monospace; font-weight: bold; color: var(--navy-ink, #002b5e);">
                        Kembali: Rp <span id="text_kembalian">0</span>
                    </div>
                </div>

                <button type="submit" class="btn-pay" onclick="siapkanCheckout(event)">BAYAR & CETAK NOTA</button>
                <a href="../admin/dashboard.php" class="btn-pay" style="display:block; text-align:center; text-decoration:none; margin-top:10px; background:#555;">KEMBALI DASHBOARD</a>
            </form>
        </div>
    </div>

    <script>
        let keranjang = [];
        let totalSeluruh = 0;

        function tambahKeKeranjang(id, nama, harga) {
            let itemAda = keranjang.find(item => item.id === id);
            if (itemAda) {
                itemAda.qty += 1;
            } else {
                keranjang.push({ id: id, nama: nama, harga: harga, qty: 1 });
            }
            renderKeranjang();
        }

        function kurangiItem(id) {
            let indeks = keranjang.findIndex(item => item.id === id);
            if (indeks !== -1) {
                if (keranjang[indeks].qty > 1) {
                    keranjang[indeks].qty -= 1;
                } else {
                    keranjang.splice(indeks, 1);
                }
            }
            renderKeranjang();
        }

        function renderKeranjang() {
            const cartBody = document.getElementById('cart-body');
            cartBody.innerHTML = '';
            totalSeluruh = 0;

            keranjang.forEach(item => {
                let subtotal = item.harga * item.qty;
                totalSeluruh += subtotal;

                cartBody.innerHTML += `
                    <tr>
                        <td style="padding: 5px 0;">${item.nama}</td>
                        <td>${item.qty}</td>
                        <td>${subtotal.toLocaleString('id-ID')}</td>
                        <td>
                            <button type="button" onclick="kurangiItem(${item.id})" style="background:#d93838; color:white; border:none; padding:2px 8px; cursor:pointer; font-weight:bold;">X</button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('total-harga').innerText = totalSeluruh.toLocaleString('id-ID');
            document.getElementById('total_belanja_hidden').value = totalSeluruh;
            hitungKembalian(); // Hitung ulang kembalian jika total berubah
        }

        function toggleCashInput() {
            let metode = document.getElementById('metode_pembayaran').value;
            let areaCash = document.getElementById('area_cash');
            if (metode === 'QRIS') {
                areaCash.style.display = 'none';
                document.getElementById('uang_diterima').value = totalSeluruh; // Otomatis pas
                hitungKembalian();
            } else {
                areaCash.style.display = 'block';
                document.getElementById('uang_diterima').value = '';
                hitungKembalian();
            }
        }

        function hitungKembalian() {
            let uang = parseInt(document.getElementById('uang_diterima').value) || 0;
            let metode = document.getElementById('metode_pembayaran').value;
            let kembalian = 0;

            if (metode === 'CASH') {
                kembalian = uang - totalSeluruh;
                if (kembalian < 0) kembalian = 0;
            }
            
            document.getElementById('text_kembalian').innerText = kembalian.toLocaleString('id-ID');
            document.getElementById('kembalian_hidden').value = kembalian;
        }

        function siapkanCheckout(event) {
            if (keranjang.length === 0) {
                alert("Keranjang masih kosong! Silakan pilih menu terlebih dahulu.");
                event.preventDefault();
                return;
            }

            let metode = document.getElementById('metode_pembayaran').value;
            let uang = parseInt(document.getElementById('uang_diterima').value) || 0;

            if (metode === 'CASH' && uang < totalSeluruh) {
                alert("Uang yang dibayarkan kurang dari total belanja!");
                event.preventDefault();
                return;
            }

            // Kemas data keranjang ke JSON
            document.getElementById('data_transaksi').value = JSON.stringify(keranjang);
        }
    </script>
</body>
</html>
