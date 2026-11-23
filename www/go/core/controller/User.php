<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\http\Request;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\jmap\Entity;
use go\core\model;
use go\core\util\ArrayObject;
use go\modules\community\history\model\LogEntry;
use go\modules\community\history\Module as HistoryModule;


/**
 * @extends EntityController<\go\core\model\User>
 */
class User extends EntityController {

	protected function getDefaultQueryFilter() : array
	{
		return ['showDisabled'=> false];
	}

	protected function canUpdate(Entity $entity): bool
	{
		if(go()->getAuthState()->isAdmin()) {
			return true;
		}
		
		if($this->rights->mayChangeUsers) {
			// Changing groups is a big no-no!
			if($entity->isModified('groups')) {
				return false;
			}
			// Changing an administrator could be used to take over the account. For example by changing the recovery e-mail.
			if($entity instanceof model\User && $entity->isAdmin()) {
				return false;
			}
			// Modules may refuse changes to their properties
			if(self::fireEvent(self::EVENT_BEFORE_CAN_UPDATE, $entity) === false) {
				return false;
			}
			// Level is not used for users. When user management is enabled only check read permissions
			return $entity->hasPermissionLevel(model\Acl::LEVEL_READ);
		}
		// A user cannot change their own groups!
		if($entity->isModified('groups')) {
			return false;
		}

		// These are managed by admins and user managers
		if($entity->isModified(model\User::MANAGER_PROPERTIES)) {
			return false;
		}
		
		return parent::canUpdate($entity);
	}

	protected function canDestroy(Entity $entity): bool
	{
		return go()->getAuthState()->isAdmin();
	}

	protected function canCreate(Entity $entity): bool
	{
		if(!$this->rights->mayChangeUsers) {
			return false;
		}
		// User managers may not create administrators or put users in other privileged groups
		return !($entity instanceof model\User) || $entity->currentUserMayAssignGroups();
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\User::class;
	}

	/**
	 * Switch the current session to another user. Only for administrators.
	 *
	 * @apiParam string userId Required. ID of the user to log in as. The user must be enabled.
	 * @apiResponse {success: bool}
	 * @apiPermission Only administrators.
	 * @throws InvalidArguments
	 * @throws Forbidden
	 */
	public function loginAs($params) {
		
		if(!isset($params['userId'])) {
			throw new InvalidArguments("Missing parameter userId");
		}
		
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}
		
		$user = model\User::findById($params['userId']);

		if(!$user) {
			throw new NotFound();
		}
		
		if(!$user->enabled) {
			throw new Exception("This user is disabled");
		}

		$admin = go()->getAuthState()->getUser(['id', 'username']);
		
		$success = go()->getAuthState()->changeUser($params['userId']);

		if($success) {
			$this->logLoginAs($admin, $user);
		}
		
		Response::get()->addResponse(['success' => $success]);
	}

	/**
	 * Write which administrator logged in as which user to the error log and the history
	 *
	 * @throws Exception
	 */
	private function logLoginAs(model\User $admin, model\User $user): void
	{
		$ip = Request::get()->getRemoteIpAddress();
		ErrorHandler::log("Administrator '" . $admin->username . "' (" . $admin->id . ") logged in as user '" . $user->username . "' (" . $user->id . ") from IP: '" . $ip . "'");

		if(!model\Module::isInstalled('community', 'history') || !HistoryModule::$enabled) {
			return;
		}

		$log = new LogEntry();
		$log->setEntity($user);
		$log->description = $user->username . ' [' . $ip . '] (' . str_replace('{username}', $admin->username, go()->t("logged in by {username}")) . ')';
		$log->setAction('login');
		$log->changes = null;
		$log->createdBy = $admin->id;
		$log->setAclId($user->findAclId());
		if(!$log->save()) {
			ErrorHandler::log("Could not save the history of logging in as user " . $user->id);
		}
	}

  /**
   * Handles the Foo entity's Foo/query command
   *
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/query
   */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

  /**
   * Handles the Foo entity's Foo/get command
   *
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/get
   */
	public function get(array $params): ArrayObject
	{
		return $this->defaultGet($params);
	}

  /**
   * Handles the Foo entity's Foo/set command
   *
   * @see https://jmap.io/spec-core.html#/set
   * @inheritDoc
   */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}


  /**
   * Handles the Foo entity's Foo/changes command
   *
   * @inheritDoc
   * @see https://jmap.io/spec-core.html#/changes
   */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}

	public function export(array $params)
	{
		return $this->defaultExport($params);
	}

	public function import(array $params)
	{
		return $this->defaultImport($params);
	}

	public function importCsvMapping(array $params)
	{
		return $this->defaultImportCSVMapping($params);
	}
	/**
	 * @param array $params
	 * @return ArrayObject
	 */
	public function exportColumns(array $params)
	{
		return $this->defaultExportColumns($params);
	}
}
