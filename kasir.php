<?php
require "koneksi.php";
require "fungsi.php";
wajib_login();
$res = $connection->query("SELECT * FROM menu ORDER BY FIELD(kategori,'Makanan','Cemilan','Minuman'), nama_makanan");
$daftar = $res->fetch_all(MYSQLI_ASSOC);
$dataJs = array_map(fn($m) => ['id' => (int)$m['id_menu'], 'nama' => $m['nama_makanan'], 'harga' => (int)$m['harga'], 'stok' => (int)$m['stok'], 'gambar' => url_gambar($m['gambar'])], $daftar);
$judul = 'Kasir'; $halaman_aktif = 'kasir';
include "layout_atas.php";
?>
<div class="pos">
    <!-- Panel menu -->
    <section class="panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Menu</h5>
            <div class="search"><i class="bi bi-search"></i><input type="text" id="cariMenu" class="form-control" placeholder="Cari menu..."><kbd>Ctrl K</kbd></div>
        </div>
        <div class="tabs" id="filterKat">
            <button class="tab active" data-kat="Semua">Semua</button>
            <?php foreach (KATEGORI_MENU as $k): ?><button class="tab" data-kat="<?= e($k) ?>"><?= e($k) ?></button><?php endforeach; ?>
        </div>
        <div class="menu-grid">
            <?php foreach ($daftar as $m): $img = url_gambar($m['gambar']); $habis = $m['stok'] <= 0; ?>
            <div class="menu-card item-menu" data-kategori="<?= e($m['kategori']) ?>" data-id="<?= (int)$m['id_menu'] ?>">
                <div class="mc-img"><?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($m['nama_makanan']) ?>"><?php else: ?>🍛<?php endif; ?></div>
                <span class="tag"><?= e($m['kategori']) ?></span>
                <div class="mc-name"><?= e($m['nama_makanan']) ?></div>
                <div class="mc-price"><?= rupiah($m['harga']) ?> / porsi · stok <span id="stok-<?= (int)$m['id_menu'] ?>"><?= (int)$m['stok'] ?></span></div>
                <div class="mc-actions">
                    <div class="stepper"><button type="button" class="qm">−</button><span class="qv">0</span><button type="button" class="qp">+</button></div>
                    <button type="button" class="btn-add" data-id="<?= (int)$m['id_menu'] ?>" <?= $habis ? 'disabled' : '' ?>><?= $habis ? 'Habis' : 'Tambah' ?></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Ringkasan pesanan -->
    <aside class="panel order-panel">
        <div class="panel-title d-flex justify-content-between">Ringkasan Pesanan <span class="badge-soft bs-orange" id="jmlItem">0</span></div>
        <div id="alertPesanan"></div>
        <div class="mb-2"><label class="form-label">Nama Pelanggan</label><input type="text" id="nama" class="form-control" placeholder="Masukkan nama" maxlength="100"></div>
        <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label">Tipe Pesanan</label>
                <select id="tipe" class="form-select"><option>Dine In</option><option>Take Away</option></select></div>
            <div class="col-6"><label class="form-label">Lokasi Meja</label>
                <select id="meja" class="form-select"><option value="">Pilih meja</option>
                    <?php for ($i = 1; $i <= JUMLAH_MEJA; $i++): ?><option value="<?= $i ?>">Meja <?= $i ?></option><?php endfor; ?>
                </select></div>
        </div>
        <div class="mb-3"><input type="text" id="catatan" class="form-control" placeholder="Catatan pesanan (opsional)" maxlength="255"></div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Item Pesanan</strong>
            <button type="button" id="btnReset" class="btn btn-sm bs-red badge-soft border-0"><i class="bi bi-trash3"></i> Kosongkan</button>
        </div>
        <div id="daftarKeranjang" style="max-height:30vh;overflow-y:auto"></div>

        <div class="mt-3 pt-2 border-top">
            <strong>Total Pembayaran</strong>
            <div class="sum-row"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
            <div class="sum-row"><span id="labelPajak">Pajak</span><span id="pajak">Rp 0</span></div>
            <div class="sum-row"><span>Diskon</span><span id="diskon">− Rp 0</span></div>
            <div class="grand"><span>Total Akhir</span><span id="total" class="text-dark">Rp 0</span></div>
        </div>
        <div class="input-group my-3">
            <input type="text" id="kodePromo" class="form-control" placeholder="Masukkan kode promo" style="text-transform:uppercase">
            <button type="button" id="btnPromo" class="btn btn-soft">Terapkan</button>
        </div>
        <div id="promoInfo" class="small text-success mb-2" style="margin-top:-8px"></div>
        <div class="d-grid gap-2">
            <button id="btnBayar" class="btn btn-or btn-lg">Pesan & Bayar</button>
            <button id="btnSimpan" class="btn btn-soft">Simpan Pesanan (Bayar Nanti)</button>
        </div>
    </aside>
