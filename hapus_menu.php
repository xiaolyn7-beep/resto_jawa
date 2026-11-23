<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
include "koneksi.php";

$stmt = $connection->prepare("DELETE FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $_GET['id']);

if ($stmt->execute()) { header("Location: dashboard.php?pesan=hapus"); }
$stmt->close();
?>