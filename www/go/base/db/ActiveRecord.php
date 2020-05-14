<?php
/**
 * Group-Office
 *
 * Copyright Intermesh BV.
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.db
 */

/**
 * All Group-Office models should extend this ActiveRecord class.
 *
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @abstract
 *
 * @property \GO\Base\Model\User $user If this model has a user_id field it will automatically create this property
 * @property \GO\Base\Model\Acl $acl If this model has an acl ID configured. See ActiveRecord::aclId it will automatically create this property.
 * @property bool $isJoinedAclField
 * @property int/array $pk Primary key value(s) for the model
 * @property string $module Name of the module this model belongs to
 * @property boolean $isNew Is the model new and not inserted in the database yet.
 * @property String $localizedName The localized human friendly name of this model.
 * @property int $permissionLevel @see \GO\Base\Model\Acl for available levels. Returns -1 if no aclField() is set in the model.
 *
 * @property GO\Files\Model\Folder $filesFolder The folder model that belongs to this model if hasFiles is true.
 */


namespace GO\Base\Db;

use GO\Base\Db\PDO;
use GO;
use go\core\db\Query;
use go\core\util\DateTime;

abstract class ActiveRecord extends \GO\Base\Model{

	/**
	 * The mode for this model on how to output the attribute data.
	 * Can be "raw", "formatted" or "html";
	 *
	 * @var StringHelper
	 */
	public static $attributeOutputMode='raw';

	/**
	 * Format attributes on input/output. We want to move to the situatation that
	 * the client does all the formatting and the server doesn't do this anymore.
	 * So on JSON payload requests this will be disabled in the controller.
	 *
	 * @var boolean
	 */
	public static $formatAttributesByDefault=true;

	/**
	 * Spaces of all varchar attibutes of the record will be trimmed
	 * To prevent this, set this value to false
	 * @var boolean 
	 */
	public static $trimOnSave = true;

	/**
	 * This relation is used when the remote model's primary key is stored in a
	 * local attribute.
	 *
	 * Addressbook->user() for example
	 */
	const BELONGS_TO=1;	// n:1

	/**
	 * This relation type is used when this model has many related models.
	 *
	 * Addressbook->contacts() for example.
	 */
	const HAS_MANY=2; // 1:n

	/**
	 * This relation type means that the relation is single and this model's primary
	 * key can be found in the remote model.
	 *
	 * User->Addressbook for example where user_id is in the addressbook table.
	 */
	const HAS_ONE=3; // 1:1

  /*
   * This relation type is used when this model has many related models.
   * The relation makes use of a linked table that has a combined key of the related model and this model.
   *
   * Example use in the model class relationship array: 'users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'linkModel'=>'GO\Base\Model\UserGroups', 'field'=>'group_id', 'remoteField'=>'user_id'),
   *
   */
  const MANY_MANY=4; // n:n

	/**
	 * Cascade delete relations. Only works on has_one and has_many relations.
	 */
	const DELETE_CASCADE = "CASCADE";

	/**
	 * Restrict delete relations. Only works on has_one and has_many relations.
	 */
	const DELETE_RESTRICT = "RESTRICT";

//	/**
//	 * The database connection of this record
//	 *
//	 * @var PDO
//	 */
//	private static $db;



	private $_attributeLabels;

	public static $db; //The database the active record should use

	/**
	 * Force this activeRecord to save itself
	 *
	 * @var boolean
	 */
	private $_forceSave = false;

	/**
	 * See http://dev.mysql.com/doc/refman/5.1/en/insert-delayed.html
	 *
	 * @var boolean
	 */
	protected $insertDelayed=false;

	/**
	 * Indiciates that the ActiveRecord is being contructed by PDO.
	 * Used in setAttribute so it skips fancy features that we know will only
	 * cause overhead.
	 *
	 * @var boolean
	 */
	protected $loadingFromDatabase=true;


	private static $_addedRelations=array();



	/**
	 *
	 * @var \GO\Base\Model\Acl
	 */
	private $_acl=false;
	
	private $_isDeleted = false;
	
	/**
	 * If this property is set the ACL of the model will be changed
	 * Possible values:
	 * - null: will not make any changes to the ACL
	 * - true: will create a new ACL and attach it to this model on save()
	 * - false: will remove the overwritten ACL if it is differend from its parent on save()
	 * and use the ACL from the parent
	 * @see setAcl_overwritten()
	 * @var boolean
	 */
	protected $overwriteAcl;

	public function setAcl_overwritten($v) {
		$this->overwriteAcl = $v;
	}

	/**
	 *
	 * @var int Link type of this Model used for the link system. See also the linkTo function
	 */
	public function modelTypeId(){
		return \GO\Base\Model\ModelType::model()->findByModelName($this->className());
	}
	
	/**
	 * For compatibility with new framework
	 * @return type
	 */
	public static function entityType() {
		return \go\core\orm\EntityType::findByClassName(static::class);
	}

	/**
	 * Get the localized human friendly name of this model.
	 * This function must be overriden.
	 *
	 * @return String
	 */
	protected function getLocalizedName(){

		$parts = explode('\\',$this->className());
		$lastPart = array_pop($parts);

		$module = strtolower($parts[1]);

		return GO::t($lastPart, $module);
	}
  
  /**
   * For compatibility with new framework
   * @return type
   */
  public static function getClientName() {
    $parts = explode('\\',static::class);
		return array_pop($parts);
  }


	/**
	 *
	 * Define the relations for the model.
	 *
	 * NOTE: To get relations use getRelations() as it also includes dynamically added relations and automatic relations.
	 *
	 * Example return value:
	 * array(
				'contacts' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'addressbook_id', 'delete'=>self::DELETE_CASCADE //with this enabled the relation will be deleted along with the model),
				'companies' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'addressbook_id', 'delete'=>self::DELETE_CASCADE),
				'addressbook' => array(
	 *				'type'=>self::BELONGS_TO,
	 *				'model'=>'GO\Addressbook\Model\Addressbook',
	 *				'field'=>'addressbook_id',
	 *				'labelAttribute'=>function($model){return $model->relation->name;} //this will automatically supply the label for a combobox in a JSON request.
	 *		)
				'users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'field'=>'group_id', 'linkModel' => 'GO\Base\Model\UserGroup'), // The "field" property is the key of the current model that is defined in the linkModel
		);
	 *
	 * The relations can be accessed as functions:
	 *
	 * Model->contacts() for example. They always return a PDO statement.
	 * You can supply FindParams as an optional parameter to narrow down the results.
	 *
	 * Note: relational queries do not check permissions!
	 *
	 * If you have a "user_id" field, an automatic relation model->user() is created that
	 * returns a \GO\Base\Model\User.
	 *
	 * "delete"=>true will automatically delete the relation along with the model. delete flags on BELONGS_TO relations are invalid and will be ignored.
	 *
	 *
	 * You can also select find parameters that will be applied to the relational query. eg.:
	 *
	 * findParams=>FindParams::newInstance()->order('sort_index');
	 *
	 * @return array relational rules.
	 */
	public function relations(){
		return array();
	}

	/**
	 * Dynamically add a relation to this ActiveRecord. See the relations() function
	 * for a description.
	 *
	 * Example to add the events relation to a user:
	 *
	 * \GO\Base\Model\User::model()->addRelation('events', array(
	 *		'type'=>  ActiveRecord::HAS_MANY,
	 *		'model'=>'GO\Calendar\Model\Event',
	 *		'field'=>'user_id'
	 *	));
	 *
	 * @param array $config @see relations
	 */
	public function addRelation($name, $config){
		self::$_addedRelations[$name]=$config;
	}

	/**
	 * This is defined as a function because it's a only property that can be set
	 * by child classes.
	 *
	 * @return StringHelper The database table name
	 */
	public function tableName(){
		return false;
	}

	/**
	 * The name of the column that has the foreignkey the the ACL record
	 * If column 'acl_id' exists it default to this
	 * You can use field of a relation separated by a dot (eg: 'category.acl_id')
	 * @return StringHelper ACL to check for permissions.
	 */
	public function aclField(){
		return false; //return isset($this->columns['acl_id']) ? 'acl_id' : false;
	}

	/**
	 * If the ACL is joined but the table has it's own acl_id column it is
	 * possible to overwrite the ACL
	 * @return boolean|StringHelper the acl_id column name or false if not overwritable
	 */
	public function aclOverwrite() {
		if(!$this->getIsJoinedAclField()) // is there is no dot in aclField()
			return false;
		return isset($this->columns['acl_id']) ? 'acl_id' : false;
	}

	/**
	 * Returns the fieldname that contains primary key of the database table of this model
	 * Can be an array of column names if the PK has more then one column
	 * @return mixed Primary key of database table. Can be a field name string or an array of fieldnames
	 */
	public function primaryKey()
	{
		return 'id';
	}

	private $_relatedCache;

	private $_joinRelationAttr;

	protected $_attributes=array();

	private $_modifiedAttributes=array();

	private $_debugSql=false;


	/**
	 * Set to true to enable a files module folder for this item. A files_folder_id
	 * column in the database is required. You will probably
	 * need to override buildFilesPath() to make it work properly.
	 *
	 * @return bool true if the Record has an files_folder_id column
	 */
	public function hasFiles(){
		return isset($this->columns['files_folder_id']);
	}
	
	/**
	 * Set to true to always create a files folder. Note that you may not use an auto increment ID in the buildFilesFolder() function when this is set to true.
	 * 
	 * @return bool
	 */
	public function alwaysCreateFilesFolder() {
		return (isset($this->acl_id) && !$this->aclOverwrite());
	} 

	/**
	 * Set to true to enable links for this model. A table go_links_$this->tableName() must be created
	 * with columns: id, model_id, model_type_id
	 *
	 * @return bool
	 */
	public function hasLinks(){return false;}


	private $_filesFolder;

	/**
	 * Get the folder model belonging to this model if it supports it.
	 *
	 * @param $autoCreate If the folder doesn't exist yet it will create it.
	 * @return \GO\Files\Model\Folder
	 */
	public function getFilesFolder($autoCreate=true){

		if(!$this->hasFiles())
			return false;

		if(!isset($this->_filesFolder)){

			if($autoCreate){
				$c = new \GO\Files\Controller\FolderController();
				$folder_id = $c->checkModelFolder($this, true, true);
			}elseif(empty($this->files_folder_id)){
				return false;
			}else
			{
				$folder_id = $this->files_folder_id;
			}

			$this->_filesFolder=\GO\Files\Model\Folder::model()->findByPk($folder_id);
			if(!$this->_filesFolder && $autoCreate)
				throw new \Exception("Could not create files folder for ".$this->className()." ".$this->pk);
		}
		return $this->_filesFolder;
	}

	/**
	 *
	 * @return boolean Call $model->isJoinedAclField to check if the aclfield is joined.
	 */
	protected function getIsJoinedAclField (){
		return strpos($this->aclField(),'.')!==false;
	}

