<?php

use go\core\App;
use go\core\auth\ForcePasswordChange;
use go\core\orm\PrincipalTrait;
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
		/** @var array $config */
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

$updates['202105111132'][] = "alter table core_auth_token
	add platform varchar(190) null after userAgent;";

$updates['202105111132'][] = "alter table core_auth_token
	add browser varchar(190) null after platform;";

$updates['202107010929'][] = "alter table core_auth_token modify userAgent varchar(190) null;";
$updates['202107010929'][] = "alter table core_customfields_field modify relatedFieldCondition text default null;";
$updates['202109280842'][] = "alter table core_user modify username varchar(190) not null;";

// use photoBlobId in user profile as avatarId for User
$updates['202110211653'][] = "UPDATE core_user u JOIN addressbook_contact c ON c.goUserId = u.id SET u.avatarId = c.photoBlobId WHERE u.avatarId IS NULL AND c.photoBlobId IS NOT NULL;";

// Some older modules try to retrieve theme files from obsolete themes. This will trigger an error. Set the default theme
// to 'Paper'
$updates['202111151100'][] = "UPDATE `core_user` SET `theme`='Paper' WHERE `theme` NOT IN ('Paper', 'Dark', 'Compact');";

// recalculate because of substitute days
$updates['202112131205'][] ="delete FROM `go_holidays` WHERE region like 'en_uk';";
// recalculate because of substitute days (again)
$updates['202112131205'][] ="delete FROM `go_holidays` WHERE region like 'en_uk';";

$updates['202112131205'][] = "UPDATE `core_user` SET `theme`='Paper' WHERE `theme` NOT IN ('Paper', 'Dark', 'Compact');";




// MASTER UPDATES

$updates['202112131205'][] = "alter table core_alert
	add data text null;";

$updates['202112131205'][] = "CREATE TABLE `core_pdf_block` (
`id` bigint(20) UNSIGNED NOT NULL,
  `pdfTemplateId` bigint(20) UNSIGNED NOT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `align` enum('L','C','R','J') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'L',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['202112131205'][] = "CREATE TABLE `core_pdf_template` (
`id` bigint(20) UNSIGNED NOT NULL,
  `moduleId` int(11) NOT NULL,
  `key` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stationaryBlobId` binary(40) DEFAULT NULL,
  `landscape` tinyint(1) NOT NULL DEFAULT 0,
  `pageSize` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A4',
  `measureUnit` enum('mm','pt','cm','in') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mm',
  `marginTop` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginRight` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginBottom` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginLeft` decimal(19,4) NOT NULL DEFAULT 10.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_block`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `pdfTemplateId` (`pdfTemplateId`);";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `stationaryBlobId` (`stationaryBlobId`);";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_block`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_template`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_block`
  ADD CONSTRAINT `core_pdf_block_ibfk_1` FOREIGN KEY (`pdfTemplateId`) REFERENCES `core_pdf_template` (`id`) ON DELETE CASCADE;";

$updates['202112131205'][] = "ALTER TABLE `core_pdf_template`
  ADD CONSTRAINT `core_pdf_template_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_pdf_template_ibfk_2` FOREIGN KEY (`stationaryBlobId`) REFERENCES `core_blob` (`id`);";


$updates['202112131205'][] = "ALTER TABLE `core_email_template` ADD `key` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL AFTER `aclId`, ADD `language` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'en' AFTER `key`;";


$updates['202112131205'][] = "alter table core_alert change alertId tag varchar(50) null;";
$updates['202112131205'][] = "create unique index core_alert_entityTypeId_entityId_tag_userId_uindex
	on core_alert (entityTypeId, entityId, tag, userId);
";



$updates['202112131205'][] = "create table core_auth_remember_me
(
	id int auto_increment,
    token varchar(190) collate ascii_bin null,
    series varchar(190) collate ascii_bin null,
    userId int not null,
    expiresAt datetime null,
    constraint core_auth_remember_me_pk
        primary key (id)
);";

$updates['202112131205'][] = "create index core_auth_remember_me_series_index
    on core_auth_remember_me (series);";

$updates['202112131205'][] = "alter table core_auth_remember_me
    add constraint core_auth_remember_me_core_user_id_fk
        foreign key (userId) references core_user (id);";

