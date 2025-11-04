-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 10:06 PM
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
-- Database: `classroom_voting`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position_id` int(11) NOT NULL,
  `status` enum('nominated','elected','lost','ineligible') DEFAULT 'nominated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `position_id`, `status`, `created_at`) VALUES
(56, 13, 1, 'nominated', '2025-10-28 06:04:31'),
(57, 6, 1, 'nominated', '2025-10-28 06:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(50) NOT NULL,
  `position_order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `position_name`, `position_order`, `created_at`) VALUES
(1, 'President', 1, '2025-10-11 23:17:55'),
(2, 'Vice President', 2, '2025-10-11 23:17:55'),
(3, 'Secretary', 3, '2025-10-11 23:17:55'),
(4, 'Treasurer', 4, '2025-10-11 23:17:55'),
(8, 'Auditor', 5, '2025-10-26 13:19:08'),
(9, 'PIO', 6, '2025-10-27 07:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `first_name`, `middle_name`, `last_name`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'ADMIN001', NULL, NULL, 'System Administrator', 'System Administrator', 'admin@school.edu', '$2y$10$wX.QtT2Vl1Y6NhYK.y52q.u1u0I2pcvyWc0UFWS05M/hC6knTQJ7.', 'admin', '2025-10-11 23:17:55'),
(2, 'STU001', NULL, NULL, 'John Doe', 'John Doe', 'john@student.edu', '$2y$10$tUolvebl62KJzhapNSI1Weznqog7Zfv0DSxHX.3mJJ1gx9es3bcle', 'student', '2025-10-11 23:17:55'),
(3, 'STU002', NULL, NULL, 'Jane Smith', 'Jane Smith', 'jane@student.edu', '$2y$10$mkTMaNoqOnkOz..XXWRaCO7eRxzSGAa2KrWiRDuueCjcep2u/839.', 'student', '2025-10-11 23:17:55'),
(4, 'STU003', NULL, NULL, 'Mike Johnson', 'Mike Johnson', 'mike@student.edu', '$2y$10$XsbiOwCfk8FOSfCuOw5EFuVyKq4vn8s89.NJ1LaJSkGc730fZplWm', 'student', '2025-10-11 23:17:55'),
(5, 'STU004', NULL, NULL, 'Sarah Williams', 'Sarah Williams', 'sarah@student.edu', '$2y$10$fQzx5Mp0OOu8o.E8laooM.vlvHSwNF7OxQSWqN4Yih3qwvFy..9ou', 'student', '2025-10-11 23:17:55'),
(6, 'STU005', NULL, NULL, 'David Brown', 'David Brown', 'david@student.edu', '$2y$10$v1rWzH2pOtf9AbgVRqkLNep5FSHOy9jlL9ydX7Xg/S8EdBpyTiGXC', 'student', '2025-10-11 23:17:55'),
(7, 'S1012', NULL, NULL, 'Rhaiza', 'Rhaiza', 'rhaizaalberto931@gmail.com', '$2y$10$MpItptAOyg/6tk7XviZ5EeinAQOvwNu3YumCJVDfLgd4/78WI7WgK', 'student', '2025-10-13 02:36:49'),
(8, 'STU006', NULL, NULL, 'JOHN', 'JOHN', 'JOHN@GMAIL.COM', '$2y$10$RpXfLbn3kP90dxnjticJ6u8B9LrB0xekNHx/rI3bxH3PbszL12Ehi', 'student', '2025-10-26 17:59:30'),
(9, 'STU726', NULL, NULL, 'Elena Alberto', 'Elena Alberto', 'alberto@gmail.com', '$2y$10$lke9EpQbqHhIZAaymiyUa.hiNtvXeLZaB/YARm6jiRrCw/8a1h2dK', 'student', '2025-10-27 02:24:10'),
(11, 'STU333', NULL, NULL, NULL, 'Jaymie Tuble', 'jaymie@gmail.com', '$2y$10$mpBvisnV8vnjWXYcKHhEzeg8yNJteyOeZ7EYYtfauIVUfwmfhS68m', 'student', '2025-10-27 07:09:34'),
(12, 'STU555', 'Rhaiza', 'Yongot', 'Alberto', 'Rhaiza Yongot Alberto', 'rhaizaalberto@gmail.com', 'pass123', 'student', '2025-10-27 07:13:54'),
(13, 'STU777', 'Anna', '', 'Bell', 'Anna Bell', 'anna@gmail.com', '$2y$10$/PytbS4wxQXVOxTtD3BWneIagdpPiI1Uw5Mzj8CwfdeCXuLliwFvK', 'student', '2025-10-27 07:17:05'),
(14, 'STU124', 'Rose', '', 'Alberto', 'Rose Alberto', 'rose@gmail.com', '$2y$10$nBnbZcvcVDbJvjSmCBdNmO0gk9DEjwGJ.m43yC.tsH..RhZ93nVUW', 'student', '2025-10-27 23:18:00'),
(15, 'STU111', 'Elsa', '', 'Frozen', 'Elsa Frozen', 'else@gmail.com', '$2y$10$RcWbIfDmJhZ1U7p6WlGYY.GLoWrvccRGJbFX9vwXy6dbidF5zQt.u', 'student', '2025-10-28 02:39:05');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `candidate_name` varchar(255) DEFAULT NULL,
  `candidate_student_id` varchar(50) DEFAULT NULL,
  `position_id` int(11) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `session_id`, `voter_id`, `candidate_id`, `candidate_name`, `candidate_student_id`, `position_id`, `voted_at`) VALUES
