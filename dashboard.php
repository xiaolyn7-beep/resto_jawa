<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
$result = mysqli_query($connection, "SELECT * FROM menu ORDER BY id_menu DESC");
$halaman = 'menu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Jawa Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include "navbar.php"; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Menu Restoran Khas Jawa</h3>
        <a href="tambah_menu.php" class="btn btn-primary">+ Tambah Menu Baru</a>
    </div>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-success">Aksi data menu (<?php echo htmlspecialchars($_GET['pesan']); ?>) berhasil dijalankan!</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th><th>Gambar</th><th>Restoran</th><th>Nama Menu</th>
                        <th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['gambar']); ?>" width="60" height="60" style="object-fit:cover" class="rounded" alt="">
                            <?php else: ?><span class="text-muted small">-</span><?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['nama_restoran']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nama_makanan']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['kategori']); ?></span></td>
                        <td><?php echo 'Rp ' . number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo (int)$row['stok']; ?></td>
                        <td>
                            <a href="edit_menu.php?id=<?php echo (int)$row['id_menu']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus_menu.php?id=<?php echo (int)$row['id_menu']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus menu ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
