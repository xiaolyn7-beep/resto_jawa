<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
include "koneksi.php";

$stmt = $connection->prepare("SELECT * FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
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
                    <form action="proses_edit.php" method="POST">
                        <input type="hidden" name="id_menu" value="<?php echo $data['id_menu']; ?>">
                        <div class="mb-3"><label class="form-label">Nama Restoran</label><input type="text" name="nama_restoran" class="form-control" value="<?php echo htmlspecialchars($data['nama_restoran']); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Nama Makanan/Minuman</label><input type="text" name="nama_makanan" class="form-control" value="<?php echo htmlspecialchars($data['nama_makanan']); ?>" required></div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="Makanan" <?php echo ($data['kategori']=='Makanan')?'selected':''; ?>>Makanan</option>
                                <option value="Minuman" <?php echo ($data['kategori']=='Minuman')?'selected':''; ?>>Minuman</option>
                                <option value="Cemilan" <?php echo ($data['kategori']=='Cemilan')?'selected':''; ?>>Cemilan</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" class="form-control" value="<?php echo $data['harga']; ?>" required></div>
                        <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" value="<?php echo $data['stok']; ?>" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea></div>
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