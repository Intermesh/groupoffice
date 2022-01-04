<?php
namespace go\modules\community\googleauthenticator;

use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\googleauthenticator\model;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;

class Module extends core\Module {

	public function getAuthor() {
		return "Intermesh BV";
	}

	public function autoInstall()
	{
		return true;
	}
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	

	public static function onMap(Mapping $mapping) {		
		$mapping->addHasOne("googleauthenticator", model\Googleauthenticator::class, ['id' => 'userId'], false);		
		return true;
	}

	protected function afterInstall(ModuleModel $model) {
		
		if(!Googleauthenticator::register()) {
			return false;
		}		
		
		if(!$model->findAcl()
						->addGroup(Group::ID_INTERNAL)
						->save()) {
			return false;
		}		
		
		return parent::afterInstall($model);
	}

	public function getSettings()
	{
		return model\Settings::get();
	}
}