	/**
	 * Compares this ActiveRecord with $record.
	 *
	 * @param ActiveRecord $record record to compare to or an array of records
	 * @return boolean whether the active records are the same database row.
	 */
	public function equals($record) {

		if(!is_array($record) && !($record instanceof \Traversable)){
			$record=array($record);
		}

		foreach($record as $r){
			if(get_class($r) != get_class($this)) {
				return false;
			}
			
			if($this->tableName()===$r->tableName() && $this->getPk()===$r->getPk())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * The columns array is loaded automatically. Validator rules can be added by
	 * overriding the init() method.
	 *
	 * @var array Holds all the column properties indexed by the field name.
	 *
	 * eg: 'id'=>array(
	 * 'type'=>PDO::PARAM_INT, //Autodetected
	 * 'required'=>true, //Will be true automatically if field in database may not be null and doesn't have a default value
	 * 'length'=><max length of the value>, //Autodetected from db
	 * 'validator'=><a function to call to validate the value>, This may be an array: array("Class", "method", "error message")
	 * 'gotype'=>'number|textfield|textarea|unixtimestamp|unixdate|user|file(GO\Base\Fs\File can be set).', //Autodetected from db as far as possible. See loadColumns()
	 * 'filePathTemplate'=>'Only when gotype='file'. Eg. billing/templates/{id}.{extension}
	 * 'decimals'=>2//only for gotype=number)
	 * 'regex'=>'A preg_match expression for validation',
	 * 'dbtype'=>'varchar' //mysql database type
	 * 'unique'=>false //true|array to enforce a unique value value can me array of related attributes
	 * 'greater'=>'start_time' //this column must be greater than column start time
	 * 'greaterorequal'=>'start_time' //this column must be greater or equal to column start time
	 * The validator looks like this:
	 *
	 * function validate ($value){
			return true;
		}
	 */
	protected $columns;

//	=array(
//				'id'=>array('type'=>PDO::PARAM_INT,'required'=>true,'length'=>null, 'validator'=>null,)
//			);
//
	private $_new=true;
	
	private $_isStaticModel;

	/**
	 * Constructor for the model
	 *
	 * @param boolean $newRecord true if this is a new model
	 * @param boolean true if this is the static model returned by \GO\Base\Model::model()
	 */
	public function __construct($newRecord=true, $isStaticModel=false){

		if(!empty(GO::session()->values['debugSql']))
			$this->_debugSql=true;

		$this->_isStaticModel = $isStaticModel;
		//$pk = $this->pk;

		$this->columns=Columns::getColumns($this);
		$this->setIsNew($newRecord);

		$this->init();

		if($this->getIsNew()){
			$this->setAttributes($this->getDefaultAttributes(),false);
			$this->loadingFromDatabase=false;
			$this->afterCreate();
		}elseif(!$isStaticModel){
			$this->castMySqlValues();
			$this->_cacheRelatedAttributes();
			$this->afterLoad();

			$this->loadingFromDatabase=false;
		}

		$this->_modifiedAttributes=array();
	}

	public function __wakeup() {

	}

	/**
	 * This function is called after the model is constructed by a find query
	 */
	protected function afterLoad(){

	}

		/**
	 * This function is called after a new model is constructed
	 */
	protected function afterCreate(){

	}


	/**
	 * When a model is joined on a find action and we need it for permissions, We
	 * select all the model attributes so we don't have to query it seperately later.
	 * eg. $contact->addressbook will work from the cache when it was already joined.
	 */
	private function _cacheRelatedAttributes(){
		foreach($this->_attributes as $name=>$value){
			$arr = explode('@',$name);
			if(count($arr)>1){

				$cur = &$this->_joinRelationAttr;

				foreach($arr as $part){
					$cur =& $cur[$part];
					//$this->_relatedCache[$arr[0]][$arr[1]]=$value;
				}
				$cur = $value;

				unset($this->_attributes[$name]);
			}
		}
	}

	/**
	 * Returns localized attribute labels for each column.
	 *
	 * The default language variable name is modelColumn.
	 *
	 * eg.: \GO\Tasks\Model\Task column 'name' will look for:
	 *
	 * $l['taskName']
	 *
	 * 'due_time' will be
	 *
	 * $l['taskDue_time']
	 *
	 * If you don't like this you may also override this function in your model.
	 *
	 * @return array
	 *
	 * A key value array eg. array('name'=>'Name', 'due_time'=>'Due time')
	 *
	 */
	public function attributeLabels(){
		if(!isset($this->_attributeLabels)){
			$this->_attributeLabels = array();

//			$classParts = explode('\\',$this->className());
//			$prefix = strtolower(array_pop($classParts));

			foreach($this->columns as $columnName=>$columnData){
				
				$str = ucfirst(str_replace("_", " ", $columnName));
				
				$label = GO::t($str, $this->getModule());
				if($label == $str) {
					$label = GO::t($str);
				}
				$this->_attributeLabels[$columnName] = $label;
				if(!$str == $label) {
						switch($columnName){
							case 'user_id':
								$this->_attributeLabels[$columnName] = GO::t("Created by");
								break;
							case 'muser_id':
								$this->_attributeLabels[$columnName] = GO::t("Modified by");
								break;

							case 'ctime':
								$this->_attributeLabels[$columnName] = GO::t("Created at");
								break;

							case 'mtime':
								$this->_attributeLabels[$columnName] = GO::t("Modified at");
								break;
							case 'name':
								$this->_attributeLabels[$columnName] = GO::t("Name");
								break;
						}
					}
				}
		}
		return $this->_attributeLabels;
	}



	/**
	 * Get the label of the asked attribute
	 *
	 * This function can be overridden in the model.
	 *
	 * @return String The label of the asked attribute
	 */
	public function getAttributeLabel($attribute) {

		$labels = $this->attributeLabels();

		return isset($labels[$attribute]) ? $labels[$attribute] : GO::t(ucfirst(str_replace("_", " ", $attribute)));
	}

	/**
	 * Set the label of an attribute
	 *
	 * This function can be overridden in the model.
	 *
	 * @param type $attribute
	 * @param type $label
	 */
	public function setAttributeLabel($attribute,$label) {
			$this->columns[$attribute]['label'] = $label;
	}

	public static function load($pk=null) {
		$self = GO::getModel(get_called_class());
		if($pk !== null)
			return $self->findByPk($pk);
		$query = new Query($self);
		return $query;
	}

	/**
	 * Can be overriden to initialize the model. Useful for setting attribute
	 * validators in the columns property for example.
	 */
	protected function init(){}

	/**
	 * Get's the primary key value. Can also be accessed with $model->pk.
	 *
	 * @return mixed The primary key value
	 */
	public function getPk(){

		$ret = null;

		if(is_array($this->primaryKey())){
			foreach($this->primaryKey() as $field){
				if(isset($this->_attributes[$field])){
					$ret[$field]=$this->_attributes[$field];
				}else
				{
					$ret[$field]=null;
				}
			}
		}elseif(isset($this->_attributes[$this->primaryKey()]))
			$ret =  $this->_attributes[$this->primaryKey()];

		return $ret;
	}

	/**
	 * Check if this model is new and not stored in the database yet.
	 *
	 * @return bool
	 */
	public function getIsNew(){

		return $this->_new;
	}

	/**
	 * Set if this model is new and not stored in the database yet.
	 * Note: this function is generally only used by the framework internally.
	 * You don't need to set this boolean. The framework takes care of that.
	 *
	 * @param bool $new
	 */
	public function setIsNew($new){

		$this->_new=$new;
	}

	private $_pdo;

	/**
	 * Returns the database connection used by active record.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return PDO the database connection used by active record.
	 */
	public function getDbConnection()
	{
		if(isset($this->_pdo))
			return $this->_pdo;
		else
			return GO::getDbConnection();
	}

	/**
	 * Connect the model to another database then the default.
	 *
	 * @param PDO $pdo
	 */
	public function setDbConnection($pdo) {
		$this->_pdo=$pdo;
		GO::modelCache()->remove($this->className());
	}

	private function _getAclJoinProps(){
		$arr = explode('.',$this->aclField());
		if(count($arr)==2 && !$this->aclOverwrite()){
			$r= $this->getRelation($arr[0]);

			return array('table'=>$r['name'], 'relation'=>$r, 'model'=>GO::getModel($r['model']), 'attribute'=>$arr[1]);
		}else
		{
			return array('attribute'=>$this->aclOverwrite() ? $this->aclOverwrite() : $this->aclField(), 'table'=>'t');
		}
	}


//	private function _joinAclTable(){
//		$arr = explode('.',$this->aclField());
//		if(count($arr)==2){
//			//we need to join a table for the acl field
//			$r= $this->getRelation($arr[0]);
//			$model = GO::getModel($r['model']);
//
//			$ret['relation']=$arr[0];
//			$ret['aclField']=$arr[1];
//			$ret['join']="\nINNER JOIN `".$model->tableName().'` '.$ret['relation'].' ON ('.$ret['relation'].'.`'.$model->primaryKey().'`=t.`'.$r['field'].'`) ';
//			$ret['fields']='';
//
//			$cols = $model->getColumns();
//
//			foreach($cols as $field=>$props){
//				$ret['fields'].=', '.$ret['relation'].'.`'.$field.'` AS `'.$ret['relation'].'@'.$field.'`';
//			}
//			$ret['table']=$ret['relation'];
//
//		}else
//		{
//			return false;
//		}
//
//		return $ret;
//	}

	/**
	 * Makes an attribute unique in the table by adding a number behind the name.
	 * eg. Name becomes Name (1) if it already exists.
	 *
	 * @param String $attributeName
	 */
	public function makeAttributeUnique($attributeName){
		$x = 1;

		$origValue = $value =  $this->$attributeName;

		while ($existing = $this->_findExisting($attributeName, $value)) {

			$value = $origValue . ' (' . $x . ')';
			$x++;
		}
		$this->$attributeName=$value;
	}

	private function _findExisting($attributeName, $value){

		$criteria = FindCriteria::newInstance()
										->addModel(GO::getModel($this->className()))
										->addCondition($attributeName, $value);

		if($this->pk)
			$criteria->addCondition($this->primaryKey(), $this->pk, '!=');

		$existing = $this->findSingle(FindParams::newInstance()
						->criteria($criteria));

		return $existing;
	}

	private $_permissionLevel;

	private $_acl_id;

	/**
	 * Find the model that controls permissions for this model.
	 *
	 * @return ActiveRecord
	 * @throws Exception
	 */
	public function findRelatedAclModel(){

		if (!$this->aclField())
			return false;



		$arr = explode('.', $this->aclField());
		if (count($arr) > 1) {
			$relation = $arr[0];

			//not really used. We use findAclId() of the model.
			$aclField = array_pop($arr);
			$modelWithAcl=$this;

			while($relation = array_shift($arr)){
				if(!$modelWithAcl->$relation){					
					throw new \Exception("Could not find relational ACL: ".$this->aclField()." ($relation) in ".$this->className()." with pk: ".$this->pk);
				}else{
					$modelWithAcl=$modelWithAcl->$relation;
				}
			}
			return $modelWithAcl;
		}else
		{
			return false;
		}
	}


	/**
	 * Check if the acl field is modified.
	 *
	 * Example: acl field is: addressbook.acl_id
	 * Then this function fill search for the addressbook relation and checks if the key is changed in this relation.
	 * If the key is changed then it will return true else it will return false.
	 *
	 * @return boolean
	 */
	private function _aclModified(){
		$aclFk = $this->_getAclFk();
		if($aclFk===false)
			return false;
		
		return $this->isModified($aclFk);
	}
	
	/**
	 * Get the FK field that link to the model containing the ACL
	 * eg. adressbook_id
	 * @return boolean|StringHelper field name or false if not an related ACL
	 */
	private function _getAclFk() {
		if (!$this->aclField())
			return false;

		$arr = explode('.', $this->aclField());

		if(count($arr)==1)
			return false;

		$relation = array_shift($arr);
		$r = $this->getRelation($relation);
		return $r['field'];
	}


	/**
	 * Find the acl_id integer value that applies to this model.
	 *
	 * @return int ACL id from core_acl_group_items table.
	 */
	public function findAclId() {
		if (!$this->aclField()) {
			$moduleName = $this->getModule();
			return \GO::modules()->{$moduleName}->acl_id;
		}

		//removed caching of _acl_id because the relation is cached already and when the relation changes the wrong acl_id is returned,
		////this happened when moving contacts from one acl to another.
		//if(!isset($this->_acl_id)){
			//ACL is mapped to a relation. eg. $contact->addressbook->acl_id is defined as "addressbook.acl_id" in the contact model.
			if(!$this->isAclOverwritten()){
				$modelWithAcl = $this->findRelatedAclModel();
				if($modelWithAcl){
					$this->_acl_id = $modelWithAcl->findAclId();
				} else {
					$this->_acl_id = $this->{$this->aclField()};
				}
			}else
			{
				$this->_acl_id = $this->{$this->aclOverwrite()};
			}
		//}

		return $this->_acl_id;
	}

	/**
	 * Returns the permission level for the current user when this model is new
	 * and does not have an ACL yet. This function can be overridden if you don't
	 * like the default action.
	 * By default it only allows new models by module admins.
	 *
	 * @return int
	 */
	protected function getPermissionLevelForNewModel(){
		//the new model has it's own ACL but it's not created yet.
		//In this case we will check the module permissions.
		$module = $this->getModule();
		if ($module == 'base') {
			return GO::user()->isAdmin() ? \GO\Base\Model\Acl::MANAGE_PERMISSION : false;
		}else
			return GO::modules()->$module->permissionLevel;
	}

	/**
	 * Returns the permission level if an aclField is defined in the model. Otherwise
	 * it returns \GO\Base\Model\Acl::MANAGE_PERMISSION;
	 *
	 * @return int \GO\Base\Model\Acl::*_PERMISSION
	 */

	public function getPermissionLevel(){

		if(GO::$ignoreAclPermissions)
			return \GO\Base\Model\Acl::MANAGE_PERMISSION;

		if(!$this->aclField())
			return \GO\Base\Model\Acl::MANAGE_PERMISSION;

		if(!GO::user())
			return false;

		//if($this->isNew && !$this->joinAclField){
		if(empty($this->{$this->aclField()}) && !$this->isJoinedAclField){
			return $this->getPermissionLevelForNewModel();
		}else
		{
			if(!isset($this->_permissionLevel)){

				$acl_id = $this->findAclId();
				if(!$acl_id){
					throw new \Exception("Could not find ACL for ".$this->className()." with pk: ".$this->pk);
				}

				$this->_permissionLevel=\GO\Base\Model\Acl::getUserPermissionLevel($acl_id);// model()->findByPk($acl_id)->getUserPermissionLevel();
			}
			return $this->_permissionLevel;
		}

	}

	/**
	 * Returns an unique ID string for a find query. That is used to store the
	 * total number of rows in session. This way we don't need to calculate the
	 * total on each pagination page when limit 0,n is used.
	 *
	 * @param array $params
	 * @return StringHelper
	 */
	private function _getFindQueryUid($params){
		//create unique query id

		unset($params['start'], $params['orderDirection'], $params['order'], $params['limit']);
		if(isset($params['criteriaObject'])){
			$params['criteriaParams']=$params['criteriaObject']->getParams();
			$params['criteriaParams']=$params['criteriaObject']->getCondition();
			unset($params['criteriaObject']);
		}
		//GO::debug($params);
		return md5(serialize($params).$this->className());
	}

	/**
	 * Finds models by attribute and value
	 * This function uses find() to check permissions!
	 *
	 * @param StringHelper $attributeName column name you want to check a value for
	 * @param mixed $value the value to find (needs to be exact)
	 * @param FindParams $findParams Extra parameters to send to the find function.
	 * @return ActiveStatement
	 */
	public function findByAttribute($attributeName, $value, $findParams=false){
		return $this->findByAttributes(array($attributeName=>$value), $findParams);
	}

	/**
	 * Finds models by an attribute=>value array.
	 * This function uses find() to check permissions!
	 *
	 * @param array $attributes
	 * @param FindParams $findParams
	 * @return static ActiveStatement
	 */
	public function findByAttributes($attributes, $findParams=false){
		$newParams = FindParams::newInstance();
		$criteria = $newParams->getCriteria()->addModel($this);

		foreach($attributes as $attributeName=>$value) {
			if(is_array($value))
				$criteria->addInCondition($attributeName, $value);
			else
				$criteria->addCondition($attributeName, $value);
		}

		if($findParams)
			$newParams->mergeWith ($findParams);

		$newParams->ignoreAcl();

		return $this->find($newParams);
	}

	/**
	 * Finds a single model by an attribute name and value.
	 *
	 * @param StringHelper $attributeName
	 * @param mixed $value
	 * @param FindParams $findParams Extra parameters to send to the find function.
	 * @return static
	 */
	public function findSingleByAttribute($attributeName, $value, $findParams=false){
		return $this->findSingleByAttributes(array($attributeName=>$value), $findParams);
	}


	/**
	 * Finds a single model by an attribute=>value array.
	 *
	 * @param StringHelper $attributeName
	 * @param mixed $value
	 * @param array $findParams Extra parameters to send to the find function.
	 * @return static
	 */
	public function findSingleByAttributes($attributes, $findParams=false){

		$cacheKey = md5(serialize($attributes));

		//Use cache so identical findByPk calls are only executed once per script request
		$cachedModel =  GO::modelCache()->get($this->className(), $cacheKey);
		if($cachedModel)
			return $cachedModel;

		$newParams = FindParams::newInstance();
		$criteria = $newParams->getCriteria()->addModel($this);

		foreach($attributes as $attributeName=>$value) {
			if(is_array($value))
				$criteria->addInCondition($attributeName, $value);
			else
				$criteria->addCondition($attributeName, $value);
		}

		if($findParams)
			$newParams->mergeWith ($findParams);

		$newParams->ignoreAcl()->limit(1);

		$stmt = $this->find($newParams);

		$model = $stmt->fetch();

		GO::modelCache()->add($this->className(), $model, $cacheKey);

		return $model;
	}

	/**
	 * Finds a single model by an attribute name and value.
	 * This function does NOT check permissions.
	 *
	 * @todo FindSingleByAttributes should use this function when this one uses the FindParams object too.
	 *
	 * @param StringHelper $attributeName
	 * @param mixed $value
	 * @param FindParams $findParams Extra parameters to send to the find function.
	 * @return static
	 */
	public function findSingle($findParams=array()){

		if(!is_array($findParams))
			$findParams = $findParams->getParams();

		$defaultParams=array('limit'=>1, 'start'=>0,'ignoreAcl'=>true);
		$params = array_merge($findParams,$defaultParams);

		$cacheKey = md5(serialize($params));
		//Use cache so identical findByPk calls are only executed once per script request
		$cachedModel = empty($params['disableModelCache']) ? GO::modelCache()->get($this->className(), $cacheKey) : false;
		if($cachedModel)
			return $cachedModel;

		$stmt = $this->find($params);
		$models = $stmt->fetchAll();

		$model = isset($models[0]) ? $models[0] : false;

		GO::modelCache()->add($this->className(), $model, $cacheKey);

		return $model;
	}

	/**
	 * Get all default select fields. It excludes BLOBS and TEXT fields.
	 * This function is used by find.
	 *
	 * @param boolean $single
	 * @param StringHelper $tableAlias
	 * @return StringHelper
	 */
	public function getDefaultFindSelectFields($single=false, $tableAlias='t'){

		$fields = array();
		
		//when upgrading we must refresh columns
		if(Columns::$forceLoad)
			$this->columns = Columns::getColumns ($this);

		if($single)
			return $tableAlias.'.*';

		foreach($this->columns as $name=>$attr){
			if(isset($attr['gotype']) && $attr['gotype']!='blob' && $attr['gotype']!='textarea'  && $attr['gotype']!='html')
				$fields[]=$name;
		}

		// This is added so we can see the class when this error occurs
		if(empty($fields)){
			throw new \Exception('Variable $fields is empty for class: '.self::className());
		}

		return "`$tableAlias`.`".implode('`, `'.$tableAlias.'`.`', $fields)."`";
	}

	/**
	 * Create or find an ActiveRecord
	 * when there is no PK supplied a new instance of the called class will be returned
	 * else it will pass the PK value to findByPk()
	 * When a multi column key is used it will create when not found
	 * @param array $params PK or record to search for
	 * @return ActiveRecord the called class
	 * @throws \GO\Base\Exception\NotFound when no record found with supplied PK
	 */
	public function createOrFindByParams($params) {

		$pkColumn = $this->primaryKey();
		if (is_array($pkColumn)) { //if primaryKey excists of multiple columns
			$pk = array();
			foreach ($pkColumn as $column) {
				if (isset($params[$column]))
					$pk[$column] = $this->formatInput($column, $params[$column]);
			}
			if (empty($pk))
				$model = new static();
			else {
				$model = $this->findByPk($pk);
				if (!$model)
					$model = new static();
			}

			if ($model->isNew)
				$model->setAttributes($params);

			return $model;
		}
		else {
			$pk = isset($params[$this->primaryKey()]) ? $params[$this->primaryKey()] : null;
			if (empty($pk)) {
				$model = new static();
				if ($model->isNew){
					$model->setAttributes($params);
				}
			}else {
				$model = $this->findByPk($pk);
				if (!$model)
					$model = new static();
			}
			return $model;
		}
	}

	private $useSqlCalcFoundRows=true;

	/**
	 * Find models
	 *
	 * Example usage:
	 *
	 * <code>
	 * //create new find params object
	 * $params = FindParams::newInstance()
	 *   ->joinCustomFields()
	 *   ->order('due_time','ASC');
	 *
	 * //select all from tasklist id = 1
	 * $params->getCriteria()->addCondition('tasklist_id,1);
	 *
	 * //find the tasks
	 * $stmt = \GO\Tasks\Model\Task::model()->find($params);
	 *
	 * //print the names
	 * while($task = $stmt->fetch()){
	 *	echo $task->name.'&lt;br&gt;';
	 * }
	 * </code>
	 *
	 *
	 * @param FindParams $params
	 * @return static ActiveStatement
	 */
	public function find($params=array()){

		if(!is_array($params))
		{
			if(!($params instanceof FindParams))
				throw new \Exception('$params parameter for find() must be instance of FindParams');

			if($params->getParam("export")){
				GO::session()->values[$params->getParam("export")]=array(
						'name'=>$params->getParam("export"),
						'model'=>$this->className(),
						'findParams'=>$params,
						'totalizeColumns'=>$params->getParam('export_totalize_columns'));
			}

			//it must be a FindParams object
			$params = $params->getParams();
		}

		if(!empty($params['single'])){
			unset($params['single']);
			return $this->findSingle($params);
		}

		if(!empty($params['debugSql'])){
			$this->_debugSql=true;
			//GO::debug($params);
		}else
		{
			$this->_debugSql=!empty(GO::session()->values['debugSql']);
		}
//		$this->_debugSql=true;
		if(GO::$ignoreAclPermissions)
			$params['ignoreAcl']=true;

		if(empty($params['userId'])){
			$params['userId']=!empty(GO::session()->values['user_id']) ? GO::session()->values['user_id'] : 1;
		}
		
		if($this->aclField() && (empty($params['ignoreAcl']) || !empty($params['joinAclFieldTable']))){
			$aclJoinProps = $this->_getAclJoinProps();

			if(isset($aclJoinProps['relation']))
				$params['joinRelations'][$aclJoinProps['relation']['name']]=array('name'=>$aclJoinProps['relation']['name'], 'type'=>'INNER');
		}

		$select = "SELECT ";

		if(!empty($params['distinct']))
			$select .= "DISTINCT ";

		//Unique query ID for storing found rows in session
		$queryUid = $this->_getFindQueryUid($params);

		if(!empty($params['calcFoundRows']) && !empty($params['limit']) && (empty($params['start']) || !isset(GO::session()->values[$queryUid]))){

			//TODO: This is MySQL only code
			if($this->useSqlCalcFoundRows)
				$select .= "SQL_CALC_FOUND_ROWS ";

			$calcFoundRows=true;
		}else
		{
			$calcFoundRows=false;
		}

//		$select .= "SQL_NO_CACHE ";
		
		

		if(empty($params['fields']))
			$params['fields']=$this->getDefaultFindSelectFields(isset($params['limit']) && $params['limit']==1);
		else
			go()->debug($params['fields']);


		$fields = $params['fields'].' ';

		$joinRelationSelectFields='';
		$joinRelationjoins='';
		if(!empty($params['joinRelations'])){

			foreach($params['joinRelations'] as $joinRelation){

				$names = explode('.', $joinRelation['name']);
				$relationModel = $this;
				$relationAlias='t';
				$attributePrefix = '';

				foreach($names as $name){
					$r = $relationModel->getRelation($name);

					$attributePrefix.=$name.'@';

					if(!$r)
						throw new \Exception("Can't join non existing relation '".$name.'"');

					$model = GO::getModel($r['model']);
					$joinRelationjoins .= "\n".$joinRelation['type']." JOIN `".$model->tableName().'` `'.$name.'` ON (';

					switch($r['type']){
						case self::BELONGS_TO:
							$joinRelationjoins .= '`'.$name.'`.`'.$model->primaryKey().'`=`'.$relationAlias.'`.`'.$r['field'].'`';
						break;

						case self::HAS_ONE:
						case self::HAS_MANY:
							if(is_array($r['field'])){
								$conditions = array();
								foreach($r['field'] as $my=>$foreign){
									$conditions[]= '`'.$name.'`.`'.$foreign.'`=t.`'.$my.'`';
								}
								$joinRelationjoins .= implode(' AND ', $conditions);
							}else{
								$joinRelationjoins .= '`'.$name.'`.`'.$r['field'].'`=t.`'.$this->primaryKey().'`';
							}
							break;

						default:
							throw new \Exception("The relation type of ".$name." is not supported by joinRelation or groupRelation");
							break;
					}

					$joinRelationjoins .=') ';

					//if a diffent fetch class is passed then we should not join the relational fields because it makes no sense.
					//\GO\Base\Model\Grouped does this for example.
					if(empty($params['fetchClass'])){
						$cols = $model->getColumns();

						foreach($cols as $field=>$props){
							$joinRelationSelectFields .=",\n`".$name.'`.`'.$field.'` AS `'.$attributePrefix.$field.'`';
						}
					}

					$relationModel=$model;
					$relationAlias=$name;

				}
			}
		}

		
		$joinCf = !empty($params['joinCustomFields']) && $this->hasCustomFields();

		if($joinCf) {
			$cfFieldModels = array_filter(static::getCustomFieldModels(), function($f) {
				return $f->getDataType()->hasColumn();
			});
			
			$names = array_map(function($f) {
				if(empty($f->databaseName)) {
					throw new Exception("Custom field ". $f->id ." has no databaseName");
				}
				return "cf." . $f->databaseName;
			}, $cfFieldModels);
			
			if(!empty($names)) {
				$fields .= ", " .implode(', ', $names);
			}
		}

		$fields .= $joinRelationSelectFields;

		if(!empty($params['groupRelationSelect'])){
			$fields .= ",\n".$params['groupRelationSelect'];
		}

		$from = "\nFROM `".$this->tableName()."` t ".$joinRelationjoins;

		$joins = "";
		if (!empty($params['linkModel'])) { //passed in case of a MANY_MANY relation query
      $linkModel = new $params['linkModel'];
      $primaryKeys = $linkModel->primaryKey();

			if(!is_array($primaryKeys))
				throw new \Exception ("Fatal error: Primary key of linkModel '".$params['linkModel']."' in relation '".$params['relation']."' should be an array.");

      $remoteField = $primaryKeys[0]==$params['linkModelLocalField'] ? $primaryKeys[1] : $primaryKeys[0];
      $joins .= "\nINNER JOIN `".$linkModel->tableName()."` link_t ON t.`".$this->primaryKey()."`= link_t.".$remoteField.' ';
    }


		if($joinCf)
			$joins .= "\nLEFT JOIN `".$this->customFieldsTableName()."` cf ON cf.id=t.id ";

		if(isset($aclJoinProps) && empty($params['ignoreAcl']))
			$joins .= $this->_appendAclJoin($params, $aclJoinProps);

		if(isset($params['join']))
			$joins .= "\n".$params['join'];

			$where = "\nWHERE 1 ";

		if(isset($params['criteriaObject'])){
			$conditionSql = $params['criteriaObject']->getCondition();
			if(!empty($conditionSql))
				$where .= "\nAND".$conditionSql;
		}

		$where = self::_appendByParamsToSQL($where, $params);

		if(isset($params['where']))
			$where .= "\nAND ".$params['where'];

    if(isset($linkModel)){
      //$primaryKeys = $linkModel->primaryKey();
      //$remoteField = $primaryKeys[0]==$params['linkModelLocalField'] ? $primaryKeys[1] : $primaryKeys[0];
      $where .= " \nAND link_t.`".$params['linkModelLocalField']."` = ".intval($params['linkModelLocalPk'])." ";
    }

		if(!empty($params['searchQuery'])){
			$where .= " \nAND (";

			if(empty($params['searchQueryFields'])){
				$searchFields = $this->getFindSearchQueryParamFields('t',$joinCf);
			}else{
				$searchFields = $params['searchQueryFields'];
			}


			if(empty($searchFields))
				throw new \Exception("No automatic search fields defined for ".$this->className().". Maybe this model has no varchar fields? You can override function getFindSearchQueryParamFields() or you can supply them with FindParams::searchFields()");

			//`name` LIKE "test" OR `content` LIKE "test"

			$first = true;
			foreach($searchFields as $searchField){
				if($first){
					$first=false;
				}else
				{
					$where .= ' OR ';
				}
				$where .= $searchField.' LIKE '.$this->getDbConnection()->quote($params['searchQuery'], PDO::PARAM_STR);
			}

			if($this->primaryKey()=='id'){
				//Searc on exact ID match too.
				$idQuery = trim($params['searchQuery'],'% ');
				if(intval($idQuery)."" === $idQuery){
					if($first){
						$first=false;
					}else
					{
						$where .= ' OR ';
					}

					$where .= 't.id='.intval($idQuery);
				}
			}

			$where .= ') ';
		}

		$group="";
		if($this->aclField() && empty($params['ignoreAcl']) && (empty($params['limit']) || $params['limit']!=1)){

			//add group by pk so acl join won't return duplicate rows. Don't do this with limit=1 because that makes no sense and causes overhead.

			$pk = is_array($this->primaryKey()) ? $this->primaryKey() : array($this->primaryKey());

			$group .= "\nGROUP BY t.`".implode('`,t.`', $pk)."` ";
			if(isset($params['group']))
				$group .= ", ";


		}elseif(isset($params['group'])){
			$group .= "\nGROUP BY ";
		}

		if(isset($params['group'])){
			if(!is_array($params['group']))
				$params['group']=array($params['group']);

			for($i=0;$i<count($params['group']);$i++){
				if($i>0)
					$group .= ', ';

				$group .= $this->_quoteColumnName($params['group'][$i]).' ';
			}
		}

		if(isset($params['having']))
			$group.="\nHAVING ".$params['having'];


		$order="";
		if(!empty($params['order'])){
			$order .= "\nORDER BY ";

			if(!is_array($params['order']))
				$params['order']=array($params['order']);

			if(!isset($params['orderDirection'])){
				$params['orderDirection']=array('ASC');
			}elseif(!is_array($params['orderDirection'])){
				$params['orderDirection']=array($params['orderDirection']);
			}

			for($i=0;$i<count($params['order']);$i++){
				if($i>0)
					$order .= ',';

				if ($params['order'][$i] instanceof \go\core\db\Expression) {
				//if(strpos($params['order'][$i], '(')!==false) {
					$order .= $params['order'][$i].' ';
				} else {
					$order .= $this->_quoteColumnName($params['order'][$i]).' ';
					if(isset($params['orderDirection'][$i])){
						$order .= strtoupper($params['orderDirection'][$i])=='ASC' ? 'ASC ' : 'DESC ';
					}else{
						$order .= strtoupper($params['orderDirection'][0])=='ASC' ? 'ASC ' : 'DESC ';
					}
				}
			}
		}

		$limit="";
		if(!empty($params['limit'])){
			if(!isset($params['start']))
				$params['start']=0;

			$limit .= "\nLIMIT ".intval($params['start']).','.intval($params['limit']);
		}


		$sql = $select.$fields.$from.$joins.$where.$group.$order.$limit;
		if($this->_debugSql)
			$this->_debugSql($params, $sql);


		try{


			if($this->_debugSql)
				$start = \GO\Base\Util\Date::getmicrotime();

			$result = $this->getDbConnection()->prepare($sql);

			if(isset($params['criteriaObject'])){
				$criteriaObjectParams = $params['criteriaObject']->getParams();

				foreach($criteriaObjectParams as $param=>$value)
					$result->bindValue($param, $value[0], $value[1]);

				$result->execute();
			}elseif(isset($params['bindParams'])){
				$result = $this->getDbConnection()->prepare($sql);
				$result->execute($params['bindParams']);
			}else
			{
				$result = $this->getDbConnection()->query($sql);
			}

			if($this->_debugSql){
				$end = \GO\Base\Util\Date::getmicrotime();
				GO::debug("SQL Query took: ".($end-$start));
			}

		}catch(\Exception $e){
			$msg = $e->getMessage();

			if(GO::config()->debug){
				$msg .= "\n\nFull SQL Query: ".$sql;

				if(isset($params['bindParams'])){
					$msg .= "\nBind params: ".var_export($params['bindParams'], true);
				}

				if(isset($criteriaObjectParams)){
					$msg .= "\nBind params: ".var_export($criteriaObjectParams, true);
				}

				$msg .= "\n\n".$e->getTraceAsString();

				GO::debug($msg);
			}

			//SQLSTATE[42S22]: Column not found: 1054 Unknown column 'progress' in 'order clause
			if(strpos($msg, 'order clause')!==false && strpos($msg, 'Unknown column')!==false)
			{
				$msg = GO::t("Sorry, you can't sort on that column. Please click on another column header in the grid for sorting.");
			}

			throw new \Exception($msg);
		}

		$AS = new ActiveStatement($result, $this);


		if(!empty($params['calcFoundRows'])){
			if(!empty($params['limit'])){

				//Total numbers are cached in session when browsing through pages.
				if($calcFoundRows){

					if($this->useSqlCalcFoundRows){
//					//TODO: This is MySQL only code
						$sql = "SELECT FOUND_ROWS() as found;";
						$r2 = $this->getDbConnection()->query($sql);
						$record = $r2->fetch(PDO::FETCH_ASSOC);
						//$foundRows = intval($record['found']);
						$foundRows = GO::session()->values[$queryUid]=intval($record['found']);
					}else{
						$countField = is_array($this->primaryKey()) ? '*' : 't.'.$this->primaryKey();

						$sql = $select.'COUNT('.$countField.') AS found '.$from.$joins.$where;

//						GO::debug($sql);

						if($this->_debugSql){
							$this->_debugSql($params, $sql);
							$start = \GO\Base\Util\Date::getmicrotime();
						}

						$r2 = $this->getDbConnection()->prepare($sql);

						if(isset($params['criteriaObject'])){
							$criteriaObjectParams = $params['criteriaObject']->getParams();

							foreach($criteriaObjectParams as $param=>$value)
								$r2->bindValue($param, $value[0], $value[1]);

							$r2->execute();
						}elseif(isset($params['bindParams'])){
							$r2 = $this->getDbConnection()->prepare($sql);
							$r2->execute($params['bindParams']);
						}else
						{
							$r2 = $this->getDbConnection()->query($sql);
						}

						if($this->_debugSql){
							$end = \GO\Base\Util\Date::getmicrotime();
							GO::debug("SQL Count Query took: ".($end-$start));
						}

						$record = $r2->fetch(PDO::FETCH_ASSOC);





						//$foundRows = intval($record['found']);
						$foundRows = GO::session()->values[$queryUid]=intval($record['found']);
					}
				}
				else
				{
					$foundRows=GO::session()->values[$queryUid];
				}


				$AS->foundRows=$foundRows;
			}
		}

//		//$result->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->className());
//		if($fetchObject)
//			$result->setFetchMode(PDO::FETCH_CLASS, $this->className(),array(false));
//		else
//			$result->setFetchMode (PDO::FETCH_ASSOC);

    //TODO these values should be set on findByPk too.
    $AS->findParams=$params;
    if(isset($params['relation']))
      $AS->relation=$params['relation'];


		if(!empty($params['fetchClass'])){
			$AS->stmt->setFetchMode(PDO::FETCH_CLASS, $params['fetchClass']);
		}

    return $AS;
	}

	public function hasCustomFields() {
		return method_exists($this, 'customFieldsTableName');
	}
	
	private function _debugSql($params, $sql){
		
		
		$sqlParams = array();

		if(isset($params['criteriaObject'])){
			
			foreach(($params['criteriaObject']->getParams()) as $param=>$value){				
				$sqlParams[$param]=$value[0];
			}
		}

		if(isset($params['bindParams'])){
			$sqlParams = array_merge($sqlParams, $params['bindParams']);
		}
		
		//sort so that :param1 does not replace :param11 first.
		arsort($sqlParams);
		
		foreach($sqlParams as $param=>$value){
			$sql = str_replace($param, '"'.$value.'"', $sql);
		}

		GO::debug($sql);
	}

	private function _appendAclJoin($findParams, $aclJoinProps){



		$sql = "\nINNER JOIN core_acl_group ON (`".$aclJoinProps['table']."`.`".$aclJoinProps['attribute']."` = core_acl_group.aclId";
		if(isset($findParams['permissionLevel']) && $findParams['permissionLevel']>\GO\Base\Model\Acl::READ_PERMISSION){
			$sql .= " AND core_acl_group.level>=".intval($findParams['permissionLevel']);
		}

		$groupIds = \GO\Base\Model\User::getGroupIds($findParams['userId']);

		if(!empty($findParams['ignoreAdminGroup'])){
			$key = array_search(GO::config()->group_root, $groupIds);
			if($key!==false)
				unset($groupIds[$key]);
		}


		$sql .= " AND core_acl_group.groupId IN (".implode(',',$groupIds).")) ";

		return $sql;
	}

	private function _quoteColumnName($name){

		//disallow \ ` and \00  : http://stackoverflow.com/questions/1542627/escaping-field-names-in-pdo-statements
		if(preg_match("/[`\\\\\\000\(\),]/", $name))
			throw new \Exception("Invalid characters found in column name: ".$name);

		$arr = explode('.',$name);

//		for($i=0,$max=count($arr);$i<$max;$i++)
//			$arr[$i]=$this->getDbConnection ()->quote($arr[$i], PDO::PARAM_STR);

		return '`'.implode('`.`',$arr).'`';
	}

	private function _appendByParamsToSQL($sql, $params){
		if(!empty($params['by'])){

			if(!isset($params['byOperator']))
				$params['byOperator']='AND';

			$first=true;
			$sql .= "\nAND (";
			foreach($params['by'] as $arr){

				$field = $arr[0];
				$value= $arr[1];
				$comparator=isset($arr[2]) ? strtoupper($arr[2]) : '=';

				if($first)
				{
					$first=false;
				}else
				{
					$sql .= $params['byOperator'].' ';
				}

				if($comparator=='IN' || $comparator=='NOT IN'){

					//prevent sql error on empty value
					if(!count($value))
						$value=array(0);

					for($i=0;$i<count($value);$i++)
						$value[$i]=$this->getDbConnection()->quote($value[$i], $this->columns[$field]['type']);

					$sql .= "t.`$field` $comparator (".implode(',',$value).") ";


				}else
				{
					if(!isset($this->columns[$field]['type']))
						throw new \Exception($field.' not found in columns for model '.$this->className());

          $sql .= "t.`$field` $comparator ".$this->getDbConnection()->quote($value, $this->columns[$field]['type'])." ";
				}
			}

			$sql .= ') ';
		}
		return $sql;
	}

	/**
	 * Override this method to supply the fields that the searchQuery argument
	 * will usein the find function.
	 *
	 * By default all fields with type PDO::PARAM_STR are returned
	 *
	 * @return array Field names that should be used for the search query.
	 */
	public function getFindSearchQueryParamFields($prefixTable='t', $withCustomFields=false){
		//throw new \Exception('Error: you supplied a searchQuery parameter to find but getFindSearchQueryParamFields() should be overriden in '.$this->className());
		$fields = array();
		foreach($this->columns as $field=>$attributes){
			
			if($field != 'uuid'){ 
				if(isset($attributes['gotype']) && ($attributes['gotype']=='textfield' || ($attributes['gotype']=='customfield' && $attributes['customfield']->customfieldtype->includeInSearches()))){
					$fields[]='`'.$prefixTable.'`.`'.$field.'`';
				}
			}
		}

//		if($withCustomFields && GO::modules()->customfields && $this->customfieldsRecord  && GO::modules()->customfields->permissionLevel)
//		{
//			$fields = array_merge($fields, $this->customfieldsRecord->getFindSearchQueryParamFields('cf'));
//		}
		return $fields;
	}

	private function _appendPkSQL($sql, $primaryKey=false){
		if(!$primaryKey)
			$primaryKey=$this->pk;

		if(is_array($this->primaryKey())){

			if(!is_array($primaryKey)){
				throw new \Exception('Primary key should be an array for the model '.$this->className());
			}

			$first = true;
			foreach($primaryKey as $field=>$value){
				//TODO: WHY ARE WE SETTING THIS????
				$this->$field=$value;
				if(!$first)
					$sql .= ' AND ';
				else
					$first=false;

				if(!isset($this->columns[$field])){
					throw new \Exception($field.' not found in columns of '.$this->className());
				}

				$sql .= "`".$field.'`='.$this->getDbConnection()->quote($value, $this->columns[$field]['type']);
			}
		}else
		{
			
			//TODO: WHY ARE WE SETTING THIS????
			$this->{$this->primaryKey()}=$primaryKey;

			$sql .= "`".$this->primaryKey().'`='.$this->getDbConnection()->quote($primaryKey, $this->columns[$this->primaryKey()]['type']);
		}
		return $sql;
	}

	/**
	 * Loads the model attributes from the database. It also automatically checks
	 * read permission for the current user.
	 *
	 * @param int $primaryKey
	 * @return static
	 */

	public function findByPk($primaryKey, $findParams=false, $ignoreAcl=false, $noCache=false){

//		if(GO::config()->debug && $findParams != false){
//			throw new \Exception('Adding findparams to findByPk is not yet available');
//		}
		
//		GO::debug($this->className()."::findByPk($primaryKey)");
		if(empty($primaryKey))
			return false;

		//Use cache so identical findByPk calls are only executed once per script request
		if(!$noCache){
			$cachedModel =  GO::modelCache()->get($this->className(), $primaryKey);
//			GO::debug("Cached : ".$this->className()."::findByPk($primaryKey)");
			if($cachedModel){

				if($cachedModel && !$ignoreAcl && !$cachedModel->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)){
					$msg = GO::config()->debug ? $this->className().' pk: '.var_export($this->getPk(), true) : '';
					throw new \GO\Base\Exception\AccessDenied($msg);
				}

				return $cachedModel;
			}
		}

		$sql = "SELECT * FROM `".$this->tableName()."` WHERE ";

		$sql = $this->_appendPkSQL($sql, $primaryKey);

//		GO::debug("DEBUG SQL: ".var_export($this->_debugSql, true));

		if($this->_debugSql)
				GO::debug($sql);

		try{
			$result = $this->getDbConnection()->query($sql);
			$result->model=$this;
			$result->findParams=$findParams;

			$result->setFetchMode(PDO::FETCH_CLASS, $this->className(),array(false));

			$models =  $result->fetchAll();
			$model = isset($models[0]) ? $models[0] : false;
		}catch(PDOException $e){
			$msg = $e->getMessage()."\n\nFull SQL Query: ".$sql;

			throw new \Exception($msg);
		}

		if($model && !$ignoreAcl && !$model->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)){
			$msg = GO::config()->debug ? $this->className().' pk: '.var_export($this->getPk(), true) : '';
			throw new \GO\Base\Exception\AccessDenied($msg);
		}

		if($model)
			GO::modelCache()->add($this->className(), $model);

		return $model;
	}

	/**
	 * Return the number of model records in the database.
	 *
	 * @return int
	 */
	public function count(){
		$stmt = $this->getDbConnection()->query("SELECT count(*) AS count FROM `".$this->tableName()."`");
		$record = $stmt->fetch();
		return $record['count'];
	}

	private function _relationExists($name){
		$r= $this->getRelation($name);

		return $r!=false;
	}

	/**
	 * Get all the relations of this activerecord. Incuding the automatic user and
	 * mUser relation and dynamically added relations.
	 *
	 * @return array
	 */
	public function getRelations(){
		$r= array_merge($this->relations(), self::$_addedRelations);

		if(isset($this->columns['user_id']) && !isset($r['user'])){
			$r['user']=array(
					'type'=>self::BELONGS_TO,
					'model'=>'GO\Base\Model\User',
					'field'=>'user_id',
					'labelAttribute'=>function($model){return $model->user->name;}
					);
		}

		if(isset($this->columns['muser_id']) && !isset($r['mUser'])){
			$r['mUser']=array(
					'type'=>self::BELONGS_TO,
					'model'=>'GO\Base\Model\User',
					'field'=>'muser_id',
					'labelAttribute'=>function($model){return !empty($model->mUser) ? $model->mUser->name : '';}
					);
		}
		
		
//\GO::debug($cfMod);
//		if($this->customfieldsModel()){
//			$r['customfields']=array(
//					'type'=>self::BELONGS_TO,
//					'model'=>$this->customfieldsModel(),
//					'field'=>'id'
//					);
//		}

		return $r;
	}

	public function getRelation($name){

		$r = $this->getRelations();

		$this->_checkRelations($r);

		if(!isset($r[$name]))
			return false;

		$r[$name]['name']=$name;

		return $r[$name];
	}

	private function _checkRelations($r){
		if(GO::config()->debug){
			foreach($r as $name => $attr){
				if(!isset($attr['model']))
					throw new \Exception('model not set in relation '.$name.' '.var_export($attr, true));

				if(isset($this->columns[$name]))
					throw new \Exception("Relation $name conflicts with column attribute in ".$this->className());

				$method = 'get'.ucfirst($name);
				if($method != 'getType' && method_exists($this, $method))
					throw new \Exception("Relation $name conflicts with getter function $method in ".$this->className());

				if($attr['type']==self::BELONGS_TO && !empty($attr['delete'])){
					throw new \Exception("BELONGS_TO Relation $name may not have a delete flag in ".$this->className());
				}
			}
		}
	}

	/**
	 * Get the findparams object used to query a defined relation.
	 *
	 * @param StringHelper $name
	 * @return FindParams
	 * @throws Exception
	 */
	public function getRelationFindParams($name, $extraFindParams=null){

		$r = $this->getRelation($name);

		if(!isset($r['findParams']))
			$r['findParams']=FindParams::newInstance();

		if($r['type']==self::HAS_MANY)
		{


			$findParams = FindParams::newInstance();


			$findParams
					->mergeWith($r['findParams'])
					->ignoreAcl()
					->relation($name);

			//the extra find params supplied with call are merged last so that you
			//can override the defaults.
			if(isset($extraFindParams))
					$findParams->mergeWith($extraFindParams);


			if(is_array($r['field'])){
				foreach($r['field'] as $my=>$foreign){
						$findParams->getCriteria()
								->addCondition($my, $this->$foreign);
				}
			}else{
				$remoteFieldThatHoldsMyPk = $r['field'];

				$findParams->getCriteria()
								->addCondition($remoteFieldThatHoldsMyPk, $this->pk);
			}


		}elseif($r['type']==self::MANY_MANY)
		{

			$findParams = FindParams::newInstance();

			if(isset($extraFindParams))
					$findParams->mergeWith($extraFindParams);

			$findParams->mergeWith($r['findParams'])
					->ignoreAcl()
					->relation($name)
					->linkModel($r['linkModel'], $r['field'], $this->pk);


		}else
		{
			throw new \Exception("getRelationFindParams not supported for ".$r[$name]['type']);
		}

		return $findParams;
	}


	private function _getRelatedCacheKey($relation){
		//append join attribute so cache is void automatically when this attribute changes.

		if(is_array($relation['field']))
			$relation['field']=implode(',', $relation['field']);

		return $relation['name'].':'.(isset($this->_attributes[$relation['field']]) ? $this->_attributes[$relation['field']] : 0);

	}

	private function _getRelated($name, $extraFindParams=null){

		$r = $this->getRelation($name);

		if(!$r)
			return false;

		$model = $r['model'];

		if(!class_exists($model)) //could be a missing module
			return false;



		if($r['type']==self::BELONGS_TO){

			$joinAttribute = $r['field'];

			if(GO::config()->debug && !isset($this->columns[$joinAttribute])){
//				var_dump($this->columns);
				throw new \Exception("You defined a non existing attribute in the 'field'='$joinAttribute' property in relation '$name' in model '".$this->className()."'");
			}

			/**
			 * Related stuff can be put in the relatedCache array for when a relation is
			 * accessed multiple times.
			 *
			 * Related stuff can also be joined in a query and be passed to the __set
			 * function as relation@relation_attribute. This array will be used here to
			 * construct the related model.
			 */

			//append join attribute so cache is void automatically when this attribute changes.
			$cacheKey = $this->_getRelatedCacheKey($r);

			if(isset($this->_joinRelationAttr[$name])){

				$attr = $this->_joinRelationAttr[$name];

				$model=new $model(false);
				$model->loadingFromDatabase = true;
				$model->setAttributes($attr, false);
				$model->castMySqlValues();
				$model->loadingFromDatabase = false;

				unset($this->_joinRelationAttr[$cacheKey]);

				if(!GO::$disableModelCache){
					$this->_relatedCache[$cacheKey] = $model;
				}

				return $model;

			}elseif(!isset($this->_relatedCache[$cacheKey]))
			{
				//In a belongs to relationship the primary key of the remote model is stored in this model in the attribute "field".
				if(!empty($this->_attributes[$joinAttribute])){
					$model = GO::getModel($model)->findByPk($this->_attributes[$joinAttribute], array('relation'=>$name), true);

					if(!GO::$disableModelCache){
						$this->_relatedCache[$cacheKey] = $model;
					}

					return $model;
				}else
				{
					return null;
				}
			}else
			{
				return $this->_relatedCache[$cacheKey];
			}

		}elseif($r['type']==self::HAS_ONE){
			//We can't put this in the related cache because there's no reliable way to check if the situation has changed.

			if(!isset($r['findParams']))
				$r['findParams']=FindParams::newInstance();

			$params =$r['findParams']->relation($name);
			if(is_array($r['field'])) {
				$fieldKeys = array_keys($r['field']);
				$local_key = $fieldKeys[0];
				$fieldValues = array_values($r['field']);
				$foreign_key = $fieldValues[0];
				return empty($this->pk) ? false : GO::getModel($model)->findSingleByAttribute($foreign_key, $this->{$local_key}, $params);
			} else {
				//In a has one to relation ship the primary key of this model is stored in the "field" attribute of the related model.
				return empty($this->pk) ? false : GO::getModel($model)->findSingleByAttribute($r['field'], $this->pk, $params);
			}
		}else{
			$findParams = $this->getRelationFindParams($name,$extraFindParams);

			$stmt = GO::getModel($model)->find($findParams);
      return $stmt;
		}
	}

	/**
	 * Formats user input for the database.
	 *
	 * @param array $attributes
	 * @return array
	 */
	protected function formatInputValues($attributes){
		$formatted = array();
		foreach($attributes as $key=>$value){
			$formatted[$key]=$this->formatInput($key, $value);
		}
		return $formatted;
	}

	/**
	 * Formats user input for the database.
	 *
	 * @param StringHelper $column
	 * @param mixed $value
	 * @return array
	 */
	public function formatInput($column, $value){
			if(!isset($this->columns[$column]['gotype'])){
				//don't process unknown columns. But keep them for flexibility.
				return $value;
			}

			switch($this->columns[$column]['gotype']){
				
				case 'time':
					return \GO\Base\Util\Date::toDbTime($value);
					break;
				
				case 'unixdate':
				case 'unixtimestamp':
					if($this->columns[$column]['null'] && ($value=="" || $value==null))
						return null;
					else
						return  \GO\Base\Util\Date::to_unixtime($value);

					break;
				case 'number':
					$value= \GO\Base\Util\Number::unlocalize($value);

					if($value===null && !$this->columns[$column]['null'])
						$value=0;

					return $value;
					break;

				case 'phone':

					//if it contains alpha chars then leave it alone.
					if(preg_match('/[a-z]+/i', $value)){
						return $value;
					}else{
						return trim(preg_replace('/[\s-_\(\)]+/','', $value));
					}
					break;
				case 'boolean':
					$ret= empty($value) || $value==="false" ? 0 : 1;
					return $ret;
					break;
				case 'date':
					return  \GO\Base\Util\Date::to_db_date($value);
					break;
				case 'datetime':
					if(empty($value))
					{
						return null;
					}
					$time = \GO\Base\Util\Date::to_unixtime($value);
					if(!$time)
					{
						return null;
					}
					$date_format =  'Y-m-d H:i:s';
					return date($date_format, $time);
					break;
				case 'textfield':
					return (string) $value;
					break;
				default:
					if($this->columns[$column]['type']==PDO::PARAM_INT){
						if($this->columns[$column]['null'] && $value=="")
							$value=null;
						else
							$value = intval($value);
					}

					return  $value;
					break;
			}
	}

	/**
	 * Format database values for display in the user's locale.
	 *
	 * @param bool $html set to true if it's used for html output
	 * @return array
	 */
	protected function formatOutputValues($html=false){

		$formatted = array();
		foreach($this->_attributes as $attributeName=>$value){
			$formatted[$attributeName]=$this->formatAttribute($attributeName, $value, $html);
		}

		return $formatted;
	}

	public function formatAttribute($attributeName, $value, $html=false){
		if(!isset($this->columns[$attributeName]['gotype'])){
			return $value;			
		}

		switch($this->columns[$attributeName]['gotype']){

			case 'time':
				return \GO\Base\Util\Date::formatTime($value);
				break;
			
			case 'unixdate':
				return \GO\Base\Util\Date::get_timestamp($value, false);
				break;

			case 'unixtimestamp':
				return \GO\Base\Util\Date::get_timestamp($value);
				break;

			case 'textarea':
				if($html){
					return \GO\Base\Util\StringHelper::text_to_html($value);
				}else
				{
					return $value;
				}
				break;

			case 'date':
				//strtotime hangs a while on parsing 0000-00-00 from the database. There shouldn't be such a date in it but
				//the old system stored dates like this.

				if($value == "0000-00-00" || empty($value))
					return "";

				$date = new \DateTime($value);
				return $date->format(GO::user()?GO::user()->completeDateFormat:GO::config()->getCompleteDateFormat());

				//return $value != '0000-00-00' ? \GO\Base\Util\Date::get_timestamp(strtotime($value),false) : '';
				break;

			case 'datetime':

				if($value == "0000-00-00" || empty($value))
					return null;

				$date = new \DateTime($value);
				return $date->format('c');
				break;

			case 'number':
				$decimals = isset($this->columns[$attributeName]['decimals']) ? $this->columns[$attributeName]['decimals'] : 2;
				return \GO\Base\Util\Number::localize($value, $decimals);
				break;

			case 'boolean':
//				Formatting as yes no breaks many functions
//				if($html)
//					return !empty($value) ? GO::t("Yes") : GO::t("No");
//				else
					return !empty($value);
				break;

			case 'raw':
			case 'html':
				return $value;
				break;

			case 'phone':
				if($html){
					if(!preg_match('/[a-z]+/i', $value)){
						if(  preg_match( '/^(\+\d{2})(\d{2})(\d{3})(\d{4})$/', $value,  $matches ) )
						{
							return $matches[1] . ' ' .$matches[2] . ' ' . $matches[3].' ' . $matches[4];
						}elseif(preg_match( '/^(\d*)(\d{3})(\d{4})$/', $value,  $matches)){
							return '('.$matches[1] . ') ' .$matches[2] . ' ' . $matches[3];
						}
					}
				}
				return $value;

				break;

			default:
				if(substr($this->columns[$attributeName]['dbtype'],-3)=='int')
					return $value;
				else
					return $html ? htmlspecialchars($value, ENT_COMPAT,'UTF-8') : $value;
				break;
		}
	}

	/**
	 * This function is used to set attributes of this model from a controller.
	 * Input may be in regional format and the model will translate it to the
	 * database format.
	 *
	 * All attributes will be set even if the attributes don't exist in the model.
	 * The only exception if for relations. You can't set an attribute named
	 * "someRelation" if it exists in the relations.
	 *
	 * The attributes array may also contain custom fields. They will be saved
	 * automatically.
	 *
	 * @param array $attributes attributes to set on this object
	 */

	public function setAttributes($attributes, $format=null){
		
		if(!isset($format)){
			$format = ActiveRecord::$formatAttributesByDefault;
		}
		
		if($format)
			$attributes = $this->formatInputValues($attributes);

		foreach($attributes as $key=>$value){

			//only set writable properties. It should either be a column or setter method.
			if(isset($this->columns[$key]) || property_exists($this, $key) || method_exists($this, 'set'.$key)){
				$this->$key=$value;
			}elseif(is_array($value) && $this->getRelation($key)){
				$this->_joinRelationAttr[$key]=$value;
			}
		}
	}



	/**
	 * Returns all column attribute values.
	 * Note, related objects are not returned.
	 * @param StringHelper $outputType Can be
	 *
	 * raw: return values as they are stored in the db
	 * formatted: return the values formatted for an input form
	 * html: Return the values formatted for HTML display
	 *
	 * @return array attribute values indexed by attribute names.
	 */
	public function getAttributes($outputType=null)
	{

		if(!isset($outputType)){
			$outputType = ActiveRecord::$formatAttributesByDefault ? 'formatted' : 'raw';
		}

		if($outputType=='raw')
			$att=$this->_attributes;
		else
			$att=$this->formatOutputValues($outputType=='html');

		if($this->aclOverwrite()) {
			$att['acl_overwritten']=$this->isAclOverwritten();
		}
		foreach($this->_getMagicAttributeNames() as $attName){
			$att[$attName]=$this->$attName;
		}

		return $att;
	}

	/**
	 * Get a selection of attributes
	 *
	 * @param array $attributeNames
	 * @param StringHelper $outputType
	 * @return array
	 */
	public function getAttributeSelection($attributeNames, $outputType='formatted'){
		$att=array();
		foreach($attributeNames as $attName){
			if(substr($attName, 0, 13) === 'customFields.') {
				$att[$attName]=$this->getCustomFields()[substr($attName, 13)] ?? null;
			}else if(isset($this->columns[$attName])){
				$att[$attName]=$this->getAttribute($attName, $outputType);
			}elseif($this->hasAttribute($attName)){
				$att[$attName]=$this->$attName;
			}else {
				$att[$attName]=null;
			}
		}
		return $att;
	}

	private static $_magicAttributeNames;

	private function _getMagicAttributeNames(){

		if(!isset(self::$_magicAttributeNames))
			self::$_magicAttributeNames=GO::cache ()->get('magicattributes');

		if(!isset(self::$_magicAttributeNames[$this->className()])){
			self::$_magicAttributeNames[$this->className()]=array();
			$r = new \ReflectionObject($this);
			$publicProperties = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
			foreach($publicProperties as $prop){
				//$att[$prop->getName()]=$prop->getValue($this);
				//$prop = new \ReflectionProperty();
				if(!$prop->isStatic()) {
					//$this->_magicAttributeNames[]=$prop->getName();
					self::$_magicAttributeNames[$this->className()][]=$prop->name;
				}
			}

//			$methods = $r->getMethods();
//
//			foreach($methods as $method){
//				$methodName = $method->getName();
//				if(substr($methodName,0,3)=='get' && !$method->getNumberOfParameters()){
//
//					echo $propName = strtolower(substr($methodName,3,1)).substr($methodName,4);
//
//					$this->_magicAttributeNames[]=$propName;
//				}
//			}
//
			GO::cache ()->set('magicattributes', self::$_magicAttributeNames);
		}
		return self::$_magicAttributeNames[$this->className()];
	}


	/**
	 * Returns all columns
	 *
	 * @see ActiveRecord::$columns
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Returns a column specification see $this->columns;
	 *
	 * @see ActiveRecord::$columns
	 * @return array
	 */
	public function getColumn($name)
	{
		if(!isset($this->columns[$name]))
			return false;
		else
			return $this->columns[$name];
	}

	/**
	 * Checks all the permissions
	 *
	 * @todo new item's which don't have ACL should check different ACL for adding new items.
	 * @return boolean
	 */
	public function checkPermissionLevel($level){

		if(!$this->aclField())
			return true;

		if($this->getPermissionLevel()==-1)
			return true;

		return $this->getPermissionLevel()>=$level;
	}

	public function hasPermissionLevel($level) {
		return $this->checkPermissionLevel($level);
	}

	/**
	 * Check when the permissions level was before moving the object to a differend
	 * related ACL object eg. moving contact to different addressbook
	 * @param int $level permissio nlevel to check for
	 * @return boolean if the user has the specified level
	 * @throws Exception if the ACL is not found
	 */
	public function checkOldPermissionLevel($level) {

		$arr = explode('.', $this->aclField());
		$relation = array_shift($arr);
		$r = $this->getRelation($relation);
		$aclFKfield = $r['field'];

		$oldValue = $this->getOldAttributeValue($aclFKfield);
		if(empty($oldValue))
			return true;
		//TODO: check if above code is needed (test by moving contact to differend addresbook)

		$acl_id = $this->_getOldParentAclId();
		$result = \GO\Base\Model\Acl::getUserPermissionLevel($acl_id)>=$level;

		return $result;
	}

	/**
	 * If the related object the contains the ACL is changed this function will
	 * retrun the ACL of the relational object before it was changed (old ACL)
	 * @return integer The ACL id
	 * @throws \Exception
	 */
	private function _getOldParentAclId() {
		$arr = explode('.', $this->aclField());
		$relation = array_shift($arr);
		$r = $this->getRelation($relation);
		$aclFKfield = $r['field'];

		$oldValue = $this->getOldAttributeValue($aclFKfield);

		if(empty($oldValue))
			return $this->findAclId();

		$newValue = $this->{$aclFKfield};
		$this->{$aclFKfield} = $oldValue;
		$acl_id = $this->findAclId();
		$this->{$aclFKfield} = $newValue;

		if(!$acl_id)
			throw new \Exception("Could not find ACL for ".$this->className()." with pk: ".$this->pk);

		return $acl_id;
	}

	public function isAclOverwritten() {
		if(!$this->aclField() || !$this->aclOverwrite() || $this->getIsNew() || !$this->isJoinedAclField){
			return false;
		}
		
		$relatedAclModel = $this->findRelatedAclModel();
				
				
//		if(!$relatedAclModel)
//			throw new \Exception(var_export($relatedAclModel, true));
		
		return $relatedAclModel && $relatedAclModel->findAclId() != $this->{$this->aclOverwrite()};
	}

	/**
		* Returns a value indicating whether the attribute is required.
		* This is determined by checking if the attribute is associated with a
		* {@link CRequiredValidator} validation rule in the current {@link scenario}.
		* @param StringHelper $attribute attribute name
		* @return boolean whether the attribute is required
		*/
	public function isAttributeRequired($attribute)
	{
		  if(!isset($this->columns[$attribute]))
				return false;
			return $this->columns[$attribute]['required'];
	}

	/**
	 * Do some things before the model will be validated.
	 */
	protected function beforeValidate(){

	}

	/**
	 * Add a custom validation rule for a column.
	 *
	 * Examples of rules:
	 *
	 * 'required'=>true, //Will be true automatically if field in database may not be null and doesn't have a default value
	 * 'length'=><max length of the value>, //Autodetected from db
	 * 'validator'=><a function to call to validate the value>, This may be an array: array("Class", "method", "error message").
	 * 'gotype'=>'number|textfield|textarea|unixtimestamp|unixdate|user', //Autodetected from db as far as possible. See loadColumns()
	 * 'decimals'=>2//only for gotype=number)
	 * 'regex'=>'A preg_match expression for validation',
	 * 'unique'=>false //true to enforce a unique value
	 * 'greater'=>'start_time' //this column must be greater than column start time
	 * 'greaterorequal'=>'start_time' //this column must be greater or equal to column start time
	 *
	 * @param StringHelper $columnName
	 * @param StringHelper $ruleName
	 * @param mixed $value
	 */
	public function setValidationRule($columnName, $ruleName, $value){
		if(!isset($this->columns[$columnName]))
			throw new \Exception("Column $columnName is unknown");
		$this->columns[$columnName][$ruleName]=$value;

		$this->_runTimeValidationRules[$columnName]=true;
	}

	private $_runTimeValidationRules=array();

	/**
	 * Validates all attributes of this model
	 *
	 * @return boolean
	 */

	public function validate(){

		//foreach($this->columns as $field=>$attributes){
		$this->beforeValidate();

		if($this->isNew){
			//validate all columns
			$fieldsToCheck = array_keys($this->columns);
		}else
		{
			//validate modified columns
			$fieldsToCheck = array_keys($this->getModifiedAttributes());

			//validate columns with validation rules that were added by controllers
			//with setValidateionRule
			if(!empty($this->_runTimeValidationRules)){
				$fieldsToCheck= array_unique(array_merge(array_keys($this->_runTimeValidationRules)));
			}
		}

		foreach($fieldsToCheck as $field){

			$attributes=$this->columns[$field];

			if(!empty($attributes['required']) && empty($this->_attributes[$field]) && $this->_attributes[$field] !== '0'){
				$this->setValidationError($field, sprintf(GO::t("Field '%s' is required"),$this->getAttributeLabel($field)));
			}elseif(!empty($attributes['length']) && !empty($this->_attributes[$field]) && \GO\Base\Util\StringHelper::length($this->_attributes[$field])>$attributes['length'])
			{
				$this->setValidationError($field, sprintf(GO::t("Field %s is longer than the maximum of %s characters"),$this->getAttributeLabel($field),$attributes['length']));
			}elseif(!empty($attributes['regex']) && !empty($this->_attributes[$field]) && !preg_match($attributes['regex'], $this->_attributes[$field]))
			{
				$this->setValidationError($field, sprintf(GO::t("Field %s is formatted incorrectly"),$this->getAttributeLabel($field)).' ('.$this->$field.')');
			}elseif(!empty($attributes['greater']) && !empty($this->_attributes[$field])){
				if($this->_attributes[$field]<=$this->_attributes[$attributes['greater']])
					$this->setValidationError($field, sprintf(GO::t("Field '%s' must be greater than '%s'"), $this->getAttributeLabel($field), $this->getAttributeLabel($attributes['greater'])));
			}elseif(!empty($attributes['greaterorequal']) && !empty($this->_attributes[$field])){
				if($this->_attributes[$field]<$this->_attributes[$attributes['greaterorequal']])
					$this->setValidationError($field, sprintf(GO::t("Field '%s' must be greater or equal than '%s'"), $this->getAttributeLabel($field), $this->getAttributeLabel($attributes['greaterorequal'])));
			}else {
				$this->_validateValidatorFunc ($attributes, $field);
			}
		}

		$this->_validateUniqueColumns();

		$this->fireEvent('validate',array(&$this));
	
		return !$this->hasValidationErrors();
	}

	private function _validateValidatorFunc($attributes, $field){
		$valid=true;
		if(!empty($attributes['validator']) && !empty($this->_attributes[$field]))
		{
			if(is_array($attributes['validator']) && count($attributes['validator'])==3){
				$errorMsg = array_pop($attributes['validator']);
			}else
			{
				$errorMsg = GO::t("Field %s was invalid");
			}

			$valid = call_user_func($attributes['validator'], $this->_attributes[$field]);
			if(!$valid)
				$this->setValidationError($field, sprintf($errorMsg,$this->getAttributeLabel($field)));
		}

		return $valid;
	}

	private function _validateUniqueColumns(){
		foreach($this->columns as $field=>$attributes){

			if(!empty($attributes['unique']) && !empty($this->_attributes[$field])){

				$relatedAttributes = array($field);
				if(is_array($attributes['unique']))
					$relatedAttributes = array_merge($relatedAttributes,$attributes['unique']);

				$modified = false;
				foreach($relatedAttributes as $relatedAttribute){
					if($this->isModified($relatedAttribute))
						$modified=true;
				}

				
				$where = array();
				if($modified){
					$criteria = FindCriteria::newInstance()
								->addModel(GO::getModel($this->className()))
								->addCondition($field, $this->_attributes[$field]);

					if(is_array($attributes['unique'])){
						foreach($attributes['unique'] as $f){
							if(isset($this->_attributes[$f])){
								$criteria->addCondition($f, $this->_attributes[$f]);
								$where[$f] = $this->_attributes[$f];
							}
						}
					}

					if(!$this->isNew){
						$where[$this->primaryKey()] = $this->pk;
						$criteria->addCondition($this->primaryKey(), $this->pk, '!=');
					}

					$existing = $this->findSingle(FindParams::newInstance()
									->ignoreAcl()
									->criteria($criteria)
					);

					if($existing) {

						$msg = str_replace(array('%cf','%val'),array($this->getAttributeLabel($field), $this->_attributes[$field]),GO::t("The value \"%val\" entered for the field \"%cf\" already exists in the database. The field value must be unique. Please enter a different value in that field.", "customfields"));
						
						if(\GO::config()->debug){
							$msg .= var_export($where, true);
						}
						
						$this->setValidationError($field, $msg);
//						$this->setValidationError($field, sprintf(GO::t("%s \"%s\" already exists"),$this->localizedName, $this->_attributes[$field]));
					}
				}
			}
		}
	}


//	public function getFilesFolder(){
//		if(!$this->hasFiles())
//			throw new \Exception("getFilesFolder() called on ".$this->className()." but hasFiles() is false for this model.");
//
//		if($this->files_folder_id==0)
//			return false;
//
//		return \GO\Files\Model\Folder::model()->findByPk($this->files_folder_id);
//
//	}

	/**
	 * Get the column name of the field this model sorts on.
	 * It will automatically give the highest number to new models.
	 * Useful in combination with \GO\Base\Controller\AbstractModelController::actionSubmitMultiple().
	 * Drag and drop actions will save the sort order in that action.
	 *
	 * @return StringHelper
	 */
	public function getSortOrderColumn(){
		return false;
	}

	/**
	 * Just update the mtime timestamp
	 */
	public function touch(){
		if ($this->getColumn('mtime')) {
			$time = time();
			if($this->mtime==$time){
				return true;
			}else{
				$this->mtime=time();
				return $this->_dbUpdate();
			}
		}
		
		if ($this->getColumn('modifiedAt')) {
			$this->modifiedAt = gmdate('Y-m-d H:i:s');
			return $this->_dbUpdate();			
		}
	}

	/**
	 * Return true if an update qwery for this record is require override if needed
	 * @return boolean true if dbupdate if required
	 */
	protected function dbUpdateRequired(){
		return $this->_forceSave || $this->isNew || $this->isModified();// || ($this->customfieldsRecord  !$this->customfieldsRecord->isModified());
	}

	/**
	 * We need to get the modified file columns before save because we need the ID field
	 * for the filePathTemplate.
	 */
	private function _getModifiedFileColumns(){

		$cols = array();
		$modified = $this->isNew ? $this->columns : $this->getModifiedAttributes();
		foreach($modified as $column=>$void){
			if($this->columns[$column]['gotype']=='file'){
				$cols[$column]=$this->_attributes[$column];

				if(!$this->isNew){
					$this->resetAttribute($column);
				}else
				{
					$this->_attributes[$column]="";
				}
			}
		}

		return $cols;
	}


	private function _processFileColumns($cols){


		foreach($cols as $column=>$newValue){

			$oldValue = $this->_attributes[$column];

			if(empty($newValue)){

				//unset of file column
				if(!empty($oldValue)){
					$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$oldValue);
					$file->delete();
					$this->$column="";
				}
			}elseif($newValue instanceof \GO\Base\Fs\File)
			{
				if(!isset($this->columns[$column]['filePathTemplate'])){
					throw new \Exception('For file columns you must set a filePathTemplate');
				}
				$destination = $this->columns[$column]['filePathTemplate'];
				foreach($this->_attributes as $key=>$value){
					$destination = str_replace('{'.$key.'}', $value, $destination);
				}
				$destination = str_replace('{extension}', $newValue->extension(), $destination);

				$destinationFile = new \GO\Base\Fs\File(GO::config()->file_storage_path.$destination);
				$destinationFolder = $destinationFile->parent();
				$destinationFolder->create();

				$newValue->move($destinationFolder, $destinationFile->name());
				$this->$column=$destinationFile->stripFileStoragePath();


			}else
			{
				throw new \Exception("Column $column must be an instance of GO\Base\Fs\File. ".var_export($newValue, true));
			}
		}

		return !empty($cols);
	}

