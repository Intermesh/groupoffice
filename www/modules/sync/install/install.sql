DROP TABLE IF EXISTS `sync_settings`;
CREATE TABLE IF NOT EXISTS `sync_settings` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;