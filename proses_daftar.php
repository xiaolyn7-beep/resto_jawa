<?php
include "koneksi.php";

// Pastikan memang belum ada admin
$cek = mysqli_query($connection, "SELECT COUNT(*) AS jumlah FROM users WHERE role = 'admin'");
$data = mysqli_fetch_assoc($cek);
if ($data['jumlah'] > 0) {
    header("Location: index.php");
    exit();
}

$username = trim($_POST["username"]);
$password = $_POST["password"];

if ($username == "" || strlen($password) < 6) {
    header("Location: daftar.php?pesan=" . urlencode("Username wajib diisi dan password minimal 6 karakter."));
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($connection, "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
mysqli_stmt_bind_param($stmt, "ss", $username, $password_hash);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php?pesan=daftar_berhasil");
} else {
    echo "Pendaftaran gagal: " . mysqli_error($connection);
}
mysqli_stmt_close($stmt);
?>
