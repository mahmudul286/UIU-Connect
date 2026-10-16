-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 05:09 PM
-- Server version: 11.8.3-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uiu_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumni_profiles`
--

CREATE TABLE `alumni_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_company` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `is_mentor` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni_profiles`
--

INSERT INTO `alumni_profiles` (`id`, `user_id`, `current_company`, `designation`, `linkedin_url`, `is_mentor`) VALUES
(1, 1, 'BJIT', 'Senior Software Engineer', 'https://linkedin.com/in/mahmudul20bd/', 1);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `student_id`, `faculty_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`) VALUES
(1, 7, 8, '2026-10-10', '12:00:00', 'Discussion Regarding FYDP', 'Approved', '2026-10-03 10:58:23'),
(2, 7, 8, '2026-10-13', '12:30:00', 'hgchg', 'Pending', '2026-10-03 17:29:43'),
(3, 7, 6, '2026-10-04', '10:00:00', 'hgfftru', 'Approved', '2026-10-03 17:46:29'),
(4, 7, 8, '2026-10-10', '12:00:00', 'rgehteh', 'Pending', '2026-10-04 08:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank`
--

CREATE TABLE `blood_bank` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `last_donation` date DEFAULT NULL,
  `status` enum('Eligible','Unavailable') DEFAULT 'Eligible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blood_donors`
--

CREATE TABLE `blood_donors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `last_donation` date DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_donors`
--

INSERT INTO `blood_donors` (`id`, `user_id`, `blood_group`, `last_donation`, `is_available`, `contact_number`) VALUES
(1, 1, 'A+', '2026-08-08', 1, '01587235873'),
(2, 7, 'O-', '2026-10-01', 1, '6757453364'),
(3, 204, 'O-', NULL, 1, '01799887766'),
(4, 205, 'AB+', NULL, 1, '01899887766'),
(5, 304, 'B+', NULL, 1, '01700112233'),
(6, 305, 'O-', NULL, 1, '01800112233');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `bags_needed` int(11) DEFAULT 1,
  `hospital_name` varchar(255) NOT NULL,
  `urgency_level` enum('Normal','Urgent','Critical') DEFAULT 'Urgent',
  `needed_date` date NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `status` enum('active','fulfilled') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `requester_id`, `patient_name`, `blood_group`, `bags_needed`, `hospital_name`, `urgency_level`, `needed_date`, `contact_number`, `status`, `created_at`) VALUES
(1, 1, 'Suyqud', 'A+', 1, 'Evarcare Hospital', 'Urgent', '2026-09-08', '0174895738', 'active', '2026-09-06 20:25:01'),
(2, 1, 'Asma Akter', 'B-', 3, 'Evercare Hospital, Bashundhara', 'Critical', '2026-10-05', '01233785467', 'active', '2026-10-03 18:30:40'),
(201, 204, 'Rubina Akter', 'A+', 1, 'Square Hospital, Panthapath', 'Critical', '2026-10-05', '01988776655', 'active', '2026-10-04 08:21:43'),
(301, 303, 'Abdul Jabbar', 'A-', 2, 'United Hospital, Gulshan', 'Urgent', '2026-10-07', '01900112233', 'active', '2026-10-04 08:52:20');

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `category`, `description`, `logo_path`, `created_at`) VALUES
(1, 'UIU Computer Club', 'Technology', 'A community of tech enthusiasts focusing on programming, CP, and development.', NULL, '2026-09-06 20:59:06'),
(2, 'UIU Cultural Club', 'Cultural', 'Promoting art, music, dance, and cultural heritage across the campus.', NULL, '2026-09-06 20:59:06'),
(3, 'UIU Robotics Club', 'Technology', 'Building the future through automation, IoT, and robotics projects.', NULL, '2026-09-06 20:59:06'),
(4, 'UIU Sports Club', 'Sports', 'Organizing intra-university tournaments and promoting physical fitness.', NULL, '2026-09-06 20:59:06'),
(201, 'UIU Robotics Club', 'Technology', 'Building the future, one robot at a time. Join us for exciting projects!', NULL, '2026-10-04 08:21:43'),
(202, 'UIU Photography Club', 'Cultural', 'Capturing moments that last forever. Weekly photo walks and exhibitions.', NULL, '2026-10-04 08:21:43'),
(301, 'UIU App Developers Club', 'Technology', 'Learn iOS, Android, and Flutter development. Weekly coding sessions.', NULL, '2026-10-04 08:52:20'),
(302, 'UIU Debating Society', 'Social', 'Enhance your public speaking and logical reasoning skills.', NULL, '2026-10-04 08:52:20');

