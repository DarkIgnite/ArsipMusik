<?php
session_start();
include 'config/koneksi.php';

$limit = 10;
if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}

$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

$genre_filter = 0;
if (isset($_GET['genre'])) {
    $genre_filter = (int)$_GET['genre'];
}

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

// hitung total data untuk pagination
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lagu $where_sql");
$count_row = mysqli_fetch_array($count_query);
$total_rows = $count_row['total'];
$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $limit;
if ($offset < 0) $offset = 0;

// ambil data lagu
$query = mysqli_query($conn, "SELECT tb_lagu.*, tb_genre.nama_genre 
          FROM tb_lagu 
          INNER JOIN tb_genre ON tb_lagu.id_genre = tb_genre.id_genre 
          $where_sql 
          ORDER BY tb_lagu.id_lagu DESC 
          LIMIT $limit OFFSET $offset");

// ambil data genre
$genres_result = mysqli_query($conn, "SELECT * FROM tb_genre ORDER BY nama_genre ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArsipMusik - Koleksi Lagu Terbaik</title>
    <link rel="icon" href="assets/logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="public-header">
        <div class="container header-wrapper">
            <a href="index.php" class="logo">
                <img src="assets/logo.png" alt="Logo" class="logo-img">
                ArsipMusik
            </a>
            <nav>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] == true): ?>
                    <a href="admin/index.php" class="nav-link-btn">Panel Admin</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link-btn">Login Admin</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        
        <!-- Search & Filter Card -->
        <section class="search-filter-section">
            <div class="search-filter-card">
                <form action="index.php" method="GET" class="search-filter-form">
                    <!-- Text Search -->
                    <input type="text" name="q" value="<?php echo $search ?>" 
                           placeholder="Cari judul lagu, artis, atau album..." class="form-input" id="search-input">
                    
                    <!-- Genre Filter -->
                    <select name="genre" class="form-input" id="genre-filter">
                        <option value="0">Semua Genre</option>
                        <?php while ($g_row = mysqli_fetch_array($genres_result)): ?>
                            <option value="<?php echo $g_row['id_genre'] ?>" <?php echo ($genre_filter == $g_row['id_genre']) ? 'selected' : '' ?>>
                                <?php echo $g_row['nama_genre'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <!-- Submit & Reset Buttons -->
                    <button type="submit" class="btn-search" id="btn-submit-search">Cari</button>
                    <?php if ($search != '' || $genre_filter > 0): ?>
                        <a href="index.php" class="btn-reset" id="btn-reset-search">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <!-- Song List Display -->
        <section class="songs-section">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <div class="songs-grid" id="songs-grid">
                    <?php while ($row = mysqli_fetch_array($query)): ?>
                        <div class="song-card">
                            <div class="card-img-wrapper">
                                <img src="uploads/<?php echo $row['gambar'] ?>" 
                                     alt="<?php echo $row['judul'] ?>" class="card-img">
                            </div>
                            <div class="card-content">
                                <span class="genre-pill"><?php echo $row['nama_genre'] ?></span>
                                <h3 class="song-title" title="<?php echo $row['judul'] ?>">
                                    <?php echo $row['judul'] ?>
                                </h3>
                                <p class="song-artist" title="<?php echo $row['artis'] ?>">
                                    <?php echo $row['artis'] ?>
                                </p>
                                
                                <div class="song-meta-grid">
                                    <div>
                                        <span class="meta-label">Album</span>
                                        <span class="meta-value" title="<?php echo $row['album'] ?>"><?php echo $row['album'] ?></span>
                                    </div>
                                    <div>
                                        <span class="meta-label">Durasi</span>
                                        <span class="meta-value"><?php echo $row['durasi'] ?></span>
                                    </div>
                                    <div>
                                        <span class="meta-label">Rilis</span>
                                        <span class="meta-value"><?php echo $row['tahun_rilis'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination Links -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container" id="pagination">
                        <!-- Prev Page Button -->
                        <a href="?q=<?php echo urlencode($search) ?>&genre=<?php echo $genre_filter ?>&page=<?php echo $page - 1 ?>" 
                           class="pagination-btn <?php echo ($page <= 1) ? 'disabled' : '' ?>"
                           <?php echo ($page <= 1) ? 'onclick="return false;"' : '' ?>>&laquo; Prev</a>

                        <!-- Page Indicator -->
                        <span class="pagination-info">
                            Halaman <?php echo $page ?> dari <?php echo $total_pages ?>
                        </span>

                        <!-- Next Page Button -->
                        <a href="?q=<?php echo urlencode($search) ?>&genre=<?php echo $genre_filter ?>&page=<?php echo $page + 1 ?>" 
                           class="pagination-btn <?php echo ($page >= $total_pages) ? 'disabled' : '' ?>"
                           <?php echo ($page >= $total_pages) ? 'onclick="return false;"' : '' ?>>Next &raquo;</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state" id="empty-state">
                    <div class="empty-state-icon"></div>
                    <h3>Tidak ada lagu ditemukan</h3>
                    <p>Coba gunakan kata kunci lain atau pilih genre yang berbeda.</p>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>