	private function _duplicateFileColumns(ActiveRecord $duplicate){


		foreach($this->columns as $column=>$attr){
			if($attr['gotype']=='file'){
				if(!empty($this->_attributes[$column])){

					$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$this->_attributes[$column]);

					$tmpFile = \GO\Base\Fs\File::tempFile('', $file->extension());

					$file->copy($tmpFile->parent(), $tmpFile->name());

					$duplicate->$column=$tmpFile;
				}
			}
		}

	}

	/**
	 * Get the URL to download a file column
	 *
	 * @param StringHelper $column
	 * @return StringHelper
	 */
	public function getFileColumnUrl($column){

		$value= isset($this->_attributes[$column]) ? $this->_attributes[$column] : null;
		if(empty($value))
			return false;

		if(substr($this->logo,0,7)=='public/'){
			return GO::url('core/downloadPublicFile',array('path'=>substr($value,7)));
		}else
		{
			return GO::url('files/file/download',array('path'=>substr($value,7)));
		}

	}
	
	private function _moveAllowed() {
		$moveAllowed = $this->isNew || !$this->_aclModified() || $this->checkOldPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION);
		
		if(!$moveAllowed){
			
			$allow = false;
			$this->fireEvent('moveallowed', array($this, &$allow));
			return $allow;
		}
				
		return $moveAllowed;
	}

	protected function _trimSpacesFromAttributes() {
		if(!static::$trimOnSave)
			return;
		foreach($this->columns as $field=>$col){
      
      if(!isset($col['type'])) {
        throw new \Exception("Column $field has no type. Does it exist in the database?");
      }
			if(isset($this->_attributes[$field]) && $col['type'] == \PDO::PARAM_STR){
				$this->_attributes[$field] = trim($this->_attributes[$field]);
			}
		}
	}


	/**
	 * Saves the model to the database
	 *
	 * @var boolean $ignoreAcl
	 * @return boolean
	 */

	public function save($ignoreAcl=false){

		//GO::debug('save'.$this->className());

		if(!$ignoreAcl && !$this->checkPermissionLevel($this->isNew?\GO\Base\Model\Acl::CREATE_PERMISSION:\GO\Base\Model\Acl::WRITE_PERMISSION)){
			$msg = GO::config()->debug ? $this->className().' pk: '.var_export($this->pk, true).' acl_id: '.$this->_acl_id : '';
			throw new \GO\Base\Exception\AccessDenied($msg);
		}

		// when foreignkey to acl field changes check PermissionLevel of origional related ACL object as well
		if(!$this->_moveAllowed()){
			$msg = GO::config()->debug ? $this->className().' pk: '.var_export($this->pk, true) : sprintf(GO::t("%s item(s) cannot be moved, you do not have the right permissions."),'1');
			throw new \GO\Base\Exception\AccessDenied($msg);
		}

		//use private customfields record so it's accessed only when accessed before
		if(!$this->validate()){
			return false;
		}

		
		/*
		 * Set some common column values
		*/
//GO::debug($this->mtime);

		if($this->dbUpdateRequired()){
			if(isset($this->columns['mtime']) && (!$this->isModified('mtime') || empty($this->mtime)))//Don't update if mtime was manually set.
				$this->mtime=time();
			if(isset($this->columns['ctime']) && empty($this->ctime)){
				$this->ctime=time();
			}
		}

		if (isset($this->columns['muser_id']) && isset($this->_modifiedAttributes['mtime']))
			$this->muser_id=GO::user() ? GO::user()->id : 1;
		
		if(isset($this->columns['modifiedBy'])) {
			$this->modifiedBy = GO::user() ? GO::user()->id : 1;
		}
		
		if(isset($this->columns['modifiedAt'])) {
			$this->modifiedAt = gmdate("Y-m-d H:i:s");
		}
		
		if(isset($this->columns['createdAt']) && empty($this->createdAt)) {
			$this->createdAt = gmdate("Y-m-d H:i:s");
		}
		
		if(isset($this->columns['createdBy']) && empty($this->createdBy)) {
			$this->createdBy = GO::user() ? GO::user()->id : 1;
		}
		
		//user id is set by defaultAttributes now.
		//do not use empty() here for checking the user id because some times it must be 0. eg. core_acl_group
//		if(isset($this->columns['user_id']) && !isset($this->user_id)){
//			$this->user_id=GO::user() ? GO::user()->id : 1;
//		}


		/**
		 * Useful event for modules. For example custom fields can be loaded or a files folder.
		 */
		if($this->fireEvent('beforesave',array(&$this))===false)
				return false;

		$fileColumns = $this->_getModifiedFileColumns();

		if($this->aclOverwrite()) {

			if($this->overwriteAcl !== null) {
				if($this->overwriteAcl && !$this->isAclOverwritten()) { //Overwrite
					
					
					$oldAcl = $this->findRelatedAclModel()->acl;
					if($oldAcl->getUserLevel() < \GO\Base\Model\Acl::MANAGE_PERMISSION) {
						throw new \GO\Base\Exception\AccessDenied("You're not allowed to change permissions");
					}					
					
					$user_id = !empty($this->user_id) ? $this->user_id : 0;
					$acl = new \GO\Base\Model\Acl();
					$acl->usedIn=$this->tableName().'.'.$this->aclOverwrite();
					$acl->ownedBy=$oldAcl->ownedBy;
					$acl->entityTypeId = $this->entityType()->getId();
					$acl->entityId = $this->id;
					$acl->save();
					
					$oldAcl->copyPermissions($acl);
					
					
					// Attach new ACL id to this object
					$this->{$this->aclOverwrite()} = $acl->id;
				} elseif(!$this->overwriteAcl && $this->isAclOverwritten()) { // Disoverwrite
					$acl = \GO\Base\Model\Acl::model()->findByPk($this->{$this->aclOverwrite()});
					$acl->delete();
					$this->{$this->aclOverwrite()} = $this->findRelatedAclModel()->findAclId();
				}
			}
//			if(!$this->isAclOverwritten() && $this->isJoinedAclField)
//				$this->{$this->aclOverwrite()} = $this->findRelatedAclModel()->findAclId();
		}

		$this->_trimSpacesFromAttributes();

		if($this->isNew){

			//automatically set sort order column
			if($this->getSortOrderColumn())
				$this->{$this->getSortOrderColumn()}=$this->nextSortOrder();

			$wasNew=true;

			if($this->aclField() && !$this->isJoinedAclField && empty($this->{$this->aclField()})){
				//generate acl id
				if(!empty($this->user_id))
					$newAcl = $this->setNewAcl($this->user_id);
				else
					$newAcl = $this->setNewAcl(GO::user() ? GO::user()->id : 1);
			}

			
			
			if(!$this->beforeSave()){
				GO::debug("WARNING: ".$this->className()."::beforeSave returned false or no value");
				return false;
			}
			
			if($this->hasFiles()){
				$this->files_folder_id = 0;
			}

			$this->_dbInsert();
			$lastInsertId = $this->getDbConnection()->lastInsertId();

			if(isset($newAcl)) {
				$newAcl->entityId = $lastInsertId;
				$newAcl->save();
			}
			
			if(!is_array($this->primaryKey())){				
				if(empty($this->{$this->primaryKey()})){
					
					if(!$lastInsertId) {
						throw new \Exception("Could not get insert ID: $lastInsertId in ".$this->className()."; attributes: ".var_export($this->_attributes, true));
					}
					$this->{$this->primaryKey()} = $lastInsertId;
					$this->castMySqlValues(array($this->primaryKey()));
				}
				
				if(empty($this->{$this->primaryKey()})){
					return false;
				}
			}			
			
			
			if ($this->hasFiles() && GO::modules()->isInstalled('files')) {		
				$this->checkModelFolder();				
			}

			$this->setIsNew(false);		

			$changed  = $this->_processFileColumns($fileColumns);
			if($changed || $this->afterDbInsert() || $this->isModified('files_folder_id')){
				$this->_dbUpdate();
			}
		}else
		{
			$wasNew=false;

			$this->_processFileColumns($fileColumns);
			
			
			//change ACL owner
			if($this->aclField() && $this->isModified('user_id')) {
				$this->acl->ownedBy = $this->user_id;
				$this->acl->save();
			}


			if ($this->hasFiles() && GO::modules()->isInstalled('files')) {
				//ACL must be generated here.
				$fc = new \GO\Files\Controller\FolderController();
				$this->files_folder_id = $fc->checkModelFolder($this);
			}

			if(!$this->beforeSave()){
				GO::debug("WARNING: ".$this->className()."::beforeSave returned false or no value");
				return false;
			}


			if($this->dbUpdateRequired() && !$this->_dbUpdate())
				return false;
		}

		//TODO modified custom fields attr?
		
		$this->log($wasNew ? \GO\Log\Model\Log::ACTION_ADD : \GO\Log\Model\Log::ACTION_UPDATE,true, false);

		if($this->hasCustomFields() && !$this->saveCustomFields()) {
			return false;
		}

		if(!$this->afterSave($wasNew)){
			GO::debug("WARNING: ".$this->className()."::afterSave returned false or no value");
			return false;
		}

		if(!$wasNew){
			$this->_fixLinkedEmailAcls();
		}

		/**
		 * Useful event for modules. For example custom fields can be loaded or a files folder.
		 */
		$this->fireEvent('save',array(&$this,$wasNew));


		$this->cacheSearchRecord();

		$this->_modifiedAttributes = array();

		return true;
	}
	
	protected function nextSortOrder() {
		return $this->count();
	}
	
	protected function checkModelFolder() {
		//ACL must be generated here.
		$fc = new \GO\Files\Controller\FolderController();
		$this->files_folder_id = $fc->checkModelFolder($this);

	}

	/**
	 * Get the message for the log module. Returns the contents of the first text column by default.
	 *
	 * @return StringHelper
	 */
	public function getLogMessage($action){

		$attr = $this->getCacheAttributes();
		if($attr){
			$msg = $attr['name'];
			if(isset($attr['description']))
				$msg.="\n".$attr['description'];
			return $msg;
		}else
			return false;
	}

	/**
	 * Get the JSON data string for the given log action
	 * 
	 * @param string $action
	 * @return array Data for the JSON string 
	 */
	public function getLogJSON($action,$modifiedCustomfieldAttrs=false){
		
		$cutoffString = ' ..Cut off at 500 chars.';
		$cutoffLength = 500;
		
		switch($action){
			case \GO\Log\Model\Log::ACTION_DELETE:
				return $this->getAttributes();
			case \GO\Log\Model\Log::ACTION_UPDATE:
				$oldValues = $this->getModifiedAttributes();
								
				$modifications = array();
				foreach($oldValues as  $key=>$oldVal){
					
					if(!is_scalar($oldVal)) {
						continue;
					}
					
					$newVal = $this->getAttribute($key);
					
					if(!is_scalar($newVal)) {
						continue;
					}
					
//					// Check if the value changed from false, to null
//					if(is_null($newVal) && $oldVal === false){
//						continue;
//					}
//					
					// Check if the value changed from false, to null
					if(empty($newVal) && empty($oldVal)){
						continue;
					}

					if(strlen($newVal) > $cutoffLength){
						$newVal = substr($newVal,0,$cutoffLength).$cutoffString;
					}
					
					if(strlen($oldVal) > $cutoffLength){
						$oldVal = substr($oldVal,0,$cutoffLength).$cutoffString;
					}
					
					$modifications[$key]=array($oldVal,$newVal);	
				}
				
				// Also track customfieldsrecord changes
//				if($this->customfieldsRecord && $modifiedCustomfieldAttrs){
//										
//					foreach($modifiedCustomfieldAttrs as  $key=>$oldVal){
//						$newVal = $this->customfieldsRecord->getAttribute($key);
//						if(empty($newVal) && empty($oldVal)){
//						continue;
//					}
//
//					if(strlen($newVal) > $cutoffLength){
//						$newVal = substr($newVal,0,$cutoffLength).$cutoffString;
//					}
//					
//					if(strlen($oldVal) > $cutoffLength){
//						$oldVal = substr($oldVal,0,$cutoffLength).$cutoffString;
//					}
//					
//					$attrLabel = $this->getCustomfieldsRecord()->getAttributeLabelWithoutCategoryName($key);
//					
//					$modifications[$attrLabel.' ('.$key.')']=array($oldVal,$newVal);	
//					}
//				}
				
				
				return $modifications;
			case \GO\Log\Model\Log::ACTION_ADD:
				$attrs =  $this->getAttributes();
				$logAttrs = array();
				foreach($attrs as $attr=>$val){
					
					if(!is_scalar($val)) {
						continue;
					}
					
					$newVal = $this->getAttribute($attr);
					
					if(!is_scalar($newVal)) {
						continue;
					}
					
					if(strlen($val) > $cutoffLength){
						$newVal = substr($newVal,0,$cutoffLength).$cutoffString;
					}
					
					$logAttrs[$attr] = $newVal;
				}
								
				return $logAttrs;
		}
		
		return array();
	}

	public static $log_enabled = true;
	
	/**
	 * Will all a log record in go_log
	 * Made protected to be used in \GO\Files\Model\File
	 * @param StringHelper $action
	 * @param boolean $save set the false to not directly save the create Log record
	 * @return boolean|\GO\Log\Model\Log returns the created log or succuss status when save is true
	 */
	protected function log($action, $save=true, $modifiedCustomfieldAttrs=false){
		// jsonData field in go_log might not exist yet during upgrade
		if(!self::$log_enabled) {
			return true;
		}
		$message = $this->getLogMessage($action);
		if($message && GO::modules()->isInstalled('log')){
			
			$data = $this->getLogJSON($action,$modifiedCustomfieldAttrs);
			
			$log = new \GO\Log\Model\Log();

			$pk = $this->pk;
			$log->model_id=is_array($pk) ? var_export($pk, true) : $pk;

			$log->action=$action;
			$log->model=$this->className();
			$log->message = $message;
			$log->object=$this;
			$log->jsonData = json_encode($data);
			if($save)
				return $log->save();
			else
				return $log;
		}
	}

	/**
	 * Acl id's of linked emails are copies from the model they are linked too.
	 * For example an e-mail linked to a contact will get the acl id of the addressbook.
	 * When you move a contact to another contact all the acl id's must change.
	 */
	private function _fixLinkedEmailAcls(){
		if($this->hasLinks() && GO::modules()->isInstalled('savemailas')){
			$arr = explode('.', $this->aclField());
			if (count($arr) > 1) {

				$relation = $this->getRelation($arr[0]);

				if($relation && $this->isModified($relation['field'])){
					//acl relation changed. We must update linked emails

					GO::debug("Fixing linked e-mail acl's because relation ".$arr[0]." changed.");

					$stmt = \GO\Savemailas\Model\LinkedEmail::model()->findLinks($this);
					while($linkedEmail = $stmt->fetch()){

						GO::debug("Updating ".$linkedEmail->subject);

						$linkedEmail->acl_id=$this->findAclId();
						$linkedEmail->save();
					}
				}
			}
		}
	}


	/**
	 * Sometimes you need the auto incremented primary key to generate another
	 * property. Like the UUID of an event or task.
	 * Or in a project number for example where you want to generate a number
	 * like PR00023 where 23 is the id for example.
	 *
	 * @return boolean NOTE: Only return true if a database update is needed.
	 */
	protected function afterDbInsert(){
		return false;
	}


	/**
	 * Get a key value array of modified attribute names with their old values
	 * that are not saved to the database yet.
	 *
	 * e. array('attributeName'=>'Old value')
	 *
	 * @return array
	 */
	public function getModifiedAttributes(){
		return $this->_modifiedAttributes;
	}

	/**
	 * Reset modified attributes information. Useful when setting properties but
	 * avoid a save to the database.
	 */
	public function clearModifiedAttributes(){
		$this->_modifiedAttributes=array();
	}

	/**
	 * Set a new ACL for this model. You need to save the model after calling this
	 * function.
	 *
	 * @param StringHelper $user_id
	 * @return \GO\Base\Model\Acl
	 */
	public function setNewAcl($user_id=0){
		if($this->aclField()===false)
			throw new \Exception('Can not create a new ACL for an object that has no ACL field');
		if(!$user_id)
			$user_id = GO::user() ? GO::user()->id : 1;

		$acl = new \GO\Base\Model\Acl();
		$acl->usedIn = $this->tableName().'.'.$this->aclField();
		$acl->ownedBy=$user_id;
		$acl->entityTypeId = $this->entityType()->getId();
		$acl->entityId = $this->id;
		if(!$acl->save()) {
			throw new \Exception("Could not save ACL: ".var_export($this->getValidationErrors(), true));
		}

		$this->{$this->aclField()}=$acl->id;

		return $acl;
	}

	/**
	 * Check is this model or model attribute name has modifications not saved to
	 * the database yet.
	 *
	 * @param string/array $attributeName
	 * @return boolean
	 */
	public function isModified($attributeName=false){
		if(!$attributeName){
			return count($this->_modifiedAttributes)>0;
		}else
		{
			if(is_array($attributeName)){
				foreach($attributeName as $a){
					if(isset($this->_modifiedAttributes[$a]))
					{
						return true;
					}
				}
				return false;
			}else
			{
				return isset($this->_modifiedAttributes[$attributeName]);
			}
		}
	}

	/**
	 * Reset attribute to it's original value and clear the modified attribute.
	 *
	 * @param StringHelper $name
	 */
	public function resetAttribute($name){
		$this->$name = $this->getOldAttributeValue($name);
		unset($this->_modifiedAttributes[$name]);
	}

	/**
	 * Reset attributes to it's original value and clear the modified attributes.
	 */
	public function resetAttributes(){
		foreach($this->_modifiedAttributes as $name => $oldValue){
			$this->$name = $oldValue;
			unset($this->_modifiedAttributes[$name]);
		}
	}

	/**
	 * Get the old value for a modified attribute.
	 *
	 * @param String $attributeName
	 * @return mixed
	 */
	public function getOldAttributeValue($attributeName){
		return isset($this->_modifiedAttributes[$attributeName]) ? $this->_modifiedAttributes[$attributeName] : false;
	}

	/**
	 * The files module will use this function. To create a files folder.
	 * Override it if you don't like the default path. Make sure this path is unique! Appending the (<id>) would be wise.
	 */
	public function buildFilesPath() {

		return isset($this->name) ? $this->getModule().'/' . \GO\Base\Fs\Base::stripInvalidChars($this->name) : false;
	}

	/**
	 * Put this model in the go_search_cache table as a \GO\Base\Model\SearchCacheRecord so it's searchable and linkable.
	 * Generally you don't need to do this. It's called from the save function automatically when getCacheAttributes is overridden.
	 * This method is only public so that the maintenance script can access it to rebuid the search cache.
	 *
	 * @return boolean
	 */
	public function cacheSearchRecord(){

		//don't do this on datbase checks.
		if(GO::router()->getControllerAction()=='checkdatabase')
			return false;

		$attr = $this->getCacheAttributes();
		if(!$attr) {		
			return false;
		}
		
		$search = \go\core\model\Search::find()->where('entityTypeId','=', static::entityType()->getId())->andWhere('entityId', '=', $this->id)->single();
		if(!$search) {
			$search = new \go\core\model\Search();
			$search->setEntity(static::entityType());
		}
		
		if(isset($attr['mtime'])) {
			$attr['modifiedAt'] = \DateTime::createFromFormat("U", $attr['mtime']);

		} else {
			$attr['modifiedAt'] = \DateTime::createFromFormat("U", $this->mtime);
		}
		unset($attr['mtime']);

		// Always unset ctime, we don't use it anymore in the searchcache table
		unset($attr['ctime']);
		unset($attr['type']);

		if(!isset($attr['description'])) {
			$attr['description'] = '';
		}		
		$search->setValues($attr);
		unset($attr['modifiedAt']);
		
		$search->entityId = $this->id;
		$search->setAclId(!empty($attr['acl_id']) ? $attr['acl_id'] : $this->findAclId());			
		//$search->createdAt = \DateTime::createFromFormat("U", $this->mtime);		
		$search->setKeywords($this->getSearchCacheKeywords($this->localizedName.','.implode(',', $attr)));
		
		//todo cut lengths
		
		if(!$search->save()) {
			throw new \Exception("Could not save search cache!");
		}

//		//GO::debug($attr);
//
//		if($attr){
//
//			$model = \GO\Base\Model\SearchCacheRecord::model()->findByPk(array('model_id'=>$this->pk, 'model_type_id'=>$this->modelTypeId()),false,true);
//
//			if(!$model)
//				$model = new \GO\Base\Model\SearchCacheRecord();
//
//			$model->mtime=0;
//
//			$acl_id = !empty($attr['acl_id']) ? $attr['acl_id'] : $this->findAclId();
//
//			//if model doesn't have an acl we use the acl of the module it belongs to.
//			if(!$acl_id)
//				$acl_id = GO::modules()->{$this->getModule ()}->acl_id;
//
//			$defaultUserId = isset(GO::session()->values['user_id']) ? GO::session()->values['user_id'] : 1;
//
//			//cache type in default system language.
//			if(GO::user())
//				GO::language()->setLanguage(GO::config()->language);
//
//
//			//GO::debug($model);
//			$autoAttr = array(
//				'model_id'=>$this->pk,
//				'model_type_id'=>$this->modelTypeId(),
//				'user_id'=>isset($this->user_id) ? $this->user_id : $defaultUserId,
//				'module'=>$this->module,
//				'model_name'=>$this->className(),
//				'name' => '',
//				'description'=>'',
//				'type'=>$this->localizedName, //deprecated, for backwards compatibilty
//				'keywords'=>$this->getSearchCacheKeywords($this->localizedName.','.implode(',', $attr)),
//				'mtime'=>$this->mtime,
//				'ctime'=>$this->ctime,
//				'acl_id'=>$acl_id
//			);
//
//			$attr = array_merge($autoAttr, $attr);
//
//			if(GO::user())
//				GO::language()->setLanguage(GO::user()->language);
//
//			if($attr['description']==null)
//				$attr['description']="";
//
//			$model->setAttributes($attr, false);
//			$model->cutAttributeLengths();
////			$model->save(true);
//			if(!$model->save(true)){
//				throw new \Exception("Error saving search cache record:\n".implode("\n", $model->getValidationErrors()));
//			}
//
//			return $model;
//
//		}
//		return false;
		
		return true;
	}
	
	
	/**
	 * Cut all attributes to their maximum lengths. Useful when importing stuff.
	 */
	public function cutAttributeLengths(){
		$attr = $this->getModifiedAttributes();
		foreach($attr as $attributeName=>$oldVal){
//			if(!empty($this->columns[$attribute]['length']) && \GO\Base\Util\StringHelper::length($this->_attributes[$attribute])>$this->columns[$attribute]['length']){
//				$this->_attributes[$attribute]=\GO\Base\Util\StringHelper::substr($this->_attributes[$attribute], 0, $this->columns[$attribute]['length']);
//			}
			$this->cutAttributeLength($attributeName);
		}
	}

	/**
	 * Cut an attribute's value to it's maximum length in the database.
	 *
	 * @param StringHelper $attributeName
	 */
	public function cutAttributeLength($attributeName){
		
		if($this->columns[$attributeName]['dbtype'] == 'text' || $this->columns[$attributeName]['dbtype'] == 'mediumtext'){
			$this->_attributes[$attributeName]= substr($this->_attributes[$attributeName], 0, $this->columns[$attributeName]['length']);
		} else if(!empty($this->columns[$attributeName]['length']) && \GO\Base\Util\StringHelper::length($this->_attributes[$attributeName]) > $this->columns[$attributeName]['length']){
			$this->_attributes[$attributeName]=\GO\Base\Util\StringHelper::substr($this->_attributes[$attributeName], 0, $this->columns[$attributeName]['length']);
		}
	}

	public function getCachedSearchRecord(){
		$model = \GO\Base\Model\SearchCacheRecord::model()->findByPk(array('model_id'=>$this->pk, 'model_type_id'=>$this->modelTypeId()));
		if($model)
			return $model;
		else
			return $this->cacheSearchRecord ();
	}

	/**
	 * Override this function if you want to put your model in the search cache.
	 *
	 * @return array cache parameters with at least 'name', 'description' and 'type'. All are strings. See \GO\Base\ModelSearchCacheRecord for more info.
	 */
	protected function getCacheAttributes(){
		return false;
	}

	/**
	 * Get keywords this model should be found on.
	 * Returns all String properties in a concatenated string.
	 *
	 * @param String $prepend
	 * @return String
	 */
	public function getSearchCacheKeywords($prepend=''){
		$keywords=array();

		foreach($this->columns as $key=>$attr)
		{
			if(isset($this->$key)){
				$value = $this->$key;

				if(is_string($value) && ($attr['gotype']=='textfield' || $attr['gotype']=='customfield' || $attr['gotype']=='textarea') && !in_array($value,$keywords)){
					if(!empty($value))
						$keywords[]=$value;
				}
			}
		}

		if($this->hasCustomFields()) {
			foreach($this->getCustomFields() as $col => $v) {
				if(!empty($v) && is_string($v)) {
					$keywords[] = $v;
				}
			}
		}

		if($this->hasLinks()) {

			$links = (new Query())
				->select('description')
				->from('core_link')
				->where('(toEntityTypeId = :e1 AND toId = :e2)')
				->orWhere('(fromEntityTypeId = :e3 AND fromId = :e4)')
				->bind([':e1' => static::entityType()->getId(), ':e2' => $this->id, ':e3' => static::entityType()->getId(), ':e4' => $this->id]);
			foreach ($links->all() as $link) {
				if (!empty($link['description']) && is_string($link['description'])) {
					$keywords[] = $link['description'];
				}
			}
		}

		$keywords = $prepend.','.implode(',',$keywords);

//		if($this->customfieldsRecord){
//			$keywords .= ','.$this->customfieldsRecord->getSearchCacheKeywords();
//		}
		
		// Remove duplicate and empty entries
		$arr = explode(',', $keywords);
		$arr = array_filter(array_unique($arr), function($item){
			return $item != '';
		});
		return implode(',', $arr);
	}

	protected function beforeSave(){

		return true;
	}

	/**
	 * May be overridden to do stuff after save
	 *
	 * @var bool $wasNew True if the model was new before saving
	 * @return boolean
	 */
	protected function afterSave($wasNew){
		return true;
	}

	/**
	 * Inserts the model into the database
	 *
	 * @return boolean
	 */
	protected function _dbInsert(){

		$fieldNames = array();

		//Build an array of fields that are set in the object. Unset columns will
		//not be in the SQL query so default values from the database are respected.
		foreach($this->columns as $field=>$col){
			if(isset($this->_attributes[$field])){
				$fieldNames[]=$field;
			}
		}


		$sql = "INSERT ";

		if($this->insertDelayed)
			$sql .= "DELAYED ";

		$sql .= "INTO `{$this->tableName()}` (`".implode('`,`', $fieldNames)."`) VALUES ".
					"(:ins".implode(',:ins', array_keys($fieldNames)).")";

		if($this->_debugSql){
			$bindParams = array();
			foreach($fieldNames as  $field){
				$bindParams[$field]=$this->_attributes[$field];
			}
			$this->_debugSql(array('bindParams'=>$bindParams), $sql);
		}

		try{
			$stmt = $this->getDbConnection()->prepare($sql);

			foreach($fieldNames as $i => $field){

				$attr = $this->columns[$field];

				$stmt->bindParam(':ins'.$i, $this->_attributes[$field], $attr['type'], empty($attr['length']) ? null : $attr['length']);
			}
			$ret =  $stmt->execute();
		}catch(\Exception $e){

			$msg = $e->getMessage();

			if(GO::config()->debug){
				$msg .= "\n\nFull SQL Query: ".$sql."\n\nParams:\n".var_export($this->_attributes, true);

				$msg .= "\n\n".$e->getTraceAsString();

				GO::debug($msg);
			}
			throw new \Exception($msg);
		}

		return $ret;
	}


	private function _dbUpdate(){

		$updates=array();

		//$pks = is_array($this->primaryKey()) ? $this->primaryKey() : array($this->primaryKey());
//		foreach($this->columns as $field => $value)
//		{
//			if(!in_array($field,$pks))
//			{
//				$updates[] = "`$field`=:".$field;
//			}
//		}
//
		$i = 0;
		$paramMap = [];		
		$bindParams=array();
		foreach($this->_modifiedAttributes as $field=>$oldValue) {
			$i++;
			$tag = ":upd".$i;
			$bindParams[$tag]=$field;
			
			$updates[] = "`$field` = ".$tag;

			$i++;
		}

		if(!count($updates))
			return true;

		$sql = "UPDATE `{$this->tableName()}` SET ".implode(',',$updates)." WHERE ";


		$pk = $this->primaryKey();
		if(!is_array($pk)){
			$pk = [$pk];
		}

		$first=true;
		foreach($pk as $field){
			if(!$first)
				$sql .= ' AND ';
			else
				$first=false;
			
			$i++;
			$tag = ":upd".$i;
			$bindParams[$tag]=$field;
			$sql .= "`".$field."` = ".$tag;
			
		}


		try{
			$stmt = $this->getDbConnection()->prepare($sql);

			//$pks = is_array($this->primaryKey()) ? $this->primaryKey() : array($this->primaryKey());

			foreach($bindParams as $tag => $field){
				$attr = $this->getColumn($field);
				$stmt->bindParam($tag, $this->_attributes[$field], $attr['type'], empty($attr['length']) ? null : $attr['length']);
			}

			if($this->_debugSql)
				$this->_debugSql(array('bindParams'=>$bindParams), $sql);

			$ret = $stmt->execute();
			if($this->_debugSql){
				GO::debug("Affected rows: ".$ret);
			}
		}catch(\Exception $e){
			$msg = $e->getMessage();

			if(GO::config()->debug){
				$msg .= "\n\nFull SQL Query: ".$sql."\n\nParams:\n".var_export($bindParams, true);

				$msg .= "\n\n".$e->getTraceAsString();

				GO::debug($msg);
			}
			throw new \Exception($msg);
		}
		return $ret;
	}

	protected function beforeDelete(){
		return true;
	}
	protected function afterDelete(){
		return true;
	}

	/**
	 * Delete's the model from the database
	 * @return PDOStatement
	 */
	public function delete($ignoreAcl=false){

		GO::setMaxExecutionTime(180); // Added this because the deletion of all relations sometimes takes a lot of time (3 minutes)

		//GO::debug("Delete ".$this->className()." pk: ".$this->pk);

		if($this->isNew)
			return true;

		if(!$ignoreAcl && !$this->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION)){
			$msg = GO::config()->debug ? $this->className().' pk: '.var_export($this->pk, true) : '';
			throw new \GO\Base\Exception\AccessDenied ($msg);
		}


		if(!$this->beforeDelete() || $this->fireEvent('beforedelete', array(&$this, $ignoreAcl))===false)
				return false;

		$r= $this->relations();
		
		foreach($r as $name => $attr){
			if (!GO::classExists($attr['model'])){				
				unset($r[$name]);
				continue;
			}
			
			if(!empty($attr['delete']) && $attr['type']!=self::BELONGS_TO){

				//for backwards compatibility
				if($attr['delete']===true)
					$attr['delete']=ActiveRecord::DELETE_CASCADE;

				switch($attr['delete']){

					case ActiveRecord::DELETE_CASCADE:
						$result = $this->$name;

						if($result instanceof ActiveStatement){
							//has_many relations result in a statement.
							while($child = $result->fetch()){
								if($child->className()!=$this->className() || $child->pk != $this->pk)//prevent delete of self
									$child->delete($ignoreAcl);
							}
						}elseif($result)
						{
							//single relations return a model.
							$result->delete($ignoreAcl);
						}
						break;

					case ActiveRecord::DELETE_RESTRICT:
						if($attr['type']==self::HAS_ONE)
							$result = $this->$name;
						else
							$result = $this->$name(FindParams::newInstance()->single());

						if($result){
							throw new \GO\Base\Exception\RelationDeleteRestrict($this, $attr);
						}

						break;
				}
			}

			//clean up link models for many_many relations
			if($attr['type']==self::MANY_MANY){// && class_exists($attr['linkModel'])){
				$stmt = GO::getModel($attr['linkModel'])->find(
				 FindParams::newInstance()
								->criteria(FindCriteria::newInstance()
												->addModel(GO::getModel($attr['linkModel']))
												->addCondition($attr['field'], $this->pk)
												)
								);
				$stmt->callOnEach('delete');
				unset($stmt);
			}			
		}

		//Set the foreign fields of the deleted relations to 0 because the relation doesn't exist anymore.
		//We do this in a separate loop because relations that should be deleted should be processed first.
		//Consider these relation definitions:
		//
		// 'messagesCustomer' => array('type'=>self::HAS_MANY, 'model'=>'GO\Tickets\Model\Message', 'field'=>'ticket_id', 'findParams'=>FindParams::newInstance()->order('id','DESC')->select('t.*')->criteria(FindCriteria::newInstance()->addCondition('is_note', 0))),
		// 'messagesNotes' => array('type'=>self::HAS_MANY, 'model'=>'GO\Tickets\Model\Message', 'field'=>'ticket_id', 'findParams'=>FindParams::newInstance()->order('id','DESC')->select('t.*')->criteria(FindCriteria::newInstance()->addCondition('is_note', 0))),
		// 'messages' => array('type'=>self::HAS_MANY, 'model'=>'GO\Tickets\Model\Message', 'field'=>'ticket_id','delete'=>true, 'findParams'=>FindParams::newInstance()->order('id','DESC')->select('t.*')),
		//
		// messagesCustomer and messagesNotes are just subsets of the messages
		// relation that must all be deleted anyway. We don't want to clear foreign keys first and then fail to delete them.

		foreach($r as $name => $attr){
			if(empty($attr['delete'])){
				if($attr['type']==self::HAS_ONE){
					//set the foreign field to 0. Because it doesn't exist anymore.
					$model = $this->$name;
					if($model){

						$columns = $model->getColumns();

						$model->{$attr['field']}=$columns[$attr['field']]['null'] ? null : 0;
						$model->save();
					}
				}elseif($attr['type']==self::HAS_MANY){
					//set the foreign field to 0 because it doesn't exist anymore.
					$stmt = $this->$name;

					while($model = $stmt->fetch()){

						$columns = $model->getColumns();

						$model->{$attr['field']}=$columns[$attr['field']]['null'] ? null : 0;
						$model->save();
					}
				}
			}
		}
	
		$sql = "DELETE FROM `".$this->tableName()."` WHERE ";
		$sql = $this->_appendPkSQL($sql);

		//remove cached models
		GO::modelCache()->remove($this->className());


		if($this->_debugSql)
			GO::debug($sql);

		$success = $this->getDbConnection()->query($sql);
		if(!$success)
			throw new \Exception("Could not delete from database");

		$this->_isDeleted = true;
		
		$this->log(\GO\Log\Model\Log::ACTION_DELETE);

		$attr = $this->getCacheAttributes();

		if($attr){
			\go\core\model\Search::delete(['entityId' => $this->pk, 'entityTypeId'=>$this->modelTypeId()]);
		}		

		if($this->hasFiles() && $this->files_folder_id > 0 && GO::modules()->isInstalled('files')){
			$folder = \GO\Files\Model\Folder::model()->findByPk($this->files_folder_id,false,true);
			if($folder)
				$folder->delete(true);
		}
		

		if($this->aclField() && (!$this->isJoinedAclField || $this->isAclOverwritten())){
			//echo 'Deleting acl '.$this->{$this->aclField()}.' '.$this->aclField().'<br />';
			$aclField = $this->isAclOverwritten() ? $this->aclOverwrite() : $this->aclField();

			$acl = \GO\Base\Model\Acl::model()->findByPk($this->{$aclField});
			if($acl) {
				$acl->delete();
			}
		}

		
		$this->_deleteLinks();	
		
		
		if(!$this->afterDelete())
			return false;
		
		if($this->hasLinks() && !is_array($this->pk)) {
			$this->deleteReminders();
		}
		
		$this->fireEvent('delete', array(&$this));

		return true;
	}
	
	public function isDeleted(){
		return $this->_isDeleted;
	}


	private function _deleteLinks(){
		//cleanup links
		if($this->hasLinks()){
			
			$sql = "DELETE FROM core_link WHERE fromEntityTypeId=".intval($this->modelTypeId()).' AND fromId='.intval($this->pk);
			$this->getDbConnection()->query($sql);

			$sql = "DELETE FROM core_link WHERE toEntityTypeId=".intval($this->modelTypeId()).' AND toId='.intval($this->pk);
			$this->getDbConnection()->query($sql);
		}
	}

