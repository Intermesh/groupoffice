<?php

namespace go\modules\community\tempsieve\util;

use go\core\exception\Unauthorized;
use GO\Email\Model\Account;
use \Net_Sieve;
use PEAR;

final class Sieve
{
	const SIEVE_ERROR_CONNECTION = 1;
	const SIEVE_ERROR_LOGIN = 2;
	const SIEVE_ERROR_NOT_EXISTS = 3;    // script not exists
	const SIEVE_ERROR_INSTALL = 4;       // script installation
	const SIEVE_ERROR_ACTIVATE = 5;      // script activation
	const SIEVE_ERROR_DELETE = 6;        // script deletion
	const SIEVE_ERROR_INTERNAL = 7;      // internal error
	const SIEVE_ERROR_DEACTIVATE = 8;    // script activation
	const SIEVE_ERROR_OTHER = 255;       // other/unknown error

	private Net_Sieve $sieve;                 // Net_Sieve object
	private $error = false;         // error flag
	private array $list = array();        // scripts list
	public $script;                 // go_sieve_script object
	public ?string $current;                // name of currently loaded script
	private array $disabled;              // array of disabled extensions

	private PEAR $_PEAR;

	/**
	 * Object constructor
	 *
	 * @param string  Username (for managesieve login)
	 * @param string  Password (for managesieve login)
	 * @param string  Managesieve server hostname/address
	 * @param string  Managesieve server port number
	 * @param string  Managesieve authentication method
	 * @param boolean Enable/disable TLS use
	 * @param array   Disabled extensions
	 * @param boolean Enable/disable debugging
	 */

	public function __construct()
	{
		$this->sieve = new Net_Sieve();
		$this->sieve->setDebug(true, array($this, 'debugHandler'));
		$this->_PEAR = new PEAR();
	}

	public function __destruct()
	{
		try {
			$this->sieve->disconnect();
		} catch (\Exception $e) {
			//ignore in production
			go()->warn($e);
		}
	}

	private function rewriteHost($host)
	{
		if (isset(go()->getConfig()->sieve_rewrite_hosts)) {

			$maps = explode(',', go()->getConfig()->sieve_rewrite_hosts);

			foreach ($maps as $map) {
				$pair = explode('=', $map);

				if ($pair[0] == $host && isset($pair[1])) {
					return $pair[1];
				}
			}
		}

		return $host;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $host
	 * @param int $port
	 * @param string|null $auth_type
	 * @param bool|null $usetls
	 * @param array|null $disabled
	 * @return bool
	 * @throws Unauthorized
	 */
	public function connect(string $username, string $password = '', string $host = 'localhost', int $port = 2000, ?string $auth_type = null, ?bool $usetls = true, ?array $disabled = array()): bool
	{
		if (empty($password)) {
			throw new Unauthorized('Wrong password');
		}
		$host = $this->rewriteHost($host);

		go()->debug("sieve::connect($username, ***, $host, $port, $auth_type, $usetls)");

		$options = null;
		// Hackish solution
		if(go()->getConfig()['debug']) {
			$options['ssl']['verify_peer'] = false;
			$options['ssl']['verify_peer_name'] = false;
			$options['ssl']['allow_self_signed'] = true;
		}

		if ($this->_PEAR->isError($this->sieve->connect($host, $port, $options, $usetls))) {
			return $this->setError(self::SIEVE_ERROR_CONNECTION);
		}

		if ($this->_PEAR->isError($this->sieve->login($username, $password, $auth_type ? strtoupper($auth_type) : null))) {
			return $this->setError(self::SIEVE_ERROR_LOGIN);
		}
		$this->disabled = $disabled;

		return true;
	}


	/**
	 * Getter for error code
	 */
	public function error()
	{
		return $this->error || false;
	}

	/**
	 * Check if an extension is available on the server
	 *
	 * @param string $extension
	 * @return boolean
	 */
	public function hasExtension(string $extension): bool
	{
		return $this->sieve->hasExtension($extension);
	}

	/**
	 * Saves current script into server
	 *
	 * @param ?string $name
	 */
	public function save(?string $name = null): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (!$this->script) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (!$name) {
			$name = $this->current;
		}

		$this->_moveOutOfOfficeToEnd();

		$script = $this->script->as_text();

		if (!$script) {
			$script = '/* empty script */';
		}

		$res = $this->sieve->installScript($name, $script, true);
		if ($this->_PEAR->isError($res)) {

			go()->debug("ERROR: " . $res);

			return $this->setError(self::SIEVE_ERROR_INSTALL . '<br/>Error message:</br>' . $res);
		}

		return true;
	}

