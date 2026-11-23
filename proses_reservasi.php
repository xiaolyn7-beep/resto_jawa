<?php
include "koneksi.php";
$stmt = $connection->prepare("INSERT INTO reservasi (nama_pemesan, nama_restoran, jumlah_orang, tanggal_reservasi) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $_POST['nama_pemesan'], $_POST['nama_restoran'], $_POST['jumlah_orang'], $_POST['tanggal_reservasi']);

if ($stmt->execute()) {
    header("Location: katalog.php?reservasi=sukses");
}
$stmt->close();
?>