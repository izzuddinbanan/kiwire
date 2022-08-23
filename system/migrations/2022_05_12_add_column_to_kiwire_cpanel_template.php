ALTER TABLE `kiwire_cpanel_template`
ADD `login` char(1) COLLATE 'utf8mb4_general_ci' NULL DEFAULT 'n' AFTER `register`,
ADD `voucher` char(1) COLLATE 'utf8mb4_general_ci' NULL DEFAULT 'n' AFTER `login`;