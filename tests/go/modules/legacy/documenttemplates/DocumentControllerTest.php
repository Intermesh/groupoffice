<?php
namespace go\modules\legacy\documenttemplates;

use GO\Base\Model\Template;
use GO\Documenttemplates\Controller\DocumentController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * Document templates are uploaded by users and extracted on the server. A template with links or entries outside its
 * folder could make the server read or write other files.
 */
class DocumentControllerTest extends TestCase
{
	private $authState;
	private Template $template;
	private string $secret;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		$this->secret = \GO::config()->getTempFolder()->path() . '/' . uniqid('secret') . '.txt';
		file_put_contents($this->secret, 'secret');
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset($this->template)) {
			$folder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'templates/' . $this->template->id . '/');
			$folder->delete();
			$this->template->delete(true);
		}
		unlink($this->secret);
		go()->setAuthState($this->authState);
	}

	/**
	 * A template where content.xml is a symbolic link to a file of the server
	 */
	private function createTemplate(): Template
	{
		$zipFile = \GO::config()->getTempFolder()->path() . '/' . uniqid('template') . '.odt';
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipFile, \ZipArchive::CREATE));
		$zip->addFromString('content.xml', $this->secret);
		$zip->setExternalAttributesName('content.xml', \ZipArchive::OPSYS_UNIX, (0120777 << 16));
		$zip->close();

		$template = new Template();
		$template->name = uniqid('linktemplate');
		$template->type = Template::TYPE_DOCUMENT;
		$template->extension = 'odt';
		$template->content = file_get_contents($zipFile);
		unlink($zipFile);
		$this->assertTrue($template->save(true), var_export($template->getValidationErrors(), true));
		return $template;
	}

	public function testLinksInTemplateAreNotExtracted(): void
	{
		$this->template = $this->createTemplate();

		$method = new \ReflectionMethod(DocumentController::class, 'actionGetLinks');
		try {
			$method->invoke(new DocumentController(), [
				'template_id' => $this->template->id,
				'model_name' => 'Contact',
				'model_id' => 0
			]);
		} catch (\Exception $e) {
			// refused, or it fails later on the contact that doesn't exist
		}

		$contentFile = \GO::config()->tmpdir . 'templates/' . $this->template->id . '/content.xml';
		$this->assertFalse(is_link($contentFile), 'A link of the template was extracted');
		$this->assertFileDoesNotExist($contentFile);
	}
}
