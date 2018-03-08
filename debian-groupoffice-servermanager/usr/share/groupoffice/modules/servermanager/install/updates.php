<?php
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `count_users` `count_users` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `install_time` `install_time` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `total_logins` `total_logins` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `report_ctime` `report_ctime` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `database_usage` `database_usage` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `file_storage_usage` `file_storage_usage` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `admin_country` `admin_country` CHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `thousands_separator` `thousands_separator` CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `billing` `billing` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `professional` `professional` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `status_change_time` `status_change_time` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201203201554"][]="ALTER TABLE `sm_installations` CHANGE `config_file` `config_file` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201203291226"][]="ALTER TABLE `sm_installations` ADD `token` VARCHAR( 100 ) NOT NULL";

$updates["201203291226"][]="CREATE TABLE IF NOT EXISTS `sm_installation_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `installation_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `installation_id` (`installation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";


$updates["201203291226"][]="CREATE TABLE IF NOT EXISTS `sm_installation_user_modules` (
  `user_id` int(11) NOT NULL,
  `module_id` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201203291226"][]="ALTER TABLE `sm_installations` CHANGE `lastlogin` `lastlogin` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201205091330"][]="CREATE TABLE IF NOT EXISTS `sm_auto_email` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL DEFAULT '',
	`days` int(5) NOT NULL DEFAULT '0',
	`mime` TEXT,
	`active` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201205091615"][]="INSERT INTO `sm_auto_email` (`id`,`name`,`days`,`mime`,`active`) VALUES (NULL , 'Example automatic email', '20',
'Message-ID: <1336645137.4fab9611360c0@localhost>
Date: Thu, 10 May 2012 12:18:57 +0200
Subject: Example automatic email
From: 
MIME-Version: 1.0
Content-Type: multipart/alternative;
 boundary=\"_=_swift_v4_13366451374fab961137ae9_=_\"
X-Mailer: Group-Office 4.0.12
X-MimeOLE: Produced by Group-Office 4.0.12


--_=_swift_v4_13366451374fab961137ae9_=_
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Dear {installation:admin_name},

You receive this e-mail becau=
se on {installation:ctime},
{automaticemail:days} days ago, you creat=
ed a 30 day trial
installation of Group-Office Professional at
h=
ttp://{installation:name}.
We want to remind you that the trial peri=
od will expire in 10 days.

We hope you are enjoying your trial p=
eriod and you will continue using
it. If you want to continue using =
Group-Office you must pay for the
service after this trial period ex=
pires. If you don't order within 20
days then we asume you don't want=
 to use Group-Office anymore and your
installation will be remov=
ed.

Thank you for using Group-Office!

With kind rega=
rds,

The Group-Office team.


--_=_swift_v4_13366451374fab961137ae9_=_
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Dear {installation:admin_name},<br><br>You receive this e-mail because o=
n {installation:ctime}, {automaticemail:days} days ago, you created a 30=
 day trial installation of Group-Office Professional at http://{installa=
tion:name}.<br>We want to remind you that the trial period will expire i=
n 10 days.<br><br>We hope you are enjoying your trial period and you wil=
l continue using it. If you want to continue using Group-Office you must=
 pay for the service after this trial period expires. If you don't order=
 within 20 days then we asume you don't want to use Group-Office anymore=
 and your installation will be removed.<br><br>Thank you for using Group=
-Office!<br><br>With kind regards,<br><br>The Group-Office team.<br>

--_=_swift_v4_13366451374fab961137ae9_=_--

', '0');";


$updates["201207051558"][]="ALTER TABLE `sm_installations` CHANGE `status` `status` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ignore'";
$updates["201207051558"][]="UPDATE sm_installations set status='ignore' where status=''";
$updates["201207051558"][]="UPDATE sm_installations set status='trial' where status!='ignore'";

//From here the servermanager will support auto invoicing and logging

$updates["201210051200"][]="ALTER TABLE `sm_installations` ENGINE = InnoDB , 
	DROP COLUMN `config_file` , 
	DROP COLUMN `status_change_time` , 
	DROP COLUMN `professional` , 
	DROP COLUMN `billing` , 
	DROP COLUMN `decimal_separator` , 
	DROP COLUMN `thousands_separator` , 
	DROP COLUMN `date_format` , 
	DROP COLUMN `admin_country` , 
	DROP COLUMN `admin_salutation` , 
	DROP COLUMN `report_ctime` , 
	DROP COLUMN `mailbox_usage` , 
	DROP COLUMN `file_storage_usage` , 
	DROP COLUMN `database_usage` , 
	DROP COLUMN `total_logins` , 
	DROP COLUMN `lastlogin` , 
	DROP COLUMN `install_time` , 
	DROP COLUMN `count_users` , 
	ADD COLUMN `trial_days` INT(11) NOT NULL DEFAULT 30  AFTER `max_users` , 
	ADD COLUMN `lastlogin` INT(11) NULL DEFAULT NULL  AFTER `trial_days` , 
	CHANGE COLUMN `name` `name` VARCHAR(100) NOT NULL;";