$updates['202112131205'][] = "alter table core_auth_remember_me
    add `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL;";

$updates['202112131205'][] = "alter table core_auth_remember_me
    add `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL";

$updates['202112131205'][] = "alter table core_auth_remember_me
	add platform varchar(190) COLLATE utf8mb4_unicode_ci null after userAgent;";

$updates['202112131205'][] = "alter table core_auth_remember_me
	add browser varchar(190) COLLATE utf8mb4_unicode_ci null after platform;";


$updates['202112131205'][] = "alter table core_alert drop foreign key fk_alert_user;";

$updates['202112131205'][] = "alter table core_alert
	add constraint fk_alert_user
		foreign key (userId) references core_user (id)
			on delete cascade;";


$updates['202112131205'][] = "CREATE TABLE `core_permission` (
  `moduleId` INT NOT NULL,
  `groupId` INT NOT NULL,
  `rights` BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`moduleId`, `groupId`),
  INDEX `fk_permission_group_idx` (`groupId` ASC),
  CONSTRAINT `fk_permission_module`
      FOREIGN KEY (`moduleId`)
          REFERENCES `core_module` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION,
  CONSTRAINT `fk_permission_group`
      FOREIGN KEY (`groupId`)
          REFERENCES `core_group` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION);";

// migratie module acl permission to action permission
$updates['202112131205'][] = "INSERT IGNORE INTO core_permission (groupId, rights, moduleId) SELECT ag.groupId, IF(ag.level > 10, 1,0), m.id FROM core_acl_group ag 
join core_module m on ag.aclId = m.aclId;";
// projects2 has finance permissions
$updates['202112131205'][] = "UPDATE core_permission p
join core_acl_group ag on ag.groupId = p.groupId
join core_module m on ag.aclId = m.aclId
SET rights = IF(ag.level=10,0,IF(ag.level=40,1,3))
WHERE m.id = p.moduleId AND m.name = 'projects2';";


$updates['202112131205'][] = "alter table core_module drop foreign key acl;";
$updates['202112131205'][] = "alter table core_module drop column aclId;";


$updates['202112131205'][] = "alter table core_alert
	add sendMail boolean default false not null;";

$updates['202112131205'][] = "insert ignore into core_setting values((select id from core_module where name='core'), 'demoDataAsked', 1)";

$updates['202201101250'][] = 'update `core_entity` set clientName = name WHERE clientName is null';

$updates['202202141231'][] = "update core_blob set staleAt = now() where staleAt is null;";

$updates['202202141231'][] = "ALTER TABLE `core_smtp_account` ADD `maxMessagesPerMinute` SMALLINT UNSIGNED NOT NULL DEFAULT 0;";


$updates['202203181327'][] = "create index core_change_modSeq_entityTypeId_entityId_index
    on core_change (modSeq, entityTypeId, entityId);";

$updates['202203181327'][] = "create index core_change_user_modSeq_userId_entityTypeId_entityId_index
    on core_change_user (modSeq, userId, entityTypeId, entityId);
";


$updates['202203251058'][] = function() {

	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("0 0 * * *", true);
};

$updates['202203251058'][] = "create index core_change_modSeq_entityTypeId_entityId_index
    on core_change (modSeq, entityTypeId, entityId);";

$updates['202203251058'][] = "create index core_change_user_modSeq_userId_entityTypeId_entityId_index
    on core_change_user (modSeq, userId, entityTypeId, entityId);
";


$updates['202203310856'][] = function() {
	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("0 0 * * *", true);
};

$updates['202203310856'][] = function() {
	echo "Correcting invalid date field values (0000-00-00 => null)\n";
	$allEntities = EntityType::findAll();
	foreach($allEntities as $e) {
		foreach (Field::findByEntity($e->getId()) as $field) {
			try {
				if ($field->getDataType() instanceof \go\core\customfield\Date) {
					$table = $field->tableName();
					$stmt = go()->getDbConnection()->update($table, $field->databaseName . ' = null', $field->databaseName . " = '0000-00-00'");
					echo $stmt . "\n";
					$stmt->execute();
				} else if ($field->getDataType() instanceof \go\core\customfield\DateTime) {
					$table = $field->tableName();
					$stmt = go()->getDbConnection()->update($table, $field->databaseName . ' = null', $field->databaseName . " = '0000-00-00 00:00:00'");
					echo $stmt . "\n";
					$stmt->execute();
				}
			} catch (PDOException $e) {
				echo "PDOException: " . $e->getMessage() . "\n";
			}
		}
	}
};


