ALTER TABLE `kiwire_int_pms`
ADD `use_first_login_only` char(1) COLLATE 'utf8mb4_general_ci' NULL DEFAULT 'n' AFTER `pass_mode`;