-- --------------------------------------------------------

--
-- Table structure for table `club_events`
--

CREATE TABLE `club_events` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club_members`
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT 'Member',
  `joined_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_members`
--

INSERT INTO `club_members` (`id`, `club_id`, `user_id`, `role`, `joined_at`) VALUES
(2, 1, 1, 'Member', '2026-09-06 21:00:11'),
(4, 4, 1, 'Member', '2026-09-06 21:00:13'),
(5, 3, 1, 'Member', '2026-09-06 21:00:14'),
(6, 2, 1, 'Member', '2026-09-09 09:02:43'),
(7, 1, 7, 'Member', '2026-10-03 17:32:24');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `comment_text`, `created_at`, `parent_id`) VALUES
(1, 1, 1, 'tnmh', '2026-09-06 18:45:11', NULL),
(2, 1, 1, 'y6h', '2026-09-06 18:50:41', 1),
(3, 1, 1, 'ukm', '2026-09-06 18:50:50', 2),
(4, 1, 1, 'ukm', '2026-09-06 18:50:52', 2),
(5, 1, 1, 'ukm', '2026-09-06 18:50:52', 2),
(6, 1, 1, 'ukm', '2026-09-06 18:50:52', 2),
(7, 1, 1, 'tgbn', '2026-09-06 18:57:42', 1),
(8, 1, 1, '6yu', '2026-09-06 18:57:50', 1),
(9, 1, 1, '9ol', '2026-09-06 18:57:55', 1),
(10, 4, 1, 'rthrt', '2026-09-06 18:59:46', NULL),
(11, 4, 1, 'erh', '2026-09-06 18:59:48', 10),
(12, 4, 1, 'erh', '2026-09-06 18:59:50', 10),
(13, 4, 1, 'erh', '2026-09-06 18:59:51', 10),
(14, 4, 1, 'erh', '2026-09-06 18:59:52', 10),
(15, 4, 1, 'erhtyju', '2026-09-06 18:59:53', 10),
(16, 4, 1, 'erhtyju', '2026-09-06 18:59:54', 10),
(17, 4, 1, 'erhtyju', '2026-09-06 18:59:54', 10),
(18, 4, 1, 'erhtyju', '2026-09-06 18:59:54', 10),
(19, 4, 1, 'erhtyju', '2026-09-06 18:59:57', 10),
(20, 4, 1, 'erhtyjuuikl', '2026-09-06 18:59:58', 10),
(21, 4, 1, 'erhtyjuuikl', '2026-09-06 18:59:58', 10),
(22, 4, 1, 'erhtyjuuikl', '2026-09-06 18:59:58', 10),
(23, 4, 1, 'yhk', '2026-09-06 19:00:06', 10),
(24, 4, 1, 'yhk', '2026-09-06 19:00:06', 10),
(25, 4, 1, 'yhk', '2026-09-06 19:00:06', 10),
(26, 4, 1, 'yhk', '2026-09-06 19:00:06', 10),
(27, 4, 1, 'yhk', '2026-09-06 19:00:07', 10),
(28, 4, 1, 'yhk', '2026-09-06 19:00:07', 10),
(29, 4, 1, 'yhk', '2026-09-06 19:00:07', 10),
(30, 4, 1, 'yuk', '2026-09-06 19:00:16', 10),
(31, 4, 1, 'yuk', '2026-09-06 19:00:16', 10),
(32, 2, 1, 'ty', '2026-09-06 19:04:15', NULL),
(33, 2, 1, 'yuik', '2026-09-06 19:04:17', 32),
(34, 2, 1, 'yuik', '2026-09-06 19:04:20', 32),
(35, 2, 1, 'yuik', '2026-09-06 19:04:21', 32),
(36, 2, 1, 'yuik', '2026-09-06 19:04:21', 32),
(37, 2, 1, 'yuik', '2026-09-06 19:04:21', 32),
(38, 2, 1, 'yu\\', '2026-09-06 19:04:28', 32),
(39, 2, 1, 'yu\\', '2026-09-06 19:04:28', 32),
(40, 2, 1, 'yu\\', '2026-09-06 19:04:28', 32),
(41, 4, 1, 'hello', '2026-09-06 19:12:51', NULL),
(42, 4, 1, 'ok', '2026-09-06 19:12:57', 41),
(43, 4, 1, 'ok', '2026-09-06 19:13:14', NULL),
(44, 4, 1, 'erfghb', '2026-09-06 19:13:20', 41),
(45, 1, 1, 'hello', '2026-09-06 20:11:31', 1),
(46, 201, 204, 'Hey Nabila, check your inbox. I sent you the drive link!', '2026-10-04 08:21:43', NULL),
(47, 203, 206, 'I am an alumni, but I can mentor your team if you want!', '2026-10-04 08:21:43', NULL),
(301, 301, 306, 'You can build an AI-based campus navigation system or a real-time transport tracker. Let me know if you need help with the architecture.', '2026-10-04 08:52:20', NULL),
(302, 303, 305, 'Welcome back, apu! Hope to see you at the networking session.', '2026-10-04 08:52:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_likes`
--

