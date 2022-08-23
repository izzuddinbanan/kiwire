ALTER TABLE `kiwire_payment_trx`
ADD `user_info` text COLLATE 'utf8mb4_general_ci' NULL AFTER `payload`;

ALTER TABLE `kiwire_paloalto` ADD `source_user`  char(255) NULL;