<?php

use go\core\App;
use go\core\db\Query;

App::get()->getCache()->flush(false);

App::get()->getDatabase()->setUtf8();

$qs[] = "DROP TRIGGER IF EXISTS `Create ACL`;";
$qs[] = "CREATE TRIGGER `__test__` BEFORE INSERT ON `go_users` FOR EACH ROW set NEW.lastlogin = NOW();";
$qs[] = "DROP TRIGGER IF EXISTS `__test__`;";

$qs[] = "DROP TABLE IF EXISTS `go_mail_counter`;";
$qs[] = function () {
	$stmt = GO()->getDbConnection()->query("SHOW TABLE STATUS");	
	
	foreach($stmt as $record){
		if($record['Engine'] == null) {

			//skip views
			continue;
		}
		
		if($record['Engine'] != 'InnoDB' && $record["Name"] != 'fs_filesearch' && $record["Name"] != 'cms_files') {
			echo "Converting ". $record["Name"] . " to InnoDB\n";
			flush();
			$sql = "ALTER TABLE `".$record["Name"]."` ENGINE=InnoDB;";
			GO()->getDbConnection()->query($sql);	
		}
		
		if($record["Collation"] != "utf8mb4_unicode_ci" ) {
			echo "Converting ". $record["Name"] . " to utf8mb4\n";
			flush();
			
			if($record['Name'] === 'em_links') {
				GO()->getDbConnection()->query("ALTER TABLE `em_links` DROP INDEX `uid`");
			}			
			$sql = "ALTER TABLE `".$record["Name"]."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
			GO()->getDbConnection()->query($sql);	
			
			if($record['Name'] === 'em_links') {
				GO()->getDbConnection()->query("ALTER TABLE `em_links` CHANGE `uid` `uid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';");
				GO()->getDbConnection()->query("ALTER TABLE `em_links` ADD INDEX(`uid`);");
			}

		}	
	}
};

$qs[] = "UPDATE go_settings SET value=0 where name = 'version';";
$qs[] = "ALTER TABLE `go_modules` ADD `package` VARCHAR(100) NULL DEFAULT NULL AFTER `id`;";
$qs[] = "ALTER TABLE `go_modules` CHANGE `id` `id` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';";

$qs[] = "RENAME TABLE `go_modules` TO `core_module`;";
$qs[] = "ALTER TABLE `core_module` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

$qs[] = "ALTER TABLE `core_module` CHANGE `id` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';";
$qs[] = "ALTER TABLE `core_module` DROP PRIMARY KEY;";
$qs[] = "ALTER TABLE `core_module` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";

$qs[] = "ALTER TABLE `core_module` ADD UNIQUE(`name`);";
$qs[] = "ALTER TABLE `core_module` CHANGE `acl_id` `aclId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `core_module` ADD INDEX(`aclId`);";
$qs[] = "ALTER TABLE `core_module` ADD CONSTRAINT `acl` FOREIGN KEY (`aclId`) REFERENCES `go_acl_items`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";


$qs[] = "CREATE TABLE `core_auth_token` (
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `expiresAt` datetime NOT NULL,
  `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$qs[] = "CREATE TABLE `core_state` (
  `entityClass` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `highestModSeq` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$qs[] = "ALTER TABLE `core_auth_token`
  ADD PRIMARY KEY (`accessToken`),
  ADD KEY `userId` (`userId`);";

$qs[] = "ALTER TABLE `core_state`
  ADD PRIMARY KEY (`entityClass`);";



$qs[] = "ALTER TABLE `go_groups` CHANGE `user_id` `createdBy` INT(11) NOT NULL DEFAULT '0';";
$qs[] = "ALTER TABLE `go_groups` CHANGE `acl_id` `aclId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_groups` DROP `admin_only`;";
$qs[] = "ALTER TABLE `go_groups` ADD `isUserGroupFor` INT NULL DEFAULT NULL AFTER `aclId`, ADD INDEX (`isUserGroupFor`);";
$qs[] = "ALTER TABLE `go_acl` DROP INDEX `acl_id_2`;";
$qs[] = "ALTER TABLE `go_acl` DROP INDEX `acl_id`;";
$qs[] = "ALTER TABLE `go_acl` ADD INDEX(`level`);";
$qs[] = "ALTER TABLE `go_groups` CHANGE `aclId` `aclId` INT(11) NULL DEFAULT NULL;";




$qs[] = "ALTER TABLE `go_acl_items` CHANGE `user_id` `ownedBy` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_acl_items` ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `mtime`;";
$qs[] = "update `go_acl_items` set modifiedAt = from_unixtime(mtime);";