INSERT INTO `comment_likes` (`id`, `user_id`, `comment_id`) VALUES
(6, 1, 1),
(1, 1, 2),
(2, 1, 7),
(4, 1, 10),
(5, 1, 32);

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user1_id` int(11) GENERATED ALWAYS AS (least(`sender_id`,`receiver_id`)) STORED,
  `user2_id` int(11) GENERATED ALWAYS AS (greatest(`sender_id`,`receiver_id`)) STORED
) ;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, 4, 1, 'accepted', '2026-09-13 15:54:56'),
(4, 7, 1, 'accepted', '2026-10-03 17:50:28'),
(5, 7, 8, 'pending', '2026-10-04 08:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `section`, `faculty_id`, `day_of_week`, `start_time`, `end_time`, `room_no`) VALUES
(5, 'CSE4165', 'Web Programming', 'G', 8, 'Saturday', '14:00:00', '16:00:00', '426'),
(6, 'CSE4325', 'Microprocessors and Microcontrollers', 'A', 6, 'Saturday', '11:11:00', '12:30:00', '428'),
(7, 'CSE 4889', 'Machine Learning', 'G', 9, 'Wednesday', '11:11:00', '12:30:00', '425'),
(8, 'ECO 4101', 'Economics', 'H', 11, 'Sunday', '15:11:00', '16:30:00', '701'),
(9, 'CSE 4326', 'Microprocessors and Microcontrollers Laboratory', 'H', 6, 'Tuesday', '14:00:00', '16:30:00', '511'),
(10, 'CSE 3421', 'Software Engineering', 'D', 8, 'Saturday', '13:50:00', '15:10:00', '322'),
(201, 'EEE2123', 'Electrical Circuits I', 'A', 202, 'Sunday', '08:30:00', '10:00:00', '315'),
(202, 'MGT3211', 'Organizational Behavior', 'B', 203, 'Monday', '10:00:00', '11:30:00', '210'),
(203, 'ENG1011', 'English I', 'C', 203, 'Tuesday', '14:00:00', '15:30:00', '405'),
(204, 'CSE2233', 'Data Structures', 'A', 202, 'Wednesday', '12:00:00', '13:30:00', '620'),
(301, 'CSE3211', 'Software Engineering', 'A', 301, 'Sunday', '10:00:00', '11:30:00', '514'),
(302, 'CSE3211', 'Software Engineering', 'B', 301, 'Tuesday', '12:00:00', '13:30:00', '515'),
(303, 'MKT2101', 'Principles of Marketing', 'A', 302, 'Monday', '08:30:00', '10:00:00', '321'),
(304, 'ACT1101', 'Financial Accounting', 'C', 302, 'Wednesday', '14:00:00', '15:30:00', '211');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('Book','Note','CT Question','Mid Question','Final Question') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_materials`
--

