ALTER TABLE `kiwire_policies`
CHANGE `updated_date` `updated_date` timestamp NOT NULL DEFAULT current_timestamp() AFTER `tenant_id`,
ADD `security_block` char(1) COLLATE 'utf8mb4_general_ci' NULL AFTER `allow_carry_forward`;