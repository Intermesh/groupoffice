<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\exception\NotFound;
use go\core\fs\Blob;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * Importing field sets from an unknown file or for an unknown entity gives a clean error
 */
class FieldSetTest extends TestCase
{
	private $authState;
	private ?Blob $blob = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	public function testImportUnknownFile(): void
	{
		$this->expectException(NotFound::class);
		(new FieldSet())->importFromJson(['blobId' => str_repeat('0', 40), 'entity' => 'Contact']);
	}

	public function testImportForUnknownEntity(): void
	{
		$blob = Blob::fromString(json_encode(['entity' => 'Contact', 'fieldSets' => []]));
		$blob->name = 'fieldsets.json';
		$blob->type = 'application/json';
		$this->assertTrue($blob->save(), $blob->getValidationErrorsAsString());

		$this->expectException(InvalidArguments::class);
		(new FieldSet())->importFromJson(['blobId' => $blob->id, 'entity' => 'NoSuchEntity']);
	}
}
