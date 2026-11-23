<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Login Admin</h4>
                    <?php if (isset($_GET["pesan"]) && $_GET["pesan"] == "salah"): ?>
                        <div class="alert alert-danger">Username atau password salah!</div>
                    <?php elseif (isset($_GET["pesan"]) && $_GET["pesan"] == "daftar_berhasil"): ?>
                        <div class="alert alert-success">Pendaftaran berhasil! Silakan login.</div>
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
                    <div class="text-center mt-3">
                        <a href="register.php">Daftar Akun</a> | <a href="katalog.php">Lihat Katalog Publik</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>