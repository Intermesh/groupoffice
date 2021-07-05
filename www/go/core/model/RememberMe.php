<?php
namespace go\core\model;

use DateInterval;
use go\core\Environment;
use go\core\http\Request;
use go\core\http\Response;
use go\core\orm\exception\SaveException;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;

/**
 * Class RememberMe
 *
 * Remember me implemented like described here:
 *
 * https://stackoverflow.com/a/244907
 *
 * @package go\core\model
 */
class RememberMe extends Entity {
	
	/**
	 * The token that identifies the user in the login process.
	 * @var string
	 */							
	public $id;
	
	/**
	 * The token that identifies the user. Sent in HTTPOnly cookie.
	 * @var string
	 */							
	public $token;

	private $unhashedToken;

	/**
	 * 
	 * @var int
	 */							
	public $userId;

	/**
	 * Time this token expires. Defaults to one day after the token was created {@see LIFETIME}
	 * @var DateTime
	 */							
	public $expiresAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $series;


	/**
	 * The remote IP address of the client connecting to the server
	 *
	 * @var string
	 */
	public $remoteIpAddress;

	/**
	 * The user agent sent by the client
	 *
	 * @var string
	 */
	public $userAgent;

	/**
	 * @var string
	 */
	public $platform;

	/**
	 * @var string
	 */
	public $browser;

	
	/**
	 * A date interval for the lifetime of a token
	 *
	 * On each JMAP request the token's expiry time will be pushed with this interval forward in time.
	 * So a request within this life time will keep it alive.
	 * The client (browser) will keep it alive by using SSE or checking for updates every 2 minutes. When the
	 * client is closed the token will be cleaned up after this lifetime.
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LIFETIME = 'P7D';

	
	protected static function defineMapping() {
		return parent::defineMapping()
		->addTable('core_auth_remember_me', 'r');
	}
	
	protected function init() {
		parent::init();
		
		if($this->isNew()) {	
			$this->setExpiryDate();
			$this->setNewToken();
			$this->setClient();

			$this->series = static::generateToken();
		}
	}

	private function setClient() {
		if(isset($_SERVER['REMOTE_ADDR'])) {
			$this->remoteIpAddress = $_SERVER['REMOTE_ADDR'];
		}

		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}else if(Environment::get()->isCli()) {
			$this->userAgent = 'cli';
		} else {
			$this->userAgent = 'Unknown';
		}

		$ua_info = \donatj\UserAgent\parse_user_agent();

		$this->platform = $ua_info['platform'];
		$this->browser = $ua_info['browser'];
	}

	private static function generateToken(){
		return uniqid().bin2hex(random_bytes(16));
	}

	private function setNewToken() {
		$this->unhashedToken = static::generateToken();
		$this->token = password_hash($this->unhashedToken, PASSWORD_DEFAULT);
	}

	/**
	 * Check if the token is expired.
	 * 
	 * @return boolean
	 */
	public function isExpired(){

		if(!isset($this->expiresAt)) {
			return false;
		}
		
		return $this->expiresAt < new DateTime();
	}

	private function setExpiryDate() {
		$expireDate = new DateTime();
		$expireDate->add(new DateInterval(self::LIFETIME));
		$this->expiresAt = $expireDate;		
	}

	public function setCookie() {
		Response::get()->setCookie('goRememberMe', $this->series . ':' . $this->unhashedToken, [
			'expires' => $this->expiresAt->format("U"),
			"path" => "/",
			"samesite" => "Lax",
			"domain" => Request::get()->getHost(),
			"httpOnly" => true
		]);
	}

	public static function unsetCookie() {
		Response::get()->setCookie('goRememberMe', "", [
			'expires' => time() - 3600,
			"path" => "/",
			"samesite" => "Lax",
			"domain" => Request::get()->getHost(),
			"httpOnly" => true
		]);
	}


	/**
	 * Verify remember me cookie
	 *
	 * @return bool|static
	 * @throws \Exception
	 */
	public static function verify($value = null) {

		if(!isset($value)) {
			if(!isset($_COOKIE['goRememberMe'])) {
				return false;
			}
			$value = $_COOKIE['goRememberMe'];
		}

		$cookieParts = explode(':', $value);

		$rememberMe = static::find()
			->where('series','=', $cookieParts[0])
			->single();

		if(!$rememberMe) {
			return false;
		}

		if($rememberMe->isExpired()) {
			static::delete($rememberMe);
			return false;
		}

		if(!password_verify($cookieParts[1], $rememberMe->token)) {
			throw new \Exception("Theft!");
		}

		$rememberMe->setNewToken();

		if(!$rememberMe->save()) {
			throw new SaveException($rememberMe);
		}


		return $rememberMe;
	}

	/**
	 * Called by GarbageCollection cron job
	 *
	 * @see GarbageCollection
	 * @return bool
	 * @throws \Exception
	 */
	public static function collectGarbage() {
		return static::delete(
			(new Query)
				->andWhere('expiresAt', '<', new DateTime()));
	}

	
}
