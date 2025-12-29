-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 02:43 PM
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
-- Database: `school_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `title`, `description`, `amount`, `created_at`) VALUES
(1, 'Study Tour', 'Biaya kegiatan study tour', 2000000.00, '2025-01-31 07:56:35'),
(2, 'Lomba Cerdas Cermat', 'Dana untuk mengikuti lomba cerdas cermat', 1000000.00, '2025-01-31 07:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `administrations`
--

CREATE TABLE `administrations` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `administrations`
--

INSERT INTO `administrations` (`id`, `title`, `description`, `amount`, `created_at`) VALUES
(1, 'SPP Bulanan', 'Pembayaran SPP bulanan siswa', 200000.00, '2025-01-26 03:59:05'),
(2, 'Dana Ujian', 'Dana untuk ujian semester', 50000.00, '2025-01-26 03:59:05'),
(3, 'Magang', 'Magang', 500000.00, '2025-09-24 11:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('hadir','sakit','izin','alfa') NOT NULL,
  `attendance_open_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `subject_id`, `date`, `status`, `attendance_open_id`) VALUES
(1, 1, 1, '2025-02-06', 'alfa', NULL),
(2, 1, 1, '2025-02-06', 'hadir', NULL),
(3, 1, 3, '2025-02-06', 'hadir', NULL),
(4, 1, 1, '2025-02-07', 'hadir', NULL),
(5, 1, 3, '2025-02-07', 'sakit', NULL),
(6, 13, 1, '2025-02-07', 'izin', NULL),
(7, 13, 3, '2025-02-07', 'izin', NULL),
(8, 1, 1, '2025-02-16', 'hadir', NULL),
(9, 1, 3, '2025-02-16', 'sakit', NULL),
(10, 1, 1, '2025-07-01', 'hadir', NULL),
(11, 1, 1, '2025-07-01', 'hadir', 16),
(12, 1, 1, '2025-07-05', 'hadir', 17),
(13, 1, 1, '2025-07-09', 'hadir', 18);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_open`
--

CREATE TABLE `attendance_open` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance_open`
--

