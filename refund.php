<?php
// Refund item makanan yang tidak memenuhi standar — KHUSUS ADMIN
require "koneksi.php";
require "fungsi.php";
wajib_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $connection->begin_transaction();
        $stmt = $connection->prepare("SELECT p.*, pb.metode FROM pesanan p JOIN pembayaran pb ON pb.id_pesanan = p.id_pesanan WHERE p.id_pesanan = ? FOR UPDATE");
        $stmt->bind_param("i", $id); $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if (!$p || $p['status_pembayaran'] !== 'Lunas') throw new PesananError('Hanya pesanan yang sudah lunas yang bisa direfund.');

        $kategori = $_POST['kategori'] ?? '';
        $alasan   = trim($_POST['alasan'] ?? '');
        if (!in_array($kategori, ALASAN_REFUND, true)) throw new PesananError('Pilih kategori alasan refund.');
        if (mb_strlen($alasan) < 5) throw new PesananError('Jelaskan alasan refund (minimal 5 karakter).');

        $qtyIn = $_POST['qty'] ?? [];
        $rasio = $p['subtotal'] > 0 ? $p['total'] / $p['subtotal'] : 1;   // diskon & pajak ikut dikembalikan secara proporsional
        $stmtD = $connection->prepare("SELECT * FROM detail_pesanan WHERE id_detail = ? AND id_pesanan = ? FOR UPDATE");
        $stmtU = $connection->prepare("UPDATE detail_pesanan SET qty_refund = qty_refund + ? WHERE id_detail = ?");
        $stmtI = $connection->prepare("INSERT INTO refund (id_pesanan, id_detail, nama_menu, qty, nominal, kategori_alasan, alasan, metode_refund, id_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $totalBaru = 0; $adaItem = false; $sisaBisa = (int)$p['total'] - (int)$p['total_refund'];
        $idAdmin = $_SESSION['id_user'] ?? null;

        foreach ($qtyIn as $idDetail => $q) {
            $idDetail = (int)$idDetail; $q = (int)$q;
            if ($q <= 0) continue;
            $stmtD->bind_param("ii", $idDetail, $id); $stmtD->execute();
            $d = $stmtD->get_result()->fetch_assoc();
            if (!$d) throw new PesananError('Item tidak ditemukan pada pesanan ini.');
            if ($q > $d['qty'] - $d['qty_refund']) throw new PesananError('Jumlah refund "' . $d['nama_menu'] . '" melebihi sisa porsi.');
            $nominal = (int)round($q * $d['harga'] * $rasio);
            $nominal = min($nominal, $sisaBisa - $totalBaru);
            $stmtU->bind_param("ii", $q, $idDetail); $stmtU->execute();
            $stmtI->bind_param("iisiisssi", $id, $idDetail, $d['nama_menu'], $q, $nominal, $kategori, $alasan, $p['metode'], $idAdmin);
            $stmtI->execute();
            $totalBaru += $nominal; $adaItem = true;
        }
        if (!$adaItem) throw new PesananError('Pilih minimal satu item yang akan direfund.');

        $stmt = $connection->prepare("UPDATE pesanan SET total_refund = total_refund + ? WHERE id_pesanan = ?");
        $stmt->bind_param("ii", $totalBaru, $id); $stmt->execute();
        $connection->commit();

        $petunjuk = ['Tunai' => 'Kembalikan uang tunai kepada pelanggan.', 'EDC' => 'Lakukan void/refund di mesin EDC.', 'QRIS' => 'Proses pengembalian dana QRIS melalui aplikasi merchant.'][$p['metode']];
        $_SESSION['flash'] = ['success', 'Refund ' . rupiah($totalBaru) . ' berhasil dicatat. ' . $petunjuk];
    } catch (PesananError $ex) {
        $connection->rollback();
        $_SESSION['flash'] = ['danger', $ex->getMessage()];
    }
    header("Location: refund.php?id=$id"); exit();
}

