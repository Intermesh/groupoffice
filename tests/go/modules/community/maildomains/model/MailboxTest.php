<?php
namespace go\modules\community\maildomains\model;

use go\core\orm\exception\SaveException;
use go\modules\community\maildomains\Module;
use PHPUnit\Framework\TestCase;

class MailboxTest extends TestCase
{
	private static bool $installedModule = false;
	private static Domain $domain;

	public static function setUpBeforeClass(): void
	{
		try {
			if (!Module::get()->isInstalled(false)) {
				if (!Module::get()->install()) {
					throw new \Exception("Could not install maildomains module");
				}
				self::$installedModule = true;
			}

			self::$domain = new Domain();
			self::$domain->domain = "defaults-" . uniqid() . ".example.com";
			self::$domain->transport = "virtual";
			self::$domain->defaultAutoExpunge = "7d";
			self::$domain->defaultFts = true;
			if (!self::$domain->save()) {
				throw new SaveException(self::$domain);
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (isset(self::$domain) && !self::$domain->isNew()) {
			Mailbox::delete(['domainId' => self::$domain->id]);
			Domain::delete(['id' => self::$domain->id]);
		}

		if (self::$installedModule) {
			Module::get()->uninstall();
		}
	}

	private function createMailbox(array $values): Mailbox
	{
		$mailbox = new Mailbox();
		$mailbox->setValues(array_merge([
			'domainId' => self::$domain->id,
			'username' => "test-" . uniqid() . "@" . self::$domain->domain,
			'password' => "Secret123!Secret",
			'smtpAllowed' => false,
		], $values));

		if (!$mailbox->save()) {
			throw new SaveException($mailbox);
		}

		return Mailbox::findById($mailbox->id);
	}

	public function testNewMailboxGetsDomainDefaults(): void
	{
		$mailbox = $this->createMailbox([]);

		$this->assertSame("7d", $mailbox->autoExpunge);
		$this->assertTrue($mailbox->fts);
	}

	public function testDomainDefaultsFoundByUsername(): void
	{
		$mailbox = new Mailbox();
		$mailbox->setValues([
			'username' => "test-" . uniqid() . "@" . self::$domain->domain,
			'password' => "Secret123!Secret",
			'smtpAllowed' => false,
		]);

		$this->assertSame("7d", $mailbox->autoExpunge);
		$this->assertTrue($mailbox->fts);
	}

	public function testExplicitValuesOverrideDomainDefaults(): void
	{
		// these equal the column defaults, so they must not be mistaken for "not set"
		$mailbox = $this->createMailbox(['fts' => false, 'autoExpunge' => "30d"]);

		$this->assertSame("30d", $mailbox->autoExpunge);
		$this->assertFalse($mailbox->fts);
	}
}
