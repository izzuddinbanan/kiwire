CREATE TABLE `kiwire_voucher_generate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `bulk_id` char(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `tenant_id` (`tenant_id`) USING BTREE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
