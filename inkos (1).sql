-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 30, 2025 at 08:52 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inkos`
--

-- --------------------------------------------------------

--
-- Table structure for table `balasan_laporan`
--

CREATE TABLE `balasan_laporan` (
  `id` int NOT NULL,
  `laporan_id` int NOT NULL,
  `pengirim_id` int NOT NULL,
  `pengirim_tipe` enum('user','admin') NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daerah`
--

CREATE TABLE `daerah` (
  `id` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `kota` varchar(50) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daerah`
--

INSERT INTO `daerah` (`id`, `nama`, `kota`, `latitude`, `longitude`, `created_at`) VALUES
(1, 'mendalo asrii', 'muaro jambi', NULL, NULL, '2025-10-02 04:44:13'),
(2, 'sungai duren', 'jambi luar kota', NULL, NULL, '2025-10-16 05:06:15'),
(4, 'telanai pura', 'Jambi', NULL, NULL, '2025-11-14 11:09:47'),
(5, 'telanai pura', 'Jambi', NULL, NULL, '2025-11-14 11:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `kos`
--

CREATE TABLE `kos` (
  `id` int NOT NULL,
  `nama_kos` varchar(100) NOT NULL,
  `deskripsi` text,
  `alamat` text NOT NULL,
  `daerah_id` int DEFAULT NULL,
  `harga_bulanan` decimal(10,2) NOT NULL,
  `rekening_pemilik` varchar(255) DEFAULT NULL,
  `nama_rekening` varchar(255) DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `tipe_kos` enum('putra','putri','campur') NOT NULL,
  `ukuran_kamar` varchar(20) NOT NULL,
  `kamar_mandi` enum('dalam','luar') NOT NULL,
  `fasilitas` json DEFAULT NULL,
  `foto_utama` varchar(255) DEFAULT NULL,
  `foto_lainnya` json DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('tersedia','dipesan','tidak_tersedia') DEFAULT 'tersedia',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kos`
--

INSERT INTO `kos` (`id`, `nama_kos`, `deskripsi`, `alamat`, `daerah_id`, `harga_bulanan`, `rekening_pemilik`, `nama_rekening`, `bank`, `tipe_kos`, `ukuran_kamar`, `kamar_mandi`, `fasilitas`, `foto_utama`, `foto_lainnya`, `user_id`, `featured`, `status`, `views`, `created_at`, `updated_at`) VALUES
(1, 'wisma nurul ilmi', 'bagus ni', 'mendalo asri', 1, '500000.00', '0001234', 'deni', 'bri', 'putra', '10x10', 'luar', '[\"wifi\", \"parkir\", \"dapur\"]', '../uploads/kos68f9d26c298d1.png', '[\"../uploads/kos68f9d26c2c636_0.png\"]', 3, 1, 'dipesan', 0, '2025-10-23 06:59:56', '2025-11-26 06:03:18'),
(16, 'al-khafi', 'mantp', 'jambi', 1, '400000.00', NULL, NULL, NULL, 'putri', '3x4', 'dalam', '[\"WiFi\", \"AC\", \"Kipas Angin\", \"Dapur\"]', 'utama_6915a8b3a255c_1763027123.jpg', NULL, 3, 1, 'tersedia', 0, '2025-11-13 09:45:23', '2025-11-26 06:02:14'),
(17, 'mawar indah', 'lggi', 'jjj', 1, '500000.00', NULL, NULL, NULL, 'putra', 'n/knk', 'dalam', NULL, '../uploads/kos69173651af1ef.png', '[\"../uploads/kos69173651b099f_0.jpg\"]', 3, 1, 'dipesan', 0, '2025-11-14 14:01:53', '2025-11-28 14:18:31'),
(18, 'alkhaf', 'mantap ni bagus', 'mendalo asri', 4, '700000.00', NULL, NULL, NULL, 'putra', '5x4', 'dalam', '[\"wifi\", \"ac\", \"laundry\", \"parkir\", \"dapur\", \"tv\", \"lemari\"]', '../uploads/kos6929ba89a2048.png', '[\"../uploads/kos6929ba89a331b_0.png\"]', 3, 1, 'tersedia', 0, '2025-11-28 15:06:49', '2025-11-30 07:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` enum('laporan','pertanyaan','keluhan','lainnya') DEFAULT 'laporan',
  `status` enum('baru','dibalas','selesai') DEFAULT 'baru',
  `dibaca_user` tinyint(1) DEFAULT '0',
  `dibaca_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int NOT NULL,
  `pemesanan_id` int NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `metode_pembayaran` enum('transfer','tunai') NOT NULL,
  `status_pembayaran` enum('menunggu','lunas','gagal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'menunggu',
  `alasan_penolakan` text,
  `tanggal_bayar` datetime DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pemesanan_id`, `jumlah_bayar`, `metode_pembayaran`, `status_pembayaran`, `alasan_penolakan`, `tanggal_bayar`, `bukti_bayar`, `created_at`) VALUES
(14, 16, '500000.00', 'transfer', 'lunas', NULL, '2025-11-14 21:58:41', 'bukti_16_1763132321.jpg', '2025-11-14 14:56:42'),
(15, 18, '6000000.00', 'transfer', 'lunas', NULL, '2025-11-28 20:00:29', 'bukti_18_1764334829.png', '2025-11-26 06:15:09'),
(16, 20, '500000.00', 'transfer', 'lunas', NULL, '2025-11-28 21:44:05', 'bukti_20_1764341045.jpg', '2025-11-28 14:43:27'),
(17, 21, '4200000.00', 'transfer', 'lunas', NULL, '2025-11-29 21:51:01', 'bukti_21_1764427861.png', '2025-11-29 14:50:37'),
(18, 22, '8400000.00', 'transfer', 'gagal', 'nipu ya', '2025-11-30 14:09:16', 'bukti_22_1764486556.jpg', '2025-11-30 07:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id` int NOT NULL,
  `kos_id` int NOT NULL,
  `pencari_id` int NOT NULL,
  `pemilik_id` int NOT NULL,
  `tanggal_pemesanan` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `durasi_bulan` int NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('menunggu','dikonfirmasi','ditolak','dibatalkan','selesai') DEFAULT 'menunggu',
  `catatan` text,
  `catatan_pembatalan` text,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status_pembayaran` enum('menunggu','pending','lunas','gagal') DEFAULT 'menunggu',
  `status_penyewaan` enum('aktif','selesai') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id`, `kos_id`, `pencari_id`, `pemilik_id`, `tanggal_pemesanan`, `tanggal_mulai`, `tanggal_selesai`, `durasi_bulan`, `total_harga`, `status`, `catatan`, `catatan_pembatalan`, `bukti_pembayaran`, `created_at`, `updated_at`, `status_pembayaran`, `status_penyewaan`) VALUES
(16, 17, 1, 3, '2025-11-14 21:50:19', '2025-11-14', '2025-12-14', 1, '500000.00', 'selesai', NULL, NULL, NULL, '2025-11-14 14:50:19', '2025-11-28 13:59:14', 'lunas', 'selesai'),
(18, 1, 1, 3, '2025-11-26 13:03:18', '2025-11-26', '2026-11-26', 12, '6000000.00', 'dikonfirmasi', '', NULL, NULL, '2025-11-26 06:03:18', '2025-11-28 13:59:03', 'lunas', NULL),
(19, 17, 1, 3, '2025-11-28 21:05:43', '2025-11-28', '2026-02-28', 3, '1500000.00', 'dibatalkan', '', NULL, NULL, '2025-11-28 14:05:43', '2025-11-28 14:05:54', 'menunggu', NULL),
(20, 17, 1, 3, '2025-11-28 21:18:31', '2025-11-28', '2025-12-28', 1, '500000.00', 'dikonfirmasi', '', NULL, NULL, '2025-11-28 14:18:31', '2025-11-28 14:45:09', 'lunas', NULL),
(21, 18, 1, 3, '2025-11-29 21:50:15', '2025-11-29', '2026-05-29', 6, '4200000.00', 'selesai', '', NULL, NULL, '2025-11-29 14:50:15', '2025-11-29 14:55:29', 'lunas', 'selesai'),
(22, 18, 1, 3, '2025-11-29 21:56:02', '2025-11-29', '2026-11-29', 12, '8400000.00', 'ditolak', 'oke saya akan memesan ini', 'nipu ya', NULL, '2025-11-29 14:56:02', '2025-11-30 07:58:14', 'gagal', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `alamat` text,
  `universitas` varchar(255) DEFAULT NULL,
  `bio` text,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('pemilik','pencari','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pencari',
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `telepon`, `alamat`, `universitas`, `bio`, `jenis_kelamin`, `tanggal_lahir`, `foto_profil`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 'pencarii', 'pencari@gmail.com', '$2y$10$xtmxoSWOptz2k1Cqxq1Jh.j4gGzaKsZYeACuFxFUNiItPSpHfe44e', '085212748146', '', NULL, 'appaan ni', 'P', '2025-11-14', 'profile_1_1763132565.jpeg', 'pencari', 1, '2025-09-26 15:21:15', '2025-11-28 13:53:25'),
(2, 'deni', 'admin@gmail.com', '$2y$10$FSYH6pzsfEBeTsAQE15xxe0j6HwosJ7HqQZDALTlE6bwZvnRri3ce', '085212748146', 'p', '', '', 'L', '2025-11-14', 'profile_2_1763125752.png', 'admin', 1, '2025-09-26 15:21:15', '2025-11-28 13:56:40'),
(3, 'si kaya', 'pemilik@gmail.com', '$2y$10$YX8vsxMMFbmjzk5XQLJ79OpvveVv1942g/rfjs37aErHjgugU0nNa', '085212748146', 'mendalo', NULL, 'menghadirkan kos terbaik', 'L', '2025-11-14', 'profile_3_1763131264.jpeg', 'pemilik', 1, '2025-09-26 15:21:15', '2025-11-28 15:08:35'),
(11, 'Deni Khairul Anam', 'deni@gmail.com', '$2y$10$nUm8H8pMbl0j4V2LL84PLuOh8fHRc8qDmgs8iXvrAaGI3rDzDgv6m', '081280119818', NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 1, '2025-11-28 16:01:26', '2025-11-28 16:05:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balasan_laporan`
--
ALTER TABLE `balasan_laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_id` (`laporan_id`),
  ADD KEY `pengirim_id` (`pengirim_id`);

--
-- Indexes for table `daerah`
--
ALTER TABLE `daerah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kos`
--
ALTER TABLE `kos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daerah_id` (`daerah_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemesanan_id` (`pemesanan_id`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kos_id` (`kos_id`),
  ADD KEY `pencari_id` (`pencari_id`),
  ADD KEY `pemilik_id` (`pemilik_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balasan_laporan`
--
ALTER TABLE `balasan_laporan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `daerah`
--
ALTER TABLE `daerah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kos`
--
ALTER TABLE `kos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `balasan_laporan`
--
ALTER TABLE `balasan_laporan`
  ADD CONSTRAINT `balasan_laporan_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kos`
--
ALTER TABLE `kos`
  ADD CONSTRAINT `kos_ibfk_1` FOREIGN KEY (`daerah_id`) REFERENCES `daerah` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kos_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`kos_id`) REFERENCES `kos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`pencari_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemesanan_ibfk_3` FOREIGN KEY (`pemilik_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
