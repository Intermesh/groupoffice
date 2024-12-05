<?php
namespace go\modules\community\davclient\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * Calendar entity
 *
 */
class DavAccount extends AclOwnerEntity {

	private static $xmlNs = [
		'd:' => "DAV:",
		'cs:' => "http://calendarserver.org/ns/",
		'cal:' => "urn:ietf:params:xml:ns:caldav",
		'card:' => "urn:ietf:params:xml:ns:carddav",
		'ical:' => "http://apple.com/ns/ical/",
	];
	/** @var HttpClient */
	private $http;
	private $service = 'caldav';

	const Cal = 'cal';
	const Card = 'card';

	public $id;
	public $active;
	public $host;
	public $username;
	public $password;
	public $basePath;
	public $principalUri;
	public $capabilities;
	public $name;
	public $refreshInterval;
	public $lastSync;
	public $lastError;
	/** @var Calendar[] the collections items with ctag and uri */
	public $collections = [];

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_davaccount", 'a')
			->addMap('collections', Calendar::class, ['id' => 'davaccountId']);
	}

	public function http() {
		if(empty($this->http)) {
			$this->http = new HttpClient('https://' . $this->host, [
				'Content-Type' => 'application/xml; charset=utf-8',
				'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
			]);
		}
		return $this->http;
	}

	public function byHref($href) {
		foreach($this->collections as $collection) {
			if($collection->uri === $href)
				return $collection;
		}
		return false;
	}

	public function byCalendar($id) {
		return $this->collections[$id];
	}

	public static function findByCalendarId($id) {
		return self::find()->join('davclient_calendar', 'c', 'c.davaccountId = a.id')
			->where('c.id', '=', $id)->single();
	}

	private function randomColor($seed) {
		srand(crc32($seed));
		$nb = rand(0,17);
		return substr('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1',
			($nb*7)+1,6);
	}

	private function isSetup() {
		return !empty($this->lastSync) && !empty($this->collections);
	}

	private function dnsResolve() {
		// fetch SRV record
		$s = 's'; // secure first
		$target = null;
		while ($target === null) {
			$result = dns_get_record("_{$this->service}$s._tcp.{$this->http()->baseUri}", DNS_SRV | DNS_TXT);
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
			$data = $this->http()->get("/.well-known/$this->service");
			$this->basePath = parse_url(rtrim($data['headers']['location'],'/'), PHP_URL_PATH).'/';
		}
	}

	// rfc6764
	private function serviceDiscovery() {

		$this->dnsResolve(); // or well-known
		$this->principalUri = $this->principalUri();

		// todo: remove if double
		$this->lastSync = new \DateTime();
		$this->save();
	}

	public function put($event) {
		$cal = $this->byCalendar($event->calendarId);
		// must exist
		return $cal->put($event);
	}

	public function sync() {
		if(!$this->isSetup()) {
			$this->serviceDiscovery();
		}
		$homesetUri = $this->homeSetUri($this->principalUri);

		go()->getDbConnection()->beginTransaction();

		$responses = $this->syncCollections($homesetUri);

		// fetch ctag for every calendar.
//		$responses = $this->propfind([
//			'd:sync-token',
//			'cs:getctag'
//		], $homesetUri, 1);

		// delete calendars not in responses
		$deletedCalendars = [];
		foreach($this->collections as $id => $calendar) {
			if(!array_key_exists($calendar->uri, $responses)) {
				// delete calendars no longer in response
				unset($this->collections[$id]);
				$deletedCalendars[] = $id;
			} else {
				$collection = $responses[$calendar->uri];
				if($calendar->isNew() || $calendar->ctag !== (string)$collection->getctag) {
					// resync
					$calendar->sync();
				}
				unset($responses[$calendar->uri]);
			}
		}

		$this->lastSync = new \DateTime();
		if(!$this->save()) {
			go()->log('Could not save dav account '. $homesetUri);
			go()->getDbConnection()->rollBack();
		} else if(!empty($deletedCalendars)) {
			\go\modules\community\calendar\model\Calendar::delete((new Query())->where('id','IN',$deletedCalendars));
		}
		go()->getDbConnection()->commit();
		// fetch
	}

	private function syncCollections($homesetUri) {
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
		], $homesetUri, 1);

		foreach ($responses as $href => $response) {
//			if (isset($response->resourcetype->addressbook)) {
//				$this->addAddressbook($href, $response);
//			}
			$isCalendar = false;
			if(isset($response->{'supported-calendar-component-set'})) {
				$isCalendar = (string)$response->{'supported-calendar-component-set'}->comp->attributes()->name === 'VEVENT';
			}

			if ($isCalendar) {
				$this->addCalendar($href, $response);
			} else {
				// $this->addTasklist()??
			}
		}
		return $responses;
	}

	/**
	 * @param string $uri href this calendar is coming from
	 * @param object $reponse parse XML response with calendar properties from caldav server
	 * @return Calendar|mixed
	 * @throws \Exception
	 */
	private function addCalendar($uri, $response)
	{
		$cal = $this->byHref($uri);

		if (empty($cal)) {
			$color = str_replace('#', '',(string) $response->{'calendar-color'});
			$order = (string) $response->{'calendar-order'};
			$tz = null;
			if(isset($response->{'calendar-timezone'})) {
				preg_match('/TZID:(\w*\/\w*)/', (string)$response->{'calendar-timezone'}, $matches);
				if(isset($matches[1])) {
					$tz = $matches[1];
				}
			}

			$model = new \go\modules\community\calendar\model\Calendar();
			$model->name = (string) $response->displayname;
			$model->description = (string) $response->{'calendar-description'};
			$model->sortOrder = is_numeric($order) ? (int)$order : 1;
			$model->color = !empty($color) ? $color : $this->randomColor($model->name);
			$model->timeZone = $tz;
			if ($model->save()) {
				$cal = new Calendar($this);
				$cal->id = $model->id;
				$cal->uri = $uri;
				$cal->ctag = (string) $response->getctag;
				$cal->synctoken = (string) $response->{'sync-token'};
				$this->collections[$cal->id] = $cal;
				//$cal->sync();
			} else {
				go()->log('Could not save Calendar '.print_r($model->getValidationErrors(),true));
			}
		}

		return $cal;
	}

	private function principalUri() {
		$prop = 'current-user-principal';
		$path = $this->basePath;
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
		$response = $this->http()->setHeader('Depth', $depth)
			->PROPFIND($uri, $xml)->parsedMultiStatus();

		if (count($response) === 0) {
			throw new \RangeException('No properties found: ' . $props);
		}
		return $response;
	}
}