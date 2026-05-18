<?php
// dashboard/community_crud.php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ========== PROSES TAMBAH DATA ==========
if (isset($_POST['add_human'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = (int)$_POST['display_order'];
    $status = 'active';

    $foto_nama = '';
    
    // Proses gambar hasil crop (Base64)
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.png';
        file_put_contents('../assets/images/community/' . $foto_nama, $img_base64);
    } 
    // Fallback jika upload biasa tanpa crop
    elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/community/' . $foto_nama);
    } else {
        $foto_nama = 'default.jpg';
    }

    mysqli_query($conn, "INSERT INTO human_archive (name, role, quote, image, display_order, status) 
                          VALUES ('$name', '$role', '$quote', '$foto_nama', '$order', '$status')");
    header("Location: community_crud.php?msg=add");
    exit;
}

// ========== UPDATE DATA ==========
if (isset($_POST['update_human'])) {
    $id = (int)$_POST['id_human'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $quote = mysqli_real_escape_string($conn, $_POST['quote']);
    $order = (int)$_POST['display_order'];
    $foto_lama = $_POST['foto_lama'] ?? '';
    
    $foto_nama = $foto_lama;

    // Jika ada gambar baru yang dicrop
    if (!empty($_POST['foto_cropped'])) {
        $img_parts = explode(";base64,", $_POST['foto_cropped']);
        $img_base64 = base64_decode($img_parts[1]);
        $foto_nama = 'human_' . time() . '_' . uniqid() . '.png';
        file_put_contents('../assets/images/community/' . $foto_nama, $img_base64);
        
        // Hapus foto lama
        if ($foto_lama && $foto_lama != 'default.jpg' && file_exists('../assets/images/community/' . $foto_lama)) {
            unlink('../assets/images/community/' . $foto_lama);
        }
    }

    mysqli_query($conn, "UPDATE human_archive SET name='$name', role='$role', quote='$quote', display_order='$order', image='$foto_nama' WHERE id=$id");
    header("Location: community_crud.php?msg=update");
    exit;
}

// ========== UPDATE URUTAN ==========
if (isset($_POST['update_order'])) {
    if (!empty($_POST['order'])) {
        foreach ($_POST['order'] as $id => $order_val) {
            $id = (int)$id;
            $order_val = (int)$order_val;
            mysqli_query($conn, "UPDATE human_archive SET display_order='$order_val' WHERE id='$id'");
        }
        header("Location: community_crud.php?msg=order");
        exit;
    }
}

// ========== TOGGLE STATUS ==========
if (isset($_GET['toggle']) && isset($_GET['current'])) {
    $id = (int)$_GET['toggle'];
    $current = $_GET['current'];
    $new = ($current == 'active') ? 'hidden' : 'active';
    mysqli_query($conn, "UPDATE human_archive SET status='$new' WHERE id='$id'");
    header("Location: community_crud.php");
    exit;
}

// ========== HAPUS DATA ==========
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $f = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM human_archive WHERE id=$id"));
    if ($f && $f['image'] != 'default.jpg' && file_exists('../assets/images/community/' . $f['image'])) {
        unlink('../assets/images/community/' . $f['image']);
    }
    mysqli_query($conn, "DELETE FROM human_archive WHERE id=$id");
    header("Location: community_crud.php?msg=delete");
    exit;
}

// ========== AMBIL DATA EDIT ==========
$edit_mode = false;
$edit_id = $edit_name = $edit_role = $edit_quote = $edit_order = $edit_status = $edit_image = "";
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM human_archive WHERE id=$id");
    $d = mysqli_fetch_assoc($q);
    if ($d) {
        $edit_mode = true;
        $edit_id = $d['id'];
        $edit_name = $d['name'];
        $edit_role = $d['role'];
        $edit_quote = $d['quote'];
        $edit_order = $d['display_order'];
        $edit_status = $d['status'];
        $edit_image = $d['image'];
    }
}

