<?php
namespace go\modules\community\webpush;

use go\core\auth\TemporaryState;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\webpush\controller\PushSubscription as PushSubscriptionController;
use go\modules\community\webpush\model\PushSubscription;
use PHPUnit\Framework\TestCase;

/**
 * Every user may subscribe their own devices to push messages, and only sees and changes their own subscriptions.
 * Only verified subscriptions get the alerts.
 */
class PushSubscriptionTest extends TestCase
{
	private static $authState;
	private static bool $installed = false;
	private static User $owner;
	private static User $other;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			if (!ModuleModel::findByName('community', 'webpush')) {
				self::assertNotFalse(Module::get()->install(), 'Could not install the webpush module');
				self::$installed = true;
			}
			self::$owner = self::createUser('pushowner');
			self::$other = self::createUser('pushother');

			// Access to the module through their personal groups
			$module = ModuleModel::findByName('community', 'webpush');
			foreach ([self::$owner, self::$other] as $user) {
				$permission = new Permission($module);
				$permission->setRights(['mayRead' => true]);
				$module->permissions[$user->getPersonalGroup()->id] = $permission;
			}
			if (!$module->save()) {
				throw new \Exception($module->getValidationErrorsAsString());
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$ids = array_filter([isset(self::$owner) ? self::$owner->id : null, isset(self::$other) ? self::$other->id : null]);
		if ($ids) {
			// Deleting the users removes their personal groups and with them the module permissions
			User::delete(['id' => $ids]);
		}
		if (self::$installed) {
			Module::get()->uninstall();
		}
		go()->setAuthState(self::$authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
		if (!$user->save()) {
			throw new \Exception($user->getValidationErrorsAsString());
		}
		return $user;
	}

	private static function base64url(string $v): string
	{
		return rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
	}

	/**
	 * Keys of a real browser subscription are needed to encrypt the verification push
	 */
	private function keys(): array
	{
		$key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
		$ec = openssl_pkey_get_details($key)['ec'];
		return ['p256dh' => self::base64url("\x04" . $ec['x'] . $ec['y']), 'auth' => self::base64url(random_bytes(16))];
	}

	private function create(int $asUserId, array $extra = []): array
	{
		go()->setAuthState(new TemporaryState($asUserId));
		return (new PushSubscriptionController())->set(['create' => ['new' => array_merge([
			// A closed local port, so the verification push fails without leaving this machine
			'url' => 'http://127.0.0.1:9/push',
			'deviceClientId' => uniqid('device'),
			'keys' => $this->keys()
		], $extra)]])->getArray();
	}

	public function testUserSubscribesOnlyThemselves(): void
	{
		$result = $this->create(self::$owner->id, ['userId' => self::$other->id]);
		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		$id = $result['created']['new']['id'];

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertSame((string) self::$owner->id, (string) PushSubscription::findById($id)->userId);
	}

	public function testOtherUsersDontSeeOrChangeTheSubscription(): void
	{
		$id = $this->create(self::$owner->id)['created']['new']['id'];

		go()->setAuthState(new TemporaryState(self::$other->id));
		$controller = new PushSubscriptionController();
		$this->assertNotContains($id, $controller->query([])->getArray()['ids']);
		$this->assertSame([], $controller->get(['ids' => [$id]])->getArray()['list']);

		$result = $controller->set(['destroy' => [$id]])->getArray();
		$this->assertArrayHasKey($id, $result['notDestroyed'] ?? []);

		go()->setAuthState(new TemporaryState(self::$owner->id));
		$this->assertContains($id, (new PushSubscriptionController())->query([])->getArray()['ids']);
	}

	public function testOnlyVerifiedSubscriptionsGetAlerts(): void
	{
		$id = $this->create(self::$owner->id)['created']['new']['id'];
		$this->assertNotContains($id, array_map(fn($s) => $s->id, PushSubscription::findActiveForUser(self::$owner->id)));

		$code = go()->getDbConnection()->selectSingleValue('verificationCode')->from('core_push_subscription')->where('id', '=', $id)->single();
		go()->setAuthState(new TemporaryState(self::$owner->id));
		$result = (new PushSubscriptionController())->set(['update' => [$id => ['verificationCode' => $code]]])->getArray();
		$this->assertArrayHasKey($id, $result['updated'] ?? [], var_export($result['notUpdated'] ?? null, true));

		$this->assertContains($id, array_map(fn($s) => $s->id, PushSubscription::findActiveForUser(self::$owner->id)));
	}
}
