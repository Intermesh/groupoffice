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

		if (isset($_COOKIE['groupoffice'])) {

			$GO_SID = $_COOKIE['groupoffice'];

			$fname = session_save_path() . "/sess_" . $GO_SID;
			if (file_exists($fname)) {
				$data = file_get_contents($fname);
				$data = $this->unserializeSession($data);
				return $data['GO_SESSION']['username'];
			}
		} else {
			$username = "";
		}

		return '';
	}

}