$msg_display = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'add') $msg_display = "Anggota Komunitas berhasil ditambahkan!";
    elseif ($_GET['msg'] == 'update') $msg_display = "Data Anggota berhasil diperbarui!";
    elseif ($_GET['msg'] == 'delete') $msg_display = "Data Anggota berhasil dihapus dari arsip!";
    elseif ($_GET['msg'] == 'order') $msg_display = "Urutan tampil berhasil diperbarui!";
    elseif ($_GET['msg'] == 'error') $msg_display = "Terjadi kesalahan pada sistem!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Komunitas - Woelandari Coffee Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        :root {
            --navy: #002B5B;
            --red: #EA4335;
            --white: #F8F9FA;
            --grid-line: rgba(208, 225, 249, 0.4);
            --bg-color: #6291d8;
            --sidebar-width: 260px;
            --shadow-clean: 12px 12px 0 rgba(0, 43, 91, 0.2);
            --border-thick: 2px solid var(--navy);
            --gap-section: 35px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier Prime', monospace;
            background-color: var(--bg-color);
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0) rotate(-0.2deg); }
        }
        @keyframes floatTape {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-2px); }
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: var(--gap-section);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            gap: var(--gap-section);
            transition: all 0.3s ease;
        }

        .paper {
            background: var(--white);
            border: var(--border-thick);
            padding: 40px;
            position: relative;
            box-shadow: var(--shadow-clean);
            width: 100%;
            opacity: 0; 
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .tape {
            position: absolute;
            top: -16px; left: 50%; transform: translateX(-50%);
            width: 140px; height: 32px;
            background: rgba(234, 67, 53, 0.9);
            border: 1px dashed rgba(255,255,255,0.5);
            z-index: 10;
            box-shadow: 2px 3px 5px rgba(0,0,0,0.1);
            animation: floatTape 3s ease-in-out infinite;
        }

        .spec-header {
            display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;
            border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 30px;
            text-transform: uppercase;
        }

        .title-main {
            font-family: 'Special Elite', cursive;
            font-size: 2.2rem; margin-bottom: 20px;
            color: var(--navy);
            border-left: 8px solid var(--red);
            padding-left: 20px;
        }

        .alert-msg { background: #fff9c4; border: 2px dashed #e0d68c; padding: 10px 15px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid var(--red); }

        .btn {
            font-family: 'Special Elite', cursive; font-size: 0.9rem; font-weight: bold;
            padding: 11px 20px; border: 2px solid var(--navy); cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; justify-content: center;
            transition: all 0.1s ease; text-decoration: none; height: 46px;
        }
        .btn-primary { background: var(--navy); color: var(--white); box-shadow: 4px 4px 0 var(--red); }
        .btn-primary:hover { background: var(--white); color: var(--navy); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--red); }
        
        .btn-secondary { background: var(--white); color: var(--navy); box-shadow: 4px 4px 0 var(--navy); }
        .btn-secondary:hover { background: #e0e0e0; transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }

        .btn-danger { background: var(--white); color: var(--red); border-color: var(--red); box-shadow: 4px 4px 0 var(--red); }
        .btn-danger:hover { background: var(--red); color: var(--white); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--navy); }

        .btn-sm { padding: 0 12px; font-size: 0.75rem; box-shadow: 3px 3px 0 rgba(0,0,0,0.15); height: 32px; }

        .table-container {
            width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            border: 2px solid var(--navy); background: white; margin-bottom: 20px;
        }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-thumb { background: var(--navy); border-radius: 4px; }

        .data-table { width: 100%; border-collapse: collapse; min-width: 750px; table-layout: fixed; }
        .data-table th { background: var(--navy); color: white; padding: 14px 15px; text-align: left; font-family: 'Special Elite'; letter-spacing: 1px; }
        
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 95px; text-align: center; } /* Foto */
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: auto; } /* Nama/Role */
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: 120px; text-align: center; } /* Urutan */
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: 130px; text-align: center; } /* Status */
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 220px; text-align: center; } /* Aksi */

        .data-table td { padding: 12px 15px; border-bottom: 1px dashed rgba(0,43,91,0.2); vertical-align: middle; word-break: break-word; }
        .data-table tbody tr:hover td { background: rgba(0, 43, 91, 0.04); }

        .thumb-img { width: 60px; height: 60px; object-fit: cover; border: 2px solid var(--navy); padding: 1px; background: white; box-shadow: 2px 2px 0 var(--navy);}
        .action-buttons { display: inline-flex; gap: 8px; justify-content: center; width: 100%; flex-wrap: wrap;}

        .status-badge {
            padding: 4px 10px; border-radius: 2px; font-size: 0.75rem; font-weight: bold; border: 1px solid currentColor; display: inline-block;
        }
        .status-active { background: rgba(21, 87, 36, 0.08); color: #155724; }
        .status-hidden { background: rgba(114, 28, 36, 0.08); color: #721c24; }

        .order-input {
            width: 60px; text-align: center; padding: 6px; border: 2px solid var(--navy);
            font-family: 'Courier Prime', monospace; font-weight: bold; background: #fff;
            outline: none; box-shadow: inset 2px 2px 0 rgba(0,0,0,0.05);
        }

        /* MODAL STYLING */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.65); backdrop-filter: blur(4px); z-index: 2000;
            justify-content: center; align-items: center; padding: 15px;
        }
        .modal-content {
            background: var(--white); border: 4px solid var(--navy);
            width: 100%; max-width: 650px; 
            max-height: 92vh; 
            display: flex; flex-direction: column;
            box-shadow: 14px 14px 0 var(--red); position: relative;
        }
        .modal-header-area { padding: 25px 25px 10px 25px; flex-shrink: 0; }
        
        .modal-body-scroll {
            padding: 10px 25px 25px 25px; overflow-y: auto; flex: 1;
        }
        .modal-body-scroll::-webkit-scrollbar { width: 6px; }
        .modal-body-scroll::-webkit-scrollbar-thumb { background: var(--navy); }

        .form-grid { display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: bold; font-size: 0.85rem; margin-bottom: 6px; color: var(--navy); text-transform: uppercase; }
        .form-input, textarea {
            width: 100%; padding: 10px; border: 2px solid var(--navy); background: white;
            font-family: 'Courier Prime'; outline: none; box-shadow: inset 2px 2px 0 rgba(0,0,0,0.03);
        }
        .form-input:focus, textarea:focus { border-color: var(--red); }

        .upload-box-safe {
            background: rgba(0,43,91,0.04); padding: 15px; 
            border: 2px dashed var(--navy); margin-top: 10px; margin-bottom: 5px; text-align: center;
        }

        .overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,43,91,0.5); backdrop-filter: blur(2px); z-index: 900; opacity: 0; transition: opacity 0.3s;
        }
        .overlay.active { display: block; opacity: 1; }
        .mobile-header { display: none; }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; width: 100%; padding: 15px; margin-top: 70px; gap: 25px;}
            .mobile-header {
                display: flex; position: fixed; top: 0; left: 0; right: 0; height: 65px; z-index: 800;
                background: rgba(248, 249, 250, 0.9); backdrop-filter: blur(8px);
                border-bottom: 3px solid var(--navy); padding: 0 20px; align-items: center; justify-content: space-between;
            }
            .paper { padding: 25px 15px; }
            .btn { width: 100%; }
            .title-main { font-size: 1.6rem; }
            .tape { width: 110px; }
        }
    </style>
