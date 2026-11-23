<?php
session_start();
include "koneksi.php";

$username      = $_POST["username"] ?? '';
$password_asli = $_POST["password"] ?? '';

$stmt = $connection->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($id_user, $password_hash, $role);
$stmt->fetch();
$stmt->close();

if ($password_hash && password_verify($password_asli, $password_hash)) {
    session_regenerate_id(true);
    $_SESSION["status_login"] = true;
    $_SESSION["username"]     = $username;
    $_SESSION["id_user"]      = $id_user;
    $_SESSION["role"]         = $role;      // 'admin' atau 'kasir'
    header("Location: kasir.php");
} else {
    header("Location: index.php?pesan=salah");
}
?>
