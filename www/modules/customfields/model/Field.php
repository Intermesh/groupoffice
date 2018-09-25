<?php
/**
 * @property Category $category 
 * @property \GO\Customfields\Customfieldtype\Text $customfieldtype
 * @property int $height
 * @property boolean $exclude_from_grid
 * @property int $treemaster_field_id
 * @property int $nesting_level
 * @property int $max
 * @property boolean $multiselect
 * @property string $helptext
 * @property string $validation_regex
 * @property boolean $required
 * @property string $function
 * @property int $sortOrder
 * @property string $datatype
 * @property string $name
 * @property int $fieldSetId
 * @property int $id
 * @property boolean $unique_values
 * @property int $number_decimals
 * @property int $max_length
 * @property string $addressbook_ids
 * @property string $extra_options Some types of data fields can have some extra options (use json format for multiple options)
 * @property string $prefix
 * @property string $suffix
 */

namespace GO\Customfields\Model;


class Field extends \GO\Base\Db\ActiveRecord{
	
	private $_datatype;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Field 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'core_customfields_field';
	}
	
	public function aclField() {
		return 'category.aclId';
	}
	
	public function relations() {
		return array(
				'category' => array('type' => self::BELONGS_TO, 'model' => 'GO\Customfields\Model\Category', 'field' => 'fieldSetId'),
				'treeOptions'=>array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\FieldTreeSelectOption', 'field' => 'field_id','delete'=>true),
				'selectOptions'=>array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\FieldSelectOption', 'field' => 'field_id','delete'=>true)
			);
	}
	
	/**
	 * Return the column name in the database of this field.
	 * @return String 
	 */
	public function columnName(){
		return $this->databaseName;
	}
	
	protected function init() {
		
		$this->columns['options']['gotype']='raw';
//		$this->columns['height']['gotype']='number';
		
		$this->columns['name']['required']=true;
			
		parent::init();
	}
	
	//Mapping PHP errors to exceptions
	public function exception_error_handler($errno, $errstr, $errfile, $errline ) {
			$this->_regex_has_errors=true;
	}
	
	private $_regex_has_errors;

	
	public function validate() {		
		
		if(!empty($this->validationRegex)){
			$this->_regex_has_errors=false;
			set_error_handler(array($this,"exception_error_handler"));
			preg_match($this->validationRegex, "");
			if($this->_regex_has_errors)
				$this->setValidationError ("validationRegex", \GO::t("The regular expression is invalid.", "customfields"));
			
			restore_error_handler();
		}
		
		return parent::validate();
	}
	
	protected function afterSave($wasNew) {
		
		$this->alterDatabase($wasNew);
				
		return parent::afterSave($wasNew);
	}
	
	public function setOptions($value) {
		$existing = empty($this->_attributes['options']) ? [] : json_decode($this->_attributes['options'], true);
		$this->_attributes['options'] = json_encode(array_merge($existing, $value));
	}
	
	public function getOptions() {
		return isset($this->_attributes['options']) ? json_decode($this->_attributes['options'], true) : [];
	}
	
	public function getOption($name) {
		$options = $this->getOptions();
		
		return isset($options[$name]) ? $options[$name] : null;
		
	}
	
	public function getExtra_options() {
	
		return $this->getOption('extraOptions');
	}
	
	public function setExtra_options($v) {
		$this->setOptions(['extraOptions' => $v]);
	}
	protected function afterDuplicate(&$duplicate) {
		
		$this->duplicateRelation('selectOptions', $duplicate);
		$this->duplicateRelation('treeOptions', $duplicate);
		
		return parent::afterDuplicate($duplicate);
	}
		
	public function alterDatabase($wasNew){
		$table=$this->category->customfieldsTableName();
		
		$fieldSql = $this->customfieldtype->fieldSql();
		
		foreach($this->getOptions() as $key => $value) {
			$fieldSql = str_replace('%' . $key .'%', $value, $fieldSql);
		}
    
    $fieldSql = str_replace('%maxLength%', $this->customfieldtype->getMaxLength(), $fieldSql);
					
		if($wasNew){			
			$sql = "ALTER TABLE `".$table."` ADD `".$this->databaseName."` ".$fieldSql.";";
			
		}else
		{
			$oldName = $this->isModified('databaseName') ? $this->getOldAttributeValue("databaseName") : $this->databaseName;
			$sql = "ALTER TABLE `".$table."` CHANGE `".$oldName."` `".$this->databaseName."` ".$fieldSql;
			
		}		
		//don't be strict in upgrade process
		\GO::getDbConnection()->query("SET sql_mode=''");
		
		if(!$this->getDbConnection()->query($sql))
			throw new \Exception("Could not create custom field");
		
//		if ($this->isModified('unique_values')) {
//			
//			if (!empty($this->unique_values))
//				$sqlUnique = "ALTER TABLE `".$table."` ADD UNIQUE INDEX ".$this->columnName()."_unique(".$this->columnName().")";
//			else
//				$sqlUnique = "ALTER TABLE `".$table."` DROP INDEX ".$this->columnName()."_unique";
//			
//			if (!$this->getDbConnection()->query($sqlUnique))
//				throw new \Exception("Could not change custom field uniqueness.");
//		}
		
		$this->_clearColumnCache();
	}
	
	/**
	 * GO caches the table schema for performance. We need to clear it 
	 */
	private function _clearColumnCache(){
	  //deleted cached column schema. See AbstractCustomFieldsRecord			
		if(!$this->category->isForEntity()) {
			\GO\Base\Db\Columns::clearCache(\GO::getModel(\GO::getModel($this->category->extendsModel)->customfieldsModel()));
			\GO::cache()->delete('customfields_'.$this->category->extendsModel);
		} else
		{
			\go\core\db\Table::getInstance($this->category->customfieldsTableName())->clearCache();
							
		}
	}
	
	public function hasLength() {
		return $this->customfieldtype->hasLength();
	}
	
	protected function getCustomfieldtype(){
		
		if(!isset($this->_datatype)){
			$className = class_exists($this->datatype) ? $this->datatype : "GO\Customfields\Customfieldtype\Text";

			$this->_datatype = new $className($this);
		}
		
		return $this->_datatype;
	}
	
	protected function afterDelete() {
		
		//don't be strict in upgrade process
		\GO::getDbConnection()->query("SET sql_mode=''");	
		
		$sql = "ALTER TABLE `".$this->category->customfieldsTableName()."` DROP `".$this->columnName()."`";
		
		try{
			$this->getDbConnection()->query($sql);
		}catch(\Exception $e){
			trigger_error("Dropping custom field column failed with error: ".$e->getMessage());
		}
			
		$this->_clearColumnCache();
		
		return parent::afterDelete();
	}
	
	
	public function getTreeSelectNestingLevel($parentOptionId=0, $nestingLevel=0){
		$stmt= FieldTreeSelectOption::model()->find(array(
			'where'=>'parent_id=:parent_id AND field_id=:field_id',
			'bindParams'=>array('parent_id'=>$parentOptionId, 'field_id'=>$this->id),
			'order'=>'sort'
		));		
		$options = $stmt->fetchAll();
		
		$startNestingLevel=$nestingLevel;
		foreach($options as $o){
			$newNestingLevel=$this->getTreeSelectNestingLevel($o->id, $startNestingLevel+1);
			if($newNestingLevel>$nestingLevel){
				$nestingLevel=$newNestingLevel;
			}
		}
		
		return $nestingLevel;
	}
	
	public function checkTreeSelectSlaves(){
		//We need to create a \GO\Customfields\Customfieldtype\TreeselectSlave field for all tree levels
		$nestingLevel = $this->getTreeSelectNestingLevel();

		for($i=1;$i<$nestingLevel;$i++){
			$field = $this->findTreeSelectSlave($this->id, $i);

			if(!$field){
				$field = new Field();
				$field->name=$this->name.' '.$i;
				$field->databaseName = $this->databaseName.$i; 
				$field->datatype='GO\Customfields\Customfieldtype\TreeselectSlave';
				$field->setOptions (array_merge($this->getOptions(), ['treeMasterFieldId' => $this->id, 'nestingLevel' => $i]));
				$field->fieldSetId=$this->fieldSetId;
				$field->save();
			}				
		}
	}
	
	private function findTreeSelectSlave($treeMasterFieldId, $nestingLevel) {
		$fields = Field::model()->findByAttributes(['datatype' => 'GO\Customfields\Customfieldtype\TreeselectSlave', 'fieldSetId' => $this->fieldSetId]);
		foreach($fields as $field) {
			$o = $field->getOptions();
			if($o['treeMasterFieldId'] == $treeMasterFieldId && $o['nestingLevel'] == $nestingLevel) {
				return $field;
			}
		}
		
		return false;
	}
	
	protected function beforeSave() {
		
		if(!$this->customfieldtype->hasLength()){
			//user may not set length so take the default
			$this->setOptions(['maxLength' => $this->customfieldtype->getMaxLength()]);
		}
		
		if($this->isNew)
			$this->sortOrder=$this->count();		
		
//		$this->addressbook_ids = preg_replace('/[^\d^,]/','',$this->addressbook_ids);
//		if (strlen($this->addressbook_ids)>0 && $this->addressbook_ids[0]==',')
//			$this->addressbook_ids = substr($this->addressbook_ids,1);
//		if (strlen($this->addressbook_ids)>0 && $this->addressbook_ids[strlen($this->addressbook_ids)-1]==',')
//			$this->addressbook_ids = substr($this->addressbook_ids,0,-1);
		
		
		return parent::beforeSave();
	}
	
	/**
	 * Get or create field if not exists
	 * 
	 * @param int $fieldSetId
	 * @param StringHelper $fieldName
	 * @return \Field 
	 */
	public function createIfNotExists($fieldSetId, $fieldName, $createAttributes=array()){
		$field = Field::model()->findSingleByAttributes(array('fieldSetId'=>$fieldSetId,'name'=>$fieldName));
		if(!$field){
			$field = new Field();
			$field->setAttributes($createAttributes, false);
			$field->fieldSetId=$fieldSetId;
			$field->name=$fieldName;
      $field->databaseName = \go\core\fs\File::stripInvalidChars($fieldName);
			$field->save();
		}
		return $field;
	}
	
	
	public function checkDatabase() {
		
		try{
			$this->alterDatabase(false);
		} catch (Exception $ex) {
			//class doesn't exist?
			
			echo "ERROR: ".$ex->getMessage()."\n";
		}
		
		
		return parent::checkDatabase();
	}
	
	
	public function toJsonArray(){
		$arr=$this->getAttributes();
		$arr['dataname']=$this->columnName();
		$arr['customfield_id']=$this->id;

		$arr['validation_modifiers']="";

		if(!empty($arr['validation_regex'])){
			$delimiter = $arr['validation_regex'][0];
			$rpos = strrpos($arr['validation_regex'], $delimiter);
			if($rpos){
				$arr['validation_modifiers']=substr($arr['validation_regex'],$rpos+1);
				$arr['validation_regex']=substr($arr['validation_regex'],1, $rpos-1);
			}else
			{
				$arr['validation_regex']="";
			}
		}
		
		return $arr;
	}
	
	/**
	 * Get all customfield models that are attached to the given model.
	 * 
	 * @param StringHelper $modelName
	 * @param int $permissionLevel Set to false to ignore permissions
	 * @return Field
	 */
	public function findByModel($modelName, $permissionLevel=  \GO\Base\Model\Acl::READ_PERMISSION){
		
		$entityId = $modelName::getType()->getId();;		
		
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->joinRelation('category')->order('sortOrder');
		
		if($permissionLevel){
			$findParams->permissionLevel($permissionLevel);
		}else
		{
			$findParams->ignoreAcl();
		}
		
		$findParams->getCriteria()->addCondition('entityId', $entityId,'=','category');
		return $this->find($findParams);
	}
	
	public function getAttributes($outputType = null) {
		$attr = parent::getAttributes($outputType);
		$attr['options'] = $this->getOptions();
		$attr['extra_options'] = $this->getExtra_options();
		
		return $attr;
	}
	
}
