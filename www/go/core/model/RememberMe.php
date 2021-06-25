<?php
namespace go\core\model;

use DateInterval;
use go\core\auth\BaseAuthenticator;
use go\core\auth\SecondaryAuthenticator;
use go\core\Environment;
use go\core\auth\Method;
use go\core\http\Request;
use go\core\http\Response;
use go\core\orm\exception\SaveException;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\core\model\Module;

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

			$this->series = static::generateToken();
		}
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
		$expireDate->add(new DateInterval(Token::LIFETIME));
		$this->expiresAt = $expireDate;		
	}

	public function setCookie() {
		Response::get()->setCookie('goRememberMe', $this->series . ':' . $this->unhashedToken, [
			'expires' => $this->expiresAt->format("U"),
			"path" => "/",
			"samesite" => "Lax",
			"domain" => Request::get()->getHost()
		]);
	}


	/**
	 * Verify remember me cookie
	 *
	 * @return bool|static
	 * @throws \Exception
	 */
	public static function verify() {

		if(!isset($_COOKIE['goRememberMe']) || isset($_COOKIE['accessToken'])) {
			return false;
		}

		$cookieParts = explode(':', $_COOKIE['goRememberMe']);

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
