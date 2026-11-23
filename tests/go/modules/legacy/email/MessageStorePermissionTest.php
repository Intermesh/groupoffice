<?php

namespace go\modules\legacy\email;

use GO\Email\Controller\MessageController;
use GO\Email\Model\Account;
use GO\Email\Model\Filter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/SharedAccountTrait.php';

/**
 * Listing the mail of an account runs the filters of the account, which move mail, and expunges the mailbox. Users who
 * may only read a shared account may not change its mail, so only users with "Use account" do that.
 */
class MessageStorePermissionTest extends TestCase
{
	use SharedAccountTrait;

	private $process;
	private $pipes;
	private string $logFile;

	private function startServer(): void
	{
		$this->logFile = tempnam(sys_get_temp_dir(), 'fakeimap');
		$this->process = proc_open(
			[PHP_BINARY, __DIR__ . '/fixtures/fake-imap-server.php', $this->logFile],
			[0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
			$this->pipes
		);
		$port = (int) fgets($this->pipes[1]);
		$this->assertGreaterThan(0, $port, 'The fake IMAP server did not start');

		$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
		$account = Account::model()->findByPk($this->account->id, false, true);
		$account->port = $port;
		$account->checkImapConnectionOnSave = false;
		$this->assertTrue($account->save(true));

		$filter = new Filter();
		$filter->account_id = $this->account->id;
		$filter->field = 'subject';
		$filter->keyword = 'spam';
		$filter->folder = 'Junk';
		$this->assertTrue($filter->save(true));
	}

	protected function tearDown(): void
	{
		if (isset($this->process)) {
			fclose($this->pipes[0]);
			proc_terminate($this->process);
			proc_close($this->process);
			unlink($this->logFile);
		}
		$this->tearDownSharedAccount();
	}

	private function store(int $userId): string
	{
		$this->actAs($userId);
		try {
			$this->invoke(new MessageController(), 'actionStore', [['account_id' => $this->account->id, 'mailbox' => 'INBOX']]);
		} catch (\Throwable $e) {
			// the fake server doesn't return real mail
		}
		return file_get_contents($this->logFile);
	}

	public static function readLevels(): array
	{
		return [
			'read only' => [\GO\Base\Model\Acl::READ_PERMISSION],
			'delegated' => [Account::ACL_DELEGATED_PERMISSION],
		];
	}

	#[DataProvider('readLevels')]
	public function testReaderDoesNotRunFiltersOrExpunge(int $level): void
	{
		$this->setUpSharedAccount($level);
		$this->startServer();

		$commands = $this->store($this->other->id);

		$this->assertStringContainsString('SELECT "INBOX"', $commands, "The mailbox was not opened");
		$this->assertStringNotContainsString('EXPUNGE', $commands);
		// the filters search the unseen mail by arrival
		$this->assertStringNotContainsString('UID SORT (ARRIVAL) US-ASCII UNSEEN', $commands, "The filters were run");
	}

	public function testUserOfAccountRunsFiltersAndExpunge(): void
	{
		$this->setUpSharedAccount(\GO\Base\Model\Acl::CREATE_PERMISSION);
		$this->startServer();

		$commands = $this->store($this->other->id);

		$this->assertStringContainsString('EXPUNGE', $commands);
		$this->assertStringContainsString('UID SORT (ARRIVAL) US-ASCII UNSEEN', $commands, "The filters were not run");
	}
}
