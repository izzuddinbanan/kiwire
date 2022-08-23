CREATE TABLE `kiwire_login_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `username` char(255) DEFAULT NULL,
  `password` char(255) DEFAULT NULL,
  `status` char(20) DEFAULT NULL,
  `reason` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;