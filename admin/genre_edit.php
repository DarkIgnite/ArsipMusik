<?php
require_once '../config/session.php';

$flash = '';
$flash_type = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
    unset($_SESSION['flash'], $_SESSION['flash_type']);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: genre_data.php");
    exit();
}

$id_genre = (int)$_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM tb_genre WHERE id_genre = '$id_genre'");

if (mysqli_num_rows($query) == 0) {
    $_SESSION['flash'] = 'Genre tidak ditemukan!';
    $_SESSION['flash_type'] = 'error';
    header("Location: genre_data.php");
    exit();
}

$genre = mysqli_fetch_array($query);

// proses submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_genre = isset($_POST['nama_genre']) ? mysqli_real_escape_string($conn, trim($_POST['nama_genre'])) : '';

    if ($nama_genre == '') {
        $_SESSION['flash'] = 'Nama genre wajib diisi!';
        $_SESSION['flash_type'] = 'error';
        header('Location: genre_edit.php?id=' . $id_genre);
        exit();
    }

    $update = mysqli_query($conn, "UPDATE tb_genre SET nama_genre = '$nama_genre' WHERE id_genre = '$id_genre'");

    if ($update) {
        $_SESSION['flash'] = 'Genre berhasil diperbarui!';
        $_SESSION['flash_type'] = 'success';
        header('Location: genre_data.php');
        exit();
    } else {
        $_SESSION['flash'] = 'Gagal memperbarui genre.';
        $_SESSION['flash_type'] = 'error';
        header('Location: genre_edit.php?id=' . $id_genre);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Genre - ArsipMusik</title>
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
            <div class="container-admin" style="max-width: 600px;">
                <div class="admin-header-tesla">
                    <h1>Edit Genre</h1>
                    <p>Perbarui informasi kategori musik</p>
                </div>

                <?php if ($flash != ''): ?>
                    <div class="flash-msg <?php echo ($flash_type == 'success') ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo $flash ?>
                    </div>
                <?php endif; ?>

                <div class="form-card" style="margin: 0; padding: 0; border: none; box-shadow: none; background: transparent;">
                    <form action="genre_edit.php?id=<?php echo $id_genre ?>" method="POST" style="margin: 0; padding: 25px; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div class="form-group">
                            <label style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px; display: block;">Nama Genre</label>
                            <input type="text" name="nama_genre" value="<?php echo $genre['nama_genre'] ?>" required style="height: 46px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 1rem; width: 100%;">
                        </div>

                        <div class="form-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 12px;">
                            <a href="genre_data.php" class="btn-secondary" style="height: 44px; border-radius: 8px; padding: 0 24px; display: flex; align-items: center; font-weight: 600;">Batal</a>
                            <button type="submit" style="width: auto; padding: 0 30px; height: 44px; border-radius: 8px; background: var(--primary); color: #fff; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
