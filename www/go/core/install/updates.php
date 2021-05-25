<?php

use go\core\App;
use go\core\db\Table;
use go\core\util\ClassFinder;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Expression;
use go\core\db\Query;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\model\Search;
use GO\Base\Model\SearchCacheRecord;
use go\core\model\Group;
use go\core\model\Acl;
use go\core\model\Field;

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
		$stmt = go()->getDbConnection()->getPDO()->prepare($sql);
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
		$stmt = go()->getDbConnection()->getPDO()->prepare($sql);
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
		$stmt = go()->getDbConnection()->getPDO()->prepare($sql);
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

$updates['201902141322'][] = "ALTER TABLE `core_search` ADD INDEX(`keywords`);";
$updates['201902141322'][] = "ALTER TABLE `go_cron` ADD INDEX `nextrun_active` (`nextrun`, `active`);";
$updates['201902141322'][] = "ALTER TABLE `go_reminders_users` ADD INDEX `user_id_time` (`user_id`, `time`);";
$updates['201902141322'][] = "ALTER TABLE `core_auth_method` ADD INDEX `moduleId_sortOrder` (`moduleId`, `sortOrder`);";
$updates['201902141322'][] = "UPDATE `core_module` SET `package` = 'community' WHERE `name` = 'serverclient';";

//Master
$updates['201902141322'][] = "update `core_entity` set highestModSeq=0 where highestModSeq is null;";
$updates['201902141322'][] = "ALTER TABLE `core_entity` CHANGE `highestModSeq` `highestModSeq` INT(11) NOT NULL DEFAULT '0';";
$updates['201902141322'][] = "truncate table core_change;";
$updates['201902141322'][] = "DROP TABLE IF EXISTS `core_acl_group_changes`;";
$updates['201902141322'][] = "CREATE TABLE IF NOT EXISTS `core_acl_group_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`,`groupId`),
  KEY `group` (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201902141322'][] = "ALTER TABLE `core_acl_group_changes`
  ADD CONSTRAINT `all` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;";

$updates['201902141322'][] = "insert into core_acl_group_changes select null, aclId, groupId, COALESCE((select highestModSeq from core_entity where name='Acl'), 0), null from core_acl_group;";

$updates['201902141322'][] = "ALTER TABLE `core_change`
ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);";


$updates['201902141322'][] = "ALTER TABLE `core_blob` ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`, ADD `staleAt` DATETIME NULL DEFAULT NULL AFTER `modifiedAt`;";
$updates['201902141322'][] = "ALTER TABLE `core_blob` ADD INDEX(`staleAt`);";
$updates['201902141322'][] = "UPDATE `core_blob` set modifiedAt = from_unixtime(modified)";
$updates['201902141322'][] = "ALTER TABLE `core_blob` DROP `modified`";

$updates['201902141322'][] = "insert into core_cron_job (moduleId,name, expression, description) values ((select id from core_module where name='core'), 'GarbageCollection', '0 * * * *', 'Garbage collection')";

$updates['201902141322'][] = "ALTER TABLE `core_customfields_field_set` ADD `filter` TEXT NULL DEFAULT NULL;";


$updates['201902141322'][] = "ALTER TABLE `core_customfields_field` CHANGE `datatype` `type` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Text';";
$updates['201902141322'][] = "ALTER TABLE `core_customfields_field` CHANGE `helptext` `hint` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;";


$updates['201902141322'][] = "RENAME TABLE `cf_select_options` TO `core_customfields_select_option`;";

$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` CHANGE `field_id` `fieldId` INT(11) NOT NULL;";
$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` CHANGE `sort_order` `sortOrder` INT(11) NOT NULL;";
$updates['201902141322'][] = "delete FROM `core_customfields_select_option` WHERE fieldId not in (select id from core_customfields_field);";
$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` ADD FOREIGN KEY (`fieldId`) REFERENCES `core_customfields_field`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['201902141322'][] = "ALTER TABLE `core_customfields_field_set` ADD `description` TEXT NULL AFTER `name`;";
$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` ADD `parentId` INT NULL DEFAULT NULL AFTER `fieldId`, ADD INDEX (`parentId`);";
$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` ADD FOREIGN KEY (`parentId`) REFERENCES `core_customfields_select_option`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['201902141322'][] = "CREATE TABLE IF NOT EXISTS `core_change_user` (
  `userId` int(11) NOT NULL,
  `entityId` varchar(21) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`entityId`,`entityTypeId`),
  KEY `entityTypeId` (`entityTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT";