</head>
<body>

<div class="overlay" id="sidebarOverlay"></div>

<?php include "../components/sidebar.php"; ?>

<main class="main-wrapper">
    <div class="mobile-header">
        <div class="logo-mobile" style="font-family:'Special Elite'; color:var(--navy); font-size: 1.2rem;">
            <i class="fas fa-users" style="color: var(--red);"></i> WOELANDARI
        </div>
        <button class="hamburger" id="hamburgerBtn" style="background:none; border:none; font-size:1.6rem; color:var(--navy); cursor:pointer;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <section class="paper">
        <div class="tape"></div>
        <div class="spec-header">
            <span><i class="fas fa-folder-open"></i> Kelola Komunitas</span>
            <span>DATE: <?= date('d/m/Y') ?></span>
        </div>
        
        <?php if ($msg_display): ?>
            <div class="alert-msg"><i class="fas fa-info-circle"></i> <?= $msg_display ?></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h1 class="title-main" style="margin-bottom:0;">DAFTAR ANGGOTA</h1>
            <button class="btn btn-primary" id="showModalBtn" style="background: var(--red); box-shadow: 4px 4px 0 var(--navy); width: auto;">
                <i class="fas fa-plus"></i> ADD MEMBER
            </button>
        </div>

        <form action="" method="POST" id="orderForm">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>FOTO</th>
                            <th>NAMA & PERAN</th>
                            <th style="text-align: center;">URUTAN</th>
                            <th style="text-align: center;">STATUS</th>
                            <th style="text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM human_archive ORDER BY display_order ASC, id DESC");
                        if (mysqli_num_rows($q) > 0):
                            while ($row = mysqli_fetch_assoc($q)):
                                $status_class = ($row['status'] == 'active') ? 'status-active' : 'status-hidden';
                        ?>
                            <tr>
                                <td style="text-align: center;">
                                    <img src="../assets/images/community/<?= $row['image'] ?>" class="thumb-img" onerror="this.src='../assets/images/menu/default.jpg'">
                                </td>
                                <td>
                                    <strong style="color: var(--navy); font-size: 1.1rem;"><?= htmlspecialchars($row['name']) ?></strong><br>
                                    <span style="color: var(--red); font-size: 0.8rem; font-weight: bold;">[ <?= strtoupper(htmlspecialchars($row['role'])) ?> ]</span>
                                    <?php if (!empty($row['quote'])): ?>
                                        <br><small style="opacity: 0.8; margin-top: 5px; display: inline-block;">"<?= htmlspecialchars(substr($row['quote'],0,50)) ?>"</small>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <input type="number" name="order[<?= $row['id'] ?>]" value="<?= $row['display_order'] ?>" class="order-input">
                                </td>
                                <td style="text-align: center;">
                                    <span class="status-badge <?= $status_class ?>"><?= strtoupper($row['status']) ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-primary btn-sm">EDIT</a>
                                        <a href="?toggle=<?= $row['id'] ?>&current=<?= $row['status'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('APAKAH ANDA YAKIN MENGHAPUS ANGGOTA INI?')">DEL</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:40px; font-weight:bold; color:var(--red);">[ DATA ANGGOTA KOSONG ]</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="text-align: left;">
                <button type="submit" name="update_order" class="btn btn-secondary" style="width: auto;">
                    <i class="fas fa-save"></i> SIMPAN URUTAN TAMPIL
                </button>
            </div>
        </form>
    </section>
