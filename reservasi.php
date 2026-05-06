<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <!-- Pastikan link CSS ini sesuai dengan lokasi file Anda -->
    <link rel="stylesheet" href="assets/css/reservasi_style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="blueprint-container">

    <!-- BAGIAN KIRI: INFO & RULES -->
    <div class="info-section">
        <div class="hero-box">
            <h1 class="spec-title">RESERVASI </h1>
            <p class="subtitle">Amankan tempatmu tanpa antre, cukup lewat WhatsApp boskuu.</p>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>// INFO OPERASIONAL</h3>
                <ul class="spec-list">
                    <li><span class="spec-label">Jam Buka</span><span class="spec-value">17.00 - 22.00</span></li>
                    <li><span class="spec-label">Max Durasi</span><span class="spec-value">2 Jam (Peak Hour)</span></li>
                    <li><span class="spec-label">Toleransi</span><span class="spec-value">15 Menit Keterlambatan</span></li>
                    <li><span class="spec-label">Biaya / DP</span><span class="spec-value text-red bold">MENYESUAIKAN</span></li>
                </ul>
            </div>

            <div class="info-card">
                <h3>// PILIHAN AREA & KAPASITAS</h3>
                <div class="area-options" id="areaPicker">
                    <!-- Area Indoor -->
                    <div class="area-item selectable" data-value="Indoor">
                        <div class="area-desc">
                            <h4>INDOOR</h4>
                            <p>2 - 4 Orang</p>
                        </div>
                    </div>

                    <!-- Area Outdoor -->
                    <div class="area-item selectable" data-value="Outdoor">
                        <div class="area-desc">
                            <h4>OUTDOOR</h4>
                            <p>4 - 6+ Orang</p>
                        </div>
                    </div>

                    <!-- Area Lesehan -->
                    <div class="area-item selectable" data-value="FULL CAFFE">
                        <div class="area-desc">
                            <h4>FULL CAFFE</h4>
                            <p> >50 Orang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rules-box">
            <div class="note-title">Rules / Ketentuan :</div>
            <p class="handwritten-text">Harap datang tepat waktu. Reservasi otomatis hangus jika terlambat lebih dari 15 menit dari jadwal yang ditentukan tanpa konfirmasi.</p>
        </div>
        
        <a href="menu.php" class="btn-return">← LIHAT MENU</a>
    </div>

    <!-- BAGIAN KANAN: FORM WA -->
    <div class="form-section">
        <div class="tape-top"></div>
        
        <div class="spec-header">
            <span class="spec-id" id="detail-id">// REF: BOOKING_FORM</span>
            <span class="spec-status text-red" id="detail-status">AWAITING_INPUT</span>
        </div>

        <h2 class="form-title">TRANSMIT DATA</h2>

        <form id="reservationForm" class="brutalist-form">
            <div class="form-group">
                <label>NAMA PEMESAN</label>
                <input type="text" id="resNama" placeholder="Masukkan nama..." required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>TANGGAL</label>
                    <input type="date" id="resTanggal" required>
                </div>
                <div class="form-group">
                    <label>JAM</label>
                    <input type="time" id="resJam" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>JUMLAH ORANG</label>
                    <select id="resOrang" required>
                        <option value="" disabled selected>Pilih kapasitas...</option>
                        <option value="1-2 Orang">1-2 Orang</option>
                        <option value="3-4 Orang">3-4 Orang</option>
                        <option value="5-6 Orang">5-6 Orang</option>
                        <option value="Rombongan (>6 Orang)">Rombongan (>6 Orang)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>AREA</label>
                    <!-- Select ini akan otomatis terupdate saat kartu diklik -->
                    <select id="resArea" required>
                        <option value="" disabled selected>Pilih area...</option>
                        <option value="Indoor">Indoor</option>
                        <option value="Outdoor">Outdoor</option>
                        <option value="FULL CAFFE">FULL CAFFE</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>CATATAN TAMBAHAN (Opsional)</label>
                <textarea id="resCatatan" rows="3" placeholder="Misal: Minta meja dekat jendela..."></textarea>
            </div>

            <button type="submit" class="btn-wa">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" style="margin-right: 8px;">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                KIRIM VIA WHATSAPP
            </button>
        </form>
    </div>

</div>

<script>
    // 1. LOGIKA PICKER AREA (KLIK KARTU)
    const areaCards = document.querySelectorAll('.area-item.selectable');
    const areaDropdown = document.getElementById('resArea');

    areaCards.forEach(card => {
        card.addEventListener('click', function() {
            // Hapus semua status aktif
            areaCards.forEach(c => c.classList.remove('active'));
            // Tambah status aktif ke yang diklik
            this.classList.add('active');
            // Update dropdown form
            const selectedValue = this.getAttribute('data-value');
            areaDropdown.value = selectedValue;
            
            // Ubah status di header form untuk interaksi
            document.getElementById('detail-status').innerText = 'AREA_SELECTED';
            document.getElementById('detail-status').style.color = 'var(--navy)';
        });
    });

    // Sinkronisasi sebaliknya: jika dropdown diubah manual
    areaDropdown.addEventListener('change', function() {
        areaCards.forEach(card => {
            if(card.getAttribute('data-value') === this.value) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
    });

    // 2. LOGIKA KIRIM WHATSAPP
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const waNumber = "628883899886"; 
        
        const nama = document.getElementById('resNama').value;
        const tanggal = document.getElementById('resTanggal').value;
        const jam = document.getElementById('resJam').value;
        const orang = document.getElementById('resOrang').value;
        const area = document.getElementById('resArea').value;
        const catatan = document.getElementById('resCatatan').value;
        
        let text = `Halo Admin Woelandari Coffee Lab,%0A%0ASaya ingin melakukan reservasi meja dengan detail berikut:%0A%0A`;
        text += `*Nama:* ${nama}%0A`;
        text += `*Tanggal:* ${tanggal}%0A`;
        text += `*Jam:* ${jam}%0A`;
        text += `*Jumlah Orang:* ${orang}%0A`;
        text += `*Pilihan Area:* ${area}%0A`;
        
        if (catatan) {
            text += `*Catatan Tambahan:* ${catatan}%0A`;
        }
        
        text += `%0AMohon konfirmasinya. Terima kasih!`;
        
        const btn = document.querySelector('.btn-wa');
        btn.innerHTML = 'MEMPROSES...';
        btn.style.background = 'var(--navy)';
        
        setTimeout(() => {
            window.open(`https://wa.me/${waNumber}?text=${text}`, '_blank');
            btn.innerHTML = 'KIRIM VIA WHATSAPP';
            btn.style.background = '#25D366';
        }, 500);
    });
</script>

</body>
</html>