(63, 31, 13, NULL, 'Anna Bell', 'STU777', 1, '2025-10-28 03:17:47'),
(66, 31, 8, NULL, 'David Brown', 'STU005', 2, '2025-10-28 03:34:30'),
(67, 31, 13, NULL, 'David Brown', 'STU005', 2, '2025-10-28 03:35:28'),
(68, 33, 13, NULL, 'Anna Bell', 'STU777', 1, '2025-10-28 05:41:17'),
(69, 33, 6, NULL, 'David Brown', 'STU005', 1, '2025-10-28 05:41:36'),
(70, 33, 9, NULL, 'Anna Bell', 'STU777', 1, '2025-10-28 05:42:29'),
(71, 34, 9, 56, 'Anna Bell', 'STU777', 1, '2025-10-28 06:05:03'),
(72, 34, 2, 56, 'Anna Bell', 'STU777', 1, '2025-10-28 06:05:40'),
(73, 34, 11, 57, 'David Brown', 'STU005', 1, '2025-10-28 06:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `vote_logs`
--

CREATE TABLE `vote_logs` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `vote_count` int(11) DEFAULT 0,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voting_sessions`
--

CREATE TABLE `voting_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(100) NOT NULL,
  `status` enum('pending','active','paused','locked','completed') DEFAULT 'pending',
  `current_position_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voting_sessions`
--

INSERT INTO `voting_sessions` (`id`, `session_name`, `status`, `current_position_id`, `created_by`, `created_at`) VALUES
(31, 'Election 2026', 'locked', NULL, 1, '2025-10-28 03:16:15'),
(33, 'Election 2025', 'locked', NULL, 1, '2025-10-28 05:40:39'),
(34, 'Classroom 2025', 'active', 1, 1, '2025-10-28 06:04:22');

-- --------------------------------------------------------

--
-- Table structure for table `winners`
--

CREATE TABLE `winners` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vote_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `winners`
--

INSERT INTO `winners` (`id`, `session_id`, `position_id`, `user_id`, `vote_count`, `created_at`) VALUES
(1, 25, 1, 13, 1, '2025-10-27 22:50:24'),
(2, 26, 1, 13, 2, '2025-10-27 23:16:51'),
(3, 28, 1, 13, 1, '2025-10-27 23:39:24'),
(5, 29, 1, 13, 2, '2025-10-28 00:11:39'),
(6, 30, 1, 6, 3, '2025-10-28 02:50:47'),
(7, 31, 1, 13, 1, '2025-10-28 03:18:19'),
(8, 31, 2, 6, 2, '2025-10-28 03:36:02'),
(9, 33, 1, 13, 2, '2025-10-28 05:43:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_candidate` (`user_id`,`position_id`),
  ADD KEY `idx_candidate_status` (`status`),
  ADD KEY `idx_candidate_user_position` (`user_id`,`position_id`),
  ADD KEY `candidates_ibfk_2` (`position_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_name` (`last_name`,`first_name`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`session_id`,`voter_id`,`position_id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `idx_votes_session_position` (`session_id`,`position_id`),
  ADD KEY `idx_votes_candidate` (`candidate_id`,`session_id`),
  ADD KEY `idx_candidate_name` (`candidate_name`);

--
-- Indexes for table `vote_logs`
--
ALTER TABLE `vote_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vote_logs_ibfk_1` (`session_id`),
  ADD KEY `vote_logs_ibfk_2` (`position_id`),
  ADD KEY `vote_logs_ibfk_3` (`candidate_id`);

--
-- Indexes for table `voting_sessions`
--
ALTER TABLE `voting_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `current_position_id` (`current_position_id`);

--
-- Indexes for table `winners`
--
ALTER TABLE `winners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_winner` (`session_id`,`position_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `vote_logs`
--
ALTER TABLE `vote_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voting_sessions`
--
ALTER TABLE `voting_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `winners`
--
ALTER TABLE `winners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `voting_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vote_logs`
--
ALTER TABLE `vote_logs`
  ADD CONSTRAINT `vote_logs_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `voting_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vote_logs_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `vote_logs_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `voting_sessions`
--
ALTER TABLE `voting_sessions`
  ADD CONSTRAINT `voting_sessions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `voting_sessions_ibfk_2` FOREIGN KEY (`current_position_id`) REFERENCES `positions` (`id`);

--
-- Constraints for table `winners`
--
ALTER TABLE `winners`
  ADD CONSTRAINT `winners_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
