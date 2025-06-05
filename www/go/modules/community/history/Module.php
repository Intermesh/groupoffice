<?php /** @noinspection PhpUnused */


namespace go\modules\community\history;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core;
use go\core\acl\model\AclOwnerEntity;
use go\core\cron\GarbageCollection;
use go\core\ErrorHandler;
use go\core\http\Request;
use go\core\jmap\Entity;
use go\core\model\CronJobSchedule;
use go\core\model\Search;
use go\core\model\User;
use go\core\orm\Query;
use go\modules\community\history\model\LogEntry;
use go\modules\community\history\model\Settings;
use GO\Projects2\Model\TimeEntry;

class Module extends core\Module
{
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public static $enabled = true;

	public function autoInstall(): bool
	{
		return true;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function defineListeners() {
		Entity::on(Entity::EVENT_SAVE, static::class, 'onEntitySave');
		Entity::on(Entity::EVENT_BEFORE_DELETE, static::class, 'onEntityDelete');
		User::on(User::EVENT_LOGIN, static::class, 'onLogin');
		User::on(User::EVENT_LOGOUT, static::class, 'onLogout');
		User::on(User::EVENT_BADLOGIN, static::class, 'onBadLogin');

		GarbageCollection::on(GarbageCollection::EVENT_RUN, static::class, 'onGarbageCollection');
	}

	/**
	 * @throws Exception
	 */
	public static function logActiveRecord(ActiveRecord $record, $action) {

		if(!self::$enabled || core\Installer::isInProgress()) {
			return;
		}

		//hacky but works for old code
		if(!$record->aclField() && !($record instanceof TimeEntry)) {
			return;
		}

		$log = new LogEntry();
		$log->setEntity($record);
		$log->setAction($action);

		if($action != 'delete') {
			$changes = $record->getLogJSON($action);
			$cfChanges = self::getCustomFieldChanges($record);

			if (!empty($cfChanges)) {
				$changes['customFields'] = $cfChanges;
			}


			if ($action == 'update' && empty($changes)) {
				return;
			}
			$log->changes = json_encode($changes);

			$l = LogEntry::getMapping()->getColumn('changes')->length;
			if (mb_strlen($log->changes) > $l) {
				foreach ($changes as $key => $v) {
					$changes[$key] = '... changes were too big to log ...';
				}
				$log->changes = json_encode($changes);
			}
		}

		self::saveLog($log);
	}

	/**
	 * @param Entity|ActiveRecord $entity
	 * @return array
	 */
	private static function getCustomFieldChanges($entity): array {
		if(method_exists($entity, 'getCustomFields')) {
			return $entity->getCustomFields(true)->getModified();
		} else{
			return [];
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onEntitySave(Entity $entity) {
		self::logEntity($entity, $entity->isNew() ? 'create' : 'update');
	}

	/**
	 * @param Query $query
	 * @param class-string<Entity> $cls
	 * @throws Exception
	 */
	public static function onEntityDelete(Query $query, string $cls) {

		if(!self::$enabled || core\Installer::isInProgress()) {
			return;
		}

		if(is_a($cls, LogEntry::class, true) || is_a($cls, Search::class, true) || !$cls::loggable()) {
			return;
		}

		$entities = $cls::find()->mergeWith(clone $query);
		foreach($entities as $e) {
			static::logEntity($e, 'delete');
		}
	}

	/**
	 * @throws Exception
	 */
	private static function logEntity(Entity $entity, $action) {
		if(!self::$enabled || core\Installer::isInProgress()) {
			return;
		}

		if(!$entity::loggable()) {
			return;
		}

		if($entity instanceof LogEntry || $entity instanceof Search  || $entity instanceof CronJobSchedule) {
			return;
		}

		$log = new LogEntry();
		$log->setEntity($entity);
		$log->setAction($action);

		if($action !== 'delete') {
			$changes = $entity->historyLog();
			unset($changes['modifiedAt']);
			unset($changes['acl']);
			unset($changes['aclId']);
			unset($changes['createdBy']);
			unset($changes['createdAt']);
			unset($changes['modifiedBy']);
			unset($changes['permissionLevel']);
//			unset($changes['filesFolderId']);

			$cfChanges = self::getCustomFieldChanges($entity);
			if(!empty($cfChanges)) {
				$changes['customFields'] = $cfChanges;
			}

			if(empty($changes)) {
				return;
			}

			if($action == 'create') {
				$changes = self::mapForCreate($changes);
			}
			$log->changes = json_encode($changes);

		} else {
			$log->changes = null;
		}

		$l = LogEntry::getMapping()->getColumn('changes')->length;
		if(isset($log->changes) && mb_strlen($log->changes) > $l) {
			foreach($changes as $key => $v) {
				$changes[$key] = '... changes were too big to log ...';
			}
			$log->changes = json_encode($changes);
		}

		self::saveLog($log);
	}

	private static function mapForCreate(array $changes): array {
		$changes = array_map(function($c) {
			if(array_key_exists(0, $c)) {
				return $c[0];
			} else{
				return self::mapForCreate($c);
			}
		}, $changes);

		$changes = array_filter($changes, function($c){
			return !empty($c);
		});

		return $changes;
	}


	private static function saveLog(LogEntry $log) {
		try {
			if (!$log->save()) {
				ErrorHandler::log("Could not save log for " . $log->getEntity() . " (" . $log->entityId . "): " . var_export($log->getValidationErrors(), true));
			}
		}catch(Exception $e) {

			ErrorHandler::logException($e);
			//try again with just ID in description. I had a case where there were malformed characters
			$log->description = $log->entityId . ': error in description';

			try {
				if(!$log->save()) {
					ErrorHandler::log("Could not save log for " . $log->getEntity() . " (" . $log->entityId ."): " . var_export($log->getValidationErrors(), true));
				}
			} catch(Exception $e) {
				ErrorHandler::log("Could not save log for " . $log->getEntity() . " (" . $log->entityId . "): " . var_export($log->getValidationErrors(), true));
				ErrorHandler::logException($e);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onLogin(User $user) {
		$log = new LogEntry();
		$log->setEntity($user);
		$log->description = $user->username . ' [' . Request::get()->getRemoteIpAddress() . ']';
		$log->setAction('login');
		$log->changes = null;
		$log->createdBy = $user->id;
		if(!$log->save()){
			throw new Exception("Could not save log");
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onBadLogin($username, User|null $user = null) {
		$log = new LogEntry();
		if(isset($user)) {
			$log->setEntity($user);
		} else{
			$log->entityTypeId = User::entityType()->getId();
		}
		$log->description = $username. ' [' . Request::get()->getRemoteIpAddress() . ']';
		$log->setAction('badlogin');
		$log->changes = null;
		if(!$log->save()){
			throw new Exception("Could not save log");
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onLogout(User $user) {
		$log = new LogEntry();
		$log->setEntity($user);
		$log->description = $user->username . ' [' . Request::get()->getRemoteIpAddress() . ']';
		$log->setAction('logout');
		$log->changes = null;
		$log->createdBy = $user->id;
		if(!$log->save()){
			throw new Exception("Could not save log");
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onGarbageCollection() {
		$days = (int) Module::get()->getSettings()->deleteAfterDays;

		if(!empty($days)) {
			LogEntry::delete(
				LogEntry::find()
					->removeJoin('core_entity')
					->where('createdAt', '<', (new core\util\DateTime("-" . $days . " days")))
			);
		}
	}

	public function getSettings()
	{
		return Settings::get();
	}

}