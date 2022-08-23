ALTER TABLE `kiwire_controller`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_radius`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_group_mapping`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_msad`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_ldap`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_social`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_email`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_sms`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_marketing_email`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_external_db`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_pms`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_api_setting`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_webhook`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_payment_gateways`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;

ALTER TABLE `kiwire_int_hss`
ADD `start_time` time DEFAULT NULL,
ADD `stop_time` time DEFAULT NULL,
ADD `is_24_hour` tinyint NULL DEFAULT 1;