INSERT INTO `attendance_open` (`id`, `class_id`, `subject_id`, `date`, `start_time`, `end_time`, `is_closed`) VALUES
(14, 1, 1, '2025-07-01', '14:45:00', '14:50:00', 1),
(15, 1, 1, '2025-07-01', '15:39:00', '15:45:00', 1),
(16, 1, 1, '2025-07-01', '15:40:00', '15:45:00', 1),
(17, 1, 1, '2025-07-05', '07:13:00', '07:20:00', 1),
(18, 1, 1, '2025-07-09', '17:23:00', '17:26:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `created_at`) VALUES
(1, 'Kelas 10 IPA', '2025-01-23 02:25:14'),
(2, 'Kelas 10 IPS', '2025-01-23 02:25:14'),
(3, 'Kelas 11 IPA', '2025-01-23 02:25:14'),
(4, 'Kelas 11 IPS', '2025-01-23 02:25:14'),
(5, 'Kelas 12 IPA', '2025-01-26 03:37:59'),
(6, 'Kelas 12 IPS', '2025-01-26 03:38:16'),
(8, 'X TKJ 1', '2025-07-10 17:04:34'),
(9, 'XI TKJ 2', '2025-07-11 04:47:18'),
(10, 'XII TKJ 2', '2025-07-11 04:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`) VALUES
(1, 1, 1),
(2, 1, 3),
(4, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `grade`) VALUES
(1, 1, 1, 85.50),
(4, 1, 4, 88.00);

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `title`, `description`, `amount`, `created_at`) VALUES
(3, 'Gaji Guru Tetap', 'Gaji Guru Tetap 2025', 1000000.00, '2025-03-07 04:01:32'),
(4, 'Gaji Guru Tidak Tetap', 'Gaji Guru Tidak Tetap', 4000000.00, '2025-03-07 04:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` varchar(255) DEFAULT NULL,
  `administrasi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `amount`, `date`, `keterangan`, `administrasi_id`) VALUES
(1, 1, 2000000.00, '2025-02-28 07:39:43', 'Lunas', 1),
(2, 1, 50000.00, '2025-07-11 01:22:34', 'Lunas', 2),
(3, 3, 500000.00, '2025-09-24 11:48:31', 'Lunas', 3);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `class_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nisn` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `status_keluarga` varchar(50) DEFAULT NULL,
  `anak_ke` int(11) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nomor_telepon` varchar(15) DEFAULT NULL,
  `sekolah_asal` varchar(100) DEFAULT NULL,
  `diterima_di_kelas` varchar(10) DEFAULT NULL,
  `tanggal_diterima` date DEFAULT NULL,
  `nama_orang_tua` varchar(100) DEFAULT NULL,
  `alamat_orang_tua` text DEFAULT NULL,
  `nama_wali` varchar(100) DEFAULT NULL,
  `alamat_wali` varchar(255) DEFAULT NULL,
  `pekerjaan_wali` varchar(100) DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `username`, `class_id`, `created_at`, `nisn`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `status_keluarga`, `anak_ke`, `alamat`, `nomor_telepon`, `sekolah_asal`, `diterima_di_kelas`, `tanggal_diterima`, `nama_orang_tua`, `alamat_orang_tua`, `nama_wali`, `alamat_wali`, `pekerjaan_wali`, `nama_ayah`, `nama_ibu`, `pekerjaan_ayah`, `pekerjaan_ibu`, `profile_picture`) VALUES
(1, 'Elvisrafi', 'siswa1', 1, '2025-01-30 16:52:45', '0083275807', 'Sidumulyo', '2009-08-05', 'P', 'Islam', 'Anak Kandung', 1, 'Jl. Panglima Minal RT 1 / RW 1, Senggoro Kec. Bengkalis Kab. Bengkalis 28751', '089623339246', '-', 'X AKL', '2024-07-01', 'Herry Irawa', 'Jl. Panglima Minai RT 1 / RW 1, Senggoro Kec. Bengkalis Kab. Bengkalis 28751', '-', '-', '-', 'HERRY IRAWA', 'LENI MARLINA', 'Karyawan Swasta', 'Tidak bekerja', 'profile_68580d2b8fccd.jpg'),
(3, 'Siswa C', 'siswa3', 2, '2025-01-30 16:52:45', '0083275810', 'Pekanbaru', '2002-12-12', 'L', '-', '-', 1, '-', '-', '-', '-', '2024-12-20', '-', '-', '-', '0', '-', '-', '-', '-', '-', NULL),
(6, '20202512', '20202512', 1, '2025-02-04 07:16:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '20212412', '20212412', 1, '2025-02-04 07:16:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'rafi', '212121', 1, '2025-02-04 07:44:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'elvis', '22222', 1, '2025-02-04 07:44:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Kirigaya', 'siswa11', 6, '2025-02-06 14:38:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Reza', 'a1a1', 1, '2025-07-10 16:31:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Ezra', 'a2a2', 1, '2025-07-10 16:31:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Elvisrafi', 'Rafi2', 1, '2025-09-26 07:44:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `kategori` enum('A','B') DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `created_at`, `kategori`) VALUES
(1, 'Matematika', '2025-01-23 02:25:25', 'A'),
(3, 'Fisika', '2025-01-23 02:25:25', 'A'),
(4, 'Kimia', '2025-01-23 02:25:25', 'A'),
(5, 'Teknik Jaringan', '2025-07-10 17:13:42', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_schedule`
--

CREATE TABLE `teacher_schedule` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_schedule`
--

INSERT INTO `teacher_schedule` (`id`, `teacher_id`, `subject_id`, `class_id`, `start_time`, `end_time`) VALUES
(1, 2, 3, 6, '19:30:00', '21:30:00'),
(3, 2, 1, 1, '08:00:00', '09:00:00'),
(4, 2, 3, 1, '13:00:00', '15:00:00'),
(5, 2, 5, 8, '08:30:00', '09:30:00'),
(6, 10, 5, 1, '01:30:00', '03:15:00'),
(7, 2, 4, 1, '09:30:00', '10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa','administrasi','kepala_sekolah') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `encrypted_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`, `encrypted_password`) VALUES
(1, 'admin1', '$2y$10$62mg6pZI9CKcgpiQfxzVCOo0NE6KS/UusPKbKp4TWxbtQW8CxW3Cy', 'admin', 'active', 'aBG9DuVHRNEf7+JCwprRt2nwTdIc4DQbdPyvqNRI+Yu31yUBNHNZwCYpC0qm4PStORCcgzqaywsmFlfXbxpnvg=='),
(2, 'guru1', '$2y$10$LB2k3oLZPACskU4aqm/AyuqJrxqE1/6XKys3kifri2oVM/dajk9xq', 'guru', 'active', 'RFc8LP9iNLUwEO1Y851ZO53B3OFdCtXdoKkdluT1ZMZh8jUA65o/K0jmGlruDNyfP6s8kz25xdqnxm2i2idKIg=='),
(3, 'siswa1', '$2y$10$HKucmueQEIY9g.6pYubokOL61Nk4LQBKHsbddfXvnrJZywa8bkB8.', 'siswa', 'active', 'Jli7TSxOGfLPoNzst4NHZ3neF9cMPYi5M04BQwZrBSKY20rJwme46tvl02nMxBEnwUXpdCc7RJEDS8oS8ChThg=='),
(4, 'administ1', '$2y$10$SuWtEYLAGxHhA56x7f0ejek.2akZggMGsU6HCAeQQcn0mnV4YbOdm', 'administrasi', 'active', 'i+JxLOfJKwjqgVeRt3DiIem5thSnSiqZHgmvmE2N03lCGlWls7k4UjwodCsUhZDlrA+m3wJuoWme+CX8K2LgFw=='),
(5, 'admin', '$2y$10$Eun9j./2ZhdR.rYGCvdkX.4LGML0yM2oPu3pY1xCZUjOviLxDHpu.', 'admin', 'inactive', 'pQblmVaeXhBJmNoeND7fe18wYyArHZKCmDBoa8hmYbGPOwpLhaGcJlOw0471dUt9ZvgGxp+DxOgFEaYgF+VMjQ=='),
(7, 'siswa3', '$2y$10$aLNnATkDUtGSnr3MwF.h9edWh5bFU5dueJ0scKHnB8lbIBSMAtHDi', 'siswa', 'active', '9ITtUkdpn+OmlNrbRdsn0iIQBuDGv8L2cf4jOS+Lbhw6ZAE3eFVGv7jGejBav36UGZhSwZ+tyVG7bJVMZ4Te5Q=='),
(8, 'siswa4', '$2y$10$FlDSFUb5e.P2ctzpqv/TTu4Nan5IUtCtqCcnTkCZZRG4gh1Esvjnq', 'siswa', 'inactive', 'stt7VwbYh9NkyVtXa8tCZ+coaqLWUZAdmOK/YsRp/v4sD5Pnvs0YPMiVhs3u3NrzQBx5PmWpUnU9Wa5+4p8esA=='),
(10, 'guru2', '$2y$10$4OaMbvd379xDjvNfVuOyXOW03KixXlywIcD0Mynxr0rJpTYLYnHiG', 'guru', 'active', 'hq4J0eQWefGvL5NR0QauJCSytMxepG36DBqWJ+1KUfiyhSc2T1FOMBWrYQw15XczQEJzUQ+nbVJ6I0/qrRDcqg=='),
(19, '20202512', '$2y$10$XHBXx3eccoR3kke.DLEfOeBkWFdEG3MLKQINYKhrq4SGqBh6LvGja', 'siswa', 'active', NULL),
(20, '20212412', '$2y$10$EryY/QJglzGl7VLZRKhZ3.SqgYlmiVFHV2phDF4/8y3DSL7vtBgly', 'siswa', 'active', NULL),
(21, '1122112', '$2y$10$NFQaKnYBtPbthUndtTxpt.yRqq8yrsIkTL5CNVcsZXqfGc7eq0HoW', 'administrasi', 'active', NULL),
(22, 'siswa7', '$2y$10$HkH0ioqStmPSavwllfoBaeWCHHazp/4wy0ziJrG75fVHk1W2i4m36', 'siswa', 'active', NULL),
(27, '212121', '$2y$10$xPiEwx7oVYSyMYCVfZ/x2uke2TCNHKyy5oBQUcoM01jUIv2vEAaay', 'siswa', 'active', NULL),
(28, '22222', '$2y$10$spkkFof2jCZVd1lJp1zi6.1PU0Je8.mugCfx7q0rfuI1Xe5cIK6Jm', 'siswa', 'active', NULL),
(29, '242324', '$2y$10$H0gy79CXOa17OBnODosjCOv.8lkC7CYjk7JqjdzrgKg7TyS.3UC7O', 'guru', 'active', NULL),
(30, '454543', '$2y$10$gy4Gkyw67q2TwaA9WZKstuZl22.ehg1A54dfNBiqD1mr5lSYz7DSK', 'administrasi', 'active', NULL),
(31, 'siswa2', '$2y$10$23xmd7BSKvxCDVuyjHSRVeZPsXvJPFk.6hP0diRyd9/ySufPH.1zS', 'siswa', 'active', NULL),
(32, 'siswa11', '$2y$10$pcHW9oE1orQK1dYq1cNHSu9SStz/QSpiPn1fBypwQVwmC0KXeU9Lu', 'siswa', 'active', NULL),
(39, 'a1a1', '$2y$10$fgR3SIvj.kFKO0ZogXQM8uVwKNdDOTxRzn9n4fB2OeRStTZiS4.9O', 'siswa', 'active', 'gnKFgXWLKR/w1CiU+YYCiGE/lD9vfOsnaRfRivgbuc/vjxLDfiPu0agdxHpSjGr0toDq6mMPIut4x1LbxN4x3g=='),
(40, 'a2a2', '$2y$10$96gjw3qtzVetgpZnjnFeOO0sAmlbQJb5qOcWJstONezUZvKSGk8B2', 'siswa', 'active', 'qG9Gsr+jN0m4ShxY5BmZ/ixTKm+v+iAgHQLkyLPzRfKX74eCTAJNfeQZ82UOGW9OZEeMk2kC9DTmH85LZepjQQ=='),
(41, 'a3a3', '$2y$10$fFeAIcLKc89nVmeH9yihXuJMmojW8gT7rCxpfBhdvFBpKME5AQOKG', 'guru', 'active', '3eiotaSaKDa9BNY1qdXZ/VZigNnEct7sK8Ta+ABSGxLJXbrEYU81+mQxt35JXkoYccMfvBZPiHOok21ITx8H9A=='),
(45, 'kepala', '$2y$10$FNe/G3q3CcSlfyjreQaQzuhm.KgiztykM4exIsruE0q5Ehl9e.5KO', 'kepala_sekolah', 'active', 'R/QRF2fKzmby/d9WKDQWAokDWHo/mrBWUuTioOOBlkOdRY+lk1Jn9+PHHD0fAfzqJfd887bAiYU3ik1qqoEpJA=='),
(46, 'Rafi2', '$2y$10$fOnBasHvYJVjlSYyvJNP3ecqC76wOG/tlSXJLvc0h/lJX7lv1d7K.', 'siswa', 'active', 'BPJT2HTszw3aeeKUM9uDMdBUqUBNW28OF9Uybt9aoUKKyaWXNul5zj8CopblxaBB6ZKI8DlW/BAq/Vc6K6OI3w=='),
(47, 'Rafi3', '$2y$10$pIGjKxfawTL3T8Ciio4qjuS5NB0YZ2QXTfZrpXisggKwUKZDzHcHS', 'guru', 'active', 'Fw0tvYxpnAoxYAvrWUMkSFIPdeYCHfUc8sbOyzP48Suush1+4KhfPALKaIQORv5fCAvG+SgdS4ks5JKsxhug+w==');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `administrations`
--
ALTER TABLE `administrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_attendance_session` (`attendance_open_id`);

--
-- Indexes for table `attendance_open`
--
ALTER TABLE `attendance_open`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_payments_administrations` (`administrasi_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_schedule`
--
ALTER TABLE `teacher_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `administrations`
--
ALTER TABLE `administrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `attendance_open`
--
ALTER TABLE `attendance_open`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_schedule`
--
ALTER TABLE `teacher_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`attendance_open_id`) REFERENCES `attendance_open` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attendance_open`
--
ALTER TABLE `attendance_open`
  ADD CONSTRAINT `attendance_open_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_open_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_administrations` FOREIGN KEY (`administrasi_id`) REFERENCES `administrations` (`id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_schedule`
--
ALTER TABLE `teacher_schedule`
  ADD CONSTRAINT `teacher_schedule_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `teacher_schedule_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `teacher_schedule_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