//	/**
//	 * Set the output mode for this model. The default value can be set globally
//	 * too with ActiveRecord::$attributeOutputMode.
//	 * It can be 'raw', 'formatted' or 'html'.
//	 *
//	 * @param type $mode
//	 */
//	public function setAttributeOutputMode($mode){
//		if($mode!='raw' && $mode!='formatted' && $mode!='html')
//			throw new \Exception("Invalid mode ".$mode." supplied to setAttributeOutputMode in ".$this->className());
//
//		$this->_attributeOutputMode=$mode;
//	}

//	/**
//	 *Get the current attributeOutputmode
//	 *
//	 * @return string
//	 */
//	public function getAttributeOutputMode(){
//
//		return $this->_attributeOutputMode;
//	}
	/**
	 * PHP getter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param StringHelper $name property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		return $this->_getMagicAttribute($name);
	}

	private function _getMagicAttribute($name){
		if(key_exists($name, $this->_attributes)){
			return $this->getAttribute($name, self::$attributeOutputMode);
		}elseif(isset($this->columns[$name])){
			//it's a db column but it's not set in the attributes array.
			return null;
		}elseif($this->_relationExists($name)){
				return $this->_getRelated($name);
		}else{
//					if(!isset($this->columns[$name]))
//					return null;
			return parent::__get($name);
		}
	}
	/**
	 * Get a single attibute raw like in the database or formatted using the \
	 * Group-Office user preferences.
	 *
	 * @param String $attributeName
	 * @param String $outputType raw, formatted or html
	 * @return mixed
	 */
	public function getAttribute($attributeName, $outputType='raw'){
		if(!isset($this->_attributes[$attributeName])){
			return null;
		}

		return $outputType=='raw' ?  $this->_attributes[$attributeName] : $this->formatAttribute($attributeName, $this->_attributes[$attributeName],$outputType=='html');
	}

	public function resolveAttribute($path, $outputType='raw'){
		
		if(substr($path, 0, 13) === 'customFields.') { 
			$cf = $this->getCustomFields();
			return $cf[substr($path, 13)] ?? null;
		}
		
		$parts = explode('.', $path);

		$model = $this;
		if(count($parts)>1){
			$last = array_pop($parts);

			while($part = array_shift($parts)){				
				$model = $model->$part;
				if(!$model){
					return null;
				}
			}

			return $model->getAttribute($last, $outputType);

		}else
		{
			return $this->getAttribute($parts[0], $outputType);
		}
	}


	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * to implement the named scope feature.
	 *
	 * @param StringHelper $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	public function __call($name,$parameters)
	{
		//todo find relation

    $extraFindParams=isset($parameters[0]) ?$parameters[0] : array();
		if($this->_relationExists($name))
			return $this->_getRelated($name,$extraFindParams);
		else
			throw new \Exception("function {$this->className()}:$name does not exist");
		//return parent::__call($name,$parameters);
	}

	/**
	 * PHP setter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 *
	 * @param StringHelper $name property name
	 * @param mixed $value property value
	 */
	public function __set($name,$value)
	{
		$this->setAttribute($name,$value);
	}

	public function __isset($name){
		return isset($this->_attributes[$name]) ||
						//isset($this->columns[$name]) || MS: removed this because it returns true when attribute is null. This might break something but it shouldn't return true.
						($this->_relationExists($name) && $this->_getRelated($name)) ||
						parent::__isset($name);
	}

	/**
	 * Check if this model has a named attribute
	 * @param StringHelper $name
	 * @return boolean
	 */
	public function hasAttribute($name){

		if(isset($this->columns[$name]))
			return true;

		if($this->_relationExists($name))
			return true;

		if(method_exists($this, 'get'.$name))
			return true;

		return false;
	}

	/**
	 * Sets a component property to be null.
	 * This method overrides the parent implementation by clearing
	 * the specified attribute value.
	 *
	 * @param StringHelper $name the property name
	 */
	public function __unset($name)
	{
		unset($this->_modifiedAttributes[$name]);
		unset($this->_attributes[$name]);
	}

	/**
	 * Mysql always returns strings. We want strict types in our model to clearly
	 * detect modifications
	 *
	 * @param array $columns
	 * @return void
	 */
	public function castMySqlValues($columns=false){

		if(!$columns)
			$columns = array_keys($this->columns);

		foreach($columns as $column){
			if(isset($this->_attributes[$column]) && isset($this->columns[$column]['dbtype'])){
				switch ($this->columns[$column]['dbtype']) {
						case 'int':
						case 'tinyint':
						case 'bigint':
							//must use floatval because of ints greater then 32 bit
							$this->_attributes[$column]=floatval($this->_attributes[$column]);
							break;

						case 'float':
						case 'double':
						case 'decimal':
							$this->_attributes[$column]=floatval($this->_attributes[$column]);
							break;
				}
			}
		}
	}


	/**
	 * Sets the named attribute value. It can also set BELONGS_TO and HAS_ONE
	 * relations if you pass a ActiveRecord
	 *
	 * You may also use $this->AttributeName to set the attribute value.
	 *
	 * @param StringHelper $name the attribute name
	 * @param mixed $value the attribute value.
	 * @return boolean whether the attribute exists and the assignment is conducted successfully
	 * @see hasAttribute
	 */
	public function setAttribute($name,$value, $format=false)
	{
//		TODO
//		if($this->_isStaticModel) {
//			throw new \Exception("Don't set on static model!");
//		}
		if($this->loadingFromDatabase){
			//skip fancy features when loading from the database.
			$this->_attributes[$name]=$value;
			return true;
		}
		
		if($format)
			$value = $this->formatInput($name, $value);

		if(isset($this->columns[$name])){
			
			if(GO::config()->debug){
				if($this->columns[$name]['gotype']!='file' && is_object($value) || is_array($value))
					throw new \Exception($this->className()."::setAttribute : Invalid attribute value for ".$name.". Type was: ".gettype($value));
			}
			
			$relationFieldName = $this->_getAclFk();
			
			if($name === $relationFieldName){
				$aclWasOverwritten = $this->isAclOverwritten();
			}

			//normalize CRLF to prevent issues with exporting to vcard etc.
			if(isset($this->columns[$name]['gotype']) && ($this->columns[$name]['gotype']=='textfield' || $this->columns[$name]['gotype']=='textarea'))
				$value=\GO\Base\Util\StringHelper::normalizeCrlf($value, "\n");

			if((!isset($this->_attributes[$name]) || (string)$this->_attributes[$name]!==(string)$value) && !$this->isModified($name)){
				$this->_modifiedAttributes[$name]=isset($this->_attributes[$name]) ? $this->_attributes[$name] : false;
//				GO::debug("Setting modified attribute $name to ".$this->_modifiedAttributes[$name]);
//				GO::debugCalledFrom(5);
			}
			
			$this->_attributes[$name]=$value;
			
			// Set the ACL_ID if the relation acl FK changed and ACL is overwritten
			if($name === $relationFieldName && !$aclWasOverwritten && $this->aclOverwrite() && $this->isModified($name)) {
				if(!empty($this->{$name})){
					$modelWithAcl = $this->findRelatedAclModel();
					if($modelWithAcl){
						$this->{$this->aclOverwrite()} = $modelWithAcl->findAclId();
					}
				}
			}

		}else{


			if($r = $this->getRelation($name)){
				if($r['type']==self::BELONGS_TO || $r['type']==self::HAS_ONE){

					if($value instanceof ActiveRecord){

						$cacheKey = $this->_getRelatedCacheKey($r);
						$this->_relatedCache[$cacheKey]=$value;
					}else
					{
						throw new \Exception("Value for relation '".$name."' must be a ActiveRecord '".  gettype($value)."' was given");
					}
				}else
				{
					throw new \Exception("Can't set one to many relation!");
				}
			}else
			{
				parent::__set($name, $value);
			}
		}

		return true;
	}


	/**
	 * Pass another model to this function and they will be linked with the
	 * Group-Office link system.

	 * @param \go\core\orm\Entity|self|GO\Base\Model\SearchCacheRecord $model
	 */

	public function link($model, $description='', $this_folder_id=0, $model_folder_id=0){

		$isSearchCacheModel = ($this instanceof \GO\Base\Model\SearchCacheRecord);

		$disableLinksFor = GO::config()->disable_links_for ? GO::config()->disable_links_for : array();
		if (!is_array($disableLinksFor)) {
			$disableLinksFor = [$disableLinksFor];
		}

		$linksDisabled = false;
		if (in_array(self::className(), $disableLinksFor, true) || in_array(get_class($model), $disableLinksFor, true)) {
			$linksDisabled = true;
		}

		if((!$this->hasLinks() && !$isSearchCacheModel) || $linksDisabled)
			throw new \Exception("Links not supported by ".$this->className ());

		if($this->linkExists($model))
			return true;

		if($model instanceof \GO\Base\Model\SearchCacheRecord){
			$to_model_id = $model->entityId;
			$to_model_type_id = $model->entityTypeId;
		}else
		{
			$to_model_id = $model->id;
			$to_model_type_id = $model->entityType()->getId();
		}
		
		

		$from_model_type_id = $isSearchCacheModel ? $this->entityTypeId : $this->modelTypeId();

		$from_model_id = $isSearchCacheModel ? $this->model_id : $this->id;
		
		if($to_model_id == $from_model_id && $to_model_type_id == $from_model_type_id) {
			//don't link to self
			return true;
		}
		
		if(!\go\core\App::get()->getDbConnection()->insert('core_link', [
				"toId" => $to_model_id,
				"toEntityTypeId" => $to_model_type_id,
				"fromId" => $from_model_id,
				"fromEntityTypeId" => $from_model_type_id,
				"description" => $description,
				"createdAt" => new \DateTime('now',new \DateTimeZone('UTC'))
				
		])->execute()){
			return false;
		}

		$reverse = [];
		$reverse['fromEntityTypeId'] = $to_model_type_id;
		$reverse['toEntityTypeId'] = $from_model_type_id;
		$reverse['toId'] = $from_model_id;
		$reverse['fromId'] = $to_model_id;		
		$reverse['description'] = $description;
		$reverse['createdAt'] = new \DateTime('now',new \DateTimeZone('UTC'));
	
		
		if(!\go\core\App::get()->getDbConnection()->insert('core_link', $reverse)->execute()) {
			return false;
		}

		$this->fireEvent('link', array($this, $model, $description, $this_folder_id, $model_folder_id));
		return true;
	}

