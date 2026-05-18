<section id="reservasi-section">
    <div class="res-container">
        <div class="res-header">
            <div class="res-tag">sistem reservasi</div>
            <h2 class="res-title">RESERVASI</h2>
            <div class="res-line"></div>
        </div>

        <div class="res-grid">
            <form id="labForm" class="res-main-card">
                <div class="card-label">Isi Form Jika Ingin Reservasi</div>
                
                <div class="form-row">
                    <div class="input-group">
                        <label>Nama</label>
                        <input type="text" id="res-nama" placeholder="NAMA LENGKAP..." required>
                    </div>
                    <div class="input-group">
                        <label>Nomor</label>
                        <input type="tel" id="res-wa" placeholder="628xxx..." required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>Tanggal</label>
                        <input type="date" id="res-tanggal" required>
                    </div>
                    <div class="input-group">
                        <label>Jam</label>
                        <input type="text" id="res-jam" placeholder="CONTOH: 19:30" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>Durasi</label>
                        <input type="text" id="res-durasi" placeholder="MISAL: 2 JAM" required>
                    </div>
                    <div class="input-group">
                        <label>Pilih Aerea</label>
                        <select id="res-area" required>
                            <option value="" disabled selected>PILIH AREA...</option>
                            <option value="INDOOR (AC)">INDOOR </option>
                            <option value="OUTDOOR (SMOKING)">OUTDOOR</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>Jumlah Orang</label>
                    <input type="number" id="res-jumlah" min="1" placeholder="ESTIMASI JUMLAH ORANG..." required>
                </div>

                <button type="button" onclick="executeBooking()" class="btn-execute">
                    RESERVASI (Otomatis Via WA) <i class="fa-solid fa-flask-vial"></i>
                </button>
            </form>

            <div class="res-side-panel">
                <div class="res-side-card info-card">
                    <div class="card-label">Ketentuan Reservasi</div>
                    <ul class="res-info-list">
                        <li><strong>Durasi Maksimal:</strong> 120 Menit</li>
                        <li><strong>Minimal Pemesanan:</strong> 1 BEVERAGE / PAX</li>
                        <li><strong>Waktu Terakhir Pemesanan:</strong> 21:00 WIB</li>
                    </ul>
                </div>

                <div class="res-side-card protocol-card">
                    <div class="card-label" style="color: #8b7d6b;">Protokol Kedatangan</div>
                    <p class="protocol-text">
                        Slot reservasi hanya dijamin selama <strong>15 menit</strong> dari jam datang yang diinput. Jika melewati batas tanpa info, sistem akan melepas meja untuk pengunjung lain.
                    </p>
                </div>

                <div class="barcode-wrapper">
                    <div class="barcode-lines"></div>
                    <div class="barcode-text">W-O-E-L-A-N-D-A-R-I</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function executeBooking() {
    const d = {
        nama: document.getElementById('res-nama').value,
        wa: document.getElementById('res-wa').value,
        tgl: document.getElementById('res-tanggal').value,
        jam: document.getElementById('res-jam').value,
        dur: document.getElementById('res-durasi').value,
        area: document.getElementById('res-area').value,
        pax: document.getElementById('res-jumlah').value
    };

    if(!d.nama || !d.tgl || !d.jam || !d.dur || !d.area || !d.pax) {
        alert("Harap lengkapi semua data laboratorium!");
        return;
    }

    const adminNum = "628118198858"; 
    const msg = `Halo Admin Woelandari Coffee Lab,%0A%0ASaya ingin mengajukan *LAB RESERVATION*:%0A%0A` +
                 `*Nama:* ${d.nama}%0A` +
                 `*WhatsApp:* ${d.wa}%0A` +
                 `*Tanggal:* ${d.tgl}%0A` +
                 `*Jam Datang:* ${d.jam}%0A` +
                 `*Durasi:* ${d.dur}%0A` +
                 `*Area:* ${d.area}%0A` +
                 `*Jumlah:* ${d.pax} Orang%0A%0A` +
                 `Mohon dicek ketersediaan slotnya. Terima kasih!`;

    window.open(`https://wa.me/${adminNum}?text=${msg}`, '_blank');
}
</script>