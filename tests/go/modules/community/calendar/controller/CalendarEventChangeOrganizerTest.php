<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\TestCase;

/**
 * Replies of attendees go to the replyTo address of the event. After "Change organizer" it must be the address of the
 * new organizer. Before, the replies went to the user who created the event, with the name of the new organizer.
 */
class CalendarEventChangeOrganizerTest extends TestCase
{
	/** @var User[] */
	private static array $users = [];
	private bool $sendMessages;
	private ?EventModel $event = null;

	public static function setUpBeforeClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			foreach (['creator', 'organizer', 'attendee'] as $name) {
				$username = uniqid('chorg' . $name);
				$user = new User();
				$user->setValues([
					'username' => $username,
					'displayName' => $name,
					'email' => $username . '@intermesh.localhost',
					'password' => 'Pa55word!!xyz'
				]);
				if (!$user->save()) {
					throw new \Exception(var_export($user->getValidationErrors(), true));
				}
				self::$users[$name] = $user;
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (self::$users) {
			User::delete(['id' => array_map(fn(User $u) => $u->id, self::$users)]);
		}
		self::$users = [];
	}

	protected function setUp(): void
	{
		$this->sendMessages = EventModel::$sendSchedulingMessages;
		EventModel::$sendSchedulingMessages = false;

		// The creator, for example an assistant, makes the event in their own calendar
		$creator = self::$users['creator'];
		go()->setAuthState(new TemporaryState($creator->id));

		$this->event = new EventModel();
		$this->event->calendarId = Calendar::fetchPersonal($creator->id);
		$this->event->title = 'Presentation';
		$this->event->timeZone = 'Europe/Amsterdam';
		$this->event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$this->event->duration = 'PT1H';
		$participants = [];
		foreach (self::$users as $name => $user) {
			$participants[$user->id] = ['email' => $user->email, 'name' => $name, 'kind' => 'individual',
				'roles' => $name === 'creator' ? ['owner' => true, 'attendee' => true] : ['attendee' => true],
				'participationStatus' => $name === 'creator' ? 'accepted' : 'needs-action'];
		}
		$this->event->setValues(['participants' => $participants]);
		$this->assertTrue($this->event->save(), json_encode($this->event->getValidationErrors()));
		$this->assertSame($creator->email, $this->event->replyTo);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if ($this->event) {
			go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $this->event->customFieldsModelId()])->execute();
		}
		Module::onGarbageCollection();
		EventModel::$sendSchedulingMessages = $this->sendMessages;
	}

	/**
	 * The controller without the constructor, which checks the module permissions of the test user
	 */
	private function controller(): CalendarEvent
	{
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	private function update(array $patch): void
	{
		go()->setAuthState(new TemporaryState(self::$users['creator']->id));
		$response = json_decode(json_encode($this->controller()->set(['update' => [$this->event->id => $patch]])), true);
		$this->assertArrayHasKey($this->event->id, $response['updated'] ?? [], json_encode($response));
	}

	private function replyTo(): ?string
	{
		return go()->getDbConnection()->selectSingleValue('replyTo')->from('calendar_event')
			->where(['eventId' => $this->event->customFieldsModelId()])->single();
	}

	public function testChangeOrganizerChangesReplyTo(): void
	{
		$creator = self::$users['creator'];
		$organizer = self::$users['organizer'];

		// What the "Change organizer" field in the event detail sends
		$this->update([
			'participants/' . $creator->id . '/roles' => ['attendee' => true, 'owner' => null],
			'participants/' . $organizer->id => ['email' => $organizer->email, 'name' => 'organizer',
				'roles' => ['attendee' => true, 'owner' => true], 'kind' => 'individual',
				'participationStatus' => 'accepted', 'expectReply' => false],
		]);

		$this->assertSame($organizer->email, $this->replyTo());
	}

	public function testOtherParticipantChangeKeepsReplyTo(): void
	{
		$this->update([
			'participants/' . self::$users['attendee']->id . '/participationStatus' => 'accepted',
			'title' => 'Presentation moved',
		]);

		$this->assertSame(self::$users['creator']->email, $this->replyTo());
	}

	public function testInvitationFromOutsideKeepsReplyTo(): void
	{
		// For an invitation from outside GroupOffice, replyTo is the address of the external organizer
		go()->getDbConnection()->update('calendar_event', ['isOrigin' => false, 'replyTo' => 'external@example.com'],
			['eventId' => $this->event->customFieldsModelId()])->execute();

		go()->setAuthState(new TemporaryState(self::$users['creator']->id));
		$event = EventModel::findById($this->event->id);
		$organizer = self::$users['organizer'];
		$event->participants[self::$users['creator']->id]->setRoles(['attendee' => true, 'owner' => null]);
		$event->participants[$organizer->id]->setRoles(['attendee' => true, 'owner' => true]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		$this->assertSame($organizer->email, $event->organizer()->email, 'The organizer must have changed');
		$this->assertSame('external@example.com', $this->replyTo());
	}
}
