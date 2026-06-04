<?php 
session_start();
if(!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include "config/koneksi.php"; 
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE id=$id"));
$old_foto = isset($data['foto']) ? $data['foto'] : 'default.png';

if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $nim = $_POST['nim'];
    $jurusan_id = $_POST['jurusan_id'];
    $foto = $old_foto;

    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($fileExtension, $allowedExt)) {
            $newFileName = uniqid() . '.' . $fileExtension;
            $destPath = 'uploads/' . $newFileName;
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                $foto = $newFileName;
                if($old_foto != 'default.png' && file_exists('uploads/' . $old_foto)) {
                    unlink('uploads/' . $old_foto);
                }
            }
        } else {
            $error = "Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.";
        }
    }

    if(!isset($error)) {
        $sql = "UPDATE mahasiswa SET nama='$nama', nim='$nim', jurusan_id='$jurusan_id', foto='$foto' WHERE id=$id";
        if (mysqli_query($koneksi, $sql)) {
            header("Location: index.php?msg=edit");
            exit;
        } else {
            $error = "Error: " . mysqli_error($koneksi);
        }
    }
}
?>
<?php
$page_title = 'Edit Mahasiswa';
include "includes/header.php";
?>

<div class="container form-container">
    <div class="glass-panel">
        <h2>Edit Mahasiswa</h2>
        
        <?php if(isset($error)) { echo "<p style='color: #ef4444; margin-bottom: 1rem; text-align: center;'>$error</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="avatar-preview">
                <?php
                $img_src = ($old_foto != 'default.png') ? 'uploads/' . $old_foto : 'https://ui-avatars.com/api/?name=' . urlencode($data['nama']) . '&background=8b5cf6&color=fff';
                ?>
                <img src="<?= $img_src ?>" class="avatar-lg" alt="Foto">
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= $data['nama'] ?>" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" value="<?= $data['nim'] ?>" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Jurusan</label>
                <select name="jurusan_id" required style="width: 100%; padding: 0.75rem 1rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 1rem;">
                    <option value="" disabled>-- Pilih Jurusan --</option>
                    <?php
                    $q_jurusan = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
                    while($j = mysqli_fetch_assoc($q_jurusan)) {
                        $selected = ($j['id'] == $data['jurusan_id']) ? 'selected' : '';
                        echo "<option value='".$j['id']."' style='color: #1e1b4b;' $selected>".$j['nama_jurusan']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ganti Foto (Opsional)</label>
                <input type="file" name="foto" accept="image/png, image/jpeg, image/jpg, image/gif">
            </div>
            
            <div class="actions" style="margin-top: 2rem;">
                <a href="index.php" class="btn btn-secondary" style="flex: 1;">Kembali</a>
                <button type="submit" name="update" class="btn btn-primary" style="flex: 2;">Update Data</button>
            </div>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>
