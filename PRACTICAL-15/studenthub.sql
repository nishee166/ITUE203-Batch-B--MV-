-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 01:35 PM
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
-- Database: `studenthub`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 14, 'RAMESH PATEL', 'ADD_STUDENT', 'student', 18, 'Added student: MIHIR  SHAH (Student ID: 25DIT056, Department: IT)', '::1', '2026-10-21 10:59:57'),
(2, 14, 'RAMESH PATEL', 'UPDATE_STUDENT', 'student', 18, 'Updated student: MIHIR  SHAH (Student ID: 25DIT056, Department: CSE)', '::1', '2026-10-21 11:00:42'),
(3, 14, 'RAMESH PATEL', 'UPDATE_EVENT', 'event', 19, 'Updated event: Athletics Meet (Category: Sports, Date: 2026-11-10, Venue: Athletics Track)', '::1', '2026-10-21 11:08:49'),
(4, 14, 'RAMESH PATEL', 'ADD_EVENT', 'event', 24, 'Added event: cooking (Category: cuitural, Date: 2028-11-19, Venue: depstar)', '::1', '2026-10-21 11:11:47'),
(5, 14, 'RAMESH PATEL', 'UPDATE_EVENT', 'event', 24, 'Updated event: cooking (Category: Cultural, Date: 2028-11-19, Venue: depstar)', '::1', '2026-10-21 11:12:50'),
(6, 14, 'RAMESH PATEL', 'DELETE_EVENT', 'event', 24, 'Deleted event: cooking (Category: Cultural)', '::1', '2026-10-21 11:13:32'),
(7, 14, 'RAMESH PATEL', 'ADD_EVENT', 'event', 25, 'Added event: sports (Category: Sports, Date: 2027-01-19, Venue: DEPSTAR)', '::1', '2026-10-21 11:16:21');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `event_date` date NOT NULL,
  `organizer` varchar(100) NOT NULL,
  `venue` varchar(150) NOT NULL,
  `event_time` varchar(30) NOT NULL,
  `status` varchar(50) NOT NULL,
  `poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `organizer`, `venue`, `event_time`, `status`, `poster`) VALUES
(1, 'Cognizance 2026', 'Technical', '2026-08-20', 'DEPSTAR', 'DEPSTAR Campus', '9:00 AM', 'Open', NULL),
(2, 'AI Workshop', 'Technical', '2026-08-28', 'DEPSTAR AI Club', 'DEPSTAR Seminar Hall', '10:00 AM', 'Open', NULL),
(3, 'Hackathon', 'Technical', '2026-09-05', 'CSPIT Innovation Cell', 'CSPIT Lab', '9:00 AM', 'Registration Open', NULL),
(4, 'Coding Competition', 'Technical', '2026-09-12', 'CSPIT IT Department', 'Computer Lab', '10:00 AM', 'Open', NULL),
(5, 'Cyber Security Seminar', 'Technical', '2026-09-20', 'DEPSTAR Cyber Club', 'DEPSTAR Hall', '11:00 AM', 'Open', NULL),
(6, 'Web Development Bootcamp', 'Technical', '2026-09-28', 'CMPICA', 'CMPICA Lab', '9:30 AM', 'Open', NULL),
(7, 'Robotics Workshop', 'Technical', '2026-10-08', 'Robotics Club', 'Innovation Lab', '10:00 AM', 'Open', NULL),
(9, 'Vrund (Navratri Celebration)', 'Cultural', '2026-10-10', 'Student Council', 'University Ground', '7:00 PM', 'Open', NULL),
(10, 'DJ Night', 'Cultural', '2026-10-11', 'Cultural Committee', 'Open Air Theatre', '8:00 PM', 'Open', NULL),
(11, 'Fashion Show', 'Cultural', '2026-10-13', 'Cultural Club', 'Auditorium', '6:30 PM', 'Open', NULL),
(12, 'Dance Competition', 'Cultural', '2026-10-15', 'Dance Club', 'Auditorium', '2:00 PM', 'Registration Open', NULL),
(13, 'Open Mic & Talent Show', 'Cultural', '2026-10-17', 'Literary Club', 'Seminar Hall', '3:00 PM', 'Open', NULL),
(14, 'Sporral 2026', 'Sports', '2026-11-05', 'Sports Committee', 'CHARUSAT Sports Complex', '8:00 AM', 'Open', NULL),
(15, 'Cricket Tournament', 'Sports', '2026-11-06', 'Sports Club', 'Cricket Ground', '8:30 AM', 'Open', NULL),
(16, 'Basketball Championship', 'Sports', '2026-11-07', 'Sports Club', 'Basketball Court', '10:00 AM', 'Open', NULL),
(18, 'Chess Tournament', 'Sports', '2026-11-09', 'Chess Club', 'Seminar Hall', '11:00 AM', 'Open', NULL),
(19, 'Athletics Meet', 'Sports', '2026-11-10', 'Sports Committee', 'Athletics Track', '7:30 AM', 'Cancelled', 'cd1991f8b6ebdec572096c21bba0f563.png'),
(20, 'Food Festival', 'Cultural', '2026-10-14', 'Student Council', 'Central Lawn', '5:00 PM', 'Open', NULL),
(22, 'POSTURE MAKING', 'TECHNICAL', '2029-09-12', 'CHARUSAT', 'DEPSTAR', '12:00 PM', 'Upcoming', 'a4a4575b8af2f315058b6799a957ad7d.png'),
(23, 'GARBA', 'CULTURAL', '2026-10-23', 'CHARUST', 'DEPSTAR', '6:00PM', 'Upcoming', '9b5e0d3e68349188b8fae6ba7193780a.png'),
(25, 'sports', 'Sports', '2027-01-19', 'CHARUSAT', 'DEPSTAR', '12:00pm', 'Upcoming', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `registration_date`) VALUES
(9, 14, 25, '2026-10-21');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `student_name`, `department`, `email`, `password`, `role`) VALUES
(6, '25DIT008', 'AASHTHA', 'IT', 'aashtha15@gmail.com', '$2y$10$SfiwQY7eyu6AIc23GX5jfeFZ.JGyK9AdsydVlsYbPIlikxYJnTz8G', 'student'),
(10, 'ADMIN008', 'rakesh patel', 'admin office', 'rakesh12@gmail.com', '$2y$10$giNkt1Ylw17C3Ai30uHGbufAN5VfV4aJzxMrtxK8nNnWF3pArZbXG', 'admin'),
(14, 'ADMIN009', 'RAMESH PATEL', 'admin office', 'ramesh133@gmail.com', '$2y$10$CwCKyrqr69cmzdvyEQFrPuZodBND5iifO7anvJEkWAwMWsgEnQfcK', 'admin'),
(15, '25DIT061', 'NISHEE PATEL', 'depstar', 'nishee166@gmmail.com', '$2y$10$wXd2OGeMjLbVw.ZQ9MlyeehbGlZfkrjKhvIaLPe6VxbOEqq0RUbfe', 'student'),
(16, '25DIT004', 'NISHA', 'depstar', 'nisha166@gmail.com', '$2y$10$c76gGBbAZcV5lROBqOHyX.nycWEQAGO79OH.V2wGSKcad45pzVwle', 'student'),
(17, '25DIT070', 'RAHUL PATEL', 'CSE', 'rahul177@gmail.com', '$2y$10$DJyOmUN4LfGU4/zKudJ6c.iJ3Bser19U2xnFS7fo5A9zqPU9jSvgS', 'student'),
(18, '25DIT056', 'MIHIR  SHAH', 'CSE', 'mihir166@gmail.com', '$2y$10$zGPnGXrYO5pRc82qrVW9h.HTtKoQQbpl.VUSuB0Dhyc9e5Zad4m26', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `registrations_ibfk_1` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
