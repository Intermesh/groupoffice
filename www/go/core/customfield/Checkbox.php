<?php

namespace go\core\customfield;

use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Query;

class Checkbox extends Base {

	protected function getFieldSQL(): string
	{
		$d = empty($this->field->getDefault()) ? "0" : "1";
		return "BOOLEAN NOT NULL DEFAULT '$d'";
	}

	public function defineFilter(Filters $filters) {
		$filters->add($this->field->databaseName, function(Criteria $criteria, $value, Query $query, array $filter){
			$this->joinCustomFieldsTable($query);

			$criteria->where('customFields.' . $this->field->databaseName , '=', $value);

			if(!$value) {
				$criteria->orWhere('customFields.' . $this->field->databaseName , 'IS', null);
			}
		});
	}
}
