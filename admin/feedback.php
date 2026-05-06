<?php
ob_start();
session_start();
// Menggunakan path asli dari dashboard.php lo
include "../config/koneksi.php";

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// LOGIKA MODERASI
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $query = "UPDATE feedback SET status_moderasi='tampil' WHERE id_feedback=$id";
    } elseif ($action === 'reject') {
        $query = "UPDATE feedback SET status_moderasi='pending' WHERE id_feedback=$id";
    } elseif ($action === 'delete') {
        $query = "DELETE FROM feedback WHERE id_feedback=$id";
    }

    if (isset($query) && mysqli_query($conn, $query)) {
        header("Location: feedback.php?msg=success");
        exit();
    }
}

// Ambil data feedback pelanggan
$query = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as tanggal FROM feedback ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>feedback-admin</title>
    <!-- Fonts sesuai desain dashboard -->
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <!-- Link ke file CSS terpisah -->
<!-- Perhatikan ../ untuk keluar dari folder admin dulu -->
<link rel="stylesheet" href="../assets/css/admin/feedback_style.css">
</head>
<body>

<aside class="sidebar">
    <div class="brand">WOELANDARI STAFF</div>
    <nav class="nav-list">
          <a href="dashboard.php" class="nav-item"><span>> DASHBOARD</span></a>
        <a href="menu_crud.php" class="nav-item"><span>> KELOLA MENU</span></a>
        <a href="gallery_crud.php" class="nav-item"><span>> KELOLA GALLERY & EVENT</span></a>
        <a href="feedback.php" class="nav-item active"><span>> KELOLA FEEDBACK & RATING</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>> KELOLA USER</span></a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
        </div>
    </nav>
</aside>

<main class="main-wrapper">
    <section class="paper">
        <div class="tape"></div>
        <div class="sticky-note">
            <p>USER: <?php echo $username; ?></p>
            <p>STATUS: <span class="blink">ONLINE</span></p>
        </div>
        
        <div class="spec-header">
          
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">FEEDBACK_SYSTEM</h1>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CLIENT_NAME</th>
                        <th>RATING</th>
                        <th>LOG_MESSAGE</th>
                        <th style="text-align: center;">STATUS</th>
                        <th style="text-align: center;">OP_CMD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#<?php echo $row['id_feedback']; ?></td>
                        <td class="bold"><?php echo strtoupper($row['nama_pelanggan']); ?></td>
                        <td class="rating-stars">
                            <?php for($i=1;$i<=5;$i++) echo $i <= $row['rating'] ? '★' : '☆'; ?>
                        </td>
                        <td>
                            <div style="margin-bottom: 8px;">"<?php echo htmlspecialchars($row['komentar']); ?>"</div>
                            <span class="timestamp"><?php echo $row['tanggal']; ?></span>
                        </td>
                        <td align="center">
                            <span class="status-badge status-<?php echo $row['status_moderasi']; ?>">
                                <?php echo strtoupper($row['status_moderasi']); ?>
                            </span>
                        </td>
                        <td align="center">
                            <div style="display: flex; justify-content: center;">
                                <?php if($row['status_moderasi']=='pending'): ?>
                                    <a href="?action=approve&id=<?php echo $row['id_feedback']; ?>" class="op-btn">APPROVE</a>
                                <?php else: ?>
                                    <a href="?action=reject&id=<?php echo $row['id_feedback']; ?>" class="op-btn">HIDE</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $row['id_feedback']; ?>" 
                                   class="op-btn del" 
                                   onclick="return confirm('ERASE DATA?')">DEL</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
<?php ob_end_flush(); ?>