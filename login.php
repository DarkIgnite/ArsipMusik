<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] == true) {
    header("Location: admin/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - ArsipMusik</title>
    <link rel="icon" href="assets/logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo">
            <a href="index.php" class="logo">
                <img src="assets/logo.png" alt="Logo" class="logo-img">
                ArsipMusik
            </a>
        </div>
        
        <h2 class="login-title">Administrator</h2>
        <p class="login-subtitle">Masuk untuk mengelola arsip lagu</p>

        <form action="" method="POST" id="login-form">
            <!-- Username Field -->
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-input" 
                       placeholder="Masukkan username admin" required autocomplete="username">
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-input" 
                       placeholder="Masukkan password admin" required autocomplete="current-password">
            </div>

            <!-- Submit Button -->
            <input type="submit" name="submit" value="Masuk" class="btn-primary-block" id="btn-login-submit">
        </form>
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="text-decoration: none; color: var(--text-secondary); font-size: 0.9rem;">&larr; Kembali ke Beranda</a>
        </div>

        <?php
        include('config/koneksi.php');
        if (isset($_POST['submit'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $sql = mysqli_query($conn, "SELECT * FROM tb_user WHERE username = '$username' AND password = '$password'") or die(mysqli_error($conn));

            if (mysqli_num_rows($sql) == 0) {
                echo "<script>alert('Username atau password salah!')</script>";
                echo '<script type="text/javascript">window.location="login.php";</script>';
            } else {
                $row = mysqli_fetch_array($sql);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['id_user'] = $row['id_user'];

                echo "<script>alert('Login Berhasil')</script>";
                echo '<script type="text/javascript">window.location="admin/index.php";</script>';
            }
        }
        ?>
    </div>

</body>
</html>
