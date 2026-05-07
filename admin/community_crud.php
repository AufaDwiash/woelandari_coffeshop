<?php 
ob_start();
session_start();
include "../config/koneksi.php"; 

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// Buat folder temp jika belum ada
if(!file_exists('../assets/images/temp')) {
    mkdir('../assets/images/temp', 0777, true);
}

// A. PROSES TAMBAH DATA (UPLOAD)
if(isset($_POST['add_human'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = $_POST['display_order'];
    $status = 'active';
    
    // Ambil data crop dari hidden field
    $crop_data = $_POST['crop_data'] ?? '';
    $temp_image = $_POST['temp_image'] ?? '';
    
    if(!empty($crop_data) && !empty($temp_image) && $crop_data != 'null') {
        // Decode base64 crop data
        $crop_data = json_decode($crop_data, true);
        
        // Path gambar temporary
        $temp_path = '../assets/images/temp/' . $temp_image;
        
        if(file_exists($temp_path)) {
            // Load gambar
            $image_info = getimagesize($temp_path);
            $image_type = $image_info['mime'];
            
            // Create image resource based on type
            switch($image_type) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($temp_path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($temp_path);
                    imagealphablending($source, false);
                    imagesavealpha($source, true);
                    break;
                default:
                    $source = imagecreatefromjpeg($temp_path);
            }
            
            // Crop dimensions
            $crop_x = (int)$crop_data['x'];
            $crop_y = (int)$crop_data['y'];
            $crop_width = (int)$crop_data['width'];
            $crop_height = (int)$crop_data['height'];
            
            // Create cropped image
            $cropped = imagecrop($source, ['x' => $crop_x, 'y' => $crop_y, 'width' => $crop_width, 'height' => $crop_height]);
            
            if($cropped !== false) {
                // Generate final filename
                $new_img_name = 'human_' . time() . '.png';
                $upload_path = '../assets/images/community/' . $new_img_name;
                
                // Save as PNG for better quality
                imagepng($cropped, $upload_path, 9);
                imagedestroy($cropped);
                imagedestroy($source);
                
                // Delete temp file
                if(file_exists($temp_path)) unlink($temp_path);
                
                // Insert to database
                mysqli_query($conn, "INSERT INTO human_archive (name, role, quote, image, display_order, status) 
                                        VALUES ('$name', '$role', '$quote', '$new_img_name', '$order', '$status')");
                echo "<script>alert('Data berhasil ditambahkan dengan crop!'); window.location='community_crud.php';</script>";
                exit;
            }
        }
    }
    
    // Fallback ke upload biasa (tanpa crop)
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $new_img_name = 'human_' . time() . '.' . $ext;
        $upload_path = '../assets/images/community/' . $new_img_name;
        
        if(move_uploaded_file($tmp_name, $upload_path)) {
            mysqli_query($conn, "INSERT INTO human_archive (name, role, quote, image, display_order, status) 
                                    VALUES ('$name', '$role', '$quote', '$new_img_name', '$order', '$status')");
            echo "<script>alert('Data berhasil ditambahkan!'); window.location='community_crud.php';</script>";
            exit;
        }
    }
    
    echo "<script>alert('Gagal menambahkan data!'); window.location='community_crud.php';</script>";
    exit;
}

// B. PROSES UPDATE URUTAN TAMPIL
if(isset($_POST['update_order'])) {
    if(!empty($_POST['order'])) {
        foreach($_POST['order'] as $id => $order_val) {
            $id = intval($id);
            $order_val = intval($order_val);
            mysqli_query($conn, "UPDATE human_archive SET display_order='$order_val' WHERE id='$id'");
        }
        echo "<script>alert('Urutan berhasil diperbarui!'); window.location='community_crud.php';</script>";
        exit;
    }
}

// C. PROSES HIDE/SHOW (Ubah Status)
if(isset($_GET['toggle']) && isset($_GET['current'])) {
    $id = intval($_GET['toggle']);
    $current_status = $_GET['current'];
    
    $new_status = ($current_status == 'active') ? 'hidden' : 'active';
    mysqli_query($conn, "UPDATE human_archive SET status='$new_status' WHERE id='$id'");
    
    header("Location: community_crud.php");
    exit;
}

// D. PROSES HAPUS PERMANEN
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $cari_foto = mysqli_query($conn, "SELECT image FROM human_archive WHERE id='$id'");
    if($data_foto = mysqli_fetch_assoc($cari_foto)) {
        if(file_exists('../assets/images/community/' . $data_foto['image'])) {
            unlink('../assets/images/community/' . $data_foto['image']);
        }
    }
    
    mysqli_query($conn, "DELETE FROM human_archive WHERE id='$id'");
    echo "<script>alert('Data dihapus permanen!'); window.location='community_crud.php';</script>";
    exit;
}

// E. UPLOAD TEMPORARY (AJAX untuk crop)
if(isset($_FILES['temp_image']) && isset($_POST['action']) && $_POST['action'] == 'upload_temp') {
    $file = $_FILES['temp_image'];
    $temp_name = 'temp_' . time() . '_' . uniqid() . '.png';
    $temp_path = '../assets/images/temp/' . $temp_name;
    
    // Process image
    $image_info = getimagesize($file['tmp_name']);
    $image_type = $image_info['mime'];
    
    switch($image_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file['tmp_name']);
            imagealphablending($source, false);
            imagesavealpha($source, true);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            $source = imagecreatefromjpeg($file['tmp_name']);
    }
    
    // Save as PNG to temp folder
    imagepng($source, $temp_path, 9);
    imagedestroy($source);
    
    echo json_encode(['success' => true, 'temp_image' => $temp_name]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Human Archive // Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&family=Caveat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        :root {
            --navy: #0A1D37;
            --red: #D63D3D;
            --white: #F8F9FA;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 8px 8px 0 rgba(10, 29, 55, 0.15);
            --border-thick: 2px solid var(--navy);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px),
                              linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            border-right: 3px solid var(--navy);
            height: 100vh;
            position: fixed;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .brand {
            font-family: 'Special Elite', cursive;
            font-size: 1.6rem;
            border-bottom: 3px double var(--navy);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--red);
            text-align: center;
        }

        .nav-item {
            display: block;
            padding: 14px 18px;
            color: var(--navy);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--navy);
            color: var(--white);
            transform: translateX(5px);
            box-shadow: 4px 4px 0 var(--red);
        }

        /* MAIN WRAPPER */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 35px;
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: 35px;
        }

        /* PAPER CARD */
        .paper {
            background: var(--white);
            border: 2px solid var(--navy);
            padding: 35px;
            position: relative;
            box-shadow: 8px 8px 0 rgba(10, 29, 55, 0.15);
            width: 100%;
        }

        .paper-style-1 { transform: rotate(-0.3deg); }
        .paper-style-2 { transform: rotate(0.3deg); }

        .tape {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 35px; 
            background: rgba(214, 61, 61, 0.8);
            border: 1px dashed rgba(255,255,255,0.4);
            z-index: 2;
        }

        .sticky-note {
            position: absolute; top: 25px; right: 25px;
            background: #fff9c4;
            padding: 12px 18px;
            width: 170px;
            transform: rotate(2deg);
            font-family: 'Caveat', cursive;
            font-size: 1.15rem;
            border: 1px solid #f0e68c;
            z-index: 5;
        }

        .spec-header {
            display: flex; 
            justify-content: space-between; 
            font-size: 11px; 
            font-weight: 900;
            border-bottom: 2px solid var(--navy); 
            padding-bottom: 10px; 
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 1.8rem; 
            margin-bottom: 25px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        .blink { animation: pulse 1.5s infinite; color: var(--red); }
        @keyframes pulse { 50% { opacity: 0.3; } }

        /* FORM STYLES */
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.4rem;
            color: var(--navy);
        }
        
        input[type="text"], 
        input[type="number"], 
        textarea, 
        input[type="file"] {
            width: 100%;
            padding: 0.7rem;
            font-family: 'Courier Prime', monospace;
            border: 2px solid #ccc;
            background: var(--white);
            color: var(--navy);
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--red);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
        }
        
        .form-row .form-group {
            flex: 1;
            min-width: 150px;
        }

        /* CROP MODAL */
        .crop-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .crop-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            max-width: 90vw;
            max-height: 90vh;
            overflow: auto;
            border: 3px solid var(--navy);
        }
        
        .crop-container h3 {
            font-family: 'Special Elite', cursive;
            margin-bottom: 15px;
            color: var(--navy);
        }
        
        .image-container {
            max-width: 800px;
            max-height: 500px;
            margin-bottom: 15px;
        }
        
        .image-container img {
            max-width: 100%;
            display: block;
        }
        
        .crop-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .crop-actions button {
            padding: 10px 25px;
            font-family: 'Special Elite', cursive;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn-crop {
            background: var(--red);
            color: white;
        }
        
        .btn-crop:hover {
            background: #b02a2a;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #666;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #444;
        }
        
        .preview-area {
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border: 1px dashed var(--navy);
        }
        
        .preview-image {
            max-width: 120px;
            max-height: 120px;
            border: 2px solid var(--navy);
            margin-top: 8px;
            border-radius: 4px;
        }
        
        /* BUTTONS */
        button[type="submit"] {
            background: var(--navy);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            font-family: 'Special Elite', cursive;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 3px 3px 0 var(--red);
            transition: all 0.2s;
            letter-spacing: 1px;
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 0 var(--red);
            background: var(--red);
        }
        
        .btn-success {
            background: #27AE60;
            box-shadow: 3px 3px 0 #1e7e48;
        }
        
        .btn-success:hover {
            background: #1e7e48;
            transform: translateY(-2px);
            box-shadow: 5px 5px 0 #1e7e48;
        }
        
        /* TABLE */
        .table-responsive {
            overflow-x: auto;
            margin-top: 1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        th {
            background: var(--navy);
            color: white;
            padding: 1rem;
            text-align: left;
            font-family: 'Special Elite', cursive;
            font-size: 0.8rem;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 1rem;
            vertical-align: middle;
            background: var(--white);
        }
        
        tr:hover td {
            background: #f9f9f9;
        }
        
        .preview-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background: #f0f0f0;
            border: 1px solid #ddd;
        }
        
        .role-badge {
            color: var(--red);
            font-family: 'Special Elite', cursive;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-active {
            background: #27AE60;
            color: white;
        }
        
        .status-hidden {
            background: #7f8c8d;
            color: white;
        }
        
        .action-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-links a {
            text-decoration: none;
            padding: 6px 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s;
            font-family: 'Special Elite', cursive;
        }
        
        .btn-toggle {
            background: #f39c12;
            color: white;
        }
        
        .btn-toggle:hover {
            background: #e67e22;
        }
        
        .btn-del {
            background: #e74c3c;
            color: white;
        }
        
        .btn-del:hover {
            background: #c0392b;
        }
        
        .order-input {
            width: 80px;
            text-align: center;
            padding: 6px;
            font-family: 'Courier Prime', monospace;
            font-weight: bold;
            border: 2px solid var(--navy);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
            font-family: 'Special Elite', cursive;
        }
        
        hr {
            border: none;
            border-top: 2px dashed rgba(0,0,0,0.1);
            margin: 1rem 0;
        }
        
        small {
            font-size: 0.65rem;
            opacity: 0.7;
            display: block;
            margin-top: 4px;
        }
        
        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar .brand, .nav-item span { display: none; }
            .main-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        }
        
        @media (max-width: 768px) {
            .main-wrapper { padding: 20px; }
            .paper { padding: 20px; }
            .form-row { flex-direction: column; gap: 0; }
            .sticky-note { display: none; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="brand">WOELANDARI STAFF</div>
    <nav>
        <a href="dashboard.php" class="nav-item"><span>> DASHBOARD</span></a>
        <a href="menu_crud.php" class="nav-item"><span>> KELOLA MENU</span></a>
        <a href="gallery_crud.php" class="nav-item"><span>> KELOLA GALLERY & EVENT</span></a>
        <a href="feedback.php" class="nav-item"><span>> KELOLA FEEDBACK & RATING</span></a>
        <a href="community_crud.php" class="nav-item active"><span>> HUMAN ARCHIVE</span></a>
        <a href="user_manajemen.php" class="nav-item"><span>> KELOLA USER</span></a>
        <div style="margin-top: auto;">
            <a href="../logout.php" class="nav-item" style="color: var(--red);"><span>KELUAR</span></a>
        </div>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="main-wrapper">
    
    <!-- FORM TAMBAH DATA -->
    <section class="paper paper-style-1">
        <div class="tape"></div>
        <div class="sticky-note">
            <p>USER: <?php echo $username; ?></p>
            <p>STATUS: <span class="blink">ONLINE</span></p>
        </div>
        
        <div class="spec-header">
            <span>MODULE: HUMAN_ARCHIVE</span>
            <span>DATE: <?php echo date('d/m/Y'); ?></span>
        </div>

        <h1 class="title-main">
            <i class="fas fa-users"></i> + Tambah Entri Baru
        </h1>
        
        <form id="mainForm" action="" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Subjek</label>
                    <input type="text" name="name" required placeholder="Cth: Satria">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Peran (Role)</label>
                    <input type="text" name="role" required placeholder="Cth: HEAD ROASTER">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-quote-left"></i> Kutipan / Quote Pendek (Opsional)</label>
                <textarea name="quote" rows="2" placeholder="Cth: Merawat mesin, merawat rasa."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-sort-numeric-down"></i> Urutan Tampil</label>
                    <input type="number" name="display_order" value="1" required>
                    <small>Paling kecil tampil duluan</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-crop-alt"></i> Foto (Crop & Upload)</label>
                    <input type="file" id="imageInput" accept="image/png,image/jpeg,image/jpg,image/webp">
                    <small>Pilih gambar, lalu crop sesuai kebutuhan (Rasio 1:1)</small>
                    
                    <input type="hidden" name="crop_data" id="cropData">
                    <input type="hidden" name="temp_image" id="tempImage">
                    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                    
                    <div id="previewArea" style="display: none;" class="preview-area">
                        <label><i class="fas fa-check-circle"></i> Preview Hasil Crop:</label>
                        <div>
                            <img id="previewImage" class="preview-image" src="">
                        </div>
                        <small style="color: green;">✓ Gambar siap diupload</small>
                    </div>
                </div>
            </div>

            <button type="submit" name="add_human" id="submitBtn">
                <i class="fas fa-save"></i> Simpan & Unggah
            </button>
        </form>
    </section>

    <!-- DAFTAR ARSIP -->
    <section class="paper paper-style-2">
        <div class="spec-header">
            <span>MODULE: ARCHIVE_LIST</span>
            <span>REF: WLDRI-HUMAN-001</span>
        </div>

        <h2 class="title-main" style="font-size: 1.5rem;">
            <i class="fas fa-archive"></i> Daftar Arsip & Pengaturan Urutan
        </h2>
        
        <form action="" method="POST">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Preview</th>
                            <th>Nama & Peran</th>
                            <th width="120">Urutan</th>
                            <th width="100">Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q = mysqli_query($conn, "SELECT * FROM human_archive ORDER BY display_order ASC, id DESC");
                        if(mysqli_num_rows($q) > 0):
                            while($d = mysqli_fetch_array($q)):
                                $status_class = ($d['status'] == 'active') ? 'status-active' : 'status-hidden';
                        ?>
                        <tr>
                            <td>
                                <img src="../assets/images/community/<?php echo $d['image']; ?>" 
                                     class="preview-img" 
                                     onerror="this.src='https://placehold.co/70x70?text=No+Image'">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($d['name']); ?></strong><br>
                                <span class="role-badge">// <?php echo htmlspecialchars($d['role']); ?></span>
                                <?php if(!empty($d['quote'])): ?>
                                    <br><small><i class="fas fa-quote-left"></i> <?php echo htmlspecialchars(substr($d['quote'], 0, 60)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" name="order[<?php echo $d['id']; ?>]" 
                                       value="<?php echo $d['display_order']; ?>" 
                                       class="order-input">
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <i class="fas <?php echo ($d['status'] == 'active') ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                                    <?php echo strtoupper($d['status']); ?>
                                </span>
                            </td>
                            <td class="action-links">
                                <a href="?toggle=<?php echo $d['id']; ?>&current=<?php echo $d['status']; ?>" class="btn-toggle">
                                    <i class="fas <?php echo ($d['status'] == 'active') ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    <?php echo ($d['status'] == 'active') ? 'HIDE' : 'SHOW'; ?>
                                </a>
                                <a href="?delete=<?php echo $d['id']; ?>" class="btn-del" 
                                   onclick="return confirm('Yakin hapus data ini?')">
                                    <i class="fas fa-trash"></i> DELETE
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fas fa-box-open"></i><br>
                                Belum ada data arsip.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <hr>
            
            <div style="margin-top: 1.5rem;">
                <button type="submit" name="update_order" class="btn-success">
                    <i class="fas fa-save"></i> Simpan Perubahan Urutan
                </button>
            </div>
        </form>
    </section>
</main>

<!-- CROP MODAL -->
<div id="cropModal" class="crop-modal">
    <div class="crop-container">
        <h3><i class="fas fa-crop-alt"></i> Crop Gambar (Rasio 1:1)</h3>
        <div class="image-container">
            <img id="cropImage" src="">
        </div>
        <div class="crop-actions">
            <button id="cropBtn" class="btn-crop"><i class="fas fa-check"></i> Crop & Simpan</button>
            <button id="cancelCropBtn" class="btn-cancel"><i class="fas fa-times"></i> Batal</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const cropModal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const cropBtn = document.getElementById('cropBtn');
    const cancelCropBtn = document.getElementById('cancelCropBtn');
    const previewArea = document.getElementById('previewArea');
    const previewImage = document.getElementById('previewImage');
    const cropDataInput = document.getElementById('cropData');
    const tempImageInput = document.getElementById('tempImage');
    const mainForm = document.getElementById('mainForm');
    const submitBtn = document.getElementById('submitBtn');
    
    let cropper = null;
    
    // Handler untuk upload file
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Hanya file gambar (JPG, PNG, WEBP) yang diperbolehkan!');
            imageInput.value = '';
            return;
        }
        
        // Validasi ukuran (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 5MB!');
            imageInput.value = '';
            return;
        }
        
        // Upload ke temporary folder via AJAX
        const formData = new FormData();
        formData.append('temp_image', file);
        formData.append('action', 'upload_temp');
        
        // Tampilkan loading
        cropBtn.textContent = 'Loading...';
        cropBtn.disabled = true;
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tempImageInput.value = data.temp_image;
                
                // Tampilkan modal crop dengan gambar temporary
                const reader = new FileReader();
                reader.onload = function(event) {
                    cropImage.src = event.target.result;
                    cropModal.style.display = 'flex';
                    
                    // Inisialisasi Cropper setelah gambar load
                    setTimeout(function() {
                        if (cropper) cropper.destroy();
                        cropper = new Cropper(cropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            background: true,
                            responsive: true,
                            checkCrossOrigin: false
                        });
                    }, 100);
                };
                reader.readAsDataURL(file);
            } else {
                alert('Gagal upload gambar!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat upload gambar!');
        })
        .finally(() => {
            cropBtn.textContent = 'Crop & Simpan';
            cropBtn.disabled = false;
        });
    });
    
    // Handler untuk crop & simpan
    cropBtn.addEventListener('click', function() {
        if (cropper) {
            // Dapatkan data crop
            const cropData = cropper.getData();
            const canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            
            // Konversi ke base64 untuk preview
            const croppedImageUrl = canvas.toDataURL('image/png');
            previewImage.src = croppedImageUrl;
            previewArea.style.display = 'block';
            
            // Simpan data crop ke hidden field
            cropDataInput.value = JSON.stringify({
                x: Math.round(cropData.x),
                y: Math.round(cropData.y),
                width: Math.round(cropData.width),
                height: Math.round(cropData.height),
                rotate: cropData.rotate || 0
            });
            
            // Tutup modal
            cropModal.style.display = 'none';
            cropper.destroy();
            cropper = null;
            
            // Reset file input
            imageInput.value = '';
        }
    });
    
    // Handler untuk batal crop
    cancelCropBtn.addEventListener('click', function() {
        cropModal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        imageInput.value = '';
        // Hapus temp file jika ada
        if (tempImageInput.value) {
            // Optional: hapus temp file via AJAX
            tempImageInput.value = '';
        }
    });
    
    // Validasi form sebelum submit
    mainForm.addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value;
        const role = document.querySelector('input[name="role"]').value;
        const cropData = cropDataInput.value;
        
        if (!name || !role) {
            e.preventDefault();
            alert('Nama dan Peran harus diisi!');
            return;
        }
        
        if (!cropData || cropData === 'null' || cropData === '') {
            e.preventDefault();
            alert('Harap upload dan crop gambar terlebih dahulu!');
            return;
        }
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>