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

class Module extends core\Module
{

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
	static function logActiveRecord(ActiveRecord $record, $action) {

		if(!self::$enabled || core\Installer::isInProgress()) {
			return;
		}

		//hacky but works for old code
		if(!$record->aclField()) {
			return;
		}

		$log = new LogEntry();
		$log->setEntity($record);
		$log->setAction($action);
		$changes = $record->getLogJSON($action);
		if($action == 'update' && empty($changes)) {
			return;
		}
		$log->changes = json_encode($changes);

		$l = LogEntry::getMapping()->getColumn('changes')->length;
		if(mb_strlen($log->changes) > $l) {
			foreach($changes as $key => $v) {
				$changes[$key] = '... changes were too big to log ...';
			}
			$log->changes = json_encode($changes);
		}

		if(!$log->save()) {
			ErrorHandler::log("Could not save log for " . $log->getEntity() . " (" . $log->entityId ."): " . var_export($log->getValidationErrors(), true));
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onEntitySave(Entity $entity) {
		self::logEntity($entity, $entity->isNew() ? 'create' : 'update');
	}

	/**
	 * @throws Exception
	 */
	public static function onEntityDelete(Query $query, $cls) {
		if(is_a($cls, LogEntry::class, true) || is_a($cls, Search::class, true)) {
			return;
		}

		//Don't delete ACL's because we're taking them over.
		if(is_a($cls, AclOwnerEntity::class, true)) {
			$cls::keepAcls();
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
			$changes = $entity->getModified();
			unset($changes['modifiedAt']);
			unset($changes['acl']);
			unset($changes['aclId']);
			unset($changes['createdBy']);
			unset($changes['createdAt']);
			unset($changes['modifiedBy']);
			unset($changes['permissionLevel']);
			unset($changes['filesFolderId']);

			if(empty($changes)) {
				return;
			}

			if($action == 'create') {
				$changes = array_map(function($c) {
					return $c[0];
				}, $changes);

				$changes = array_filter($changes, function($c){
					return $c !== "";
				});
			}
			$log->changes = json_encode($changes);

		} else {
			$log->changes = null;
		}

		$l = LogEntry::getMapping()->getColumn('changes')->length;
		if(mb_strlen($log->changes) > $l) {
			foreach($changes as $key => $v) {
				$changes[$key] = '... changes were too big to log ...';
			}
			$log->changes = json_encode($changes);
		}

		if(!$log->save()) {
			ErrorHandler::log("Could not save log for " . $log->getEntity() . " (" . $log->entityId ."): " . var_export($log->getValidationErrors(), true));
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
		if(!$log->save()){
			throw new Exception("Could not save log");
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onBadLogin($username, User $user = null) {
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
		if(!$log->save()){
			throw new Exception("Could not save log");
		}
	}

	/**
	 * @throws Exception
	 */
	public static function onGarbageCollection() {
		$years = (int) Module::get()->getSettings()->deleteAfterYears;

		if(!empty($years)) {
			LogEntry::delete(LogEntry::find()->where('createdAt', '<', (new core\util\DateTime("-" . $years . " years"))));
		}
	}

	public function getSettings()
	{
		return Settings::get();
	}

}