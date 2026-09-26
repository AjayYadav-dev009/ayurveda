-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 09:34 AM
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

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `country`, `pincode`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ajay Yadav', '+91-11-45052477', 'Tikri Boarder', '', 'Jharoda Kalan', 'Delhi', 'India', '110072', 1, '2026-09-23 10:13:01', '2026-09-23 10:13:01');

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

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `button_text`, `button_url`, `position`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Banner 1 for home Page', NULL, 'banner5-e18e1eaa7faa.jpg', NULL, 'http://localhost/ayurveda/products/products.php?category_slug=mens-wellness', 'homepage_hero', 1, 1, '2026-09-22 18:17:00', '2026-09-25 11:23:34'),
(2, 'Home banner 2', NULL, 'banner2-fe5258332939.jpg', NULL, 'http://localhost/ayurveda/products/products.php?category_slug=weight-loss', 'homepage_hero', 1, 2, '2026-09-22 18:17:32', '2026-09-25 11:11:10'),
(3, 'Detux Banner 1', NULL, 'combo-44311f7e1dd1-723a9e2240b2.png', NULL, NULL, 'detux_hero', 1, 1, '2026-09-24 06:45:16', '2026-09-24 06:45:16'),
(4, NULL, NULL, '6092ed21277cc3e0f78ea993e4efce92-fa50a1ecaf22.jpg', NULL, NULL, 'detox_consultation_cta', 1, 0, '2026-09-24 07:03:50', '2026-09-24 07:03:50'),
(5, 'Personalised Ayurvedic Care, Rooted in You', NULL, 'consult-veda-370047a8a3dc.jpg', NULL, NULL, 'consult_veda_hero', 1, 1, '2026-09-24 08:49:24', '2026-09-24 08:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
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
  `hero_image` varchar(500) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Draft','Active','Inactive') NOT NULL DEFAULT 'Draft',
  `published_at` datetime DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `hero_image`, `category_id`, `status`, `published_at`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'Beyond the Trend: How to Actually Integrate Ayurvedic Products into Daily Life', 'beyond-the-trend-how-to-actually-integrate-ayurvedic-products-into-daily-life', 'Walking down modern wellness aisles, ancient Sanskrit names like Ashwagandha, Triphala, and Kumkumadi appear everywhere. Yet treating Ayurvedic formulations as mere drop-in supplements misses their true purpose.', '<p>In traditional Ayurveda, health is rooted in balance across your constitutional energies (<strong>Doshas</strong>—Vata, Pitta, and Kapha) and the strength of your digestive fire (<strong>Agni</strong>). Ayurvedic products are not quick fixes designed to mask symptoms; they are botanical and mineral tools formulated to support the body’s innate rhythm and restorative capacity.</p><h2>Foundational Ayurvedic Formulations Worth Knowing</h2><p>Instead of overcrowding your medicine cabinet, focusing on a few core, time-tested formulations provides the greatest systemic benefit.</p><h3>1. Ashwagandha (<i>Withania somnifera</i>) — The Adaptogenic Balancer</h3><ul><li><strong>Primary Role:</strong> Calming the nervous system, modulating cortisol, and nourishing depleted tissues (<i>Dhatus</i>).</li><li><strong>Best For:</strong> Stress relief, cognitive fatigue, and restless sleep patterns caused by excess Vata.</li><li><strong>How to Use:</strong> Typically taken as a warm decoction or powder (<i>churna</i>) blended with warm milk or plant milk and a pinch of nutmeg before bed.</li></ul><h3>2. Triphala — The Digestive Tonic</h3><ul><li><strong>Primary Role:</strong> Gentle bowel regulation, detoxification, and gut microbiome balance.</li><li><strong>What It Contains:</strong> An equal blend of three dried fruits: <i>Amalaki</i> (Indian Gooseberry), <i>Bibhitaki</i>, and <i>Haritaki</i>.</li><li><strong>Why It Works:</strong> Unlike harsh chemical laxatives, Triphala tones the intestinal muscles and supports nutrient absorption without creating dependency.</li></ul><h3>3. Kumkumadi Tailam — The Saffron Facial Elixir</h3><ul><li><strong>Primary Role:</strong> Evening out skin tone, improving radiance, and calming surface inflammation.</li><li><strong>What It Contains:</strong> A classical herbal oil infused with Kashmiri saffron, red sandalwood, vetiver, and lotus stamens cooked in sesame oil and goat\'s milk.</li><li><strong>How to Use:</strong> Press 2–3 drops onto clean, damp skin at night. It is particularly soothing for combination and Pitta-prone, reactive skin.</li></ul><h3>4. Brahmi (<i>Bacopa monnieri</i>) — The Mind Cleanser</h3><ul><li><strong>Primary Role:</strong> Supporting memory retention, emotional calm, and laser focus.</li><li><strong>Best For:</strong> High-stress work environments and mental burnout where the mind feels overheated and scattered.</li></ul><h2>Choosing Wisely: Modern Formulations vs. Your Dosha</h2><p>Not every Ayurvedic herb suits every body type. Here is how common formulations map across constitutional profiles:</p><figure class=\"table\"><table><thead><tr><th><strong>Formulation</strong></th><th><strong>Energetic Nature</strong></th><th><strong>Best Suited For</strong></th><th><strong>Key Benefit</strong></th></tr></thead><tbody><tr><td><strong>Ashwagandha</strong></td><td>Warming, grounding</td><td>Vata &amp; Kapha</td><td>Rebuilds stamina and calms anxious overdrive</td></tr><tr><td><strong>Shatavari</strong></td><td>Cooling, nourishing</td><td>Pitta &amp; Vata</td><td>Balances hormones and cools internal heat</td></tr><tr><td><strong>Triphala</strong></td><td>Neutral, balancing</td><td>All Doshas (Tridoshic)</td><td>Gentle systemic digestive detox and regularity</td></tr><tr><td><strong>Neem &amp; Turmeric</strong></td><td>Cooling, drying</td><td>Pitta &amp; Kapha</td><td>Clears skin blemishes and purifies sluggish blood</td></tr><tr><td><strong>Sesame-based Oils</strong></td><td>Warming, heavy</td><td>Vata</td><td>Grounding daily self-massage (<i>Abhyanga</i>)</td></tr></tbody></table></figure><h2>&nbsp;</h2>', 'images-c71678c7f8ad.jpg', 'imges1-db624aed62a1.jpg', NULL, 'Active', '2026-09-24 13:29:00', 'Beyond the Trend: How to Actually Integrate Ayurvedic Products into Daily Life', NULL, '2026-09-24 11:29:54', '2026-09-24 11:45:56'),
