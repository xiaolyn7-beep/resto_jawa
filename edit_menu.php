<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

$stmt = $connection->prepare("SELECT * FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$data) { header("Location: dashboard.php"); exit(); }
$img = url_gambar($data['gambar']);
if (!empty($_GET['error'])) $_SESSION['flash'] = ['danger', $_GET['error']];
$judul = 'Edit Menu'; $halaman_aktif = 'menu';
include "layout_atas.php";
?>
<div class="panel" style="max-width:640px">
    <div class="panel-title">Edit Menu Khas Jawa</div>
    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_menu" value="<?= (int)$data['id_menu'] ?>">
        <div class="mb-3"><label class="form-label">Nama Makanan/Minuman</label><input type="text" name="nama_makanan" class="form-control" value="<?= e($data['nama_makanan']) ?>" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label">Kategori</label>
            <select name="kategori" class="form-select"><?php foreach (KATEGORI_MENU as $k): ?><option <?= $data['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
        <div class="row">
            <div class="col-6 mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" min="0" class="form-control" value="<?= (int)$data['harga'] ?>" required></div>
            <div class="col-6 mb-3"><label class="form-label">Stok</label><input type="number" name="stok" min="0" class="form-control" value="<?= (int)$data['stok'] ?>" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"><?= e($data['deskripsi']) ?></textarea></div>
        <div class="mb-3">
            <label class="form-label">Gambar Menu</label>
            <div class="mb-2">
                <img id="preview" <?= $img ? 'src="' . e($img) . '"' : 'style="display:none"' ?> class="img-thumbnail" style="max-height:160px" alt="">
                <?php if (!$img): ?><div id="belum" class="text-muted small">Belum ada gambar.</div><?php endif; ?>
            </div>
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">Pilih file baru untuk mengganti gambar (JPG/PNG/WEBP, maks 2 MB).</small>
            <?php if ($img): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="hapus_gambar" value="1" id="hg"><label class="form-check-label" for="hg">Hapus gambar saat ini</label></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-or">Update</button>
        <a href="dashboard.php" class="btn btn-soft">Batal</a>
    </form>
</div>
<script>
document.getElementById('gambar').addEventListener('change', function () {
    const p = document.getElementById('preview'), b = document.getElementById('belum');
    if (this.files[0]) { p.src = URL.createObjectURL(this.files[0]); p.style.display = 'block'; if (b) b.style.display = 'none'; }
});
</script>
<?php include "layout_bawah.php"; ?>
