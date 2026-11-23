<?php
require "koneksi.php";
require "fungsi.php";
if (sudah_login()) { header("Location: kasir.php"); exit(); }
$jumlahUser = (int)$connection->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= e(NAMA_RESTORAN) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrap"><div class="auth-card panel p-4">
    <div class="text-center mb-4">
        <span class="brand-logo d-inline-flex mb-2" style="width:52px;height:52px;font-size:1.6rem"><i class="bi bi-cup-hot-fill"></i></span>
        <h4 class="fw-bold mb-0"><?= e(NAMA_RESTORAN) ?></h4>
        <small class="text-muted">Sistem Kasir Restoran Jawa</small>
    </div>
    <?php if (($_GET['pesan'] ?? '') === 'salah'): ?><div class="alert alert-danger py-2">Username atau password salah.</div>
    <?php elseif (($_GET['pesan'] ?? '') === 'daftar_berhasil'): ?><div class="alert alert-success py-2">Akun admin dibuat. Silakan login.</div><?php endif; ?>
    <form action="cek_login.php" method="POST">
        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-or w-100">Masuk</button>
    </form>
    <?php if ($jumlahUser === 0): ?>
        <div class="text-center mt-3"><a href="register.php" class="text-decoration-none">Belum ada akun — buat admin pertama</a></div>
    <?php endif; ?>
</div></div>
</body>
</html>
