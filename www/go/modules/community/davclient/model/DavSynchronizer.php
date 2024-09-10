<?php
namespace go\modules\community\davclient\model;


use go\core\orm\Query;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;

class DavSynchronizer {

	private static $xmlNs = [
		'd:' => "DAV:",
		'cs:' => "http://calendarserver.org/ns/",
		'cal:' => "urn:ietf:params:xml:ns:caldav",
		'card:' => "urn:ietf:params:xml:ns:carddav",
		'ical:' => "http://apple.com/ns/ical/",
	];
	private $account;

	/** @var HttpClient */
	private $http;

	public function __construct(DavAccount $account, $service = 'caldav') {
		$this->account = $account;
		$this->service = $service;

		$this->http = new HttpClient('https://' . $this->account->host, [
			'Content-Type' => 'application/xml; charset=utf-8',
			'Authorization' => 'Basic ' . base64_encode($this->account->username . ':' . $this->account->password),
		]);
	}

	private function dnsResolve() {
		// fetch SRV record
		$s = 's'; // secure first
		$target = null;
		while ($target === null) {
			$result = dns_get_record("_{$this->service}$s._tcp.{$this->http->baseUri}", DNS_SRV | DNS_TXT);
			foreach ($result as $record) {
				if ($record['type'] === 'SRV' && ($target === null || $target['pri'] < $record['pri'])) {
					$host = $record['target'];
					$port = $record['port'];
					// $record['weight'] for picking chance when 'pri' is equal
					$target = $record;
				} else if ($record['type'] === 'TXT' && strpos($record['txt'], 'path=') === 0) {
					$path = substr($record['txt'], 5);
				}
			}
			if ($s === '') {
				break;
			}
			$s = '';
		}

		if (!isset($host)) {
			$data = $this->http->get("/.well-known/$this->service");
			$this->account->basePath = parse_url(rtrim($data['headers']['location'],'/'), PHP_URL_PATH).'/';
		}
	}

	// rfc6764
	public function serviceDiscovery() {

		$this->dnsResolve(); // or well-known
		$principalUri = $this->principalUri();

		return [
			'baseUri' => $this->http->baseUri,
			'principalHref' => $principalUri,
			'collections' => $this->collections($principalUri)
		];
	}
	public function sync() {
		if($this->account->collections === null) {
			$this->account->collections = [];
		}

		// fetch ctag for every calendar.
		$responses = $this->propfind([
			'd:sync-token',
			'cs:getctag'
		], $this->homeSetUri($this->account->principalUri), 1);

		// todo: delete calendars not in responses
		foreach ($responses as $href => $response) {
			$collection = $this->account->collections[$href];
			if(!empty($collection)) {
				// todo: add new calendar
			}
			if($collection->ctag !== (string)$response->getctag) {
				// resync
				$this->fetchEtags($collection);
			}
		}
		$this->account->lastSync = new \DateTime();
		$this->account->save();
		// fetch
	}

	public function put($event, $calendar) {

		$http = $this->http->setHeader('Content-Type', 'text/calendar; charset=utf-8');
		if(!empty($event->etag)) { // update
			$http->setHeader('If-Match', $event->etag);
		}
		if(empty($event->uri)) { // create
			$event->uri = $calendar->path . '/' . $event->uid . '.ics';
		}
		$http->PUT($event->uri, $event->toVObject());

		$newEtag = $http->responseHeaders('etag');
		if(empty($newEtag)) {
			// todo: issue another GET because the server has modified the object.
		} else {
			$event->etag = $newEtag;
		}

		return true;
	}

