<?php


$findParams = \GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->criteria(\GO\Base\Db\FindCriteria::newInstance()
								->addCondition('acl_id', '','=')
								);

$stmt = \GO\ServerManager\Model\Installation::model()->find($findParams);

// admin
$userId = 1;

foreach ($stmt as $installation) {
	
	$installation->setNewAcl($userId);
	$installation->save();
}