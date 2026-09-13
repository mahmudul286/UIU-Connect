-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 04:55 PM
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
(1, 1, 'A+', '2026-08-08', 1, '01587235873');

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
(1, 1, 'Suyqud', 'A+', 1, 'Evarcare Hospital', 'Urgent', '2026-09-08', '0174895738', 'active', '2026-09-06 20:25:01');

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
(4, 'UIU Sports Club', 'Sports', 'Organizing intra-university tournaments and promoting physical fitness.', NULL, '2026-09-06 20:59:06');

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
(6, 2, 1, 'Member', '2026-09-09 09:02:43');

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
(45, 1, 1, 'hello', '2026-09-06 20:11:31', 1);

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
  `room_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 1),
(2, 1, 2),
(3, 1, 3);

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 1, 5);

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
(1, 1, 'Data Structure book', 600.00, 'Like New', 'approved', '2026-09-06 19:58:36', 'Others', NULL, NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` enum('like','comment','connection','announcement') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `is_edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `post_type`, `content`, `tags`, `created_at`, `media_path`, `media_type`, `poll_data`, `shared_from_id`, `is_edited`) VALUES
(1, 1, 'General', 'sddgrh', NULL, '2026-09-06 13:56:11', NULL, NULL, NULL, NULL, 0),
(3, 1, 'General', 'bfggnfg', NULL, '2026-09-06 18:49:58', NULL, NULL, '[{\"text\":\"ewg\",\"votes\":0},{\"text\":\"erg\",\"votes\":0},{\"text\":\"errh\",\"votes\":0}]', NULL, 0),
(6, 1, 'General', 'hello dear', NULL, '2026-09-06 20:05:32', NULL, NULL, NULL, NULL, 0),
(7, 1, 'General', 'wedfg', NULL, '2026-09-06 20:05:36', NULL, NULL, NULL, NULL, 0),
(8, 1, 'General', 'erg', NULL, '2026-09-06 20:05:38', NULL, NULL, NULL, NULL, 0);

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Mahmudul Hasan', 'hdfhdfhetet@gmail.com', '$2y$10$9h5cHI.7UWp8KxPcof7z0eztuU.cHQ.hRSh159RZdL3jNYIgXYJn6', 'Student', 'CSE', NULL, 'assets/default-avatar.png', '2026-09-06 10:59:44', 'uploads/profiles/1788729230_323c03dcf1f144378d7e6b75f6ca26f2.jpg', 'Competitive Programmer', 'I am a great learner', 'Dhaka', 'Tangail');

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
(8, 1, 'HTML');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blood_bank`
--
ALTER TABLE `blood_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blood_donors`
--
ALTER TABLE `blood_donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `club_events`
--
ALTER TABLE `club_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `club_members`
--
ALTER TABLE `club_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faculty_slots`
--
ALTER TABLE `faculty_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `marketplace`
--
ALTER TABLE `marketplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poll_votes`
--
ALTER TABLE `poll_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_likes`
--
ALTER TABLE `project_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saved_posts`
--
ALTER TABLE `saved_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
