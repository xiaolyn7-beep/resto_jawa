<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
$result = $connection->query("SELECT * FROM menu ORDER BY id_menu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Nusantara Food Hub</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link active" href="dashboard.php">Kelola Menu</a>
            <a class="nav-link" href="admin_reservasi.php">Data Reservasi</a>
            <a class="nav-link" href="katalog.php" target="_blank">Lihat Web Publik</a>
        </div>
        <div class="d-flex align-items-center text-white">
            <span class="me-3">Halo, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

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
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Restoran</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['nama_restoran']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nama_makanan']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $row['kategori']; ?></span></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td>
                            <a href="edit_menu.php?id=<?php echo $row['id_menu']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus_menu.php?id=<?php echo $row['id_menu']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus menu ini?');">Hapus</a>
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