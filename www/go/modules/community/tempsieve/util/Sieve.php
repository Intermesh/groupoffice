<?php

namespace go\modules\community\tempsieve\util;

use go\core\exception\Unauthorized;
use go\core\fs\Blob;
use go\core\orm\exception\SaveException;
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
	public $rawScript;                 // go_sieve_script object
	public $script; // temporary
	public ?string $current;                // name of currently loaded script
	private array $disabled;              // array of disabled extensions

	public string $blobId; // For JMAP, Sieve scripts are to be saved into blobs.

	public string $accountId;
	private PEAR $_PEAR;

	public function __construct()
	{
		$this->sieve = new Net_Sieve();
		$this->sieve->setDebug(go()->getConfig()['debug'], array($this, 'debugHandler'));
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

		if (isset(go()->getConfig()->sieve_rewrite_hosts)) {
			$maps = explode(',', go()->getConfig()->sieve_rewrite_hosts);
			foreach ($maps as $map) {
				$pair = explode('=', $map);
				if ($pair[0] == $host && isset($pair[1])) {
					$host = $pair[1];
				}
			}
		}

//		go()->debug("sieve::connect($username, ***, $host, $port, $auth_type, $usetls)");

		$options = null;
		// Hackish solution
		if (go()->getConfig()['debug']) {
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
		$this->accountId = md5($username);

		return true;
	}

	/**
	 * Check if an extension is available on the server
	 *
	 * @param string $extension
	 * @return boolean
	 * /
	 * public function hasExtension(string $extension): bool
	 * {
	 * return $this->sieve->hasExtension($extension);
	 * }*/


	/**
	 * In the JMAP scenario, a blob is being saved as the new sieve script.
	 *
	 * @param string|null $name
	 * @param Blob $blob
	 * @return bool
	 * @throws \Exception
	 */
	public function saveBlob(?string $name, Blob $blob): bool
	{
		$name = $name ?? $this->getActive($this->accountId);
		$file = $blob->getFile();
		$script = $file->getContents();
		if (!$script) {
			$script = '/ * empty script * /';
		}
		$res = $this->sieve->installScript($name, $script, true);
		if ($this->_PEAR->isError($res)) {
		    go()->debug("ERROR: " . $res);

		    return $this->setError(self::SIEVE_ERROR_INSTALL . '<br/>Error message:</br>' . $res);
		}
		return true;
	}

	/**
	 * Saves current script into server
	 *
	 * @param ?string $name
	 * @return bool
	 * /
	 * public function save(?string $name = null): bool
	 * {
	 * if (!$this->sieve) {
	 * return $this->setError(self::SIEVE_ERROR_INTERNAL);
	 * }
	 *
	 * if (!$this->script) {
	 * return $this->setError(self::SIEVE_ERROR_INTERNAL);
	 * }
	 *
	 * if (!$name) {
	 * $name = $this->current;
	 * }
	 *
	 * $this->_moveOutOfOfficeToEnd();
	 *
	 * $script = $this->script->as_text();
	 *
	 * if (!$script) {
	 * $script = '/ * empty script * /';
	 * }
	 *
	 * $res = $this->sieve->installScript($name, $script, true);
	 * if ($this->_PEAR->isError($res)) {
	 *
	 * go()->debug("ERROR: " . $res);
	 *
	 * return $this->setError(self::SIEVE_ERROR_INSTALL . '<br/>Error message:</br>' . $res);
	 * }
	 *
	 * return true;
	 * }
	 * @deprecated: to be shot into Sieve using managesieve in the JMAP controller. Somehow.
	 */
	/**
	 * Move the "Out of office" rule to the end of the sieve file.
	 * /
	 * private function _moveOutOfOfficeToEnd(): void
	 * {
	 *
	 *
	 * // De out of office rule altijd als laatste.
	 * foreach ($this->script->content as $key => $val) {
	 *
	 * if (isset($val['name']) && $val['name'] == 'Out of office') {
	 * $item = $this->script->content[$key];
	 * unset($this->script->content[$key]);
	 * array_push($this->script->content, $item);
	 * break;
	 * }
	 * }
	 * }*/

	/**
	 * Saves text script into server
	 *
	 * @param string $name
	 * @param string|null $content
	 * @return bool
	 * /
	 * public function saveScript(string $name, ?string $content = null): bool
	 * {
	 * if (!$this->sieve) {
	 * return $this->setError(self::SIEVE_ERROR_INTERNAL);
	 * }
	 *
	 * if (!$content) {
	 * $content = '/ * empty script * /';
	 * }
	 *
	 * $res = $this->sieve->installScript($name, $content);
	 * if ($this->_PEAR->isError($res)) {
	 * return $this->setError(self::SIEVE_ERROR_INSTALL);
	 * }
	 *
	 * return true;
	 * }*/

	/**
	 * Activates specified script
	 * @TODO? Refactor into JMAP
	 */
	public function activate(?string $name = null): bool
	{
		$name = $name ?? $this->current;

		if ($this->_PEAR->isError($this->sieve->setActive($name))) {
			return $this->setError(self::SIEVE_ERROR_ACTIVATE);
		}

		return true;
	}

	/**
	 * De-activates specified script
	 * @TODO? Refactor into JMAP
	 */
	public function deactivate(): bool
	{
		if ($this->_PEAR->isError($this->sieve->setActive(''))) {
			return $this->setError(self::SIEVE_ERROR_DEACTIVATE);
		}

		return true;
	}

	/**
	 * Removes specified script
	 * /
	 * public function remove(?string $name = null)
	 * {
	 * if (!$this->sieve) {
	 * return $this->setError(self::SIEVE_ERROR_INTERNAL);
	 * }
	 *
	 * if (!$name) {
	 * $name = $this->current;
	 * }
	 *
	 * // script must be deactivated first
	 * if ($name == $this->sieve->getActive())
	 * if ($this->_PEAR->isError($this->sieve->setActive('')))
	 * return $this->setError(self::SIEVE_ERROR_DELETE);
	 *
	 * if ($this->_PEAR->isError($this->sieve->removeScript($name)))
	 * return $this->setError(self::SIEVE_ERROR_DELETE);
	 *
	 * if ($name == $this->current)
	 * $this->current = null;
	 *
	 * return true;
	 * }*/

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
	public function getSieveScripts(): array|bool
	{
		if (!$this->list) {
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
	public function getActive(string $accountId): ?string
	{
		return  $this->sieve->getActive();
	}

	/**
	 * Loads script by name
	 * @throws SaveException
	 * @throws \Exception
	 */
	public function load(string $name): bool
	{
		if (!$this->sieve) {
			return $this->setError(self::SIEVE_ERROR_INTERNAL);
		}

		if (isset($this->current) && $this->current === $name) {
			return true;
		}

		$script = $this->sieve->getScript($name);

		if ($this->_PEAR->isError($script)) {
			return $this->setError(self::SIEVE_ERROR_OTHER);
		}
		// JMAP Sieve stuff requires a blobId to be saved or retrieved
		// For non-JMAP sieve this does not make sense; the script is already retrieved.
		// Both scenarios should probably be into separate classes, using the same interface

		$blob = Blob::fromString($script);
		$blob->name = $this->accountId . '_' . $name . '.siv';
		$blob->type = 'application/sieve';
		$blob->save();

		$this->blobId = $blob->id;

		$this->rawScript = $script;

		$this->current = $name;

		return true;
	}

	/**
	 * Wrapper for the CHECKSCRIPT ManageSieve command.
	 *
	 * @return bool
	 */
	public function validate(): bool
	{
		$stringLength = $this->sieve->_getLineLength($this->rawScript);
		$command      = sprintf(
			"CHECKSCRIPT {%d+}\r\n%s",
			$stringLength,
			$this->rawScript
		);

		$res = $this->sieve->_doCmd($command);
		if (is_a($res, 'PEAR_Error')) {
			go()->debug("ERROR: " . $res);
			return $this->setError($res->code . '<br/>Error message:</br>' . $res->message);
		}

		return true;
	}

	/**
	 * Wrapper function to save both a raw script and a blob. As stated elsewhere, the JMAP way of Sieve-ing should
	 * be separated from the non-JMAP way. We are not there yet.
	 *
	 * @param string $script
	 * @return void
	 * @throws \Exception
	 */
	public function setRawScript(string $script): void
	{
		$this->rawScript = $script;
		$blob = Blob::fromString($script);
		$blob->name = $this->accountId . '_' . $this->getActive($this->accountId) . '.siv';
		$blob->type = 'application/sieve';
		$blob->save();
		$this->blobId = $blob->id;
	}

	/**
	 * Creates empty script or copy of other script
	 * /
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
	*/


	private function setError(string $error): bool
	{
		$this->error = 'Errorcode: ' . $error;
		go()->debug("SIEVE ERROR: " . $error);
		return false;
	}

	public function getError(): string
	{
		return $this->error;
	}

	/**
	 * This is our own debug handler for connection
	 */
	public function debugHandler($sieve, $message): void
	{
		go()->debug("SIEVE DEBUG: " . $message);
	}

}