$updates['201902141322'][] = "ALTER TABLE `core_change_user`
  ADD CONSTRAINT `core_change_user_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_user_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;";

$updates['201902141322'][] = "ALTER TABLE `core_search` ADD `filter` VARCHAR(50) NULL DEFAULT NULL AFTER `keywords`;";
$updates['201902141322'][] = "ALTER TABLE `core_search` ADD INDEX(`filter`);";


$updates['201902141322'][] = "CREATE TABLE IF NOT EXISTS `core_change_user_modseq` (
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `highestModSeq` int(11) NOT NULL DEFAULT 0,
  `lowestModSeq` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userId`,`entityTypeId`),
  KEY `entityTypeId` (`entityTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";


$updates['201902141322'][] = "ALTER TABLE `core_change_user_modseq`
  ADD CONSTRAINT `core_change_user_modseq_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_user_modseq_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;";

$updates['201902141322'][] = "ALTER TABLE `core_customfields_field` CHANGE `databaseName` `databaseName` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;";

$updates['201902141322'][] = function() {
	$m = new \go\core\install\MigrateCustomFields63to64();
	$m->convertTypeNames();
};


$updates['201902141322'][] = "UPDATE core_module set sort_order = sort_order + 100 where package != 'core' or package is null;";

$updates['201902141322'][] = "ALTER TABLE `core_customfields_select_option` DROP `sortOrder`;";

//Is either renamed by legacy addressbook module or created here if address book module was not installed.
$updates['201902141322'][] = "CREATE TABLE IF NOT EXISTS `go_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `content` longblob NOT NULL,
  `extension` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


$updates['201902141322'][] = function() {
	
	$duplicates = go()->getDbConnection()->selectSingleValue('name')->from('core_group')->groupBy(["name"])->having('count(*) > 1');
	$count = -1;
	foreach($duplicates as $name) {		
		foreach(go()->getDbConnection()->select("id, name")->from('core_group')->where('name', '=', $name) as $record) {
			$count++;
			if($count > 0) {
				go()->getDbConnection()->update('core_group', ['name' => $record['name'] .' '.$count], ['id'=>$record['id']])->execute();
			}
		}		
	}
	
	go()->getDbConnection()->exec("ALTER TABLE `core_group` ADD UNIQUE(`name`);");	
};


$updates['201902141322'][] = function() {	
	$m = new \go\core\install\MigrateCustomFields63to64();
	$m->migrateEntity("User");	
};

$updates['201902141322'][] = "delete from core_setting where moduleId = 0;";
$updates['201902141322'][] = "update `core_entity` e inner join core_module m on m.id = e.moduleId  set e.moduleId = (select id from core_module where name = 'core' and package='core') where m.package='core';";
$updates['201902141322'][] = "update `core_setting` e inner join core_module m on m.id = e.moduleId  set e.moduleId = (select id from core_module where name = 'core' and package='core') where m.package='core';";
$updates['201902141322'][] = "update `core_auth_method` e inner join core_module m on m.id = e.moduleId  set e.moduleId = (select id from core_module where name = 'core' and package='core') where m.package='core';";
$updates['201902141322'][] = "delete from core_module where package = 'core' and name != 'core';";


$updates['201902141322'][] = "ALTER TABLE `core_change` DROP FOREIGN KEY `core_change_ibfk_2`;";
$updates['201902141322'][] = "ALTER TABLE `core_change` ADD CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";


$updates['201903011422'][] = "ALTER TABLE `core_change_user` CHANGE `entityId` `entityId` INT NOT NULL;";

$updates['201903111422'][] = "ALTER TABLE `core_entity` ADD `defaultAclId` INT NULL DEFAULT NULL AFTER `highestModSeq`;";
$updates['201903111422'][] = "ALTER TABLE `core_entity` ADD FOREIGN KEY (`defaultAclId`) REFERENCES `core_acl`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";



$updates['201903151726'][] = "CREATE TABLE `core_entity_filter` (
  `id` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `filter` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";


$updates['201903151726'][] = "ALTER TABLE `core_entity_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclid` (`aclId`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `entityTypeId` (`entityTypeId`);";


$updates['201903151726'][] = "ALTER TABLE `core_entity_filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";


$updates['201903151726'][] = "ALTER TABLE `core_entity_filter`
  ADD CONSTRAINT `core_entity_filter_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
	ADD CONSTRAINT `core_entity_filter_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;";
	
$updates['201901251344'][] = function() {
	go()->getDbConnection()->query("ALTER TABLE `core_search` CHANGE `keywords` `keywords` VARCHAR(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';");
	go()->getDbConnection()->query("ALTER TABLE `core_search` ADD INDEX(`keywords`);");
};

$updates['201903221350'][] = "ALTER TABLE `core_customfields_field_set` ADD `isTab` BOOLEAN NOT NULL DEFAULT FALSE AFTER `filter`;";
$updates['201904021341'][] = "ALTER TABLE `core_search` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;";
$updates['201904291603'][] = "ALTER TABLE `core_auth_password` DROP `digest`;";


$updates['201905101208'][] = "CREATE TABLE `core_smtp_account` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT 587,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `encryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `verifyCertificate` tinyint(1) NOT NULL DEFAULT 1,
  `fromName` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fromEmail` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201905101208'][] = "ALTER TABLE `core_smtp_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `aclId` (`aclId`);";


$updates['201905101208'][] = "ALTER TABLE `core_smtp_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";


$updates['201905101208'][] = "ALTER TABLE `core_smtp_account`
  ADD CONSTRAINT `core_smtp_account_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_smtp_account_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);";




$updates['201905101208'][] = "CREATE TABLE `core_email_template` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";

$updates['201905101208'][] = "CREATE TABLE `core_email_template_attachment` (
  `id` int(11) NOT NULL,
  `emailTemplateId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inline` tinyint(1) NOT NULL DEFAULT 0,
  `attachment` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";


$updates['201905101208'][] = "ALTER TABLE `core_email_template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `moduleId` (`moduleId`);";

$updates['201905101208'][] = "ALTER TABLE `core_email_template_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templateId` (`emailTemplateId`),
  ADD KEY `blobId` (`blobId`);";


$updates['201905101208'][] = "ALTER TABLE `core_email_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

  $updates['201905101208'][] = "ALTER TABLE `core_email_template_attachment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";


$updates['201905101208'][] = "ALTER TABLE `core_email_template`
  ADD CONSTRAINT `core_email_template_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `core_email_template_ibfk_2` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;";

$updates['201905101208'][] = "ALTER TABLE `core_email_template_attachment`
  ADD CONSTRAINT `core_email_template_attachment_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `core_email_template_attachment_ibfk_2` FOREIGN KEY (`emailTemplateId`) REFERENCES `core_email_template` (`id`) ON DELETE CASCADE;";


$updates['201905201227'][] = "ALTER TABLE `core_acl` ADD `entityTypeId` INT NULL DEFAULT NULL AFTER `modifiedAt`, ADD `entityId` INT NULL DEFAULT NULL AFTER `entityTypeId`;";
$dupates['201905201227'][] = "ALTER TABLE `core_acl` ADD FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
$updates['201905201227'][] = function() {
 
  $cf = new ClassFinder();
  $classes = $cf->findByParent(AclOwnerEntity::class);
  
  $mods = GO::modules()->getAll();
  foreach($mods as $m) {
    if($m->package == null && $m->isAvailable()) {
      $classes = array_merge($classes, array_map(function($c){return $c->getName();},$m->moduleManager->getModels()));
    }
  }


  foreach($classes as $cls) {    

    if($cls === Search::class || $cls === SearchCacheRecord::class) {
      continue;
    }

    if(is_subclass_of($cls, AclOwnerEntity::class)) {
      $type = $cls::entityType();
      $tables = $cls::getMapping()->getTables();
      $table = array_values($tables)[0]->getName();
      $colName = 'aclId';
    } else {
      if(!is_subclass_of($cls, ActiveRecord::class)) {
        continue;
      }
      $colName = $cls::model()->aclField();
      if(!$colName || ($cls::model()->isJoinedAclField && $cls != "GO\\Files\\Model\\Folder")) {
        continue;
      }
      $type = $cls::entityType();
      $table = $cls::model()->tableName();     
    }

    $stmt = go()->getDbConnection()->update(
      'core_acl', 
      [
        'acl.entityTypeId' => $type->getId(), 
        'acl.entityId' => new Expression('entity.id')],
      (new Query())
        ->tableAlias('acl')
        ->join($table, 'entity', 'entity.'.$colName.' = acl.id'));
  
   if(!$stmt->execute()) {
     throw new \Exception("Could not update ACL");
   }
  }
};

$updates['201906032000'][] = "ALTER TABLE `core_search` DROP INDEX `keywords`;";
$updates['201906032000'][] = "ALTER TABLE `core_search` CHANGE `keywords` `keywords` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';";
$updates['201906032000'][] = "ALTER TABLE `core_search` DROP INDEX `name`;";
$updates['201906032000'][] = "ALTER TABLE `core_search` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201906032000'][] = "";// "ALTER TABLE `core_search` ADD FULLTEXT( `name`, `keywords`);";


$updates['201906032000'][] = "ALTER TABLE `core_acl` DROP FOREIGN KEY `core_acl_ibfk_1`;";
$updates['201906032000'][] = "ALTER TABLE `core_acl` ADD CONSTRAINT `core_acl_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['201906211622'][] = function() {
	go()->getDbConnection()->query("ALTER TABLE `core_search` CHANGE `keywords` `keywords` VARCHAR(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
};

$updates['201906211622'][] = function() {
  EntityType::findByName('FieldSet')->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
};


$updates['201908300937'][] = function() {
  //Ensure all custom fields are correcty created in the databaase
  
  foreach(Field::find() as $field) {
    echo "Checking custom field " . $field->id ."\n";
    try {
      $field->save();
    } catch(\Exception $e) {
      echo "WARNING: Checking custom field failed: ". $e->getMessage() . "\n";
    }
  }
};


$updates['201908300937'][] = "DELETE FROM core_setting WHERE moduleId=0";

$updates['201910031702'][] = 'insert ignore into core_group (id, name) values (3, "Internal");';


$updates['201910101025'][] = "ALTER TABLE `core_change` ADD INDEX(`entityId`);";

$updates['201910101025'][] = "ALTER TABLE `core_search` CHANGE `keywords` `keywords` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";

$updates['201910101025'][] = "ALTER TABLE `core_search` DROP INDEX `name`;";
$updates['201910101025'][] = "ALTER TABLE `core_search` ADD INDEX(`keywords`);";

$updates['201911071025'][] = "ALTER TABLE `core_auth_token` CHANGE `expiresAt` `expiresAt` DATETIME NULL DEFAULT NULL;";


$updates['201911181430'][] = "update`core_user` set `displayName` = `username` where displayName = '' or displayName is null";
$updates['201911181430'][] = "ALTER TABLE `core_user` CHANGE `displayName` `displayName` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";

$updates['201911120000'][] = "ALTER TABLE `core_customfields_field` ADD `requiredCondition` varchar(255) NOT NULL DEFAULT '' AFTER `required`";


$updates['201912170000'][] = "ALTER TABLE `core_user` CHANGE `theme` `theme` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Paper';";
$updates['201912170000'][] = "update `core_user` set theme='Paper' where theme='Default';";


$updates['201912190000'][] = "CREATE TABLE `core_auth_allow_group` (
`id` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `ipPattern` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP Address. Wildcards can be used where * matches anything and ? matches exactly one character'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201912190000'][] = "ALTER TABLE `core_auth_allow_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupId` (`groupId`);";


