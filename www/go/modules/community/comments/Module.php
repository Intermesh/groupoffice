<?php
namespace go\modules\community\comments;

use go\core;
use go\core\cron\GarbageCollection;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Query;
use GO\Base\Db\ActiveRecord;
use go\core\model\Group;
use go\core\model\Module as GoModule;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	public function defineListeners() {
		GarbageCollection::on(GarbageCollection::EVENT_RUN, static::class, 'garbageCollection');
	}

	protected function afterInstall(GoModule $model) {
		
		if(!$model->findAcl()
						->addGroup(Group::ID_INTERNAL)
						->save()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}
	
	public static function garbageCollection() {
//		$types = EntityType::findAll();
//
//		go()->debug("Cleaning up comments");
//		foreach($types as $type) {
//			if($type->getName() == "Link" || $type->getName() == "Search" ||  !is_a($type->getClassName(), Entity::class, true)) {
//				continue;
//			}
//
//			$cls = $type->getClassName();
//
//			if(is_a($cls,  ActiveRecord::class, true)) {
//				$tableName = $cls::model()->tableName();
//			} else{
//				$tableName = array_values($cls::getMapping()->getTables())[0]->getName();
//			}
//			$query = (new Query)->select('sub.id')->from($tableName);
//
//			$stmt = go()->getDbConnection()->delete('comments_comment', (new Query)
//				->where('entityId', '=', $type->getId())
//				->andWhere('entityId', 'NOT IN', $query)
//			);
//			$stmt->execute();
//
//			go()->debug("Deleted ". $stmt->rowCount() . " comments for $cls");
//		}
	}
}
