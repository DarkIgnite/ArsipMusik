<?php
// Secure authorization layer
require_once '../config/session.php';

// Include database connection
require_once '../config/koneksi.php';

// Check if dynamic track ID is present
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_lagu = (int)$_GET['id'];

    // 1. Retrieve the track's current image to clean up disk storage
    $stmt = $conn->prepare("SELECT gambar FROM tb_lagu WHERE id_lagu = ?");
    $stmt->bind_param("i", $id_lagu);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $lagu = $result->fetch_assoc();
        $gambar = $lagu['gambar'];

        // Delete associated image file from uploads/ directory if it is not default.jpg
        if ($gambar !== 'default.jpg') {
            $image_path = '../uploads/' . $gambar;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // 2. Perform DB deletion using Prepared Statement
        $delete_stmt = $conn->prepare("DELETE FROM tb_lagu WHERE id_lagu = ?");
        $delete_stmt->bind_param("i", $id_lagu);

        if ($delete_stmt->execute()) {
            echo "<script>alert('Lagu berhasil dihapus dari arsip!'); window.location.href = 'index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal menghapus lagu dari database.'); window.location.href = 'index.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Arsip lagu tidak ditemukan!'); window.location.href = 'index.php';</script>";
        exit();
    }
} else {
    // Redirect back to dashboard if no ID was provided
    header("Location: index.php");
    exit();
}
?>
