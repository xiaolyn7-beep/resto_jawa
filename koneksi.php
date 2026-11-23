<?php
$connection = new mysqli("localhost", "root", "", "javanesefood");
if ($connection->connect_error) {
    die("Koneksi basis data gagal: " . $connection->connect_error);
}
?>