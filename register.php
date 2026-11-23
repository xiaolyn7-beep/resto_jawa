<?php
// Hanya untuk membuat ADMIN PERTAMA. Setelah ada user, akun baru dibuat admin lewat pengguna.php
require "koneksi.php";
require "fungsi.php";
$jumlahUser = (int)$connection->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
if ($jumlahUser > 0) { header("Location: index.php"); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $u)) $error = 'Username 3-50 karakter (huruf, angka, _ atau .).';
    elseif (strlen($p) < 6) $error = 'Password minimal 6 karakter.';
    else {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->bind_param("ss", $u, $hash);
        $stmt->execute();
        header("Location: index.php?pesan=daftar_berhasil"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Admin Pertama</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrap"><div class="auth-card panel p-4">
    <h4 class="fw-bold text-center mb-1">Buat Admin Pertama</h4>
    <p class="text-center text-muted small mb-4">Akun ini otomatis berperan sebagai <b>Admin</b>.</p>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
        <button class="btn btn-or w-100">Buat Akun Admin</button>
    </form>
</div></div>
</body>
</html>
