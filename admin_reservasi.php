<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_id'])) {
    $stmt = $connection->prepare("UPDATE reservasi SET status = 'Dikonfirmasi' WHERE id_reservasi = ?");
    $stmt->bind_param("i", $_POST['konfirmasi_id']);
    $stmt->execute();
    header("Location: admin_reservasi.php");
    exit();
}
$data = $connection->query("SELECT * FROM reservasi ORDER BY id_reservasi DESC");
$judul = 'Reservasi'; $halaman_aktif = 'reservasi';
include "layout_atas.php";
?>
<div class="row g-3">
    <div class="col-lg-4"><div class="panel">
        <div class="panel-title">Tambah Reservasi</div>
        <form action="proses_reservasi.php" method="POST">
            <div class="mb-3"><label class="form-label">Nama Pemesan</label><input type="text" name="nama_pemesan" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Jumlah Orang</label><input type="number" name="jumlah_orang" min="1" max="20" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Tanggal Kedatangan</label><input type="date" name="tanggal_reservasi" min="<?= date('Y-m-d') ?>" class="form-control" required></div>
            <button class="btn btn-or w-100">Simpan Reservasi</button>
        </form>
    </div></div>
    <div class="col-lg-8"><div class="panel">
        <div class="panel-title">Daftar Reservasi Meja</div>
        <table class="table align-middle mb-0">
            <thead><tr><th>No</th><th>Pemesan</th><th>Jumlah</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="fw-semibold"><?= e($r['nama_pemesan']) ?></td>
                <td><?= (int)$r['jumlah_orang'] ?> orang</td>
                <td><?= e($r['tanggal_reservasi']) ?></td>
                <td><span class="badge-soft <?= $r['status'] === 'Dikonfirmasi' ? 'bs-green' : 'bs-orange' ?>"><?= e($r['status']) ?></span></td>
                <td><?php if ($r['status'] === 'Pending'): ?>
                    <form method="POST"><input type="hidden" name="konfirmasi_id" value="<?= (int)$r['id_reservasi'] ?>"><button class="btn btn-or btn-sm py-1">Konfirmasi</button></form>
                <?php else: ?><span class="text-muted small">Selesai</span><?php endif; ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div></div>
</div>
<?php include "layout_bawah.php"; ?>
