<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

$id = (int)$_GET['id'];
$stmt = $connection->prepare("SELECT gambar FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $connection->prepare("DELETE FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

if ($m) hapus_gambar($m['gambar']);
header("Location: dashboard.php?pesan=hapus");
