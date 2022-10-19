--
-- Table structure for table `#__ghsthing_frontpage`
--

CREATE TABLE IF NOT EXISTS `#__ghsthing_frontpage` (
  `ghsthing_id` int NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `featured_up` datetime,
  `featured_down` datetime,
  PRIMARY KEY (`ghsthing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
