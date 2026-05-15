SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE DATABASE IF NOT EXISTS `green` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `green`;

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `action` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `borrowed_tools` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `tool_id` int(11) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` enum('Active','Returned') DEFAULT 'Active',
  `usage_counter` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `co_tenants` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `farmer_inventory` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `seed_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `irrigation_schedule` (
  `id` int(11) NOT NULL,
  `schedule_time` datetime DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `marketplace` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `produce_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `allergy_tags` varchar(255) DEFAULT NULL,
  `post_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mentorship` (
  `id` int(11) NOT NULL,
  `beginner_id` int(11) DEFAULT NULL,
  `expert_id` int(11) DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Active','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `plot` (
  `id` int(11) NOT NULL,
  `soil_type` varchar(50) DEFAULT NULL,
  `leased_by` int(11) DEFAULT NULL,
  `status` enum('Available','Rented','Shared') DEFAULT 'Available',
  `moisture_level` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `seed_bank` (
  `id` int(11) NOT NULL,
  `seed_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tools` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `availability` varchar(20) DEFAULT 'available',
  `status` enum('Available','Borrowed') DEFAULT 'Available',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `community_points` int(11) DEFAULT 0,
  `karma_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `volunteer_shifts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hours_logged` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `weather_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `borrowed_tools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `tool_id` (`tool_id`);

ALTER TABLE `co_tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `partner_id` (`partner_id`);

ALTER TABLE `farmer_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farmer_id` (`farmer_id`,`seed_name`);

ALTER TABLE `irrigation_schedule`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `marketplace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

ALTER TABLE `mentorship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beginner_id` (`beginner_id`),
  ADD KEY `expert_id` (`expert_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `plot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leased_by` (`leased_by`);

ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `plot_id` (`plot_id`);

ALTER TABLE `seed_bank`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tools`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `volunteer_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vs_user` (`user_id`),
  ADD KEY `fk_vs_shift` (`shift_id`);

ALTER TABLE `weather_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_weather_user` (`user_id`);


ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `borrowed_tools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `farmer_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `irrigation_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `marketplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mentorship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `plot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `seed_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `volunteer_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `weather_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `borrowed_tools`
  ADD CONSTRAINT `borrowed_tools_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowed_tools_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`) ON DELETE CASCADE;

ALTER TABLE `co_tenants`
  ADD CONSTRAINT `co_tenants_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plot` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `co_tenants_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `co_tenants_ibfk_3` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `farmer_inventory`
  ADD CONSTRAINT `farmer_inventory_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `marketplace`
  ADD CONSTRAINT `marketplace_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `mentorship`
  ADD CONSTRAINT `mentorship_ibfk_1` FOREIGN KEY (`beginner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentorship_ibfk_2` FOREIGN KEY (`expert_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `plot`
  ADD CONSTRAINT `plot_ibfk_1` FOREIGN KEY (`leased_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`plot_id`) REFERENCES `plot` (`id`) ON DELETE CASCADE;

ALTER TABLE `volunteer_shifts`
  ADD CONSTRAINT `fk_vs_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `weather_requests`
  ADD CONSTRAINT `fk_weather_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
