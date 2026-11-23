<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($connection, "SELECT * FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$data) { header("Location: dashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning"><h5 class="mb-0">Edit Menu Khas Jawa</h5></div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_menu" value="<?php echo (int)$data['id_menu']; ?>">
                        <div class="mb-3"><label class="form-label">Nama Restoran</label><input type="text" name="nama_restoran" class="form-control" value="<?php echo htmlspecialchars($data['nama_restoran']); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Nama Makanan/Minuman</label><input type="text" name="nama_makanan" class="form-control" value="<?php echo htmlspecialchars($data['nama_makanan']); ?>" required></div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select">
                                <?php foreach (['Makanan', 'Minuman', 'Cemilan'] as $k): ?>
                                    <option value="<?php echo $k; ?>" <?php echo ($data['kategori'] == $k) ? 'selected' : ''; ?>><?php echo $k; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Harga (Rp)</label><input type="number" min="0" name="harga" class="form-control" value="<?php echo (int)$data['harga']; ?>" required></div>
                        <div class="mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stok" class="form-control" value="<?php echo (int)$data['stok']; ?>" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea></div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Menu</label>
                            <?php if ($data['gambar']): ?>
                                <div class="mb-2"><img src="uploads/<?php echo htmlspecialchars($data['gambar']); ?>" height="100" class="rounded border" alt=""></div>
                            <?php endif; ?>
                            <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti gambar. Maks 2 MB (JPG/PNG/GIF/WEBP).</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
