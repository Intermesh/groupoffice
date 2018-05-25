<?php
/**
* A Class for connecting to a caldav server
*
* Based on caldav-client-v2.php by Andrew McMillan <andrew@mcmillan.net.nz>
* but using cURL instead of home-brew request construction. cURL code re-used
* from carddav.php by Jean-Louis Dupond. Additional bugfixes to
* caldav-client-v2.php by xbgmsharp <xbgmsharp@gmail.com>.
*
* Copyright Andrew McMillan (original caldav-client-v2.php), Jean-Louis Dupond (cURL code), xbgmsharp (bugfixes)
* Copyright Thorsten Köster
* License   GNU LGPL version 3 or later (http://www.gnu.org/licenses/lgpl-3.0.txt)
*/

require_once('XMLDocument.php');

/**
* A class for holding basic calendar information
*/
class CalendarInfo {
	public $url;
	public $displayname;
	public $getctag;
	public $id;

	function __construct( $url, $displayname = null, $getctag = null, $id = null ) {
		$this->url = $url;
		$this->displayname = $displayname;
		$this->getctag = $getctag;
		$this->id = $id;
	}

	function __toString() {
		return( '(URL: '.$this->url.'   Ctag: '.$this->getctag.'   Displayname: '.$this->displayname .')'. "\n" );
	}
}


/**
* A class for accessing DAViCal via CalDAV, as a client
*
* @package   awl
*/
class CalDAVClient {
	/**
	* Server, username, password, calendar
	*
	* @var string
	*/
	protected $server, $base_url, $user, $pass, $auth;

	/**
	* The principal-URL we're using
	*/
	protected $principal_url;

	/**
	* The calendar-URL we're using
	*/
	protected $calendar_url;

	/**
	* The calendar-home-set we're using
	*/
	protected $calendar_home_set;

	/**
	* The calendar_urls we have discovered
	*/
	protected $calendar_urls;

	/**
	* Construct URL
	*/
	protected $url;

	/**
	* The useragent which is send to the caldav server
	*
	* @var string
	*/
	const USERAGENT = 'ModifiedDAViCalClient';

	protected $headers = array();
	protected $xmlResponse = "";  // xml received
	protected $httpResponseCode = 0; // http response code
	protected $httpResponseHeaders = "";
	protected $httpResponseBody = "";

	protected $parser; // our XML parser object

	/**
	 * CardDAV server connection (curl handle)
	 *
	 * @var	resource
	 */
	private $curl = false;

    private $synctoken = array();

