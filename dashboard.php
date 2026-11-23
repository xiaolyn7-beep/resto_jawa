<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();
$result = $connection->query("SELECT * FROM menu ORDER BY id_menu DESC");
$pesan = ['tambah' => 'Menu baru berhasil ditambahkan.', 'edit' => 'Menu berhasil diperbarui.', 'hapus' => 'Menu berhasil dihapus.'];
if (isset($_GET['pesan'], $pesan[$_GET['pesan']])) $_SESSION['flash'] = ['success', $pesan[$_GET['pesan']]];
$judul = 'Kelola Menu'; $halaman_aktif = 'menu';
include "layout_atas.php";
?>
<div class="panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="panel-title mb-0">Daftar Menu Khas Jawa</div>
        <a href="tambah_menu.php" class="btn btn-or"><i class="bi bi-plus-lg"></i> Tambah Menu</a>
    </div>
    <table class="table align-middle mb-0">
        <thead><tr><th>Gambar</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th></th></tr></thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): $img = url_gambar($row['gambar']); ?>
        <tr>
            <td><div class="mc-img" style="width:60px;aspect-ratio:1;margin:0;font-size:1.6rem"><?php if ($img): ?><img src="<?= e($img) ?>" alt=""><?php else: ?>🍛<?php endif; ?></div></td>
            <td class="fw-semibold"><?= e($row['nama_makanan']) ?></td>
            <td><span class="badge-soft bs-gray"><?= e($row['kategori']) ?></span></td>
            <td><?= rupiah($row['harga']) ?></td>
            <td><?= (int)$row['stok'] ?></td>
            <td class="text-end">
                <a href="edit_menu.php?id=<?= (int)$row['id_menu'] ?>" class="btn btn-soft btn-sm">Edit</a>
                <a href="hapus_menu.php?id=<?= (int)$row['id_menu'] ?>" class="btn btn-soft btn-sm text-danger" onclick="return confirm('Yakin hapus menu ini?');">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include "layout_bawah.php"; ?>
