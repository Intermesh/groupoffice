<?php


namespace go\modules\community\history;

use GO\Base\Db\ActiveRecord;
use go\core;
use go\core\acl\model\AclOwnerEntity;
use go\core\model\Token;
use go\core\jmap\Entity;
use go\modules\community\history\model\LogEntry;

class Module extends core\Module
{

	public function getAuthor() {
		return "Intermesh BV <info@intermesh.nl>";
	}

	public static function initListeners(){
//		ActiveRecord::model()->addListener('save', self::class, 'onActiveRecordSave');
//		ActiveRecord::model()->addListener('delete', self::class, 'onActiveRecordDelete');
	}

	public function defineListeners() {
		Entity::on(Entity::EVENT_SAVE, static::class, 'onEntitySave');
		Entity::on(Entity::EVENT_BEFORE_DELETE, static::class, 'onEntityDelete');
		//cant get id
		Token::on(Entity::EVENT_SAVE, static::class, 'onLogin');
	}

	static function onActiveRecordSave(ActiveRecord $record, $cache, $action) {

		if(!$cache) {
			return;
		}

		$pk = $record->getPk();
		$log = new LogEntry();
		$log->entityId = is_array($pk) ? var_export($pk, true) : $pk;
		$log->entityTypeId = $record::entityType()->getId();
		$log->setAction($action);
		$log->description = $cache ? $cache['name'] : get_class($record);
		$log->changes = json_encode($record->getLogJSON($action));
		$log->setAclId($record->findAclId());
		if(!$log->save()) {
			throw new \Exception("Could not save log");
		}
	}

	static function onEntitySave(Entity $entity) {
		self::logEntity($entity, $entity->isNew() ? 'create' : 'update');
	}

	static function onEntityDelete(core\orm\Query $query, $cls) {
		// find al items with $query and log that they are being deleted
		if(!method_exists($cls, 'getSearchName')) return;

		$entities = $cls::find()->mergeWith(clone $query);

		foreach($entities as $e) {
			static::logEntity($e, 'delete');
		}
	}

	private static function logEntity(Entity $entity, $action) {
		if(!method_exists($entity, 'getSearchName')) return;

		$log = new LogEntry();
		$log->entityId = $entity->id;
		$log->removeAcl = is_a($entity, AclOwnerEntity::class);
		$log->description = $entity->getSearchName();
		$log->entityTypeId = $entity->entityType()->getId();
		$log->setAction($action);
		$log->changes = json_encode($action ==='update' ? $entity->getModified() : $entity->toArray());
		$log->setAclId($entity->findAclId());
		if(!$log->save()) {
			throw new \Exception ("Could not save log");
		}
	}


	static function onLogin(Token $token) {
		$log = new LogEntry();
		$log->entityId = $token->userId;
		$log->removeAcl = 0;
		$log->description = $token->remoteIpAddress;
		$log->entityTypeId = core\orm\EntityType::findByName('User')->getId(); // token doesnt have one
		$log->setAction('login');
		$log->changes = '{"login":"success"}';
		$log->setAclId($token->findAclId());
		$log->save();
	}

}