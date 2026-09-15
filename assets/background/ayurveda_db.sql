-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 10:59 AM
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
(1, 'Administrator', 'admin@example.com', '$2y$10$YuHa3Coq5WsTlFABtPXqX.FkpaJW53THnCeEJj.6lkFMIof2k.WnS', 'admin', 'Active', NULL, '2026-09-03 12:50:42', '2026-09-03 12:55:25');

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

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `button_text`, `button_url`, `position`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Home Banner 1', NULL, 'banner1-d37ae45b087f.jpg', NULL, '/ayurveda/products.php?category_slug=digestive-powders', 'homepage_hero', 1, 1, '2026-09-14 07:08:34', '2026-09-15 05:14:10'),
(2, 'Home Banner 2', NULL, 'banner2-7bbf0b82b762.jpg', NULL, '/ayurveda/products.php?category_slug=hair-oils', 'homepage_hero', 1, 2, '2026-09-14 07:08:44', '2026-09-15 05:15:00'),
(3, 'Home Banner 3', NULL, 'banner3-68e8f71ce4f5.jpg', NULL, '/ayurveda/products.php?category_slug=relaxation-supplements', 'homepage_hero', 1, 3, '2026-09-14 07:24:08', '2026-09-15 05:16:03'),
(4, 'Home Banner 4', NULL, 'banner4-069ba87ffc9d.jpg', NULL, '/ayurveda/products.php', 'homepage_hero', 1, 4, '2026-09-14 07:24:18', '2026-09-15 05:16:38'),
(5, 'Home Banner 5', NULL, 'banner5-db59c2a3bd25.jpg', NULL, '/ayurveda/products.php', 'homepage_hero', 1, 5, '2026-09-14 07:24:30', '2026-09-15 05:17:08'),
(6, 'Home Banner 6', NULL, 'banner6-dc4e01ebe90a.jpg', NULL, '/ayurveda/products.php', 'homepage_hero', 1, 6, '2026-09-14 07:24:41', '2026-09-15 05:17:25'),
(7, 'Home Banner 7', NULL, 'banner7-b75d5b26b794.jpg', NULL, '/ayurveda/products.php', 'homepage_hero', 1, 7, '2026-09-14 07:24:53', '2026-09-15 05:17:41'),
(8, 'Detux banner 1', NULL, 'detux1-aaf830545bef.png', NULL, NULL, 'detux_hero', 1, 1, '2026-09-15 06:58:39', '2026-09-15 06:58:39'),
(9, 'Detux banner 2', NULL, 'detux2-a1a74ff4d318.png', NULL, NULL, 'detux_hero', 1, 2, '2026-09-15 06:59:06', '2026-09-15 06:59:06'),
(10, 'Detux banner 3', NULL, 'detux3-c824b85ce26c.png', NULL, NULL, 'detux_hero', 1, 3, '2026-09-15 06:59:21', '2026-09-15 06:59:39'),
(11, 'Detux banner 4', NULL, 'detux4-fd3de9165022.png', NULL, NULL, 'detux_hero', 1, 4, '2026-09-15 07:00:01', '2026-09-15 07:00:01'),
(12, 'Detux banner 5', NULL, 'detux5-209e8c9a889c.png', NULL, NULL, 'detux_hero', 1, 5, '2026-09-15 07:00:21', '2026-09-15 07:00:21'),
(13, 'Detux banner 6', NULL, 'detux6-38b1066b573d.png', NULL, NULL, 'detux_hero', 1, 6, '2026-09-15 07:00:42', '2026-09-15 07:00:42');

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
(1, NULL, 'Ayurvedic Medicines', 'ayurvedic-medicines', 'Ayurvedic Medicines', 'Explore traditional Ayurvedic formulations and herbal wellness products.', 'Explore traditional Ayurvedic formulations and herbal wellness products.', 'b-a-m-d36429c03c3c.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:08:43'),
(2, NULL, 'Classical Ayurvedic Medicines', 'classical-ayurvedic-medicines', 'Classical Ayurvedic Medicines', 'Traditional Ayurvedic formulations based on classical preparations.', 'Traditional Ayurvedic formulations based on classical preparations.', 'b-a-m-103646c6dd9d.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:10:42'),
(3, 1, 'Herbal Formulations', 'herbal-formulations', 'Herbal Formulations', 'Natural herbal formulations for everyday wellness.', 'Explore herbal formulations made with traditional Ayurvedic ingredients.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(4, 1, 'Ayurvedic Tablets', 'ayurvedic-tablets', 'Ayurvedic Tablets', 'Ayurvedic herbal tablets and formulations.', 'Traditional Ayurvedic tablets and herbal formulations.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(5, 1, 'Ayurvedic Churna', 'ayurvedic-churna', 'Ayurvedic Churna', 'Traditional Ayurvedic herbal powders and churnas.', 'Traditional powdered Ayurvedic herbal formulations.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(6, 1, 'Ayurvedic Syrups', 'ayurvedic-syrups', 'Ayurvedic Syrups', 'Ayurvedic herbal syrups and liquid formulations.', 'Herbal syrups and traditional liquid Ayurvedic preparations.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(7, 1, 'Ayurvedic Rasayana', 'ayurvedic-rasayana', 'Ayurvedic Rasayana', 'Traditional Ayurvedic rasayana and rejuvenation products.', 'Traditional Ayurvedic rasayana products for general wellness.', NULL, 'Active', 6, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(8, NULL, 'Herbal Supplements', 'herbal-supplements', 'Herbal Supplements', 'Shop natural herbal supplements and Ayurvedic wellness products.', 'A range of herbal supplements and traditional wellness products.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(9, NULL, 'Herbal Capsules', 'herbal-capsules', 'Herbal Capsules', 'Herbal capsules for everyday wellness.', 'Herbal capsules for everyday wellness.', 'b-a-m-e08834b8b594.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:10:49'),
(10, 8, 'Herbal Powders', 'herbal-powders', 'Herbal Powders', 'Natural herbal powders and Ayurvedic ingredients.', 'Traditional herbal powders and plant-based ingredients.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(11, 8, 'Herbal Extracts', 'herbal-extracts', 'Herbal Extracts', 'Natural herbal extracts and plant-based formulations.', 'Herbal extracts prepared from traditional botanical ingredients.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(12, 8, 'Ayurvedic Tonics', 'ayurvedic-tonics', 'Ayurvedic Tonics', 'Ayurvedic herbal tonics for everyday wellness.', 'Traditional herbal tonics and wellness formulations.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(13, 8, 'Herbal Supplements for Daily Wellness', 'daily-wellness-supplements', 'Daily Wellness Supplements', 'Herbal supplements for everyday wellness.', 'Herbal products intended to complement everyday wellness routines.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(14, NULL, 'Digestive Wellness', 'digestive-wellness', 'Ayurvedic Digestive Wellness', 'Ayurvedic products for digestive wellness.', 'Explore traditional Ayurvedic products for digestive wellness.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(15, NULL, 'Digestive Powders', 'digestive-powders', 'Digestive Powders', 'Herbal powders traditionally used as part of digestive wellness routines.', 'Herbal powders traditionally used as part of digestive wellness routines.', 'b-a-m-b346257fcb3d.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:09:54'),
(16, 14, 'Digestive Tablets', 'digestive-tablets', 'Digestive Tablets', 'Ayurvedic herbal tablets for digestive wellness.', 'Traditional herbal tablets for digestive wellness.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(17, 14, 'Digestive Tonics', 'digestive-tonics', 'Digestive Tonics', 'Ayurvedic digestive tonics and herbal formulations.', 'Herbal tonics designed for digestive wellness.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(18, 14, 'Fiber & Herbal Blends', 'fiber-herbal-blends', 'Fiber & Herbal Blends', 'Herbal and fiber-based wellness products.', 'Herbal and fiber-based products for everyday digestive routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(19, NULL, 'Immunity & Wellness', 'immunity-wellness', 'Ayurvedic Immunity & Wellness', 'Ayurvedic herbal products for immunity and general wellness.', 'Explore herbal products commonly used in traditional wellness routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(20, NULL, 'Immunity Supplements', 'immunity-supplements', 'Immunity Supplements', 'Herbal supplements for general wellness routines.', 'Herbal supplements for general wellness routines.', 'b-a-m-be0f79680654.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:10:58'),
(21, 19, 'Herbal Syrups', 'immunity-herbal-syrups', 'Herbal Wellness Syrups', 'Traditional herbal syrups and wellness formulations.', 'Traditional herbal syrups and liquid formulations.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(22, 19, 'Ayurvedic Wellness Kits', 'ayurvedic-wellness-kits', 'Ayurvedic Wellness Kits', 'Ayurvedic wellness product combinations and kits.', 'Curated combinations of Ayurvedic wellness products.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(23, 19, 'Daily Wellness', 'daily-wellness', 'Daily Ayurvedic Wellness', 'Products for everyday Ayurvedic wellness routines.', 'Products suitable for incorporating traditional wellness practices into daily routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(24, NULL, 'Hair Care', 'hair-care', 'Ayurvedic Hair Care', 'Natural Ayurvedic hair care products for hair and scalp.', 'Explore Ayurvedic and herbal products for everyday hair care.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(25, NULL, 'Hair Oils', 'hair-oils', 'Hair Oils', 'Herbal and Ayurvedic oils for hair care routines.', 'Herbal and Ayurvedic oils for hair care routines.', 'b-a-m-b1daf8b7c875.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:10:25'),
(26, 24, 'Shampoo', 'ayurvedic-shampoo', 'Ayurvedic Shampoo', 'Natural and Ayurvedic shampoos.', 'Herbal and Ayurvedic shampoos for everyday hair care.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(27, 24, 'Hair Conditioner', 'hair-conditioner', 'Ayurvedic Hair Conditioner', 'Herbal and natural hair conditioners.', 'Natural hair conditioning products.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(28, 24, 'Hair Masks', 'hair-masks', 'Ayurvedic Hair Masks', 'Natural herbal hair masks and treatments.', 'Herbal hair masks for regular hair care routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(29, 24, 'Hair Growth Care', 'hair-growth-care', 'Ayurvedic Hair Growth Care', 'Ayurvedic and herbal products for hair care.', 'Herbal products commonly used in hair care routines.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(30, 24, 'Anti-Dandruff Care', 'anti-dandruff-care', 'Ayurvedic Anti-Dandruff Care', 'Herbal products for scalp and dandruff care.', 'Herbal hair and scalp care products.', NULL, 'Active', 6, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(31, NULL, 'Skin Care', 'skin-care', 'Ayurvedic Skin Care', 'Natural Ayurvedic and herbal skin care products.', 'Explore natural and Ayurvedic products for everyday skin care.', NULL, 'Active', 6, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(32, NULL, 'Face Care', 'face-care', 'Face Care', 'Herbal products for everyday facial care.', 'Herbal products for everyday facial care.', 'b-a-m-4ab5bc2b02c4.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:54:41'),
(33, 31, 'Face Wash', 'face-wash', 'Herbal Face Wash', 'Natural and herbal face washes.', 'Herbal face cleansing products.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(34, 31, 'Face Creams', 'face-creams', 'Ayurvedic Face Creams', 'Natural Ayurvedic face creams and moisturizers.', 'Herbal creams and moisturizers for everyday skin care.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(35, 31, 'Face Oils', 'face-oils', 'Ayurvedic Face Oils', 'Natural herbal and Ayurvedic face oils.', 'Plant-based oils for facial care routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(36, 31, 'Face Masks', 'face-masks', 'Herbal Face Masks', 'Natural herbal face masks and packs.', 'Traditional herbal face masks and skin care packs.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(37, 31, 'Body Care', 'body-care', 'Ayurvedic Body Care', 'Natural Ayurvedic body care products.', 'Herbal products for everyday body care.', NULL, 'Active', 6, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(38, 31, 'Soaps', 'ayurvedic-soaps', 'Ayurvedic Herbal Soaps', 'Natural and herbal Ayurvedic soaps.', 'Traditional herbal and Ayurvedic bathing products.', NULL, 'Active', 7, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(39, NULL, 'Ayurvedic Oils', 'ayurvedic-oils', 'Ayurvedic Oils', 'Traditional Ayurvedic and herbal oils.', 'Explore traditional herbal oils for different wellness and personal care routines.', NULL, 'Active', 7, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(40, NULL, 'Massage Oils', 'massage-oils', 'Massage Oils', 'Herbal oils commonly used for massage and body care.', 'Herbal oils commonly used for massage and body care.', 'b-a-m-73c9874292c5.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:54:50'),
(41, 39, 'Body Oils', 'body-oils', 'Ayurvedic Body Oils', 'Natural herbal body oils.', 'Plant-based oils for body care.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(42, 39, 'Hair Oils', 'ayurvedic-hair-oils', 'Ayurvedic Hair Oils', 'Traditional herbal hair oils.', 'Ayurvedic and herbal oils for hair care.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(43, 39, 'Joint Massage Oils', 'joint-massage-oils', 'Ayurvedic Massage Oils', 'Traditional herbal massage oils.', 'Ayurvedic oils commonly used in traditional massage routines.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(44, NULL, 'Joint & Bone Wellness', 'joint-bone-wellness', 'Ayurvedic Joint & Bone Wellness', 'Ayurvedic and herbal products for joint and bone wellness.', 'Traditional Ayurvedic products used as part of joint and body wellness routines.', NULL, 'Active', 8, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(45, NULL, 'Joint Care Supplements', 'joint-care-supplements', 'Joint Care Supplements', 'Herbal supplements for general joint wellness.', 'Herbal supplements for general joint wellness.', 'b-a-m-35ea6d2f0e1a.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:54:58'),
(46, 44, 'Joint Massage Oils', 'joint-care-oils', 'Joint Care Oils', 'Traditional Ayurvedic massage oils.', 'Herbal oils used in traditional massage routines.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(47, 44, 'Herbal Balms', 'herbal-balms', 'Ayurvedic Herbal Balms', 'Traditional herbal balms and topical products.', 'Herbal balms for personal care and massage routines.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(48, NULL, 'Stress & Sleep Wellness', 'stress-sleep-wellness', 'Ayurvedic Stress & Sleep Wellness', 'Ayurvedic and herbal products for relaxation and wellness.', 'Traditional herbal products for relaxation and wellness routines.', NULL, 'Active', 9, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(49, NULL, 'Relaxation Supplements', 'relaxation-supplements', 'Relaxation Supplements', 'Herbal supplements commonly included in relaxation routines.', 'Herbal supplements commonly included in relaxation routines.', 'b-a-m-8fa3b80437ab.jpg', 'Active', 1, '2026-09-10 11:20:54', '2026-09-14 06:55:11'),
(50, 48, 'Sleep Wellness', 'sleep-wellness', 'Ayurvedic Sleep Wellness', 'Herbal products for healthy sleep routines.', 'Traditional herbal products for nighttime wellness routines.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(51, 48, 'Meditation & Wellness Products', 'meditation-wellness', 'Meditation & Wellness', 'Products for relaxation and traditional wellness practices.', 'Products supporting traditional relaxation and wellness practices.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(52, NULL, 'Men\'s Wellness', 'mens-wellness', 'Ayurvedic Men\'s Wellness', 'Ayurvedic and herbal products for men\'s wellness.', 'Explore herbal and Ayurvedic products for men\'s general wellness.', NULL, 'Active', 10, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(53, 52, 'Men\'s Supplements', 'mens-supplements', 'Men\'s Herbal Supplements', 'Herbal supplements for men\'s wellness.', 'Herbal products for men\'s general wellness routines.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(54, 52, 'Men\'s Personal Care', 'mens-personal-care', 'Men\'s Ayurvedic Personal Care', 'Natural personal care products for men.', 'Natural and herbal personal care products for men.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(55, 52, 'Men\'s Hair Care', 'mens-hair-care', 'Men\'s Hair Care', 'Ayurvedic and herbal hair care products for men.', 'Hair and scalp care products suitable for men.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(56, NULL, 'Women\'s Wellness', 'womens-wellness', 'Ayurvedic Women\'s Wellness', 'Ayurvedic and herbal products for women\'s wellness.', 'Explore herbal and Ayurvedic products for women\'s general wellness.', NULL, 'Active', 11, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(57, 56, 'Women\'s Supplements', 'womens-supplements', 'Women\'s Herbal Supplements', 'Herbal supplements for women\'s wellness.', 'Herbal products for women\'s general wellness routines.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(58, 56, 'Women\'s Personal Care', 'womens-personal-care', 'Women\'s Ayurvedic Personal Care', 'Natural personal care products for women.', 'Natural and herbal personal care products for women.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(59, 56, 'Women\'s Hair Care', 'womens-hair-care', 'Women\'s Hair Care', 'Ayurvedic and herbal hair care products for women.', 'Herbal hair and scalp care products.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(60, NULL, 'Weight & Fitness Wellness', 'weight-fitness-wellness', 'Ayurvedic Weight & Fitness Wellness', 'Herbal products for healthy lifestyle and wellness routines.', 'Explore herbal products that complement healthy lifestyle routines.', NULL, 'Active', 12, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(61, 60, 'Weight Management', 'weight-management', 'Ayurvedic Weight Management', 'Herbal products for weight management routines.', 'Herbal products designed to complement healthy lifestyle routines.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(62, 60, 'Detox & Cleansing', 'detox-cleansing', 'Ayurvedic Detox & Cleansing', 'Traditional Ayurvedic products for cleansing and wellness routines.', 'Traditional products used as part of Ayurvedic cleansing routines.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(63, 60, 'Fitness Supplements', 'fitness-supplements', 'Herbal Fitness Supplements', 'Herbal wellness products for active lifestyles.', 'Herbal products for general fitness and wellness routines.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(64, NULL, 'Herbal Teas & Drinks', 'herbal-teas-drinks', 'Herbal Teas & Ayurvedic Drinks', 'Natural herbal teas and traditional Ayurvedic drinks.', 'Explore herbal teas and wellness beverages.', NULL, 'Active', 13, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(65, 64, 'Herbal Tea', 'herbal-tea', 'Herbal Tea', 'Natural herbal teas and tea blends.', 'Herbal tea blends for everyday enjoyment and wellness routines.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(66, 64, 'Ayurvedic Tea Blends', 'ayurvedic-tea-blends', 'Ayurvedic Tea Blends', 'Traditional herbal tea blends.', 'Traditional herbal tea combinations.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(67, 64, 'Herbal Drinks', 'herbal-drinks', 'Herbal Wellness Drinks', 'Natural herbal drinks and wellness beverages.', 'Herbal beverages for everyday wellness routines.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(68, NULL, 'Personal Care', 'personal-care', 'Ayurvedic Personal Care', 'Natural and Ayurvedic personal care products.', 'Natural and herbal personal care products for everyday use.', NULL, 'Active', 14, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(69, 68, 'Bath & Body', 'bath-body', 'Ayurvedic Bath & Body', 'Natural Ayurvedic bath and body products.', 'Herbal products for bathing and body care.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(70, 68, 'Herbal Soaps', 'herbal-soaps', 'Herbal Soaps', 'Natural herbal and Ayurvedic soaps.', 'Herbal bathing soaps made with traditional ingredients.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(71, 68, 'Oral Care', 'oral-care', 'Ayurvedic Oral Care', 'Natural Ayurvedic oral care products.', 'Traditional herbal oral care products.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(72, 68, 'Toothpaste', 'ayurvedic-toothpaste', 'Ayurvedic Toothpaste', 'Natural herbal and Ayurvedic toothpaste.', 'Herbal oral care products for everyday use.', NULL, 'Active', 4, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(73, 68, 'Mouth Fresheners', 'herbal-mouth-fresheners', 'Herbal Mouth Fresheners', 'Natural herbal mouth fresheners.', 'Traditional herbal mouth freshening products.', NULL, 'Active', 5, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(74, NULL, 'Ayurvedic Home & Lifestyle', 'ayurvedic-home-lifestyle', 'Ayurvedic Home & Lifestyle', 'Ayurvedic and natural products for home and lifestyle.', 'Products inspired by traditional Ayurvedic and natural living practices.', NULL, 'Active', 15, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(75, 74, 'Herbal Incense', 'herbal-incense', 'Herbal Incense', 'Natural herbal incense and traditional products.', 'Traditional aromatic and herbal products.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(76, 74, 'Ayurvedic Bath Products', 'ayurvedic-bath-products', 'Ayurvedic Bath Products', 'Natural Ayurvedic bathing products.', 'Herbal and Ayurvedic products for traditional bathing routines.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(77, 74, 'Wellness Accessories', 'wellness-accessories', 'Wellness Accessories', 'Accessories for traditional wellness routines.', 'Useful products and accessories for wellness practices.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(78, NULL, 'Ayurvedic Beauty', 'ayurvedic-beauty', 'Ayurvedic Beauty Products', 'Natural Ayurvedic beauty and personal care products.', 'Explore natural beauty products inspired by Ayurvedic traditions.', NULL, 'Active', 16, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(79, 78, 'Natural Face Care', 'natural-face-care', 'Natural Face Care', 'Natural and herbal face care products.', 'Plant-based and herbal products for face care.', NULL, 'Active', 1, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(80, 78, 'Natural Body Care', 'natural-body-care', 'Natural Body Care', 'Natural and herbal body care products.', 'Plant-based body care products.', NULL, 'Active', 2, '2026-09-10 11:20:54', '2026-09-10 11:20:54'),
(81, 78, 'Natural Beauty Products', 'natural-beauty-products', 'Natural Beauty Products', 'Natural and herbal beauty products.', 'Natural beauty products for everyday personal care.', NULL, 'Active', 3, '2026-09-10 11:20:54', '2026-09-10 11:20:54');

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
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `subtotal`, `discount`, `tax`, `shipping`, `total`, `coupon_id`, `coupon_code`, `payment_method`, `payment_status`, `order_status`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_country`, `shipping_pincode`, `tracking_number`, `shipping_provider`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-DEMO-0001', 399.00, 0.00, 0.00, 49.00, 448.00, NULL, NULL, 'COD', 'paid', 'delivered', 'Sunita Rani', '9812300001', '12 MG Road', 'Jaipur', 'Rajasthan', 'India', '302001', NULL, NULL, NULL, '2026-08-04 06:30:00', '2026-08-09 06:30:00'),
(2, 3, 'ORD-DEMO-0002', 499.00, 0.00, 0.00, 49.00, 548.00, NULL, NULL, 'COD', 'paid', 'delivered', 'Sumit Kaushik', '9812300002', '45 Sector 21', 'Gurugram', 'Haryana', 'India', '122001', NULL, NULL, NULL, '2026-08-07 06:30:00', '2026-08-12 06:30:00'),
(3, 4, 'ORD-DEMO-0003', 249.00, 0.00, 0.00, 49.00, 298.00, NULL, NULL, 'COD', 'paid', 'delivered', 'S Solanki', '9812300003', '7 Lake View Road', 'Pune', 'Maharashtra', 'India', '411001', NULL, NULL, NULL, '2026-08-11 06:30:00', '2026-08-16 06:30:00'),
(4, 5, 'ORD-DEMO-0004', 379.00, 0.00, 0.00, 49.00, 428.00, NULL, NULL, 'Prepaid', 'paid', 'delivered', 'Priya Verma', '9812300004', '9 Park Street', 'Kolkata', 'West Bengal', 'India', '700016', NULL, NULL, NULL, '2026-08-14 06:30:00', '2026-08-19 06:30:00'),
(5, 7, 'ORD-DEMO-0005', 329.00, 0.00, 0.00, 49.00, 378.00, NULL, NULL, 'COD', 'paid', 'delivered', 'Anjali Nair', '9812300006', '23 Marine Drive', 'Kochi', 'Kerala', 'India', '682001', NULL, NULL, NULL, '2026-08-22 06:30:00', '2026-08-27 06:30:00'),
(6, 9, 'ORD-DEMO-0006', 599.00, 0.00, 0.00, 49.00, 648.00, NULL, NULL, 'Prepaid', 'paid', 'delivered', 'Deepa Iyer', '9812300008', '61 Anna Salai', 'Chennai', 'Tamil Nadu', 'India', '600002', NULL, NULL, NULL, '2026-08-30 06:30:00', '2026-09-04 06:30:00'),
(7, 5, 'ORD-DEMO-0007', 249.00, 0.00, 0.00, 49.00, 298.00, NULL, NULL, 'Prepaid', 'paid', 'delivered', 'Priya Verma', '9812300004', '9 Park Street', 'Kolkata', 'West Bengal', 'India', '700016', NULL, NULL, NULL, '2026-09-01 06:30:00', '2026-09-06 06:30:00');

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `variant_name`, `sku`, `price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 1, NULL, 'Ashwagandha Herbal Capsules', NULL, NULL, 399.00, 1, 399.00, '2026-08-04 06:30:00'),
(2, 2, 4, NULL, 'Chyawanprash Herbal Blend', NULL, NULL, 499.00, 1, 499.00, '2026-08-07 06:30:00'),
(3, 3, 2, NULL, 'Triphala Churna', NULL, NULL, 249.00, 1, 249.00, '2026-08-11 06:30:00'),
(4, 4, 14, NULL, 'Turmeric Herbal Face Cream', NULL, NULL, 379.00, 1, 379.00, '2026-08-14 06:30:00'),
(5, 5, 10, NULL, 'Bhringraj Hair Oil', NULL, NULL, 329.00, 1, 329.00, '2026-08-22 06:30:00'),
(6, 6, 15, NULL, 'Kumkumadi Face Oil', NULL, NULL, 599.00, 1, 599.00, '2026-08-30 06:30:00'),
(7, 7, 13, NULL, 'Aloe Vera Herbal Face Wash', NULL, NULL, 249.00, 1, 249.00, '2026-09-01 06:30:00');

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

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, 'f75d22dff0f0a0e99d337e130a5e2d65d831b40245d6a348541a8f482aef1355', '2026-09-10 13:13:05', '2026-09-10 15:43:38', '2026-09-10 10:13:05'),
(2, 1, '53e95917a126aed1ac18c8991c3c03d786d514b83e9d9ca3ad83c59487dae767', '2026-09-10 13:13:38', NULL, '2026-09-10 10:13:38');

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
(1, 'Ashwagandha Herbal Capsules', 'ashwagandha-herbal-capsules', 'Traditional ashwagandha capsules for everyday wellness routines.', 'A herbal supplement featuring ashwagandha root extract, prepared as easy-to-use capsules for daily wellness routines.', 499.00, 399.00, 1, 0, 1, 1, 1, 0, 'Active', 'Ashwagandha Herbal Capsules | Ayurvedic Wellness', 'Shop ashwagandha herbal capsules for everyday Ayurvedic wellness routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(2, 'Triphala Churna', 'triphala-churna', 'Traditional Triphala herbal powder for digestive wellness routines.', 'A traditional blend of amla, haritaki and bibhitaki in powdered form.', 299.00, 249.00, 1, 0, 0, 1, 1, 1, 'Active', 'Triphala Churna | Traditional Herbal Powder', 'Traditional Triphala churna for everyday digestive wellness routines.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(3, 'Giloy Herbal Tablets', 'giloy-herbal-tablets', 'Traditional Giloy-based herbal tablets for daily wellness.', 'Herbal tablets containing giloy as a traditional Ayurvedic ingredient.', 349.00, 299.00, 1, 0, 1, 0, 1, 0, 'Active', 'Giloy Herbal Tablets | Ayurvedic Wellness', 'Shop Giloy herbal tablets for traditional daily wellness routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(4, 'Chyawanprash Herbal Blend', 'chyawanprash-herbal-blend', 'Traditional herbal wellness spread made with amla and herbs.', 'A traditional chyawanprash-style herbal preparation featuring amla and a blend of botanical ingredients.', 599.00, 499.00, 1, 0, 1, 1, 1, 1, 'Active', 'Chyawanprash Herbal Blend | Ayurvedic Wellness', 'Traditional herbal chyawanprash-style wellness preparation.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(5, 'Brahmi Herbal Capsules', 'brahmi-herbal-capsules', 'Brahmi-based herbal capsules for everyday wellness routines.', 'Capsules featuring brahmi as a traditional Ayurvedic botanical ingredient.', 449.00, 379.00, 1, 0, 0, 1, 1, 0, 'Active', 'Brahmi Herbal Capsules | Herbal Wellness', 'Brahmi herbal capsules for traditional daily wellness routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(6, 'Digestive Herbal Churna', 'digestive-herbal-churna', 'Traditional herbal powder for digestive wellness routines.', 'A powdered herbal blend designed for use as part of a traditional digestive wellness routine.', 279.00, 229.00, 1, 0, 0, 0, 1, 1, 'Active', 'Digestive Herbal Churna', 'Traditional herbal churna for digestive wellness.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(7, 'Jeera Ajwain Digestive Blend', 'jeera-ajwain-digestive-blend', 'Cumin and ajwain herbal blend for everyday digestive routines.', 'A traditional combination of cumin and ajwain in convenient powdered form.', 249.00, 199.00, 1, 0, 0, 0, 1, 0, 'Active', 'Jeera Ajwain Digestive Blend', 'Cumin and ajwain herbal blend for traditional digestive routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(8, 'Herbal Immunity Syrup', 'herbal-immunity-syrup', 'A botanical syrup for everyday wellness routines.', 'A traditional-style herbal syrup made with a blend of commonly used botanical ingredients.', 399.00, 329.00, 1, 0, 1, 1, 1, 1, 'Active', 'Herbal Immunity Syrup | Ayurvedic Wellness', 'Herbal syrup for everyday wellness routines.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(9, 'Amla Herbal Tablets', 'amla-herbal-tablets', 'Amla-based herbal tablets for daily wellness.', 'Herbal tablets featuring amla, a traditional Ayurvedic ingredient.', 329.00, 279.00, 1, 0, 0, 1, 1, 0, 'Active', 'Amla Herbal Tablets', 'Traditional amla herbal tablets for everyday wellness.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(10, 'Bhringraj Hair Oil', 'bhringraj-hair-oil', 'Traditional bhringraj hair oil for regular hair care.', 'A herbal hair oil featuring bhringraj and a blend of plant-based oils.', 399.00, 329.00, 1, 0, 1, 1, 1, 1, 'Active', 'Bhringraj Hair Oil | Ayurvedic Hair Care', 'Traditional herbal hair oil featuring bhringraj.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(11, 'Amla Hair Oil', 'amla-hair-oil', 'Amla-infused herbal hair oil for everyday hair care.', 'A traditional hair oil featuring amla and nourishing plant oils.', 349.00, 299.00, 1, 0, 0, 1, 1, 0, 'Active', 'Amla Hair Oil | Ayurvedic Hair Care', 'Amla herbal hair oil for everyday hair care routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(12, 'Neem Herbal Shampoo', 'neem-herbal-shampoo', 'Herbal shampoo featuring neem for everyday scalp and hair care.', 'A plant-based shampoo formulated with neem and other herbal ingredients.', 329.00, 279.00, 1, 0, 0, 1, 1, 1, 'Active', 'Neem Herbal Shampoo', 'Herbal neem shampoo for regular hair care.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(13, 'Aloe Vera Herbal Face Wash', 'aloe-vera-herbal-face-wash', 'Gentle herbal face wash with aloe vera.', 'A daily face cleanser featuring aloe vera and botanical ingredients.', 299.00, 249.00, 1, 0, 1, 0, 1, 0, 'Active', 'Aloe Vera Herbal Face Wash', 'Herbal aloe vera face wash for everyday cleansing.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(14, 'Turmeric Herbal Face Cream', 'turmeric-herbal-face-cream', 'Herbal face cream featuring turmeric and botanical ingredients.', 'A daily-use facial cream made with turmeric and plant-based ingredients.', 449.00, 379.00, 1, 0, 0, 1, 1, 1, 'Active', 'Turmeric Herbal Face Cream', 'Natural herbal face cream featuring turmeric.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(15, 'Kumkumadi Face Oil', 'kumkumadi-face-oil', 'Traditional-style herbal facial oil for a regular skin care routine.', 'A botanical facial oil inspired by traditional Kumkumadi oil preparations.', 699.00, 599.00, 1, 0, 1, 1, 1, 0, 'Active', 'Kumkumadi Face Oil | Ayurvedic Skin Care', 'Traditional-style Kumkumadi herbal face oil.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(16, 'Herbal Ubtan Face Pack', 'herbal-ubtan-face-pack', 'Traditional herbal ubtan powder for face care routines.', 'A powdered ubtan-style blend featuring traditional botanical and mineral ingredients.', 299.00, 249.00, 1, 0, 0, 0, 1, 1, 'Active', 'Herbal Ubtan Face Pack', 'Traditional herbal ubtan powder for face care.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(17, 'Sandalwood Herbal Soap', 'sandalwood-herbal-soap', 'Traditional herbal bathing soap with sandalwood.', 'A plant-based bathing soap featuring sandalwood fragrance and herbal ingredients.', 199.00, 159.00, 1, 0, 0, 1, 1, 0, 'Active', 'Sandalwood Herbal Soap', 'Herbal sandalwood bathing soap.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(18, 'Ayurvedic Abhyanga Massage Oil', 'ayurvedic-abhyanga-massage-oil', 'Traditional body massage oil for Ayurvedic-inspired routines.', 'A herbal massage oil inspired by traditional Abhyanga-style body massage practices.', 549.00, 449.00, 1, 0, 1, 1, 1, 1, 'Active', 'Ayurvedic Abhyanga Massage Oil', 'Traditional Ayurvedic-inspired massage oil.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(19, 'Mahanarayan Massage Oil', 'mahanarayan-massage-oil', 'Traditional herbal massage oil for body massage routines.', 'A traditional-style herbal oil blend intended for regular body massage routines.', 649.00, 549.00, 1, 0, 0, 1, 1, 0, 'Active', 'Mahanarayan Massage Oil', 'Traditional herbal massage oil for body care routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(20, 'Herbal Joint Massage Oil', 'herbal-joint-massage-oil', 'Herbal massage oil for traditional body massage routines.', 'A botanical oil blend designed for massage and personal body-care routines.', 449.00, 379.00, 1, 0, 0, 0, 1, 1, 'Active', 'Herbal Joint Massage Oil', 'Herbal oil for traditional massage routines.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(21, 'Herbal Joint Care Capsules', 'herbal-joint-care-capsules', 'Herbal capsules for general joint wellness routines.', 'A botanical supplement containing traditional herbal ingredients commonly used in wellness formulations.', 549.00, 449.00, 1, 0, 1, 0, 1, 0, 'Active', 'Herbal Joint Care Capsules', 'Herbal supplement for general joint wellness routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(22, 'Tulsi Ginger Herbal Tea', 'tulsi-ginger-herbal-tea', 'Aromatic tulsi and ginger herbal tea blend.', 'A caffeine-free herbal tea blend combining tulsi and ginger.', 249.00, 199.00, 1, 0, 1, 1, 1, 1, 'Active', 'Tulsi Ginger Herbal Tea', 'Tulsi and ginger herbal tea blend.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(23, 'Cinnamon Wellness Tea', 'cinnamon-wellness-tea', 'Warm cinnamon herbal tea blend for everyday enjoyment.', 'An aromatic herbal tea blend featuring cinnamon and complementary botanicals.', 299.00, 249.00, 1, 0, 0, 1, 1, 0, 'Active', 'Cinnamon Wellness Tea', 'Cinnamon herbal wellness tea blend.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(24, 'Herbal Relaxation Capsules', 'herbal-relaxation-capsules', 'Botanical capsules for relaxation-focused wellness routines.', 'A herbal supplement featuring traditional botanical ingredients used in relaxation-oriented wellness routines.', 499.00, 399.00, 1, 0, 0, 0, 1, 1, 'Active', 'Herbal Relaxation Capsules', 'Herbal capsules for relaxation-focused wellness routines.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(25, 'Herbal Sleep Wellness Tablets', 'herbal-sleep-wellness-tablets', 'Traditional herbal tablets for nighttime wellness routines.', 'A botanical tablet formulation intended for inclusion in a regular nighttime wellness routine.', 449.00, 379.00, 1, 0, 1, 0, 1, 0, 'Active', 'Herbal Sleep Wellness Tablets', 'Herbal tablets for nighttime wellness routines.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(26, 'Men\'s Herbal Wellness Capsules', 'mens-herbal-wellness-capsules', 'Herbal supplement for men\'s general wellness routines.', 'A botanical supplement formulated for men\'s general wellness routines.', 599.00, 499.00, 1, 0, 1, 1, 1, 1, 'Active', 'Men\'s Herbal Wellness Capsules', 'Herbal supplement for men\'s general wellness.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(27, 'Women\'s Herbal Wellness Capsules', 'womens-herbal-wellness-capsules', 'Herbal supplement for women\'s general wellness routines.', 'A botanical supplement formulated for women\'s general wellness routines.', 599.00, 499.00, 1, 0, 1, 1, 1, 0, 'Active', 'Women\'s Herbal Wellness Capsules', 'Herbal supplement for women\'s general wellness.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(28, 'Herbal Detox Tea', 'herbal-detox-tea', 'Botanical tea blend for cleansing-focused wellness routines.', 'An herbal tea blend made with traditional botanical ingredients.', 299.00, 249.00, 1, 0, 0, 1, 1, 1, 'Active', 'Herbal Detox Tea', 'Botanical herbal tea for wellness routines.', '2026-09-10 11:27:04', '2026-09-14 01:57:01'),
(29, 'Natural Herbal Toothpaste', 'natural-herbal-toothpaste', 'Herbal toothpaste for everyday oral care.', 'A plant-based toothpaste featuring traditional herbal ingredients for regular oral care.', 249.00, 199.00, 1, 0, 0, 1, 1, 0, 'Active', 'Natural Herbal Toothpaste', 'Natural herbal toothpaste for everyday oral care.', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(30, 'Herbal Mouth Freshener', 'herbal-mouth-freshener', 'Traditional herbal mouth freshener with aromatic botanicals.', 'A blend of aromatic herbs and spices intended for use as a traditional mouth freshener.', 179.00, 149.00, 1, 0, 0, 0, 1, 1, 'Active', 'Herbal Mouth Freshener', 'Traditional herbal mouth freshener blend.', '2026-09-10 11:27:04', '2026-09-14 01:57:01');

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
(1, 8, 1),
(2, 5, 1),
(3, 4, 1),
(4, 7, 1),
(5, 8, 1),
(6, 15, 1),
(7, 15, 1),
(8, 19, 1),
(9, 20, 1),
(10, 25, 1),
(11, 25, 1),
(12, 26, 1),
(13, 33, 1),
(14, 34, 1),
(15, 35, 1),
(16, 36, 1),
(17, 38, 1),
(18, 40, 1),
(19, 40, 1),
(20, 46, 1),
(21, 45, 1),
(22, 65, 1),
(23, 65, 1),
(24, 49, 1),
(25, 50, 1),
(26, 53, 1),
(27, 57, 1),
(28, 62, 1),
(28, 65, 0),
(29, 72, 1),
(30, 73, 1);

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
(5, 1, 'Ashwagandha root extract, vegetable capsule shell', 'Traditionally used in Ayurvedic wellness routines; suitable for a daily supplement format.', 'Take according to the product label and professional guidance.', 'Follow the manufacturer\'s recommended serving size.', 'Do not exceed the recommended serving. Consult a qualified professional if pregnant, nursing, taking medication, or managing a medical condition.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(6, 2, 'Amla, Haritaki, Bibhitaki', 'Traditional Triphala blend used in Ayurvedic digestive wellness routines.', 'Mix the recommended amount with water as directed on the label.', 'Use the serving size stated on the product label.', 'Use only as directed. Consult a qualified professional if you have a medical condition or take medication.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(7, 3, 'Giloy (Tinospora cordifolia)', 'Traditional herbal ingredient used in Ayurvedic wellness preparations.', 'Use according to the product label.', 'Follow the manufacturer\'s recommended dosage.', 'Do not exceed the recommended dosage. Seek professional advice when using medicines or during pregnancy.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(8, 4, 'Amla, herbal extracts, jaggery, botanical ingredients', 'Traditional-style herbal wellness preparation based around amla.', 'Consume according to the label instructions.', 'Follow the serving recommendation on the package.', 'Contains sweetening ingredients. Check the label for complete ingredients and allergens.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(9, 5, 'Brahmi (Bacopa monnieri) extract, vegetable capsule shell', 'Traditional botanical ingredient used in Ayurvedic wellness routines.', 'Use according to the product label.', 'Follow the manufacturer\'s recommended serving size.', 'Consult a qualified professional if pregnant, nursing, taking medication, or managing a medical condition.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(10, 6, 'Cumin, ajwain, fennel and traditional digestive herbs', 'Traditional herbal blend for digestive wellness routines.', 'Mix with water as directed on the package.', 'Follow the serving size stated on the label.', 'Use only as directed and discontinue if sensitivity occurs.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(11, 7, 'Cumin, ajwain, black salt and herbal spices', 'Traditional cumin and ajwain blend used around meals.', 'Use according to package directions.', 'Follow the recommended serving size.', 'Check ingredients for dietary sensitivities.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(12, 8, 'Tulsi, ginger, licorice and selected herbal extracts', 'Traditional botanical blend for everyday wellness routines.', 'Measure and consume according to label instructions.', 'Follow the manufacturer\'s recommended serving size.', 'Check the full ingredient list before use. Consult a professional when taking medication.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(13, 9, 'Amla extract', 'Traditional amla-based herbal wellness formulation.', 'Use according to label directions.', 'Follow the manufacturer\'s recommended dosage.', 'Use only as directed.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(14, 10, 'Bhringraj, amla, coconut oil, sesame oil and herbal extracts', 'Traditional herbal hair oil for regular scalp and hair-care routines.', 'Massage a small amount into the scalp and hair as directed on the label.', 'Use as needed as part of a regular hair-care routine.', 'For external use only. Avoid contact with eyes and discontinue if irritation occurs.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(15, 11, 'Amla, coconut oil, sesame oil and herbal extracts', 'Traditional amla-based hair oil for regular hair care.', 'Apply to scalp and hair as directed.', 'Use as needed.', 'For external use only. Avoid contact with eyes.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(16, 12, 'Neem extract, aloe vera, shikakai and herbal cleansers', 'Herbal shampoo for everyday hair and scalp cleansing.', 'Wet hair, apply shampoo, gently massage and rinse.', 'Use according to personal hair-care needs.', 'For external use only. Avoid contact with eyes.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(17, 13, 'Aloe vera, neem, turmeric and botanical cleansing ingredients', 'Gentle herbal cleanser for daily face-care routines.', 'Apply to wet face, massage gently and rinse.', 'Use once or twice daily as suitable.', 'For external use only. Patch test before regular use if sensitive.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(18, 14, 'Turmeric, aloe vera, botanical oils and plant extracts', 'Herbal facial moisturizer for everyday skin-care routines.', 'Apply a small amount to clean skin.', 'Use as part of a daily skin-care routine.', 'For external use only. Patch test before use.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(19, 15, 'Sesame oil, saffron, manjistha, sandalwood and traditional botanical ingredients', 'Traditional-style botanical facial oil for regular skin-care routines.', 'Apply a small amount to clean skin as directed.', 'Use according to product instructions.', 'For external use only. Patch test before use. Avoid contact with eyes.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(20, 16, 'Turmeric, gram flour, sandalwood, neem and herbal powders', 'Traditional-style ubtan powder for face-care routines.', 'Mix with water or a suitable carrier, apply and rinse as directed.', 'Use according to skin-care routine.', 'For external use only. Patch test before use.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(21, 17, 'Sandalwood fragrance, coconut oil, herbal extracts and soap base', 'Herbal bathing soap for everyday cleansing.', 'Wet skin, lather and rinse thoroughly.', 'Use as part of a normal bathing routine.', 'For external use only. Avoid contact with eyes.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(22, 18, 'Sesame oil, bala, ashwagandha, dashamoola and herbal extracts', 'Traditional Ayurvedic-inspired body massage oil.', 'Warm a small quantity if desired and massage onto the body as directed.', 'Use according to massage routine.', 'For external use only. Patch test before use.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(23, 19, 'Sesame oil, ashwagandha, dashamoola and traditional herbal extracts', 'Traditional-style herbal massage oil for body-care routines.', 'Massage a suitable quantity onto the body.', 'Use as needed.', 'For external use only. Patch test before use.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(24, 20, 'Sesame oil, castor oil, eucalyptus and traditional herbal extracts', 'Herbal oil for massage-focused personal care routines.', 'Apply a small amount and massage gently.', 'Use according to personal routine.', 'For external use only. Avoid broken skin and eyes.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(25, 21, 'Guggul, shallaki, turmeric and selected botanical extracts', 'Herbal supplement designed for general joint wellness routines.', 'Use according to product label.', 'Follow the manufacturer\'s recommended serving size.', 'Consult a qualified professional if taking medication or managing a medical condition.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(26, 22, 'Tulsi, ginger, lemongrass and natural herbal ingredients', 'Aromatic herbal tea blend for everyday enjoyment.', 'Steep one serving in hot water according to package directions.', 'Use according to preference.', 'Check ingredients for sensitivities.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(27, 23, 'Cinnamon, cardamom, ginger and herbal tea ingredients', 'Warm aromatic tea blend for everyday wellness and enjoyment.', 'Steep in hot water as directed.', 'Use as preferred.', 'Check ingredients for sensitivities.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(28, 24, 'Ashwagandha, brahmi, jatamansi and botanical extracts', 'Herbal formulation for relaxation-focused wellness routines.', 'Use according to label directions.', 'Follow the recommended serving size.', 'Consult a qualified professional if taking medication or managing a medical condition.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(29, 25, 'Ashwagandha, brahmi, jatamansi and traditional herbal extracts', 'Botanical formulation intended for a regular nighttime wellness routine.', 'Use according to product label.', 'Follow the recommended dosage.', 'Do not combine with medicines or supplements without professional advice.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(30, 26, 'Ashwagandha, shatavari, gokshura and botanical extracts', 'Herbal supplement for men\'s general wellness routines.', 'Use according to label directions.', 'Follow the recommended serving size.', 'Consult a qualified professional if taking medication or managing a medical condition.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(31, 27, 'Shatavari, amla, ashwagandha and botanical extracts', 'Herbal supplement for women\'s general wellness routines.', 'Use according to label directions.', 'Follow the recommended serving size.', 'Consult a qualified professional during pregnancy, nursing, or when taking medication.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(32, 28, 'Green tea, tulsi, ginger, fennel and herbal botanicals', 'Botanical tea blend for cleansing-focused wellness routines.', 'Steep in hot water according to package directions.', 'Use according to preference.', 'Contains plant ingredients. Check the complete label before use.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(33, 29, 'Neem, clove, miswak, tulsi and herbal ingredients', 'Herbal toothpaste for regular oral-care routines.', 'Brush with a small amount and rinse thoroughly.', 'Use twice daily or as recommended by a dental professional.', 'For oral use only. Do not swallow.', 'Demo Ayurveda Pvt. Ltd.', 'India', '24 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(34, 30, 'Fennel, cardamom, clove and aromatic herbs', 'Traditional aromatic herbal blend used as a mouth freshener.', 'Chew a small quantity after meals or as directed.', 'Use according to package directions.', 'Do not exceed the recommended serving. Check ingredients for sensitivities.', 'Demo Ayurveda Pvt. Ltd.', 'India', '18 months', '2026-09-10 11:27:04', '2026-09-10 11:27:04');

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
(1, 1, 'uploads/products/ashwagandha-herbal-capsules-1.webp', 'Ashwagandha Herbal Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(2, 1, 'uploads/products/ashwagandha-herbal-capsules-2.webp', 'Ashwagandha Herbal Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(3, 2, 'uploads/products/triphala-churna-1.webp', 'Triphala Churna product image 1', 1, 1, '2026-09-10 11:27:04'),
(4, 2, 'uploads/products/triphala-churna-2.webp', 'Triphala Churna product image 2', 0, 2, '2026-09-10 11:27:04'),
(5, 3, 'uploads/products/giloy-herbal-tablets-1.webp', 'Giloy Herbal Tablets product image 1', 1, 1, '2026-09-10 11:27:04'),
(6, 3, 'uploads/products/giloy-herbal-tablets-2.webp', 'Giloy Herbal Tablets product image 2', 0, 2, '2026-09-10 11:27:04'),
(7, 4, 'uploads/products/chyawanprash-herbal-blend-1.webp', 'Chyawanprash Herbal Blend product image 1', 1, 1, '2026-09-10 11:27:04'),
(8, 4, 'uploads/products/chyawanprash-herbal-blend-2.webp', 'Chyawanprash Herbal Blend product image 2', 0, 2, '2026-09-10 11:27:04'),
(9, 5, 'uploads/products/brahmi-herbal-capsules-1.webp', 'Brahmi Herbal Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(10, 5, 'uploads/products/brahmi-herbal-capsules-2.webp', 'Brahmi Herbal Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(11, 6, 'uploads/products/digestive-herbal-churna-1.webp', 'Digestive Herbal Churna product image 1', 1, 1, '2026-09-10 11:27:04'),
(12, 6, 'uploads/products/digestive-herbal-churna-2.webp', 'Digestive Herbal Churna product image 2', 0, 2, '2026-09-10 11:27:04'),
(13, 7, 'uploads/products/jeera-ajwain-digestive-blend-1.webp', 'Jeera Ajwain Digestive Blend product image 1', 1, 1, '2026-09-10 11:27:04'),
(14, 7, 'uploads/products/jeera-ajwain-digestive-blend-2.webp', 'Jeera Ajwain Digestive Blend product image 2', 0, 2, '2026-09-10 11:27:04'),
(15, 8, 'uploads/products/herbal-immunity-syrup-1.webp', 'Herbal Immunity Syrup product image 1', 1, 1, '2026-09-10 11:27:04'),
(16, 8, 'uploads/products/herbal-immunity-syrup-2.webp', 'Herbal Immunity Syrup product image 2', 0, 2, '2026-09-10 11:27:04'),
(17, 9, 'uploads/products/amla-herbal-tablets-1.webp', 'Amla Herbal Tablets product image 1', 1, 1, '2026-09-10 11:27:04'),
(18, 9, 'uploads/products/amla-herbal-tablets-2.webp', 'Amla Herbal Tablets product image 2', 0, 2, '2026-09-10 11:27:04'),
(19, 10, 'uploads/products/bhringraj-hair-oil-1.webp', 'Bhringraj Hair Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(20, 10, 'uploads/products/bhringraj-hair-oil-2.webp', 'Bhringraj Hair Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(21, 11, 'uploads/products/amla-hair-oil-1.webp', 'Amla Hair Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(22, 11, 'uploads/products/amla-hair-oil-2.webp', 'Amla Hair Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(23, 12, 'uploads/products/neem-herbal-shampoo-1.webp', 'Neem Herbal Shampoo product image 1', 1, 1, '2026-09-10 11:27:04'),
(24, 12, 'uploads/products/neem-herbal-shampoo-2.webp', 'Neem Herbal Shampoo product image 2', 0, 2, '2026-09-10 11:27:04'),
(25, 13, 'uploads/products/aloe-vera-herbal-face-wash-1.webp', 'Aloe Vera Herbal Face Wash product image 1', 1, 1, '2026-09-10 11:27:04'),
(26, 13, 'uploads/products/aloe-vera-herbal-face-wash-2.webp', 'Aloe Vera Herbal Face Wash product image 2', 0, 2, '2026-09-10 11:27:04'),
(27, 14, 'uploads/products/turmeric-herbal-face-cream-1.webp', 'Turmeric Herbal Face Cream product image 1', 1, 1, '2026-09-10 11:27:04'),
(28, 14, 'uploads/products/turmeric-herbal-face-cream-2.webp', 'Turmeric Herbal Face Cream product image 2', 0, 2, '2026-09-10 11:27:04'),
(29, 15, 'uploads/products/kumkumadi-face-oil-1.webp', 'Kumkumadi Face Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(30, 15, 'uploads/products/kumkumadi-face-oil-2.webp', 'Kumkumadi Face Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(31, 16, 'uploads/products/herbal-ubtan-face-pack-1.webp', 'Herbal Ubtan Face Pack product image 1', 1, 1, '2026-09-10 11:27:04'),
(32, 16, 'uploads/products/herbal-ubtan-face-pack-2.webp', 'Herbal Ubtan Face Pack product image 2', 0, 2, '2026-09-10 11:27:04'),
(33, 17, 'uploads/products/sandalwood-herbal-soap-1.webp', 'Sandalwood Herbal Soap product image 1', 1, 1, '2026-09-10 11:27:04'),
(34, 17, 'uploads/products/sandalwood-herbal-soap-2.webp', 'Sandalwood Herbal Soap product image 2', 0, 2, '2026-09-10 11:27:04'),
(35, 18, 'uploads/products/ayurvedic-abhyanga-massage-oil-1.webp', 'Ayurvedic Abhyanga Massage Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(36, 18, 'uploads/products/ayurvedic-abhyanga-massage-oil-2.webp', 'Ayurvedic Abhyanga Massage Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(37, 19, 'uploads/products/mahanarayan-massage-oil-1.webp', 'Mahanarayan Massage Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(38, 19, 'uploads/products/mahanarayan-massage-oil-2.webp', 'Mahanarayan Massage Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(39, 20, 'uploads/products/herbal-joint-massage-oil-1.webp', 'Herbal Joint Massage Oil product image 1', 1, 1, '2026-09-10 11:27:04'),
(40, 20, 'uploads/products/herbal-joint-massage-oil-2.webp', 'Herbal Joint Massage Oil product image 2', 0, 2, '2026-09-10 11:27:04'),
(41, 21, 'uploads/products/herbal-joint-care-capsules-1.webp', 'Herbal Joint Care Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(42, 21, 'uploads/products/herbal-joint-care-capsules-2.webp', 'Herbal Joint Care Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(43, 22, 'uploads/products/tulsi-ginger-herbal-tea-1.webp', 'Tulsi Ginger Herbal Tea product image 1', 1, 1, '2026-09-10 11:27:04'),
(44, 22, 'uploads/products/tulsi-ginger-herbal-tea-2.webp', 'Tulsi Ginger Herbal Tea product image 2', 0, 2, '2026-09-10 11:27:04'),
(45, 23, 'uploads/products/cinnamon-wellness-tea-1.webp', 'Cinnamon Wellness Tea product image 1', 1, 1, '2026-09-10 11:27:04'),
(46, 23, 'uploads/products/cinnamon-wellness-tea-2.webp', 'Cinnamon Wellness Tea product image 2', 0, 2, '2026-09-10 11:27:04'),
(47, 24, 'uploads/products/herbal-relaxation-capsules-1.webp', 'Herbal Relaxation Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(48, 24, 'uploads/products/herbal-relaxation-capsules-2.webp', 'Herbal Relaxation Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(49, 25, 'uploads/products/herbal-sleep-wellness-tablets-1.webp', 'Herbal Sleep Wellness Tablets product image 1', 1, 1, '2026-09-10 11:27:04'),
(50, 25, 'uploads/products/herbal-sleep-wellness-tablets-2.webp', 'Herbal Sleep Wellness Tablets product image 2', 0, 2, '2026-09-10 11:27:04'),
(51, 26, 'uploads/products/mens-herbal-wellness-capsules-1.webp', 'Men\'s Herbal Wellness Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(52, 26, 'uploads/products/mens-herbal-wellness-capsules-2.webp', 'Men\'s Herbal Wellness Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(53, 27, 'uploads/products/womens-herbal-wellness-capsules-1.webp', 'Women\'s Herbal Wellness Capsules product image 1', 1, 1, '2026-09-10 11:27:04'),
(54, 27, 'uploads/products/womens-herbal-wellness-capsules-2.webp', 'Women\'s Herbal Wellness Capsules product image 2', 0, 2, '2026-09-10 11:27:04'),
(55, 28, 'uploads/products/herbal-detox-tea-1.webp', 'Herbal Detox Tea product image 1', 1, 1, '2026-09-10 11:27:04'),
(56, 28, 'uploads/products/herbal-detox-tea-2.webp', 'Herbal Detox Tea product image 2', 0, 2, '2026-09-10 11:27:04'),
(57, 29, 'uploads/products/natural-herbal-toothpaste-1.webp', 'Natural Herbal Toothpaste product image 1', 1, 1, '2026-09-10 11:27:04'),
(58, 29, 'uploads/products/natural-herbal-toothpaste-2.webp', 'Natural Herbal Toothpaste product image 2', 0, 2, '2026-09-10 11:27:04'),
(59, 30, 'uploads/products/herbal-mouth-freshener-1.webp', 'Herbal Mouth Freshener product image 1', 1, 1, '2026-09-10 11:27:04'),
(60, 30, 'uploads/products/herbal-mouth-freshener-2.webp', 'Herbal Mouth Freshener product image 2', 0, 2, '2026-09-10 11:27:04');

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

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `sku`, `price`, `sale_price`, `stock`, `weight_grams`, `is_default`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, '60 capsules', 'AYU-001-01', 499.00, 399.00, 57, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(2, 1, '120 capsules', 'AYU-001-02', 848.00, 678.00, 70, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(3, 2, '100 g', 'AYU-002-01', 299.00, 249.00, 64, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(4, 2, '200 g', 'AYU-002-02', 508.00, 423.00, 77, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(5, 3, '60 capsules', 'AYU-003-01', 349.00, 299.00, 71, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(6, 3, '120 capsules', 'AYU-003-02', 593.00, 508.00, 84, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(7, 4, '100 g', 'AYU-004-01', 299.00, 249.00, 78, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(8, 4, '200 g', 'AYU-004-02', 499.00, 399.00, 91, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(9, 5, '60 capsules', 'AYU-005-01', 449.00, 379.00, 85, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(10, 5, '120 capsules', 'AYU-005-02', 763.00, 644.00, 98, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(11, 6, '100 g', 'AYU-006-01', 279.00, 229.00, 92, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(12, 6, '200 g', 'AYU-006-02', 474.00, 389.00, 105, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(13, 7, '100 g', 'AYU-007-01', 299.00, 249.00, 99, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(14, 7, '200 g', 'AYU-007-02', 499.00, 399.00, 112, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(15, 8, '100 ml', 'AYU-008-01', 399.00, 329.00, 106, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(16, 8, '200 ml', 'AYU-008-02', 678.00, 559.00, 119, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(17, 9, '60 capsules', 'AYU-009-01', 329.00, 279.00, 113, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(18, 9, '120 capsules', 'AYU-009-02', 559.00, 474.00, 126, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(19, 10, '100 ml', 'AYU-010-01', 399.00, 329.00, 120, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(20, 10, '200 ml', 'AYU-010-02', 678.00, 559.00, 133, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(21, 11, '100 ml', 'AYU-011-01', 349.00, 299.00, 127, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(22, 11, '200 ml', 'AYU-011-02', 593.00, 508.00, 140, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(23, 12, '100 ml', 'AYU-012-01', 329.00, 279.00, 134, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(24, 12, '200 ml', 'AYU-012-02', 559.00, 474.00, 147, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(25, 13, '100 ml', 'AYU-013-01', 299.00, 249.00, 141, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(26, 13, '200 ml', 'AYU-013-02', 508.00, 423.00, 154, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(27, 14, '100 ml', 'AYU-014-01', 449.00, 379.00, 148, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(28, 14, '200 ml', 'AYU-014-02', 763.00, 644.00, 161, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(29, 15, '100 ml', 'AYU-015-01', 699.00, 599.00, 155, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(30, 15, '200 ml', 'AYU-015-02', 1188.00, 1018.00, 168, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(31, 16, '100 g', 'AYU-016-01', 299.00, 249.00, 162, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(32, 16, '200 g', 'AYU-016-02', 508.00, 423.00, 55, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(33, 17, '100 g', 'AYU-017-01', 199.00, 159.00, 169, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(34, 17, '3 x 100 g', 'AYU-017-02', 517.00, 413.00, 62, 300, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(35, 18, '100 ml', 'AYU-018-01', 549.00, 449.00, 56, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(36, 18, '200 ml', 'AYU-018-02', 933.00, 763.00, 69, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(37, 19, '100 ml', 'AYU-019-01', 649.00, 549.00, 63, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(38, 19, '200 ml', 'AYU-019-02', 1103.00, 933.00, 76, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(39, 20, '100 ml', 'AYU-020-01', 449.00, 379.00, 70, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(40, 20, '200 ml', 'AYU-020-02', 763.00, 644.00, 83, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(41, 21, '60 capsules', 'AYU-021-01', 549.00, 449.00, 77, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(42, 21, '120 capsules', 'AYU-021-02', 933.00, 763.00, 90, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(43, 22, '100 ml', 'AYU-022-01', 249.00, 199.00, 84, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(44, 22, '200 ml', 'AYU-022-02', 423.00, 338.00, 97, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(45, 23, '100 ml', 'AYU-023-01', 299.00, 249.00, 91, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(46, 23, '200 ml', 'AYU-023-02', 508.00, 423.00, 104, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(47, 24, '60 capsules', 'AYU-024-01', 499.00, 399.00, 98, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(48, 24, '120 capsules', 'AYU-024-02', 848.00, 678.00, 111, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(49, 25, '60 capsules', 'AYU-025-01', 449.00, 379.00, 105, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(50, 25, '120 capsules', 'AYU-025-02', 763.00, 644.00, 118, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(51, 26, '60 capsules', 'AYU-026-01', 599.00, 499.00, 112, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(52, 26, '120 capsules', 'AYU-026-02', 1018.00, 848.00, 125, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(53, 27, '60 capsules', 'AYU-027-01', 599.00, 499.00, 119, 60, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(54, 27, '120 capsules', 'AYU-027-02', 1018.00, 848.00, 132, 120, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(55, 28, '100 ml', 'AYU-028-01', 299.00, 249.00, 126, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(56, 28, '200 ml', 'AYU-028-02', 508.00, 423.00, 139, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(57, 29, '100 g', 'AYU-029-01', 249.00, 199.00, 133, 100, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(58, 29, '200 g', 'AYU-029-02', 423.00, 338.00, 146, 200, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(59, 30, '50 g', 'AYU-030-01', 179.00, 149.00, 140, 50, 1, 'Active', 1, '2026-09-10 11:27:04', '2026-09-10 11:27:04'),
(60, 30, '100 g', 'AYU-030-02', 304.00, 253.00, 153, 100, 0, 'Active', 2, '2026-09-10 11:27:04', '2026-09-10 11:27:04');

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `review`, `status`, `verified_purchase`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 'For me, using Maharishi Ayurveda products regularly is a great step towards preventive care. The brand is known for its high-quality Ayurvedic products and I never stopped taking whatever I shopped from them. I highly recommend Maharishi Ayurveda!', 'Active', 1, '2026-08-10 03:30:00', '2026-08-10 03:30:00'),
(2, 4, 3, 5, 'I try to use only Ayurvedic products and order them from different Ayurvedic brands. But Maharishi Ayurveda is by far the best one. I loved this brand due to its product quality and good results. Excellent products, fast shipping, responsive and friendly customer service, and great experience.', 'Active', 1, '2026-08-13 05:00:00', '2026-08-13 05:00:00'),
(3, 2, 4, 5, 'It\'s the one-stop shop for all my Ayurveda health needs. I have been their regular customer for the past 5 years and the reason I chose them each time is that their formulations are authentic, and the service is dependable.', 'Active', 1, '2026-08-17 10:15:00', '2026-08-17 10:15:00'),
(4, 14, 5, 4, 'Noticed a visible glow after about two weeks of regular use. Doesn\'t feel heavy on the skin and layers well under makeup. Would buy again.', 'Active', 1, '2026-08-20 02:50:00', '2026-08-20 02:50:00'),
(5, 21, 6, 4, 'Helped ease my knee stiffness noticeably within a couple of weeks. Still monitoring how it holds up long term, but happy with it so far.', 'Active', 0, '2026-08-23 13:40:00', '2026-08-23 13:40:00'),
(6, 10, 7, 5, 'Reduced my hair fall within a month of regular use, and it smells great too — not the usual heavy medicinal smell. Will be repurchasing.', 'Active', 1, '2026-08-28 02:05:00', '2026-08-28 02:05:00'),
(7, 8, 8, 3, 'Decent product overall, does what it says. The taste could be better though — took some getting used to.', 'Active', 0, '2026-08-29 06:30:00', '2026-08-29 06:30:00'),
(8, 15, 9, 5, 'Luxurious feel and a little goes a long way. My skin feels noticeably softer the morning after. Worth the price for how long a bottle lasts.', 'Active', 1, '2026-09-01 15:45:00', '2026-09-01 15:45:00'),
(9, 3, 1, 4, 'Good general immunity support and the tablets are easy to swallow, unlike some other herbal tablets I\'ve tried. No complaints so far.', 'Active', 0, '2026-09-03 05:30:00', '2026-09-03 05:30:00'),
(10, 22, 2, 5, 'Perfect evening wind-down tea, very soothing and not too strong on the ginger. Part of my nightly routine now.', 'Active', 0, '2026-09-05 15:10:00', '2026-09-05 15:10:00'),
(11, 17, 3, 4, 'Nice mild fragrance and gentle on the skin, doesn\'t dry it out like some soaps do. Just waiting to see how long a bar lasts before buying more.', 'Pending', 0, '2026-09-08 03:55:00', '2026-09-08 03:55:00'),
(12, 9, 4, 2, 'Didn\'t see much difference for me personally after a few weeks, though I know results can vary by person.', 'Pending', 0, '2026-09-09 12:20:00', '2026-09-09 12:20:00'),
(13, 13, 5, 5, 'My go-to face wash now — no more breakouts since I switched, and it doesn\'t leave my skin feeling tight or dry afterward.', 'Pending', 1, '2026-09-10 08:35:00', '2026-09-10 08:35:00'),
(14, 24, 6, 1, 'Packaging arrived damaged and I honestly haven\'t noticed any effect after finishing the bottle.', 'Rejected', 0, '2026-09-11 02:30:00', '2026-09-11 02:30:00'),
(15, 25, 8, 5, 'Helped me fall asleep faster within just a few days of taking it, genuinely surprised by how well it worked for me.', 'Rejected', 0, '2026-09-12 17:00:00', '2026-09-12 17:00:00');

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

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Ayurveda Store', '2026-09-02 05:53:51'),
(2, 'currency', 'INR', '2026-09-02 05:53:51'),
(3, 'currency_symbol', '₹', '2026-09-02 05:53:51'),
(4, 'tax_enabled', '1', '2026-09-02 05:53:51'),
(5, 'tax_percentage', '0', '2026-09-02 05:53:51'),
(6, 'shipping_enabled', '1', '2026-09-02 05:53:51'),
(7, 'free_shipping_minimum', '0', '2026-09-02 05:53:51');

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

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `designation`, `bio`, `image`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Priyanka Jagota', 'Maharishi Expert Vaidya', 'Dr. Priyanka Jagota is an experienced Ayurvedic physician with over seven years of clinical practice. She specialises in managing fatty liver, digestive disorders, joint pain, chronic inflammation, and hormonal imbalances including thyroid, PCOS/PCOD, and menopausal concerns. Her approach focuses on root-cause healing through classical Ayurveda, dietary correction, and sustainable lifestyle changes.', NULL, 1, 1, '2026-09-14 02:25:37', '2026-09-14 02:25:37'),
(2, 'Dr. Arvind Sharma', 'Senior Ayurvedic Consultant', 'Dr. Arvind Sharma brings over a decade of clinical experience in classical Ayurvedic medicine, with a focus on chronic pain management, respiratory conditions, and stress-related disorders. He combines traditional diagnostic methods with personalised herbal and lifestyle protocols.', NULL, 1, 2, '2026-09-14 02:25:37', '2026-09-14 02:25:37'),
(3, 'Dr. Neha Kulkarni', 'Ayurvedic Skin & Hair Specialist', 'Dr. Neha Kulkarni specialises in Ayurvedic dermatology, treating skin and hair concerns through internal and external herbal therapies rooted in classical texts, paired with modern lifestyle guidance.', NULL, 1, 3, '2026-09-14 02:25:37', '2026-09-14 02:25:37');

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

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ajay', 'ajay@gmail.com', NULL, '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-09-10 10:10:41', '2026-09-10 10:10:41'),
(2, 'Sunita Rani', 'sunita.rani@example.com', '9812300001', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-02 04:45:00', '2026-08-02 04:45:00'),
(3, 'Sumit Kaushik', 'sumit.kaushik@example.com', '9812300002', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-05 04:10:00', '2026-08-05 04:10:00'),
(4, 'S Solanki', 's.solanki@example.com', '9812300003', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-09 08:52:00', '2026-08-09 08:52:00'),
(5, 'Priya Verma', 'priya.verma@example.com', '9812300004', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-12 12:35:00', '2026-08-12 12:35:00'),
(6, 'Rakesh Gupta', 'rakesh.gupta@example.com', '9812300005', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-15 06:00:00', '2026-08-15 06:00:00'),
(7, 'Anjali Nair', 'anjali.nair@example.com', '9812300006', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-20 03:20:00', '2026-08-20 03:20:00'),
(8, 'Manoj Tiwari', 'manoj.tiwari@example.com', '9812300007', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-24 10:40:00', '2026-08-24 10:40:00'),
(9, 'Deepa Iyer', 'deepa.iyer@example.com', '9812300008', '$2y$10$Qo5Jp9UHNphg9ckroLIuNeDm/WbXQKlI26uotfOzOikGfHlwgx8K2', 'Active', '2026-08-28 07:35:00', '2026-08-28 07:35:00');

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
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_newsletter_subscribers_email` (`email`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

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
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
