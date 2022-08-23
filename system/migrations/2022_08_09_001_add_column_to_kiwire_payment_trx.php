ALTER TABLE `kiwire_payment_trx`
ADD `total` float NULL AFTER `amount`,
ADD `tax` float NULL AFTER `total`,
ADD `user_name` char(250) COLLATE 'utf8mb4_general_ci' NULL AFTER `user_info`,
ADD `user_email` char(250) COLLATE 'utf8mb4_general_ci' NULL AFTER `user_name`,
ADD `user_phone_no` char(250) COLLATE 'utf8mb4_general_ci' NULL AFTER `user_email`;