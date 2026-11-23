<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
include "upload.php";

$id = (int)$_POST['id_menu'];

// ambil gambar lama
$q = mysqli_prepare($connection, "SELECT gambar FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($q, "i", $id);
mysqli_stmt_execute($q);
$hasil = mysqli_stmt_get_result($q);
$baris = mysqli_fetch_assoc($hasil);
$lama = $baris['gambar'];
mysqli_stmt_close($q);

$error = '';
$baru  = upload_gambar($_FILES['gambar'] ?? null, $error);
if ($baru === false) {
    header("Location: edit_menu.php?id=$id&error=" . urlencode($error));
    exit();
}
if ($baru === null) {
    $gambar = $lama;   // tidak upload -> pakai gambar lama
} else {
    $gambar = $baru;
}

$stmt = mysqli_prepare($connection, "UPDATE menu SET nama_restoran=?, nama_makanan=?, kategori=?, harga=?, stok=?, deskripsi=?, gambar=? WHERE id_menu=?");
mysqli_stmt_bind_param($stmt, "sssiissi", $_POST['nama_restoran'], $_POST['nama_makanan'], $_POST['kategori'], $_POST['harga'], $_POST['stok'], $_POST['deskripsi'], $gambar, $id);

if (mysqli_stmt_execute($stmt)) {
    if ($baru !== null) hapus_gambar($lama);   // hapus berkas lama setelah berhasil diganti
    header("Location: dashboard.php?pesan=edit");
}
mysqli_stmt_close($stmt);
?>
