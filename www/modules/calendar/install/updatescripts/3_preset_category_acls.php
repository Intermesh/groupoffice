<?php
\GO\Base\Db\Columns::$forceLoad = true;

try{
\GO::getDbConnection()->query("ALTER TABLE `cal_categories` ADD `user_id` INT NOT NULL ; ");
}catch(Exception $e){
	
}

try{
\GO::getDbConnection()->query("update cal_categories set user_id = (select user_id from cal_calendars where id=cal_categories.calendar_id) where calendar_id>0;");
}catch(Exception $e){
	
}

$stmt = \GO\Calendar\Model\Category::model()->find(
	\GO\Base\Db\FindParams::newInstance()->ignoreAcl()
);
foreach ($stmt as $categoryModel) {
	$aclModel = $categoryModel->setNewAcl($categoryModel->user_id);
//	$aclModel->addGroup(2, \GO\Base\Model\Acl::WRITE_PERMISSION); // Give 'everybody' group (id: 2) permission.
	$categoryModel->save();
}