$updates['201912190000'][] = "ALTER TABLE `core_auth_allow_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";


$updates['201912190000'][] = "ALTER TABLE `core_auth_allow_group`
  ADD CONSTRAINT `core_auth_allow_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;";

$updates['201912290000'][] = "ALTER TABLE `core_customfields_field` 
  ADD `conditionallyHidden` tinyint(1) NOT NULL DEFAULT '0' AFTER `requiredCondition`";

$updates['202001060000'][] = "ALTER TABLE `core_customfields_field` CHANGE `requiredCondition` `relatedFieldCondition` varchar(190) NOT NULL DEFAULT '' AFTER `required`";
$updates['202001060000'][] = "ALTER TABLE `core_customfields_field` ADD `conditionallyHidden` BOOLEAN NOT NULL DEFAULT FALSE AFTER `relatedFieldCondition`";
$updates['202001060000'][] = "ALTER TABLE `core_customfields_field` ADD `conditionallyRequired` BOOLEAN NOT NULL DEFAULT FALSE AFTER `conditionallyHidden`";

$updates['202002041223'][] = "ALTER TABLE `core_link` ADD INDEX(`fromEntityTypeId`);";
$updates['202002041223'][] = "ALTER TABLE `core_link` ADD INDEX(`fromId`);";
$updates['202002041223'][] = "ALTER TABLE `core_link` ADD INDEX(`toEntityTypeId`);";
$updates['202002041223'][] = "ALTER TABLE `core_link` ADD INDEX(`toId`);";

$updates['202004281031'][] = "UPDATE `core_customfields_field` SET `type` = 'FunctionField' WHERE `type` = 'Function';";
$updates['202004292101'][] ="delete FROM `go_holidays` WHERE region like 'en_uk';";


$updates['202006041416'][] = "CREATE TABLE `core_oauth_access_token` (
`identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expiryDateTime` datetime DEFAULT NULL,
  `userIdentifier` int(11) NOT NULL,
  `clientId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202006041416'][] = "CREATE TABLE `core_oauth_client` (
