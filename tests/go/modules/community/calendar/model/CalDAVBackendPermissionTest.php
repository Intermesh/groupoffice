<?php

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclItemEntity;
use go\core\auth\TemporaryState;
use go\core\dav\davacl\PrincipalBackend;
use go\core\dav\davacl\PrincipalCollection;
use go\core\dav\schedule\SchedulePlugin;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\Module;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;
use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

/**
 * CalDAV writes don't go through the JMAP controller, so the backend and the Sabre ACL must enforce the calendar
 * permissions. Requests are sent through a Sabre server set up like go/core/dav/index.php.
 */
class CalDAVBackendPermissionTest extends TestCase
{
	/** @var User[] */
	private array $users = [];
	private ?Calendar $shared = null;
	private ?TaskList $tasklist = null;
	/** @var string[] calendar and task list ids made over CalDAV, like "c-1" */
	private array $created = [];
	/** @var array|null [calendar id, acl] */
	private ?array $restoreAcl = null;
	private array $eventIds = [];
	private bool $sendMessages;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;

		// Users are reused and never deleted so other tests that rely on low user ids keep working.
		foreach (['owner', 'reader', 'writer', 'deleter', 'outsider'] as $name) {
			$username = 'davperm' . $name;
			$user = User::find()->where(['username' => $username])->single();
			if (!$user) {
				$user = new User();
				$user->username = $username;
				$user->displayName = $name;
				$user->email = $user->recoveryEmail = $username . '@intermesh.localhost';
				$user->setPassword('sdfTgs-Dfgg-$#ddg');
				$this->assertTrue($user->save(), json_encode($user->getValidationErrors()));
			}
			$this->users[$name] = $user;
			$this->syncToDevice($name, (int) Calendar::fetchPersonal($user->id));
		}

		$this->actAs($this->users['owner']->id);
		$this->shared = new Calendar();
		$this->shared->name = 'CalDAV permission test';
		$this->shared->setOwnerId($this->users['owner']->id);
		$this->shared->setAcl([
			$this->users['reader']->getPersonalGroup()->id => Acl::LEVEL_READ,
			$this->users['writer']->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			$this->users['deleter']->getPersonalGroup()->id => Acl::LEVEL_DELETE,
		]);
		$this->assertTrue($this->shared->save(), json_encode($this->shared->getValidationErrors()));

