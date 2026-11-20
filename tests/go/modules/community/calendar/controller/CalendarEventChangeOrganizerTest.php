<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Group;
use go\core\model\Module as CoreModule;
use go\core\model\Permission;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\TestCase;

/**
 * "Change organizer" in the event detail needs the "mayChangeOrganizer" right of the calendar module. Replies of
 * attendees go to the replyTo address of the event, so after the change it must be the address of the new organizer.
 * Before, the replies went to the user who created the event, with the name of the new organizer.
 */
class CalendarEventChangeOrganizerTest extends TestCase
{
	/** @var User[] */
	private static array $users = [];
	private static ?Group $group = null;
	private bool $sendMessages;
	/** @var EventModel[] */
	private array $events = [];

	public static function setUpBeforeClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$group = new Group();
			self::$group->name = uniqid('mayChangeOrganizer');
			if (!self::$group->save()) {
				throw new \Exception(self::$group->getValidationErrorsAsString());
			}
			$module = CoreModule::findByName('community', 'calendar');
			$module->permissions[self::$group->id] = (new Permission($module))
				->setRights(['mayRead' => true, 'mayChangeOrganizer' => true]);
			if (!$module->save()) {
				throw new \Exception($module->getValidationErrorsAsString());
			}

			// The creator, for example an assistant, has the right. The restricted user is a normal internal user.
			foreach (['creator', 'restricted', 'organizer', 'attendee'] as $name) {
				$username = uniqid('chorg' . $name);
				$user = new User();
				$user->setValues([
					'username' => $username,
					'displayName' => $name,
					'email' => $username . '@intermesh.localhost',
					'password' => 'Pa55word!!xyz'
				]);
				$user->groups = $name === 'creator'
					? [Group::ID_EVERYONE, Group::ID_INTERNAL, self::$group->id]
					: [Group::ID_EVERYONE, Group::ID_INTERNAL];
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
		if (isset(self::$group->id)) {
			$module = CoreModule::findByName('community', 'calendar');
			unset($module->permissions[self::$group->id]);
			$module->save();
			Group::delete(['id' => self::$group->id]);
		}
		self::$group = null;
	}

	protected function setUp(): void
	{
		$this->sendMessages = EventModel::$sendSchedulingMessages;
		EventModel::$sendSchedulingMessages = false;
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		foreach ($this->events as $event) {
			go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $event->customFieldsModelId()])->execute();
		}
		$this->events = [];
		Module::onGarbageCollection();
		EventModel::$sendSchedulingMessages = $this->sendMessages;
	}

	/**
	 * An event in the calendar of the given user, who is the organizer. The organizer and attendee users are invited.
	 */
	private function createEvent(string $ownerKey): EventModel
	{
		$owner = self::$users[$ownerKey];
		go()->setAuthState(new TemporaryState($owner->id));

		$event = new EventModel();
		$event->calendarId = Calendar::fetchPersonal($owner->id);
		$event->title = 'Presentation';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$event->duration = 'PT1H';
		$participants = [];
		foreach ([$ownerKey, 'organizer', 'attendee'] as $name) {
			$user = self::$users[$name];
			$participants[$user->id] = ['email' => $user->email, 'name' => $name, 'kind' => 'individual',
				'roles' => $name === $ownerKey ? ['owner' => true, 'attendee' => true] : ['attendee' => true],
				'participationStatus' => $name === $ownerKey ? 'accepted' : 'needs-action'];
		}
		$event->setValues(['participants' => $participants]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->assertSame($owner->email, $event->replyTo);
		$this->events[] = $event;
		return $event;
	}

	/**
	 * The controller without the constructor, which checks the module permissions of the test user
	 */
	private function controller(): CalendarEvent
	{
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	private function update(EventModel $event, string $userKey, array $patch): array
	{
		go()->setAuthState(new TemporaryState(self::$users[$userKey]->id));
		return json_decode(json_encode($this->controller()->set(['update' => [$event->id => $patch]])), true);
	}

	/**
	 * What the "Change organizer" field in the event detail sends
	 */
	private function changeOrganizerPatch(string $fromKey): array
	{
		$organizer = self::$users['organizer'];
		return [
			'participants/' . self::$users[$fromKey]->id . '/roles' => ['attendee' => true, 'owner' => null],
			'participants/' . $organizer->id => ['email' => $organizer->email, 'name' => 'organizer',
				'roles' => ['attendee' => true, 'owner' => true], 'kind' => 'individual',
				'participationStatus' => 'accepted', 'expectReply' => false],
		];
	}

	private function replyTo(EventModel $event): ?string
	{
		return go()->getDbConnection()->selectSingleValue('replyTo')->from('calendar_event')
			->where(['eventId' => $event->customFieldsModelId()])->single();
	}

	private function organizerEmail(EventModel $event): ?string
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		return EventModel::findById($event->id)->organizer()?->email;
	}

	public function testChangeOrganizerChangesReplyTo(): void
	{
		$event = $this->createEvent('creator');

		$response = $this->update($event, 'creator', $this->changeOrganizerPatch('creator'));

		$this->assertArrayHasKey($event->id, $response['updated'] ?? [], json_encode($response));
		$this->assertSame(self::$users['organizer']->email, $this->organizerEmail($event));
		$this->assertSame(self::$users['organizer']->email, $this->replyTo($event));
	}

	public static function refusedChanges(): array
	{
		return [
			'change organizer' => ['change'],
			'remove organizer' => ['remove'],
		];
	}

	/**
	 * @dataProvider refusedChanges
	 */
	public function testChangeOrganizerWithoutRightIsRefused(string $change): void
	{
		$event = $this->createEvent('restricted');
		$restricted = self::$users['restricted'];

		$patch = $change === 'change'
			? $this->changeOrganizerPatch('restricted')
			: ['participants/' . $restricted->id => null];
		try {
			$response = $this->update($event, 'restricted', $patch);
			$this->fail('The organizer must not change without "mayChangeOrganizer": ' . json_encode($response));
		} catch (Forbidden) {
			// Like the other changes a client may not make, see CalendarEvent::internalSave()
		}

		$this->assertSame($restricted->email, $this->organizerEmail($event));
		$this->assertSame($restricted->email, $this->replyTo($event));
	}

	public function testOtherChangesWithoutRightAreAllowed(): void
	{
		$event = $this->createEvent('restricted');

		$response = $this->update($event, 'restricted', [
			'participants/' . self::$users['attendee']->id . '/participationStatus' => 'accepted',
			'title' => 'Presentation moved',
		]);

		$this->assertArrayHasKey($event->id, $response['updated'] ?? [], json_encode($response));
		$this->assertSame(self::$users['restricted']->email, $this->replyTo($event));
	}

	public function testInvitationFromOutsideKeepsReplyTo(): void
	{
		$event = $this->createEvent('creator');
		// For an invitation from outside GroupOffice, replyTo is the address of the external organizer
		go()->getDbConnection()->update('calendar_event', ['isOrigin' => false, 'replyTo' => 'external@example.com'],
			['eventId' => $event->customFieldsModelId()])->execute();

		go()->setAuthState(new TemporaryState(self::$users['creator']->id));
		$event = EventModel::findById($event->id);
		$organizer = self::$users['organizer'];
		$event->participants[self::$users['creator']->id]->setRoles(['attendee' => true, 'owner' => null]);
		$event->participants[$organizer->id]->setRoles(['attendee' => true, 'owner' => true]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		$this->assertSame($organizer->email, $event->organizer()->email, 'The organizer must have changed');
		$this->assertSame('external@example.com', $this->replyTo($event));
	}
}
