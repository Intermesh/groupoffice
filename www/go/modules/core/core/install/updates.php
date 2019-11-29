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

	if(!isset($config['title'])) {
		$config['title'] = 'Group-Office';
	}

	if(!isset($config['language'])) {
		$config['language'] = 'en';
	}

  if(!isset($config['webmaster_email'])) {
    $config['webmaster_email'] = 'webmaster@example.com';
  }

	if(!isset($config['smtp_host'])) {
		$config['smtp_host'] = 'localhost';
	}

	if(!isset($config['smtp_port'])) {
		$config['smtp_port'] = '25';
	}

	if(!isset($config['smtp_username'])) {
		$config['smtp_username'] = '';
	}

	if(!isset($config['smtp_password'])) {
		$config['smtp_password'] = '';
	}

	if(!isset($config['smtp_encryption'])) {
		$config['smtp_encryption'] = '';
	}

	if(!isset($config['password_min_length'])) {
		$config['password_min_length'] = 6;
	}	

	foreach ($values as $old => $new) {
		// if (empty($config[$old])) {
		// 	continue;
		// }
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
$updates["201804062007"][] = "update `core_entity` set clientName = name WHERE clientName is null";
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

$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `date_format` `dateFormat` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd-m-Y';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `time_format` `timeFormat` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'G:i';";

$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `thousands_separator` `thousandsSeparator` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '.';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `decimal_separator` `decimalSeparator` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ',';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `first_weekday` `firstWeekday` TINYINT(4) NOT NULL DEFAULT '0';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `list_separator` `listSeparator` CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ';';";
$updates["201806051638"][] ="ALTER TABLE `core_user` CHANGE `text_separator` `textSeparator` CHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '\"';";
$updates["201806051638"][] ="ALTER TABLE `core_user` ADD UNIQUE(`username`);";

$updates['201806141530'][] = "ALTER TABLE `cf_core_user` CHANGE `model_id` `id` INT(11) NOT NULL DEFAULT '0';";
$updates['201806141530'][] = 'RENAME TABLE `cf_core_user` TO `core_user_custom_fields`;';
$updates['201806141530'][] = 'DELETE FROM`core_user_custom_fields` WHERE id NOT IN (SELECT id FROM core_user);';
$updates['201806141530'][] = "ALTER TABLE `core_user_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201806141530'][] = "ALTER TABLE `core_user_custom_fields` CHANGE `id` `id` INT(11) NOT NULL;";

$updates['201806141530'][] = "update core_entity set moduleId = (select id from core_module where name = 'groups') where name='Group';";


$updates['201807271339'][] = "CREATE TABLE `core_cron_job` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `nextRunAt` datetime DEFAULT NULL,
  `lastRunAt` datetime DEFAULT NULL,
  `runningSince` datetime DEFAULT NULL,
  `lastError` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201807271339'][] = "ALTER TABLE `core_cron_job`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`),
  ADD KEY `moduleId` (`moduleId`);";


$updates['201807271339'][] = "ALTER TABLE `core_cron_job`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";


$updates['201807271339'][] = "ALTER TABLE `core_cron_job`
  ADD CONSTRAINT `core_cron_job_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;";

$updates['201807271339'][] = "DROP TABLE `core_state`;";

$updates['201807271339'][] = "ALTER TABLE `core_entity` ADD `highestModSeq` INT NULL DEFAULT NULL AFTER `clientName`;";

$updates['201807271339'][] = "CREATE TABLE `core_change` (
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `destroyed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";


$updates['201807271339'][] = "ALTER TABLE `core_change`
  ADD PRIMARY KEY (`entityId`,`entityTypeId`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `entityTypeId` (`entityTypeId`);";



$updates['201807271339'][] = "ALTER TABLE `core_change`
  ADD CONSTRAINT `core_change_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE;";
$updates["201810071410"][]="DROP TABLE IF EXISTS `go_mail_counter`;";

$updates["201810091544"][]="update core_user set theme='Paper' where theme='Group-Office' or theme='Default' or theme = 'ExtJS'";

$updates["201810111129"][]="DELETE FROM `core_setting` WHERE `name` = 'defaultGroups'";

$updates["201810111129"][]="CREATE TABLE IF NOT EXISTS `core_user_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates["201810111129"][]="ALTER TABLE `core_user_default_group`
  ADD CONSTRAINT `core_user_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;";

$updates["201810111129"][]="CREATE TABLE IF NOT EXISTS `core_group_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates["201810111129"][]="ALTER TABLE `core_group_default_group`
  ADD CONSTRAINT `core_group_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;";

$updates["201810111129"][] = "INSERT INTO `core_group_default_group` (`groupId`) VALUES (2);";

$updates["201810111129"][] = "ALTER TABLE `core_user` ADD `shortDateInList` BOOLEAN NOT NULL DEFAULT TRUE AFTER `dateFormat`;";
$updates["201810251129"][] = "TRUNCATE TABLE go_state"; //for fixed date columns

$updates["201811020837"][] = "ALTER TABLE `core_user` CHANGE `firstWeekday` `firstWeekday` TINYINT(4) NOT NULL DEFAULT '1';";

$updates['201811020837'][] = "";
$updates['201811020837'][] = function() {
	foreach(GO\Customfields\Model\Field::model()->find(GO\Base\Db\FindParams::newInstance()->ignoreAcl()) as $field) {
		if(preg_match("/[^a-z0-9A-Z_]+/", $field->databaseName)) {
				
			$field->databaseName = $stripped = preg_replace('/[^a-z0-9A-Z_]+/', '_', $field->databaseName);
			$i = 1;
			$tableName = $field->category->customfieldsTableName();
			while(\go\core\db\Table::getInstance($tableName)->hasColumn($field->databaseName)) {
				$field->databaseName = $stripped .'_' .$i++;
			}
			if(!$field->save(true)) {
				echo "Save of field ". $field->name . " failed: ". var_export($field->getValidationErrors(), true) ."\n";
				throw new \Exception("Failed to save custom field.");
			}
		}
	}
};


$updates['201901251344'][] = function() {
	GO()->getDbConnection()->query("ALTER TABLE `core_search` CHANGE `keywords` `keywords` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';");
	GO()->getDbConnection()->query("ALTER TABLE `core_search` ADD INDEX(`keywords`);");
};

$updates['201901301035'][] = "ALTER TABLE `go_cron` ADD INDEX `nextrun_active` (`nextrun`, `active`);";
$updates['201901301035'][] = "ALTER TABLE `go_reminders_users` ADD INDEX `user_id_time` (`user_id`, `time`);";
$updates['201901301035'][] = "ALTER TABLE `core_auth_method` ADD INDEX `moduleId_sortOrder` (`moduleId`, `sortOrder`);";

$updates['201902141322'][] = "UPDATE `core_module` SET `package` = 'community' WHERE `name` = 'serverclient';";