</main>

<div id="modalCommunity" class="modal">
    <div class="modal-content">
        <div class="tape" style="top: -16px; width: 100px; height: 25px;"></div>
        
        <div class="modal-header-area">
            <div class="spec-header" style="margin-bottom:10px; border-bottom: 2px dashed var(--navy);">
                <span id="modalTitle"><?= $edit_mode ? 'UPDATE ARSIP ANGGOTA' : 'TAMBAH ANGGOTA BARU' ?></span>
            </div>
        </div>
        
        <div class="modal-body-scroll">
            <form id="mainForm" action="" method="POST" enctype="multipart/form-data">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id_human" value="<?= $edit_id ?>">
                <?php endif; ?>
                
                <input type="hidden" name="foto_lama" id="fotoLama" value="<?= htmlspecialchars($edit_image) ?>">
                <input type="hidden" name="foto_cropped" id="fotoCropped" value="">

                <div class="form-group">
                    <label class="form-label">NAMA LENGKAP</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($edit_name) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">ROLE / JABATAN (MISAL: BARISTA)</label>
                    <input type="text" name="role" class="form-input" value="<?= htmlspecialchars($edit_role) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">QUOTE (OPSIONAL)</label>
                    <textarea name="quote" class="form-input" rows="3"><?= htmlspecialchars($edit_quote) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">URUTAN TAMPIL (ANGKA)</label>
                    <input type="number" name="display_order" class="form-input" value="<?= $edit_order ?: 1 ?>" required>
                </div>
                
                <div class="upload-box-safe">
                    <label class="form-label" style="margin-bottom:8px;">UPLOAD FOTO (RASIO 1:1)</label>
                    
                    <?php if ($edit_mode && $edit_image && $edit_image != 'default.jpg'): ?>
                        <div style="margin-bottom:15px;">
                            <img src="../assets/images/community/<?= $edit_image ?>" style="width:80px; height:80px; object-fit:cover; border:2px solid var(--navy); box-shadow: 3px 3px 0 var(--red);">
                            <p style="font-size:0.75rem; margin-top:5px; font-weight:bold;">[ FOTO SAAT INI ]</p>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="imageInput" name="image" accept="image/*" style="font-family:'Courier Prime'; font-size:0.8rem; width:100%;">
                    <p style="font-size:0.75rem; margin-top:10px; color:var(--red);">* Pilih gambar, form CROP akan otomatis muncul.</p>
                    
                    <div id="previewArea" style="display:none; margin-top:15px; border-top: 1px dashed var(--navy); padding-top: 15px;">
                        <p style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px;">[ PREVIEW CROP ]</p>
                        <img id="previewImage" style="max-width:120px; border:2px solid var(--navy); box-shadow: 4px 4px 0 rgba(0,0,0,0.15);">
                        <p style="color:green; font-weight:bold; font-size:0.8rem; margin-top:5px;">✓ GAMBAR SIAP DISIMPAN</p>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px; padding-bottom: 5px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">BATAL</button>
                    <button type="submit" name="<?= $edit_mode ? 'update_human' : 'add_human' ?>" id="submitBtn" class="btn btn-primary">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="cropModal" class="modal" style="z-index:2100;">
    <div class="modal-content" style="max-width:460px; padding: 20px;">
        <div class="spec-header" style="margin-bottom: 15px;">CROP GAMBAR (1:1)</div>
        <div style="border: 2px solid var(--navy); background: #000; overflow:hidden;">
            <img id="cropImage" src="" style="max-width:100%; display:block;">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
            <button class="btn btn-secondary" id="cancelCropBtn">BATAL</button>
            <button class="btn btn-primary" id="cropBtn" style="background:var(--red);">CROP & SET</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // Toggle Sidebar Mobile
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(btn) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }
    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Modal Control
    const modal = document.getElementById('modalCommunity');
    const showModalBtn = document.getElementById('showModalBtn');

    function openModal() { modal.style.display = 'flex'; }
    function closeModal() { 
        modal.style.display = 'none'; 
        if(window.location.href.includes('edit=')){
            window.location.href = 'community_crud.php';
        }
    }
    
    if(showModalBtn) showModalBtn.onclick = openModal;
    <?php if ($edit_mode) echo "openModal();"; ?>

    // CROPPER LOGIC (Metode Base64 yang lebih handal, tanpa AJAX)
    let cropper;
    const imageInput = document.getElementById('imageInput');
    const cropModal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const cropBtn = document.getElementById('cropBtn');
    const cancelCropBtn = document.getElementById('cancelCropBtn');
    const previewArea = document.getElementById('previewArea');
    const previewImage = document.getElementById('previewImage');
    const fotoCropped = document.getElementById('fotoCropped');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) { 
            alert('Hanya file gambar (JPG, PNG, WEBP) yang diperbolehkan!'); 
            imageInput.value = ''; 
            return; 
        }
        if (file.size > 5 * 1024 * 1024) { 
            alert('Ukuran gambar maksimal 5MB!'); 
            imageInput.value = ''; 
            return; 
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            cropImage.src = event.target.result;
            cropModal.style.display = 'flex';
            if (cropper) cropper.destroy();
            // Inisialisasi cropper dengan rasio 1:1
            cropper = new Cropper(cropImage, { aspectRatio: 1, viewMode: 1 });
        };
        reader.readAsDataURL(file);
    });

    cropBtn.addEventListener('click', () => {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 500 });
            const croppedBase64 = canvas.toDataURL('image/png', 0.9);
            
            // Masukkan ke input hidden untuk dikirim via form submit PHP
            fotoCropped.value = croppedBase64;
            
            // Tampilkan preview
            previewImage.src = croppedBase64;
            previewArea.style.display = 'block';
            
            cropModal.style.display = 'none';
            cropper.destroy();
        }
    });

    cancelCropBtn.addEventListener('click', () => {
        cropModal.style.display = 'none';
        if (cropper) cropper.destroy();
        imageInput.value = '';
    });

    // Validasi submit form jika ada gambar baru tapi belum dicrop
    document.getElementById('mainForm').addEventListener('submit', function(e) {
        const file = imageInput.files[0];
        if (file && !fotoCropped.value) {
            e.preventDefault();
            alert('Harap menyelesaikan proses CROP foto sebelum menyimpan!');
        }
    });
</script>
</body>
</html>