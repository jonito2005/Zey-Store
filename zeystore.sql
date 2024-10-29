-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 19, 2024 at 04:47 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zeystore`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `product_id`, `customer_name`, `customer_phone`, `status`, `created_at`) VALUES
(1, 0, 1, 'tes', '1234567890', 'success', '2024-09-18 22:01:23'),
(2, 3, 3, 'CUSTOMER', '', 'success', '2024-09-19 04:14:23'),
(3, 3, 3, 'CUSTOMER', '', 'pending', '2024-09-19 04:21:24'),
(4, 3, 3, 'CUSTOMER', '', 'pending', '2024-09-19 04:26:11'),
(5, 3, 2, 'CUSTOMER', '', 'pending', '2024-09-19 04:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` int NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`) VALUES
(1, 'E-book Pemrograman PHP', 'Panduan lengkap belajar pemrograman PHP dari dasar hingga mahir.', 50000, 'image/1.jpg'),
(2, 'Template Website HTML', 'Template website responsif menggunakan HTML dan CSS.', 75000, 'image/2.jpg'),
(3, 'Kursus Online JavaScript', 'Akses kursus online JavaScript selama 1 tahun.', 150000, 'image/3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('member','admin') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `created_at`, `role`) VALUES
(1, 'ADMIN', 'admin@gmail.com', '081111111', '$2y$10$yAUiQHA0u00cgqByYbOuY.lOxFKzUYOH6SA0KAu2eMEwU89gBme9O', '2024-09-18 09:00:02', 'admin'),
(2, 'JAMAL SUCIPTO', 'joniyogakusuma2005@gmail.com', '081255873922', '$2y$10$fglqZxTEeZVir/cfnLD.COKIILivsrmIJ40veUb0EsSZQgSI5EyPm', '2024-09-18 20:12:59', 'member'),
(3, 'CUSTOMER', 'customer@gmail.com', '081266863922', '$2y$10$lEX4xXkwNJ0j6drmyl3F1ukPJJNKCCgCvywRVPyJR4qUBXysbCF/a', '2024-09-18 20:43:59', 'member'),
(4, 'TES', 'customer1@gmail.com', '082348848344', '$2y$10$CRzUgko4bTiVNPuLNELAwe4T92HdTsXAtmtMXV7CT/WR4sDqzS7g2', '2024-09-19 03:28:35', 'member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