INSERT INTO `course_materials` (`id`, `course_id`, `uploader_id`, `title`, `category`, `file_path`, `uploaded_at`) VALUES
(1, 5, 7, 'Mid Question 2025 Summer', 'Mid Question', 'uploads/materials/1791045751_955c0adc885d9876.pdf', '2026-10-03 16:42:31'),
(2, 7, 9, 'Mid Question 2024 Fall', 'Mid Question', 'uploads/materials/1791101001_fbaf788b6c8bdb03.pdf', '2026-10-04 08:03:21'),
(3, 9, 6, 'Mid Question 2025 Summer', 'Mid Question', 'uploads/materials/1791103747_af19e0e490c5e0ba.pdf', '2026-10-04 08:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`) VALUES
(12, 204, 201),
(13, 204, 203),
(14, 205, 204),
(15, 205, 202),
(16, 1, 5),
(17, 1, 6),
(18, 1, 9),
(19, 1, 7),
(20, 1, 8),
(21, 303, 301),
(22, 304, 301),
(23, 305, 303),
(24, 305, 304),
(25, 7, 5),
(26, 7, 6),
(27, 7, 9),
(28, 7, 7),
(29, 7, 8);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_slots`
--

CREATE TABLE `faculty_slots` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_slots`
--

INSERT INTO `faculty_slots` (`id`, `faculty_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 8, 'Saturday', '12:00:00', '13:50:00'),
(2, 8, 'Tuesday', '12:30:00', '15:10:00'),
(3, 6, 'Sunday', '10:00:00', '12:00:00'),
(201, 202, 'Monday', '14:00:00', '16:00:00'),
(202, 202, 'Thursday', '10:00:00', '12:00:00'),
(203, 203, 'Wednesday', '11:00:00', '13:00:00'),
(204, 6, 'Saturday', '13:50:00', '15:10:00'),
(301, 301, 'Sunday', '12:00:00', '14:00:00'),
(302, 301, 'Tuesday', '14:00:00', '16:00:00'),
(303, 302, 'Monday', '11:00:00', '13:00:00'),
(304, 302, 'Wednesday', '10:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `posted_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT 'Remote',
  `job_type` enum('Full-Time','Part-Time','Internship','Contract') DEFAULT 'Full-Time',
  `apply_link` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `admin_approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `posted_by`, `title`, `company`, `location`, `job_type`, `apply_link`, `description`, `created_at`, `admin_approval_status`) VALUES
(1, 7, 'Software Engineering', 'BJIT', 'Dhaka', 'Part-Time', 'https://www.bjit.com', 'jshgfsaiugfwy7', '2026-10-03 18:00:31', 'approved'),
(201, 206, 'Electrical Engineer', 'EnergyPac', 'Tejgaon, Dhaka', 'Full-Time', 'https://energypac.com/careers', 'Looking for enthusiastic EEE graduates with a passion for power systems.', '2026-10-04 08:21:43', 'approved'),
(202, 206, 'Management Trainee', 'Robi Axiata', 'Gulshan, Dhaka', 'Internship', 'https://robi.com/careers', 'Great opportunity for final year BBA students to learn corporate management.', '2026-10-04 08:21:43', 'pending'),
(301, 306, 'Frontend Developer (React)', 'Pathao', 'Banani, Dhaka', 'Full-Time', 'https://pathao.com/careers', 'Looking for a passionate frontend developer with 1+ years of React experience.', '2026-10-04 08:52:20', 'approved'),
(302, 307, 'HR Executive Intern', 'BRAC Bank', 'Tejgaon, Dhaka', 'Internship', 'https://bracbank.com/careers', 'Great opportunity for final year BBA students major in HR.', '2026-10-04 08:52:20', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`) VALUES
(4, 1, 1),
(8, 1, 3),
(9, 1, 4),
(11, 1, 5),
(14, 1, 201),
(15, 6, 301);

-- --------------------------------------------------------

--
-- Table structure for table `marketplace`
--

CREATE TABLE `marketplace` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `item_title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `item_condition` varchar(50) DEFAULT 'Used',
  `admin_approval_status` enum('pending','approved') DEFAULT 'approved',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT 'Others',
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace`
--

INSERT INTO `marketplace` (`id`, `seller_id`, `item_title`, `price`, `item_condition`, `admin_approval_status`, `created_at`, `category`, `description`, `image_path`) VALUES
(1, 1, 'Data Structure book', 600.00, 'Like New', 'approved', '2026-09-06 19:58:36', 'Others', NULL, NULL),
(3, 1, 'Arduino', 150.00, 'Used', 'approved', '2026-10-03 18:53:42', 'Electronics', '', 'uploads/marketplace/4127ab9c0813463354d805e236427330.jpg'),
(201, 205, 'Mini Drafter & Engineering Scale', 350.00, 'Used', 'approved', '2026-10-04 08:21:43', 'Stationery', 'Used only for one semester in Engineering Drawing lab. Perfect condition.', NULL),
(202, 204, 'Physics Lab Coat (Size L)', 250.00, 'Good', 'pending', '2026-10-04 08:21:43', 'Others', 'Clean and washed. No stains.', NULL),
(203, 206, 'Digital Logic Design Textbook', 150.00, 'Good', 'approved', '2026-10-04 08:21:43', 'Books', 'Morris Mano 5th Edition. A bit highlighted.', NULL),
(301, 303, 'ESP32 Microcontroller Board', 400.00, 'Used', 'approved', '2026-10-04 08:52:20', 'Electronics', 'WiFi and Bluetooth enabled. Perfect for IoT projects.', NULL),
(302, 304, 'Calculus Early Transcendentals (10th Ed)', 300.00, 'Good', 'approved', '2026-10-04 08:52:20', 'Books', 'Original print. Some pages are highlighted.', NULL),
(303, 305, 'A3 Size Drafting Table', 1500.00, 'Good', 'pending', '2026-10-04 08:52:20', 'Stationery', 'Wooden drafting table for engineering drawing. Very sturdy.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_reports`
--

CREATE TABLE `marketplace_reports` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace_reports`
--

INSERT INTO `marketplace_reports` (`id`, `item_id`, `user_id`, `created_at`) VALUES
(1, 1, 7, '2026-10-03 19:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message_text`, `is_read`, `created_at`) VALUES
(1, 4, 1, 'hello', 1, '2026-09-13 15:57:40'),
(2, 1, 4, 'hi', 1, '2026-09-13 15:58:40'),
(3, 1, 4, 'good', 1, '2026-09-13 15:58:57'),
(4, 1, 7, 'VyTDd7r1fCO5kf4P9WtJvXRsbW02ZTJCNTduVEx6ZWJQbDg1YUE9PQ==', 1, '2026-10-03 17:53:56');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` enum('like','comment','connection','announcement','appointment') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `sender_id`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 8, 7, 'appointment', 1, 1, '2026-10-03 10:58:23'),
(2, 7, 8, 'appointment', 1, 1, '2026-10-03 11:06:14'),
(3, 1, 7, 'connection', NULL, 1, '2026-10-03 11:08:42'),
(4, 1, 7, 'connection', NULL, 1, '2026-10-03 11:08:44'),
(5, 8, 7, 'appointment', 2, 0, '2026-10-03 17:29:43'),
(6, 6, 7, 'appointment', 3, 1, '2026-10-03 17:46:29'),
(7, 7, 6, 'appointment', 3, 1, '2026-10-03 17:47:05'),
(8, 1, 7, 'connection', NULL, 1, '2026-10-03 17:50:28'),
(9, 1, 7, 'like', 8, 1, '2026-10-03 17:50:45'),
(10, 7, 5, 'announcement', NULL, 1, '2026-10-03 18:01:58'),
(11, 1, 5, 'announcement', NULL, 1, '2026-10-03 19:19:30'),
(12, 7, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(13, 8, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(14, 4, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(15, 1, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(16, 2, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(17, 3, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(18, 6, 5, 'announcement', 9, 0, '2026-10-03 19:20:15'),
(19, 7, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(20, 5, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(21, 8, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(22, 4, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(23, 1, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(24, 2, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(25, 3, 6, 'announcement', NULL, 0, '2026-10-03 19:20:45'),
(26, 205, 1, 'like', 201, 0, '2026-10-04 08:22:13'),
(27, 303, 6, 'like', 301, 0, '2026-10-04 08:53:12'),
(28, 301, 6, 'like', 302, 0, '2026-10-04 08:53:13'),
(29, 8, 7, 'appointment', 4, 0, '2026-10-04 08:58:50'),
(30, 8, 7, 'connection', NULL, 0, '2026-10-04 08:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `poll_votes`
--

CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `option_index` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_votes`
--

INSERT INTO `poll_votes` (`id`, `post_id`, `option_index`, `user_id`) VALUES
(1, 3, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_type` enum('General','Question','Announcement') NOT NULL,
  `content` text NOT NULL,
  `tags` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','document','video') DEFAULT NULL,
  `poll_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`poll_data`)),
  `shared_from_id` int(11) DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `privacy` enum('Public','Connections','Only Me') DEFAULT 'Public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `post_type`, `content`, `tags`, `created_at`, `media_path`, `media_type`, `poll_data`, `shared_from_id`, `is_edited`, `privacy`) VALUES
