--
-- Table structure for table `zpa_devices`
--

CREATE TABLE IF NOT EXISTS `zpa_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(50) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `remote_addr` varchar(20) NOT NULL,
  `can_connect` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `new` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(190) NOT NULL DEFAULT '',
  `comment` text,
  `as_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
	KEY `device_id` (`device_id`,`username`)
) ENGINE=InnoDB ;