`id` int(11) NOT NULL,
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `isConfidential` tinyint(1) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirectUri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(128) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202006041416'][] = "ALTER TABLE `core_oauth_access_token`
  ADD PRIMARY KEY (`identifier`),
  ADD KEY `userIdentifier` (`userIdentifier`),
  ADD KEY `clientId` (`clientId`);";

$updates['202006041416'][] = "ALTER TABLE `core_oauth_client`
  ADD PRIMARY KEY (`id`);";

$updates['202006041416'][] = "ALTER TABLE `core_oauth_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$updates['202006041416'][] = "ALTER TABLE `core_oauth_access_token`
  ADD CONSTRAINT `core_oauth_access_token_ibfk_2` FOREIGN KEY (`userIdentifier`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_oauth_access_token_ibfk_3` FOREIGN KEY (`clientId`) REFERENCES `core_oauth_client` (`id`) ON DELETE CASCADE;";

$updates['202006191648'][] = "ALTER TABLE `core_customfields_field` ADD `hiddenInGrid` BOOLEAN NOT NULL DEFAULT TRUE AFTER `options`;";
$updates['202006191648'][] = "ALTER TABLE `core_entity_filter` ADD `type` ENUM('fixed','variable') NOT NULL DEFAULT 'fixed' AFTER `aclId`;";
$updates['202006191648'][] = "ALTER TABLE `core_entity_filter` CHANGE `filter` `filter` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;";
$updates['202007302016'][] = "ALTER TABLE `core_customfields_field_set` ADD `columns` TINYINT NOT NULL DEFAULT '2' AFTER `isTab`;";


$updates['202010231035'][] = "delete FROM `core_search` WHERE aclId not in (select id from core_acl);";
$updates['202010231035'][] = "ALTER TABLE `core_search` ADD  FOREIGN KEY (`aclId`) REFERENCES `core_acl`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['202010261619'][] = "ALTER TABLE `core_email_template` ROW_FORMAT=DYNAMIC;";
$updates['202010261619'][] = "ALTER TABLE `core_email_template_attachment` ROW_FORMAT=DYNAMIC;";

$updates['202010261619'][] = "ALTER TABLE `core_search` ROW_FORMAT=DYNAMIC;";
$updates['202010261619'][] = "ALTER TABLE `core_search` CHANGE `keywords` `keywords` VARCHAR(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";


$updates['202010261619'][] = "ALTER TABLE `core_acl` CHANGE `ownedBy` `ownedBy` INT(11) NULL;";

$updates['202010261619'][] = "update `core_acl` set ownedBy = 1 where ownedBy not in (select id from core_user);";

$updates['202010261619'][] = "ALTER TABLE `core_acl` ADD FOREIGN KEY (`ownedBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";

$updates['202010271619'][] = "UPDATE `core_cron_job` SET `expression` = '0 0 * * *' WHERE `core_cron_job`.`name` = 'GarbageCollection' and moduleId = (select id from core_module where name='core' and package='core')";

$updates['202011021149'][] = "ALTER TABLE core_customfields_select_option DROP FOREIGN KEY core_customfields_select_option_ibfk_2;";

$updates['202011021149'][] = "CREATE TABLE `core_oauth_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` int(11) NOT NULL,
  `identifier` varchar(128) COLLATE ascii_bin NOT NULL,
  `userIdentifier` int(11) NOT NULL,
  `expiryDateTime` datetime NOT NULL,
  `nonce` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['202012231410'][] = function() {
	$allEntities = EntityType::findAll();
	foreach($allEntities as $e) {
		foreach (Field::findByEntity($e->getId())->where(['type' => 'Text']) as $field) {
			//correct default null to default ""
			$field->forceAlterTable = true;
			$field->save();
		}
	}
};

$updates['202102111534'][] = ""; // Intentionally left blank

$updates['202102111534'][] = ""; // Intentionally left blank


$updates['202102111534'][] = "CREATE TABLE `core_alert` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entityTypeId` INT NOT NULL,
  `entityId` INT NOT NULL,
  `userId` INT NOT NULL,
  `triggerAt` DATETIME NOT NULL,
  `alertId` INT NOT NULL,
  `recurrenceId` VARCHAR(32) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `dk_alert_entityType_idx` (`entityTypeId` ASC),
  INDEX `fk_alert_user_idx` (`userId` ASC),
  CONSTRAINT `fk_alert_entityType`
    FOREIGN KEY (`entityTypeId`)
    REFERENCES `core_entity` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_alert_user`
    FOREIGN KEY (`userId`)
    REFERENCES `core_user` (`id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION);";