$updates['202204051245'][] = "alter table core_search
    add `rebuild` bool default false not null;";


$updates['202204051245'][] = function() {

	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("0 0 * * *", true);

};


$updates['202204131216'][] = function() {

	//run build search cache on cron immediately. This job will deactivate itself.
	\go\core\cron\BuildSearchCache::install("* * * * *", true);

	echo "\n\n======\nNOTE: Search cache will be rebuilt.\n======\n\n";
};

$updates['202205101416'][] = function() {

	\go\core\fs\FileSystemObject::allowRootFolderDelete();

	go()->getDataFolder()->getFolder('cache2')->delete();
	go()->getDataFolder()->getFolder('clientscripts')->delete();
	go()->getDataFolder()->getFolder('cache')->delete();

	\go\core\fs\FileSystemObject::allowRootFolderDelete(false);
};


//create index core_entity_highestModSeq_index
//    on core_entity (highestModSeq);


$updates['202206031343'][] = "drop index clientName_2 on core_entity;";

$updates['202206031343'][] = "drop index moduleId_2 on core_entity;";


$updates['202207041200'][] = "alter table core_customfields_field_set
    add parentFieldSetId int null;";

$updates['202207041200'][] = "alter table core_customfields_field_set
    add constraint core_customfields_field_set_core_customfields_field_set_id_fk
        foreign key (parentFieldSetId) references core_customfields_field_set (id)
            on delete set null;";

$updates['202209291100'][] = "ALTER TABLE `core_customfields_select_option` ADD COLUMN `foregroundColor` VARCHAR(6) DEFAULT NULL AFTER `text`, 
    ADD COLUMN `backgroundColor` VARCHAR(6) DEFAULT NULL AFTER `foregroundColor`, 
    ADD COLUMN `renderMode` VARCHAR(20) DEFAULT NULL AFTER `backgroundColor`;";

$updates['202211071330'][] = "ALTER TABLE `core_user` ADD KEY `email` (`email`);";


$updates['202211251153'][] = "alter table core_auth_remember_me
    drop foreign key core_auth_remember_me_core_user_id_fk;";

$updates['202211251153'][] = "alter table core_auth_remember_me
    add constraint core_auth_remember_me_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade;";

$updates['202211291426'][] = "alter table `core_customfields_field` add column `filterable` BOOLEAN NOT NULL DEFAULT FALSE";

$updates['202212081208'][] = "UPDATE core_acl_group SET level = 30 WHERE level = 10 AND aclId in (SELECT aclId FROM core_customfields_field_set);";
$updates['202212081208'][] = "UPDATE core_acl_group SET level = 30 WHERE level = 10 AND aclId = (SELECT defaultAclId FROM core_entity WHERE name = 'FieldSet');";

$updates['202212090912'][] = function() {
	// make sure groups are visible to themselves
	$stmt = go()->getDbConnection()
		->insertIgnore(
			'core_acl_group',
			go()->getDbConnection()->select('aclId, id, "10"')->from("core_group"),
			['aclId', 'groupId', 'level']
		);

	$stmt->execute();
};

