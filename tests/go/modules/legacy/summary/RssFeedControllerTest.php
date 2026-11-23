<?php
namespace go\modules\legacy\summary;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Summary\Controller\RssFeedController;
use GO\Summary\Model\RssFeed;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * RSS feeds belong to a user. Users may only change and delete their own feeds and only managers of the module may add
 * a feed for all users.
 */
class RssFeedControllerTest extends TestCase
{
	private $authState;
	private User $user;
	private int $adminFeedId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('rss');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		// inserted directly because saving a feed downloads it
		go()->getDbConnection()->insert('su_rss_feeds', [
			'user_id' => User::ID_SUPER_ADMIN,
			'title' => 'Feed of the admin',
			'url' => 'https://www.example.com/admin.rss',
			'summary' => 0
		])->execute();
		$this->adminFeedId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('su_rss_feeds', ['user_id' => [User::ID_SUPER_ADMIN, $this->user->id]])->execute();
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function saveFeeds(array $feeds): void
	{
		$method = new ReflectionMethod(RssFeedController::class, 'actionSaveFeeds');
		$method->invoke(new RssFeedController(), ['feeds' => json_encode($feeds)]);
	}

	private function adminFeed(): array
	{
		return go()->getDbConnection()->selectSingleValue('*')->from('su_rss_feeds')->where('id', '=', $this->adminFeedId)
			->fetchMode(\PDO::FETCH_ASSOC)->single();
	}

	private function countFeeds(): int
	{
		return (int) go()->getDbConnection()->selectSingleValue('count(*)')->from('su_rss_feeds')->single();
	}

	public function testUserCannotAddFeedForAllUsers()
	{
		$this->actAs($this->user->id);
		$count = $this->countFeeds();

		try {
			$this->saveFeeds([['title' => '<img src=x onerror=alert(1)>', 'url' => 'https://www.example.invalid/feed.rss', 'summary' => true, 'allUsers' => true]]);
			$this->fail('A user without manage permission added a feed for all users');
		} catch (AccessDenied $e) {
		}

		$this->assertSame($count, $this->countFeeds());
	}

	public function testOnlyManagersMayManage()
	{
		$this->assertTrue(RssFeed::mayManage());
		$this->actAs($this->user->id);
		$this->assertFalse(RssFeed::mayManage());
	}

	public function testUserCannotChangeFeedOfOthers()
	{
		$this->actAs($this->user->id);

		try {
			$this->saveFeeds([['id' => $this->adminFeedId, 'title' => 'Taken', 'url' => 'https://www.example.invalid/feed.rss', 'summary' => true]]);
			$this->fail('A user changed the feed of another user');
		} catch (AccessDenied $e) {
		}

		$feed = $this->adminFeed();
		$this->assertSame(User::ID_SUPER_ADMIN, (int) $feed['user_id']);
		$this->assertSame('Feed of the admin', $feed['title']);
	}

	public function testUserCannotReadOrDeleteFeedOfOthers()
	{
		$this->actAs($this->user->id);

		try {
			RssFeed::model()->findByPk($this->adminFeedId);
			$this->fail('A user read the feed of another user');
		} catch (AccessDenied $e) {
		}

		$feed = RssFeed::model()->findByPk($this->adminFeedId, false, true);
		try {
			$feed->delete();
			$this->fail('A user deleted the feed of another user');
		} catch (AccessDenied $e) {
		}

		$this->assertNotEmpty($this->adminFeed());
	}

	public function testOwnerCanReadAndDeleteOwnFeed()
	{
		$feed = RssFeed::model()->findByPk($this->adminFeedId);
		$this->assertNotEmpty($feed);
		$this->assertTrue($feed->delete());
		$this->assertNull(go()->getDbConnection()->selectSingleValue('id')->from('su_rss_feeds')->where('id', '=', $this->adminFeedId)->single());
	}
}