$qs[] = "ALTER TABLE `go_acl_items` DROP `mtime`;";

$qs[] = "delete from go_acl where user_id > 0 AND user_id not in (select id from go_users)";
$qs[] = "delete from go_acl where group_id > 0 AND group_id not in (select id from go_groups)";

$qs[] = "ALTER TABLE `go_acl` CHANGE `group_id` `groupId` INT(11) NOT NULL DEFAULT '0';";

$qs[] = "ALTER TABLE `go_acl` CHANGE `acl_id` `aclId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_groups` CHANGE `name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";

$qs[] = "CREATE TRIGGER `Create ACL` BEFORE INSERT ON `go_groups` FOR EACH ROW BEGIN INSERT INTO `go_acl_items` (`ownedBy`, `description`) VALUES (NEW.createdBy, 'go_groups.aclId'); set NEW.aclId = (SELECT last_insert_id()); END";
$qs[] = "insert into go_groups (name, createdBy, isUserGroupFor) select username,id,id from go_users;";
$qs[] = "DROP TRIGGER `Create ACL`;";


$qs[] = "ALTER TABLE `go_acl` DROP PRIMARY KEY;";
$qs[] = "insert into `go_acl` (groupId, aclId, level) select id,aclId,50 from go_groups where isUserGroupFor is not null;";
$qs[] = "insert into `go_acl` (groupId, aclId, level) select '1',aclId,50 from go_groups where isUserGroupFor is not null;";
$qs[] = "ALTER TABLE `go_groups` CHANGE `createdBy` `createdBy` INT(11) NOT NULL;";
$qs[] = "update `go_acl` a set groupId = (select id from go_groups where isUserGroupFor = a.user_id) where user_id > 0; ";
$qs[] = "ALTER TABLE `go_acl` DROP `user_id`;";
$qs[] = "delete from go_acl where aclId not in (select id from go_acl_items);";
$qs[] = "ALTER TABLE `go_acl` ADD PRIMARY KEY( `aclId`, `groupId`);";
$qs[] = "ALTER TABLE `go_users_groups` CHANGE `group_id` `groupId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_users_groups` CHANGE `user_id` `userId` INT(11) NOT NULL;";
$qs[] = "insert into `go_users_groups` select id,isUserGroupFor from go_groups where isUserGroupFor is not null;";
$qs[] = "DROP TABLE go_db_sequence;";
$qs[] = "ALTER TABLE `go_acl_items` CHANGE `description` `usedIn` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$qs[] = "delete from go_users_groups where userId not in(select id from go_users);";
$qs[] = "delete from go_users_groups where groupId not in(select id from go_groups);";
$qs[] = "delete from go_acl where groupId not in(select id from go_groups);";
$qs[] = "ALTER TABLE `go_acl` ADD FOREIGN KEY (`groupId`) REFERENCES `go_groups`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `go_users_groups` ADD FOREIGN KEY (`groupId`) REFERENCES `go_groups`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; ALTER TABLE `go_users_groups` ADD FOREIGN KEY (`userId`) REFERENCES `go_users`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `go_groups` ADD FOREIGN KEY (`aclId`) REFERENCES `go_acl_items`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `go_groups` ADD FOREIGN KEY (`isUserGroupFor`) REFERENCES `go_users`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `go_acl` ADD FOREIGN KEY (`aclId`) REFERENCES `go_acl_items`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$qs[] = "ALTER TABLE `go_users` DROP `acl_id`;";
$qs[] = "RENAME TABLE `go_acl` TO `core_acl_group`;";
$qs[] = "RENAME TABLE `go_acl_items` TO `core_acl`;";
$qs[] = "CREATE TABLE `core_acl_group_changes` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$qs[] = "ALTER TABLE `core_acl_group_changes`
  ADD PRIMARY KEY (`aclId`,`groupId`);";

