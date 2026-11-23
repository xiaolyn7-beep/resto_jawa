-- ===== INSTALASI BARU (hapus & buat ulang semua tabel) =====
CREATE DATABASE IF NOT EXISTS javanesefood CHARACTER SET utf8mb4;
USE javanesefood;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS refund, pembayaran, detail_pesanan, pesanan, promo, reservasi, menu, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE reservasi (
    id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
    nama_pemesan VARCHAR(100) NOT NULL,
    jumlah_orang INT NOT NULL,
    tanggal_reservasi DATE NOT NULL,
    status ENUM('Pending','Dikonfirmasi','Selesai') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nama_makanan VARCHAR(100) NOT NULL,
    kategori ENUM('Makanan','Minuman','Cemilan') NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    deskripsi TEXT,
    gambar VARCHAR(255) NULL
);
CREATE TABLE promo (
    id_promo INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(30) NOT NULL UNIQUE,
    tipe ENUM('persen','nominal') NOT NULL,
    nilai INT NOT NULL,
    min_belanja INT NOT NULL DEFAULT 0,
    berlaku_sampai DATE NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1
);
CREATE TABLE pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30) NULL UNIQUE,
    nama_pelanggan VARCHAR(100) NOT NULL,
    nomor_meja VARCHAR(10) NULL,
    tipe_pesanan ENUM('Dine In','Take Away') NOT NULL DEFAULT 'Dine In',
    catatan VARCHAR(255) NULL,
    subtotal INT NOT NULL DEFAULT 0,
    diskon INT NOT NULL DEFAULT 0,
    kode_promo VARCHAR(30) NULL,
    pajak INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    total_refund INT NOT NULL DEFAULT 0,
    status_pesanan ENUM('Baru','Diproses','Selesai','Batal') NOT NULL DEFAULT 'Baru',
    status_pembayaran ENUM('Belum Bayar','Lunas') NOT NULL DEFAULT 'Belum Bayar',
    id_user INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_menu INT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    qty INT NOT NULL,
    qty_refund INT NOT NULL DEFAULT 0,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE SET NULL
);
CREATE TABLE pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL UNIQUE,
    metode ENUM('Tunai','EDC','QRIS') NOT NULL,
    total_tagihan INT NOT NULL,
    jumlah_bayar INT NOT NULL,
    kembalian INT NOT NULL DEFAULT 0,
    nomor_referensi VARCHAR(40) NULL,
    id_kasir INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
);
CREATE TABLE refund (
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

INSERT INTO menu (nama_makanan, kategori, harga, stok, deskripsi) VALUES
('Gudeg Komplit Telur & Ayam','Makanan',35000,20,'Gudeg nangka manis khas Jogja dengan telur bacem dan ayam suwir.'),
('Soto Ayam Lamongan','Makanan',25000,30,'Soto ayam kampung dengan bumbu rempah spesial.'),
('Mie Ongklok Wonosobo','Makanan',20000,25,'Mie dan sayuran dengan kuah kental bumbu kacang khas Wonosobo.'),
('Nasi Liwet Solo','Makanan',28000,20,'Nasi gurih santan dengan suwiran ayam, telur, dan areh.'),
('Rawon Surabaya','Makanan',30000,15,'Sup daging berkuah hitam kluwek dengan tauge dan sambal.'),
('Lumpia Semarang','Cemilan',5000,40,'Lumpia isi rebung dan daging, digoreng renyah.'),
('Tahu Gimbal','Cemilan',18000,20,'Tahu goreng, lontong, dan gimbal udang dengan bumbu kacang.'),
('Es Dawet Ayu','Minuman',10000,50,'Es dawet dengan gula jawa cair dan santan segar.'),
('Wedang Jahe','Minuman',8000,40,'Minuman jahe hangat dengan gula batu.'),
('Es Teh Manis','Minuman',5000,60,'Teh manis dingin segar.');

INSERT INTO promo (kode, tipe, nilai, min_belanja) VALUES
('HEMAT10','persen',10,50000),
('JAWA5K','nominal',5000,30000);
-- Akun pertama dibuat lewat register.php (otomatis menjadi ADMIN).
