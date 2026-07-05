-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 04:12 PM
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
-- Database: `course_allocation`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `target_audience` enum('all','students','lecturers') DEFAULT 'all',
  `created_by` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) NOT NULL DEFAULT 3,
  `department` varchar(100) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `max_students` int(11) DEFAULT 50,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `description`, `credits`, `department`, `lecturer_id`, `semester`, `year`, `max_students`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Com 101', 'Intro. To Computing', 'Basic of computer science', 2, 'computer science', 0, 'Fall', 2024, 200, 'active', '2025-07-12 10:20:38', '2025-07-12 10:20:38'),
(2, 'AGT 101', 'Intro. To Agriculture Science', 'Intro ', 3, 'Agricultural Science', 0, 'Fall', 2025, 250, 'active', '2025-07-13 11:30:50', '2025-07-13 11:30:50'),
(3, 'Com 103', 'Algorithm', 'Step By Step', 4, 'computer science', 0, 'Fall', 2025, 150, 'active', '2025-07-13 11:32:51', '2025-07-13 11:32:51');

-- --------------------------------------------------------

--
-- Table structure for table `course_allocations`
--

CREATE TABLE `course_allocations` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `allocated_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_allocations`
--

INSERT INTO `course_allocations` (`id`, `course_id`, `lecturer_id`, `allocated_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-10-06', 'confirmed', 'Be Punctual', '2025-07-12 10:55:32', '2025-07-12 10:59:47'),
(4, 3, 5, '2025-07-01', 'confirmed', '', '2025-07-13 11:39:11', '2025-07-13 11:40:01'),
(5, 2, 4, '2025-07-01', 'confirmed', '', '2025-07-13 11:39:53', '2025-07-13 11:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `course_requests`
--

CREATE TABLE `course_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `request_type` enum('enrollment','drop','schedule_change','grade_review','other') NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `grade_type` enum('midterm','final','assignment','quiz') DEFAULT 'final',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL,
  `lecturer_id` varchar(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`id`, `lecturer_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `department`, `status`, `created_at`, `updated_at`) VALUES
(1, '', 'sarah1', 'sarah.johnson@university.edu', '$2y$10$7qcgsge1LQhV8EJzuHPAResILs3MWU5B8vFNb5ePYS2efELel4V9a', 'Sarah', 'Johnson', '08032446390', 'computer science', 'active', '2025-07-12 09:50:15', '2025-07-12 09:50:15'),
(4, '', 'quadri1', 'olapesequadri1@gmail.com', '$2y$10$I38TUOzJd30YjN0xMjW4VOIB5BcFYormMpt3bEe814ac4HbuwxX2O', 'Dr. Quadri', 'Olapese', '09077724355', 'Agricultural Science', 'active', '2025-07-13 11:23:12', '2025-07-13 11:23:12'),
(5, '', 'fawas1', 'bolaji01@gmail.com', '$2y$10$Bp5.r7ojMAOUDiH19L/cjOmGr6DGgDhj2J43ZIRPykhxSv9bSmaP2', 'Prof. Fawas', 'Lawal', '09115432678', 'computer science', 'active', '2025-07-13 11:25:54', '2025-07-13 11:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lecturer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `course_id`, `day_of_week`, `start_time`, `end_time`, `room`, `created_at`, `lecturer_id`) VALUES
(1, 1, 'Tuesday', '10:00:00', '12:00:00', 'com room 001', '2025-07-12 14:42:58', 1),
(2, 3, 'Monday', '08:00:00', '10:00:00', 'com room 001', '2025-07-13 11:40:46', 5),
(3, 2, 'Monday', '08:00:00', '10:00:00', 'Hannah Hall', '2025-07-13 11:41:35', 4);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `department`, `year_of_study`, `gpa`, `created_at`, `updated_at`) VALUES
(1, 'cs2024010001', 'Bola', 'Ahamed', 'bolaahamed@gmail.com', '$2y$10$tsY2UyZ/rCDH.7g8idw7J..ROLP3XrlXxgVCxzC3JfRrKCoCysctq', '09023229087', 'computer science', 1, 0.00, '2025-07-12 10:34:55', '2025-07-12 10:34:55'),
(3, 'cs2024010002', 'Azeezat', 'Rasheed', 'azeezatrash1@gmail.com', '$2y$10$u63dUu00biBHZnKQYskQqegdAUw0xoRi2p7Tm98VMzNdHJ8uKG0ka', '09115432678', 'Agricultural Science', 2, 3.40, '2025-07-13 11:27:58', '2025-07-13 11:27:58'),
(4, 'cs2024010003', 'Tola', 'Ade', 'adetola1@gmail.com', '$2y$10$p1VmRg1Da7lS4Zos5K/HBuVSHLmU4d2WZN2ndOO0bqXtSsyxbr1aa', '08032446390', 'Agricultural Science', 2, 3.52, '2025-07-14 11:18:07', '2025-07-14 11:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','dropped','completed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`id`, `student_id`, `course_id`, `enrollment_date`, `status`) VALUES
(1, 1, 1, '2025-07-12 12:54:56', 'dropped'),
(2, 3, 3, '2025-07-13 14:23:18', 'enrolled'),
(3, 3, 2, '2025-07-13 14:23:22', 'dropped'),
(4, 3, 1, '2025-07-13 14:23:27', 'enrolled'),
(5, 4, 3, '2025-07-14 12:19:23', 'enrolled'),
(6, 4, 1, '2025-07-14 12:27:49', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `student_course_allocations`
--

CREATE TABLE `student_course_allocations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `allocated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollments`
--

CREATE TABLE `student_enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `status` enum('enrolled','completed','dropped') DEFAULT 'enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_enrollments`
--

INSERT INTO `student_enrollments` (`id`, `student_id`, `course_id`, `enrollment_date`, `grade`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 3, '2025-07-14', NULL, 'enrolled', '2025-07-14 11:19:23', '2025-07-14 11:19:23'),
(2, 4, 1, '2025-07-14', NULL, 'enrolled', '2025-07-14 11:27:49', '2025-07-14 11:27:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','lecturer') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `department`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@university.edu', '$2y$10$Eu9F/k7V8XLdiV2Nd9yrVOXUh7HDgy7C0cODaEEFKEEB94gxDGKc6', 'admin', 'System', 'Administrator', NULL, NULL, '2025-07-12 09:47:53', '2025-07-12 09:47:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `course_allocations`
--
ALTER TABLE `course_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_lecturer` (`course_id`,`lecturer_id`),
  ADD KEY `course_allocations_ibfk_2` (`lecturer_id`);

--
-- Indexes for table `course_requests`
--
ALTER TABLE `course_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_course_allocations`
--
ALTER TABLE `student_course_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_allocations`
--
ALTER TABLE `course_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `course_requests`
--
ALTER TABLE `course_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_course_allocations`
--
ALTER TABLE `student_course_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `lecturers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_allocations`
--
ALTER TABLE `course_allocations`
  ADD CONSTRAINT `course_allocations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_allocations_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_requests`
--
ALTER TABLE `course_requests`
  ADD CONSTRAINT `course_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_requests_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_course_allocations`
--
ALTER TABLE `student_course_allocations`
  ADD CONSTRAINT `student_course_allocations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_course_allocations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD CONSTRAINT `student_enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
