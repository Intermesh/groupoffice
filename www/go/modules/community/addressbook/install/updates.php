<?php
use go\modules\community\addressbook\Module;

$updates = [];

$updates['201811272011'][] = function() {
	\go\core\db\Utils::runSQLFile(\go()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/upgrade.sql"));
};
$updates['201811272011'][] = "DROP TABLE addressbook_contact_custom_fields";

$updates['201904021547'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->run();
};

$updates['201904021547'][] = "CREATE TABLE IF NOT EXISTS `addressbook_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultAddressBookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `defaultAddressBookId` (`defaultAddressBookId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";

$updates['201905201100'][] = "ALTER TABLE `addressbook_contact` DROP FOREIGN KEY `addressbook_contact_ibfk_1`;";
$updates['201905201100'][] = "ALTER TABLE `addressbook_contact` ADD CONSTRAINT `addressbook_contact_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";
$updates['201905201100'][] = "ALTER TABLE `addressbook_group` DROP FOREIGN KEY `addressbook_group_ibfk_1`;";
$updates['201905201100'][] = "ALTER TABLE `addressbook_group` ADD CONSTRAINT `addressbook_group_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

$updates['201905281248'][] = "ALTER TABLE `addressbook_email_address` DROP FOREIGN KEY addressbook_email_address_ibfk_1;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_email_address` CHANGE `id` `id` INT(11) NOT NULL;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_email_address` DROP PRIMARY KEY;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_email_address` DROP `id`;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_email_address` ADD FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_phone_number` DROP `id`;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_date` DROP `id`;";
$updates['201905281248'][] = "ALTER TABLE addressbook_url DROP FOREIGN KEY addressbook_url_ibfk_1;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_url` CHANGE `id` `id` INT(11) NOT NULL;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_url` DROP PRIMARY KEY;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_url` DROP `id`;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_url` ADD FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201905281248'][] = "ALTER TABLE `addressbook_address` DROP `id`";

$updates['201905281248'][] = "DELETE FROM core_entity WHERE moduleId = (select id from core_module where name='addressbook' and package='community') and name='Addresslist';";

$updates['201906181248'][] = "update `addressbook_contact_star` set starred = null where starred = 0;";

$updates['201906181248'][] = "ALTER TABLE `addressbook_contact_star` CHANGE `starred` `starred` TINYINT(1) NULL DEFAULT NULL;";

$updates['201907021042'][] = "ALTER TABLE `addressbook_user_settings` ADD `salutationTemplate` TEXT NOT NULL AFTER `defaultAddressBookId`, ADD `sortBy` ENUM('name','lastName') NOT NULL DEFAULT 'name' AFTER `salutationTemplate`;";

$updates['201908141101'][] = "ALTER TABLE `addressbook_addressbook` ADD `filesFolderId` INT NULL DEFAULT NULL AFTER `createdBy`;";

$updates['201908141101'][] = "ALTER TABLE `addressbook_addressbook` ADD `salutationTemplate` TEXT NULL AFTER `filesFolderId`;";
$updates['201908141101'][] = "ALTER TABLE `addressbook_user_settings` DROP `salutationTemplate`;";

$updates['201908301421'][] = "ALTER TABLE `addressbook_contact` ADD `initials` VARCHAR(50) DEFAULT NULL AFTER `prefixes`;";
$updates['201908301421'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->addInitials();
};

$updates['201909181300'][] = "ALTER TABLE `addressbook_contact` ADD `salutation` VARCHAR(100) NULL DEFAULT NULL AFTER `suffixes`;";
$updates['201909181300'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->addSalutation();
};


$updates['201909181300'][] = function() {
	Module::checkRootFolder();
};


$updates['201909241006'][] = 'delete from `addressbook_contact` WHERE addressBookId = (select value from core_setting where name="userAddressBookId") and firstName = "" and lastName = "" and name = "" and isOrganization = 0 and goUserId is null';

$updates['201910012019'][] = 'ALTER TABLE `addressbook_contact` DROP FOREIGN KEY `addressbook_contact_ibfk_3`;';
$updates['201910012019'][] = 'ALTER TABLE `addressbook_contact` ADD CONSTRAINT `addressbook_contact_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;';

$updates['201910012019'][] = 'ALTER TABLE `addressbook_contact` CHANGE `createdBy` `createdBy` INT(11) NULL DEFAULT NULL;';

$updates['201910012019'][] = 'ALTER TABLE `addressbook_addressbook` CHANGE `createdBy` `createdBy` INT(11) NULL DEFAULT NULL;';

$updates['201910012019'][] = 'UPDATE addressbook_addressbook set createdBy=null where createdBy not in (select id from core_user);';
$updates['201910012019'][] = 'ALTER TABLE `addressbook_addressbook` ADD FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;';
$updates['201910012019'][] = 'ALTER TABLE `addressbook_contact` DROP FOREIGN KEY `addressbook_contact_ibfk_4`; ';
$updates['201910012019'][] = 'ALTER TABLE `addressbook_contact` ADD CONSTRAINT `addressbook_contact_ibfk_4` FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;';

$updates['201910281039'][] = 'update `addressbook_contact` set lastName = null, firstName = null, middleName = null, suffixes = null, prefixes = null, modifiedAt = now() where isOrganization = true;';

$updates['201911111041'][] = 'ALTER TABLE `addressbook_contact` ADD INDEX(`isOrganization`)';

$updates['201912231421'][] = 'ALTER TABLE `addressbook_contact` ADD `color` CHAR(6) NULL DEFAULT NULL AFTER `uri`;';

$updates['202001091545'][] = 'update addressbook_contact set uri = null where uri = ".vcf" and uid is null;';
$updates['202001091545'][] = 'update addressbook_contact set uri = concat(uid, ".vcf") where uri = \'.vcf\' and uid is not null';


$updates['202003191040'][] = 'ALTER TABLE addressbook_contact ADD nameBank varchar(50);';
$updates['202003191040'][] = 'ALTER TABLE addressbook_contact ADD BIC varchar(11);';

$updates['202004011205'][] = "ALTER TABLE `addressbook_contact` CHANGE `uid` `uid` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL, CHANGE `uri` `uri` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";

$updates['202004161427'][] = "ALTER TABLE `addressbook_contact` ADD `department` VARCHAR(100) NULL DEFAULT NULL AFTER `jobTitle`;";

$updates['202004161427'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->addDepartment();
};

$updates['202006291222'][] = "ALTER TABLE `addressbook_user_settings` DROP `sortBy`";

$updates['202010080821'][] = "update `addressbook_contact` set color = null;";

$updates['202010080821'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->addColor();
};

$updates['202011241530'][] = 'CREATE TABLE IF NOT EXISTS `addressbook_portlet_birthday` (`userId` int(11) NOT NULL, `addressBookId` int(11) NOT NULL, PRIMARY KEY (`userId`, `addressBookId`) ) ENGINE=InnoDB';
$updates['202011241530'][] = 'ALTER TABLE `addressbook_portlet_birthday` ADD CONSTRAINT `addressbook_portlet_birthday_fk1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `addressbook_portlet_birthday_fk2` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`);';

$updates['202011241530'][] = 'ALTER TABLE `addressbook_portlet_birthday` DROP FOREIGN KEY `addressbook_portlet_birthday_fk2`;';
$updates['202011241530'][] = 'ALTER TABLE `addressbook_portlet_birthday` ADD CONSTRAINT `addressbook_portlet_birthday_fk2` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';

$updates['202102101145'][] = 'ALTER TABLE `addressbook_addressbook` DROP INDEX `acid`, ADD INDEX `aclId` (`aclId`) USING BTREE;';


$updates['202011271105'][] = "ALTER TABLE `addressbook_contact` ADD INDEX(`name`);";
$updates['202011271105'][] = "ALTER TABLE `addressbook_contact` ADD INDEX(`modifiedAt`);";
$updates['202011271105'][] = "ALTER TABLE `addressbook_contact` ADD INDEX(`lastName`);";

$updates['202011271105'][] = "update addressbook_contact set lastName = name where isOrganization = true;";



$updates['202105171220'][] = "create index addressbook_contact_addressBookId_lastName_index
	on addressbook_contact (addressBookId, lastName);";

$updates['202105171220'][] = "create index addressbook_contact_addressBookId_name_index
	on addressbook_contact (addressBookId, name);";



$updates['202106171331'][] = "create index addressbook_contact_isOrganization_index
	on addressbook_contact (isOrganization);";


$updates['202202070921'][] = "update `addressbook_phone_number` set type='mobile' where type='cell';";


$updates['202202070921'][] = "ALTER TABLE `addressbook_user_settings` ADD `lastAddressBookId` INT(11) null;";

$updates['202202070921'][] = "alter table addressbook_user_settings
	add startIn enum('allcontacts', 'starred', 'default', 'remember') default 'allcontacts' not null;";


$updates['202202070921'][] = "update `addressbook_phone_number` set type='mobile' where type='cell';";


$updates['202205101237'][] = "update addressbook_contact set filesFolderId = null where filesFolderId=0;";

// Were missing for Softaculous. Add them again. Will be ignored if already there.
$updates['202206020948'][] = 'ALTER TABLE addressbook_contact ADD nameBank varchar(50);';
$updates['202206020948'][] = 'ALTER TABLE addressbook_contact ADD BIC varchar(11);';

$updates['202210171545'][] = 'ALTER TABLE `addressbook_contact` CHANGE `salutation` `salutation` VARCHAR(382) DEFAULT NULL;';

$updates['202211041158'][] = 'alter table addressbook_contact
    modify department varchar(200) null;';

$updates['202211071330'][] = "ALTER TABLE `addressbook_email_address` ADD KEY `email` (`email`);";

// 6.7

$updates['202211071330'][] = "alter table addressbook_address
    add address text null;";

$updates['202211071330'][] = function() {

	go()->getDbConnection()->exec("alter table addressbook_address ADD id INT AUTO_INCREMENT PRIMARY KEY;");
	go()->getDatabase()->clearCache();
	try {
		$addresses = go()->getDbConnection()->select('id,street,street2,countryCode')
			->from("addressbook_address");
		foreach ($addresses as $address) {

			$a = go()->getLanguage()->formatAddress(['street' => $address['street'], 'street2' => $address['street2']], $address['countryCode'], false);

			go()->getDbConnection()->update("addressbook_address", ['address' => $a], ['id' => $address['id']])->execute();
		}
	} finally
	{
		go()->getDbConnection()->exec("alter table addressbook_address drop id;");
	}



};

$updates['202302281622'][] = "UPDATE core_setting s JOIN core_module m ON s.moduleId = m.id
SET s.value = IF(s.value = '1', 'on', 'off'), s.name = 'autoLink' WHERE m.name = 'addressbook' AND s.name = 'autoLinkEmail';";

$updates['202311271130'][] = '';
$updates['202311271130'][] = "";
$updates['202311271130'][] = 'ALTER TABLE `addressbook_contact` CHANGE `lastName` `lastName` VARCHAR(100) DEFAULT NULL;';
$updates['202311271130'][] = "ALTER TABLE `addressbook_contact` CHANGE `name` `name` VARCHAR(191) DEFAULT '';";

$updates['202311271130'][] = "alter table addressbook_contact
    add newsletterAllowed boolean default true null;";

$updates['202406271434'][] = "alter table addressbook_contact
    add icd varchar(4) null after registrationNumber;";


$updates['202406271434'][] = "update addressbook_contact set lastName = SUBSTRING(name, 1, 100) where isOrganization=true;";


$updates['202412031558'][] = "alter table addressbook_contact
    add lastContactAt datetime null;";

$updates['202412031558'][] = "create index addressbook_contact_lastContactAt_index
    on addressbook_contact (lastContactAt);";

$updates['202412031558'][] = function() {
	if(go()->getDatabase()->hasTable("comments_comment")) {
		$sql = "update addressbook_contact c 
    inner join comments_comment com on 
        com.entityId = c.id and com.entityTypeId = (select id from core_entity where clientName='Contact')
set c.lastContactAt = com.createdAt;";

		echo "Running: " . $sql . "\n";
		go()->getDbConnection()->exec($sql);
	}
};


$updates['202412031558'][] = "alter table addressbook_contact
    add actionAt date null;";

$updates['202412031558'][] = "create index addressbook_contact_actionAt_index
    on addressbook_contact (actionAt);";

$updates['202412031558'][] = "update addressbook_contact c
set c.actionAt = (select max(date) from addressbook_date where contactId = c.id);";

$updates['202508081003'][] = "update `addressbook_phone_number` set type='cell' where type='mobile';";
$updates['202508081003'][] = "update `addressbook_phone_number` set type='workcell' where type='workmobile';";
