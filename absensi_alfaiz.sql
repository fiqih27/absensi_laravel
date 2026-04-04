-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 03:02 AM
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
-- Database: `absensi_alfaiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','late','permission') NOT NULL DEFAULT 'present',
  `note` text DEFAULT NULL,
  `verification_method` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `broadcast_histories`
--

CREATE TABLE `broadcast_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `broadcast_id` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` varchar(255) NOT NULL,
  `total_recipients` int(11) NOT NULL,
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `recipients_detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipients_detail`)),
  `failed_recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`failed_recipients`)),
  `status` varchar(255) NOT NULL DEFAULT 'processing',
  `notes` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `broadcast_histories`
--

INSERT INTO `broadcast_histories` (`id`, `broadcast_id`, `message`, `recipient_type`, `total_recipients`, `sent_count`, `failed_count`, `recipients_detail`, `failed_recipients`, `status`, `notes`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(6, 'BROADCAST_X9ETUINA_1775100634', 'nbkjhjhkjhjk', 'all', 2, 2, 0, '[{\"id\":3,\"name\":\"jokowi\",\"phone\":\"08967878999\",\"student_name\":\"fiqih kurniadi\",\"status\":\"sent\",\"sent_at\":\"2026-04-02 10:30:35\",\"message_id\":\"wamid.HBgMNjI4OTY3ODc4OTk5FQIAERgSNEJBODc0QTBBOTJGMUQyQjhFAA==\"},{\"id\":4,\"name\":\"jokowi\",\"phone\":\"08963665110\",\"student_name\":\"prabowo\",\"status\":\"sent\",\"sent_at\":\"2026-04-02 10:30:36\",\"message_id\":\"wamid.HBgMNjI4OTYzNjY1MTEwFQIAERgSQUE1NDdGNDkzNUMwOUUzQTkwAA==\"}]', '[]', 'completed', 'Broadcast selesai: 2 berhasil, 0 gagal', '2026-04-02 03:30:36', '2026-04-02 03:30:36', '2026-04-02 03:30:36', '2026-04-02 03:30:36'),
(7, 'BROADCAST_ZBXPNJZ7_1775101087', 'wdwdwdwdwddwdwdwdwdwwdwddw', 'all', 1, 1, 0, '[{\"id\":\"broadcast_number\",\"name\":\"WhatsApp Kesiswaan\",\"phone\":\"6289636651100\",\"student_name\":\"Broadcast\",\"status\":\"sent\",\"sent_at\":\"2026-04-02 10:38:07\",\"message_id\":\"wamid.HBgNNjI4OTYzNjY1MTEwMBUCABEYEjIxNzQ2MjJBQjc3RTc3RTY4QQA=\"}]', '[]', 'completed', 'Dikirim ke nomor broadcast: 6289636651100. Data mencakup 2 siswa (2 aktif).', '2026-04-02 03:38:07', '2026-04-02 03:38:07', '2026-04-02 03:38:07', '2026-04-02 03:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-send_notification', 'b:0;', 1775186829),
('laravel-cache-whatsapp_broadcast_number', 's:13:\"6289636651100\";', 1775186829);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `device_name` varchar(50) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `port` int(11) NOT NULL DEFAULT 4370,
  `serial_number` varchar(255) DEFAULT NULL,
  `status` enum('online','offline') NOT NULL DEFAULT 'offline',
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_28_065637_create_students_table', 1),
(5, '2026_03_28_065638_create_devices_table', 1),
(6, '2026_03_28_065638_create_parents_table', 1),
(7, '2026_03_28_070123_create_attendances_table', 1),
(8, '2026_03_28_070320_notif', 1),
(9, '2026_04_01_094852_create_broadcast_histories_table', 2),
(10, '2026_04_02_133210_create_whatsapp_conversations_table', 3),
(11, '2026_04_02_133210_create_whatsapp_messages_table', 3),
(12, '2026_04_02_134749_create_personal_access_tokens_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attendance_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_phone` varchar(15) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `wa_response` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `student_id`, `name`, `phone`, `email`, `created_at`, `updated_at`) VALUES
(3, 4, 'jokowi', '08967878999', 'fiqihkurniadi2003@gmail.com', '2026-04-02 03:29:13', '2026-04-02 03:29:13'),
(4, 5, 'jokowi', '08963665110', 'fiqihkurniadi2003@gmail.com', '2026-04-02 03:30:01', '2026-04-02 03:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('meT6nhA5dqBhSIXxgv2AIPMb7zO7HTHDtTPsHeSu', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIxcGFMRlVhTzNLS1duU2c0WWt4bHg4aGp2b01OdUI1U2kyUmVTNDB0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC93aGF0c2FwcC1jaGF0XC9tZXNzYWdlc1wvOSIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1775264512),
('tqF1m07wcybhrmrS7DwIvO5DESnsfiDptfr5w7BK', NULL, '192.168.21.81', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIxSjNEcFhrZGhFalB3aXBZT1lDR0NPbVhFckp4dGdrWHBLUkg4Sm1kIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzE5Mi4xNjguMjAuNDA6ODAwMFwvd2hhdHNhcHAtY2hhdFwvbWVzc2FnZXNcLzkiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1775264527);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  `fingerprint_uid` varchar(20) DEFAULT NULL,
  `device_user_id` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `nisn`, `name`, `class`, `fingerprint_uid`, `device_user_id`, `status`, `created_at`, `updated_at`) VALUES
(4, '12345678', 'fiqih kurniadi', '3', 'a123', '123', 'active', '2026-04-02 03:28:55', '2026-04-02 03:28:55'),
(5, '1234', 'prabowo', '3', '654', '123', 'active', '2026-04-02 03:29:45', '2026-04-02 03:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_conversations`
--

CREATE TABLE `whatsapp_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `unread_count` int(11) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_conversations`
--

INSERT INTO `whatsapp_conversations` (`id`, `phone_number`, `contact_name`, `last_message`, `last_message_at`, `unread_count`, `is_archived`, `created_at`, `updated_at`) VALUES
(9, '6285709298897', '6285709298897', 'Ada yang ingin ditanyakan lagi, kak? 😊🙏🏻', '2026-04-04 00:43:56', 0, 0, '2026-04-02 08:55:15', '2026-04-04 00:43:58');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_messages`
--

CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `direction` enum('incoming','outgoing') NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_messages`
--

INSERT INTO `whatsapp_messages` (`id`, `conversation_id`, `message_id`, `direction`, `message`, `status`, `sent_at`, `created_at`, `updated_at`) VALUES
(29, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYIEE1MzM2QjcyMTAzNkNFQzYwMDY4MDc0NzZBQTY1REZDAA==', 'incoming', 'Assalamualaikum', 'delivered', '2026-04-02 08:55:15', '2026-04-02 08:55:15', '2026-04-02 08:55:15'),
(30, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEkJDNDk3M0ZCQTMyQkFFRDRBRQA=', 'outgoing', 'waalaikumsallam', 'sent', '2026-04-02 08:55:26', '2026-04-02 08:55:26', '2026-04-02 08:55:26'),
(31, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjBFOTRFNEJEM0M0MkYyRjE4NjMA', 'incoming', 'Waalaikumsalam kak! Ada yang bisa saya bantu? 😊🙏🏻', 'delivered', '2026-04-02 08:55:30', '2026-04-02 08:55:30', '2026-04-02 08:55:30'),
(32, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjEyNjgyNjNFOEM0QkMxNUI2OAA=', 'outgoing', 'ini kak saya pengen beli pesawat terbang]', 'sent', '2026-04-02 08:55:49', '2026-04-02 08:55:49', '2026-04-02 08:55:49'),
(33, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjBFNTEzMzU2QTkwMjE4QzQxRjkA', 'incoming', 'Maaf kak, saya tidak bisa membantu untuk pembelian pesawat terbang. Tapi jika ada pertanyaan lain tentang layanan Al Faiz, silakan tanya ya! 😊🙏🏻', 'delivered', '2026-04-02 08:55:53', '2026-04-02 08:55:53', '2026-04-02 08:55:53'),
(34, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEkREMDAzOTZEN0IzQTk5REM5QgA=', 'outgoing', 'bedanya to skd dan cpns apa kak', 'sent', '2026-04-02 09:14:08', '2026-04-02 09:14:08', '2026-04-02 09:14:08'),
(35, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjAzN0Y1MDg4ODlCOUIwMjc0ODIA', 'incoming', 'Tentu kak! TO SKD adalah tryout untuk seleksi kompetensi dasar yang biasanya digunakan dalam proses penerimaan CPNS. Sedangkan CPNS itu sendiri adalah Calon Pegawai Negeri Sipil, yaitu mereka yang mengikuti seleksi untuk menjadi pegawai negeri. Jadi, TO SKD itu adalah bagian dari persiapan untuk mengikuti ujian CPNS. Jika ada yang ingin ditanyakan lebih lanjut, silakan kak! 😊🙏🏻', 'delivered', '2026-04-02 09:14:16', '2026-04-02 09:14:16', '2026-04-02 09:14:16'),
(36, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEkRGODE5NzYyMUE1ODQwNThERAA=', 'outgoing', 'owalah oke terimakasih daftar nya gimna ya kak', 'sent', '2026-04-02 09:42:27', '2026-04-02 09:42:27', '2026-04-02 09:42:27'),
(37, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjA0OTJBNjk2M0Q1OEU0RTk2NTAA', 'incoming', 'Sama-sama kak! Untuk mendaftar, kakak bisa kunjungi website kami. Berikut langkah-langkahnya:\n\n1. Login atau Daftar di website.\n2. Mulai Belajar.\n3. Pilih Kelas atau Tryout yang diinginkan.\n4. Klik Beli Sekarang.\n5. Lakukan pembayaran via Qris, VA, atau Transfer.\n\nKalau kakak mengalami kendala, jangan ragu untuk bertanya ya! 😊🙏🏻', 'delivered', '2026-04-02 09:42:32', '2026-04-02 09:42:32', '2026-04-02 09:42:32'),
(38, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjIwOERGNTRBNkM4ODQ1NDdFQgA=', 'outgoing', 'alamat website nya apa ya kak', 'sent', '2026-04-02 09:42:55', '2026-04-02 09:42:55', '2026-04-02 09:42:55'),
(39, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjBBMjA0RDk0MkEwNDg4MjZCOTgA', 'incoming', 'Tentu kak! Berikut alamat website untuk mendaftar sesuai dengan program yang kakak pilih:\n\n- SKD CPNS: casn.privatalfaiz.id\n- UTBK SNBT: utbk.privatalfaiz.id\n- Tes BUMN: bumn.privatalfaiz.id\n\nSilakan buka sesuai program yang kakak minati ya! 😊🙏🏻', 'delivered', '2026-04-02 09:43:00', '2026-04-02 09:43:00', '2026-04-02 09:43:00'),
(40, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjZDMzVDRUEzRDhBMDUzQzQwRgA=', 'outgoing', 'p', 'sent', '2026-04-04 00:07:50', '2026-04-04 00:07:50', '2026-04-04 00:07:50'),
(41, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjg4NjM1NkY5MEYyNTNBQzJEMwA=', 'outgoing', 'p', 'sent', '2026-04-04 00:08:41', '2026-04-04 00:08:41', '2026-04-04 00:08:41'),
(42, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjZGREEwMEY1RDNCNDQ5MDY0MwA=', 'outgoing', 'p', 'sent', '2026-04-04 00:09:04', '2026-04-04 00:09:04', '2026-04-04 00:09:04'),
(43, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYIEE1QkI3MEM0RjBDNzlEMEY4NUYzQzIwREY1MTZCMTI2AA==', 'incoming', 'P', 'delivered', '2026-04-04 00:09:22', '2026-04-04 00:09:22', '2026-04-04 00:09:22'),
(44, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjJDNjJEREFGMUU4OTQyODRDNQA=', 'outgoing', 'p', 'sent', '2026-04-04 00:09:33', '2026-04-04 00:09:33', '2026-04-04 00:09:33'),
(45, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjJDNkFGMTc3ODlCNUZFODdCMAA=', 'outgoing', 'p', 'sent', '2026-04-04 00:09:51', '2026-04-04 00:09:51', '2026-04-04 00:09:51'),
(46, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjc2MUFDMDMzQjFBNUIyMDg2NwA=', 'outgoing', 'test', 'sent', '2026-04-04 00:36:01', '2026-04-04 00:36:01', '2026-04-04 00:36:01'),
(47, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYIEE1RkJCOTkwMTNDQjA2NTFFQTcxNkI5RjI4RjU4MDA4AA==', 'incoming', 'Oke', 'delivered', '2026-04-04 00:36:08', '2026-04-04 00:36:08', '2026-04-04 00:36:08'),
(48, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABEYEjg0NDBCQUM1Q0VGQjJCN0IyOAA=', 'outgoing', 'p', 'sent', '2026-04-04 00:40:07', '2026-04-04 00:40:07', '2026-04-04 00:40:07'),
(49, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYIEE1QzM0RkIxODgxRDAyOUE4MUFDQjQ0MTBBMkY3NUY1AA==', 'incoming', 'Siapp', 'delivered', '2026-04-04 00:40:13', '2026-04-04 00:40:13', '2026-04-04 00:40:13'),
(50, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjAxQTMyNjhERDJCQTc1NDg4NTYA', 'incoming', 'Halo kak! Apakah ada yang ingin ditanyakan atau perlu bantuan? 😊🙏🏻', 'delivered', '2026-04-04 00:43:55', '2026-04-04 00:43:55', '2026-04-04 00:43:55'),
(51, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjBGRUNCNkMwRDg4MTkwOUVFMTgA', 'incoming', 'Ada yang ingin kakak tanyakan lebih lanjut? 😊🙏🏻', 'delivered', '2026-04-04 00:43:55', '2026-04-04 00:43:55', '2026-04-04 00:43:55'),
(52, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjA1NDhBNDE0QTNGQzc1RTlFMjIA', 'incoming', 'Ada yang bisa saya bantu lebih lanjut, kak? 😊🙏🏻', 'delivered', '2026-04-04 00:43:56', '2026-04-04 00:43:56', '2026-04-04 00:43:56'),
(53, 9, 'wamid.HBgNNjI4NTcwOTI5ODg5NxUCABIYFjNFQjBGQzgxM0M4NEVGOTcxMDUzNkIA', 'incoming', 'Ada yang ingin ditanyakan lagi, kak? 😊🙏🏻', 'delivered', '2026-04-04 00:43:56', '2026-04-04 00:43:56', '2026-04-04 00:43:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_student_id_foreign` (`student_id`),
  ADD KEY `attendances_device_id_foreign` (`device_id`);

--
-- Indexes for table `broadcast_histories`
--
ALTER TABLE `broadcast_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `broadcast_histories_broadcast_id_unique` (`broadcast_id`),
  ADD KEY `broadcast_histories_status_created_at_index` (`status`,`created_at`),
  ADD KEY `broadcast_histories_broadcast_id_index` (`broadcast_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_attendance_id_foreign` (`attendance_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parents_student_id_foreign` (`student_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_nisn_unique` (`nisn`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `whatsapp_conversations`
--
ALTER TABLE `whatsapp_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_conversations_phone_number_unique` (`phone_number`);

--
-- Indexes for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_messages_conversation_id_foreign` (`conversation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `broadcast_histories`
--
ALTER TABLE `broadcast_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_conversations`
--
ALTER TABLE `whatsapp_conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD CONSTRAINT `whatsapp_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `whatsapp_conversations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
