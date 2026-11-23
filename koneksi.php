<?php
$connection = mysqli_connect("localhost", "root", "", "db_resto_projek");
if (!$connection) {
    die("Koneksi basis data gagal: " . mysqli_connect_error());
}
?>