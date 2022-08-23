ALTER TABLE `kiwire_payment_trx`
ADD `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `tenant_id`,
ADD `response` text COLLATE 'utf8mb4_general_ci' NULL AFTER `status`,
ADD `payload` text COLLATE 'utf8mb4_general_ci' NULL AFTER `response`;