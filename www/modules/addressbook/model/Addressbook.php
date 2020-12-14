<?php


namespace GO\Addressbook\Model;


class Addressbook extends \GO\Base\Model\AbstractUserDefaultModel
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function aclField(){
		return 'aclId';
	}

	public function tableName(){
		return 'addressbook_addressbook';
	}

}