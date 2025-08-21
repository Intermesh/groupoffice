<?php
namespace go\modules\community\davclient\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\model\Module;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\Crypt;

/**
 * Calendar entity
 *
 */
class DavAccount extends AclOwnerEntity {

	static $keepData = false;

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

	public ?string $id;
	public $active;
	public $host;
	public $username;
	protected $password;
	public $basePath;
	public $principalUri;
	public $capabilities;
	public $name;
	public $refreshInterval;
	public $lastSync;
	public $lastError;
	/** @var Calendar[] the collections items with ctag and uri */
	public $collections = [];

	public function setPassword($v) {
		$this->password = Crypt::encrypt($v);
	}
	public function decryptPassword(): string
	{
		return Crypt::decrypt($this->password);
	}
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_davaccount", 'a')
			->addMap('collections', Calendar::class, ['id' => 'davaccountId']);
	}

	protected function internalSave(): bool
	{
		if($this->isModified(['active', 'password', 'host', 'username']) && $this->active === true) {
			// when account is new or reactivated. Trigger discovery and resync homeset.
			if(!$this->sync()) {
				$this->active = false;
			}
		}
		return parent::internalSave();// maybe again for lastError and lastSync
	}

	public function needsSync() {

		$needSync = new \DateTime();
		$needSync->sub(new \DateInterval('PT'.$this->refreshInterval.'M'));

		return $this->lastSync < $needSync; // longer then [interval] minutes ago
	}

	protected function canCreate(): bool
	{
		return Module::findByName('community', 'davclient')
			->getUserRights()->mayManage;
	}

	public function http() {
		if(empty($this->http)) {
			$proto = substr($this->host, -2, 2) === '80' ? 'http://' : 'https://';
			$this->http = new HttpClient($proto . $this->host, [
				'Content-Type' => 'application/xml; charset=utf-8',
				'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->decryptPassword()),
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

	private function isSetup() {
		return !empty($this->lastSync) && !empty($this->collections);
	}

	protected static function internalDelete(Query $query): bool
	{
		$calIDs = \go\modules\community\calendar\model\Calendar::find()->selectSingleValue('calendar_calendar.id')
			->join('davclient_calendar','d', 'd.id = calendar_calendar.id')
			->where(['d.davaccountId' => $query])->all();

		$ok = parent::internalDelete($query);
		if($ok && !self::$keepData && \go\core\Module::isAllowed('calendar', 'community')) {
			if(!empty($calIDs)) {
				// remove the calendars after
				if (!\go\modules\community\calendar\model\Calendar::delete(['id' => $calIDs])) {
					$ok = false;
					throw new \Exception("Unable to delete calendars related to dav account");
				}
			}

		}
		return $ok;
	}

	private function serviceDiscovery() {
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
			// Thunderbird will do HEAD /, GET /, PROPFIND .well-known
			$data = $this->http()->get("/.well-known/$this->service");
			if(isset($data['headers']['location'])) {
				$this->basePath = parse_url(rtrim($data['headers']['location'], '/'), PHP_URL_PATH) . '/';
				$this->principalUri = $this->principalUri();
			} else {
				$responses = $this->propfind(['d:current-user-principal'], "/.well-known/$this->service");
				foreach ($responses as $href => $response) {
					if (isset($response->{'current-user-principal'})) {
						$this->principalUri = $href;
						return;
					}
				}
				throw new \Exception("Could not find principalUri");
			}
		}
	}

	// rfc6764
	public function put($event) {
		$cal = $this->byCalendar($event->calendarId);
		// must exist
		return $cal->put($event);
	}

	public function remove($event) {
		// calendar and uri is needed
		$cal = $this->byCalendar($event->calendarId);
		// calendar must exist
		return $cal->remove($event);
	}

	public function sync($syncItems = false) {
		try {
			$this->lastError = '';
			if($this->isNew()) {
				parent::internalSave();
			}
//		if(!$this->isSetup()) {
			$this->serviceDiscovery();
//		}
			$homesetUri = $this->homeSetUri($this->principalUri);
			$responses = $this->syncCollections($homesetUri);

			// fetch ctag for every calendar.
//		$responses = $this->propfind([
//			'd:sync-token',
//			'cs:getctag'
//		], $homesetUri, 1);

			// delete calendars not in responses
			$deletedCalendars = [];
			foreach ($this->collections as $id => $calendar) {
				if (!array_key_exists($calendar->uri, $responses)) {
					// delete calendars no longer in response
					unset($this->collections[$id]);
					$deletedCalendars[] = $id;
				} else if ($syncItems) {
					$collection = $responses[$calendar->uri];
					if ($calendar->isNew() || $calendar->ctag !== (string)$collection->getctag) {
						// resync
						go()->getDbConnection()->beginTransaction();
						go()->log('Synchronizing ' . $calendar->uri . ' ctag mismatch [' . $calendar->ctag . ' != ' . $collection->getctag . ']');
						if ($calendar->sync()) {
							$calendar->ctag = (string)$collection->getctag;
						}
						go()->getDbConnection()->commit();
					}
					unset($responses[$calendar->uri]);
				}
			}

			$this->lastSync = new \DateTime();
//			if(!$this->save()) {
//				go()->log('Could not save last sync '. $homesetUri);
//				//go()->getDbConnection()->rollBack();
//			} else
			if (!empty($deletedCalendars)) {
				\go\modules\community\calendar\model\Calendar::delete((new Query())->where('id', 'IN', $deletedCalendars));
			}
			return true;
		} catch(\Exception $e) {
			$this->lastError = $e->getMessage();
		}
		return false;
	}

	public function syncCollection(Calendar $calendar) {

		$responses = $this->propfind([
			$this->service === 'caldav' ? 'cal:supported-calendar-component-set' : 'card:supported-addressbook-component-set',
			'd:sync-token',
			'cs:getctag'
		], $calendar->uri, 0);

		$collection = $responses[$calendar->uri];


		if($calendar->ctag !== (string)$collection->getctag) {
			// resync
			go()->getDbConnection()->beginTransaction();
			go()->log('Synchronizing '. $calendar->uri. ' ctag mismatch ['. $calendar->ctag .' != '. $collection->getctag.']');
			if($calendar->sync()) {
				$calendar->ctag = (string)$collection->getctag;
			}
			go()->getDbConnection()->commit();
		} else {
			$calendar->lastSync = new \DateTime(); // nothing to do
		}
		return $this->save();
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

		$calendars = [];

		foreach ($responses as $href => $response) {
//			if (isset($response->resourcetype->addressbook)) {
//				$this->addAddressbook($href, $response);
//			}
			$isCalendar = false;
			if(isset($response->resourcetype->calendar)) {
				$isCalendar = true;
				if(isset($response->{'supported-calendar-component-set'})) {
					$isCalendar = (string)$response->{'supported-calendar-component-set'}->comp->attributes()->name === 'VEVENT';
				}
			}

			if ($isCalendar) {
				$this->addCalendar($href, $response);
				$calendars[$href] = $response;
			} else {
				// $this->addTasklist()??
			}
		}
		return $calendars;
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
		$color = str_replace('#', '',(string) $response->{'calendar-color'});
		$order = (string) $response->{'calendar-order'};
		if (empty($cal)) {
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
			$model->color = !empty($color) ? $color : \go\modules\community\calendar\model\Calendar::randomColor($model->name);
			$model->timeZone = $tz;
			if ($model->save()) {
				$cal = new Calendar($this);
				$cal->id = $model->id;
				$cal->uri = $uri;
				$cal->ctag = '';// (string) $response->getctag;
				$cal->synctoken = '' ;// (string) $response->{'sync-token'}; ( not supported at the moment)
				$this->collections[$cal->id] = $cal;
				//$cal->sync();
			} else {
				go()->log('Could not save Calendar '.print_r($model->getValidationErrors(),true));
			}
		} else {
			// update name, description, order and color
			$model = \go\modules\community\calendar\model\Calendar::findById($cal->id);
			$model->name = (string) $response->displayname;
			$model->description = (string) $response->{'calendar-description'};
			$model->sortOrder = is_numeric($order) ? (int)$order : 1;
			$model->color = !empty($color) ? $color : \go\modules\community\calendar\model\Calendar::randomColor($model->name);
			$model->save();
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
			throw new \RangeException('No properties found: ' . $props . ' at: '.$this->http()->baseUri);
		}
		return $response;
	}
}