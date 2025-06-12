-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20241213.325760150e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 10, 2025 at 10:19 AM
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
('1', 'cover1.jpg', 'Filosofi Teras', 'Andi Saputra', 'Erlangga', '2020', 350, '\"Filosofi Teras\" adalah buku pengantar yang menjelaskan bagaimana ajaran filsafat kuno Stoa (Stoisisme) bisa membantu kita menghadapi kehidupan modern yang penuh tekanan, kecemasan, dan overthinking. Dengan gaya bahasa yang ringan dan relevan untuk pembaca masa kini, Henry Manampiring mengajak kita mengenal prinsip-prinsip Stoa seperti dikotomi kendali, membedakan mana yang bisa kita kendalikan dan mana yang tidak, serta pentingnya hidup selaras dengan nilai kebajikan.', 6, 3, '23322212'),
('10', '1748778847_27424325.jpg', 'One Thousand Ways to Make Money', 'Page Fox', 'Guttenburg', '2017', 350, 'One Thousand Ways to Make Money adalah buku klasik yang menyajikan berbagai ide dan strategi praktis untuk meraih penghasilan dari beragam bidang kehidupan. Ditulis oleh Page Fox, buku ini menjadi panduan bagi siapa saja yang ingin membangun kemandirian finansial melalui kreativitas, ketekunan, dan inisiatif pribadi.', 7, 3, '9614'),
('2', '1748784962_71-vKSCi5zL._AC_UF1000,1000_QL80_.jpg', 'The Origin of Species by Means of Natural Selection', 'Charles Darwin', 'Gramedia', '2018', 400, 'The Origin of Species by Means of Natural Selection adalah karya revolusioner Charles Darwin yang memperkenalkan teori evolusi melalui seleksi alam. Dalam buku ini, Darwin menjelaskan bagaimana spesies makhluk hidup berubah dan berkembang seiring waktu sebagai hasil dari proses alamiah yang ia sebut sebagai \"seleksi alam\" — yaitu bertahannya individu-individu yang paling sesuai dengan lingkungannya.', 9, 3, '8754'),
('4', '1748779492_61CqQMY7mxS.jpg', 'Autobiography of Benjamin Franklinn', 'Benjamin Franklin', 'Guttenburg', '2006', 320, 'The Autobiography of Benjamin Franklin adalah catatan kehidupan salah satu tokoh paling berpengaruh dalam sejarah Amerika. Ditulis langsung oleh Benjamin Franklin, buku ini menggambarkan perjalanan hidupnya dari seorang anak tukang sablon menjadi ilmuwan, penemu, diplomat, dan tokoh negarawan terkemuka.', 4, 4, '2675'),
('5', '1748757262_william-shakespeare-romeo-dan-juliet-2018.jpg', 'Romeo and Juliet', 'William Shakespeare', 'Bentang', '2017', 280, 'Romeo and Juliet adalah kisah cinta tragis antara dua remaja dari keluarga yang saling bermusuhan: Romeo dari keluarga Montague dan Juliet dari keluarga Capulet. Meskipun hubungan mereka dilarang, keduanya jatuh cinta dan diam-diam menikah. Namun, serangkaian kesalahpahaman dan konflik berdarah menyebabkan tragedi: Romeo membunuh sepupu Juliet, Tybalt, lalu diasingkan. Juliet dipaksa menikah dengan pria lain dan merencanakan pelarian dengan meminum ramuan tidur palsu.', 8, 1, '18481'),
('6', '1748778987_4057664594303.jfif', 'An Introduction to Machine Drawing and Designn', 'David Allan Low', 'Guttenburg', '2012', 300, 'An Introduction to Machine Drawing and Design adalah buku teknis klasik yang dirancang untuk memberikan pemahaman dasar mengenai cara menggambar dan merancang mesin secara mekanis. Buku ini membahas prinsip-prinsip utama dalam gambar teknik, proyeksi ortogonal, toleransi, serta simbol-simbol yang digunakan dalam perancangan komponen mesin.', 12, 5, '7615'),
('7', '1748757118_91xNmlf86yL.jpg', 'Moby Dick', 'Herman Melville', 'Gramedia', '2016', 360, 'Ishmael, seorang pemuda yang ingin merasakan kehidupan sebagai pelaut, bergabung dengan kapal penangkap paus Pequod. Kapal tersebut dipimpin oleh Kapten Ahab, seorang pria misterius yang ternyata menyimpan dendam mendalam terhadap seekor paus putih raksasa bernama Moby Dick—makhluk yang telah merenggut kakinya di masa lalu.', 3, 1, '23243'),
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
  `keterangan` text COMMENT 'Catatan tambahan dari admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mencatat transaksi denda yang diproses manual oleh admin';

