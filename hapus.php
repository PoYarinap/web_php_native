<?php
session_start();
if(!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include "config/koneksi.php";
$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM mahasiswa WHERE id=$id"));
if($data && isset($data['foto']) && $data['foto'] != 'default.png' && file_exists('uploads/' . $data['foto'])) {
    unlink('uploads/' . $data['foto']);
}

$sql = "DELETE FROM mahasiswa WHERE id=$id";
if (mysqli_query($koneksi, $sql)) {
    header("Location: index.php?msg=delete");
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>
