<?php
require "fungsi.php";
wajib_admin();
if (!empty($_GET['error'])) $_SESSION['flash'] = ['danger', $_GET['error']];
$judul = 'Tambah Menu'; $halaman_aktif = 'menu';
include "layout_atas.php";
?>
<div class="panel" style="max-width:640px">
    <div class="panel-title">Tambah Menu Khas Jawa</div>
    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3"><label class="form-label">Nama Makanan/Minuman</label><input type="text" name="nama_makanan" class="form-control" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label">Kategori</label>
            <select name="kategori" class="form-select"><?php foreach (KATEGORI_MENU as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div>
        <div class="row">
            <div class="col-6 mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" min="0" class="form-control" required></div>
            <div class="col-6 mb-3"><label class="form-label">Stok</label><input type="number" name="stok" min="0" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
        <div class="mb-3">
            <label class="form-label">Gambar Menu <small class="text-muted">(JPG/PNG/WEBP, maks 2 MB)</small></label>
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/jpeg,image/png,image/webp">
            <img id="preview" class="img-thumbnail mt-2" style="display:none;max-height:160px" alt="">
        </div>
        <button type="submit" class="btn btn-or">Simpan</button>
        <a href="dashboard.php" class="btn btn-soft">Batal</a>
    </form>
</div>
<script>
document.getElementById('gambar').addEventListener('change', function () {
    const p = document.getElementById('preview');
    if (this.files[0]) { p.src = URL.createObjectURL(this.files[0]); p.style.display = 'block'; } else p.style.display = 'none';
});
</script>
<?php include "layout_bawah.php"; ?>
