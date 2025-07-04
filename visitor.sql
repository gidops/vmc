-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2025 at 11:36 PM
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
-- Database: `visitor`
--

-- --------------------------------------------------------

--
-- Table structure for table `cso`
--

CREATE TABLE `cso` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cso`
--

INSERT INTO `cso` (`id`, `email`, `password_hash`) VALUES
(1, 'Giddy', '$2y$10$M0fUC5Mf6I4H5rz4aB29e.a6iWMBrVV2EboHYTHAeR6nB1DntXp9m'),
(2, 'sgt.pro501@gmail.com', '$2y$10$gFebHt6Xfc3kHbSCz9EYwOE.Tb89Z3JlyZe2AsYLyAPHHoFoD8J9C'),
(3, 'ugorjigideon2@gmail.com', '$2y$10$i.LPBAP6dnPEOlO08ehuTO1SrBV443uDUQvnpWz7GwTPofY48JDJ.'),
(4, 'ugorjigideon@outlook.com', '$2y$10$AoS4urT5yJqdJx8.zccrqOLbsKcbZ4HkUGoz2m2n5r.vTwu2PiFI2'),
(5, 'pengagon@gmail.com', '$2y$10$kLdqcFYhYxJCnmdzjz26P.tAHrl25efpSUK9a8W41SM6KGWS9DMWi'),
(6, 'johndumelo228@gmail.com', '$2y$10$BtpG/LI5UHLq6z87m/hMxO0bJP1p4.nuM2nxehaAtGY4GSlgDpzr2'),
(7, 'luckydio500@gmail.com', '$2y$10$IBAICIxollYad2oykRXmTeoC6OBe7dsvcxrkIJ4eNXBl/k2QI7JDe'),
(8, 'luckydio900@gmail.com', 'We12345678@');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(2, 'Gideon', 'ugorjigideon@outlook.com', '$2y$10$OOEzNuMhllWVwyPlIGRKBu.COSi1qhEt044hR4WW6Rmm9WtO.1Q7W', '2025-04-02 01:30:48'),
(3, 'lucky', 'pengagon@gmail.com', '$2y$10$S8.rte/8NfCauEL0Su5I..vm3tEu0eK7JTvEwlXIiJ6iNUehQnlVG', '2025-04-02 01:36:41'),
(4, 'Lucky Wenapere', 'luckydio10@gmail.com', '$2y$10$O1W67l08IfyQmjrE/XnUzu400I9ejPO0lwWv62JGiLap3/6yPo6mG', '2025-04-02 11:10:36'),
(5, 'Cephas', 'dambi032@gmail.com', '$2y$10$mYb5Mm0joArr4YS57lXCz.87OMBgctXswDHdfX1RhwQWCcS.q9c3q', '2025-04-02 11:58:06'),
(6, 'Tochi', 'verizon@gmail.com', '$2y$10$JxNzvav9g2d9jalgiTlEq.vgn8/sSk97CAupQYkjq2Q7l8UANluFG', '2025-04-02 12:36:07'),
(7, 'Lizzy', 'earnestelizabeth166@gmail.com', '$2y$10$TyqsP3V1TdGLt/z/B3eaAOD5Kc0BQ8IfbObXquWi1UrpqWtIfA5Ba', '2025-04-02 15:31:42'),
(8, 'ebube', 'sgt.pro501@gmail.com', '$2y$10$BQoAByKkBjSjWEovZKfUZe8QfEBKfvo3hUQXigzJcgz2PMHLsc8U6', '2025-04-04 09:15:51'),
(9, 'Gidops', 'gidops@gmail.com', '$2y$10$rbHt6FV492P0ND39tjzUmebh7QpW/Ja45pX7.ID5dcG9r7OT3vuce', '2025-04-04 12:06:38'),
(10, 'lucky', 'lucy@gmail.com', '$2y$10$rHFeq6TSwv9ick2bqSUC2eqbnQ0WHUBcZ4vFIbv0IXSkCZBmRCaTy', '2025-04-04 12:24:39'),
(11, 'engr richard', 'ben@gmail.com', '$2y$10$S7kh0ArCtGwUQoN1oFepOeeV0wojg7ZS1OD0zaMtbBzb.PviWtRn.', '2025-04-04 12:58:22'),
(12, 'engr richard', 'lucky@gmail.com', '$2y$10$Vwwe5M9E0Yim0l4ExE5wM.QeWLlYv7ik7FZ1ZsebcdTA6kT9S.cve', '2025-04-04 13:00:58'),
(13, 'lucky', 'luckyy@gmail.com', '$2y$10$ntoACFt9cCklJ.smrRXzNOPIdyoYkJkeCZ0iiSxJeRECoT1CPwuzm', '2025-04-04 13:08:50'),
(14, 'lucky2', 'lucky2@gmail.com', '$2y$10$2QUh4lgwLQiD.X4yYKCv7OrptAYLvkS9AIo1hjb7OtJBcFI/9TvLi', '2025-04-04 13:14:21'),
(15, 'john doe', 'johndumelo228@gmail.com', '$2y$10$CZGLiRkYsZj5LGpCJNSPGuKuOfXdZ5IOhK7vG/XA7XyYzT1HETbjO', '2025-04-04 18:52:38'),
(16, 'ebube', 'ozioma@gmail.com', '$2y$10$6j/CCHWsj4oWIlXwZuWSMuTkHleXU3Gi5zCUlWTq8D.dDD4s0l7Xi', '2025-04-04 22:18:04'),
(17, 'Gidops', 'john@example.com', '$2y$10$kMTR5EFNDWMyGf/Tpn3bxe9zsxBvV1ca/DYNfkQBKqHQxediTVJ8u', '2025-04-05 12:17:41'),
(18, 'Success Elton', 'success@gmail.com', '$2y$10$MD0k8EP11.1AsMo4VKmWo.xoESEmCx8nHO.Pb4Up6hi/VXLnQkvFO', '2025-04-05 13:42:59'),
(19, 'idara', 'giddy@gmail.com', '$2y$10$RtclhXAY16YlD6AwpoV9vOQ4rHePVpXINaETJ58IBfoFtEHyyLHnq', '2025-04-06 08:22:43'),
(20, 'lucky staff', 'steve@gmail.com', '$2y$10$ylDx7Aw7A9Uob3ONRxKwfO3xX/3yS8Vs2aXEluYHda5L05NYIDlhK', '2025-04-06 08:48:57'),
(21, 'mr jean', 'mrjean@gmail.com', '$2y$10$XzLNXnW1LGw7SeQfhgLEjOnmbc5kfJGZ.lKB543H4WVeUY7c0pvhC', '2025-04-06 09:57:51'),
(22, 'lucky gideon', 'luckygideon@gmail.com', '$2y$10$C57H.Z7yEa0rtvHh0YU0ueb9c6KtY0pVN/e8r2mOXFjiizFzMGuK.', '2025-04-06 12:54:10'),
(23, 'Emmanuel Jean', 'jeanboski@gmail.com', '$2y$10$mzGieqOoVab26Y2iaO2oQuMwjWqY2KVoa6cnAYu5pueV7oHDA1zZK', '2025-04-06 14:15:28'),
(26, 'lucky', 'luckydio100@gmail.com', '$2y$10$mVIJpXNrsVe9EtU7rEkXP.6D0bsMEEEJej7jUbWerXchh97u/g7N.', '2025-04-12 18:35:14'),
(27, 'idara', 'luckydio700@gmail.com', '$2y$10$SmecSD5feOI6aAnylp0na.ilCJflSCwdYaQY1gm9MT6I9vHYrR2Qq', '2025-04-13 09:29:15'),
(28, 'lucky', 'luckydio600@gmail.com', '$2y$10$NBG9bzL2mybogK/HpJBzuOT5oNC.x6shs.sv/uo5RGA6woRxy8P96', '2025-04-13 09:30:26'),
(29, 'Luckyy', 'luckydio1000@gmail.com', '$2y$10$up1oao9ljRxIFZV/bhZyruepItC4JU9mPUTo8K7hbRGnadzRAwJL6', '2025-04-13 11:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `host_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `host_id` int(11) NOT NULL,
  `arrival_date` date DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `organization` varchar(255) NOT NULL,
  `visit_date` date DEFAULT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `name`, `host_name`, `phone`, `email`, `status`, `qr_code`, `created_at`, `employee_id`, `host_id`, `arrival_date`, `arrival_time`, `organization`, `visit_date`, `reason`) VALUES
