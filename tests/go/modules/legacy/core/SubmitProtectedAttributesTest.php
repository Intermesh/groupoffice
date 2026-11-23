<?php

namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use GO\Base\Controller\AbstractModelController;
use GO\Base\Model\Template;
use PHPUnit\Framework\TestCase;

class SubmitProtectedAttributesTestController extends AbstractModelController
{
	protected $model = Template::class;
}

/**
 * The legacy submit action sets all posted attributes. A user who may change an item with its own ACL may not change
 * its owner (user_id) or ACL (acl_id). Only admins may do that.
 */
class SubmitProtectedAttributesTest extends TestCase
{
	private $authState;
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private Template $template;
	private Acl $otherAcl;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('subowner');
		$this->other = $this->createUser('subother');

		$this->actAs(User::ID_SUPER_ADMIN);
		$this->template = new Template();
		$this->template->setAttributes([
			'user_id' => $this->owner->id,
			'name' => 'Shared template',
			'type' => 0,
			'content' => 'Subject: Template'
		], false);
		$this->assertTrue($this->template->save(true), var_export($this->template->getValidationErrors(), true));

		$acl = Acl::findById($this->template->acl_id);
		$acl->addGroup($this->other->getPersonalGroup()->id, Acl::LEVEL_WRITE);
		$this->assertTrue($acl->save());

		// an ACL of the other user, for example of one of their own items
		$this->otherAcl = new Acl();
		$this->otherAcl->usedIn = 'go_templates.acl_id';
		$this->otherAcl->ownedBy = $this->other->id;
		$this->otherAcl->addGroup($this->other->getPersonalGroup()->id, Acl::LEVEL_MANAGE);
		$this->assertTrue($this->otherAcl->save());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$template = Template::model()->findByPk($this->template->id, false, true);
		if($template) {
			// restore the ACL so the template's own ACL is deleted with it
			$template->acl_id = $this->template->acl_id;
			$template->delete(true);
		}
		Acl::delete(['id' => [$this->otherAcl->id, $this->template->acl_id]]);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);
		go()->setAuthState($this->authState);
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function submit(array $params): array
	{
		$controller = new SubmitProtectedAttributesTestController();
		$method = new \ReflectionMethod($controller, 'actionSubmit');
		$method->setAccessible(true);
		return $method->invoke($controller, $params);
	}

	private function reload(): Template
	{
		return Template::model()->findByPk($this->template->id, false, true);
	}

	public function testUserCannotChangeOwnerOrAcl(): void
	{
		$this->actAs($this->other->id);

		$response = $this->submit([
			'id' => $this->template->id,
			'name' => 'Renamed',
			'user_id' => $this->other->id,
			'acl_id' => $this->otherAcl->id
		]);
		$this->assertTrue($response['success']);

		$template = $this->reload();
		$this->assertSame('Renamed', $template->name);
		$this->assertEquals($this->owner->id, $template->user_id);
		$this->assertEquals($this->template->acl_id, $template->acl_id);
	}

	public function testUserCannotChangeOwnerOrAclWithSubmitMultiple(): void
	{
		$this->actAs($this->other->id);

		$controller = new SubmitProtectedAttributesTestController();
		$method = new \ReflectionMethod($controller, 'actionSubmitMultiple');
		$method->setAccessible(true);
		$method->invoke($controller, ['records' => json_encode([[
			'id' => $this->template->id,
			'user_id' => $this->other->id,
			'acl_id' => $this->otherAcl->id
		]])]);

		$template = $this->reload();
		$this->assertEquals($this->owner->id, $template->user_id);
		$this->assertEquals($this->template->acl_id, $template->acl_id);
	}

	public function testAdminCanChangeOwner(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);

		$response = $this->submit([
			'id' => $this->template->id,
			'user_id' => $this->other->id
		]);
		$this->assertTrue($response['success']);

		$this->assertEquals($this->other->id, $this->reload()->user_id);
	}
}
