/*
SQLyog Community v13.1.7 (64 bit)
MySQL - 10.4.24-MariaDB : Database - db_price_update
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_price_update` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `db_price_update`;

/*Table structure for table `audit_logs` */

DROP TABLE IF EXISTS `audit_logs`;

CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint(20) unsigned DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_al_user` (`user_id`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `brands` */

DROP TABLE IF EXISTS `brands`;

CREATE TABLE `brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_name` (`brand_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `competitor_prices` */

DROP TABLE IF EXISTS `competitor_prices`;

CREATE TABLE `competitor_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `competitor_id` int(10) unsigned NOT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `captured_date` date NOT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_competitor_price` (`product_id`,`competitor_id`,`captured_date`),
  KEY `fk_cp_competitor` (`competitor_id`),
  CONSTRAINT `fk_cp_competitor` FOREIGN KEY (`competitor_id`) REFERENCES `competitors` (`id`),
  CONSTRAINT `fk_cp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `competitors` */

DROP TABLE IF EXISTS `competitors`;

CREATE TABLE `competitors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competitor_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `competitor_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competitor_code` (`competitor_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `email_logs` */

DROP TABLE IF EXISTS `email_logs`;

CREATE TABLE `email_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `price_change_batch_id` bigint(20) unsigned NOT NULL,
  `recipient_user_id` int(10) unsigned DEFAULT NULL,
  `recipient_email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('queued','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `error_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_el_batch` (`price_change_batch_id`),
  KEY `fk_el_user` (`recipient_user_id`),
  CONSTRAINT `fk_el_batch` FOREIGN KEY (`price_change_batch_id`) REFERENCES `price_change_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_el_user` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `email_templates` */

DROP TABLE IF EXISTS `email_templates`;

CREATE TABLE `email_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_template` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification_group_channels` */

DROP TABLE IF EXISTS `notification_group_channels`;

CREATE TABLE `notification_group_channels` (
  `group_id` int(10) unsigned NOT NULL,
  `channel_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`channel_id`),
  KEY `fk_ngc_channel` (`channel_id`),
  CONSTRAINT `fk_ngc_channel` FOREIGN KEY (`channel_id`) REFERENCES `price_channels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ngc_group` FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification_group_members` */

DROP TABLE IF EXISTS `notification_group_members`;

CREATE TABLE `notification_group_members` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `fk_ngm_user` (`user_id`),
  CONSTRAINT `fk_ngm_group` FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ngm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification_groups` */

DROP TABLE IF EXISTS `notification_groups`;

CREATE TABLE `notification_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `price_change_batches` */

DROP TABLE IF EXISTS `price_change_batches`;

CREATE TABLE `price_change_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `effective_date` date NOT NULL,
  `changed_by` int(10) unsigned NOT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`new_values`)),
  `notify_status` enum('pending','processing','sent','partial','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_code` (`batch_code`),
  KEY `fk_pcb_product` (`product_id`),
  KEY `fk_pcb_vendor` (`vendor_id`),
  KEY `fk_pcb_user` (`changed_by`),
  CONSTRAINT `fk_pcb_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_pcb_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pcb_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `price_channels` */

DROP TABLE IF EXISTS `price_channels`;

CREATE TABLE `price_channels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_code` (`channel_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_categories` */

DROP TABLE IF EXISTS `product_categories`;

CREATE TABLE `product_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_prices` */

DROP TABLE IF EXISTS `product_prices`;

CREATE TABLE `product_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `channel_id` int(10) unsigned NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `effective_date` date NOT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_price_current` (`product_id`,`vendor_id`,`channel_id`),
  KEY `fk_pp_vendor` (`vendor_id`),
  KEY `fk_pp_channel` (`channel_id`),
  KEY `fk_pp_user` (`updated_by`),
  CONSTRAINT `fk_pp_channel` FOREIGN KEY (`channel_id`) REFERENCES `price_channels` (`id`),
  CONSTRAINT `fk_pp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pp_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pp_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_vendor_costs` */

DROP TABLE IF EXISTS `product_vendor_costs`;

CREATE TABLE `product_vendor_costs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned NOT NULL,
  `modal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `target_hpp_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `srp_suggest` decimal(15,2) NOT NULL DEFAULT 0.00,
  `srp_markup_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `srp_margin_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_primary_vendor` tinyint(1) NOT NULL DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_vendor` (`product_id`,`vendor_id`),
  KEY `fk_pvc_vendor` (`vendor_id`),
  KEY `fk_pvc_user` (`updated_by`),
  CONSTRAINT `fk_pvc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pvc_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pvc_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'pcs',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `fk_products_brand` (`brand_id`),
  KEY `fk_products_category` (`category_id`),
  KEY `fk_products_creator` (`created_by`),
  CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `fk_products_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_code` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `vendors` */

DROP TABLE IF EXISTS `vendors`;

CREATE TABLE `vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_code` (`vendor_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