(1, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67ebf3f540f93', '2025-04-01 14:10:16', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(2, 'Ebubechukwu Ugorji', '', '09020280436', 'gidowalski@gmail.com', 'approved', 'QR-67ebf4b6aa51d', '2025-04-01 14:13:50', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(3, 'james', '', '09020280436', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec0bcaa232e', '2025-04-01 15:52:21', NULL, 2, '2025-04-05', '04:34:16', '', NULL, ''),
(4, 'ebube', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ee8401dca24', '2025-04-01 16:09:50', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(5, 'tim', '', 'ug', 'gidowalski@gmail.com', 'approved', 'QR-67ee8407203e1', '2025-04-01 16:14:09', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(6, 'Ebubechukwu Ugorji', '', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67ee8a0588258', '2025-04-01 16:21:03', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(7, 'lucky', '', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec13ea387d6', '2025-04-01 16:26:56', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(8, 'Ebubechukwu Ugorji', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ec150844052', '2025-04-01 16:31:54', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(9, 'Ebubechukwu Ugorji', '', '09020280436', 'gidowalski@gmail.com', 'approved', 'QR-67ec1616859dc', '2025-04-01 16:36:25', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(10, 'Ebubechukwu Ugorji', '', '09020280436', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec9690ad9cc', '2025-04-01 16:38:52', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(11, 'Ebubechukwu Ugorji', '', '09020280436', 'gidowalski@gmail.com', 'approved', 'QR-67ec1867290bd', '2025-04-01 16:46:19', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(12, 'Ebubechukwu Ugorji', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ec1ac68e417', '2025-04-01 16:56:28', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(13, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ec1ee0b5734', '2025-04-01 17:13:53', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(14, ' Ugorji', '', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec2e4e5987e', '2025-04-01 18:19:45', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(15, 'Ebubechukwu Ugorji', '', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec329ba23bd', '2025-04-01 18:38:01', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(16, 'Ebubechukwu Ugorji', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67ec451209cc8', '2025-04-01 19:55:44', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(17, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee840eba5dc', '2025-04-01 20:01:13', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(18, 'Ebubechukwu Ugorji', '', '07067367057', 'gidowalski@gmail.com', 'approved', 'QR-67ec4a12ec615', '2025-04-01 20:18:12', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(19, 'gid', '', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec76dde338f', '2025-04-01 23:29:11', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(20, 'Ebubechukwu Ugorji', '', '09020280436', 'gidowalski@gmail.com', 'approved', 'QR-67ec77a122e7d', '2025-04-01 23:32:32', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(21, 'Ebubechukwu Ugorji', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67ec7b4e9404b', '2025-04-01 23:39:40', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(22, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'denied', NULL, '2025-04-01 23:43:02', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(23, 'Ebubechukwu Ugorji', '', '09020280436', 'sgt.pro501@gmail.com', 'approved', 'QR-67ec7b6c3b734', '2025-04-01 23:44:17', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(24, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'denied', NULL, '2025-04-01 23:47:40', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(25, 'Ebubechukwu Ugorji', '', '09020280436', 'sgt.pro501@gmail.com', 'denied', NULL, '2025-04-01 23:48:11', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(26, 'Ebubechukwu Ugorji', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ec7c78c75bd', '2025-04-01 23:53:12', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(27, 'Ebubechukwu Ugorji', '', '09031669648', 'sgt.pro501@gmail.com', 'denied', NULL, '2025-04-02 00:34:48', NULL, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(28, 'Ebubechukwu Ugorji', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ec95e1b56dc', '2025-04-02 01:41:06', 3, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(29, 'Lucky Wenapere', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67ed1b9b316fe', '2025-04-02 11:11:51', 4, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(30, 'Lucky Wenapere', '', '09058600082', 'kosisochukwuonwuliri@gmail.com', 'approved', 'QR-67ed2eab8a364', '2025-04-02 12:01:19', 5, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(31, 'Tochi', '', '07067367057', 'egbomtochi@gmail.com', 'approved', 'QR-67ed2f8e25afc', '2025-04-02 12:37:19', 6, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(32, 'Mr. Jean', '', '09023299324', 'ernestelizabeth166@gmail.com', 'approved', 'QR-67ed59908bd0a', '2025-04-02 15:35:02', 4, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(33, 'idara', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee78feb13fe', '2025-04-03 12:02:53', 1, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(34, 'engr richard', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ee7cb3cf9e5', '2025-04-03 12:18:47', 1, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(35, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee7eba10c95', '2025-04-03 12:27:23', 1, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(36, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee822e50107', '2025-04-03 12:41:44', 1, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(37, 'Success', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee90acd8a1c', '2025-04-03 13:21:44', 1, 0, '2025-04-05', '04:34:16', '', NULL, ''),
(38, 'Ck', '', '02344343', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee9b151d36f', '2025-04-03 14:26:54', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(39, 'Cka', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ee9b0b20c43', '2025-04-03 14:27:16', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(40, 'Success Elton', '', '07067367057', 'eltonsuccess3@gmail.com', 'approved', 'QR-67ee9d28962dc', '2025-04-03 14:37:15', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(41, 'Kelly', '', '0912288832', 'ugorjigideon@outlook.com', 'approved', 'QR-67eea6278cd14', '2025-04-03 15:15:08', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(42, 'Mrs Richard', '', '09765673233', 'luckydio10@gmail.com', 'approved', 'QR-67eea76584207', '2025-04-03 15:20:26', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(43, 'Ebubechukwu Ugorji', '', '07067367057', 'gidowalski@gmail.com', 'approved', 'QR-67ef926012cd7', '2025-04-04 08:03:21', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(44, 'engr richard', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67ef9483632a8', '2025-04-04 08:12:36', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(45, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67ef96fc9de1f', '2025-04-04 08:21:43', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(46, 'engr richard', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67ef9715e21aa', '2025-04-04 08:23:40', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(47, 'jean', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67ef996734fe9', '2025-04-04 08:33:23', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(48, 'Gideon', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67ef9d680988c', '2025-04-04 08:50:38', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(49, 'idara', '', '09031669648', 'gidowalski@gmail.com', 'approved', 'QR-67ef9f78d6ec5', '2025-04-04 08:59:16', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(50, 'idara', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67efa3cfc9d93', '2025-04-04 09:17:48', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(51, 'Joy Favor', '', '09031669648', 'sgt.pro501@gmail.com', 'approved', 'QR-67efa616e2886', '2025-04-04 09:27:20', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(52, 'Ebubechukwu Ugorji', '', '09020280436', 'sgt.pro501@gmail.com', 'approved', 'QR-67efa72648977', '2025-04-04 09:32:07', 1, 1, '2025-04-05', '04:34:16', '', NULL, ''),
(53, 'lucky', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67efdfaab2e58', '2025-04-04 13:33:00', 14, 14, '2025-04-05', '04:34:16', '', NULL, ''),
(54, 'idara', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67f02aefe6e12', '2025-04-04 18:53:51', 15, 15, '2025-04-05', '04:34:16', '', NULL, ''),
(55, 'Mr Emmanuel Jean', '', '09023299324', 'emmanuel.osede@Pentagonsecurities.net', 'approved', 'QR-67f05afd8b135', '2025-04-04 22:19:27', 16, 16, '2025-04-05', '04:34:16', '', NULL, ''),
(56, 'Dozie Etassie', '', '07067367057', 'dozietassie@pentagonsecurities.net', 'approved', 'QR-67f05fd74dea9', '2025-04-04 22:40:06', 16, 16, '2025-04-05', '04:34:16', '', NULL, ''),
(57, 'Mr Bright', '', '09031669648', 'ugorjigideon@outlook.com', 'denied', NULL, '2025-04-05 12:30:48', 17, 17, NULL, NULL, '', NULL, ''),
(58, 'giddy ugo', '', '09023299324', 'gidowalski@gmail.com', 'approved', 'QR-67f142cd3e01a', '2025-04-05 13:43:50', 18, 18, NULL, NULL, '', NULL, ''),
(59, 'lucky2', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67f142a965585', '2025-04-05 14:30:22', 18, 18, NULL, NULL, '', NULL, ''),
(60, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67f1430ed702e', '2025-04-05 14:49:40', 18, 18, NULL, NULL, '', NULL, ''),
(61, 'melody', '', '07067367057', 'ugorjigideon@outlook.com', 'approved', 'QR-67f147355dff7', '2025-04-05 15:01:10', 18, 18, NULL, NULL, '', NULL, ''),
(62, 'Ebubechukwu Ugorji', '', '09031669648', 'ugorjigideon@outlook.com', 'approved', 'QR-67f14b16f31c9', '2025-04-05 15:23:49', 18, 18, NULL, NULL, '', NULL, ''),
(63, 'Ebubechukwu Ugorji', '', '07067367057', 'gidowalski@gmail.com', 'approved', 'QR-67f240ff150f8', '2025-04-06 08:49:39', 20, 20, NULL, NULL, '', NULL, ''),
(64, 'Ebubechukwu Ugorji', '', '09020280436', 'gidowalski@gmail.com', 'approved', 'QR-67f2451dcebc4', '2025-04-06 09:05:08', 20, 20, NULL, NULL, '', NULL, ''),
(65, 'Williams', '', '38384934939', 'willibosky@gmail.com', 'approved', 'QR-67f245ca37998', '2025-04-06 09:13:29', 20, 20, NULL, NULL, '', NULL, ''),
(66, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67f2464754d75', '2025-04-06 09:15:36', 20, 20, NULL, NULL, '', NULL, ''),
(67, 'Mr Bright', '', '09031669648', 'sgt.pro501@gmail.com', 'approved', 'QR-67f2baab8adf1', '2025-04-06 10:00:16', 21, 21, NULL, NULL, '', NULL, ''),
(68, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67f3f7513c477', '2025-04-06 10:12:48', 21, 21, NULL, NULL, '', NULL, ''),
(69, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67f2bb840a95d', '2025-04-06 10:12:59', 21, 21, NULL, NULL, '', NULL, ''),
(70, 'Ebubechukwu Ugorji', '', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67fb9c86d395d', '2025-04-06 10:13:08', 21, 21, NULL, NULL, '', NULL, ''),
(71, 'Mr Bright', '', '09031669648', 'sgt.pro501@gmail.com', 'denied', NULL, '2025-04-06 10:18:38', 21, 21, NULL, NULL, '', NULL, ''),
(72, 'Lucky Staff', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f40c972627f', '2025-04-06 10:20:15', 21, 21, NULL, NULL, '', NULL, ''),
(73, 'Lucky Staff', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f40c9c29cf5', '2025-04-06 10:25:41', 21, 21, NULL, NULL, '', NULL, ''),
(74, 'Ebubechukwu Ugorji', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f2ba4eada6a', '2025-04-06 10:26:02', 21, 21, NULL, NULL, '', NULL, ''),
(75, 'Ebubechukwu Ugorji', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f297481fcd3', '2025-04-06 12:29:41', 21, 21, NULL, NULL, '', NULL, ''),
(76, 'Ebubechukwu Ugorji', '', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f297210ab30', '2025-04-06 12:38:05', 21, 21, NULL, NULL, '', NULL, ''),
(77, 'Ebubechukwu Ugorji', 'Mr John2', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67f2bb3f87f48', '2025-04-06 12:43:02', 21, 21, NULL, NULL, '', NULL, ''),
(78, 'success joy', 'lucky gideon', '07067367057', 'sgt.pro501@gmail.com', 'approved', 'QR-67f279d7ac03c', '2025-04-06 12:55:35', 22, 22, NULL, NULL, '', NULL, ''),
(79, 'Dozie Tassie', 'Emmanuel Jean', '09082000222', 'dozietassie@pentagonsecurities.net', 'approved', 'QR-67f295b563efe', '2025-04-06 14:37:16', 23, 23, NULL, NULL, '', NULL, ''),
(80, 'John', 'Lucky', '07067367057', 'willibosky@gmail.com', 'approved', 'QR-67fb9d0d809e9', '2025-04-13 11:16:05', 29, 29, NULL, NULL, '', NULL, ''),
(81, 'Ebubechukwu Ugorji', 'Lucky', '07067367057', 'luckydio10@gmail.com', 'approved', 'QR-67fb9d153f086', '2025-04-13 11:16:21', 29, 29, NULL, NULL, '', NULL, ''),
(82, 'Ebubechukwu Ugorji', 'Lucky', '07067367057', 'luckydio10@gmail.com', 'denied', NULL, '2025-04-13 11:21:13', 29, 29, NULL, NULL, '', NULL, ''),
(83, 'Ebubechukwu Ugorji', 'Luckyyyyy', '07067367057', 'luckydio10@gmail.com', 'pending', NULL, '2025-04-13 11:38:32', 29, 29, NULL, NULL, '', NULL, ''),
(84, 'guy', 'you are', '193938', 'luckydio200@gmail.com', 'pending', NULL, '2025-04-13 11:38:32', 29, 29, NULL, NULL, '', NULL, ''),
(85, 'john bosco', 'Lucy', '0707070707', 'luckydio300@gmail.com', 'pending', NULL, '2025-04-13 11:38:32', 29, 29, NULL, NULL, '', NULL, ''),
(86, 'French', 'juliet', '09074757575757', 'luckydio400@gmail.com', 'pending', NULL, '2025-04-13 11:38:32', 29, 29, NULL, NULL, '', NULL, ''),
(87, 'Ebubechukwu Ugorji', 'Luckyyyyy', '07067367057', 'luckydio10@gmail.com', 'pending', NULL, '2025-04-13 12:35:39', 29, 29, NULL, NULL, 'Pentagon', '2025-04-14', 'Meeting'),
(88, 'Ebubechukwu Ugorji', 'Luckyyyyy', '07067367057', 'luckydio10@gmail.com', 'pending', NULL, '2025-04-13 13:04:16', 29, 29, NULL, NULL, 'Pentagon', '2025-04-14', 'Meeting'),
(89, 'Success Elton', 'Lucky Wenapere', '09020280436', 'eltonsuccess3@gmail.com', 'approved', 'QR-67fbbb97f3dd0', '2025-04-13 13:17:52', 29, 29, NULL, NULL, 'Pentagon', '2025-04-14', 'meeting'),
(90, 'Tari', 'Lucky', '09020280436', 'ugorjigideon@outlook.com', 'approved', 'QR-67fc2b63d9f06', '2025-04-13 21:22:56', 29, 29, NULL, NULL, 'Greenfield', '2025-04-14', 'Meeting');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cso`
--
ALTER TABLE `cso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cso`
--
ALTER TABLE `cso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
