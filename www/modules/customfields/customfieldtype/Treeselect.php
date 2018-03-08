<?php

namespace GO\Customfields\Customfieldtype;


class Treeselect extends Select{
	
	public function name(){
		return 'Treeselect';
	}
	
	public function fieldSql(){
		//needs to be text for multiselect field
		if(!empty($this->field) && $this->field->multiselect)
			return "TEXT NULL";		
		else
			return parent::fieldSql ();
	}
	
	public function includeInSearches() {
		return true;
	}
	

	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		
		if(!empty($attributes[$key])) {

			//multiselect is only valid for the last treeselect_slave
			if(!empty($this->field->multiselect)){

				$value_arr=array();
				$id_value_arr = explode('|', $attributes[$this->field->dataname]);
				foreach($id_value_arr as $value){
					$id_value = explode(':', $value);
					if(isset($id_value[1])){
						array_shift($id_value);
						$value_arr[]=implode(':', $id_value);
					}
				}

				$attributes[$key] = implode(', ', $value_arr);
			}else {
				$value = explode(':', $attributes[$key]);			
				//var_dump(\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport);
				if(isset($value[1])){
					
					// Only strip the first part
					array_shift($value);
					$attributes[$key] = implode(':', $value);
					
					//$attributes[$key] = $value[1];
				}
			}
		}
		
		return $attributes[$key];
	}
    
    public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
	
      if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
          return parent::formatFormOutput($key, $attributes, $model);
      }else
      {
          return $this->getName($attributes[$key]);
      }		
	}	
	
	

}