(1, 1, 'General', 'sddgrh', NULL, '2026-09-06 13:56:11', NULL, NULL, NULL, NULL, 0, 'Public'),
(3, 1, 'General', 'bfggnfg', NULL, '2026-09-06 18:49:58', NULL, NULL, '[{\"text\":\"ewg\",\"votes\":0},{\"text\":\"erg\",\"votes\":0},{\"text\":\"errh\",\"votes\":0}]', NULL, 0, 'Public'),
(6, 1, 'General', 'hello dear', NULL, '2026-09-06 20:05:32', NULL, NULL, NULL, NULL, 0, 'Public'),
(7, 1, 'General', 'wedfg', NULL, '2026-09-06 20:05:36', NULL, NULL, NULL, NULL, 0, 'Public'),
(8, 1, 'General', 'erg', NULL, '2026-09-06 20:05:38', NULL, NULL, NULL, NULL, 0, 'Public'),
(9, 5, 'Announcement', 'Hey There whats up', NULL, '2026-10-03 19:20:15', NULL, NULL, NULL, NULL, 0, 'Public'),
(10, 6, 'Announcement', 'Hello Students', NULL, '2026-10-03 19:20:45', NULL, NULL, NULL, NULL, 0, 'Public'),
(201, 205, 'General', 'Does anyone have the syllabus or slide link for Data Structures (CSE2233)? I missed the first class.', NULL, '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, 0, 'Public'),
(202, 202, 'Announcement', 'Attention EEE2123 Students: We will have an extra makeup class this coming Thursday. Be present on time.', NULL, '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, 0, 'Public'),
(203, 204, 'General', 'Looking for teammates for the upcoming Robotics Hackathon. Anyone interested?', NULL, '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, 0, 'Public'),
(301, 303, 'General', 'Can anyone suggest a good topic for our Software Engineering final project? We are a team of 3.', NULL, '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, 0, 'Public'),
(302, 301, 'Announcement', 'Attention CSE3211 Students: The deadline for the first project proposal has been extended to next Friday.', NULL, '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, 0, 'Public'),
(303, 307, 'General', 'It feels great to be back on campus for the Alumni gathering after 2 years! UIU has changed so much.', NULL, '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, 0, 'Public');

-- --------------------------------------------------------

--
-- Table structure for table `post_edit_history`
--

CREATE TABLE `post_edit_history` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `old_content` text NOT NULL,
  `edited_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

CREATE TABLE `post_reports` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_reports`
--

INSERT INTO `post_reports` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(1, 8, 5, '2026-10-03 18:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `repo_url` varchar(255) DEFAULT NULL,
  `live_url` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `tech_stack` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `repo_url`, `live_url`, `image_path`, `created_at`, `tech_stack`) VALUES
(2, 7, 'UIU Connect', 'UIU Connect is a centralized, role-based campus operating system designed exclusively for United International University. It unifies academic tracking, secure peer-to-peer messaging, and dynamic social networking into a single, intuitive platform. Students, faculty, and alumni can seamlessly collaborate, find emergency blood donors, trade in the campus marketplace, join clubs, and explore career opportunities. Built with end-to-end encryption and real-time AJAX interactions, the platform ensures a premium, seamless user experience. The entire ecosystem is governed by a highly secure master admin portal, creating a safe and efficient digital campus.', 'https://github.com/mahmudul286/UIU-Connect.git', '', 'uploads/projects/1791047699_cddeaebccbccaf15.jpeg', '2026-10-03 17:14:59', 'HTML, CSS, PHP, MySQL');

-- --------------------------------------------------------

--
-- Table structure for table `project_likes`
--

CREATE TABLE `project_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_posts`
--

CREATE TABLE `saved_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_posts`
--

INSERT INTO `saved_posts` (`id`, `user_id`, `post_id`) VALUES
(4, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `task_title` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `course_id`, `faculty_id`, `task_title`, `deadline`, `created_at`, `file_path`) VALUES
(1, 3, 6, 'Assignment - 01', '2026-10-09 00:00:00', '2026-10-02 14:54:52', NULL),
(2, 6, 6, 'Assignment - 01', '2026-10-10 14:40:00', '2026-10-03 17:38:44', NULL),
(3, 6, 6, 'Assignment - 02', '2026-10-20 15:45:00', '2026-10-03 17:41:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Student','Faculty','Admin') DEFAULT 'Student',
  `department` varchar(50) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT 'assets/default-avatar.png',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `bio` varchar(150) DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `current_city` varchar(100) DEFAULT NULL,
  `hometown` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `department`, `semester`, `avatar_path`, `created_at`, `profile_pic`, `bio`, `about_me`, `current_city`, `hometown`) VALUES
(1, 'Mahmudul Hasan', 'hdfhdfhetet@gmail.com', '$2y$10$9h5cHI.7UWp8KxPcof7z0eztuU.cHQ.hRSh159RZdL3jNYIgXYJn6', '', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-06 10:59:44', 'uploads/profiles/1790925059_jonatan-pie-h8nxGssjQXs-unsplash.jpg', 'Competitive Programmer', 'I am a great learner', 'Dhaka', 'Tangail'),
(2, 'Mahmudul Hasan', 'mah@gmail.com', '$2y$10$r/NR1SSyjZPsH2WZOclIAuRRDS3u6VBDg1iBkmVTsj28oq.GPji3i', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-13 15:22:44', NULL, NULL, NULL, NULL, NULL),
(3, 'Mahmudul Hasan', 'mahmudul@gmail.com', '$2y$10$jK6.3iaIxbEseMbGuwCyM.qkvdJaX5Nz2nMWqE74nX0mmw2xU8QPC', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-13 15:33:25', NULL, NULL, NULL, NULL, NULL),
(4, 'Farhan', 'farhan@gmail.com', '$2y$10$oMvwtN2bLJ02AycFlavyLux0KaR9Q2oxa1dKHEsqcemDBwU/L2T6e', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-13 15:49:31', NULL, NULL, NULL, NULL, NULL),
(5, 'admin', 'admin@system.in', '$2y$10$.tQ8BqIst./HrMESufLvpu6FRdRbu87wSBzkuGr5eSGEEpkpmPcUW', 'Admin', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-13 15:49:31', NULL, NULL, NULL, NULL, NULL),
(6, 'Shafiq Ahmed', 'shafiq@gmail.com', '$2y$10$Ruo40rDkgauEGrkMLG2gCOcAG3PkwuWe3sPf4rdU0i734k9Dz1HF2', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-02 14:39:50', NULL, NULL, NULL, NULL, NULL),
(7, 'Abir Hasan', 'abir@bscse.uiu.ac.bd', '$2y$10$BzxMbwRLQLErJHvPJt1xxe5QuqMNJWEAuYXo8IaQPu/CWEkWRl4Qe', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-03 09:09:05', NULL, NULL, NULL, NULL, NULL),
(8, 'Dr. Anisur Rahman', 'anisur@cse.uiu.ac.bd', '$2y$10$sbVFyWi0Tp9PHcI1/fl2OOfB.1mt1Ihcry7suVaSz7au7R3MjshrW', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-03 09:13:00', NULL, NULL, NULL, NULL, NULL),
(9, 'Dr. Kamal Hossain', 'kamal@bscse.uiu.ac.bd', '$2y$10$Yts0ZiXTNkV./X1jTjuJOObVZWUAzTjVdwcI3yC8YMuu4FknSiEIG', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 07:59:55', NULL, NULL, NULL, NULL, NULL),
(10, 'Dr. Rafiqul Islam', 'rafiqul@bscse.uiu.ac.bd', '$2y$10$.DFCE1Ftqt/n5xodF0UQhO3Hk89Yv2OP3xp8Zh/g/cS..Np98dF36', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:13:13', NULL, NULL, NULL, NULL, NULL),
(11, 'Dr. Serajul Islam Choudhury', 'serajul@bscse.uiu.ac.bd', '$2y$10$/RYGwOa71AYtM93AUTLX/.y00VcU0YhFXZpDfYgA0LTyxENpG4g2.', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:14:01', NULL, NULL, NULL, NULL, NULL),
(202, 'Dr. Salma Khatun', 'salma@eee.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faculty', 'EEE', NULL, 'assets/default-avatar.png', '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, NULL),
(203, 'Prof. Rakib Uddin', 'rakib@bba.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faculty', 'BBA', NULL, 'assets/default-avatar.png', '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, NULL),
(204, 'Tanvir Hossain', 'tanvir@bseee.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'EEE', NULL, 'assets/default-avatar.png', '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, NULL),
(205, 'Nabila Haque', 'nabila@bscse.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, NULL),
(206, 'Fahim Rahman', 'fahim@alumni.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'EEE', NULL, 'assets/default-avatar.png', '2026-10-04 08:21:43', NULL, NULL, NULL, NULL, NULL),
(301, 'Prof. Kamrul Hasan', 'kamrul@cse.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faculty', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(302, 'Dr. Laila Zaman', 'laila@bba.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faculty', 'BBA', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(303, 'Sajjad Hossain', 'sajjad@bscse.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(304, 'Nusrat Jahan', 'nusrat@bseee.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'EEE', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(305, 'Imran Khan', 'imran@bba.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'BBA', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(306, 'Arafat Rahman', 'arafat@alumni.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'CSE', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL),
(307, 'Sumaiya Tabassum', 'sumaiya@alumni.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'BBA', NULL, 'assets/default-avatar.png', '2026-10-04 08:52:20', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_name`) VALUES
(4, 1, 'ReactJS'),
(5, 1, 'PHP'),
(6, 1, 'UI/UX Design'),
(7, 1, 'Python'),
(8, 1, 'HTML'),
(9, 7, 'html'),
(10, 7, 'ReactJS'),
(11, 7, 'Python');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_alumni` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `blood_bank`
--
ALTER TABLE `blood_bank`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blood_donors`
--
ALTER TABLE `blood_donors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_donor` (`user_id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_events`
--
ALTER TABLE `club_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `club_members`
--
ALTER TABLE `club_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_membership` (`club_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_clike` (`user_id`,`comment_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_connection` (`user1_id`,`user2_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty_slots`
--
ALTER TABLE `faculty_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`);

--
-- Indexes for table `marketplace`
--
ALTER TABLE `marketplace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `marketplace_reports`
--
ALTER TABLE `marketplace_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`,`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `poll_votes`
--
ALTER TABLE `poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`post_id`,`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_report` (`post_id`,`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project_likes`
--
ALTER TABLE `project_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proj_like` (`user_id`,`project_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`post_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blood_bank`
--
ALTER TABLE `blood_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blood_donors`
--
ALTER TABLE `blood_donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `club_events`
--
ALTER TABLE `club_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `club_members`
--
ALTER TABLE `club_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `faculty_slots`
--
ALTER TABLE `faculty_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `marketplace`
--
ALTER TABLE `marketplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `marketplace_reports`
--
ALTER TABLE `marketplace_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `poll_votes`
--
ALTER TABLE `poll_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_likes`
--
ALTER TABLE `project_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saved_posts`
--
ALTER TABLE `saved_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=308;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alumni_profiles`
--
ALTER TABLE `alumni_profiles`
  ADD CONSTRAINT `alumni_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_bank`
--
ALTER TABLE `blood_bank`
  ADD CONSTRAINT `blood_bank_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_donors`
--
ALTER TABLE `blood_donors`
  ADD CONSTRAINT `blood_donors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_events`
--
ALTER TABLE `club_events`
  ADD CONSTRAINT `club_events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_members`
--
ALTER TABLE `club_members`
  ADD CONSTRAINT `club_members_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_materials_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_slots`
--
ALTER TABLE `faculty_slots`
  ADD CONSTRAINT `faculty_slots_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketplace`
--
ALTER TABLE `marketplace`
  ADD CONSTRAINT `marketplace_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD CONSTRAINT `post_reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_likes`
--
ALTER TABLE `project_likes`
  ADD CONSTRAINT `project_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_likes_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
