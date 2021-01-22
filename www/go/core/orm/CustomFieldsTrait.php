<?php
namespace go\core\orm;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\customfield\Html;
use go\core\customfield\TextArea;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\Installer;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\core\model\Field;
use PDOException;
use go\core\util\JSON;

/**
 * Entities can use this trait to enable a customFields property that can be 
 * extended by the user.
 * 
 * @property array $customFields 
 */
trait CustomFieldsTrait {

	private static $customFieldsTableName;

	private $customFieldsModel;

	/**
	 * Set the default return type of @see getCustomFields()
	 *
	 * @var bool
	 */
	public $returnAsText = false;

  /**
   * Get all custom fields data for an entity
   *
   * @param bool $asText Returns all values printable as text. Useful for templates and exports.
   * @return array
   * @throws Exception
   */
	public function getCustomFields($asText = null) {

		if(!isset($asText)) {
			$asText = $this->returnAsText;
		}

		if(!isset($this->customFieldsModel)) {
			$this->customFieldsModel = new CustomFieldsModel($this);
		}

		$this->customFieldsModel->returnAsText($asText);

		return $this->customFieldsModel;
	}

  /**
   * Setter for legacy modules
   *
   * @param $json
   * @throws Exception
   */
	public function setCustomFieldsJSON($json) {
		$data = JSON::decode($json, true);
		$this->setCustomFields($data);
	}

	/**
	 * Set custom field data
	 *
	 * The data array may hold partial data. It will be merged into the existing
	 * data.
	 *
	 * @param array|CustomFieldsModel $data
	 * @param bool $asText
	 * @return $this
	 * @throws Exception
	 */
	public function setCustomFields($data, $asText = false)
	{
		$this->getCustomFields($asText)->setValues($data);

		return $this;
	}

  /**
   * Set a custom field value
   *
   * @param string $name
   * @param mixed $value
   * @param bool $asText
   * @return $this
   * @throws Exception
   */
	public function setCustomField($name, $value, $asText = false) {
		return $this->setCustomFields([$name => $value], $asText);
	}
	
	private static $customFieldModels;
	
	/**
	 * Check if custom fields are modified
	 * 
	 * @return bool
	 */
	public function isCustomFieldsModified() {
		return isset($this->customFieldsModel) && $this->getCustomFields()->isModified();
	}

  /**
   * Get all custom fields for this entity indexed by database name
   *
   * @return Field[]
   * @throws Exception
   */
	public static function getCustomFieldModels() {
		$cacheKey = 'custom-field-models-' . static::customFieldsEntityType()->getId();
	 	$m = go()->getCache()->get($cacheKey);
		if($m === null) {
			$m = array();
			foreach(Field::find(['id', 'databaseName', 'fieldSetId', 'type', 'options', 'required'], true)
						->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId')
						->where(['fs.entityId' => static::customFieldsEntityType()->getId()]) as $field) {
				$m[$field->databaseName] = $field;
			}

			go()->getCache()->set($cacheKey, $m);
		}
		
		return $m;
	}

  /**
   * Saves custom fields to the database. Is called by Entity::internalSave()
   *
   * @return boolean
   * @throws PDOException
   * @throws Exception
   */
	public function saveCustomFields() {
		if(!isset($this->customFieldsModel) ) {
			return true;
		}
		return $this->getCustomFields()->save();
	}

  /**
   * Get table name for custom fields data
   *
   * @return string
   * @throws Exception
   */
	public static function customFieldsTableName() {

		if(isset(self::$customFieldsTableName)) {
			return self::$customFieldsTableName;
		}

		$cls = static::customFieldsEntityType()->getClassName();
		
		if(is_a($cls, Entity::class, true)) {		
			$mainTableName = $cls::getMapping()->getPrimaryTable()->getName();				
		} else
		{
			//ActiveRecord
			$mainTableName = $cls::model()->tableName();
		}
		
		self::$customFieldsTableName = $mainTableName.'_custom_fields';

		return self::$customFieldsTableName;
	}

	/**
	 * The entity type the custom fields are for.
	 * 
	 * Usually this is the static::entityType() but sometimes a model extends another like with filesearch. Then you can override this function:
	 * 
	 * ```php
	 * use CustomFieldsTrait {
	 * 		customFieldsEntityType as origCustomFieldsEntityType;
	 * }
	 * 
	 * public static function customFieldsEntityType() {
	 * 		return File2::entityType();
	 * }
	 * ```
	 * 
	 * @return EntityType
	 */
	public static function customFieldsEntityType() {
		return static::entityType();
	}

  /**
   * Defines filters for all custom fields
   *
   * @param Filters $filters
   * @throws Exception
   */
	protected static function defineCustomFieldFilters(Filters $filters) {
		
		$fields = static::getCustomFieldModels();		
		
		foreach($fields as $field) {
			if(!$filters->hasFilter($field->databaseName)) {
				$field->getDataType()->defineFilter($filters);
			}
		}		
	}


	protected function getCustomFieldsSearchKeywords()
	{
		$keywords = [];

		$cfData = $this->getCustomFields(true);

		foreach (static::getCustomFieldModels() as $field) {

			if ($field->getDataType() instanceof Html) {
				continue;
			}

			$v = $cfData[$field->databaseName];

			if (is_array($v)) {
				foreach ($v as $i) {
					if (!empty($v) && is_string($v)) {
						$keywords[] = $v;
					}
				}
			} else if (!empty($v) && is_string($v)) {

				$split = $field->getDataType() instanceof TextArea;

				if ($split) {
					$keywords = array_merge($keywords, SearchableTrait::splitTextKeywords($v));
				} else {
					$keywords[] = $v;
				}

			}
		}

		return $keywords;

	}
}
