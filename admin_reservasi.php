<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["status_login"] !== true) { header("Location: index.php"); exit(); }
include "koneksi.php";

// Update status jika tombol konfirmasi diklik
if (isset($_GET['konfirmasi_id'])) {
    $stmt = $connection->prepare("UPDATE reservasi SET status = 'Dikonfirmasi' WHERE id_reservasi = ?");
    $stmt->bind_param("i", $_GET['konfirmasi_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_reservasi.php");
    exit();
}

$data = $connection->query("SELECT * FROM reservasi ORDER BY id_reservasi DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Reservasi - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Nusantara Food Hub</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="dashboard.php">Kelola Menu</a>
            <a class="nav-link active" href="admin_reservasi.php">Data Reservasi</a>
        </div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-3">Daftar Reservasi Meja Pelanggan</h3>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Pemesan</th>
                        <th>Restoran</th>
                        <th>Jumlah Orang</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($r['nama_pemesan']); ?></td>
                        <td><?php echo htmlspecialchars($r['nama_restoran']); ?></td>
                        <td><?php echo $r['jumlah_orang']; ?> Orang</td>
                        <td><?php echo $r['tanggal_reservasi']; ?></td>
                        <td>
                            <span class="badge <?php echo ($r['status']=='Dikonfirmasi')?'bg-success':'bg-warning text-dark'; ?>">
                                <?php echo $r['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'Pending'): ?>
                                <a href="admin_reservasi.php?konfirmasi_id=<?php echo $r['id_reservasi']; ?>" class="btn btn-success btn-sm">Konfirmasi</a>
                            <?php else: ?>
                                <span class="text-muted small">Selesai</span>
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