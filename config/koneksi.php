<?php
// koneksi data ke database agar masuk
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'arsipmusik';

$conn = mysqli_connect($hostname, $username, $password, $dbname) or die('Koneksi database gagal');
?>
