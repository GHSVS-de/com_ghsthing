ALTER TABLE `#__ghsthing` DROP `description` /** CAN FAIL **/;

UPDATE `#__ghsthing` SET `description` = 'The first record created with version 1.0.0' WHERE `id` = 1 /** CAN FAIL **/;

INSERT IGNORE INTO `#__ghsthing` (`id`, `title`, `description`) VALUES
(2, 'Record 2', 'The second record created with version 1.0.1') /** CAN FAIL **/;
