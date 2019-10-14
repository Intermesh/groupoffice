<?php

namespace GO\Customfields\Customfieldtype;


class Text extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Text';
	}
	
	public function includeInSearches() {
		return true;
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		$prefix = !empty($this->field->prefix) ? $this->field->prefix.' ' : '';
		$suffix = !empty($this->field->suffix) ? ' '.$this->field->suffix : '';
		return $prefix.\GO\Base\Util\StringHelper::text_to_html($attributes[$key]).$suffix;
	}
}
