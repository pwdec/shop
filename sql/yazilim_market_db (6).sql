-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 26 Ara 2025, 14:37:51
-- Sunucu sürümü: 8.0.44
-- PHP Sürümü: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `yazilim_market_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'PHP Scriptler'),
(2, 'WordPress Temaları');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL DEFAULT '5',
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `comments`
--

INSERT INTO `comments` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 5, 'süper ürün alın aldırın', '2025-12-08 17:51:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coupons`
--

CREATE TABLE `coupons` (
  `id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `discount_rate` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_rate`) VALUES
(1, 'YAZILIM20', 20);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_no` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `user_id`, `product_id`, `price`, `status`, `created_at`) VALUES
(1, NULL, 1, 1, 800.00, 'completed', '2025-12-08 17:29:44'),
(2, 'ORD-973952', 1, 1, 1000.00, 'completed', '2025-12-08 17:33:20'),
(3, 'ORD-956003', 1, 1, 1000.00, 'completed', '2025-12-08 17:33:34'),
(4, 'ORD-972957', 1, 2, 800.00, 'completed', '2025-12-08 18:25:23'),
(5, 'ORD-964168', 1, 1, 1000.00, 'completed', '2025-12-08 18:46:58'),
(6, 'ORD-100024', 1, 1, 1000.00, 'completed', '2025-12-08 18:50:05'),
(7, 'ORD-981555', 1, 2, 800.00, 'completed', '2025-12-08 18:51:35'),
(8, 'ORD-313714', 1, 1, 1000.00, 'completed', '2025-12-08 19:30:38'),
(9, 'ORD-1765224722961', 1, 2, 800.00, 'completed', '2025-12-08 20:12:02'),
(10, 'ORD-1765224773469', 1, 2, 800.00, 'completed', '2025-12-08 20:12:53'),
(11, 'ORD-1765225040738', 1, 2, 800.00, 'completed', '2025-12-08 20:17:20'),
(12, 'ORD-1765225045761', 1, 2, 800.00, 'completed', '2025-12-08 20:17:25'),
(13, 'ORD-1765225140742', 1, 2, 800.00, 'completed', '2025-12-08 20:19:00'),
(14, 'ORD-1765225295796', 1, 2, 800.00, 'completed', '2025-12-08 20:21:35'),
(15, 'ORD-1765225623999', 1, 2, 800.00, 'completed', '2025-12-08 20:27:03'),
(16, 'ORD-1766759822569', 1, 1, 1000.00, 'completed', '2025-12-26 14:37:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `site_demo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_demo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `features` text COLLATE utf8mb4_general_ci,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `download_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `category_id`, `title`, `description`, `price`, `site_demo`, `admin_demo`, `features`, `image`, `download_link`, `created_at`) VALUES
(1, 1, 'Monify', '<p>gelir&nbsp; gider scripti</p>\r\n', 1000.00, 'https://monify.burakkilic.com.tr/butce_scripti', 'https://monify.burakkilic.com.tr/butce_scripti', NULL, 'uploads/images/69370a587eb15.png', 'uploads/files/file_693729b1a9cfa.zip', '2025-12-08 17:26:48'),
(2, 1, 'Kasapro', '<p>kasaproo</p>\r\n', 800.00, 'https://monify.burakkilic.com.tr/butce_scripti', 'https://monify.burakkilic.com.tr/butce_scripti', NULL, 'uploads/images/cover_693717ec3187d.png', 'uploads/files/file_693729ac34f8c.zip', '2025-12-08 18:24:44'),
(3, 2, 'epin yazılımı', '<p>epin</p>\r\n', 6000.00, '', '', NULL, 'uploads/images/cover_693734f2a2e6a.jpeg', '', '2025-12-08 20:28:34');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`) VALUES
(1, 1, 'uploads/images/gallery_693717a8e58d2.png', '2025-12-08 18:23:36'),
(2, 1, 'uploads/images/gallery_693717a8e6693.png', '2025-12-08 18:23:36'),
(3, 1, 'uploads/images/gallery_693717a8e73e7.png', '2025-12-08 18:23:36'),
(4, 1, 'uploads/images/gallery_693717a8e807c.png', '2025-12-08 18:23:36'),
(5, 2, 'uploads/images/gallery_693717ec32a8a.png', '2025-12-08 18:24:44'),
(6, 2, 'uploads/images/gallery_693717ec33567.png', '2025-12-08 18:24:44'),
(7, 2, 'uploads/images/gallery_693717ec34217.png', '2025-12-08 18:24:44');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `site_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'Yazılım Market',
  `site_desc` text COLLATE utf8mb4_general_ci,
  `contact_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `iban_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `topbar_status` tinyint(1) DEFAULT '0',
  `topbar_text` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `topbar_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '#000000',
  `popup_status` tinyint(1) DEFAULT '0',
  `popup_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `popup_text` text COLLATE utf8mb4_general_ci,
  `popup_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `popup_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `site_title`, `site_desc`, `contact_email`, `iban_number`, `bank_name`, `topbar_status`, `topbar_text`, `topbar_color`, `popup_status`, `popup_title`, `popup_text`, `popup_link`, `popup_image`) VALUES
(1, 'Yazılım Market', 'YAZILIM SATIŞ SİTESİ', 'admin@site.com', 'TR000000000', 'GARANTİ', 1, 'Hoşgeldiniz! %20 İndirim Kodu: YAZILIM20', '#00b3ff', 1, 'Büyük Fırsat!', 'deneme', '#', 'uploads/images/popup_1765214873.png');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tickets`
--

CREATE TABLE `tickets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('acik','cevaplandi','kapali') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'acik',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `subject`, `status`, `created_at`) VALUES
(1, 1, 'Merhaba', 'kapali', '2025-12-08 17:45:40'),
(2, 1, 'sad', 'kapali', '2025-12-08 17:45:52');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `user_id`, `message`, `is_admin`, `created_at`) VALUES
(1, 1, 1, 'hatalar var hocam', 0, '2025-12-08 17:45:40'),
(2, 2, 1, 'sd', 0, '2025-12-08 17:45:52'),
(3, 2, 1, 'sa merhaba', 1, '2025-12-08 17:46:05'),
(4, 2, 1, 'merhaba hocam hatalar alıyorum kodda', 0, '2025-12-08 17:48:08'),
(5, 2, 1, 'tamam hocam kontrol edicem', 1, '2025-12-08 17:48:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$KvbCYpRj15bnBCAvjxRFbOOMTCZqG1hfIFiY7gCe.7rk.MS/hY46u', 'admin', '2025-12-08 17:22:26');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
