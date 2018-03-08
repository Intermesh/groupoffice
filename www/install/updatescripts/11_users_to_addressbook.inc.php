<?php

if(\GO::modules()->addressbook){
	try{
      \GO::getDbConnection()->query("ALTER TABLE `fs_folders` DROP `path`");
	  \GO::getDbConnection()->query("ALTER TABLE `ab_addressbooks` ADD `users` BOOLEAN NOT NULL ");
    } catch(PDOException $e) {
      //NOP: if column doesn't exists we don't want to hold
    }
}

//Added for compatibility  of upgrading a 3.7 database to version 6.1
if(\GO::modules()->files){
	try{
      \GO::getDbConnection()->query("ALTER TABLE `fs_folders` ADD `quota_user_id` INT NOT NULL DEFAULT '0' AFTER `muser_id`;");
			\GO::getDbConnection()->query("ALTER TABLE `core_user` ADD `birthday` DATE NULL DEFAULT NULL AFTER `email2`;");
    } catch(PDOException $e) {
      //NOP: if column doesn't exists we don't want to hold
    }
}

if(\GO::modules()->addressbook){
	$ab = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('users', '1');//\GO::t("Users"));
	if(!$ab){

		$ab = new \GO\Addressbook\Model\Addressbook();
		$ab->name=\GO::t("Users");
		$ab->users=true;
		$ab->save();

		$pdo = \GO::getDbConnection();

		$pdo->query("INSERT INTO ab_contacts (`addressbook_id`,`first_name`, `middle_name`, `last_name`, `initials`, `title`, `sex`, `birthday`, `email`, `department`, `function`, `home_phone`, `work_phone`, `fax`, `cellular`, `country`, `state`, `city`, `zip`, `address`, `address_no`,`go_user_id`) SELECT {$ab->id},`first_name`, `middle_name`, `last_name`, `initials`, `title`, `sex`, `birthday`, `email`, `department`, `function`, `home_phone`, `work_phone`, `fax`, `cellular`, `country`, `state`, `city`, `zip`, `address`, `address_no`,`id`  FROM `core_user` ");

	}
}

