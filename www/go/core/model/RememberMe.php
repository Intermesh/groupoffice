<?php
namespace go\core\model;

use DateInterval;
use Exception;
use go\core\ErrorHandler;
use go\core\exception\RememberMeTheft;
use go\core\http\Request;
use go\core\http\Response;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;
use ReflectionException;

/**
 * Class RememberMe
 *
 * Remember me implemented like described here:
 *
 * https://stackoverflow.com/a/244907
 *
 * https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence#title.2
 *
 * @package go\core\model
 */
class RememberMe extends Entity {
	
	/**
	 * The token that identifies the user in the login process.
	 * @var string
	 */							
	public ?string $id;
	
	/**
	 * The token that identifies the user. Sent in HTTPOnly cookie.
	 * @var string
	 */							
	public string $token;

	private $unhashedToken;

	public ?string $userId;

	/**
	 * Time this token expires. Defaults to one day after the token was created {@see LIFETIME}
	 * @var ?DateTime
	 */							
	public ?\DateTimeInterface $expiresAt = null;

	public string $series;


	/**
	 * FK to the core_client table
	 */
	public string $clientId;

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


	/**
	 * @throws ReflectionException
	 */
	protected static function defineMapping(): Mapping
	{
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

	private function setClient() {

		if(empty($this->clientId)) {
			// must exists. because created with token
			$client = User::findById($this->userId)->currentClient();
			if(!empty($client)) {
				$this->clientId = $client->id;
			}
		}
	}

	protected function internalSave(): bool
	{
		$this->setClient();
		return parent::internalSave();
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	private static function generateToken(): string
	{
		return uniqid().bin2hex(random_bytes(16));
	}

	/**
	 * @throws Exception
	 */
	private function setNewToken() {
		$this->unhashedToken = static::generateToken();
		$this->token = password_hash($this->unhashedToken, PASSWORD_DEFAULT);
	}

	/**
	 * Check if the token is expired.
	 * 
	 * @return boolean
	 */
	public function isExpired(): bool
	{
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

	/**
	 * Get the token for the client with the unhashed value
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getToken(): string
	{
		if(!isset($this->unhashedToken)) {
			throw new Exception("You can only get the token when it was just created");
		}
		return $this->series . ':' . $this->unhashedToken;
	}

	/**
	 * @throws Exception
	 */
	public function setCookie() {
		Response::get()->setCookie('goRememberMe', $this->getToken(), [
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
	 * @throws Exception
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

		/** @var static $rememberMe */

		if(!$rememberMe) {
			return false;
		}

		if($rememberMe->isExpired()) {
			static::delete($rememberMe);
			return false;
		}

		if(!password_verify($cookieParts[1], $rememberMe->token)) {

			ErrorHandler::log("Remember me token theft. Cookie: " . $value . " didn't match token: " . $rememberMe->token);

			// clear logins
			Token::delete(
				(new Query())
					->where('userId', '=', $rememberMe->userId)
						//below is for the api keys module. It sets tokens that never expire.
						// A remember me token is never used for such a key so they can be
						// safely ignored.
					->andWhere('expiresAt', 'IS NOT', null)
			);
			RememberMe::delete(['userId' => $rememberMe->userId]);
			self::unsetCookie();
			throw new RememberMeTheft();
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
	 * @throws Exception
	 */
	public static function collectGarbage(): bool
	{
		return static::delete(
			(new Query)
				->andWhere('expiresAt', '<', new DateTime()));
	}

	
}