$qs[] = "insert `core_acl_group_changes` select aclId, groupId, 1, null from core_acl_group";


$qs[] = "ALTER TABLE `core_acl_group_changes` ADD FOREIGN KEY (`aclId`) REFERENCES `core_acl`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; ALTER TABLE `core_acl_group_changes` ADD FOREIGN KEY (`groupId`) REFERENCES `go_groups`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$qs[] = 'RENAME TABLE `cf_categories` TO `core_customfields_field_set`;';
$qs[] = 'ALTER TABLE `core_customfields_field_set` CHANGE `extends_model` `extendsModel` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;';
$qs[] = 'ALTER TABLE `core_customfields_field_set` CHANGE `acl_id` `aclId` INT(11) NOT NULL;';
$qs[] = 'ALTER TABLE `core_customfields_field_set` CHANGE `sort_index` `sortOrder` TINYINT(4) NOT NULL DEFAULT \'0\';';
// Next query may fail but some databases are not successfully upgraded in 2014
$qs[] = 'ALTER TABLE `cf_fields` ADD `prefix` VARCHAR( 32 ) NOT NULL DEFAULT \'\', ADD `suffix` VARCHAR( 32 ) NOT NULL DEFAULT \'\';';
$qs[] = 'RENAME TABLE `cf_fields` TO `core_customfields_field`;';
$qs[] = 'ALTER TABLE `core_customfields_field` CHANGE `category_id` `fieldSetId` INT(11) NOT NULL;';
$qs[] = 'ALTER TABLE `core_customfields_field` CHANGE `sort_index` `sortOrder` INT(11) NOT NULL DEFAULT \'0\';';
$qs[] = 'ALTER TABLE `core_customfields_field` CHANGE `validation_regex` `validationRegex` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;';
$qs[] = 'ALTER TABLE `core_customfields_field` CHANGE `validationRegex` `validationRegex` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;';
$qs[] = 'ALTER TABLE `core_customfields_field` ADD `options` TEXT NULL DEFAULT NULL AFTER `suffix`;';

$qs[] = function() {
	$stmt = (new Query)
					->select('*')
					->from('core_customfields_field')
					->execute();

	while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$jsonData = [];

		if (!empty($record['max_length'])) {
			$jsonData['maxLength'] = (int) $record['max_length'];
		}

		if (!empty($record['function'])) {
			$jsonData['function'] = $record['function'];
		}
		if (!empty($record['validation_regex'])) {
			$jsonData['validationRegex'] = $record['validation_regex'];
		}
		if (!empty($record['multiselect'])) {
			$jsonData['multiselect'] = (bool) $record['multiselect'];
		}
		if (!empty($record['treemaster_field_id'])) {
			$jsonData['treeMasterFieldId'] = (int) $record['treemaster_field_id'];
		}
		if (!empty($record['nesting_level'])) {
			$jsonData['nestingLevel'] = (int) $record['nesting_level'];
		}
		if (!empty($record['height'])) {
			$jsonData['height'] = (int) $record['height'];
		}
		if (!empty($record['number_decimals'])) {
			$jsonData['numberDecimals'] = (int) $record['number_decimals'];
		}
		if (!empty($record['addressbook_ids'])) {
			$jsonData['addressBookIds'] = $record['addressbook_ids'];
		}
		if (!empty($record['extra_options'])) {
			$jsonData['extraOptions'] = $record['extra_options'];
		}


		App::get()->getDbConnection()
						->update('core_customfields_field', ['options' => json_encode($jsonData)], ['id' => $record['id']])
						->execute();
	}
};

