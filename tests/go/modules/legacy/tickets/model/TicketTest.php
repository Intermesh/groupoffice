<?php
namespace go\modules\legacy\tickets\model;

use GO\Base\Exception\NotFound;
use GO\Tickets\Controller\ExternalpageController;
use GO\Tickets\Model\Ticket;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * The public ticket pages find a ticket by its number and verifier from the link. The verifier is the secret of that
 * link.
 */
class TicketTest extends TestCase
{
	use ModuleTablesTrait;

	private const NUMBER = 'T0123456789';
	private const VERIFIER = 12345678;

	private bool $disableModelCache;
	private int $ticketId;

	protected function setUp(): void
	{
		// The tables are created for every test, so tickets get the same id
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;
		$this->createTables(go()->getEnvironment()->getInstallPath() . '/promodules/tickets/install/install.sql', ['ti_tickets', 'ti_settings']);
		// Added by updates after install.sql
		foreach (['last_agent_response_time', 'last_contact_response_time'] as $column) {
			if (!go()->getDbConnection()->query("SHOW COLUMNS FROM `ti_tickets` LIKE '" . $column . "'")->fetch()) {
				go()->getDbConnection()->exec('ALTER TABLE `ti_tickets` ADD `' . $column . '` INT NOT NULL DEFAULT 0');
			}
		}

		go()->getDbConnection()->insert('ti_tickets', [
			'ticket_number' => self::NUMBER,
			'ticket_verifier' => self::VERIFIER,
			'subject' => 'Verifier test'
		])->execute();
		$this->ticketId = (int) go()->getDbConnection()->getPDO()->lastInsertId();

		// A ticket of the same number without a verifier, like old or imported tickets may have
		go()->getDbConnection()->insert('ti_tickets', [
			'ticket_number' => 'T0',
			'ticket_verifier' => 0,
			'subject' => 'Verifier test without verifier'
		])->execute();
	}

	protected function tearDown(): void
	{
		go()->getDbConnection()->delete('ti_settings', ['from_name' => 'Verifier test'])->execute();
		go()->getDbConnection()->delete('ti_tickets', ['ticket_number' => [self::NUMBER, 'T0']])->execute();
		$this->uninstallModuleTables();
		\GO::$disableModelCache = $this->disableModelCache;
	}

	public function testTicketIsFoundWithItsVerifier(): void
	{
		$ticket = Ticket::findByNumberAndVerifier(self::NUMBER, (string) self::VERIFIER);
		$this->assertNotNull($ticket);
		$this->assertEquals($this->ticketId, $ticket->id);
	}

	public static function wrongVerifiers(): array
	{
		return [
			'list of verifiers' => [self::NUMBER, [self::VERIFIER, 1]],
			'list of numbers' => [[self::NUMBER], (string) self::VERIFIER],
			'other verifier' => [self::NUMBER, '12345679'],
			'verifier with text' => [self::NUMBER, self::VERIFIER . 'abc'],
			'empty verifier' => [self::NUMBER, ''],
			'no verifier' => [self::NUMBER, null],
			'ticket without verifier' => ['T0', '0'],
		];
	}

	#[DataProvider('wrongVerifiers')]
	public function testWrongVerifierIsRefused($number, $verifier): void
	{
		$this->assertNull(Ticket::findByNumberAndVerifier($number, $verifier));
	}

	private function setExternalPage(bool $enabled): void
	{
		go()->getDbConnection()->delete('ti_settings', ['from_name' => 'Verifier test'])->execute();
		if (go()->getDbConnection()->selectSingleValue('id')->from('ti_settings')->single()) {
			$this->markTestSkipped('The ticket settings exist');
		}
		go()->getDbConnection()->insert('ti_settings', [
			'from_email' => 'test@example.com',
			'from_name' => 'Verifier test',
			'subject' => '',
			'logo' => '',
			'customer_message' => '',
			'response_message' => '',
			'enable_external_page' => $enabled,
			'allow_anonymous' => 1
		])->execute();
	}

	private function callExternalPage(string $action, $number, $verifier): void
	{
		$method = new \ReflectionMethod(ExternalpageController::class, $action);
		ob_start();
		try {
			$method->invoke(new ExternalpageController(), $number, $verifier);
		} finally {
			ob_end_clean();
		}
	}

	public function testExternalPageRefusesListOfVerifiers(): void
	{
		$this->setExternalPage(true);
		$this->expectException(NotFound::class);
		$this->callExternalPage('actionTicket', self::NUMBER, [self::VERIFIER, 1]);
	}

	public function testTicketCreatedPageNeedsExternalPage(): void
	{
		$this->setExternalPage(false);
		$this->expectException(NotFound::class);
		$this->callExternalPage('actionTicketCreated', self::NUMBER, (string) self::VERIFIER);
	}
}
