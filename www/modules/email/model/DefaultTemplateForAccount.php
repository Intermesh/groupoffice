<?php

namespace GO\Email\Model;

use GO\Base\Model\Template;

class DefaultTemplateForAccount extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'email_default_email_account_templates';
	}
	
	public function primaryKey() {
		return 'account_id';
	}
	
	public function relations(){
		return array(
			'emailTemplate' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Base\Model\Template', 'field'=>'template_id'),
			'emailAccount' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Email\Model\Account', 'field'=>'account_id')
		);
	}

	
	protected function defaultAttributes() {
		$attr = parent::defaultAttributes();
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->limit(1);
		$stmt = Template::model()->find($findParams);
		
		if($template=$stmt->fetch())
		{
			$attr['template_id']=$template->id;
		}
		
		return $attr;
	}
}
