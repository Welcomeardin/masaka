-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 02, 2026 at 12:53 PM
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
-- Database: `multi_lang_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `vision_title` varchar(255) DEFAULT NULL,
  `vision_text` text DEFAULT NULL,
  `vision_image` varchar(500) DEFAULT NULL,
  `mission_title` varchar(255) DEFAULT NULL,
  `mission_text` text DEFAULT NULL,
  `mission_image` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `language_id`, `title`, `content`, `image_url`, `vision_title`, `vision_text`, `vision_image`, `mission_title`, `mission_text`, `mission_image`, `status`, `created_at`) VALUES
(1, 1, 'Empower Local Creators', '\"We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026. Because when local creators succeed, communities thrive.\"', 'uploads/about/about_69f3ef8b48d75_40769591.jpeg', 'We make it real by daily actions for audience.', '\r\nA future where sustainable energy / healthy communities / digital equity is available to everyone.', 'uploads/about/vision_69f3ef8b49399_72647385.jpeg', '\"We make it real by daily actions for audience.', 'To provide / build / teach / empower] solution to target audience through key activities.', 'uploads/about/mission_69f3ef8b4966f_87330731.jpeg', 'active', '2026-05-01 00:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Muco-shop', 'welcomeardin@gmail.com', NULL, 'nashaka ubufasha', 'nashaka ubufasha yuko mwompa amahera agera kuri 2500000fbu yamarundi', 'read', '2026-05-02 09:36:51', '2026-05-02 09:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `full_name`, `email`, `phone`, `amount`, `currency`, `payment_method`, `transaction_id`, `message`, `status`, `created_at`) VALUES
(1, 'asa asas', 'welcomeardin@gmail.com', NULL, 25000.00, 'USD', NULL, NULL, NULL, 'completed', '2026-02-17 09:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `language_id`, `title`, `description`, `image_url`, `event_date`, `event_time`, `location`, `status`, `created_at`) VALUES
(1, 1, 'Women\'s Empowerment Workshop', 'Skills training in sewing, baking, and small business management', 'uploads/events/event_69f3ef114a346_93021863.jpeg', '2026-05-01', '12:01:00', 'Kirimiro', 'upcoming', '2026-05-01 00:08:49'),
(2, 1, 'School Supply Drive', 'Providing notebooks, pens, and uniforms to underprivileged students', 'uploads/events/event_69f5be56adb36_85154530.jpeg', '2026-05-02', '13:13:00', 'kirimiro', 'upcoming', '2026-05-02 09:05:26'),
(3, 1, 'Pastors\' Conference', 'Equipping local church leaders with ministry resources and training', 'uploads/events/event_69f5be8563787_99366435.jpeg', '2026-05-02', '12:01:00', 'Kirimiro', 'upcoming', '2026-05-02 09:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_categories`
--

CREATE TABLE `gallery_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_items`
--

CREATE TABLE `gallery_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_items_translations`
--

CREATE TABLE `gallery_items_translations` (
  `id` int(11) NOT NULL,
  `gallery_item_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `code`, `name`, `is_default`, `is_active`, `created_at`) VALUES
(1, 'en', 'English', 1, 1, '2026-02-16 13:46:21'),
(2, 'sw', 'Kiswahili', 0, 1, '2026-02-16 13:46:21'),
(3, 'fr', 'French', 0, 1, '2026-02-16 13:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `facebook_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `twitter_link` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `email`, `phone`, `address`, `facebook_link`, `instagram_link`, `youtube_link`, `twitter_link`, `logo_url`, `created_at`, `updated_at`) VALUES
(1, 'masakainitiative@gmail.com', '+257 12345678', 'Kirimiro', 'https://windsurf.com/download/editor', 'https://windsurf.com/download/editor', 'https://windsurf.com/download/editor', 'https://windsurf.com/download/editor', 'uploads/settings/logo_69f3ef3667234_1777594166.png', '2026-05-01 00:07:58', '2026-05-02 09:47:23');

-- --------------------------------------------------------

--
-- Table structure for table `settings_translations`
--

CREATE TABLE `settings_translations` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `address_text` text DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `copyright_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings_translations`
--