$qs[] = 'ALTER TABLE `core_customfields_field`
  DROP `multiselect`,
  DROP `max`,
  DROP `nesting_level`,
  DROP `treemaster_field_id`,
  DROP `height`,
  DROP `number_decimals`,
	DROP `function`,
  DROP `max_length`,
  DROP `extra_options`;';

$qs[] = 'ALTER TABLE `core_customfields_field` DROP `addressbook_ids`;';
$qs[] = 'ALTER TABLE `core_customfields_field` DROP `validationRegex`;';

$qs[] = 'ALTER TABLE `core_customfields_field` ADD `databaseName` VARCHAR(190) NOT NULL AFTER `name`;';
$qs[] = 'UPDATE `core_customfields_field` set databaseName = concat("col_", id);';

//Remove old projects entity because it will lead to a duplicate key error
$qs[] = 'DELETE FROM `go_model_types` WHERE model_name="GO\\\\Projects\\\\Model\\\\Project";';


$qs[] = 'RENAME TABLE `go_model_types` TO `core_entity`;';
$qs[] = 'ALTER TABLE `core_entity` CHANGE `model_name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;';
$qs[] = 'ALTER TABLE `core_entity` ADD `moduleId` INT NULL DEFAULT NULL AFTER `id`, ADD INDEX (`moduleId`);';
$qs[] = 'ALTER TABLE `core_entity` ADD INDEX(`name`);';

$qs[] = "ALTER TABLE `core_entity`  ADD `clientName` VARCHAR(190) NULL DEFAULT NULL;";
$qs[] = "ALTER TABLE `core_entity` ADD UNIQUE(`clientName`);";



$qs[] = "insert into core_entity (name) select distinct extendsModel from core_customfields_field_set where extendsModel not in (select name from core_entity)";
$qs[] = 'ALTER TABLE `core_customfields_field_set` ADD `entityId` INT NOT NULL AFTER `id`, ADD INDEX (`entityId`);';


//deduplicate core_entity
$qs[] = "DELETE t1 FROM core_entity t1 INNER JOIN core_entity t2 WHERE t1.id > t2.id AND t1.name = t2.name;";
$qs[] = 'update `core_customfields_field_set` set entityId = (select id from core_entity where name = extendsModel);';
$qs[] = 'ALTER TABLE `core_entity` DROP INDEX `name`;';


//will set moduleId and convert class name to short name
$qs[] = function() {
	$stmt = (new Query)
					->select('*')
					->from('core_entity')
					->execute();

	while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {

		//this could lead to a "clientName" column having a null value. Which lead to ticket #201816451
		if (method_exists($record['name'], 'getModule')) {
			$moduleName = $record['name']::getModule();
			$clientName = $record['name']::getClientName();
		} else {
			// Search for module name based on the name column(namespace) in the core_entity table (Ticket: #201817072)
			// Needed for modules that are already refactored and where the activerecord does not exist anymore (Notes)
			$nameParts = explode('\\',$record['name']);
			if(!isset($nameParts[1])){
				continue;
			}
			$moduleName = strtolower($nameParts[1]);
			$clientName = array_pop($nameParts);
		}
			
		$module = (new Query)
										->select('id')->from('core_module')->where(['name' => $moduleName])
										->execute()->fetch();

		$shortName = substr($record['name'], strrpos($record['name'], '\\') + 1);
		
		//Conflicting custom modules. Append the namespace part.
		$existing = (new Query())->select()->from("core_entity")->where(['clientName' => $clientName])->single();
		if($existing) {
			$clientName = ucfirst($moduleName) .  $clientName;
		}
		
		App::get()->getDbConnection()
						->update('core_entity', ['moduleId' => $module ? $module['id'] : null, 'name' => $shortName, 'clientName' => $clientName], ['id' => $record['id']])
						->execute();
	}
};
//UPDATE core_entity SET name = CONCAT(UCASE(LEFT(name, 1)), SUBSTRING(name, 2))


