<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white"><h5 class="mb-0">Tambah Menu Khas Jawa</h5></div>
                <div class="card-body">
                    <form action="proses_tambah.php" method="POST">
                        <div class="mb-3"><label class="form-label">Nama Restoran</label><input type="text" name="nama_restoran" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nama Makanan/Minuman</label><input type="text" name="nama_makanan" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Cemilan">Cemilan</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>