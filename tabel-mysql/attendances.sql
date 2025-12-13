-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Des 2025 pada 01.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `presensi_sd_musapuga`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` datetime DEFAULT NULL,
  `jam_pulang` datetime DEFAULT NULL,
  `status` enum('HADIR','IZIN','SAKIT') DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `employee_id`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, '0000-00-00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'HADIR', NULL, '2025-12-09 13:33:50', '2025-12-09 13:34:36'),
(2, 2, '2025-12-09', '2025-12-09 20:39:38', '2025-12-09 20:39:58', 'HADIR', NULL, '2025-12-09 13:39:38', '2025-12-09 13:39:58'),
(3, 4, '2025-12-09', NULL, NULL, 'SAKIT', 'Demam', '2025-12-10 06:14:50', '2025-12-10 06:14:50'),
(4, 3, '2025-12-09', NULL, NULL, 'IZIN', 'Pergi Luar Kota', '2025-12-10 06:18:21', '2025-12-10 06:18:21'),
(5, 1, '2025-12-11', '2025-12-11 14:26:24', '2025-12-11 14:27:05', 'HADIR', NULL, '2025-12-11 07:26:24', '2025-12-11 07:27:05'),
(6, 2, '2025-12-11', '2025-12-11 14:28:37', NULL, 'HADIR', NULL, '2025-12-11 07:28:37', '2025-12-11 07:28:37'),
(7, 1, '2025-12-12', '2025-12-12 05:42:03', '2025-12-12 19:01:29', 'HADIR', '', '2025-12-11 22:42:03', '2025-12-12 12:01:29'),
(8, 6, '2025-12-12', '2025-12-12 19:14:11', '2025-12-12 19:14:15', 'HADIR', 'Cuti', '2025-12-12 07:44:43', '2025-12-12 12:14:15'),
(9, 2, '2025-12-12', '2025-12-12 19:01:32', '2025-12-12 19:01:35', 'HADIR', NULL, '2025-12-12 12:01:32', '2025-12-12 12:01:35'),
(10, 7, '2025-12-12', NULL, NULL, 'SAKIT', 'flu', '2025-12-12 12:07:30', '2025-12-12 12:07:30');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_attendance_per_day` (`employee_id`,`tanggal`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `fk_attendances_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
