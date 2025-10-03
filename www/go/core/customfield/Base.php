<?php
namespace go\core\customfield;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\data\Model;
use go\core\db\Criteria;
use go\core\db\Utils;
use go\core\ErrorHandler;
use go\core\model\Field;
use go\core\orm\CustomFieldsModel;
use go\core\orm\Entity;
use go\core\orm\Filters;
use go\core\orm\Query;
use go\core\util\ClassFinder;
use go\core\validate\ErrorCode;


/**
 * Abstract data type class
 * 
 * The data types handles:
 * 
 * 1. Column creation in database (Override getFieldSql())
 * 2. Input formatting with apiToDb();
 * 3. Output formatting with dbToApi();
 * 
 */
abstract class Base extends Model {
	
	/**
	 *
	 * @var Field
	 */
	protected $field;
	
	public function __construct(Field $field) {
		$this->field = $field;
	}

	/**
	 * Return true if a field save needs to be applied on the database.
	 * By default it will only do this when these properties change:
	 *
	 * 1. unique
	 * 2. default
	 * 3. options
	 * 4. databaseName
	 * 5. required
	 *
	 * Override this to implement special behaviour. @see Select.
	 *
	 * @return bool
	 */
	public function isModified(): bool
	{
		return false;
	}


  /**
   * Get column definition for SQL.
   *
   * When false is returned no databaseName is required and no field will be created.
   *
   * @return string|boolean
   * @throws Exception
   */
	protected function getFieldSQL()
	{
		$maxLength = $this->field->getOption('maxLength') ?? 190;

		if($maxLength > 255) {
			return "TEXT NULL";
		}

		$def = $this->field->getDefault();
		if(!empty($def)) {
			$def = go()->getDbConnection()->getPDO()->quote($def);
		} else{
			$def = "NULL";
		}

		return "VARCHAR(" . $maxLength . ") DEFAULT " . $def;
	}

  /**
   *
   * Check if this custom field has a column in the custom field record table.
   *
   * @return bool
   * @throws Exception
   */
	public function hasColumn(): bool
	{
		return $this->getFieldSQL() != false;
	}
	
	public function onFieldValidate(): bool
	{
		$fieldSql = $this->getFieldSQL();
		if(!$fieldSql) {
			return true;
		}
		
		if($this->field->isModified("databaseName") && preg_match('/[^a-zA-Z_0-9]/', $this->field->databaseName)) {
			$this->field->setValidationError('databaseName', ErrorCode::INVALID_INPUT, go()->t("Invalid database name. Only use alpha numeric chars and underscores.", 'core','customfields'));
			return false;
		}

		//check database name exists
		return true;
	}

  /**
   * Called when the field is saved
   *
   * @return bool
   * @throws Exception
   */
	public function onFieldSave(): bool
	{
		
		$fieldSql = $this->getFieldSQL();
		if(!$fieldSql) {
			return true;
		}
		
		$table = $this->field->tableName();

		$oldName = !$this->field->isNew() && $this->field->isModified('databaseName') ? $this->field->getOldValue("databaseName") : $this->field->databaseName;

		$quotedDbName = Utils::quoteColumnName($this->field->databaseName);
		
		if ($this->field->isNew() || !go()->getDatabase()->getTable($table)->hasColumn($oldName)) {
			$sql = "ALTER TABLE `" . $table . "` ADD " . $quotedDbName . " " . $fieldSql . ";";
			go()->getDbConnection()->exec($sql);
			if($this->field->getUnique()) {
				$sql = "ALTER TABLE `" . $table . "` ADD UNIQUE(". $quotedDbName  . ");";
				go()->getDbConnection()->exec($sql);
			}			
		} else {

			$col = go()->getDatabase()->getTable($table)->getColumn($oldName);

			if($col->nullAllowed && stristr($fieldSql, 'NOT NULL')) {
				//Set null values to the default if it was allowed.
				go()->getDbConnection()->exec("UPDATE `" . $table . "` SET `" . $oldName . "` = ". go()->getDbConnection()->getPDO()->quote($this->field->getDefault()) ." WHERE `" . $oldName . "` IS NULL");
			}
			
			$sql = "ALTER TABLE `" . $table . "` CHANGE " . Utils::quoteColumnName($oldName) . " " . $quotedDbName . " " . $fieldSql;
			go()->getDbConnection()->exec($sql);
			
			if($this->field->getUnique() && !$col->unique) {
				try {
					$sql = "ALTER TABLE `" . $table . "` ADD UNIQUE ".$quotedDbName." (". $quotedDbName  . ");";
					go()->getDbConnection()->exec($sql);
				} catch(\Exception $e) {
					//key is needed for contraint in select field
					$sql = "ALTER TABLE `" . $table . "` DROP INDEX " . $quotedDbName .", ADD UNIQUE ".$quotedDbName." (". $quotedDbName  . ");";
					go()->getDbConnection()->exec($sql);					
				}
			} else if(!$this->field->getUnique() && $col->unique) {
				try {
					$sql = "ALTER TABLE `" . $table . "` DROP INDEX " . $quotedDbName;
					go()->getDbConnection()->exec($sql);

				} catch(\Exception $e) {
					//key is needed for contraint in select field
					$sql = "ALTER TABLE `" . $table . "` DROP INDEX " . $quotedDbName .", ADD INDEX ".$quotedDbName." (". $quotedDbName  . ");";
					go()->getDbConnection()->exec($sql);					
				}
			}
		}

        go()->rebuildCache(true);
		
		return true;
	}

