<?php
include "koneksi.php";
$menus = $connection->query("SELECT * FROM menu ORDER BY nama_restoran ASC");
$restorans = $connection->query("SELECT DISTINCT nama_restoran FROM menu");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nusantara Food Hub - Kuliner Khas Jawa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="katalog.php">Nusantara Food Hub (Khas Jawa)</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">Login Admin</a>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_GET['reservasi']) && $_GET['reservasi'] == 'sukses'): ?>
        <div class="alert alert-success">Reservasi meja berhasil dikirim! Silakan tunggu konfirmasi pihak restoran.</div>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Katalog Menu & Filter JS (Modul 3) -->
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Katalog Kuliner Jawa</h4>
                <input type="text" id="cariMenu" class="form-control w-50" placeholder="Cari makanan atau restoran...">
            </div>
            <div class="row" id="daftarKartu">
                <?php while ($m = $menus->fetch_assoc()): ?>
                <div class="col-md-6 mb-3 item-menu">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-warning text-dark mb-2"><?php echo htmlspecialchars($m['nama_restoran']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($m['nama_makanan']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <strong class="text-success">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></strong>
                                <span class="badge bg-light text-dark border">Stok: <?php echo $m['stok']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Form Reservasi Meja -->
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Reservasi Meja Restoran</h5></div>
                <div class="card-body">
                    <form action="proses_reservasi.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemesan</label>
                            <input type="text" name="nama_pemesan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Restoran</label>
                            <select name="nama_restoran" class="form-select" required>
                                <?php while ($r = $restorans->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($r['nama_restoran']); ?>"><?php echo htmlspecialchars($r['nama_restoran']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Orang</label>
                            <input type="number" name="jumlah_orang" min="1" max="20" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kedatangan</label>
                            <input type="date" name="tanggal_reservasi" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Pesan Meja Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript DOM Filter Pencarian Real-time (Modul 3) -->
<script>
document.querySelector("#cariMenu").addEventListener("input", function() {
    const keyword = this.value.toLowerCase();
    const items = document.querySelectorAll(".item-menu");
    items.forEach(function(item) {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(keyword) ? "block" : "none";
    });
});
</script>
</body>
</html>