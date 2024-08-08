<?php
namespace go\modules\community\davclient\model;


class DavSynchronizer {

	private static $xmlNs = [
		'd' => "DAV:",
		'cs' => "http://calendarserver.org/ns/",
		'cal' => "urn:ietf:params:xml:ns:caldav",
		'card' => "urn:ietf:params:xml:ns:carddav",
		'ical' => "http://apple.com/ns/ical/",
	];
	private $account;
	private $http;

	public function __construct($account, $service = 'caldav') {
		$this->account = $account;
		$this->service = $service;
		$details = $this->account->connectionDetails;

		$this->http = new HttpClient($details->uri, [
			'Content-Type' => 'application/xml; charset=utf-8',
			'Authorization' => 'Basic ' . base64_encode($details['user'] . ':' . $details['pass']),
		]);
	}

	private function dnsResolve() {
		// fetch SRV record
		$s = 's'; // secure first
		$path = '';
		$target = null;
		$port = 443;
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
			$host = $this->http->baseUri;
			$path = "/.well-known/$this->service";
		}
		$proto = ($port === 443) ? 'https://' : 'http://';
		$uri = $proto . $host . ':' . $port . $path;
		$this->http->baseUri = $uri;
	}

	// rfc6764
	public function serviceDiscovery() {

		$this->dnsResolve(); // or well-known
		$principalUri = $this->principalUri();
		$homeSetUri = $this->homeSetUri($principalUri);
		$colections = $this->collections($homeSetUri);

		return [
			'baseUri' => $this->http->baseUri,
			'principalHref' => $principalUri,
			'homeSetUri' => $homeSetUri,
			'collections' => $colections
		];
	}
	public function sync() {

	}

	protected function collections($homeSetUri) {
		$responses = $this->propfind([
			$this->service === 'caldav' ? 'cal:supported-calendar-component-set' : 'card:supported-addressbook-component-set',
			'd:resourcetype',
			'd:displayname',
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
				$collections[] = [
					'href' => $href,
					'ctag' => (string) $response->getctag,
					'synctoken' => (string) $response->{'sync-token'},
					'displayname' => (string) $response->displayname
				];
			}
		}
		return $collections;
	}

	protected function principalUri() {
		$prop = 'current-user-principal';
		$response = $this->propfind(['d:' . $prop], '/');
		if (!isset($response['/']->{$prop})) {
			throw new \RangeException($prop . ' not found');
		}
		return (string) $response['/']->{$prop}->href;
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
		$multistatus = new \SimpleXMLElement($body);
		$result = [];
		foreach ($multistatus->response as $response) {
			list(, $status, ) = explode(' ', $response->propstat->status, 3);
			if ($status == 200) {
				$result[(string) $response->href] = $response->propstat->prop;
			}
		}
		return $result;
	}

	public function fetchEvents($calUri) {
		$xml = <<<XML
<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
    <d:prop>
        <d:getetag />
        <c:calendar-data />
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
			->REPORT($calUri, $xml)
			->body();
		$responses = $this->parseMultiStatus($body);
		$fetched = [];
		foreach ($responses as $href => $response) {
			if (isset($response->{'calendar-data'})) {
				$fetched[$href] = $response->{'calendar-data'};
			}
		}
		return $fetched;
	}
	private function propfind($props, $uri, $depth = 0) {
		$ns = array_filter(array_map(function($value) {
			$i = substr($value, 0, strpos($value, ':'));
			if ($i === 'd')
				return '';
			return $i . '="' . self::$xmlNs[$i] . '"';
		}, $props));

		$props = '<' . implode(' /><', $props) . ' />';
		$ns = !empty($ns) ? ' xmlns:' . implode(' xmlns:', $ns) : '';
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