  /**
   * Called when a field is deleted
   *
   * @return bool
   * @throws Exception
   */
	public function onFieldDelete(): bool
	{
		
		$fieldSql = $this->getFieldSQL();
		if(!$fieldSql) {
			return true;
		}
		
		$table = $this->field->tableName();
		$sql = "ALTER TABLE `" . $table . "` DROP " . Utils::quoteColumnName($this->field->databaseName) ;

		try {
			go()->getDbConnection()->query($sql);
		} catch (Exception $e) {
			ErrorHandler::logException($e);
		}
		
		go()->rebuildCache(true);
		
		return true;
	}

	/**
	 * Format data from API to model
	 *
	 * This function is called when the API data is applied to the model with setValues();
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $values The values to be saved in the custom fields table
	 * @param Entity|ActiveRecord $entity
	 * @return mixed
	 * @see MultiSelect for an advaced example
	 */
	public function apiToDb($value, CustomFieldsModel $values, $entity)
	{
		return $value;
	}

	/**
	 * Format data from model to API
	 *
	 * This function is called when the data is serialized to JSON
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $values All the values of the custom fields to be returned to the API
	 * @param Entity|ActiveRecord $entity
	 * @return mixed
	 * @see MultiSelect for an advaced example
	 */
	public function dbToApi($value, CustomFieldsModel $values, $entity)
	{
		return $value;
	}

	/**
	 * Get the data as string
	 * Used for templates or export
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $values The values inserted in the database
	 * @param Entity|ActiveRecord $entity
	 * @return string
	 */
	public function dbToText($value, CustomFieldsModel $values, $entity) {
		return $this->dbToApi($value, $values, $entity);
	}

	/**
	 * Set the data as string
	 * Used for templates or export
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $values The values inserted in the database
	 * @param Entity|ActiveRecord $entity
	 * @return string
	 */
	public function textToDb($value, CustomFieldsModel $values, $entity) {
		return $this->apiToDb($value, $values, $entity);
	}

	/**
	 * Called after the data is saved to API.
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $customFieldData The custom fields data
	 * @param Entity|ActiveRecord $entity
	 * @return bool
	 * @see MultiSelect for an advaced example
	 */
	public function afterSave($value, CustomFieldsModel &$customFieldModel, $entity) : bool
	{
		
		return true;
	}

	/**
	 * Validate the input on the model. 
	 * 
	 * Use setValidationError if data is invalid:
	 * @return bool
	 * 
	 */
	public function validate($value, Field $field, $model): bool
	{
		if (!empty($field->requiredCondition)) {
			if (!$this->validateRequiredCondition($value, $field, $model)) {
                $model->setValidationError("customFields." . $field->databaseName, ErrorCode::REQUIRED);
                return false;
            }
		}
		return true;
	}