//	/**
//	 * Can be overriden to do something after linking. It's a public method because sometimes
//	 * searchCacheRecord models are used for linking. In that case we can call the afterLink method of the real model instead of the searchCacheRecord model.
//	 *
//	 * @param ActiveRecord $model
//	 * @param boolean $isSearchCacheModel True if the given model is a search cache model.
//	 *	In that case you can use the following code to get the real model:  $realModel = $isSearchCacheModel ? GO::getModel($this->model_name)->findByPk($this->model_id) : $this;
//	 * @param string $description
//	 * @param int $this_folder_id
//	 * @param int $model_folder_id
//	 * @param boolean $linkBack
//	 * @return boolean
//	 */
//	public function afterLink(ActiveRecord $model, $isSearchCacheModel, $description='', $this_folder_id=0, $model_folder_id=0, $linkBack=true){
//		return true;
//	}

	/**
	 * 
	 * @param \go\core\orm\Entity|self|GO\Base\Model\SearchCacheRecord $model
	 * @return boolean
	 */
	public function linkExists($model){

		if($model instanceof \GO\Base\Model\SearchCacheRecord){
			$to_model_id = $model->entityId;
			$to_model_type_id = $model->entityTypeId;
		}else
		{
			$to_model_id = $model->id;
			$to_model_type_id = $model->entityType()->getId();
		}
		
		if(!$to_model_id)
			return false;

		$from_model_type_id = $this->className()=="GO\Base\Model\SearchCacheRecord" ? $this->entityTypeId : $this->modelTypeId();
		$from_id = $this->className()=="GO\Base\Model\SearchCacheRecord" ? $this->model_id : $this->id;

		$sql = "SELECT id FROM `core_link` WHERE ".
			"`fromId`=".intval($from_id)." AND fromEntityTypeId=".$from_model_type_id." AND toEntityTypeId=".$to_model_type_id." AND `toId`=".intval($to_model_id);
		
		$stmt = $this->getDbConnection()->query($sql);
		return $stmt->fetchColumn(0);
	}
