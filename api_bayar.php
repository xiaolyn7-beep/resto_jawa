<?php
// API pembayaran: percabangan Tunai / EDC / QRIS + validasi kembalian
require "koneksi.php";
require "fungsi.php";
wajib_login(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['sukses' => false, 'pesan' => 'Metode tidak diizinkan.'], 405);
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['sukses' => false, 'pesan' => 'Data tidak valid.'], 400);

$idPesanan = (int)($in['id_pesanan'] ?? 0);
$metode    = $in['metode'] ?? '';
$referensi = trim($in['referensi'] ?? '');
$bayar     = (int)($in['jumlah_bayar'] ?? 0);

if (!in_array($metode, ['Tunai', 'EDC', 'QRIS'], true)) json_out(['sukses' => false, 'pesan' => 'Metode pembayaran tidak valid.']);

$connection->begin_transaction();
try {
    $stmt = $connection->prepare("SELECT total, status_pesanan, status_pembayaran FROM pesanan WHERE id_pesanan = ? FOR UPDATE");
    $stmt->bind_param("i", $idPesanan);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) throw new PesananError('Pesanan tidak ditemukan.');
    if ($p['status_pembayaran'] === 'Lunas') throw new PesananError('Pesanan ini sudah dibayar.');
    if ($p['status_pesanan'] === 'Batal') throw new PesananError('Pesanan sudah dibatalkan.');
    $total = (int)$p['total'];

    switch ($metode) {
        case 'Tunai':
            if ($bayar <= 0 || $bayar > 1000000000) throw new PesananError('Nominal uang tunai tidak valid.');
            if ($bayar < $total) throw new PesananError('Uang kurang ' . rupiah($total - $bayar) . '.');
            $kembalian = $bayar - $total;     // dihitung server
            $referensi = null;
            break;
        case 'EDC':
            if (!preg_match('/^[A-Za-z0-9]{4,20}$/', $referensi)) throw new PesananError('Kode approval EDC wajib diisi (4-20 huruf/angka).');
            $bayar = $total; $kembalian = 0;
            break;
        case 'QRIS':
            if (!preg_match('/^[A-Za-z0-9\-]{6,40}$/', $referensi)) throw new PesananError('ID/Referensi transaksi QRIS wajib diisi (6-40 karakter).');
            $bayar = $total; $kembalian = 0;
            break;
    }

    $idKasir = $_SESSION['id_user'] ?? null;
    $stmt = $connection->prepare("INSERT INTO pembayaran (id_pesanan, metode, total_tagihan, jumlah_bayar, kembalian, nomor_referensi, id_kasir) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiisi", $idPesanan, $metode, $total, $bayar, $kembalian, $referensi, $idKasir);
    $stmt->execute();

    $stmt = $connection->prepare("UPDATE pesanan SET status_pembayaran = 'Lunas', status_pesanan = IF(status_pesanan = 'Baru', 'Diproses', status_pesanan) WHERE id_pesanan = ?");
    $stmt->bind_param("i", $idPesanan);
    $stmt->execute();

    $connection->commit();
    json_out(['sukses' => true, 'kembalian' => $kembalian]);
} catch (PesananError $ex) {
    $connection->rollback();
    json_out(['sukses' => false, 'pesan' => $ex->getMessage()]);
} catch (Throwable $ex) {
    $connection->rollback();
    json_out(['sukses' => false, 'pesan' => 'Terjadi kesalahan server, coba lagi.'], 500);
}