$qs[] = 'ALTER TABLE `core_entity` ADD FOREIGN KEY (`moduleId`) REFERENCES `core_module`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';


$qs[] = 'ALTER TABLE `core_customfields_field_set` DROP `extendsModel`;';




$qs[] = "ALTER TABLE `core_auth_token` ADD `passedMethods` VARCHAR(190) NULL DEFAULT NULL AFTER `userAgent`;";

$qs[] = "TRUNCATE `core_auth_token`;";
$qs[] = "ALTER TABLE `core_auth_token` ADD `loginToken` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL FIRST;";
$qs[] = "ALTER TABLE `core_auth_token` DROP PRIMARY KEY;";
$qs[] = "ALTER TABLE `core_auth_token` ADD PRIMARY KEY(`loginToken`);";
$qs[] = "ALTER TABLE `core_auth_token` ADD INDEX(`accessToken`);";
$qs[] = "ALTER TABLE `core_auth_token` CHANGE `accessToken` `accessToken` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";

$qs[] = "CREATE TABLE `core_auth_method` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
	`moduleId` INT NOT NULL,
  `sortOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$qs[] = "ALTER TABLE `core_auth_method`
  ADD PRIMARY KEY (`id`);";

$qs[] = "CREATE TABLE `core_auth_password` (
  `userId` int(11) NOT NULL,
  `password` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
	`digest` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$qs[] = "ALTER TABLE `core_auth_password`
  ADD PRIMARY KEY (`userId`);";

$qs[] = "INSERT INTO `core_auth_password` (`userId`, `password`, `digest`) SELECT `id`,`password`,`digest` from `go_users`;";

$qs[] = "ALTER TABLE `go_users` DROP `password`;";
$qs[] = "ALTER TABLE `go_users` DROP `password_type`;";
$qs[] = "ALTER TABLE `go_users` DROP `digest`;";

$qs[] = "INSERT INTO `core_auth_method` (`id`, `moduleId`, `sortOrder`) select 'password', id, '1' from core_module where name='users';";

$qs[] = "ALTER TABLE `core_auth_method` ADD INDEX(`moduleId`)";
$qs[] = "ALTER TABLE `core_auth_method` ADD FOREIGN KEY (`moduleId`) REFERENCES `core_module`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$qs[] = "CREATE TABLE `core_setting` (
  `moduleId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$qs[] = "ALTER TABLE `core_setting`
  ADD PRIMARY KEY (`moduleId`,`name`);";

$qs[] = "ALTER TABLE `core_setting` ADD CONSTRAINT `module` FOREIGN KEY (`moduleId`) REFERENCES `core_module`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


////customfields module
//$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
//$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
//$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'customfields','core','0' from core_acl;";
//
////users module (not refactored yet but needed for user entity)
//$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
//$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
//$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'users','core','0' from core_acl;";
//
////groups module
//$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
//$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
//$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'groups','core','0' from core_acl;";

//links module
$qs[] = "DELETE FROM core_module where name='links';"; //might be installed in some installations
$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'links','core','0' from core_acl;";

//core module
$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'core','core','0' from core_acl;";

$qs[] = "update core_module set package='core' where name in ('users','groups','search', 'customfields', 'modules')";
//search module
//$qs[] = "INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`) VALUES (NULL, '1', 'core_module.aclId', NOW());";
//$qs[] = "insert into core_acl_group (aclId,groupId, level) select max(id),'1','50' from core_acl;";
//$qs[] = "insert into core_module (aclId,name,package,version) select max(id),'search','core','0' from core_acl;";


