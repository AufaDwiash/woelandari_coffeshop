<?php
// Sesuaikan path koneksi dengan struktur folder Anda
include '../config/koneksi.php';

if (isset($_POST['submit_feedback'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']); // Opsional
    $rating = $_POST['rating'];
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
    $tanggal = date('Y-m-d H:i:s');
    
    // Status default 'pending' agar tidak langsung tayang sebelum di-approve Admin
    $status = 'pending'; 

    $query = "INSERT INTO feedback (nama_pelanggan, kontak, rating, komentar, tanggal, status) 
              VALUES ('$nama', '$kontak', '$rating', '$komentar', '$tanggal', '$status')";
              
    if(mysqli_query($conn, $query)) {
        echo "<script>alert('TRANSMISSION_SUCCESS: Terima kasih atas ulasan Anda!'); window.location='feedback.php';</script>";
    } else {
        echo "<script>alert('SYSTEM_ERROR: Gagal mengirim data.'); window.location='feedback.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Feedback Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Kirim Feedback</h4>
                </div>
                <div class="card-body">
                    <form action="proses_simpan.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="nama_pelanggan" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" placeholder="Masukkan nama Anda" required>
                        </div>

                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (1-5)</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="" selected disabled>Pilih Rating...</option>
                                <option value="5">5 - Sangat Puas</option>
                                <option value="4">4 - Puas</option>
                                <option value="3">3 - Cukup</option>
                                <option value="2">2 - Kurang</option>
                                <option value="1">1 - Sangat Kurang</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="komentar" class="form-label">Komentar / Feedback</label>
                            <textarea class="form-control" id="komentar" name="komentar" rows="4" placeholder="Tulis masukan Anda di sini..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status_moderasi" class="form-label">Status Moderasi</label>
                            <select class="form-select" id="status_moderasi" name="status_moderasi">
                                <option value="pending" selected>Pending</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirim Feedback</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>