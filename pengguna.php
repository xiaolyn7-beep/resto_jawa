<?php
require "koneksi.php";
require "fungsi.php";
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'tambah') {
            $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? ''; $r = $_POST['role'] ?? 'kasir';
            if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $u)) throw new PesananError('Username 3-50 karakter (huruf, angka, _ atau .).');
            if (strlen($p) < 6) throw new PesananError('Password minimal 6 karakter.');
            if (!in_array($r, ['admin', 'kasir'], true)) $r = 'kasir';
            $hash = password_hash($p, PASSWORD_DEFAULT);
            try {
                $stmt = $connection->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $u, $hash, $r);
                $stmt->execute();
            } catch (mysqli_sql_exception $ex) { throw new PesananError('Username sudah dipakai.'); }
            $_SESSION['flash'] = ['success', "Pengguna $u ($r) ditambahkan."];
        } elseif ($aksi === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === (int)$_SESSION['id_user']) throw new PesananError('Anda tidak bisa menghapus akun sendiri.');
            $stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['flash'] = ['success', 'Pengguna dihapus.'];
        }
    } catch (PesananError $ex) { $_SESSION['flash'] = ['danger', $ex->getMessage()]; }
    header("Location: pengguna.php"); exit();
}

$users = $connection->query("SELECT id, username, role, created_at FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$judul = 'Pengguna'; $halaman_aktif = 'pengguna';
include "layout_atas.php";
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-title">Tambah Pengguna</div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="6" required></div>
                <div class="mb-3"><label class="form-label">Role</label>
                    <select name="role" class="form-select"><option value="kasir">Kasir</option><option value="admin">Admin</option></select>
                </div>
                <button class="btn btn-or w-100">Simpan</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel">
            <div class="panel-title">Daftar Pengguna</div>
            <table class="table align-middle mb-0">
                <thead><tr><th>Username</th><th>Role</th><th>Dibuat</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= e($u['username']) ?></td>
                    <td><span class="badge-soft <?= $u['role'] === 'admin' ? 'bs-orange' : 'bs-blue' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-end">
                        <?php if ((int)$u['id'] !== (int)$_SESSION['id_user']): ?>
                        <form method="POST" onsubmit="return confirm('Hapus pengguna ini?')"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-sm btn-soft text-danger">Hapus</button></form>
                        <?php else: ?><small class="text-muted">Anda</small><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "layout_bawah.php"; ?>