$stmt = $connection->prepare("SELECT p.*, pb.metode FROM pesanan p LEFT JOIN pembayaran pb ON pb.id_pesanan = p.id_pesanan WHERE p.id_pesanan = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header("Location: kasir_pesanan.php"); exit(); }
$stmt = $connection->prepare("SELECT * FROM detail_pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt = $connection->prepare("SELECT r.*, u.username AS admin FROM refund r LEFT JOIN users u ON u.id = r.id_admin WHERE r.id_pesanan = ? ORDER BY r.id_refund DESC");
$stmt->bind_param("i", $id); $stmt->execute();
$riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rasio = $p['subtotal'] > 0 ? $p['total'] / $p['subtotal'] : 1;
$bisa = $p['status_pembayaran'] === 'Lunas' && $p['total_refund'] < $p['total'];
$judul = 'Refund'; $halaman_aktif = 'pesanan';
include "layout_atas.php";
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-title d-flex justify-content-between"><span>Refund — <?= e($p['kode_pesanan']) ?></span>
                <span class="badge-soft bs-green"><?= e($p['metode'] ?? '-') ?> • <?= rupiah($p['total']) ?></span></div>
            <?php if (!$bisa): ?>
                <div class="alert alert-warning">Pesanan ini <?= $p['status_pembayaran'] !== 'Lunas' ? 'belum lunas' : 'sudah direfund penuh' ?>, tidak ada yang bisa direfund.</div>
            <?php else: ?>
            <form method="POST" id="formRefund" onsubmit="return confirm('Proses refund ini? Tindakan tidak dapat dibatalkan.')">
                <input type="hidden" name="id" value="<?= $id ?>">
                <table class="table align-middle">
                    <thead><tr><th>Menu</th><th>Harga</th><th>Dipesan</th><th>Sudah direfund</th><th style="width:90px">Refund</th></tr></thead>
                    <tbody>
                    <?php foreach ($detail as $d): $sisa = $d['qty'] - $d['qty_refund']; ?>
                    <tr>
                        <td><?= e($d['nama_menu']) ?></td><td><?= rupiah($d['harga']) ?></td><td><?= (int)$d['qty'] ?></td><td><?= (int)$d['qty_refund'] ?></td>
                        <td><input type="number" class="form-control form-control-sm qty-refund" name="qty[<?= (int)$d['id_detail'] ?>]" min="0" max="<?= $sisa ?>" value="0" data-harga="<?= (int)$d['harga'] ?>" <?= $sisa <= 0 ? 'disabled' : '' ?>></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="mb-3"><label class="form-label">Kategori Masalah</label>
                    <select name="kategori" class="form-select" required><option value="">Pilih alasan...</option>
                        <?php foreach (ALASAN_REFUND as $a): ?><option><?= e($a) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Keterangan / Hasil Pengecekan</label>
                    <textarea name="alasan" class="form-control" rows="2" maxlength="255" required placeholder="mis. Nasi terlalu keras, sudah dicek di dapur"></textarea></div>
                <div class="p-3 rounded-3 d-flex justify-content-between mb-3" style="background:var(--or-l)"><span>Estimasi nominal refund (termasuk pajak/diskon proporsional)</span><strong id="estimasi">Rp 0</strong></div>
                <button class="btn btn-or w-100">Proses Refund</button>
            </form>
            <script>
            const RASIO = <?= json_encode($rasio) ?>;
            const Rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
            function hitung() {
                let t = 0; document.querySelectorAll('.qty-refund').forEach(i => t += (parseInt(i.value) || 0) * i.dataset.harga * RASIO);
                document.getElementById('estimasi').textContent = Rp(Math.round(t));
            }
            document.querySelectorAll('.qty-refund').forEach(i => i.addEventListener('input', hitung));
            </script>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-title">Riwayat Refund</div>
            <?php if (!$riwayat): ?><p class="text-muted mb-0">Belum ada refund untuk pesanan ini.</p><?php endif; ?>
            <?php foreach ($riwayat as $r): ?>
                <div class="border rounded-3 p-2 mb-2">
                    <div class="d-flex justify-content-between"><strong><?= (int)$r['qty'] ?>× <?= e($r['nama_menu']) ?></strong><span class="text-danger fw-semibold">−<?= rupiah($r['nominal']) ?></span></div>
                    <div class="small"><span class="badge-soft bs-orange"><?= e($r['kategori_alasan']) ?></span></div>
                    <div class="small text-muted mt-1"><?= e($r['alasan']) ?></div>
                    <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?> • oleh <?= e($r['admin'] ?? '-') ?></div>
                </div>
            <?php endforeach; ?>
            <a href="kasir_pesanan.php" class="btn btn-soft w-100 mt-2">Kembali ke Pesanan</a>
        </div>
    </div>
</div>
<?php include "layout_bawah.php"; ?>
