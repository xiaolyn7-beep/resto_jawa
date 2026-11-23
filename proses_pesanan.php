<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "kasir") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";

// Kembali ke halaman kasir dengan pesan error
function balik($pesan) {
    header("Location: kasir.php?error=" . urlencode($pesan));
    exit();
}

$keranjang = json_decode($_POST['keranjang'], true);
if (!is_array($keranjang) || count($keranjang) == 0) {
    balik("Keranjang masih kosong.");
}

$metode = $_POST['metode_bayar'];
if ($metode != 'Tunai' && $metode != 'QRIS' && $metode != 'Transfer' && $metode != 'Debit') {
    balik("Metode pembayaran tidak valid.");
}

$pelanggan = trim($_POST['nama_pelanggan']);
if ($pelanggan == "") {
    $pelanggan = "Umum";
}

mysqli_begin_transaction($connection);
try {
    // 1. Hitung total dari harga di database (bukan dari browser)
    $total = 0;
    $daftar = array();
    foreach ($keranjang as $item) {
        $id  = (int)$item['id'];
        $qty = (int)$item['qty'];
        if ($qty < 1) {
            throw new Exception("Jumlah pesanan tidak valid.");
        }

        $stmt = mysqli_prepare($connection, "SELECT nama_makanan, harga, stok FROM menu WHERE id_menu = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $menu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$menu) {
            throw new Exception("Menu tidak ditemukan.");
        }
        if ($menu['stok'] < $qty) {
            throw new Exception("Stok " . $menu['nama_makanan'] . " tidak cukup (sisa " . $menu['stok'] . ").");
        }

        $subtotal = $menu['harga'] * $qty;
        $total = $total + $subtotal;
        $daftar[] = array(
            'id' => $id,
            'nama' => $menu['nama_makanan'],
            'harga' => $menu['harga'],
            'qty' => $qty,
            'subtotal' => $subtotal
        );
    }

    // 2. Pesanan harus langsung dibayar
    if ($metode == 'Tunai') {
        $bayar = (int)$_POST['uang_bayar'];
    } else {
        $bayar = $total;
    }
    if ($bayar < $total) {
        throw new Exception("Uang bayar kurang. Pesanan harus langsung dibayar lunas.");
    }
    $kembalian = $bayar - $total;

    // 3. Simpan pesanan
    $kasir = $_SESSION['username'];
    $stmt = mysqli_prepare($connection, "INSERT INTO pesanan (kasir, nama_pelanggan, total, metode_bayar, uang_bayar, kembalian, status) VALUES (?, ?, ?, ?, ?, ?, 'Lunas')");
    mysqli_stmt_bind_param($stmt, "ssisii", $kasir, $pelanggan, $total, $metode, $bayar, $kembalian);
    mysqli_stmt_execute($stmt);
    $id_pesanan = mysqli_insert_id($connection);
    mysqli_stmt_close($stmt);

    $kode = "INV-" . date('Ymd') . "-" . str_pad($id_pesanan, 4, "0", STR_PAD_LEFT);
    $stmt = mysqli_prepare($connection, "UPDATE pesanan SET kode_pesanan = ? WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt, "si", $kode, $id_pesanan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 4. Simpan detail dan kurangi stok
    foreach ($daftar as $d) {
        $stmt = mysqli_prepare($connection, "INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_makanan, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisiii", $id_pesanan, $d['id'], $d['nama'], $d['harga'], $d['qty'], $d['subtotal']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($connection, "UPDATE menu SET stok = stok - ? WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt, "ii", $d['qty'], $d['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($connection);
    header("Location: pesanan.php?sukses=" . $id_pesanan);
} catch (Exception $ex) {
    mysqli_rollback($connection);
    balik($ex->getMessage());
}
?>
