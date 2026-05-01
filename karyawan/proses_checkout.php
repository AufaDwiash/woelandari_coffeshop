<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu_kasir.php');
    exit;
}

$items = json_decode($_POST['data_transaksi'] ?? '[]', true);
$total = max(0, (int) ($_POST['total_belanja_hidden'] ?? 0));
$kembalian = max(0, (int) ($_POST['kembalian_hidden'] ?? 0));
$metode = ($_POST['metode_pembayaran'] ?? 'CASH') === 'QRIS' ? 'QRIS' : 'CASH';
$uang_diterima = $metode === 'QRIS' ? $total : max(0, (int) ($_POST['uang_diterima'] ?? 0));

if (empty($items) || $total <= 0 || ($metode === 'CASH' && $uang_diterima < $total)) {
    echo "<script>alert('Transaksi tidak valid.'); window.location='menu_kasir.php';</script>";
    exit;
}

mysqli_begin_transaction($conn);

try {
    mysqli_query($conn, "INSERT INTO penjualan (total, metode_pembayaran, uang_diterima, kembalian) VALUES ($total, '$metode', $uang_diterima, $kembalian)");
    $id_penjualan = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $id_menu = (int) ($item['id'] ?? 0);
        $nama = mysqli_real_escape_string($conn, $item['nama'] ?? '');
        $harga = max(0, (int) ($item['harga'] ?? 0));
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $subtotal = $harga * $qty;

        mysqli_query($conn, "INSERT INTO detail_penjualan (id_penjualan, id_menu, nama_menu, harga, qty, subtotal) VALUES ($id_penjualan, $id_menu, '$nama', $harga, $qty, $subtotal)");
        mysqli_query($conn, "UPDATE menu SET stok = GREATEST(stok - $qty, 0) WHERE id_menu = $id_menu");
    }

    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    echo "<script>alert('Gagal menyimpan transaksi.'); window.location='menu_kasir.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Transaksi - Woelandari</title>
    <style>
        body { font-family: "Courier New", monospace; max-width: 420px; margin: 30px auto; color: #111; }
        h1 { font-size: 20px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        td, th { padding: 6px 0; border-bottom: 1px dashed #999; text-align: left; }
        .right { text-align: right; }
        .total { font-weight: bold; font-size: 18px; }
        .actions { display: flex; gap: 10px; margin-top: 20px; }
        a, button { flex: 1; padding: 10px; border: 2px solid #111; background: #fff; color: #111; text-align: center; text-decoration: none; cursor: pointer; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <h1>WOELANDARI COFFEE LAB</h1>
    <p>Nota: #<?php echo (int) $id_penjualan; ?><br>Tanggal: <?php echo date('d/m/Y H:i'); ?><br>Kasir: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th class="right">Sub</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nama']); ?></td>
                <td><?php echo (int) $item['qty']; ?></td>
                <td class="right"><?php echo number_format(((int) $item['harga']) * ((int) $item['qty']), 0, ',', '.'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total">TOTAL: Rp <?php echo number_format($total, 0, ',', '.'); ?></p>
    <p>Metode: <?php echo htmlspecialchars($metode); ?><br>Dibayar: Rp <?php echo number_format($uang_diterima, 0, ',', '.'); ?><br>Kembali: Rp <?php echo number_format($kembalian, 0, ',', '.'); ?></p>
    <div class="actions">
        <button onclick="window.print()">Cetak</button>
        <a href="menu_kasir.php">Transaksi Baru</a>
    </div>
</body>
</html>