$updates['202212231031'][] = "alter table core_auth_token
    add `CSRFToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL after accessToken;";


$updates['202301091428'][] ="delete FROM `go_holidays` WHERE region like 'en_uk';";
$updates['202301121428'][] ="delete FROM `go_holidays` WHERE region like 'en_uk';";
$updates['202301231301'][] = 'delete from go_settings where name = "file_storage_usage"';
$updates['202301231301'][] = 'delete from go_settings where name = "database_usage"';
$updates['202301231301'][] = 'delete from go_settings where name = "mailbox_usage"';






// Start 6.7
$updates['202302211524'][] = "alter table core_pdf_block modify x int null;";

$updates['202302211524'][] = "alter table core_pdf_block modify y int null;";

$updates['202302211524'][] = "alter table core_pdf_block modify width int null;";

$updates['202302211524'][] = "alter table core_pdf_block modify height int null;";

$updates['202302211524'][] = "alter table core_pdf_template
	add `key` varchar(20) default null null after moduleId;";


$updates['202302211524'][] = "drop index name on core_email_template;";


$updates['202302211524'][] = "create unique index core_email_template_moduleId_key_uindex
    on core_email_template (moduleId, `key`);";


$updates["202302211524"][] = "TRUNCATE TABLE go_state"; //for fixed non resizable columns getting 100px width


$updates["202302211524"][] = "alter table core_pdf_template
    add logoBlobId binary(40) null after stationaryBlobId;";

$updates["202302211524"][] = "alter table core_pdf_template
    add constraint core_pdf_template_core_blob_id_fk
        foreign key (logoBlobId) references core_blob (id);";


$updates["202302211524"][] = "alter table core_email_template
    drop foreign key core_email_template_ibfk_1;";

$updates["202302211524"][] = "alter table core_email_template
    drop column aclId;";

$updates["202302211524"][] = "create index core_pdf_template_key_index
    on core_pdf_template (moduleId, `key`);";


$updates["202302211524"][] = "alter table core_email_template
    drop key core_email_template_moduleId_key_uindex;";

$updates["202302211524"][] = "create index core_email_template_moduleId_key_index
    on core_email_template (moduleId, `key`);";


$updates["202302211524"][] = "alter table core_change
    modify entityId varchar(100) collate ascii_bin not null;";


$updates['202302211524'][] = "alter table core_auth_remember_me
    drop foreign key core_auth_remember_me_core_user_id_fk;";

$updates['202302211524'][] = "alter table core_auth_remember_me
    add constraint core_auth_remember_me_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade;";


$updates['202302211524'][] = "create table core_import_mapping
(
	entityTypeId int                        null,
    checksum     char(32) collate ascii_bin null,
    mapping      text                       null,
    updateBy     varchar(100) default null  null,
    constraint core_import_mapping_core_entity_null_fk
        foreign key (entityTypeId) references core_entity (id)
            on delete cascade
)";

$updates['202302211524'][] = "alter table core_import_mapping
    add constraint core_import_mapping_pk
        primary key (entityTypeId, checksum);";

$updates['202302211524'][] = "drop index moduleId on core_pdf_template;";



$updates['202302211524'][] = "ALTER TABLE `core_import_mapping` DROP FOREIGN KEY `core_import_mapping_core_entity_null_fk`;";
$updates['202302211524'][] = "ALTER TABLE `core_import_mapping` 
ADD COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    ADD COLUMN `name` VARCHAR(120) NOT NULL DEFAULT '(unnamed)' AFTER `checksum`,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`id`);";

$updates['202302211524'][] = "ALTER TABLE `core_import_mapping` 
ADD CONSTRAINT `core_import_mapping_core_entity_null_fk`
  FOREIGN KEY (`entityTypeId`)
  REFERENCES `core_entity` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;";

$updates['202302211524'][] = "ALTER TABLE `core_customfields_field_set` ADD `collapseIfEmpty` BOOLEAN NOT NULL DEFAULT FALSE AFTER `isTab`;";

$updates['202302211524'][] = "";
$updates['202302211524'][] = "";
$updates['202302211524'][] = "";
$updates['202302211524'][] = "";
$updates['202302211524'][] = ""; //empty for fixing duplicates in file


$updates['202302211524'][] = "ALTER TABLE `core_pdf_template`
  ADD PRIMARY KEY (`id`)";


$updates['202302211524'][] = "ALTER TABLE `core_pdf_template`
  MODIFY `id` bigint unsigned auto_increment";

$updates['202303131003'][] = function() {
	$sql = "delete from core_acl_group where groupId = (select id from core_group where isUserGroupFor=1)";

	if(go()->getDatabase()->hasTable("em_accounts")) {
		$sql .= " AND aclId not IN (select acl_id from em_accounts)";
	}

	echo $sql ."\n";

	try {
		go()->getDbConnection()->exec($sql);
	}catch(Exception $e) {
		echo "Exception: " . $e->getMessage() ."\n";
	}
};

$updates['202303151524'][] = "ALTER TABLE `core_user` 
ADD COLUMN `themeColorScheme` ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'light' AFTER `theme`;";

$updates['202303151524'][] = "UPDATE `core_user` SET theme = 'Paper', themeColorScheme = 'dark' WHERE theme = 'Dark';";

$updates['202303311400'][] = "CREATE TABLE `go_template_group` (
   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`))
ENGINE = InnoDB;";

