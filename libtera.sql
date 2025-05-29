-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20241213.325760150e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 29, 2025 at 01:51 PM
-- Server version: 8.0.30
-- PHP Version: 8.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `libtera`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_siswa` (IN `p_nisn` INT, IN `p_nama` VARCHAR(255), IN `p_username` VARCHAR(50), IN `p_password` VARCHAR(255), IN `p_jk` ENUM('L','P'), IN `p_id_kelas` INT, IN `p_id_jurusan` INT, IN `p_no_tlp` VARCHAR(15))   BEGIN
  INSERT INTO siswa(nisn,nama,username,password,jenis_kelamin,id_kelas,id_jurusan,no_tlp)
  VALUES(p_nisn,p_nama,p_username,p_password,p_jk,p_id_kelas,p_id_jurusan,p_no_tlp);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `nama_admin` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  `tgl_daftar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama_admin`, `username`, `password`, `no_tlp`, `tgl_daftar`) VALUES
(1, 'Aziz', 'aziz', 'pass123', '081234567890', '2025-05-20 22:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` varchar(20) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `penerbit` varchar(255) NOT NULL,
  `tahun_terbit` varchar(5) NOT NULL,
  `jumlah_halaman` int NOT NULL,
  `deskripsi` text,
  `stok` int DEFAULT '1',
  `id_kategori` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `cover`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `jumlah_halaman`, `deskripsi`, `stok`, `id_kategori`) VALUES
('1', 'cover1.jpg', 'Belajar Pemrograman', 'Andi Saputra', 'Erlangga', '2020', 350, 'Buku panduan lengkap belajar pemrograman dari dasar.', 10, 5),
('10', 'cover10.jpg', 'Teknologi Informasi', 'Samsul Arifin', 'Kompas', '2018', 350, 'Teknologi informasi terkini dan trend masa depan.', 7, 5),
('2', 'cover2.jpg', 'Sejarah Indonesia', 'Budi Santoso', 'Gramedia', '2018', 400, 'Sejarah Indonesia dari masa ke masa.', 9, 3),
('3', 'cover3.jpg', 'Kisah Inspiratif', 'Sari Melati', 'Mizan', '2019', 250, 'Kumpulan kisah inspiratif kehidupan.', 7, 4),
('4', 'cover4.jpg', 'Teknologi Masa Depan', 'Dewi Kartika', 'Kompas', '2021', 320, 'Membahas teknologi terbaru dan dampaknya.', 8, 5),
('5', 'cover5.jpg', 'Fiksi Ilmiah', 'Agus Pratama', 'Bentang', '2017', 280, 'Novel fiksi ilmiah yang menarik dan seru.', 10, 1),
('6', 'cover6.jpg', 'Panduan Bisnis', 'Rina Wijaya', 'Erlangga', '2022', 300, 'Strategi bisnis untuk pemula dan profesional.', 12, 2),
('7', 'cover7.jpg', 'Biografi Tokoh Dunia', 'Joko Sutrisno', 'Gramedia', '2016', 360, 'Biografi tokoh dunia yang menginspirasi.', 4, 4),
('8', 'cover8.jpg', 'Ilmu Komputer Dasar', 'Lia Pratiwi', 'Informatika', '2019', 410, 'Dasar-dasar ilmu komputer dan aplikasi.', 9, 3),
('9', 'cover9.jpg', 'Novel Romantis', 'Dina Lestari', 'Mizan', '2020', 270, 'Cerita cinta romantis penuh emosi.', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ebooks`
--

CREATE TABLE `ebooks` (
  `id_ebook` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `file_path` varchar(255) NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `cover_ebook` varchar(255) DEFAULT NULL,
  `tanggal_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ebooks`
--

INSERT INTO `ebooks` (`id_ebook`, `judul`, `penulis`, `deskripsi`, `file_path`, `id_kategori`, `cover_ebook`, `tanggal_upload`) VALUES
(1, 'Mahir Python untuk Pemula', 'Budi Santoso', 'Buku panduan lengkap belajar Python dari dasar hingga mahir, cocok untuk pemula yang ingin terjun ke dunia pemrograman.', 'mahir_python_pemula.pdf', 1, 'cover_python_pemula.jpg', '2025-05-29 13:49:53'),
(2, 'Panduan Desain Grafis Modern', 'Citra Ayu', 'Pelajari prinsip-prinsip desain grafis modern, penggunaan warna, tipografi, dan layout untuk menghasilkan karya visual yang menarik.', 'panduan_desain_grafis.pdf', 2, 'cover_desain_grafis.png', '2025-05-29 13:49:53'),
(3, 'Kumpulan Cerpen Anak Langit', 'Endang Lestari', 'Kumpulan cerita pendek yang inspiratif dan penuh makna, mengajak pembaca merenungi kehidupan.', 'cerpen_anak_langit.pdf', 3, NULL, '2025-05-29 13:49:53'),
(4, 'Teknik SEO 2025', 'Rangga Aditya', 'Strategi Search Engine Optimization terbaru untuk meningkatkan peringkat website Anda di mesin pencari Google.', 'teknik_seo_2025.pdf', 1, 'cover_seo_2025.jpg', '2025-05-29 13:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id_jurusan`, `nama_jurusan`) VALUES
(1, 'RPL'),
(3, 'TITL'),
(2, 'TKJ');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Fiksi'),
(2, 'Non-Fiksi'),
(3, 'Ilmu Pengetahuan'),
(4, 'Biografi'),
(5, 'Teknologi');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int NOT NULL,
  `nama_kelas` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`) VALUES
(1, 'X'),
(2, 'XI'),
(3, 'XII');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_buku` varchar(20) NOT NULL,
  `id_admin` int NOT NULL,
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` enum('PINJAM','KEMBALI') DEFAULT 'PINJAM'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_siswa`, `id_buku`, `id_admin`, `tgl_pinjam`, `tgl_kembali`, `status`) VALUES
(2, 1, '5', 1, '2025-05-27', NULL, 'PINJAM');

