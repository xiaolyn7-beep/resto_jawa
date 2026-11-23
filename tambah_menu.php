<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
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
                        <div class="mb-3"><label class="form-label">Harga (Rp)</label><input type="number" min="0" name="harga" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stok" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Menu</label>
                            <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                            <div class="form-text">Opsional. Format JPG/PNG/GIF/WEBP, maksimal 2 MB.</div>
                        </div>
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
