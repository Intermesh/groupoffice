<?php


namespace go\modules\community\history;

use GO\Base\Db\ActiveRecord;
use go\core;
use go\core\model\Token;
use go\core\orm\Entity;
use go\modules\community\history\model\LogEntry;

class Module extends core\Module
{
	public function getAuthor() {
		return "Intermesh BV <info@intermesh.nl>";
	}

	public static function initListeners(){
		ActiveRecord::model()->addListener('save', self::class, 'onActiveRecordSave');
		ActiveRecord::model()->addListener('delete', self::class, 'onActiveRecordDelete');
	}

	public function defineListeners() {
		Entity::on(Entity::EVENT_SAVE, static::class, 'onEntitySave');
		Entity::on(Entity::EVENT_DELETE, static::class, 'onEntityDelete');
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
		$log->action = $action;
		$log->changes = json_encode($record->getLogJSON($action));
		$log->setAclId($record->findAclId());
		$log->save();
	}

	static function onEntitySave(Entity $entity, $action) {
		self::logEntity($entity, $entity->isNew() ? 'create' : 'update');
	}

	static function onEntityDelete($entity) {
		self::logEntity($entity, 'delete');
	}

	private static function logEntity($entity, $action) {
		$log = new LogEntry();
		$log->entityId = $entity->id;
		$log->entityTypeId = $entity->entityType()->id;
		$log->action = $action;
		$log->changes = json_encode($action ==='delete' ? $entity->toArray() : $entity->getOldValues());
		$log->setAclId($entity->findAclId());
		$log->save();
	}

	static function onLogin($token) {
		// todo
	}

}