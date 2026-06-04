<?php
// Start session to detect if admin is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/koneksi.php';

// Pagination configurations
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Search and Filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$genre_filter = isset($_GET['genre']) ? (int)$_GET['genre'] : 0;

// Build search filter query conditions
$where_clauses = [];
if ($search !== '') {
    $safe_search = $conn->real_escape_string($search);
    $where_clauses[] = "(tb_lagu.judul LIKE '%$safe_search%' OR tb_lagu.artis LIKE '%$safe_search%' OR tb_lagu.album LIKE '%$safe_search%')";
}
if ($genre_filter > 0) {
    $where_clauses[] = "tb_lagu.id_genre = $genre_filter";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Get total tracks for pagination calculation
$count_query = "SELECT COUNT(*) as total FROM tb_lagu $where_sql";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $limit;
if ($offset < 0) $offset = 0;

// Fetch tracks with INNER JOIN to get genre name
$query = "SELECT tb_lagu.*, tb_genre.nama_genre 
          FROM tb_lagu 
          INNER JOIN tb_genre ON tb_lagu.id_genre = tb_genre.id_genre 
          $where_sql 
          ORDER BY tb_lagu.id_lagu DESC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Fetch all genres for filter dropdown
$genres_query = "SELECT * FROM tb_genre ORDER BY nama_genre ASC";
$genres_result = $conn->query($genres_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArsipMusik - Koleksi Lagu Terbaik</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="public-header">
        <div class="container header-wrapper">
            <a href="index.php" class="logo">
                <span class="logo-icon"></span>
                ArsipMusik
            </a>
            <nav>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
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
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Cari judul lagu, artis, atau album..." class="form-input" id="search-input">
                    
                    <!-- Genre Filter -->
                    <select name="genre" class="form-input" id="genre-filter">
                        <option value="0">Semua Genre</option>
                        <?php while ($g_row = $genres_result->fetch_assoc()): ?>
                            <option value="<?= $g_row['id_genre'] ?>" <?= ($genre_filter == $g_row['id_genre']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g_row['nama_genre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <!-- Submit & Reset Buttons -->
                    <button type="submit" class="btn-search" id="btn-submit-search">Cari</button>
                    <?php if ($search !== '' || $genre_filter > 0): ?>
                        <a href="index.php" class="btn-reset" id="btn-reset-search">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <!-- Song List Display -->
        <section class="songs-section">
            <?php if ($result->num_rows > 0): ?>
                <div class="songs-grid" id="songs-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="song-card">
                            <div class="card-img-wrapper">
                                <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" 
                                     alt="<?= htmlspecialchars($row['judul']) ?>" class="card-img">
                            </div>
                            <div class="card-content">
                                <span class="genre-pill"><?= htmlspecialchars($row['nama_genre']) ?></span>
                                <h3 class="song-title" title="<?= htmlspecialchars($row['judul']) ?>">
                                    <?= htmlspecialchars($row['judul']) ?>
                                </h3>
                                <p class="song-artist" title="<?= htmlspecialchars($row['artis']) ?>">
                                    <?= htmlspecialchars($row['artis']) ?>
                                </p>
                                
                                <div class="song-meta-grid">
                                    <div>
                                        <span class="meta-label">Album</span>
                                        <span class="meta-value" title="<?= htmlspecialchars($row['album']) ?>"><?= htmlspecialchars($row['album']) ?></span>
                                    </div>
                                    <div>
                                        <span class="meta-label">Durasi</span>
                                        <span class="meta-value"><?= htmlspecialchars($row['durasi']) ?></span>
                                    </div>
                                    <div>
                                        <span class="meta-label">Rilis</span>
                                        <span class="meta-value"><?= htmlspecialchars($row['tahun_rilis']) ?></span>
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
                        <a href="?q=<?= urlencode($search) ?>&genre=<?= $genre_filter ?>&page=<?= $page - 1 ?>" 
                           class="pagination-btn <?= ($page <= 1) ? 'disabled' : '' ?>"
                           <?= ($page <= 1) ? 'onclick="return false;"' : '' ?>>&laquo; Prev</a>

                        <!-- Page Indicator -->
                        <span class="pagination-info">
                            Halaman <?= $page ?> dari <?= $total_pages ?>
                        </span>

                        <!-- Next Page Button -->
                        <a href="?q=<?= urlencode($search) ?>&genre=<?= $genre_filter ?>&page=<?= $page + 1 ?>" 
                           class="pagination-btn <?= ($page >= $total_pages) ? 'disabled' : '' ?>"
                           <?= ($page >= $total_pages) ? 'onclick="return false;"' : '' ?>>Next &raquo;</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state" id="empty-state">
                    <div class="empty-state-icon">🎵</div>
                    <h3>Tidak ada lagu ditemukan</h3>
                    <p>Coba gunakan kata kunci lain atau pilih genre yang berbeda.</p>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>
