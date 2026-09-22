-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 03:08 PM
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
-- Database: `ayurveda_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `pincode` varchar(10) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@123.com', '$2y$10$./mHXqHBdscoscHPi2u74e5W1FLluhZJlvzU8PHbeLuB8G6OzeQum', 'super_admin', 'Active', NULL, '2026-09-22 09:28:42', '2026-09-22 09:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(500) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('Draft','Active','Inactive') NOT NULL DEFAULT 'Draft',
  `published_at` datetime DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `meta_title` varchar(180) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `meta_title`, `meta_description`, `description`, `image`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Men\'s Wellness', 'mens-wellness', 'Men\'s Wellness | Vedorishi Ayurveda', 'Ayurvedic vitality, stamina and strength products for men.', 'Ayurvedic formulations to support men\'s vitality, stamina and strength.', 'menwell-category-1-87f8aa5dabf3.jpg', 'Active', 1, '2026-09-22 09:09:45', '2026-09-22 13:07:42'),
(2, NULL, 'Weight Loss', 'weight-loss', 'Weight Loss | Vedorishi Ayurveda', 'Ayurvedic weight management products to support metabolism and appetite control.', 'Ayurvedic formulations to support healthy metabolism, fat reduction and appetite control.', 'uploads/categories/weight-loss-1.jpg', 'Active', 2, '2026-09-22 09:09:45', '2026-09-22 12:58:35'),
(3, NULL, 'Combo', 'combo', 'Combo Packs | Vedorishi Ayurveda', 'Combo packs bundling our best-selling wellness products.', 'Bundled combo packs. Products and category image pending.', NULL, 'Active', 3, '2026-09-22 09:09:45', '2026-09-22 12:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `minimum_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `maximum_discount` decimal(12,2) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `per_user_limit` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'pending',
  `shipping_name` varchar(150) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_country` varchar(100) NOT NULL DEFAULT 'India',
  `shipping_pincode` varchar(10) NOT NULL,
  `tracking_number` varchar(150) DEFAULT NULL,
  `shipping_provider` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_name` varchar(150) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_logs`
--

CREATE TABLE `order_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(191) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_response` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_sale_price` decimal(12,2) DEFAULT NULL,
  `has_variants` tinyint(1) NOT NULL DEFAULT 0,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `bestseller` tinyint(1) NOT NULL DEFAULT 0,
  `trending` tinyint(1) NOT NULL DEFAULT 0,
  `seasonal` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive','Draft') NOT NULL DEFAULT 'Draft',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `slug`, `short_description`, `description`, `base_price`, `base_sale_price`, `has_variants`, `stock`, `featured`, `bestseller`, `trending`, `seasonal`, `status`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'Alpha X Resin', 'alpha-x-resin', 'Natural resin for vitality and stamina.', 'Alpha X Resin is a traditional Ayurvedic resin formulation crafted to support vitality, stamina and overall male wellness.', 2499.00, 2374.05, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 12:59:08'),
