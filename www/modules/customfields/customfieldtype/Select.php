<?php

namespace GO\Customfields\Customfieldtype;


class Select extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Select';
	}
	
	
	public function fieldSql(){
		//needs to be text for multiselect field
		if($this->field && $this->field->getOption("multiselect"))
			return "TEXT NULL";		
		else
			return parent::fieldSql ();
	}
	
	/**
	 * This function is used to format the database value for the interface edit
	 * form.
	 * 
	 * @param StringHelper $key Database column 'col_x'
	 * @param array $attributes Customfield model attributes
	 * @return Mixed 
	 */
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		
		$value = $attributes[$key];
		
		//implode array values with pipes for multiselect fields
		if(is_array($value))
				$value=implode('|',$value);
				
		
		return $value;
	}
	
//	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {		
//		
//		if(!empty($this->field->multiselect) && isset($attributes[$key]))
//			$attributes[$key.'[]'] = $attributes[$key];
//		
//		return parent::formatFormOutput($key, $attributes, $model);
//	}
//	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {		
		
		if(!empty($this->field->multiselect) && isset($attributes[$key]))
			$attributes[$key] = str_replace('|', ', ', $attributes[$key]);
		
		return parent::formatDisplay($key, $attributes, $model);
	}
	
	public function includeInSearches() {
		return true;
	}
}
