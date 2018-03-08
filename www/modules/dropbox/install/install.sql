CREATE TABLE IF NOT EXISTS `dbx_users` (
  `user_id` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `dropbox_user_id` varchar(100) NOT NULL,
  `delta_cursor` text,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;