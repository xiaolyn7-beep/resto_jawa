<?php
include "koneksi.php";
$username      = $_POST["username"];
$password_asli = $_POST["password"];
$password_hash = password_hash($password_asli, PASSWORD_DEFAULT);

$stmt = $connection->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password_hash);

if ($stmt->execute()) {
    header("Location: index.php?pesan=daftar_berhasil");
} else {
    echo "Pendaftaran gagal: " . $connection->error;
}
$stmt->close();
?>