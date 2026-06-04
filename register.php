<?php
session_start();
if(isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}
include "config/koneksi.php";

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Check if username exists
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($cek) > 0) {
        $error = "Username sudah terdaftar!";
    } else {
        // Hash password
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password_hashed')";
        if(mysqli_query($koneksi, $sql)) {
            header("Location: login.php?msg=registered");
            exit;
        } else {
            $error = "Error: " . mysqli_error($koneksi);
        }
    }
}
?>
<?php
$page_title = 'Register - Data Mahasiswa';
include "includes/header.php";
?>
<div class="container form-container" style="max-width: 400px; margin-top: 5rem;">
    <div class="glass-panel">
        <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Buat Akun</h2>
        
        <?php if(isset($error)) { echo "<p style='color: #ef4444; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;'>$error</p>"; } ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off" placeholder="Pilih username...">
            </div>
            <div class="form-group">
                <label>Password</label>
                <!-- Using inline style matching text inputs for password -->
                <input type="password" name="password" required placeholder="Masukkan password..." style="width: 100%; padding: 0.75rem 1rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 1rem; transition: all 0.3s ease;">
                <style>input[type="password"]:focus { outline: none; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); background: rgba(0, 0, 0, 0.3); }</style>
            </div>
            
            <button type="submit" name="register" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Daftar Sekarang</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            Sudah punya akun? <a href="login.php" style="color: #a855f7; text-decoration: none;">Login di sini</a>
        </p>
    </div>
</div>
<?php include "includes/footer.php"; ?>
