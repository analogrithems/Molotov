
DROP TABLE IF EXISTS `av_activities`;
CREATE TABLE IF NOT EXISTS `av_activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_title` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `short_description` text COLLATE utf8_unicode_ci,
  `special_instructions` text COLLATE utf8_unicode_ci,
  `supplier_activity_id` int(10) unsigned DEFAULT NULL,
  `language` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `seo_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `status` enum('active','inactive','abandoned','decommissioned','rev','translating') COLLATE utf8_unicode_ci DEFAULT 'inactive',
  `type` enum('activity','ground_transfer') COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rev_id` int(11) DEFAULT NULL,
  `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created` timestamp NULL DEFAULT NULL,
  `originating_activity_id` int(10) unsigned DEFAULT '0' COMMENT 'This is the activity id that this record was created from.  Used when doing translations and tracing how the activity and content was created',
  `min_inv` int(11) DEFAULT NULL,
  `max_inv` int(11) DEFAULT NULL,
  `booking_cutoff_mins` int(11) DEFAULT NULL,
  `booking_cutoff_hours` int(11) DEFAULT NULL,
  `cfa` tinyint(1) DEFAULT NULL,
  `terms_and_conditions` text COLLATE utf8_unicode_ci,
  `cancellation_policy` text COLLATE utf8_unicode_ci,
  `single_voucher` tinyint(3) unsigned DEFAULT '1' COMMENT 'a single voucher would mean that for each ticket sold you only need one voucher for it.  If this is false it would print multiple vouchers for a single ticket',
  `workflow_status` enum('unedited','inprogress','complete') COLLATE utf8_unicode_ci DEFAULT 'unedited',
  PRIMARY KEY (`id`),
  KEY `language` (`language`,`supplier_id`),
  KEY `originating_activity_id` (`originating_activity_id`,`owner_id`),
  KEY `findMine` (`owner_id`,`language`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_addon_options`;
CREATE TABLE IF NOT EXISTS `av_addon_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_addon_id` int(10) unsigned DEFAULT NULL,
  `choice` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_activity_addon_options_activity_addons1_idx` (`activity_addon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_addon_options_language`;
CREATE TABLE IF NOT EXISTS `av_addon_options_language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon_options_id` int(10) unsigned NOT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  `choice` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `addon_options_id_activity_id` (`addon_options_id`,`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_addons`;
CREATE TABLE IF NOT EXISTS `av_addons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_activity_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `taxable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_activity_addons_activitys1_idx` (`supplier_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_addons_language`;
CREATE TABLE IF NOT EXISTS `av_addons_language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_addon_id` int(10) unsigned NOT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_addon_id_activity_id` (`activity_addon_id`,`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_fees`;
CREATE TABLE IF NOT EXISTS `av_fees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_activity_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `fee` decimal(10,2) DEFAULT NULL,
  `percent` float(10,5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_activity_fees_activitys1_idx` (`supplier_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_fees_language`;
CREATE TABLE IF NOT EXISTS `av_fees_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(10) unsigned NOT NULL,
  `activity_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_id` (`fee_id`),
  CONSTRAINT `fees_language_ibfk_1` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_geo`;
CREATE TABLE IF NOT EXISTS `av_geo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_activity_id` int(10) unsigned DEFAULT NULL,
  `geo_info_id` int(10) unsigned DEFAULT NULL,
  `pickupTime` int(10) unsigned DEFAULT NULL,
  `pickupDayOfWeek` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo_type` enum('pickup','eventLocation') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee` float(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_geo_products1_idx` (`supplier_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_geo_cache`;
CREATE TABLE IF NOT EXISTS `av_geo_cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address_des` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `formatted_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` decimal(18,12) DEFAULT NULL,
  `lng` decimal(18,12) DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postalcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_geo_info`;
CREATE TABLE IF NOT EXISTS `av_geo_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address_des` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` decimal(18,12) DEFAULT NULL,
  `lng` decimal(18,12) DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postalcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_ground_transport`;
CREATE TABLE IF NOT EXISTS `av_ground_transport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `transport_zone` text COLLATE utf8_unicode_ci,
  `direction` enum('non-directional','roundtrip','fromport','toport') COLLATE utf8_unicode_ci DEFAULT NULL,
  `vehicle_type` enum('Unspecified','FixedRouteShuttle','StdCar','Minivan','Minibus','Shuttle','WaterTaxi','Ferry','SUV','Train','ExecCar','LuxCar','Limo','Towncar','SpeedyShuttle') COLLATE utf8_unicode_ci DEFAULT NULL,
  `bags` int(11) DEFAULT NULL,
  `passengers` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_guest_type`;
CREATE TABLE IF NOT EXISTS `av_guest_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_activity_id` int(10) unsigned NOT NULL,
  `master_guest_type_label_id` int(10) unsigned DEFAULT NULL,
  `label_override` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `vendor_sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_option` enum('web','pos','both') COLLATE utf8_unicode_ci DEFAULT 'both',
  `default_price` float(20,6) DEFAULT NULL,
  `display_price` float(20,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_activity_guest_type_master_guest_type_label1_idx` (`master_guest_type_label_id`),
  KEY `idx_guest_type_label_override` (`label_override`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_guest_type_restrictions`;
CREATE TABLE IF NOT EXISTS `av_guest_type_restrictions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guest_type_id` int(10) unsigned DEFAULT NULL,
  `range_type` enum('Age','Capacity','Duration','Height','Weight') COLLATE utf8_unicode_ci DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `unit` enum('Years','Travelers','Days','Hours','Inches','Lbs','Kg','CM') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_guest_type_restrictions_guest_type1_idx` (`guest_type_id`),
  CONSTRAINT `guest_type_restrictions_ibfk_1` FOREIGN KEY (`guest_type_id`) REFERENCES `guest_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_master_guest_type_label`;
CREATE TABLE IF NOT EXISTS `av_master_guest_type_label` (
  `id` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(510) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ruleFlag` enum('Individual','Group','Other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Individual',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_master_guest_type_label_lang`;
CREATE TABLE IF NOT EXISTS `av_master_guest_type_label_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `master_guest_type_label_id` int(10) unsigned DEFAULT NULL,
  `language` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_media`;
CREATE TABLE IF NOT EXISTS `av_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL,
  `media_id` int(10) unsigned NOT NULL,
  `type` enum('image','movie') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'image',
  `order` tinyint(3) unsigned NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_products`;
CREATE TABLE IF NOT EXISTS `av_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `description` text,
  `product_type` enum('ACTIVITY') DEFAULT 'ACTIVITY',
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `language` varchar(6) NOT NULL DEFAULT '0',
  `parent_product_id` int(11) NOT NULL DEFAULT '0',
  `source_product_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `av_term_relationships`;
CREATE TABLE IF NOT EXISTS `av_term_relationships` (
  `activity_id` int(10) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` int(10) unsigned NOT NULL DEFAULT '0',
  `weight` int(11) DEFAULT '0',
  PRIMARY KEY (`activity_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`),
  KEY `fk_term_relationships_activitys1_idx` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_term_taxonomy`;
CREATE TABLE IF NOT EXISTS `av_term_taxonomy` (
  `term_taxonomy_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` int(10) unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `language` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_term_taxonomy_meta`;
CREATE TABLE IF NOT EXISTS `av_term_taxonomy_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term_taxonomy_id` int(11) DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `lookup` (`term_taxonomy_id`,`meta_key`),
  KEY `fk_term_taxonomy_meta_term_taxonomy1_idx` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_terms`;
CREATE TABLE IF NOT EXISTS `av_terms` (
  `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`term_id`,`language`),
  KEY `name` (`name`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `av_times`;
CREATE TABLE IF NOT EXISTS `av_times` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_activity_id` int(11) DEFAULT NULL,
  `startDayOfWeek` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') COLLATE utf8_unicode_ci DEFAULT NULL,
  `endDayOfWeek` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') COLLATE utf8_unicode_ci DEFAULT NULL,
  `startTime` int(4) unsigned DEFAULT NULL,
  `endTime` int(4) unsigned DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `rule` enum('available','blackout','lifespan') COLLATE utf8_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`id`),
  KEY `pk_times` (`id`),
  KEY `supplier_activity_id` (`supplier_activity_id`,`startDate`,`endDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


