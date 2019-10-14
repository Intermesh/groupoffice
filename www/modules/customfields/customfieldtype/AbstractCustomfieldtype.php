<?php

namespace GO\Customfields\Customfieldtype;


abstract class AbstractCustomfieldtype extends \GO\Base\Observable{
	
	/**
	 * The field model that this datatype will be used for.
	 * 
	 * @var \GO\Customfields\Model\Field 
	 */
	protected $field;
	
	protected $maxLength=190;
	
	public function __construct($field=false){
		if($field)
			$this->field=$field;
	}
	
	/**
	 * The SQL to create the database field. Use '%MAXLENGTH' to be substituted
	 * by the Field model's 'max_length' attribute.
	 * 
	 * @return MySQL field 
	 */
	public function fieldSql(){
		return "VARCHAR(%maxLength%) NOT NULL default ''";
	}

	public function hasLength() {
		$fieldSql = $this->fieldSql();
		return ( get_class($this)!=='GO\Addressbook\Customfieldtype\Contact'
				&& get_class($this)!=='GO\Addressbook\Customfieldtype\Company'
				&& get_class($this)!=='GO\Files\Customfieldtype\File'
				&& get_class($this)!=='GO\Addressbook\Customfieldtype\Contact'
				&& get_class($this)!=='GO\Site\Customfieldtype\Sitefile'
				&& get_class($this)!=='GO\Site\Customfieldtype\Sitemultifile'
			) && (
				strpos(strtolower($fieldSql),'varchar')===0
//			|| strpos(strtolower($fieldSql),'int')===0
//			|| strpos(strtolower($fieldSql),'tinyint')===0
//			|| strpos(strtolower($fieldSql),'double')===0
//			|| strpos(strtolower($fieldSql),'enum')===0
//			|| strpos(strtolower($fieldSql),'float')===0
//			|| strpos(strtolower($fieldSql),'smallint')===0
//			|| strpos(strtolower($fieldSql),'mediumint')===0
//			|| strpos(strtolower($fieldSql),'integer')===0
//			|| strpos(strtolower($fieldSql),'bigint')===0
		);
	}
	
	public function getMaxLength() {
		return $this->maxLength;
	}
	
	/**
	 * This function is used when $model->customFieldRecord->att is accessed
	 * 
	 * @param StringHelper $key Database column 'col_x'
	 * @param array $attributes Customfield model attributes
	 * @return Mixed 
	 */
	public function formatRawOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){	
		return $attributes[$key];
	}

	/**
	 * This function is used to format the database value for the interface edit
	 * form.
	 * 
	 * @param StringHelper $key Database column 'col_x'
	 * @param array $attributes Customfield model attributes
	 * @return Mixed 
	 */
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){	
		return $attributes[$key];
	}
	
	/**
	 * This function is used to format the value that comes from the interface for
	 * the database.
	 * 
	 * @param StringHelper $key Database column 'col_x'
	 * @param array $attributes Customfield model attributes
	 * @return Mixed 
	 */
	
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		return $attributes[$key];
	}
	
	/**
	 * Can be overridden if you want. For example, if the extended class entails
	 * companies, return 'GO\Addressbook\Model\Company'. If it entails users,
	 * return 'GO\Base\Model\User'.
	 * @return boolean/string
	 */
	public static function getModelName() {
		return false;
	}
	
	/**
	 * This function is used to format the database value for the interface display
	 * panel (HTML).
	 * 
	 * @param StringHelper $key Database column 'col_x'
	 * @param array $attributes Customfield model attributes
	 * @return Mixed 
	 */
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		return \GO\Base\Util\StringHelper::text_to_html($attributes[$key]);
	}
	
	/**
	 * Returns the name of this custom field type localized.
	 * 
	 * @return String
	 */
	abstract public function name();
	
	
	/**
	 * Validate the input
	 * 
	 * @param mixed $value The value of the customfield that needs to be validated
	 * @return boolean Is the value valid?
	 */
	public function validate($value){
		return true;
	}
	
	/**
	 * Get the validation error message
	 * 
	 * @return StringHelper The errormessage for this validator 
	 */
	public function getValidationError(){
		return \GO::t("The value was not formatted correctly", "customfields");
	}
	
	
	protected function getId($cf) {
		$pos = strpos($cf,':');
		return substr($cf,0,$pos);
	}

	protected function getName($cf) {
		$pos = strpos($cf,':');
		if(!$pos) {
			return $cf;
		}
		return substr($cf,$pos+1);
	}
	
	/**
	 * Include this column in quick search actions in grids
	 */
	public function includeInSearches(){
		return false;
	}
	
	public function selectForGrid(){
		return true;
	}
	
	/**
	 * Function to enable this customfield type for some models only.
	 * When no modeltype is given then this customfield will work on all models.
	 * Otherwise it will only be available for the given modeltypes.
	 * 
	 * Example:
	 *	return array('GO\Site\Model\Content','GO\Site\Model\Site');
	 *  
	 * @return array
	 */
	public function supportedModels(){
		return array();
	}
	
	/**
	 * 
	 * @return \GO\Customfields\Model\Field
	 */
	public function getField(){
		return $this->field;
	}
	
	/**
	 * Do something after the parent is saved
	 * 
	 * @param string $key
	 * @param array $attributes
	 * @param array $modifiedAttributes
	 * @param AbstractCustomFieldsRecord $model
	 */
	public function afterParentSave($key, $attributes, $modifiedAttributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		
	}
	
}
