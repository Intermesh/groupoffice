<?php

namespace GO\Customfields\Customfieldtype;


class EncryptedText extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Encrypted text';
	}
	
	public function fieldSql(){
		return "TEXT NULL";
	}
	
	public function includeInSearches() {
		return false;
	}
	
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return \GO\Base\Util\Crypt::encrypt($attributes[$key]);
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$decrypted = !empty($attributes[$key]) ? \GO\Base\Util\Crypt::decrypt($attributes[$key]) : '';
		return $decrypted;
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		if(\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
			return \GO\Base\Util\Crypt::decrypt($attributes[$key]);
		}
		$decrypted = !empty($attributes[$key]) ? '<div ext:qtip="'.htmlspecialchars(\GO\Base\Util\Crypt::decrypt($attributes[$key]),ENT_COMPAT, 'utf-8').'">'.\GO::t('pointForText').'</div>' : '';
		return $decrypted;
	}
}