-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 01:09 PM
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
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `nik`, `nama`, `alamat`, `no_hp`, `email`, `password`) VALUES
(6, '161100', 'iqbal', 'jl', '00', 'iqbalmonyet@gmail.com', '161100'),
(8, '1234', 'aji', 'jakarta', '90', 'aji@gmail.com', '123'),
(9, '12345', 'rafli', 'jakarta', '08', 'rafliaja@gmail.com', ''),
(10, '090402', 'adelina', 'jln.asmin', '082235282765', 'adelinalumbantoruan2020@gmail.com', '2002');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `isbn`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `kategori`, `stok`, `gambar`) VALUES
(6, '978‑602‑03‑2478‑4', 'Hujan', 'Tere Liye', 'PT Gramedia Pustaka Utama', '2016', 'Fiksi Ilmiah (Science Fiction), Drama / Roman, You', 9, 'buku_685fd5fc9930b.jpeg'),
(7, '978‑602‑7870‑99‑4 ', 'Dilan Bagian Kedua: Dia Adalah Dilanku Tahun 1991', 'Pidi Baiq', 'Mizan', '2015', 'Romansa / Drama Remaja, Young Adult (Remaja Kontem', 9, 'buku_685fda0e08a8d.jpg'),
(8, '978-623-00-1234-5', 'Mozaciko', 'Aulia F. Ramadhani', 'Penerbit Langit Biru', '2025', 'Fiksi Fantasi / Petualangan', 8, 'buku_6860eeab623c8.jpg'),
(9, '978-602-03-4567-8', 'Bintang', 'Tere Liye', 'Republika Penerbit', '2018', 'Fiksi Remaja / Inspiratif', 10, 'buku_6860ef5d0c612.jpg'),
(10, '978-623-00-9999-9', 'Friend Zone', 'Nadya Salsabila', 'Lovepub Publishing', '2018', 'Fiksi Romantis / Remaja', 10, 'buku_6860effaabde4.jpeg'),
(11, '978‑623‑493‑303‑1', 'timun jelita', 'Raditya Dika', 'GagasMedia', '2024', 'Fiksi – Komedi, Drama, Musik', 15, 'buku_6860f3938caf7.jpeg'),
(12, '978‑979‑780‑915‑7 ', 'ubur ubur lembur', 'Raditya Dika', 'GagasMedia', '2018', 'humor ', 10, 'buku_6860f49088cba.jpg'),
(13, '978‑602‑085‑1563', 'Milea Suara dari dilan', 'Pidi Baiq', 'Pastel Books', '2016', 'Romance', 10, 'buku_6860f5cb18ff7.jpeg'),
(14, '978-623-95545-2-1', 'pulang pergi', 'Tere Liye', 'Sabak Grip Nusantara', '2021', 'Aksi', 12, 'buku_6860f6f74ec0e.jpeg'),
(15, '978‑602‑5783‑83‑8', 'suami abadi', 'Fyodor Dostoevsky', 'Basabasi', '2019', ' Cerpen ', 20, 'buku_6860f874f07fc.webp');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(2, 'Fiksi Remaja / Inspiratif'),
(3, 'Romansa / Drama Remaja, Young Adult (Remaja Kontem'),
(4, 'Romance	'),
(5, 'aksi '),
(6, 'cerpen'),
(7, 'Fiksi – Komedi, Drama, Musik'),
(8, 'Fiksi Fantasi / Petualangan');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `id_petugas` int(11) DEFAULT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('Dipinjam','Dikembalikan') DEFAULT 'Dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_anggota`, `id_buku`, `id_petugas`, `tanggal_pinjam`, `tanggal_kembali`, `status`) VALUES
(8, 6, 14, 4, '2025-06-29', '2025-06-29', 'Dikembalikan'),
(9, 8, 6, 4, '2025-06-29', '2025-06-30', 'Dipinjam');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id_pengembalian` int(11) NOT NULL,
  `id_peminjaman` int(11) NOT NULL,
  `tanggal_dikembalikan` date DEFAULT NULL,
  `denda` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengembalian`
--

INSERT INTO `pengembalian` (`id_pengembalian`, `id_peminjaman`, `tanggal_dikembalikan`, `denda`) VALUES
(4, 8, '2025-06-29', 3000);

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `nama_petugas` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`) VALUES
(4, 'Administrator', 'mili', 'admin123'),
(10, 'nia', 'nia', '040404');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_petugas` (`id_petugas`),
  ADD KEY `peminjaman_ibfk_2` (`id_buku`);

--
-- Indexes for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id_pengembalian`),
  ADD KEY `id_peminjaman` (`id_peminjaman`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id_pengembalian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`),
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_3` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`);

--
-- Constraints for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
