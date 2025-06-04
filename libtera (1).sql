-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20241213.325760150e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 04, 2025 at 07:59 AM
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
  `id_kategori` int DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `cover`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `jumlah_halaman`, `deskripsi`, `stok`, `id_kategori`, `isbn`) VALUES
('1', 'cover1.jpg', 'Filosofi Teras', 'Andi Saputra', 'Erlangga', '2020', 350, '\"Filosofi Teras\" adalah buku pengantar yang menjelaskan bagaimana ajaran filsafat kuno Stoa (Stoisisme) bisa membantu kita menghadapi kehidupan modern yang penuh tekanan, kecemasan, dan overthinking. Dengan gaya bahasa yang ringan dan relevan untuk pembaca masa kini, Henry Manampiring mengajak kita mengenal prinsip-prinsip Stoa seperti dikotomi kendali, membedakan mana yang bisa kita kendalikan dan mana yang tidak, serta pentingnya hidup selaras dengan nilai kebajikan.', 8, 3, '23322212'),
('10', '1748778847_27424325.jpg', 'One Thousand Ways to Make Money', 'Page Fox', 'Guttenburg', '2017', 350, 'One Thousand Ways to Make Money adalah buku klasik yang menyajikan berbagai ide dan strategi praktis untuk meraih penghasilan dari beragam bidang kehidupan. Ditulis oleh Page Fox, buku ini menjadi panduan bagi siapa saja yang ingin membangun kemandirian finansial melalui kreativitas, ketekunan, dan inisiatif pribadi.', 7, 3, '9614'),
('2', '1748784962_71-vKSCi5zL._AC_UF1000,1000_QL80_.jpg', 'The Origin of Species by Means of Natural Selection', 'Charles Darwin', 'Gramedia', '2018', 400, 'The Origin of Species by Means of Natural Selection adalah karya revolusioner Charles Darwin yang memperkenalkan teori evolusi melalui seleksi alam. Dalam buku ini, Darwin menjelaskan bagaimana spesies makhluk hidup berubah dan berkembang seiring waktu sebagai hasil dari proses alamiah yang ia sebut sebagai \"seleksi alam\" — yaitu bertahannya individu-individu yang paling sesuai dengan lingkungannya.', 9, 3, '8754'),
('3', '1748779142_91VdAZp3fVL._AC_UF1000,1000_QL80_DpWeblab_.jpg', 'The inventions, researches and writings of Nikola Tesla', 'T. Commerford Martin', 'Guttenburg', '2012', 250, 'The Inventions, Researches and Writings of Nikola Tesla adalah kompilasi monumental karya dan pemikiran ilmuwan visioner Nikola Tesla, yang disusun oleh insinyur dan penulis Thomas Commerford Martin. Buku ini merekam berbagai penemuan Tesla yang revolusioner di bidang listrik dan elektromagnetisme, termasuk sistem arus bolak-balik (AC), transformator Tesla, dan eksperimen-eksperimennya dalam transmisi energi nirkabel.', 2, 5, '3581'),
('4', '1748779492_61CqQMY7mxS.jpg', 'Autobiography of Benjamin Franklin', 'Benjamin Franklin', 'Guttenburg', '2006', 320, 'The Autobiography of Benjamin Franklin adalah catatan kehidupan salah satu tokoh paling berpengaruh dalam sejarah Amerika. Ditulis langsung oleh Benjamin Franklin, buku ini menggambarkan perjalanan hidupnya dari seorang anak tukang sablon menjadi ilmuwan, penemu, diplomat, dan tokoh negarawan terkemuka.', 4, 4, '2675'),
('5', '1748757262_william-shakespeare-romeo-dan-juliet-2018.jpg', 'Romeo and Juliet', 'William Shakespeare', 'Bentang', '2017', 280, 'Romeo and Juliet adalah kisah cinta tragis antara dua remaja dari keluarga yang saling bermusuhan: Romeo dari keluarga Montague dan Juliet dari keluarga Capulet. Meskipun hubungan mereka dilarang, keduanya jatuh cinta dan diam-diam menikah. Namun, serangkaian kesalahpahaman dan konflik berdarah menyebabkan tragedi: Romeo membunuh sepupu Juliet, Tybalt, lalu diasingkan. Juliet dipaksa menikah dengan pria lain dan merencanakan pelarian dengan meminum ramuan tidur palsu.', 8, 1, '18481'),
('6', '1748778987_4057664594303.jfif', 'An Introduction to Machine Drawing and Design', 'David Allan Low', 'Guttenburg', '2012', 300, 'An Introduction to Machine Drawing and Design adalah buku teknis klasik yang dirancang untuk memberikan pemahaman dasar mengenai cara menggambar dan merancang mesin secara mekanis. Buku ini membahas prinsip-prinsip utama dalam gambar teknik, proyeksi ortogonal, toleransi, serta simbol-simbol yang digunakan dalam perancangan komponen mesin.', 12, 5, '7615'),
('7', '1748757118_91xNmlf86yL.jpg', 'Moby Dick', 'Herman Melville', 'Gramedia', '2016', 360, 'Ishmael, seorang pemuda yang ingin merasakan kehidupan sebagai pelaut, bergabung dengan kapal penangkap paus Pequod. Kapal tersebut dipimpin oleh Kapten Ahab, seorang pria misterius yang ternyata menyimpan dendam mendalam terhadap seekor paus putih raksasa bernama Moby Dick—makhluk yang telah merenggut kakinya di masa lalu.', 3, 1, '23243'),
('8', '1748779036_61jCvKACGvL.jpg', 'The Complete Herbal', 'Nicholas Culpeper', 'Bapa-bapa', '2019', 410, 'The Complete Herbal adalah buku klasik yang merangkum berbagai tanaman obat dan penggunaannya dalam pengobatan tradisional. Ditulis pada abad ke-17 oleh Nicholas Culpeper, seorang ahli herbal dan tabib terkenal asal Inggris, buku ini menyajikan ratusan jenis tumbuhan lengkap dengan deskripsi, manfaat medis, serta cara pengolahannya.', 9, 3, '1984'),
('9', '1748778778_81YIwoG-kGL.jpg', 'The art of money getting or, golden rules for making money', 'P. T. Barnum', 'Guttenburg', '2005', 270, 'The Art of Money Getting adalah panduan klasik tentang cara mencapai kesuksesan finansial, ditulis oleh showman dan pengusaha terkenal abad ke-19, P.T. Barnum. Dalam buku ini, Barnum membagikan prinsip-prinsip praktis dan etika dalam meraih kekayaan yang ia pelajari dari pengalaman hidupnya', 6, 3, '9886');

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id_denda` int NOT NULL,
  `id_peminjaman` int NOT NULL COMMENT 'Merujuk ke peminjaman yang terkait',
  `jumlah_denda_dikenakan` decimal(10,2) NOT NULL COMMENT 'Total denda yang ditetapkan oleh admin saat itu',
  `jumlah_telah_dibayar` decimal(10,2) DEFAULT '0.00' COMMENT 'Total jumlah yang sudah dibayar untuk denda ini',
  `tgl_transaksi_denda` date NOT NULL COMMENT 'Tanggal denda ini dicatat atau tanggal pembayaran terakhir',
  `status_denda` enum('Lunas','Belum Lunas','Dihapuskan') NOT NULL DEFAULT 'Belum Lunas',
  `id_admin_pencatat` int DEFAULT NULL COMMENT 'Admin yang mencatat transaksi denda ini',
  `keterangan` text COMMENT 'Catatan tambahan dari admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mencatat transaksi denda yang diproses manual oleh admin';

--
-- Dumping data for table `denda`
--

INSERT INTO `denda` (`id_denda`, `id_peminjaman`, `jumlah_denda_dikenakan`, `jumlah_telah_dibayar`, `tgl_transaksi_denda`, `status_denda`, `id_admin_pencatat`, `keterangan`, `created_at`) VALUES
(1, 9, 60000.00, 60000.00, '2025-06-01', 'Dihapuskan', 1, 'minggu depan\nDihapuskan oleh Admin ID: 1 pada 2025-06-01 04:19:15. Alasan: rusak', '2025-05-31 20:02:54'),
(2, 10, 65000.00, 65000.00, '2025-06-01', 'Lunas', 1, 'minggu depan terakhir\nPembayaran Lanjutan: Rp 200.000 pada 01 Jun 2025 oleh Admin ID: 1.', '2025-06-01 04:00:14'),
(3, 11, 200000.00, 60000.00, '2025-06-02', 'Belum Lunas', 1, 'BUKU HILANG. buku hilang di jalan\nPembayaran Lanjutan: Rp 30.000 pada 01 Jun 2025 oleh Admin ID: 1. Ket: sisa nya minggu depan\nPembayaran Lanjutan: Rp 30.000 pada 02 Jun 2025. Ket: minggu depan terakhir', '2025-06-01 12:02:00');

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
  `tanggal_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `isbn` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ebooks`
--

INSERT INTO `ebooks` (`id_ebook`, `judul`, `penulis`, `deskripsi`, `file_path`, `id_kategori`, `cover_ebook`, `tanggal_upload`, `isbn`) VALUES
(1, 'The art of money getting or, golden rules for making money', 'P. T. Barnum', 'The Art of Money Getting adalah panduan klasik karya P.T. Barnum yang menawarkan nasihat praktis dan prinsip abadi untuk meraih kesuksesan finansial. Dengan gaya lugas dan penuh pengalaman pribadi, Barnum membagikan \"aturan emas\" yang mencakup pentingnya kerja keras, kejujuran, pengelolaan waktu, serta penghindaran dari utang dan gaya hidup boros.', '1748778213_sodapdf-converted (4).pdf', 3, '1748778733_81YIwoG-kGL.jpg', '2025-05-29 13:49:53', NULL),
(2, 'Moby Dick; Or, The Whale', 'Herman Melville', 'Ishmael, seorang pemuda yang ingin merasakan kehidupan sebagai pelaut, bergabung dengan kapal penangkap paus Pequod. Kapal tersebut dipimpin oleh Kapten Ahab, seorang pria misterius yang ternyata menyimpan dendam mendalam terhadap seekor paus putih raksasa bernama Moby Dick—makhluk yang telah merenggut kakinya di masa lalu.', '1748778474_sodapdf-converted.pdf', 1, '1748778474_91xNmlf86yL.jpg', '2025-05-29 13:49:53', NULL),
(3, 'hashing', 'Endang Lestari', 'Kumpulan cerita pendek yang inspiratif dan penuh makna, mengajak pembaca merenungi kehidupan.', '1748716846_5_3-Hashing.pdf', 3, '1748716846_61Qr6xXSYJL._AC_UF1000,1000_QL80_.jpg', '2025-05-29 13:49:53', NULL),
(4, 'Romeo and Juliet', 'William Shakespeare', 'Romeo and Juliet adalah kisah cinta tragis antara dua remaja dari keluarga yang saling bermusuhan: Romeo dari keluarga Montague dan Juliet dari keluarga Capulet. Meskipun hubungan mereka dilarang, keduanya jatuh cinta dan diam-diam menikah. Namun, serangkaian kesalahpahaman dan konflik berdarah menyebabkan tragedi: Romeo membunuh sepupu Juliet, Tybalt, lalu diasingkan. Juliet dipaksa menikah dengan pria lain dan merencanakan pelarian dengan meminum ramuan tidur palsu.', '1748778549_sodapdf-converted (2).pdf', 1, '1748778549_william-shakespeare-romeo-dan-juliet-2018.jpg', '2025-05-29 13:49:53', NULL),
(5, 'Cara Cepat Membaca Bahasa Tubuh', 'Joe Navarro', 'Pria itu duduk dengan kaku di kursi yang terletak di\r\nujung meja. Dengan hati-hati, ia merangkai jawaban\r\natas pertanyaan agen FBI. Ia bukan tersangka utama kasus\r\npembunuhan. Alibinya dapat dipercaya, dan ia terdengar\r\njujur. Namun, penyidik tetap menekan. Dengan segala\r\nkemungkinan sebagai tersangka, pria tersebut ditanyai\r\nsejumlah pertanyaan tentang pembunuhan bersenjata:', '1749023456_Cara Cepat Membaca Bahasa Tubuh - Joe Navarro.pdf', 7, '1749023456_Screenshot 2025-06-04 145031.png', '2025-06-04 07:50:56', NULL);

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
(4, 'Biografii'),
(5, 'Teknologi'),
(7, 'Psikologi');

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
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_siswa`, `id_buku`, `tgl_pinjam`, `tgl_kembali`, `status`) VALUES
(9, 1, '3', '2025-03-01', '2025-05-31', 'KEMBALI'),
(10, 1, '5', '2025-03-01', '2025-06-01', 'KEMBALI'),
(11, 1, '3', '2025-06-01', '2025-06-01', 'HILANG'),
(12, 1, '3', NULL, NULL, 'DIBATALKAN'),
(13, 1, '7', NULL, NULL, 'DIBATALKAN'),
(14, 1, '1', NULL, NULL, 'PENDING'),
(15, 1, '3', NULL, NULL, 'DIBATALKAN'),
(16, 1, '5', NULL, NULL, 'PENDING'),
(17, 1, '4', NULL, NULL, 'PENDING');

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
(1, 1, '5', 4),
(2, 1, '3', 4),
(3, 1, '1', 3);

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
  `tgl_daftar` datetime DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nisn`, `nama`, `username`, `password`, `jenis_kelamin`, `id_kelas`, `id_jurusan`, `no_tlp`, `tgl_daftar`, `email`) VALUES
(1, 12345, 'Budi', 'budi', 'pwd123', 'L', 1, 1, '0898989898', '2025-05-20 22:56:06', '230411100191@student.trunojoyo.ac.id'),
(2, 67890, 'Siti', 'siti', 'pwd456', 'P', 2, 2, '0898787878', '2025-05-20 22:56:06', NULL),
(3, 1785189, 'sasa', 'sasa', '$2y$10$DZNVmv4zK/TiTrarQ3.1tO/.6eiIebMIjSD5CoguAJdt6qgg0KCjK', 'P', 2, 2, '08715983151', '2025-06-01 20:13:18', NULL),
(4, 12516, 'loke', 'loke', '456', 'L', 2, 1, '0981827582', '2025-06-01 20:17:54', NULL);

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
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `idx_id_peminjaman_denda` (`id_peminjaman`),
  ADD KEY `id_admin_pencatat` (`id_admin_pencatat`);

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
  ADD KEY `peminjaman_ibfk_2` (`id_buku`);

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
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id_ebook` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `denda_ibfk_2` FOREIGN KEY (`id_admin_pencatat`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE;

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
