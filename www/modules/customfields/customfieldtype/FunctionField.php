<?php


namespace GO\Customfields\Customfieldtype;


class FunctionField extends AbstractCustomfieldtype {

	public function name() {
		return 'Function';
	}
	
	public function fieldSql() {
		return "DOUBLE NULL";
	}

//	public function formatFormOutput($column, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
//		$result_string = '';
//
//		$function = $this->field->getOption("function");
//		
//		if (!empty($function)) {
//			
//			$this->fireEvent('formatformoutput',array($this, $column, &$attributes, $model, &$function));
//			
//			preg_match_all('/\{([^}]*)\}/',$function,$matches);
//			if (!empty($matches[1])) {
//				foreach ($matches[1] as $key) {		
//					if(!isset($attributes[$key])||$attributes[$key]==='')
//						return null;
//					else
//						$value = $attributes[$key];
//					$function = str_replace('{' . $key . '}', floatval($value), $function);				
//				}
//			}
//			$function = preg_replace('/\{[^}]*\}/', '0',$function);
//			
//			eval("\$result_string=" . $function . ";");
//			
////			\GO::debug("Function ($column): ".$this->field->function.' => '.$f.' = '.$result_string);
//		}
//		
//
//		$attributes[$column] = \GO\Base\Util\Number::localize($result_string);
//		return $attributes[$column];
//	}

	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		// recalculate on display will fail sum of col1 and col2 + col3 as the sum would already be localized
		// however when updating a record the fields need to be recalculated so reverted
		$value = $this->formatFormOutput($key, $attributes, $model);
		if (isset($value)) {
			$prefix = !empty($this->field->prefix) ? $this->field->prefix.' ' : '';
			$suffix = !empty($this->field->suffix) ? ' '.$this->field->suffix : '';
			return $prefix.$value.$suffix;
		} else {
			return null;
		}
	}
	
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		$result_string = '';

		$f = $this->field->getOption("function");
		if (!empty($f)) {
			foreach ($attributes as $key=>$value) {
				
					$f = str_replace('{' . $key . '}', floatval(\GO\Base\Util\Number::unlocalize($value)), $f);
				
			}
			$f = preg_replace('/\{[^}]*\}/', '0',$f);
			
			$old = ini_set("display_errors", "on"); //if we don't set display_errors to on the next eval will send a http 500 status. Wierd but this works.
			@eval("\$result_string=" . $f . ";");
			if($old!==false)
				ini_set("display_errors", $old);
			
			if($result_string=="") {
				//$result_string=""
				unset($attributes[$key]);
				return 0;
			}			
		}

		$attributes[$key] = $result_string;
		return (double) $attributes[$key];
	}

}
