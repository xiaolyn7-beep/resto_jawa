<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
include "koneksi.php";

$stmt = $connection->prepare("INSERT INTO menu (nama_restoran, nama_makanan, kategori, harga, stok, deskripsi) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiis", $_POST['nama_restoran'], $_POST['nama_makanan'], $_POST['kategori'], $_POST['harga'], $_POST['stok'], $_POST['deskripsi']);

if ($stmt->execute()) { header("Location: dashboard.php?pesan=tambah"); }
$stmt->close();
?>