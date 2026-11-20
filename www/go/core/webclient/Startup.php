<?php

namespace go\core\webclient;

use Exception;
use LogicException;
use go\core\App;
use go\core\auth\Authenticate;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\RememberMeTheft;
use go\core\exception\Unavailable;
use go\core\http\Request;
use go\core\jmap\State;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\modules\business\license\model\License;
use Throwable;

/**
 * Checks and logins before the web client page is output
 *
 * Used by index.php for the GOUI web client and by legacy.php when it's called without a route. Must run before any
 * output, because it sets cookies and may output a page of its own and exit.
 */
final class Startup
{
	/**
	 * Run all checks and logins for a page request
	 *
	 * Exits with a page when the system is not available.
	 *
	 * @throws Throwable
	 */
	public static function run(): void
	{
		//fire index event before version check so multi instance can check if it exists before comparing versions
		go()->fireEvent(App::EVENT_INDEX);

		self::checkAvailability();

		if (self::loginWithHandoffToken()) {
			// Load the page with GET, so a reload doesn't post the token again
			header('Location: ' . Request::get()->getFullUrl(), true, 303);
			exit();
		}

		self::loginWithRememberMe();
	}

	/**
	 * Show a page and exit when the database is being upgraded or the license is invalid
	 */
	public static function checkAvailability(): void
	{
		if (go()->getSettings()->databaseVersion != go()->getVersion()) {
			self::outputPage(go()->t("Service unavailable"), go()->t("The system is not available because an update is currently being installed. Please try again later."));
		}

		if (!empty(go()->getSettings()->license) && !License::isValid()) {
			self::outputPage("Invalid license", License::$validationError);
		}
	}

	/**
	 * Output a simple page with a title and text and exit
	 *
	 * @param string $title
	 * @param string $text
	 * @return never
	 */
	private static function outputPage(string $title, string $text): never
	{
		$installPath = go()->getEnvironment()->getInstallPath();
		require($installPath . '/views/Extjs3/externalHeader.php');
		echo "<h1>" . htmlspecialchars($title) . "</h1>";
		echo "<p>" . htmlspecialchars($text) . "</p>";
		require($installPath . '/views/Extjs3/externalFooter.php');
		exit();
	}

	/**
	 * Log in with an access token posted by another server
	 *
	 * The multi_instance module uses this to sign in to an instance directly. Only a short-lived token made for this
	 * is accepted ({@see Token::findHandoff()}), otherwise another website could log the user in to its own account. The
	 * token is replaced, so it can be used only once.
	 *
	 * @return bool true when the user was logged in
	 * @throws Exception
	 */
	public static function loginWithHandoffToken(): bool
	{
		if (empty($_POST['accessToken']) || !is_string($_POST['accessToken'])) {
			return false;
		}

		$old = date_default_timezone_get();
		date_default_timezone_set('UTC');
		try {
			$token = Token::findHandoff($_POST['accessToken']);
			// isAuthenticated() also checks the expiry. An expired token must not be revived.
			if (!$token || !$token->isAuthenticated() || !self::userMayLogin($token->userId) || !$token->rotate()) {
				return false;
			}

			self::setToken($token);
			return true;
		} finally {
			unset($_POST['accessToken']);
			date_default_timezone_set($old);
		}
	}

	/**
	 * Log in with the persistent "Remember my login" cookie
	 *
	 * Only on GET requests and when the user isn't logged in already. Sets the access token cookie that the web client
	 * uses.
	 *
	 * @return bool true when the user was logged in
	 * @throws RememberMeTheft when the cookie looks stolen. All logins of the user are ended.
	 * @throws Throwable
	 */
	public static function loginWithRememberMe(): bool
	{
		if (($_SERVER['REQUEST_METHOD'] ?? '') != "GET" || go()->getAuthState()->isAuthenticated()) {
			return false;
		}

		try {
			$rememberMe = RememberMe::verify();
		} catch (Forbidden | Unavailable) {
			// Not allowed to login (now). Show the login page instead of an error. It tells why after logging in.
			return false;
		}

		if (!$rememberMe) {
			return false;
		}

		$rememberMe->setCookie();

		$token = new Token();
		$token->userId = $rememberMe->userId;
		self::setToken($token);

		return true;
	}

	/**
	 * Use the token for this request and set the access token cookie for the web client
	 *
	 * @throws Exception
	 */
	private static function setToken(Token $token): void
	{
		$state = go()->getAuthState();
		if (!$state instanceof State) {
			throw new LogicException("The JMAP auth state is required");
		}
		$state->setToken($token);
		$token->setAuthenticated();
		$token->setCookie();
	}

	/**
	 * Logging in with a token must follow the same rules as a password login: disabled users, IP restrictions and
	 * maintenance mode
	 *
	 * @param int|string $userId
	 * @return bool
	 */
	private static function userMayLogin(int|string $userId): bool
	{
		try {
			(new Authenticate())->assertUserIdMayLogin($userId);
			return true;
		} catch (Forbidden | Unavailable) {
			return false;
		}
	}

	/**
	 * Handle an exception of a page request
	 *
	 * Shows a warning when the remember me cookie looks stolen. Other errors redirect to the installer, unless
	 * debugging is enabled.
	 *
	 * @param Throwable $e
	 */
	public static function handleException(Throwable $e): void
	{
		if ($e instanceof RememberMeTheft) {
			self::outputTheftPage();
			return;
		}

		if (!Request::get()->isXHR() && (empty($_REQUEST['r']) || $_REQUEST['r'] != 'maintenance/upgrade')) {

			$msg = ErrorHandler::logException($e);

			if (go()->getDebugger()->enabled || headers_sent()) {

				echo "DEBUGGER: Showing error message because debug is enabled. Normally we would have redirected to install. I you're doing a fresh install and your database is empty then you can safely ignore this.:<br /><br />";
				echo $msg;
				echo "<pre>" . $e->getTraceAsString() . "</pre>";
				echo '<br /><br /><a href="install/">Click here to launch the installer</a>';
				exit();
			}

			header('Location: install/');
			exit();
		} else {
			echo "<h1>Fatal error</h1>";
			echo "<pre>";
			echo $e->getMessage();
			if (go()->getDebugger()->enabled) {
				echo $e->getTraceAsString();
			}
			echo "</pre>";
		}
	}

	private static function outputTheftPage(): void
	{
		$tp = Extjs3::get()->getThemePath();
		require($tp . 'pageHeader.php');
		?>
		<section>
			<fieldset>
				<h1><?= go()->t("Security warning"); ?></h1>
				<p><?= go()->t("It looks like someone might have had unauthorized access to your account. You have been logged out everywhere. Please reset your password immediately."); ?></p>
				<a class="button primary right" href="<?= Extjs3::get()->getBaseUrl(); ?>"><?= go()->t('Continue'); ?></a>
			</fieldset>
		</section>
		<?php
		require($tp . 'pageFooter.php');
	}
}
