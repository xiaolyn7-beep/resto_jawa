<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
include "upload.php";

$id = (int)($_GET['id'] ?? 0);

$q = mysqli_prepare($connection, "SELECT gambar FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($q, "i", $id);
mysqli_stmt_execute($q);
$hasil = mysqli_stmt_get_result($q);
$baris = mysqli_fetch_assoc($hasil);
$gambar = $baris['gambar'];
mysqli_stmt_close($q);

$stmt = mysqli_prepare($connection, "DELETE FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    hapus_gambar($gambar);
    header("Location: dashboard.php?pesan=hapus");
}
mysqli_stmt_close($stmt);
?>
