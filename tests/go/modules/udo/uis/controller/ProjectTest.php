<?php
namespace go\modules\udo\uis\controller;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use go\core\jmap\exception\InvalidArguments;
use go\modules\legacy\projects2\ProjectsTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../legacy/projects2/fixtures/ProjectsTrait.php';

/**
 * A file from the website is stored in the folder of a project. The user needs write permission on that folder and the
 * file name may not contain a path.
 */
class ProjectTest extends TestCase
{
	use ProjectsTrait;

	protected function setUp(): void
	{
		if (!class_exists(Project::class)) {
			$this->markTestSkipped('The uis module is required');
		}
		$this->setUpProjects();
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->tearDownProjects();
	}

	private function loadFile(int $projectId, string $fileName): void
	{
		$this->actAs($this->user->id);
		$controller = (new \ReflectionClass(Project::class))->newInstanceWithoutConstructor();
		$controller->loadFile(['create' => [[
			'projectId' => $projectId,
			'file' => base64_encode('test'),
			'folder' => '',
			'fileName' => $fileName
		]]]);
	}

	public function testReadOnlyProject(): void
	{
		$project = $this->createProject($this->createType(Acl::READ_PERMISSION));

		$this->expectException(AccessDenied::class);
		$this->loadFile($project->id, 'test.txt');
	}

	public function testFileNameWithPath(): void
	{
		$project = $this->createProject($this->createType(Acl::WRITE_PERMISSION));

		$this->expectException(InvalidArguments::class);
		$this->loadFile($project->id, '..');
	}
}