--
-- Triggers `peminjaman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_peminjaman` AFTER INSERT ON `peminjaman` FOR EACH ROW BEGIN
  UPDATE buku
    SET stok = stok - 1
    WHERE id_buku = NEW.id_buku;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id_rating` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_buku` varchar(20) NOT NULL,
  `nilai_rating` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id_rating`, `id_siswa`, `id_buku`, `nilai_rating`) VALUES
(1, 1, '5', 3),
(2, 1, '3', 4),
(3, 1, '1', 5);

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL,
  `nisn` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `id_kelas` int NOT NULL,
  `id_jurusan` int NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  `tgl_daftar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nisn`, `nama`, `username`, `password`, `jenis_kelamin`, `id_kelas`, `id_jurusan`, `no_tlp`, `tgl_daftar`) VALUES
(1, 12345, 'Budi', 'budi', 'pwd123', 'L', 1, 1, '0898989898', '2025-05-20 22:56:06'),
(2, 67890, 'Siti', 'siti', 'pwd456', 'P', 2, 2, '0898787878', '2025-05-20 22:56:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `fk_kategori` (`id_kategori`);

--
-- Indexes for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD PRIMARY KEY (`id_ebook`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`),
  ADD UNIQUE KEY `nama_jurusan` (`nama_jurusan`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD UNIQUE KEY `nama_kelas` (`nama_kelas`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `peminjaman_ibfk_1` (`id_siswa`),
  ADD KEY `peminjaman_ibfk_2` (`id_buku`),
  ADD KEY `peminjaman_ibfk_3` (`id_admin`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id_rating`),
  ADD UNIQUE KEY `unique_rating_siswa_buku` (`id_siswa`,`id_buku`),
  ADD UNIQUE KEY `unique_rating_per_siswa_per_buku` (`id_siswa`,`id_buku`),
  ADD KEY `fk_rating_buku` (`id_buku`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_jurusan` (`id_jurusan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id_ebook` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD CONSTRAINT `ebooks_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `fk_rating_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rating_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id_jurusan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
