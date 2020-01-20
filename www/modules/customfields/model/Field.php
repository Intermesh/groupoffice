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
 * @property string $required_condition
 * @property string $function
 * @property int $sort_index
 * @property string $datatype
 * @property string $name
 * @property int $category_id
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
		return 'cf_fields';
	}
	
	public function aclField() {
		return 'category.acl_id';
	}
	
	public function relations() {
		return array(
				'category' => array('type' => self::BELONGS_TO, 'model' => 'GO\Customfields\Model\Category', 'field' => 'category_id'),
				'treeOptions'=>array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\FieldTreeSelectOption', 'field' => 'field_id','delete'=>true),
				'selectOptions'=>array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\FieldSelectOption', 'field' => 'field_id','delete'=>true)
			);
	}
	
	/**
	 * Return the column name in the database of this field.
	 * @return String 
	 */
	public function columnName(){
		return 'col_'.$this->id;
	}
	
	protected function init() {
		
//		$this->columns['max']['gotype']='number';
//		$this->columns['height']['gotype']='number';
		
		$this->columns['name']['required']=true;
		$this->columns['max_length']['gotype']='number';
		$this->columns['max_length']['decimals']=0;
		
		parent::init();
	}
	
	//Mapping PHP errors to exceptions
	public function exception_error_handler($errno, $errstr, $errfile, $errline ) {
			$this->_regex_has_errors=true;
	}
	
	private $_regex_has_errors;

	
	public function validate() {		
		
		if(!empty($this->validation_regex)){
			$this->_regex_has_errors=false;
			set_error_handler(array($this,"exception_error_handler"));
			preg_match($this->validation_regex, "");
			if($this->_regex_has_errors)
				$this->setValidationError ("validation_regex", \GO::t("invalidRegex","customfields"));
			
			restore_error_handler();
		}

		return parent::validate();
	}
	
	protected function afterSave($wasNew) {
		
		$this->alterDatabase($wasNew);
		
		return parent::afterSave($wasNew);
	}
	
	protected function afterDuplicate(&$duplicate) {
		
		$this->duplicateRelation('selectOptions', $duplicate);
		$this->duplicateRelation('treeOptions', $duplicate);
		
		return parent::afterDuplicate($duplicate);
	}
		
	public function alterDatabase($wasNew){
		$table=$this->category->customfieldsTableName();
					
		if($wasNew){			
			$sql = "ALTER TABLE `".$table."` ADD `".$this->columnName()."` ".str_replace('%MAX_LENGTH',$this->max_length,$this->customfieldtype->fieldSql()).";";
			
		}else
		{
			$sql = "ALTER TABLE `".$table."` CHANGE `".$this->columnName()."` `".$this->columnName()."` ".str_replace('%MAX_LENGTH',$this->max_length,$this->customfieldtype->fieldSql());
			
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
		\GO\Base\Db\Columns::clearCache(\GO::getModel(\GO::getModel($this->category->extends_model)->customfieldsModel()));
		\GO::cache()->delete('customfields_'.$this->category->extends_model);	
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
			$field =Field::model()->findSingleByAttributes(array('treemaster_field_id'=>$this->id,'nesting_level'=>$i));

			if(!$field){
				$field = new Field();
				$field->name=$this->name.' '.$i;
				$field->datatype='GO\Customfields\Customfieldtype\TreeselectSlave';
				$field->treemaster_field_id=$this->id;
				$field->nesting_level=$i;
				$field->category_id=$this->category_id;
				$field->save();
			}				
		}
	}
	
	protected function beforeSave() {
		
		if(!$this->customfieldtype->hasLength()){
			//user may not set length so take the default
			$this->max_length = $this->customfieldtype->getMaxLength();
		}
		
		if($this->isNew)
			$this->sort_index=$this->count();		
		
		$this->addressbook_ids = preg_replace('/[^\d^,]/','',$this->addressbook_ids);
		if (strlen($this->addressbook_ids)>0 && $this->addressbook_ids[0]==',')
			$this->addressbook_ids = substr($this->addressbook_ids,1);
		if (strlen($this->addressbook_ids)>0 && $this->addressbook_ids[strlen($this->addressbook_ids)-1]==',')
			$this->addressbook_ids = substr($this->addressbook_ids,0,-1);
		
		return parent::beforeSave();
	}
	
	/**
	 * Get or create field if not exists
	 * 
	 * @param int $category_id
	 * @param StringHelper $fieldName
	 * @return \Field 
	 */
	public function createIfNotExists($category_id, $fieldName, $createAttributes=array()){
		$field = Field::model()->findSingleByAttributes(array('category_id'=>$category_id,'name'=>$fieldName));
		if(!$field){
			$field = new Field();
			$field->setAttributes($createAttributes, false);
			$field->category_id=$category_id;
			$field->name=$fieldName;
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
		$findParams = \GO\Base\Db\FindParams::newInstance()->joinRelation('category')->order('sort_index');
		
		if($permissionLevel){
			$findParams->permissionLevel($permissionLevel);
		}else
		{
			$findParams->ignoreAcl();
		}
		
		$findParams->getCriteria()->addCondition('extends_model', $modelName,'=','category');
		return $this->find($findParams);
	}
}