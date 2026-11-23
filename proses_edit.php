<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

$id = (int)$_POST['id_menu'];
$kategori = in_array($_POST['kategori'] ?? '', KATEGORI_MENU, true) ? $_POST['kategori'] : 'Makanan';
$harga = max(0, (int)$_POST['harga']);
$stok  = max(0, (int)$_POST['stok']);

$stmt = $connection->prepare("SELECT gambar FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$lama = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$lama) { header("Location: dashboard.php"); exit(); }
$gambar = $lama['gambar'];

$error = '';
$baru = simpan_gambar($_FILES['gambar'] ?? null, $error);
if ($baru === false) { header("Location: edit_menu.php?id=$id&error=" . urlencode($error)); exit(); }
if ($baru !== null) { $gambar = $baru; }
elseif (!empty($_POST['hapus_gambar'])) { $gambar = null; }

$stmt = $connection->prepare("UPDATE menu SET nama_makanan=?, kategori=?, harga=?, stok=?, deskripsi=?, gambar=? WHERE id_menu=?");
$stmt->bind_param("ssiissi", $_POST['nama_makanan'], $kategori, $harga, $stok, $_POST['deskripsi'], $gambar, $id);
$stmt->execute();
$stmt->close();

if ($lama['gambar'] && $lama['gambar'] !== $gambar) hapus_gambar($lama['gambar']);  // bersihkan file lama
header("Location: dashboard.php?pesan=edit");
