<?php
namespace go\core\model;

use DateInterval;
use Exception;
use go\core\auth\Authenticate;
use go\core\db\DbException;
use go\core\ErrorHandler;
use go\core\exception\RememberMeTheft;
use go\core\http\Request;
use go\core\http\Response;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\core\util\Password;
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
	
	protected function init(): void
	{
		parent::init();
		
		if($this->isNew()) {	
			$this->setExpiryDate();
			$this->setNewToken();

			$this->series = static::generateToken();
		}
	}

	private function setClient(): void
	{

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
	private function setNewToken(): void
	{
		$this->unhashedToken = static::generateToken();
		$this->token = Password::hash($this->unhashedToken);
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

	private function setExpiryDate(): void
	{
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
	public function setCookie(): void
	{
		Response::get()->setCookie('goRememberMe', $this->getToken(), self::cookieOptions((int) $this->expiresAt->format("U")));
	}

	public static function unsetCookie(): void
	{
		Response::get()->setCookie('goRememberMe', "", self::cookieOptions(time() - 3600));
	}

	/**
	 * The cookie logs the user in for a week, so it must never be sent over an unencrypted connection.
	 */
	private static function cookieOptions(int $expires): array
	{
		return [
			'expires' => $expires,
			"path" => "/",
			"samesite" => "Lax",
			"secure" => Request::get()->isHttps(),
			"domain" => Request::get()->getHost(),
			"httpOnly" => true
		];
	}


	/**
	 * Verify remember me cookie
	 *
	 * @param ?string $value - the rememberMe token to be verified
	 * @return bool|static
	 * @throws Exception|RememberMeTheft|SaveException|\Throwable
	 */
	public static function verify(?string $value = null): bool|static
	{

		if(!isset($value)) {
			if(!isset($_COOKIE['goRememberMe']) || !is_string($_COOKIE['goRememberMe'])) {
				return false;
			}
			$value = $_COOKIE['goRememberMe'];
		}

		$cookieParts = self::parseCookie($value);
		if(!$cookieParts) {
			// Not a value that this class created. Don't treat it as theft.
			return false;
		}

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

			// Don't log the cookie or the hash. They would allow guessing or using the token.
			ErrorHandler::log("Remember me token theft detected for user ID " . $rememberMe->userId . " from IP: " . Request::get()->getRemoteIpAddress());

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

		// Apply the same rules as a password login (disabled, IP restrictions, maintenance mode). This must be done
		// before the token is replaced. Otherwise the client keeps a token that no longer matches and the next attempt
		// would be seen as theft.
		(new Authenticate())->assertUserIdMayLogin($rememberMe->userId);

		$rememberMe->setNewToken();

		if(!$rememberMe->save()) {
			throw new SaveException($rememberMe);
		}


		return $rememberMe;
	}

	/**
	 * Split the cookie value in series and token
	 *
	 * @param string $value
	 * @return string[]|null [series, token] or null when it's not a value created by {@see getToken()}
	 */
	private static function parseCookie(string $value): ?array
	{
		$parts = explode(':', $value);
		if(count($parts) != 2 || !preg_match('/^[0-9a-f]{1,100}$/', $parts[0]) || !preg_match('/^[0-9a-f]{1,100}$/', $parts[1])) {
			return null;
		}
		return $parts;
	}

	/**
	 * Delete the remember me login of the cookie, eg. when the user logs out
	 *
	 * @param string|null $value The cookie value. Defaults to the cookie of the request.
	 * @return bool True if a remember me login was deleted
	 * @throws Exception
	 */
	public static function deleteByCookie(?string $value = null): bool
	{
		if(!isset($value)) {
			$value = $_COOKIE['goRememberMe'] ?? null;
			if(!is_string($value)) {
				return false;
			}
		}

		$cookieParts = self::parseCookie($value);
		if(!$cookieParts) {
			return false;
		}

		// The whole series is deleted, also when the token doesn't match because it was already used by a thief
		$ids = static::find()->selectSingleValue('id')->where('series', '=', $cookieParts[0])->all();
		if(empty($ids)) {
			return false;
		}
		return static::delete(['id' => $ids]);
	}

	/**
	 * Called by GarbageCollection cron job
	 *
	 * @return bool
	 * @throws DbException|\Throwable
	 * @see GarbageCollection
	 */
	public static function collectGarbage(): bool
	{
		go()->debug("GC: RememberMe");
		try {
			do {
				// delete in batches to keep transaction small
				static::delete(
					(new Query)
						->andWhere('expiresAt', '<', new DateTime())
						->limit(1000)
				);
			} while (self::$lastDeleteStmt->rowCount() > 0);
		} catch(DbException $e) {
			// Just log exceptions here but continue
			ErrorHandler::logException($e);
		}

		return true;

	}

	
}
