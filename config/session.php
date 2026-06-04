<?php
include('../config/koneksi.php');
session_start();
if (!isset($_SESSION['admin_logged_in']) || ($_SESSION['admin_logged_in'] != true)) { ?>
<script>
alert('Silakan Login Terlebih Dahulu')
window.location = "../login.php";
</script>
<?php
exit();
}
$session_id = $_SESSION['id_user'];

$user_query = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$session_id'") or die(mysqli_error($conn));
$user_row = mysqli_fetch_array($user_query);
?>