		foreach (['owner', 'reader', 'writer', 'deleter'] as $name) {
			$this->syncToDevice($name, $this->shared->id);
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(1));
		foreach ($this->eventIds as $eventId) {
			go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $eventId])->execute();
		}
		if ($this->shared) {
			Calendar::delete(['id' => $this->shared->id]);
		}
		if ($this->tasklist) {
			TaskList::delete(['id' => $this->tasklist->id]);
		}
		foreach ($this->created as $collectionId) {
			[$type, $id] = explode('-', $collectionId, 2);
			$type === 'c' ? Calendar::delete(['id' => $id]) : TaskList::delete(['id' => $id]);
		}
		if ($this->restoreAcl) {
			$calendar = Calendar::findById($this->restoreAcl[0]);
			$calendar->setAcl(array_replace(array_map(fn($level) => null, $calendar->getAcl()->getArrayCopy()), $this->restoreAcl[1]));
			$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
		}
		Module::onGarbageCollection();
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
	}

	/**
	 * Switch user. Permission levels are cached per process, which is one user in a real DAV request: the calendar of
	 * an event is cached with the permission level of the user that loaded it first and ACL levels are not cleared
	 * when an ACL changes.
	 */
	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		(new \ReflectionProperty(AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
		(new \ReflectionProperty(Acl::class, 'permissionLevelCache'))->setValue(null, []);
	}

	private function syncToDevice(string $userKey, int $calendarId): void
	{
		$this->actAs($this->users[$userKey]->id);
		$calendar = Calendar::findById($calendarId);
		$calendar->isSubscribed = true;
		$calendar->syncToDevice = true;
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
	}

	private function server(string $userKey): DAV\Server
	{
		$username = $this->users[$userKey]->username;
		$this->actAs($this->users[$userKey]->id);

		$principalBackend = new PrincipalBackend();
		$server = new DAV\Server([
			new PrincipalCollection($principalBackend),
			new CalDAV\CalendarRoot($principalBackend, new CalDAVBackend())
		]);
		$server->setBaseUri('/dav/');
		$server->addPlugin(new DAV\Auth\Plugin(new class($username) implements DAV\Auth\Backend\BackendInterface {
			public function __construct(private string $username) {}

			public function check(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response): array
			{
				return [true, 'principals/' . $this->username];
			}

			public function challenge(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response): void
			{
			}
		}));
		$server->addPlugin(new CalDAV\Plugin());
		$aclPlugin = new DAVACL\Plugin();
		$aclPlugin->allowUnauthenticatedAccess = false;
		$aclPlugin->hideNodesFromListings = true;
		$server->addPlugin($aclPlugin);
		$server->addPlugin(new SchedulePlugin());

		return $server;
	}

	/**
	 * Send a request as the given user
	 *
	 * @return int HTTP status
	 */
	private function request(string $userKey, string $method, int|string $calendarId, string $file = '', ?string $body = null, array $headers = [], ?Response &$response = null): int
	{
		$collection = str_starts_with((string) $calendarId, 't-') ? $calendarId : 'c-' . $calendarId;
		return $this->requestPath($userKey, $method, $collection . '/' . $file, $body, $headers, $response);
	}

	/**
	 * Send a request as the given user to a path in their calendar home
	 *
	 * @return int HTTP status
	 */
	private function requestPath(string $userKey, string $method, string $path, ?string $body = null, array $headers = [], ?Response &$response = null): int
	{
		$server = $this->server($userKey);
		$url = '/dav/calendars/' . $this->users[$userKey]->username . '/' . $path;
		$request = new Request($method, $url, $headers, $body);
		$request->setBaseUrl('/dav/'); // done by Server::start()
		$response = new Response();
		$server->httpRequest = $request;
		$server->httpResponse = $response;
		try {
			$server->invokeMethod($request, $response, false);
		} catch (DAV\Exception $e) {
			return $e->getHTTPCode();
		}
		return $response->getStatus();
	}

	private function put(string $userKey, int|string $calendarId, string $file, string $ics): int
	{
		return $this->request($userKey, 'PUT', $calendarId, $file, $ics, ['Content-Type' => 'text/calendar']);
	}

	private function ics(string $uid, string $title, string $participants = '', string $prodId = '-//Test//EN'): string
	{
		return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:$prodId\r\nBEGIN:VEVENT\r\n" .
			"UID:$uid\r\nDTSTAMP:20261001T100000Z\r\n" .
			"DTSTART:20271001T100000Z\r\nDTEND:20271001T110000Z\r\nSUMMARY:$title\r\n" .
			$participants .
			"END:VEVENT\r\nEND:VCALENDAR\r\n";
	}

	private function createEvent(string $userKey, int $calendarId, array $participants = []): CalendarEvent
	{
		$this->actAs($this->users[$userKey]->id);
		$event = new CalendarEvent();
		$event->calendarId = $calendarId;
		$event->title = 'Original';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$event->duration = 'PT1H';
		if ($participants) {
			$event->setValues(['participants' => $participants]);
		}
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->eventIds[] = $event->customFieldsModelId();
		return $event;
	}

	private function title(CalendarEvent $event): ?string
	{
		return go()->getDbConnection()->selectSingleValue('title')
			->from('calendar_event')
			->where(['eventId' => $event->customFieldsModelId()])
			->single() ?: null;
	}

	private function isLinked(CalendarEvent $event, int $calendarId): bool
	{
		return (bool) go()->getDbConnection()->selectSingleValue('id')
			->from('calendar_calendar_event')
			->where(['eventId' => $event->customFieldsModelId(), 'calendarId' => $calendarId])
			->single();
	}

	private function eventIdsByUid(string $uid): array
	{
		return go()->getDbConnection()->selectSingleValue('eventId')
			->from('calendar_event')
			->where(['uid' => $uid])
			->all();
	}

	public function testReadOnlyUserCannotWrite(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);
		$file = $event->uri();

		$this->assertSame(403, $this->put('reader', $this->shared->id, $file, $this->ics($event->uid, 'Changed')));
		$this->assertSame(403, $this->put('reader', $this->shared->id, 'new.ics', $this->ics(uniqid('davperm'), 'New')));
		$this->assertSame(403, $this->request('reader', 'DELETE', $this->shared->id, $file));

		$proppatch = '<?xml version="1.0"?><d:propertyupdate xmlns:d="DAV:"><d:set><d:prop>' .
			'<d:displayname>Renamed</d:displayname></d:prop></d:set></d:propertyupdate>';
		$this->assertSame(403, $this->request('reader', 'PROPPATCH', $this->shared->id, '', $proppatch, ['Content-Type' => 'application/xml']));

		$this->assertSame('Original', $this->title($event));
		$this->assertTrue($this->isLinked($event, $this->shared->id));
		$this->assertSame('CalDAV permission test', Calendar::findById($this->shared->id)->name);
	}

	public function testWriterCanUpdateButNotDelete(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);
		$file = $event->uri();

		$this->assertSame(204, $this->put('writer', $this->shared->id, $file, $this->ics($event->uid, 'Changed')));
		$this->assertSame('Changed', $this->title($event));

		$this->assertSame(403, $this->request('writer', 'DELETE', $this->shared->id, $file));
		$this->assertTrue($this->isLinked($event, $this->shared->id));
	}

	public function testDeleterCanDelete(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);

		$this->assertSame(204, $this->request('deleter', 'DELETE', $this->shared->id, $event->uri()));
		$this->assertFalse($this->isLinked($event, $this->shared->id));
	}

	public function testUnreadableEventIsNotLinkedByUid(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);
		$calendarId = (int) Calendar::fetchPersonal($this->users['outsider']->id);

		$this->assertSame(403, $this->put('outsider', $calendarId, 'stolen.ics', $this->ics($event->uid, 'Hijacked')));

		$this->assertSame('Original', $this->title($event));
		$this->assertFalse($this->isLinked($event, $calendarId));
	}

	public function testReadableEventIsLinkedWithoutChangingIt(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);
		$calendarId = (int) Calendar::fetchPersonal($this->users['reader']->id);

		$status = $this->put('reader', $calendarId, 'copy.ics', $this->ics($event->uid, 'Changed'));

		$this->assertSame(201, $status);
		$this->assertTrue($this->isLinked($event, $calendarId));
		$this->assertSame('Original', $this->title($event));
	}

	public function testAttendeeOnlyChangesParticipationStatus(): void
	{
		$owner = $this->users['owner'];
		$attendee = $this->users['outsider'];
		$event = $this->createEvent('owner', (int) Calendar::fetchPersonal($owner->id), [
			$owner->id => ['email' => $owner->email, 'name' => 'Owner', 'roles' => ['owner' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			$attendee->id => ['email' => $attendee->email, 'name' => 'Attendee', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]);
		$calendarId = (int) Calendar::fetchPersonal($attendee->id);
		$this->assertTrue($this->isLinked($event, $calendarId), 'The invitation must be in the attendee calendar');

		// What Thunderbird sends when accepting, here with a changed title as well
		$participants = "ORGANIZER;CN=Owner:mailto:{$owner->email}\r\n" .
			"ATTENDEE;CN=Owner;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:{$owner->email}\r\n" .
			"ATTENDEE;CN=Attendee;PARTSTAT=ACCEPTED;ROLE=REQ-PARTICIPANT:mailto:{$attendee->email}\r\n" .
			"X-MOZ-RECEIVED-SEQUENCE:0\r\nX-MOZ-GENERATION:1\r\nTRANSP:TRANSPARENT\r\n" .
			"BEGIN:VALARM\r\nACTION:DISPLAY\r\nTRIGGER:-PT15M\r\nDESCRIPTION:Reminder\r\nEND:VALARM\r\n";
		$ics = $this->ics($event->uid, 'Changed by attendee', $participants, '-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN');

		$organizerCalendarId = (int) Calendar::fetchPersonal($owner->id);
		$this->assertSame(200, $this->request('owner', 'GET', $organizerCalendarId, $event->uri(), null, [], $before));
		// The GET stores the iCalendar data of the organizer. Make it older so the change in the same second is visible.
		$blobId = go()->getDbConnection()->selectSingleValue('veventBlobId')->from('calendar_event_user')
			->where(['eventId' => $event->customFieldsModelId(), 'userId' => $owner->id])->single();
		$hourAgo = new DateTime('-1 hour');
		go()->getDbConnection()->update('calendar_event', ['modifiedAt' => $hourAgo], ['eventId' => $event->customFieldsModelId()])->execute();
		go()->getDbConnection()->update('core_blob', ['modifiedAt' => $hourAgo], ['id' => $blobId])->execute();
		$this->assertSame(200, $this->request('owner', 'GET', $organizerCalendarId, $event->uri(), null, [], $before));

		$this->assertSame(204, $this->put('outsider', $calendarId, $event->uri(), $ics));

		$this->assertSame('Original', $this->title($event));
		$status = go()->getDbConnection()->selectSingleValue('participationStatus')
			->from('calendar_participant')
			->where(['eventId' => $event->customFieldsModelId(), 'email' => $attendee->email])
			->single();
		$this->assertSame(Participant::Accepted, $status);

		// The alert and free / busy status are the attendee's own
		$this->actAs($attendee->id);
		$attendeeEvent = CalendarEvent::find()->where(['cce.calendarId' => $calendarId, 'cce.eventId' => $event->customFieldsModelId()])->single();
		$this->assertSame(CalendarEvent::FREEBUSY_FREE, $attendeeEvent->freeBusyStatus);
		$this->assertCount(1, $attendeeEvent->alerts);
		$this->assertSame('-PT15M', array_values($attendeeEvent->alerts)[0]->getTrigger()['offset']);

		// The organizer client sees the accept
		$this->assertSame(200, $this->request('owner', 'GET', $organizerCalendarId, $event->uri(), null, [], $after));
		$this->assertNotSame($before->getHeader('ETag'), $after->getHeader('ETag'));
		$this->assertMatchesRegularExpression('/PARTSTAT=ACCEPTED[^\r\n]*' . preg_quote($attendee->email, '/') . '/i',
			str_replace("\r\n ", '', $after->getBodyAsString()));
	}

	public function testInvitationReachesCalendarTheOrganizerCannotRead(): void
	{
		$organizer = $this->users['outsider'];
		$attendee = $this->users['owner'];
		// Personal calendars are readable for everyone by default
		$this->actAs($attendee->id);
		$calendar = Calendar::findById(Calendar::fetchPersonal($attendee->id));
		$this->restoreAcl = [$calendar->id, $calendar->getAcl()->getArrayCopy()];
		// setAcl() patches the ACL. Null removes a group.
		$acl = array_map(fn($level) => null, $this->restoreAcl[1]);
		$acl[$attendee->getPersonalGroup()->id] = Acl::LEVEL_MANAGE;
		$calendar->setAcl($acl);
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));

		$this->actAs($organizer->id);
		$this->assertSame(0, Calendar::findById(Calendar::fetchPersonal($attendee->id))->getPermissionLevel(),
			'The organizer must not have access to the attendee calendar');
		$uid = uniqid('davperm');
		$participants = "ORGANIZER;CN=Organizer:mailto:{$organizer->email}\r\n" .
			"ATTENDEE;CN=Organizer;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:{$organizer->email}\r\n" .
			"ATTENDEE;CN=Attendee;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto:{$attendee->email}\r\n";

		$this->assertSame(201, $this->put('outsider', (int) Calendar::fetchPersonal($organizer->id), $uid . '.ics', $this->ics($uid, 'Invitation', $participants)));

		$eventIds = $this->eventIdsByUid($uid);
		$this->eventIds = array_merge($this->eventIds, $eventIds);
		$this->assertCount(1, $eventIds);
		$linked = go()->getDbConnection()->selectSingleValue('id')
			->from('calendar_calendar_event')
			->where(['eventId' => $eventIds[0], 'calendarId' => Calendar::fetchPersonal($attendee->id)])
			->single();
		$this->assertNotEmpty($linked, 'The invitation must be in the attendee calendar');
	}

	public function testReadOnlyTaskList(): void
	{
		$this->actAs($this->users['owner']->id);
		$this->tasklist = new TaskList();
		$this->tasklist->name = 'CalDAV permission test';
		$this->tasklist->setAcl([$this->users['reader']->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->tasklist->save(), json_encode($this->tasklist->getValidationErrors()));

		$this->actAs($this->users['reader']->id);
		$tasklist = TaskList::findById($this->tasklist->id);
		$tasklist->isSubscribed = true;
		$tasklist->syncToDevice = true;
		$this->assertTrue($tasklist->save(), json_encode($tasklist->getValidationErrors()));

		$uid = uniqid('davperm');
		$vtodo = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nBEGIN:VTODO\r\nUID:$uid\r\n" .
			"DTSTAMP:20261001T100000Z\r\nSUMMARY:Task\r\nEND:VTODO\r\nEND:VCALENDAR\r\n";
		$this->assertSame(403, $this->put('reader', 't-' . $this->tasklist->id, $uid . '.ics', $vtodo));
		$this->assertSame(0, (int) go()->getDbConnection()->selectSingleValue('count(*)')->from('tasks_task')->where(['uid' => $uid])->single());
	}

	/**
	 * @return array calendar records by calendar id
	 */
	private function calendarsOf(string $userKey): array
	{
		$records = [];
		foreach ((new CalDAVBackend())->getCalendarsForUser('principals/' . $this->users[$userKey]->username) as $record) {
			$records[$record['id']] = $record;
		}
		return $records;
	}

	public function testCalendarListUsesPermissionsOfThePrincipal(): void
	{
		$this->actAs($this->users['reader']->id);
		$calendars = $this->calendarsOf('reader');

		$personal = 'c-' . Calendar::fetchPersonal($this->users['reader']->id);
		$this->assertArrayHasKey($personal, $calendars);
		$this->assertFalse($calendars[$personal]['{http://sabredav.org/ns}read-only']);
		$this->assertArrayHasKey('c-' . $this->shared->id, $calendars);
		$this->assertTrue($calendars['c-' . $this->shared->id]['{http://sabredav.org/ns}read-only']);
	}

	/**
	 * Sabre lists the calendars of another user for free / busy. Only the calendars of that user may be used, not the
	 * calendars shared with them, even when the logged in user can read those.
	 */
	public function testCalendarListOfAnotherUserOnlyHasTheirCalendars(): void
	{
		$this->actAs($this->users['owner']->id);
		$calendars = $this->calendarsOf('reader');

		$this->assertArrayNotHasKey('c-' . $this->shared->id, $calendars, 'The reader subscribed to the shared calendar, but it is not theirs');
		foreach ($calendars as $record) {
			$this->assertStringStartsWith('c-', $record['id'], 'Task lists have no free / busy information');
		}
	}

	public function testCalendarListOfAnotherUserRequiresFreeBusyPermission(): void
	{
		// The outsider can't see the shared calendar of the owner
		$this->actAs($this->users['outsider']->id);
		$this->assertArrayNotHasKey('c-' . $this->shared->id, $this->calendarsOf('owner'));

		// Free / busy permission is enough, like for the availability in the web interface
		$this->actAs($this->users['owner']->id);
		$calendar = Calendar::findById($this->shared->id);
		$calendar->setAcl([$this->users['outsider']->getPersonalGroup()->id => Calendar::LEVEL_FREEBUSY]);
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));

		$this->actAs($this->users['outsider']->id);
		$this->assertArrayHasKey('c-' . $this->shared->id, $this->calendarsOf('owner'));
	}

	public function testUnknownPrincipal(): void
	{
		$this->expectException(DAV\Exception\NotFound::class);
		(new CalDAVBackend())->getCalendarsForUser('principals/' . uniqid('nobody'));
	}

	public function testUpdateOfMissingObjectFails(): void
	{
		$this->actAs($this->users['owner']->id);
		$this->expectException(DAV\Exception\NotFound::class);
		(new CalDAVBackend())->updateCalendarObject('c-' . $this->shared->id, uniqid('missing') . '.ics', $this->ics(uniqid('davperm'), 'Missing'));
	}

	public function testCalendarUpdateReportsFailures(): void
	{
		$this->actAs($this->users['owner']->id);
		$backend = new CalDAVBackend();

		$propPatch = new DAV\PropPatch(['{DAV:}displayname' => str_repeat('x', 200)]);
		$backend->updateCalendar('c-' . $this->shared->id, $propPatch);
		$propPatch->commit();
		$this->assertSame(403, $propPatch->getResult()['{DAV:}displayname'], 'The name is too long');
		$this->assertSame('CalDAV permission test', Calendar::findById($this->shared->id)->name);

		$propPatch = new DAV\PropPatch(['{DAV:}displayname' => 'Renamed']);
		$backend->updateCalendar('c-999999999', $propPatch);
		$propPatch->commit();
		$this->assertSame(404, $propPatch->getResult()['{DAV:}displayname']);
	}

	/**
	 * Make a calendar with MKCALENDAR
	 *
	 * @param string|null $components component set, for example '<C:comp name="VTODO"/>'. Null to leave it out.
	 * @return int HTTP status
	 */
	private function mkcalendar(string $userKey, string $name, ?string $components): int
	{
		$body = '<?xml version="1.0"?><C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav"><D:set><D:prop>' .
			'<D:displayname>' . $name . '</D:displayname>' .
			($components === null ? '' : '<C:supported-calendar-component-set>' . $components . '</C:supported-calendar-component-set>') .
			'</D:prop></D:set></C:mkcalendar>';
		return $this->requestPath($userKey, 'MKCALENDAR', uniqid('new') . '/', $body, ['Content-Type' => 'application/xml']);
	}

	/**
	 * @return string|null the id of the calendar or task list of the user with this name, like "c-1"
	 */
	private function collectionByName(string $userKey, string $name): ?string
	{
		$this->actAs($this->users[$userKey]->id);
		foreach ($this->calendarsOf($userKey) as $record) {
			if ($record['{DAV:}displayname'] === $name) {
				$this->created[] = $record['id'];
				return $record['id'];
			}
		}
		return null;
	}

	public function testMakeCalendarWithoutComponentSet(): void
	{
		$name = uniqid('Made over CalDAV ');
		$this->assertSame(201, $this->mkcalendar('reader', $name, null));

		$id = $this->collectionByName('reader', $name);
		$this->assertNotNull($id, 'The new calendar must be listed for the user that made it');
		$this->assertStringStartsWith('c-', $id);
	}

	public function testMakeTaskList(): void
	{
		$name = uniqid('Made over CalDAV ');
		$this->assertSame(201, $this->mkcalendar('reader', $name, '<C:comp name="VTODO"/>'));

		$id = $this->collectionByName('reader', $name);
		$this->assertNotNull($id, 'The new task list must be listed for the user that made it');
		$this->assertStringStartsWith('t-', $id);
	}

	public function testMakeCalendarWithUnsupportedComponents(): void
	{
		$name = uniqid('Made over CalDAV ');
		$this->assertSame(400, $this->mkcalendar('reader', $name, '<C:comp name="VJOURNAL"/>'));
		$this->assertNull($this->collectionByName('reader', $name));
	}

	/**
	 * An event is stored once for all calendars it's in, but every client uses its own name for its copy.
	 */
	public function testEveryCalendarKeepsItsOwnUri(): void
	{
		$event = $this->createEvent('owner', $this->shared->id);
		$ownerFile = $event->uri();
		$calendarId = (int) Calendar::fetchPersonal($this->users['reader']->id);

		$this->assertSame(201, $this->put('reader', $calendarId, 'my-copy.ics', $this->ics($event->uid, 'Original')));

		$this->assertSame(200, $this->request('owner', 'GET', $this->shared->id, $ownerFile));
		$this->assertSame(200, $this->request('reader', 'GET', $calendarId, 'my-copy.ics'));
		$this->assertSame(404, $this->request('reader', 'GET', $calendarId, $ownerFile));
	}
}