	/**
	* Constructor, initialises the class
	*
	* @param string $caldav_url  The URL for the calendar server
	* @param string $user        The name of the user logging in
	* @param string $pass        The password for that user
	*/
	function __construct( $caldav_url, $user, $pass ) {
        $this->url = $caldav_url;
		$this->user = $user;
		$this->pass = $pass;
		$this->auth = $user . ':' . $pass;
		$this->headers = array();

		$parsed_url = parse_url($caldav_url);
		if ($parsed_url === false) {
			ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCalDAV->caldav_backend(): Couldn't parse URL: %s", $caldav_url));
            return;
		}

		$this->server = $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'];
		$this->base_url  = $parsed_url['path'];
		//ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->caldav_backend(): base_url '%s'", $this->base_url));
        //$this->base_url .= !empty($parsed_url['query'])    ? '?' . $parsed_url['query']    : '';
        //$this->base_url .= !empty($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        if (substr($this->base_url, -1) !== '/') {
			$this->base_url = $this->base_url . '/';
		}
	}

	/**
     * Checks if the CalDAV server is reachable
     *
     * @return  boolean
     */
    public function CheckConnection() {
        $result = $this->DoRequest($this->url, 'OPTIONS');

        switch ($this->httpResponseCode) {
            case 200:
            case 207:
            case 401:
                $status = true;
                break;
            default:
                $status = false;
        }

        return $status;
    }

    /**
     * Disconnect curl connection
     *
     */
    public function Disconnect() {
        if ($this->curl !== false) {
            curl_close($this->curl);
            $this->curl = false;
        }
    }


	/**
	* Adds an If-Match or If-None-Match header
	*
	* @param bool $match to Match or Not to Match, that is the question!
	* @param string $etag The etag to match / not match against.
	*/
	function SetMatch( $match, $etag = '*' ) {
		$this->headers['match'] = sprintf( "%s-Match: \"%s\"", ($match ? "If" : "If-None"), trim($etag,'"'));
	}

	/**
	* Add a Depth: header.  Valid values are 0, 1 or infinity
	*
	* @param int $depth  The depth, default to infinity
	*/
	function SetDepth( $depth = '0' ) {
		$this->headers['depth'] = 'Depth: '. ($depth == '1' ? "1" : ($depth == 'infinity' ? $depth : "0") );
	}

	/**
	* Set the calendar_url we will be using for a while.
	*
	* @param string $url The calendar_url
	*/
	function SetCalendar( $url ) {
		$this->calendar_url = $url;
	}

	/**
	* Split response into httpResponse and xmlResponse
	*
	* @param string Response from server
	*/
	function ParseResponse( $response ) {
		$pos = strpos($response, '<?xml');
		if ($pos !== false) {
			$this->xmlResponse = trim(substr($response, $pos));
			$this->xmlResponse = preg_replace('{>[^>]*$}s', '>',$this->xmlResponse );
			$parser = xml_parser_create_ns('UTF-8');
			xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );
			xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, 0 );

			if ( xml_parse_into_struct( $parser, $this->xmlResponse, $this->xmlnodes, $this->xmltags ) === 0 ) {
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("XML parsing error: %s - %s", xml_get_error_code($parser), xml_error_string(xml_get_error_code($parser))));
//				debug_print_backtrace();
//				echo "\nNodes array............................................................\n"; print_r( $this->xmlnodes );
//				echo "\nTags array............................................................\n";  print_r( $this->xmltags );
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("XML Reponse:\n%s\n", $this->xmlResponse));
			}

			xml_parser_free($parser);
		}
	}


	public function curl_init() {
		if ($this->curl === false) {
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_HEADER, true);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_USERAGENT, self::USERAGENT);

			if ($this->auth !== null) {
				curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($this->curl, CURLOPT_USERPWD, $this->auth);
			}
		}
	}


	/**
	* Send a request to the server
	*
	* @param string $url The URL to make the request to
	*
	* @return string The content of the response from the server
	*/
	function DoRequest($url, $method, $content = null, $content_type = "text/plain") {
		$this->curl_init();

		if ( !isset($url) ) $url = $this->base_url;
		$url = preg_replace('{^https?://[^/]+}', '', $url);
		$url = $this->server . $url;

		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30); // 30 seconds it's already too big

		if ($content !== null)
		{
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $content);
		}
		else
		{
			curl_setopt($this->curl, CURLOPT_POST, false);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, null);
		}

		$headers = array();
		$headers['content-type'] = 'Content-type: ' . $content_type;
		foreach( $this->headers as $ii => $head ) {
		  $headers[$ii] = $head;
		}
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

		$this->xmlResponse = '';

// 		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Request:\n%s\n", $content));
		$response					= curl_exec($this->curl);
