<?php
namespace go\core\util;

use go\core\http\Client;

class Geolocation {

	public static function locate($ip) {

		$client = new Client();
		$response = $client->get('http://www.geoplugin.net/json.gp?ip=' . $ip);

		$data = JSON::decode($response['body'], true);

		return [
			'countryCode' => $data['geoplugin_countryCode'],
			'countryName' => $data['geoplugin_countryName'],
			'regionCode' => $data['geoplugin_regionCode'],
			'regionName' => $data['geoplugin_regionName'],
		];
	}
}