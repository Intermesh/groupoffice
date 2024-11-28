<?php

namespace go\modules\community\davclient\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;

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

	public function sync() {
		if($this->isNew())
			$this->fetchEvents();
		else {
			$this->fetchEtags(); // sync changed only after comparing etags
		}
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

		$event->etag($http->responseHeaders('etag'));
		if(empty($event->etag())) {
			// the server made changed and we need to fetch the new VEVENT
			//if($event->save())
			//$this->fetchEvents([], [$event->uri()]);
		}

		return true;
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
			if(!isset($perHref[$href])) {
				// create new hrefs
				$create[] = $href;
			} else if($perHref[$href]->etag() !== $response->getetag) {
				// refetch changed etags
				$update[] = $href;
			}
		}

		// Remove missing
		if(!empty($delete))
			CalendarEvent::delete((new Query())->where(['id'=> $delete]));
		// fetch changed and new
		if(!empty($update) || !empty($create)) {
			$this->fetchEvents($create, $update);
		}
	}

	private function fetchEvents($create = [], $update = []) {
		$hrefs = array_merge($create,$update);
		$xml = '<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
    <d:prop>
        <d:getetag />
        <c:calendar-data />
    </d:prop>'.
			(!empty($hrefs) ? implode("\n",array_map(function($h) {return '<d:href>'.$h.'</d:href>';},$hrefs)) : '')
			.'<c:filter>
        <c:comp-filter name="VCALENDAR">
			<c:comp-filter name="VEVENT" />
        </c:comp-filter>
    </c:filter>
</c:calendar-query>';
		$responses = $this->owner->http()
			->setHeader('Depth', 1)
			->setHeader('Prefer', 'return-minimal')
			->REPORT($this->uri, $xml)
			->parsedMultiStatus();
		//$events = [];
		foreach ($responses as $href => $response) {
			if (isset($response->{'calendar-data'})) {
				if(in_array($href, $update)) // only
					$event = CalendarEvent::find()
						->where(['calendarId'=>$this->id, 'uri'=>basename($href)])->single();
				if(empty($event)) {
					$event = new CalendarEvent();
					$event->calendarId = $this->id;
					$event->useDefaultAlerts = true;
				}
				$event = ICalendarHelper::parseVObject((string)$response->{'calendar-data'}, $event);
				$event->etag((string)$response->etag);
				$event->uri(basename($href));
				if(!$event->save()){
					go()->log('cannot sync event '.print_r($event->getValidationErrors(), true));
				}
				$event = null;
			}
		}
	}
}