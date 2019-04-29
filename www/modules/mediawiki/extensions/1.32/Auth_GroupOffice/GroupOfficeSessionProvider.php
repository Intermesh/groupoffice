<?php
namespace MediaWiki\Extensions\Auth_GroupOffice;

use MediaWiki\Session\SessionInfo;

class GroupOfficeSessionProvider extends UserNameSessionProvider {

	public function __construct($params = array()) {
		
	
		$params['priority'] = SessionInfo::MAX_PRIORITY;
		$params['remoteUserNames'] = [];
		
		$username = $this->getGroupOfficeUsername();
		
//		var_dump($username);
		
		if($username)
		{
			$params['remoteUserNames'][] = $username;
		}

		parent::__construct($params);
	}

	private function unserializeSession($data) {
		$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0; isset($vars[$i]); $i++) {
			$result[$vars[$i++]] = unserialize($vars[$i]);
		}
		return $result;
	}

	public function getGroupOfficeUsername() {
		
		$username = "";
		
		if (isset($_COOKIE['accessToken'])) {
			$GO_TOKEN = $_COOKIE['accessToken'];
			
			global $wgGoApiUrl;

			$fullGOUrl = $wgGoApiUrl.'jmap.php';
		
			// Make POST request with curl
			$ch = curl_init($fullGOUrl);          
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);                                                                  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json; charset=utf-8', 
					"Authorization: Bearer " . $GO_TOKEN)                                                                       
			);                                                                                                                   
																																																												
			$result = curl_exec($ch);
			//check for request error.
			if (!$result) {
				die("Failed to send request!" . curl_error($ch));
			}
			$responses = json_decode($result, true);
			
			if(isset($responses['username'])){
				$username = $responses['username'];
			}

		}

		return $username;
	}

}
