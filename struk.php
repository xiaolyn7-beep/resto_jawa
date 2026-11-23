<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $connection->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header("Location: kasir_pesanan.php"); exit(); }

$stmt = $connection->prepare("SELECT * FROM detail_pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $connection->prepare("SELECT pb.*, u.username AS kasir FROM pembayaran pb LEFT JOIN users u ON u.id = pb.id_kasir WHERE pb.id_pesanan = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$b = $stmt->get_result()->fetch_assoc();

$stmt = $connection->prepare("SELECT * FROM refund WHERE id_pesanan = ? ORDER BY id_refund");
$stmt->bind_param("i", $id); $stmt->execute();
$refunds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$baru = isset($_GET['baru']) && $b;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk <?= e($p['kode_pesanan']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .struk{max-width:320px;margin:20px auto;background:#fff;padding:16px;font-family:'Courier New',monospace;font-size:13px;border:1px dashed #999;border-radius:6px}
        .struk hr{border-top:1px dashed #666;margin:8px 0;opacity:1}
        .baris{display:flex;justify-content:space-between}
        @media print{.no-print{display:none!important} body{background:#fff!important} .struk{border:0;margin:0}}
    </style>
</head>
<body>
<div class="container no-print mt-3" style="max-width:420px">
    <?php if ($baru): ?>
    <div class="alert alert-success text-center">
        <div>Pembayaran <strong><?= e($b['metode']) ?></strong> berhasil ✅</div>
        <?php if ($b['metode'] === 'Tunai'): ?><div class="fs-4 fw-bold">Kembalian: <?= rupiah($b['kembalian']) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="d-grid gap-2">
        <button class="btn btn-or" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Struk</button>
        <a href="kasir.php" class="btn btn-soft">Transaksi Baru</a>
        <a href="kasir_pesanan.php" class="btn btn-link text-muted">Daftar Pesanan</a>
    </div>
</div>

<div class="struk">
    <div class="text-center"><strong><?= e(NAMA_RESTORAN) ?></strong><br>Kuliner Khas Jawa</div>
    <hr>
    <div><?= e($p['kode_pesanan']) ?></div>
    <div><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
    <div>Pelanggan: <?= e($p['nama_pelanggan']) ?></div>
    <div><?= e($p['tipe_pesanan']) ?><?= ($p['nomor_meja'] !== null && $p['nomor_meja'] !== '') ? ' - Meja ' . e($p['nomor_meja']) : '' ?></div>
    <?php if ($b): ?><div>Kasir: <?= e($b['kasir'] ?? '-') ?></div><?php endif; ?>
    <hr>
    <?php foreach ($detail as $d): ?>
        <div><?= e($d['nama_menu']) ?></div>
        <div class="baris"><span><?= (int)$d['qty'] ?> x <?= number_format($d['harga'], 0, ',', '.') ?></span><span><?= number_format($d['subtotal'], 0, ',', '.') ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="baris"><span>Subtotal</span><span><?= rupiah($p['subtotal']) ?></span></div>
    <?php if ($p['diskon'] > 0): ?><div class="baris"><span>Diskon <?= e($p['kode_promo']) ?></span><span>-<?= rupiah($p['diskon']) ?></span></div><?php endif; ?>
    <div class="baris"><span>Pajak <?= PAJAK_PERSEN ?>%</span><span><?= rupiah($p['pajak']) ?></span></div>
    <div class="baris"><strong>TOTAL</strong><strong><?= rupiah($p['total']) ?></strong></div>
    <?php if ($b): ?>
        <div class="baris"><span>Bayar (<?= e($b['metode']) ?>)</span><span><?= rupiah($b['jumlah_bayar']) ?></span></div>
        <div class="baris"><span>Kembali</span><span><?= rupiah($b['kembalian']) ?></span></div>
        <?php if ($b['nomor_referensi']): ?><div>Ref: <?= e($b['nomor_referensi']) ?></div><?php endif; ?>
        <?php if ($refunds): ?>
            <hr><div><strong>REFUND</strong></div>
            <?php foreach ($refunds as $r): ?><div class="baris"><span><?= (int)$r['qty'] ?>x <?= e($r['nama_menu']) ?></span><span>-<?= number_format($r['nominal'], 0, ',', '.') ?></span></div><?php endforeach; ?>
            <div class="baris"><strong>Total Refund</strong><strong>-<?= rupiah($p['total_refund']) ?></strong></div>
        <?php endif; ?>
        <hr><div class="text-center">LUNAS<br>Matur nuwun! 🙏</div>
    <?php else: ?>
        <hr><div class="text-center"><strong>BELUM LUNAS</strong></div>
        <div class="text-center no-print mt-2"><a href="pembayaran.php?id=<?= $id ?>" class="btn btn-sm btn-or">Bayar Sekarang</a></div>
    <?php endif; ?>
</div>
</body>
</html>
