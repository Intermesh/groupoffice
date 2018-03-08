<?php
$stmt = \GO\Bookmarks\Model\Category::model()->findByAttribute('acl_id', 0);
while($category=$stmt->fetch()){
	$category->setNewAcl($category->user_id);
	$category->save();
}