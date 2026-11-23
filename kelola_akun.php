<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";

$notif = ''; $tipe = 'success';
$roles = ['admin', 'kasir'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi     = $_POST['aksi'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $role     = $_POST['role'] ?? 'kasir';
    $password = $_POST['password'] ?? '';
    $diriSendiri = ($username === $_SESSION['username']);

    if (!in_array($role, $roles, true)) $role = 'kasir';

    if ($aksi === 'tambah') {
        $cek = mysqli_prepare($connection, "SELECT 1 FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        $ada = mysqli_num_rows(mysqli_stmt_get_result($cek)) > 0;
        mysqli_stmt_close($cek);

        if ($username === '' || strlen($password) < 6) {
            $notif = "Username wajib diisi dan password minimal 6 karakter."; $tipe = 'danger';
        } elseif ($ada) {
            $notif = "Username sudah dipakai."; $tipe = 'danger';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = mysqli_prepare($connection, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($st, "sss", $username, $hash, $role);
            mysqli_stmt_execute($st); mysqli_stmt_close($st);
            $notif = "Akun $username berhasil ditambahkan.";
        }
    } elseif ($aksi === 'ubah') {
        if ($diriSendiri) $role = 'admin'; // admin tidak boleh menurunkan role dirinya sendiri
        if ($password !== '' && strlen($password) < 6) {
            $notif = "Password baru minimal 6 karakter."; $tipe = 'danger';
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = mysqli_prepare($connection, "UPDATE users SET role = ?, password = ? WHERE username = ?");
                mysqli_stmt_bind_param($st, "sss", $role, $hash, $username);
            } else {
                $st = mysqli_prepare($connection, "UPDATE users SET role = ? WHERE username = ?");
                mysqli_stmt_bind_param($st, "ss", $role, $username);
            }
            mysqli_stmt_execute($st); mysqli_stmt_close($st);
            $notif = "Akun $username berhasil diperbarui.";
        }
    } elseif ($aksi === 'hapus') {
        if ($diriSendiri) {
            $notif = "Anda tidak bisa menghapus akun yang sedang dipakai."; $tipe = 'danger';
        } else {
            $st = mysqli_prepare($connection, "DELETE FROM users WHERE username = ?");
            mysqli_stmt_bind_param($st, "s", $username);
            mysqli_stmt_execute($st); mysqli_stmt_close($st);
            $notif = "Akun $username dihapus.";
        }
    }
}

$users = mysqli_query($connection, "SELECT username, role FROM users ORDER BY role, username");
$halaman = 'akun';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akun - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include "navbar.php"; ?>
<div class="container mt-4">
    <h3 class="mb-3">Kelola Akun</h3>
    <?php if ($notif): ?><div class="alert alert-<?php echo $tipe; ?>"><?php echo htmlspecialchars($notif); ?></div><?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Tambah Akun Baru</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <input type="hidden" name="aksi" value="tambah">
                <div class="col-md-4"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password (min. 6)" minlength="6" required></div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-success w-100">Tambah</button></div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead class="table-dark"><tr><th>Username</th><th>Role</th><th>Password Baru (opsional)</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $i = 0; while ($u = mysqli_fetch_assoc($users)): $i++; $saya = ($u['username'] === $_SESSION['username']); ?>
                    <tr>
                        <td>
                            <form id="f<?php echo $i; ?>" method="POST">
                                <input type="hidden" name="aksi" value="ubah">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($u['username']); ?>">
                            </form>
                            <strong><?php echo htmlspecialchars($u['username']); ?></strong> <?php if ($saya): ?><span class="badge bg-info text-dark">Anda</span><?php endif; ?>
                        </td>
                        <td>
                            <select name="role" form="f<?php echo $i; ?>" class="form-select form-select-sm" <?php echo $saya ? 'disabled' : ''; ?>>
                                <option value="kasir" <?php echo $u['role'] == 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                                <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </td>
                        <td><input type="password" name="password" form="f<?php echo $i; ?>" class="form-control form-control-sm" placeholder="Kosongkan jika tidak diganti"></td>
                        <td class="d-flex gap-1">
                            <button form="f<?php echo $i; ?>" class="btn btn-primary btn-sm">Simpan</button>
                            <?php if (!$saya): ?>
                            <form method="POST" onsubmit="return confirm('Hapus akun ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($u['username']); ?>">
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                            <?php endif; ?>
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
