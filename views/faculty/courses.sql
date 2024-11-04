-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2024 at 06:17 PM
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
-- Database: `eyebook`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `universities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`universities`)),
  `course_book_url` varchar(255) DEFAULT NULL,
  `course_plan` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`course_plan`)),
  `course_materials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`course_materials`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `university_id`, `created_at`, `updated_at`, `universities`, `course_book_url`, `course_plan`, `course_materials`) VALUES
(1, 'Deep Learning', 'AIML', NULL, '2024-10-19 04:31:00', '2024-10-30 17:14:06', '[\"1\"]', '[{\"unitTitle\":\"IT\",\"materials\":[{\"scormDir\":\"uploads\\/1729341744-IT DATA STRUCTURE\",\"indexPath\":\"uploads\\/1729341744-IT DATA STRUCTURE\\/index.html\"}]}]', '{\"url\": \"uploads/1730308446-DL Forward and Backward Propagation - Solved.pdf\"}', NULL),
(2, 'Machine Learning', 'AIML', NULL, '2024-10-19 07:58:13', '2024-10-19 07:58:13', '[]', NULL, NULL, NULL),
(3, 'Predictive Analysis', 'AIML', NULL, '2024-10-19 16:18:31', '2024-10-19 16:18:31', '[]', NULL, NULL, NULL),
(4, 'IT', 'AIML', NULL, '2024-10-19 16:19:40', '2024-10-29 17:53:00', '[\"1\"]', '[{\"unitTitle\":\"IT\",\"materials\":[{\"scormDir\":\"uploads\\/1729354938-IT DATA STRUCTURE\",\"indexPath\":\"uploads\\/1729354938-IT DATA STRUCTURE\\/index.html\"}]}]', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `university_id` (`university_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
