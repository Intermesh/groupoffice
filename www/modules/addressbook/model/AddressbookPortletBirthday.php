<?php


namespace GO\Addressbook\Model;


class AddressbookPortletBirthday extends \GO\Base\Db\ActiveRecord
{
	public function tableName()
	{
		return 'addressbook_portlet_birthday';
	}

	public function primaryKey()
	{
		return array('id');
	}

	public function relations()
	{
		return array(
			'addressbook' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\Addressbook', 'field' => 'addressbookId', 'delete' => false),
		);
	}

}