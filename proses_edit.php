<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
include "koneksi.php";

$stmt = $connection->prepare("UPDATE menu SET nama_restoran=?, nama_makanan=?, kategori=?, harga=?, stok=?, deskripsi=? WHERE id_menu=?");
$stmt->bind_param("sssiisi", $_POST['nama_restoran'], $_POST['nama_makanan'], $_POST['kategori'], $_POST['harga'], $_POST['stok'], $_POST['deskripsi'], $_POST['id_menu']);

if ($stmt->execute()) { header("Location: dashboard.php?pesan=edit"); }
$stmt->close();
?>