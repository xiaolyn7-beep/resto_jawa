<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
include "upload.php";

$error  = '';
$gambar = upload_gambar($_FILES['gambar'] ?? null, $error);
if ($gambar === false) {
    header("Location: tambah_menu.php?error=" . urlencode($error));
    exit();
}

$stmt = mysqli_prepare($connection, "INSERT INTO menu (nama_restoran, nama_makanan, kategori, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssiiss", $_POST['nama_restoran'], $_POST['nama_makanan'], $_POST['kategori'], $_POST['harga'], $_POST['stok'], $_POST['deskripsi'], $gambar);

if (mysqli_stmt_execute($stmt)) { header("Location: dashboard.php?pesan=tambah"); }
mysqli_stmt_close($stmt);
?>
