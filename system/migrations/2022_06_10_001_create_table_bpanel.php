CREATE TABLE `kiwire_bpanel_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `enabled` char(1) DEFAULT 'n',
  `page` char(120) DEFAULT NULL,
  `page_complete` char(120) DEFAULT NULL,
  `profile` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;