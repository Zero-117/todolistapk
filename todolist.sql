-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Bulan Mei 2025 pada 02.03
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
-- Database: `todolist`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `color`, `created_at`) VALUES
(1, NULL, 'Kompetensi Keahlian', '#2196F3', '2025-05-13 00:26:40'),
(2, NULL, 'Matematika', '#F44336', '2025-05-13 00:26:40'),
(3, NULL, 'Produk Kreatif Kewirausahaan', '#4CAF50', '2025-05-13 00:26:40'),
(4, 2, 'Pendidikan Pancasila', '#00eeff', '2025-05-13 01:58:24'),
(5, 1, 'Pendidikan Pancasila', '#00eeff', '2025-05-13 03:15:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `task`
--

CREATE TABLE `task` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `due_date` date NOT NULL,
  `priority` enum('rendah','sedang','tinggi') NOT NULL,
  `status` enum('belum_selasai','dalam_proses','selesai') NOT NULL,
  `category` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `task`
--

INSERT INTO `task` (`id`, `title`, `description`, `due_date`, `priority`, `status`, `category`, `created_at`, `update_at`, `user_id`) VALUES
(7, 'Project membuat aplikasi', 'tewas kelelahan', '2025-05-14', 'tinggi', 'selesai', 'Kompetensi Keahlian', '2025-05-13 06:48:53', '2025-05-13 06:48:53', 2),
(8, 'Berjualan', 'Membuat produk yang mana akan di perjualbelikan', '2025-05-31', 'sedang', 'selesai', 'Produk Kreatif Kewirausahaan', '2025-05-13 06:58:26', '2025-05-13 06:58:26', 2),
(9, 'Nyatet', 'Nyatet Halaman 163', '2025-05-14', 'rendah', 'selesai', 'Pendidikan Pancasila', '2025-05-13 08:03:53', '2025-05-13 08:03:53', 2),
(10, 'Maen PB', 'Refresing', '2025-05-13', 'rendah', 'selesai', 'Kompetensi Keahlian', '2025-05-13 08:18:07', '2025-05-13 08:18:07', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`) VALUES
(1, 'faiz', 'faizpedo@gmail.com', '$2y$10$2fZbEimRYLtFqd25Uuhd2uxN6dRfA5rFMxtuo4soHHDTR6tskoXRG'),
(2, 'fahri', 'fahriganteng@gmail.com', '$2y$10$qZonGZMLdF2Agx.DZEnOpOmIfoWRdIzj3COHkOPtdkYMIszJngLCi');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `task`
--
ALTER TABLE `task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
