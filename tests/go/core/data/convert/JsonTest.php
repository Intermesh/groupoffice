<?php

namespace go\core\data\convert;

use go\core\auth\TemporaryState;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use PHPUnit\Framework\TestCase;

/**
 * Export and import of entities as JSON. Uses address books because they own an ACL.
 */
class JsonTest extends TestCase
{
	private $authState;
	private string $prefix;
	private Group $group;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->prefix = uniqid('jsontest');

		$this->group = new Group();
		$this->group->name = $this->prefix;
		$this->assertTrue($this->group->save(), $this->group->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$ids = AddressBook::find()
			->selectSingleValue('id')
			->where('name', 'LIKE', $this->prefix . '%')
			->all();
		if (!empty($ids)) {
			AddressBook::delete(['id' => $ids]);
		}
		Group::delete(['id' => $this->group->id]);

		go()->setAuthState($this->authState);
	}

	private function addressBook(string $name): AddressBook
	{
		$ab = new AddressBook();
		$ab->name = $this->prefix . $name;
		$ab->setAcl([$this->group->id => Acl::LEVEL_WRITE]);
		$this->assertTrue($ab->save(), $ab->getValidationErrorsAsString());
		return $ab;
	}

	private function export(array $ids): string
	{
		$blob = AddressBook::findConverter('json')->exportToBlob($ids, [], false);
		$this->assertNotNull($blob);
		return $blob->getFile()->getContents();
	}

	private function import(string $json): array
	{
		$file = File::tempFile('json');
		$file->putContents($json);
		return AddressBook::findConverter('json')->importFile($file);
	}

	public function testExportIsValidJson(): void
	{
		$a = $this->addressBook('a');
		$b = $this->addressBook('b');

		$data = json_decode($this->export([$a->id, $b->id]), true);
		$this->assertIsArray($data, "Export is not valid JSON");
		$this->assertSame([$a->name, $b->name], array_column($data, 'name'));
	}

	public function testExportOfOneEntityIsValidJson(): void
	{
		$a = $this->addressBook('a');

		$data = json_decode($this->export([$a->id]), true);
		$this->assertIsArray($data, "Export is not valid JSON");
		$this->assertSame([$a->name], array_column($data, 'name'));
	}

	public function testEntityThatFailsIsLeftOut(): void
	{
		$a = $this->addressBook('a');

		// The id doesn't exist, so it fails
		$data = json_decode($this->export([$a->id, '999999999']), true);
		$this->assertIsArray($data, "Export is not valid JSON");
		$this->assertSame([$a->name], array_column($data, 'name'));
	}

	/**
	 * The file may come from another installation, so the id and ACL groups of the file are not used.
	 */
	public function testImportDoesNotCopyIdOrAcl(): void
	{
		$a = $this->addressBook('a');
		$data = json_decode($this->export([$a->id]), true);
		$this->assertArrayHasKey($this->group->id, $data[0]['acl']);
		$data[0]['name'] = $this->prefix . 'imported';

		$result = $this->import(json_encode($data));
		$this->assertTrue($result['success']);
		$this->assertSame(1, $result['count'], implode("\n", $result['errors']));

		$imported = AddressBook::find()->where(['name' => $this->prefix . 'imported'])->single();
		$this->assertNotEquals($a->id, $imported->id);
		$this->assertNotEquals($a->findAclId(), $imported->findAclId());
		$this->assertArrayNotHasKey($this->group->id, $imported->getAcl()->getArray());
		$this->assertEquals(User::ID_SUPER_ADMIN, Acl::findById($imported->findAclId())->ownedBy);
	}

	public function testItemWithInvalidDataIsReported(): void
	{
		// groups must be an array, setting a string throws a TypeError, which is an Error and not an Exception
		$result = $this->import(json_encode([
			['name' => $this->prefix . 'invalid', 'groups' => 'invalid'],
			['name' => $this->prefix . 'valid'],
		]));

		$this->assertTrue($result['success']);
		$this->assertSame(1, $result['count']);
		$this->assertCount(1, $result['errors']);
		$this->assertStringStartsWith('Item 0:', $result['errors'][0]);
		$this->assertStringContainsString('of type array', $result['errors'][0]);
		$this->assertNotNull(AddressBook::find()->where(['name' => $this->prefix . 'valid'])->single());
	}

	public function testItemThatIsNotAnObjectIsReported(): void
	{
		$result = $this->import(json_encode([
			'not an object',
			['name' => $this->prefix . 'after'],
		]));

		$this->assertSame(1, $result['count']);
		$this->assertCount(1, $result['errors']);
		$this->assertStringStartsWith('Item 0:', $result['errors'][0]);
		$this->assertNotNull(AddressBook::find()->where(['name' => $this->prefix . 'after'])->single());
	}

	public function testInvalidFileFails(): void
	{
		$result = $this->import(json_encode(['name' => $this->prefix . 'not a list']));

		$this->assertFalse($result['success']);
		$this->assertNotEmpty($result['errors']);
		$this->assertSame(0, $result['count']);
	}
}
