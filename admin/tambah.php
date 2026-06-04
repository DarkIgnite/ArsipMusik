<?php
// Include session checking
require_once '../config/session.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and escape inputs
    $judul       = isset($_POST['judul']) ? mysqli_real_escape_string($conn, trim($_POST['judul'])) : '';
    $artis       = isset($_POST['artis']) ? mysqli_real_escape_string($conn, trim($_POST['artis'])) : '';
    $album       = isset($_POST['album']) ? mysqli_real_escape_string($conn, trim($_POST['album'])) : '';
    $id_genre    = isset($_POST['id_genre']) ? (int)$_POST['id_genre'] : 0;
    $tahun_rilis = isset($_POST['tahun_rilis']) ? (int)$_POST['tahun_rilis'] : 0;
    $durasi      = isset($_POST['durasi']) ? mysqli_real_escape_string($conn, trim($_POST['durasi'])) : '';

    // Validation check
    if ($judul == '' || $artis == '' || $album == '' || $id_genre == 0 || $tahun_rilis == 0 || $durasi == '') {
        echo "<script>alert('Semua data input wajib diisi!'); window.history.back();</script>";
        exit();
    }

    // Cover Art Image Upload Handler
    $new_file_name = 'default.jpg'; // Fallback file name

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['gambar']['name'];
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_size = $_FILES['gambar']['size'];
        
        $file_parts = explode('.', $file_name);
        $file_ext  = strtolower(end($file_parts));
        
        // Validation check for file format
        $allowed_exts = array('jpg', 'jpeg', 'png');
        if (!in_array($file_ext, $allowed_exts)) {
            echo "<script>alert('Format gambar tidak valid! Hanya diperbolehkan format JPG, JPEG, atau PNG.'); window.history.back();</script>";
            exit();
        }

        // Validation check for file size (limit: 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            echo "<script>alert('Ukuran file gambar tidak boleh melebihi 2MB!'); window.history.back();</script>";
            exit();
        }

        // Unique file name generation using timestamp
        $new_file_name = 'cover_' . time() . '.' . $file_ext;
        
        // Define destination upload folder
        $upload_dir = '../uploads/';
        
        // Attempt to move file to uploads/
        if (!move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            echo "<script>alert('Gagal mengupload cover art!'); window.history.back();</script>";
            exit();
        }
    }

    // insert data ke database
    $insert = mysqli_query($conn, "INSERT INTO tb_lagu (judul, artis, album, id_genre, tahun_rilis, durasi, gambar) VALUES ('$judul', '$artis', '$album', '$id_genre', '$tahun_rilis', '$durasi', '$new_file_name')") or die(mysqli_error($conn));

    if ($insert) {
        echo "<script>alert('Lagu baru berhasil disimpan ke arsip!'); window.location.href = 'index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menyimpan data ke database. Silakan coba lagi.'); window.history.back();</script>";
        exit();
    }
}

// Fetch all genres for form selector dropdown
$genres_result = mysqli_query($conn, "SELECT * FROM tb_genre ORDER BY nama_genre ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Lagu Baru - ArsipMusik</title>
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
                    <h1 class="admin-title">Tambah Lagu</h1>
                    <p class="admin-subtitle">Unggah lagu baru beserta album art</p>
                </div>
                <a href="index.php" class="btn-secondary" style="height: 40px;" id="btn-back-dashboard">
                    &larr; Kembali
                </a>
            </header>

            <!-- Card Form Wrapper -->
            <div class="form-card">
                <form action="tambah.php" method="POST" enctype="multipart/form-data" id="add-track-form">
                    
                    <div class="form-grid">
                        
                        <!-- Title Field -->
                        <div class="form-group form-grid-full">
                            <label for="judul" class="form-label">Judul Lagu</label>
                            <input type="text" name="judul" id="judul" class="form-input" 
                                   placeholder="Contoh: Bohemian Rhapsody" required>
                        </div>

                        <!-- Artist Field -->
                        <div class="form-group">
                            <label for="artis" class="form-label">Artis / Penyanyi</label>
                            <input type="text" name="artis" id="artis" class="form-input" 
                                   placeholder="Contoh: Queen" required>
                        </div>

                        <!-- Album Field -->
                        <div class="form-group">
                            <label for="album" class="form-label">Album</label>
                            <input type="text" name="album" id="album" class="form-input" 
                                   placeholder="Contoh: A Night at the Opera" required>
                        </div>

                        <!-- Genre Field (populated dynamically) -->
                        <div class="form-group">
                            <label for="id_genre" class="form-label">Genre</label>
                            <select name="id_genre" id="id_genre" class="form-input" required>
                                <option value="" disabled selected>Pilih Genre</option>
                                <?php while ($g_row = mysqli_fetch_array($genres_result)): ?>
                                    <option value="<?php echo $g_row['id_genre'] ?>">
                                        <?php echo $g_row['nama_genre'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Release Year Field -->
                        <div class="form-group">
                            <label for="tahun_rilis" class="form-label">Tahun Rilis</label>
                            <input type="number" name="tahun_rilis" id="tahun_rilis" class="form-input" 
                                   min="1900" max="2100" placeholder="Contoh: 1975" required>
                        </div>

                        <!-- Duration Field -->
                        <div class="form-group">
                            <label for="durasi" class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="durasi" id="durasi" class="form-input" 
                                   placeholder="Contoh: 05:55" required pattern="^([0-9]{1,2}):([0-5][0-9])$" 
                                   title="Format durasi harus Menit:Detik (contoh: 03:45 atau 12:05)">
                        </div>

                        <!-- Cover Art Upload Field -->
                        <div class="form-group form-grid-full">
                            <label for="gambar" class="form-label">Cover Art</label>
                            <div class="file-upload-container">
                                <!-- Placeholder Image -->
                                <img src="../uploads/default.jpg" alt="Preview Cover" class="file-upload-preview" id="preview-image">
                                <div class="file-upload-input-wrapper">
                                    <input type="file" name="gambar" id="gambar" class="form-input" style="padding-top: 8px;" 
                                           accept="image/png, image/jpeg, image/jpg" onchange="previewFile()">
                                    <div class="file-upload-info">Max: 2MB. Diperbolehkan: JPG, JPEG, PNG. Kosongkan untuk menggunakan cover default.</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Form submission controls -->
                    <div class="form-actions">
                        <a href="index.php" class="btn-secondary" id="btn-add-cancel">Batal</a>
                        <button type="submit" class="btn-action-primary" style="height: 44px;" id="btn-add-submit">Simpan</button>
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
