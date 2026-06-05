<?php
require_once '../config/session.php';

$query = mysqli_query($conn, "SELECT * FROM tb_genre ORDER BY id_genre DESC");

$flash = '';
$flash_type = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
    unset($_SESSION['flash'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Genre - ArsipMusik</title>
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
                    <h1>Kelola Genre</h1>
                    <p>Daftar kategori musik yang tersedia dalam arsip</p>
                </div>

                <?php if ($flash != ''): ?>
                    <div class="flash-msg <?php echo ($flash_type == 'success') ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo $flash ?>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 24px;">
                    <a href="genre_tambah.php" class="btn-action-primary" style="height: 42px;">+ Tambah Genre Baru</a>
                </div>

                <table class="table1">
                    <thead>
                        <tr>
                            <th width="80px">No</th>
                            <th>Nama Genre</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($query) > 0): 
                            $no = 1;
                            while ($row = mysqli_fetch_array($query)): 
                        ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $no++; ?></td>
                                    <td style="font-weight: 600;"><?php echo $row['nama_genre'] ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <a href="genre_edit.php?id=<?php echo $row['id_genre'] ?>" class="btn-edit">Edit</a>
                                            <a href="hapus.php?id_genre=<?php echo $row['id_genre'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin hapus genre ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 60px; color: var(--text-muted);">Tidak ada genre ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
