<?php
require_once __DIR__ . '/fungsi.php';
$aktif  = $halaman_aktif ?? '';
$judul  = $judul ?? NAMA_RESTORAN;
$nama   = $_SESSION['username'] ?? '';
$menuUtama = [
    ['kasir', 'kasir.php', 'bi-receipt', 'Kasir'],
    ['pesanan', 'kasir_pesanan.php', 'bi-journal-text', 'Pesanan'],
    ['reservasi', 'admin_reservasi.php', 'bi-calendar-check', 'Reservasi'],
];
$menuAdmin = [
    ['menu', 'dashboard.php', 'bi-egg-fried', 'Kelola Menu'],
    ['promo', 'promo.php', 'bi-tag', 'Promo'],
    ['pengguna', 'pengguna.php', 'bi-people', 'Pengguna'],
];
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - <?= e(NAMA_RESTORAN) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand"><span class="brand-logo"><i class="bi bi-cup-hot-fill"></i></span><span><?= e(NAMA_RESTORAN) ?></span></div>
        <div class="side-label">Menu</div>
        <?php foreach ($menuUtama as [$k, $url, $ico, $lbl]): ?>
            <a class="side-link <?= $aktif === $k ? 'active' : '' ?>" href="<?= $url ?>"><i class="bi <?= $ico ?>"></i><?= $lbl ?></a>
        <?php endforeach; ?>
        <?php if (is_admin()): ?>
            <div class="side-label mt-3">Admin</div>
            <?php foreach ($menuAdmin as [$k, $url, $ico, $lbl]): ?>
                <a class="side-link <?= $aktif === $k ? 'active' : '' ?>" href="<?= $url ?>"><i class="bi <?= $ico ?>"></i><?= $lbl ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="side-user">
            <span class="avatar"><?= e(strtoupper(substr($nama, 0, 1))) ?></span>
            <div class="flex-grow-1 lh-sm"><div class="fw-semibold"><?= e($nama) ?></div><small class="text-muted text-capitalize"><?= e(role()) ?></small></div>
            <a href="logout.php" class="text-muted fs-5" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </aside>
    <main class="main">
        <?php if ($flash): ?><div class="alert alert-<?= e($flash[0]) ?> alert-dismissible fade show"><?= e($flash[1]) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
