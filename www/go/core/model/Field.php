<?php
namespace go\core\model;

use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\customfield\Base;
use go\core\customfield\Text;
use go\core\db\Criteria;
use go\core\db\Table;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\Relation;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

/**
 * Field
 * 
 * A custom field
 */
class Field extends AclItemEntity {

	/**
	 * The Entity ID
	 */
	public ?string $id;
	
	/**
	 * Display name
	 */
	public string $name;
	
	/**
	 * Foreign key for fieldSet
	 */
	public ?string $fieldSetId;
	
	/**
	 * Sort order
	 */
	public int $sortOrder;
	protected $options;
	
	
	/**
	 * The database column name
	 */
	public ?string $databaseName;
	
	/**
	 * True if an entry is requied
	 * @var boolean
	 */
	public bool $required = false;

	public ?string $relatedFieldCondition = null;

	public bool $conditionallyRequired = false;

	public bool $conditionallyHidden = false;
	
	/**
	 * Hint text to display in the form
	 */
	public ?string $hint = null;
	
	/**
	 * Field prefix
	 * 
	 * eg. :"€:
	 */
	public ?string $prefix;
	
	/**
	 * Field suffix
	 * 
	 * eg. "%"
	 */
	public ?string $suffix;
	
	/**
	 * Data type
	 */
	public string $type;
	
	/**
	 * Modified at time
	 */
	public ?\DateTimeInterface $modifiedAt;
	
	/**
	 * Created at time
	 */
	public ?\DateTimeInterface $createdAt;

	/**
	 * @var Relation[]
	 */
	public array $customFieldRelations = [];

	/**
	 * Hide field by default in grids
	 */
	public bool $hiddenInGrid = true;

	
	private $default;
	private $defaultModified = false;
	private $unique;
	private $uniqueModified = false;


	/**
	 * When true alter table is performed on save
	 *
	 * @var bool
	 */
	public $forceAlterTable = false;

	public $skipAlterTable = false;

