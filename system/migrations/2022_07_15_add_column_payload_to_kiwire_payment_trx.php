ALTER TABLE `kiwire_payment_trx`
ADD `payload` text COLLATE 'utf8mb4_general_ci' NULL AFTER `response`;