<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'tambah') {
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $tipe = ($_POST['tipe'] ?? '') === 'nominal' ? 'nominal' : 'persen';
            $nilai = (int)($_POST['nilai'] ?? 0); $min = max(0, (int)($_POST['min_belanja'] ?? 0));
            $sampai = ($_POST['berlaku_sampai'] ?? '') !== '' ? $_POST['berlaku_sampai'] : null;
            if (!preg_match('/^[A-Z0-9]{3,30}$/', $kode)) throw new PesananError('Kode promo 3-30 huruf/angka tanpa spasi.');
            if ($nilai <= 0 || ($tipe === 'persen' && $nilai > 100)) throw new PesananError('Nilai promo tidak valid.');
            try {
                $stmt = $connection->prepare("INSERT INTO promo (kode, tipe, nilai, min_belanja, berlaku_sampai) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiis", $kode, $tipe, $nilai, $min, $sampai);
                $stmt->execute();
            } catch (mysqli_sql_exception $ex) { throw new PesananError('Kode promo sudah ada.'); }
            $_SESSION['flash'] = ['success', "Promo $kode ditambahkan."];
        } elseif ($aksi === 'toggle') {
            $stmt = $connection->prepare("UPDATE promo SET aktif = 1 - aktif WHERE id_promo = ?");
            $stmt->bind_param("i", $_POST['id']); $stmt->execute();
        } elseif ($aksi === 'hapus') {
            $stmt = $connection->prepare("DELETE FROM promo WHERE id_promo = ?");
            $stmt->bind_param("i", $_POST['id']); $stmt->execute();
        }
    } catch (PesananError $ex) { $_SESSION['flash'] = ['danger', $ex->getMessage()]; }
    header("Location: promo.php"); exit();
}
$promos = $connection->query("SELECT * FROM promo ORDER BY id_promo DESC")->fetch_all(MYSQLI_ASSOC);
$judul = 'Promo'; $halaman_aktif = 'promo';
include "layout_atas.php";
?>
<div class="row g-3">
    <div class="col-lg-4"><div class="panel">
        <div class="panel-title">Tambah Kode Promo</div>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="mb-3"><label class="form-label">Kode</label><input type="text" name="kode" class="form-control" style="text-transform:uppercase" required></div>
            <div class="row g-2 mb-3">
                <div class="col-6"><label class="form-label">Tipe</label><select name="tipe" class="form-select"><option value="persen">Persen (%)</option><option value="nominal">Nominal (Rp)</option></select></div>
                <div class="col-6"><label class="form-label">Nilai</label><input type="number" name="nilai" min="1" class="form-control" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Minimal Belanja (Rp)</label><input type="number" name="min_belanja" min="0" value="0" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Berlaku Sampai (opsional)</label><input type="date" name="berlaku_sampai" class="form-control"></div>
            <button class="btn btn-or w-100">Simpan</button>
        </form>
    </div></div>
    <div class="col-lg-8"><div class="panel">
        <div class="panel-title">Daftar Promo</div>
        <table class="table align-middle mb-0">
            <thead><tr><th>Kode</th><th>Potongan</th><th>Min. Belanja</th><th>Berlaku</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($promos as $pr): ?>
            <tr>
                <td class="fw-semibold"><?= e($pr['kode']) ?></td>
                <td><?= $pr['tipe'] === 'persen' ? (int)$pr['nilai'] . '%' : rupiah($pr['nilai']) ?></td>
                <td><?= rupiah($pr['min_belanja']) ?></td>
                <td><?= $pr['berlaku_sampai'] ? date('d/m/Y', strtotime($pr['berlaku_sampai'])) : 'Tanpa batas' ?></td>
                <td><span class="badge-soft <?= $pr['aktif'] ? 'bs-green' : 'bs-gray' ?>"><?= $pr['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-end"><div class="d-flex gap-1 justify-content-end">
                    <form method="POST"><input type="hidden" name="aksi" value="toggle"><input type="hidden" name="id" value="<?= (int)$pr['id_promo'] ?>"><button class="btn btn-soft btn-sm"><?= $pr['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
                    <form method="POST" onsubmit="return confirm('Hapus promo ini?')"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$pr['id_promo'] ?>"><button class="btn btn-soft btn-sm text-danger">Hapus</button></form>
                </div></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
<?php include "layout_bawah.php"; ?>
