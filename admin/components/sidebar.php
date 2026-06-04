<?php
// Get current filename to toggle active CSS class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="logo">
            <span class="logo-icon"></span>
            ArsipMusik <span style="font-size: 0.65rem; background: var(--border-color); padding: 2px 6px; border-radius: 4px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Admin</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Dashboard Link -->
        <a href="index.php" class="sidebar-link <?= ($current_page === 'index.php') ? 'active' : '' ?>" id="sidebar-dashboard">
            <span>📊</span> Dashboard
        </a>
        
        <!-- Add Song Link -->
        <a href="tambah.php" class="sidebar-link <?= ($current_page === 'tambah.php') ? 'active' : '' ?>" id="sidebar-tambah">
            <span>➕</span> Tambah Lagu
        </a>
        
        <!-- Public Website Link -->
        <a href="../index.php" class="sidebar-link" target="_blank" id="sidebar-website">
            <span>🌐</span> Lihat Website
        </a>
    </nav>
    
    <!-- Sidebar Footer Logout Button -->
    <div class="sidebar-footer">
        <a href="../logout.php" class="sidebar-link" style="color: var(--danger);" 
           onclick="return confirm('Apakah Anda yakin ingin keluar dari panel admin?')" id="sidebar-logout">
            <span>🚪</span> Keluar
        </a>
    </div>
</aside>
