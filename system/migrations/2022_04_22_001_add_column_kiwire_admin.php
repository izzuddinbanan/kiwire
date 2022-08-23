ALTER TABLE `kiwire_admin`
ADD `attempt_count` tinyint COLLATE 'utf8mb4_general_ci' NULL DEFAULT 0,
ADD `attempt_time` datetime DEFAULT NULL,
ADD `is_active` tinyint NULL DEFAULT 1;