<?php
// sertakan pengecekan sesi
require_once '../config/session.php';

// pengaturan paginasi
$limit = 10;
if (isset($_GET['page'])) {
    $page = max(1, (int)$_GET['page']);
} else {
    $page = 1;
}

// variabel pencarian dan filter
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

$genre_filter = 0;
if (isset($_GET['genre'])) {
    $genre_filter = (int)$_GET['genre'];
}

// buat kondisi query
$where_clauses = array();
if ($search != '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(tb_lagu.judul LIKE '%$safe_search%' OR tb_lagu.artis LIKE '%$safe_search%' OR tb_lagu.album LIKE '%$safe_search%')";
}
if ($genre_filter > 0) {
    $where_clauses[] = "tb_lagu.id_genre = $genre_filter";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// ambil jumlah baris untuk perhitungan paginasi
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lagu $where_sql");
$count_data = mysqli_fetch_array($count_query);
$total_rows = $count_data['total'];
$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $limit;
if ($offset < 0) $offset = 0;

// ambil detail lagu dengan inner join
$query = mysqli_query($conn, "SELECT tb_lagu.*, tb_genre.nama_genre 
          FROM tb_lagu 
          INNER JOIN tb_genre ON tb_lagu.id_genre = tb_genre.id_genre 
          $where_sql 
          ORDER BY tb_lagu.id_lagu DESC 
          LIMIT $limit OFFSET $offset");

// ambil daftar genre untuk filter dropdown
$genres_result = mysqli_query($conn, "SELECT * FROM tb_genre ORDER BY nama_genre ASC");

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
    <title>Data Lagu - ArsipMusik</title>
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
                    <h1>Arsip Lagu</h1>
                    <p>Kelola koleksi lagu dalam database Anda</p>
                </div>

                <?php if ($flash != ''): ?>
                    <div class="flash-msg <?php echo ($flash_type == 'success') ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo $flash ?>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <a href="lagu_tambah.php" class="btn-action-primary" style="height: 42px;">+ Tambah Lagu Baru</a>
                    </div>
                    
                    <div class="admin-search-container" style="margin-bottom: 0; padding: 15px;">
                        <form action="lagu_data.php" method="GET" class="admin-search-form">
                            <input type="text" name="q" value="<?php echo $search ?>" placeholder="Cari judul, artis..." style="width: 250px;">
                            <select name="genre">
                                <option value="0">Semua Genre</option>
                                <?php 
                                mysqli_data_seek($genres_result, 0);
                                while ($g_row = mysqli_fetch_array($genres_result)): 
                                ?>
                                    <option value="<?php echo $g_row['id_genre'] ?>" <?php echo ($genre_filter == $g_row['id_genre']) ? 'selected' : '' ?>>
                                        <?php echo $g_row['nama_genre'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit">Cari</button>
                            <?php if ($search != '' || $genre_filter > 0): ?>
                                <a href="lagu_data.php" class="btn-secondary" style="height: 42px; border-radius: 8px;">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <table class="table1">
                    <thead>
                        <tr>
                            <th width="50px">No</th>
                            <th width="60px">Cover</th>
                            <th>Judul & Artis</th>
                            <th>Album</th>
                            <th>Genre</th>
                            <th>Tahun</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($query) > 0): 
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_array($query)): 
                        ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $no++; ?></td>
                                    <td>
                                        <img src="../uploads/<?php echo $row['gambar'] ?>" alt="Cover" class="table-img">
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);"><?php echo $row['judul'] ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo $row['artis'] ?></div>
                                    </td>
                                    <td><?php echo $row['album'] ?></td>
                                    <td><span class="table-badge"><?php echo $row['nama_genre'] ?></span></td>
                                    <td><?php echo $row['tahun_rilis'] ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <a href="lagu_edit.php?id=<?php echo $row['id_lagu'] ?>" class="btn-edit">Edit</a>
                                            <a href="hapus.php?id_lagu=<?php echo $row['id_lagu'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin hapus lagu ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-muted);">
                                    <div style="font-size: 2rem; margin-bottom: 10px;">🔍</div>
                                    Tidak ada lagu yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 32px; display: flex; justify-content: center; gap: 8px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1 ?>&q=<?php echo $search ?>&genre=<?php echo $genre_filter ?>" 
                               style="padding: 8px 16px; border: 1px solid var(--border-color); text-decoration: none; border-radius: 8px; color: var(--text-secondary); font-weight: 500;">&larr; Seb.</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i ?>&q=<?php echo $search ?>&genre=<?php echo $genre_filter ?>" 
                               style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid <?php echo ($page == $i) ? 'var(--primary)' : 'var(--border-color)'; ?>; text-decoration: none; border-radius: 8px; <?php echo ($page == $i) ? 'background: var(--primary); color: #fff;' : 'color: var(--text-primary);'; ?> font-weight: 600;">
                                <?php echo $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1 ?>&q=<?php echo $search ?>&genre=<?php echo $genre_filter ?>" 
                               style="padding: 8px 16px; border: 1px solid var(--border-color); text-decoration: none; border-radius: 8px; color: var(--text-secondary); font-weight: 500;">Sel. &rarr;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
