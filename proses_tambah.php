<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

$kategori = in_array($_POST['kategori'] ?? '', KATEGORI_MENU, true) ? $_POST['kategori'] : 'Makanan';
$harga = max(0, (int)$_POST['harga']);
$stok  = max(0, (int)$_POST['stok']);

$error = '';
$gambar = simpan_gambar($_FILES['gambar'] ?? null, $error);
if ($gambar === false) { header("Location: tambah_menu.php?error=" . urlencode($error)); exit(); }

$stmt = $connection->prepare("INSERT INTO menu (nama_makanan, kategori, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiss", $_POST['nama_makanan'], $kategori, $harga, $stok, $_POST['deskripsi'], $gambar);
$stmt->execute();
$stmt->close();
header("Location: dashboard.php?pesan=tambah");
