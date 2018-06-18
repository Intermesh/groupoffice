<?php

use go\core\App;
use go\core\util\IniFile;

$updates["201803090847"][] = "ALTER TABLE `go_log` ADD `jsonData` TEXT NULL AFTER `message`;";

$updates["201803161130"][] = function() {


	$configFile = App::findConfigFile('config.php');
	if(!$configFile) {
		echo "No config.php found. Skipping conversion\n";
		return;
	}
	

	$globalConfig = [];
	if (file_exists('/etc/groupoffice/globalconfig.inc.php')) {
		require('/etc/groupoffice/globalconfig.inc.php');
		$globalConfig = $config;
	}



	require($configFile);

	$config = array_merge($globalConfig, $config);


	$values = [
			'title' => 'title',
			'language' => 'language',
			'webmaster_email' => 'systemEmail',
			'smtp_host' => 'smtpHost',
			'smtp_port' => 'smtpPort',
			'smtp_username' => 'smtpUsername',
			'smtp_password' => 'smtpPassword',
			'smtp_encryption' => 'smtpEncryption',
			'password_min_length' => 'passwordMinLength'
	];

	foreach ($values as $old => $new) {
		if (empty($config[$old])) {
			continue;
		}
		$sql = "replace into core_setting select id as moduleId, '" . $new . "' as name, :value as value from core_module where name='core'";
		$stmt = GO()->getDbConnection()->getPDO()->prepare($sql);
		$stmt->bindValue(":value", $config[$old]);
		$stmt->execute();
	}

	$values = [
			'default_timezone' => 'defaultTimezone',
			'default_time_format' => 'defaultTimeFormat',
			'default_currency' => 'defaultCurrency',
			'default_first_weekday' => 'defaultFirstWeekday',
			'default_list_separator' => 'defaultListSeparator',
			'default_text_separator' => 'defaultTextSeparator',
			'default_thousands_separator' => 'defaultThousandSeparator',
			'default_decimal_separator' => 'defaultDecimalSeparator'
			//'register_user_groups' => 'defaultGroups'
	];

	foreach ($values as $old => $new) {
		if (empty($config[$old])) {
			continue;
		}
		$sql = "replace into core_setting select id as moduleId, '" . $new . "' as name, :value as value from core_module where name='users'";
		$stmt = GO()->getDbConnection()->getPDO()->prepare($sql);
		$stmt->bindValue(":value", $config[$old]);
		$stmt->execute();
	}

	if (isset($config['default_date_format']) && isset($config['default_date_separator'])) {
		$f = $config['default_date_format'][0] .
						$config['default_date_separator'] .
						$config['default_date_format'][1] .
						$config['default_date_separator'] .
						$config['default_date_format'][2];

		$sql = "replace into core_setting select id as moduleId, 'defaultDateFormat' as name, :value as value from core_module where name='users'";
		$stmt = GO()->getDbConnection()->getPDO()->prepare($sql);
		$stmt->bindValue(":value", $f);
		$stmt->execute();
	}

};


$updates["201804042007"][] = "delete  FROM `core_search` WHERE entityTypeId not in (select id from core_entity);";
$updates["201804042007"][] = "ALTER TABLE `core_search` ADD FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates["201804062007"][] = "ALTER TABLE `core_entity`  ADD `clientName` VARCHAR(190) NULL DEFAULT NULL;";
$updates["201804062007"][] = "update `core_entity` set clientName = name;";
$updates["201804062007"][] = "ALTER TABLE `core_entity` ADD UNIQUE(`clientName`);";

$updates["201804062008"][] = "CREATE TABLE `core_blob` (
  `id` binary(40) NOT NULL,
  `type` varchar(129) NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT '0',
  `modified` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `createdAt` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;";

$updates["201804101629"][] = "ALTER TABLE `core_user` 
ADD COLUMN `avatarId` BINARY(40) NULL AFTER `displayName`,
ADD INDEX `fk_user_avatar_id_idx` (`avatarId` ASC);
ALTER TABLE `core_user` 
ADD CONSTRAINT `fk_user_avatar_id`
  FOREIGN KEY (`avatarId`)
  REFERENCES `core_blob` (`id`)
  ON DELETE RESTRICT
  ON UPDATE NO ACTION;";


$updates["201804261506"][] ="ALTER TABLE `core_auth_token` ADD `lastActiveAt` DATETIME NOT NULL AFTER `expiresAt`;";

$updates["201805311636"][] ="ALTER TABLE `core_entity` ADD UNIQUE( `clientName`);";
$updates["201805311636"][] ="ALTER TABLE `core_entity` DROP INDEX `name`, ADD UNIQUE `name` (`name`, `moduleId`) USING BTREE;";
$updates["201805311636"][] ="ALTER TABLE `core_entity` DROP INDEX `model_name`;";
$updates["201805311636"][] ="ALTER TABLE `core_entity` ADD UNIQUE( `moduleId`, `name`);";


$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `logins` `loginCount` INT(11) NOT NULL DEFAULT '0';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `lastlogin` `_lastlogin` INT(11) NOT NULL DEFAULT '0';";
$updates["201806051638"][] ="ALTER TABLE `core_user` ADD `lastLogin` DATETIME NULL DEFAULT NULL AFTER `recoverySendAt`, ADD `createdAt` DATETIME NULL DEFAULT NULL AFTER `lastLogin`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`;";
$updates["201806051638"][] ="update core_user set lastLogin = from_unixtime(_lastlogin);";
$updates["201806051638"][] ="update core_user set createdAt = from_unixtime(ctime);";
$updates["201806051638"][] ="update core_user set modifiedAt = from_unixtime(mtime);";
$updates["201806051638"][] ="ALTER TABLE `core_user`
  DROP `_lastlogin`,
  DROP `ctime`,
  DROP `mtime`;";

$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `date_format` `dateFormat` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'd-m-Y';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `time_format` `timeFormat` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'G:i';";

$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `thousands_separator` `thousandsSeparator` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '.';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `decimal_separator` `decimalSeparator` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ',';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `first_weekday` `firstWeekday` TINYINT(4) NOT NULL DEFAULT '0';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `list_separator` `listSeparator` CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ';';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `text_separator` `textSeparator` CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '\"';";
$updates["201806051638"][] ="ALTER TABLE `core_user` ADD UNIQUE(`username`);";

$updates['201806141530'][] = "ALTER TABLE `cf_core_user` CHANGE `model_id` `id` INT(11) NOT NULL DEFAULT '0';";
$updates['201806141530'][] = 'RENAME TABLE `cf_core_user` TO `core_user_custom_fields`;';
$updates['201806141530'][] = 'DELETE FROM`core_user_custom_fields` WHERE id NOT IN (SELECT id FROM core_user);';
$updates['201806141530'][] = "ALTER TABLE `core_user_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201806141530'][] = "ALTER TABLE `core_user_custom_fields` CHANGE `id` `id` INT(11) NOT NULL;";

$updates['201806141530'][] = "update core_entity set moduleId = (select id from core_module where name = 'groups') where name='Group';";