	private $dataType;
	
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('core_customfields_field', 'f');
	}

	protected static function aclEntityClass(): string
	{
		return FieldSet::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['fieldSetId' => 'id'];
	}
	
	protected function internalValidate(): void
	{
		
		$this->getDataType()->onFieldValidate();
		
		parent::internalValidate();
	}


	/**
	 * Get field options.
	 *
	 * These options can vary per data type.
	 *
	 * eg. "multiselect" for select fields or maxLength for text fields.
	 *
	 * @return array
	 */
	public function getOptions() {
		return empty($this->options) ? [] : json_decode($this->options, true);
	}

	public function setOptions($options) {
		$this->options = json_encode(array_merge($this->getOptions(), $options));
	}

	/**
	 * Get field option
	 *
	 * @see getOptions()
	 * @param string $name
	 * @return mixed
	 */
	public function getOption($name) {
		$o = $this->getOptions();
		return isset($o[$name]) ? $o[$name] : null;
	}

	/**
	 * Set a field option
	 *
	 * @see getOptions()
	 * @param string $name
	 * @param mixed $value
	 */
	public function setOption($name, $value) {
		$o = $this->getOptions();
		$o[$name] = $value;
		$this->setOptions($o);
	}

  /**
   * Get default value for the column
   *
   * @return mixed
   * @throws Exception
   */
	public function getDefault() {
		if($this->defaultModified || $this->isNew()) {
			return $this->default;
		}
		if(isset($this->databaseName)) {
			$c = Table::getInstance($this->tableName())->getColumn($this->databaseName);
		}
		if(empty($c)) {
			go()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
			return null;
		}
		
		return $c->default;
	}
	
	public function setDefault($v) {
		$this->default = $v;
		$this->defaultModified = true;
	}

  /**
   * Check's if the column has a unique index
   *
   * @return bool
   * @throws Exception
   */
	public function getUnique() {
		if($this->uniqueModified || $this->isNew()) {
			return $this->unique;
		}
		if(isset($this->databaseName)) {
			$c = Table::getInstance($this->tableName())->getColumn($this->databaseName);
		}
		if(empty($c)) {
			go()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
			return false;
		}
		
		return !!$c->unique;
						
	}
	
	public function setUnique($v) {
		$this->unique = $v;
		$this->uniqueModified = true;
	}

	/**
	 * The data type object
	 * 
	 * @return Base
	 */
	public function getDataType(): Base
	{
		
		if(!isset($this->dataType)) {			
			$dataType = Base::findByName($this->type);
			$this->dataType = (new $dataType($this));
		}		
		return $this->dataType;
	}

	/**
	 * Used by the API to set values on the datatype
	 *
	 * @param array|Base $values
	 */
	public function setDataType(array|Base $values): void
	{
		if($values instanceof Base) {
			//ignore
			$this->type = $values::getName();
			$this->dataType = null;
			$this->getDataType()->setValues($values->toArray());
		} else {
			$this->getDataType()->setValues($values);
		}
	}

	protected function internalSave(): bool
	{
		if(!parent::internalSave()) {
			return false;
		}

		if($this->skipAlterTable) {
			return true;
		}


		$modified = $this->forceAlterTable || $this->isNew() || $this->uniqueModified || $this->defaultModified || $this->getDataType()->isModified() || $this->isModified(['databaseName', 'options', 'required']);
		if(!$modified) {
			return true;
		}

		try {
			go()->getDbConnection()->pauseTransactions();
			$this->getDataType()->onFieldSave();
		} catch(Exception $e) {
			go()->warn($e);

			if($this->isNew()) {
				//call parent so that field is not deleted from the table when for example
				//a duplicate column has been entered.
				parent::internalDelete(self::normalizeDeleteQuery($this->primaryKeyValues()));
			}

			go()->getDbConnection()->resumeTransactions();

			$this->setValidationError('databaseName', ErrorCode::GENERAL, $e->getMessage());
			
			return false;
		} 
		go()->getDbConnection()->resumeTransactions();

		$this->deleteFieldsQueryCache();
		
		return true;
	}

	private function deleteFieldsQueryCache() {
		$fieldSet = FieldSet::findById($this->fieldSetId);
		$entityName = $fieldSet->getEntity();
		$entityType = EntityType::findByName($entityName);

		$cacheKey = 'custom-field-models-' . $entityType->getId();
		go()->getCache()->delete($cacheKey);
	}

	protected static function internalDelete(Query $query): bool
	{
		try {
			go()->getDbConnection()->pauseTransactions();
			$fields = Field::find()->mergeWith($query);
			foreach($fields as $field) {
				$field->getDataType()->onFieldDelete();
				$field->deleteFieldsQueryCache();
			}
		} catch(Exception $e) {
			go()->warn($e);
			return false;
		} finally {
			go()->getDbConnection()->resumeTransactions();
		}
		
		return parent::internalDelete($query);
	}

	private $tableName;

  /**
   * Get the table name this field is stored in.
   *
   * @return string
   * @throws Exception
   */
	public function tableName() {
		if(!isset($this->tableName) || $this->isModified(['fieldSetId'])) {
			$fieldSet = FieldSet::findById($this->fieldSetId);
			$entityName = $fieldSet->getEntity();
			$entityType = EntityType::findByName($entityName);
			if (!$entityType) {
				throw new Exception("EntityType '$entityName' not found for custom field " . $this->name . ' (' . $this->id . ')');
			}
			$entityCls = $entityType->getClassName();
			$this->tableName = $entityCls::customFieldsTableName(); //From customfieldstrait
		}

		return $this->tableName;
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add('fieldSetId', function (Criteria $criteria, $value){
							$criteria->andWhere(['fieldSetId' => $value]);
						})
						->add('type', function(Criteria $criteria, string $value){
							$criteria->andWhere(['type' => $value]);
						});
	}

  /**
   * Find all fields for an entity
   *
   * @param int|string $entityTypeId
   * @return Query
   * @throws Exception
   */
	public static function findByEntity($entityTypeId) {
		if(!is_numeric($entityTypeId)) {
			$entityTypeId = EntityType::findByName($entityTypeId)->getId();
		}
		return static::find()->where(['fs.entityId' => $entityTypeId])
			->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId');
	}


  /**
   * Find or create custom field
   *
   * @example
   * ```
   * Field::create("Contract", "Intermesh", "hostname", "Hostname", Text::getName(), [
   *  "options" => ["maxLength" => 255]
   * ]);
   * ```
   *
   * @param string $entity eg. "User"
   * @param string $fieldSetName eg. "Forum"
   * @param string $databaseName eg. "numberOfPosts"
   * @param string $name eg "Number of posts"
   * @param string $type Type of custom field eg. Type
   * @param array $values extra values to set on the field.
   *
   * @return static
   * @throws Exception
   */
	public static function create(string $entity, string $fieldSetName, string $databaseName, string $name, string $type = 'Text', array $values = []): Field
	{
		$field = Field::findByEntity($entity)->where(['databaseName' => $databaseName])->single();

		if($field) {
			return $field;
		}

		$fieldSet = FieldSet::findByEntity($entity)->where(['name' => $fieldSetName])->single();
		if(!$fieldSet) {
			$fieldSet = new FieldSet();		
			$fieldSet->name = $fieldSetName;
			$fieldSet->setEntity($entity);
			if(!$fieldSet->save()) {
				throw new Exception("Could not save fieldset");
			}
		}

		$field = new Field();
		$field->databaseName = $databaseName;
		$field->name = $name;
		$field->type = $type;
		$field->fieldSetId = $fieldSet->id;
		$field->setValues($values);
		if(!$field->save()) {
			throw new Exception("Could not save field");
		}
		return $field;
	}

	public function copy(): static
	{
		$copy = parent::copy();

		$copy->getDataType()->onCopy();

		return $copy;
	}

}