(2, 'The Art of Dinacharya: Ayurvedic Daily Rituals for Energy and Balance', 'the-art-of-dinacharya-ayurvedic-daily-rituals-for-energy-and-balance', 'Most modern wellness advice focuses on what to eliminate—cutting carbs, removing screen time, or detoxing from stress. Classical Ayurveda takes the opposite approach: sustainable health starts with consistent, grounding rhythms known as Dinacharya (daily routine).\r\n\r\nAccording to Ayurvedic texts, aligning your daily habits with natural circadian transitions balances the doshas, stokes your digestive fire (Agni), and eliminates accumulated metabolic waste (Ama). Incorporating these five classic morning rituals takes less than twenty minutes, yet fundamentally transforms daily vitality.', '<h2>1. Ushapan: Awaken the Digestive Fire</h2><p>Before reaching for an espresso, start the day with <i>Ushapan</i>—drinking 1–2 glasses of warm or room-temperature water first thing after waking, preferably stored overnight in a pure copper vessel (<i>Tamra Jal</i>).</p><ul><li><strong>The Mechanism:</strong> Warm water gently stimulates bowel peristalsis and flushes out stagnant nighttime toxins. Copper naturally ionizes water, imparting mild antimicrobial and antioxidant properties.</li><li><strong>Practice Note:</strong> Keep the water warm rather than iced; cold water constricts digestive capillaries and dampens <i>Agni</i>.</li></ul><h2>2. Jivha Nirlekhana: Copper Tongue Scraping</h2><p>Brushing your teeth cleans enamel, but sleep allows systemic toxins (<i>Ama</i>) to collect as a white or yellowish film across the tongue.</p><ul><li><strong>The Mechanism:</strong> Scraping the tongue with a U-shaped copper or stainless-steel scraper removes bacteria, freshens breath, and clears the taste buds. Clean taste receptors send more accurate satiety and enzyme signals directly to the stomach.</li><li><strong>How to Do It:</strong> Gently draw the scraper from the back of the tongue forward 5 to 7 times before brushing.</li></ul><h2>3. Kavala &amp; Gandusha: Oil Pulling</h2><p>Swishing oil inside the mouth is an ancient ritual to strengthen oral tissues and tone the jawline.</p><ul><li><strong>The Oils:</strong> Cold-pressed sesame oil is classical and warming; virgin coconut oil is cooling and ideal for sensitive or bleeding gums (excess Pitta).</li><li><strong>How to Do It:</strong> Swish 1 tablespoon of oil continuously for 5–10 minutes without swallowing. Spit it directly into a trash can (to prevent clogged drains) and rinse thoroughly with warm water.</li></ul><h2>4. Abhyanga: Warm Herbal Self-Massage</h2><p>Often viewed as an occasional spa luxury, <i>Abhyanga</i> is traditionally a daily act of preventative medicine and nervous system regulation.</p><ul><li><strong>The Science:</strong> The skin is the home of Vata dosha, governed by the nervous system. Massaging warm, unrefined oil into the skin calms sensory overload, lubricates joints, and enhances lymphatic drainage.</li><li><strong>The Routine:</strong> Warm 2 tablespoons of oil (sesame for dry/cold types, coconut for warm/reactive skin). Massage in long strokes along the limbs and circular motions over the joints and abdomen. Leave on for 10 minutes before stepping into a warm shower.</li></ul><h2>5. Nasya: Nourishing the Senses</h2><p>In Ayurveda, the nose is considered the direct doorway to the brain and consciousness (<i>Prana</i>).</p><ul><li><strong>The Practice:</strong> Placing 1–2 drops of warm <i>Anu Tailam</i> (medicated herbal oil) or plain organic sesame oil into each nostril cleanses nasal passages, lubricates dry mucous membranes, and relieves tension headaches.</li><li><strong>When to Avoid:</strong> Skip during acute sinus infections, productive coughs, or right before bed.</li></ul><h2>Quick Morning Sequence at a Glance</h2><figure class=\"table\"><table><thead><tr><th><strong>Order</strong></th><th><strong>Ritual</strong></th><th><strong>Time Needed</strong></th><th><strong>Primary System Targeted</strong></th></tr></thead><tbody><tr><td><strong>1</strong></td><td>Copper Water (<i>Ushapan</i>)</td><td>1 min</td><td>GI Tract &amp; Elimination</td></tr><tr><td><strong>2</strong></td><td>Tongue Scraping (<i>Jivha Nirlekhana</i>)</td><td>1 min</td><td>Taste Buds &amp; Oral Biome</td></tr><tr><td><strong>3</strong></td><td>Oil Pulling (<i>Gandusha</i>)</td><td>5–10 mins</td><td>Gums, Teeth &amp; Throat</td></tr><tr><td><strong>4</strong></td><td>Warm Oil Massage (<i>Abhyanga</i>)</td><td>5–10 mins</td><td>Nervous System &amp; Lymph</td></tr><tr><td><strong>5</strong></td><td>Nasal Oil Drops (<i>Nasya</i>)</td><td>1 min</td><td>Sinuses, Mind &amp; Respiration</td></tr></tbody></table></figure>', '1-ddd9955646f6.jpg', 'images-1-62780d6d251a.jpg', NULL, 'Active', '2026-09-24 13:55:51', 'The Art of Dinacharya: Ayurvedic Daily Rituals for Energy and Balance', NULL, '2026-09-24 11:55:51', '2026-09-24 11:55:51');

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
(2, NULL, 'Weight Loss', 'weight-loss', 'Weight Loss | Vedorishi Ayurveda', 'Ayurvedic weight management products to support metabolism and appetite control.', 'Ayurvedic formulations to support healthy metabolism, fat reduction and appetite control.', 'wl-category-1-51d249965ad5.jpg', 'Active', 2, '2026-09-22 09:09:45', '2026-09-22 15:01:35'),
(3, NULL, 'Combo', 'combo', 'Combo', 'Bundled combo packs. Products and category image pending.', 'Bundled combo packs. Products and category image pending.', 'combo-44311f7e1dd1.png', 'Active', 3, '2026-09-22 09:09:45', '2026-09-22 15:11:30');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `topic` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Replied','Spam') NOT NULL DEFAULT 'New',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `phone`, `topic`, `message`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, NULL, 'jatin kdpl', 'kdpljatin@gmail.com', '09211339966', 'Complete Gut Detox Programme', 'Hello Buddy', 'New', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-25 12:23:41', '2026-09-26 05:17:36');

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
-- Table structure for table `dosha_leads`
--

