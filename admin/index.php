<?php
// Secure authorization layer
require_once '../config/session.php';

// Include database connection
require_once '../config/koneksi.php';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Search and Filter variables
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$genre_filter = isset($_GET['genre']) ? (int)$_GET['genre'] : 0;

// Build query conditions
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

// Fetch row count for pagination calculations
$count_query = "SELECT COUNT(*) as total FROM tb_lagu $where_sql";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $limit;
if ($offset < 0) $offset = 0;

// Fetch track details with INNER JOIN
$query = "SELECT tb_lagu.*, tb_genre.nama_genre 
          FROM tb_lagu 
          INNER JOIN tb_genre ON tb_lagu.id_genre = tb_genre.id_genre 
          $where_sql 
          ORDER BY tb_lagu.id_lagu DESC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Fetch genre listings for dropdown filter
$genres_query = "SELECT * FROM tb_genre ORDER BY nama_genre ASC";
$genres_result = $conn->query($genres_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ArsipMusik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="admin-layout">
        
        <!-- Sidebar Navigation Component -->
        <?php include 'components/sidebar.php'; ?>

        <!-- Main Workspace -->
        <main class="admin-main">
            
            <!-- Dashboard Header Section -->
            <header class="admin-header">
                <div class="admin-title-desc">
                    <h1 class="admin-title">Arsip Lagu</h1>
                    <p class="admin-subtitle">Kelola dan update arsip koleksi musik Anda</p>
                </div>
                <a href="tambah.php" class="btn-action-primary" id="btn-add-track">
                    <span>➕</span> Tambah Lagu Baru
                </a>
            </header>

            <!-- Search and Filtering Section -->
            <section class="search-filter-section" style="margin-top: 0;">
                <div class="search-filter-card">
                    <form action="index.php" method="GET" class="search-filter-form">
                        <!-- Text Search field -->
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Cari judul lagu, artis, atau album..." class="form-input" id="admin-search-input">
                        
                        <!-- Genre Select field -->
                        <select name="genre" class="form-input" id="admin-genre-filter">
                            <option value="0">Semua Genre</option>
                            <?php while ($g_row = $genres_result->fetch_assoc()): ?>
                                <option value="<?= $g_row['id_genre'] ?>" <?= ($genre_filter == $g_row['id_genre']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g_row['nama_genre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <button type="submit" class="btn-search" id="btn-admin-submit-search">Cari</button>
                        <?php if ($search !== '' || $genre_filter > 0): ?>
                            <a href="index.php" class="btn-reset" id="btn-admin-reset-search">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            </section>

            <!-- Display Track Table -->
            <?php if ($result->num_rows > 0): ?>
                <div class="table-card" id="admin-table-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Cover</th>
                                    <th>Lagu</th>
                                    <th>Album</th>
                                    <th>Genre</th>
                                    <th style="width: 100px;">Rilis</th>
                                    <th style="width: 100px;">Durasi</th>
                                    <th style="width: 160px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <!-- Album Cover column -->
                                        <td>
                                            <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" 
                                                 alt="Cover <?= htmlspecialchars($row['judul']) ?>" class="table-song-thumb">
                                        </td>
                                        <!-- Song Details column -->
                                        <td>
                                            <div class="table-song-info">
                                                <div>
                                                    <div class="table-song-title"><?= htmlspecialchars($row['judul']) ?></div>
                                                    <div class="table-song-artist"><?= htmlspecialchars($row['artis']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Album column -->
                                        <td><?= htmlspecialchars($row['album']) ?></td>
                                        <!-- Genre Badge column -->
                                        <td>
                                            <span class="table-badge"><?= htmlspecialchars($row['nama_genre']) ?></span>
                                        </td>
                                        <!-- Year column -->
                                        <td><?= htmlspecialchars($row['tahun_rilis']) ?></td>
                                        <!-- Duration column -->
                                        <td><?= htmlspecialchars($row['durasi']) ?></td>
                                        <!-- Action buttons column -->
                                        <td style="text-align: center;">
                                            <div class="action-buttons-cell">
                                                <a href="edit.php?id=<?= $row['id_lagu'] ?>" class="btn-icon btn-edit">Edit</a>
                                                <a href="hapus.php?id=<?= $row['id_lagu'] ?>" 
                                                   class="btn-icon btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus lagu ini?')">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Admin Pagination Links -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container" id="admin-pagination">
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
                <div class="empty-state" id="admin-empty-state">
                    <div class="empty-state-icon">📁</div>
                    <h3>Arsip lagu kosong</h3>
                    <p>Mulai dengan menambahkan lagu baru menggunakan tombol "Tambah Lagu Baru".</p>
                </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>
