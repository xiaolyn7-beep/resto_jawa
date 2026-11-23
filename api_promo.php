<?php
require "koneksi.php";
require "fungsi.php";
wajib_login(true);

$kode = strtoupper(trim($_GET['kode'] ?? ''));
if ($kode === '') json_out(['sukses' => false, 'pesan' => 'Masukkan kode promo.']);
$p = cari_promo($connection, $kode);
if (!$p) json_out(['sukses' => false, 'pesan' => 'Kode promo tidak valid atau sudah kedaluwarsa.']);
json_out(['sukses' => true, 'promo' => [
    'kode' => $p['kode'], 'tipe' => $p['tipe'], 'nilai' => (int)$p['nilai'], 'min_belanja' => (int)$p['min_belanja']
]]);