	private function fetchEtags($collection) {
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
		$body = $this->http
			->setHeader('Depth', 1)
			->setHeader('Prefer', 'return-minimal')
			->REPORT($collection->uri, $xml)
			->body();
		$responses = $this->parseMultiStatus($body);
		$events = CalendarEvent::find(['id','etag', 'uri'])->where('calendarId','=',$collection->calendarId);
		$create = [];
		$update = [];
		$delete = [];

		$perHref = [];
		foreach($events as $event) {
			if(!isset($responses[$event->uri()])) {
				// delete missing hrefs
				$delete[] = $event->id;
			} else {
				$perHref[$event->uri()] = $event;
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
			CalendarEvent::delete((new Query())->where('id', 'IN', $delete));
		// fetch changed and new
		if(!empty($update) || !empty($create)) {
			$this->fetchEvents($collection, $create, $update);
		}
	}

	public function fetchEvents($collection, $create = [], $update = []) {
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
		$body = $this->http
			->setHeader('Depth', 1)
			->setHeader('Prefer', 'return-minimal')
			->REPORT($collection->uri, $xml)
			->body();
		$responses = $this->parseMultiStatus($body);
		//$events = [];
		foreach ($responses as $href => $response) {
			if (isset($response->{'calendar-data'})) {
				if(in_array($href, $update)) // only
					$event = CalendarEvent::find()->where('uri', '=', $href)->single();
				if(empty($event)) {
					$event = new CalendarEvent();
					$event->calendarId = $collection->calendarId;
					$event->useDefaultAlerts = true;
				}
				$event = ICalendarHelper::parseVObject((string)$response->{'calendar-data'}, $event);
				$event->etag((string)$response->etag);
				$event->uri($href);
				if(!$event->save()){
					go()->log('cannot sync event '.print_r($event->getValidationErrors(), true));
				}
				$event = null;
				//$events[$href] = $event;
			}
		}
		//return $events;
	}
	protected function collections($principalUri) {
		$homeSetUri = $this->homeSetUri($principalUri);
		$responses = $this->propfind([
			$this->service === 'caldav' ? 'cal:supported-calendar-component-set' : 'card:supported-addressbook-component-set',
			'd:resourcetype',
			'd:displayname',
			'ical:calendar-order',
			'ical:calendar-color',
			'cal:calendar-timezone',
			'cal:calendar-description',
			'd:sync-token',
			'cs:getctag'
		], $homeSetUri, 1);

		$collections = [];
		foreach ($responses as $href => $response) {
//			if (isset($response->resourcetype->addressbook)) {
//				$collections[] = [
//					'href' => $href,
//					'ctag' => (string) $response->getctag,
//					'synctoken' => (string) $response->{'sync-token'},
//					'displayname' => (string) $response->displayname
//				];
//			}
			if (isset($response->resourcetype->calendar)) {
				$tz = null;
				if(isset($response->{'calendar-timezone'})) {
					preg_match('/TZID:(\w*\/\w*)/', (string)$response->{'calendar-timezone'}, $matches);
					if(isset($matches[1])) {
						$tz = $matches[1];
					}
				}
				$collections[] = [
					'uri' => $href,
					'ctag' => (string) $response->getctag,
					'synctoken' => (string) $response->{'sync-token'},
					'name' => (string) $response->displayname,
					'description' => (string) $response->{'calendar-description'},
					'color' => str_replace('#', '',(string) $response->{'calendar-color'}),
					'sortOrder' => (string) $response->{'calendar-order'},
					'timeZone' => $tz
				];
			}
		}
		return $collections;
	}

	protected function principalUri() {
		$prop = 'current-user-principal';
		$path = $this->account->basePath;
		$response = $this->propfind(['d:' . $prop], $path);
		if (!isset($response[$path]->{$prop})) {
			throw new \RangeException($prop . ' not found');
		}
		return (string) $response[$path]->{$prop}->href;
	}

	private function homeSetUri($principalUri) {
		$prop = 'calendar-home-set';
		$ns = 'cal';
		if ($this->service == 'carddav') {
			$prop = 'addressbook-home-set';
			$ns = 'card';
		}
		$response = $this->propfind([$ns . ':' . $prop], $principalUri);
		if (!isset($response[$principalUri]->{$prop})) {
			throw new \RangeException("No $prop found");
		}
		return (string) $response[$principalUri]->{$prop}->href;
	}

	private function parseMultiStatus($body) {
		// skip namespace complexity
		$xml = str_ireplace(['<d:','<cal:','<ical:','<cs:','<card:'], '<', $body);
		$xml = str_ireplace(['</d:','</cal:','</ical:','</cs:','</ard:'], '</', $xml);
		$multistatus = \simplexml_load_string($xml);
		$result = [];
		foreach ($multistatus->response as $response) {
			list(, $status, ) = explode(' ', $response->propstat->status, 3);
			if ($status == 200) {
				$result[(string) $response->href] = $response->propstat->prop;
			}
		}
		return $result;
	}

	private function propfind($props, $uri, $depth = 0) {
		$ns = [];
		foreach($props as $prop) {
			$i = substr($prop, 0, strpos($prop, ':'));
			$ns[$i] = $i.'="'.self::$xmlNs[$i.':'].'"';
		}
		unset($ns['d']);
		$ns = !empty($ns) ? ' xmlns:' . implode(' xmlns:', $ns) : '';
		$props = '<' . implode(' /><', $props) . ' />';

		$xml = "<d:propfind xmlns:d=\"DAV:\"$ns><d:prop>$props</d:prop></d:propfind>";
		$body = $this->http->setHeader('Depth', $depth)
			->PROPFIND($uri, $xml)->body();
		if ($this->http->statusCode() === 401) {
			throw new \RuntimeException('Dav Authentication failed');
		}
		if ($this->http->statusCode() === 403) {
			throw new \RuntimeException('Dav Forbidden');
		}
		if (!$body) {
			throw new \OutOfBoundsException('No response for: ' . $xml);
		}
		$response = $this->parseMultiStatus($body);
		if (count($response) === 0) {
			throw new \RangeException('No properties found: ' . $props);
		}
		return $response;
	}
}