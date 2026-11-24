<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$st = mysqli_prepare($connection, "SELECT COUNT(*) jml, COALESCE(SUM(total),0) omzet FROM pesanan WHERE DATE(tanggal) BETWEEN ? AND ?");
mysqli_stmt_bind_param($st, "ss", $dari, $sampai);
mysqli_stmt_execute($st);
$ringkas = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);

$st = mysqli_prepare($connection, "SELECT COALESCE(SUM(d.jumlah),0) item FROM detail_pesanan d JOIN pesanan p ON p.id_pesanan = d.id_pesanan WHERE DATE(p.tanggal) BETWEEN ? AND ?");
mysqli_stmt_bind_param($st, "ss", $dari, $sampai);
mysqli_stmt_execute($st);
$hasil = mysqli_stmt_get_result($st);
$baris = mysqli_fetch_assoc($hasil);
$item = $baris['item'];
mysqli_stmt_close($st);

$st = mysqli_prepare($connection, "SELECT d.nama_makanan, SUM(d.jumlah) qty, SUM(d.subtotal) total FROM detail_pesanan d JOIN pesanan p ON p.id_pesanan = d.id_pesanan WHERE DATE(p.tanggal) BETWEEN ? AND ? GROUP BY d.nama_makanan ORDER BY qty DESC LIMIT 5");
mysqli_stmt_bind_param($st, "ss", $dari, $sampai);
mysqli_stmt_execute($st);
$hasil = mysqli_stmt_get_result($st);
$top = array();
while ($baris = mysqli_fetch_assoc($hasil)) {
    $top[] = $baris;
}
mysqli_stmt_close($st);

$st = mysqli_prepare($connection, "SELECT pesanan.*, COALESCE(users.username, '(akun dihapus)') AS kasir FROM pesanan LEFT JOIN users ON users.id = pesanan.id_kasir WHERE DATE(pesanan.tanggal) BETWEEN ? AND ? ORDER BY pesanan.id_pesanan DESC");
mysqli_stmt_bind_param($st, "ss", $dari, $sampai);
mysqli_stmt_execute($st);
$hasil = mysqli_stmt_get_result($st);
$pesanan = array();
while ($baris = mysqli_fetch_assoc($hasil)) {
    $pesanan[] = $baris;
}
mysqli_stmt_close($st);

$halaman = 'laporan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pesanan - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>@media print { nav, .no-print { display: none !important; } .collapse { display: table-row !important; } }</style>
</head>
<body class="bg-light">
<?php include "navbar.php"; ?>
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Laporan Pesanan</h3>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print">Cetak</button>
    </div>

    <form method="GET" class="row g-2 mb-4 no-print">
        <div class="col-auto"><label class="col-form-label">Dari</label></div>
        <div class="col-auto"><input type="date" name="dari" value="<?php echo htmlspecialchars($dari); ?>" class="form-control"></div>
        <div class="col-auto"><label class="col-form-label">Sampai</label></div>
        <div class="col-auto"><input type="date" name="sampai" value="<?php echo htmlspecialchars($sampai); ?>" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div>
    </form>

    <div class="row mb-4">
        <div class="col-md-4 mb-2"><div class="card shadow-sm border-success"><div class="card-body"><div class="text-muted small">Total Pendapatan</div><h4 class="text-success mb-0"><?php echo 'Rp ' . number_format($ringkas['omzet'], 0, ',', '.'); ?></h4></div></div></div>
        <div class="col-md-4 mb-2"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Jumlah Pesanan</div><h4 class="mb-0"><?php echo (int)$ringkas['jml']; ?></h4></div></div></div>
        <div class="col-md-4 mb-2"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Item Terjual</div><h4 class="mb-0"><?php echo (int)$item; ?></h4></div></div></div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">5 Menu Terlaris</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Menu</th><th>Terjual</th><th class="text-end">Pendapatan</th></tr></thead>
                <tbody>
                <?php foreach ($top as $t): ?>
                    <tr><td><?php echo htmlspecialchars($t['nama_makanan']); ?></td><td><?php echo (int)$t['qty']; ?></td><td class="text-end"><?php echo 'Rp ' . number_format($t['total'], 0, ',', '.'); ?></td></tr>
                <?php endforeach; if (!$top): ?><tr><td colspan="3" class="text-muted">Belum ada data.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Daftar Pesanan</div>
        <div class="card-body p-0"><?php if (count($pesanan) == 0): ?>
            <p class="text-muted p-3 mb-0">Belum ada pesanan.</p>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Kode</th><th>Waktu</th><th>Kasir</th><th>Pelanggan</th><th>Metode</th><th class="text-end">Total</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($pesanan as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['kode_pesanan']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($p['kasir']); ?></td>
                        <td><?php echo htmlspecialchars($p['nama_pelanggan']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($p['metode_bayar']); ?></span> <span class="badge bg-success"><?php echo htmlspecialchars($p['status']); ?></span></td>
                        <td class="text-end"><?php echo 'Rp ' . number_format($p['total'], 0, ',', '.'); ?></td>
                        <td><button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#d<?php echo $p['id_pesanan']; ?>">Detail</button></td>
                    </tr>
                    <tr class="collapse" id="d<?php echo $p['id_pesanan']; ?>">
                        <td colspan="7" class="bg-light">
                            <table class="table table-sm mb-2">
                                <thead><tr><th>Menu</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                                <tbody>
                                <?php
                                $id_p = (int)$p['id_pesanan'];
                                $hasil_detail = mysqli_query($connection, "SELECT * FROM detail_pesanan WHERE id_pesanan = $id_p");
                                while ($d = mysqli_fetch_assoc($hasil_detail)):
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($d['nama_makanan']); ?></td>
                                        <td><?php echo 'Rp ' . number_format($d['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $d['jumlah']; ?></td>
                                        <td class="text-end"><?php echo 'Rp ' . number_format($d['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                            <small class="text-muted">Dibayar: <?php echo 'Rp ' . number_format($p['uang_bayar'], 0, ',', '.'); ?> | Kembalian: <?php echo 'Rp ' . number_format($p['kembalian'], 0, ',', '.'); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
