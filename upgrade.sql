-- ===== UPGRADE dari versi sebelumnya (data lama tetap aman). Butuh MariaDB XAMPP. =====
USE javanesefood;
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir';
UPDATE users SET role='admin' ORDER BY id LIMIT 1;   -- akun pertama menjadi admin

ALTER TABLE pesanan
  ADD COLUMN IF NOT EXISTS subtotal INT NOT NULL DEFAULT 0 AFTER catatan,
  ADD COLUMN IF NOT EXISTS diskon INT NOT NULL DEFAULT 0 AFTER subtotal,
  ADD COLUMN IF NOT EXISTS kode_promo VARCHAR(30) NULL AFTER diskon,
  ADD COLUMN IF NOT EXISTS pajak INT NOT NULL DEFAULT 0 AFTER kode_promo,
  ADD COLUMN IF NOT EXISTS total_refund INT NOT NULL DEFAULT 0 AFTER total;
UPDATE pesanan SET subtotal = total WHERE subtotal = 0;
ALTER TABLE pesanan DROP COLUMN IF EXISTS sumber;
ALTER TABLE detail_pesanan ADD COLUMN IF NOT EXISTS qty_refund INT NOT NULL DEFAULT 0 AFTER qty;

CREATE TABLE IF NOT EXISTS promo (
    id_promo INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(30) NOT NULL UNIQUE,
    tipe ENUM('persen','nominal') NOT NULL,
    nilai INT NOT NULL,
    min_belanja INT NOT NULL DEFAULT 0,
    berlaku_sampai DATE NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1
);
CREATE TABLE IF NOT EXISTS refund (
    id_refund INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_detail INT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    qty INT NOT NULL,
    nominal INT NOT NULL,
    kategori_alasan VARCHAR(50) NOT NULL,
    alasan VARCHAR(255) NOT NULL,
    metode_refund ENUM('Tunai','EDC','QRIS') NOT NULL,
    id_admin INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan),
    FOREIGN KEY (id_detail) REFERENCES detail_pesanan(id_detail) ON DELETE SET NULL
);
INSERT IGNORE INTO promo (kode, tipe, nilai, min_belanja) VALUES ('HEMAT10','persen',10,50000),('JAWA5K','nominal',5000,30000);
