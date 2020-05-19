<?php

namespace go\core\customfield;

use GO;
use go\core\db\Criteria;
use go\core\orm\Entity;
use go\core\orm\Filters;
use go\core\orm\Query;

class Date extends Base {

  /**
   * @inheritDoc
   */
	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) && $d != "" ? go()->getDbConnection()->getPDO()->quote($d) : "NULL";
		return "DATE DEFAULT " . $d;
	}

  /**
   * @inheritDoc
   */
	public function defineFilter(Filters $filters) {		
		
		$filters->addDate($this->field->databaseName, function(Criteria $criteria, $comparator, $value, Query $query, array $filter){
			$this->joinCustomFieldsTable($query);						
			$criteria->where('customFields.' . $this->field->databaseName, $comparator, $value);
		});
	}

	public function dbToText($value, &$values, $entity)
	{
		if(!isset($value)) {
			return "";
		}
		return $value->toUserFormat();

	}

}