--
-- Dumping data for table `denda`
--

INSERT INTO `denda` (`id_denda`, `id_peminjaman`, `jumlah_denda_dikenakan`, `jumlah_telah_dibayar`, `tgl_transaksi_denda`, `status_denda`, `keterangan`, `created_at`) VALUES
(2, 10, 65000.00, 65000.00, '2025-06-01', 'Lunas', 'minggu depan terakhir\nPembayaran Lanjutan: Rp 200.000 pada 01 Jun 2025 oleh Admin ID: 1.', '2025-06-01 04:00:14'),
(4, 18, 80000.00, 14000.00, '2025-06-10', 'Belum Lunas', 'BUKU HILANG. buku hilang di jalan\nPembayaran Lanjutan: Rp 7.000 pada 10 Jun 2025.\nPembayaran Lanjutan: Rp 7.000 pada 10 Jun 2025.', '2025-06-10 09:43:32');

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
(1, 'Karena Hidup Itu Indah', 'Cathreen Moko', 'Buku Karena Hidup Itu Indah adalah refleksi penuh makna tentang bagaimana menemukan keindahan dalam hal-hal sederhana di tengah hiruk-pikuk kehidupan. Melalui kisah-kisah inspiratif dan renungan sehari-hari, penulis mengajak pembaca untuk lebih peka terhadap kebahagiaan kecil, mensyukuri apa yang dimiliki, dan melihat hidup dari sudut pandang yang lebih positif.', '1749275834_Karena Hidup Itu Indah.pdf', 2, '1749275834_25637153.jpg', '2025-05-29 13:49:53', NULL),
(2, 'Bumi', 'Tere Liye', 'Bumi adalah novel fantasi karya Tere Liye yang mengisahkan petualangan tiga remaja—Raib, Seli, dan Ali—yang memiliki kemampuan luar biasa dan ditakdirkan untuk menyelamatkan dunia dari ancaman kegelapan. Raib, gadis berusia 15 tahun dari Klan Bulan, mampu menghilang dengan menutup wajahnya; Seli, sahabatnya dari Klan Matahari, dapat mengeluarkan petir; sementara Ali, pemuda jenius dari Klan Bumi, memiliki kecerdasan luar biasa dan kemampuan analitis yang tajam.', '1749277482_1. Tere Liyee-Bumi.pdf', 1, '1749277482_Bumi.png', '2025-05-29 13:49:53', NULL),
(3, 'hashing', 'Endang Lestari', 'Buku The Joy of Hashing adalah panduan teknis yang mendalam namun menyenangkan untuk memahami konsep dan penerapan teknik hashing dalam ilmu komputer. Ditujukan bagi pelajar, programmer, hingga profesional di bidang teknologi, buku ini membahas beragam metode hashing seperti hash table, open addressing, double hashing, hingga cuckoo hashing dan bloom filter.', '1748716846_5_3-Hashing.pdf', 5, '1748716846_61Qr6xXSYJL._AC_UF1000,1000_QL80_.jpg', '2025-05-29 13:49:53', NULL),
(4, 'Life is Short, Make it Great', 'Dale Carniage', 'Life is Short, Make It Great adalah sebuah panduan yang menginspirasi pembaca untuk menjalani hidup dengan penuh semangat dan makna, mengingat waktu yang kita miliki sangat terbatas. Buku ini menekankan pentingnya mengambil keputusan yang tepat, berani keluar dari zona nyaman, dan fokus pada hal-hal yang benar-benar penting dalam hidup.', '1749275635_Life Is Short Make It Great.pdf', 2, '1749275635_covUFK-091.jpg', '2025-05-29 13:49:53', NULL),
(5, 'Kamu Itu Hebat', 'Nadia Natasya Ifada', 'Buku Kamu Itu Hebat adalah sebuah karya motivasi yang ditulis dengan penuh ketulusan untuk membangkitkan semangat dan kepercayaan diri setiap pembacanya. Melalui kata-kata yang sederhana namun mengena, buku ini mengingatkan bahwa setiap orang memiliki potensi luar biasa di dalam dirinya—termasuk kamu.', '1749276072_Kamu Itu Hebat - Nadia Natasya Ifada.pdf', 2, '1749276072_kamu itu hebat.jpg', '2025-06-07 06:01:12', NULL),
(6, 'Artificial Intelligence', 'Viktor Vekky Ronald Repi', 'Buku Artificial Intelligence yang disusun oleh Viktor Vekky Ronald Repi bersama sejumlah penulis lain, diterbitkan oleh Penamuda Media pada Januari 2024 dengan ISBN 978-623-097-345-1. Buku ini membahas konsep, teknologi, dan aplikasi dari kecerdasan buatan (AI), termasuk jaringan saraf tiruan, logika fuzzy, algoritma genetika, dan pembelajaran mendalam. Selain itu, buku ini juga membahas etika dan tantangan yang terkait dengan pengembangan dan penggunaan AI, seperti privasi, keamanan, dan dampak sosial.', '1749278134_Artificial Inteligence (Tim_ (Z-Library).pdf', 5, '1749278134_ai.jpg', '2025-06-07 06:03:11', NULL),
(7, 'Good Habits, Bad Habits', 'Wendy Wood', 'Buku ini mengungkap bahwa sekitar 43% dari aktivitas harian kita dilakukan secara otomatis tanpa kesadaran penuh—hasil dari kebiasaan yang terbentuk dalam pikiran bawah sadar. Wendy Wood, seorang profesor psikologi dengan pengalaman lebih dari tiga dekade, menjelaskan bahwa perubahan perilaku yang bertahan lama tidak cukup hanya mengandalkan tekad atau niat baik.', '1749276298_Good Habits, Bad Habits - Cara Membentuk Kebiasaan Baik - Wendy Wood.pdf', 2, '1749276298_good habits, bad habits.jpg', '2025-06-07 06:04:58', NULL),
(8, 'Pulang', 'Leila S Chudori', 'Pulang karya Leila S. Chudori adalah novel fiksi sejarah yang menggugah, mengisahkan kehidupan Dimas Suryo, seorang wartawan Indonesia yang terpaksa hidup dalam pengasingan di Paris setelah peristiwa 30 September 1965. Bersama tiga rekannya—Nugroho, Risjaf, dan Tjai—Dimas mendirikan Restoran Tanah Air sebagai simbol kerinduan mereka terhadap tanah kelahiran. Melalui sudut pandang Dimas dan putrinya, Lintang Utara, yang datang ke Indonesia pada Mei 1998 untuk menyelesaikan tugas akhirnya, novel ini mengeksplorasi tema kehilangan, identitas, dan kerinduan akan tanah air.', '1749277752_350. Pulang - Leila.pdf', 1, '1749277752_9786024242756_Pulang-New-C.png', '2025-06-07 06:06:38', NULL),
(9, 'Find Why You', 'David Mead', 'Find Your Why karya Simon Sinek, bersama David Mead dan Peter Docker, adalah panduan praktis untuk membantu individu dan tim menemukan tujuan mendasar yang memberi makna pada apa yang mereka lakukan. Melanjutkan konsep \"Start with Why\", buku ini menawarkan pendekatan langkah demi langkah untuk menggali cerita pribadi, mengidentifikasi tema-tema utama, dan merumuskan pernyataan WHY yang jelas dan inspiratif.', '1749276484_Find Your Why - Panduan Praktis Untuk Menemukan Tujuan Anda & Tim Anda - Simon Sinek.pdf', 2, '1749276484_find your why.jpg', '2025-06-07 06:08:04', NULL),
(10, 'Ego is the Enemy', 'Ryan Holiday', '', '1749276554_Ego Is The Enemy.pdf', 2, '1749276554_ego is the enemy.jfif', '2025-06-07 06:09:14', NULL),
(11, 'Dilan 1990', 'Pidi Baiq', 'Dilan: Dia adalah Dilanku Tahun 1990 karya Pidi Baiq adalah novel remaja romantis yang berlatar di Bandung pada tahun 1990. Cerita ini mengikuti Milea, seorang siswi SMA yang pindah sekolah dan bertemu dengan Dilan, cowok unik, jenaka, dan tidak biasa. Alih-alih menggunakan cara umum untuk mendekati Milea, Dilan menawan hatinya dengan cara yang nyeleneh, lucu, dan tak terduga.', '1749281249_Dilan 1990 (1).pdf', 1, '1749281249_dilan.jpg', '2025-06-07 07:27:29', NULL),
(12, 'Indiepreneur', 'Panji Pragiwaksono', 'Indiepreneur karya Pandji Pragiwaksono adalah buku panduan bagi para pelaku industri kreatif yang ingin membangun karier secara independen tanpa mengorbankan idealisme. Melalui pengalaman pribadinya sebagai musisi, komedian, dan entrepreneur, Pandji membagikan strategi dan prinsip yang membantunya sukses tanpa bergantung pada industri besar.', '1749281569_Indiepreneur - Pandji Pragiwaksono.pdf', 3, '1749281569_25685886.jpg', '2025-06-07 07:32:49', NULL),
(13, 'Marketing 4.0', 'Philip Kotler', 'Marketing 4.0 adalah buku karya Philip Kotler, Hermawan Kartajaya, dan Iwan Setiawan yang membahas transformasi dunia pemasaran dari era tradisional ke era digital. Buku ini menjelaskan bagaimana perilaku konsumen berubah di tengah konektivitas internet, media sosial, dan teknologi mobile.', '1749281668_Marketing_4_0_Bergerak_dari_Tradisional_ke_Digital_Philip_Kotler.pdf', 3, '1749281668_marketing 4.0.jpg', '2025-06-07 07:34:28', NULL),
(14, 'Atomic Habits', 'James Clear', 'Atomic Habits karya James Clear adalah buku pengembangan diri yang membahas bagaimana perubahan kecil yang konsisten dapat menghasilkan hasil luar biasa dalam jangka panjang. Dengan pendekatan ilmiah dan praktis, James menjelaskan bahwa kesuksesan bukan hasil dari perubahan besar yang instan, tetapi dari kebiasaan kecil (atomic) yang dilakukan setiap hari.', '1749281989_Atomic Habits.pdf', 2, '1749281989_atomic.jpg', '2025-06-07 07:39:49', NULL),
(15, 'Focus', 'Daniel Coleman', 'Buku Focus: The Hidden Driver of Excellence karya Daniel Goleman membahas pentingnya fokus sebagai keterampilan mental utama yang menentukan keberhasilan dalam hidup pribadi maupun profesional. Goleman menjelaskan bahwa ada tiga jenis fokus—fokus pada diri sendiri, pada orang lain, dan pada dunia sekitar—yang semuanya perlu dilatih dan diseimbangkan.', '1749282200_Focus.pdf', 2, '1749282200_focus.jpg', '2025-06-07 07:43:20', NULL);

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
(2, 'Self Dev'),
(3, 'Bisnis'),
(4, 'Biografii'),
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
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_siswa`, `id_buku`, `tgl_pinjam`, `tgl_kembali`, `status`) VALUES
(10, 1, '5', '2025-03-01', '2025-06-01', 'KEMBALI'),
(13, 1, '7', NULL, NULL, 'DIBATALKAN'),
(14, 1, '1', NULL, NULL, 'DIBATALKAN'),
(16, 1, '5', NULL, NULL, 'DIBATALKAN'),
(17, 1, '4', NULL, NULL, 'DITOLAK'),
(18, 1, '1', '2025-06-07', '2025-06-10', 'HILANG'),
(19, 1, '6', NULL, NULL, 'DIBATALKAN'),
(20, 5, '5', '2025-06-10', '2025-06-10', 'KEMBALI');

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
(1, 1, '5', 5),
(3, 1, '1', 3);

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL,
  `nisn` varchar(50) NOT NULL,
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
(1, '12345', 'Budi', 'budi', 'pwd123', 'L', 1, 1, '0898989898', '2025-05-20 22:56:06', 'abid.ays23456@gmail.com'),
(3, '1785189', 'sasa', 'sasa', '$2y$10$DZNVmv4zK/TiTrarQ3.1tO/.6eiIebMIjSD5CoguAJdt6qgg0KCjK', 'P', 2, 2, '08715983151', '2025-06-01 20:13:18', NULL),
(4, '12516', 'loke', 'loke', '456', 'L', 2, 1, '0981827582', '2025-06-01 20:17:54', NULL),
(5, '230411100191', 'M Sultan Abdurrahman', 'sultan', '123', 'L', 1, 2, '083123133839', '2025-06-10 16:36:42', NULL);

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
  ADD KEY `idx_id_peminjaman_denda` (`id_peminjaman`);

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
  MODIFY `id_denda` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id_ebook` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE ON UPDATE CASCADE;

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
