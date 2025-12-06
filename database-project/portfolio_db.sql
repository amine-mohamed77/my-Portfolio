-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 06:49 PM
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
-- Database: `portfolio_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(3, 'amin', 'admin@example.com', '$2y$10$4mVXieI5csfyeiAIpJmS6ONTO.LgMP9duPOkKKWQxafR.o8ps127a', '2025-12-06 17:44:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `dashboard_stats`
-- (See below for the actual view)
--
CREATE TABLE `dashboard_stats` (
`total_skills` bigint(21)
,`total_projects` bigint(21)
,`unread_messages` bigint(21)
,`avg_skill_level` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `tech_stack` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`tech_stack`)),
  `image_path` varchar(255) DEFAULT NULL,
  `live_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `tech_stack`, `image_path`, `live_url`, `github_url`, `display_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(11, 'Wincom by INWI - Internet Service Provider Platform', 'A comprehensive, modern web platform for Wincom internet service provider featuring a public website, admin panel, and customer management system built with PHP and MySQL.', '[\"PHP\",\"MySQL\",\"CSS\"]', 'uploads/projects/project_69344601d6f2e.png', 'https://wincom5g.com/', 'https://github.com/amine-mohamed77/windcome-by-inwi.git', 0, 0, 1, '2025-12-06 15:04:33', '2025-12-06 15:04:33'),
(12, 'SAKA--NY – Landing Page', 'A modern landing page for a student housing assistance platform\r\n\r\nThis project is the official Landing Page for UniHousing, a platform designed to help university students easily find affordable and nearby accommodation.\r\nThe page is built using HTML and TailwindCSS, focusing on clean UI, fast performance, and a smooth user experience.', '[\"HTML\",\"TailwindCSS\"]', 'uploads/projects/project_6934497fc5a13.png', 'https://amine-mohamed77.github.io/SAKA--NY/', 'https://github.com/amine-mohamed77/SAKA--NY.git', 0, 0, 1, '2025-12-06 15:19:06', '2025-12-06 15:19:27'),
(13, 'Product Management System – CRUD', 'A simple and efficient CRUD (Create, Read, Update, Delete) product management system built using HTML, CSS, and JavaScript.\r\nThe interface allows users to manage products by adding prices, taxes, ads, discounts, categories, and automatically calculating the total.\r\n\r\nThis project is lightweight, fast, and runs fully on the browser with localStorage support.', '[\"HTML\",\"CSS\",\"JavaScript.\"]', 'uploads/projects/project_69344c355b3ff.png', 'https://amine-mohamed77.github.io/management-system-crud/', 'https://github.com/amine-mohamed77/management-system-crud.git', 0, 0, 1, '2025-12-06 15:29:50', '2025-12-06 15:31:01'),
(14, 'Laravel eCommerce Platform', 'A complete eCommerce web application built with Laravel, providing a full shopping experience including product management, categories, cart functionality, checkout, authentication, and order handling.\r\n\r\nThis project is designed to be clean, scalable, and easy to extend for real-world online stores.', '[\"PHP\",\"Blade\",\"CSS\",\"JavaScript\",\"SCSS\",\"HTML\",\"Laravel\"]', 'uploads/projects/project_69345584050fe.png', NULL, 'https://github.com/amine-mohamed77/ecom_project_laravel.git', 0, 0, 1, '2025-12-06 16:03:50', '2025-12-06 16:10:44'),
(15, 'Android App with Jetpack Compose', 'This project is a simple Android application built using Jetpack Compose, the modern toolkit for building native Android UI.\r\nIt demonstrates basic Compose concepts such as state management, composable functions, themes, and navigation.', '[\"Kotlin\"]', 'uploads/projects/project_69346c0991932.png', NULL, 'https://github.com/amine-mohamed77/Android-with-Compose.git', 0, 0, 1, '2025-12-06 16:13:51', '2025-12-06 17:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `site_analytics`
--

CREATE TABLE `site_analytics` (
  `id` int(11) NOT NULL,
  `page_views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `total_skills` int(11) DEFAULT 0,
  `total_projects` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_analytics`
--

INSERT INTO `site_analytics` (`id`, `page_views`, `unique_visitors`, `total_skills`, `total_projects`, `updated_at`) VALUES
(1, 0, 0, 0, 0, '2025-12-02 13:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` int(11) NOT NULL CHECK (`level` >= 0 and `level` <= 100),
  `category` varchar(50) NOT NULL,
  `icon_type` varchar(50) DEFAULT 'text',
  `icon_value` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3b82f6',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `level`, `category`, `icon_type`, `icon_value`, `color`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'PHP', 95, 'Backend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg', '#3b82f6', 2, 1, '2025-12-02 13:47:04', '2025-12-05 16:45:17'),
(3, 'Laravel', 90, 'Backend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg', '#ae2929', 3, 1, '2025-12-02 13:47:04', '2025-12-05 18:24:29'),
(5, 'MySQL', 92, 'Database', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg', '#3b82f6', 5, 1, '2025-12-02 13:47:04', '2025-12-05 16:46:19'),
(8, 'Git', 90, 'DevOps', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg', '#f97316', 8, 1, '2025-12-02 13:47:04', '2025-12-05 19:52:11'),
(12, 'React', 70, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg', '#3b82f6', 0, 1, '2025-12-04 19:09:21', '2025-12-05 16:42:51'),
(13, 'HTML', 100, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg', '#f97316', 0, 1, '2025-12-05 16:35:44', '2025-12-05 19:51:50'),
(15, 'css', 100, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg', '#3b82f6', 0, 1, '2025-12-05 20:27:52', '2025-12-05 20:27:52'),
(16, 'TypeScript', 60, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg', '#3b82f6', 0, 1, '2025-12-05 20:28:52', '2025-12-05 20:28:52'),
(17, 'JavaScript', 80, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg', '#f5d60f', 0, 1, '2025-12-05 20:32:28', '2025-12-05 20:32:28'),
(18, 'TailwindCSS', 70, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg', '#3b82f6', 0, 1, '2025-12-05 20:34:00', '2025-12-05 20:34:00'),
(19, 'GitHub', 95, 'Frontend', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg', '#595454', 0, 1, '2025-12-05 20:48:06', '2025-12-05 20:48:55'),
(20, 'npm', 80, 'DevOps', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/npm/npm-original-wordmark.svg', '#ef4444', 0, 1, '2025-12-05 20:51:03', '2025-12-05 20:51:31'),
(21, 'Kotlin', 60, 'DevOps', 'text', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/kotlin/kotlin-original.svg', '#8b5cf6', 0, 1, '2025-12-05 20:53:22', '2025-12-05 20:54:14');

-- --------------------------------------------------------

--
-- Structure for view `dashboard_stats`
--
DROP TABLE IF EXISTS `dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_stats`  AS SELECT (select count(0) from `skills` where `skills`.`is_active` = 1) AS `total_skills`, (select count(0) from `projects` where `projects`.`is_active` = 1) AS `total_projects`, (select count(0) from `contact_messages` where `contact_messages`.`is_read` = 0) AS `unread_messages`, (select avg(`skills`.`level`) from `skills` where `skills`.`is_active` = 1) AS `avg_skill_level` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_read` (`is_read`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_projects_featured` (`is_featured`),
  ADD KEY `idx_projects_active` (`is_active`);

--
-- Indexes for table `site_analytics`
--
ALTER TABLE `site_analytics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_skills_category` (`category`),
  ADD KEY `idx_skills_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `site_analytics`
--
ALTER TABLE `site_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
