ALTER TABLE `kiwire_admin`
CHANGE `updated_date` `updated_date` timestamp NOT NULL DEFAULT current_timestamp() AFTER `tenant_id`,
ADD `photo` varchar(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `password`;