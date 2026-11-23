<?php
include "koneksi.php";

// Halaman ini hanya bisa dibuka kalau belum ada admin sama sekali
$cek = mysqli_query($connection, "SELECT COUNT(*) AS jumlah FROM users WHERE role = 'admin'");
$data = mysqli_fetch_assoc($cek);
if ($data['jumlah'] > 0) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up Admin - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-2">Sign Up Admin</h4>
                    <p class="text-center text-muted small">Pembuatan akun admin pertama</p>
                    <?php if (isset($_GET["pesan"])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET["pesan"]); ?></div>
                    <?php endif; ?>
                    <form action="proses_daftar.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    </form>
                    <div class="text-center mt-3"><a href="index.php">Kembali ke Login</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
