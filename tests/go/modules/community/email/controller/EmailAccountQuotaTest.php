<?php

namespace go\modules\community\email\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\email\model;
use go\modules\community\email\Module;
use PHPUnit\Framework\TestCase;

class EmailAccountQuotaTest extends TestCase {

	private static int $accountId;
	private static int $aclId;
	private static User $allowed;
	private static User $denied;

	public static function setUpBeforeClass(): void {
		$module = Module::get();
		if (!$module->isInstalled()) {
			self::assertNotFalse($module->install(), "Could not install email module");
		}

		self::$allowed = self::createUser('quotaallowed');
		self::$denied = self::createUser('quotadenied');

		// both may use the module, so only the account ACL decides
		$moduleModel = ModuleModel::findByName('community', 'email');
		foreach ([self::$allowed, self::$denied] as $user) {
			$moduleModel->permissions[Group::findPersonalGroupID($user->id)] = new Permission($moduleModel);
		}
		self::assertTrue($moduleModel->save());

		$acl = new Acl();
		$acl->entityTypeId = model\EmailAccount::entityType()->getId();
		$acl->addGroup(Group::findPersonalGroupID(self::$allowed->id), Acl::LEVEL_READ);
		self::assertTrue($acl->save());
		self::$aclId = $acl->id;

		// raw insert because saving an EmailAccount connects to the IMAP server
		$db = go()->getDbConnection();
		$db->insert('email_account', ['name' => 'quota', 'email' => 'quota@intermesh.localhost', 'mtaDsn' => '',
			'mdaDsn' => '', 'mdaCapabilities' => '["CAPABILITY","IMAP4rev1","IDLE"]', 'createdAt' => '2026-10-01 00:00:00',
			'aclId' => self::$aclId])->execute();
		self::$accountId = (int)$db->getPDO()->lastInsertId();
	}

	private static function createUser(string $username): User {
		$user = new User();
		$user->username = $username;
		$user->setPassword('sdfTgs-Dfgg-$#ddg');
		$user->displayName = $username;
		$user->email = $user->recoveryEmail = $username . '@intermesh.localhost';
		self::assertTrue($user->save());
		return $user;
	}

	public static function tearDownAfterClass(): void {
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		go()->getDbConnection()->delete('email_account', ['id' => self::$accountId])->execute();
		$moduleModel = ModuleModel::findByName('community', 'email');
		foreach ([self::$allowed, self::$denied] as $user) {
			unset($moduleModel->permissions[Group::findPersonalGroupID($user->id)]);
		}
		$moduleModel->save();
		User::delete(['id' => [self::$allowed->id, self::$denied->id]]);
		Acl::delete(['id' => self::$aclId]);
	}

	public function testQuotaWithoutAccessIsForbidden() {
		go()->setAuthState(new TemporaryState(self::$denied->id));
		try {
			$this->expectException(Forbidden::class);
			(new EmailAccount())->quota(['accountId' => (string)self::$accountId]);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		}
	}

	public function testQuotaWithoutServerSupportIsEmpty() {
		go()->setAuthState(new TemporaryState(self::$allowed->id));
		try {
			// the stored capabilities have no QUOTA, so the (unconfigured) IMAP server isn't contacted
			$this->assertSame(['accountId' => (string)self::$accountId, 'list' => []],
				(new EmailAccount())->quota(['accountId' => (string)self::$accountId]));
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		}
	}
}
