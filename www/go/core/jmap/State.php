<?php
namespace go\core\jmap;

use go\core\auth\State as AbstractState;
use go\core\auth\model\Token;
use go\modules\core\users\model\User;
use go\core\jmap\Request;
use go\core\http\Response;

class State extends AbstractState {
	
	private function getFromHeader() {
		
		$auth = Request::get()->getHeader('Authorization');
		if(!$auth) {
			return false;
		}
		preg_match('/Bearer (.*)/', $auth, $matches);
		if(!isset($matches[1])){
			return false;
		}
		
		return $matches[1];
	}
	
	/**
	 *
	 * @var Token 
	 */
	private $token;
	
	/**
	 * Get the authorization token by reading the request header "Authorization"
	 * 
	 * @return boolean|Token 
	 */
	public function getToken() {

		
		if(!isset($this->token)) {
						
			$tokenStr = $this->getFromHeader();
//			if(!$tokenStr && GO()->getRequest()->getMethod() == 'GET' && isset($_COOKIE['accessToken'])) {
//				$tokenStr = $_COOKIE['accessToken'];
//			}
			

			if(!$tokenStr) {
				return false;
			}
		
			$this->token = Token::find()->where(['accessToken' => $tokenStr])->single();

			if(!$this->token) {
				return false;
			}		

			if($this->token->isExpired()) {				
				$this->token->delete();				
				$this->token = false;
			}
		}
		
		return $this->token;
	}
  
	public function setToken(Token $token) {
		$this->token = $token;
	}
	
	public function isAuthenticated() {
		return $this->getToken() !== false;
	}
	
	/**
	 * Return the JMAP session data.
	 * Called when the user makes an authenticated GET request
	 */
	public function outputSession() {		
		
		if (!$this->isAuthenticated()) {
			Response::get()->setStatus(401);
			Response::get()->output([
					"auth" => [
							"domains" => User::getAuthenticationDomains()
					]
			]);
		} else
		{
			Response::get()->output($this->getSession());
		}
	}

	public function getSession() {	
		
		$settings = \go\modules\core\core\model\Settings::get();
		
		$user = $this->getToken()->getUser();
		$response = [
			'username' => $user->username,
			'accounts' => ['1'=> [
				'name'=>'Virtual',
				'isPrimary' => true,
				'isReadOnly' => false,
				'hasDataFor' => []
			]],
			"auth" => [
						"domains" => User::getAuthenticationDomains()
			],
			'capabilities' => Capabilities::get(),
			'apiUrl' => $settings->URL.'jmap.php',
			'downloadUrl' => $settings->URL.'download.php?blob={blobId}',
			'uploadUrl' => $settings->URL.'upload.php',
			'eventSourceUrl' => $settings->URL.'sse.php',
			'user' => $user->toArray(),
			'oldSettings' => $this->clientSettings(), // added for compatibility
		];

		return $response;
	}
	
	private function clientSettings() {
		$user = \GO::user();
		return [
			'state' => \GO\Base\Model\State::model()->getFullClientState($user->id)
			,'user_id' => $user->id
			,'avatarId' => $user->avatarId
			,'has_admin_permission' => $user->isAdmin()
			,'username' => $user->username
			,'displayName' => $user->displayName
			,'email' => $user->email
			,'thousands_separator' => $user->thousandsSeparator
			,'decimal_separator' => $user->decimalSeparator
			,'date_format' => $user->completeDateFormat
			,'time_format' => $user->timeFormat
			,'currency' => $user->currency
			,'lastlogin' => $user->getLastlogin()
			,'max_rows_list' => $user->max_rows_list
			,'timezone' => $user->timezone
			,'start_module' => $user->start_module
			,'theme' => $user->theme
			,'mute_sound' => $user->mute_sound
			,'mute_reminder_sound' => $user->mute_reminder_sound
			,'mute_new_mail_sound' => $user->mute_new_mail_sound
			,'popup_reminders' => $user->popup_reminders
			,'popup_emails' => $user->popup_emails
			,'show_smilies' => $user->show_smilies
			,'auto_punctuation' => $user->auto_punctuation
			,'first_weekday' => $user->firstWeekday
			,'sort_name' => $user->sort_name
			,'list_separator' => $user->listSeparator
			,'text_separator' => $user->textSeparator
			,'modules' => \GO::view()->exportModules()
		];
	}
	
	/**
	 * Get the user ID
	 * @return int
	 */
	public function getUserId() {
		return $this->getToken() ? $this->getToken()->userId : null;
	}
	
	/**
	 * 
	 * @return User
	 */
	public function getUser() {
		return $this->getToken() ? $this->getToken()->getUser() : null;
	}

}
