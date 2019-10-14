<?php
$stmt = (new go\core\db\Query)
				->select('*')
				->from('core_custom_fields_field')
				->execute();

while($record = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	$jsonData = [];
	
	if(!empty($record['max_length'])) {
			$jsonData['maxLength'] = (int) $record['max_length'];
	}
	
	if(!empty($record['function'])) {
			$jsonData['function'] = $record['function'];
	}
	if(!empty($record['validation_regex'])) {
			$jsonData['validationRegex'] = $record['validation_regex'];
	}
	if(!empty($record['multiselect'])) {
			$jsonData['multiselect'] = (bool) $record['multiselect'];
	}
	if(!empty($record['treemaster_field_id'])) {
			$jsonData['treeMasterFieldId'] = (int) $record['treemaster_field_id'];
	}
	if(!empty($record['nesting_level'])) {
			$jsonData['nestingLevel'] = (int) $record['nesting_level'];
	}
	if(!empty($record['height'])) {
			$jsonData['height'] = (int) $record['height'];
	}
	if(!empty($record['number_decimals'])) {
			$jsonData['numberDecimals'] = (int) $record['number_decimals'];
	}
	if(!empty($record['addressbook_ids'])) {
			$jsonData['addressBookIds'] = $record['addressbook_ids'];
	}
	if(!empty($record['extra_options'])) {
			$jsonData['extraOptions'] = $record['extra_options'];
	}
	
	
	\go\core\App::get()->getDbConnection()
					->update('core_custom_fields_field', 
									['options' => json_encode($jsonData)], 
									['id' => $record['id']])
					->execute();
	
}

