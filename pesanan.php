<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "kasir") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";

$tgl = $_GET['tanggal'] ?? date('Y-m-d');
$st = mysqli_prepare($connection, "SELECT pesanan.*, users.username AS kasir FROM pesanan JOIN users ON users.id = pesanan.id_kasir WHERE DATE(pesanan.tanggal) = ? AND pesanan.id_kasir = ? ORDER BY pesanan.id_pesanan DESC");
mysqli_stmt_bind_param($st, "si", $tgl, $_SESSION['id_user']);
mysqli_stmt_execute($st);
$hasil = mysqli_stmt_get_result($st);
$pesanan = array();
while ($baris = mysqli_fetch_assoc($hasil)) {
    $pesanan[] = $baris;
}
mysqli_stmt_close($st);
$total = array_sum(array_column($pesanan, 'total'));

$sukses = null;
if (isset($_GET['sukses'])) {
    $s = mysqli_prepare($connection, "SELECT * FROM pesanan WHERE id_pesanan = ? AND id_kasir = ?");
    mysqli_stmt_bind_param($s, "ii", $_GET['sukses'], $_SESSION['id_user']);
    mysqli_stmt_execute($s);
    $sukses = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    mysqli_stmt_close($s);
}
$halaman = 'pesanan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include "navbar.php"; ?>
<div class="container mt-4 mb-5">
    <?php if ($sukses): ?>
        <div class="alert alert-success">
            Pesanan <strong><?php echo htmlspecialchars($sukses['kode_pesanan']); ?></strong> berhasil dibayar (<?php echo htmlspecialchars($sukses['metode_bayar']); ?>).
            Total <?php echo 'Rp ' . number_format($sukses['total'], 0, ',', '.'); ?>, dibayar <?php echo 'Rp ' . number_format($sukses['uang_bayar'], 0, ',', '.'); ?>,
            <strong>kembalian <?php echo 'Rp ' . number_format($sukses['kembalian'], 0, ',', '.'); ?></strong>.
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Pesanan Saya</h3>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="tanggal" value="<?php echo htmlspecialchars($tgl); ?>" class="form-control">
            <button class="btn btn-primary">Lihat</button>
        </form>
    </div>
    <p class="text-muted"><?php echo count($pesanan); ?> pesanan &middot; Total: <strong><?php echo 'Rp ' . number_format($total, 0, ',', '.'); ?></strong></p>

    <div class="card shadow-sm"><div class="card-body p-0"><?php if (count($pesanan) == 0): ?>
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
        <?php endif; ?></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