$qs[] = "update `core_entity` set moduleId = (select id from core_module where name='users' AND package='core') where name = 'GO\\\\Base\\\\Model\\\\User';";

$qs[] = "ALTER TABLE `core_customfields_field` ADD FOREIGN KEY (`fieldSetId`) REFERENCES `core_customfields_field_set`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `core_customfields_field_set` ADD FOREIGN KEY (`entityId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$qs[] = "ALTER TABLE `core_customfields_field_set` ADD FOREIGN KEY (`aclId`) REFERENCES `core_acl`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

$qs[] = "ALTER TABLE `core_customfields_field` ADD `modSeq` INT DEFAULT NULL AFTER `fieldSetId`, ADD `createdAt` DATETIME DEFAULT NULL AFTER `modSeq`, ADD `modifiedAt` DATETIME DEFAULT NULL AFTER `createdAt`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `modifiedAt`, ADD INDEX (`modSeq`);";
$qs[] = "update core_customfields_field set modifiedAt = now(), createdAt = now();";
$qs[] = "ALTER TABLE `core_customfields_field_set` ADD `modSeq` INT DEFAULT NULL AFTER `id`, ADD `createdAt` DATETIME DEFAULT NULL AFTER `modSeq`, ADD `modifiedAt` DATETIME DEFAULT NULL AFTER `createdAt`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `modifiedAt`, ADD INDEX (`modSeq`);";
$qs[] = "update core_customfields_field_set set modifiedAt = now(), createdAt = now();";

$qs[] = "ALTER TABLE `go_users` ADD `displayName` VARCHAR(190) DEFAULT '' AFTER `username`;";
$qs[] = "UPDATE `go_users` SET `displayName`= REPLACE(CONCAT_WS(' ',`first_name`,`middle_name`,`last_name`),'  ',' ');";
$qs[] = "ALTER TABLE `go_users` CHANGE `recovery_email` `recoveryEmail` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
$qs[] = "ALTER TABLE `go_users` DROP `first_name`, DROP `middle_name`, DROP `last_name`;";

$qs[] = "ALTER TABLE `go_search_cache` DROP `model_name`;";
$qs[] = "ALTER TABLE `go_search_cache` CHANGE `model_type_id` `entityTypeId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_search_cache` CHANGE `model_id` `entityId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_search_cache` DROP `user_id`;";
$qs[] = "ALTER TABLE `go_search_cache` DROP `type`;";
$qs[] = "ALTER TABLE `go_search_cache` CHANGE `acl_id` `aclId` INT(11) NOT NULL;";
$qs[] = "ALTER TABLE `go_search_cache` ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `mtime`;";
$qs[] = "update go_search_cache set modifiedAt = from_unixtime(mtime);";
$qs[] = "ALTER TABLE `go_search_cache` DROP `mtime`;";
$qs[] = "ALTER TABLE `go_search_cache` ADD `moduleId` INT NULL DEFAULT NULL AFTER `module`, ADD INDEX (`moduleId`);";
$qs[] = "ALTER TABLE `go_search_cache` CHANGE `module` `module` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";

$qs[] = "ALTER TABLE `core_module` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';";

$qs[] = "update go_search_cache set moduleId = (select id from core_module where name = go_search_cache.module);";
$qs[] = "update go_search_cache set moduleId = (select id from core_module where name = 'users' and package='core') where module='base';";
$qs[] = "ALTER TABLE `go_search_cache` DROP `module`;";
$qs[] = "RENAME TABLE `go_search_cache` TO `core_search`;";
$qs[] = "ALTER TABLE `core_search` DROP PRIMARY KEY";
$qs[] = "ALTER TABLE `core_search` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);";
$qs[] = "ALTER TABLE `core_search` ADD UNIQUE( `entityId`, `entityTypeId`);";





