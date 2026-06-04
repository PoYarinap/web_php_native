<?php
$host = "localhost";
$user = "root";   // default user XAMPP
$pass = "";       // default password XAMPP kosong
$db   = "crud_mahasiswa";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
