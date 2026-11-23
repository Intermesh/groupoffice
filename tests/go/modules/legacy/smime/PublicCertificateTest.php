<?php
namespace go\modules\legacy\smime;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Smime\Controller\PublicCertificateController;
use GO\Smime\Model\PublicCertificate;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Public certificates belong to a user, and the URI's in a certificate may only point to public addresses because
 * anyone can send a certificate.
 */
class PublicCertificateTest extends TestCase
{
	private static bool $installedModule = false;
	private static $server;
	private static int $port;
	private static string $docRoot;

	private $authState;
	private User $user;
	private int $adminCertId;

	public static function setUpBeforeClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		if (!\GO::modules()->isInstalled('smime')) {
			\GO\Base\Model\Module::install('smime');
			self::$installedModule = true;
		}

		// Records every request, so the test can see if the internal address was requested
		self::$docRoot = sys_get_temp_dir() . '/go-smime-test-' . uniqid();
		mkdir(self::$docRoot);
		file_put_contents(self::$docRoot . '/router.php', <<<'PHP'
<?php
file_put_contents(__DIR__ . '/requests.log', $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
echo 'internal';
PHP
		);

		$socket = stream_socket_server('tcp://127.0.0.1:0');
		self::$port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
		fclose($socket);

		self::$server = proc_open(
			[PHP_BINARY, '-S', '127.0.0.1:' . self::$port, self::$docRoot . '/router.php'],
			[['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
			$pipes
		);

		for ($i = 0; $i < 50; $i++) {
			$c = @fsockopen('127.0.0.1', self::$port);
			if ($c) {
				fclose($c);
				return;
			}
			usleep(100000);
		}
		self::fail('Test HTTP server did not start');
	}

	public static function tearDownAfterClass(): void
	{
		proc_terminate(self::$server);
		proc_close(self::$server);
		@unlink(self::$docRoot . '/requests.log');
		unlink(self::$docRoot . '/router.php');
		rmdir(self::$docRoot);

		if (self::$installedModule) {
			go()->getDbConnection()->delete('core_module', ['name' => 'smime', 'package' => null])->execute();
			go()->getDbConnection()->exec('DROP TABLE IF EXISTS smi_account_settings, smi_pkcs12, smi_certs');
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('smime');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->adminCertId = $this->insertCert(User::ID_SUPER_ADMIN, $this->createCertificate('https://www.example.com'));
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('smi_certs', ['user_id' => [User::ID_SUPER_ADMIN, $this->user->id]])->execute();
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function insertCert(int $userId, string $cert): int
	{
		go()->getDbConnection()->insert('smi_certs', [
			'user_id' => $userId,
			'email' => 'cert@example.com',
			'cert' => $cert
		])->execute();
		return (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	/**
	 * Create a self signed certificate with the OCSP and issuer URI's on the given base URL
	 */
	private function createCertificate(string $baseUrl): string
	{
		$config = tempnam(sys_get_temp_dir(), 'smime-cnf');
		file_put_contents($config, "[req]\ndistinguished_name = dn\n[dn]\n[aia]\nauthorityInfoAccess = OCSP;URI:" . $baseUrl . "/ocsp,caIssuers;URI:" . $baseUrl . "/issuer.der\n");
		try {
			$args = ['config' => $config, 'x509_extensions' => 'aia', 'digest_alg' => 'sha256'];
			$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
			$csr = openssl_csr_new(['commonName' => 'Test', 'emailAddress' => 'cert@example.com'], $key, $args);
			$x509 = openssl_csr_sign($csr, null, $key, 1, $args);
			openssl_x509_export($x509, $pem);
		} finally {
			unlink($config);
		}
		return $pem;
	}

	public function testUserCannotReadCertificateOfOthers()
	{
		$this->actAs($this->user->id);

		try {
			PublicCertificate::model()->findByPk($this->adminCertId);
			$this->fail('A user read the certificate of another user');
		} catch (AccessDenied $e) {
		}

		$this->expectException(AccessDenied::class);
		$method = new ReflectionMethod(PublicCertificateController::class, 'actionVerify');
		$method->invoke(new PublicCertificateController(), ['email' => 'cert@example.com', 'cert_id' => $this->adminCertId]);
	}

	public function testUserCannotChangeOrDeleteCertificateOfOthers()
	{
		$this->actAs($this->user->id);

		$cert = PublicCertificate::model()->findByPk($this->adminCertId, false, true);
		$cert->email = 'taken@example.com';
		try {
			$cert->save();
			$this->fail('A user changed the certificate of another user');
		} catch (AccessDenied $e) {
		}

		try {
			$cert->delete();
			$this->fail('A user deleted the certificate of another user');
		} catch (AccessDenied $e) {
		}

		$email = go()->getDbConnection()->selectSingleValue('email')->from('smi_certs')->where('id', '=', $this->adminCertId)->single();
		$this->assertSame('cert@example.com', $email);
	}

	public function testUserCannotAddCertificateForOthers()
	{
		$this->actAs($this->user->id);

		$cert = new PublicCertificate();
		$cert->user_id = User::ID_SUPER_ADMIN;
		$cert->email = 'planted@example.com';
		$cert->cert = $this->createCertificate('https://www.example.com');

		$this->expectException(AccessDenied::class);
		$cert->save();
	}

	public function testOwnerCanReadOwnCertificate()
	{
		$cert = PublicCertificate::model()->findByPk($this->adminCertId);
		$this->assertSame('cert@example.com', $cert->email);
	}

	public function testUrisOfCertificateAreNotRequestedOnInternalAddress()
	{
		$this->actAs($this->user->id);
		$id = $this->insertCert($this->user->id, $this->createCertificate('http://127.0.0.1:' . self::$port));

		$result = PublicCertificate::model()->findByPk($id)->parse();

		$this->assertFalse($result['ocsp']);
		$this->assertFileDoesNotExist(self::$docRoot . '/requests.log', 'The certificate made the server request an internal address');
	}
}
