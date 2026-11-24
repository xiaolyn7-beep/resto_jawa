<?php
session_start();
include "koneksi.php";

// cek session admin atau kasir
if (isset($_SESSION["status_login"])) {
    if ($_SESSION["role"] == "admin") {
        header("Location: dashboard.php");
    } else {
        header("Location: kasir.php");
    }
    exit();
}

// pengecekan akun admin
$cek = mysqli_query($connection, "SELECT COUNT(*) AS jumlah FROM users WHERE role = 'admin'");
$data = mysqli_fetch_assoc($cek);
$belum_ada_admin = ($data['jumlah'] == 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Jawa Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Login Jawa Food</h4>
                    <?php if (isset($_GET["pesan"]) && $_GET["pesan"] == "salah"): ?>
                        <div class="alert alert-danger">Username atau password salah!</div>
                    <?php elseif (isset($_GET["pesan"]) && $_GET["pesan"] == "daftar_berhasil"): ?>
                        <div class="alert alert-success">Akun admin berhasil dibuat. Silakan login.</div>
                    <?php endif; ?>
                    <form action="cek_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <?php if ($belum_ada_admin): ?>
                        <div class="text-center mt-3">
                            Belum ada akun admin? <a href="daftar.php">Sign Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
