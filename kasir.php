<?php
session_start();
if (!isset($_SESSION["status_login"]) || $_SESSION["role"] != "kasir") {
    header("Location: index.php");
    exit();
}
include "koneksi.php";
$menus = mysqli_query($connection, "SELECT * FROM menu ORDER BY kategori, nama_makanan");
$halaman = 'kasir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu Kasir - Nusantara Food</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include "navbar.php"; ?>
<div class="container-fluid px-4 mt-4">
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <div class="row">
        <!-- Daftar menu -->
        <div class="col-lg-8">
            <div class="d-flex gap-2 mb-3">
                <input type="text" id="cariMenu" class="form-control" placeholder="Cari menu atau restoran...">
                <select id="filterKategori" class="form-select w-auto">
                    <option value="">Semua</option><option>Makanan</option><option>Minuman</option><option>Cemilan</option>
                </select>
            </div>
            <div class="row" id="daftarMenu">
                <?php while ($m = mysqli_fetch_assoc($menus)): ?>
                <div class="col-6 col-md-4 mb-3 item-menu" data-kategori="<?php echo htmlspecialchars($m['kategori']); ?>">
                    <div class="card h-100 shadow-sm">
                        <?php if ($m['gambar']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($m['gambar']); ?>" class="card-img-top" style="height:130px;object-fit:cover" alt="">
                        <?php else: ?>
                            <div class="bg-secondary-subtle d-flex align-items-center justify-content-center text-muted small" style="height:130px">Tanpa gambar</div>
                        <?php endif; ?>
                        <div class="card-body p-2">
                            <small class="text-muted"><?php echo htmlspecialchars($m['nama_restoran']); ?></small>
                            <h6 class="mb-1"><?php echo htmlspecialchars($m['nama_makanan']); ?></h6>
                            <div class="d-flex justify-content-between">
                                <strong class="text-success"><?php echo 'Rp ' . number_format($m['harga'], 0, ',', '.'); ?></strong>
                                <small class="text-muted">Stok: <?php echo (int)$m['stok']; ?></small>
                            </div>
                        </div>
                        <div class="card-footer p-2">
                            <button type="button" class="btn btn-primary btn-sm w-100 btn-tambah"
                                data-id="<?php echo (int)$m['id_menu']; ?>"
                                data-nama="<?php echo htmlspecialchars($m['nama_makanan']); ?>"
                                data-harga="<?php echo (int)$m['harga']; ?>"
                                data-stok="<?php echo (int)$m['stok']; ?>"
                                <?php echo $m['stok'] < 1 ? 'disabled' : ''; ?>>
                                <?php echo $m['stok'] < 1 ? 'Stok Habis' : '+ Tambah'; ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Keranjang & pembayaran -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top:20px">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Pesanan Baru</h5></div>
                <div class="card-body">
                    <form action="proses_pesanan.php" method="POST" id="formPesanan">
                        <input type="hidden" name="keranjang" id="inputKeranjang">
                        <div id="isiKeranjang" class="mb-3"></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 mb-3"><span>Total</span><strong id="totalText">Rp 0</strong></div>
                        <div class="mb-2">
                            <label class="form-label mb-1">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Umum">
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-1">Metode Bayar (langsung dibayar)</label>
                            <select name="metode_bayar" id="metode" class="form-select">
                                <option>Tunai</option><option>QRIS</option><option>Transfer</option><option>Debit</option>
                            </select>
                        </div>
                        <div class="mb-2" id="boxTunai">
                            <label class="form-label mb-1">Uang Diterima (Rp)</label>
                            <input type="number" min="0" name="uang_bayar" id="uangBayar" class="form-control">
                            <div class="small mt-1">Kembalian: <strong id="kembalianText">Rp 0</strong></div>
                        </div>
                        <button type="submit" id="btnBayar" class="btn btn-success w-100 mt-2" disabled>Bayar &amp; Simpan Pesanan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const cart = {};
const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const total = () => Object.values(cart).reduce((s, i) => s + i.harga * i.qty, 0);

function render() {
    const wadah = document.getElementById('isiKeranjang');
    wadah.innerHTML = '';
    const items = Object.values(cart);
    if (!items.length) {
        wadah.innerHTML = '<p class="text-muted text-center my-3">Keranjang kosong</p>';
    }
    items.forEach(i => {
        const row = document.createElement('div');
        row.className = 'd-flex justify-content-between align-items-center mb-2';
        const info = document.createElement('div');
        const nama = document.createElement('div');
        nama.className = 'small fw-bold'; nama.textContent = i.nama;
        const hrg = document.createElement('div');
        hrg.className = 'small text-muted'; hrg.textContent = fmt(i.harga) + ' x ' + i.qty + ' = ' + fmt(i.harga * i.qty);
        info.append(nama, hrg);
        const grup = document.createElement('div');
        grup.className = 'btn-group btn-group-sm';
        grup.innerHTML = '<button type="button" class="btn btn-outline-secondary" data-aksi="kurang" data-id="' + i.id + '">-</button>' +
                         '<button type="button" class="btn btn-outline-secondary" data-aksi="tambah" data-id="' + i.id + '">+</button>' +
                         '<button type="button" class="btn btn-outline-danger" data-aksi="hapus" data-id="' + i.id + '">&times;</button>';
        row.append(info, grup);
        wadah.appendChild(row);
    });
    document.getElementById('totalText').textContent = fmt(total());
    hitung();
}

function hitung() {
    const tunai = document.getElementById('metode').value === 'Tunai';
    document.getElementById('boxTunai').style.display = tunai ? 'block' : 'none';
    const bayar = parseInt(document.getElementById('uangBayar').value) || 0;
    const t = total();
    document.getElementById('kembalianText').textContent = fmt(Math.max(0, bayar - t));
    const ok = t > 0 && (!tunai || bayar >= t);
    document.getElementById('btnBayar').disabled = !ok;
}

document.querySelectorAll('.btn-tambah').forEach(b => b.addEventListener('click', () => {
    const id = b.dataset.id, stok = parseInt(b.dataset.stok);
    if (!cart[id]) cart[id] = { id: id, nama: b.dataset.nama, harga: parseInt(b.dataset.harga), stok: stok, qty: 0 };
    if (cart[id].qty < stok) cart[id].qty++; else alert('Stok tidak mencukupi.');
    render();
}));

document.getElementById('isiKeranjang').addEventListener('click', e => {
    const btn = e.target.closest('button'); if (!btn) return;
    const i = cart[btn.dataset.id]; if (!i) return;
    if (btn.dataset.aksi === 'tambah') { if (i.qty < i.stok) i.qty++; else alert('Stok tidak mencukupi.'); }
    if (btn.dataset.aksi === 'kurang') { i.qty--; if (i.qty < 1) delete cart[i.id]; }
    if (btn.dataset.aksi === 'hapus') delete cart[i.id];
    render();
});

document.getElementById('metode').addEventListener('change', hitung);
document.getElementById('uangBayar').addEventListener('input', hitung);
document.getElementById('formPesanan').addEventListener('submit', () => {
    document.getElementById('inputKeranjang').value = JSON.stringify(Object.values(cart).map(i => ({ id: i.id, qty: i.qty })));
});

function filterMenu() {
    const kw = document.getElementById('cariMenu').value.toLowerCase();
    const kat = document.getElementById('filterKategori').value;
    document.querySelectorAll('.item-menu').forEach(el => {
        const cocok = el.textContent.toLowerCase().includes(kw) && (!kat || el.dataset.kategori === kat);
        el.style.display = cocok ? '' : 'none';
    });
}
document.getElementById('cariMenu').addEventListener('input', filterMenu);
document.getElementById('filterKategori').addEventListener('change', filterMenu);
render();
</script>
</body>
</html>