$updates["201210051200"][]="ALTER TABLE `sm_installation_users` ENGINE = InnoDB , DROP COLUMN `email` , DROP COLUMN `last_name` , DROP COLUMN `middle_name` , DROP COLUMN `first_name` , DROP COLUMN `id` , ADD COLUMN `user_id` INT(11) NOT NULL  FIRST , ADD COLUMN `used_modules` TEXT NOT NULL  AFTER `installation_id` , CHANGE COLUMN `lastlogin` `lastlogin` INT(11) NULL DEFAULT NULL  , CHANGE COLUMN `enabled` `enabled` TINYINT(1) NULL DEFAULT NULL  , 
  ADD CONSTRAINT `fk_sm_installation_users_sm_installations1`
  FOREIGN KEY (`installation_id` )
  REFERENCES `sm_installations` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, DROP PRIMARY KEY 
, ADD PRIMARY KEY (`user_id`, `installation_id`) 
, ADD INDEX `fk_sm_installation_users_sm_installations1` (`installation_id` ASC) 
, DROP INDEX `installation_id`;";

$updates["201210051200"][]="CREATE  TABLE IF NOT EXISTS `sm_user_prices` (
  `max_users` INT NOT NULL ,
  `price_per_month` DOUBLE NOT NULL ,
  PRIMARY KEY (`max_users`) )
ENGINE = InnoDB;";

$updates["201210051200"][]="CREATE  TABLE IF NOT EXISTS `sm_usage_history` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ctime` INT NOT NULL ,
  `count_users` INT NOT NULL ,
  `database_usage` DOUBLE NOT NULL ,
  `file_storage_usage` DOUBLE NOT NULL ,
  `mailbox_usage` DOUBLE NOT NULL DEFAULT 0,
  `total_logins` INT NOT NULL DEFAULT 0 ,
  `installation_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sm_usage_history_sm_installations1` (`installation_id` ASC) ,
  CONSTRAINT `fk_sm_usage_history_sm_installations1`
    FOREIGN KEY (`installation_id` )
    REFERENCES `sm_installations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;";

$updates["201210051200"][]="CREATE  TABLE IF NOT EXISTS `sm_module_prices` (
  `module_name` VARCHAR(45) NOT NULL ,
  `price_per_month` DOUBLE NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`module_name`) )
ENGINE = InnoDB;";

$updates["201210051200"][]="CREATE  TABLE IF NOT EXISTS `sm_automatic_invoices` (
  `id` INT NOT NULL ,
  `enable_invoicing` TINYINT(1) NOT NULL DEFAULT 0 ,
  `discount_price` DOUBLE NOT NULL DEFAULT 0 ,
  `discount_description` VARCHAR(255) NULL DEFAULT 'Discount' ,
  `discount_percentage` DOUBLE NOT NULL DEFAULT 0 ,
  `invoice_timespan` INT NOT NULL DEFAULT 1 ,
  `next_invoice_time` INT NOT NULL ,
  `customer_name` VARCHAR(255) NOT NULL ,
  `customer_address` VARCHAR(255) NOT NULL ,
  `customer_address_no` VARCHAR(10) NOT NULL ,
  `customer_zip` VARCHAR(45) NOT NULL ,
  `customer_state` VARCHAR(255) NULL ,
  `customer_country` VARCHAR(45) NOT NULL ,
  `customer_vat` VARCHAR(255) NULL ,
  `customer_city` VARCHAR(255) NOT NULL ,
  `installation_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sm_automatic_invoices_sm_installations1` (`installation_id` ASC) ,
  CONSTRAINT `fk_sm_automatic_invoices_sm_installations1`
    FOREIGN KEY (`installation_id` )
    REFERENCES `sm_installations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;";

