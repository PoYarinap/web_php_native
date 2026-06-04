<?php
session_start();
if(isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}
include "config/koneksi.php";

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if(password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<?php
$page_title = 'Login - Data Mahasiswa';
include "includes/header.php";
?>
<div class="container form-container" style="max-width: 400px; margin-top: 5rem;">
    <div class="glass-panel">
        <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Login Admin</h2>
        
        <?php if(isset($error)) { echo "<p style='color: #ef4444; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;'>$error</p>"; } ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off" placeholder="Masukkan username...">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password..." style="width: 100%; padding: 0.75rem 1rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 1rem; transition: all 0.3s ease;">
                <style>input[type="password"]:focus { outline: none; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); background: rgba(0, 0, 0, 0.3); }</style>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Masuk</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            Belum punya akun? <a href="register.php" style="color: #a855f7; text-decoration: none;">Daftar di sini</a>
        </p>
    </div>
</div>

<script>
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.has('msg') && urlParams.get('msg') === 'registered') {
    Swal.fire({
        icon: 'success',
        title: 'Hore! Akun Berhasil Dibuat 🎉',
        text: 'Silakan masuk menggunakan username dan password yang baru saja Anda daftarkan.',
        background: '#1e1b4b',
        color: '#f8fafc',
        confirmButtonColor: '#8b5cf6',
        confirmButtonText: 'Siap, Login!'
    });
    // Bersihkan URL dari msg=registered
    window.history.replaceState(null, null, window.location.pathname);
}
</script>
<?php include "includes/footer.php"; ?>
