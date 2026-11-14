-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 12:05 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yza_automotive_laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_cuci`
--

CREATE TABLE `booking_cuci` (
  `id_booking_cuci` bigint(20) UNSIGNED NOT NULL,
  `kode_booking` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_kendaraan_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_paket` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `status` enum('menunggu','diterima','proses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_cuci`
--

INSERT INTO `booking_cuci` (`id_booking_cuci`, `kode_booking`, `id_transaksi`, `id_pelanggan`, `id_kendaraan_pelanggan`, `id_paket`, `id_petugas`, `tanggal`, `jam`, `harga`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'BCW-202609-067D478B', 5, 1, 1, 1, 2, '2026-09-26', '13:00:00', 75000.00, 'selesai', 'Paket 3 QA', '2026-09-23 08:12:46', '2026-09-23 08:12:46');

-- --------------------------------------------------------

--
-- Table structure for table `booking_mobil`
--

CREATE TABLE `booking_mobil` (
  `id_booking` bigint(20) UNSIGNED NOT NULL,
  `kode_booking` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `tanggal_booking` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_expired` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','dikonfirmasi','dibatalkan','selesai','kadaluarsa') NOT NULL DEFAULT 'menunggu',
  `id_petugas_konfirmasi` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_mobil`
--

INSERT INTO `booking_mobil` (`id_booking`, `kode_booking`, `id_pelanggan`, `id_mobil`, `tanggal_booking`, `tanggal_expired`, `catatan`, `status`, `id_petugas_konfirmasi`, `created_at`, `updated_at`) VALUES
(1, 'BKG-202609-6DF967', 1, 5, '2026-09-23 12:40:49', '2026-09-26 12:40:49', 'Booking A Paket 2', 'dibatalkan', 2, '2026-09-23 05:40:49', '2026-09-23 05:40:58'),
(2, 'BKG-202609-62F6EB', 2, 5, '2026-09-23 12:40:49', '2026-09-23 12:41:17', 'Booking B Paket 2', 'kadaluarsa', NULL, '2026-09-23 05:40:49', '2026-09-23 05:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `booking_servis`
--

CREATE TABLE `booking_servis` (
  `id_booking_servis` bigint(20) UNSIGNED NOT NULL,
  `kode_booking` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_kendaraan_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_jenis_servis` bigint(20) UNSIGNED DEFAULT NULL,
  `id_preferred_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `keluhan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','diterima','ditolak','dibatalkan','diproses','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_servis`
--

INSERT INTO `booking_servis` (`id_booking_servis`, `kode_booking`, `id_pelanggan`, `id_kendaraan_pelanggan`, `id_jenis_servis`, `id_preferred_petugas`, `tanggal`, `jam`, `keluhan`, `catatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'BSV-202609-52D02542', 1, 1, 1, NULL, '2026-09-26', '09:00:00', 'QA servis', 'Paket 3 QA', 'selesai', '2026-09-23 08:12:25', '2026-09-23 08:12:46'),
(16155, 'BSV-202610-89C56E36', 750, 24348, 1, NULL, '2026-10-12', '09:00:00', 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Skenario Uji Demo Resmi', 'diproses', '2026-10-11 15:09:45', '2026-10-11 15:09:46'),
(16156, 'BSV-202610-29E1F146', 750, 24348, 1, NULL, '2026-10-12', '09:00:00', 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Skenario Uji Demo Resmi', 'diproses', '2026-10-11 15:10:18', '2026-10-11 15:10:18'),
(16157, 'BSV-202610-C6CC10A5', 750, 24348, 1, NULL, '2026-10-12', '09:00:00', 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Skenario Uji Demo Resmi', 'selesai', '2026-10-11 15:10:39', '2026-10-11 15:10:39'),
(16158, 'BSV-202610-AA7E9984', 750, 24348, 1, NULL, '2026-10-12', '09:00:00', 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Skenario Uji Demo Resmi', 'selesai', '2026-10-11 15:10:53', '2026-10-11 15:10:53'),
(16159, 'BSV-202610-61FE6915', 750, 24348, 3, NULL, '2026-10-22', '14:00:00', 'Mesin terasa sedikit kasar saat idle dan respons akselerasi terasa\r\nkurang halus. Mohon dilakukan pemeriksaan menyeluruh pada kondisi\r\nmesin, sistem pengapian, filter udara, serta komponen yang berkaitan\r\ndengan performa kendaraan.', 'Mohon informasikan terlebih dahulu apabila ditemukan komponen lain\r\nyang perlu diganti atau terdapat biaya tambahan di luar estimasi awal\r\nsebelum pekerjaan dilakukan.', 'menunggu', '2026-10-12 16:52:19', '2026-10-12 16:52:19'),
(16160, 'BSV-202610-3C92794F', 750, 24348, 3, NULL, '2026-10-22', '14:00:00', 'Mesin terasa sedikit kasar saat idle dan respons akselerasi terasa\r\nkurang halus. Mohon dilakukan pemeriksaan menyeluruh pada kondisi\r\nmesin, sistem pengapian, filter udara, serta komponen yang berkaitan\r\ndengan performa kendaraan.', 'Mohon informasikan terlebih dahulu apabila ditemukan komponen lain\r\nyang perlu diganti atau terdapat biaya tambahan di luar estimasi awal\r\nsebelum pekerjaan dilakukan.', 'menunggu', '2026-10-12 20:54:28', '2026-10-12 20:54:28'),
(16161, 'BSV-202610-B60C5ECB', 750, 24348, 3, NULL, '2026-10-22', '14:00:00', 'Mesin terasa sedikit kasar saat idle dan respons akselerasi terasa\r\nkurang halus. Mohon dilakukan pemeriksaan menyeluruh pada kondisi\r\nmesin, sistem pengapian, filter udara, serta komponen yang berkaitan\r\ndengan performa kendaraan.', 'Mohon informasikan terlebih dahulu apabila ditemukan komponen lain\r\nyang perlu diganti atau terdapat biaya tambahan di luar estimasi awal\r\nsebelum pekerjaan dilakukan.', 'menunggu', '2026-10-12 20:57:16', '2026-10-12 20:57:16'),
(16162, 'BSV-202610-0D6081F1', 750, 24348, 1, NULL, '2026-10-14', '10:00:00', 'Bunyi bunti gardan', NULL, 'dibatalkan', '2026-10-13 04:11:06', '2026-11-06 13:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan_sparepart`
--

CREATE TABLE `detail_penjualan_sparepart` (
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_penjualan_sparepart` bigint(20) UNSIGNED NOT NULL,
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_penjualan_sparepart`
--

INSERT INTO `detail_penjualan_sparepart` (`id_detail`, `id_penjualan_sparepart`, `id_sparepart`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 3, 35000.00, 105000.00),
(2, 2, 1, 1, 35000.00, 35000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_servis`
--

CREATE TABLE `detail_servis` (
  `id_detail_servis` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `id_jenis_servis` bigint(20) UNSIGNED DEFAULT NULL,
  `deskripsi_pekerjaan` varchar(255) NOT NULL,
  `biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_servis`
--

INSERT INTO `detail_servis` (`id_detail_servis`, `id_servis`, `id_jenis_servis`, `deskripsi_pekerjaan`, `biaya`, `catatan`) VALUES
(1, 1, NULL, 'Ganti oli', 150000.00, 'QA'),
(877, 13823, NULL, 'Jasa Ganti Oli Mesin & General Inspection 21 Titik', 75000.00, NULL),
(878, 13824, NULL, 'Jasa Ganti Oli Mesin & General Inspection 21 Titik', 75000.00, NULL),
(879, 13825, NULL, 'Jasa Ganti Oli Mesin & General Inspection 21 Titik', 75000.00, NULL),
(880, 13822, NULL, 'Jasa Penggantian Oli Mesin dan Pemeriksaan Filter Oli', 75000.00, 'Oli mesin lama dikuras dan diganti menggunakan oli baru sesuai spesifikasi kendaraan. Kondisi filter oli diperiksa. Setelah pengerjaan, dilakukan pengecekan level oli dan kebocoran pada sistem pelumasan mesin.');

-- --------------------------------------------------------

--
-- Table structure for table `detail_stok_masuk`
--

CREATE TABLE `detail_stok_masuk` (
  `id_detail_stok_masuk` bigint(20) UNSIGNED NOT NULL,
  `id_stok_masuk` bigint(20) UNSIGNED NOT NULL,
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_beli` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_stok_masuk`
--

INSERT INTO `detail_stok_masuk` (`id_detail_stok_masuk`, `id_stok_masuk`, `id_sparepart`, `jumlah`, `harga_beli`, `subtotal`) VALUES
(1, 1, 1, 6, 25000.00, 150000.00),
(226, 448, 461, 10, 950000.00, 9500000.00),
(227, 450, 450, 20, 125000.00, 2500000.00),
(228, 451, 450, 20, 125000.00, 2500000.00),
(229, 452, 450, 20, 125000.00, 2500000.00),
(230, 453, 450, 20, 125000.00, 2500000.00),
(231, 454, 450, 20, 125000.00, 2500000.00),
(232, 455, 450, 20, 125000.00, 2500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `event_pembayaran_gateway`
--

CREATE TABLE `event_pembayaran_gateway` (
  `id_event_gateway` bigint(20) UNSIGNED NOT NULL,
  `id_pembayaran_gateway` bigint(20) UNSIGNED NOT NULL,
  `event_key` varchar(190) NOT NULL,
  `transaction_status` varchar(40) NOT NULL,
  `status_code` varchar(10) NOT NULL,
  `fraud_status` varchar(30) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `provider_transaction_id` varchar(100) DEFAULT NULL,
  `payload_hash` varchar(128) DEFAULT NULL,
  `received_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_pembayaran_gateway`
--

INSERT INTO `event_pembayaran_gateway` (`id_event_gateway`, `id_pembayaran_gateway`, `event_key`, `transaction_status`, `status_code`, `fraud_status`, `payment_type`, `provider_transaction_id`, `payload_hash`, `received_at`, `created_at`, `updated_at`) VALUES
(425, 712, 'midtrans:YZA-MBR-140-UF6JHGIA:settlement:200:36a291df-67a0-4efd-a5f6-98f85d0d2beb', 'settlement', '200', 'accept', 'bank_transfer', '36a291df-67a0-4efd-a5f6-98f85d0d2beb', '3037b638dbb7608c27f81b0bb648bed328e54d4dd6f2f99e332e62463c07ad6e', '2026-10-05 11:00:37', '2026-10-05 04:00:37', '2026-10-05 04:00:37'),
(591, 998, 'midtrans:YZA-19956-ZXL5CIMBFK:pending:404:none', 'pending', '404', NULL, NULL, NULL, '8ab016dc534327c72924e74c9ed3bb3b710dcc34aee665b6797e1e27e0d24fa3', '2026-10-10 18:33:54', '2026-10-10 11:33:54', '2026-10-10 11:33:54'),
(595, 1004, 'midtrans:YZA-MBR-360-EI0AZV5K:settlement:200:5d749f24-c27c-438b-82b5-d2927d74bee0', 'settlement', '200', 'accept', 'bank_transfer', '5d749f24-c27c-438b-82b5-d2927d74bee0', '20f4dafd2f6e236c020147704e26ee879814bb6744b404251cc6d6dc93f1ae91', '2026-10-10 21:52:49', '2026-10-10 14:52:49', '2026-10-10 14:52:49'),
(596, 1006, 'midtrans:YZA-MBR-361-7AS6QXO3:pending:201:0a5c6eac-2bf9-455c-95f3-2d1319d7d12b', 'pending', '201', 'accept', 'bank_transfer', '0a5c6eac-2bf9-455c-95f3-2d1319d7d12b', 'dd9a7d67ec67900ded091d01aed48ed95d05bf7d322974cdbdf30d4db0dac8f3', '2026-10-11 18:58:46', '2026-10-11 11:58:46', '2026-10-11 11:58:46'),
(600, 1005, 'midtrans:YZA-19963-HKUBFK3NKZ:settlement:200:d6236338-dc84-4f80-8858-42f8c13c830b', 'settlement', '200', 'accept', 'bank_transfer', 'd6236338-dc84-4f80-8858-42f8c13c830b', 'ec0556e3a6feeb547a539e630da2a0a5342f7756556ae94133c77269cd388975', '2026-10-11 20:03:38', '2026-10-11 13:03:38', '2026-10-11 13:03:38'),
(607, 1018, 'midtrans:1018:70a95eaacd0fc56aedc491e1bdf8165f2806b55cdcb80f3a9f59a5e724dea592', 'settlement', '200', 'accept', 'bank_transfer', 'ca9c7788-c61b-41ab-8391-eab4e2ca6832', '70a95eaacd0fc56aedc491e1bdf8165f2806b55cdcb80f3a9f59a5e724dea592', '2026-11-03 10:59:15', '2026-11-03 03:59:15', '2026-11-03 03:59:15'),
(608, 1019, 'midtrans:1019:f0d109e4ed6db4ae6824e019cfa3f1553eb89f22b85d4dd314b41b047afe8489', 'settlement', '200', 'accept', 'bank_transfer', '0637e6c5-8097-4ed0-9e91-f89fed7d809f', 'f0d109e4ed6db4ae6824e019cfa3f1553eb89f22b85d4dd314b41b047afe8489', '2026-11-10 11:14:44', '2026-11-10 04:14:44', '2026-11-10 04:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `financial_adjustments`
--

CREATE TABLE `financial_adjustments` (
  `id_financial_adjustment` bigint(20) UNSIGNED NOT NULL,
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `adjustment_type` varchar(30) NOT NULL DEFAULT 'refund',
  `channel` varchar(30) NOT NULL DEFAULT 'offline',
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'requested',
  `reason` varchar(500) NOT NULL,
  `evidence_reference` varchar(255) DEFAULT NULL,
  `provider_reference` varchar(150) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_adjustment_events`
--

CREATE TABLE `financial_adjustment_events` (
  `id_financial_adjustment_event` bigint(20) UNSIGNED NOT NULL,
  `id_financial_adjustment` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `actor_petugas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `evidence_reference` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foto_mobil`
--

CREATE TABLE `foto_mobil` (
  `id_foto` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `foto_utama` tinyint(1) NOT NULL DEFAULT 0,
  `urutan` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foto_mobil`
--

INSERT INTO `foto_mobil` (`id_foto`, `id_mobil`, `nama_file`, `foto_utama`, `urutan`, `created_at`) VALUES
(2, 9, 'demo-yza-001-toyota-avanza.png', 1, 1, '2026-09-23 17:26:40'),
(3, 10, 'demo-yza-002-toyota-innova-zenix.png', 1, 1, '2026-09-23 17:26:40'),
(4, 11, 'demo-yza-003-honda-brio.png', 1, 1, '2026-09-23 17:26:40'),
(5, 12, 'demo-yza-004-honda-hrv.png', 1, 1, '2026-09-23 17:26:40'),
(6, 13, 'demo-yza-005-mitsubishi-xpander.png', 1, 1, '2026-09-23 17:26:40'),
(7, 14, 'demo-yza-006-suzuki-xl7.png', 1, 1, '2026-09-23 17:26:40'),
(8, 15, 'demo-yza-007-toyota-fortuner.png', 1, 1, '2026-09-23 17:26:40'),
(9, 16, 'demo-yza-008-honda-civic.png', 1, 1, '2026-09-23 17:26:40'),
(10, 17, 'demo-yza-009-toyota-raize.png', 1, 1, '2026-09-23 17:26:40'),
(11, 195, 'toyota-gr-supra.png', 1, 0, '2026-10-03 14:47:47'),
(13, 204, 'toyota-supra-mk4.png', 1, 0, '2026-10-03 18:05:49');

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_mobil`
--

CREATE TABLE `inspeksi_mobil` (
  `id_inspeksi` bigint(20) UNSIGNED NOT NULL,
  `id_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `tanggal_inspeksi` datetime NOT NULL DEFAULT current_timestamp(),
  `kondisi_mesin` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_body` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_interior` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_kelistrikan` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_ban` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_suspensi` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_ac` enum('baik','cukup','perlu_perbaikan') DEFAULT NULL,
  `kondisi_dokumen` enum('lengkap','tidak_lengkap','bermasalah') DEFAULT NULL,
  `kilometer` int(10) UNSIGNED DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `harga_penawaran` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspeksi_mobil`
--

INSERT INTO `inspeksi_mobil` (`id_inspeksi`, `id_pengajuan`, `id_petugas`, `tanggal_inspeksi`, `kondisi_mesin`, `kondisi_body`, `kondisi_interior`, `kondisi_kelistrikan`, `kondisi_ban`, `kondisi_suspensi`, `kondisi_ac`, `kondisi_dokumen`, `kilometer`, `catatan`, `harga_penawaran`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-09-23 15:29:46', 'baik', 'cukup', 'baik', 'baik', 'cukup', 'baik', 'baik', 'lengkap', 45000, 'QA', 100000000.00, '2026-09-23 08:29:46', '2026-09-23 08:29:46'),
(2, 2, 2, '2026-09-23 15:31:07', 'baik', 'baik', 'baik', 'baik', 'baik', 'baik', 'baik', 'lengkap', 45000, 'QA', 50000000.00, '2026-09-23 08:31:07', '2026-09-23 08:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_servis`
--

CREATE TABLE `jenis_servis` (
  `id_jenis_servis` bigint(20) UNSIGNED NOT NULL,
  `nama_servis` varchar(120) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `estimasi_harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_servis`
--

INSERT INTO `jenis_servis` (`id_jenis_servis`, `nama_servis`, `deskripsi`, `estimasi_harga`, `duration_minutes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Servis Berkala', 'Pemeriksaan dan perawatan berkala kendaraan', 350000.00, 120, 'aktif', '2026-09-23 03:31:41', '2026-10-12 08:45:36'),
(2, 'Ganti Oli', 'Penggantian oli mesin sesuai kebutuhan kendaraan', 150000.00, 60, 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(3, 'Tune Up', 'Pemeriksaan dan penyetelan performa mesin', 400000.00, 120, 'aktif', '2026-09-23 03:31:41', '2026-10-12 08:45:36'),
(4, 'Servis Rem', 'Pemeriksaan dan perawatan sistem pengereman', 300000.00, 120, 'aktif', '2026-09-23 03:31:41', '2026-10-12 08:45:36'),
(5, 'Servis AC', 'Pemeriksaan dan perawatan sistem AC kendaraan', 300000.00, 120, 'aktif', '2026-09-23 03:31:41', '2026-10-12 08:45:36'),
(6, 'Kelistrikan', 'Pemeriksaan sistem kelistrikan kendaraan', 250000.00, 120, 'aktif', '2026-09-23 03:31:41', '2026-10-12 08:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_sparepart`
--

CREATE TABLE `kategori_sparepart` (
  `id_kategori` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(80) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_sparepart`
--

INSERT INTO `kategori_sparepart` (`id_kategori`, `nama_kategori`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Oli', 'Pelumas mesin dan transmisi', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(2, 'Filter', 'Filter oli, udara, kabin, dan bahan bakar', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(3, 'Rem', 'Komponen sistem pengereman', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(4, 'Kelistrikan', 'Komponen elektrikal kendaraan', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(5, 'Ban', 'Ban dan komponen terkait', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(6, 'Aki', 'Baterai kendaraan', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(7, 'Lampu', 'Lampu dan komponen pencahayaan', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(8, 'Mesin', 'Komponen mesin kendaraan', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(9, 'Lainnya', 'Kategori spare part lainnya', 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(240, 'Perawatan', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan_pelanggan`
--

CREATE TABLE `kendaraan_pelanggan` (
  `id_kendaraan_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_penjualan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pesanan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_serah_terima` bigint(20) UNSIGNED DEFAULT NULL,
  `merk` varchar(80) NOT NULL,
  `model` varchar(100) NOT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `tahun` smallint(5) UNSIGNED DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `transmisi` enum('manual','automatic','cvt','dct','lainnya') DEFAULT NULL,
  `bahan_bakar` enum('bensin','diesel','hybrid','listrik','lainnya') DEFAULT NULL,
  `nomor_polisi` varchar(20) DEFAULT NULL,
  `nomor_rangka` varchar(100) DEFAULT NULL,
  `nomor_mesin` varchar(100) DEFAULT NULL,
  `kilometer` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('aktif','dijual','ditukar','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kendaraan_pelanggan`
--

INSERT INTO `kendaraan_pelanggan` (`id_kendaraan_pelanggan`, `id_pelanggan`, `id_penjualan`, `id_pesanan`, `id_serah_terima`, `merk`, `model`, `tipe`, `tahun`, `warna`, `transmisi`, `bahan_bakar`, `nomor_polisi`, `nomor_rangka`, `nomor_mesin`, `kilometer`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, NULL, 'Toyota', 'Avanza', '1.5 G', 2022, 'Hitam', 'automatic', 'bensin', 'B 1234 YZA', 'DEV2B-RANGKA-001', 'DEV2B-MESIN-001', 45000, 'dijual', '2026-09-23 04:44:53', '2026-09-23 08:31:38'),
(24348, 750, 2912, 1, 1, 'Toyota', 'GR Supra', 'Performance', 2024, 'Obsidian Black', 'automatic', 'bensin', 'B 1984 YZA', 'YZA-GRSUPRA-2024-001', 'YZA-GRSUPRA-B58-001', 5000, 'aktif', '2026-10-11 13:22:10', '2026-10-12 15:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id_membership` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `member_id` varchar(30) NOT NULL,
  `status` enum('pending_payment','active','cancelled') NOT NULL DEFAULT 'pending_payment',
  `activation_fee` decimal(15,2) NOT NULL,
  `registered_at` datetime NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `gateway_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id_membership`, `id_pelanggan`, `member_id`, `status`, `activation_fee`, `registered_at`, `activated_at`, `cancelled_at`, `gateway_payment_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'YZA-MBR-000001', 'active', 249000.00, '2026-10-04 10:32:19', '2026-10-04 10:32:19', NULL, NULL, '2026-10-04 03:32:19', '2026-10-04 03:32:19'),
(2, 2, 'YZA-MBR-000002', 'active', 249000.00, '2026-10-04 10:32:19', '2026-10-04 10:32:19', NULL, NULL, '2026-10-04 03:32:19', '2026-10-04 03:32:19'),
(3, 3, 'YZA-MBR-000003', 'active', 249000.00, '2026-10-04 10:32:19', '2026-10-04 10:32:19', NULL, NULL, '2026-10-04 03:32:19', '2026-10-04 03:32:19'),
(49, 439, 'YZA-DEMO-7C122E9E', 'active', 249000.00, '2026-10-04 12:38:53', '2026-10-04 12:38:53', NULL, NULL, '2026-10-04 05:38:53', '2026-10-04 05:38:53'),
(50, 440, 'YZA-DEMO-A6096436', 'pending_payment', 249000.00, '2026-10-04 12:38:53', NULL, NULL, NULL, '2026-10-04 05:38:53', '2026-10-04 05:38:53'),
(138, 528, 'YZA-MBR-000528', 'pending_payment', 249000.00, '2026-10-04 21:46:01', NULL, NULL, NULL, '2026-10-04 14:46:01', '2026-10-04 14:46:01'),
(139, 529, 'YZA-MBR-000529', 'pending_payment', 249000.00, '2026-10-04 23:07:01', NULL, NULL, NULL, '2026-10-04 16:07:01', '2026-10-04 16:07:01'),
(140, 530, 'YZA-MBR-000530', 'active', 249000.00, '2026-10-05 09:58:12', '2026-10-05 11:00:37', NULL, 712, '2026-10-05 02:58:12', '2026-10-05 04:00:37'),
(360, 750, 'YZA-MBR-000750', 'active', 249000.00, '2026-10-10 21:52:26', '2026-10-10 21:52:49', NULL, 1004, '2026-10-10 14:52:26', '2026-10-10 14:52:49'),
(361, 751, 'YZA-MBR-000751', 'pending_payment', 249000.00, '2026-10-11 18:55:12', NULL, NULL, NULL, '2026-10-11 11:55:12', '2026-10-11 11:55:12'),
(362, 752, 'YZA-MBR-000752', 'active', 249000.00, '2026-11-03 10:58:06', '2026-11-03 10:59:06', NULL, 1018, '2026-11-03 03:58:06', '2026-11-03 03:59:15'),
(363, 753, 'YZA-MBR-000753', 'active', 249000.00, '2026-11-10 11:13:28', '2026-11-10 11:14:39', NULL, 1019, '2026-11-10 04:13:28', '2026-11-10 04:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_09_28_000000_create_yza_identity_tables', 1),
(2, '2026_09_28_010000_create_mobil_tables', 2),
(4, '2026_09_28_020000_create_sparepart_master_tables', 3),
(5, '2026_09_28_030000_create_kendaraan_pelanggan_table', 4),
(6, '2026_09_28_040000_create_service_carwash_settings_tables', 5),
(7, '2026_09_29_050000_create_inventory_tables', 6),
(8, '2026_09_29_060000_create_booking_test_drive_tables', 7),
(9, '2026_09_29_070000_create_booking_servis_table', 8),
(10, '2026_09_29_080000_create_booking_cuci_table', 9),
(11, '2026_09_29_090000_create_servis_workorder_tables', 10),
(12, '2026_09_29_100000_create_servis_sparepart_table', 11),
(13, '2026_09_29_110000_create_transaksi_table', 12),
(14, '2026_09_29_120000_create_pembayaran_table', 13),
(15, '2026_09_29_130000_create_penjualan_sparepart_tables', 14),
(16, '2026_09_29_140000_create_penjualan_mobil_table', 15),
(17, '2026_09_29_150000_create_used_car_submission_tables', 16),
(18, '2026_09_29_160000_create_pembelian_mobil_bekas_table', 17),
(19, '2026_09_30_170000_create_trade_in_table', 18),
(20, '2026_09_30_180000_create_yza_points_tables', 19),
(21, '2026_09_30_190000_create_promo_member_table', 20),
(22, '2026_09_30_200000_create_midtrans_gateway_tables', 21),
(23, '2026_10_03_210000_add_toyota_gr_supra_to_catalog', 22),
(24, '2026_10_03_220000_add_toyota_supra_catalog_variant', 23),
(25, '2026_10_04_010000_rename_toyota_supra_to_mk4', 24),
(26, '2026_10_04_020000_create_memberships_table', 25),
(27, '2026_10_04_021000_extend_gateway_for_membership', 25),
(28, '2026_10_04_022000_add_membership_fee_setting', 25),
(29, '2026_10_04_023000_upgrade_promo_engine', 26),
(30, '2026_10_04_024000_add_promo_snapshot_to_transactions', 27),
(31, '2026_10_04_025000_add_promo_projection_fields', 27),
(32, '2026_10_04_026000_add_redemption_status', 27),
(33, '2026_10_04_027000_add_trade_in_promo_scope', 27),
(34, '2026_10_04_028000_add_spk_association_to_promo', 28),
(35, '2026_10_04_029000_create_promo_simulations', 29),
(36, '2026_10_04_030000_create_mobil_gallery_variants', 30),
(37, '2026_10_04_031000_replace_duplicate_used_avanza_catalog', 31),
(38, '2026_10_04_040000_add_structured_address_to_pelanggan_table', 32),
(39, '2026_10_10_010000_create_member_vehicle_purchase_tables', 33),
(40, '2026_10_10_020000_add_limited_edition_to_mobil', 34),
(41, '2026_10_10_030000_add_showcase_slug_to_mobil', 35),
(42, '2026_10_11_040000_create_member_vehicle_preorders_table', 36),
(43, '2026_10_11_050000_create_vehicle_inventory_tables', 37),
(44, '2026_10_11_120000_add_payment_verification_audit_fields', 38),
(45, '2026_10_11_210000_add_finish_url_to_pembayaran_gateway', 39),
(46, '2026_10_11_220000_add_pdi_pickup_ownership_workflow', 40),
(47, '2026_10_12_010000_add_yza_service_scheduling_phase2a', 41),
(48, '2026_10_12_020000_add_yza_service_personnel_phase3a', 42),
(49, '2026_10_12_030000_add_yza_service_execution_phase4a', 43),
(50, '2026_10_13_010000_add_sparepart_fitment_metadata', 44),
(52, '2026_10_15_010000_create_yza_permissions_tables', 45),
(53, '2026_10_15_020000_add_yza_service_assignment_history', 45),
(54, '2026_10_15_030000_create_sales_management_tables', 46),
(55, '2026_10_15_040000_create_sales_awards_tables', 47),
(56, '2026_10_15_050000_create_financial_adjustments_tables', 48);

-- --------------------------------------------------------

--
-- Table structure for table `mobil`
--

CREATE TABLE `mobil` (
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `kode_mobil` varchar(30) NOT NULL,
  `showcase_slug` varchar(80) DEFAULT NULL,
  `merk` varchar(80) NOT NULL,
  `model` varchar(100) NOT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `tahun` smallint(5) UNSIGNED DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `transmisi` enum('manual','automatic','cvt','dct','lainnya') DEFAULT NULL,
  `bahan_bakar` enum('bensin','diesel','hybrid','listrik','lainnya') DEFAULT NULL,
  `nomor_rangka` varchar(100) NOT NULL,
  `nomor_mesin` varchar(100) NOT NULL,
  `kilometer` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kondisi` enum('baru','bekas') NOT NULL,
  `status` enum('tersedia','dibooking','dalam_pemeriksaan','terjual','nonaktif') NOT NULL DEFAULT 'tersedia',
  `is_limited_edition` tinyint(1) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `sumber_mobil` enum('dealer','pembelian_bekas','trade_in','lainnya') NOT NULL DEFAULT 'dealer',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`id_mobil`, `kode_mobil`, `showcase_slug`, `merk`, `model`, `tipe`, `tahun`, `warna`, `transmisi`, `bahan_bakar`, `nomor_rangka`, `nomor_mesin`, `kilometer`, `harga_beli`, `harga_jual`, `kondisi`, `status`, `is_limited_edition`, `deskripsi`, `tanggal_masuk`, `sumber_mobil`, `created_at`, `updated_at`) VALUES
(1, 'DEV-2A-001', NULL, 'Toyota', 'Avanza', '1.5 G CVT', 2026, 'Graphite Silver', 'cvt', 'bensin', 'DEV2A-RANGKA-001', 'DEV2A-MESIN-001', 12, 200000000.00, 285000000.00, 'baru', 'nonaktif', 0, 'Unit development untuk verifikasi Tahap 2A.', '2026-09-23', 'dealer', '2026-09-23 04:24:40', '2026-09-23 04:27:52'),
(5, 'DEV-P2-MOBIL-01', NULL, 'Toyota', 'Yaris', '1.5 S', 2022, 'Putih', 'automatic', 'bensin', 'DEV-P2-RANGKA-01', 'DEV-P2-MESIN-01', 12000, 220000000.00, 245000000.00, 'bekas', 'terjual', 0, 'Unit development Paket 2', '2026-09-23', 'dealer', '2026-09-23 05:40:29', '2026-09-23 05:41:34'),
(6, 'MOB-202609-MOBILIO', NULL, 'Honda', 'Mobilio', '1.5 E CVT', 2022, 'Deep Metallic Blue', 'cvt', 'bensin', 'DEV-P4-RANGKA-01', 'DEV-P4-MESIN-01', 45000, 100000000.00, 0.00, 'bekas', 'tersedia', 0, 'Honda Mobilio bekas dengan kabin luas dan praktis untuk kebutuhan keluarga.', '2026-09-23', 'pembelian_bekas', '2026-09-23 08:29:46', '2026-10-04 08:51:45'),
(7, 'DEV-P4-TRADE-01', NULL, 'Honda', 'Brio', 'Satya', 2024, 'Putih', 'manual', 'bensin', 'DEV-P4-DEALER-RANGKA', 'DEV-P4-DEALER-MESIN', 0, 0.00, 80000000.00, 'baru', 'terjual', 0, 'QA dealer', '2026-09-23', 'dealer', '2026-09-23 08:31:07', '2026-09-23 08:31:07'),
(8, 'MOB-202609-XENIA', NULL, 'Daihatsu', 'Xenia', '1.5 R CVT', 2022, 'Silver Metallic', 'cvt', 'bensin', 'DEV-P4-TRADE-RANGKA', 'DEV-P4-TRADE-MESIN', 45000, 50000000.00, 0.00, 'bekas', 'tersedia', 0, 'Daihatsu Xenia bekas yang nyaman dan efisien untuk perjalanan keluarga.', '2026-09-23', 'trade_in', '2026-09-23 08:31:07', '2026-10-04 08:51:45'),
(9, 'DEMO-YZA-001', NULL, 'Toyota', 'Avanza', '1.5 G CVT', 2026, 'White Pearl', 'cvt', 'bensin', 'DEMOYZA00000000001', 'DEMENG-YZA-001', 0, 273000000.00, 299000000.00, 'baru', 'tersedia', 0, 'Toyota Avanza 1.5 G CVT dengan kabin lega dan transmisi otomatis. Cocok untuk kebutuhan keluarga maupun mobilitas harian.', '2026-09-20', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(10, 'DEMO-YZA-002', NULL, 'Toyota', 'Innova Zenix', '2.0 G CVT', 2026, 'Silver Metallic', 'cvt', 'hybrid', 'DEMOYZA00000000002', 'DEMENG-YZA-002', 0, 462000000.00, 499000000.00, 'baru', 'tersedia', 0, 'Toyota Innova Zenix 2.0 G CVT menawarkan kabin lapang dan kenyamanan perjalanan jarak jauh. Pilihan praktis untuk keluarga modern.', '2026-09-19', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(11, 'DEMO-YZA-003', NULL, 'Honda', 'Brio', 'RS CVT', 2025, 'Phoenix Orange Pearl', 'cvt', 'bensin', 'DEMOYZA00000000003', 'DEMENG-YZA-003', 35, 226000000.00, 249000000.00, 'baru', 'tersedia', 0, 'Honda Brio RS CVT berukuran kompak dengan gaya sporty dan mudah dikendarai di area perkotaan. Kondisi siap pakai dengan kilometer sangat rendah.', '2026-09-18', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(12, 'DEMO-YZA-004', NULL, 'Honda', 'HR-V', 'SE CVT', 2025, 'Platinum White Pearl', 'cvt', 'bensin', 'DEMOYZA00000000004', 'DEMENG-YZA-004', 42, 409000000.00, 449000000.00, 'baru', 'tersedia', 0, 'Honda HR-V SE CVT menghadirkan kabin modern, posisi berkendara nyaman, dan ruang bagasi fleksibel. Unit baru dengan fitur yang lengkap.', '2026-09-17', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(13, 'DEMO-YZA-005', NULL, 'Mitsubishi', 'Xpander', 'Ultimate CVT', 2026, 'Graphite Gray', 'cvt', 'bensin', 'DEMOYZA00000000005', 'DEMENG-YZA-005', 18, 328000000.00, 359000000.00, 'baru', 'tersedia', 0, 'Mitsubishi Xpander Ultimate CVT memiliki kabin lapang dan karakter berkendara nyaman. Sesuai untuk keluarga dan perjalanan luar kota.', '2026-09-16', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(14, 'DEMO-YZA-006', NULL, 'Suzuki', 'XL7', 'Alpha AT', 2025, 'Brave Khaki', 'automatic', 'bensin', 'DEMOYZA00000000006', 'DEMENG-YZA-006', 67, 266000000.00, 289000000.00, 'baru', 'tersedia', 0, 'Suzuki XL7 Alpha AT memadukan tampilan crossover dengan kabin tiga baris yang praktis. Unit terawat dan siap menemani aktivitas harian.', '2026-09-15', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(15, 'DEMO-YZA-007', NULL, 'Toyota', 'Fortuner', '2.4 VRZ AT', 2023, 'Attitude Black', 'automatic', 'diesel', 'DEMOYZA00000000007', 'DEMENG-YZA-007', 38500, 555000000.00, 599000000.00, 'bekas', 'tersedia', 0, 'Toyota Fortuner 2.4 VRZ AT bekas dengan tampilan gagah dan kabin nyaman. Riwayat perawatan tercatat dan kondisi siap digunakan.', '2026-09-12', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(16, 'DEMO-YZA-008', NULL, 'Honda', 'Civic', 'RS', 2022, 'Ignite Red Metallic', 'cvt', 'bensin', 'DEMOYZA00000000008', 'DEMENG-YZA-008', 27800, 487000000.00, 529000000.00, 'bekas', 'tersedia', 0, 'Honda Civic RS bekas bergaya sporty dengan performa responsif dan interior nyaman. Kilometer terukur untuk pemakaian harian.', '2026-09-10', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(17, 'DEMO-YZA-009', NULL, 'Toyota', 'Raize', '1.0 Turbo GR Sport', 2024, 'Turquoise Blue', 'cvt', 'bensin', 'DEMOYZA00000000009', 'DEMENG-YZA-009', 16200, 214000000.00, 239000000.00, 'bekas', 'tersedia', 0, 'Toyota Raize 1.0 Turbo GR Sport bekas yang lincah untuk kota dan tetap nyaman untuk perjalanan akhir pekan. Kondisi terawat dengan fitur modern.', '2026-09-08', 'dealer', '2026-09-23 17:26:40', '2026-09-23 17:26:40'),
(195, 'YZA-TOYOTA-GR-SUPRA-2024', NULL, 'Toyota', 'GR Supra', 'Performance', 2024, 'Supra Red', 'automatic', 'bensin', 'YZA-GRSUPRA-2024-001', 'YZA-GRSUPRA-B58-001', 0, 1850000000.00, 2350000000.00, 'baru', 'tersedia', 0, 'Toyota GR Supra Performance 2024 dengan karakter sporty, kabin premium, dan pengalaman berkendara eksklusif.', '2026-10-03', 'dealer', '2026-10-03 14:47:47', '2026-10-03 14:47:47'),
(204, 'YZA-TOYOTA-SUPRA-MK4-2025', NULL, 'Toyota', 'Supra MK4', '2JZ-GTE Turbo', 1998, 'Supra Red', 'manual', 'bensin', 'YZA-SUPRA-2025-001', 'YZA-SUPRA-B58-001', 0, 1800000000.00, 2268600000.00, 'baru', 'tersedia', 0, 'Toyota Supra MK4 A80 klasik dengan bodi coupe 90-an, mesin 2JZ-GTE Turbo, dan karakter ikonik untuk kolektor otomotif.', '2026-10-03', 'dealer', '2026-10-03 16:55:25', '2026-10-03 18:05:49'),
(250, 'PREM-01', NULL, 'Lamborghini', 'Revuelto', 'V12 Hybrid Performance', 2025, 'Rosso Mars', 'automatic', 'bensin', 'PREM-01-VIN', 'PREM-01-ENGINE', 0, 11250000000.00, 12500000000.00, 'baru', 'tersedia', 0, 'Revuelto premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:34', '2026-10-04 05:37:34'),
(251, 'PREM-02', NULL, 'Ferrari', 'SF90 Stradale', 'Assetto Fiorano', 2024, 'Rosso Corsa', 'automatic', 'bensin', 'PREM-02-VIN', 'PREM-02-ENGINE', 0, 13050000000.00, 14500000000.00, 'baru', 'tersedia', 0, 'SF90 Stradale premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:34', '2026-10-04 05:37:34'),
(252, 'PREM-03', NULL, 'Porsche', '911 GT3 RS', '4.0 Performance', 2024, 'Guards Red', 'automatic', 'bensin', 'PREM-03-VIN', 'PREM-03-ENGINE', 0, 7650000000.00, 8500000000.00, 'baru', 'tersedia', 0, '911 GT3 RS premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:34', '2026-10-04 05:37:34'),
(253, 'PREM-04', NULL, 'Mercedes Amg', 'GT Black Series', 'AMG Performance', 2024, 'Magno Grey', 'automatic', 'bensin', 'PREM-04-VIN', 'PREM-04-ENGINE', 0, 7650000000.00, 8500000000.00, 'baru', 'tersedia', 0, 'GT Black Series premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:34', '2026-10-04 05:37:34'),
(254, 'PREM-05', NULL, 'Aston Martin', 'DBS 770 Ultimate', 'V12 Ultimate', 2024, 'Minotaur Green', 'automatic', 'bensin', 'PREM-05-VIN', 'PREM-05-ENGINE', 0, 10800000000.00, 12000000000.00, 'baru', 'tersedia', 0, 'DBS 770 Ultimate premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35'),
(255, 'PREM-06', NULL, 'Chevrolet', 'Corvette Z06', '6.2L V8 Performance', 2024, 'Torch Red', 'automatic', 'bensin', 'PREM-06-VIN', 'PREM-06-ENGINE', 0, 3150000000.00, 3500000000.00, 'baru', 'tersedia', 0, 'Corvette Z06 premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35'),
(256, 'PREM-07', NULL, 'Ford', 'Mustang Shelby GT500', '5.2L V8 Shelby', 2024, 'Grabber Blue', 'automatic', 'bensin', 'PREM-07-VIN', 'PREM-07-ENGINE', 0, 2880000000.00, 3200000000.00, 'baru', 'tersedia', 0, 'Mustang Shelby GT500 premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35'),
(257, 'PREM-08', NULL, 'Dodge', 'Challenger SRT Hellcat Redeye', '6.2L Supercharged V8', 2023, 'TorRed', 'automatic', 'bensin', 'PREM-08-VIN', 'PREM-08-ENGINE', 0, 2250000000.00, 2500000000.00, 'baru', 'tersedia', 0, 'Challenger SRT Hellcat Redeye premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35'),
(258, 'PREM-09', NULL, 'Nissan', 'GT-R Nismo', 'Nismo Performance', 2024, 'Nismo Stealth Gray', 'automatic', 'bensin', 'PREM-09-VIN', 'PREM-09-ENGINE', 0, 5850000000.00, 6500000000.00, 'baru', 'tersedia', 0, 'GT-R Nismo premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35'),
(259, 'PREM-10', NULL, 'Lexus', 'LFA', '4.8L V10 Performance', 2012, 'Pearl White', 'automatic', 'bensin', 'PREM-10-VIN', 'PREM-10-ENGINE', 0, 8100000000.00, 9000000000.00, 'baru', 'tersedia', 0, 'LFA premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.', '2026-10-04', 'dealer', '2026-10-04 05:37:35', '2026-10-04 05:37:35');

-- --------------------------------------------------------

--
-- Table structure for table `mobil_galeri`
--

CREATE TABLE `mobil_galeri` (
  `id_galeri` bigint(20) UNSIGNED NOT NULL,
  `id_warna` bigint(20) UNSIGNED NOT NULL,
  `sudut` enum('front','side','rear','interior') NOT NULL,
  `path` varchar(255) NOT NULL,
  `urutan` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobil_galeri`
--

INSERT INTO `mobil_galeri` (`id_galeri`, `id_warna`, `sudut`, `path`, `urutan`, `created_at`, `updated_at`) VALUES
(237, 60, 'front', 'assets/images/vehicles/premium/lamborghini-revuelto/rosso-mars/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(238, 60, 'side', 'assets/images/vehicles/premium/lamborghini-revuelto/rosso-mars/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(239, 60, 'rear', 'assets/images/vehicles/premium/lamborghini-revuelto/rosso-mars/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(240, 60, 'interior', 'assets/images/vehicles/premium/lamborghini-revuelto/rosso-mars/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(241, 61, 'front', 'assets/images/vehicles/premium/lamborghini-revuelto/giallo-inti/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(242, 61, 'side', 'assets/images/vehicles/premium/lamborghini-revuelto/giallo-inti/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(243, 61, 'rear', 'assets/images/vehicles/premium/lamborghini-revuelto/giallo-inti/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(244, 61, 'interior', 'assets/images/vehicles/premium/lamborghini-revuelto/giallo-inti/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(245, 62, 'front', 'assets/images/vehicles/premium/lamborghini-revuelto/verde-selvans/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(246, 62, 'side', 'assets/images/vehicles/premium/lamborghini-revuelto/verde-selvans/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(247, 62, 'rear', 'assets/images/vehicles/premium/lamborghini-revuelto/verde-selvans/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(248, 62, 'interior', 'assets/images/vehicles/premium/lamborghini-revuelto/verde-selvans/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(249, 63, 'front', 'assets/images/vehicles/premium/lamborghini-revuelto/nero-noctis/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(250, 63, 'side', 'assets/images/vehicles/premium/lamborghini-revuelto/nero-noctis/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(251, 63, 'rear', 'assets/images/vehicles/premium/lamborghini-revuelto/nero-noctis/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(252, 63, 'interior', 'assets/images/vehicles/premium/lamborghini-revuelto/nero-noctis/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(253, 64, 'front', 'assets/images/vehicles/premium/lamborghini-revuelto/bianco-monocerus/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(254, 64, 'side', 'assets/images/vehicles/premium/lamborghini-revuelto/bianco-monocerus/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(255, 64, 'rear', 'assets/images/vehicles/premium/lamborghini-revuelto/bianco-monocerus/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(256, 64, 'interior', 'assets/images/vehicles/premium/lamborghini-revuelto/bianco-monocerus/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(257, 65, 'front', 'assets/images/vehicles/premium/ferrari-sf90-stradale/rosso-corsa/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(258, 65, 'side', 'assets/images/vehicles/premium/ferrari-sf90-stradale/rosso-corsa/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(259, 65, 'rear', 'assets/images/vehicles/premium/ferrari-sf90-stradale/rosso-corsa/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(260, 65, 'interior', 'assets/images/vehicles/premium/ferrari-sf90-stradale/rosso-corsa/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(261, 66, 'front', 'assets/images/vehicles/premium/ferrari-sf90-stradale/giallo-modena/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(262, 66, 'side', 'assets/images/vehicles/premium/ferrari-sf90-stradale/giallo-modena/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(263, 66, 'rear', 'assets/images/vehicles/premium/ferrari-sf90-stradale/giallo-modena/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(264, 66, 'interior', 'assets/images/vehicles/premium/ferrari-sf90-stradale/giallo-modena/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(265, 67, 'front', 'assets/images/vehicles/premium/ferrari-sf90-stradale/blu-corsa/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(266, 67, 'side', 'assets/images/vehicles/premium/ferrari-sf90-stradale/blu-corsa/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(267, 67, 'rear', 'assets/images/vehicles/premium/ferrari-sf90-stradale/blu-corsa/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(268, 67, 'interior', 'assets/images/vehicles/premium/ferrari-sf90-stradale/blu-corsa/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(269, 68, 'front', 'assets/images/vehicles/premium/ferrari-sf90-stradale/nero-daytona/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(270, 68, 'side', 'assets/images/vehicles/premium/ferrari-sf90-stradale/nero-daytona/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(271, 68, 'rear', 'assets/images/vehicles/premium/ferrari-sf90-stradale/nero-daytona/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(272, 68, 'interior', 'assets/images/vehicles/premium/ferrari-sf90-stradale/nero-daytona/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(273, 69, 'front', 'assets/images/vehicles/premium/ferrari-sf90-stradale/bianco-avus/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(274, 69, 'side', 'assets/images/vehicles/premium/ferrari-sf90-stradale/bianco-avus/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(275, 69, 'rear', 'assets/images/vehicles/premium/ferrari-sf90-stradale/bianco-avus/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(276, 69, 'interior', 'assets/images/vehicles/premium/ferrari-sf90-stradale/bianco-avus/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(277, 70, 'front', 'assets/images/vehicles/premium/porsche-911-gt3-rs/guards-red/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(278, 70, 'side', 'assets/images/vehicles/premium/porsche-911-gt3-rs/guards-red/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(279, 70, 'rear', 'assets/images/vehicles/premium/porsche-911-gt3-rs/guards-red/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(280, 70, 'interior', 'assets/images/vehicles/premium/porsche-911-gt3-rs/guards-red/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(281, 71, 'front', 'assets/images/vehicles/premium/porsche-911-gt3-rs/racing-yellow/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(282, 71, 'side', 'assets/images/vehicles/premium/porsche-911-gt3-rs/racing-yellow/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(283, 71, 'rear', 'assets/images/vehicles/premium/porsche-911-gt3-rs/racing-yellow/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(284, 71, 'interior', 'assets/images/vehicles/premium/porsche-911-gt3-rs/racing-yellow/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(285, 72, 'front', 'assets/images/vehicles/premium/porsche-911-gt3-rs/gt-silver-metallic/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(286, 72, 'side', 'assets/images/vehicles/premium/porsche-911-gt3-rs/gt-silver-metallic/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(287, 72, 'rear', 'assets/images/vehicles/premium/porsche-911-gt3-rs/gt-silver-metallic/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(288, 72, 'interior', 'assets/images/vehicles/premium/porsche-911-gt3-rs/gt-silver-metallic/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(289, 73, 'front', 'assets/images/vehicles/premium/porsche-911-gt3-rs/jet-black-metallic/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(290, 73, 'side', 'assets/images/vehicles/premium/porsche-911-gt3-rs/jet-black-metallic/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(291, 73, 'rear', 'assets/images/vehicles/premium/porsche-911-gt3-rs/jet-black-metallic/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(292, 73, 'interior', 'assets/images/vehicles/premium/porsche-911-gt3-rs/jet-black-metallic/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(293, 74, 'front', 'assets/images/vehicles/premium/porsche-911-gt3-rs/arctic-grey/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(294, 74, 'side', 'assets/images/vehicles/premium/porsche-911-gt3-rs/arctic-grey/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(295, 74, 'rear', 'assets/images/vehicles/premium/porsche-911-gt3-rs/arctic-grey/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(296, 74, 'interior', 'assets/images/vehicles/premium/porsche-911-gt3-rs/arctic-grey/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(297, 75, 'front', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/magno-grey/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(298, 75, 'side', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/magno-grey/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(299, 75, 'rear', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/magno-grey/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(300, 75, 'interior', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/magno-grey/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(301, 76, 'front', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/obsidian-black/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(302, 76, 'side', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/obsidian-black/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(303, 76, 'rear', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/obsidian-black/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(304, 76, 'interior', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/obsidian-black/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(305, 77, 'front', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/solarbeam-yellow/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(306, 77, 'side', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/solarbeam-yellow/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(307, 77, 'rear', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/solarbeam-yellow/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(308, 77, 'interior', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/solarbeam-yellow/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(309, 78, 'front', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/patagonia-red/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(310, 78, 'side', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/patagonia-red/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(311, 78, 'rear', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/patagonia-red/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(312, 78, 'interior', 'assets/images/vehicles/premium/mercedes-amg-gt-black-series/patagonia-red/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(313, 79, 'front', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/minotaur-green/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(314, 79, 'side', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/minotaur-green/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(315, 79, 'rear', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/minotaur-green/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(316, 79, 'interior', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/minotaur-green/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(317, 80, 'front', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/xenon-grey/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(318, 80, 'side', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/xenon-grey/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(319, 80, 'rear', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/xenon-grey/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(320, 80, 'interior', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/xenon-grey/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(321, 81, 'front', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/onyx-black/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(322, 81, 'side', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/onyx-black/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(323, 81, 'rear', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/onyx-black/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(324, 81, 'interior', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/onyx-black/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(325, 82, 'front', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/magnetic-silver/front.png', 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(326, 82, 'side', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/magnetic-silver/side.png', 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(327, 82, 'rear', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/magnetic-silver/rear.png', 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(328, 82, 'interior', 'assets/images/vehicles/premium/aston-martin-dbs-770-ultimate/magnetic-silver/interior.png', 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(329, 83, 'front', 'assets/images/vehicles/premium/chevrolet-corvette-z06/torch-red/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(330, 83, 'side', 'assets/images/vehicles/premium/chevrolet-corvette-z06/torch-red/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(331, 83, 'rear', 'assets/images/vehicles/premium/chevrolet-corvette-z06/torch-red/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(332, 83, 'interior', 'assets/images/vehicles/premium/chevrolet-corvette-z06/torch-red/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(333, 84, 'front', 'assets/images/vehicles/premium/chevrolet-corvette-z06/black/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(334, 84, 'side', 'assets/images/vehicles/premium/chevrolet-corvette-z06/black/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(335, 84, 'rear', 'assets/images/vehicles/premium/chevrolet-corvette-z06/black/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(336, 84, 'interior', 'assets/images/vehicles/premium/chevrolet-corvette-z06/black/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(337, 85, 'front', 'assets/images/vehicles/premium/chevrolet-corvette-z06/arctic-white/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(338, 85, 'side', 'assets/images/vehicles/premium/chevrolet-corvette-z06/arctic-white/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(339, 85, 'rear', 'assets/images/vehicles/premium/chevrolet-corvette-z06/arctic-white/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(340, 85, 'interior', 'assets/images/vehicles/premium/chevrolet-corvette-z06/arctic-white/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(341, 86, 'front', 'assets/images/vehicles/premium/chevrolet-corvette-z06/rapid-blue/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(342, 86, 'side', 'assets/images/vehicles/premium/chevrolet-corvette-z06/rapid-blue/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(343, 86, 'rear', 'assets/images/vehicles/premium/chevrolet-corvette-z06/rapid-blue/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(344, 86, 'interior', 'assets/images/vehicles/premium/chevrolet-corvette-z06/rapid-blue/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(345, 87, 'front', 'assets/images/vehicles/premium/chevrolet-corvette-z06/accelerate-yellow/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(346, 87, 'side', 'assets/images/vehicles/premium/chevrolet-corvette-z06/accelerate-yellow/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(347, 87, 'rear', 'assets/images/vehicles/premium/chevrolet-corvette-z06/accelerate-yellow/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(348, 87, 'interior', 'assets/images/vehicles/premium/chevrolet-corvette-z06/accelerate-yellow/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(349, 88, 'front', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/grabber-blue/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(350, 88, 'side', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/grabber-blue/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(351, 88, 'rear', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/grabber-blue/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(352, 88, 'interior', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/grabber-blue/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(353, 89, 'front', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/race-red/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(354, 89, 'side', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/race-red/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(355, 89, 'rear', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/race-red/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(356, 89, 'interior', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/race-red/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(357, 90, 'front', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/shadow-black/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(358, 90, 'side', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/shadow-black/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(359, 90, 'rear', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/shadow-black/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(360, 90, 'interior', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/shadow-black/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(361, 91, 'front', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/oxford-white/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(362, 91, 'side', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/oxford-white/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(363, 91, 'rear', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/oxford-white/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(364, 91, 'interior', 'assets/images/vehicles/premium/ford-mustang-shelby-gt500/oxford-white/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(365, 92, 'front', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/torred/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(366, 92, 'side', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/torred/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(367, 92, 'rear', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/torred/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(368, 92, 'interior', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/torred/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(369, 93, 'front', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/pitch-black/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(370, 93, 'side', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/pitch-black/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(371, 93, 'rear', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/pitch-black/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(372, 93, 'interior', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/pitch-black/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(373, 94, 'front', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/frostbite-blue/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(374, 94, 'side', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/frostbite-blue/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(375, 94, 'rear', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/frostbite-blue/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(376, 94, 'interior', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/frostbite-blue/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(377, 95, 'front', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/destroyer-grey/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(378, 95, 'side', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/destroyer-grey/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(379, 95, 'rear', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/destroyer-grey/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(380, 95, 'interior', 'assets/images/vehicles/premium/dodge-challenger-srt-hellcat-redeye/destroyer-grey/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(381, 96, 'front', 'assets/images/vehicles/premium/nissan-gt-r-nismo/nismo-stealth-gray/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(382, 96, 'side', 'assets/images/vehicles/premium/nissan-gt-r-nismo/nismo-stealth-gray/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(383, 96, 'rear', 'assets/images/vehicles/premium/nissan-gt-r-nismo/nismo-stealth-gray/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(384, 96, 'interior', 'assets/images/vehicles/premium/nissan-gt-r-nismo/nismo-stealth-gray/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(385, 97, 'front', 'assets/images/vehicles/premium/nissan-gt-r-nismo/pearl-white/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(386, 97, 'side', 'assets/images/vehicles/premium/nissan-gt-r-nismo/pearl-white/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(387, 97, 'rear', 'assets/images/vehicles/premium/nissan-gt-r-nismo/pearl-white/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(388, 97, 'interior', 'assets/images/vehicles/premium/nissan-gt-r-nismo/pearl-white/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(389, 98, 'front', 'assets/images/vehicles/premium/nissan-gt-r-nismo/vibrant-red/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(390, 98, 'side', 'assets/images/vehicles/premium/nissan-gt-r-nismo/vibrant-red/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(391, 98, 'rear', 'assets/images/vehicles/premium/nissan-gt-r-nismo/vibrant-red/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(392, 98, 'interior', 'assets/images/vehicles/premium/nissan-gt-r-nismo/vibrant-red/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(393, 99, 'front', 'assets/images/vehicles/premium/nissan-gt-r-nismo/super-silver/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(394, 99, 'side', 'assets/images/vehicles/premium/nissan-gt-r-nismo/super-silver/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(395, 99, 'rear', 'assets/images/vehicles/premium/nissan-gt-r-nismo/super-silver/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(396, 99, 'interior', 'assets/images/vehicles/premium/nissan-gt-r-nismo/super-silver/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(397, 100, 'front', 'assets/images/vehicles/premium/lexus-lfa/pearl-white/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(398, 100, 'side', 'assets/images/vehicles/premium/lexus-lfa/pearl-white/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(399, 100, 'rear', 'assets/images/vehicles/premium/lexus-lfa/pearl-white/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(400, 100, 'interior', 'assets/images/vehicles/premium/lexus-lfa/pearl-white/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(401, 101, 'front', 'assets/images/vehicles/premium/lexus-lfa/red/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(402, 101, 'side', 'assets/images/vehicles/premium/lexus-lfa/red/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(403, 101, 'rear', 'assets/images/vehicles/premium/lexus-lfa/red/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(404, 101, 'interior', 'assets/images/vehicles/premium/lexus-lfa/red/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(405, 102, 'front', 'assets/images/vehicles/premium/lexus-lfa/silver-metallic/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(406, 102, 'side', 'assets/images/vehicles/premium/lexus-lfa/silver-metallic/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(407, 102, 'rear', 'assets/images/vehicles/premium/lexus-lfa/silver-metallic/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(408, 102, 'interior', 'assets/images/vehicles/premium/lexus-lfa/silver-metallic/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(409, 103, 'front', 'assets/images/vehicles/premium/lexus-lfa/matte-black/front.png', 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(410, 103, 'side', 'assets/images/vehicles/premium/lexus-lfa/matte-black/side.png', 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(411, 103, 'rear', 'assets/images/vehicles/premium/lexus-lfa/matte-black/rear.png', 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(412, 103, 'interior', 'assets/images/vehicles/premium/lexus-lfa/matte-black/interior.png', 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `mobil_warna`
--

CREATE TABLE `mobil_warna` (
  `id_warna` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(80) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `hex` varchar(7) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `urutan` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobil_warna`
--

INSERT INTO `mobil_warna` (`id_warna`, `id_mobil`, `slug`, `nama`, `hex`, `is_default`, `urutan`, `created_at`, `updated_at`) VALUES
(60, 250, 'rosso-mars', 'Rosso Mars', '#b5121b', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(61, 250, 'giallo-inti', 'Giallo Inti', '#e5b51b', 0, 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(62, 250, 'verde-selvans', 'Verde Selvans', '#18513c', 0, 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(63, 250, 'nero-noctis', 'Nero Noctis', '#151618', 0, 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(64, 250, 'bianco-monocerus', 'Bianco Monocerus', '#f1f1ee', 0, 4, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(65, 251, 'rosso-corsa', 'Rosso Corsa', '#c81722', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(66, 251, 'giallo-modena', 'Giallo Modena', '#e5b51b', 0, 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(67, 251, 'blu-corsa', 'Blu Corsa', '#164b86', 0, 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(68, 251, 'nero-daytona', 'Nero Daytona', '#151618', 0, 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(69, 251, 'bianco-avus', 'Bianco Avus', '#f3f3f0', 0, 4, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(70, 252, 'guards-red', 'Guards Red', '#c91c26', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(71, 252, 'racing-yellow', 'Racing Yellow', '#e4b51b', 0, 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(72, 252, 'gt-silver-metallic', 'GT Silver Metallic', '#aeb3b8', 0, 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(73, 252, 'jet-black-metallic', 'Jet Black Metallic', '#151618', 0, 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(74, 252, 'arctic-grey', 'Arctic Grey', '#777d84', 0, 4, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(75, 253, 'magno-grey', 'Magno Grey', '#555961', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(76, 253, 'obsidian-black', 'Obsidian Black', '#151618', 0, 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(77, 253, 'solarbeam-yellow', 'Solarbeam Yellow', '#e1b21c', 0, 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(78, 253, 'patagonia-red', 'Patagonia Red', '#9d171f', 0, 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(79, 254, 'minotaur-green', 'Minotaur Green', '#315b46', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(80, 254, 'xenon-grey', 'Xenon Grey', '#737981', 0, 1, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(81, 254, 'onyx-black', 'Onyx Black', '#151618', 0, 2, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(82, 254, 'magnetic-silver', 'Magnetic Silver', '#b8bdc2', 0, 3, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(83, 255, 'torch-red', 'Torch Red', '#c81c26', 1, 0, '2026-10-04 06:18:22', '2026-10-04 06:18:22'),
(84, 255, 'black', 'Black', '#151618', 0, 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(85, 255, 'arctic-white', 'Arctic White', '#f1f1ee', 0, 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(86, 255, 'rapid-blue', 'Rapid Blue', '#1d609f', 0, 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(87, 255, 'accelerate-yellow', 'Accelerate Yellow', '#e3b51d', 0, 4, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(88, 256, 'grabber-blue', 'Grabber Blue', '#1d5e9c', 1, 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(89, 256, 'race-red', 'Race Red', '#b71c25', 0, 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(90, 256, 'shadow-black', 'Shadow Black', '#151618', 0, 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(91, 256, 'oxford-white', 'Oxford White', '#f1f1ee', 0, 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(92, 257, 'torred', 'TorRed', '#c71a25', 1, 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(93, 257, 'pitch-black', 'Pitch Black', '#151618', 0, 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(94, 257, 'frostbite-blue', 'Frostbite Blue', '#1e5790', 0, 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(95, 257, 'destroyer-grey', 'Destroyer Grey', '#737981', 0, 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(96, 258, 'nismo-stealth-gray', 'Nismo Stealth Gray', '#565b62', 1, 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(97, 258, 'pearl-white', 'Pearl White', '#f1f1ee', 0, 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(98, 258, 'vibrant-red', 'Vibrant Red', '#bd1a24', 0, 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(99, 258, 'super-silver', 'Super Silver', '#b8bdc2', 0, 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(100, 259, 'pearl-white', 'Pearl White', '#f1f1ee', 1, 0, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(101, 259, 'red', 'Red', '#b51b24', 0, 1, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(102, 259, 'silver-metallic', 'Silver Metallic', '#b8bdc2', 0, 2, '2026-10-04 06:18:23', '2026-10-04 06:18:23'),
(103, 259, 'matte-black', 'Matte Black', '#151618', 0, 3, '2026-10-04 06:18:23', '2026-10-04 06:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` enum('info','sukses','peringatan','stok','booking','transaksi','servis','sistem') NOT NULL DEFAULT 'info',
  `referensi_tipe` varchar(50) DEFAULT NULL,
  `referensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_user`, `judul`, `pesan`, `tipe`, `referensi_tipe`, `referensi_id`, `is_read`, `created_at`) VALUES
(1, 1, 'Stok Spare Part Menipis', 'Filter Oli Development tersisa 4 pcs. Batas minimum 5 pcs.', 'stok', 'sparepart', 1, 1, '2026-09-23 05:09:09'),
(2, 1, 'Stok Spare Part Habis', 'Filter Oli Development saat ini habis.', 'stok', 'sparepart', 1, 1, '2026-09-23 05:09:09'),
(3, 1, 'Stok Spare Part Menipis', 'Filter Oli Development tersisa 5 pcs. Batas minimum 5 pcs.', 'stok', 'sparepart', 1, 0, '2026-09-23 08:12:46'),
(5919, 990, 'Booking servis diterima', 'Booking BSV-202610-89C56E36 sudah diterima. Silakan menunggu jadwal pemeriksaan kendaraan.', 'servis', 'booking_servis', 16155, 1, '2026-10-11 15:09:45'),
(5920, 990, 'Kendaraan mulai diperiksa', 'Kendaraan Anda sudah masuk tahap pemeriksaan dengan work order SRV-202610-6935FE9E.', 'servis', 'servis', 13822, 1, '2026-10-11 15:09:46'),
(5921, 990, 'Booking servis diterima', 'Booking BSV-202610-29E1F146 sudah diterima. Silakan menunggu jadwal pemeriksaan kendaraan.', 'servis', 'booking_servis', 16156, 1, '2026-10-11 15:10:18'),
(5922, 990, 'Kendaraan mulai diperiksa', 'Kendaraan Anda sudah masuk tahap pemeriksaan dengan work order SRV-202610-E046A133.', 'servis', 'servis', 13823, 1, '2026-10-11 15:10:18'),
(5923, 990, 'Servis mulai dikerjakan', 'Pemeriksaan selesai. Tim teknisi mulai mengerjakan kendaraan Anda.', 'servis', 'servis', 13823, 1, '2026-10-11 15:10:18'),
(5924, 990, 'Booking servis diterima', 'Booking BSV-202610-C6CC10A5 sudah diterima. Silakan menunggu jadwal pemeriksaan kendaraan.', 'servis', 'booking_servis', 16157, 1, '2026-10-11 15:10:39'),
(5925, 990, 'Kendaraan mulai diperiksa', 'Kendaraan Anda sudah masuk tahap pemeriksaan dengan work order SRV-202610-5738F37B.', 'servis', 'servis', 13824, 1, '2026-10-11 15:10:39'),
(5926, 990, 'Servis mulai dikerjakan', 'Pemeriksaan selesai. Tim teknisi mulai mengerjakan kendaraan Anda.', 'servis', 'servis', 13824, 1, '2026-10-11 15:10:39'),
(5927, 990, 'Pembayaran diverifikasi', 'Pembayaran untuk SRV-202610-5738F37B telah diverifikasi oleh YZA Automotive.', 'servis', 'servis', 13824, 1, '2026-10-11 15:10:39'),
(5928, 990, 'Servis selesai', 'Pembayaran diterima. Servis SRV-202610-5738F37B telah selesai dan kendaraan dapat diambil sesuai konfirmasi bengkel.', 'servis', 'servis', 13824, 1, '2026-10-11 15:10:39'),
(5929, 990, 'Booking servis diterima', 'Booking BSV-202610-AA7E9984 sudah diterima. Silakan menunggu jadwal pemeriksaan kendaraan.', 'servis', 'booking_servis', 16158, 1, '2026-10-11 15:10:53'),
(5930, 990, 'Kendaraan mulai diperiksa', 'Kendaraan Anda sudah masuk tahap pemeriksaan dengan work order SRV-202610-5B49AEEA.', 'servis', 'servis', 13825, 1, '2026-10-11 15:10:53'),
(5931, 990, 'Servis mulai dikerjakan', 'Pemeriksaan selesai. Tim teknisi mulai mengerjakan kendaraan Anda.', 'servis', 'servis', 13825, 1, '2026-10-11 15:10:53'),
(5932, 990, 'Pembayaran diverifikasi', 'Pembayaran untuk SRV-202610-5B49AEEA telah diverifikasi oleh YZA Automotive.', 'servis', 'servis', 13825, 1, '2026-10-11 15:10:53'),
(5933, 990, 'Servis selesai', 'Pembayaran diterima. Servis SRV-202610-5B49AEEA telah selesai dan kendaraan dapat diambil sesuai konfirmasi bengkel.', 'servis', 'servis', 13825, 1, '2026-10-11 15:10:53'),
(5934, 990, 'Servis mulai dikerjakan', 'Pemeriksaan selesai. Tim teknisi mulai mengerjakan kendaraan Anda.', 'servis', 'servis', 13822, 1, '2026-10-12 11:22:23'),
(5935, 990, 'Persetujuan servis diperlukan', 'Ada pekerjaan tambahan yang menunggu persetujuan Anda.', 'servis', 'servis', 13822, 1, '2026-10-12 11:32:03'),
(5936, 990, 'Persetujuan servis diterima', 'Pekerjaan tambahan disetujui customer.', 'servis', 'servis', 13822, 1, '2026-10-12 13:54:32'),
(5937, 1, 'Booking Servis Baru', 'Lintar Yan Zuhara mengajukan Servis Berkala untuk Toyota GR Supra · B 1984 YZA pada 14 Oct 2026 pukul 10:00 WIB.', 'booking', 'booking_servis', 16162, 1, '2026-10-13 04:11:06'),
(6243, 990, 'Pembayaran ditolak', 'Pembayaran pesanan ORD-202610-AD27FF42 ditolak. Silakan pilih metode pembayaran kembali.', 'transaksi', 'pesanan_mobil_member', 18, 1, '2026-10-16 15:15:30'),
(6244, 1, 'Pesanan Mobil Baru', 'Pesanan ORD-202611-D1F5298F — Lexus LFA / Red — Lintar Yan Zuhara. Menunggu verifikasi admin.', 'transaksi', 'pesanan_mobil_member', 19, 1, '2026-11-07 18:47:22'),
(6245, 992, 'Pesanan Mobil Baru', 'Pesanan ORD-202611-D1F5298F — Lexus LFA / Red — Lintar Yan Zuhara. Menunggu verifikasi admin.', 'transaksi', 'pesanan_mobil_member', 19, 0, '2026-11-07 18:47:22'),
(6246, 1, 'Pre-Order Mobil Menunggu Stok', 'Bugatti Chiron / Atlantic Blue — pre-order #1 menunggu ketersediaan stok.', 'transaksi', 'pre_order_mobil', 1, 0, '2026-11-07 18:47:22'),
(6247, 992, 'Pre-Order Mobil Menunggu Stok', 'Bugatti Chiron / Atlantic Blue — pre-order #1 menunggu ketersediaan stok.', 'transaksi', 'pre_order_mobil', 1, 0, '2026-11-07 18:47:22'),
(6248, 1, 'Pesanan Mobil Baru', 'Pesanan ORD-202611-70CCE8F5 — Dodge Challenger SRT Hellcat Redeye / TorRed — Lintar Yan Zuhara. Menunggu verifikasi admin.', 'transaksi', 'pesanan_mobil_member', 20, 0, '2026-11-08 04:53:05'),
(6249, 992, 'Pesanan Mobil Baru', 'Pesanan ORD-202611-70CCE8F5 — Dodge Challenger SRT Hellcat Redeye / TorRed — Lintar Yan Zuhara. Menunggu verifikasi admin.', 'transaksi', 'pesanan_mobil_member', 20, 0, '2026-11-08 04:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `paket_cuci`
--

CREATE TABLE `paket_cuci` (
  `id_paket` bigint(20) UNSIGNED NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estimasi_menit` smallint(5) UNSIGNED DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paket_cuci`
--

INSERT INTO `paket_cuci` (`id_paket`, `nama_paket`, `deskripsi`, `harga`, `estimasi_menit`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 'Cuci exterior dan vacuum interior', 75000.00, 45, 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(2, 'Premium', 'Cuci exterior, vacuum, dashboard cleaning, dan semir ban', 125000.00, 75, 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(3, 'Detailing', 'Interior detailing, exterior detailing, dan wax', 350000.00, 180, 'aktif', '2026-09-23 03:31:41', '2026-09-23 03:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `pdi_pesanan_mobil`
--

CREATE TABLE `pdi_pesanan_mobil` (
  `id_pdi` bigint(20) UNSIGNED NOT NULL,
  `id_pesanan` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `mesin` tinyint(1) NOT NULL DEFAULT 0,
  `eksterior` tinyint(1) NOT NULL DEFAULT 0,
  `interior` tinyint(1) NOT NULL DEFAULT 0,
  `rem` tinyint(1) NOT NULL DEFAULT 0,
  `ban` tinyint(1) NOT NULL DEFAULT 0,
  `kelistrikan` tinyint(1) NOT NULL DEFAULT 0,
  `lampu` tinyint(1) NOT NULL DEFAULT 0,
  `ac` tinyint(1) NOT NULL DEFAULT 0,
  `dokumen` tinyint(1) NOT NULL DEFAULT 0,
  `final_quality` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'belum_diperiksa',
  `result` varchar(30) DEFAULT NULL,
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `nomor_rangka_snapshot` varchar(100) DEFAULT NULL,
  `nomor_mesin_snapshot` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `diperiksa_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pdi_pesanan_mobil`
--

INSERT INTO `pdi_pesanan_mobil` (`id_pdi`, `id_pesanan`, `id_petugas`, `started_at`, `completed_at`, `mesin`, `eksterior`, `interior`, `rem`, `ban`, `kelistrikan`, `lampu`, `ac`, `dokumen`, `final_quality`, `status`, `result`, `checklist`, `nomor_rangka_snapshot`, `nomor_mesin_snapshot`, `catatan`, `diperiksa_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'lulus', NULL, NULL, NULL, NULL, 'PDI dinyatakan lulus sempurna.', '2026-10-11 13:22:10', '2026-10-11 13:21:54', '2026-10-11 13:22:10');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_pelanggan` varchar(30) NOT NULL,
  `nama_lengkap` varchar(120) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `province_code` varchar(10) DEFAULT NULL,
  `province_name` varchar(120) DEFAULT NULL,
  `city_code` varchar(10) DEFAULT NULL,
  `city_name` varchar(160) DEFAULT NULL,
  `district_code` varchar(12) DEFAULT NULL,
  `district_name` varchar(160) DEFAULT NULL,
  `village_code` varchar(16) DEFAULT NULL,
  `village_name` varchar(160) DEFAULT NULL,
  `address_detail` varchar(500) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `id_user`, `kode_pelanggan`, `nama_lengkap`, `jenis_kelamin`, `no_hp`, `alamat`, `province_code`, `province_name`, `city_code`, `city_name`, `district_code`, `district_name`, `village_code`, `village_name`, `address_detail`, `tanggal_lahir`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'PLG-DEV-001', 'Pelanggan Test YZA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2026-09-23 04:11:32', '2026-09-23 04:11:32'),
(2, 4, 'PLG-202609-2BF122', 'Pelanggan Test 2B', 'P', '081234567891', 'Data dev Tahap 2B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1998-02-02', NULL, 'aktif', '2026-09-23 04:42:30', '2026-09-23 04:48:09'),
(3, NULL, 'PLG-202609-7D99C2', 'Pelanggan Walk-in 2B', 'L', '081234567892', 'Alamat dev Tahap 2B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1990-01-01', NULL, 'aktif', '2026-09-23 04:45:11', '2026-09-23 04:47:39'),
(439, 586, 'DEMO-7C122E9E', 'Demo Active', NULL, '080000000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2026-10-04 05:33:48', '2026-10-04 05:33:48'),
(440, 587, 'DEMO-A6096436', 'Demo Pending', NULL, '080000000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2026-10-04 05:34:34', '2026-10-04 05:34:34'),
(528, NULL, 'PLG-202610-65F73A', 'Lintar Yan Zuhara', 'L', '08138126925', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'rt/rw = 006/013', '2003-02-13', NULL, 'aktif', '2026-10-04 14:46:01', '2026-10-04 14:46:01'),
(529, NULL, 'PLG-202610-6E1AF3', 'Lintar Yan Zuhara', 'L', '08138126925', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'rt/rw = 006/013', '2003-02-13', NULL, 'aktif', '2026-10-04 16:07:01', '2026-10-04 16:07:01'),
(530, NULL, 'PLG-202610-B76E82', 'Lintar Yan Zuhara', 'L', '08138126925', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'rt/rw = 006/013', '2003-02-13', NULL, 'aktif', '2026-10-05 02:58:12', '2026-10-05 02:58:12'),
(750, 990, 'PLG-202610-7EE538', 'Lintar Yan Zuhara', 'L', '087784304417', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'rt/rw = 006/013', '2003-02-13', NULL, 'aktif', '2026-10-10 14:52:26', '2026-10-10 14:52:26'),
(751, 991, 'PLG-202610-F3263B', 'Admin YZA', 'L', '081234567890', 'Jl. Sudirman No. 1, LATIUNG, TEUPAH SELATAN, KABUPATEN SIMEULUE, ACEH', '11', 'ACEH', '1101', 'KABUPATEN SIMEULUE', '1101010', 'TEUPAH SELATAN', '1101010001', 'LATIUNG', 'Jl. Sudirman No. 1', '1990-01-01', NULL, 'aktif', '2026-10-11 11:55:12', '2026-10-11 11:55:12'),
(752, 1001, 'PLG-202611-56C7D8', 'Lucky Yan Zuhara', 'L', '089616054765', 'gondang 3 rt/rw =006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'gondang 3 rt/rw =006/013', '2004-11-03', NULL, 'aktif', '2026-11-03 03:58:06', '2026-11-03 03:58:06'),
(753, 1002, 'PLG-202611-192537', 'fahri', 'L', '081515148', 'tinggal dandan, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', '31', 'DKI JAKARTA', '3174', 'KOTA JAKARTA BARAT', '3174080', 'KALI DERES', '3174080002', 'KALIDERES', 'tinggal dandan', '2004-02-13', NULL, 'aktif', '2026-11-10 04:13:28', '2026-11-10 04:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `kode_pembayaran` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `arah` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `metode` enum('tunai','transfer','kredit','lainnya') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `receipt_reference` varchar(100) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `kode_pembayaran`, `id_transaksi`, `tanggal`, `arah`, `metode`, `jumlah`, `bukti_transfer`, `status`, `verified_by`, `verified_at`, `receipt_reference`, `rejection_reason`, `catatan`, `created_at`) VALUES
(1, 'PAY-202609-5C3A24A3', 1, '2026-09-23 12:41:34', 'masuk', 'tunai', 100000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA car payment', '2026-09-23 05:41:34'),
(2, 'PAY-202609-294A74B7', 1, '2026-09-23 12:41:34', 'masuk', 'tunai', 140000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA car payment', '2026-09-23 05:41:34'),
(3, 'PAY-202609-E3AA4A99', 2, '2026-09-23 12:42:48', 'masuk', 'transfer', 105000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA transfer pending', '2026-09-23 05:42:48'),
(4, 'PAY-202609-B9498724', 3, '2026-09-23 12:47:53', 'masuk', 'transfer', 35000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'Paket 2 QA', '2026-09-23 05:47:53'),
(5, 'PAY-202609-7ADFDE16', 4, '2026-09-23 15:12:46', 'masuk', 'tunai', 100000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA partial', '2026-09-23 08:12:46'),
(6, 'PAY-202609-E565D03C', 4, '2026-09-23 15:12:46', 'masuk', 'tunai', 85000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA lunas', '2026-09-23 08:12:46'),
(7, 'PAY-202609-1F8ECFA9', 5, '2026-09-23 15:12:46', 'masuk', 'tunai', 75000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA wash', '2026-09-23 08:12:46'),
(8, 'PAY-202609-CFF948B0', 6, '2026-09-23 15:29:46', 'keluar', 'tunai', 40000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA partial', '2026-09-23 08:29:46'),
(9, 'PAY-202609-E40DD034', 6, '2026-09-23 15:29:46', 'keluar', 'tunai', 60000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA final', '2026-09-23 08:29:46'),
(10, 'PAY-202609-FCDB4C88', 7, '2026-09-23 15:31:07', 'masuk', 'tunai', 15000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA partial', '2026-09-23 08:31:07'),
(11, 'PAY-202609-22D3F400', 7, '2026-09-23 15:31:07', 'masuk', 'tunai', 15000000.00, NULL, 'terverifikasi', NULL, NULL, NULL, NULL, 'QA final', '2026-09-23 08:31:07'),
(11290, 'PAY-202610-B9913623', 19963, '2026-10-11 20:03:39', 'masuk', 'transfer', 2350000000.00, NULL, 'terverifikasi', 1, '2026-10-11 19:29:27', NULL, NULL, 'Midtrans Sandbox YZA-19963-HKUBFK3NKZ', '2026-10-11 13:03:39'),
(11292, 'PAY-202610-FBBFF316', 19966, '2026-10-11 22:10:39', 'masuk', 'tunai', 240000.00, NULL, 'terverifikasi', 1, '2026-10-11 22:10:39', NULL, NULL, 'Pembayaran servis tunai di kasir resmi YZA Automotive', '2026-10-11 15:10:39'),
(11293, 'PAY-202610-78C9342C', 19967, '2026-10-11 22:10:53', 'masuk', 'tunai', 240000.00, NULL, 'terverifikasi', 1, '2026-10-11 22:10:53', NULL, NULL, 'Pembayaran servis tunai di kasir resmi YZA Automotive', '2026-10-11 15:10:53'),
(11358, 'PAY-202610-EFB9586B', 20079, '2026-10-16 19:54:48', 'masuk', 'tunai', 2500000000.00, NULL, 'ditolak', 1, '2026-10-16 20:05:45', NULL, 'sdadd', NULL, '2026-10-16 12:54:49'),
(11359, 'PAY-202610-1661A489', 20079, '2026-10-16 21:07:01', 'masuk', 'tunai', 2500000000.00, NULL, 'ditolak', 1, '2026-10-16 21:23:33', NULL, 'aasas', NULL, '2026-10-16 14:07:01'),
(11360, 'PAY-202610-9864F726', 20079, '2026-10-16 21:54:44', 'masuk', 'tunai', 2500000000.00, NULL, 'ditolak', 1, '2026-10-16 22:15:30', NULL, 'bjj', NULL, '2026-10-16 14:54:44'),
(11361, 'PAY-202610-E796A28C', 20079, '2026-10-16 22:38:03', 'masuk', 'tunai', 2500000000.00, NULL, 'ditolak', 1, '2026-10-16 22:38:21', NULL, 'add', NULL, '2026-10-16 15:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_gateway`
--

CREATE TABLE `pembayaran_gateway` (
  `id_pembayaran_gateway` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_membership` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pembayaran` bigint(20) UNSIGNED DEFAULT NULL,
  `provider` varchar(30) NOT NULL DEFAULT 'midtrans',
  `order_id` varchar(50) NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `status_provider` varchar(40) NOT NULL DEFAULT 'created',
  `payment_type` varchar(50) DEFAULT NULL,
  `provider_transaction_id` varchar(100) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `finish_url` varchar(500) DEFAULT NULL,
  `initiated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `last_synced_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembayaran_gateway`
--

INSERT INTO `pembayaran_gateway` (`id_pembayaran_gateway`, `id_transaksi`, `id_membership`, `id_pembayaran`, `provider`, `order_id`, `gross_amount`, `status_provider`, `payment_type`, `provider_transaction_id`, `snap_token`, `redirect_url`, `finish_url`, `initiated_by`, `processed_at`, `last_synced_at`, `created_at`, `updated_at`) VALUES
(676, NULL, 139, NULL, 'midtrans', 'YZA-MBR-139-ASOS8NOS', 249000.00, 'pending', NULL, NULL, '52bdd233-300d-49fe-83c1-f937625e0422', 'https://app.sandbox.midtrans.com/snap/v4/redirection/52bdd233-300d-49fe-83c1-f937625e0422', NULL, NULL, NULL, '2026-10-04 23:43:32', '2026-10-04 16:43:31', '2026-10-04 16:43:32'),
(712, NULL, 140, NULL, 'midtrans', 'YZA-MBR-140-UF6JHGIA', 249000.00, 'settlement', 'bank_transfer', '36a291df-67a0-4efd-a5f6-98f85d0d2beb', '4096733e-f9cc-441d-a7c6-55ad54900436', 'https://app.sandbox.midtrans.com/snap/v4/redirection/4096733e-f9cc-441d-a7c6-55ad54900436', NULL, NULL, NULL, '2026-10-05 11:00:37', '2026-10-05 02:58:16', '2026-10-05 04:00:37'),
(998, 19956, NULL, NULL, 'midtrans', 'YZA-19956-ZXL5CIMBFK', 10000.00, 'pending', NULL, NULL, '26f14c8e-7668-48e6-95d1-e1739dcb62ed', 'https://app.sandbox.midtrans.com/snap/v4/redirection/26f14c8e-7668-48e6-95d1-e1739dcb62ed', NULL, 3, NULL, '2026-10-10 18:33:54', '2026-10-10 11:33:43', '2026-10-10 11:33:54'),
(1004, NULL, 360, NULL, 'midtrans', 'YZA-MBR-360-EI0AZV5K', 249000.00, 'settlement', 'bank_transfer', '5d749f24-c27c-438b-82b5-d2927d74bee0', '2d125d78-b094-4dcc-bdf5-982af40c18c7', 'https://app.sandbox.midtrans.com/snap/v4/redirection/2d125d78-b094-4dcc-bdf5-982af40c18c7', NULL, 990, NULL, '2026-10-10 21:52:49', '2026-10-10 14:52:28', '2026-10-10 14:52:49'),
(1005, 19963, NULL, 11290, 'midtrans', 'YZA-19963-HKUBFK3NKZ', 2350000000.00, 'settlement', 'bank_transfer', 'd6236338-dc84-4f80-8858-42f8c13c830b', 'e56353ae-c540-45d9-abc0-84ac7e0fb7a6', 'https://app.sandbox.midtrans.com/snap/v4/redirection/e56353ae-c540-45d9-abc0-84ac7e0fb7a6', NULL, 990, '2026-10-11 19:29:27', '2026-10-11 20:03:39', '2026-10-11 11:17:38', '2026-10-11 13:03:39'),
(1006, NULL, 361, NULL, 'midtrans', 'YZA-MBR-361-7AS6QXO3', 249000.00, 'pending', 'bank_transfer', '0a5c6eac-2bf9-455c-95f3-2d1319d7d12b', 'c394cb55-d3e1-42de-9ac9-a339ef5ddf43', 'https://app.sandbox.midtrans.com/snap/v4/redirection/c394cb55-d3e1-42de-9ac9-a339ef5ddf43', NULL, 991, NULL, '2026-10-11 18:59:35', '2026-10-11 11:55:52', '2026-10-11 11:59:35'),
(1007, 19963, NULL, NULL, 'midtrans', 'YZA-19963-HNM2Z9DGBS', 2350000000.00, 'pending', NULL, NULL, '81a4bc3b-48e6-4359-979e-f425d9b2cd25', 'https://app.sandbox.midtrans.com/snap/v4/redirection/81a4bc3b-48e6-4359-979e-f425d9b2cd25', 'http://127.0.0.1:8000/pelanggan/pembayaran/midtrans/kembali?order_id=YZA-19963-HNM2Z9DGBS', 990, NULL, '2026-10-11 19:56:02', '2026-10-11 12:56:01', '2026-10-11 12:56:02'),
(1017, 20079, NULL, NULL, 'midtrans', 'YZA-20079-IQ8CMYZDLR', 2500000000.00, 'manual_selected', NULL, NULL, 'dc867eb0-23de-4fa1-97b8-518059dbccfb', 'https://app.sandbox.midtrans.com/snap/v4/redirection/dc867eb0-23de-4fa1-97b8-518059dbccfb', 'http://127.0.0.1:8000/pelanggan/pembayaran/midtrans/kembali?order_id=YZA-20079-IQ8CMYZDLR', 990, NULL, '2026-10-16 19:54:48', '2026-10-16 12:36:43', '2026-10-16 12:54:48'),
(1018, NULL, 362, NULL, 'midtrans', 'YZA-MBR-362-EZN7OV0H', 249000.00, 'settlement', 'bank_transfer', 'ca9c7788-c61b-41ab-8391-eab4e2ca6832', 'e1d8ce13-86eb-4f68-8e75-b4e36f0a6bbc', 'https://app.sandbox.midtrans.com/snap/v4/redirection/e1d8ce13-86eb-4f68-8e75-b4e36f0a6bbc', NULL, 1001, '2026-11-03 10:59:06', '2026-11-03 10:59:15', '2026-11-03 03:58:19', '2026-11-03 03:59:15'),
(1019, NULL, 363, NULL, 'midtrans', 'YZA-MBR-363-GIVRCSC1', 249000.00, 'settlement', 'bank_transfer', '0637e6c5-8097-4ed0-9e91-f89fed7d809f', '3c99a719-2bea-4417-9580-94dba172ec58', 'https://app.sandbox.midtrans.com/snap/v4/redirection/3c99a719-2bea-4417-9580-94dba172ec58', NULL, 1002, '2026-11-10 11:14:39', '2026-11-10 11:14:44', '2026-11-10 04:13:39', '2026-11-10 04:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_mobil_bekas`
--

CREATE TABLE `pembelian_mobil_bekas` (
  `id_pembelian` bigint(20) UNSIGNED NOT NULL,
  `kode_pembelian` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `id_inspeksi` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_pembelian` datetime NOT NULL DEFAULT current_timestamp(),
  `harga_pembelian` decimal(15,2) NOT NULL,
  `status` enum('proses','selesai','dibatalkan') NOT NULL DEFAULT 'proses',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembelian_mobil_bekas`
--

INSERT INTO `pembelian_mobil_bekas` (`id_pembelian`, `kode_pembelian`, `id_transaksi`, `id_pengajuan`, `id_inspeksi`, `id_pelanggan`, `id_petugas`, `id_mobil`, `tanggal_pembelian`, `harga_pembelian`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'BUY-202609-487E3F23', 6, 1, 1, 1, 2, 6, '2026-09-23 15:29:46', 100000000.00, 'selesai', 'QA purchase', '2026-09-23 08:29:46', '2026-09-23 08:29:46');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_mobil`
--

CREATE TABLE `pengajuan_mobil` (
  `id_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `kode_pengajuan` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_kendaraan_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `merk` varchar(80) NOT NULL,
  `model` varchar(100) NOT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `tahun` smallint(5) UNSIGNED DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `transmisi` enum('manual','automatic','cvt','dct','lainnya') DEFAULT NULL,
  `bahan_bakar` enum('bensin','diesel','hybrid','listrik','lainnya') DEFAULT NULL,
  `nomor_polisi` varchar(20) DEFAULT NULL,
  `nomor_rangka` varchar(100) DEFAULT NULL,
  `nomor_mesin` varchar(100) DEFAULT NULL,
  `kilometer` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `harga_harapan` decimal(15,2) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('diajukan','menunggu_inspeksi','dalam_inspeksi','penawaran_diberikan','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengajuan_mobil`
--

INSERT INTO `pengajuan_mobil` (`id_pengajuan`, `kode_pengajuan`, `id_pelanggan`, `id_kendaraan_pelanggan`, `merk`, `model`, `tipe`, `tahun`, `warna`, `transmisi`, `bahan_bakar`, `nomor_polisi`, `nomor_rangka`, `nomor_mesin`, `kilometer`, `harga_harapan`, `foto`, `catatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PJM-202609-FCB62A49', 1, 1, 'Toyota', 'Avanza', 'QA P4', 2022, 'Hitam', 'automatic', 'bensin', 'DEV-P4-PLAT-01', 'DEV-P4-RANGKA-01', 'DEV-P4-MESIN-01', 45000, 120000000.00, NULL, 'Paket 4 QA', 'selesai', '2026-09-23 08:29:46', '2026-09-23 08:29:46'),
(2, 'PJM-202609-4C07B1B3', 1, 1, 'Toyota', 'Avanza', '1.5 G', 2022, 'Hitam', 'automatic', 'bensin', 'DEV-P4-TRADE-PLAT', 'DEV-P4-TRADE-RANGKA', 'DEV-P4-TRADE-MESIN', 45000, 50000000.00, NULL, 'QA trade', 'selesai', '2026-09-23 08:31:07', '2026-09-23 08:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id_pengaturan` bigint(20) UNSIGNED NOT NULL,
  `kunci_pengaturan` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id_pengaturan`, `kunci_pengaturan`, `nilai`, `deskripsi`, `updated_at`) VALUES
(1, 'nama_perusahaan', 'PT Yan Zuhara Andriyanto Automotive', 'Nama resmi perusahaan', '2026-09-23 03:31:41'),
(2, 'nama_singkat', 'YZA Automotive', 'Nama singkat perusahaan', '2026-09-23 03:31:41'),
(3, 'nama_sistem', 'YZA Dealer Management System', 'Nama aplikasi', '2026-09-23 03:31:41'),
(4, 'alamat_perusahaan', '', 'Alamat dealer', '2026-09-23 03:31:41'),
(5, 'no_hp_perusahaan', '', 'Nomor telepon dealer', '2026-09-23 03:31:41'),
(6, 'email_perusahaan', '', 'Email dealer', '2026-09-23 03:31:41'),
(7, 'mata_uang', 'IDR', 'Mata uang transaksi', '2026-09-23 03:31:41'),
(8, 'stok_notifikasi_default', '5', 'Batas stok minimum default', '2026-09-23 03:31:41'),
(9, 'timezone', 'Asia/Jakarta', 'Zona waktu aplikasi', '2026-09-23 03:31:41'),
(10, 'membership_activation_fee', '249000', 'Biaya aktivasi YZA Member (sekali bayar)', '2026-10-04 03:32:19'),
(11, 'service_capacity_per_slot', '3', 'Kapasitas kendaraan aktif bengkel per slot waktu', '2026-10-12 08:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_mobil`
--

CREATE TABLE `penjualan_mobil` (
  `id_penjualan` bigint(20) UNSIGNED NOT NULL,
  `kode_penjualan` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `id_sales` bigint(20) UNSIGNED DEFAULT NULL,
  `id_booking` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_penjualan` datetime NOT NULL DEFAULT current_timestamp(),
  `harga_mobil` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dp` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_akhir` decimal(15,2) NOT NULL,
  `metode_pembelian` enum('tunai','kredit') NOT NULL DEFAULT 'tunai',
  `status` enum('proses','selesai','dibatalkan') NOT NULL DEFAULT 'proses',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualan_mobil`
--

INSERT INTO `penjualan_mobil` (`id_penjualan`, `kode_penjualan`, `id_transaksi`, `id_pelanggan`, `id_mobil`, `id_petugas`, `id_sales`, `id_booking`, `tanggal_penjualan`, `harga_mobil`, `diskon`, `dp`, `total_akhir`, `metode_pembelian`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'SALE-202609-8203F497', 1, 1, 5, 1, NULL, NULL, '2026-09-23 12:41:23', 245000000.00, 5000000.00, 0.00, 240000000.00, 'tunai', 'selesai', 'QA car sale', '2026-09-23 05:41:23', '2026-09-23 05:41:34'),
(2912, 'SALE-202610-B348ABA7', 19963, 750, 195, 1, NULL, NULL, '2026-10-11 14:54:35', 2350000000.00, 0.00, 0.00, 2350000000.00, 'tunai', 'selesai', 'Pesanan member ORD-202610-91F1E6DE', '2026-10-11 07:54:35', '2026-10-11 13:22:10'),
(2938, 'SALE-202610-4EAD3A40', 20079, 750, 257, 1, NULL, NULL, '2026-10-16 10:23:31', 2500000000.00, 0.00, 0.00, 2500000000.00, 'tunai', 'proses', 'Pesanan member ORD-202610-AD27FF42', '2026-10-16 03:23:31', '2026-10-16 03:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_sparepart`
--

CREATE TABLE `penjualan_sparepart` (
  `id_penjualan_sparepart` bigint(20) UNSIGNED NOT NULL,
  `kode_penjualan` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','proses','selesai','dibatalkan') NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualan_sparepart`
--

INSERT INTO `penjualan_sparepart` (`id_penjualan_sparepart`, `kode_penjualan`, `id_transaksi`, `id_pelanggan`, `id_petugas`, `tanggal`, `subtotal`, `diskon`, `total`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'SPS-202609-29E646FA', 2, 1, 1, '2026-09-23 12:41:57', 105000.00, 0.00, 105000.00, 'selesai', 'QA sparepart sale', '2026-09-23 05:41:57', '2026-09-23 05:48:09'),
(2, 'SPS-202609-6524502C', 3, 1, 2, '2026-09-23 12:47:39', 35000.00, 0.00, 35000.00, 'selesai', 'Paket 2 QA total', '2026-09-23 05:47:39', '2026-09-23 05:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id_permission` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(80) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id_permission`, `kode`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'dashboard.view', 'Melihat dashboard', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(2, 'sales.view', 'Melihat penjualan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(3, 'sales.manage', 'Mengelola penjualan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(4, 'customers.view', 'Melihat pelanggan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(5, 'service.view', 'Melihat layanan dan work order', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(6, 'service.manage', 'Mengelola work order', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(7, 'service.assigned', 'Mengelola pekerjaan yang ditugaskan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(8, 'inventory.view', 'Melihat inventori', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(9, 'inventory.manage', 'Mengelola inventori', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(10, 'finance.view', 'Melihat keuangan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(11, 'finance.verify', 'Memverifikasi pembayaran', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(12, 'reports.view', 'Melihat laporan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(13, 'staff.manage', 'Mengelola karyawan dan role', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(14, 'audit.view', 'Melihat audit aktivitas', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(15, 'qc.manage', 'Mengelola QC dan PDI', '2026-10-15 04:37:50', '2026-10-15 04:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_mobil_member`
--

CREATE TABLE `pesanan_mobil_member` (
  `id_pesanan` bigint(20) UNSIGNED NOT NULL,
  `nomor_pesanan` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_penjualan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sales` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_pembeli` varchar(120) NOT NULL,
  `email_pembeli` varchar(120) DEFAULT NULL,
  `telepon_pembeli` varchar(25) DEFAULT NULL,
  `alamat_pembeli` text DEFAULT NULL,
  `snapshot_merk` varchar(80) NOT NULL,
  `snapshot_model` varchar(100) NOT NULL,
  `snapshot_tipe` varchar(100) DEFAULT NULL,
  `snapshot_tahun` smallint(5) UNSIGNED DEFAULT NULL,
  `snapshot_warna` varchar(50) DEFAULT NULL,
  `snapshot_harga` decimal(15,2) NOT NULL,
  `total_tagihan` decimal(15,2) NOT NULL,
  `syarat_disetujui` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(40) NOT NULL DEFAULT 'menunggu_verifikasi',
  `id_petugas_keputusan` bigint(20) UNSIGNED DEFAULT NULL,
  `diputuskan_at` timestamp NULL DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `jadwal_pengambilan` timestamp NULL DEFAULT NULL,
  `lokasi_pengambilan` varchar(180) DEFAULT NULL,
  `status_pengambilan` varchar(30) NOT NULL DEFAULT 'belum_dijadwalkan',
  `jadwal_dikonfirmasi_at` timestamp NULL DEFAULT NULL,
  `catatan_pengambilan` text DEFAULT NULL,
  `diserahkan_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan_mobil_member`
--

INSERT INTO `pesanan_mobil_member` (`id_pesanan`, `nomor_pesanan`, `id_pelanggan`, `id_mobil`, `id_transaksi`, `id_penjualan`, `id_sales`, `nama_pembeli`, `email_pembeli`, `telepon_pembeli`, `alamat_pembeli`, `snapshot_merk`, `snapshot_model`, `snapshot_tipe`, `snapshot_tahun`, `snapshot_warna`, `snapshot_harga`, `total_tagihan`, `syarat_disetujui`, `status`, `id_petugas_keputusan`, `diputuskan_at`, `catatan_admin`, `jadwal_pengambilan`, `lokasi_pengambilan`, `status_pengambilan`, `jadwal_dikonfirmasi_at`, `catatan_pengambilan`, `diserahkan_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-202610-91F1E6DE', 750, 195, 19963, 2912, NULL, 'Lintar Yan Zuhara', 'lintaryanzuhara.yz@gmail.com', '087784304417', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', 'Toyota', 'GR Supra', 'Performance', 2024, 'Obsidian Black', 2350000000.00, 2350000000.00, 1, 'diserahkan', 1, '2026-10-11 07:54:35', NULL, NULL, NULL, 'belum_dijadwalkan', NULL, NULL, '2026-10-11 13:22:10', '2026-10-11 03:59:11', '2026-10-11 13:22:10'),
(18, 'ORD-202610-AD27FF42', 750, 257, 20079, 2938, NULL, 'Lintar Yan Zuhara', 'lintaryanzuhara.yz@gmail.com', '087784304417', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', 'Dodge', 'Challenger SRT Hellcat Redeye', '6.2L Supercharged V8', 2023, 'Destroyer Grey', 2500000000.00, 2500000000.00, 1, 'dibatalkan', 1, '2026-10-16 03:23:31', 'Pembayaran ditolak; silakan pilih metode pembayaran kembali.', NULL, NULL, 'belum_dijadwalkan', NULL, NULL, NULL, '2026-10-16 03:22:11', '2026-10-16 15:38:21'),
(19, 'ORD-202611-D1F5298F', 750, 259, NULL, NULL, NULL, 'Lintar Yan Zuhara', 'lintaryanzuhara.yz@gmail.com', '087784304417', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', 'Lexus', 'LFA', '4.8L V10 Performance', 2012, 'Red', 9000000000.00, 9000000000.00, 1, 'menunggu_verifikasi', NULL, NULL, NULL, NULL, NULL, 'belum_dijadwalkan', NULL, NULL, NULL, '2026-11-06 11:09:13', '2026-11-06 11:09:13'),
(20, 'ORD-202611-70CCE8F5', 750, 257, NULL, NULL, NULL, 'Lintar Yan Zuhara', NULL, '087784304417', 'rt/rw = 006/013, KALIDERES, KALI DERES, KOTA JAKARTA BARAT, DKI JAKARTA', 'Dodge', 'Challenger SRT Hellcat Redeye', '6.2L Supercharged V8', 2023, 'TorRed', 2500000000.00, 2500000000.00, 1, 'menunggu_verifikasi', NULL, NULL, NULL, NULL, NULL, 'belum_dijadwalkan', NULL, NULL, NULL, '2026-11-08 04:53:05', '2026-11-08 04:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `kode_petugas` varchar(30) NOT NULL,
  `nama_lengkap` varchar(120) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jabatan` varchar(80) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `id_user`, `kode_petugas`, `nama_lengkap`, `no_hp`, `alamat`, `jabatan`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'ADM-DEV-001', 'Administrator YZA', NULL, NULL, 'Admin', NULL, 'aktif', '2026-09-23 04:11:32', '2026-09-23 04:11:32'),
(2, 2, 'PTG-DEV-001', 'Petugas YZA', NULL, NULL, 'Petugas', NULL, 'aktif', '2026-09-23 04:11:32', '2026-09-23 04:11:32'),
(3, 5, 'PTG-202609-D1A4F5', 'Petugas Development 3', '081230000004', 'Alamat Dev Petugas', 'Inventory QA', NULL, 'aktif', '2026-09-23 05:09:28', '2026-09-23 05:09:49'),
(242, 992, 'PTG-SUPER_ADMIN-930A1F29', 'Super Admin YZA', NULL, NULL, 'Super Admin', NULL, 'nonaktif', '2026-10-15 04:37:50', '2026-11-08 06:36:29'),
(243, 993, 'PTG-OWNER-14B2D2B6', 'Owner YZA', NULL, NULL, 'Owner', NULL, 'aktif', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(244, 994, 'PTG-SALES-3F3AC6AE', 'Sales YZA', NULL, NULL, 'Sales', NULL, 'aktif', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(245, 995, 'PTG-SERVICE_ADVISOR-53E87601', 'Service Advisor YZA', NULL, NULL, 'Service Advisor', NULL, 'aktif', '2026-10-15 04:37:51', '2026-10-15 04:37:51'),
(246, 996, 'PTG-MECHANIC-07252684', 'Mekanik YZA', NULL, NULL, 'Mechanic', NULL, 'aktif', '2026-10-15 04:37:51', '2026-10-15 04:37:51'),
(247, 997, 'PTG-WAREHOUSE_STAFF-AEB26F8A', 'Petugas Gudang YZA', NULL, NULL, 'Warehouse Staff', NULL, 'aktif', '2026-10-15 04:37:51', '2026-10-15 04:37:51'),
(248, 998, 'PTG-FINANCE-AD1E9930', 'Finance YZA', NULL, NULL, 'Finance', NULL, 'aktif', '2026-10-15 04:37:51', '2026-10-15 04:37:51'),
(249, 999, 'PTG-OPERATIONAL_STAFF-5580245D', 'Operasional YZA', NULL, NULL, 'Operational Staff', NULL, 'aktif', '2026-10-15 04:37:52', '2026-10-15 04:37:52'),
(250, 1000, 'PTG-QUALITY_CONTROL-189B9D7E', 'QC / PDI YZA', NULL, NULL, 'Quality Control', NULL, 'aktif', '2026-10-15 04:37:52', '2026-10-15 04:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `poin_member`
--

CREATE TABLE `poin_member` (
  `id_poin_member` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `saldo` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_diperoleh` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `poin_member`
--

INSERT INTO `poin_member` (`id_poin_member`, `id_pelanggan`, `saldo`, `total_diperoleh`, `created_at`, `updated_at`) VALUES
(1, 1, 27038, 27038, '2026-09-29 21:41:54', '2026-09-29 21:42:14'),
(12, 2, 0, 0, '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(2517, 528, 0, 0, '2026-10-04 14:46:01', '2026-10-04 14:46:01'),
(2535, 529, 0, 0, '2026-10-04 16:07:01', '2026-10-04 16:07:01'),
(2623, 530, 0, 0, '2026-10-05 02:58:12', '2026-10-05 02:58:12'),
(3919, 750, 48, 48, '2026-10-10 14:52:26', '2026-10-11 15:10:53'),
(3920, 751, 0, 0, '2026-10-11 11:55:12', '2026-10-11 11:55:12'),
(3940, 752, 0, 0, '2026-11-03 03:58:06', '2026-11-03 03:58:06'),
(3941, 753, 0, 0, '2026-11-10 04:13:28', '2026-11-10 04:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `pre_order_mobil_member`
--

CREATE TABLE `pre_order_mobil_member` (
  `id_pre_order` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED DEFAULT NULL,
  `product_key` varchar(120) NOT NULL,
  `showcase_slug` varchar(80) DEFAULT NULL,
  `nama_mobil` varchar(180) NOT NULL,
  `varian` varchar(120) DEFAULT NULL,
  `tahun` varchar(10) DEFAULT NULL,
  `warna` varchar(100) DEFAULT NULL,
  `harga_referensi` decimal(15,2) DEFAULT NULL,
  `status` enum('menunggu_stok','stok_tersedia','notifikasi_terkirim','dilanjutkan_ke_checkout','dibatalkan') NOT NULL DEFAULT 'menunggu_stok',
  `notified_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `id_pesanan` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pre_order_mobil_member`
--

INSERT INTO `pre_order_mobil_member` (`id_pre_order`, `id_pelanggan`, `id_mobil`, `product_key`, `showcase_slug`, `nama_mobil`, `varian`, `tahun`, `warna`, `harga_referensi`, `status`, `notified_at`, `cancelled_at`, `id_pesanan`, `created_at`, `updated_at`) VALUES
(1, 750, NULL, 'showcase:bugatti-chiron', 'bugatti-chiron', 'Bugatti Chiron', 'Super Sport', '2024', 'Atlantic Blue', 68000000000.00, 'menunggu_stok', NULL, NULL, NULL, '2026-10-13 04:33:38', '2026-10-13 04:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `promo_member`
--

CREATE TABLE `promo_member` (
  `id_promo` bigint(20) UNSIGNED NOT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jenis_benefit` enum('persentase','nominal','informasi') NOT NULL,
  `nilai_benefit` decimal(15,2) DEFAULT NULL,
  `minimum_transaksi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `maksimum_benefit` decimal(15,2) DEFAULT NULL,
  `quota` int(10) UNSIGNED DEFAULT NULL,
  `usage_per_member` int(10) UNSIGNED DEFAULT NULL,
  `member_only` tinyint(1) NOT NULL DEFAULT 1,
  `berlaku_untuk` enum('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in') NOT NULL DEFAULT 'semua',
  `mulai_at` datetime NOT NULL,
  `selesai_at` datetime NOT NULL,
  `syarat_ketentuan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `projection_transactions` int(10) UNSIGNED DEFAULT NULL,
  `projection_revenue` decimal(15,2) DEFAULT NULL,
  `projection_discount` decimal(15,2) DEFAULT NULL,
  `projection_margin` decimal(15,2) DEFAULT NULL,
  `spk_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `spk_recommendation` varchar(40) DEFAULT NULL,
  `estimated_cost_rate` decimal(8,4) DEFAULT NULL,
  `approved_spk_simulation_id` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_member`
--

INSERT INTO `promo_member` (`id_promo`, `kode_promo`, `nama`, `deskripsi`, `jenis_benefit`, `nilai_benefit`, `minimum_transaksi`, `maksimum_benefit`, `quota`, `usage_per_member`, `member_only`, `berlaku_untuk`, `mulai_at`, `selesai_at`, `syarat_ketentuan`, `status`, `created_by`, `created_at`, `updated_at`, `projection_transactions`, `projection_revenue`, `projection_discount`, `projection_margin`, `spk_score`, `spk_recommendation`, `estimated_cost_rate`, `approved_spk_simulation_id`) VALUES
(1, 'YZA-SERVICE10', 'Promo Servis Member', 'Benefit katalog servis untuk YZA Member.', 'persentase', 10.00, 0.00, NULL, NULL, NULL, 1, 'servis', '2026-09-30 00:00:00', '2027-09-30 00:00:00', 'Promo tersedia sebagai benefit katalog YZA Member dan belum merupakan potongan transaksi.', 'aktif', NULL, '2026-09-29 21:54:02', '2026-09-29 21:54:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'YZA-CARWASH20', 'Promo Car Wash Member', 'Benefit katalog car wash untuk YZA Member.', 'nominal', 20000.00, 0.00, NULL, NULL, NULL, 1, 'cuci_mobil', '2026-09-30 00:00:00', '2027-09-30 00:00:00', 'Promo tersedia sebagai benefit katalog YZA Member dan belum merupakan potongan transaksi.', 'aktif', NULL, '2026-09-29 21:54:02', '2026-09-29 21:54:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'YZA-PARTS5', 'Promo Spare Part Member', 'Benefit katalog spare part untuk YZA Member.', 'persentase', 5.00, 0.00, NULL, NULL, NULL, 1, 'penjualan_sparepart', '2026-09-30 00:00:00', '2027-09-30 00:00:00', 'Promo tersedia sebagai benefit katalog YZA Member dan belum merupakan potongan transaksi.', 'aktif', NULL, '2026-09-29 21:54:02', '2026-09-29 21:54:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(522, 'YZA-CARSALE5', 'Promo Penjualan Mobil Member', NULL, 'persentase', 5.00, 0.00, 10000000.00, NULL, NULL, 0, 'penjualan_mobil', '2026-10-04 00:00:00', '2027-10-04 00:00:00', 'Data demo Stage 14.', 'aktif', NULL, '2026-10-04 05:34:35', '2026-10-04 05:34:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(523, 'YZA-TRADEIN5', 'Promo Trade-In Member', NULL, 'nominal', 5000000.00, 0.00, NULL, NULL, NULL, 0, 'trade_in', '2026-10-04 00:00:00', '2027-10-04 00:00:00', 'Data demo Stage 14.', 'aktif', NULL, '2026-10-04 05:34:35', '2026-10-04 05:34:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(524, 'YZA-SPK-LAYAK', 'Promo SPK LAYAK', NULL, 'persentase', 5.00, 0.00, 10000000.00, NULL, NULL, 0, 'penjualan_mobil', '2026-10-04 00:00:00', '2027-10-04 00:00:00', 'Draft demo SPK.', 'nonaktif', NULL, '2026-10-04 05:34:35', '2026-10-04 05:34:35', NULL, NULL, NULL, NULL, 88, 'AMAN / LAYAK', NULL, 'SPK-DEMO-LAYAK'),
(525, 'YZA-SPK-BERISIKO', 'Promo SPK BERISIKO', NULL, 'persentase', 50.00, 0.00, 10000000.00, NULL, NULL, 0, 'penjualan_mobil', '2026-10-04 00:00:00', '2027-10-04 00:00:00', 'Draft demo SPK.', 'nonaktif', NULL, '2026-10-04 05:34:35', '2026-10-04 05:34:35', NULL, NULL, NULL, NULL, 45, 'BERISIKO', NULL, 'SPK-DEMO-BERISIKO');

-- --------------------------------------------------------

--
-- Table structure for table `promo_redemptions`
--

CREATE TABLE `promo_redemptions` (
  `id_redemption` bigint(20) UNSIGNED NOT NULL,
  `id_promo` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) NOT NULL,
  `total_final` decimal(15,2) NOT NULL,
  `status` enum('reserved','completed','released') NOT NULL DEFAULT 'completed',
  `used_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_simulations`
--

CREATE TABLE `promo_simulations` (
  `id_promo_simulation` bigint(20) UNSIGNED NOT NULL,
  `simulation_id` varchar(80) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `scope` varchar(40) NOT NULL DEFAULT 'semua',
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `max_discount` decimal(15,2) DEFAULT NULL,
  `min_transaction` decimal(15,2) NOT NULL DEFAULT 0.00,
  `input_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_payload`)),
  `result_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`result_payload`)),
  `score` tinyint(3) UNSIGNED DEFAULT NULL,
  `status` varchar(40) DEFAULT NULL,
  `recommendation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_simulations`
--

INSERT INTO `promo_simulations` (`id_promo_simulation`, `simulation_id`, `created_by`, `scope`, `discount_type`, `discount_value`, `max_discount`, `min_transaction`, `input_payload`, `result_payload`, `score`, `status`, `recommendation`, `created_at`, `updated_at`) VALUES
(2, 'SPK-DEMO-LAYAK', NULL, 'penjualan_mobil', 'percentage', 5.00, 10000000.00, 0.00, '{\"demo\":true}', '{\"before\":{\"revenue\":1000000,\"cost\":300000,\"margin\":700000},\"after\":{\"revenue\":900000,\"cost\":300000,\"margin\":600000},\"projection\":{\"transactions\":10,\"revenue\":9000000,\"discount\":1000000,\"margin\":6000000},\"scores\":[],\"final_score\":88,\"status\":\"AMAN \\/ LAYAK\",\"recommendations\":[]}', 88, 'AMAN / LAYAK', 'Data demo Stage 14', '2026-10-04 05:34:35', '2026-10-04 05:34:35'),
(3, 'SPK-DEMO-BERISIKO', NULL, 'penjualan_mobil', 'percentage', 50.00, 10000000.00, 0.00, '{\"demo\":true}', '{\"before\":{\"revenue\":1000000,\"cost\":300000,\"margin\":700000},\"after\":{\"revenue\":500000,\"cost\":300000,\"margin\":200000},\"projection\":{\"transactions\":10,\"revenue\":5000000,\"discount\":5000000,\"margin\":2000000},\"scores\":[],\"final_score\":45,\"status\":\"BERISIKO\",\"recommendations\":[]}', 45, 'BERISIKO', 'Data demo Stage 14', '2026-10-04 05:34:35', '2026-10-04 05:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_poin`
--

CREATE TABLE `riwayat_poin` (
  `id_riwayat_poin` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis` enum('perolehan','pembalikan') NOT NULL,
  `perubahan_poin` bigint(20) UNSIGNED NOT NULL,
  `saldo_setelah` bigint(20) UNSIGNED NOT NULL,
  `idempotency_key` varchar(120) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_poin`
--

INSERT INTO `riwayat_poin` (`id_riwayat_poin`, `id_pelanggan`, `id_transaksi`, `jenis`, `perubahan_poin`, `saldo_setelah`, `idempotency_key`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'perolehan', 24000, 24000, 'earn:transaction:1', 'Perolehan YZA Points dari TRX-202609-2105105C', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(2, 1, 2, 'perolehan', 10, 24010, 'earn:transaction:2', 'Perolehan YZA Points dari TRX-202609-B4218986', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(3, 1, 3, 'perolehan', 3, 24013, 'earn:transaction:3', 'Perolehan YZA Points dari TRX-202609-9D1F8EF4', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(4, 1, 4, 'perolehan', 18, 24031, 'earn:transaction:4', 'Perolehan YZA Points dari TRX-202609-A3095A75', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(5, 1, 5, 'perolehan', 7, 24038, 'earn:transaction:5', 'Perolehan YZA Points dari TRX-202609-89EE590D', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(6, 1, 7, 'perolehan', 3000, 27038, 'earn:transaction:7', 'Perolehan YZA Points dari TRX-202609-C17A881F', '2026-09-29 21:42:14', '2026-09-29 21:42:14'),
(1790, 750, 19966, 'perolehan', 24, 24, 'earn:transaction:19966', 'Perolehan YZA Points dari TRX-202610-5ABE0909', '2026-10-11 15:10:39', '2026-10-11 15:10:39'),
(1791, 750, 19967, 'perolehan', 24, 48, 'earn:transaction:19967', 'Perolehan YZA Points dari TRX-202610-E8DF8099', '2026-10-11 15:10:53', '2026-10-11 15:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id_riwayat` bigint(20) UNSIGNED NOT NULL,
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `jenis_transaksi` enum('stok_masuk','penjualan','servis','penyesuaian') NOT NULL,
  `jumlah_perubahan` int(11) NOT NULL,
  `stok_sebelum` int(10) UNSIGNED NOT NULL,
  `stok_sesudah` int(10) UNSIGNED NOT NULL,
  `referensi_tipe` varchar(50) DEFAULT NULL,
  `referensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id_riwayat`, `id_sparepart`, `id_petugas`, `tanggal`, `jenis_transaksi`, `jumlah_perubahan`, `stok_sebelum`, `stok_sesudah`, `referensi_tipe`, `referensi_id`, `catatan`, `created_at`) VALUES
(1, 1, 1, '2026-09-23 12:08:49', 'stok_masuk', 6, 0, 6, 'stok_masuk', 1, 'Penerimaan SM-202609-A575A5F0', '2026-09-23 05:08:49'),
(2, 1, 1, '2026-09-23 12:09:09', 'penyesuaian', -2, 6, 4, 'sparepart', 1, 'Stock opname menipis — QA Tahap 3', '2026-09-23 05:09:09'),
(3, 1, 1, '2026-09-23 12:09:09', 'penyesuaian', -4, 4, 0, 'sparepart', 1, 'Stock opname habis — QA Tahap 3', '2026-09-23 05:09:09'),
(4, 1, 1, '2026-09-23 12:09:09', 'penyesuaian', 10, 0, 10, 'sparepart', 1, 'Restock QA — QA Tahap 3', '2026-09-23 05:09:09'),
(5, 1, 1, '2026-09-23 12:41:57', 'penjualan', -3, 10, 7, 'penjualan_sparepart', 1, 'Finalisasi SPS-202609-29E646FA', '2026-09-23 05:41:57'),
(6, 1, 2, '2026-09-23 12:47:39', 'penjualan', -1, 7, 6, 'penjualan_sparepart', 2, 'Finalisasi SPS-202609-6524502C', '2026-09-23 05:47:39'),
(7, 1, 2, '2026-09-23 15:12:46', 'servis', -1, 6, 5, 'servis', 1, 'Finalisasi SRV-202609-70A40DC2', '2026-09-23 08:12:46'),
(4693, 450, 1, '2026-10-11 22:09:03', 'stok_masuk', 20, 0, 20, 'stok_masuk', 450, 'Penerimaan SM-20261011220902-894', '2026-10-11 15:09:03'),
(4694, 450, 1, '2026-10-11 22:09:16', 'stok_masuk', 20, 20, 40, 'stok_masuk', 451, 'Penerimaan SM-20261011220916-282', '2026-10-11 15:09:16'),
(4695, 450, 1, '2026-10-11 22:09:45', 'stok_masuk', 20, 0, 20, 'stok_masuk', 452, 'Penerimaan SM-20261011220945-442', '2026-10-11 15:09:45'),
(4696, 450, 1, '2026-10-11 22:10:18', 'stok_masuk', 20, 0, 20, 'stok_masuk', 453, 'Penerimaan SM-20261011221017-704', '2026-10-11 15:10:18'),
(4697, 450, 1, '2026-10-11 22:10:18', 'servis', -1, 20, 19, 'servis', 13823, 'Finalisasi SRV-202610-E046A133', '2026-10-11 15:10:18'),
(4698, 450, 1, '2026-10-11 22:10:39', 'stok_masuk', 20, 0, 20, 'stok_masuk', 454, 'Penerimaan SM-20261011221039-155', '2026-10-11 15:10:39'),
(4699, 450, 1, '2026-10-11 22:10:39', 'servis', -1, 20, 19, 'servis', 13824, 'Finalisasi SRV-202610-5738F37B', '2026-10-11 15:10:39'),
(4700, 450, 1, '2026-10-11 22:10:53', 'stok_masuk', 20, 0, 20, 'stok_masuk', 455, 'Penerimaan SM-20261011221053-964', '2026-10-11 15:10:53'),
(4701, 450, 1, '2026-10-11 22:10:53', 'servis', -1, 20, 19, 'servis', 13825, 'Finalisasi SRV-202610-5B49AEEA', '2026-10-11 15:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` tinyint(3) UNSIGNED NOT NULL,
  `nama_role` varchar(30) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `nama_role`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Akses penuh untuk manajemen sistem YZA Automotive', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(2, 'petugas', 'Akses operasional dealer', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(3, 'pelanggan', 'Portal pelanggan', '2026-09-23 03:31:41', '2026-09-23 03:31:41'),
(4, 'super_admin', 'Akses penuh seluruh sistem', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(5, 'owner', 'Pimpinan bisnis dan laporan read-only', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(6, 'sales', 'Penjualan dan prospek pelanggan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(7, 'service_advisor', 'Booking dan operasional service advisor', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(8, 'mechanic', 'Pekerjaan mekanik dan work order terassign', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(9, 'warehouse_staff', 'Sparepart dan inventori gudang', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(10, 'finance', 'Pembayaran dan laporan penerimaan', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(11, 'operational_staff', 'Operasional kendaraan, test drive, dan car wash', '2026-10-15 04:37:50', '2026-10-15 04:37:50'),
(12, 'quality_control', 'QC dan PDI kendaraan', '2026-10-15 04:37:50', '2026-10-15 04:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id_role` tinyint(3) UNSIGNED NOT NULL,
  `id_permission` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id_role`, `id_permission`, `created_at`, `updated_at`) VALUES
(5, 1, NULL, NULL),
(5, 2, NULL, NULL),
(5, 4, NULL, NULL),
(5, 5, NULL, NULL),
(5, 8, NULL, NULL),
(5, 10, NULL, NULL),
(5, 12, NULL, NULL),
(6, 1, NULL, NULL),
(6, 2, NULL, NULL),
(6, 3, NULL, NULL),
(6, 4, NULL, NULL),
(7, 1, NULL, NULL),
(7, 4, NULL, NULL),
(7, 5, NULL, NULL),
(7, 6, NULL, NULL),
(8, 1, NULL, NULL),
(8, 5, NULL, NULL),
(8, 7, NULL, NULL),
(8, 8, NULL, NULL),
(9, 1, NULL, NULL),
(9, 8, NULL, NULL),
(9, 9, NULL, NULL),
(10, 1, NULL, NULL),
(10, 10, NULL, NULL),
(10, 11, NULL, NULL),
(10, 12, NULL, NULL),
(11, 1, NULL, NULL),
(11, 5, NULL, NULL),
(12, 1, NULL, NULL),
(12, 5, NULL, NULL),
(12, 15, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_assignment_audits`
--

CREATE TABLE `sales_assignment_audits` (
  `id_sales_assignment_audit` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(40) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `previous_sales_id` bigint(20) UNSIGNED DEFAULT NULL,
  `new_sales_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_petugas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_awards`
--

CREATE TABLE `sales_awards` (
  `id_sales_award` bigint(20) UNSIGNED NOT NULL,
  `award_year` smallint(5) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'provisional',
  `rule_version` varchar(40) NOT NULL DEFAULT 'units-target-achievement-value-v1',
  `method_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`method_snapshot`)),
  `finalized_by` bigint(20) UNSIGNED DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_awards`
--

INSERT INTO `sales_awards` (`id_sales_award`, `award_year`, `status`, `rule_version`, `method_snapshot`, `finalized_by`, `finalized_at`, `created_at`, `updated_at`) VALUES
(1, 2026, 'provisional', 'units-target-achievement-value-v1', '{\"rule_version\":\"units-target-achievement-value-v1\",\"priority\":[\"final_units\",\"target_achievement\",\"final_amount\"],\"valid_sale\":[\"transaction_not_cancelled\",\"verified_incoming_payment\",\"handover_status_diserahkan\",\"handover_date_in_award_year\"],\"digital_sales\":\"Included in company totals but not attributed when id_sales is null\",\"timezone\":\"Asia\\/Jakarta\"}', NULL, NULL, '2026-10-15 08:39:02', '2026-10-15 08:39:02');

-- --------------------------------------------------------

--
-- Table structure for table `sales_award_audits`
--

CREATE TABLE `sales_award_audits` (
  `id_sales_award_audit` bigint(20) UNSIGNED NOT NULL,
  `id_sales_award` bigint(20) UNSIGNED NOT NULL,
  `actor_petugas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_award_results`
--

CREATE TABLE `sales_award_results` (
  `id_sales_award_result` bigint(20) UNSIGNED NOT NULL,
  `id_sales_award` bigint(20) UNSIGNED NOT NULL,
  `id_sales` bigint(20) UNSIGNED DEFAULT NULL,
  `ranking` int(10) UNSIGNED DEFAULT NULL,
  `final_units` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `target_units` int(10) UNSIGNED DEFAULT NULL,
  `achievement_percentage` decimal(8,2) DEFAULT NULL,
  `final_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `eligibility_status` varchar(40) NOT NULL DEFAULT 'eligible',
  `tie_group` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_leads`
--

CREATE TABLE `sales_leads` (
  `id_sales_lead` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_mobil` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sales` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_prospek` varchar(120) NOT NULL,
  `kontak` varchar(120) DEFAULT NULL,
  `sumber` varchar(60) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'baru',
  `follow_up_at` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_lead_activities`
--

CREATE TABLE `sales_lead_activities` (
  `id_sales_lead_activity` bigint(20) UNSIGNED NOT NULL,
  `id_sales_lead` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis` varchar(40) NOT NULL DEFAULT 'catatan',
  `catatan` text NOT NULL,
  `follow_up_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_targets`
--

CREATE TABLE `sales_targets` (
  `id_sales_target` bigint(20) UNSIGNED NOT NULL,
  `id_sales` bigint(20) UNSIGNED NOT NULL,
  `periode_mulai` date NOT NULL,
  `periode_selesai` date NOT NULL,
  `target_unit` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `target_nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `serah_terima_pesanan_mobil`
--

CREATE TABLE `serah_terima_pesanan_mobil` (
  `id_serah_terima` bigint(20) UNSIGNED NOT NULL,
  `id_pesanan` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_penerima` varchar(120) NOT NULL,
  `nomor_identitas` varchar(80) DEFAULT NULL,
  `pelunasan_terverifikasi` tinyint(1) NOT NULL DEFAULT 0,
  `dokumen_lengkap` tinyint(1) NOT NULL DEFAULT 0,
  `kendaraan_sesuai` tinyint(1) NOT NULL DEFAULT 0,
  `catatan` text DEFAULT NULL,
  `diserahkan_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `serah_terima_pesanan_mobil`
--

INSERT INTO `serah_terima_pesanan_mobil` (`id_serah_terima`, `id_pesanan`, `id_petugas`, `nama_penerima`, `nomor_identitas`, `pelunasan_terverifikasi`, `dokumen_lengkap`, `kendaraan_sesuai`, `catatan`, `diserahkan_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Lintar Yan Zuhara', '3173000000000001', 1, 1, 1, 'Serah terima resmi berhasil.', '2026-10-11 13:22:10', '2026-10-11 13:22:10', '2026-10-11 13:22:10');

-- --------------------------------------------------------

--
-- Table structure for table `service_approval_requests`
--

CREATE TABLE `service_approval_requests` (
  `id_service_approval_request` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `id_service_finding` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_approval_requests`
--

INSERT INTO `service_approval_requests` (`id_service_approval_request`, `id_servis`, `id_service_finding`, `title`, `reason`, `status`, `requested_by`, `requested_at`, `responded_at`, `customer_note`, `created_at`, `updated_at`) VALUES
(1, 13822, 1, 'Persetujuan Penggantian Filter Oli', 'Filter oli ditemukan kotor saat pemeriksaan dan    direkomendasikan untuk diganti bersamaan dengan oli mesin', 'approved', 1, '2026-10-12 11:32:03', '2026-10-12 13:54:32', 'Setuju, silakan lakukan penggantian filter oli sesuai hasil pemeriksaan dan estimasi biaya yang diberikan.', '2026-10-12 11:32:03', '2026-10-12 13:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `service_approval_request_items`
--

CREATE TABLE `service_approval_request_items` (
  `id_service_approval_request_item` bigint(20) UNSIGNED NOT NULL,
  `approval_request_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price_snapshot` decimal(15,2) NOT NULL,
  `total_snapshot` decimal(15,2) NOT NULL,
  `id_jenis_servis` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sparepart` bigint(20) UNSIGNED DEFAULT NULL,
  `applied_detail_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applied_sparepart_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_approval_request_items`
--

INSERT INTO `service_approval_request_items` (`id_service_approval_request_item`, `approval_request_id`, `item_type`, `description`, `quantity`, `unit_price_snapshot`, `total_snapshot`, `id_jenis_servis`, `id_sparepart`, `applied_detail_id`, `applied_sparepart_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'sparepart', 'Oli Mesin Fully Synthetic 0W-20 (1 Liter)', 1, 165000.00, 165000.00, NULL, 450, NULL, 3063, '2026-10-12 11:32:03', '2026-10-12 13:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `service_blackout_dates`
--

CREATE TABLE `service_blackout_dates` (
  `id_service_blackout_date` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 1,
  `custom_open_time` time DEFAULT NULL,
  `custom_close_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_execution_events`
--

CREATE TABLE `service_execution_events` (
  `id_service_execution_event` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `event_key` varchar(60) NOT NULL,
  `label_snapshot` varchar(160) NOT NULL,
  `actor_type` varchar(40) DEFAULT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_execution_events`
--

INSERT INTO `service_execution_events` (`id_service_execution_event`, `id_servis`, `event_key`, `label_snapshot`, `actor_type`, `actor_id`, `metadata`, `occurred_at`) VALUES
(1, 13822, 'inspection_completed', 'Pemeriksaan selesai', 'petugas', 1, NULL, '2026-10-12 11:22:23'),
(2, 13822, 'finding_created', 'Temuan tambahan dibuat', 'petugas', 1, '{\"finding_id\":1}', '2026-10-12 11:28:52'),
(3, 13822, 'approval_requested', 'Persetujuan customer diminta', 'petugas', 1, '{\"approval_id\":1}', '2026-10-12 11:32:03'),
(4, 13822, 'approval_approved', 'Approval customer disetujui', 'pelanggan', 750, '{\"approval_id\":1}', '2026-10-12 13:54:32'),
(5, 13822, 'approval_clarification_requested', 'Klarifikasi keterkaitan item approval diajukan', 'petugas', 1, '{\"approval_id\":1,\"approval_item_ids\":[1],\"reason\":\"Snapshot item approval tidak sesuai dengan temuan\\/rekomendasi teknisi; persetujuan baru diperlukan untuk pekerjaan dan item sebenarnya.\",\"next_action\":\"Buat persetujuan customer baru dengan pekerjaan dan item sebenarnya.\"}', '2026-10-12 20:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `service_findings`
--

CREATE TABLE `service_findings` (
  `id_service_finding` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `recommendation` text DEFAULT NULL,
  `severity` varchar(30) NOT NULL DEFAULT 'info',
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_findings`
--

INSERT INTO `service_findings` (`id_service_finding`, `id_servis`, `title`, `description`, `recommendation`, `severity`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 13822, 'Filter Oli Perlu Diganti', 'Filter oli ditemukan kotor dan sudah tidak optimal untuk digunakan kembali.', 'Disarankan mengganti filter oli bersamaan dengan penggantian oli mesin.', 'warning', 'open', 1, '2026-10-12 11:28:52', '2026-10-12 11:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `service_operating_hours`
--

CREATE TABLE `service_operating_hours` (
  `id_service_operating_hour` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 0,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `slot_interval_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_operating_hours`
--

INSERT INTO `service_operating_hours` (`id_service_operating_hour`, `day_of_week`, `is_open`, `open_time`, `close_time`, `break_start`, `break_end`, `slot_interval_minutes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(2, 2, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(3, 3, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(4, 4, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(5, 5, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(6, 6, 0, NULL, NULL, NULL, NULL, 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36'),
(7, 7, 0, NULL, NULL, NULL, NULL, 60, '2026-10-12 08:45:36', '2026-10-12 08:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `service_quality_controls`
--

CREATE TABLE `service_quality_controls` (
  `id_service_quality_control` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'not_started',
  `checked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_quality_control_items`
--

CREATE TABLE `service_quality_control_items` (
  `id_service_quality_control_item` bigint(20) UNSIGNED NOT NULL,
  `quality_control_id` bigint(20) UNSIGNED NOT NULL,
  `item_key` varchar(80) NOT NULL,
  `label_snapshot` varchar(180) NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `result` varchar(10) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_specializations`
--

CREATE TABLE `service_specializations` (
  `id_service_specialization` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_specializations`
--

INSERT INTO `service_specializations` (`id_service_specialization`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'General Service', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(2, 'Engine', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(3, 'Transmission', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(4, 'Brake', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(5, 'Electrical', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(6, 'AC', 1, '2026-10-12 09:17:48', '2026-10-12 09:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `service_staff_profiles`
--

CREATE TABLE `service_staff_profiles` (
  `id_service_staff_profile` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `can_be_technician` tinyint(1) NOT NULL DEFAULT 0,
  `can_be_service_advisor` tinyint(1) NOT NULL DEFAULT 0,
  `experience_years` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_bookable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_staff_profiles`
--

INSERT INTO `service_staff_profiles` (`id_service_staff_profile`, `id_petugas`, `can_be_technician`, `can_be_service_advisor`, `experience_years`, `is_bookable`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 0, 0, 0, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(2, 2, 0, 0, 0, 0, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(3, 3, 0, 0, 0, 0, '2026-10-12 09:17:48', '2026-10-12 09:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `service_staff_specializations`
--

CREATE TABLE `service_staff_specializations` (
  `id_service_staff_profile` bigint(20) UNSIGNED NOT NULL,
  `id_service_specialization` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_work_order_assignments`
--

CREATE TABLE `service_work_order_assignments` (
  `id_service_work_order_assignment` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `assignment_role` varchar(20) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unassigned_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_work_order_assignments`
--

INSERT INTO `service_work_order_assignments` (`id_service_work_order_assignment`, `id_servis`, `id_petugas`, `assignment_role`, `assigned_at`, `unassigned_at`, `assigned_by`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'primary', '2026-09-23 08:12:25', NULL, 2, 'Migrasi riwayat penugasan awal', '2026-09-23 08:12:25', '2026-09-23 08:12:46'),
(2, 2, 2, 'primary', '2026-09-23 08:14:51', NULL, 2, 'Migrasi riwayat penugasan awal', '2026-09-23 08:14:51', '2026-09-23 08:14:51'),
(3, 13822, 1, 'primary', '2026-10-11 15:09:46', NULL, 1, 'Migrasi riwayat penugasan awal', '2026-10-11 15:09:46', '2026-10-12 11:22:23'),
(4, 13823, 1, 'primary', '2026-10-11 15:10:18', NULL, 1, 'Migrasi riwayat penugasan awal', '2026-10-11 15:10:18', '2026-10-11 15:10:18'),
(5, 13824, 1, 'primary', '2026-10-11 15:10:39', NULL, 1, 'Migrasi riwayat penugasan awal', '2026-10-11 15:10:39', '2026-10-11 15:10:39'),
(6, 13825, 1, 'primary', '2026-10-11 15:10:53', NULL, 1, 'Migrasi riwayat penugasan awal', '2026-10-11 15:10:53', '2026-10-11 15:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `servis`
--

CREATE TABLE `servis` (
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `kode_servis` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_booking_servis` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_kendaraan_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `id_service_advisor` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_masuk` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `kilometer` int(10) UNSIGNED DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `hasil_pemeriksaan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `biaya_jasa` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_sparepart` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('menunggu','diterima','pemeriksaan','dikerjakan','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `servis`
--

INSERT INTO `servis` (`id_servis`, `kode_servis`, `id_transaksi`, `id_booking_servis`, `id_pelanggan`, `id_kendaraan_pelanggan`, `id_petugas`, `id_service_advisor`, `tanggal_masuk`, `tanggal_selesai`, `kilometer`, `keluhan`, `hasil_pemeriksaan`, `catatan`, `biaya_jasa`, `biaya_sparepart`, `total_biaya`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SRV-202609-70A40DC2', 4, 1, 1, 1, 2, NULL, '2026-09-23 15:12:25', '2026-09-23 15:12:46', 45000, 'QA servis', 'Perlu ganti oli', 'QA', 150000.00, 35000.00, 185000.00, 'selesai', '2026-09-23 08:12:25', '2026-09-23 08:12:46'),
(2, 'SRV-202609-33D6B419', NULL, NULL, 1, 1, 2, NULL, '2026-09-23 15:14:51', NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 'dikerjakan', '2026-09-23 08:14:51', '2026-09-23 08:14:51'),
(13822, 'SRV-202610-6935FE9E', NULL, 16155, 750, 24348, 1, NULL, '2026-10-11 22:09:46', NULL, 5000, 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Kondisi oli mesin sudah menghitam dan perlu diganti. Filter oli perlu diperiksa/ganti. Level fluida normal, tidak ditemukan kebocoran mesin. Sistem rem, kelistrikan, ban, dan indikator dashboard dalam kondisi normal.', 'Kendaraan diterima di service bay. Pemeriksaan awal telah dilakukan. Direkomendasikan penggantian oli mesin dan pemeriksaan filter oli sesuai jadwal servis berkala.', 0.00, 0.00, 0.00, 'dikerjakan', '2026-10-11 15:09:46', '2026-10-12 11:22:23'),
(13823, 'SRV-202610-E046A133', 19965, 16156, 750, 24348, 1, NULL, '2026-10-11 22:10:18', NULL, 5000, 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Kondisi oli lama hitam pekat, level normal. Filter oli kotor. Siap dilakukan drain & refil oli baru.', 'Pemeriksaan awal tuntas.', 75000.00, 165000.00, 240000.00, 'dikerjakan', '2026-10-11 15:10:18', '2026-10-11 15:10:18'),
(13824, 'SRV-202610-5738F37B', 19966, 16157, 750, 24348, 1, NULL, '2026-10-11 22:10:39', '2026-10-11 22:10:39', 5000, 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Kondisi oli lama hitam pekat, level normal. Filter oli kotor. Siap dilakukan drain & refil oli baru.', 'Pemeriksaan awal tuntas.', 75000.00, 165000.00, 240000.00, 'selesai', '2026-10-11 15:10:39', '2026-10-11 15:10:39'),
(13825, 'SRV-202610-5B49AEEA', 19967, 16158, 750, 24348, 1, NULL, '2026-10-11 22:10:53', '2026-10-11 22:10:53', 5000, 'Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA', 'Kondisi oli lama hitam pekat, level normal. Filter oli kotor. Siap dilakukan drain & refil oli baru.', 'Pemeriksaan awal tuntas.', 75000.00, 165000.00, 240000.00, 'selesai', '2026-10-11 15:10:53', '2026-10-11 15:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `servis_sparepart`
--

CREATE TABLE `servis_sparepart` (
  `id_servis_sparepart` bigint(20) UNSIGNED NOT NULL,
  `id_servis` bigint(20) UNSIGNED NOT NULL,
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `servis_sparepart`
--

INSERT INTO `servis_sparepart` (`id_servis_sparepart`, `id_servis`, `id_sparepart`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 1, 35000.00, 35000.00),
(2, 2, 1, 6, 35000.00, 210000.00),
(3060, 13823, 450, 1, 165000.00, 165000.00),
(3061, 13824, 450, 1, 165000.00, 165000.00),
(3062, 13825, 450, 1, 165000.00, 165000.00),
(3063, 13822, 450, 1, 165000.00, 165000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sparepart`
--

CREATE TABLE `sparepart` (
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `kode_sparepart` varchar(30) NOT NULL,
  `id_kategori` bigint(20) UNSIGNED NOT NULL,
  `id_supplier` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_sparepart` varchar(150) NOT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `nomor_part_oem` varchar(80) DEFAULT NULL,
  `nomor_part_aftermarket` varchar(80) DEFAULT NULL,
  `deskripsi_spesifikasi` text DEFAULT NULL,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_diperbarui_at` datetime DEFAULT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stok_minimum` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `satuan` varchar(30) NOT NULL DEFAULT 'pcs',
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sparepart`
--

INSERT INTO `sparepart` (`id_sparepart`, `kode_sparepart`, `id_kategori`, `id_supplier`, `nama_sparepart`, `merk`, `nomor_part_oem`, `nomor_part_aftermarket`, `deskripsi_spesifikasi`, `harga_beli`, `harga_jual`, `harga_diperbarui_at`, `stok`, `stok_minimum`, `satuan`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DEV-SP-3', 1, NULL, 'Filter Oli Development', 'YZA Test', NULL, NULL, NULL, 25000.00, 35000.00, NULL, 5, 5, 'pcs', 'dcc79a6bbfb735738936d5268eb058c1.png', 'aktif', '2026-09-23 05:08:49', '2026-09-23 08:12:46'),
(450, 'YZA-OIL-SYN1L', 1, NULL, 'Oli Mesin Fully Synthetic 0W-20 (1 Liter)', 'YZA Genuine Oil', NULL, NULL, NULL, 125000.00, 165000.00, NULL, 19, 6, 'botol 1L', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 15:10:53'),
(451, 'YZA-OIL-SYN4L', 1, NULL, 'Oli Mesin Fully Synthetic 5W-30 (Galon 4 Liter)', 'YZA Genuine Oil', NULL, NULL, NULL, 450000.00, 590000.00, NULL, 0, 4, 'galon 4L', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(452, 'YZA-OIL-ATF1L', 1, NULL, 'Oli Transmisi Matic ATF WS / CVT Fluid (1 Liter)', 'YZA Fluid Tech', NULL, NULL, NULL, 110000.00, 150000.00, NULL, 0, 4, 'botol 1L', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(453, 'YZA-FLT-OIL01', 2, NULL, 'Filter Oli Mesin Presisi Tinggi (Oil Filter)', 'YZA Parts', NULL, NULL, NULL, 45000.00, 65000.00, NULL, 0, 5, 'pcs', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(454, 'YZA-FLT-AIR01', 2, NULL, 'Filter Udara Mesin Heavy Duty (Air Filter)', 'YZA Filter', NULL, NULL, NULL, 95000.00, 135000.00, NULL, 0, 4, 'pcs', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(455, 'YZA-FLT-CAB01', 2, NULL, 'Filter Kabin AC Karbon Aktif (Cabin Air Filter)', 'YZA Filter', NULL, NULL, NULL, 85000.00, 120000.00, NULL, 0, 4, 'pcs', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(456, 'YZA-IGN-SPK01', 8, NULL, 'Busi Iridium Performance (Spark Plug)', 'NGK Laser Iridium by YZA', NULL, NULL, NULL, 115000.00, 160000.00, NULL, 0, 8, 'pcs', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(457, 'YZA-BRK-PADF1', 3, NULL, 'Kampas Rem Depan Ceramic (Brake Pad Front Set)', 'YZA Performance Brakes', NULL, NULL, NULL, 380000.00, 520000.00, NULL, 0, 3, 'set', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(458, 'YZA-BRK-PADR1', 3, NULL, 'Kampas Rem Belakang (Brake Pad Rear Set)', 'YZA Performance Brakes', NULL, NULL, NULL, 320000.00, 450000.00, NULL, 0, 2, 'set', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(459, 'YZA-BRK-FLD01', 3, NULL, 'Minyak Rem DOT 4 Synthetic (500 ml)', 'Brembo / YZA Fluid', NULL, NULL, NULL, 55000.00, 80000.00, NULL, 0, 5, 'botol 500ml', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(460, 'YZA-CLT-RAD01', 240, NULL, 'Coolant Radiator Long Life Antifreeze (Galon 4 Liter)', 'YZA Cooling Tech', NULL, NULL, NULL, 90000.00, 130000.00, NULL, 0, 3, 'galon 4L', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(461, 'YZA-ELC-BAT01', 4, NULL, 'Aki Kering Maintenance Free MF 55D23L (60 Ampere)', 'GS Astra / YZA Power', NULL, NULL, NULL, 950000.00, 1250000.00, NULL, 0, 2, 'unit', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(462, 'YZA-WPR-SET01', 240, NULL, 'Wiper Blade Hybrid Aerodinamis (Sepasang 24 + 16 inch)', 'Bosch Aerotwin by YZA', NULL, NULL, NULL, 140000.00, 195000.00, NULL, 0, 4, 'set', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(463, 'YZA-CLN-WSH01', 240, NULL, 'Cairan Pembersih Kaca Konsentrat Wiper Fluid (1 Liter)', 'YZA Crystal Clear', NULL, NULL, NULL, 25000.00, 40000.00, NULL, 0, 5, 'botol 1L', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20'),
(464, 'YZA-CLN-ENG01', 1, NULL, 'Engine Flush & Treatment Conditioner (300 ml)', 'YZA Engine Care', NULL, NULL, NULL, 60000.00, 90000.00, NULL, 0, 5, 'kaleng 300ml', NULL, 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `sparepart_fitments`
--

CREATE TABLE `sparepart_fitments` (
  `id_sparepart_fitment` bigint(20) UNSIGNED NOT NULL,
  `id_sparepart` bigint(20) UNSIGNED NOT NULL,
  `merk_kendaraan` varchar(80) DEFAULT NULL,
  `model_kendaraan` varchar(120) DEFAULT NULL,
  `tahun_mulai` smallint(5) UNSIGNED DEFAULT NULL,
  `tahun_akhir` smallint(5) UNSIGNED DEFAULT NULL,
  `varian` varchar(120) DEFAULT NULL,
  `kode_mesin` varchar(80) DEFAULT NULL,
  `nomor_part_referensi` varchar(100) DEFAULT NULL,
  `status_verifikasi` enum('verified_fit','unverified','verified_not_fit') NOT NULL DEFAULT 'unverified',
  `sumber_bukti` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_masuk`
--

CREATE TABLE `stok_masuk` (
  `id_stok_masuk` bigint(20) UNSIGNED NOT NULL,
  `kode_stok_masuk` varchar(40) NOT NULL,
  `id_supplier` bigint(20) UNSIGNED DEFAULT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total_biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','diterima','dibatalkan') NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stok_masuk`
--

INSERT INTO `stok_masuk` (`id_stok_masuk`, `kode_stok_masuk`, `id_supplier`, `id_petugas`, `tanggal`, `total_biaya`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'SM-202609-A575A5F0', 1, 1, '2026-09-23 12:08:00', 150000.00, 'diterima', 'QA Tahap 3', '2026-09-23 05:08:49', '2026-09-23 05:08:49'),
(448, 'SM-20261011220347-475', NULL, 1, '2026-10-11 22:03:00', 9500000.00, 'draft', '', '2026-10-11 15:03:47', '2026-10-11 15:05:13'),
(449, 'SM-20261011220843-919', 1, 1, '2026-10-11 22:08:43', 0.00, 'draft', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:08:43', '2026-10-11 15:08:43'),
(450, 'SM-20261011220902-894', 1, 1, '2026-10-11 22:09:02', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:09:02', '2026-10-11 15:09:03'),
(451, 'SM-20261011220916-282', 1, 1, '2026-10-11 22:09:16', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:09:16', '2026-10-11 15:09:16'),
(452, 'SM-20261011220945-442', 1, 1, '2026-10-11 22:09:45', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:09:45', '2026-10-11 15:09:45'),
(453, 'SM-20261011221017-704', 1, 1, '2026-10-11 22:10:17', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:10:17', '2026-10-11 15:10:18'),
(454, 'SM-20261011221039-155', 1, 1, '2026-10-11 22:10:39', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:10:39', '2026-10-11 15:10:39'),
(455, 'SM-20261011221053-964', 1, 1, '2026-10-11 22:10:53', 2500000.00, 'diterima', 'Uji Demo Resmi: Pengadaan 20 Botol Oli Mesin YZA-OIL-SYN1L', '2026-10-11 15:10:53', '2026-10-11 15:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` bigint(20) UNSIGNED NOT NULL,
  `kode_supplier` varchar(30) NOT NULL,
  `nama_supplier` varchar(120) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kontak_person` varchar(120) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `kode_supplier`, `nama_supplier`, `no_hp`, `email`, `alamat`, `kontak_person`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DEV-SUP-3', 'Supplier Development 3', '081230000003', 'dev3@yza.local', 'Alamat Dev 3', 'QA YZA', 'aktif', '2026-09-23 05:08:48', '2026-09-23 05:08:48'),
(231, 'SUP-YZA-OFFICIAL', 'PT YZA Automotive Parts Indonesia', '081123456789', 'parts@yza-automotive.com', 'YZA Automotive Tower, Jl. Otomotif Raya No. 1, Jakarta Barat', 'Budi Santoso (Logistik)', 'aktif', '2026-10-11 13:35:20', '2026-10-11 13:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `technician_time_off`
--

CREATE TABLE `technician_time_off` (
  `id_technician_time_off` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `technician_working_hours`
--

CREATE TABLE `technician_working_hours` (
  `id_technician_working_hour` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL,
  `is_working` tinyint(1) NOT NULL DEFAULT 0,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `technician_working_hours`
--

INSERT INTO `technician_working_hours` (`id_technician_working_hour`, `id_petugas`, `day_of_week`, `is_working`, `open_time`, `close_time`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(2, 1, 2, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(3, 1, 3, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(4, 1, 4, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(5, 1, 5, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(6, 1, 6, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(7, 1, 7, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(8, 2, 1, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(9, 2, 2, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(10, 2, 3, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(11, 2, 4, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(12, 2, 5, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(13, 2, 6, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(14, 2, 7, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(15, 3, 1, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(16, 3, 2, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(17, 3, 3, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(18, 3, 4, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(19, 3, 5, 1, '08:00:00', '17:00:00', '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(20, 3, 6, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48'),
(21, 3, 7, 0, NULL, NULL, '2026-10-12 09:17:48', '2026-10-12 09:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_drive`
--

CREATE TABLE `test_drive` (
  `id_test_drive` bigint(20) UNSIGNED NOT NULL,
  `kode_test_drive` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_mobil` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sales` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `hasil` varchar(60) DEFAULT NULL,
  `catatan_sales` text DEFAULT NULL,
  `follow_up_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `test_drive`
--

INSERT INTO `test_drive` (`id_test_drive`, `kode_test_drive`, `id_pelanggan`, `id_mobil`, `id_petugas`, `id_sales`, `tanggal`, `jam`, `lokasi`, `catatan`, `status`, `hasil`, `catatan_sales`, `follow_up_at`, `created_at`, `updated_at`) VALUES
(1, 'TDR-202609-3C0B76', 1, 5, 2, NULL, '2026-09-24', '10:00:00', 'Showroom YZA', 'QA Test Drive', 'selesai', NULL, NULL, NULL, '2026-09-23 05:41:11', '2026-09-23 05:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `trade_in`
--

CREATE TABLE `trade_in` (
  `id_trade_in` bigint(20) UNSIGNED NOT NULL,
  `kode_trade_in` varchar(40) NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pelanggan` bigint(20) UNSIGNED NOT NULL,
  `id_pengajuan` bigint(20) UNSIGNED NOT NULL,
  `id_inspeksi` bigint(20) UNSIGNED NOT NULL,
  `id_mobil_lama_masuk` bigint(20) UNSIGNED DEFAULT NULL,
  `id_mobil_dibeli` bigint(20) UNSIGNED NOT NULL,
  `id_petugas` bigint(20) UNSIGNED NOT NULL,
  `nilai_mobil_lama` decimal(15,2) NOT NULL,
  `harga_mobil_dibeli` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selisih_bayar` decimal(15,2) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('proses','selesai','dibatalkan') NOT NULL DEFAULT 'proses',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trade_in`
--

INSERT INTO `trade_in` (`id_trade_in`, `kode_trade_in`, `id_transaksi`, `id_pelanggan`, `id_pengajuan`, `id_inspeksi`, `id_mobil_lama_masuk`, `id_mobil_dibeli`, `id_petugas`, `nilai_mobil_lama`, `harga_mobil_dibeli`, `diskon`, `selisih_bayar`, `tanggal`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 'TRD-202609-032CEF4C', 7, 1, 2, 2, 8, 7, 2, 50000000.00, 80000000.00, 0.00, 30000000.00, '2026-09-23 15:31:07', 'selesai', 'QA trade', '2026-09-23 08:31:07', '2026-09-23 08:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `kode_transaksi` varchar(40) NOT NULL,
  `id_pelanggan` bigint(20) UNSIGNED DEFAULT NULL,
  `id_petugas` bigint(20) UNSIGNED DEFAULT NULL,
  `id_promo` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_transaksi` enum('penjualan_mobil','pembelian_mobil','trade_in','penjualan_sparepart','servis','cuci_mobil') NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','belum_bayar','dp','lunas','dibatalkan') NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `promo_code_snapshot` varchar(50) DEFAULT NULL,
  `promo_name_snapshot` varchar(150) DEFAULT NULL,
  `promo_type_snapshot` varchar(30) DEFAULT NULL,
  `promo_value_snapshot` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `kode_transaksi`, `id_pelanggan`, `id_petugas`, `id_promo`, `jenis_transaksi`, `tanggal`, `subtotal`, `diskon`, `total`, `status`, `catatan`, `created_at`, `updated_at`, `promo_code_snapshot`, `promo_name_snapshot`, `promo_type_snapshot`, `promo_value_snapshot`) VALUES
(1, 'TRX-202609-2105105C', 1, 1, NULL, 'penjualan_mobil', '2026-09-23 12:41:23', 245000000.00, 5000000.00, 240000000.00, 'lunas', 'QA car sale', '2026-09-23 05:41:23', '2026-09-23 05:41:34', NULL, NULL, NULL, NULL),
(2, 'TRX-202609-B4218986', 1, 1, NULL, 'penjualan_sparepart', '2026-09-23 12:41:57', 105000.00, 0.00, 105000.00, 'lunas', 'QA sparepart sale', '2026-09-23 05:41:57', '2026-09-23 05:48:09', NULL, NULL, NULL, NULL),
(3, 'TRX-202609-9D1F8EF4', 1, 2, NULL, 'penjualan_sparepart', '2026-09-23 12:47:39', 35000.00, 0.00, 35000.00, 'lunas', 'Paket 2 QA total', '2026-09-23 05:47:39', '2026-09-23 05:47:53', NULL, NULL, NULL, NULL),
(4, 'TRX-202609-A3095A75', 1, 2, NULL, 'servis', '2026-09-23 15:12:46', 185000.00, 0.00, 185000.00, 'lunas', 'Tagihan SRV-202609-70A40DC2', '2026-09-23 08:12:46', '2026-09-23 08:12:46', NULL, NULL, NULL, NULL),
(5, 'TRX-202609-89EE590D', 1, 2, NULL, 'cuci_mobil', '2026-09-23 15:12:46', 75000.00, 0.00, 75000.00, 'lunas', 'Tagihan BCW-202609-067D478B', '2026-09-23 08:12:46', '2026-09-23 08:12:46', NULL, NULL, NULL, NULL),
(6, 'TRX-202609-6BFAD176', 1, 2, NULL, 'pembelian_mobil', '2026-09-23 15:29:46', 100000000.00, 0.00, 100000000.00, 'lunas', 'QA purchase', '2026-09-23 08:29:46', '2026-09-23 08:29:46', NULL, NULL, NULL, NULL),
(7, 'TRX-202609-C17A881F', 1, 2, NULL, 'trade_in', '2026-09-23 15:31:07', 30000000.00, 0.00, 30000000.00, 'lunas', 'QA trade', '2026-09-23 08:31:07', '2026-09-23 08:31:07', NULL, NULL, NULL, NULL),
(19956, 'STAGE4-70D50945', 1, 2, NULL, 'cuci_mobil', '2026-10-10 18:33:43', 10000.00, 0.00, 10000.00, 'belum_bayar', 'Stage 4 synthetic Midtrans sandbox UAT', '2026-10-10 11:33:43', '2026-10-10 11:33:43', NULL, NULL, NULL, NULL),
(19963, 'TRX-202610-22BA21E5', 750, 1, NULL, 'penjualan_mobil', '2026-10-11 14:54:35', 2350000000.00, 0.00, 2350000000.00, 'lunas', 'Checkout member ORD-202610-91F1E6DE', '2026-10-11 07:54:35', '2026-10-11 13:03:39', NULL, NULL, NULL, NULL),
(19965, 'TRX-202610-5AD33B18', 750, 1, NULL, 'servis', '2026-10-11 22:10:18', 240000.00, 0.00, 240000.00, 'belum_bayar', 'Tagihan SRV-202610-E046A133', '2026-10-11 15:10:18', '2026-10-11 15:10:18', NULL, NULL, NULL, NULL),
(19966, 'TRX-202610-5ABE0909', 750, 1, NULL, 'servis', '2026-10-11 22:10:39', 240000.00, 0.00, 240000.00, 'lunas', 'Tagihan SRV-202610-5738F37B', '2026-10-11 15:10:39', '2026-10-11 15:10:39', NULL, NULL, NULL, NULL),
(19967, 'TRX-202610-E8DF8099', 750, 1, NULL, 'servis', '2026-10-11 22:10:53', 240000.00, 0.00, 240000.00, 'lunas', 'Tagihan SRV-202610-5B49AEEA', '2026-10-11 15:10:53', '2026-10-11 15:10:53', NULL, NULL, NULL, NULL),
(20079, 'TRX-202610-A9317F0E', 750, 1, NULL, 'penjualan_mobil', '2026-10-16 10:23:31', 2500000000.00, 0.00, 2500000000.00, 'dibatalkan', 'Checkout member ORD-202610-AD27FF42', '2026-10-16 03:23:31', '2026-10-16 15:38:21', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_role` tinyint(3) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `id_role`, `username`, `email`, `password`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin506', 'admin@yza.local', '$2y$12$VMFw2TGecgtrQo2DEDakL.SzCgbjWdQoDNfnct3sAiSDrtIaKW1em', 'aktif', '2026-11-11 16:57:14', '2026-09-23 04:11:32', '2026-11-11 09:57:14'),
(2, 2, 'petugas.yza', 'petugas@yza.local', '$2y$10$3VetyvJdVs72voq2lgetNeDkoVTZY3aZBSS7VACQAKzCpzzBNW/SG', 'aktif', '2026-09-28 19:45:46', '2026-09-23 04:11:32', '2026-09-28 12:45:46'),
(3, 3, 'pelanggan.yza', 'pelanggan@yza.local', '$2y$10$z/Cac4.Pbw6jL2SOlKtBCuZZZjT0Gn80x.j1BxGSh46rGtxYkUWCO', 'aktif', '2026-09-28 19:45:47', '2026-09-23 04:11:32', '2026-09-28 12:45:47'),
(4, 3, 'pelanggan2b.test', 'pelanggan2b.test@yza.local', '$2y$10$2i5/jtijDwWh2BOS50c3Lejp0eI6oVq/wa3vcadk9f4fJ3IEHmv1y', 'aktif', '2026-09-23 12:42:17', '2026-09-23 04:42:30', '2026-09-23 05:42:17'),
(5, 2, 'dev.petugas3', 'dev.petugas3@yza.local', '$2y$10$0FsNu8Sk3TAFoLT1GrPBFe9jG8VeN7rpzktarEr/CIp2SuqyNG8zq', 'aktif', '2026-09-23 12:09:49', '2026-09-23 05:09:28', '2026-09-23 05:09:49'),
(586, 3, 'demo.active', 'demo.active@yza.local', '$2y$12$XkoeG3sBlokmzoK/pJgr1.z7U6bRGDpk1DR.JKO.RipccuhapPepO', 'aktif', '2026-10-12 17:44:28', '2026-10-04 05:33:48', '2026-10-12 10:44:28'),
(587, 3, 'demo.pending', 'demo.pending@yza.local', '$2y$12$mT.JDZEqK6QshPUM0zetle9uo4F6smXct7ZZXYYFo6Fzi18ipcwF2', 'aktif', NULL, '2026-10-04 05:34:34', '2026-10-04 05:34:34'),
(990, 3, 'lintar12345', 'lintaryanzuhara.yz@gmail.com', '$2y$12$F6YS4rZ.982DUBIOf1MuauVqCXoXWfGVwaw.xGBRxFYct5HvTnsq2', 'aktif', '2026-11-11 17:58:39', '2026-10-10 14:52:26', '2026-11-11 10:58:39'),
(991, 3, 'admin', 'admin@yza.com', '\\.SzCgbjWdQoDNfnct3sAiSDrtIaKW1em', 'aktif', '2026-10-11 21:08:57', '2026-10-11 11:55:12', '2026-10-11 15:21:01'),
(992, 4, 'superadmin506', 'superadmin506@yza.local', '$2y$12$nNuD26boGhXXI4gPhw3UceJS1qjmI8Wi7dcihlLRyC7CYfpL8SxrG', 'nonaktif', '2026-11-08 13:16:48', '2026-10-15 04:37:50', '2026-11-08 06:36:29'),
(993, 5, 'owner506', 'owner506@yza.local', '$2y$12$61YrNC9cJ8h3/TMUoy18n.4rl0zueMjR0nrBCFVpMvNAe9Yq7lIcm', 'aktif', '2026-10-27 08:14:20', '2026-10-15 04:37:50', '2026-10-27 01:14:20'),
(994, 6, 'sales506', 'sales506@yza.local', '$2y$12$aqKMLLNah9lPYBr6hV5YxOJWygxukCAHlx18qhskVYE6ilY9rt/JK', 'aktif', '2026-10-15 15:03:20', '2026-10-15 04:37:50', '2026-10-15 08:03:20'),
(995, 7, 'advisor506', 'advisor506@yza.local', '$2y$12$rAXsE.f4bkUlO0oMJqVztOr6T6NfqSu4NpflZzp80jp2/nJjdDlDW', 'aktif', '2026-10-16 00:12:18', '2026-10-15 04:37:51', '2026-10-15 17:12:18'),
(996, 8, 'mekanik506', 'mekanik506@yza.local', '$2y$12$PTFjCS4rv0c7vO6a1VyLsejtxm0tU1qyaWHgUea50BxJDQfbm3sMm', 'aktif', '2026-10-15 15:02:59', '2026-10-15 04:37:51', '2026-10-15 08:02:59'),
(997, 9, 'gudang506', 'gudang506@yza.local', '$2y$12$XENDMoiTxYohq7TvpZ3a4Oe/.4SnRUSSddHuxbbYlDDH69UmCk3sm', 'aktif', '2026-10-15 15:03:02', '2026-10-15 04:37:51', '2026-10-15 08:03:02'),
(998, 10, 'finance506', 'finance506@yza.local', '$2y$12$.2mb4ONocnK819lEskT8K.aEh7UXt7QDMVqT0r8h8Hs0TLva.O6gm', 'aktif', '2026-10-15 15:03:08', '2026-10-15 04:37:51', '2026-10-15 08:03:08'),
(999, 11, 'operasional506', 'operasional506@yza.local', '$2y$12$XKZEh6xdbdq88bJsmr5n7uVM6zY5xLUcj2rLtbvfIm3lhTZ3Eh96e', 'aktif', '2026-10-15 15:03:11', '2026-10-15 04:37:52', '2026-10-15 08:03:11'),
(1000, 12, 'qc506', 'qc506@yza.local', '$2y$12$pZCeXVZf2jevFjdTd66OdOWBS/mg8EaSOUBX5rO3kqaLci.tNKOWu', 'aktif', '2026-10-15 15:03:14', '2026-10-15 04:37:52', '2026-10-15 08:03:14'),
(1001, 3, 'lucky12345', 'luckyyanzuhara.yz@gmail.com', '$2y$12$c033AODeTVdQ3CgjFaxVPumt49zyU5rC.jF7WmA573j5yIW8y90v2', 'aktif', '2026-11-08 11:35:28', '2026-11-03 03:58:06', '2026-11-08 04:35:28'),
(1002, 3, 'fahri123', 'fahri@gmail.com', '$2y$12$jI7Xr0m.hVDWK9kA7ODNl.YhWUF3A5ta5fIv/ZEXWhMoZrHh23R3W', 'aktif', '2026-11-11 17:00:26', '2026-11-10 04:13:28', '2026-11-11 10:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_inventories`
--

CREATE TABLE `vehicle_inventories` (
  `id_vehicle_inventory` bigint(20) UNSIGNED NOT NULL,
  `inventory_key` varchar(255) NOT NULL,
  `id_mobil` bigint(20) UNSIGNED DEFAULT NULL,
  `stock_total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sold_units` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_inventories`
--

INSERT INTO `vehicle_inventories` (`id_vehicle_inventory`, `inventory_key`, `id_mobil`, `stock_total`, `sold_units`, `created_at`, `updated_at`) VALUES
(1, 'toyota|gr supra|performance|2024|supra red|automatic|bensin|baru|0', 195, 5, 1, '2026-10-11 02:39:41', '2026-10-11 13:22:10'),
(2, 'lamborghini|revuelto|v12 hybrid performance|2025|rosso mars|automatic|bensin|baru|0', 250, 8, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(3, 'toyota|supra mk4|2jz-gte turbo|1998|supra red|manual|bensin|baru|0', 204, 3, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(4, 'ferrari|sf90 stradale|assetto fiorano|2024|rosso corsa|automatic|bensin|baru|0', 251, 6, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(5, 'porsche|911 gt3 rs|4.0 performance|2024|guards red|automatic|bensin|baru|0', 252, 2, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(6, 'mercedes amg|gt black series|amg performance|2024|magno grey|automatic|bensin|baru|0', 253, 4, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(7, 'aston martin|dbs 770 ultimate|v12 ultimate|2024|minotaur green|automatic|bensin|baru|0', 254, 7, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(8, 'chevrolet|corvette z06|6.2l v8 performance|2024|torch red|automatic|bensin|baru|0', 255, 5, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(9, 'ford|mustang shelby gt500|5.2l v8 shelby|2024|grabber blue|automatic|bensin|baru|0', 256, 9, 0, '2026-10-11 02:39:41', '2026-11-08 11:01:18'),
(10, 'dodge|challenger srt hellcat redeye|6.2l supercharged v8|2023|torred|automatic|bensin|baru|0', 257, 8, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(11, 'nissan|gt-r nismo|nismo performance|2024|nismo stealth gray|automatic|bensin|baru|0', 258, 6, 0, '2026-10-11 02:39:41', '2026-10-11 02:39:41'),
(12, 'lexus|lfa|4.8l v10 performance|2012|pearl white|automatic|bensin|baru|0', 259, 6, 0, '2026-10-11 02:39:41', '2026-11-08 11:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_inventory_histories`
--

CREATE TABLE `vehicle_inventory_histories` (
  `id_vehicle_inventory_history` bigint(20) UNSIGNED NOT NULL,
  `id_vehicle_inventory` bigint(20) UNSIGNED NOT NULL,
  `jenis` varchar(40) NOT NULL,
  `jumlah_perubahan` int(11) NOT NULL DEFAULT 0,
  `stock_before` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stock_after` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sold_before` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sold_after` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `referensi_tipe` varchar(80) DEFAULT NULL,
  `referensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_inventory_histories`
--

INSERT INTO `vehicle_inventory_histories` (`id_vehicle_inventory_history`, `id_vehicle_inventory`, `jenis`, `jumlah_perubahan`, `stock_before`, `stock_after`, `sold_before`, `sold_after`, `referensi_tipe`, `referensi_id`, `id_user`, `catatan`, `created_at`) VALUES
(1, 1, 'stok_awal_operasional', 5, 0, 5, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(2, 2, 'stok_awal_operasional', 8, 0, 8, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(3, 3, 'stok_awal_operasional', 3, 0, 3, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(4, 4, 'stok_awal_operasional', 6, 0, 6, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(5, 5, 'stok_awal_operasional', 2, 0, 2, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(6, 6, 'stok_awal_operasional', 4, 0, 4, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(7, 7, 'stok_awal_operasional', 7, 0, 7, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(8, 8, 'stok_awal_operasional', 5, 0, 5, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(9, 9, 'stok_awal_operasional', 3, 0, 3, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(10, 10, 'stok_awal_operasional', 8, 0, 8, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(11, 11, 'stok_awal_operasional', 6, 0, 6, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(12, 12, 'stok_awal_operasional', 2, 0, 2, 0, 0, 'migration', NULL, NULL, 'Penetapan stok operasional awal YZA Automotive.', '2026-10-11 02:39:41'),
(13, 1, 'penjualan', 0, 5, 5, 0, 1, 'pesanan_mobil_member', 1, NULL, 'Unit agregat diserahkan kepada pelanggan.', '2026-10-11 13:22:10'),
(21, 12, 'penyesuaian', 4, 2, 6, 0, 0, 'admin', NULL, 1, 'Restock unit baru dari dealer', '2026-11-08 11:00:45'),
(22, 9, 'penyesuaian', 6, 3, 9, 0, 0, 'admin', NULL, 1, 'Restock unit baru dari dealer', '2026-11-08 11:01:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_cuci`
--
ALTER TABLE `booking_cuci`
  ADD PRIMARY KEY (`id_booking_cuci`),
  ADD UNIQUE KEY `uq_booking_cuci_kode` (`kode_booking`),
  ADD UNIQUE KEY `uq_booking_cuci_transaksi` (`id_transaksi`),
  ADD KEY `booking_cuci_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `booking_cuci_id_kendaraan_pelanggan_foreign` (`id_kendaraan_pelanggan`),
  ADD KEY `booking_cuci_id_paket_foreign` (`id_paket`),
  ADD KEY `booking_cuci_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_booking_cuci_jadwal` (`tanggal`,`jam`,`status`);

--
-- Indexes for table `booking_mobil`
--
ALTER TABLE `booking_mobil`
  ADD PRIMARY KEY (`id_booking`),
  ADD UNIQUE KEY `uq_booking_mobil_kode` (`kode_booking`),
  ADD KEY `booking_mobil_id_petugas_konfirmasi_foreign` (`id_petugas_konfirmasi`),
  ADD KEY `idx_booking_mobil_status` (`id_mobil`,`status`),
  ADD KEY `idx_booking_pelanggan` (`id_pelanggan`,`status`);

--
-- Indexes for table `booking_servis`
--
ALTER TABLE `booking_servis`
  ADD PRIMARY KEY (`id_booking_servis`),
  ADD UNIQUE KEY `uq_booking_servis_kode` (`kode_booking`),
  ADD KEY `booking_servis_id_kendaraan_pelanggan_foreign` (`id_kendaraan_pelanggan`),
  ADD KEY `booking_servis_id_jenis_servis_foreign` (`id_jenis_servis`),
  ADD KEY `idx_booking_servis_jadwal` (`tanggal`,`jam`,`status`),
  ADD KEY `idx_booking_servis_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_booking_preferred_petugas` (`id_preferred_petugas`);

--
-- Indexes for table `detail_penjualan_sparepart`
--
ALTER TABLE `detail_penjualan_sparepart`
  ADD PRIMARY KEY (`id_detail`),
  ADD UNIQUE KEY `uq_penjualan_sp_item` (`id_penjualan_sparepart`,`id_sparepart`),
  ADD KEY `idx_detail_penjualan_sp_barang` (`id_sparepart`);

--
-- Indexes for table `detail_servis`
--
ALTER TABLE `detail_servis`
  ADD PRIMARY KEY (`id_detail_servis`),
  ADD KEY `detail_servis_id_servis_index` (`id_servis`),
  ADD KEY `detail_servis_id_jenis_servis_foreign` (`id_jenis_servis`);

--
-- Indexes for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  ADD PRIMARY KEY (`id_detail_stok_masuk`),
  ADD UNIQUE KEY `uq_detail_stok_item` (`id_stok_masuk`,`id_sparepart`),
  ADD KEY `idx_detail_stok_sparepart` (`id_sparepart`);

--
-- Indexes for table `event_pembayaran_gateway`
--
ALTER TABLE `event_pembayaran_gateway`
  ADD PRIMARY KEY (`id_event_gateway`),
  ADD UNIQUE KEY `uq_gateway_event_key` (`event_key`),
  ADD KEY `event_pembayaran_gateway_id_pembayaran_gateway_foreign` (`id_pembayaran_gateway`);

--
-- Indexes for table `financial_adjustments`
--
ALTER TABLE `financial_adjustments`
  ADD PRIMARY KEY (`id_financial_adjustment`),
  ADD KEY `financial_adjustments_requested_by_foreign` (`requested_by`),
  ADD KEY `financial_adjustments_approved_by_foreign` (`approved_by`),
  ADD KEY `financial_adjustments_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `idx_adjustment_payment_status` (`id_pembayaran`,`status`),
  ADD KEY `idx_adjustment_transaction_status` (`id_transaksi`,`status`);

--
-- Indexes for table `financial_adjustment_events`
--
ALTER TABLE `financial_adjustment_events`
  ADD PRIMARY KEY (`id_financial_adjustment_event`),
  ADD KEY `financial_adjustment_events_actor_petugas_id_foreign` (`actor_petugas_id`),
  ADD KEY `idx_adjustment_event_history` (`id_financial_adjustment`,`created_at`);

--
-- Indexes for table `foto_mobil`
--
ALTER TABLE `foto_mobil`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_foto_mobil` (`id_mobil`,`urutan`);

--
-- Indexes for table `inspeksi_mobil`
--
ALTER TABLE `inspeksi_mobil`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `idx_inspeksi_pengajuan` (`id_pengajuan`,`tanggal_inspeksi`),
  ADD KEY `inspeksi_mobil_id_petugas_foreign` (`id_petugas`);

--
-- Indexes for table `jenis_servis`
--
ALTER TABLE `jenis_servis`
  ADD PRIMARY KEY (`id_jenis_servis`),
  ADD UNIQUE KEY `uq_jenis_servis_nama` (`nama_servis`);

--
-- Indexes for table `kategori_sparepart`
--
ALTER TABLE `kategori_sparepart`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `uq_kategori_sparepart_nama` (`nama_kategori`);

--
-- Indexes for table `kendaraan_pelanggan`
--
ALTER TABLE `kendaraan_pelanggan`
  ADD PRIMARY KEY (`id_kendaraan_pelanggan`),
  ADD UNIQUE KEY `uq_kendaraan_source_penjualan` (`id_penjualan`),
  ADD KEY `idx_kendaraan_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_kendaraan_nomor_polisi` (`nomor_polisi`),
  ADD KEY `fk_kendaraan_source_serah_terima` (`id_serah_terima`),
  ADD KEY `idx_kendaraan_source_pesanan` (`id_pesanan`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id_membership`),
  ADD UNIQUE KEY `uq_membership_customer` (`id_pelanggan`),
  ADD UNIQUE KEY `uq_membership_member_id` (`member_id`),
  ADD UNIQUE KEY `uq_membership_gateway` (`gateway_payment_id`),
  ADD KEY `idx_membership_status_date` (`status`,`registered_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobil`
--
ALTER TABLE `mobil`
  ADD PRIMARY KEY (`id_mobil`),
  ADD UNIQUE KEY `uq_mobil_kode` (`kode_mobil`),
  ADD UNIQUE KEY `uq_mobil_nomor_rangka` (`nomor_rangka`),
  ADD UNIQUE KEY `uq_mobil_nomor_mesin` (`nomor_mesin`),
  ADD UNIQUE KEY `uq_mobil_showcase_slug` (`showcase_slug`),
  ADD KEY `idx_mobil_filter` (`status`,`kondisi`,`merk`,`tahun`),
  ADD KEY `idx_mobil_harga` (`harga_jual`),
  ADD KEY `idx_mobil_limited_edition` (`is_limited_edition`);

--
-- Indexes for table `mobil_galeri`
--
ALTER TABLE `mobil_galeri`
  ADD PRIMARY KEY (`id_galeri`),
  ADD UNIQUE KEY `uq_mobil_galeri_sudut` (`id_warna`,`sudut`);

--
-- Indexes for table `mobil_warna`
--
ALTER TABLE `mobil_warna`
  ADD PRIMARY KEY (`id_warna`),
  ADD UNIQUE KEY `uq_mobil_warna_slug` (`id_mobil`,`slug`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `notifikasi_id_user_is_read_created_at_index` (`id_user`,`is_read`,`created_at`),
  ADD KEY `notifikasi_referensi_tipe_referensi_id_index` (`referensi_tipe`,`referensi_id`);

--
-- Indexes for table `paket_cuci`
--
ALTER TABLE `paket_cuci`
  ADD PRIMARY KEY (`id_paket`),
  ADD UNIQUE KEY `uq_paket_cuci_nama` (`nama_paket`);

--
-- Indexes for table `pdi_pesanan_mobil`
--
ALTER TABLE `pdi_pesanan_mobil`
  ADD PRIMARY KEY (`id_pdi`),
  ADD UNIQUE KEY `pdi_pesanan_mobil_id_pesanan_unique` (`id_pesanan`),
  ADD KEY `pdi_pesanan_mobil_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_pdi_status_completed` (`status`,`completed_at`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `uq_pelanggan_kode` (`kode_pelanggan`),
  ADD UNIQUE KEY `uq_pelanggan_user` (`id_user`),
  ADD KEY `idx_pelanggan_nama` (`nama_lengkap`),
  ADD KEY `idx_pelanggan_status` (`status`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `uq_pembayaran_kode` (`kode_pembayaran`),
  ADD KEY `idx_pembayaran_transaksi` (`id_transaksi`,`status`),
  ADD KEY `idx_pembayaran_tanggal` (`tanggal`),
  ADD KEY `pembayaran_verified_by_foreign` (`verified_by`);

--
-- Indexes for table `pembayaran_gateway`
--
ALTER TABLE `pembayaran_gateway`
  ADD PRIMARY KEY (`id_pembayaran_gateway`),
  ADD UNIQUE KEY `uq_gateway_order` (`order_id`),
  ADD UNIQUE KEY `uq_gateway_payment` (`id_pembayaran`),
  ADD KEY `pembayaran_gateway_initiated_by_foreign` (`initiated_by`),
  ADD KEY `idx_gateway_transaction_status` (`id_transaksi`,`status_provider`),
  ADD KEY `idx_gateway_membership_status` (`id_membership`,`status_provider`);

--
-- Indexes for table `pembelian_mobil_bekas`
--
ALTER TABLE `pembelian_mobil_bekas`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD UNIQUE KEY `uq_pembelian_bekas_kode` (`kode_pembelian`),
  ADD UNIQUE KEY `uq_pembelian_bekas_transaksi` (`id_transaksi`),
  ADD KEY `idx_pembelian_bekas_pengajuan_status` (`id_pengajuan`,`status`),
  ADD KEY `idx_pembelian_bekas_tanggal` (`tanggal_pembelian`),
  ADD KEY `pembelian_mobil_bekas_id_inspeksi_foreign` (`id_inspeksi`),
  ADD KEY `pembelian_mobil_bekas_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `pembelian_mobil_bekas_id_petugas_foreign` (`id_petugas`),
  ADD KEY `pembelian_mobil_bekas_id_mobil_foreign` (`id_mobil`);

--
-- Indexes for table `pengajuan_mobil`
--
ALTER TABLE `pengajuan_mobil`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD UNIQUE KEY `uq_pengajuan_mobil_kode` (`kode_pengajuan`),
  ADD KEY `idx_pengajuan_status` (`status`,`created_at`),
  ADD KEY `idx_pengajuan_pelanggan` (`id_pelanggan`),
  ADD KEY `pengajuan_mobil_id_kendaraan_pelanggan_foreign` (`id_kendaraan_pelanggan`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id_pengaturan`),
  ADD UNIQUE KEY `uq_pengaturan_kunci` (`kunci_pengaturan`);

--
-- Indexes for table `penjualan_mobil`
--
ALTER TABLE `penjualan_mobil`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `uq_penjualan_mobil_kode` (`kode_penjualan`),
  ADD UNIQUE KEY `uq_penjualan_mobil_transaksi` (`id_transaksi`),
  ADD KEY `idx_penjualan_mobil_unit` (`id_mobil`,`status`),
  ADD KEY `idx_penjualan_mobil_tanggal` (`tanggal_penjualan`),
  ADD KEY `penjualan_mobil_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `penjualan_mobil_id_petugas_foreign` (`id_petugas`),
  ADD KEY `penjualan_mobil_id_booking_foreign` (`id_booking`),
  ADD KEY `idx_penjualan_mobil_sales_status` (`id_sales`,`status`);

--
-- Indexes for table `penjualan_sparepart`
--
ALTER TABLE `penjualan_sparepart`
  ADD PRIMARY KEY (`id_penjualan_sparepart`),
  ADD UNIQUE KEY `uq_penjualan_sp_transaksi` (`id_transaksi`),
  ADD UNIQUE KEY `uq_penjualan_sp_kode` (`kode_penjualan`),
  ADD KEY `idx_penjualan_sp_tanggal` (`tanggal`),
  ADD KEY `penjualan_sparepart_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `penjualan_sparepart_id_petugas_foreign` (`id_petugas`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id_permission`),
  ADD UNIQUE KEY `permissions_kode_unique` (`kode`);

--
-- Indexes for table `pesanan_mobil_member`
--
ALTER TABLE `pesanan_mobil_member`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `pesanan_mobil_member_nomor_pesanan_unique` (`nomor_pesanan`),
  ADD UNIQUE KEY `pesanan_mobil_member_id_transaksi_unique` (`id_transaksi`),
  ADD UNIQUE KEY `pesanan_mobil_member_id_penjualan_unique` (`id_penjualan`),
  ADD KEY `pesanan_mobil_member_id_pelanggan_created_at_index` (`id_pelanggan`,`created_at`),
  ADD KEY `pesanan_mobil_member_id_mobil_status_index` (`id_mobil`,`status`),
  ADD KEY `pesanan_mobil_member_id_petugas_keputusan_foreign` (`id_petugas_keputusan`),
  ADD KEY `pesanan_mobil_member_status_index` (`status`),
  ADD KEY `idx_member_pickup_status` (`status_pengambilan`,`jadwal_pengambilan`),
  ADD KEY `idx_pesanan_mobil_sales_status` (`id_sales`,`status`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `uq_petugas_user` (`id_user`),
  ADD UNIQUE KEY `uq_petugas_kode` (`kode_petugas`),
  ADD KEY `idx_petugas_status` (`status`);

--
-- Indexes for table `poin_member`
--
ALTER TABLE `poin_member`
  ADD PRIMARY KEY (`id_poin_member`),
  ADD UNIQUE KEY `uq_poin_member_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `pre_order_mobil_member`
--
ALTER TABLE `pre_order_mobil_member`
  ADD PRIMARY KEY (`id_pre_order`),
  ADD UNIQUE KEY `uq_preorder_customer_product` (`id_pelanggan`,`product_key`),
  ADD KEY `pre_order_mobil_member_id_mobil_foreign` (`id_mobil`),
  ADD KEY `idx_preorder_product_status` (`product_key`,`status`),
  ADD KEY `idx_preorder_customer_status` (`id_pelanggan`,`status`);

--
-- Indexes for table `promo_member`
--
ALTER TABLE `promo_member`
  ADD PRIMARY KEY (`id_promo`),
  ADD UNIQUE KEY `uq_promo_member_kode` (`kode_promo`),
  ADD KEY `fk_promo_member_creator` (`created_by`),
  ADD KEY `idx_promo_member_visibility` (`status`,`mulai_at`,`selesai_at`),
  ADD KEY `promo_member_approved_spk_simulation_id_index` (`approved_spk_simulation_id`);

--
-- Indexes for table `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  ADD PRIMARY KEY (`id_redemption`),
  ADD UNIQUE KEY `uq_promo_transaction` (`id_promo`,`id_transaksi`),
  ADD KEY `promo_redemptions_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `promo_redemptions_id_transaksi_foreign` (`id_transaksi`),
  ADD KEY `idx_promo_customer` (`id_promo`,`id_pelanggan`),
  ADD KEY `idx_redemptions_promo_status` (`id_promo`,`status`);

--
-- Indexes for table `promo_simulations`
--
ALTER TABLE `promo_simulations`
  ADD PRIMARY KEY (`id_promo_simulation`),
  ADD UNIQUE KEY `promo_simulations_simulation_id_unique` (`simulation_id`),
  ADD KEY `promo_simulations_scope_status_index` (`scope`,`status`);

--
-- Indexes for table `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD PRIMARY KEY (`id_riwayat_poin`),
  ADD UNIQUE KEY `uq_riwayat_poin_idempotency` (`idempotency_key`),
  ADD KEY `fk_riwayat_poin_transaksi` (`id_transaksi`),
  ADD KEY `idx_riwayat_poin_member_date` (`id_pelanggan`,`created_at`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `riwayat_stok_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_riwayat_stok_barang` (`id_sparepart`,`tanggal`),
  ADD KEY `idx_riwayat_stok_jenis` (`jenis_transaksi`,`tanggal`),
  ADD KEY `idx_riwayat_referensi` (`referensi_tipe`,`referensi_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `uq_roles_nama` (`nama_role`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id_role`,`id_permission`),
  ADD KEY `role_permissions_id_permission_foreign` (`id_permission`);

--
-- Indexes for table `sales_assignment_audits`
--
ALTER TABLE `sales_assignment_audits`
  ADD PRIMARY KEY (`id_sales_assignment_audit`),
  ADD KEY `sales_assignment_audits_previous_sales_id_foreign` (`previous_sales_id`),
  ADD KEY `sales_assignment_audits_new_sales_id_foreign` (`new_sales_id`),
  ADD KEY `sales_assignment_audits_actor_petugas_id_foreign` (`actor_petugas_id`),
  ADD KEY `sales_assignment_audits_entity_type_entity_id_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `sales_awards`
--
ALTER TABLE `sales_awards`
  ADD PRIMARY KEY (`id_sales_award`),
  ADD UNIQUE KEY `sales_awards_award_year_unique` (`award_year`),
  ADD KEY `sales_awards_finalized_by_foreign` (`finalized_by`);

--
-- Indexes for table `sales_award_audits`
--
ALTER TABLE `sales_award_audits`
  ADD PRIMARY KEY (`id_sales_award_audit`),
  ADD KEY `sales_award_audits_actor_petugas_id_foreign` (`actor_petugas_id`),
  ADD KEY `sales_award_audits_id_sales_award_action_index` (`id_sales_award`,`action`);

--
-- Indexes for table `sales_award_results`
--
ALTER TABLE `sales_award_results`
  ADD PRIMARY KEY (`id_sales_award_result`),
  ADD UNIQUE KEY `sales_award_results_id_sales_award_id_sales_unique` (`id_sales_award`,`id_sales`),
  ADD KEY `sales_award_results_id_sales_foreign` (`id_sales`),
  ADD KEY `sales_award_results_id_sales_award_ranking_index` (`id_sales_award`,`ranking`);

--
-- Indexes for table `sales_leads`
--
ALTER TABLE `sales_leads`
  ADD PRIMARY KEY (`id_sales_lead`),
  ADD KEY `sales_leads_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `sales_leads_id_mobil_foreign` (`id_mobil`),
  ADD KEY `sales_leads_id_sales_status_index` (`id_sales`,`status`),
  ADD KEY `sales_leads_status_follow_up_at_index` (`status`,`follow_up_at`);

--
-- Indexes for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  ADD PRIMARY KEY (`id_sales_lead_activity`),
  ADD KEY `sales_lead_activities_id_petugas_foreign` (`id_petugas`),
  ADD KEY `sales_lead_activities_id_sales_lead_created_at_index` (`id_sales_lead`,`created_at`);

--
-- Indexes for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD PRIMARY KEY (`id_sales_target`),
  ADD UNIQUE KEY `uq_sales_target_period` (`id_sales`,`periode_mulai`,`periode_selesai`),
  ADD KEY `sales_targets_periode_mulai_periode_selesai_status_index` (`periode_mulai`,`periode_selesai`,`status`);

--
-- Indexes for table `serah_terima_pesanan_mobil`
--
ALTER TABLE `serah_terima_pesanan_mobil`
  ADD PRIMARY KEY (`id_serah_terima`),
  ADD UNIQUE KEY `serah_terima_pesanan_mobil_id_pesanan_unique` (`id_pesanan`),
  ADD KEY `serah_terima_pesanan_mobil_id_petugas_foreign` (`id_petugas`);

--
-- Indexes for table `service_approval_requests`
--
ALTER TABLE `service_approval_requests`
  ADD PRIMARY KEY (`id_service_approval_request`),
  ADD KEY `service_approval_requests_id_servis_status_index` (`id_servis`,`status`);

--
-- Indexes for table `service_approval_request_items`
--
ALTER TABLE `service_approval_request_items`
  ADD PRIMARY KEY (`id_service_approval_request_item`),
  ADD KEY `idx_approval_items_type` (`approval_request_id`,`item_type`);

--
-- Indexes for table `service_blackout_dates`
--
ALTER TABLE `service_blackout_dates`
  ADD PRIMARY KEY (`id_service_blackout_date`),
  ADD UNIQUE KEY `uq_service_blackout_date` (`date`);

--
-- Indexes for table `service_execution_events`
--
ALTER TABLE `service_execution_events`
  ADD PRIMARY KEY (`id_service_execution_event`),
  ADD KEY `service_execution_events_id_servis_event_key_index` (`id_servis`,`event_key`);

--
-- Indexes for table `service_findings`
--
ALTER TABLE `service_findings`
  ADD PRIMARY KEY (`id_service_finding`),
  ADD KEY `service_findings_id_servis_status_index` (`id_servis`,`status`);

--
-- Indexes for table `service_operating_hours`
--
ALTER TABLE `service_operating_hours`
  ADD PRIMARY KEY (`id_service_operating_hour`),
  ADD UNIQUE KEY `uq_service_operating_day` (`day_of_week`);

--
-- Indexes for table `service_quality_controls`
--
ALTER TABLE `service_quality_controls`
  ADD PRIMARY KEY (`id_service_quality_control`),
  ADD UNIQUE KEY `service_quality_controls_id_servis_unique` (`id_servis`);

--
-- Indexes for table `service_quality_control_items`
--
ALTER TABLE `service_quality_control_items`
  ADD PRIMARY KEY (`id_service_quality_control_item`),
  ADD UNIQUE KEY `uq_service_qc_item` (`quality_control_id`,`item_key`);

--
-- Indexes for table `service_specializations`
--
ALTER TABLE `service_specializations`
  ADD PRIMARY KEY (`id_service_specialization`),
  ADD UNIQUE KEY `uq_service_specialization_name` (`name`);

--
-- Indexes for table `service_staff_profiles`
--
ALTER TABLE `service_staff_profiles`
  ADD PRIMARY KEY (`id_service_staff_profile`),
  ADD UNIQUE KEY `uq_service_staff_petugas` (`id_petugas`);

--
-- Indexes for table `service_staff_specializations`
--
ALTER TABLE `service_staff_specializations`
  ADD PRIMARY KEY (`id_service_staff_profile`,`id_service_specialization`),
  ADD KEY `service_staff_specializations_id_service_specialization_foreign` (`id_service_specialization`);

--
-- Indexes for table `service_work_order_assignments`
--
ALTER TABLE `service_work_order_assignments`
  ADD PRIMARY KEY (`id_service_work_order_assignment`),
  ADD KEY `service_work_order_assignments_id_servis_unassigned_at_index` (`id_servis`,`unassigned_at`),
  ADD KEY `service_work_order_assignments_id_petugas_unassigned_at_index` (`id_petugas`,`unassigned_at`);

--
-- Indexes for table `servis`
--
ALTER TABLE `servis`
  ADD PRIMARY KEY (`id_servis`),
  ADD UNIQUE KEY `uq_servis_kode` (`kode_servis`),
  ADD UNIQUE KEY `uq_servis_transaksi` (`id_transaksi`),
  ADD UNIQUE KEY `uq_servis_booking` (`id_booking_servis`),
  ADD KEY `servis_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `servis_id_kendaraan_pelanggan_foreign` (`id_kendaraan_pelanggan`),
  ADD KEY `servis_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_servis_service_advisor` (`id_service_advisor`);

--
-- Indexes for table `servis_sparepart`
--
ALTER TABLE `servis_sparepart`
  ADD PRIMARY KEY (`id_servis_sparepart`),
  ADD UNIQUE KEY `uq_servis_sparepart_item` (`id_servis`,`id_sparepart`),
  ADD KEY `idx_servis_sparepart_barang` (`id_sparepart`);

--
-- Indexes for table `sparepart`
--
ALTER TABLE `sparepart`
  ADD PRIMARY KEY (`id_sparepart`),
  ADD UNIQUE KEY `uq_sparepart_kode` (`kode_sparepart`),
  ADD KEY `idx_sparepart_nama` (`nama_sparepart`),
  ADD KEY `idx_sparepart_stok` (`stok`,`stok_minimum`),
  ADD KEY `idx_sparepart_kategori` (`id_kategori`),
  ADD KEY `sparepart_id_supplier_foreign` (`id_supplier`);

--
-- Indexes for table `sparepart_fitments`
--
ALTER TABLE `sparepart_fitments`
  ADD PRIMARY KEY (`id_sparepart_fitment`),
  ADD KEY `sparepart_fitments_verified_by_foreign` (`verified_by`),
  ADD KEY `sparepart_fitments_id_sparepart_status_verifikasi_index` (`id_sparepart`,`status_verifikasi`),
  ADD KEY `sparepart_fitments_model_kendaraan_kode_mesin_index` (`model_kendaraan`,`kode_mesin`);

--
-- Indexes for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD PRIMARY KEY (`id_stok_masuk`),
  ADD UNIQUE KEY `uq_stok_masuk_kode` (`kode_stok_masuk`),
  ADD KEY `stok_masuk_id_supplier_foreign` (`id_supplier`),
  ADD KEY `stok_masuk_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_stok_masuk_tanggal` (`tanggal`),
  ADD KEY `idx_stok_masuk_status` (`status`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`),
  ADD UNIQUE KEY `uq_supplier_kode` (`kode_supplier`),
  ADD KEY `idx_supplier_nama` (`nama_supplier`);

--
-- Indexes for table `technician_time_off`
--
ALTER TABLE `technician_time_off`
  ADD PRIMARY KEY (`id_technician_time_off`),
  ADD UNIQUE KEY `uq_technician_time_off` (`id_petugas`,`date`);

--
-- Indexes for table `technician_working_hours`
--
ALTER TABLE `technician_working_hours`
  ADD PRIMARY KEY (`id_technician_working_hour`),
  ADD UNIQUE KEY `uq_technician_weekday` (`id_petugas`,`day_of_week`);

--
-- Indexes for table `test_drive`
--
ALTER TABLE `test_drive`
  ADD PRIMARY KEY (`id_test_drive`),
  ADD UNIQUE KEY `uq_test_drive_kode` (`kode_test_drive`),
  ADD KEY `test_drive_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `test_drive_id_petugas_foreign` (`id_petugas`),
  ADD KEY `idx_test_drive_jadwal` (`tanggal`,`jam`,`status`),
  ADD KEY `idx_test_drive_mobil` (`id_mobil`,`status`),
  ADD KEY `idx_test_drive_sales_status` (`id_sales`,`status`);

--
-- Indexes for table `trade_in`
--
ALTER TABLE `trade_in`
  ADD PRIMARY KEY (`id_trade_in`),
  ADD UNIQUE KEY `uq_trade_in_kode` (`kode_trade_in`),
  ADD UNIQUE KEY `uq_trade_in_transaksi` (`id_transaksi`),
  ADD KEY `idx_trade_in_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_trade_in_tanggal` (`tanggal`),
  ADD KEY `trade_in_id_pengajuan_foreign` (`id_pengajuan`),
  ADD KEY `trade_in_id_inspeksi_foreign` (`id_inspeksi`),
  ADD KEY `trade_in_id_mobil_lama_masuk_foreign` (`id_mobil_lama_masuk`),
  ADD KEY `trade_in_id_mobil_dibeli_foreign` (`id_mobil_dibeli`),
  ADD KEY `trade_in_id_petugas_foreign` (`id_petugas`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `uq_transaksi_kode` (`kode_transaksi`),
  ADD KEY `idx_transaksi_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_transaksi_petugas` (`id_petugas`),
  ADD KEY `idx_transaksi_jenis_tanggal` (`jenis_transaksi`,`tanggal`),
  ADD KEY `idx_transaksi_status` (`status`),
  ADD KEY `idx_transaksi_promo` (`id_promo`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role_status` (`id_role`,`status`);

--
-- Indexes for table `vehicle_inventories`
--
ALTER TABLE `vehicle_inventories`
  ADD PRIMARY KEY (`id_vehicle_inventory`),
  ADD UNIQUE KEY `uq_vehicle_inventory_key` (`inventory_key`),
  ADD KEY `idx_vehicle_inventory_mobil` (`id_mobil`);

--
-- Indexes for table `vehicle_inventory_histories`
--
ALTER TABLE `vehicle_inventory_histories`
  ADD PRIMARY KEY (`id_vehicle_inventory_history`),
  ADD KEY `idx_vehicle_inventory_history` (`id_vehicle_inventory`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_cuci`
--
ALTER TABLE `booking_cuci`
  MODIFY `id_booking_cuci` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3898;

--
-- AUTO_INCREMENT for table `booking_mobil`
--
ALTER TABLE `booking_mobil`
  MODIFY `id_booking` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2441;

--
-- AUTO_INCREMENT for table `booking_servis`
--
ALTER TABLE `booking_servis`
  MODIFY `id_booking_servis` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16288;

--
-- AUTO_INCREMENT for table `detail_penjualan_sparepart`
--
ALTER TABLE `detail_penjualan_sparepart`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3563;

--
-- AUTO_INCREMENT for table `detail_servis`
--
ALTER TABLE `detail_servis`
  MODIFY `id_detail_servis` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=887;

--
-- AUTO_INCREMENT for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  MODIFY `id_detail_stok_masuk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `event_pembayaran_gateway`
--
ALTER TABLE `event_pembayaran_gateway`
  MODIFY `id_event_gateway` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=609;

--
-- AUTO_INCREMENT for table `financial_adjustments`
--
ALTER TABLE `financial_adjustments`
  MODIFY `id_financial_adjustment` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `financial_adjustment_events`
--
ALTER TABLE `financial_adjustment_events`
  MODIFY `id_financial_adjustment_event` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `foto_mobil`
--
ALTER TABLE `foto_mobil`
  MODIFY `id_foto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `inspeksi_mobil`
--
ALTER TABLE `inspeksi_mobil`
  MODIFY `id_inspeksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3271;

--
-- AUTO_INCREMENT for table `jenis_servis`
--
ALTER TABLE `jenis_servis`
  MODIFY `id_jenis_servis` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `kategori_sparepart`
--
ALTER TABLE `kategori_sparepart`
  MODIFY `id_kategori` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `kendaraan_pelanggan`
--
ALTER TABLE `kendaraan_pelanggan`
  MODIFY `id_kendaraan_pelanggan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24519;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id_membership` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `mobil`
--
ALTER TABLE `mobil`
  MODIFY `id_mobil` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=441;

--
-- AUTO_INCREMENT for table `mobil_galeri`
--
ALTER TABLE `mobil_galeri`
  MODIFY `id_galeri` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=413;

--
-- AUTO_INCREMENT for table `mobil_warna`
--
ALTER TABLE `mobil_warna`
  MODIFY `id_warna` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6250;

--
-- AUTO_INCREMENT for table `paket_cuci`
--
ALTER TABLE `paket_cuci`
  MODIFY `id_paket` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT for table `pdi_pesanan_mobil`
--
ALTER TABLE `pdi_pesanan_mobil`
  MODIFY `id_pdi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=754;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11362;

--
-- AUTO_INCREMENT for table `pembayaran_gateway`
--
ALTER TABLE `pembayaran_gateway`
  MODIFY `id_pembayaran_gateway` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1020;

--
-- AUTO_INCREMENT for table `pembelian_mobil_bekas`
--
ALTER TABLE `pembelian_mobil_bekas`
  MODIFY `id_pembelian` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1220;

--
-- AUTO_INCREMENT for table `pengajuan_mobil`
--
ALTER TABLE `pengajuan_mobil`
  MODIFY `id_pengajuan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3679;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id_pengaturan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `penjualan_mobil`
--
ALTER TABLE `penjualan_mobil`
  MODIFY `id_penjualan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2939;

--
-- AUTO_INCREMENT for table `penjualan_sparepart`
--
ALTER TABLE `penjualan_sparepart`
  MODIFY `id_penjualan_sparepart` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4201;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pesanan_mobil_member`
--
ALTER TABLE `pesanan_mobil_member`
  MODIFY `id_pesanan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `poin_member`
--
ALTER TABLE `poin_member`
  MODIFY `id_poin_member` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3942;

--
-- AUTO_INCREMENT for table `pre_order_mobil_member`
--
ALTER TABLE `pre_order_mobil_member`
  MODIFY `id_pre_order` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promo_member`
--
ALTER TABLE `promo_member`
  MODIFY `id_promo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1252;

--
-- AUTO_INCREMENT for table `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  MODIFY `id_redemption` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_simulations`
--
ALTER TABLE `promo_simulations`
  MODIFY `id_promo_simulation` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  MODIFY `id_riwayat_poin` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1798;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id_riwayat` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4724;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales_assignment_audits`
--
ALTER TABLE `sales_assignment_audits`
  MODIFY `id_sales_assignment_audit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_awards`
--
ALTER TABLE `sales_awards`
  MODIFY `id_sales_award` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_award_audits`
--
ALTER TABLE `sales_award_audits`
  MODIFY `id_sales_award_audit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_award_results`
--
ALTER TABLE `sales_award_results`
  MODIFY `id_sales_award_result` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_leads`
--
ALTER TABLE `sales_leads`
  MODIFY `id_sales_lead` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  MODIFY `id_sales_lead_activity` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_targets`
--
ALTER TABLE `sales_targets`
  MODIFY `id_sales_target` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `serah_terima_pesanan_mobil`
--
ALTER TABLE `serah_terima_pesanan_mobil`
  MODIFY `id_serah_terima` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_approval_requests`
--
ALTER TABLE `service_approval_requests`
  MODIFY `id_service_approval_request` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `service_approval_request_items`
--
ALTER TABLE `service_approval_request_items`
  MODIFY `id_service_approval_request_item` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service_blackout_dates`
--
ALTER TABLE `service_blackout_dates`
  MODIFY `id_service_blackout_date` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_execution_events`
--
ALTER TABLE `service_execution_events`
  MODIFY `id_service_execution_event` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `service_findings`
--
ALTER TABLE `service_findings`
  MODIFY `id_service_finding` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_operating_hours`
--
ALTER TABLE `service_operating_hours`
  MODIFY `id_service_operating_hour` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_quality_controls`
--
ALTER TABLE `service_quality_controls`
  MODIFY `id_service_quality_control` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_quality_control_items`
--
ALTER TABLE `service_quality_control_items`
  MODIFY `id_service_quality_control_item` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `service_specializations`
--
ALTER TABLE `service_specializations`
  MODIFY `id_service_specialization` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_staff_profiles`
--
ALTER TABLE `service_staff_profiles`
  MODIFY `id_service_staff_profile` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_work_order_assignments`
--
ALTER TABLE `service_work_order_assignments`
  MODIFY `id_service_work_order_assignment` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `servis`
--
ALTER TABLE `servis`
  MODIFY `id_servis` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13909;

--
-- AUTO_INCREMENT for table `servis_sparepart`
--
ALTER TABLE `servis_sparepart`
  MODIFY `id_servis_sparepart` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3081;

--
-- AUTO_INCREMENT for table `sparepart`
--
ALTER TABLE `sparepart`
  MODIFY `id_sparepart` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;

--
-- AUTO_INCREMENT for table `sparepart_fitments`
--
ALTER TABLE `sparepart_fitments`
  MODIFY `id_sparepart_fitment` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  MODIFY `id_stok_masuk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=458;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232;

--
-- AUTO_INCREMENT for table `technician_time_off`
--
ALTER TABLE `technician_time_off`
  MODIFY `id_technician_time_off` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `technician_working_hours`
--
ALTER TABLE `technician_working_hours`
  MODIFY `id_technician_working_hour` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `test_drive`
--
ALTER TABLE `test_drive`
  MODIFY `id_test_drive` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1563;

--
-- AUTO_INCREMENT for table `trade_in`
--
ALTER TABLE `trade_in`
  MODIFY `id_trade_in` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1642;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20080;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;

--
-- AUTO_INCREMENT for table `vehicle_inventories`
--
ALTER TABLE `vehicle_inventories`
  MODIFY `id_vehicle_inventory` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vehicle_inventory_histories`
--
ALTER TABLE `vehicle_inventory_histories`
  MODIFY `id_vehicle_inventory_history` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_cuci`
--
ALTER TABLE `booking_cuci`
  ADD CONSTRAINT `booking_cuci_id_kendaraan_pelanggan_foreign` FOREIGN KEY (`id_kendaraan_pelanggan`) REFERENCES `kendaraan_pelanggan` (`id_kendaraan_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_cuci_id_paket_foreign` FOREIGN KEY (`id_paket`) REFERENCES `paket_cuci` (`id_paket`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_cuci_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_cuci_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `booking_mobil`
--
ALTER TABLE `booking_mobil`
  ADD CONSTRAINT `booking_mobil_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_mobil_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_mobil_id_petugas_konfirmasi_foreign` FOREIGN KEY (`id_petugas_konfirmasi`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `booking_servis`
--
ALTER TABLE `booking_servis`
  ADD CONSTRAINT `booking_servis_id_jenis_servis_foreign` FOREIGN KEY (`id_jenis_servis`) REFERENCES `jenis_servis` (`id_jenis_servis`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_servis_id_kendaraan_pelanggan_foreign` FOREIGN KEY (`id_kendaraan_pelanggan`) REFERENCES `kendaraan_pelanggan` (`id_kendaraan_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_servis_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_preferred_petugas` FOREIGN KEY (`id_preferred_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `detail_penjualan_sparepart`
--
ALTER TABLE `detail_penjualan_sparepart`
  ADD CONSTRAINT `detail_penjualan_sparepart_id_penjualan_sparepart_foreign` FOREIGN KEY (`id_penjualan_sparepart`) REFERENCES `penjualan_sparepart` (`id_penjualan_sparepart`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_penjualan_sparepart_id_sparepart_foreign` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`) ON UPDATE CASCADE;

--
-- Constraints for table `detail_servis`
--
ALTER TABLE `detail_servis`
  ADD CONSTRAINT `detail_servis_id_jenis_servis_foreign` FOREIGN KEY (`id_jenis_servis`) REFERENCES `jenis_servis` (`id_jenis_servis`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_servis_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  ADD CONSTRAINT `detail_stok_masuk_id_sparepart_foreign` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`) ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_stok_masuk_id_stok_masuk_foreign` FOREIGN KEY (`id_stok_masuk`) REFERENCES `stok_masuk` (`id_stok_masuk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_pembayaran_gateway`
--
ALTER TABLE `event_pembayaran_gateway`
  ADD CONSTRAINT `event_pembayaran_gateway_id_pembayaran_gateway_foreign` FOREIGN KEY (`id_pembayaran_gateway`) REFERENCES `pembayaran_gateway` (`id_pembayaran_gateway`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `financial_adjustments`
--
ALTER TABLE `financial_adjustments`
  ADD CONSTRAINT `financial_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_adjustments_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_adjustments_id_pembayaran_foreign` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`),
  ADD CONSTRAINT `financial_adjustments_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `financial_adjustments_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `petugas` (`id_petugas`);

--
-- Constraints for table `financial_adjustment_events`
--
ALTER TABLE `financial_adjustment_events`
  ADD CONSTRAINT `financial_adjustment_events_actor_petugas_id_foreign` FOREIGN KEY (`actor_petugas_id`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_adjustment_events_id_financial_adjustment_foreign` FOREIGN KEY (`id_financial_adjustment`) REFERENCES `financial_adjustments` (`id_financial_adjustment`) ON DELETE CASCADE;

--
-- Constraints for table `foto_mobil`
--
ALTER TABLE `foto_mobil`
  ADD CONSTRAINT `fk_foto_mobil` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inspeksi_mobil`
--
ALTER TABLE `inspeksi_mobil`
  ADD CONSTRAINT `inspeksi_mobil_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_mobil` (`id_pengajuan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inspeksi_mobil_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE;

--
-- Constraints for table `kendaraan_pelanggan`
--
ALTER TABLE `kendaraan_pelanggan`
  ADD CONSTRAINT `fk_kendaraan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kendaraan_source_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan_mobil` (`id_penjualan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kendaraan_source_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan_mobil_member` (`id_pesanan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kendaraan_source_serah_terima` FOREIGN KEY (`id_serah_terima`) REFERENCES `serah_terima_pesanan_mobil` (`id_serah_terima`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_gateway_payment_id_foreign` FOREIGN KEY (`gateway_payment_id`) REFERENCES `pembayaran_gateway` (`id_pembayaran_gateway`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `memberships_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mobil_galeri`
--
ALTER TABLE `mobil_galeri`
  ADD CONSTRAINT `mobil_galeri_id_warna_foreign` FOREIGN KEY (`id_warna`) REFERENCES `mobil_warna` (`id_warna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mobil_warna`
--
ALTER TABLE `mobil_warna`
  ADD CONSTRAINT `mobil_warna_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pdi_pesanan_mobil`
--
ALTER TABLE `pdi_pesanan_mobil`
  ADD CONSTRAINT `pdi_pesanan_mobil_id_pesanan_foreign` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan_mobil_member` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pdi_pesanan_mobil_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `fk_pelanggan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran_gateway`
--
ALTER TABLE `pembayaran_gateway`
  ADD CONSTRAINT `pembayaran_gateway_id_membership_foreign` FOREIGN KEY (`id_membership`) REFERENCES `memberships` (`id_membership`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_gateway_id_pembayaran_foreign` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_gateway_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_gateway_initiated_by_foreign` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pembelian_mobil_bekas`
--
ALTER TABLE `pembelian_mobil_bekas`
  ADD CONSTRAINT `pembelian_mobil_bekas_id_inspeksi_foreign` FOREIGN KEY (`id_inspeksi`) REFERENCES `inspeksi_mobil` (`id_inspeksi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembelian_mobil_bekas_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pembelian_mobil_bekas_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembelian_mobil_bekas_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_mobil` (`id_pengajuan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembelian_mobil_bekas_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembelian_mobil_bekas_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengajuan_mobil`
--
ALTER TABLE `pengajuan_mobil`
  ADD CONSTRAINT `pengajuan_mobil_id_kendaraan_pelanggan_foreign` FOREIGN KEY (`id_kendaraan_pelanggan`) REFERENCES `kendaraan_pelanggan` (`id_kendaraan_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pengajuan_mobil_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;

--
-- Constraints for table `penjualan_mobil`
--
ALTER TABLE `penjualan_mobil`
  ADD CONSTRAINT `penjualan_mobil_id_booking_foreign` FOREIGN KEY (`id_booking`) REFERENCES `booking_mobil` (`id_booking`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_mobil_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_mobil_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_mobil_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_mobil_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `penjualan_mobil_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON UPDATE CASCADE;

--
-- Constraints for table `penjualan_sparepart`
--
ALTER TABLE `penjualan_sparepart`
  ADD CONSTRAINT `penjualan_sparepart_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_sparepart_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE,
  ADD CONSTRAINT `penjualan_sparepart_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON UPDATE CASCADE;

--
-- Constraints for table `pesanan_mobil_member`
--
ALTER TABLE `pesanan_mobil_member`
  ADD CONSTRAINT `pesanan_mobil_member_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_mobil_member_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_mobil_member_id_penjualan_foreign` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan_mobil` (`id_penjualan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_mobil_member_id_petugas_keputusan_foreign` FOREIGN KEY (`id_petugas_keputusan`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_mobil_member_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanan_mobil_member_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `petugas`
--
ALTER TABLE `petugas`
  ADD CONSTRAINT `fk_petugas_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `poin_member`
--
ALTER TABLE `poin_member`
  ADD CONSTRAINT `fk_poin_member_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pre_order_mobil_member`
--
ALTER TABLE `pre_order_mobil_member`
  ADD CONSTRAINT `pre_order_mobil_member_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pre_order_mobil_member_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `promo_member`
--
ALTER TABLE `promo_member`
  ADD CONSTRAINT `fk_promo_member_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  ADD CONSTRAINT `promo_redemptions_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_redemptions_id_promo_foreign` FOREIGN KEY (`id_promo`) REFERENCES `promo_member` (`id_promo`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_redemptions_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD CONSTRAINT `fk_riwayat_poin_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_riwayat_poin_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `riwayat_stok_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `riwayat_stok_id_sparepart_foreign` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`) ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_id_permission_foreign` FOREIGN KEY (`id_permission`) REFERENCES `permissions` (`id_permission`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE CASCADE;

--
-- Constraints for table `sales_assignment_audits`
--
ALTER TABLE `sales_assignment_audits`
  ADD CONSTRAINT `sales_assignment_audits_actor_petugas_id_foreign` FOREIGN KEY (`actor_petugas_id`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_assignment_audits_new_sales_id_foreign` FOREIGN KEY (`new_sales_id`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_assignment_audits_previous_sales_id_foreign` FOREIGN KEY (`previous_sales_id`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `sales_awards`
--
ALTER TABLE `sales_awards`
  ADD CONSTRAINT `sales_awards_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `sales_award_audits`
--
ALTER TABLE `sales_award_audits`
  ADD CONSTRAINT `sales_award_audits_actor_petugas_id_foreign` FOREIGN KEY (`actor_petugas_id`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_award_audits_id_sales_award_foreign` FOREIGN KEY (`id_sales_award`) REFERENCES `sales_awards` (`id_sales_award`) ON DELETE CASCADE;

--
-- Constraints for table `sales_award_results`
--
ALTER TABLE `sales_award_results`
  ADD CONSTRAINT `sales_award_results_id_sales_award_foreign` FOREIGN KEY (`id_sales_award`) REFERENCES `sales_awards` (`id_sales_award`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_award_results_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `sales_leads`
--
ALTER TABLE `sales_leads`
  ADD CONSTRAINT `sales_leads_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_leads_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_leads_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  ADD CONSTRAINT `sales_lead_activities_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_lead_activities_id_sales_lead_foreign` FOREIGN KEY (`id_sales_lead`) REFERENCES `sales_leads` (`id_sales_lead`) ON DELETE CASCADE;

--
-- Constraints for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD CONSTRAINT `sales_targets_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE;

--
-- Constraints for table `serah_terima_pesanan_mobil`
--
ALTER TABLE `serah_terima_pesanan_mobil`
  ADD CONSTRAINT `serah_terima_pesanan_mobil_id_pesanan_foreign` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan_mobil_member` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `serah_terima_pesanan_mobil_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `service_approval_requests`
--
ALTER TABLE `service_approval_requests`
  ADD CONSTRAINT `service_approval_requests_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE;

--
-- Constraints for table `service_approval_request_items`
--
ALTER TABLE `service_approval_request_items`
  ADD CONSTRAINT `service_approval_request_items_approval_request_id_foreign` FOREIGN KEY (`approval_request_id`) REFERENCES `service_approval_requests` (`id_service_approval_request`) ON DELETE CASCADE;

--
-- Constraints for table `service_execution_events`
--
ALTER TABLE `service_execution_events`
  ADD CONSTRAINT `service_execution_events_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE;

--
-- Constraints for table `service_findings`
--
ALTER TABLE `service_findings`
  ADD CONSTRAINT `service_findings_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE;

--
-- Constraints for table `service_quality_controls`
--
ALTER TABLE `service_quality_controls`
  ADD CONSTRAINT `service_quality_controls_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE;

--
-- Constraints for table `service_quality_control_items`
--
ALTER TABLE `service_quality_control_items`
  ADD CONSTRAINT `service_quality_control_items_quality_control_id_foreign` FOREIGN KEY (`quality_control_id`) REFERENCES `service_quality_controls` (`id_service_quality_control`) ON DELETE CASCADE;

--
-- Constraints for table `service_staff_profiles`
--
ALTER TABLE `service_staff_profiles`
  ADD CONSTRAINT `service_staff_profiles_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_staff_specializations`
--
ALTER TABLE `service_staff_specializations`
  ADD CONSTRAINT `service_staff_specializations_id_service_specialization_foreign` FOREIGN KEY (`id_service_specialization`) REFERENCES `service_specializations` (`id_service_specialization`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_staff_specializations_id_service_staff_profile_foreign` FOREIGN KEY (`id_service_staff_profile`) REFERENCES `service_staff_profiles` (`id_service_staff_profile`) ON DELETE CASCADE;

--
-- Constraints for table `service_work_order_assignments`
--
ALTER TABLE `service_work_order_assignments`
  ADD CONSTRAINT `service_work_order_assignments_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`),
  ADD CONSTRAINT `service_work_order_assignments_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE;

--
-- Constraints for table `servis`
--
ALTER TABLE `servis`
  ADD CONSTRAINT `fk_servis_service_advisor` FOREIGN KEY (`id_service_advisor`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `servis_id_booking_servis_foreign` FOREIGN KEY (`id_booking_servis`) REFERENCES `booking_servis` (`id_booking_servis`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `servis_id_kendaraan_pelanggan_foreign` FOREIGN KEY (`id_kendaraan_pelanggan`) REFERENCES `kendaraan_pelanggan` (`id_kendaraan_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `servis_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `servis_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE;

--
-- Constraints for table `servis_sparepart`
--
ALTER TABLE `servis_sparepart`
  ADD CONSTRAINT `servis_sparepart_id_servis_foreign` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servis_sparepart_id_sparepart_foreign` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`) ON UPDATE CASCADE;

--
-- Constraints for table `sparepart`
--
ALTER TABLE `sparepart`
  ADD CONSTRAINT `fk_sparepart_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_sparepart` (`id_kategori`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sparepart_id_supplier_foreign` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sparepart_fitments`
--
ALTER TABLE `sparepart_fitments`
  ADD CONSTRAINT `sparepart_fitments_id_sparepart_foreign` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sparepart_fitments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD CONSTRAINT `stok_masuk_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stok_masuk_id_supplier_foreign` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `technician_time_off`
--
ALTER TABLE `technician_time_off`
  ADD CONSTRAINT `technician_time_off_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE;

--
-- Constraints for table `technician_working_hours`
--
ALTER TABLE `technician_working_hours`
  ADD CONSTRAINT `technician_working_hours_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE;

--
-- Constraints for table `test_drive`
--
ALTER TABLE `test_drive`
  ADD CONSTRAINT `test_drive_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `test_drive_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `test_drive_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `test_drive_id_sales_foreign` FOREIGN KEY (`id_sales`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `trade_in`
--
ALTER TABLE `trade_in`
  ADD CONSTRAINT `trade_in_id_inspeksi_foreign` FOREIGN KEY (`id_inspeksi`) REFERENCES `inspeksi_mobil` (`id_inspeksi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_mobil_dibeli_foreign` FOREIGN KEY (`id_mobil_dibeli`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_mobil_lama_masuk_foreign` FOREIGN KEY (`id_mobil_lama_masuk`) REFERENCES `mobil` (`id_mobil`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_mobil` (`id_pengajuan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON UPDATE CASCADE,
  ADD CONSTRAINT `trade_in_id_transaksi_foreign` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_promo` FOREIGN KEY (`id_promo`) REFERENCES `promo_member` (`id_promo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_id_petugas_foreign` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON UPDATE CASCADE;

--
-- Constraints for table `vehicle_inventories`
--
ALTER TABLE `vehicle_inventories`
  ADD CONSTRAINT `vehicle_inventories_id_mobil_foreign` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `vehicle_inventory_histories`
--
ALTER TABLE `vehicle_inventory_histories`
  ADD CONSTRAINT `vehicle_inventory_histories_id_vehicle_inventory_foreign` FOREIGN KEY (`id_vehicle_inventory`) REFERENCES `vehicle_inventories` (`id_vehicle_inventory`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