CREATE TABLE `dosha_leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_token` char(36) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Female','Male','Other','Prefer not to say') NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `location` varchar(150) NOT NULL,
  `wellness_goal` varchar(150) DEFAULT NULL,
  `status` enum('started','completed') NOT NULL DEFAULT 'started',
  `dosha_result` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(1, 1, 'ORD-20260923-6AE763', 3798.10, 0.00, 0.00, 49.00, 3847.10, NULL, NULL, 'COD', 'failed', 'returned', 'Ajay Yadav', '+91-11-45052477', 'Tikri Boarder', 'Jharoda Kalan', 'Delhi', 'India', '110072', NULL, NULL, NULL, '2026-09-23 10:13:08', '2026-09-23 10:46:45'),
(2, 1, 'ORD-20260923-FFAC87', 3798.10, 0.00, 0.00, 49.00, 3847.10, NULL, NULL, 'COD', 'paid', 'delivered', 'Ajay Yadav', '+91-11-45052477', 'Tikri Boarder', 'Jharoda Kalan', 'Delhi', 'India', '110072', NULL, NULL, NULL, '2026-09-23 11:03:00', '2026-09-23 11:15:09'),
(3, 1, 'ORD-20260923-ECEF51', 4748.10, 0.00, 0.00, 49.00, 4797.10, NULL, NULL, 'COD', 'refunded', 'cancelled', 'Ajay Yadav', '+91-11-45052477', 'Tikri Boarder', 'Jharoda Kalan', 'Delhi', 'India', '110072', NULL, NULL, NULL, '2026-09-23 11:14:25', '2026-09-23 11:15:05'),
(4, 1, 'ORD-20260923-E31932', 21205.00, 0.00, 0.00, 49.00, 21254.00, NULL, NULL, 'COD', 'refunded', 'cancelled', 'Ajay Yadav', '+91-11-45052477', 'Tikri Boarder', 'Jharoda Kalan', 'Delhi', 'India', '110072', NULL, NULL, NULL, '2026-09-23 11:22:25', '2026-09-23 11:22:44');

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
(1, 1, 2, NULL, 'Alpha X Powder', NULL, NULL, 1899.05, 2, 3798.10, '2026-09-23 10:13:08'),
(2, 2, 2, NULL, 'Alpha X Powder', NULL, NULL, 1899.05, 2, 3798.10, '2026-09-23 11:03:00'),
(3, 3, 1, NULL, 'Alpha X Resin', NULL, NULL, 2374.05, 2, 4748.10, '2026-09-23 11:14:25'),
(4, 4, 9, NULL, 'Weight Loss Full Kit', NULL, NULL, 4241.00, 5, 21205.00, '2026-09-23 11:22:25');

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