INSERT INTO `settings_translations` (`id`, `language_id`, `address_text`, `footer_text`, `copyright_text`) VALUES
(1, 1, 'sd', 'sadf', 'asd'),
(2, 2, '', '', ''),
(3, 3, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `slides`
--

CREATE TABLE `slides` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slides`
--

INSERT INTO `slides` (`id`, `language_id`, `title`, `content`, `image_url`, `button_text`, `button_link`, `sort_order`, `status`, `created_at`) VALUES
(1, 2, 'sdf', 'fsd', 'uploads/slides/slide_69f3ef6b983aa_98953143.jpeg', '', '', 1, 'active', '2026-05-01 00:10:19'),
(2, 1, 'Slide Subtitle:  “Building a better future — one [step/project/interaction] at a time.”', '“We exist to [solve a specific problem] by [your unique approach]. Our mission is to empower [target audience] with [key benefit], while staying true to our values of [value 1], [value 2], and [value 3].”', 'uploads/slides/slide_69f5a1b60bcaa_73342818.jpeg', '', '', 2, 'active', '2026-05-02 07:03:18'),
(3, 1, 'Learn Why We Do What We Do', '“We exist to [solve a specific problem] by [your unique approach]. Our mission is to empower [target audience] with [key benefit], while staying true to our values of [value 1], [value 2], and [value 3].”', 'uploads/slides/slide_69f5a1f0080af_66067830.jpeg', '', '', 3, 'active', '2026-05-02 07:04:16'),
(4, 1, 'Learn Why We Do What We Do', '\"We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026. Because when local creators succeed, communities thrive.\"', 'uploads/slides/slide_69f5a23e98d92_86675281.jpeg', '', '', 4, 'active', '2026-05-02 07:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `name`, `role`, `image_url`, `bio`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Welcome ardin', 'wert', 'uploads/team/team_69f5a950ee0b7_30837329.jpg', 'For a CEO or founder, Developer Talent is often the most critical lever in the business. Without strong devs, product dies. Full stop.', 0, 'active', '2026-05-02 07:35:44', '2026-05-02 07:37:12'),
(3, 'Nikeza ', 'CEO', 'uploads/team/team_69f5acc9c89fe_59827503.jpg', 'We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026.', 1, 'active', '2026-05-02 07:50:33', '2026-05-02 07:50:33'),
(4, 'NexDev', 'Founder', 'uploads/team/team_69f5ad592fb64_97089065.jpg', 'We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026. ', 2, 'active', '2026-05-02 07:52:57', '2026-05-02 07:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `team_translations`
--

CREATE TABLE `team_translations` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_translations`
--

INSERT INTO `team_translations` (`id`, `team_id`, `language_id`, `role`, `bio`) VALUES
(1, 1, 1, 'Dev', 'For a CEO or founder, Developer Talent is often the most critical lever in the business. Without strong devs, product dies. Full stop.'),
(2, 2, 1, 'Dev', 'For a CEO or founder, Developer Talent is often the most critical lever in the business. Without strong devs, product dies. Full stop.'),
(3, 3, 1, 'CEO', 'We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026.'),
(4, 4, 1, 'Founder', 'We provide free tools and training to help 1,000 small makers grow sustainable businesses by 2026. ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `role` enum('admin','editor','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `status`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$ej7GMIfxXDc4pIBaHcc8J.Euhbhw2IyGIxGdXTn1pXhWeG4ClaWzW', 'active', 'admin', '2026-02-16 13:46:21', '2026-04-30 07:11:54'),
(6, 'welcomeardin', 'admin@hospital.com', '$2y$10$pgXmDzO.lbnrQxluVZJlle3nTtUwqdqDBWlIW51Ck/I20XqdORokC', 'active', 'admin', '2026-04-30 15:34:41', '2026-04-30 15:54:27'),
(7, 'kewa', 'directeur@school.com', '$2y$10$pQ3fGf1I2llyknwmwu5Mh.5WES/qYdwHd07Gxg2pAceS7RYk4oeuO', 'inactive', 'editor', '2026-04-30 18:39:50', '2026-04-30 18:40:05');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_gallery_full`
-- (See below for the actual view)
--
CREATE TABLE `vw_gallery_full` (
`id` int(11)
,`media_type` enum('image','video')
,`media_url` varchar(500)
,`thumbnail_url` varchar(500)
,`category_name` varchar(255)
,`title` varchar(255)
,`description` text
,`link_url` varchar(255)
,`language_code` varchar(2)
,`language_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_gallery_full`
--
DROP TABLE IF EXISTS `vw_gallery_full`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_gallery_full`  AS SELECT `gi`.`id` AS `id`, `gi`.`media_type` AS `media_type`, `gi`.`media_url` AS `media_url`, `gi`.`thumbnail_url` AS `thumbnail_url`, `gc`.`name` AS `category_name`, `git`.`title` AS `title`, `git`.`description` AS `description`, `git`.`link_url` AS `link_url`, `l`.`code` AS `language_code`, `l`.`name` AS `language_name` FROM (((`gallery_items` `gi` left join `gallery_categories` `gc` on(`gi`.`category_id` = `gc`.`id`)) left join `gallery_items_translations` `git` on(`gi`.`id` = `git`.`gallery_item_id`)) left join `languages` `l` on(`git`.`language_id` = `l`.`id`)) WHERE `gi`.`status` = 'active' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_items`
--
ALTER TABLE `gallery_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_items_translations`
--
ALTER TABLE `gallery_items_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_item_lang` (`gallery_item_id`,`language_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings_translations`
--
ALTER TABLE `settings_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lang` (`language_id`);

--
-- Indexes for table `slides`
--
ALTER TABLE `slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_translations`
--
ALTER TABLE `team_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_lang` (`team_id`,`language_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_items`
--
ALTER TABLE `gallery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_items_translations`
--
ALTER TABLE `gallery_items_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings_translations`
--
ALTER TABLE `settings_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `slides`
--
ALTER TABLE `slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `team_translations`
--
ALTER TABLE `team_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
