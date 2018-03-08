<?php

use go\core\App;
use go\core\db\Query;

$stmt = (new Query)
				->select('*')
				->from('core_entity')
				->execute();

while($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
	
	if(method_exists($record['name'], 'getModule')) {
	
	$name = $record['name']::getModule();
	
	$module = (new Query)
					->select('id')->from('core_module')->where(['name' => $name])
					->execute()->fetch();
	
	
	App::get()->getDbConnection()
					->update('core_entity', 
									['moduleId' => $module['id']], 
									['id' => $record['id']])
					->execute();
	}
	
}