$updates["201210051200"][]="CREATE  TABLE IF NOT EXISTS `sm_installation_modules` (
  `name` VARCHAR(100) NOT NULL ,
  `installation_id` INT NOT NULL ,
  `ctime` INT NOT NULL ,
  `mtime` INT NOT NULL ,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1 ,
  INDEX `fk_sm_installation_modules_sm_installations1` (`installation_id` ASC) ,
  PRIMARY KEY (`name`, `installation_id`) ,
  CONSTRAINT `fk_sm_installation_modules_sm_installations1`
    FOREIGN KEY (`installation_id` )
    REFERENCES `sm_installations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;";

$updates["201210051200"][]="ALTER TABLE `sm_auto_email` ENGINE = InnoDB , CHANGE COLUMN `days` `days` INT(11) NOT NULL DEFAULT 0;";

$updates["201210051200"][]="DROP TABLE IF EXISTS `sm_installation_user_modules`;";

$updates['201210051200'][]="INSERT INTO `sm_user_prices` (`max_users`, `price_per_month`) VALUES
	(1, 10),
	(2, 19),
	(3, 28),
	(4, 36),
	(5, 43),
	(6, 50),
	(7, 57),
	(8, 63),
	(9, 69),
	(10, 74),
	(15, 95),
	(20, 109),
	(25, 123)
	(30, 134),
	(35, 141),
	(40, 146),
	(45, 148),
	(50, 150);";

$updates['201210051200'][]="INSERT INTO `sm_module_prices` (`module_name` ,`price_per_month`) VALUES 
	('billing', '20');";


$updates['201212111333'][]="ALTER TABLE  `sm_usage_history` CHANGE  `mailbox_usage`  `mailbox_usage` DOUBLE NOT NULL DEFAULT  '0'";
$updates['201212111333'][]="ALTER TABLE  `sm_usage_history` CHANGE  `database_usage`  `database_usage` DOUBLE NOT NULL DEFAULT  '0'";
$updates['201212111333'][]="ALTER TABLE  `sm_usage_history` CHANGE  `file_storage_usage`  `file_storage_usage
	ALTER TABLE  `sm_usage_history` CHANGE  `count_users`  `count_users` INT( 11 ) NOT NULL DEFAULT  '0'
` DOUBLE NOT NULL DEFAULT  '0'";

$updates['201212111333'][]="DROP TABLE sm_installation_users";
$updates['201212111333'][]="CREATE  TABLE IF NOT EXISTS `sm_installation_users` (
  `user_id` INT NOT NULL ,
	`username` VARCHAR(100) NOT NULL,
  `installation_id` INT NOT NULL ,
  `used_modules` TEXT NOT NULL ,
  `ctime` INT NOT NULL ,
  `lastlogin` INT NULL ,
  `enabled` TINYINT(1) NULL ,
  PRIMARY KEY (`user_id`, `installation_id`) ,
  INDEX `fk_sm_installation_users_sm_installations1` (`installation_id` ASC) ,
  CONSTRAINT `fk_sm_installation_users_sm_installations1`
    FOREIGN KEY (`installation_id` )
    REFERENCES `sm_installations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;";


$updates['201212111333'][]="CREATE TABLE `li_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `uname` varchar(100) DEFAULT NULL,
  `version` varchar(45) NOT NULL DEFAULT '-',
  `active` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$updates['201212111333'][]="ALTER TABLE  `sm_installation_modules` ADD  `usercount` INT NOT NULL DEFAULT  '0'";


$updates['201301211616'][]="ALTER TABLE `sm_installations` ADD `file_storage_usage` INT NOT NULL DEFAULT '0',
ADD `database_usage` BIGINT NOT NULL DEFAULT '0',
ADD `mailbox_usage` BIGINT NOT NULL DEFAULT '0',
ADD `quota` BIGINT NOT NULL DEFAULT '0',
ADD `total_logins` BIGINT NOT NULL DEFAULT '0'";


$updates["201303201600"][]="script:1_install_cron.php";

$updates["201304021023"][]="ALTER TABLE  `sm_installation_users` ADD  `email` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `username`";


$updates["201304080841"][]="ALTER TABLE  `sm_installations` CHANGE  `file_storage_usage`  `file_storage_usage` BIGINT NOT NULL DEFAULT  '0'";


$updates["201403071221"][]="ALTER TABLE  `sm_installations` CHANGE  `token`  `token` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;";

$updates["201606140924"][]="ALTER TABLE `sm_installations` CHANGE `mailbox_usage` `mailbox_usage` BIGINT(20) NOT NULL DEFAULT '0'; ";
$updates["201606140924"][]="ALTER TABLE `sm_installations` CHANGE `database_usage` `database_usage` BIGINT(20) NOT NULL DEFAULT '0'; ";


$updates["201608010924"][]="ALTER TABLE `sm_installations` ADD `acl_id` INT(11) NOT NULL DEFAULT '0';";

$updates["201608010924"][]="script:2_add_acl_id.php";