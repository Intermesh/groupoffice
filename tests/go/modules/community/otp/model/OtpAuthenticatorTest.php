<?php
namespace go\modules\community\otp\model;

use DateInterval;
use go\core\auth\TemporaryState;
use go\core\controller\User as UserController;
use go\core\exception\Forbidden;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\otp\Module;
use PHPUnit\Framework\TestCase;

/**
 * An expiry date disables the authenticator, so users may not set it through the API and changing it on an existing
 * authenticator requires the current password.
 */
class OtpAuthenticatorTest extends TestCase
{
	private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
	private const OTHER_SECRET = 'KRUGKIDROVUWG2ZAMJZG653OEBTG66BA';

	private static bool $installedByTest = false;
	private $authState;
	private User $user;

	public static function setUpBeforeClass(): void
	{
		// The module is not installed by default in the test environment
		if (!ModuleModel::findByName('community', 'otp')) {
			self::assertNotFalse(Module::get()->install(), 'Could not install the otp module');
			self::$installedByTest = true;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installedByTest) {
			Module::get()->uninstall();
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('otpuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	/**
	 * Give the test user a verified, permanent authenticator. Done as admin, so no password is needed.
	 */
	private function enableOtp(): void
	{
		$otp = new OtpAuthenticator($this->user);
		$otp->setSecret(self::SECRET);
		$otp->setVerify($otp->getCode(self::SECRET));
		$this->user->otp = $otp;
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));
	}

	private function actAsUser(): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
	}

	private function storedOtp(): array
	{
		return go()->getDbConnection()
			->select(['secret', 'expiresAt', 'verified'])
			->from('otp_secret')
			->where('userId', '=', $this->user->id)
			->single();
	}

	private function updateOtp(array $otp): \ArrayAccess
	{
		return (new UserController())->set(['update' => [$this->user->id => ['otp' => $otp]]]);
	}

	public function testUserMayNotExpireAuthenticatorThroughApi(): void
	{
		$this->enableOtp();
		$this->actAsUser();

		$this->updateOtp(['expiresAt' => '2000-01-01T00:00:00']);

		$stored = $this->storedOtp();
		$this->assertNull($stored['expiresAt']);
		$this->assertTrue(\go\modules\community\otp\OtpAuthenticator::isAvailableFor($this->user->username));
	}

	public function testUserMayNotReplaceSecretWithExpiryWithoutPassword(): void
	{
		$this->enableOtp();
		$this->actAsUser();

		$future = (new DateTime())->add(new DateInterval('P1D'))->format('Y-m-d\TH:i:s');
		try {
			$this->updateOtp(['secret' => self::OTHER_SECRET, 'expiresAt' => $future]);
			$this->fail('Changing the secret without the current password should be forbidden');
		} catch (Forbidden) {
		}

		$stored = $this->storedOtp();
		$this->assertSame(self::SECRET, $stored['secret']);
		$this->assertNull($stored['expiresAt']);
	}

	public function testExpiringExistingAuthenticatorNeedsPassword(): void
	{
		$this->enableOtp();
		$this->actAsUser();

		$user = User::findById($this->user->id);
		$user->otp->expireAt(new DateTime('2000-01-01'));

		$this->expectException(Forbidden::class);
		$user->save();
	}

	public function testNewTemporarySecretNeedsNoPassword(): void
	{
		// This is what the LDAP authenticator does on login
		$this->actAsUser();

		$user = User::findById($this->user->id);
		$otp = new OtpAuthenticator($user);
		$otp->setSecret(self::SECRET);
		$otp->expireAt((new DateTime())->add(new DateInterval('PT10M')));
		$user->otp = $otp;
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		$this->assertTrue(User::findById($this->user->id)->otp->getIsEnabled());
		$this->assertNotNull(User::findById($this->user->id)->otp->getExpiresAt());
	}

	/**
	 * Like logging in: what \go\modules\community\otp\OtpAuthenticator::authenticate() checks
	 */
	private function login(string $code): bool
	{
		$token = new \go\core\model\Token();
		$token->userId = $this->user->id;
		$auth = new \go\modules\community\otp\OtpAuthenticator();
		return $auth->authenticate($token, ['otp_code' => $code]);
	}

	public function testCodeCanOnlyBeUsedOnceToLogIn(): void
	{
		$this->enableOtp();
		$otp = User::findById($this->user->id)->otp;
		$timeSlice = (int) floor(time() / 30);

		$this->assertTrue($this->login($otp->getCode(self::SECRET, $timeSlice)));
		// somebody who saw the code
		$this->assertFalse($this->login($otp->getCode(self::SECRET, $timeSlice)));
		// an older code that is still within the allowed time drift
		$this->assertFalse($this->login($otp->getCode(self::SECRET, $timeSlice - 1)));
		// the next code works
		$this->assertTrue($this->login($otp->getCode(self::SECRET, $timeSlice + 1)));
	}

	public function testWrongCodeDoesNotBlockLogin(): void
	{
		$this->enableOtp();
		$otp = User::findById($this->user->id)->otp;

		$this->assertFalse($this->login('000000' === $otp->getCode(self::SECRET) ? '111111' : '000000'));
		$this->assertTrue($this->login($otp->getCode(self::SECRET)));
	}
}