(2, 'Alpha X Powder', 'alpha-x-powder', 'Strength, energy & endurance powder enriched with Kaunch Beej, Safed Musli & Gokhru. 150 gm.', 'Alpha X Powder is enriched with Kaunch Beej, Safed Musli and Gokhru to support strength, energy and endurance. Pack size: 150 gm.', 1999.00, 1899.05, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 12:59:13'),
(3, 'Alpha X Tablet', 'alpha-x-tablet', 'Vitality, stamina & confidence tablets enriched with Ashwagandha, Shilajit & Kaunch Beej. 60 tabs.', 'Alpha X Tablet is enriched with Ashwagandha, Shilajit and Kaunch Beej to support vitality, stamina and confidence. Pack size: 60 tablets.', 1999.00, 1899.05, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 12:59:19'),
(4, 'Alpha X Oil', 'alpha-x-oil', 'Massage & vitality oil. Natural, safe, effective. 30 ml.', 'Alpha X Oil is a natural massage and vitality oil formulated to be safe and effective. Pack size: 30 ml.', 1199.00, 1139.05, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 13:00:23'),
(5, 'Lean Plus Capsule', 'lean-plus-capsule', 'Ayurvedic weight management capsules. Proprietary medicine. 60 capsules.', 'Lean Plus Capsule supports healthy metabolism, helps reduce fat accumulation, and supports appetite and craving control. Pack size: 60 capsules.', 3095.00, 2940.25, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 13:00:19'),
(6, 'Lean Plus Syrup', 'lean-plus-syrup', 'Ayurvedic weight management syrup. Proprietary medicine. 300 ml.', 'Lean Plus Syrup supports healthy metabolism, helps reduce fat accumulation, supports appetite and craving control, and helps maintain energy levels. Pack size: 300 ml.', 0.00, NULL, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-22 13:00:15'),
(8, 'Men\'s Wellness Full Kit', 'mens-wellness-full-kit', 'Complete Alpha X kit: Resin, Powder, Tablet & Oil in one pack.', 'The Men\'s Wellness Full Kit bundles all four Alpha X formulations — Resin, Powder, Tablet and Oil — for a complete vitality, stamina and strength routine.', 7696.00, 6541.60, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:21:03', '2026-09-22 13:00:11'),
(9, 'Weight Loss Full Kit', 'weight-loss-full-kit', 'Complete Lean Plus kit: Capsule & Syrup in one pack.', 'The Weight Loss Full Kit bundles Lean Plus Capsule and Lean Plus Syrup together to support metabolism, fat reduction and appetite control.', 0.00, NULL, 0, 0, 0, 0, 0, 0, 'Active', NULL, NULL, '2026-09-22 09:21:03', '2026-09-22 13:00:08');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `is_primary`) VALUES
(1, 1, 1),
(2, 1, 1),
(3, 1, 1),
(4, 1, 1),
(5, 2, 1),
(6, 2, 1),
(8, 3, 1),
(9, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `ingredients` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `directions` text DEFAULT NULL,
  `dosage` text DEFAULT NULL,
  `precautions` text DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `country_of_origin` varchar(100) DEFAULT 'India',
  `shelf_life` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_details`
--

INSERT INTO `product_details` (`id`, `product_id`, `ingredients`, `benefits`, `directions`, `dosage`, `precautions`, `manufacturer`, `country_of_origin`, `shelf_life`, `created_at`, `updated_at`) VALUES
(1, 1, 'Each 1 gm contains: Shuddh Shilajit extract 700 mg, Ashwagandha extract 100 mg, Gokhru extract 75 mg, Safed Musli extract 50 mg, Salam Panja extract 25 mg, Javitri/Jaiphal extract 20 mg, Base q.s.', 'Strength, energy & endurance. Supports vitality, stamina and confidence.', 'Take pea size (300 mg) with warm water or milk, or as directed by the physician.', '300 mg (pea size) once daily, or as directed by consultant.', 'Store in a cool, dry place away from children. Do not exceed recommended dosage.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(2, 2, 'Each 10 gm contains: Kaunch Beej 2000 mg, Safed Musli 1500 mg, Ashwagandha root 1500 mg, Vidarikand 1000 mg, Gokhru 1000 mg, Salam Panja 500 mg, Shatavari 500 mg, Excipients q.s.', 'Strength, energy & endurance. Supports daily vitality, active lifestyle, men\'s wellness and overall wellbeing.', 'Take 10 gm of Alpha X powder with warm water or milk, or as directed by consultant.', '10 gm once daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(3, 3, 'Each tablet contains: Ashwagandha extract 300 mg, Gokhru extract 150 mg, Safed Musli extract 100 mg, Akarkara extract 75 mg, Shilajit extract 100 mg, Kaunch Beej extract 100 mg, Kesar 15 mg, Safedsi extract 25 mg, Excipients q.s.', 'Vitality, stamina & confidence. Supports daily vitality, active lifestyle, men\'s wellness and inner wellness.', 'Take 1 tablet a day with water, or as directed by consultant.', '1 tablet daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(4, 4, 'Each 30 ml contains: Malkangni 600 mg, Akarkara 300 mg, Jalphal 300 mg, Kesar 150 mg, Laung 100 mg, Til Tail q.s. to 30 ml.', 'Natural care, body comfort, relaxing muscles. Natural, safe, effective massage & vitality oil.', 'Apply 1-3 ml of oil externally on the affected area and massage gently once a day, or as directed by the physician.', '1-3 ml externally, once daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children. Do not refrigerate.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(5, 5, 'Each capsule contains: Triphala 50 mg, Ajwain 40 mg, Punernava 30 mg, Heeng 20 mg, Vidang 50 mg, Guggal 50 mg, Shudh Shilajit 20 mg, Trikuta 20 mg, Yavkshar 30 mg, Pipplamool 50 mg, Arogyavardhini 50 mg, Sounth 40 mg, Methi Dana 50 mg, Garcinia 50 mg.', 'Supports healthy weight management by promoting fat metabolism, improving digestion, reducing appetite naturally, enhancing energy levels, and helping maintain a healthy lipid profile.', '1 capsule twice a day with water, or as directed by consultant.', '1 capsule twice daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(6, 6, 'Each 5 ml contains: Garcinia Cambogia 1000 mg, Trigonella foenum-graecum 100 mg, Allium sativum 750 mg, Emblica officinalis 200 mg, Terminalia chebula 200 mg, Terminalia bellerica 200 mg, Trachyspermum ammi 100 mg, Foeniculum vulgare 400 mg, Zingiber officinale 100 mg.', 'Supports by boosting metabolism, reducing fat accumulation, and controlling appetite & cravings. No side effects; best used alongside a healthy lifestyle.', 'As directed by the consultant. Shake well before use.', 'As directed by consultant.', 'Colour, taste & aroma may vary batch to batch (natural herbal extract). Consume within one month of opening. Store in a cool, dry place.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(7, 8, 'Bundle of: Alpha X Resin, Alpha X Powder, Alpha X Tablet and Alpha X Oil. See each product\'s own listing for its full ingredient panel.', 'Complete men\'s wellness routine covering vitality, stamina, strength and topical massage support.', 'Follow the individual direction-for-use printed on each item in the kit.', 'As directed on each individual product, or by consultant.', 'Store all items in a cool, dry & dark place. Keep away from children.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01'),
(8, 9, 'Bundle of: Lean Plus Capsule and Lean Plus Syrup. See each product\'s own listing for its full ingredient panel.', 'Complete weight management routine combining a capsule and syrup formulation to support metabolism, fat reduction and appetite control.', 'Follow the individual direction-for-use printed on each item in the kit.', 'As directed on each individual product, or by consultant.', 'Store all items in a cool, dry & dark place. Keep away from children.', NULL, 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-22 09:32:01');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'uploads/products/alpha-x-resin-1.jpg', 'Alpha X Resin', 1, 1, '2026-09-22 09:09:45'),
(2, 1, 'uploads/products/alpha-x-resin-2.jpg', 'Alpha X Resin', 0, 2, '2026-09-22 09:09:45'),
(3, 1, 'uploads/products/alpha-x-resin-3.jpg', 'Alpha X Resin', 0, 3, '2026-09-22 09:09:45'),
(4, 1, 'uploads/products/alpha-x-resin-4.jpg', 'Alpha X Resin', 0, 4, '2026-09-22 09:09:45'),
(5, 2, 'uploads/products/alpha-x-powder-1.jpg', 'Alpha X Powder', 1, 1, '2026-09-22 09:09:45'),
(6, 2, 'uploads/products/alpha-x-powder-2.jpg', 'Alpha X Powder', 0, 2, '2026-09-22 09:09:45'),
(7, 2, 'uploads/products/alpha-x-powder-3.jpg', 'Alpha X Powder', 0, 3, '2026-09-22 09:09:45'),
(8, 3, 'uploads/products/alpha-x-tablet-1.jpg', 'Alpha X Tablet', 1, 1, '2026-09-22 09:09:45'),
(9, 3, 'uploads/products/alpha-x-tablet-2.jpg', 'Alpha X Tablet', 0, 2, '2026-09-22 09:09:45'),
(10, 3, 'uploads/products/alpha-x-tablet-3.jpg', 'Alpha X Tablet', 0, 3, '2026-09-22 09:09:45'),
(11, 4, 'uploads/products/alpha-x-oil-1.jpg', 'Alpha X Oil', 1, 1, '2026-09-22 09:09:45'),
(12, 4, 'uploads/products/alpha-x-oil-2.jpg', 'Alpha X Oil', 0, 2, '2026-09-22 09:09:45'),
(13, 4, 'uploads/products/alpha-x-oil-3.jpg', 'Alpha X Oil', 0, 3, '2026-09-22 09:09:45'),
(14, 5, 'uploads/products/lean-plus-capsule-1.jpg', 'Lean Plus Capsule', 1, 1, '2026-09-22 09:09:45'),
(15, 5, 'uploads/products/lean-plus-capsule-2.jpg', 'Lean Plus Capsule', 0, 2, '2026-09-22 09:09:45'),
(16, 5, 'uploads/products/lean-plus-capsule-3.jpg', 'Lean Plus Capsule', 0, 3, '2026-09-22 09:09:45'),
(17, 5, 'uploads/products/lean-plus-capsule-4.jpg', 'Lean Plus Capsule', 0, 4, '2026-09-22 09:09:45'),
(18, 6, 'uploads/products/lean-plus-syrup-1.jpg', 'Lean Plus Syrup', 1, 1, '2026-09-22 09:09:45'),
(19, 6, 'uploads/products/lean-plus-syrup-2.jpg', 'Lean Plus Syrup', 0, 2, '2026-09-22 09:09:45'),
(20, 6, 'uploads/products/lean-plus-syrup-3.jpg', 'Lean Plus Syrup', 0, 3, '2026-09-22 09:09:45'),
(21, 8, 'uploads/products/mens-wellness-full-kit-1.jpg', 'Men\'s Wellness Full Kit', 1, 1, '2026-09-22 09:21:03'),
(22, 8, 'uploads/products/mens-wellness-full-kit-2.jpg', 'Men\'s Wellness Full Kit', 0, 2, '2026-09-22 09:21:03'),
(23, 9, 'uploads/products/weight-loss-full-kit-1.jpg', 'Weight Loss Full Kit', 1, 1, '2026-09-22 09:21:03'),
(24, 9, 'uploads/products/weight-loss-full-kit-2.jpg', 'Weight Loss Full Kit', 0, 2, '2026-09-22 09:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_name` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `weight_grams` int(10) UNSIGNED DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotional_videos`
--

CREATE TABLE `promotional_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video_type` enum('upload','youtube','vimeo') NOT NULL DEFAULT 'upload',
  `video_url` varchar(500) DEFAULT NULL,
  `video_file` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `orientation` enum('vertical','horizontal') DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(500) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `status` enum('Pending','Active','Rejected') NOT NULL DEFAULT 'Pending',
  `verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transformations`
--

CREATE TABLE `transformations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `before_image` varchar(500) NOT NULL,
  `after_image` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive','Blocked') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`),
  ADD KEY `idx_admin_role` (`role`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_banners_position` (`position`,`status`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_slug` (`slug`),
  ADD KEY `idx_blog_status` (`status`,`published_at`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart_item` (`user_id`,`product_id`,`variant_id`),
  ADD KEY `idx_cart_product` (`product_id`),
  ADD KEY `idx_cart_variant` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_category_slug` (`slug`),
  ADD KEY `idx_category_parent` (`parent_id`),
  ADD KEY `idx_category_status` (`status`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupon_code` (`code`),
  ADD KEY `idx_coupon_status` (`status`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupon_order` (`coupon_id`,`order_id`),
  ADD KEY `idx_coupon_usage_user` (`user_id`),
  ADD KEY `idx_coupon_usage_coupon` (`coupon_id`),
  ADD KEY `fk_coupon_usage_order` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_payment` (`payment_status`),
  ADD KEY `idx_orders_created` (`created_at`),
  ADD KEY `fk_orders_coupon` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`),
  ADD KEY `fk_order_items_variant` (`variant_id`);

--
-- Indexes for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_logs_order` (`order_id`),
  ADD KEY `fk_order_logs_admin` (`admin_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reset_token` (`token_hash`),
  ADD KEY `idx_reset_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_transaction` (`transaction_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_slug` (`slug`),
  ADD KEY `idx_product_status` (`status`),
  ADD KEY `idx_product_featured` (`featured`),
  ADD KEY `idx_product_bestseller` (`bestseller`),
  ADD KEY `idx_product_trending` (`trending`),
  ADD KEY `idx_products_stock` (`stock`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `idx_pc_category` (`category_id`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_details` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images` (`product_id`,`sort_order`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_variant_sku` (`sku`),
  ADD KEY `idx_variant_product` (`product_id`),
  ADD KEY `idx_variant_stock` (`stock`);

--
-- Indexes for table `promotional_videos`
--
ALTER TABLE `promotional_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_review` (`product_id`,`user_id`),
  ADD KEY `idx_reviews_product` (`product_id`),
  ADD KEY `idx_reviews_status` (`status`),
  ADD KEY `fk_reviews_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_team_members_status_sort` (`status`,`sort_order`);

--
-- Indexes for table `transformations`
--
ALTER TABLE `transformations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_phone` (`phone`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wishlist` (`user_id`,`product_id`),
  ADD KEY `idx_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotional_videos`
--
ALTER TABLE `promotional_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transformations`
--
ALTER TABLE `transformations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `fk_coupon_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_coupon_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_coupon_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD CONSTRAINT `fk_order_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_logs_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_details`
--
ALTER TABLE `product_details`
  ADD CONSTRAINT `fk_product_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transformations`
--
ALTER TABLE `transformations`
  ADD CONSTRAINT `transformations_product_id_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
