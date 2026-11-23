<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();   // reservasi kini diinput oleh staf (kasir/admin)

$stmt = $connection->prepare("INSERT INTO reservasi (nama_pemesan, jumlah_orang, tanggal_reservasi) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $_POST['nama_pemesan'], $_POST['jumlah_orang'], $_POST['tanggal_reservasi']);
$stmt->execute();
$stmt->close();
$_SESSION['flash'] = ['success', 'Reservasi berhasil ditambahkan.'];
header("Location: admin_reservasi.php");
