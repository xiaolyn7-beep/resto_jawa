<?php
session_start();
include "koneksi.php";

$username      = $_POST["username"];
$password_asli = $_POST["password"];

$stmt = mysqli_prepare($connection, "SELECT password, role FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$user  = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($user && password_verify($password_asli, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION["status_login"] = true;
    $_SESSION["username"]     = $username;
    $_SESSION["role"]         = $user['role'];
    if ($user['role'] == 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: kasir.php");
    }
} else {
    header("Location: index.php?pesan=salah");
}
?>
