// Keranjang kasir: item, promo, pajak, total (server tetap menghitung ulang saat pesanan disimpan)
const Rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

class Keranjang {
    constructor(daftarMenu, el, pajakPersen) {
        this.menu = {}; daftarMenu.forEach(m => this.menu[m.id] = m);
        this.items = {}; this.promo = null; this.pajakPersen = pajakPersen; this.el = el;
        el.list.addEventListener('click', ev => {
            const b = ev.target.closest('button[data-aksi]'); if (!b) return;
            const id = +b.dataset.id;
            if (b.dataset.aksi === 'plus') this.tambah(id);
            if (b.dataset.aksi === 'minus') this.kurang(id);
            if (b.dataset.aksi === 'hapus') { delete this.items[id]; this.render(); }
        });
        this.render();
    }
    tambah(id, n = 1) {
        const m = this.menu[id], q = this.items[id] || 0;
        if (!m) return;
        if (q + n > m.stok) { alert('Stok ' + m.nama + ' tinggal ' + m.stok + '.'); return; }
        this.items[id] = q + n; this.render();
    }
    kurang(id) { if (!this.items[id]) return; if (--this.items[id] <= 0) delete this.items[id]; this.render(); }
    kosongkan() { this.items = {}; this.render(); }
    kosong() { return Object.keys(this.items).length === 0; }
    daftar() { return Object.entries(this.items).map(([id, qty]) => ({ id: +id, qty })); }
    jumlah() { return Object.values(this.items).reduce((a, b) => a + b, 0); }
    subtotal() { return Object.entries(this.items).reduce((t, [id, q]) => t + this.menu[id].harga * q, 0); }
    diskon() {
        const s = this.subtotal(), p = this.promo;
        if (!p || s < p.min_belanja) return 0;
        const d = p.tipe === 'persen' ? Math.floor(s * p.nilai / 100) : p.nilai;
        return Math.min(d, s);
    }
    pajak() { return Math.round((this.subtotal() - this.diskon()) * this.pajakPersen / 100); }
    total() { return this.subtotal() - this.diskon() + this.pajak(); }

    render() {
        const e = this.el;
        e.list.innerHTML = this.kosong()
            ? '<div class="text-center text-muted py-4"><i class="bi bi-cart3 fs-2 d-block mb-1"></i>Keranjang masih kosong</div>'
            : Object.entries(this.items).map(([id, q]) => {
                const m = this.menu[id];
                const gambar = m.gambar ? `<img src="${esc(m.gambar)}" alt="">` : '🍛';
                return `<div class="cart-item"><div class="thumb">${gambar}</div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between"><span class="fw-semibold">${esc(m.nama)}</span>
                      <button type="button" class="x" data-aksi="hapus" data-id="${id}" title="Hapus"><i class="bi bi-x-lg"></i></button></div>
                    <div class="text-muted small">${Rp(m.harga)}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <div class="stepper"><button type="button" data-aksi="minus" data-id="${id}">−</button><span>${q}</span><button type="button" data-aksi="plus" data-id="${id}">+</button></div>
                      <strong>${Rp(m.harga * q)}</strong></div>
                  </div></div>`;
            }).join('');
        e.jml.textContent = this.jumlah();
        e.subtotal.textContent = Rp(this.subtotal());
        e.diskon.textContent = '− ' + Rp(this.diskon());
        e.pajak.textContent = Rp(this.pajak());
        e.total.textContent = Rp(this.total());
        e.labelPajak.textContent = 'Pajak (' + this.pajakPersen + '%)';
        if (this.promo && this.subtotal() < this.promo.min_belanja)
            e.promoInfo.textContent = 'Promo ' + this.promo.kode + ' butuh minimal belanja ' + Rp(this.promo.min_belanja) + '.';
        else if (this.promo) e.promoInfo.textContent = 'Promo ' + this.promo.kode + ' diterapkan.';
        else e.promoInfo.textContent = '';
    }
}

async function kirimPesanan(payload) {
    const r = await fetch('api_pesanan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    return r.json();
}

// Sinkronkan tampilan stok setelah pesanan tersimpan
function perbaruiStok(keranjang, terjual) {
    terjual.forEach(({ id, qty }) => {
        const m = keranjang.menu[id]; if (!m) return;
        m.stok = Math.max(0, m.stok - qty);
        const el = document.getElementById('stok-' + id); if (el) el.textContent = m.stok;
        const btn = document.querySelector('.btn-add[data-id="' + id + '"]');
        if (btn && m.stok <= 0) { btn.disabled = true; btn.textContent = 'Habis'; }
    });
}

// Filter pencarian + tab kategori untuk kartu .item-menu
function aktifkanFilter(inputCari, tabs) {
    let kat = 'Semua';
    const terapkan = () => {
        const kw = inputCari.value.toLowerCase();
        document.querySelectorAll('.item-menu').forEach(el => {
            const ok = (kat === 'Semua' || el.dataset.kategori === kat) && el.textContent.toLowerCase().includes(kw);
            el.style.display = ok ? '' : 'none';
        });
    };
    inputCari.addEventListener('input', terapkan);
    tabs.forEach(b => b.addEventListener('click', () => {
        tabs.forEach(x => x.classList.remove('active')); b.classList.add('active'); kat = b.dataset.kat; terapkan();
    }));
}