$updates['202303311400'][] = "ALTER TABLE `go_templates` 
ADD COLUMN `group_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `extension`,
ADD INDEX `fk_go_templates_go_template_group_idx` (`group_id` ASC);";

$updates['202303311400'][] = "ALTER TABLE `go_templates` 
ADD CONSTRAINT `fk_go_templates_go_template_group`
  FOREIGN KEY (`group_id`)
  REFERENCES `go_template_group` (`id`)
  ON DELETE SET NULL";

//ZPUSH-2FA

$updates['202303311400'][] = "ALTER TABLE `core_auth_token` 
ADD CONSTRAINT `fk_auth_token_user`
  FOREIGN KEY (`userId`)
  REFERENCES `core_user` (`id`)
  ON DELETE CASCADE;";

$updates['202303311400'][] = "CREATE TABLE `core_client` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `deviceId` VARCHAR(80) NOT NULL,
    `platform` VARCHAR(45) NOT NULL,
    `name` VARCHAR(80) NOT NULL,
    `version` VARCHAR(190) NOT NULL,
    `ip` VARCHAR(45) NOT NULL,
    `lastSeen` DATETIME NOT NULL,
    `createdAt` DATETIME NOT NULL,
    `status` ENUM('new', 'allowed', 'denied') NOT NULL DEFAULT 'new',
    `needResync` TINYINT(1) NULL NULL DEFAULT 0,
    `userId` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `core_client_core_user_id_fk`
        FOREIGN KEY (`userId`)
            REFERENCES `core_user` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB;";

$updates['202303311400'][] = "ALTER TABLE `core_auth_token` 
ADD COLUMN `clientId` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `passedAuthenticators`";

$updates['202303311400'][] = "ALTER TABLE `core_auth_remember_me` 
ADD COLUMN `clientId` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `userId`";

// create clients
$updates['202303311400'][] = "INSERT INTO core_client (`deviceId`, `ip`,`platform`, `name`, `version`, `lastSeen`, `createdAt`, `status`, `userId`) SELECT '-' as deviceId, remoteIpAddress, platform, browser as name, userAgent as version, max(lastSeen) as lastSeen, NOW() as createdAt, 'allowed' as status, userId
FROM (
	(
	SELECT remoteIpAddress, platform, browser, userAgent, max(lastActiveAt) as lastSeen, userId
		FROM `core_auth_token` `sub`
		WHERE  `sub`.`expiresAt` > NOW() OR `sub`.`expiresAt` IS NULL
		GROUP BY `sub`.`remoteIpAddress`, `sub`.`platform`, `sub`.`browser`, `sub`.`userId`
	
) UNION (
	SELECT DISTINCT remoteIpAddress, platform, browser, userAgent, max(DATE_SUB(expiresAt, INTERVAL 7 DAY)) as lastSeen, userId
		FROM `core_auth_remember_me` `t`
		WHERE  `t`.`expiresAt` > NOW()
		GROUP BY `t`.`remoteIpAddress`, `t`.`platform`, `t`.`browser`, `t`.`userId`
)
) `t`
GROUP BY `remoteIpAddress`, `platform`, `browser`, `userId`";

//set clientIds
$updates['202303311400'][] = "UPDATE `core_auth_token` t JOIN `core_client` c ON c.ip = t.remoteIpAddress AND c.userId = t.userId AND c.platform = t.platform AND c.name = t.browser SET t.clientId = c.id;";
$updates['202303311400'][] = "UPDATE `core_auth_remember_me` t JOIN `core_client` c ON c.ip = t.remoteIpAddress AND c.userId = t.userId AND c.platform = t.platform AND c.name = t.browser SET t.clientId = c.id;";
//remove tokens without client
$updates['202303311400'][] = "DELETE from `core_auth_token` WHERE clientId = 0;";
$updates['202303311400'][] = "DELETE FROM `core_auth_remember_me` WHERE clientId = 0;";

// drop old columns and add constraints
$updates['202303311400'][] = "ALTER TABLE `core_auth_remember_me` 
DROP COLUMN `browser`,
DROP COLUMN `platform`,
DROP COLUMN `userAgent`,
DROP COLUMN `remoteIpAddress`,
ADD INDEX `fk_core_auth_remember_me_core_client1_idx` (`clientId` ASC);";

$updates['202303311400'][] = "ALTER TABLE `core_auth_token` 
DROP COLUMN `browser`,
DROP COLUMN `platform`,
DROP COLUMN `userAgent`,
DROP COLUMN `remoteIpAddress`,
DROP COLUMN `lastActiveAt`,
ADD INDEX `fk_core_auth_token_core_client1_idx` (`clientId` ASC);";

$updates['202303311400'][] = "ALTER TABLE `core_auth_remember_me` 
ADD CONSTRAINT `fk_core_auth_remember_me_core_client1`
  FOREIGN KEY (`clientId`)
  REFERENCES `core_client` (`id`)
  ON DELETE CASCADE;";

$updates['202303311400'][] = "ALTER TABLE `core_auth_token`
ADD CONSTRAINT `fk_core_auth_token_core_client1`
  FOREIGN KEY (`clientId`)
  REFERENCES `core_client` (`id`)
  ON DELETE CASCADE;";


$updates['202306191435'][] = "alter table core_pdf_template
    add header text null;";

$updates['202306191435'][] = "alter table core_pdf_template
    add footer text null;";

$updates['202306191435'][] = "alter table core_pdf_template
    alter column marginTop set default 20;";

$updates['202306191435'][] = "alter table core_pdf_template
    alter column marginBottom set default 20.0000;";

$updates['202306191435'][] = "alter table core_pdf_template
    add headerX decimal(19, 4) default 0 null after header;";

$updates['202306191435'][] = "alter table core_pdf_template
    add headerY decimal(19, 4) default 10 null after headerX;";

$updates['202306191435'][] = "alter table core_pdf_template
    add footerX decimal(19, 4) default 0 null;";

$updates['202306191435'][] = "alter table core_pdf_template
    add footerY decimal(19, 4) default -12 null;";


$updates['202310301525'][] = "ALTER TABLE core_entity DROP FOREIGN KEY core_entity_ibfk_2;";
$updates['202310301526'][] = "ALTER TABLE core_entity ADD CONSTRAINT core_entity_ibfk_2 FOREIGN KEY (defaultAclId) REFERENCES core_acl(id) ON DELETE CASCADE ON UPDATE RESTRICT;";


$updates['202310301526'][] = "alter table core_cron_job
    drop key description;";

$updates['202310301526'][] = "create unique index name
    on core_cron_job (name);";

$updates['202310301526'][] = "alter table core_search
    modify filter varchar(190) null;";

$updates['202310301526'][] = "create index core_search_filter_index
    on core_search (filter);";


$updates['202403181539'][] = "delete from core_acl_group_changes;";

$updates['202403181539'][] = "alter table core_acl_group_changes change grantModSeq modSeq int not null;";

$updates['202403181539'][] = "alter table core_acl_group_changes  add granted boolean not null;";

$updates['202403181539'][] = "alter table core_acl_group_changes drop column revokeModSeq;";

$updates['202403181539'][] = "create index aclId2
    on core_acl_group_changes (aclId, groupId, modSeq);";

$updates['202403181539'][] = "drop index aclId on core_acl_group_changes;";

$updates['202403181539'][] = "";


$updates['202403181539'][] = "alter table core_user
    drop column last_password_change;";

$updates['202403181539'][] = "alter table core_user
    drop column force_password_change;";

$updates['202403181539'][] = "alter table core_user
    add passwordModifiedAt datetime null;";

$updates['202403181539'][] = "alter table core_user
    add forcePasswordChange boolean default false not null;";

$updates['202403181539'][] = function() {
	ForcePasswordChange::register();
};


$updates['202403181539'][] = function() {
	try {
		echo "Installing `;-- Have I been Pwned module";
		\go\modules\community\pwned\Module::get()->install();

	} catch(Throwable $e) {
		echo "ERROR: " . $e->getMessage();
	}
};

