<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SettingsController.php 18043 2014-08-28 09:12:47Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */


namespace GO\Mediawiki\Controller;

use GO;
use GO\Base\Controller\AbstractController;
use GO\Base\View\JsonView;

/**
 * 
 * The Category controller
 * 
 */
class SettingsController extends AbstractController {
	
	protected function init() {
		
		$this->view = new JsonView();
		parent::init();
	}

	protected function actionSave($params) {

		GO::config()->save_setting('mediawiki_external_url', $_POST['external_url']);
		$response['data']['external_url'] = $_POST['external_url'];
		GO::config()->save_setting('mediawiki_title', $_POST['title']);
		$response['data']['title'] = $_POST['title'];
		$response['success']=true;

		echo $this->render('json',$response);
	}
	
	protected function actionLoad($params) {
		
		$response['data'] = array();
		$response['data']['title'] = GO::config()->get_setting('mediawiki_title');
			if (empty($response['data']['title'])) $response['data']['title'] = 'Mediawiki';
		$response['data']['external_url'] = GO::config()->get_setting('mediawiki_external_url');
			if (empty($response['data']['external_url'])) $response['data']['external_url'] = '';
		$response['success'] = true;

		echo $this->render('json',$response);
		
	}
	
	public static function postRequest($url, $referer, $_data, $header=array()) {
		// convert variables array to string:
		$data = array();
		while(list($n,$v) = each($_data)){
				$data[] = "$n=$v";
		}
		$data = implode('&', $data);
		// format --> test1=a&test2=b etc.

		// parse the given URL
		$url = parse_url($url);
		if ($url['scheme'] != 'http') {
				die('Only HTTP request are supported !');
		}

		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];

		// open a socket connection on port 80
		$fp = fsockopen($host, 80);

		// send the request headers:
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Referer: $referer\r\n");
		foreach($header as $h)
			fputs($fp, $h."\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data);

		$result = '';
		while(!feof($fp)) {
				// receive the results of the request
				$result .= fgets($fp, 128);
		}

		// close the socket connection:
		fclose($fp);

		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);

		$header = isset($result[0]) ? $result[0] : '';
		$content = isset($result[1]) ? $result[1] : '';

		// return as array:
		return array($header, $content);
	}

}
