<?php
$connection = new mysqli("localhost", "root", "", "db_resto_projek");
if ($connection->connect_error) {
    die("Koneksi basis data gagal: " . $connection->connect_error);
}
?>