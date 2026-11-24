-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 07:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_resto_projek`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_menu` int(11) DEFAULT NULL,
  `nama_makanan` varchar(150) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_menu`, `nama_makanan`, `harga`, `jumlah`, `subtotal`) VALUES
(1, 1, 5, 'Lumpia Semarang', 5000, 4, 20000),
(2, 2, 5, 'Lumpia Semarang', 5000, 11, 55000);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_restoran` varchar(100) NOT NULL,
  `nama_makanan` varchar(100) NOT NULL,
  `kategori` enum('Makanan','Minuman','Cemilan') NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_restoran`, `nama_makanan`, `kategori`, `harga`, `stok`, `deskripsi`, `gambar`) VALUES
(1, 'Gudeg Yu Djum', 'Gudeg Komplit Telur & Ayam', 'Makanan', 35000, 20, 'Gudeg nangka manis khas Jogja dengan telur bacem dan ayam suwir.', NULL),
(2, 'Soto Lamongan', 'Soto Ayam Lamongan Spesial', 'Makanan', 25000, 30, 'Soto ayam lamongan yang terbuat dari ayam kampung diramu dengan bumbu bumbu spesial.', NULL),
(3, 'Mie Ongklok', 'Mie Ongklok Khas Wonosobo', 'Makanan', 20000, 25, 'Mie dan sayuran ditambah bumbu kacang dan bumbu spesial yang nikmat khas kota Wonosobo', NULL),
(4, 'Warung Es Dawet Ayu', 'Es Dawet Ayu Banjarnegara', 'Minuman', 10000, 50, 'Es dawet dengan gula jawa cair dan santan segar.', NULL),
(5, 'Lumpia', 'Lumpia Semarang', 'Makanan', 5000, 0, 'Adonan tepung yang diisi daging, rebung, lalu digulung dan digoreng.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `kode_pesanan` varchar(30) NOT NULL DEFAULT '',
  `id_kasir` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(100) NOT NULL DEFAULT 'Umum',
  `total` int(11) NOT NULL,
  `metode_bayar` varchar(20) NOT NULL,
  `uang_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Lunas',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_kasir`, `nama_pelanggan`, `total`, `metode_bayar`, `uang_bayar`, `kembalian`, `status`, `tanggal`) VALUES
(1, 'INV-20261123-0001', 2, 'coba', 20000, 'QRIS', 20000, 0, 'Lunas', '2026-11-23 22:09:11'),
(2, 'INV-20261123-0002', 2, 'lol', 55000, 'Tunai', 100000, 45000, 'Lunas', '2026-11-24 03:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `role`) VALUES
(2, 'xiaoar', '$2y$10$7zeZgiVKU828dGRueKshiOFx.J6XiV60D88oRXhTzLtFSsQ.3yv/m', '2026-11-23 15:07:59', 'kasir'),
(3, 'admin', '$2y$10$3UnZBf2tUzYKJ1LBMzDqAOkrLaEnjOeqyOvhFTfSXsFo/hLgciPAW', '2026-11-23 23:23:39', 'admin'),
(4, 'gfgfg', '$2y$10$XiISzvuMXChnz.f7gy12LOFmYn0nji8jdl0l1DHNPTPiVGwB4jZQO', '2026-11-23 23:26:17', 'kasir'),
(5, 'gfgfgfg', '$2y$10$dh7LEELQU5P4d4IY3dW/vOhE4kOv0eyJvvOKHOhLeXRedsAiO3wF6', '2026-11-23 23:30:03', 'kasir');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_menu` (`id_menu`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `fk_pesanan_kasir` (`id_kasir`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detail_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
