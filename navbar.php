<?php
// Halaman beranda sesuai role
if ($_SESSION['role'] == 'admin') {
    $beranda = 'dashboard.php';
} else {
    $beranda = 'kasir.php';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $beranda; ?>">Nusantara Food Hub</a>
        <div class="navbar-nav me-auto">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a class="nav-link <?php if ($halaman == 'menu') echo 'active'; ?>" href="dashboard.php">Kelola Menu</a>
                <a class="nav-link <?php if ($halaman == 'akun') echo 'active'; ?>" href="kelola_akun.php">Kelola Akun</a>
                <a class="nav-link <?php if ($halaman == 'laporan') echo 'active'; ?>" href="laporan.php">Laporan Pesanan</a>
            <?php else: ?>
                <a class="nav-link <?php if ($halaman == 'kasir') echo 'active'; ?>" href="kasir.php">Menu Kasir</a>
                <a class="nav-link <?php if ($halaman == 'pesanan') echo 'active'; ?>" href="pesanan.php">Pesanan</a>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center text-white">
            <span class="me-3">Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>