--
-- Dumping data for table `order_status_logs`
--

INSERT INTO `order_status_logs` (`id`, `order_id`, `old_status`, `new_status`, `note`, `admin_id`, `created_at`) VALUES
(1, 1, 'pending', 'delivered', 'Manual change', 1, '2026-09-23 10:14:12'),
(2, 1, 'delivered', 'delivered', 'Payment status: pending → paid', 1, '2026-09-23 10:14:12'),
(3, 1, 'delivered', 'delivered', 'Payment status: paid → failed', 1, '2026-09-23 10:46:38'),
(4, 1, 'delivered', 'returned', 'Manual change', 1, '2026-09-23 10:46:45'),
(5, 2, 'pending', 'delivered', 'Manual change', 1, '2026-09-23 11:03:12'),
(6, 3, 'pending', 'cancelled', 'Manual change', 1, '2026-09-23 11:15:05'),
(7, 3, 'cancelled', 'cancelled', 'Payment status: pending → refunded', 1, '2026-09-23 11:15:05'),
(8, 2, 'delivered', 'delivered', 'Payment status: pending → paid', 1, '2026-09-23 11:15:09'),
(9, 4, 'pending', 'cancelled', 'Manual change', 1, '2026-09-23 11:22:44'),
(10, 4, 'cancelled', 'cancelled', 'Payment status: pending → refunded', 1, '2026-09-23 11:22:44');

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
(1, 'Alpha X Resin', 'alpha-x-resin', 'Natural resin for vitality and stamina.', 'Alpha X Resin is a traditional Ayurvedic resin formulation crafted to support vitality, stamina and overall male wellness.', 2499.00, 2374.05, 0, 98, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 11:14:25'),
(2, 'Alpha X Powder', 'alpha-x-powder', 'Strength, energy & endurance powder enriched with Kaunch Beej, Safed Musli & Gokhru. 150 gm.', 'Alpha X Powder is enriched with Kaunch Beej, Safed Musli and Gokhru to support strength, energy and endurance. Pack size: 150 gm.', 1999.00, 1899.05, 0, 0, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 11:03:00'),
(3, 'Alpha X Tablet', 'alpha-x-tablet', 'Vitality, stamina & confidence tablets enriched with Ashwagandha, Shilajit & Kaunch Beej. 60 tabs.', 'Alpha X Tablet is enriched with Ashwagandha, Shilajit and Kaunch Beej to support vitality, stamina and confidence. Pack size: 60 tablets.', 1999.00, 1899.05, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 02:20:09'),
(4, 'Alpha X Oil', 'alpha-x-oil', 'Massage & vitality oil. Natural, safe, effective. 30 ml.', 'Alpha X Oil is a natural massage and vitality oil formulated to be safe and effective. Pack size: 30 ml.', 1199.00, 1139.05, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 02:20:18'),
(5, 'Lean Plus Capsule', 'lean-plus-capsule', 'Ayurvedic weight management capsules. Proprietary medicine. 60 capsules.', 'Lean Plus Capsule supports healthy metabolism, helps reduce fat accumulation, and supports appetite and craving control. Pack size: 60 capsules.', 3095.00, 2940.25, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 02:20:27'),
(6, 'Lean Plus Syrup', 'lean-plus-syrup', 'Ayurvedic weight management syrup. Proprietary medicine. 300 ml.', 'Lean Plus Syrup supports healthy metabolism, helps reduce fat accumulation, supports appetite and craving control, and helps maintain energy levels. Pack size: 300 ml.', 1895.00, 1800.25, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:09:45', '2026-09-23 02:20:35'),
(8, 'Men\'s Wellness Full Kit', 'men-s-wellness-full-kit', 'Complete Alpha X kit: Resin, Powder, Tablet & Oil in one pack.', 'The Men\'s Wellness Full Kit bundles all four Alpha X formulations — Resin, Powder, Tablet and Oil — for a complete vitality, stamina and strength routine.', 7696.00, 6541.60, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:21:03', '2026-09-23 02:19:25'),
(9, 'Weight Loss Full Kit', 'weight-loss-full-kit', 'Complete Lean Plus kit: Capsule & Syrup in one pack.', 'The Weight Loss Full Kit bundles Lean Plus Capsule and Lean Plus Syrup together to support metabolism, fat reduction and appetite control.', 4990.00, 4241.00, 0, 100, 1, 1, 1, 0, 'Active', NULL, NULL, '2026-09-22 09:21:03', '2026-09-23 11:22:44');

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
(8, 1, 0),
(8, 3, 1),
(9, 2, 0),
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
(1, 1, 'Each 1 gm contains: Shuddh Shilajit extract 700 mg, Ashwagandha extract 100 mg, Gokhru extract 75 mg, Safed Musli extract 50 mg, Salam Panja extract 25 mg, Javitri/Jaiphal extract 20 mg, Base q.s.', 'Strength, energy & endurance. Supports vitality, stamina and confidence.', 'Take pea size (300 mg) with warm water or milk, or as directed by the physician.', '300 mg (pea size) once daily, or as directed by consultant.', 'Store in a cool, dry place away from children. Do not exceed recommended dosage.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:19:48'),
(2, 2, 'Each 10 gm contains: Kaunch Beej 2000 mg, Safed Musli 1500 mg, Ashwagandha root 1500 mg, Vidarikand 1000 mg, Gokhru 1000 mg, Salam Panja 500 mg, Shatavari 500 mg, Excipients q.s.', 'Strength, energy & endurance. Supports daily vitality, active lifestyle, men\'s wellness and overall wellbeing.', 'Take 10 gm of Alpha X powder with warm water or milk, or as directed by consultant.', '10 gm once daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:19:58'),
(3, 3, 'Each tablet contains: Ashwagandha extract 300 mg, Gokhru extract 150 mg, Safed Musli extract 100 mg, Akarkara extract 75 mg, Shilajit extract 100 mg, Kaunch Beej extract 100 mg, Kesar 15 mg, Safedsi extract 25 mg, Excipients q.s.', 'Vitality, stamina & confidence. Supports daily vitality, active lifestyle, men\'s wellness and inner wellness.', 'Take 1 tablet a day with water, or as directed by consultant.', '1 tablet daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:20:09'),
(4, 4, 'Each 30 ml contains: Malkangni 600 mg, Akarkara 300 mg, Jalphal 300 mg, Kesar 150 mg, Laung 100 mg, Til Tail q.s. to 30 ml.', 'Natural care, body comfort, relaxing muscles. Natural, safe, effective massage & vitality oil.', 'Apply 1-3 ml of oil externally on the affected area and massage gently once a day, or as directed by the physician.', '1-3 ml externally, once daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children. Do not refrigerate.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:20:18'),
(5, 5, 'Each capsule contains: Triphala 50 mg, Ajwain 40 mg, Punernava 30 mg, Heeng 20 mg, Vidang 50 mg, Guggal 50 mg, Shudh Shilajit 20 mg, Trikuta 20 mg, Yavkshar 30 mg, Pipplamool 50 mg, Arogyavardhini 50 mg, Sounth 40 mg, Methi Dana 50 mg, Garcinia 50 mg.', 'Supports healthy weight management by promoting fat metabolism, improving digestion, reducing appetite naturally, enhancing energy levels, and helping maintain a healthy lipid profile.', '1 capsule twice a day with water, or as directed by consultant.', '1 capsule twice daily, or as directed by consultant.', 'Store in a cool, dry & dark place. Keep away from children.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:20:27'),
(6, 6, 'Each 5 ml contains: Garcinia Cambogia 1000 mg, Trigonella foenum-graecum 100 mg, Allium sativum 750 mg, Emblica officinalis 200 mg, Terminalia chebula 200 mg, Terminalia bellerica 200 mg, Trachyspermum ammi 100 mg, Foeniculum vulgare 400 mg, Zingiber officinale 100 mg.', 'Supports by boosting metabolism, reducing fat accumulation, and controlling appetite & cravings. No side effects; best used alongside a healthy lifestyle.', 'As directed by the consultant. Shake well before use.', 'As directed by consultant.', 'Colour, taste & aroma may vary batch to batch (natural herbal extract). Consume within one month of opening. Store in a cool, dry place.', 'Vedorishi', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:20:35'),
(7, 8, 'Bundle of: Alpha X Resin, Alpha X Powder, Alpha X Tablet and Alpha X Oil. See each product\'s own listing for its full ingredient panel.', 'Complete men\'s wellness routine covering vitality, stamina, strength and topical massage support.', 'Follow the individual direction-for-use printed on each item in the kit.', 'As directed on each individual product, or by consultant.', 'Store all items in a cool, dry & dark place. Keep away from children.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:19:25'),
(8, 9, 'Bundle of: Lean Plus Capsule and Lean Plus Syrup. See each product\'s own listing for its full ingredient panel.', 'Complete weight management routine combining a capsule and syrup formulation to support metabolism, fat reduction and appetite control.', 'Follow the individual direction-for-use printed on each item in the kit.', 'As directed on each individual product, or by consultant.', 'Store all items in a cool, dry & dark place. Keep away from children.', '', 'India', '36 months from date of manufacture', '2026-09-22 09:32:01', '2026-09-23 02:19:35');

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
(25, 8, '8/menwell-category-1-9a5e071e802e.jpg', NULL, 0, 1, '2026-09-22 16:10:09'),
(26, 8, '8/menwell-category-2-638289b2d168.jpg', NULL, 1, 2, '2026-09-22 16:10:09'),
(27, 9, '9/wl-category-1-21679c3c4f67.jpg', NULL, 1, 1, '2026-09-22 17:46:52'),
(28, 9, '9/wl-category-2-249341450c56.jpg', NULL, 0, 2, '2026-09-22 17:46:52'),
(29, 1, '1/menwell-product1-1-64e2fe714e29.jpg', NULL, 1, 1, '2026-09-22 17:54:19'),
(30, 1, '1/menwell-product1-2-b69300303ee7.jpg', NULL, 0, 2, '2026-09-22 17:54:19'),
(31, 1, '1/menwell-product1-3-ade19092c1e7.jpg', NULL, 0, 3, '2026-09-22 17:54:19'),
(32, 1, '1/menwell-product1-4-89bd1400b83c.jpg', NULL, 0, 4, '2026-09-22 17:54:19'),
(33, 2, '2/menwell-product2-1-d4d394be09c5.jpg', NULL, 1, 1, '2026-09-22 17:55:07'),
(34, 2, '2/menwell-product2-2-c62d07c1c61d.jpg', NULL, 0, 2, '2026-09-22 17:55:07'),
(35, 2, '2/menwell-product2-3-6d09f06aa771.jpg', NULL, 0, 3, '2026-09-22 17:55:07'),
(36, 3, '3/menwell-product3-1-341e13f82ef4.jpg', NULL, 1, 1, '2026-09-22 17:56:05'),
(37, 3, '3/menwell-product3-2-94c466abf3d8.jpg', NULL, 0, 2, '2026-09-22 17:56:05'),
(38, 3, '3/menwell-product3-3-9b4db7fe93a3.jpg', NULL, 0, 3, '2026-09-22 17:56:05'),
(39, 4, '4/menwell-product4-1-ac6f298a8dee.jpg', NULL, 1, 1, '2026-09-22 17:56:58'),
(40, 4, '4/menwell-product4-2-217c311f75ac.jpg', NULL, 0, 2, '2026-09-22 17:56:58'),
(41, 4, '4/menwell-product4-3-435994486869.jpg', NULL, 0, 3, '2026-09-22 17:56:58'),
(42, 5, '5/wl-product1-1-879a7ccf0811.jpg', NULL, 1, 1, '2026-09-22 17:58:17'),
(43, 5, '5/wl-product1-2-4ccade9b589c.jpg', NULL, 0, 2, '2026-09-22 17:58:17'),
(44, 5, '5/wl-product1-3-0993003b9c87.jpg', NULL, 0, 4, '2026-09-22 17:58:17'),
(45, 5, '5/wl-product1-4-47fd6565083b.jpg', NULL, 0, 4, '2026-09-22 17:58:17'),
(46, 6, '6/wl-product2-1-29ea00f7874c.jpg', NULL, 0, 1, '2026-09-22 17:59:03'),
(47, 6, '6/wl-product2-2-a573dd6a2748.jpg', NULL, 1, 2, '2026-09-22 17:59:03'),
(48, 6, '6/wl-product2-3-9c1ed61ea2e3.jpg', NULL, 0, 3, '2026-09-22 17:59:03');

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

--
-- Dumping data for table `promotional_videos`
--

INSERT INTO `promotional_videos` (`id`, `title`, `description`, `video_type`, `video_url`, `video_file`, `thumbnail`, `orientation`, `button_text`, `button_url`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Promotion 1', 'Promotion', 'youtube', 'https://youtu.be/zt6i6vVgiO4', NULL, NULL, NULL, '', '', 1, 1, '2026-09-23 17:46:07', '2026-09-25 07:55:32'),
(2, 'Promotion 2', 'Promotion', 'youtube', 'https://youtu.be/Fm6nXvupBcs', NULL, NULL, NULL, '', '', 1, 2, '2026-09-23 17:47:03', '2026-09-25 07:57:12'),
(3, 'Promotion 3', 'Promotion', 'youtube', 'https://www.youtube.com/shorts/FB7AhG5QLyc?feature=share', NULL, NULL, NULL, '', '', 1, 3, '2026-09-23 17:48:13', '2026-09-25 07:57:40'),
(4, '', '', 'youtube', 'https://www.youtube.com/shorts/dpDiiNLCCqs?feature=share', NULL, NULL, NULL, '', '', 1, 4, '2026-09-25 07:51:42', '2026-09-25 07:52:40'),
(5, '', '', 'youtube', 'https://www.youtube.com/shorts/lXuYeNiyEmI?feature=share', NULL, NULL, NULL, '', '', 1, 5, '2026-09-25 07:53:41', '2026-09-25 07:53:41');

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
(2, 2, 1, 5, '234569', 'Active', 1, '2026-09-23 10:15:06', '2026-09-23 10:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) NOT NULL DEFAULT 'text',
  `setting_group` varchar(100) NOT NULL DEFAULT 'General',
  `label` varchar(150) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `label`, `description`, `status`, `sort_order`, `updated_at`) VALUES
(1, 'contact_phone', '123456789123', 'phone', 'Contact', 'Phone Number', '', 'Active', 1, '2026-09-25 12:16:58'),
(2, 'contact_phone_note', '(Mon – Sat, 9 AM – 6 PM)', 'text', 'Contact', 'Phone Note', NULL, 'Active', 2, '2026-09-25 08:37:50'),
(3, 'contact_email', 'hello@ayurveda.com', 'email', 'Contact', 'Email Address', NULL, 'Active', 3, '2026-09-25 08:37:50'),
(4, 'contact_email_note', 'We\'ll respond within 24 hours.', 'text', 'Contact', 'Email Note', NULL, 'Active', 4, '2026-09-25 08:37:50'),
(5, 'contact_address', 'Dwarka Mor, Near Dwarka Mor Metro Station.', 'textarea', 'Contact', 'Address', '', 'Active', 5, '2026-09-25 12:18:05'),
(6, 'contact_topics', 'Complete Gut Detox Programme\nBook a Consultation\nAyurvedic Products\nOrder or Delivery Support\nSomething Else', 'textarea', 'Contact', 'Consultation Topics', NULL, 'Active', 6, '2026-09-25 08:37:50'),
(7, 'contact_notify_email', '', 'email', 'Contact', 'Notify Email', NULL, 'Active', 7, '2026-09-25 08:37:50'),
(9, 'site_logo', 'uploads/settings/setting_7f9b0bfa4a6a7b10.png', 'image', 'General', 'Site Logo', '', 'Active', 8, '2026-09-25 11:59:52'),
(10, 'site_name', 'Vedorishi', 'text', 'General', 'Site Name', 'Site Nme', 'Active', 1, '2026-09-25 12:02:09');

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
(1, 'Dr. Karan Yadav', 'Senior Ayurveda Practitioner', 'An experienced and dedicated Ayurveda practitioner focused on holistic wellness, natural healing, and helping patients build healthier lifestyles through traditional Ayurvedic principles.', 'uploads/team/6092ed21277cc3e0f78ea993e4efce92.jpg', 1, 1, '2026-09-24 02:09:20', '2026-09-24 02:11:37'),
(2, 'Dr. Radhika Tiwari', 'Ayurveda Practitioner', 'A compassionate Ayurveda practitioner with a patient-focused approach, combining traditional Ayurvedic knowledge with practical wellness guidance for balanced health and well-being.', 'uploads/team/66c6de8f60b6c45504cdcbd922e8ddb4.jpg', 1, 2, '2026-09-24 02:12:29', '2026-09-24 02:12:39'),
(3, 'Vaidya Manoj Panday', 'Senior Vaidya', 'An experienced Ayurveda practitioner dedicated to traditional Ayurvedic wellness, natural healing, and personalized guidance for healthier living.', 'uploads/team/9488618bdd1f84ebf5f3f0a1fcf734bb.jpg', 1, 3, '2026-09-24 02:14:15', '2026-09-24 02:14:15'),
(4, 'Vaidya Monika Satyapati', 'Ayurveda Consultant', 'A compassionate Ayurveda practitioner focused on holistic well-being, combining traditional Ayurvedic principles with practical lifestyle guidance for balanced health.', 'uploads/team/0a504579687320826d7861ad90a954a3.jpg', 1, 4, '2026-09-24 02:15:12', '2026-09-24 02:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `transformations`
--

CREATE TABLE `transformations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `slug` varchar(191) NOT NULL DEFAULT '',
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

--
-- Dumping data for table `transformations`
--

INSERT INTO `transformations` (`id`, `customer_name`, `slug`, `before_image`, `after_image`, `description`, `product_id`, `duration`, `is_verified`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Rasmika Tiwari', 'rasmika-tiwari', 'uploads/transformations/before-51fac8d218a2e7c4.jpg', 'uploads/transformations/after-3d370a747cfdf27b.jpg', 'Rasmika Tiwari chose Vedorishi Ayurveda’s The Weight Loss Full Kit, featuring Lean Plus Capsules and Lean Plus Syrup, as part of her wellness journey. With consistency and a dedicated approach to her lifestyle, she worked toward feeling healthier, more active, and more confident.', 9, '12 Weeks', 1, 'Active', 1, '2026-09-23 17:34:45', '2026-09-26 07:06:39'),
(2, 'Arjun Pandey', 'arjun-pandey', 'uploads/transformations/before-2e5915d1ccb5e1c4.jpg', 'uploads/transformations/after-16b42a920afd14be.jpg', 'Arjun Pandey chose Vedorishi Ayurveda’s The Weight Loss Full Kit, featuring Lean Plus Capsules and Lean Plus Syrup, as part of his wellness journey. With consistency and a dedicated approach to his lifestyle, he worked toward feeling healthier, more active, and more confident.', 9, '12 Weeks', 1, 'Active', 2, '2026-09-23 17:35:55', '2026-09-26 07:06:39'),
(3, 'Anshika', 'anshika', 'uploads/transformations/before-75a43446372146ac.jpg', 'uploads/transformations/after-38ab5b9a11a29f4c.jpg', 'Anshika chose Vedorishi Ayurveda’s The Weight Loss Full Kit, featuring Lean Plus Capsules and Lean Plus Syrup, as part of her wellness journey. With consistency and a dedicated approach to her lifestyle, she worked toward feeling healthier, more active, and more confident.', 9, '12 Weeks', 1, 'Active', 3, '2026-09-23 17:37:39', '2026-09-26 07:06:39');

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
(1, 'Ajay Yadav', 'ajay@gmail.com', '+91-11-45052477', '$2y$10$CFV5YphhdDnAKIwP1EFT9OdMIsq/D4tayMZs/h9MpNoRsF/MWnYdG', 'Active', '2026-09-23 03:07:47', '2026-09-23 03:07:47');

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
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_category_slug` (`slug`),
  ADD KEY `idx_blog_category_status` (`status`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_slug` (`slug`),
  ADD KEY `idx_blog_status` (`status`,`published_at`),
  ADD KEY `idx_blog_posts_category` (`category_id`);

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
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_ip_created` (`ip_address`,`created_at`),
  ADD KEY `idx_email_created` (`email`,`created_at`);

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
-- Indexes for table `dosha_leads`
--
ALTER TABLE `dosha_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `fk_dosha_leads_user` (`user_id`),
  ADD KEY `email` (`email`);

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
  ADD UNIQUE KEY `transformations_slug_unique` (`slug`),
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `dosha_leads`
--
ALTER TABLE `dosha_leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotional_videos`
--
ALTER TABLE `promotional_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transformations`
--
ALTER TABLE `transformations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_posts_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `dosha_leads`
--
ALTER TABLE `dosha_leads`
  ADD CONSTRAINT `fk_dosha_leads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