//
//	/**
//	 * Update folder_id or description of a link
//	 *
//	 * @param ActiveRecord $model
//	 * @param array $attributes
//	 * @return boolean
//	 */
//	public function updateLink(ActiveRecord $model, array $attributes){
//		$sql = "UPDATE `go_links_".$this->tableName()."`";
//
//		$updates=array();
//		$bindParams=array();
//		foreach($attributes as $field=>$value){
//			$updates[] = "`$field`=:".$field;
//			$bindParams[':'.$field]=$value;
//		}
//
//		$sql .= "SET ".implode(',',$updates).
//			" WHERE model_type_id=".$model->modelTypeId()." AND model_id=".$model->id;
//
//		$result = $this->getDbConnection()->prepare($sql);
//		return $result->execute($bindParams);
//	}
//
	/**
	 * Unlink a model from this model
	 *
	 * @param ActiveRecord $model
	 * @param boolean $unlinkBack For private use only
	 * @return boolean
	 */
	public function unlink($model){
		
		$isSearchCacheModel = ($this instanceof \GO\Base\Model\SearchCacheRecord);

		if(!$this->hasLinks() && !$isSearchCacheModel)
			throw new \Exception("Links not supported by ".$this->className ());


		if($model instanceof \GO\Base\Model\SearchCacheRecord){
			$to_model_id = $model->entityId;
			$to_model_type_id = $model->entityTypeId;
		}else
		{
			$to_model_id = $model->id;
			$to_model_type_id = $model->entityType()->getId();
		}
		
		

		$from_model_type_id = $isSearchCacheModel ? $this->entityTypeId : $this->modelTypeId();

		$from_model_id = $isSearchCacheModel ? $this->model_id : $this->id;
		
		
		
		
		if(!\go\core\App::get()->getDbConnection()->delete('core_link', [
				"toId" => $to_model_id,
				"toEntityTypeId" => $to_model_type_id,
				"fromId" => $from_model_id,
				"fromEntityTypeId" => $from_model_type_id				
		])->execute()){
			return false;
		}
		
		
		
		$reverse = [];
		$reverse['fromEntityTypeId'] = $to_model_type_id;
		$reverse['toEntityTypeId'] = $from_model_type_id;
		$reverse['toId'] = $from_model_id;
		$reverse['fromId'] = $to_model_id;		
		
		
		return \go\core\App::get()->getDbConnection()->delete('core_link', $reverse)->execute();
	}