$updates['202403181539'][] = "alter table core_acl_group_changes
    drop foreign key `group`;";

$updates['202403181539'][] = "alter table core_acl_group_changes
    add constraint `group`
        foreign key (groupId) references core_group (id)
            on delete cascade on update cascade";



$updates['202405171539'][] = "alter table core_module
    add shadowAclId int null;";

$updates['202405171539'][] = "alter table core_module
    add constraint core_module_core_acl_id_fk
        foreign key (shadowAclId) references core_acl (id);";


$updates['202405171539'][] = "alter table core_acl
    drop foreign key core_acl_ibfk_1;";

$updates['202405171539'][] = "alter table core_acl
    add constraint core_acl_ibfk_1
        foreign key (entityTypeId) references core_entity (id) on delete set null;";


$updates['202409160946'][] = "alter table core_import_mapping
    add dateFormat varchar(20) null;";

$updates['202409160946'][] = "alter table core_import_mapping
    add timeFormat varchar(20) null;";

$updates['202409160946'][] = "alter table core_import_mapping
    add decimalSeparator char null;";

$updates['202409160946'][] = "alter table core_import_mapping
    add thousandsSeparator char null;";



$updates['202410310946'][] = "alter table core_acl_group
    drop foreign key core_acl_group_ibfk_2;";

