<?php
session_start();
include "koneksi.php";

$username      = $_POST["username"];
$password_asli = $_POST["password"];

$stmt = $connection->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($password_hash);
$stmt->fetch();
$stmt->close();

if ($password_hash && password_verify($password_asli, $password_hash)) {
    $_SESSION["status_login"] = true;
    $_SESSION["username"]     = $username;
    header("Location: dashboard.php");
} else {
    header("Location: index.php?pesan=salah");
}
?>