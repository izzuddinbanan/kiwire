CREATE TABLE `kiwire_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `name` char(255) DEFAULT NULL,
  `service_name` char(255) DEFAULT NULL,
  `status` char(20) DEFAULT 'Active',
  `last_check` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;