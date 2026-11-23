<?php
namespace go\modules\legacy\postfixadmin;

use GO\Postfixadmin\Controller\DomainController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The CLI action correctMaildirPaths runs as root and puts the domain and username of each mailbox in paths of shell
 * commands. They must stay one folder name below the mail root.
 */
class DomainControllerTest extends TestCase
{
	private function home(string $domain, string $username): string
	{
		return (new \ReflectionMethod(DomainController::class, 'mailboxHome'))->invoke(null, '/var/mail/vhosts/', $domain, $username);
	}

	public function testHome(): void
	{
		$this->assertEquals('/var/mail/vhosts/example.com/john/', $this->home('example.com', 'john@example.com'));
	}

	public static function invalid(): array
	{
		return [
			'parent username' => ['example.com', '..@example.com'],
			'slash in username' => ['example.com', '../../../etc@example.com'],
			'backslash' => ['example.com', 'a\\b@example.com'],
			'nul' => ['example.com', "a\0@example.com"],
			'parent domain' => ['..', 'john@example.com'],
			'slash in domain' => ['example.com/../..', 'john@example.com'],
			'empty' => ['example.com', '@example.com'],
		];
	}

	#[DataProvider('invalid')]
	public function testInvalidPathIsRefused(string $domain, string $username): void
	{
		$this->expectExceptionMessage('Invalid characters');
		$this->home($domain, $username);
	}

	public function testShellCharactersAreEscaped(): void
	{
		$source = file_get_contents((new \ReflectionClass(DomainController::class))->getFileName());
		$start = strpos($source, 'function actionCorrectMaildirPaths');
		$action = substr($source, $start, strpos($source, 'function mailboxHome') - $start);

		// every command with a path escapes it and the Dovecot files are moved without a shell
		$this->assertEquals(0, preg_match('/exec\\((?!.*escapeshellarg)[^;]*\\$(home|maildir|tmp)/', $action), "A path is passed to the shell without escaping");
		$this->assertStringNotContainsString("exec('mv -f", $action);
	}
}
