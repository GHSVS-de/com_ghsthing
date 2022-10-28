--
-- Table structure for table `#__ghsthing`
-- CREATE TABLE IF NOT EXISTS `pkuej_ghsthing` LIKE `pkuej_contact_details`;
--

CREATE TABLE IF NOT EXISTS `#__ghsthing`
(
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	`introtext` mediumtext NOT NULL,
	`fulltext` mediumtext NOT NULL,
	`state` tinyint NOT NULL DEFAULT 0,
	`catid` int unsigned NOT NULL DEFAULT 0,
	`created` datetime NOT NULL,
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`modified` datetime NOT NULL,
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`checked_out` int unsigned,
	`checked_out_time` datetime NULL DEFAULT NULL,
	`publish_up` datetime NULL DEFAULT NULL,
	`publish_down` datetime NULL DEFAULT NULL,
	`images` text NOT NULL,
	`params` text NOT NULL,
	`version` int unsigned NOT NULL DEFAULT 1,
	`ordering` int NOT NULL DEFAULT 0,
	`metakey` text,
	`metadesc` text NOT NULL,
	`access` int unsigned NOT NULL DEFAULT 0,
	`metadata` text NOT NULL,
	`featured` tinyint unsigned NOT NULL DEFAULT 0,
	`language` char(7) NOT NULL,
	`note` varchar(255) NOT NULL DEFAULT '',
	`asset_id` int unsigned NOT NULL DEFAULT 0,

	PRIMARY KEY (`id`),
	KEY `idx_access` (`access`),
	KEY `idx_checkout` (`checked_out`),
	KEY `idx_state` (`state`),
	KEY `idx_catid` (`catid`),
	KEY `idx_createdby` (`created_by`),
	KEY `idx_featured_catid` (`featured`,`catid`),
	KEY `idx_language` (`language`),
	KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ghsthing_frontpage`
--

CREATE TABLE IF NOT EXISTS `#__ghsthing_frontpage` (
  `content_id` int NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `featured_up` datetime,
  `featured_down` datetime,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Inserts for table `#__content_types
-- See installerScript.php
--
