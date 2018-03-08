<?php

namespace GO\Users\Customfields\Model;


class User extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Users\Model\CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	public function extendsModel() {
		return "GO\Base\Model\User";
	}
}