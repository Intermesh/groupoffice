<?php

namespace go\modules\community\davclient\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\davclient\Module;

/**
 * Each calendar is a syncable collection in a DAV account
 * it has a reference to a calendar in Group Office were the data is stored.
 * @property DavAccount $owner
 */
class Calendar extends Property
{
	protected $davaccountId;
	/** @var string uri to the calendar on the server, also key of map */
	public $uri;

	public $id; // not auto increment but uses the Calendar id

	/** @var string if server has different ctag we need to fetch all etag to find out what changed. */
	public $ctag;
	public $synctoken;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_calendar");
	}

	public function sync(): bool {
//		if($this->isNew())
//			return $this->fetchEvents();
//		else {
			return $this->fetchEtags(); // sync changed only after comparing etags
		//}
	}

	public function put(CalendarEvent $event) {
		$http = $this->owner->http()->setHeader('Content-Type', 'text/calendar; charset=utf-8');
		if(!empty($event->etag())) { // update
			$http->setHeader('If-Match', $event->etag());
		}
		if(empty($event->uri())) { // create
			$event->uri($event->uid . '.ics');
		}
		$http->PUT( $this->uri . $event->uri(), $event->toVObject());
		if($http->statusCode() === 412) { // precondition (if-match) failed
			// the server made changed and we need to fetch the new VEVENT
			$this->fetchEvents([], [$event->uri()]);
			// here we will have disposed our own changes
		} else if ($http->statusCode() <= 299) {
			$event->etag($http->responseHeaders('etag'));
		} else {
			return false;
		}
		return true;
	}

	public function remove(CalendarEvent $event) {
		$http = $this->owner->http()->setHeader('Content-Type', 'text/calendar; charset=utf-8');
		$http->DELETE( $this->uri . $event->uri());
		// any success status from the server will indicate all is well.
		return ($http->statusCode() <= 299);
	}


	private function fetchEtags() {
		$xml = <<<XML
<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
	<d:prop>
		<d:getetag />
	</d:prop>
	<c:filter>
		<c:comp-filter name="VCALENDAR">
			<c:comp-filter name="VEVENT" />
		</c:comp-filter>
	</c:filter>
</c:calendar-query>
XML;
		$responses = $this->owner->http()
			->setHeader('Depth', 1)
			->setHeader('Prefer', 'return-minimal')
			->REPORT($this->uri, $xml)
			->parsedMultiStatus();
		$events = CalendarEvent::find(['id','etag', 'uri'])->where('calendarId','=',$this->id);
		$create = [];
		$update = [];
		$delete = [];

		$perHref = [];
		foreach($events as $event) {
			if(!isset($responses[$this->uri.$event->uri()])) {

				// delete missing hrefs
				$delete[] = $event->id;
			} else {
				$perHref[$this->uri.$event->uri()] = $event;
			}
		}

		// fetch changed etags
		foreach ($responses as $href => $response) {
			if($href === $this->uri) continue;
			if(!isset($perHref[$href])) {
				// create new hrefs
				$create[] = $href;
			} else if($perHref[$href]->etag() !== (string)$response->getetag) {
				// refetch changed etags
				$update[] = $href;
			}
		}
		go()->log('Found: New.'.count($create). ' Changed.'.count($update). ' Removed.'.count($delete));
		$success = true;
		// Remove missing
		if(!empty($delete))
			$success = CalendarEvent::delete((new Query())->where(['id'=> $delete]));
		// fetch changed and new
		if(!empty($update) || !empty($create)) {
			go()->log([$create,$update]);
			$success = $this->fetchEvents($create, $update) && $success;
		}
		return $success;
	}

	private function fetchAll() {

	}

	private function fetchEvents($create = [], $update = []) {
		$hrefs = array_merge($create,$update);
		$xml = '<c:calendar-multiget xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
    <d:prop>
        <d:getetag />
        <c:calendar-data />
    </d:prop>'.
			(!empty($hrefs) ? implode("\n",array_map(function($h) {return '<d:href>'.$h.'</d:href>';},$hrefs)) : '')
		.'</c:calendar-multiget>';
		$responses = $this->owner->http()
			->setHeader('Depth', 1)
			->setHeader('Prefer', 'return-minimal')
			->REPORT($this->uri, $xml)
			->parsedMultiStatus();
		//$events = [];
		$success=true;
		Module::$IS_SYNCING = true;
		foreach ($responses as $href => $response) {
			if (isset($response->{'calendar-data'})) {
				go()->log($href);
				if(in_array($href, $update)) // only
					$event = CalendarEvent::find()
						->where(['calendarId'=>$this->id, 'uri'=>basename($href)])->single();
				if(empty($event)) {
					$event = new CalendarEvent();
					$event->calendarId = $this->id;
					$event->useDefaultAlerts = true;
				}
				$event = ICalendarHelper::parseVObject((string)$response->{'calendar-data'}, $event);
				$event->etag((string)$response->getetag);
				$event->uri(basename($href));
				if(!$event->save()){
					$success = false;
					go()->log('cannot sync event '.print_r($event->getValidationErrors(), true));
				}
				$event = null;
			}
		}
		Module::$IS_SYNCING = false;
		return $success;
	}
}