<?php

namespace go\core\validate;

use go\core\data\Model;
use go\core\orm\Query;

/**
 * Checks if the attribute is unique. Can also validate it in combination with other columns
 * 
 * Do not set this yourself. Just define a unique key on the database and it will
 * be generated automatically. Can also validate it in combination with other columns.
 * 
 * If for some reason you can't do this you can set it yourself:
 * 
 * <p>eg. in ActiveRecord do:</p>
 * 
 * ```````````````````````````````````````````````````````````````````````````
 * protected static function defineValidationRules() {
 * 	
 * 		self::getColumn('username')->required=true;
 * 		
 * 		return array(
 * 				new ValidateEmail("email"),
 * 				new ValidateUnique('email'),
 * 				new ValidateUnique('username'),
 *        new ValidatePassword('password', 'passwordConfirm') //Also encrypts it on success
 * 		);
 * 	}
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ValidateUnique extends Base {

	private $relatedColumns = [];

	/**
	 * Validate a unique value of this column in combination with other columns.
	 * 
	 * @param array $relatedColumns
	 */
	public function setRelatedColumns(array $relatedColumns) {
		$this->relatedColumns = $relatedColumns;
	}

	/**
	 * Run the validation
	 * 
	 * @param Model $model
	 * @return boolean
	 */
	protected function internalValidate(Model $model) {
		$relatedColumns = $this->relatedColumns;

		if (!in_array($this->getId(), $relatedColumns)) {
			$relatedColumns[] = $this->getId();
		}

		$query = (new Query());

		foreach ($relatedColumns as $f) {

			//Multiple null values are allowed
			if ($model->{$f} == null) {
				return true;
			}

			$query->andWhere([$f => $model->{$f}]);
		}

		if (!$model->isNew()) {
			$query->andWhere(['!=', $model->pk()]);
		}
		
		$existing = $model->find($query)->single();
		if ($existing) {			
			$this->setValidationError(ErrorCode::UNIQUE, 'The value must be unique', ['relatedColumns' => $this->relatedColumns]);
		}
	}
}