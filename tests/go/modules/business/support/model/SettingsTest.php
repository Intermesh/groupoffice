<?php
namespace go\modules\business\support\model;

use go\core\auth\TemporaryState;
use go\core\model\EmailTemplate;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The e-mail template of the support settings is only written when the settings are saved, so a settings update that
 * is refused doesn't change it.
 */
class SettingsTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private ?EmailTemplate $template = null;
	private ?string $subject = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->installModuleTables('business', 'support', go()->getEnvironment()->getInstallPath() . '/go/modules/business/support/install/install.sql');
		$this->resetSettings();

		$this->template = Settings::get()->getEmailTemplate();
		$this->subject = $this->template->subject;
	}

	protected function tearDown(): void
	{
		if (isset($this->template)) {
			$template = EmailTemplate::findById($this->template->id);
			if ($template) {
				$template->subject = $this->subject;
				$template->save();
			}
		}
		$this->resetSettings();
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	/**
	 * Settings::get() keeps the settings in a static property
	 */
	private function resetSettings(): void
	{
		$prop = new \ReflectionProperty(\go\core\SettingsEntity::class, 'instance');
		$instances = $prop->getValue();
		unset($instances[Settings::class]);
		$prop->setValue(null, $instances);
	}

	private function storedSubject(): string
	{
		return go()->getDbConnection()->selectSingleValue('subject')->from('core_email_template')
			->where(['id' => $this->template->id])->single();
	}

	public function testEmailTemplateIsOnlyWrittenOnSave(): void
	{
		$subject = uniqid('subject');
		$settings = Settings::get();
		$settings->setEmailTemplate(['subject' => $subject]);

		$this->assertSame($this->subject, $this->storedSubject());

		$this->assertTrue($settings->save(), $settings->getValidationErrorsAsString());
		$this->assertSame($subject, $this->storedSubject());
	}
}