$updates['202102111534'][] = "TRUNCATE core_search";
$updates['202102111534'][] = "ALTER TABLE `core_search` DROP `keywords`";

$updates['202102111534'][] = "CREATE TABLE `core_search_word` (
`searchId` int(11) NOT NULL,
  `word` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202102111534'][] = "ALTER TABLE `core_search_word`
  ADD PRIMARY KEY (`word`,`searchId`),
  ADD KEY `searchId` (`searchId`);";


$updates['202102111534'][] = "ALTER TABLE `core_search_word`
  ADD CONSTRAINT `core_search_word_ibfk_1` FOREIGN KEY (`searchId`) REFERENCES `core_search` (`id`) ON DELETE CASCADE;";





$updates['202102111534'][] = function() {

	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("* * * * *");

	echo "NOTE: Search cache will be rebuilt by a scheduled task. This may take a lot of time.";
};


$updates['202102111534'][] = "CREATE TABLE `core_spreadsheet_export` (
`id` int(10) UNSIGNED NOT NULL,
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `columns` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202102111534'][] = "ALTER TABLE `core_spreadsheet_export`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `entityTypeId` (`entityTypeId`),
  ADD KEY `name` (`name`);";


$updates['202102111534'][] = "ALTER TABLE `core_spreadsheet_export`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;";