//
//	protected function afterUnlink(ActiveRecord $model){
//
//		return true;
//	}
//
	/**
	 * Get the number of links this model has to other models.
	 *
	 * @param int $model_id
	 * @return int
	 */
	public function countLinks($model_id=0){
		if($model_id==0)
			$model_id=$this->id;
		$sql = "SELECT count(*) FROM `core_link` WHERE fromId=".intval($model_id)." AND fromEntityTypeId = ".$this->modelTypeId();
		$stmt = $this->getDbConnection()->query($sql);
		return intval($stmt->fetchColumn(0));
	}

	/**
	 * Find links of this model type to a given model.
	 *
	 * eg.:
	 *
	 * \GO\Addressbook\Model\Contact::model()->findLinks($noteModel);
	 *
	 * selects all contacts linked to the $noteModel
	 *
	 * @param ActiveRecord $model
	 * @param FindParams $findParams
	 * @return ActiveStatement
	 */
	public function findLinks($model, $extraFindParams=false){

		$findParams = FindParams::newInstance ();

		$findParams->select('t.*,l.description AS link_description');

		$joinCriteria = FindCriteria::newInstance()
						->addCondition('fromId', $model->id,'=','l')
						->addCondition('fromEntityTypeId', $model->modelTypeId(),'=','l')
						->addRawCondition("t.id", "l.toId")
						->addCondition('toEntityTypeId', $this->modelTypeId(),'=','l');

		$findParams->join("core_link", $joinCriteria, 'l');

		if($extraFindParams)
			$findParams->mergeWith ($extraFindParams);

		return $this->find($findParams);
	}


	/**
	 * Copy links from this model to the target model.
	 *
	 * @param ActiveRecord $targetModel
	 */
	public function copyLinks(ActiveRecord $targetModel){
		if(!$this->hasLinks() || !$targetModel->hasLinks())
			return false;

		$stmt = \GO\Base\Model\SearchCacheRecord::model()->findLinks($this);
		while($searchCacheModel = $stmt->fetch()){
			$targetModel->link($searchCacheModel, $searchCacheModel->link_description);
		}
		return true;
	}



	/**
	 * Get's the Acces Control List for this model if it has one.
	 *
	 * @return \GO\Base\Model\Acl
	 */
	public function getAcl(){
		if($this->_acl){
			return $this->_acl;
		}else
		{
			$aclId = $this->findAclId();
			if($aclId){
				$this->_acl=\GO\Base\Model\Acl::model()->findByPk($aclId);
				return $this->_acl;
			}else{
				return false;
			}
		}
	}

	/**
	 * Check if it's necessary to run a database check for this model.
	 * If it has an ACL, Files or an overrided method it returns true.
	 * @return boolean
	 */
	public function checkDatabaseSupported(){

		if($this->aclField())
			return true;

		if($this->hasFiles() && GO::modules()->isInstalled('files'))
			return true;

		$class = new \GO\Base\Util\ReflectionClass($this->className());
		return $class->methodIsOverridden('checkDatabase');
	}

	/**
	 * A function that checks the consistency with the database.
	 * Generally this is called by r=maintenance/checkDabase
	 */
	public function checkDatabase(){
		//$this->save();

		echo "Checking ".(is_array($this->pk)?implode(',',$this->pk):$this->pk)." ".$this->className()."\n";
		flush();

		if($this->aclField() && (!$this->isJoinedAclField || $this instanceof GO\Files\Model\Folder)){

			$acl = $this->acl;
			if(!$acl)
				$this->setNewAcl();
			else
			{
				$user_id = empty($this->user_id) ? 1 : $this->user_id;
				$acl->ownedBy=$user_id;
				$acl->usedIn=$this->tableName().'.'.$this->aclField();
				$acl->save();
			}
		}

		if ($this->hasFiles() && GO::modules()->isInstalled('files')) {
			//ACL must be generated here.
			$fc = new \GO\Files\Controller\FolderController();
			$this->files_folder_id = $fc->checkModelFolder($this);
		}

		//normalize crlf
		foreach($this->columns as $field=>$attr){
			if(($attr['gotype']=='textfield' || $attr['gotype']=='textarea') && !empty($this->_attributes[$field])){
				$this->$field=\GO\Base\Util\StringHelper::normalizeCrlf($this->_attributes[$field], "\n");
			}
		}

		//fill in empty required attributes that have defaults
		$defaults=$this->getDefaultAttributes();
		foreach($this->columns as $field=>$attr){
			if($attr['required'] && empty($this->$field) && isset($defaults[$field])){
				$this->$field=$defaults[$field];

				echo "Setting default value ".$this->className().":".$this->id." $field=".$defaults[$field]."\n";

			}
		}

		if($this->isModified())
			$this->save();
	}


	public function rebuildSearchCache() {		
		
		
				
		$rc = new \GO\Base\Util\ReflectionClass($this);
		$overriddenMethods = $rc->getOverriddenMethods();
		if(in_array("getCacheAttributes", $overriddenMethods)){
			
			echo "Processing ".static::class ."\n";
			
			$entityTypeId = static::entityType()->getId();
		
			$start = 0;
			$limit = 100;
			
			$findParams = FindParams::newInstance()
							->ignoreAcl()
							->debugSql()
							->select('t.*')
							->limit($limit)
							->start($start)
							->join('core_search', FindCriteria::newInstance()->addRawCondition('search.entityId', 't.id')->addRawCondition("search.entityTypeId", $entityTypeId), 'search', 'LEFT');
			
			$findParams->getCriteria()->addCondition('entityId',null, 'IS', 'search');							
			
			//In small batches to keep memory low
			$stmt = $this->find($findParams);
			while($stmt->rowCount()) {	
	
				while ($m = $stmt->fetch()) {
				
					try {
						flush();
						
						if($m->cacheSearchRecord()) {
							echo ".";
						} else
						{
							echo "S";
							$start++;
						}
						
					} catch (\Exception $e) {
						\go\core\ErrorHandler::logException($e);
						echo "E";
						$start++;
					}
				}
				echo "\n";
				
				$stmt = $this->find($findParams->start($start));				
			}
			
			echo "\nDone\n\n";
			
		}
	}


	/**
	 * Duplicates the current activerecord to a new one.
	 *
	 * Instead of cloning it will create a new instance of the called class
	 * Copy all the attributes from the original and overwrite the one in the $attibutes parameter
	 * Unset the primary key if it's not multicolumn and assumably auto_increment
	 *
	 * @param array $attributes Array of attributes that need to be set in
	 * the newly created activerecord as KEY => VALUE.
	 * Like: $params = array('attribute1'=>1,'attribute2'=>'Hello');
	 * @param boolean $save if the copy should be save when calling this function
	 * @param boolean $ignoreAclPermissions
	 * @return mixed The newly created object or false if before or after duplicate fails
	 *
	 */
	public function duplicate($attributes = array(), $save=true, $ignoreAclPermissions=false, $ignoreCustomFields = false) {

		$copy = new static();
		$copiedAttrs = $this->getAttributes('raw');
		unset($copiedAttrs['ctime'],$copiedAttrs['files_folder_id']);
		$pkField = $this->primaryKey();
		if(!is_array($pkField))
			unset($copiedAttrs[$pkField]);

		$copiedAttrs = array_merge($copiedAttrs, $attributes);

		$copy->setAttributes($copiedAttrs,false);

		if(!$this->beforeDuplicate($copy)){
			return false;
		}

		foreach($attributes as $key=>$value) {
			$copy->$key = $value;
		}

		//Generate new acl for this model
		if($this->aclField() && !$this->isJoinedAclField){

			$user_id = isset($this->user_id) ? $this->user_id : GO::user()->id;
			$copy->setNewAcl($user_id);
		}

		if(!$ignoreCustomFields && $this->hasCustomFields()){
			$copy->setCustomFields($this->getCustomFields());
		}

		$this->_duplicateFileColumns($copy);

		if($save){

			if(!$copy->save($ignoreAclPermissions)){
				throw new \Exception("Could not save duplicate: ".implode("\n",$copy->getValidationErrors()));

			}
		}

		if(!$this->afterDuplicate($copy)){
			$copy->delete(true);
			return false;
		}

		return $copy;
	}

	protected function beforeDuplicate(&$duplicate){
		return true;
	}
	protected function afterDuplicate(&$duplicate){
		return true;
	}

	/**
	 * Duplicate related items to another model.
	 *
	 * @param StringHelper $relationName
	 * @param ActiveRecord $duplicate
	 * @return boolean
	 * @throws Exception
	 */
	public function duplicateRelation($relationName, $duplicate, array $attributes=array(), $findParams=false){

		$r= $this->relations();

		if(!isset($r[$relationName]))
			throw new \Exception("Relation $relationName not found");

		if($r[$relationName]['type']!=self::HAS_MANY){
			throw new \Exception("Only HAS_MANY relations are supported in duplicateRelation");
		}

		$field = $r[$relationName]['field'];

		if(!$findParams)
			$findParams=  FindParams::newInstance ();

		$findParams->select('t.*');

		$stmt = $this->_getRelated($relationName, $findParams);
		while($model = $stmt->fetch()){

			//set new foreign key
			$attributes[$field]=$duplicate->pk;

//			var_dump(array_merge($model->getAttributes('raw'),$attributes));

			$duplicateRelatedModel = $model->duplicate($attributes);

			$this->afterDuplicateRelation($relationName, $model, $duplicateRelatedModel);
		}

		return true;
	}

	protected function afterDuplicateRelation($relationName, ActiveRecord $relatedModel, ActiveRecord $duplicatedRelatedModel){
		return true;
	}

	/**
	 * Lock the database table
	 *
	 * @param StringHelper $mode Modes are: "read", "read local", "write", "low priority write"
	 * @return boolean
	 */
	public function lockTable($mode="WRITE"){
		$sql = "LOCK TABLES `".$this->tableName()."` AS t $mode";
		$this->getDbConnection()->query($sql);

		if($this->hasFiles() && GO::modules()->isInstalled('files')){
			$sql = "LOCK TABLES `fs_folders` AS t $mode";
			$this->getDbConnection()->query($sql);
		}

		return true;
	}
	/**
	 * Unlock tables
	 *
	 * @return bool True on success
	 */

	public function unlockTable(){
		$sql = "UNLOCK TABLES;";
		return $this->getDbConnection()->query($sql);
	}

	/**
	 * Get's all the default attributes. The defaults coming from the database and
	 * the programmed ones defined in defaultAttributes().
	 *
	 * @return array
	 */
	public function getDefaultAttributes(){
		$attr=array();
		foreach($this->getColumns() as $field => $colAttr){
			if(isset($colAttr['default']))
				$attr[$field]=$colAttr['default'];
		}

		if(isset($this->columns['user_id']))
			$attr['user_id']=GO::user() ? GO::user()->id : 1;
		if(isset($this->columns['muser_id']))
			$attr['muser_id']=GO::user() ? GO::user()->id : 1;

		return array_merge($attr, $this->defaultAttributes());
	}

	/**
	 *
	 * Get the extra default attibutes not determined from the database.
	 *
	 * This function can be overridden in the model.
	 * Example override:
	 * $attr = parent::defaultAttributes();
	 * $attr['time'] = time();
	 * return $attr;
	 *
	 * @return Array An empty array.
	 */
	protected function defaultAttributes() {
		return array();
	}



	/**
	 * Delete all reminders linked to this midel.
	 */
	public function deleteReminders(){

		$stmt = \GO\Base\Model\Reminder::model()->findByModel($this->className(), $this->pk);
		$stmt->callOnEach("delete");
	}

	/**
	 * Add a reminder linked to this model
	 *
	 * @param StringHelper $name The name of the reminder
	 * @param int $time This needs to be an unixtimestamp
	 * @param int $user_id The user where this reminder belongs to.
	 * @param int $vtime The time that will be displayed in the reminder
	 * @return \GO\Base\Model\Reminder
	 */
	public function addReminder($name, $time, $user_id, $vtime=null){

		$userModel = \GO\Base\Model\User::model()->findByPk($user_id, false, true);
		if (!empty($userModel) && !$userModel->no_reminders) {
			$reminder = \GO\Base\Model\Reminder::newInstance($name, $time, $this->className(), $this->pk, $vtime);
			$reminder->setForUser($user_id);

			return $reminder;
		} else {
			return false;
		}

	}

	/**
	 * Add a record to the given MANY_MANY relation
	 *
	 * @param String $relationName
	 * @param int $foreignPk
	 * @param array $extraAttributes
	 * @return boolean Saved
	 */
	public function addManyMany($relationName, $foreignPk, $extraAttributes=array()){

		if(empty($foreignPk))
			return false;

		if(!$this->hasManyMany($relationName, $foreignPk)){

			$r = $this->getRelation($relationName);

			if($this->isNew)
				throw new \Exception("Can't add manymany relation to a new model. Call save() first.");

			if(!$r)
				throw new \Exception("Relation '$relationName' not found in ActiveRecord::addManyMany()");

			$linkModel = new $r['linkModel'];
			$linkModel->{$r['field']} = $this->pk;

			$keys = $linkModel->primaryKey();

			$foreignField = $keys[0]==$r['field'] ? $keys[1] : $keys[0];

			$linkModel->$foreignField = $foreignPk;

			$linkModel->setAttributes($extraAttributes);

			return $linkModel->save();
		}else
		{
			return true;
		}
  }

	/**
	 * Remove a record from the given MANY_MANY relation
	 *
	 * @param String $relationName
	 * @param int $foreignPk
	 *
	 * @return ActiveRecord or false
	 */
	public function removeManyMany($relationName, $foreignPk){
		$linkModel = $this->hasManyMany($relationName, $foreignPk);

		if($linkModel)
			return $linkModel->delete();
		else
			return true;
	}

	public function removeAllManyMany($relationName){
		$r = $this->getRelation($relationName);
		if(!$r)
			throw new \Exception("Relation '$relationName' not found in ActiveRecord::hasManyMany()");
		$linkModel = GO::getModel($r['linkModel']);

		$linkModel->deleteByAttribute($r['field'],$this->pk);
	}

  /**
   * Check for records in the given MANY_MANY relation
   *
   * @param String $relationName
	 * @param int $foreignPk
	 *
   * @return ActiveRecord or false
   */
  public function hasManyMany($relationName, $foreignPk){
		$r = $this->getRelation($relationName);
		if(!$r)
			throw new \Exception("Relation '$relationName' not found in ActiveRecord::hasManyMany()");

		if($this->isNew)
			throw new \Exception("You can't call hasManyMany on a new model. Call save() first.");

		$linkModel = GO::getModel($r['linkModel']);
		$keys = $linkModel->primaryKey();
		if(count($keys)!=2){
			throw new \Exception("Primary key of many many linkModel ".$r['linkModel']." must be an array of two fields");
		}
		$foreignField = $keys[0]==$r['field'] ? $keys[1] : $keys[0];

		$primaryKey = array($r['field']=>$this->pk, $foreignField=>$foreignPk);

    return $linkModel->findByPk($primaryKey);
  }

	/**
	 * Quickly delete all records by attribute. This function does NOT check the ACL.
	 *
	 * @param StringHelper $name
	 * @param mixed $value
	 */
	public function deleteByAttribute($name, $value){
		$this->deleteByAttributes([$name => $value]);
	}
	
	public function deleteByAttributes($attributes){
		$criteria = FindCriteria::newInstance();
		foreach($attributes as $name => $value) {
			$criteria->addCondition($name, $value);
		}
		$stmt = $this->find(FindParams::newInstance()->ignoreAcl()->criteria($criteria));
		$stmt->callOnEach('delete');
	}

	/**
	 * Add a comment to the model. If the comments module is not installed this
	 * function will return false.
	 *
	 * @param StringHelper $text
	 * @return boolean
	 */
	public function addComment($text){
		if(!GO::modules()->isInstalled('comments') || !GO::modules()->isInstalled('comments') && !$this->hasLinks())
			return false;		

		$comment = new \go\modules\community\comments\model\Comment();
		$comment->setEntity($this->entityType());
		$comment->entityId = $this->id;
		$comment->text=$text;
		if(!$comment->save()) {			
			throw new \Exception("Failed to save comment");
		}
		
		return $comment;

	}

	/**
	 * Merge this model with another one of the same type.
	 *
	 * All attributes of the given model will be applied to this model if they are empty. Textarea's will be concatenated.
	 * All links will be moved to this model.
	 * Finally the given model will be deleted.
	 *
	 * @param ActiveRecord $model
	 */
	public function mergeWith(ActiveRecord $model, $mergeAttributes=true, $deleteModel=true){

		if($model->id==$this->id && $this->className()==$model->className())
			return false;

		//copy attributes if models are of the same type.
		if($mergeAttributes){
			$attributes = $model->getAttributes('raw');

			//don't copy primary key
			if(is_array($this->primaryKey())){
				foreach($this->primaryKey() as $field)
					unset($attributes[$field]);
			}else
				unset($attributes[$this->primaryKey()]);

			unset($attributes['files_folder_id']);

			foreach($attributes as $name=>$value){
				$isset = isset($this->columns[$name]);

				if($isset && !empty($value)){
					if($this->columns[$name]['gotype']=='textarea'){
						$this->$name .= "\n\n-- merge --\n\n".$value;
					}elseif($this->columns[$name]['gotype']='date' && $value == '0000-00-00')
					  $this->$name=""; //Don't copy old 0000-00-00 that might still be in the database
					elseif(empty($this->$name))
						$this->$name=$value;

				}
			}
			
			if($this->hasCustomFields()) {
				$this->setCustomFields($model->getCustomFields());
			}
			
			$this->save();
		}

		$model->copyLinks($this);

		//move files.
		if($deleteModel){
			$this->_moveFiles($model);

			$this->_moveComments($model);
		}else
		{
			$this->_copyFiles($model);

			$this->_copyComments($model);
		}

		$this->afterMergeWith($model);

		if($deleteModel)
			$model->delete();
	}

	private function _copyComments(ActiveRecord $sourceModel) {
		if (GO::modules()->isInstalled('comments') && $this->hasLinks()) {
			$findParams = FindParams::newInstance()
							->ignoreAcl()
							->order('id', 'DESC')
							->select()
							->criteria(
							FindCriteria::newInstance()
							->addCondition('model_id', $sourceModel->id)
							->addCondition('model_type_id', $sourceModel->modelTypeId())
			);
			$stmt = \GO\Comments\Model\Comment::model()->find($findParams);
			while ($comment = $stmt->fetch()) {
				$comment->duplicate(
								array(
										'model_type_id' => $this->modelTypeId(),
										'model_id' => $this->id,
										'ctime' => $comment->ctime
								)
				);
			}
		}
	}

	private function _copyFiles(ActiveRecord $sourceModel) {
		if (!$this->hasFiles()) {
			return false;
		}

		$sourceFolder = \GO\Files\Model\Folder::model()->findByPk($sourceModel->files_folder_id);
		if (!$sourceFolder) {
			return false;
		}

		$this->filesFolder->copyContentsFrom($sourceFolder);
	}

	private function _moveComments(ActiveRecord $sourceModel){
		if(GO::modules()->isInstalled('comments') && $this->hasLinks()){
			$findParams = FindParams::newInstance()
						->ignoreAcl()
						->order('id','DESC')
						->criteria(
										FindCriteria::newInstance()
											->addCondition('model_id', $sourceModel->id)
											->addCondition('model_type_id', $sourceModel->modelTypeId())
										);

			$stmt = \GO\Comments\Model\Comment::model()->find($findParams);
			while($comment = $stmt->fetch()){
				$comment->model_type_id=$this->modelTypeId();
				$comment->model_id=$this->id;
				$comment->save();
			}
		}
	}

	private function _moveFiles(ActiveRecord $sourceModel){
		if(!$this->hasFiles())
			return false;

		$sourceFolder = \GO\Files\Model\Folder::model()->findByPk($sourceModel->files_folder_id);
		if(!$sourceFolder)
			return false;

		$this->filesFolder->moveContentsFrom($sourceFolder);
	}

	/**
	 * This function forces this activeRecord to save itself.
	 */
	public function forceSave(){

		$this->_forceSave=true;
	}

	/**
	 * Override this if you need to do extra stuff after merging.
	 * Move relations for example.
	 *
	 * @param ActiveRecord $model The model that will be deleted after merging.
	 */
	protected function afterMergeWith(ActiveRecord $model){}

	/**
	 * This function will unset the invalid properties so they will not be saved.
	 */
	public function ignoreInvalidProperties(){
		$this->validate();

		foreach($this->_validationErrors as $attrib=>$error){
			GO::debug('Atribute not successfully validated, unsetting '.$attrib);
			$this->_unsetAttribute($attrib);
		}
	}

	private function _unsetAttribute($attribute){
		unset($this->$attribute);

		if(isset($this->_validationErrors[$attribute]))
			unset($this->_validationErrors[$attribute]);

		if(isset($this->_modifiedAttributes[$attribute]))
			unset($this->_modifiedAttributes[$attribute]);
	}

	/**
	 * Find the relation names that are using the given culumnName
	 *
	 * You can also provide the types of relations as an array to filter.
	 * Example array:
	 *	$relationTypes = array(
	 *		\GO\Base\Db\ActiveRecord::BELONGS_TO,
	 *		\GO\Base\Db\ActiveRecord::HAS_MANY,
	 *		\GO\Base\Db\ActiveRecord::HAS_ONE,
	 *		\GO\Base\Db\ActiveRecord::MANY_MANY
	 *	);
	 *
	 * You can also leave the $relationTypes variable empty to search for all types
	 *
	 * @param StringHelper $columnName
	 * @param array $relationTypes
	 * @return array With names of the relations Eg. array('categories','users');
	 */
	public function findRelationsByColumnName($columnName,$relationTypes = false){

		$relationNames = array();

		if(!is_array($relationTypes) && $relationTypes !== false)
			Throw new Exception('RelationTypes needs to be false or an array');

		$relations = $this->getRelations();

		foreach($relations as $relationKey=>$relation){

			if($relationTypes !== false){

				if(in_array($relation['type'], $relationTypes) && $relation['field'] === $columnName){
					$relationNames[] = $relationKey;
				}

			} else {

				if($relation['field'] === $columnName){
					$relationNames[] = $relationKey;
				}

			}

		}

		return $relationNames;
	}
	
	
	/**
	 * 
	 * Get's the class name without the namespace
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 * 
	 * @return string
	 */
	public static function getClassName() {
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}
	
}
