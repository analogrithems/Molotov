CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) CHARACTER SET latin1 NOT NULL,
  `short_description` varchar(400) CHARACTER SET latin1 DEFAULT NULL,
  `description` text CHARACTER SET latin1,
  `special_instructions` text CHARACTER SET latin1,
  `language` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT 'en_US',
  `currency` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT 'USD',
  `status` enum('active','inactive','trash') CHARACTER SET latin1 NOT NULL DEFAULT 'active',
  `workflow` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_type` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'Product',
  `source_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_catalog_relationships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `original_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `catalog_id` int(10) unsigned NOT NULL,
  `has_children` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `i18n` varchar(10) CHARACTER SET latin1 NOT NULL,
  `rank_id` int(11) NOT NULL DEFAULT '10' COMMENT 'This is used for manual rank sorting',
  PRIMARY KEY (`id`),
  KEY `owner` (`company_id`),
  KEY `activity` (`product_id`,`company_id`),
  KEY `catalog` (`company_id`,`catalog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_fee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `root_product_id` int(10) unsigned NOT NULL,
  `amount` decimal(18,6) DEFAULT NULL,
  `percent` decimal(18,6) DEFAULT NULL,
  `required` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `root_product` (`root_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_fee_translation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `product_fee_id` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `sku` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `productfee` (`product_id`,`product_fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `meta_value` text CHARACTER SET latin1,
  PRIMARY KEY (`id`),
  KEY `product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_taxonomy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taxonomy` varchar(45) CHARACTER SET latin1 NOT NULL COMMENT 'the taxonomy type, category, keyword, tag',
  `term` varchar(255) CHARACTER SET latin1 NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Global tags are company_id=0 but a company can create custom tags that are owned by only them',
  `parent_id` int(11) DEFAULT '0',
  `language` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en_US',
  PRIMARY KEY (`id`),
  KEY `taxonomy` (`taxonomy`,`term`),
  KEY `company` (`company_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_taxonomy_relationships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `product_taxonomy_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product2term` (`product_id`,`product_taxonomy_id`),
  KEY `term2priduct` (`product_taxonomy_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_variance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `root_product_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `multi` tinyint(4) NOT NULL DEFAULT '0',
  `required` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `root_product` (`root_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_variance_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `root_product_id` int(10) unsigned NOT NULL,
  `product_variance_id` int(10) unsigned NOT NULL,
  `amount` decimal(18,6) DEFAULT NULL,
  `item_order` int(11) NOT NULL DEFAULT '100',
  `inventory` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `productVariance` (`product_variance_id`,`root_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_variance_item_translation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `variance_item_id` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `product_variance` (`variance_item_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `product_variance_translation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_variance_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `productVariance` (`product_id`,`product_variance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
