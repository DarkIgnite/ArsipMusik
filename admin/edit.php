<?php
// Secure authorization layer
require_once '../config/session.php';

// Include database connection
require_once '../config/koneksi.php';

// Check if dynamic track ID is present
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_lagu = (int)$_GET['id'];

// Retrieve existing track details
$stmt = $conn->prepare("SELECT * FROM tb_lagu WHERE id_lagu = ?");
$stmt->bind_param("i", $id_lagu);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Arsip lagu tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

$lagu = $result->fetch_assoc();

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $judul       = isset($_POST['judul']) ? trim($_POST['judul']) : '';
    $artis       = isset($_POST['artis']) ? trim($_POST['artis']) : '';
    $album       = isset($_POST['album']) ? trim($_POST['album']) : '';
    $id_genre    = isset($_POST['id_genre']) ? (int)$_POST['id_genre'] : 0;
    $tahun_rilis = isset($_POST['tahun_rilis']) ? (int)$_POST['tahun_rilis'] : 0;
    $durasi      = isset($_POST['durasi']) ? trim($_POST['durasi']) : '';

    // 1. Strict Server-Side Validation: Check for empty values
    if ($judul === '' || $artis === '' || $album === '' || $id_genre === 0 || $tahun_rilis === 0 || $durasi === '') {
        echo "<script>alert('Semua data input wajib diisi!'); window.history.back();</script>";
        exit();
    }

    $old_image = $lagu['gambar'];
    $new_file_name = $old_image; // Keep current file name as fallback

    // 2. Handle New Cover Upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['gambar']['name'];
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_size = $_FILES['gambar']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Format check
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_exts)) {
            echo "<script>alert('Format gambar tidak valid! Hanya diperbolehkan JPG, JPEG, atau PNG.'); window.history.back();</script>";
            exit();
        }

        // Size check (limit: 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            echo "<script>alert('Ukuran file gambar tidak boleh melebihi 2MB!'); window.history.back();</script>";
            exit();
        }

        // Unique renaming
        $new_file_name = 'cover_' . uniqid('', true) . '.' . $file_ext;
        $upload_dir = '../uploads/';
        
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            // Delete old file if it exists and is not the default image
            if ($old_image !== 'default.jpg') {
                $old_image_path = $upload_dir . $old_image;
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } else {
            echo "<script>alert('Gagal mengupload gambar baru!'); window.history.back();</script>";
            exit();
        }
    }

    // 3. Database Entry Update via Secure Prepared Statements
    $update_stmt = $conn->prepare("UPDATE tb_lagu SET judul = ?, artis = ?, album = ?, id_genre = ?, tahun_rilis = ?, durasi = ?, gambar = ? WHERE id_lagu = ?");
    $update_stmt->bind_param("sssiissi", $judul, $artis, $album, $id_genre, $tahun_rilis, $durasi, $new_file_name, $id_lagu);

    if ($update_stmt->execute()) {
        echo "<script>alert('Arsip lagu berhasil diperbarui!'); window.location.href = 'index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data ke database.'); window.history.back();</script>";
        exit();
    }
}

// Fetch all genres for select menu option population
$genres_result = $conn->query("SELECT * FROM tb_genre ORDER BY nama_genre ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lagu - ArsipMusik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="admin-layout">
        
        <!-- Sidebar Navigation Component -->
        <?php include 'components/sidebar.php'; ?>

        <!-- Main Workspace -->
        <main class="admin-main">
            
            <header class="admin-header">
                <div class="admin-title-desc">
                    <h1 class="admin-title">Edit Lagu</h1>
                    <p class="admin-subtitle">Perbarui atribut arsip lagu dan cover art</p>
                </div>
                <a href="index.php" class="btn-secondary" style="height: 40px;" id="btn-edit-back-dashboard">
                    &larr; Kembali
                </a>
            </header>

            <!-- Card Form Wrapper -->
            <div class="form-card">
                <form action="edit.php?id=<?= $id_lagu ?>" method="POST" enctype="multipart/form-data" id="edit-track-form">
                    
                    <div class="form-grid">
                        
                        <!-- Title Field -->
                        <div class="form-group form-grid-full">
                            <label for="judul" class="form-label">Judul Lagu</label>
                            <input type="text" name="judul" id="judul" class="form-input" 
                                   value="<?= htmlspecialchars($lagu['judul']) ?>" required>
                        </div>

                        <!-- Artist Field -->
                        <div class="form-group">
                            <label for="artis" class="form-label">Artis / Penyanyi</label>
                            <input type="text" name="artis" id="artis" class="form-input" 
                                   value="<?= htmlspecialchars($lagu['artis']) ?>" required>
                        </div>

                        <!-- Album Field -->
                        <div class="form-group">
                            <label for="album" class="form-label">Album</label>
                            <input type="text" name="album" id="album" class="form-input" 
                                   value="<?= htmlspecialchars($lagu['album']) ?>" required>
                        </div>

                        <!-- Genre Field Selector -->
                        <div class="form-group">
                            <label for="id_genre" class="form-label">Genre</label>
                            <select name="id_genre" id="id_genre" class="form-input" required>
                                <?php while ($g_row = $genres_result->fetch_assoc()): ?>
                                    <option value="<?= $g_row['id_genre'] ?>" <?= ($lagu['id_genre'] == $g_row['id_genre']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g_row['nama_genre']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Release Year Field -->
                        <div class="form-group">
                            <label for="tahun_rilis" class="form-label">Tahun Rilis</label>
                            <input type="number" name="tahun_rilis" id="tahun_rilis" class="form-input" 
                                   min="1900" max="2100" value="<?= htmlspecialchars($lagu['tahun_rilis']) ?>" required>
                        </div>

                        <!-- Duration Field -->
                        <div class="form-group">
                            <label for="durasi" class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="durasi" id="durasi" class="form-input" 
                                   value="<?= htmlspecialchars($lagu['durasi']) ?>" required pattern="^([0-9]{1,2}):([0-5][0-9])$" 
                                   title="Format durasi harus Menit:Detik (contoh: 03:45 atau 12:05)">
                        </div>

                        <!-- Cover Art Field with Live Preview -->
                        <div class="form-group form-grid-full">
                            <label for="gambar" class="form-label">Cover Art</label>
                            <div class="file-upload-container">
                                <img src="../uploads/<?= htmlspecialchars($lagu['gambar']) ?>" 
                                     alt="Preview Cover" class="file-upload-preview" id="preview-image">
                                <div class="file-upload-input-wrapper">
                                    <input type="file" name="gambar" id="gambar" class="form-input" style="padding-top: 8px;" 
                                           accept="image/png, image/jpeg, image/jpg" onchange="previewFile()">
                                    <div class="file-upload-info">Max: 2MB. Diperbolehkan: JPG, JPEG, PNG. Kosongkan jika tidak ingin mengubah cover art.</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Submission Controls -->
                    <div class="form-actions">
                        <a href="index.php" class="btn-secondary" id="btn-edit-cancel">Batal</a>
                        <button type="submit" class="btn-action-primary" style="height: 44px;" id="btn-edit-submit">Simpan Perubahan</button>
                    </div>

                </form>
            </div>

        </main>
    </div>

    <!-- Live Preview Script -->
    <script>
        function previewFile() {
            const preview = document.getElementById('preview-image');
            const file = document.getElementById('gambar').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>

</body>
</html>