// 		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Reponse:\n%s\n", $response));
		$header_size				= curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		$this->httpResponseCode		= curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		$this->httpResponseHeaders	= trim(substr($response, 0, $header_size));
		$this->httpResponseBody		= substr($response, $header_size);

		$this->headers = array();  // reset the headers array for our next request
		$this->ParseResponse($this->httpResponseBody);
		return $response;
	}


	/**
	* Send an OPTIONS request to the server
	*
	* @param string $url The URL to make the request to
	*
	* @return array The allowed options
	*/
	function DoOptionsRequest( $url = null ) {
		$headers = $this->DoRequest($url === null ? $this->url : $url, "OPTIONS");
		$options_header = preg_replace( '/^.*Allow: ([a-z, ]+)\r?\n.*/is', '$1', $headers );
		$options = array_flip( preg_split( '/[, ]+/', $options_header ));
		return $options;
	}


	/**
	* Send an XML request to the server (e.g. PROPFIND, REPORT, MKCALENDAR)
	*
	* @param string $method The method (PROPFIND, REPORT, etc) to use with the request
	* @param string $xml The XML to send along with the request
	* @param string $url The URL to make the request to
	*
	* @return array An array of the allowed methods
	*/
	function DoXMLRequest( $request_method, $xml, $url = null ) {
		return $this->DoRequest($url, $request_method, $xml, "text/xml");
	}


	/**
	* Get a single item from the server.
	*
	* @param string $url The URL to GET
	*/
	function DoGETRequest( $url ) {
		return $this->DoRequest($url, "GET");
	}


	/**
	* Get the HEAD of a single item from the server.
	*
	* @param string $url The URL to HEAD
	*/
	function DoHEADRequest( $url ) {
		return $this->DoRequest($url, "HEAD");
	}


	/**
	* PUT a text/icalendar resource, returning the etag
	*
	* @param string $url The URL to make the request to
	* @param string $icalendar The iCalendar resource to send to the server
	* @param string $etag The etag of an existing resource to be overwritten, or '*' for a new resource.
	*
	* @return string The content of the response from the server
	*/
	function DoPUTRequest( $url, $icalendar, $etag = null ) {
		if ( $etag != null ) {
		  $this->SetMatch( ($etag != '*'), $etag );
		}
		$this->DoRequest($url, "PUT", $icalendar, 'text/calendar; encoding="utf-8"');

		$etag = null;
		if ( preg_match( '{^ETag:\s+"([^"]*)"\s*$}im', $this->httpResponseHeaders, $matches ) ) {
			$etag = $matches[1];
		}
		if ( !isset($etag) || $etag == '' ) {
			ZLog::Write(LOGLEVEL_DEBUG, sprintf("No etag in:\n%s\n", $this->httpResponseHeaders));
			$save_response_headers = $this->httpResponseHeaders;
			$this->DoHEADRequest( $url );
			if ( preg_match( '{^Etag:\s+"([^"]*)"\s*$}im', $this->httpResponseHeaders, $matches ) ) {
				$etag = $matches[1];
			}
			if ( !isset($etag) || $etag == '' ) {
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("Still No etag in:\n%s\n", $this->httpResponseHeaders));
			}
			$this->httpResponseHeaders = $save_response_headers;
		}
		return $etag;
	}


	/**
	* DELETE a text/icalendar resource
	*
	* @param string $url The URL to make the request to
	* @param string $etag The etag of an existing resource to be deleted, or '*' for any resource at that URL.
	*
	* @return int The HTTP Result Code for the DELETE
	*/
	function DoDELETERequest( $url, $etag = null ) {
		if ( $etag != null ) {
			$this->SetMatch( true, $etag );
		}
		$this->DoRequest($url, "DELETE");
		return $this->httpResponseCode;
	}


	/**
	* Get a single item from the server.
	*
	* @param string $url The URL to PROPFIND on
	*/
	function DoPROPFINDRequest( $url, $props, $depth = 0 ) {
		$this->SetDepth($depth);
		$xml = new XMLDocument( array( 'DAV:' => '', 'urn:ietf:params:xml:ns:caldav' => 'C' ) );
		$prop = new XMLElement('prop');
		foreach( $props AS $v ) {
			$xml->NSElement($prop,$v);
		}

		$this->DoRequest($url, "PROPFIND", $xml->Render('propfind',$prop), "text/xml");
		return $this->xmlResponse;
	}


	/**
	* Get/Set the Principal URL
	*
	* @param $url string The Principal URL to set
	*/
	function PrincipalURL( $url = null ) {
		if ( isset($url) ) {
			$this->principal_url = $url;
		}
		return $this->principal_url;
	}


	/**
	* Get/Set the calendar-home-set URL
	*
	* @param $url array of string The calendar-home-set URLs to set
	*/
	function CalendarHomeSet( $urls = null ) {
		if ( isset($urls) ) {
			if ( !is_array($urls) ) {
				$urls = array($urls);
			}
			$this->calendar_home_set = $urls;
		}
		return $this->calendar_home_set;
	}


	/**
	* Get/Set the calendar-home-set URL
	*
	* @param $urls array of string The calendar URLs to set
	*/
	function CalendarUrls( $urls = null ) {
		if ( isset($urls) ) {
			if ( !is_array($urls) ) {
				$urls = array($urls);
			}
			$this->calendar_urls = $urls;
		}
		return $this->calendar_urls;
	}


	/**
	* Return the first occurrence of an href inside the named tag.
	*
	* @param string $tagname The tag name to find the href inside of
	*/
	function HrefValueInside( $tagname ) {
		foreach( $this->xmltags[$tagname] AS $k => $v ) {
			$j = $v + 1;
			if ( $this->xmlnodes[$j]['tag'] == 'DAV::href' ) {
				return rawurldecode($this->xmlnodes[$j]['value']);
			}
		}
		return null;
	}


	/**
	* Return the href containing this property.  Except only if it's inside a status != 200
	*
	* @param string $tagname The tag name of the property to find the href for
	* @param integer $which Which instance of the tag should we use
	*/
	function HrefForProp( $tagname, $i = 0 ) {
		if ( isset($this->xmltags[$tagname]) && isset($this->xmltags[$tagname][$i]) ) {
			$j = $this->xmltags[$tagname][$i];
			while( $j-- > 0 && $this->xmlnodes[$j]['tag'] != 'DAV::href' ) {
//				printf( "Node[$j]: %s\n", $this->xmlnodes[$j]['tag']);
				if ( $this->xmlnodes[$j]['tag'] == 'DAV::status' && $this->xmlnodes[$j]['value'] != 'HTTP/1.1 200 OK' ) {
					return null;
				}
			}
//			printf( "Node[$j]: %s\n", $this->xmlnodes[$j]['tag']);
			if ( $j > 0 && isset($this->xmlnodes[$j]['value']) ) {
//				printf( "Value[$j]: %s\n", $this->xmlnodes[$j]['value']);
				return rawurldecode($this->xmlnodes[$j]['value']);
			}
		}
		else {
			ZLog::Write(LOGLEVEL_DEBUG, sprintf("xmltags[$tagname] or xmltags[$tagname][$i] is not set."));
		}
		return null;
	}


	/**
	* Return the href which has a resourcetype of the specified type
	*
	* @param string $tagname The tag name of the resourcetype to find the href for
	* @param integer $which Which instance of the tag should we use
	*/
	function HrefForResourcetype( $tagname, $i = 0 ) {
		if ( isset($this->xmltags[$tagname]) && isset($this->xmltags[$tagname][$i]) ) {
			$j = $this->xmltags[$tagname][$i];
			while( $j-- > 0 && $this->xmlnodes[$j]['tag'] != 'DAV::resourcetype' );
			if ( $j > 0 ) {
				while( $j-- > 0 && $this->xmlnodes[$j]['tag'] != 'DAV::href' );
				if ( $j > 0 && isset($this->xmlnodes[$j]['value']) ) {
					return rawurldecode($this->xmlnodes[$j]['value']);
				}
			}
		}
		return null;
	}


	/**
	* Return the <prop> ... </prop> of a propstat where the status is OK
	*
	* @param string $nodenum The node number in the xmlnodes which is the href
	*/
	function GetOKProps( $nodenum ) {
		$props = null;
		$level = $this->xmlnodes[$nodenum]['level'];
		$status = '';
		while ( $this->xmlnodes[++$nodenum]['level'] >= $level ) {
			if ( $this->xmlnodes[$nodenum]['tag'] == 'DAV::propstat' ) {
				if ( $this->xmlnodes[$nodenum]['type'] == 'open' ) {
					$props = array();
					$status = '';
				} else {
					if ( $status == 'HTTP/1.1 200 OK' ) {
						break;
					}
				}
			} elseif ( !isset($this->xmlnodes[$nodenum]) || !is_array($this->xmlnodes[$nodenum]) ) {
				break;
			} elseif ( $this->xmlnodes[$nodenum]['tag'] == 'DAV::status' ) {
				$status = $this->xmlnodes[$nodenum]['value'];
			} else {
				$props[] = $this->xmlnodes[$nodenum];
			}
		}
		return $props;
	}


	/**
	* Attack the given URL in an attempt to find a principal URL
	*
	* @param string $url The URL to find the principal-URL from
	*/
	function FindPrincipal( $url=null ) {
		$xml = $this->DoPROPFINDRequest( $url, array('resourcetype', 'current-user-principal', 'owner', 'principal-URL', 'urn:ietf:params:xml:ns:caldav:calendar-home-set'), 1);

		$principal_url = $this->HrefForProp('DAV::principal');

		if ( !isset($principal_url) ) {
			foreach( array('DAV::current-user-principal', 'DAV::principal-URL', 'DAV::owner') AS $href ) {
				if ( !isset($principal_url) ) {
					$principal_url = $this->HrefValueInside($href);
				}
			}
		}

		return $this->PrincipalURL($principal_url);
	}


	/**
	* Attack the given URL in an attempt to find a principal URL
	*
	* @param string $url The URL to find the calendar-home-set from
	*/
	function FindCalendarHome( $recursed=false ) {
		if ( !isset($this->principal_url) ) {
			$this->FindPrincipal();
		}
		if ( $recursed ) {
			$this->DoPROPFINDRequest( $this->principal_url, array('urn:ietf:params:xml:ns:caldav:calendar-home-set'), 0);
		}

		$calendar_home = array();
		foreach( $this->xmltags['urn:ietf:params:xml:ns:caldav:calendar-home-set'] AS $k => $v ) {
			if ( $this->xmlnodes[$v]['type'] != 'open' ) {
				continue;
			}
			while( $this->xmlnodes[++$v]['type'] != 'close' && $this->xmlnodes[$v]['tag'] != 'urn:ietf:params:xml:ns:caldav:calendar-home-set' ) {
//				printf( "Tag: '%s' = '%s'\n", $this->xmlnodes[$v]['tag'], $this->xmlnodes[$v]['value']);
				if ( $this->xmlnodes[$v]['tag'] == 'DAV::href' && isset($this->xmlnodes[$v]['value']) ) {
					$calendar_home[] = rawurldecode($this->xmlnodes[$v]['value']);
				}
			}
		}

		if ( !$recursed && count($calendar_home) < 1 ) {
			$calendar_home = $this->FindCalendarHome(true);
		}

		return $this->CalendarHomeSet($calendar_home);
	}


	/**
	* Find the calendars, from the calendar_home_set
	*/
	function FindCalendars( $recursed=false ) {
		if ( !isset($this->calendar_home_set[0]) ) {
			$this->FindCalendarHome();
		}
		$this->DoPROPFINDRequest( $this->calendar_home_set[0], array('resourcetype','displayname','http://calendarserver.org/ns/:getctag'), 1);

		$calendars = array();
		if ( isset($this->xmltags['urn:ietf:params:xml:ns:caldav:calendar']) ) {
			$calendar_urls = array();
			foreach( $this->xmltags['urn:ietf:params:xml:ns:caldav:calendar'] AS $k => $v ) {
				$calendar_urls[$this->HrefForProp('urn:ietf:params:xml:ns:caldav:calendar', $k)] = 1;
			}

			foreach( $this->xmltags['DAV::href'] AS $i => $hnode ) {
				$href = rawurldecode($this->xmlnodes[$hnode]['value']);

				if ( !isset($calendar_urls[$href]) ) {
					continue;
				}

//				printf("Seems '%s' is a calendar.\n", $href );

				$calendar = new CalendarInfo($href);
				$ok_props = $this->GetOKProps($hnode);
				foreach( $ok_props AS $v ) {
//					printf("Looking at: %s[%s]\n", $href, $v['tag'] );
					switch( $v['tag'] ) {
						case 'http://calendarserver.org/ns/:getctag':
							$calendar->getctag = $v['value'];
							break;
						case 'DAV::displayname':
							$calendar->displayname = $v['value'];
							break;
					}
				}
				$calendar->id = rtrim(str_replace($this->calendar_home_set[0], "", $calendar->url), "/");
				$calendars[] = $calendar;
			}
		}

		return $this->CalendarUrls($calendars);
	}


	/**
	* Find the calendars, from the calendar_home_set
	*/
	function GetCalendarDetails( $url = null ) {
		if ( isset($url) ) {
			$this->SetCalendar($url);
		}
		if ( !isset($this->calendar_home_set[0]) ) {
			$this->FindCalendarHome();
		}

		$calendar_properties = array( 'resourcetype', 'displayname', 'http://calendarserver.org/ns/:getctag', 'urn:ietf:params:xml:ns:caldav:calendar-timezone', 'supported-report-set' );
		$this->DoPROPFINDRequest( $this->calendar_url, $calendar_properties, 0);

		$hnode = $this->xmltags['DAV::href'][0];
		$href = rawurldecode($this->xmlnodes[$hnode]['value']);

		$calendar = new CalendarInfo($href);
		$ok_props = $this->GetOKProps($hnode);
		foreach( $ok_props AS $k => $v ) {
			$name = preg_replace( '{^.*:}', '', $v['tag'] );
			if ( isset($v['value'] ) ) {
				$calendar->{$name} = $v['value'];
			} /* else {
				printf( "Calendar property '%s' has no text content\n", $v['tag'] );
			}*/
		}
    	$calendar->id = rtrim(str_replace($this->calendar_home_set[0], "", $calendar->url), "/");

		return $calendar;
	}


	/**
	* Get all etags for a calendar
	*/
	function GetCollectionETags( $url = null ) {
		if ( isset($url) ) {
			$this->SetCalendar($url);
		}

		$this->DoPROPFINDRequest( $this->calendar_url, array('getetag'), 1);

		$etags = array();
		if ( isset($this->xmltags['DAV::getetag']) ) {
			foreach( $this->xmltags['DAV::getetag'] AS $k => $v ) {
				$href = $this->HrefForProp('DAV::getetag', $k);
				if ( isset($href) && isset($this->xmlnodes[$v]['value']) ) {
					$etags[$href] = $this->xmlnodes[$v]['value'];
				}
			}
		}

		return $etags;
	}


	/**
	* Get a bunch of events for a calendar with a calendar-multiget report
	*/
	function CalendarMultiget( $event_hrefs, $url = null ) {
		if ( isset($url) ) {
			$this->SetCalendar($url);
		}

		$hrefs = '';
		foreach( $event_hrefs AS $k => $href ) {
			$href = str_replace( rawurlencode('/'),'/',rawurlencode($href));
			$hrefs .= '<href>'.$href.'</href>';
		}
		$body = <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<C:calendar-multiget xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
<prop><getetag/><C:calendar-data/></prop>
$hrefs
</C:calendar-multiget>
EOXML;

		$this->DoRequest($this->calendar_url, "REPORT", $body, "text/xml");

		$events = array();
		if ( isset($this->xmltags['urn:ietf:params:xml:ns:caldav:calendar-data']) ) {
			foreach( $this->xmltags['urn:ietf:params:xml:ns:caldav:calendar-data'] AS $k => $v ) {
				$href = $this->HrefForProp('urn:ietf:params:xml:ns:caldav:calendar-data', $k);
//				echo "Calendar-data:\n"; print_r($this->xmlnodes[$v]);
				$events[$href] = $this->xmlnodes[$v]['value'];
			}
		} else {
			foreach( $event_hrefs AS $k => $href ) {
				$this->DoGETRequest($href);
				$events[$href] = $this->httpResponseBody;
			}
		}

		return $events;
	}


	/**
	* Given XML for a calendar query, return an array of the events (/todos) in the
	* response.  Each event in the array will have a 'href', 'etag' and '$response_type'
	* part, where the 'href' is relative to the calendar and the '$response_type' contains the
	* definition of the calendar data in iCalendar format.
	*
	* @param string $filter XML fragment which is the <filter> element of a calendar-query
	* @param string $url The URL of the calendar, or empty/null to use the 'current' calendar_url
	*
	* @return array An array of the relative URLs, etags, and events from the server.  Each element of the array will
	*               be an array with 'href', 'etag' and 'data' elements, corresponding to the URL, the server-supplied
	*               etag (which only varies when the data changes) and the calendar data in iCalendar format.
	*/
	function DoCalendarQuery( $filter, $url = null ) {
		if ( !empty($url) ) {
			$this->SetCalendar($url);
		}

		$body = <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
  <D:prop>
    <C:calendar-data/>
    <D:getetag/>
  </D:prop>
  $filter
</C:calendar-query>
EOXML;

        $this->SetDepth(1);
		$this->DoRequest($this->calendar_url, "REPORT", $body, "text/xml");

		$report = array();
		foreach( $this->xmlnodes as $k => $v ) {
			switch( $v['tag'] ) {
				case 'DAV::response':
					if ( $v['type'] == 'open' ) {
						$response = array();
					} elseif ( $v['type'] == 'close' ) {
						$report[] = $response;
					}
					break;
				case 'DAV::href':
					$response['href'] = basename( rawurldecode($v['value']) );
					break;
				case 'DAV::getetag':
					$response['etag'] = preg_replace('/^"?([^"]+)"?/', '$1', $v['value']);
					break;
				case 'urn:ietf:params:xml:ns:caldav:calendar-data':
					$response['data'] = $v['value'];
					break;
			}
		}
		return $report;
	}


	/**
	* Get the events in a range from $start to $finish.  The dates should be in the
	* format yyyymmddThhmmssZ and should be in GMT.  The events are returned as an
	* array of event arrays.  Each event array will have a 'href', 'etag' and 'event'
	* part, where the 'href' is relative to the calendar and the event contains the
	* definition of the event in iCalendar format.
	*
	* @param timestamp $start The start time for the period
	* @param timestamp $finish The finish time for the period
	* @param string    $relative_url The URL relative to the base_url specified when the calendar was opened.  Default ''.
	*
	* @return array An array of the relative URLs, etags, and events, returned from DoCalendarQuery() @see DoCalendarQuery()
	*/
	function GetEvents( $start = null, $finish = null, $relative_url = null ) {
		$filter = "";
		if ( isset($start) && isset($finish) ) {
			$range = "<C:time-range start=\"$start\" end=\"$finish\"/>";
		} else {
			$range = '';
		}

		$filter = <<<EOFILTER
  <C:filter>
    <C:comp-filter name="VCALENDAR">
      <C:comp-filter name="VEVENT">
        $range
      </C:comp-filter>
    </C:comp-filter>
  </C:filter>
EOFILTER;

		return $this->DoCalendarQuery($filter, $relative_url);
	}


	/**
	* Get the todo's in a range from $start to $finish.  The dates should be in the
	* format yyyymmddThhmmssZ and should be in GMT.  The events are returned as an
	* array of event arrays.  Each event array will have a 'href', 'etag' and 'event'
	* part, where the 'href' is relative to the calendar and the event contains the
	* definition of the event in iCalendar format.
	*
	* @param timestamp $start The start time for the period
	* @param timestamp $finish The finish time for the period
	* @param boolean   $completed Whether to include completed tasks
	* @param boolean   $cancelled Whether to include cancelled tasks
	* @param string    $relative_url The URL relative to the base_url specified when the calendar was opened.  Default ''.
	*
	* @return array An array of the relative URLs, etags, and events, returned from DoCalendarQuery() @see DoCalendarQuery()
	*/
	function GetTodos( $start, $finish, $completed = false, $cancelled = false, $relative_url = null ) {

		if ( $start && $finish ) {
			$time_range = <<<EOTIME
                <C:time-range start="$start" end="$finish"/>
EOTIME;
		} else {
        	$time_range = "";
    	}

		// Warning!  May contain traces of double negatives...
		$neg_cancelled = ( $cancelled === true ? "no" : "yes" );
		$neg_completed = ( $cancelled === true ? "no" : "yes" );

		$filter = <<<EOFILTER
  <C:filter>
    <C:comp-filter name="VCALENDAR">
          <C:comp-filter name="VTODO">
                <C:prop-filter name="STATUS">
                        <C:text-match negate-condition="$neg_completed">COMPLETED</C:text-match>
                </C:prop-filter>
                <C:prop-filter name="STATUS">
                        <C:text-match negate-condition="$neg_cancelled">CANCELLED</C:text-match>
                </C:prop-filter>$time_range
          </C:comp-filter>
    </C:comp-filter>
  </C:filter>
EOFILTER;

		return $this->DoCalendarQuery($filter, $relative_url);
	}


	/**
	* Get the calendar entry by UID
	*
	* @param uid
	* @param string    $relative_url The URL relative to the base_url specified when the calendar was opened.  Default ''.
	* @param string    $component_type The component type inside the VCALENDAR.  Default 'VEVENT'.
	*
	* @return array An array of the relative URL, etag, and calendar data returned from DoCalendarQuery() @see DoCalendarQuery()
	*/
	function GetEntryByUid( $uid, $relative_url = null, $component_type = 'VEVENT' ) {
		$filter = "";
		if ( $uid ) {
			$filter = <<<EOFILTER
  <C:filter>
    <C:comp-filter name="VCALENDAR">
          <C:comp-filter name="$component_type">
                <C:prop-filter name="UID">
                        <C:text-match icollation="i;octet">$uid</C:text-match>
                </C:prop-filter>
          </C:comp-filter>
    </C:comp-filter>
  </C:filter>
EOFILTER;
		}

		return $this->DoCalendarQuery($filter, $relative_url);
	}


	/**
	* Get the calendar entry by HREF
	*
	* @param string    $href         The href from a call to GetEvents or GetTodos etc.
	*
	* @return string The iCalendar of the calendar entry
	*/
	function GetEntryByHref( $href ) {
		$href = str_replace( rawurlencode('/'),'/',rawurlencode($href));
		return $this->DoGETRequest( $href );
	}


    /**
     * Do a Sync operation. This is the fastest way to detect changes.
     *
     * @param string    $url                URL for the calendar
     * @param boolean   $initial            It's the first synchronization
     * @param boolean   $support_dav_sync   The CalDAV server supports sync-collection
     *
     * @return array of responses
     */
    public function GetSync($relative_url = null, $initial = true, $support_dav_sync = false) {
        if (!empty($relative_url)) {
            $this->SetCalendar($relative_url);
        }

        $hasToken = !$initial && isset($this->synctoken[$this->calendar_url]);
        if ($support_dav_sync) {
            $token = ($hasToken ? $this->synctoken[$this->calendar_url] : "");

            $body = <<<EOXML
<?xml version="1.0" encoding="utf-8"?>
<D:sync-collection xmlns:D="DAV:">
    <D:sync-token>$token</D:sync-token>
    <D:sync-level>1</D:sync-level>
    <D:prop>
        <D:getetag/>
        <D:getlastmodified/>
    </D:prop>
</D:sync-collection>
EOXML;
        }
        else {
            $body = <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
  <D:prop>
    <D:getetag/>
    <D:getlastmodified/>
  </D:prop>
  <C:filter>
    <C:comp-filter name="VCALENDAR" />
  </C:filter>
</C:calendar-query>
EOXML;
        }

        $this->SetDepth(1);
        $this->DoRequest($this->calendar_url, "REPORT", $body, "text/xml");

        $report = array();
        foreach ($this->xmlnodes as $k => $v) {
            switch ($v['tag']) {
                case 'DAV::response':
                    if ($v['type'] == 'open') {
                        $response = array();
                    }
                    elseif ($v['type'] == 'close') {
                        $report[] = $response;
                    }
                    break;
                case 'DAV::href':
                    $response['href'] = basename( rawurldecode($v['value']) );
                    break;
                case 'DAV::getlastmodified':
                    if (isset($v['value'])) {
                        $response['getlastmodified'] = $v['value'];
                    }
                    else {
                        $response['getlastmodified'] = '';
                    }
                    break;
                case 'DAV::getetag':
                    $response['etag'] = preg_replace('/^"?([^"]+)"?/', '$1', $v['value']);
                    break;
                case 'DAV::sync-token':
                    $this->synctoken[$this->calendar_url] = $v['value'];
                    break;
            }
        }

        // Report sync-token support on initial sync
        if ($initial && $support_dav_sync && !isset($this->synctoken[$this->calendar_url])) {
            ZLog::Write(LOGLEVEL_WARN, 'CalDAVClient->GetSync(): no DAV::sync-token received; did you set CALDAV_SUPPORTS_SYNC correctly?');
        }

        return $report;
    }

}