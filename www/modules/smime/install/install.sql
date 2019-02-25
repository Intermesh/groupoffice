CREATE TABLE IF NOT EXISTS `smi_certs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cert` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB ;

DROP TABLE IF EXISTS `smi_pkcs12`;
CREATE TABLE IF NOT EXISTS `smi_pkcs12` (
  `account_id` int(11) NOT NULL,
  `cert` blob,
  `always_sign` tinyint(1) NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB;