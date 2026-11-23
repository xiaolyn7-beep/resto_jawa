<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();

$tgl   = (isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal'])) ? $_GET['tanggal'] : date('Y-m-d');
$bayar = in_array($_GET['bayar'] ?? '', ['Belum Bayar', 'Lunas'], true) ? $_GET['bayar'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idp = (int)($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';
    try {
        $connection->begin_transaction();
        $stmt = $connection->prepare("SELECT status_pesanan, status_pembayaran FROM pesanan WHERE id_pesanan = ? FOR UPDATE");
        $stmt->bind_param("i", $idp); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) throw new PesananError('Pesanan tidak ditemukan.');

        if ($aksi === 'proses' && $row['status_pesanan'] === 'Baru') {
            $connection->query("UPDATE pesanan SET status_pesanan='Diproses' WHERE id_pesanan=$idp");
        } elseif ($aksi === 'selesai' && $row['status_pesanan'] === 'Diproses') {
            $connection->query("UPDATE pesanan SET status_pesanan='Selesai' WHERE id_pesanan=$idp");
        } elseif ($aksi === 'batal') {
            if ($row['status_pembayaran'] === 'Lunas') throw new PesananError('Pesanan lunas tidak bisa dibatalkan (gunakan refund oleh admin).');
            if ($row['status_pesanan'] === 'Batal') throw new PesananError('Pesanan sudah dibatalkan.');
            $connection->query("UPDATE menu m JOIN detail_pesanan d ON d.id_menu = m.id_menu SET m.stok = m.stok + d.qty WHERE d.id_pesanan = $idp");
            $connection->query("UPDATE pesanan SET status_pesanan='Batal' WHERE id_pesanan=$idp");
        }
        $connection->commit();
        $_SESSION['flash'] = ['success', 'Status pesanan diperbarui.'];
    } catch (PesananError $ex) {
        $connection->rollback();
        $_SESSION['flash'] = ['danger', $ex->getMessage()];
    }
    header("Location: kasir_pesanan.php?tanggal=$tgl&bayar=" . urlencode($bayar));
    exit();
}

$sql = "SELECT p.*, pb.metode, GROUP_CONCAT(CONCAT(d.qty, 'x ', d.nama_menu) SEPARATOR ', ') AS ringkas
        FROM pesanan p
        LEFT JOIN detail_pesanan d ON d.id_pesanan = p.id_pesanan
        LEFT JOIN pembayaran pb ON pb.id_pesanan = p.id_pesanan
        WHERE DATE(p.created_at) = ?";
$params = [$tgl]; $tipeBind = "s";
if ($bayar !== '') { $sql .= " AND p.status_pembayaran = ?"; $params[] = $bayar; $tipeBind .= "s"; }
$sql .= " GROUP BY p.id_pesanan, pb.metode ORDER BY p.id_pesanan DESC";
$stmt = $connection->prepare($sql);
$stmt->bind_param($tipeBind, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$belum = 0; $omzet = 0; $refund = 0;
foreach ($rows as $r) {
    if ($r['status_pesanan'] === 'Batal') continue;
    if ($r['status_pembayaran'] === 'Belum Bayar') $belum++;
    else { $omzet += $r['total'] - $r['total_refund']; $refund += $r['total_refund']; }
}
$warna = ['Baru' => 'bs-blue', 'Diproses' => 'bs-orange', 'Selesai' => 'bs-green', 'Batal' => 'bs-gray'];
$judul = 'Pesanan'; $halaman_aktif = 'pesanan';
include "layout_atas.php";
?>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="panel"><div class="text-muted small">Jumlah Pesanan</div><div class="fs-3 fw-bold"><?= count($rows) ?></div></div></div>
    <div class="col-md-3"><div class="panel"><div class="text-muted small">Belum Dibayar</div><div class="fs-3 fw-bold text-danger"><?= $belum ?></div></div></div>
    <div class="col-md-3"><div class="panel"><div class="text-muted small">Pendapatan Bersih</div><div class="fs-3 fw-bold text-success"><?= rupiah($omzet) ?></div></div></div>
    <div class="col-md-3"><div class="panel"><div class="text-muted small">Total Refund</div><div class="fs-3 fw-bold" style="color:var(--or)"><?= rupiah($refund) ?></div></div></div>
</div>

<div class="panel">
    <form class="row g-2 mb-3" method="GET">
        <div class="col-auto"><input type="date" name="tanggal" value="<?= e($tgl) ?>" class="form-control"></div>
        <div class="col-auto"><select name="bayar" class="form-select">
            <option value="">Semua pembayaran</option>
            <option value="Belum Bayar" <?= $bayar === 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
            <option value="Lunas" <?= $bayar === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
        </select></div>
        <div class="col-auto"><button class="btn btn-or">Filter</button></div>
    </form>
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Kode</th><th>Jam</th><th>Pelanggan</th><th>Pesanan</th><th class="text-end">Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada pesanan.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): $lunas = $r['status_pembayaran'] === 'Lunas'; $batal = $r['status_pesanan'] === 'Batal'; ?>
        <tr>
            <td class="fw-semibold"><?= e($r['kode_pesanan']) ?></td>
            <td><?= date('H:i', strtotime($r['created_at'])) ?></td>
            <td><?= e($r['nama_pelanggan']) ?><br><small class="text-muted"><?= e($r['tipe_pesanan']) ?><?= ($r['nomor_meja'] !== null && $r['nomor_meja'] !== '') ? ' • Meja ' . e($r['nomor_meja']) : '' ?></small></td>
            <td class="small" style="max-width:260px"><?= e($r['ringkas']) ?></td>
            <td class="text-end"><?= rupiah($r['total']) ?><?= $r['total_refund'] > 0 ? '<br><small class="text-danger">refund −' . rupiah($r['total_refund']) . '</small>' : '' ?></td>
            <td><span class="badge-soft <?= $lunas ? 'bs-green' : 'bs-red' ?>"><?= e($r['status_pembayaran']) ?></span><?= $r['metode'] ? '<br><small class="text-muted">' . e($r['metode']) . '</small>' : '' ?></td>
            <td><span class="badge-soft <?= $warna[$r['status_pesanan']] ?>"><?= e($r['status_pesanan']) ?></span></td>
            <td><div class="d-flex flex-wrap gap-1">
                <?php if (!$batal): ?>
                    <?php if (!$lunas): ?><a href="pembayaran.php?id=<?= (int)$r['id_pesanan'] ?>" class="btn btn-or btn-sm py-1">Bayar</a>
                    <?php else: ?><a href="struk.php?id=<?= (int)$r['id_pesanan'] ?>" target="_blank" class="btn btn-soft btn-sm">Struk</a><?php endif; ?>
                    <?php if ($r['status_pesanan'] === 'Baru'): ?>
                        <form method="POST"><input type="hidden" name="id" value="<?= (int)$r['id_pesanan'] ?>"><button name="aksi" value="proses" class="btn btn-soft btn-sm">Proses</button></form>
                    <?php elseif ($r['status_pesanan'] === 'Diproses'): ?>
                        <form method="POST"><input type="hidden" name="id" value="<?= (int)$r['id_pesanan'] ?>"><button name="aksi" value="selesai" class="btn btn-soft btn-sm">Selesai</button></form>
                    <?php endif; ?>
                    <?php if (!$lunas): ?>
                        <form method="POST" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')"><input type="hidden" name="id" value="<?= (int)$r['id_pesanan'] ?>"><button name="aksi" value="batal" class="btn btn-soft btn-sm text-danger">Batal</button></form>
                    <?php elseif (is_admin()): ?>
                        <a href="refund.php?id=<?= (int)$r['id_pesanan'] ?>" class="btn btn-soft btn-sm text-danger"><i class="bi bi-arrow-counterclockwise"></i> Refund</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<script>setTimeout(() => { if (!document.hidden) location.reload(); }, 30000);</script>
<?php include "layout_bawah.php"; ?>
