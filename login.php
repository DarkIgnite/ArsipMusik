<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to admin panel if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/index.php");
    exit();
}

require_once 'config/koneksi.php';

$error = '';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Server-side validation
    if ($username === '' || $password === '') {
        echo "<script>alert('Username dan password tidak boleh kosong!'); window.history.back();</script>";
        exit();
    }

    // Secure database lookup
    $safe_username = $conn->real_escape_string($username);
    $query = "SELECT * FROM tb_user WHERE username = '$safe_username' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Verify BCrypt hashed password
        if (password_verify($password, $user_data['password'])) {
            // Establish authorized session parameters
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['id_user'] = $user_data['id_user'];
            
            // Redirect to dashboard
            header("Location: admin/index.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - ArsipMusik</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo">
            <a href="index.php" class="logo">
                <span class="logo-icon"></span>
                ArsipMusik
            </a>
        </div>
        
        <h2 class="login-title">Administrator</h2>
        <p class="login-subtitle">Masuk untuk mengelola arsip lagu</p>

        <!-- Display error alert if login fails -->
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" id="login-error-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="login-form">
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
            <button type="submit" class="btn-primary-block" id="btn-login-submit">Masuk</button>
        </form>
    </div>

</body>
</html>
