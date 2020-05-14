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
		//ActiveRecord::model()->addListener('save', self::class, 'onActiveRecordSave');
		//ActiveRecord::model()->addListener('delete', self::class, 'onActiveRecordDelete');
	}

	public function defineListeners() {
		Entity::on(Entity::EVENT_SAVE, static::class, 'onEntitySave');
		Entity::on(Entity::EVENT_DELETE, static::class, 'onEntityDelete');
		//cant get id
		Token::on(Entity::EVENT_SAVE, static::class, 'onLogin');
	}

	static function onActiveRecordSave(ActiveRecord $record) {
		self::logActiveRecord($record,$record->isNew ? 'create' : 'update');
	}
	static function onActiveRecordDelete($record) {
		self::logActiveRecord($record, 'delete');
	}

	private static function logActiveRecord($record, $action) {
		$pk = $record->getPk();
		$log = new LogEntry();
		$log->entityId = is_array($pk) ? var_export($pk, true) : $pk;
		$log->entityTypeId = $record->className();
		$log->setAction($action);
		$log->changes = json_encode($record->getLogJSON($action));
		$log->setAclId($record->findAclId());
		$log->save();
	}

	static function onEntitySave(Entity $entity) {
		self::logEntity($entity, $entity->isNew() ? 'create' : 'update');
	}

	static function onEntityDelete($query) {
		// find al items with $query and log that they are being deleted
		self::logEntity($query, 'delete');
	}

	private static function logEntity(Entity $entity, $action) {
		if(is_a($entity,LogEntry::class)) return;

		$log = new LogEntry();
		$log->entityId = $entity->id;
		$log->removeAcl = is_a($entity, AclOwnerEntity::class);
		$log->description = self::parseDescription($entity);
		$log->entityTypeId = $entity->entityType()->getId();
		$log->setAction($action);
		$log->changes = json_encode($action ==='update' ? $entity->getModified() : $entity->toArray());
		$log->setAclId($entity->findAclId());
		$log->save();
	}

	private static function parseDescription($entity) {
		$m = $entity::getMapping();
		if($m->getColumn('title')) {
			return $entity->title;
		}
		if($m->getColumn('name')) {
			return $entity->name;
		}
		if($m->getColumn('displayName')) {
			return $entity->displayName;
		}
		if($m->getColumn('description')) {
			return $entity->description;
		}
		return get_class($entity);
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