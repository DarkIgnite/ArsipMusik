<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
<li><a href="lagu_data.php" class="<?php echo ($current_page == 'lagu_data.php' || $current_page == 'lagu_tambah.php' || $current_page == 'lagu_edit.php') ? 'active' : ''; ?>">Data Lagu</a></li>
<li><a href="genre_data.php" class="<?php echo ($current_page == 'genre_data.php' || $current_page == 'genre_tambah.php' || $current_page == 'genre_edit.php') ? 'active' : ''; ?>">Data Genre</a></li>
<li><a href="../index.php" target="_blank">Lihat Website</a></li>
<li><a href="../logout.php" style="color: var(--danger);" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar</a></li>
