-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2024 at 06:57 AM
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

CREATE TABLE `departments` (
  `id` tinyint NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `ticket` int NOT NULL,
  `user` int NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `message` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

CREATE TABLE `priorities` (
  `id` tinyint NOT NULL,
  `name` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `roles` (
  `id` tinyint NOT NULL,
  `role_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `splited_tickets`
--

CREATE TABLE `splited_tickets` (
  `id` int NOT NULL,
  `old_ticket` int NOT NULL,
  `new_ticket` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` tinyint NOT NULL,
  `name` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`) VALUES
(1, 'waiting'),
(2, 'in progress'),
(3, 'closed'),
(4, 'splitted');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_year` smallint(4) NOT NULL,
  `created_month` tinyint NOT NULL,
  `created_day` tinyint NOT NULL,
  `closed_date` timestamp NULL DEFAULT NULL,
  `closed_year` smallint(4) DEFAULT NULL,
  `closed_month` tinyint DEFAULT NULL,
  `closed_day` tinyint DEFAULT NULL,
  `department` tinyint NOT NULL,
  `created_by` int NOT NULL,
  `handled_by` int NOT NULL,
  `priority` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `ticket` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `role_id` tinyint NOT NULL,
  `phone` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_ticket_tickets_id` (`ticket`),
  ADD KEY `fk_messages_user_users_id` (`user`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messageattachments_message_messages_id` (`message`);

--
-- Indexes for table `priorities`
--
ALTER TABLE `priorities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `splited_tickets`
--
ALTER TABLE `splited_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_splitedtickets_newticket_tickets_id` (`new_ticket`),
  ADD KEY `fk_splitedtickets_oldticket_tickets_id` (`old_ticket`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tickets_createdby_users_id` (`created_by`),
  ADD KEY `fk_tickets_department_departments_id` (`department`),
  ADD KEY `fk_tickets_handledby_users_id` (`handled_by`),
  ADD KEY `fk_tickets_priority_priorities_id` (`priority`),
  ADD KEY `fk_tickets_status_statuses_id` (`status`);

--
-- Indexes for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticketattachments_ticket_tickets_id` (`ticket`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users_roleid_roles_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` tinyint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `priorities`
--
ALTER TABLE `priorities`
  MODIFY `id` tinyint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `splited_tickets`
--
ALTER TABLE `splited_tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` tinyint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_ticket_tickets_id` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `fk_messages_user_users_id` FOREIGN KEY (`user`) REFERENCES `users` (`id`);

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_messageattachments_message_messages_id` FOREIGN KEY (`message`) REFERENCES `messages` (`id`);

--
-- Constraints for table `splited_tickets`
--
ALTER TABLE `splited_tickets`
  ADD CONSTRAINT `fk_splitedtickets_newticket_tickets_id` FOREIGN KEY (`new_ticket`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `fk_splitedtickets_oldticket_tickets_id` FOREIGN KEY (`old_ticket`) REFERENCES `tickets` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_createdby_users_id` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_tickets_department_departments_id` FOREIGN KEY (`department`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_tickets_handledby_users_id` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_tickets_priority_priorities_id` FOREIGN KEY (`priority`) REFERENCES `priorities` (`id`),
  ADD CONSTRAINT `fk_tickets_status_statuses_id` FOREIGN KEY (`status`) REFERENCES `statuses` (`id`);

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `fk_ticketattachments_ticket_tickets_id` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roleid_roles_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
