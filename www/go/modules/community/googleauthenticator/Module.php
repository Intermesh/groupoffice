<?php
namespace go\modules\community\googleauthenticator;

use go\core\module\Base;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\googleauthenticator\model;
use go\modules\core\groups\model\Group;
use go\modules\core\modules\model\Module as ModuleModel;
use go\modules\core\users\model\User;

class Module extends Base {

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
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
	
	public static function onMap(Mapping $mapping) {		
		$mapping->addRelation("googleauthenticator", model\Googleauthenticator::class, ['id' => 'userId'], false);		
		return true;
	}

}
