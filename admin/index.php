<?php
// sertakan pengecekan sesi
require_once '../config/session.php';

// ambil beberapa statistik untuk dashboard
$count_lagu_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lagu");
$count_lagu = mysqli_fetch_array($count_lagu_query)['total'];

$count_genre_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_genre");
$count_genre = mysqli_fetch_array($count_genre_query)['total'];

$latest_lagu_query = mysqli_query($conn, "SELECT tb_lagu.*, tb_genre.nama_genre FROM tb_lagu JOIN tb_genre ON tb_lagu.id_genre = tb_genre.id_genre ORDER BY id_lagu DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ArsipMusik</title>
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
                    <h1>Dashboard</h1>
                    <p>Ringkasan aktivitas dan statistik koleksi musik Anda</p>
                </div>

                <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px;">
                    <!-- Stat Card 1 -->
                    <div class="stat-card" style="background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                        <div style="background: rgba(37, 99, 235, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #2563eb; font-weight: bold;">L</div>
                        <div>
                            <h3 style="margin-bottom: 4px; color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Total Lagu</h3>
                            <p style="font-size: 1.75rem; font-weight: 700; color: var(--primary);"><?php echo $count_lagu; ?></p>
                        </div>
                    </div>
                    <!-- Stat Card 2 -->
                    <div class="stat-card" style="background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                        <div style="background: rgba(16, 185, 129, 0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #10b981; font-weight: bold;">G</div>
                        <div>
                            <h3 style="margin-bottom: 4px; color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;">Total Genre</h3>
                            <p style="font-size: 1.75rem; font-weight: 700; color: var(--primary);"><?php echo $count_genre; ?></p>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 1.25rem; font-weight: 700;">Penambahan Terbaru</h2>
                    <a href="lagu_tambah.php" class="btn-action-primary" style="height: 38px; font-size: 0.85rem;">+ Tambah Lagu</a>
                </div>

                <table class="table1">
                    <thead>
                        <tr>
                            <th width="60px">Cover</th>
                            <th>Judul</th>
                            <th>Artis</th>
                            <th>Genre</th>
                            <th width="100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_array($latest_lagu_query)): ?>
                            <tr>
                                <td><img src="../uploads/<?php echo $row['gambar']; ?>" alt="Cover" class="table-img"></td>
                                <td style="font-weight: 600;"><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['artis']; ?></td>
                                <td><span class="table-badge"><?php echo $row['nama_genre']; ?></span></td>
                                <td>
                                    <a href="lagu_edit.php?id=<?php echo $row['id_lagu']; ?>" class="btn-edit">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="lagu_data.php" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;">
                        Lihat Semua Lagu <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
