<?php
// Test cases for groupoffice.yml. Run with: semgrep --test scripts/semgrep
// "ruleid: x" means the next line must match rule x, "ok: x" means it must not.

namespace go\modules\community\test\controller;

use go\core\Controller;
use go\core\exception\Forbidden;
use GO;

class Test extends Controller
{
	public function clientIp()
	{
		// ruleid: go-client-ip-from-forwarded-header
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		// ruleid: go-client-ip-from-forwarded-header
		$ip = $_SERVER['HTTP_X_REAL_IP'];
		// ruleid: go-client-ip-from-remote-addr
		$ip = $_SERVER['REMOTE_ADDR'];
		// ok: go-client-ip-from-remote-addr
		$ip = go()->getRequest()->getRemoteIpAddress();
	}

	public function code($params)
	{
		// ruleid: go-eval
		eval($params['code']);

		// ruleid: go-include-from-data-folder
		require go()->getDataFolder()->getPath() . '/language/en.php';
		$path = go()->getConfig()['file_storage_path'] . 'x.php';
		// ruleid: go-include-from-data-folder
		include $path;
		// ok: go-include-from-data-folder
		require __DIR__ . '/en.php';

		// ruleid: go-unserialize-objects
		$v = unserialize($params['v']);
		// ok: go-unserialize-objects
		$v = unserialize($params['v'], ['allowed_classes' => false]);
	}

	public function sql($params)
	{
		$db = go()->getDbConnection();
		// ruleid: go-sql-from-request
		$db->select('*')->from('core_user')->where("username = '" . $params['username'] . "'");
		// ruleid: go-sql-from-request
		$db->query("SELECT * FROM core_user WHERE id = " . $params['id']);
		// ruleid: go-sql-from-request
		$db->select('*')->from('core_user')->where($params['column'], '=', 1);
		// ok: go-sql-from-request
		$db->select('*')->from('core_user')->where('username', '=', $params['username']);
		// ok: go-sql-from-request
		$db->select('*')->from('core_user')->where(['username' => $params['username']]);
		// ok: go-sql-from-request
		$db->query("SELECT * FROM core_user WHERE id = " . (int) $params['id']);
	}

	public function classes($params)
	{
		$cls = $params['class'];
		// ruleid: go-class-from-request
		$o = new $cls();
		// ruleid: go-class-from-request
		$cls::create();
		$method = $params['method'];
		// ruleid: go-class-from-request
		$this->$method();
		// ruleid: go-class-from-request
		call_user_func($params['callback']);

		$entityType = \go\core\orm\EntityType::findByName($params['entity']);
		$entityCls = $entityType->getClassName();
		// ok: go-class-from-request
		$entityCls::find();

		$user = \go\core\model\User::findById($params['id']);
		// ok: go-class-from-request
		$user->save();
	}

	public function shell($params)
	{
		// ruleid: go-shell-from-request
		exec('zip -r out.zip ' . $params['file']);
		// ok: go-shell-from-request
		exec('zip -r out.zip -- ' . escapeshellarg($params['file']));
	}

	public function paths($params)
	{
		// ruleid: go-path-from-request
		file_put_contents('/tmp/' . $params['name'], 'x');
		// ruleid: go-path-from-request
		$file = go()->getTmpFolder()->getFile($params['name']);
		// ok: go-path-from-request
		file_put_contents('/tmp/' . basename($params['name']), 'x');
		// ok: go-path-from-request
		$file = fopen($_FILES['upload']['tmp_name'], 'r');
	}

	public function imap($params, $imap)
	{
		// ruleid: go-imap-command-from-request
		$imap->send_command('SEARCH TEXT "' . $params['query'] . '"' . "\r\n");
	}

	public function guards($params)
	{
		if (!go()->getAuthState()->isAdmin()) {
			// ruleid: go-exception-not-thrown
			new Forbidden("Only admins");
		}
		if (!go()->getAuthState()->isAdmin()) {
			// ok: go-exception-not-thrown
			throw new Forbidden("Only admins");
		}
		// ok: go-exception-not-thrown
		$e = new \Exception("x");
	}

	public function aclBypassNotRestored()
	{
		// ruleid: go-acl-bypass-not-restored
		GO::$ignoreAclPermissions = true;
		$this->doSomething();
	}

	public function aclBypassRestored()
	{
		// ok: go-acl-bypass-not-restored
		$old = GO::setIgnoreAclPermissions(true);
		try {
			$this->doSomething();
		} finally {
			GO::setIgnoreAclPermissions($old);
		}
	}

	public function aclBypassRestoredInFinallyWithCatch($ignore)
	{
		$old = null;
		try {
			if ($ignore) {
				// ok: go-acl-bypass-not-restored
				$old = GO::setIgnoreAclPermissions(true);
			}
			$this->doSomething();
		} catch (\Exception $e) {
			$this->handle($e);
		} finally {
			if (isset($old))
				GO::setIgnoreAclPermissions($old);
		}
	}

	public function aclBypassFinallyWithoutRestore()
	{
		try {
			// ruleid: go-acl-bypass-not-restored
			$old = GO::setIgnoreAclPermissions(true);
			$this->doSomething();
		} finally {
			$this->cleanup();
		}
	}

	public function aclBypassPropertyRestored()
	{
		$old = GO::$ignoreAclPermissions;
		// ok: go-acl-bypass-not-restored
		GO::$ignoreAclPermissions = true;
		$this->doSomething();
		GO::$ignoreAclPermissions = $old;
	}
}

class SetterModel extends \go\core\orm\Entity
{
	private $pending;

	public function setDefaultGroups(array $groups)
	{
		// ruleid: go-setter-persists
		go()->getDbConnection()->exec("DELETE FROM core_group_default_group");
		foreach ($groups as $groupId) {
			// ruleid: go-setter-persists
			go()->getDbConnection()->insert("core_group_default_group", ['groupId' => $groupId])->execute();
		}
	}

	public function setEntities($entities)
	{
		// ruleid: go-setter-persists
		\go\core\model\Group::entityType()->setDefaultAcl($entities);
	}

	public function setTemplate($values)
	{
		// ruleid: go-setter-persists
		$this->getTemplate()->setValues($values)->save();
	}

	public function setStaged($values)
	{
		// ok: go-setter-persists
		$this->pending = $values;
	}

	protected function internalSave(): bool
	{
		// ok: go-setter-persists
		go()->getDbConnection()->exec("DELETE FROM core_group_default_group");
		return parent::internalSave();
	}
}


// Base classes and exceptions written with their namespace must match too
class LegacyTest extends \GO\Base\Controller\AbstractModelController
{
	protected function actionRequest($params)
	{
		$db = go()->getDbConnection();
		// ruleid: go-sql-from-request
		$db->query("SELECT * FROM core_user WHERE id = " . $params['id']);

		$cls = $params['class'];
		// ruleid: go-class-from-request
		$o = new $cls();

		// ruleid: go-shell-from-request
		exec('zip -r out.zip ' . $params['file']);

		// ruleid: go-path-from-request
		file_put_contents('/tmp/' . $params['name'], 'x');

		// ruleid: go-imap-command-from-request
		$params['imap']->send_command('SEARCH TEXT "' . $params['query'] . '"' . "\r\n");

		if (!go()->getAuthState()->isAdmin()) {
			// ruleid: go-exception-not-thrown
			new \go\core\exception\Forbidden("Only admins");
		}
		if (!go()->getAuthState()->isAdmin()) {
			// ruleid: go-exception-not-thrown
			new \GO\Base\Exception\AccessDenied();
		}
	}
}
