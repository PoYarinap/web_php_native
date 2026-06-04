<?php 
session_start();
if(!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include "config/koneksi.php"; 

// Data untuk Chart & Cards
$query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa");
$total_mhs = mysqli_fetch_assoc($query_total)['total'];

$query_jurusan = mysqli_query($koneksi, "SELECT COUNT(DISTINCT jurusan_id) as total FROM mahasiswa");
$total_jurusan = mysqli_fetch_assoc($query_jurusan)['total'];

$query_chart = mysqli_query($koneksi, "SELECT jurusan.nama_jurusan, COUNT(*) as jumlah FROM mahasiswa LEFT JOIN jurusan ON mahasiswa.jurusan_id = jurusan.id GROUP BY mahasiswa.jurusan_id");
$labels = [];
$data_counts = [];
while($row = mysqli_fetch_assoc($query_chart)){
    $labels[] = $row['nama_jurusan'];
    $data_counts[] = $row['jumlah'];
}
$labels_json = json_encode($labels);
$data_json = json_encode($data_counts);

$page_title = 'Data Mahasiswa';
include "includes/header.php";
?>

<div class="container">
    <div class="glass-panel">
        <div class="header-actions">
            <h2>Data Mahasiswa</h2>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="color: var(--text-muted); font-size: 0.9rem; margin-right: 0.5rem;">Hai, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="tambah.php" class="btn btn-primary">+ Tambah Data</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>

        <!-- Dashboard Statistik -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 1rem; margin-bottom: 1.5rem;">
            <!-- Card Total Mahasiswa -->
            <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border); display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <h3 style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;">Total Mahasiswa</h3>
                <span style="font-size: 2.5rem; font-weight: bold; color: #fff;"><?= $total_mhs ?></span>
            </div>
            <!-- Card Total Jurusan -->
            <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border); display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <h3 style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;">Total Jurusan</h3>
                <span style="font-size: 2.5rem; font-weight: bold; color: #34d399;"><?= $total_jurusan ?></span>
            </div>
            <!-- Chart Container -->
            <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 12px; border: 1px solid var(--glass-border); display: flex; justify-content: center; align-items: center; max-height: 150px;">
                <?php if($total_mhs > 0) { ?>
                    <canvas id="jurusanChart"></canvas>
                <?php } else { ?>
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Belum ada data</span>
                <?php } ?>
            </div>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <input type="text" name="search" placeholder="Cari berdasarkan nama atau NIM..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" autocomplete="off" style="margin-bottom: 0;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            <?php if(isset($_GET['search']) && $_GET['search'] != '') { ?>
                <a href="index.php" class="btn btn-danger">Reset</a>
            <?php } ?>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
                    if ($search) {
                        $query = "SELECT mahasiswa.*, jurusan.nama_jurusan FROM mahasiswa LEFT JOIN jurusan ON mahasiswa.jurusan_id = jurusan.id WHERE mahasiswa.nama LIKE '%$search%' OR mahasiswa.nim LIKE '%$search%'";
                    } else {
                        $query = "SELECT mahasiswa.*, jurusan.nama_jurusan FROM mahasiswa LEFT JOIN jurusan ON mahasiswa.jurusan_id = jurusan.id";
                    }
                    $result = mysqli_query($koneksi, $query);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) { 
                            $foto = isset($row['foto']) ? $row['foto'] : 'default.png';
                            $img_src = ($foto != 'default.png') ? 'uploads/' . $foto : 'https://ui-avatars.com/api/?name=' . urlencode($row['nama']) . '&background=random&color=fff';
                        ?>
                        <tr>
                            <td><img src="<?= $img_src ?>" class="avatar-sm" alt="Foto"></td>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['nim'] ?></td>
                            <td><?= $row['nama_jurusan'] ?></td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <?php } 
                    } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">Data mahasiswa tidak ditemukan.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Animasi Konfirmasi Hapus (SweetAlert)
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin hapus data?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#4f46e5',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1e1b4b', // Sesuaikan tema web
        color: '#f8fafc'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus.php?id=' + id;
        }
    })
}

// Menampilkan Notifikasi Toast berdasarkan parameter URL (msg)
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.has('msg')) {
    const msg = urlParams.get('msg');
    let text = '';
    
    if(msg === 'add') text = 'Data berhasil ditambahkan!';
    if(msg === 'edit') text = 'Data berhasil diperbarui!';
    if(msg === 'delete') text = 'Data berhasil dihapus!';
    
    if(text) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: text,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1e1b4b',
            color: '#f8fafc',
            iconColor: '#34d399' // Warna hijau pastel
        });
        
        // Membersihkan URL dari parameter '?msg=...' tanpa me-refresh halaman
        window.history.replaceState(null, null, window.location.pathname);
    }
}

// Inisialisasi Chart.js
<?php if($total_mhs > 0) { ?>
const ctx = document.getElementById('jurusanChart');
if(ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= $labels_json ?>,
            datasets: [{
                data: <?= $data_json ?>,
                backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#06b6d4'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#f8fafc', font: { size: 11 } } }
            }
        }
    });
}
<?php } ?>
</script>
<?php include "includes/footer.php"; ?>