$updates['202410310946'][] = "alter table core_acl_group
    add constraint core_acl_group_ibfk_2
        foreign key (aclId) references core_acl (id)
            on update cascade on delete cascade;";


$updates['202410310946'][] = "alter table core_acl_group_changes
    drop foreign key `all`;";

$updates['202410310946'][] = "alter table core_acl_group_changes
    add constraint `all`
        foreign key (aclId) references core_acl (id)
            on update cascade on delete cascade;";

$updates['202410310946'][] = "alter table core_search
    drop foreign key core_search_ibfk_2;";

$updates['202410310946'][] = "alter table core_search
    add constraint core_search_ibfk_2
        foreign key (aclId) references core_acl (id)
            on update cascade on delete cascade;";


$updates['202411221010'][] = "alter table core_user
    add enableSendShortcut boolean default true not null;";

$updates['202412090921'][] = "alter table core_auth_token
    add constraint core_auth_token_pk
        unique (accessToken);
";

$updates['202412090921'][] = "drop index accessToken on core_auth_token;";



# ------ 6.9 ---------------

$updates['202412090921'][] = "CREATE TABLE `core_principal`(
   `id` VARCHAR(60) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`email` VARCHAR(255) NULL,
	`type` ENUM('individual', 'group', 'resource', 'location', 'other'),
	`description` VARCHAR(255) NOT NULL,
	`timeZone` VARCHAR(129) NULL,
	`entityTypeId` INT NOT NULL,
	`avatarId` BINARY(40) NULL,
	`entityId` INT UNSIGNED NOT NULL,
	`aclId` INT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `index_core_entity_id` ( `entityTypeId` ),
	INDEX `index_core_blob_id` ( `avatarId` ),
	CONSTRAINT `fk_core_principal_core_acl1`
		FOREIGN KEY (`aclId`)
			REFERENCES `core_acl` (`id`),
	CONSTRAINT `lnk_core_entity_core_principal` FOREIGN KEY ( `entityTypeId` )
		REFERENCES `core_entity`( `id` )
		ON DELETE Cascade
		ON UPDATE Cascade,
	CONSTRAINT `lnk_core_blob_core_principal` FOREIGN KEY ( `avatarId` )
		REFERENCES `core_blob`( `id` )
		ON DELETE Restrict
		ON UPDATE No Action
) ENGINE = InnoDB;";

$updates['202412090921'][] = function() {

	go()->getDbConnection()->exec('replace into core_principal (id, name, email, type, description, timeZone, entityTypeId, avatarId, entityId, aclId)
SELECT u.id, u.displayName, u.email, "individual", u.username, u.timezone, (select id from core_entity where name="User"), u.avatarId, u.id, g.aclId from core_user u
inner join core_group g on g.isUserGroupFor = u.id;');


	if(\go\core\model\Module::isInstalled("community", "addressbook")) {
		go()->getDbConnection()->exec('replace into core_principal (id, name, email, type, description, timeZone, entityTypeId, avatarId, entityId, aclId)
SELECT concat("contact:", u.id), u.name, e.email, "individual", if(u.isOrganization, "Organization", "Contact"), null, (select id from core_entity where name="Contact"), u.photoBlobId, u.id, a.aclId from addressbook_contact u
inner join addressbook_addressbook a on a.id = u.addressBookId
inner join addressbook_email_address e on e.contactId = u.id
group by u.id;');
	}

};

$updates['202412090921'][] = "alter table core_module drop key name;";
$updates['202412090921'][] = "alter table core_module add constraint name unique (name, package);";
$updates['202412090921'][] = "ALTER TABLE `core_alert` ADD COLUMN `staleAt` DATETIME NULL AFTER `triggerAt`;";