	/**
	 * Move the "Out of office" rule to the end of the sieve file.
	 */
	private function _moveOutOfOfficeToEnd(): void
	{


		// De out of office rule altijd als laatste.
		foreach ($this->script->content as $key => $val) {

			if (isset($val['name']) && $val['name'] == 'Out of office') {
				$item = $this->script->content[$key];
				unset($this->script->content[$key]);
				array_push($this->script->content, $item);
				break;
			}
		}
	}

	/**
	 * Saves text script into server
	 *
	 * @param string $name
	 * @param string|null $content
	 * @return bool
	 */
	public function saveScript(string $name, ?string $content = null): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (!$content) {
			$content = '/* empty script */';
		}

		$res = $this->sieve->installScript($name, $content);
		if ($this->_PEAR->isError($res)) {
			return $this->setError(self::SIEVE_ERROR_INSTALL);
		}

		return true;
	}

	/**
	 * Activates specified script
	 */
	public function activate(?string $name = null): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (!$name) {
			$name = $this->current;
		}

		if ($this->_PEAR->isError($this->sieve->setActive($name))) {
			return $this->setError(self::SIEVE_ERROR_ACTIVATE);
		}

		return true;
	}

	/**
	 * De-activates specified script
	 */
	public function deactivate(): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if ($this->_PEAR->isError($this->sieve->setActive(''))) {
			return $this->setError(self::SIEVE_ERROR_DEACTIVATE);
		}

		return true;
	}

	/**
	 * Removes specified script
	 */
	public function remove(?string $name = null)
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (!$name) {
			$name = $this->current;
		}

		// script must be deactivated first
		if ($name == $this->sieve->getActive())
			if ($this->_PEAR->isError($this->sieve->setActive('')))
				return $this->setError(self::SIEVE_ERROR_DELETE);

		if ($this->_PEAR->isError($this->sieve->removeScript($name)))
			return $this->setError(self::SIEVE_ERROR_DELETE);

		if ($name == $this->current)
			$this->current = null;

		return true;
	}

	/**
	 * Gets list of supported by server Sieve extensions
	 */
	public function getSieveExtensions(): array|bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		$ext = $this->sieve->getExtensions();
		// we're working on lower-cased names
		$ext = array_map('strtolower', (array)$ext);

		if ($this->script) {
			$supported = $this->script->get_extensions();
			foreach ($ext as $idx => $ext_name) {
				if (!in_array($ext_name, $supported)) {
					unset($ext[$idx]);
				}
			}
		}

		return array_values($ext);
	}

	/**
	 * Gets list of scripts from server
	 */
	public function getSieveScripts()
	{
		if (!$this->list) {

			if (!$this->sieve) {
				return $this->setError(self::SIEVE_ERROR_INTERNAL);
			}

			$this->list = $this->sieve->listScripts();

			if ($this->_PEAR->isError($this->list)) {
				return $this->setError(self::SIEVE_ERROR_OTHER);
			}
		}

		return $this->list;
	}

	/**
	 * Returns active script name
	 */
	public function getActive(string $accountId)
	{
		$aliasEmails = array();
		$aliasesStmt = \GO\Email\Model\Alias::model()->findByAttribute('account_id', $accountId);

		$account_model = array(
			'account_id' => $accountId,
		);
		$account = Account::model()->findByPk($account_model['account_id']);
		$spamFolder = go()->getConfig()->spam_folder ?? $account->spam;
		if (empty($spamFolder)) {
			$spamFolder = 'Spam';
		}

		while ($aliasModel = $aliasesStmt->fetch()) {
			$aliasEmails[] = $aliasModel->email;
		}

		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		$active = $this->sieve->getActive();


		if (!$active) {

			$all_scripts = $this->getSieveScripts();

			if (empty($all_scripts)) {

				$createFlag = '';
				$require = array('fileinto', 'imap4flags', 'include');

				if ($this->sieve->hasExtension('vacation')) {
					$require[] = 'vacation';
				}

				// Check if the "mailbox" extension is supported
				if ($this->sieve->hasExtension('mailbox')) {
					$require[] = 'mailbox';
					$createFlag = ':create';
				}

				$requireString = 'require ["' . implode('","', $require) . '"];';

				// If vacation is available then add that rule to the default sieve script.
				if ($this->sieve->hasExtension('vacation')) {

					$content = $requireString . "

	# rule:[includeGlobal]
	if false # anyof (true)
	{
	### include global rule
	## /etc/dovecot/sieve/default.sieve
	include :global \"default\";
	}

	# rule:[" . GO::t("Standard vacation rule", "sieve") . "]
	if false # anyof (true)
	{
	\tvacation :days 3 :addresses [\"" . implode('","', $aliasEmails) . "\"] \"" . GO::t("I am on vacation", "sieve") . "\";
	\tstop;
	}

	# rule:[Spam]
	if anyof (header :contains \"X-Spam-Flag\" \"YES\")
	{
		setflag \"\\\Seen\";
		fileinto " . $createFlag . " \"" . $spamFolder . "\";
		stop;
	}";
				} else {
					$content = $requireString . "

	# rule:[includeGlobal]
	if false # anyof (true)
	{
	### include global rule
	## /etc/dovecot/sieve/default.sieve
	include :global \"default\";
	}

	# rule:[Spam]
	if anyof (header :contains \"X-Spam-Flag\" \"YES\")
	{
		setflag \"\\\Seen\";
		fileinto " . $createFlag . " \"" . $spamFolder . "\";
		stop;
	}";
				}


				if (!$this->saveScript('default', $content)) {
					throw new \Exception("Could not create default sieve script: " . $this->error());
				}
				$this->activate('default');
				$active = 'default';
			} else {
				$this->activate($all_scripts[0]);
				$active = $all_scripts[0];
			}

		}
		return $active;
	}

	/**
	 * Loads script by name
	 */
	public function load($name): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if ($this->current == $name) {
			return true;
		}

		$script = $this->sieve->getScript($name);

		if ($this->_PEAR->isError($script))
			return $this->setError(self::SIEVE_ERROR_OTHER);

		// try to parse from Roundcube format
		$this->script = $this->parse($script);

		$this->current = $name;

		return true;
	}

	/**
	 * Loads script from text content
	 */
	public function loadScript($script)
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		// try to parse from Roundcube format
		$this->script = $this->parse($script);
	}

	/**
	 * Creates go_sieve_script object from text script
	 */
	private function parse(string $txt)
	{
		// try to parse from Roundcube format
		$script = new go_sieve_script($txt, $this->disabled, $this, true);

		// ... else try to import from different formats
		if (empty($script->content)) {
			$script = $this->importRules($txt);
			$script = new go_sieve_script($script, $this->disabled, $this, true);
		}

		return $script;
	}

	/**
	 * Gets specified script as text
	 */
	public function getSieveScriet(string $name)
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		$content = $this->sieve->getScript($name);

		//var_dump($content);

		if ($this->_PEAR->isError($content)) {
			return $this->setError(self::SIEVE_ERROR_OTHER);
		}

		return $content;
	}

	/**
	 * Creates empty script or copy of other script
	 */
	public function copy($name, $copy)
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if ($copy) {
			$content = $this->sieve->getScript($copy);

			if ($this->_PEAR->isError($content)) {
				return $this->setError(self::SIEVE_ERROR_OTHER);
			}
		}

		return $this->saveScript($name, $content);
	}

	private function importRules($script)
	{
		$i = 0;
		$name = array();
		$content = '';

		// Squirrelmail (Avelsieve)
		if ($tokens = preg_split('/(#START_SIEVE_RULE.*END_SIEVE_RULE)\n/', $script, -1, PREG_SPLIT_DELIM_CAPTURE)) {
			foreach ($tokens as $token) {
				if (preg_match('/^#START_SIEVE_RULE.*/', $token, $matches)) {
					$name[$i] = "unnamed rule " . ($i + 1);
					$content .= "# rule:[" . $name[$i] . "]\n";
				} elseif (isset($name[$i])) {
					// This preg_replace is added because I've found some Avelsieve scripts
					// with rules containing "if" here. I'm not sure it was working
					// before without this or not.
					$token = preg_replace('/^if\s+/', '', trim($token));
					$content .= "if $token\n";
					$i++;
				}
			}
		} // Horde (INGO)
		else if ($tokens = preg_split('/(# .+)\r?\n/i', $script, -1, PREG_SPLIT_DELIM_CAPTURE)) {
			foreach ($tokens as $token) {
				if (preg_match('/^# (.+)/i', $token, $matches)) {
					$name[$i] = $matches[1];
					$content .= "# rule:[" . $name[$i] . "]\n";
				} elseif (isset($name[$i])) {
					$token = str_replace(":comparator \"i;ascii-casemap\" ", "", $token);
					$content .= $token . "\n";
					$i++;
				}
			}
		}

		return $content;
	}

	private function setError($error)
	{
		$this->error = 'Errorcode: ' . $error;
		go()->debug("SIEVE ERROR: " . $error);
		return false;
	}

	/**
	 * This is our own debug handler for connection
	 */
	public function debugHandler($sieve, $message): void
	{
		go()->debug("SIEVE DEBUG: " . $message);
	}

}