$updates['202102111534'][] = "ALTER TABLE `core_spreadsheet_export`
  ADD CONSTRAINT `core_spreadsheet_export_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_spreadsheet_export_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;";


$updates['202102111534'][] = "ALTER TABLE `core_search_word` ADD `drow` VARCHAR(100) NOT NULL AFTER `word`;";
$updates['202102111534'][] = "update `core_search_word` set drow = reverse (word)";
$updates['202102111534'][] = "ALTER TABLE `core_search_word` ADD INDEX(`drow`);";

$updates['202102111534'][] = "ALTER TABLE `core_customfields_select_option` ADD `enabled` BOOLEAN NOT NULL DEFAULT TRUE AFTER `text`;";

$updates['202102111534'][] = "update `core_customfields_select_option` set enabled=0, text = REPLACE(text,'** Missing ** ', '') where text like '** Missing **%';";

$updates['202102111534'][] = "CREATE TABLE `core_oauth_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` int(11) NOT NULL,
  `identifier` varchar(128) COLLATE ascii_bin NOT NULL,
  `userIdentifier` int(11) NOT NULL,
  `expiryDateTime` datetime NOT NULL,
  `nonce` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202102111534'][] = "";
$updates['202102111534'][] = "ALTER TABLE `go_templates` ADD COLUMN `filename` VARCHAR(100) NULL DEFAULT NULL AFTER `content`";


$updates['202102111534'][] = "delete from go_state where user_id not in (select id from core_user);";

$updates['202102111534'][] = "alter table go_state
	add constraint go_state_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";


$updates['202102111534'][] = "alter table core_auth_token change `passedMethods` `passedAuthenticators` varchar(190) null;";
$updates['202103091517'][] = "ALTER TABLE `core_customfields_select_option` ADD COLUMN `sortOrder` INT(11) UNSIGNED DEFAULT 0 AFTER `text`;";


$updates['202104061227'][] = "alter table core_user drop column popup_reminders;";

$updates['202104061227'][] = "alter table core_user drop column popup_emails;";

$updates['202104161227'][] = "ALTER TABLE core_search DROP INDEX `filter`;";
$updates['202104161227'][] = "create index core_search_entityTypeId_filter_modifiedAt_aclId_index
    on core_search (entityTypeId, filter, modifiedAt, aclId);";

$updates['202104161227'][] = "ALTER TABLE `core_search_word`
  DROP `drow`;";

$updates['202104161227'][] = "ALTER TABLE `core_search` DROP INDEX `entityTypeId`";

$updates['202104161227'][] = function() {

	go()->getDbConnection()->exec("truncate core_search_word");
	go()->getDbConnection()->exec("SET foreign_key_checks = 0;");
	go()->getDbConnection()->exec("truncate core_search");
	go()->getDbConnection()->exec("SET foreign_key_checks = 1;");

	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("* * * * *", true);

	echo "NOTE: Search cache will be rebuilt by a scheduled task. This may take a lot of time.";
};


$updates['202105041513'][] = "delete from core_module where name='log' and package is null";

$updates['202105041513'][] = "alter table core_user
	add homeDir varchar(190) not null;";

$updates['202105041513'][] = "update core_user set homeDir=concat('users/', username);";

$updates['202105041513'][] = "delete from core_acl_group where groupId = 1;";

$updates['202105041513'][] = "delete from core_module where name='timeregistration' and package is null";
$updates['202105041513'][] = "delete from core_module where name='search' and package is null";
$updates['202105041513'][] = "delete from core_module where name='phpcustomfield' and package is null";

$updates['202105041513'][] = "delete from core_module where name='ipwhitelist' and package is null";
$updates['202105041513'][] = "delete from core_module where name='wopicollabora' and package is null";
$updates['202105041513'][] = "delete from core_module where name='wopioffice365' and package is null";
$updates['202105041513'][] = "delete from core_module where name='tfs' and package is null";
$updates['202105041513'][] = "delete from core_module where name='phpbb3' and package is null";
$updates['202105041513'][] = "delete from core_module where name='voip' and package is null";
$updates['202105041513'][] = "delete from core_module where name='voippro' and package is null";

$updates['202105111132'][] = "ALTER TABLE `core_user` ADD COLUMN `confirmOnMove` TINYINT(1) NOT NULL DEFAULT 0 AFTER `homeDir`;";

$updates['202105251048'][] = 'alter table core_alert add sentAt DATETIME null;';
