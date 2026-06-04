<?php
// Include session checking
require_once '../config/session.php';

// Handle Track Deletion
if (isset($_GET['id_lagu']) && !empty($_GET['id_lagu'])) {
    $id_lagu = (int)$_GET['id_lagu'];

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

        // Perform DB deletion
        $delete = mysqli_query($conn, "DELETE FROM tb_lagu WHERE id_lagu = '$id_lagu'");

        if ($delete) {
            $_SESSION['flash'] = 'Lagu berhasil dihapus dari arsip!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Gagal menghapus lagu dari database.';
            $_SESSION['flash_type'] = 'error';
        }
    } else {
        $_SESSION['flash'] = 'Arsip lagu tidak ditemukan!';
        $_SESSION['flash_type'] = 'error';
    }
    header("Location: lagu_data.php");
    exit();
}

// Handle Genre Deletion
if (isset($_GET['id_genre']) && !empty($_GET['id_genre'])) {
    $id_genre = (int)$_GET['id_genre'];

    // Check if there are songs associated with this genre
    $check_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lagu WHERE id_genre = '$id_genre'");
    $count = mysqli_fetch_array($check_query)['total'];

    if ($count > 0) {
        $_SESSION['flash'] = 'Gagal menghapus! Masih ada ' . $count . ' lagu yang menggunakan genre ini.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $delete = mysqli_query($conn, "DELETE FROM tb_genre WHERE id_genre = '$id_genre'");

        if ($delete) {
            $_SESSION['flash'] = 'Genre berhasil dihapus!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Gagal menghapus genre dari database.';
            $_SESSION['flash_type'] = 'error';
        }
    }
    header("Location: genre_data.php");
    exit();
}

// Redirect back to dashboard if no valid ID was provided
header("Location: index.php");
exit();
?>
