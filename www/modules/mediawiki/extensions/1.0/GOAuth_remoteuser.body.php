<?php


include_once 'Auth_remoteuser/Auth_remoteuser.body.php';


Class GOAuth_remoteuser extends Auth_remoteuser {
	
	
	function go_unserializesession($data) {
		$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0; isset($vars[$i]); $i++)
			$result[$vars[$i++]] = unserialize($vars[$i]);
		return $result;
	}
	
	public function getRemoteUsername( ) {

		if (isset($_COOKIE['groupoffice'])) {

			$GO_SID=$_COOKIE['groupoffice'];

			$fname = session_save_path() . "/sess_" . $GO_SID;

				if (file_exists($fname)) {
					$data = file_get_contents($fname);
					$data = $this->go_unserializesession($data);
					return $data['GO_SESSION']['username'];
				}
		
		} else {
			$username = "";
		}

		return '';
	}

public function initUser( &$user, $autocreate = false ) {
		$username = $this->getRemoteUsername();
		if ( Hooks::run( "GOAuthRemoteUserInitUser",
				array( $user, $autocreate ) ) ) {

			$this->setRealName( $user );

			$this->setEmail( $user, $username );

			$user->mEmailAuthenticated = wfTimestampNow();
			$user->setToken();

			$this->setNotifications( $user );
		}
		$user->saveSettings();
	}
	
}