	/**
	 * @param mixed $value
	 * @param Field $field
	 * @param Entity|ActiveRecord $model
	 * @return bool
	 */
	protected function validateRequiredCondition($value, Field $field, $model)
    {
        $value = trim($value);

        $condition = $field->requiredCondition;
        $isEmptyCondition = false;
        $isNotEmptyCondition = false;
        $fieldName = null;
        $allowBlank = true;

        if (strpos($condition, 'is empty') !== false) {
            $isEmptyCondition = true;
            $condition = str_replace('is empty', '', $condition);
            $fieldName = str_replace(' ', '', $condition);
            if (property_exists($model, $fieldName)) {
                $fieldValue = $model->$fieldName;
            } else {
                $fieldName = null;
            }
        } else if (strpos($condition, 'is not empty') !== false) {
            $isNotEmptyCondition = true;
            $condition = str_replace('is not empty', '', $condition);
            $fieldName = str_replace(' ', '', $condition);
            if (property_exists($model, $fieldName)) {
                $fieldValue = $model->$fieldName;
            } else {
                $fieldName = null;
            }
        } else {
            $conditionParts = explode(' ', $condition);

            if (count($conditionParts) === 3) {
                $operator = $conditionParts[1];
                $fieldName = $conditionParts[0];

                if (property_exists($model, $fieldName)) {
                    $fieldValue = $model->$fieldName;
                    $requiredValue = $conditionParts[2];
                } else {
                    $fieldName = null;
                }

                if (null === $fieldName) {
                    $fieldName = $conditionParts[2];
                    if (property_exists($model, $fieldName)) {
                        $fieldValue = $model->$fieldName;
                        $requiredValue = $conditionParts[0];
                    } else {
                        $fieldName = null;
                    }
                }
            }
        }

        if (null !== $fieldName) {
            if ($isEmptyCondition) {
                $allowBlank = !empty($fieldValue);
            } else if ($isNotEmptyCondition) {
                $allowBlank = empty($fieldValue);
            } else {
                switch ($operator) {
                    case '=':
                    case '==':
                        $allowBlank = !($fieldValue == $requiredValue);
                        break;
                    case '>':
                        $allowBlank = !($fieldValue > $requiredValue);
                        break;
                    case '<':
                        $allowBlank = !($fieldValue < $requiredValue);
                        break;
                }
            }
        }

        if (!$allowBlank && empty($value)) {
            return false;
        }
        return true;
    }

	/**
	 * Called before the data is saved to API.
	 *
	 * @param mixed $value The value for this field
	 * @param CustomFieldsModel $model
	 * @param Entity|ActiveRecord $entity
	 * @param array $record The values inserted in the database
	 * @return bool
	 * @see MultiSelect for an advaced example
	 */
	public function beforeSave($value, CustomFieldsModel $model, $entity, &$record): bool
	{
		return true;
	}
	
	/**
	 *
	 * Get the modelClass for this customfield, only needed if an id of a related record is stored
	 *
	 * @return bool|string
	 */
	public function getModelClass()
	{
		return false;
	}
	/**
	 * Get the name of this data type
	 * 
	 * @return string
	 */
	public static function getName(): string
	{
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}

	/**
	 * Get all field types
	 *
	 * @return string[] eg ['functionField' => "go\core\customfield\FunctionField"];
	 * @throws Exception
	 */
	public static function findAll(): array
	{
		
		$types = go()->getCache()->get("customfield-types");
		
		if($types === null) {
			$classFinder = new ClassFinder();
			$classes = $classFinder->findByParent(self::class);

			$types = [];

			foreach($classes as $class) {
				$types[$class::getName()] = $class;
			}
			
			if(go()->getModule(null, "files")) {
				$types['File'] = \GO\Files\Customfield\File::class;
			}
			
			go()->getCache()->set("customfield-types", $types);
		}
		
		return $types;		
	}

	/**
	 * Find the class for a type
	 *
	 * @param string $name
	 * @return string
	 * @throws Exception
	 */
	public static function findByName(string $name): string
	{
		
		//for compatibility with old version
		//TODO remove when refactored completely
		$pos = strrpos($name, '\\');
		if($pos !== false) {
			$name = lcfirst(substr($name, $pos + 1));
		}
		$all = static::findAll();

		if(!isset($all[$name])) {
			go()->debug("WARNING: Custom field type '$name' not found");			
			return Text::class;
		}
		
		return $all[$name];
	}
	
	protected function joinCustomFieldsTable(Query $query) {
		if(!$query->isJoined($this->field->tableName())){
			$cls = $query->getModel();
			$primaryTableAlias = array_values($cls::getMapping()->getTables())[0]->getAlias();
			$query->join($this->field->tableName(),'customFields', 'customFields.id = '.$primaryTableAlias.'.id', 'LEFT');
		}
	}
	
	
	/**
	 * Defines an entity filter for this field.
	 * 
	 * @see Entity::defineFilters()
	 * @param Filters $filters
	 */
	public function defineFilter(Filters $filters) {
		$filters->addText($this->field->databaseName, function(Criteria $criteria, $comparator, $value, Query $query, array $filter){
			$this->joinCustomFieldsTable($query);						
			$criteria->where('customFields.' . $this->field->databaseName, $comparator, $value);
		});
	}


	/**
	 * Called when field is copied
	 * @return void
	 */
	public function onCopy() {
	}
}