</div>

<script src="keranjang.js"></script>
<script>
const $ = id => document.getElementById(id);
const MENU = <?= json_encode($dataJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const keranjang = new Keranjang(MENU, {
    list: $('daftarKeranjang'), jml: $('jmlItem'), subtotal: $('subtotal'), diskon: $('diskon'),
    pajak: $('pajak'), total: $('total'), labelPajak: $('labelPajak'), promoInfo: $('promoInfo')
}, <?= (int)PAJAK_PERSEN ?>);

aktifkanFilter($('cariMenu'), document.querySelectorAll('#filterKat .tab'));
document.addEventListener('keydown', e => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); $('cariMenu').focus(); } });

// stepper + tombol tambah pada kartu menu
document.querySelectorAll('.menu-card').forEach(card => {
    const id = +card.dataset.id, qv = card.querySelector('.qv'); let q = 0;
    card.querySelector('.qp').onclick = () => { if (q < keranjang.menu[id].stok - (keranjang.items[id] || 0)) qv.textContent = ++q; };
    card.querySelector('.qm').onclick = () => { if (q > 0) qv.textContent = --q; };
    card.querySelector('.btn-add').onclick = () => { keranjang.tambah(id, q || 1); q = 0; qv.textContent = 0; };
});

$('tipe').addEventListener('change', () => { $('meja').disabled = $('tipe').value === 'Take Away'; if ($('meja').disabled) $('meja').value = ''; });
$('btnReset').onclick = () => keranjang.kosongkan();

function tampil(jenis, teks) { $('alertPesanan').innerHTML = `<div class="alert alert-${jenis} py-2 small">${esc(teks)}</div>`; }

$('btnPromo').onclick = async () => {
    if (keranjang.promo) { keranjang.promo = null; $('kodePromo').value = ''; $('kodePromo').disabled = false; $('btnPromo').textContent = 'Terapkan'; keranjang.render(); return; }
    const kode = $('kodePromo').value.trim();
    if (!kode) return;
    try {
        const r = await (await fetch('api_promo.php?kode=' + encodeURIComponent(kode))).json();
        if (r.sukses) { keranjang.promo = r.promo; $('kodePromo').disabled = true; $('btnPromo').textContent = 'Batal'; keranjang.render(); tampil('success', 'Kode promo valid.'); }
        else tampil('danger', r.pesan);
    } catch (e) { tampil('danger', 'Gagal memeriksa kode promo.'); }
};

async function kirim(langsungBayar) {
    if (keranjang.kosong()) { tampil('warning', 'Keranjang masih kosong.'); return; }
    const tombol = [$('btnBayar'), $('btnSimpan')];
    tombol.forEach(b => b.disabled = true);
    try {
        const r = await kirimPesanan({
            nama: $('nama').value, tipe: $('tipe').value, meja: $('meja').value, catatan: $('catatan').value,
            kode_promo: keranjang.promo ? keranjang.promo.kode : '', items: keranjang.daftar()
        });
        if (r.sukses) {
            if (langsungBayar) { window.location = 'pembayaran.php?id=' + r.id_pesanan; return; }
            tampil('success', 'Pesanan ' + r.kode + ' tersimpan (' + Rp(r.total) + '). Belum dibayar.');
            perbaruiStok(keranjang, keranjang.daftar());
            keranjang.kosongkan(); keranjang.promo = null;
            $('kodePromo').value = ''; $('kodePromo').disabled = false; $('btnPromo').textContent = 'Terapkan';
            ['nama', 'meja', 'catatan'].forEach(i => $(i).value = '');
            keranjang.render();
        } else tampil('danger', r.pesan);
    } catch (e) { tampil('danger', 'Gagal menghubungi server.'); }
    tombol.forEach(b => b.disabled = false);
}
$('btnBayar').onclick = () => kirim(true);
$('btnSimpan').onclick = () => kirim(false);
</script>
<?php include "layout_bawah.php"; ?>
