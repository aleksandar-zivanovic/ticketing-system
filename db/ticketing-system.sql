-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 06:39 PM
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
-- Database: `ticketing-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'Unassigned'),
(2, 'Human Resources'),
(3, 'Finance'),
(4, 'Operations'),
(5, 'Marketing'),
(6, 'Information Technology');

-- --------------------------------------------------------

--
-- Table structure for table `favorite_tickets`
--

DROP TABLE IF EXISTS `favorite_tickets`;
CREATE TABLE IF NOT EXISTS `favorite_tickets` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `ticket` int(11) NOT NULL,
  KEY `fk_favorite_tickets_user_users_id` (`user`),
  KEY `favorite_tickets_ticket_tickets_id` (`ticket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_messages_ticket_tickets_id` (`ticket`),
  KEY `fk_messages_user_users_id` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

DROP TABLE IF EXISTS `message_attachments`;
CREATE TABLE IF NOT EXISTS `message_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `message` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_messageattachments_message_messages_id` (`message`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `used` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
CREATE TABLE IF NOT EXISTS `priorities` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`id`, `name`) VALUES
(1, 'low'),
(2, 'normal'),
(3, 'high'),
(4, 'urgent');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'user'),
(2, 'moderator'),
(3, 'admin'),
(4, 'unverified'),
(5, 'blocked');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
CREATE TABLE IF NOT EXISTS `statuses` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`) VALUES
(1, 'waiting'),
(2, 'in progress'),
(3, 'closed');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_date` timestamp NULL DEFAULT NULL,
  `department` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `priority` tinyint(4) NOT NULL,
  `statusId` tinyint(4) NOT NULL,
  `was_reopened` tinyint(1) NOT NULL DEFAULT 0,
  `closing_type` varchar(10) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent_ticket` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tickets_createdby_users_id` (`created_by`),
  KEY `fk_tickets_department_departments_id` (`department`),
  KEY `fk_tickets_handledby_users_id` (`handled_by`),
  KEY `fk_tickets_priority_priorities_id` (`priority`),
  KEY `fk_tickets_statusId_statuses_id` (`statusId`) USING BTREE,
  KEY `fk_tickets_parent_ticket_tikcets_id` (`parent_ticket`)
) ENGINE=InnoDB AUTO_INCREMENT=485 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `ticket` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ticketattachments_ticket_tickets_id` (`ticket`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `role_id` tinyint(4) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `department_id` tinyint(4) DEFAULT NULL,
  `verification_code` varchar(40) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `session_version` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_users_roleid_roles_id` (`role_id`),
  KEY `fk_users_departmentid_departments_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_management_audit`
--

DROP TABLE IF EXISTS `user_management_audit`;
CREATE TABLE IF NOT EXISTS `user_management_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `performed_by` int(11) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `old_value` varchar(20) NOT NULL,
  `new_value` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_uma_performed_by_users_id` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_management_audit_targets`
--

DROP TABLE IF EXISTS `user_management_audit_targets`;
CREATE TABLE IF NOT EXISTS `user_management_audit_targets` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`audit_id`,`user_id`),
  KEY `fk_umat_user_id _users_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_password_history`
--

DROP TABLE IF EXISTS `user_password_history`;
CREATE TABLE IF NOT EXISTS `user_password_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `years`
--

DROP TABLE IF EXISTS `years`;
CREATE TABLE IF NOT EXISTS `years` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=2025 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorite_tickets`
--
ALTER TABLE `favorite_tickets`
  ADD CONSTRAINT `favorite_tickets_ticket_tickets_id` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favorite_tickets_user_users_id` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_ticket_tickets_id` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_user_users_id` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_messageattachments_message_messages_id` FOREIGN KEY (`message`) REFERENCES `messages` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_createdby_users_id` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_tickets_department_departments_id` FOREIGN KEY (`department`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_tickets_handledby_users_id` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_tickets_parent_ticket_tikcets_id` FOREIGN KEY (`parent_ticket`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `fk_tickets_priority_priorities_id` FOREIGN KEY (`priority`) REFERENCES `priorities` (`id`),
  ADD CONSTRAINT `fk_tickets_statusId_statuses_id` FOREIGN KEY (`statusId`) REFERENCES `statuses` (`id`);

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `fk_ticketattachments_ticket_tickets_id` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_departmentid_departments_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_users_roleid_roles_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_management_audit`
--
ALTER TABLE `user_management_audit`
  ADD CONSTRAINT `fk_uma_performed_by_users_id` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_management_audit_targets`
--
ALTER TABLE `user_management_audit_targets`
  ADD CONSTRAINT `fk_umat_audit_id_uma_id` FOREIGN KEY (`audit_id`) REFERENCES `user_management_audit` (`id`),
  ADD CONSTRAINT `fk_umat_user_id _users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
