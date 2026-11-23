<?php
// API pembuatan pesanan oleh kasir/admin
require "koneksi.php";
require "fungsi.php";
wajib_login(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['sukses' => false, 'pesan' => 'Metode tidak diizinkan.'], 405);
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['sukses' => false, 'pesan' => 'Data tidak valid.'], 400);

$nama    = substr(trim($in['nama'] ?? ''), 0, 100);
if ($nama === '') $nama = 'Pelanggan';
$tipe    = ($in['tipe'] ?? '') === 'Take Away' ? 'Take Away' : 'Dine In';
$meja    = $tipe === 'Dine In' ? substr(trim($in['meja'] ?? ''), 0, 10) : '';
$catatan = substr(trim($in['catatan'] ?? ''), 0, 255);
$kodePromo = strtoupper(trim($in['kode_promo'] ?? ''));

if ($tipe === 'Dine In' && $meja === '') json_out(['sukses' => false, 'pesan' => 'Pilih lokasi meja untuk pesanan Dine In.']);

$items = [];
foreach (($in['items'] ?? []) as $it) {
    $id = (int)($it['id'] ?? 0); $qty = (int)($it['qty'] ?? 0);
    if ($id <= 0 || $qty <= 0 || $qty > 99) json_out(['sukses' => false, 'pesan' => 'Item pesanan tidak valid.']);
    $items[$id] = ($items[$id] ?? 0) + $qty;
}
if (!$items) json_out(['sukses' => false, 'pesan' => 'Keranjang masih kosong.']);

$connection->begin_transaction();
try {
    $stmtM = $connection->prepare("SELECT nama_makanan, harga FROM menu WHERE id_menu = ? FOR UPDATE");
    $stmtS = $connection->prepare("UPDATE menu SET stok = stok - ? WHERE id_menu = ? AND stok >= ?");
    $baris = []; $subtotal = 0;

    foreach ($items as $id => $qty) {
        $stmtM->bind_param("i", $id);
        $stmtM->execute();
        $m = $stmtM->get_result()->fetch_assoc();
        if (!$m) throw new PesananError('Salah satu menu sudah tidak tersedia.');
        $stmtS->bind_param("iii", $qty, $id, $qty);
        $stmtS->execute();
        if ($stmtS->affected_rows < 1) throw new PesananError('Stok "' . $m['nama_makanan'] . '" tidak mencukupi.');
        $sub = (int)$m['harga'] * $qty;     // harga selalu dari database
        $subtotal += $sub;
        $baris[] = [$id, $m['nama_makanan'], (int)$m['harga'], $qty, $sub];
    }

    // promo, pajak, total — dihitung ulang di server
    $diskon = 0; $kodeSimpan = null;
    if ($kodePromo !== '') {
        $promo = cari_promo($connection, $kodePromo);
        if (!$promo) throw new PesananError('Kode promo tidak valid atau sudah kedaluwarsa.');
        if ($subtotal < (int)$promo['min_belanja']) throw new PesananError('Promo ' . $kodePromo . ' butuh minimal belanja ' . rupiah($promo['min_belanja']) . '.');
        $diskon = hitung_diskon($promo, $subtotal);
        $kodeSimpan = $promo['kode'];
    }
    $pajak = hitung_pajak($subtotal - $diskon);
    $total = $subtotal - $diskon + $pajak;

    $idUser = $_SESSION['id_user'] ?? null;
    $stmt = $connection->prepare("INSERT INTO pesanan (nama_pelanggan, nomor_meja, tipe_pesanan, catatan, subtotal, diskon, kode_promo, pajak, total, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiisiii", $nama, $meja, $tipe, $catatan, $subtotal, $diskon, $kodeSimpan, $pajak, $total, $idUser);
    $stmt->execute();
    $idPesanan = $connection->insert_id;
    $kode = 'PSN' . date('ymd') . str_pad($idPesanan, 4, '0', STR_PAD_LEFT);
    $stmt = $connection->prepare("UPDATE pesanan SET kode_pesanan = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $kode, $idPesanan);
    $stmt->execute();

    $stmtD = $connection->prepare("INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_menu, harga, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($baris as $b) {
        $stmtD->bind_param("iisiii", $idPesanan, $b[0], $b[1], $b[2], $b[3], $b[4]);
        $stmtD->execute();
    }
    $connection->commit();
    json_out(['sukses' => true, 'id_pesanan' => $idPesanan, 'kode' => $kode, 'total' => $total]);
} catch (PesananError $ex) {
    $connection->rollback();
    json_out(['sukses' => false, 'pesan' => $ex->getMessage()]);
} catch (Throwable $ex) {
    $connection->rollback();
    json_out(['sukses' => false, 'pesan' => 'Terjadi kesalahan server, coba lagi.'], 500);
}
