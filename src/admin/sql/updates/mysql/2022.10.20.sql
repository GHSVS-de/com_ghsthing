--- Joomla bricht Update ab, wenn Änderung schon vorhanden. Trotz IGNORE.
ALTER IGNORE TABLE `#__ghsthing_frontpage` CHANGE `ghsthing_id` `content_id` INT(11) NOT NULL DEFAULT '0' /** CAN FAIL **/;

ALTER TABLE `#__ghsthing` ADD `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `asset_id` /** CAN FAIL **/;

UPDATE `#__ghsthing` SET `description` = 'The first record created with version 1.0.0' WHERE `id` = 1;

INSERT IGNORE INTO `#__ghsthing` (`id`, `title`, `description`) VALUES
(2, 'Record 2', 'The second record created with version 1.0.1');
