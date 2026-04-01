-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 08:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_limits`
--

CREATE TABLE `budget_limits` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `monthly_limit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_limits`
--

INSERT INTO `budget_limits` (`id`, `category`, `monthly_limit`, `updated_at`) VALUES
(1, 'Food', 5000.00, '2026-03-31 12:16:15'),
(2, 'Travel', 5500.00, '2026-03-31 12:20:56'),
(3, 'Shopping', 6000.00, '2026-03-31 12:16:15'),
(4, 'Education', 5000.00, '2026-04-01 05:38:58');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(10) DEFAULT '?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Food', '🍔'),
(2, 'Travel', '✈️'),
(3, 'Shopping', '🛍️'),
(4, 'Education', '📚');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `title`, `amount`, `category`, `date`, `category_id`) VALUES
(2, 'Dinner at restaurant', 4528.00, 'Food', '2026-03-28', 1),
(3, 'Shopping at H&M', 6870.00, 'Shopping', '2026-03-22', 3),
(4, 'Novels', 1400.00, 'Education', '2026-03-12', 4),
(5, 'travelling delhi', 2400.00, 'Travel', '2026-03-21', 2),
(9, 'meal', 150.00, 'Food', '2026-04-01', NULL),
(10, 'metro', 100.00, 'Travel', '2026-04-01', NULL);

--
-- Triggers `expenses`
--
DELIMITER $$
CREATE TRIGGER `before_expense_delete` BEFORE DELETE ON `expenses` FOR EACH ROW BEGIN
  INSERT INTO expense_log (expense_id, title, amount, category, action)
  VALUES (OLD.id, OLD.title, OLD.amount, OLD.category, 'DELETED');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `expense_log`
--

CREATE TABLE `expense_log` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `action` varchar(20) DEFAULT 'DELETED',
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_log`
--

INSERT INTO `expense_log` (`id`, `expense_id`, `title`, `amount`, `category`, `action`, `logged_at`) VALUES
(1, 7, 'momos', 180.00, 'Food', 'DELETED', '2026-03-31 18:50:42'),
(2, 8, 'momos', 180.00, 'Food', 'DELETED', '2026-04-01 03:26:31'),
(3, 11, 'nykaa', 2200.00, 'Shopping', 'DELETED', '2026-04-01 05:46:51');

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_summary`
-- (See below for the actual view)
--
CREATE TABLE `monthly_summary` (
`month` varchar(7)
,`month_label` varchar(37)
,`category` varchar(50)
,`total_entries` bigint(21)
,`total_spent` decimal(32,2)
,`highest_expense` decimal(10,2)
,`avg_expense` decimal(11,2)
);

-- --------------------------------------------------------

--
-- Structure for view `monthly_summary`
--
DROP TABLE IF EXISTS `monthly_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_summary`  AS SELECT date_format(`expenses`.`date`,'%Y-%m') AS `month`, date_format(`expenses`.`date`,'%b %Y') AS `month_label`, `expenses`.`category` AS `category`, count(0) AS `total_entries`, sum(`expenses`.`amount`) AS `total_spent`, max(`expenses`.`amount`) AS `highest_expense`, round(avg(`expenses`.`amount`),2) AS `avg_expense` FROM `expenses` GROUP BY date_format(`expenses`.`date`,'%Y-%m'), `expenses`.`category` ORDER BY date_format(`expenses`.`date`,'%Y-%m') DESC, sum(`expenses`.`amount`) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget_limits`
--
ALTER TABLE `budget_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category` (`category`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `expense_log`
--
ALTER TABLE `expense_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget_limits`
--
ALTER TABLE `budget_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `expense_log`
--
ALTER TABLE `expense_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
