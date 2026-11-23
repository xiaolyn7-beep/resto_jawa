<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $connection->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p || $p['status_pesanan'] === 'Batal') { header("Location: kasir_pesanan.php"); exit(); }
if ($p['status_pembayaran'] === 'Lunas') { header("Location: struk.php?id=$id"); exit(); }

$stmt = $connection->prepare("SELECT * FROM detail_pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$punyaQris = is_file(__DIR__ . '/qris.png');
$judul = 'Pembayaran'; $halaman_aktif = 'pesanan';
include "layout_atas.php";
?>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-title d-flex justify-content-between"><span><?= e($p['kode_pesanan']) ?></span><span class="badge-soft bs-red">Belum Bayar</span></div>
            <div class="text-muted small mb-3"><?= e($p['nama_pelanggan']) ?> • <?= e($p['tipe_pesanan']) ?><?= ($p['nomor_meja'] !== null && $p['nomor_meja'] !== '') ? ' • Meja ' . e($p['nomor_meja']) : '' ?></div>
            <?php foreach ($detail as $d): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div><?= e($d['nama_menu']) ?><br><small class="text-muted"><?= (int)$d['qty'] ?> × <?= rupiah($d['harga']) ?></small></div>
                    <div><?= rupiah($d['subtotal']) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="sum-row mt-3"><span>Subtotal</span><span><?= rupiah($p['subtotal']) ?></span></div>
            <?php if ($p['diskon'] > 0): ?><div class="sum-row"><span>Diskon <?= e($p['kode_promo']) ?></span><span>− <?= rupiah($p['diskon']) ?></span></div><?php endif; ?>
            <div class="sum-row"><span>Pajak (<?= PAJAK_PERSEN ?>%)</span><span><?= rupiah($p['pajak']) ?></span></div>
            <div class="grand"><span>Total Akhir</span><span><?= rupiah($p['total']) ?></span></div>
            <?php if ($p['catatan']): ?><div class="alert alert-light border small mt-3 mb-0">Catatan: <?= e($p['catatan']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-title">Metode Pembayaran</div>
            <div id="alertBayar"></div>
            <div class="btn-group w-100 mb-3">
                <input type="radio" class="btn-check" name="metode" id="mTunai" value="Tunai" checked><label class="btn btn-outline-secondary" for="mTunai"><i class="bi bi-cash-coin"></i> Tunai</label>
                <input type="radio" class="btn-check" name="metode" id="mEdc" value="EDC"><label class="btn btn-outline-secondary" for="mEdc"><i class="bi bi-credit-card"></i> EDC / Kartu</label>
                <input type="radio" class="btn-check" name="metode" id="mQris" value="QRIS"><label class="btn btn-outline-secondary" for="mQris"><i class="bi bi-qr-code"></i> QRIS</label>
            </div>

            <div id="panelTunai">
                <label class="form-label">Uang diterima (Rp)</label>
                <input type="number" id="uang" class="form-control form-control-lg" min="0" step="500" autofocus>
                <div class="d-flex flex-wrap gap-2 my-2" id="uangCepat"></div>
                <div class="p-3 rounded-3 d-flex justify-content-between fs-5" style="background:var(--or-l)"><span>Kembalian</span><strong id="kembalian">Rp 0</strong></div>
            </div>
            <div id="panelEDC" style="display:none">
                <p class="text-muted small">Gesek/tap kartu di mesin EDC. Setelah mesin menampilkan <em>APPROVED</em>, masukkan kode approval.</p>
                <label class="form-label">Kode Approval EDC</label>
                <input type="text" id="refEdc" class="form-control" maxlength="20" placeholder="mis. 123456">
            </div>
            <div id="panelQRIS" style="display:none">
                <div class="text-center mb-3">
                    <?php if ($punyaQris): ?><img src="qris.png" alt="QRIS" style="max-width:220px" class="img-thumbnail">
                    <?php else: ?><div class="border rounded-3 p-4 text-muted small">Taruh gambar QRIS toko sebagai <code>qris.png</code> di folder project agar tampil di sini.</div><?php endif; ?>
                    <div class="fw-bold mt-2">Tagihan: <?= rupiah($p['total']) ?></div>
                </div>
                <label class="form-label">ID / Referensi Transaksi QRIS</label>
                <input type="text" id="refQris" class="form-control" maxlength="40" placeholder="Lihat di notifikasi pembayaran">
            </div>

            <button id="btnProses" class="btn btn-or btn-lg w-100 mt-3" disabled>Konfirmasi Pembayaran</button>
            <a href="kasir_pesanan.php" class="btn btn-link w-100 mt-1 text-muted">Kembali</a>
        </div>
    </div>
</div>

<script>
const TOTAL = <?= (int)$p['total'] ?>, ID = <?= (int)$id ?>;
const Rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const $ = id => document.getElementById(id);
const metode = () => document.querySelector('input[name=metode]:checked').value;

[...new Set([TOTAL, Math.ceil(TOTAL/10000)*10000, Math.ceil(TOTAL/50000)*50000, Math.ceil(TOTAL/100000)*100000])].forEach((n, i) => {
    const b = document.createElement('button');
    b.type = 'button'; b.className = 'btn btn-soft btn-sm'; b.textContent = i === 0 ? 'Uang Pas' : Rp(n);
    b.onclick = () => { $('uang').value = n; validasi(); };
    $('uangCepat').appendChild(b);
});

function validasi() {
    const m = metode();
    $('panelTunai').style.display = m === 'Tunai' ? '' : 'none';
    $('panelEDC').style.display = m === 'EDC' ? '' : 'none';
    $('panelQRIS').style.display = m === 'QRIS' ? '' : 'none';
    let ok = false;
    if (m === 'Tunai') {
        const sel = parseInt($('uang').value || 0) - TOTAL;
        $('kembalian').textContent = sel >= 0 ? Rp(sel) : 'Kurang ' + Rp(-sel);
        $('kembalian').className = sel >= 0 ? 'text-success' : 'text-danger';
        ok = parseInt($('uang').value || 0) > 0 && sel >= 0;
    } else if (m === 'EDC') ok = /^[A-Za-z0-9]{4,20}$/.test($('refEdc').value.trim());
    else ok = /^[A-Za-z0-9\-]{6,40}$/.test($('refQris').value.trim());
    $('btnProses').disabled = !ok;
}
document.querySelectorAll('input[name=metode]').forEach(r => r.addEventListener('change', validasi));
['uang', 'refEdc', 'refQris'].forEach(i => $(i).addEventListener('input', validasi));

$('btnProses').addEventListener('click', async function () {
    const m = metode();
    this.disabled = true;
    try {
        const r = await (await fetch('api_bayar.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({
            id_pesanan: ID, metode: m, jumlah_bayar: m === 'Tunai' ? parseInt($('uang').value || 0) : TOTAL,
            referensi: m === 'EDC' ? $('refEdc').value : (m === 'QRIS' ? $('refQris').value : '') }) })).json();
        if (r.sukses) { window.location = 'struk.php?id=' + ID + '&baru=1'; return; }
        $('alertBayar').innerHTML = '<div class="alert alert-danger py-2"></div>'; $('alertBayar').firstChild.textContent = r.pesan;
    } catch (e) { $('alertBayar').innerHTML = '<div class="alert alert-danger py-2">Gagal menghubungi server.</div>'; }
    this.disabled = false;
});
validasi();
</script>
<?php include "layout_bawah.php"; ?>
