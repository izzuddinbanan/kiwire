-- MySQL dump 10.18  Distrib 10.3.26-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: kiwire
-- ------------------------------------------------------
-- Server version	10.3.26-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `kiwire_account_auth`
--

DROP TABLE IF EXISTS `kiwire_account_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_account_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `creator` char(64) NOT NULL,
  `username` char(120) DEFAULT NULL,
  `fullname` char(255) NOT NULL,
  `email_address` char(64) DEFAULT NULL,
  `phone_number` char(20) DEFAULT NULL,
  `password` char(255) DEFAULT NULL,
  `remark` char(120) DEFAULT NULL,
  `profile_subs` char(120) DEFAULT NULL,
  `profile_curr` char(120) NOT NULL,
  `profile_cus` char(255) NOT NULL,
  `price` int(11) NOT NULL,
  `ktype` char(8) DEFAULT NULL,
  `bulk_id` char(10) NOT NULL,
  `status` char(10) DEFAULT NULL,
  `integration` char(10) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `allowed_mac` char(120) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `date_value` datetime DEFAULT NULL,
  `date_expiry` datetime DEFAULT NULL,
  `date_last_login` datetime DEFAULT NULL,
  `date_last_logout` datetime DEFAULT NULL,
  `date_activate` datetime DEFAULT NULL,
  `date_remove` datetime DEFAULT NULL,
  `date_password` datetime DEFAULT NULL,
  `session_time` int(11) DEFAULT NULL,
  `quota_in` bigint(20) DEFAULT NULL,
  `quota_out` bigint(20) DEFAULT NULL,
  `login` int(11) NOT NULL,
  `password_history` char(255) DEFAULT NULL,
  `total_outstanding` float NOT NULL DEFAULT 0,
  `campaign_history` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_username` (`username`),
  KEY `ki_profile_subs` (`profile_subs`),
  KEY `ki_profile_curr` (`profile_curr`),
  KEY `ki_ktype` (`ktype`),
  KEY `ki_bulk_id` (`bulk_id`),
  KEY `ki_status` (`status`),
  KEY `ki_date_create` (`date_create`),
  KEY `ki_date_expiry` (`date_expiry`),
  KEY `ki_date_activate` (`date_activate`),
  KEY `ki_date_remove` (`date_remove`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_account_info`
--

DROP TABLE IF EXISTS `kiwire_account_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_account_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `source` char(20) DEFAULT 'system',
  `username` char(120) DEFAULT NULL,
  `fullname` char(120) DEFAULT NULL,
  `email_address` char(64) DEFAULT NULL,
  `phone_number` char(20) DEFAULT NULL,
  `gender` char(10) DEFAULT NULL,
  `age_group` char(10) DEFAULT NULL,
  `location` char(60) DEFAULT NULL,
  `birthday` char(20) DEFAULT NULL,
  `interest` text DEFAULT NULL,
  `picture` char(255) DEFAULT NULL,
  `field1` char(120) DEFAULT NULL,
  `field2` char(120) DEFAULT NULL,
  `field3` char(120) DEFAULT NULL,
  `field4` char(120) DEFAULT NULL,
  `field5` char(120) DEFAULT NULL,
  `field6` char(120) DEFAULT NULL,
  `field7` char(120) DEFAULT NULL,
  `field8` char(120) DEFAULT NULL,
  `field9` char(120) DEFAULT NULL,
  `field10` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_username` (`username`),
  KEY `ki_fullname` (`fullname`),
  KEY `ki_source` (`source`),
  KEY `ki_gender` (`gender`),
  KEY `ki_age_group` (`age_group`),
  KEY `ki_location` (`location`),
  KEY `ki_birthday` (`birthday`),
  KEY `ki_field1` (`field1`),
  KEY `ki_field2` (`field2`),
  KEY `ki_field3` (`field3`),
  KEY `ki_field4` (`field4`),
  KEY `ki_field5` (`field5`),
  KEY `ki_field6` (`field6`),
  KEY `ki_field7` (`field7`),
  KEY `ki_field8` (`field8`),
  KEY `ki_field9` (`field9`),
  KEY `ki_field10` (`field10`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_account_policy`
--

DROP TABLE IF EXISTS `kiwire_account_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_account_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `tenant_id` char(120) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'n',
  `name` char(120) NOT NULL,
  `exec_action` char(60) NOT NULL,
  `action_value` char(120) DEFAULT NULL,
  `username` char(120) DEFAULT NULL,
  `frequency` char(30) NOT NULL,
  `policy_status` char(20) DEFAULT NULL,
  `policy_integration` char(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_active_session`
--

DROP TABLE IF EXISTS `kiwire_active_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_active_session` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_admin`
--

DROP TABLE IF EXISTS `kiwire_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `username` char(120) DEFAULT NULL,
  `password` char(120) DEFAULT NULL,
  `groupname` char(120) DEFAULT NULL,
  `fullname` char(120) DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `email` char(120) DEFAULT NULL,
  `theme` char(20) NOT NULL,
  `monitor` char(1) NOT NULL DEFAULT 'n',
  `temp_pass` tinyint(1) NOT NULL DEFAULT 1,
  `permission` char(2) NOT NULL DEFAULT 'rw',
  `balance_credit` decimal(12,2) DEFAULT 0.00,
  `last_change_pass` datetime DEFAULT current_timestamp(),
  `first_login` datetime DEFAULT current_timestamp(),
  `tenant_default` char(120) DEFAULT NULL,
  `tenant_allowed` mediumtext DEFAULT NULL,
  `mfactor_key` char(64) DEFAULT NULL,
  `require_mfactor` char(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_admin_group`
--

DROP TABLE IF EXISTS `kiwire_admin_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_admin_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` char(64) DEFAULT NULL,
  `moduleid` char(120) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`),
  KEY `moduleid` (`moduleid`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_allowed_zone`
--

DROP TABLE IF EXISTS `kiwire_allowed_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_allowed_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `zone` char(225) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_name` (`name`),
  KEY `ki_zone` (`zone`),
  KEY `ki_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_auto_reset`
--

DROP TABLE IF EXISTS `kiwire_auto_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_auto_reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `exec_when` char(64) DEFAULT NULL,
  `profile` char(120) DEFAULT NULL,
  `grace` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_exec_when` (`exec_when`),
  KEY `ki_profile` (`profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_bandwidth`
--

DROP TABLE IF EXISTS `kiwire_bandwidth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_bandwidth` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `applied_to` varchar(50) DEFAULT NULL,
  `at_user` varchar(50) DEFAULT NULL,
  `k_trigger` varchar(20) DEFAULT NULL,
  `at_zone` varchar(50) DEFAULT NULL,
  `priority` int(5) NOT NULL DEFAULT 0,
  `download_speed` varchar(10) NOT NULL DEFAULT '0',
  `upload_speed` varchar(10) NOT NULL DEFAULT '0',
  `updated_date` datetime DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_blacklist_domain`
--

DROP TABLE IF EXISTS `kiwire_blacklist_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_blacklist_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_domain` char(50) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kiwire_bl_mails_x` (`mail_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_campaign_ads`
--

DROP TABLE IF EXISTS `kiwire_campaign_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_campaign_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `fn_desktop` char(255) DEFAULT NULL,
  `fn_phone` char(255) NOT NULL DEFAULT 'NULL',
  `fn_tablet` char(255) NOT NULL DEFAULT 'NULL',
  `type` char(10) DEFAULT 'img',
  `link` char(255) DEFAULT NULL,
  `status` char(1) NOT NULL,
  `msg` char(255) NOT NULL DEFAULT 'NULL',
  `captcha_txt` char(250) NOT NULL DEFAULT 'NULL',
  `viewport` char(20) NOT NULL DEFAULT 'NULL',
  `remark` char(255) DEFAULT NULL,
  `verified_by` char(50) DEFAULT NULL,
  `verified_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` char(50) DEFAULT NULL,
  `json_url` text NOT NULL,
  `json_path` char(255) NOT NULL,
  `random` char(120) NOT NULL,
  `mapping` varchar(225) NOT NULL,
  `ads_max_no` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nasname` (`fn_desktop`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_campaign_apps`
--

DROP TABLE IF EXISTS `kiwire_campaign_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_campaign_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_title` varchar(100) DEFAULT NULL,
  `app_author` varchar(50) DEFAULT NULL,
  `app_price` varchar(10) DEFAULT NULL,
  `app_logopath` varchar(255) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `app_playstore_url` varchar(255) DEFAULT NULL,
  `app_appstore_url` varchar(255) DEFAULT NULL,
  `status` varchar(3) DEFAULT 'n',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_campaign_manager`
--

DROP TABLE IF EXISTS `kiwire_campaign_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_campaign_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `creator` char(120) NOT NULL,
  `name` char(120) NOT NULL,
  `status` char(10) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `c_order` int(11) NOT NULL,
  `remark` char(120) NOT NULL,
  `expired_click` int(11) NOT NULL,
  `expired_impress` int(11) NOT NULL,
  `current_click` int(11) NOT NULL,
  `current_impress` int(11) NOT NULL,
  `target` char(10) NOT NULL,
  `target_value` char(120) NOT NULL,
  `target_option` char(120) NOT NULL,
  `c_interval` char(10) NOT NULL,
  `c_interval_time_start` char(10) NOT NULL,
  `c_interval_time_stop` char(10) NOT NULL,
  `c_trigger` char(10) NOT NULL,
  `c_trigger_value` int(11) NOT NULL DEFAULT 0,
  `action` char(20) NOT NULL,
  `action_method` char(120) NOT NULL,
  `action_value` text NOT NULL,
  `c_space` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_name` (`name`),
  KEY `ki_status` (`status`),
  KEY `ki_date_start` (`date_start`),
  KEY `ki_date_end` (`date_end`),
  KEY `ki_c_trigger` (`c_trigger`),
  KEY `ki_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_clouds`
--

DROP TABLE IF EXISTS `kiwire_clouds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_clouds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` char(1) NOT NULL DEFAULT 'y',
  `name` char(120) DEFAULT NULL,
  `address` char(255) DEFAULT NULL,
  `phone` char(20) DEFAULT NULL,
  `industry` char(20) DEFAULT NULL,
  `website` char(120) DEFAULT NULL,
  `currency` char(3) DEFAULT 'MYR',
  `volume_metrics` char(2) NOT NULL DEFAULT 'Mb',
  `default_language` char(2) NOT NULL DEFAULT 'en',
  `gst_percentage` float(11,2) DEFAULT NULL,
  `ask_web_push` char(1) DEFAULT NULL,
  `nps_enabled` char(1) NOT NULL DEFAULT 'n',
  `nps_template` char(120) DEFAULT NULL,
  `forgot_password_method` char(10) DEFAULT NULL,
  `forgot_password_template` char(120) DEFAULT NULL,
  `insight_reporting` char(1) DEFAULT NULL,
  `check_arrangement_auto` text DEFAULT NULL,
  `check_arrangement_login` text DEFAULT NULL,
  `voucher_prefix` char(20) DEFAULT NULL,
  `voucher_engine` char(10) DEFAULT NULL,
  `voucher_template` char(120) DEFAULT NULL,
  `voucher_limit` int(11) DEFAULT NULL,
  `voucher_avoid_ambiguous` char(1) DEFAULT NULL,
  `campaign_wait_second` int(11) DEFAULT NULL,
  `campaign_multi_ads` char(1) DEFAULT NULL,
  `campaign_autoplay` char(1) DEFAULT NULL,
  `campaign_overlay` char(1) DEFAULT NULL,
  `campaign_cookies` char(1) DEFAULT NULL,
  `campaign_require_verification` char(1) NOT NULL DEFAULT 'n',
  `timezone` text DEFAULT NULL,
  `require_mfactor` char(1) NOT NULL DEFAULT 'n',
  `carry_forward_topup` char(1) DEFAULT NULL,
  `concurrent_user` int(11) NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_controller`
--

DROP TABLE IF EXISTS `kiwire_controller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_controller` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `unique_id` char(120) DEFAULT NULL,
  `device_ip` char(60) NOT NULL,
  `coa_port` int(11) DEFAULT 3799,
  `vendor` char(120) DEFAULT NULL,
  `device_type` char(120) NOT NULL,
  `shared_secret` char(120) DEFAULT NULL,
  `description` char(120) DEFAULT '',
  `community` char(20) DEFAULT NULL,
  `location` char(120) DEFAULT NULL,
  `monitor_method` char(20) NOT NULL,
  `username` char(120) DEFAULT NULL,
  `password` char(120) DEFAULT NULL,
  `seamless_type` char(20) DEFAULT NULL,
  `snmpv` char(5) DEFAULT NULL,
  `mib` char(120) DEFAULT NULL,
  `status` char(20) NOT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_device_ip` (`device_ip`),
  KEY `ki_vendor` (`vendor`),
  KEY `ki_device_type` (`device_type`),
  KEY `ki_last_update` (`last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_controller_map`
--

DROP TABLE IF EXISTS `kiwire_controller_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_controller_map` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `device_position` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_coupon_generator`
--

DROP TABLE IF EXISTS `kiwire_coupon_generator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_coupon_generator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime DEFAULT current_timestamp(),
  `title` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `price` varchar(100) NOT NULL,
  `additional_info` varchar(300) DEFAULT NULL,
  `date_expired` date NOT NULL,
  `code_method` char(120) NOT NULL,
  `code` varchar(50) NOT NULL,
  `img_name` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_cpanel_template`
--

DROP TABLE IF EXISTS `kiwire_cpanel_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_cpanel_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `enabled` char(1) DEFAULT 'n',
  `dashboard` char(1) DEFAULT 'n',
  `information` char(1) DEFAULT 'n',
  `profile` char(1) DEFAULT 'n',
  `statistics` char(1) DEFAULT 'n',
  `history` char(1) DEFAULT 'n',
  `recharge` char(1) DEFAULT 'n',
  `register` char(1) DEFAULT 'n',
  `login_type` char(20) DEFAULT 'account',
  `label_username` char(20) DEFAULT 'Username',
  `label_password` char(20) DEFAULT 'Password',
  `label_tenant` char(20) DEFAULT 'Tenant',
  `label_welcome` char(255) DEFAULT 'Welcome back, please login to your account.',
  `label_title` char(255) DEFAULT 'Login',
  `allow_inactive` char(1) DEFAULT 'y',
  `label_logout` char(255) DEFAULT 'You have been logout',
  `label_wrong_credential` char(255) DEFAULT 'Wrong credential provided',
  `history_month` int(11) DEFAULT 3,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_device_history`
--

DROP TABLE IF EXISTS `kiwire_device_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_device_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime DEFAULT current_timestamp(),
  `mac_address` char(25) DEFAULT NULL,
  `last_account` char(120) DEFAULT NULL,
  `last_auto` datetime DEFAULT NULL,
  `last_zone` char(64) DEFAULT NULL,
  `login_count` int(11) DEFAULT NULL,
  `details` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kiwire_device_time` (`updated_date`),
  KEY `mac_address` (`mac_address`),
  KEY `username` (`last_account`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_device_policy`
--

DROP TABLE IF EXISTS `kiwire_device_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_device_policy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `status` varchar(1) DEFAULT 'n',
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT NULL,
  `profile` varchar(50) DEFAULT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `value` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `status` (`status`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_profile` (`profile`),
  KEY `ki_zone` (`zone`),
  KEY `ki_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_device_register`
--

DROP TABLE IF EXISTS `kiwire_device_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_device_register` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `username` char(50) DEFAULT NULL,
  `mac_address` char(20) DEFAULT NULL,
  `dtype` char(10) DEFAULT NULL,
  `dbrand` char(10) DEFAULT NULL,
  `dmodel` char(10) DEFAULT NULL,
  `dos` char(10) DEFAULT NULL,
  `verified` varchar(1) DEFAULT 'n',
  `verified_by` char(50) DEFAULT NULL,
  `verified_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mac_address` (`mac_address`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_device_unique`
--

DROP TABLE IF EXISTS `kiwire_device_unique`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_device_unique` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `tenant_id` char(120) NOT NULL,
  `mac_address` char(120) DEFAULT NULL,
  `impress` text DEFAULT NULL,
  `click` text DEFAULT NULL,
  `profile` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_mac_address` (`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_facebook_reputation`
--

DROP TABLE IF EXISTS `kiwire_facebook_reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_facebook_reputation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageid` char(20) NOT NULL,
  `pagename` char(50) NOT NULL,
  `access_token` char(255) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `status` char(100) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_firewall`
--

DROP TABLE IF EXISTS `kiwire_firewall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_firewall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasid` char(20) NOT NULL,
  `dest` char(50) NOT NULL,
  `type` char(20) DEFAULT NULL,
  `remark` char(255) DEFAULT NULL,
  `username` char(15) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_nasid` (`nasid`),
  KEY `ki_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_group_mapping`
--

DROP TABLE IF EXISTS `kiwire_group_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_group_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `type` char(10) NOT NULL DEFAULT 'NULL',
  `group_name` char(120) DEFAULT NULL,
  `profile` char(120) DEFAULT NULL,
  `status` char(1) NOT NULL,
  `priority` int(11) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_type` (`type`),
  KEY `ki_group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_html_template`
--

DROP TABLE IF EXISTS `kiwire_html_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_html_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user` char(50) DEFAULT NULL,
  `type` char(10) DEFAULT NULL,
  `name` char(50) DEFAULT NULL,
  `subject` char(100) DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_speed` (`tenant_id`,`type`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_api_setting`
--

DROP TABLE IF EXISTS `kiwire_int_api_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_api_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `api_key` char(254) DEFAULT NULL,
  `enabled` char(1) NOT NULL,
  `permission` char(2) NOT NULL DEFAULT 'r',
  `module` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_api_key` (`api_key`),
  KEY `ki_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_email`
--

DROP TABLE IF EXISTS `kiwire_int_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `host` char(120) NOT NULL,
  `port` int(11) NOT NULL,
  `auth` char(5) NOT NULL,
  `user` char(120) NOT NULL,
  `password` char(120) NOT NULL,
  `from_name` char(120) NOT NULL,
  `from_email` char(120) NOT NULL,
  `cc_email` char(120) NOT NULL,
  `verification_page` char(225) NOT NULL,
  `confirm_page` char(120) NOT NULL,
  `email_template` char(120) NOT NULL,
  `profile` char(120) NOT NULL,
  `validity` int(11) NOT NULL,
  `allowed_domain` char(225) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_external_db`
--

DROP TABLE IF EXISTS `kiwire_int_external_db`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_external_db` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `host` char(120) DEFAULT NULL,
  `port` int(11) NOT NULL,
  `user` char(25) DEFAULT NULL,
  `pass` char(100) DEFAULT NULL,
  `dbname` char(50) DEFAULT NULL,
  `dbtype` char(50) DEFAULT NULL,
  `command` char(255) DEFAULT NULL,
  `variables` char(255) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `profile` char(50) DEFAULT NULL,
  `validity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_ldap`
--

DROP TABLE IF EXISTS `kiwire_int_ldap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_ldap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) DEFAULT NULL,
  `host` char(120) DEFAULT NULL,
  `port` char(5) NOT NULL DEFAULT 'NULL',
  `rdn` char(254) DEFAULT NULL,
  `profile` char(64) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_marketing_email`
--

DROP TABLE IF EXISTS `kiwire_int_marketing_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_marketing_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `profile` char(120) NOT NULL,
  `validity` int(11) NOT NULL,
  `madmini_email` char(250) DEFAULT NULL,
  `madmini_api` char(250) DEFAULT NULL,
  `madmini_list` char(250) DEFAULT NULL,
  `madmini_en` char(1) DEFAULT NULL,
  `mailchimp_api` char(200) DEFAULT NULL,
  `mailchimp_lid` char(100) DEFAULT NULL,
  `mailchimp_en` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_msad`
--

DROP TABLE IF EXISTS `kiwire_int_msad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_msad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` char(1) DEFAULT NULL,
  `host` char(255) DEFAULT NULL,
  `basedn` char(254) DEFAULT NULL,
  `accsuffix` char(254) DEFAULT NULL,
  `adminuser` char(254) DEFAULT NULL,
  `adminpw` char(254) DEFAULT NULL,
  `profile` char(120) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_payment_gateways`
--

DROP TABLE IF EXISTS `kiwire_int_payment_gateways`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_payment_gateways` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `allowed_profile` mediumtext NOT NULL,
  `validity` int(11) NOT NULL,
  `notification_send` char(1) NOT NULL,
  `notification_mode` char(20) NOT NULL,
  `page_success` char(20) NOT NULL,
  `page_failed` char(20) NOT NULL,
  `on_success` char(20) NOT NULL,
  `on_after_success` char(20) NOT NULL,
  `paymenttype` char(20) NOT NULL,
  `merchant_id` char(120) NOT NULL,
  `merchant_key` char(120) NOT NULL,
  `passphrase` char(120) NOT NULL,
  `reference` char(120) NOT NULL,
  `description` char(120) NOT NULL,
  `confirmation_email` char(120) NOT NULL,
  `security_sequence` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_pms`
--

DROP TABLE IF EXISTS `kiwire_int_pms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_pms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `enabled` char(1) NOT NULL,
  `pms_type` char(20) NOT NULL,
  `pms_host` char(120) DEFAULT NULL,
  `pms_port` int(11) DEFAULT NULL,
  `pms_project` char(120) DEFAULT NULL,
  `pms_token` char(120) DEFAULT NULL,
  `vip_match` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `pass_mode` int(11) NOT NULL,
  `pass_predefined` char(120) NOT NULL,
  `pass_percentage` int(11) NOT NULL DEFAULT 60,
  `zone_allowed` char(120) NOT NULL,
  `credential_string` char(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_pms_payment`
--

DROP TABLE IF EXISTS `kiwire_int_pms_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_pms_payment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `login_date` datetime NOT NULL,
  `post_date` datetime DEFAULT NULL,
  `room` char(60) NOT NULL,
  `status` char(20) NOT NULL,
  `amount` float NOT NULL,
  `profile` char(60) NOT NULL,
  `name` char(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_pms_payment_queue`
--

DROP TABLE IF EXISTS `kiwire_int_pms_payment_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_pms_payment_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `posting_date` datetime NOT NULL DEFAULT current_timestamp(),
  `roomno` char(120) DEFAULT NULL,
  `fullname` char(120) DEFAULT NULL,
  `profile` char(120) DEFAULT NULL,
  `charges` int(11) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  `remark` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`roomno`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_updated_date` (`updated_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_pms_transaction`
--

DROP TABLE IF EXISTS `kiwire_int_pms_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_pms_transaction` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime NOT NULL,
  `check_in_date` datetime NOT NULL,
  `check_out_date` datetime DEFAULT NULL,
  `tenant_id` char(120) NOT NULL,
  `room` char(10) NOT NULL,
  `first_name` char(60) DEFAULT NULL,
  `last_name` char(60) DEFAULT NULL,
  `vip_code` char(20) DEFAULT NULL,
  `status` char(20) NOT NULL DEFAULT 'check-in',
  `printed` char(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_pms_vipcode`
--

DROP TABLE IF EXISTS `kiwire_int_pms_vipcode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_pms_vipcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `tenant_id` char(120) DEFAULT NULL,
  `code` char(60) NOT NULL,
  `price` int(11) DEFAULT NULL,
  `profile` char(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_vipcode` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_radius`
--

DROP TABLE IF EXISTS `kiwire_int_radius`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_radius` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT '''NULL''',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `domain` char(120) NOT NULL,
  `port` int(11) DEFAULT NULL,
  `host` char(255) DEFAULT NULL,
  `secret` char(255) DEFAULT NULL,
  `nasid` char(255) DEFAULT NULL,
  `forward_profile` char(10) NOT NULL DEFAULT 'NULL',
  `profile` char(120) DEFAULT NULL,
  `keyword_str` char(120) DEFAULT NULL,
  `data_type` char(120) DEFAULT NULL,
  `validity` int(11) NOT NULL,
  `allowed_zone` varchar(50) DEFAULT NULL,
  `use_domain` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_sms`
--

DROP TABLE IF EXISTS `kiwire_int_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `allowed_zone` char(120) NOT NULL,
  `profile` char(120) NOT NULL DEFAULT 'NULL',
  `template_id` char(120) NOT NULL DEFAULT 'NULL',
  `validity` int(11) NOT NULL,
  `after_register` char(20) DEFAULT NULL,
  `mode` int(1) NOT NULL,
  `operator` char(120) NOT NULL DEFAULT 'NULL',
  `sms_text` char(8) NOT NULL DEFAULT 'none',
  `prefix_phoneno` char(1) NOT NULL,
  `template` char(20) DEFAULT NULL,
  `twilio_sid` char(120) DEFAULT NULL,
  `twilio_token` char(120) DEFAULT NULL,
  `twilio_no` char(120) DEFAULT NULL,
  `twilio_use_whatsapp` char(1) NOT NULL,
  `syn_account` char(120) DEFAULT NULL,
  `syn_key` char(120) DEFAULT NULL,
  `u_operator` char(120) DEFAULT NULL,
  `u_uri` longtext DEFAULT NULL,
  `u_phoneno` char(120) DEFAULT NULL,
  `u_message` char(120) DEFAULT NULL,
  `u_method` char(120) DEFAULT NULL,
  `u_header` char(120) DEFAULT NULL,
  `g_url` char(255) DEFAULT NULL,
  `g_clientid` char(60) DEFAULT NULL,
  `g_username` char(60) DEFAULT NULL,
  `g_key` char(255) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_sms_prefix`
--

DROP TABLE IF EXISTS `kiwire_int_sms_prefix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_sms_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` char(4) NOT NULL,
  `country` char(250) NOT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_prefix` (`prefix`),
  KEY `ki_country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_social`
--

DROP TABLE IF EXISTS `kiwire_int_social`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `profile` char(120) DEFAULT NULL,
  `validity` int(11) DEFAULT NULL,
  `socialgate` char(1) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `facebook` char(1) DEFAULT NULL,
  `facebook_page` char(250) DEFAULT NULL,
  `facebook_en` char(1) DEFAULT NULL,
  `twitter_en` char(1) DEFAULT NULL,
  `twitter` char(1) DEFAULT NULL,
  `linkedin_en` char(1) DEFAULT NULL,
  `instagram_en` char(1) DEFAULT NULL,
  `twitter_page` char(250) DEFAULT NULL,
  `wechat_en` char(1) DEFAULT NULL,
  `vk_en` char(1) DEFAULT NULL,
  `microsoft_en` char(1) DEFAULT NULL,
  `365_domain` char(100) DEFAULT NULL,
  `microsoft_profile` char(50) DEFAULT NULL,
  `microsoft_zone` char(50) DEFAULT NULL,
  `line_en` char(1) DEFAULT NULL,
  `kakao_en` char(1) DEFAULT NULL,
  `zalo_en` char(1) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_int_webhook`
--

DROP TABLE IF EXISTS `kiwire_int_webhook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_webhook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `status` char(1) NOT NULL,
  `zone` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `url` varchar(225) DEFAULT NULL,
  `header` text DEFAULT NULL,
  `when` varchar(20) NOT NULL,
  `method` char(120) DEFAULT NULL,
  `payload` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_zone` (`zone`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_invoice`
--

DROP TABLE IF EXISTS `kiwire_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_invoice` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reference` char(60) NOT NULL,
  `username` char(120) NOT NULL DEFAULT 'NULL',
  `profile` char(120) NOT NULL DEFAULT 'NULL',
  `balance` decimal(12,2) DEFAULT 0.00,
  `total_paid` decimal(12,2) DEFAULT 0.00,
  `content` mediumtext NOT NULL,
  `printed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `plan` (`profile`),
  KEY `inv_date` (`updated_date`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_login_journey`
--

DROP TABLE IF EXISTS `kiwire_login_journey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_login_journey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime DEFAULT current_timestamp(),
  `journey_name` char(120) NOT NULL DEFAULT 'NULL',
  `page_list` char(120) NOT NULL,
  `created_by` char(120) DEFAULT NULL,
  `created_when` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` char(1) DEFAULT 'N',
  `lang` char(3) NOT NULL DEFAULT 'en',
  `pre_login` char(20) NOT NULL,
  `pre_login_url` char(255) NOT NULL,
  `post_login` char(20) NOT NULL,
  `post_login_url` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `JOURNEY_INDEX` (`page_list`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_journey_name` (`journey_name`),
  KEY `ki_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_login_pages`
--

DROP TABLE IF EXISTS `kiwire_login_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_login_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `unique_id` char(10) NOT NULL,
  `page_name` char(120) DEFAULT NULL,
  `count_impress` char(1) DEFAULT 'y',
  `purpose` char(60) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `remark` char(120) DEFAULT NULL,
  `default_page` char(1) DEFAULT NULL,
  `bg_lg` char(255) DEFAULT NULL,
  `bg_md` char(255) DEFAULT NULL,
  `bg_sm` char(255) DEFAULT NULL,
  `bg_css` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rand_id` (`unique_id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_page_name` (`page_name`),
  KEY `ki_purpose` (`purpose`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_message`
--

DROP TABLE IF EXISTS `kiwire_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `tenant_id` char(120) NOT NULL,
  `sender` char(120) NOT NULL,
  `recipient` char(120) NOT NULL,
  `date_sent` datetime NOT NULL,
  `date_read` datetime NOT NULL,
  `title` char(120) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_sender` (`sender`),
  KEY `ki_recipient` (`recipient`),
  KEY `ki_read` (`date_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_mikrotik_bypass`
--

DROP TABLE IF EXISTS `kiwire_mikrotik_bypass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_mikrotik_bypass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `username` char(50) DEFAULT NULL,
  `mac` char(20) DEFAULT NULL,
  `activities` mediumtext DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `time_x` (`updated_date`),
  KEY `username` (`username`),
  KEY `mac` (`mac`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_moduleid`
--

DROP TABLE IF EXISTS `kiwire_moduleid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_moduleid` (
  `moduleid` char(120) DEFAULT NULL,
  `mod_group` varchar(20) DEFAULT 'general',
  `updated_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_nms_log`
--

DROP TABLE IF EXISTS `kiwire_nms_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_nms_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `unique_id` char(50) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed` int(11) DEFAULT 0,
  `status` char(50) DEFAULT NULL,
  `reason` char(225) NOT NULL,
  `ping` int(11) NOT NULL,
  `system_name` char(120) NOT NULL,
  `uptime` int(11) NOT NULL,
  `cpu_load` int(11) NOT NULL,
  `memory_total` int(11) NOT NULL,
  `memory_used` int(11) NOT NULL,
  `disk_total` int(11) NOT NULL,
  `disk_used` int(11) NOT NULL,
  `input_vol` bigint(20) NOT NULL,
  `output_vol` bigint(20) NOT NULL,
  `if_total` int(11) NOT NULL,
  `if_status` char(255) NOT NULL,
  `if_desc` char(255) NOT NULL,
  `dev_loc` char(120) NOT NULL,
  `device_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `nms_log` (`tenant_id`,`unique_id`,`status`),
  KEY `ki_unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_nms_mib`
--

DROP TABLE IF EXISTS `kiwire_nms_mib`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_nms_mib` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `mib_name` char(100) DEFAULT NULL,
  `description` char(255) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `system_name` char(50) DEFAULT NULL,
  `uptime` char(50) NOT NULL DEFAULT 'NULL',
  `cpu_load` char(50) DEFAULT NULL,
  `memory_total` char(50) NOT NULL DEFAULT 'NULL',
  `memory_used` char(50) NOT NULL DEFAULT 'NULL',
  `disk_total` char(50) DEFAULT NULL,
  `disk_used` char(50) DEFAULT NULL,
  `input_vol` char(50) DEFAULT NULL,
  `output_vol` char(50) DEFAULT NULL,
  `if_total` char(50) DEFAULT NULL,
  `if_status` char(50) DEFAULT NULL,
  `if_speed` char(255) NOT NULL,
  `if_desc` char(50) DEFAULT NULL,
  `dev_loc` char(50) DEFAULT NULL,
  `device_count` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_mib_name` (`mib_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_nms_rules`
--

DROP TABLE IF EXISTS `kiwire_nms_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_nms_rules` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` char(200) DEFAULT NULL,
  `description` char(255) NOT NULL DEFAULT 'NULL',
  `mib` char(100) DEFAULT NULL,
  `warning_cpu` int(11) DEFAULT NULL,
  `critical_cpu` int(11) DEFAULT NULL,
  `warning_disk` int(11) DEFAULT NULL,
  `critical_disk` int(11) DEFAULT NULL,
  `warning_memory` int(11) DEFAULT NULL,
  `critical_memory` int(11) DEFAULT NULL,
  `ignore_warning` char(1) NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_name` (`name`),
  KEY `ki_mib` (`mib`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_notification`
--

DROP TABLE IF EXISTS `kiwire_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `tenant_id` char(120) DEFAULT NULL,
  `notification_account_created` char(128) DEFAULT NULL,
  `notification_password_reset` char(128) DEFAULT NULL,
  `error_no_credential` char(128) DEFAULT NULL,
  `error_password_verification_failed` char(128) DEFAULT NULL,
  `error_wrong_otp` char(128) DEFAULT NULL,
  `error_username_existed` char(128) DEFAULT NULL,
  `error_future_value_date` char(128) DEFAULT NULL,
  `error_account_inactive` char(128) DEFAULT NULL,
  `error_wrong_credential` char(128) DEFAULT NULL,
  `error_reached_quota_limit` char(128) DEFAULT NULL,
  `error_reached_time_limit` char(128) DEFAULT NULL,
  `error_max_simultaneous_use` char(128) DEFAULT NULL,
  `error_zone_restriction` char(128) DEFAULT NULL,
  `error_wrong_mac_address` char(128) DEFAULT NULL,
  `error_zone_reached_limit` char(128) DEFAULT NULL,
  `error_invalid_email_address` char(128) DEFAULT NULL,
  `error_invalid_phone_number` char(128) DEFAULT NULL,
  `error_no_profile_subscribe` char(128) DEFAULT NULL,
  `error_wrong_captcha` char(128) DEFAULT NULL,
  `error_country_code` char(128) DEFAULT NULL,
  `error_device_blacklisted` char(128) DEFAULT NULL,
  `error_password_expired` char(128) DEFAULT NULL,
  `error_password_contained_num` char(128) DEFAULT NULL,
  `error_password_contained_alp` char(128) DEFAULT NULL,
  `error_password_contained_sym` char(128) DEFAULT NULL,
  `error_password_length` char(128) DEFAULT NULL,
  `error_password_not_same` char(128) DEFAULT NULL,
  `error_password_max_attemp` char(128) DEFAULT NULL,
  `error_pass_username_matched` char(120) DEFAULT NULL,
  `error_password_reused` char(120) DEFAULT NULL,
  `error_user_email_mismatched` char(120) DEFAULT NULL,
  `error_user_sms_mismatched` char(120) DEFAULT NULL,
  `error_user_not_found` char(120) DEFAULT NULL,
  `error_username_cannot_space` char(120) DEFAULT NULL,
  `error_missing_sponsor_email` char(120) DEFAULT NULL,
  `error_missing_credential_check` char(120) DEFAULT NULL,
  `error_empty_password` char(120) DEFAULT NULL,
  `notification_password_changed` char(120) DEFAULT NULL,
  `error_inactive_account` char(120) DEFAULT NULL,
  `error_ot_reset_grace` char(120) DEFAULT NULL,
  `error_password_need_to_change` char(120) DEFAULT NULL,
  `error_password_change_day` char(120) DEFAULT NULL,
  `error_password_too_much_retries` char(120) DEFAULT NULL,
  `error_max_concurrent_user` char(120) DEFAULT "You have reached max concurrent user limit for this tenant.",
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_nps_score`
--

DROP TABLE IF EXISTS `kiwire_nps_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_nps_score` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `score` int(3) DEFAULT NULL,
  `score_type` varchar(15) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'waiting',
  `updated_date` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT NULL,
  `magnitude` decimal(2,1) DEFAULT NULL,
  `sentiment_c` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sentiment_c` (`sentiment_c`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_username` (`username`),
  KEY `ki_score_type` (`score_type`),
  KEY `ki_magnitude` (`magnitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_omaya`
--

DROP TABLE IF EXISTS `kiwire_omaya`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_omaya` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `status` varchar(1) DEFAULT 'n',
  `api_id` varchar(100) DEFAULT NULL,
  `api_secret` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `user` varchar(50) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_one_click_login`
--

DROP TABLE IF EXISTS `kiwire_one_click_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_one_click_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `enabled` char(1) NOT NULL,
  `allowed_zone` char(120) NOT NULL,
  `profile` char(120) NOT NULL,
  `validity` int(11) NOT NULL,
  `login_using_id` char(20) NOT NULL,
  `username` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_payment_trx`
--

DROP TABLE IF EXISTS `kiwire_payment_trx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_payment_trx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `ref_no` varchar(120) NOT NULL,
  `username` varchar(120) NOT NULL,
  `payment_type` varchar(120) NOT NULL,
  `amount` float NOT NULL,
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_persona`
--

DROP TABLE IF EXISTS `kiwire_persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_persona` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `rule` mediumtext DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_policies`
--

DROP TABLE IF EXISTS `kiwire_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT 'NULL',
  `updated_date` datetime DEFAULT current_timestamp(),
  `mac_auto_register` char(1) DEFAULT NULL,
  `mac_max_register` int(11) NOT NULL,
  `mac_auto_same_zone` char(1) DEFAULT 'n',
  `mac_security` char(1) DEFAULT NULL,
  `kick_on_simultaneous` char(1) DEFAULT NULL,
  `kick_on_simultaneous_idle` char(1) DEFAULT NULL,
  `suspend_exhausted_account` char(1) DEFAULT NULL,
  `remember_me` char(1) DEFAULT NULL,
  `cookies_login` char(1) DEFAULT NULL,
  `cookies_login_validity` int(11) DEFAULT NULL,
  `captcha` char(1) DEFAULT NULL,
  `delete_unverified` char(1) DEFAULT NULL,
  `two-factors` char(1) DEFAULT 'n',
  `password_days` char(1) DEFAULT NULL,
  `password_reused` char(1) DEFAULT NULL,
  `password_attempts` char(1) DEFAULT NULL,
  `password_first_login` char(1) DEFAULT NULL,
  `password_same` char(1) DEFAULT NULL,
  `password_policy` char(1) DEFAULT NULL,
  `password_character` char(1) DEFAULT NULL,
  `password_alphabet` char(1) DEFAULT NULL,
  `password_numeral` char(1) DEFAULT NULL,
  `password_change` char(1) DEFAULT NULL,
  `password_symbol` char(1) DEFAULT NULL,
  `change_passpage` char(10) DEFAULT NULL,
  `auto_login` char(1) DEFAULT 'n',
  `mac_auto_login` char(1) DEFAULT NULL,
  `mac_auto_login_days` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_profiles`
--

DROP TABLE IF EXISTS `kiwire_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `price` int(11) DEFAULT NULL,
  `type` char(20) DEFAULT NULL,
  `advance` char(120) DEFAULT NULL,
  `grace` char(20) DEFAULT '0',
  `a_limit` int(11) DEFAULT NULL,
  `attribute` text DEFAULT NULL,
  `attribute_custom` char(255) DEFAULT NULL,
  `remark` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`) USING BTREE,
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_project`
--

DROP TABLE IF EXISTS `kiwire_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `name` char(120) DEFAULT NULL,
  `zone_list` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_push_subscription`
--

DROP TABLE IF EXISTS `kiwire_push_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_push_subscription` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `mac_address` char(64) DEFAULT NULL,
  `push_key` text DEFAULT NULL,
  `push_hash` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE,
  KEY `ki_mac` (`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_qr`
--

DROP TABLE IF EXISTS `kiwire_qr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_qr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `enabled` char(1) DEFAULT NULL,
  `profile` char(254) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `validity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_queue_int_pms_guest`
--

DROP TABLE IF EXISTS `kiwire_queue_int_pms_guest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_queue_int_pms_guest` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `status` enum('1','2','3','4') DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `firstname` varchar(200) DEFAULT NULL,
  `lastname` varchar(200) DEFAULT NULL,
  `vipcode` varchar(10) DEFAULT NULL,
  `updatestatus` enum('0','1') DEFAULT NULL,
  `print` char(1) DEFAULT NULL,
  `tenant_id` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`username`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_campaign_general`
--

DROP TABLE IF EXISTS `kiwire_report_campaign_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_campaign_general` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `zone` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `source` char(10) NOT NULL,
  `impress` int(11) DEFAULT 0,
  `click` int(11) DEFAULT 0,
  `u_impress` int(11) DEFAULT 0,
  `u_click` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_report_date` (`report_date`),
  KEY `ki_zone` (`zone`),
  KEY `ki_name` (`name`),
  KEY `ki_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_campaign_offline`
--

DROP TABLE IF EXISTS `kiwire_report_campaign_offline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_campaign_offline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `zone` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `name` char(120) NOT NULL,
  `source` char(10) NOT NULL,
  `execute` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_report_date` (`report_date`),
  KEY `ki_zone` (`zone`),
  KEY `ki_name` (`name`),
  KEY `ki_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_controller`
--

DROP TABLE IF EXISTS `kiwire_report_controller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_controller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `report_date` datetime NOT NULL,
  `total` int(11) DEFAULT 0,
  `running` int(11) DEFAULT 0,
  `incident_count` int(11) DEFAULT 0,
  `issue` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_report_date` (`report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_controller_statistics`
--

DROP TABLE IF EXISTS `kiwire_report_controller_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_controller_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `report_date` datetime NOT NULL,
  `source` char(120) NOT NULL,
  `unique_id` char(20) NOT NULL,
  `quota_upload` bigint(20) NOT NULL,
  `quota_download` bigint(20) NOT NULL,
  `avg_upload_speed` float DEFAULT 0,
  `avg_download_speed` float DEFAULT 0,
  `avg_speed` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_report_date` (`report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_login_device`
--

DROP TABLE IF EXISTS `kiwire_report_login_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_login_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `zone` char(120) NOT NULL,
  `info` char(120) NOT NULL,
  `value` char(120) NOT NULL,
  `count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_login_dwell`
--

DROP TABLE IF EXISTS `kiwire_report_login_dwell`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_login_dwell` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `tenant_id` char(120) NOT NULL,
  `zone` char(120) DEFAULT NULL,
  `type` char(60) NOT NULL,
  `count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_report_date` (`report_date`),
  KEY `ki_zone` (`zone`),
  KEY `ki_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_login_error`
--

DROP TABLE IF EXISTS `kiwire_report_login_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_login_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `error_hash` char(120) NOT NULL,
  `error_message` char(255) NOT NULL,
  `count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_login_general`
--

DROP TABLE IF EXISTS `kiwire_report_login_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_login_general` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `zone` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `attemp` int(11) DEFAULT 0,
  `succeed` int(11) DEFAULT 0,
  `failed` int(11) DEFAULT 0,
  `quota` int(11) DEFAULT 0,
  `time` int(11) DEFAULT 0,
  `disconnect` int(11) DEFAULT 0,
  `sms` int(11) DEFAULT 0,
  `email` int(11) DEFAULT 0,
  `account_create` int(11) DEFAULT 0,
  `integration` int(11) DEFAULT 0,
  `account_return` int(11) DEFAULT 0,
  `account_new` int(11) DEFAULT 0,
  `account_unique` int(11) DEFAULT 0,
  `device_unique` int(11) DEFAULT 0,
  `device_new` int(11) DEFAULT 0,
  `device_return` int(11) DEFAULT 0,
  `dwell` int(11) DEFAULT 0,
  `impression` int(11) DEFAULT 0,
  `connected` int(11) DEFAULT 0,
  `quota_in` bigint(20) DEFAULT 0,
  `quota_out` bigint(20) DEFAULT 0,
  `ulogin` int(11) DEFAULT 0,
  `concurrent` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_report_date` (`report_date`),
  KEY `ki_zone` (`zone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_report_login_profile`
--

DROP TABLE IF EXISTS `kiwire_report_login_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_report_login_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` datetime NOT NULL,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `zone` char(120) NOT NULL,
  `profile` char(120) NOT NULL,
  `dwell` int(11) DEFAULT 0,
  `login` int(11) DEFAULT 0,
  `u_login` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_zone` (`zone`),
  KEY `ki_profile` (`profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_reputation_data`
--

DROP TABLE IF EXISTS `kiwire_reputation_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_reputation_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewer_name` char(120) NOT NULL,
  `reviewer_id` char(20) NOT NULL,
  `review_text` mediumtext DEFAULT NULL,
  `page_name` char(120) DEFAULT NULL,
  `score` decimal(2,1) DEFAULT NULL,
  `recommendation` char(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_session_template`
--

DROP TABLE IF EXISTS `kiwire_session_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_session_template` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202004`
--

DROP TABLE IF EXISTS `kiwire_sessions_202004`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202004` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202005`
--

DROP TABLE IF EXISTS `kiwire_sessions_202005`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202005` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202006`
--

DROP TABLE IF EXISTS `kiwire_sessions_202006`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202006` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202007`
--

DROP TABLE IF EXISTS `kiwire_sessions_202007`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202007` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202008`
--

DROP TABLE IF EXISTS `kiwire_sessions_202008`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202008` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202009`
--

DROP TABLE IF EXISTS `kiwire_sessions_202009`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202009` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202010`
--

DROP TABLE IF EXISTS `kiwire_sessions_202010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202010` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202011`
--

DROP TABLE IF EXISTS `kiwire_sessions_202011`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202011` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202012`
--

DROP TABLE IF EXISTS `kiwire_sessions_202012`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202012` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202101`
--

DROP TABLE IF EXISTS `kiwire_sessions_202101`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202101` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sessions_202102`
--

DROP TABLE IF EXISTS `kiwire_sessions_202102`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sessions_202102` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` char(120) NOT NULL,
  `unique_id` char(120) NOT NULL,
  `controller` char(120) NOT NULL,
  `controller_ip` char(20) NOT NULL,
  `zone` char(120) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `ip_address` char(20) NOT NULL,
  `ipv6_address` char(60) DEFAULT NULL,
  `profile` char(120) NOT NULL,
  `session_table` char(25) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `session_time` int(11) NOT NULL,
  `quota_in` bigint(20) NOT NULL,
  `quota_out` bigint(20) NOT NULL,
  `terminate_reason` char(20) DEFAULT NULL,
  `avg_speed` int(11) NOT NULL,
  `system` char(20) NOT NULL,
  `class` char(20) NOT NULL,
  `brand` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `hostname` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_updated_date` (`updated_date`),
  KEY `ki_session_id` (`session_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_controller` (`controller`),
  KEY `ki_zone` (`zone`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`),
  KEY `ki_start_time` (`start_time`),
  KEY `ki_stop_time` (`stop_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_signup_public`
--

DROP TABLE IF EXISTS `kiwire_signup_public`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_signup_public` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL DEFAULT 'NULL',
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) NOT NULL,
  `profile` char(120) DEFAULT NULL,
  `validity` char(10) DEFAULT NULL,
  `after_register` varchar(30) DEFAULT 'internet',
  `allowed_zone` char(120) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  `public_remark` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_signup_visitor`
--

DROP TABLE IF EXISTS `kiwire_signup_visitor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_signup_visitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime DEFAULT current_timestamp(),
  `tenant_id` char(120) DEFAULT NULL,
  `verification_content` char(120) DEFAULT NULL,
  `confirmation_content` char(120) DEFAULT NULL,
  `send_notification` char(10) DEFAULT NULL,
  `confirmed_page` char(8) DEFAULT NULL,
  `domain` char(254) DEFAULT NULL,
  `profile` char(120) DEFAULT NULL,
  `enabled` char(1) NOT NULL DEFAULT 'n',
  `validity` char(10) DEFAULT NULL,
  `prefix` char(10) DEFAULT NULL,
  `allowed_zone` char(120) DEFAULT NULL,
  `guest_remark` char(255) DEFAULT NULL,
  `data` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_sso`
--

DROP TABLE IF EXISTS `kiwire_sso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_sso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `enabled` char(1) DEFAULT NULL,
  `sso_server` text DEFAULT NULL,
  `sso_port` char(50) DEFAULT NULL,
  `sso_info` mediumtext DEFAULT NULL,
  `sso_secret` char(20) DEFAULT NULL,
  `sso_simul` int(11) DEFAULT NULL,
  `sso_timeout` int(11) DEFAULT NULL,
  `sso_retry` int(11) DEFAULT NULL,
  `acctsessionid` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  `nasporttype` varchar(32) DEFAULT NULL,
  `acctsessiontime` int(12) DEFAULT NULL,
  `acctoutputoctets` bigint(20) DEFAULT NULL,
  `acctinputoctets` bigint(20) DEFAULT NULL,
  `calledstationid` varchar(50) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `acctterminatecause` varchar(30) NOT NULL DEFAULT '',
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_survey_list`
--

DROP TABLE IF EXISTS `kiwire_survey_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_survey_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `unique_id` char(8) NOT NULL,
  `name` char(120) NOT NULL,
  `description` char(255) NOT NULL,
  `status` char(1) NOT NULL,
  `questions` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_name` (`name`),
  KEY `ki_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_survey_respond`
--

DROP TABLE IF EXISTS `kiwire_survey_respond`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_survey_respond` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `unique_id` char(8) NOT NULL,
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  `answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_unique_id` (`unique_id`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_temporary_access`
--

DROP TABLE IF EXISTS `kiwire_temporary_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_temporary_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `username` char(120) NOT NULL,
  `mac_address` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_username` (`username`),
  KEY `ki_mac_address` (`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_topup_code`
--

DROP TABLE IF EXISTS `kiwire_topup_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_topup_code` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
  `tenant_id` char(120) NOT NULL,
  `code` char(20) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'n',
  `username` char(120) DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp(),
  `date_activate` datetime DEFAULT NULL,
  `quota` bigint(20) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `quota_in` bigint(20) DEFAULT NULL,
  `quota_out` bigint(20) DEFAULT NULL,
  `session_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_total_counter`
--

DROP TABLE IF EXISTS `kiwire_total_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_total_counter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `data` char(60) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `total_counter` (`data`,`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_zapier_data`
--

DROP TABLE IF EXISTS `kiwire_zapier_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_zapier_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `enabled` char(1) NOT NULL DEFAULT 'n',
  `api_key` mediumtext DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `api_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_zone`
--

DROP TABLE IF EXISTS `kiwire_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_zone` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(60) DEFAULT NULL,
  `status` char(1) DEFAULT 'n',
  `tenant_id` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `created_by` char(60) DEFAULT NULL,
  `auto_login` char(120) DEFAULT NULL,
  `simultaneous` int(11) DEFAULT 10000,
  `journey` char(120) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `force_profile` char(120) DEFAULT NULL,
  `force_allowed_zone` char(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `status` (`status`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kiwire_zone_child`
--

DROP TABLE IF EXISTS `kiwire_zone_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_zone_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` char(120) NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `master_id` char(60) NOT NULL,
  `nasid` char(120) DEFAULT NULL,
  `ipaddr` char(120) DEFAULT NULL,
  `ipv6addr` char(120) DEFAULT NULL,
  `vlan` char(120) DEFAULT NULL,
  `ssid` char(120) DEFAULT NULL,
  `dzone` char(120) DEFAULT NULL,
  `priority` int(11) NOT NULL,
  `hash` char(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ki_tenant` (`tenant_id`),
  KEY `ki_master_id` (`master_id`),
  KEY `ki_hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-02-25  9:36:03

-- Add column in table for module policy > configuration  
ALTER TABLE kiwire_policies ADD COLUMN allow_carry_forward char(1) DEFAULT 'n' AFTER delete_unverified;



--
-- Table structure for table `kiwire_int_hss`
--

DROP TABLE IF EXISTS `kiwire_int_hss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_int_hss` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `status` char(1) DEFAULT 'n',
  `tenant_id` char(120) DEFAULT NULL,
  `username` char(50) DEFAULT NULL,
  `password` char(120) DEFAULT NULL,
  `hss_server_url` char(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  `last_test` datetime DEFAULT NULL,
  `last_test_status` char(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `kiwire_int_hss`
--

DROP TABLE IF EXISTS `kiwire_account_hss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiwire_account_hss` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_sync` char(1) DEFAULT 'n',
  `tenant_id` char(120) DEFAULT NULL,
  `username` char(120) DEFAULT NULL COMMENT 'username (unique ID for each SIM card)',
  `imsi` char(120) DEFAULT NULL COMMENT 'IMSI (unique ID for each SIM card)',
  `hlrsn` char(120) DEFAULT NULL,
  `private_key` char(120) DEFAULT NULL COMMENT 'KI(unique private key for each SIM card)',
  `card_type` char(120) DEFAULT NULL,
  `alg` char(120) DEFAULT NULL,
  `opsno` char(120) DEFAULT NULL,
  `key_type` char(120) DEFAULT NULL,
  `isdn` char(120) DEFAULT NULL COMMENT 'ISDN(kinds of link mobile number)',
  `tpltype` char(120) DEFAULT NULL,
  `tplid` char(120) DEFAULT NULL,
  `updated_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `kiwire_topup_code` ADD `price` float(11,2) NOT NULL AFTER `status`;


ALTER TABLE `kiwire_clouds` ADD `allow_topup_to` varchar(100) NULL AFTER `carry_forward_topup`;

INSERT INTO `kiwire_moduleid` (`moduleid`, `mod_group`, `updated_date`) VALUES ('Account -> Tejas', 'Account', 'current_timestamp()');

CREATE TABLE `kiwire_account_sync_info` (
  `id` bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `is_sync` char(1) DEFAULT 'n',
  `tenant_id` char(120)DEFAULT NULL,
  `username` char(120)DEFAULT NULL COMMENT 'username (unique ID for each SIM card)',
  `imsi` char(120)DEFAULT NULL COMMENT 'IMSI (unique ID for each SIM card)',
  `isdn` char(120)DEFAULT NULL ,
  `profile` char(120)DEFAULT NULL ,
  `odb` char(120)DEFAULT NULL ,
  `status` char(120)DEFAULT NULL ,
  `eps_service` char(120)DEFAULT NULL ,
  `algo` char(120)DEFAULT NULL ,
  `trace_depth` char(120)DEFAULT NULL ,
  `subs_name` char(120)DEFAULT NULL ,
  `apn_id` char(120)DEFAULT NULL,
  `max_ul` char(120)DEFAULT NULL ,
  `max_dl` char(120)DEFAULT NULL ,
  `ip` char(120)DEFAULT NULL ,
  `ipv6` char(120)DEFAULT NULL ,
  `pdn_type` char(120)DEFAULT NULL,
  `pgw_address` char(120)DEFAULT NULL,
  `pgw_addressV6` char(120)DEFAULT NULL,
  `pgw_identity_host` char(120)DEFAULT NULL,
  `pgw_identity_realm` char(120)DEFAULT NULL,
  `op` char(120)DEFAULT NULL COMMENT 'encrypted format of security key  op',
  `amf` char(120)DEFAULT NULL COMMENT 'encrypted format of security key  amf',
  `k` char(120)DEFAULT NULL COMMENT 'encrypted format of security key  k',
  `dflt_apn_id` char(120)DEFAULT NULL,
  `ntw_access` char(120)DEFAULT NULL,
  `hss_plmn` char(120)DEFAULT NULL COMMENT 'mcc,mnc',
  `profile_max_ul` char(120)DEFAULT NULL,
  `profile_max_dl` char(120)DEFAULT NULL,
  `profile_name` char(120)DEFAULT NULL,
  `zonal_code` char(120)DEFAULT NULL,
  `apn_oi_rplcmnt` char(120)DEFAULT NULL,
  `subs_periodic_rau_tau_timer` char(120)DEFAULT NULL
);

ALTER TABLE `kiwire_clouds` ADD `ip_address` char(200) COLLATE 'utf8mb4_general_ci' NULL AFTER `status`;
ALTER TABLE `kiwire_clouds`ADD `custom_style` char(1) COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT 'n' AFTER `volume_metrics`;
INSERT INTO `kiwire_moduleid` (`moduleid`, `mod_group`, `updated_date`) VALUES ('Cloud -> Custom Style', 'cloud', 'current_timestamp()');


ALTER TABLE `kiwire_clouds` ADD `topup_prefix` char(20) COLLATE 'utf8mb4_general_ci' NULL;

ALTER TABLE `kiwire_topup_code`
ADD `creator` char(64) COLLATE 'utf8mb4_general_ci' NULL AFTER `tenant_id`,
ADD `plan_name` char(120) COLLATE 'utf8mb4_general_ci' NULL AFTER `username`,
ADD `date_expiry` datetime NULL AFTER `date_activate`,
ADD `bulk_id` char(10) AFTER `date_expiry`,
ADD `remark` char(120) NULL;

INSERT INTO `kiwire_moduleid` (`moduleid`, `mod_group`, `updated_date`)
VALUES ('Report -> Accounts -> Topup Availibility', 'Report', now());

ALTER TABLE `kiwire_account_info`
ADD `identity_no` char(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `picture`,
ADD `identity_no_hash6` char(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `identity_no`;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration_file` varchar(255) NOT NULL,
  `status` char(1) NOT NULL,
  `updated_date` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE `kiwire_campaign_manager`
CHANGE `status` `status` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `name`,
CHANGE `date_start` `date_start` datetime NULL AFTER `status`,
CHANGE `date_end` `date_end` datetime NULL AFTER `date_start`,
CHANGE `c_order` `c_order` int(11) NULL AFTER `date_end`,
CHANGE `remark` `remark` char(120) COLLATE 'utf8mb4_general_ci' NULL AFTER `c_order`,
CHANGE `expired_click` `expired_click` int(11) NULL AFTER `remark`,
CHANGE `expired_impress` `expired_impress` int(11) NULL AFTER `expired_click`,
CHANGE `current_click` `current_click` int(11) NULL AFTER `expired_impress`,
CHANGE `current_impress` `current_impress` int(11) NULL AFTER `current_click`,
CHANGE `target` `target` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `current_impress`,
CHANGE `target_value` `target_value` char(120) COLLATE 'utf8mb4_general_ci' NULL AFTER `target`,
CHANGE `target_option` `target_option` char(120) COLLATE 'utf8mb4_general_ci' NULL AFTER `target_value`,
CHANGE `c_interval` `c_interval` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `target_option`,
CHANGE `c_interval_time_start` `c_interval_time_start` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `c_interval`,
CHANGE `c_interval_time_stop` `c_interval_time_stop` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `c_interval_time_start`,
CHANGE `c_trigger` `c_trigger` char(10) COLLATE 'utf8mb4_general_ci' NULL AFTER `c_interval_time_stop`,
CHANGE `c_trigger_value` `c_trigger_value` int(11) NULL DEFAULT '0' AFTER `c_trigger`,
CHANGE `action` `action` char(20) COLLATE 'utf8mb4_general_ci' NULL AFTER `c_trigger_value`,
CHANGE `action_method` `action_method` char(120) COLLATE 'utf8mb4_general_ci' NULL AFTER `action`,
CHANGE `action_value` `action_value` text COLLATE 'utf8mb4_general_ci' NULL AFTER `action_method`,
CHANGE `c_space` `c_space` char(20) COLLATE 'utf8mb4_general_ci' NULL AFTER `action_value`;

ALTER TABLE `kiwire_clouds` ADD `date_format` text  NULL AFTER `concurrent_user`;

ALTER TABLE `kiwire_clouds` CHANGE `concurrent_user` `concurrent_user` int(11) NOT NULL DEFAULT '500' AFTER `allow_topup_to`;

ALTER TABLE `kiwire_clouds` ADD `send_email` char(1) DEFAULT 'n'  NULL AFTER `topup_prefix`;

ALTER TABLE `kiwire_policies` ADD `security_block`  char(1) COLLATE 'utf8mb4_general_ci' NULL AFTER `allow_carry_forward`;


INSERT INTO `kiwire_moduleid` (`moduleid`, `mod_group`, `updated_date`)
VALUES ('Help -> Version', 'Help', 'current_timestamp()');