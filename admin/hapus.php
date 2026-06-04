<?php
// Include session checking
require_once '../config/session.php';

// Check if dynamic track ID is present
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_lagu = (int)$_GET['id'];

    // Retrieve the track's current image to clean up disk storage
    $query = mysqli_query($conn, "SELECT gambar FROM tb_lagu WHERE id_lagu = '$id_lagu'");

    if ($query && mysqli_num_rows($query) > 0) {
        $lagu = mysqli_fetch_array($query);
        $gambar = $lagu['gambar'];

        // Delete associated image file from uploads/ directory if it is not default.jpg
        if ($gambar != 'default.jpg') {
            $image_path = '../uploads/' . $gambar;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Perform DB deletion using procedural query
        $delete = mysqli_query($conn, "DELETE FROM tb_lagu WHERE id_lagu = '$id_lagu'");

        if ($delete) {
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
