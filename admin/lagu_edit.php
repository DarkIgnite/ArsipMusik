<?php
// Include session checking
require_once '../config/session.php';

$flash = '';
$flash_type = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
    unset($_SESSION['flash'], $_SESSION['flash_type']);
}

// Check if dynamic track ID is present
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lagu_data.php");
    exit();
}

$id_lagu = (int)$_GET['id'];

// Retrieve existing track details
$query = mysqli_query($conn, "SELECT * FROM tb_lagu WHERE id_lagu = '$id_lagu'");
if (mysqli_num_rows($query) == 0) {
    $_SESSION['flash'] = 'Arsip lagu tidak ditemukan!';
    $_SESSION['flash_type'] = 'error';
    header("Location: lagu_data.php");
    exit();
}

$lagu = mysqli_fetch_array($query);

// Handle form submission (POST)
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
        $_SESSION['flash'] = 'Semua data input wajib diisi!';
        $_SESSION['flash_type'] = 'error';
        header('Location: lagu_edit.php?id=' . $id_lagu);
        exit();
    }

    $old_image = $lagu['gambar'];
    $new_file_name = $old_image; // Keep current file name as fallback

    // Handle New Cover Upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['gambar']['name'];
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_size = $_FILES['gambar']['size'];
        
        $file_parts = explode('.', $file_name);
        $file_ext  = strtolower(end($file_parts));
        
        // Format check
        $allowed_exts = array('jpg', 'jpeg', 'png');
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['flash'] = 'Format gambar tidak valid! Hanya diperbolehkan JPG, JPEG, atau PNG.';
            $_SESSION['flash_type'] = 'error';
            header('Location: lagu_edit.php?id=' . $id_lagu);
            exit();
        }

        // Size check (limit: 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $_SESSION['flash'] = 'Ukuran file gambar tidak boleh melebihi 2MB!';
            $_SESSION['flash_type'] = 'error';
            header('Location: lagu_edit.php?id=' . $id_lagu);
            exit();
        }

        // Unique renaming using timestamp
        $new_file_name = 'cover_' . time() . '.' . $file_ext;
        $upload_dir = '../uploads/';
        
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            // Delete old file if it exists and is not the default image
            if ($old_image != 'default.jpg') {
                $old_image_path = $upload_dir . $old_image;
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } else {
            $_SESSION['flash'] = 'Gagal mengupload gambar baru!';
            $_SESSION['flash_type'] = 'error';
            header('Location: lagu_edit.php?id=' . $id_lagu);
            exit();
        }
    }

    // Database Entry Update via procedural query
    $update = mysqli_query($conn, "UPDATE tb_lagu SET judul = '$judul', artis = '$artis', album = '$album', id_genre = '$id_genre', tahun_rilis = '$tahun_rilis', durasi = '$durasi', gambar = '$new_file_name' WHERE id_lagu = '$id_lagu'") or die(mysqli_error($conn));

    if ($update) {
        $_SESSION['flash'] = 'Arsip lagu berhasil diperbarui!';
        $_SESSION['flash_type'] = 'success';
        header('Location: lagu_data.php');
        exit();
    } else {
        $_SESSION['flash'] = 'Gagal memperbarui data ke database.';
        $_SESSION['flash_type'] = 'error';
        header('Location: lagu_edit.php?id=' . $id_lagu);
        exit();
    }
}

// Fetch all genres for select menu option population
$genres_result = mysqli_query($conn, "SELECT * FROM tb_genre ORDER BY nama_genre ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lagu - ArsipMusik</title>
    <link rel="icon" href="../assets/logo.png">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="wrapper">
        <div class="header"></div>

        <div class="sidebar">
            <div class="sidebar-title">
                <img src="../assets/logo.png" alt="Logo" class="sidebar-logo">
                <b>ArsipMusik</b>
            </div>
            <ul>
                <?php include 'components/sidebar.php' ?>
            </ul>
        </div>

        <div class="section">
            <div class="container-admin">
                <div class="admin-header-tesla">
                    <h1>Edit Lagu</h1>
                    <p>Perbarui informasi lagu dan album art dalam koleksi Anda</p>
                </div>

                <?php if ($flash != ''): ?>
                    <div class="flash-msg <?php echo ($flash_type == 'success') ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo $flash ?>
                    </div>
                <?php endif; ?>

                <div class="form-card" style="max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); padding: 32px;">
                    <form action="lagu_edit.php?id=<?php echo $id_lagu ?>" method="POST" enctype="multipart/form-data">
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Judul Lagu</label>
                                <input type="text" name="judul" value="<?php echo $lagu['judul'] ?>" required style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem;">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Artis / Penyanyi</label>
                                <input type="text" name="artis" value="<?php echo $lagu['artis'] ?>" required style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem;">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Album</label>
                                <input type="text" name="album" value="<?php echo $lagu['album'] ?>" required style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem;">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Genre</label>
                                <select name="id_genre" required style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem; background: #fff;">
                                    <?php while ($g_row = mysqli_fetch_array($genres_result)): ?>
                                        <option value="<?php echo $g_row['id_genre'] ?>" <?php echo ($lagu['id_genre'] == $g_row['id_genre']) ? 'selected' : '' ?>>
                                            <?php echo $g_row['nama_genre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Tahun Rilis</label>
                                <input type="number" name="tahun_rilis" min="1900" max="2100" value="<?php echo $lagu['tahun_rilis'] ?>" required style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem;">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Durasi (MM:SS)</label>
                                <input type="text" name="durasi" value="<?php echo $lagu['durasi'] ?>" required pattern="^([0-9]{1,2}):([0-5][0-9])$" style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem;">
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: block;">Cover Art</label>
                                <div class="file-upload-container" style="display: flex; align-items: center; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                    <img src="../uploads/<?php echo $lagu['gambar'] ?>" alt="Preview" class="file-upload-preview" id="preview-image" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color); background: #fff;">
                                    <div class="file-upload-input-wrapper" style="flex: 1;">
                                        <input type="file" name="gambar" accept="image/*" onchange="previewFile()" style="margin-bottom: 8px; font-size: 0.9rem;">
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">Max: 2MB. Kosongkan jika tidak ingin mengubah gambar.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 16px;">
                            <a href="lagu_data.php" class="btn-secondary" style="height: 46px; border-radius: 8px; padding: 0 28px; display: flex; align-items: center; font-weight: 600;">Batal</a>
                            <button type="submit" style="width: auto; padding: 0 40px; height: 46px; border-radius: 8px; background: var(--primary); color: #fff; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewFile() {
            const preview = document.getElementById('preview-image');
            const file = document.querySelector('input[type=file]').files[0];
            const reader = new FileReader();
            reader.onloadend = function () { preview.src = reader.result; }
            if (file) { reader.readAsDataURL(file); }
        }
    </script>

</body>
</html>
