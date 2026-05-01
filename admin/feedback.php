<?php
require_once __DIR__ . '/auth.php';

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($id > 0 && $action === 'approve') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi = 'tampil' WHERE id_feedback = $id");
    } elseif ($id > 0 && $action === 'reject') {
        mysqli_query($conn, "UPDATE feedback SET status_moderasi = 'pending' WHERE id_feedback = $id");
    } elseif ($id > 0 && $action === 'delete') {
        mysqli_query($conn, "DELETE FROM feedback WHERE id_feedback = $id");
    }

    header("Location: feedback.php?msg=success");
    exit;
}

$result = mysqli_query($conn, "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') AS tanggal FROM feedback ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Moderation - Woelandari</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin/feedback_style.css">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <div>
            <span class="tag">// MODERATION_UNIT</span>
            <h1>FEEDBACK SYSTEM</h1>
        </div>
        <nav>
            <a href="dashboard.php" class="btn-nav">Dashboard</a>
            <a href="menu_crud.php" class="btn-nav">Menu</a>
            <a href="gallery_crud.php" class="btn-nav">Gallery</a>
            <a href="user_manajemen.php" class="btn-nav">User</a>
            <a href="../logout.php" class="btn-nav">Logout</a>
        </nav>
    </header>

    <div class="status-bar">
        OPERATOR: <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?> |
        RECORDS: <?php echo mysqli_num_rows($result); ?>
        <?php if (isset($_GET['msg'])): ?> | ACTION_SUCCESS<?php endif; ?>
    </div>

    <main class="content">
        <div class="table-wrapper">
            <table class="brutalist-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Rating</th>
                    <th>Komentar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr>
                        <td colspan="6">Belum ada feedback pelanggan.</td>
                    </tr>
                <?php endif; ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="<?php echo $row['status_moderasi'] === 'pending' ? 'row-pending' : ''; ?>">
                        <td>#<?php echo (int) $row['id_feedback']; ?></td>
                        <td><strong><?php echo htmlspecialchars(strtoupper($row['nama_pelanggan'])); ?></strong></td>
                        <td class="rating-cell">
                            <?php for ($i = 1; $i <= 5; $i++) echo $i <= (int) $row['rating'] ? '&#9733;' : '&#9734;'; ?>
                        </td>
                        <td class="comment-cell">
                            "<?php echo htmlspecialchars($row['komentar']); ?>"
                            <br><small><?php echo htmlspecialchars($row['tanggal']); ?></small>
                        </td>
                        <td>
                            <span class="status-tag <?php echo htmlspecialchars($row['status_moderasi']); ?>">
                                <?php echo htmlspecialchars(strtoupper($row['status_moderasi'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-cell">
                                <?php if ($row['status_moderasi'] === 'pending'): ?>
                                    <a href="feedback.php?action=approve&id=<?php echo (int) $row['id_feedback']; ?>" class="btn-action approve" title="Tampilkan"><i class="fa-solid fa-check"></i></a>
                                <?php else: ?>
                                    <a href="feedback.php?action=reject&id=<?php echo (int) $row['id_feedback']; ?>" class="btn-action reject" title="Sembunyikan"><i class="fa-solid fa-eye-slash"></i></a>
                                <?php endif; ?>
                                <a href="feedback.php?action=delete&id=<?php echo (int) $row['id_feedback']; ?>" class="btn-action delete" title="Hapus" onclick="return confirm('Hapus feedback ini?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="admin-footer">WOELANDARI COFFEE LAB // CUSTOMER VOICE ARCHIVE</footer>
</div>
</body>
</html>
