<?php
namespace go\modules\business\support;

use go\core\auth\TemporaryState;
use go\core\mail\Mailer;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use go\modules\business\support\model\SupportList;
use go\modules\business\support\model\SupportTicket;
use go\modules\community\comments\controller\Comment as CommentController;
use go\modules\community\comments\model\Comment;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../fixtures/ModuleTablesTrait.php';

/**
 * Replies of agents on a support ticket are e-mailed to the customer. Private notes and drafts are not, and the
 * customer can't read them. When sending is disabled, like for an AI assistant, a reply is refused so it can be saved
 * as a draft instead.
 */
class ModuleTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private static User $customer;
	private static User $agent;
	private static SupportList $list;
	private static SupportTicket $ticket;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::installModuleTables('business', 'support', go()->getEnvironment()->getInstallPath() . '/go/modules/business/support/install/install.sql');

			self::$customer = self::createUser('supportcustomer');
			self::$agent = self::createUser('supportagent');

			self::$list = new SupportList();
			self::$list->name = uniqid('support');
			self::$list->setAcl([
				Group::ID_INTERNAL => null,
				Group::ID_EVERYONE => null,
				Group::ID_ADMINS => Acl::LEVEL_MANAGE,
				self::$customer->getPersonalGroup()->id => SupportList::ACL_LEVEL_SUBMIT_AS_CUSTOMER,
				self::$agent->getPersonalGroup()->id => Acl::LEVEL_WRITE
			]);
			self::assertTrue(self::$list->save(), self::$list->getValidationErrorsAsString());

			self::$ticket = new SupportTicket();
			self::$ticket->tasklistId = self::$list->id;
			self::$ticket->title = 'Ticket of the customer';
			self::$ticket->customerId = (string) self::$customer->id;
			self::assertTrue(self::$ticket->save(), self::$ticket->getValidationErrorsAsString());
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$ticket->id)) {
			Comment::delete(['entityId' => self::$ticket->id, 'entityTypeId' => SupportTicket::entityType()->getId()]);
			SupportTicket::delete(['id' => self::$ticket->id]);
		}
		if (isset(self::$list->id)) {
			SupportList::delete(['id' => self::$list->id]);
		}
		$ids = [];
		foreach (['customer', 'agent'] as $prop) {
			if (isset(self::$$prop)) {
				$ids[] = self::$$prop->id;
			}
		}
		if ($ids) {
			User::delete(['id' => $ids]);
		}
		self::uninstallModuleTables();
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		Mailer::$sendingDisabled = null;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		Comment::delete(['entityId' => self::$ticket->id, 'entityTypeId' => SupportTicket::entityType()->getId()]);
		go()->setAuthState($this->authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	/**
	 * Create comments as the agent through JMAP
	 *
	 * @return \ArrayObject The Comment/set response
	 */
	private function createAsAgent(array $comments): \ArrayObject
	{
		go()->setAuthState(new TemporaryState(self::$agent->id));
		$create = [];
		foreach ($comments as $key => $values) {
			$create[$key] = ['entity' => 'SupportTicket', 'entityId' => self::$ticket->id, 'text' => $key] + $values;
		}
		return (new CommentController())->set(['create' => $create]);
	}

	public function testCustomerCannotReadDraftsAndPrivateNotes(): void
	{
		$result = $this->createAsAgent([
			'draft' => ['section' => Comment::SECTION_DRAFT],
			'private' => ['section' => 'private']
		]);
		$this->assertCount(2, $result['created'], json_encode($result));

		go()->setAuthState(new TemporaryState(self::$customer->id));
		$ids = array_column($result['created'], 'id');
		$get = (new CommentController())->get(['ids' => $ids]);

		$this->assertEmpty($get['list']);
		$this->assertEqualsCanonicalizing($ids, $get['notFound']);
	}

	public function testDraftIsNotSent(): void
	{
		$result = $this->createAsAgent(['draft' => ['section' => Comment::SECTION_DRAFT]]);
		$this->assertArrayHasKey('draft', $result['created'], json_encode($result));

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$draft = Comment::findById($result['created']['draft']['id']);
		$this->assertNull($draft->mimeMessageId);
		$this->assertNull($draft->mailStatus);
	}

	/**
	 * Without this guard a reply of an AI assistant is saved and shown to the customer, but marked as sent while it
	 * was never e-mailed.
	 */
	public function testReplyIsRefusedWhenSendingIsDisabled(): void
	{
		Mailer::$sendingDisabled = 'Sending is disabled in this test.';

		$result = $this->createAsAgent([
			'reply' => [],
			'draft' => ['section' => Comment::SECTION_DRAFT]
		]);

		$this->assertArrayHasKey('reply', $result['notCreated'], json_encode($result));
		$this->assertArrayHasKey('section', $result['notCreated']['reply']['validationErrors'], json_encode($result));
		$this->assertStringContainsString('"draft"', $result['notCreated']['reply']['validationErrors']['section']['description']);
		$this->assertArrayHasKey('draft', $result['created'], json_encode($result));

		// Posting the draft would send it, so that is refused too
		$id = $result['created']['draft']['id'];
		$update = (new CommentController())->set(['update' => [$id => ['section' => null]]]);
		$this->assertArrayHasKey($id, $update['notUpdated'], json_encode($update));

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertEquals(Comment::SECTION_DRAFT, Comment::findById($id)->section);
	}

	public function testCustomerReplyIsNotRefused(): void
	{
		Mailer::$sendingDisabled = 'Sending is disabled in this test.';

		go()->setAuthState(new TemporaryState(self::$customer->id));
		$result = (new CommentController())->set(['create' => [
			'reply' => ['entity' => 'SupportTicket', 'entityId' => self::$ticket->id, 'text' => 'Question of the customer']
		]]);

		$this->assertArrayHasKey('reply', $result['created'], json_encode($result));
	}

	/**
	 * The customer must not be able to find out the text of private notes and drafts with the "comment" filter
	 */
	public function testCustomerCannotSearchPrivateNotes(): void
	{
		$secret = uniqid('secret');
		$result = $this->createAsAgent([
			$secret . '-private' => ['section' => 'private'],
			$secret . '-draft' => ['section' => Comment::SECTION_DRAFT]
		]);
		$this->assertCount(2, $result['created'], json_encode($result));

		// The filters of the ticket list in the customer portal, and of the agents
		$query = function (int $userId, array $filter): array {
			go()->setAuthState(new TemporaryState($userId));
			return SupportTicket::find(['id'])
				->filter(['tasklistId' => 'any'] + $filter)
				->selectSingleValue('task.id')
				->all();
		};
		$customer = ['customerId' => '{{me}}', 'permissionLevel' => SupportList::ACL_LEVEL_SUBMIT_AS_CUSTOMER];
		$agent = ['permissionLevel' => Acl::LEVEL_READ];
		$search = ['comment' => '%' . $secret . '%'];

		$this->assertEquals([self::$ticket->id], $query(self::$customer->id, $customer), 'The customer finds the ticket');
		$this->assertEquals([], $query(self::$customer->id, $customer + $search));
		$this->assertEquals([self::$ticket->id], $query(self::$agent->id, $agent + $search));
	}
}
