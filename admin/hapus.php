<?php
// sertakan pengecekan sesi
require_once '../config/session.php';

// tangani penghapusan lagu
if (isset($_GET['id_lagu']) && !empty($_GET['id_lagu'])) {
    $id_lagu = (int)$_GET['id_lagu'];

    // ambil gambar lagu saat ini untuk membersihkan penyimpanan
    $query = mysqli_query($conn, "SELECT gambar FROM tb_lagu WHERE id_lagu = '$id_lagu'");

    if ($query && mysqli_num_rows($query) > 0) {
        $lagu = mysqli_fetch_array($query);
        $gambar = $lagu['gambar'];

        // hapus file gambar terkait dari direktori uploads jika bukan default.jpg
        if ($gambar != 'default.jpg') {
            $image_path = '../uploads/' . $gambar;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // lakukan penghapusan di database
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

// tangani penghapusan genre
if (isset($_GET['id_genre']) && !empty($_GET['id_genre'])) {
    $id_genre = (int)$_GET['id_genre'];

    // periksa apakah ada lagu yang terkait dengan genre ini
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

// alihkan kembali ke dashboard jika tidak ada id valid yang diberikan
header("Location: index.php");
exit();
?>