$qs[] = "CREATE TABLE `core_link` (
  `id` int(11) NOT NULL,
  `fromEntityTypeId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toEntityTypeId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
	`folderId` INT NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$qs[] = "ALTER TABLE `core_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fromEntityId` (`fromEntityTypeId`,`fromId`,`toEntityTypeId`,`toId`) USING BTREE,
  ADD KEY `toEntity` (`toEntityTypeId`);";


$qs[] = "ALTER TABLE `core_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$qs[] = "ALTER TABLE `core_link`
  ADD CONSTRAINT `fromEntity` FOREIGN KEY (`fromEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `toEntity` FOREIGN KEY (`toEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;";

$qs[] = "ALTER TABLE `core_entity` ADD `highestModSeq` INT NULL DEFAULT NULL AFTER `clientName`;";

$qs[] = function() {
	
	$entities = \go\core\orm\EntityType::findAll();
	
	$db = new \go\core\db\Database();
	
	foreach($entities as $entity) {
		$cls = $entity->getClassName();
		
		if(method_exists($cls, "model")) {
			$i = $cls::model();
			$tableName = 'go_links_' . $i->tableName();
			if($db->hasTable($tableName)) {
				$q = "insert ignore into core_link select NULL, ".$entity->getId().",id, model_type_id, model_id, description, from_unixtime(ctime), NULL, NULL, folder_id from " . $tableName. " where model_type_id in (select id from core_entity)";
				echo $q ."\n";
				App::get()->getDbConnection()->query($q);
				
				//What about link folders?
				$q = "DROP TABLE $tableName";
				echo $q."\n";
				App::get()->getDbConnection()->query($q);
			}			
		}
	}
	
};


$qs[] = function() {
	\go\modules\core\customfields\model\FieldSet::getType();
	\go\modules\core\customfields\model\Field::getType();
	
	\go\modules\core\links\model\Link::getType();
	\go\modules\core\search\model\Search::getType();
	\go\modules\core\users\model\User::getType();
	\go\modules\core\groups\model\Group::getType();
	
	\go\modules\core\modules\model\Module::getType();
};

$qs[] = "RENAME TABLE `go_users` TO `core_user`;";
$qs[] = "RENAME TABLE `go_users_groups` TO `core_user_group`;";
$qs[] = "RENAME TABLE `go_groups` TO `core_group`;";
$qs[] = "RENAME TABLE `cf_go_users` TO `cf_core_user`;";

$qs[] = "TRUNCATE go_state;";

$qs[] = "ALTER TABLE `core_module` ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `enabled`, ADD `modSeq` INT NULL DEFAULT NULL AFTER `modifiedAt`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `modSeq`;";

$qs[] = "ALTER TABLE `core_auth_password` ADD FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$qs[] = "update core_entity set moduleId = (select id from core_module where name='users') where name='User';";


//obsolete modules
$qs[] = "delete from core_module where name IN ('servermanager', 'admin2userlogin', 'formprocessor', 'settings', 'sites', 'syncml', 'dropbox', 'timeregistration', 'projects', 'hoursapproval', 'webodf','imapauth','ldapauth', 'presidents','ab2users', 'backupmanager', 'calllog', 'emailportlet', 'gnupg', 'language', 'mailings', 'newfiles')";

foreach($qs as $q) {
	if(is_string($q)) {
		try {
			echo $q ."\n";
			App::get()->getDbConnection()->query($q);
		} catch(\Exception $e) {
			
			echo 'ERROR: '. $e->getMessage().' Query '. $q;
			
			//MS: remove $e->getCode() == 42000 because that should not be ignored?
			
			if ($e->getCode() == '42S21' || $e->getCode() == '42S01' || $e->getCode() == '42S22') {
				//duplicate and drop errors. Ignore those on updates
			} else {
				echo "ERROR: A fatal upgrade error occurred. You will not be able to continue upgrading this database! Please report this error message.\n\n";
				exit();
			}
		}
	} else
	{
		call_user_func($q);
	}
}

echo "Core 6.3 upgrade done!\n";
