<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: sieve.class.inc.php 0000 2010-12-15 08:33:19Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

namespace GO\Sieve\Util;

use GO;
use GO\Base\Mail\Exception\ImapAuthenticationFailedException;
use GO\Email\Model\Account;

// make sure path_separator is defined
if (!defined('PATH_SEPARATOR')) {
	define('PATH_SEPARATOR', (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? ';' : ':');
}

$include_path = \GO::config()->root_path . 'go/vendor/pear/' . PATH_SEPARATOR;
$include_path.= ini_get('include_path');

if (set_include_path($include_path) === false) {
	die("Fatal error: ini_set/set_include_path does not work.");
}

require_once 'Net/Sieve.php';


define('SIEVE_ERROR_CONNECTION', 1);
define('SIEVE_ERROR_LOGIN', 2);
define('SIEVE_ERROR_NOT_EXISTS', 3);	// script not exists
define('SIEVE_ERROR_INSTALL', 4);	   // script installation
define('SIEVE_ERROR_ACTIVATE', 5);	  // script activation
define('SIEVE_ERROR_DELETE', 6);		// script deletion
define('SIEVE_ERROR_INTERNAL', 7);	  // internal error
define('SIEVE_ERROR_DEACTIVATE', 8);	// script activation
define('SIEVE_ERROR_OTHER', 255);	   // other/unknown error


class Sieve {

	private $sieve;				 // Net_Sieve object
	private $error = false;		 // error flag
	private $list = array();		// scripts list
	public $script;				 // go_sieve_script object
	public $current;				// name of currently loaded script
	private $disabled;			  // array of disabled extensions
	
	private $_PEAR;

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

	public function __construct() {
		$this->sieve = new \Net_Sieve();
		$this->sieve->setDebug(true, array($this, "debug_handler"));
		$this->_PEAR = new \PEAR();
	}

	private function rewrite_host($host) {
		if (isset(\GO::config()->sieve_rewrite_hosts)) {

			$maps = explode(',', \GO::config()->sieve_rewrite_hosts);

			foreach ($maps as $map) {
				$pair = explode('=', $map);

				if ($pair[0] == $host && isset($pair[1]))
					return $pair[1];
			}
		}

		return $host;
	}

	public function connect($username, $password='', $host='localhost', $port=2000, $auth_type=null, $usetls=true, $disabled=array(), $debug=false) {

		if (empty($password)) {
			throw new \AuthenticationRequiredException('Wrong password');
		}
		$host = $this->rewrite_host($host);

		\GO::debug("sieve::connect($username, ***, $host, $port, $auth_type, $usetls)");
		
		if ($this->_PEAR->isError($this->sieve->connect($host, $port, NULL, $usetls))) {
			return $this->_set_error(SIEVE_ERROR_CONNECTION);
		}

		if ($this->_PEAR->isError($this->sieve->login($username, $password,
								$auth_type ? strtoupper($auth_type) : null))
		) {
			return $this->_set_error(SIEVE_ERROR_LOGIN);
		}
		$this->disabled = $disabled;
	
		return true;
	}

	public function __destruct() {
	  try {
      $this->sieve->disconnect();
    }
    catch(Exception $e) {
	    //ignore in production
      go()->warn($e);
    }
	}

	/**
	 * Getter for error code
	 */
	public function error() {
		return $this->error ? $this->error : false;
	}

	/**
	 * Check if an extension is available on the server
	 * 
	 * @param string $extension
	 * @return boolean
	 */
	public function hasExtension($extension){
		return $this->sieve->hasExtension($extension);
	}
	
	/**
	 * Saves current script into server
	 */
	public function save($name = null) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if (!$this->script)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if (!$name)
			$name = $this->current;

		$this->_moveOutOfOfficeToEnd();
		
		$script = $this->script->as_text();
		
		if (!$script)
			$script = '/* empty script */';

		$res = $this->sieve->installScript($name, $script, true);
		if ($this->_PEAR->isError($res)){
			
			\GO::debug("ERROR: ".$res);
			
			return $this->_set_error(SIEVE_ERROR_INSTALL.'<br/>Error message:</br>'.$res);
		}

		return true;
	}
	
	/**
	 * Move the "Out of office" rule to the end of the sieve file.
	 */
	private function _moveOutOfOfficeToEnd(){
		
	
		// De out of office rule altijd als laatste.
			foreach($this->script->content as $key => $val) {
				
				if(isset($val['name']) && $val['name'] == 'Out of office') {
					$item = $this->script->content[$key];
					unset($this->script->content[$key]);
					array_push($this->script->content, $item); 
					break;
				}
			}
	}

	/**
	 * Saves text script into server
	 */
	public function save_script($name, $content = null) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if (!$content)
			$content = '/* empty script */';
		
		$res = $this->sieve->installScript($name, $content);
		if ($this->_PEAR->isError($res)) {
			return $this->_set_error(SIEVE_ERROR_INSTALL);
		}

		return true;
	}

	/**
	 * Creates a script for the spam filter
	 */
	public function createSpamScript($test) {
		$test['test'] = 'X-Spam-Flag';
		$test['not'] = 'false';
		$test['type'] = 'contains';
		$test['arg'] = 'YES';
		$test['arg1'] = '';
		$test['arg2'] = '';

		return $test;
	}

	/**
	 * Activates specified script
	 */
	public function activate($name = null) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if (!$name)
			$name = $this->current;

		if ($this->_PEAR->isError($this->sieve->setActive($name)))
			return $this->_set_error(SIEVE_ERROR_ACTIVATE);

		return true;
	}

	/**
	 * De-activates specified script
	 */
	public function deactivate() {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if ($this->_PEAR->isError($this->sieve->setActive('')))
			return $this->_set_error(SIEVE_ERROR_DEACTIVATE);

		return true;
	}

	/**
	 * Removes specified script
	 */
	public function remove($name = null) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if (!$name)
			$name = $this->current;

		// script must be deactivated first
		if ($name == $this->sieve->getActive())
			if ($this->_PEAR->isError($this->sieve->setActive('')))
				return $this->_set_error(SIEVE_ERROR_DELETE);

		if ($this->_PEAR->isError($this->sieve->removeScript($name)))
			return $this->_set_error(SIEVE_ERROR_DELETE);

		if ($name == $this->current)
			$this->current = null;

		return true;
	}

	/**
	 * Gets list of supported by server Sieve extensions
	 */
	public function get_extensions() {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		$ext = $this->sieve->getExtensions();
		// we're working on lower-cased names
		$ext = array_map('strtolower', (array) $ext);

		if ($this->script) {
			$supported = $this->script->get_extensions();
			foreach ($ext as $idx => $ext_name)
				if (!in_array($ext_name, $supported))
					unset($ext[$idx]);
		}

		return array_values($ext);
	}

	/**
	 * Gets list of scripts from server
	 */
	public function get_scripts() {
		if (!$this->list) {

			if (!$this->sieve)
				return $this->_set_error(SIEVE_ERROR_INTERNAL);

			$this->list = $this->sieve->listScripts();

			if ($this->_PEAR->isError($this->list))
				return $this->_set_error(SIEVE_ERROR_OTHER);
		}

		return $this->list;
	}

	/**
	 * Returns active script name
	 */
	public function get_active($accountId) {
		$aliasEmails = array();
		$aliasesStmt = \GO\Email\Model\Alias::model()->findByAttribute('account_id',$accountId);

		$account_model = array(
			'account_id'	=> $accountId,
		);
		$account = Account::model()->findByPk($account_model['account_id']);
		$spamFolder = isset(GO::config()->spam_folder) ? GO::config()->spam_folder : $account->spam;
		if (empty($spamFolder)) {
			$spamFolder = 'Spam';
    }

		while ($aliasModel = $aliasesStmt->fetch())
			$aliasEmails[] = $aliasModel->email;
		
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		$active = $this->sieve->getActive();
		
		
		if (!$active) {
			
			$all_scripts = $this->get_scripts();
			
			if(empty($all_scripts)){

				$createFlag = '';
				$require = array('fileinto', 'imap4flags', 'include');

				if($this->sieve->hasExtension('vacation')){
					$require[] = 'vacation';
				}
				
				// Check if the "mailbox" extension is supported
				if($this->sieve->hasExtension('mailbox')){
					$require[] = 'mailbox';
					$createFlag = ':create';
				}
								
				$requireString = 'require ["'.implode('","', $require).'"];';

				// If vacation is available then add that rule to the default sieve script.
				if($this->sieve->hasExtension('vacation')){
					
				$content = $requireString."

	# rule:[includeGlobal]
	if false # anyof (true)
	{
	### include global rule
	## /etc/dovecot/sieve/default.sieve
	include :global \"default\";
	}

	# rule:[".GO::t("Standard vacation rule", "sieve")."]
	if false # anyof (true)
	{
	\tvacation :days 3 :addresses [\"".implode('","',$aliasEmails)."\"] \"".GO::t("I am on vacation", "sieve")."\";
	\tstop;
	}

	# rule:[Spam]
	if anyof (header :contains \"X-Spam-Flag\" \"YES\")
	{
		setflag \"\\\Seen\";
		fileinto ".$createFlag." \"".$spamFolder."\";
		stop;
	}";
				} else {
					$content = $requireString."

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
		fileinto ".$createFlag." \"".$spamFolder."\";
		stop;
	}";
				}
			
				
				if(!$this->save_script('default', $content)) {
					throw new \Exception("Could not create default sieve script: ".$this->error());
				}
				$this->activate('default');
				$active = 'default';
			}else
			{
				$this->activate($all_scripts[0]);
				$active = $all_scripts[0];
			}
			
		}
		return $active;
	}

	/**
	 * Loads script by name
	 */
	public function load($name) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if ($this->current == $name)
			return true;

		$script = $this->sieve->getScript($name);
		
		if ($this->_PEAR->isError($script))
			return $this->_set_error(SIEVE_ERROR_OTHER);

		// try to parse from Roundcube format
		$this->script = $this->_parse($script);

		$this->current = $name;

		return true;
	}

	/**
	 * Loads script from text content
	 */
	public function load_script($script) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		// try to parse from Roundcube format
		$this->script = $this->_parse($script);
	}

	/**
	 * Creates go_sieve_script object from text script
	 */
	private function _parse($txt) {
		// try to parse from Roundcube format
		$script = new go_sieve_script($txt, $this->disabled, $this, true);

		// ... else try to import from different formats
		if (empty($script->content)) {
			$script = $this->_import_rules($txt);
			$script = new go_sieve_script($script, $this->disabled, $this, true);
		}

		// replace all elsif with if+stop, we support only ifs
    //
    // Stop rule is inserted client side now
    // 
//		foreach ($script->content as $idx => $rule) {
//			if (!isset($script->content[$idx + 1])
//					|| preg_match('/^else|elsif$/', $script->content[$idx + 1]['type'])) {
//				// 'stop' not found?
//				if (!preg_match('/^(stop|vacation)$/', $rule['actions'][count($rule['actions']) - 1]['type'])) {
//					$script->content[$idx]['actions'][] = array(
//						'type' => 'stop'
//					);
//				}
//			}
//		}

		return $script;
	}

	/**
	 * Gets specified script as text
	 */
	public function get_script($name) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		$content = $this->sieve->getScript($name);

		//var_dump($content);

		if ($this->_PEAR->isError($content))
			return $this->_set_error(SIEVE_ERROR_OTHER);

		return $content;
	}

	/**
	 * Creates empty script or copy of other script
	 */
	public function copy($name, $copy) {
		if (!$this->sieve)
			return $this->_set_error(SIEVE_ERROR_INTERNAL);

		if ($copy) {
			$content = $this->sieve->getScript($copy);

			if ($this->_PEAR->isError($content))
				return $this->_set_error(SIEVE_ERROR_OTHER);
		}

		return $this->save_script($name, $content);
	}

	private function _import_rules($script) {
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
		}
		// Horde (INGO)
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

	private function _set_error($error) {
		$this->error = 'Errorcode: '.$error;
		\GO::debug("SIEVE ERROR: ".$error);
		return false;
	}

	/**
	 * This is our own debug handler for connection
	 */
	public function debug_handler(&$sieve, $message) {
		//write_log('sieve', preg_replace('/\r\n$/', '', $message));
//		if (isset(\GO::config()))
			\GO::debug("SIEVE DEBUG: ".$message);
	}

}

/**
 *  Class for operations on Sieve scripts
 *
 * Copyright (C) 2008-2011, The Roundcube Dev Team
 * Copyright (C) 2011, Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class go_sieve_script
{
    public $content = array();      // script rules array

		public $sieve = false;
		
    private $vars = array();        // "global" variables
    private $prefix = '';           // script header (comments)
    private $supported = array(     // Sieve extensions supported by class
        'body',                     // RFC5173
        'copy',                     // RFC3894
        'date',                     // RFC5260
        'enotify',                  // RFC5435
        'envelope',                 // RFC5228
        'ereject',                  // RFC5429
        'fileinto',                 // RFC5228
        'imapflags',                // draft-melnikov-sieve-imapflags-06
        'imap4flags',               // RFC5232
        'include',                  // draft-ietf-sieve-include-12
        'index',                    // RFC5260
        'notify',                   // draft-martin-sieve-notify-01,
        'regex',                    // draft-ietf-sieve-regex-01
        'reject',                   // RFC5429
        'relational',               // RFC3431
        'subaddress',               // RFC5233
        'vacation',                 // RFC5230
        'vacation-seconds',         // RFC6131
        'variables',								// RFC5229
				'mailbox'										// RFC5490
        // @TODO: spamtest+virustest
    );

    /**
     * Object constructor
     *
     * @param  StringHelper  Script's text content
     * @param  array   List of capabilities supported by server
     */
    public function __construct($script, $capabilities=array(), GO\Sieve\Util\Sieve $sieve, $getCapabilitiesFromServer=false)
    {
			
			$this->sieve = $sieve;
			
			if($getCapabilitiesFromServer){
				
				\GO::debug("===== SIEVE GET CAPABILITIES FROM SERVER =====");
				$capabilities = $this->sieve->get_extensions();
			}

			$capabilities = array_map('strtolower', (array) $capabilities);

			\GO::debug("====== SIEVE SUPPORTED FUNCTIONS BY SERVER =====");
			\GO::debug(var_export($capabilities,true));
			\GO::debug("== END OF SIEVE SUPPORTED FUNCTIONS BY SERVER ==");

			// disable features by server capabilities
			if (!empty($capabilities)) {
					foreach ($this->supported as $idx => $ext) {
							if (!in_array($ext, $capabilities)) {
									unset($this->supported[$idx]);
							}
					}
			}

			// Parse text content of the script
			$this->_parse_text($script);
    }

    /**
     * Adds rule to the script (at the end)
     *
     * @param string Rule name
     * @param array  Rule content (as array)
     *
     * @return int The index of the new rule
     */
    public function add_rule($content,$index=-1)
    {
			if($index > -1){
				// Insert the rule at the 
				array_splice($this->content, $index,0,array($content));
			} else {
//				TODO: check this->supported
        array_push($this->content, $content);
			}
      return sizeof($this->content)-1;
    }

    public function delete_rule($index)
    {
        if(isset($this->content[$index])) {
            unset($this->content[$index]);
            return true;
        }
        return false;
    }

    public function size()
    {
        return sizeof($this->content);
    }

    public function update_rule($index, $content)
    {
        // TODO: check this->supported
        if ($this->content[$index]) {
            $this->content[$index] = $content;
            return $index;
        }
        return false;
    }

    /**
     * Sets "global" variable
     *
     * @param string $name  Variable name
     * @param string $value Variable value
     * @param array  $mods  Variable modifiers
     */
    public function set_var($name, $value, $mods = array())
    {
        // Check if variable exists
        for ($i=0, $len=count($this->vars); $i<$len; $i++) {
            if ($this->vars[$i]['name'] == $name) {
                break;
            }
        }

        $var = array_merge($mods, array('name' => $name, 'value' => $value));
        $this->vars[$i] = $var;
    }

    /**
     * Unsets "global" variable
     *
     * @param string $name  Variable name
     */
    public function unset_var($name)
    {
        // Check if variable exists
        foreach ($this->vars as $idx => $var) {
            if ($var['name'] == $name) {
                unset($this->vars[$idx]);
                break;
            }
        }
    }

    /**
     * Gets the value of  "global" variable
     *
     * @param string $name  Variable name
     *
     * @return string Variable value
     */
    public function get_var($name)
    {
        // Check if variable exists
        for ($i=0, $len=count($this->vars); $i<$len; $i++) {
            if ($this->vars[$i]['name'] == $name) {
                return $this->vars[$i]['name'];
            }
        }
    }

    /**
     * Sets script header content
     *
     * @param string $text  Header content
     */
    public function set_prefix($text)
    {
        $this->prefix = $text;
    }

    /**
     * Returns script as text
     */
    public function as_text()
    {
        $output = '';
        $exts   = array();
        $idx    = 0;

        if (!empty($this->vars)) {
            if (in_array('variables', (array)$this->supported)) {
                $has_vars = true;
                array_push($exts, 'variables');
            }
            foreach ($this->vars as $var) {
                if (empty($has_vars)) {
                    // 'variables' extension not supported, put vars in comments
                    $output .= sprintf("# %s %s\n", $var['name'], $var['value']);
                }
                else {
                    $output .= 'set ';
                    foreach (array_diff(array_keys($var), array('name', 'value')) as $opt) {
                        $output .= ":$opt ";
                    }
                    $output .= self::escape_string($var['name']) . ' ' . self::escape_string($var['value']) . ";\n";
                }
            }
        }

				\GO::debug("====== SIEVE SUPPORTED FUNCTIONS BY GO =====");
				\GO::debug(var_export($this->supported,true));
				\GO::debug("== END OF SIEVE SUPPORTED FUNCTIONS BY GO ==");
				
        $imapflags = in_array('imap4flags', $this->supported) ? 'imap4flags' : 'imapflags';
        $notify    = in_array('enotify', $this->supported) ? 'enotify' : 'notify';

        // rules
        foreach ($this->content as $rule) {
            $script    = '';
            $tests     = array();
            $i         = 0;

            // header
            if (!empty($rule['name']) && strlen($rule['name'])) {
                $script .= '# rule:[' . $rule['name'] . "]\n";
            }

            // constraints expressions
            if (!empty($rule['tests'])) {
                foreach ($rule['tests'] as $test) {
                    $tests[$i] = '';
                    switch ($test['test']) {
                    case 'size':
                        $tests[$i] .= ($test['not'] ? 'not ' : '');
                        $tests[$i] .= 'size :' . ($test['type']=='under' ? 'under ' : 'over ') . $test['arg'];
                        break;

                    case 'true':
                        $tests[$i] .= ($test['not'] ? 'false' : 'true');
                        break;

                    case 'exists':
                        $tests[$i] .= ($test['not'] ? 'not ' : '');
                        $tests[$i] .= 'exists ' . self::escape_string($test['arg']);
                        break;

                    case 'header':
                        $tests[$i] .= ($test['not'] ? 'not ' : '');
                        $tests[$i] .= 'header';

                        $this->add_index($test, $tests[$i], $exts);
                        $this->add_operator($test, $tests[$i], $exts);

                        $tests[$i] .= ' ' . self::escape_string($test['arg1']);
                        $tests[$i] .= ' ' . self::escape_string($test['arg2']);
                        break;

                    case 'address':
                    case 'envelope':
                        if ($test['test'] == 'envelope') {
                            array_push($exts, 'envelope');
                        }

                        $tests[$i] .= ($test['not'] ? 'not ' : '');
                        $tests[$i] .= $test['test'];

                        if ($test['test'] != 'envelope') {
                            $this->add_index($test, $tests[$i], $exts);
                        }

                        // :all address-part is optional, skip it
                        if (!empty($test['part']) && $test['part'] != 'all') {
                            $tests[$i] .= ' :' . $test['part'];
                            if ($test['part'] == 'user' || $test['part'] == 'detail') {
                                array_push($exts, 'subaddress');
                            }
                        }

                        $this->add_operator($test, $tests[$i], $exts);

                        $tests[$i] .= ' ' . self::escape_string($test['arg1']);
                        $tests[$i] .= ' ' . self::escape_string($test['arg2']);
                        break;

                    case 'body':
                        array_push($exts, 'body');

                        $tests[$i] .= ($test['not'] ? 'not ' : '') . 'body';
							  if(empty($test['part']) && $test['type'] === 'contains') {
								  $test['part'] = 'text';
							  }

                        if (!empty($test['part'])) {
                            $tests[$i] .= ' :' . $test['part'];

                            if (!empty($test['content']) && $test['part'] == 'content') {
                                $tests[$i] .= ' ' . self::escape_string($test['content']);
                            }
                        }

                        $this->add_operator($test, $tests[$i], $exts);

                        $tests[$i] .= ' ' . self::escape_string($test['arg']);
                        break;

                    case 'date':
                    case 'currentdate':
                        array_push($exts, 'date');

                        $tests[$i] .= ($test['not'] ? 'not ' : '') . $test['test'];

                        $this->add_index($test, $tests[$i], $exts);

                        if (!empty($test['originalzone']) && $test['test'] == 'date') {
                            $tests[$i] .= ' :originalzone';
                        }
                        else if (!empty($test['zone'])) {
                            $tests[$i] .= ' :zone ' . self::escape_string($test['zone']);
                        }

                        $this->add_operator($test, $tests[$i], $exts);

                        if ($test['test'] == 'date') {
                            $tests[$i] .= ' ' . self::escape_string($test['header']);
                        }

                        $tests[$i] .= ' ' . self::escape_string($test['part']);
                        $tests[$i] .= ' ' . self::escape_string($test['arg']);

                        break;
                    }
                    $i++;
                }
            }

            // disabled rule: if false #....
            if (!empty($tests)) {
                $script .= 'if ' . ($rule['disabled'] ? 'false # ' : '');

                if (count($tests) > 1) {
                    $tests_str = implode(', ', $tests);
                }
                else {
                    $tests_str = $tests[0];
                }

                if ($rule['join'] || count($tests) > 1) {
                    $script .= sprintf('%s (%s)', $rule['join'] ? 'allof' : 'anyof', $tests_str);
                }
                else {
                    $script .= $tests_str;
                }
                $script .= "\n{\n";
            }

            // action(s)
            if (!empty($rule['actions'])) {
                foreach ($rule['actions'] as $action) {
                    $action_script = '';

                    switch ($action['type']) {

                    case 'fileinto':
                        array_push($exts, 'fileinto');
											
//												if($this->sieve->hasExtension('mailbox')){
//													array_push($exts, 'mailbox');
//													$action_script .= 'fileinto :create ';
//												} else {
													$action_script .= 'fileinto ';
//												}
												
                        if (!empty($action['copy'])) {
                            $action_script .= ':copy ';
                            array_push($exts, 'copy');
                        }
                        $action_script .= self::escape_string($action['target']);
                        break;

                    case 'redirect':
                        $action_script .= 'redirect ';
                        if (!empty($action['copy'])) {
                            $action_script .= ':copy ';
                            array_push($exts, 'copy');
                        }
                        $action_script .= self::escape_string($action['target']);
                        break;

                    case 'reject':
                    case 'ereject':
                        array_push($exts, $action['type']);
                        $action_script .= $action['type'].' '
                            . self::escape_string($action['target']);
                        break;

                    case 'addflag':
                    case 'setflag':
                    case 'removeflag':
                        array_push($exts, $imapflags);
												$action_script .= $action['type'].' '.self::escape_string($action['target']);
                        break;

                    case 'keep':
                    case 'discard':
                    case 'stop':
                        $action_script .= $action['type'];
                        break;

                    case 'include':
                        array_push($exts, 'include');
                        $action_script .= 'include ';
                        foreach (array_diff(array_keys($action), array('target', 'type')) as $opt) {
                            $action_script .= ":$opt ";
                        }
                        $action_script .= self::escape_string($action['target']);
                        break;

                    case 'set':
                        array_push($exts, 'variables');
                        $action_script .= 'set ';
                        foreach (array_diff(array_keys($action), array('name', 'value', 'type')) as $opt) {
                            $action_script .= ":$opt ";
                        }
                        $action_script .= self::escape_string($action['name']) . ' ' . self::escape_string($action['value']);
                        break;

                    case 'notify':
                        array_push($exts, $notify);
                        $action_script .= 'notify';

                        $method = $action['method'];
                        unset($action['method']);
                        $action['options'] = (array) $action['options'];

                        // Here we support draft-martin-sieve-notify-01 used by Cyrus
                        if ($notify == 'notify') {
                            switch ($action['importance']) {
                                case 1: $action_script .= " :high"; break;
                                //case 2: $action_script .= " :normal"; break;
                                case 3: $action_script .= " :low"; break;
                            }

                            // Old-draft way: :method "mailto" :options "email@address"
                            if (!empty($method)) {
                                $parts = explode(':', $method, 2);
                                $action['method'] = $parts[0];
                                array_unshift($action['options'], $parts[1]);
                            }

                            unset($action['importance']);
                            unset($action['from']);
                            unset($method);
                        }

                        foreach (array('id', 'importance', 'method', 'options', 'from', 'message') as $n_tag) {
                            if (!empty($action[$n_tag])) {
                                $action_script .= " :$n_tag " . self::escape_string($action[$n_tag]);
                            }
                        }

                        if (!empty($method)) {
                            $action_script .= ' ' . self::escape_string($method);
                        }

                        break;

                    case 'vacation':
                        array_push($exts, 'vacation');
                        $action_script .= 'vacation';
                        if (isset($action['seconds'])) {
                            array_push($exts, 'vacation-seconds');
                            $action_script .= " :seconds " . intval($action['seconds']);
                        }
                        else if (!empty($action['days'])) {
                            $action_script .= " :days " . intval($action['days']);
                        }
                        if (!empty($action['addresses']))
                            $action_script .= " :addresses " . self::escape_string($action['addresses']);
                        if (!empty($action['subject']))
                            $action_script .= " :subject " . self::escape_string($action['subject']);
                        if (!empty($action['handle']))
                            $action_script .= " :handle " . self::escape_string($action['handle']);
                        if (!empty($action['from']))
                            $action_script .= " :from " . self::escape_string($action['from']);
                        if (!empty($action['mime']))
                            $action_script .= " :mime";
                        $action_script .= " " . self::escape_string($action['reason']);
                        break;
                    }

                    if ($action_script) {
                        $script .= !empty($tests) ? "\t" : '';
                        $script .= $action_script . ";\n";
                    }
                }
            }

            if ($script) {
                $output .= $script . (!empty($tests) ? "}\n" : '');
                $idx++;
            }
        }

        // requires
        if (!empty($exts)) {
            $exts = array_unique($exts);

            if (in_array('vacation-seconds', $exts) && ($key = array_search('vacation', $exts)) !== false) {
                unset($exts[$key]);
            }

            sort($exts); // for convenience use always the same order

            $output = 'require ["' . implode('","', $exts) . "\"];\n" . $output;
        }

        if (!empty($this->prefix)) {
            $output = $this->prefix . "\n\n" . $output;
        }

        return $output;
    }

    /**
     * Returns script object
     *
     */
    public function as_array()
    {
        return $this->content;
    }

    /**
     * Returns array of supported extensions
     *
     */
    public function get_extensions()
    {
        return array_values($this->supported);
    }

    /**
     * Converts text script to rules array
     *
     * @param string Text script
     */
    private function _parse_text($script)
    {
        $prefix     = '';
        $options = array();

        while ($script) {
            $script = trim($script);
            $rule   = array();

            // Comments
            while (!empty($script) && $script[0] == '#') {
                $endl = strpos($script, "\n");
                $line = $endl ? substr($script, 0, $endl) : $script;

                // Roundcube format
                if (preg_match('/^# rule:\[(.*)\]/', $line, $matches)) {
                    $rulename = $matches[1];
                }
                // KEP:14 variables
                else if (preg_match('/^# (EDITOR|EDITOR_VERSION) (.+)$/', $line, $matches)) {
                    $this->set_var($matches[1], $matches[2]);
                }
                // Horde-Ingo format
                else if (!empty($options['format']) && $options['format'] == 'INGO'
                    && preg_match('/^# (.*)/', $line, $matches)
                ) {
                    $rulename = $matches[1];
                }
                else if (empty($options['prefix'])) {
                    $prefix .= $line . "\n";
                }

                $script = ltrim(substr($script, strlen($line) + 1));
            }

            // handle script header
            if (empty($options['prefix'])) {
                $options['prefix'] = true;
                if ($prefix && strpos($prefix, 'horde.org/ingo')) {
                    $options['format'] = 'INGO';
                }
            }

            // Control structures/blocks
            if (preg_match('/^(if|else|elsif)/i', $script)) {
                $rule = $this->_tokenize_rule($script);
                if (strlen($rulename) && !empty($rule)) {
                    $rule['name'] = $rulename;
                }
            }
            // Simple commands
            else {
                $rule = $this->_parse_actions($script, ';');
                if (!empty($rule[0]) && is_array($rule)) {
                    // set "global" variables
                    if ($rule[0]['type'] == 'set') {
                        unset($rule[0]['type']);
                        $this->vars[] = $rule[0];
                        unset($rule);
                    }
                    else {
                        $rule = array('actions' => $rule);
                    }
                }
            }

            $rulename = '';

            if (!empty($rule)) {
                $this->content[] = $rule;
            }
        }

        if (!empty($prefix)) {
            $this->prefix = trim($prefix);
        }
    }

    /**
     * Convert text script fragment to rule object
     *
     * @param string Text rule
     *
     * @return array Rule data
     */
    private function _tokenize_rule(&$content)
    {
        $cond = strtolower(self::tokenize($content, 1));

        if ($cond != 'if' && $cond != 'elsif' && $cond != 'else') {
            return null;
        }

        $disabled = false;
        $join     = false;

        // disabled rule (false + comment): if false # .....
        if (preg_match('/^\s*false\s+#/i', $content)) {
            $content = preg_replace('/^\s*false\s+#\s*/i', '', $content);
            $disabled = true;
        }

        while (strlen($content)) {
            $tokens = self::tokenize($content, true);
            $separator = array_pop($tokens);

            if (!empty($tokens)) {
                $token = array_shift($tokens);
            }
            else {
                $token = $separator;
            }

            $token = strtolower($token);

            if ($token == 'not') {
                $not = true;
                $token = strtolower(array_shift($tokens));
            }
            else {
                $not = false;
            }

            switch ($token) {
            case 'allof':
                $join = true;
                break;
            case 'anyof':
                break;

            case 'size':
                $test = array('test' => 'size', 'not'  => $not);

                $test['arg'] = array_pop($tokens);

                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (!is_array($tokens[$i])
                        && preg_match('/^:(under|over)$/i', $tokens[$i])
                    ) {
                        $test['type'] = strtolower(substr($tokens[$i], 1));
                    }
                }

                $tests[] = $test;
                break;

            case 'header':
            case 'address':
            case 'envelope':
                $test = array('test' => $token, 'not' => $not);

                $test['arg2'] = array_pop($tokens);
                $test['arg1'] = array_pop($tokens);

                $test += $this->test_tokens($tokens);

                if ($token != 'header' && !empty($tokens)) {
                    for ($i=0, $len=count($tokens); $i<$len; $i++) {
                        if (!is_array($tokens[$i]) && preg_match('/^:(localpart|domain|all|user|detail)$/i', $tokens[$i])) {
                            $test['part'] = strtolower(substr($tokens[$i], 1));
                        }
                    }
                }

                $tests[] = $test;
                break;

            case 'body':
                $test = array('test' => 'body', 'not' => $not);

                $test['arg'] = array_pop($tokens);

                $test += $this->test_tokens($tokens);

                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (!is_array($tokens[$i]) && preg_match('/^:(raw|content|text)$/i', $tokens[$i])) {
                        $test['part'] = strtolower(substr($tokens[$i], 1));

                        if ($test['part'] == 'content') {
                            $test['content'] = $tokens[++$i];
                        }
                    }
                }

                $tests[] = $test;
                break;

            case 'date':
            case 'currentdate':
                $test = array('test' => $token, 'not' => $not);

                $test['arg']  = array_pop($tokens);
                $test['part'] = array_pop($tokens);

                if ($token == 'date') {
                    $test['header']  = array_pop($tokens);
                }

                $test += $this->test_tokens($tokens);

                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (!is_array($tokens[$i]) && preg_match('/^:zone$/i', $tokens[$i])) {
                        $test['zone'] = $tokens[++$i];
                    }
                    else if (!is_array($tokens[$i]) && preg_match('/^:originalzone$/i', $tokens[$i])) {
                        $test['originalzone'] = true;
                    }
                }

                $tests[] = $test;
                break;

            case 'exists':
                $tests[] = array('test' => 'exists', 'not'  => $not,
                    'arg'  => array_pop($tokens));
                break;

            case 'true':
                $tests[] = array('test' => 'true', 'not'  => $not);
                break;

            case 'false':
                $tests[] = array('test' => 'true', 'not'  => !$not);
                break;
            }

            // goto actions...
            if ($separator == '{') {
                break;
            }
        }

        // ...and actions block
        $actions = $this->_parse_actions($content);

        $result = array(
            'type'     => $cond,
            'tests'    => $tests ? $tests : [],
            'actions'  => $actions ? $actions : [],
            'join'     => $join,
            'disabled' => $disabled,
        );

        return $result;
    }

    /**
     * Parse body of actions section
     *
     * @param string $content  Text body
     * @param string $end      End of text separator
     *
     * @return array Array of parsed action type/target pairs
     */
    private function _parse_actions(&$content, $end = '}')
    {
        $result = null;

        while (strlen($content)) {
            $tokens    = self::tokenize($content, true);
            $separator = array_pop($tokens);
            $token     = !empty($tokens) ? array_shift($tokens) : $separator;

            switch ($token) {
            case 'discard':
            case 'keep':
            case 'stop':
                $result[] = array('type' => $token);
                break;

            case 'fileinto':
            case 'redirect':
                $action  = array('type' => $token, 'target' => array_pop($tokens));
                $args    = array('copy');
                $action += $this->action_arguments($tokens, $args);

                $result[] = $action;
                break;

            case 'vacation':
                $action  = array('type' => 'vacation', 'reason' => array_pop($tokens));
                $args    = array('mime');
                $vargs   = array('seconds', 'days', 'addresses', 'subject', 'handle', 'from');
                $action += $this->action_arguments($tokens, $args, $vargs);

                $result[] = $action;
                break;

            case 'reject':
            case 'ereject':
            case 'setflag':
            case 'addflag':
            case 'removeflag':
                $result[] = array('type' => $token, 'target' => array_pop($tokens));
                break;

            case 'include':
                $action  = array('type' => 'include', 'target' => array_pop($tokens));
                $args    = array('once', 'optional', 'global', 'personal');
                $action += $this->action_arguments($tokens, $args);

                $result[] = $action;
                break;

            case 'set':
                $action  = array('type' => 'set', 'value' => array_pop($tokens), 'name' => array_pop($tokens));
                $args    = array('lower', 'upper', 'lowerfirst', 'upperfirst', 'quotewildcard', 'length');
                $action += $this->action_arguments($tokens, $args);

                $result[] = $action;
                break;

            case 'require':
                // skip, will be build according to used commands
                // $result[] = array('type' => 'require', 'target' => array_pop($tokens));
                break;

            case 'notify':
                $action     = array('type' => 'notify');
                $priorities = array('high' => 1, 'normal' => 2, 'low' => 3);
                $vargs      = array('from', 'id', 'importance', 'options', 'message', 'method');
                $args       = array_keys($priorities);
                $action    += $this->action_arguments($tokens, $args, $vargs);

                // Here we'll convert draft-martin-sieve-notify-01 into RFC 5435
                if (!isset($action['importance'])) {
                    foreach ($priorities as $key => $val) {
                        if (isset($action[$key])) {
                            $action['importance'] = $val;
                            unset($action[$key]);
                        }
                    }
                }

                $action['options'] = isset($action['options']) ? (array) $action['options'] : [];

                // Old-draft way: :method "mailto" :options "email@address"
                if (!empty($action['method']) && !empty($action['options'])) {
                    $action['method'] .= ':' . array_shift($action['options']);
                }
                // unnamed parameter is a :method in enotify extension
                else if (!isset($action['method'])) {
                    $action['method'] = array_pop($tokens);
                }

                $result[] = $action;
                break;
            }

            if ($separator == $end)
                break;
        }

        return $result;
    }

    /**
     * Add comparator to the test
     */
    private function add_comparator($test, &$out, &$exts)
    {
        if (empty($test['comparator'])) {
            return;
        }

        if ($test['comparator'] == 'i;ascii-numeric') {
            array_push($exts, 'relational');
            array_push($exts, 'comparator-i;ascii-numeric');
        }
        else if (!in_array($test['comparator'], array('i;octet', 'i;ascii-casemap'))) {
            array_push($exts, 'comparator-' . $test['comparator']);
        }

        // skip default comparator
        if ($test['comparator'] != 'i;ascii-casemap') {
            $out .= ' :comparator ' . self::escape_string($test['comparator']);
        }
    }

    /**
     * Add index argument to the test
     */
    private function add_index($test, &$out, &$exts)
    {
        if (!empty($test['index'])) {
            array_push($exts, 'index');
            $out .= ' :index ' . intval($test['index']) . ($test['last'] ? ' :last' : '');
        }
    }

    /**
     * Add operators to the test
     */
    private function add_operator($test, &$out, &$exts)
    {
        if (empty($test['type'])) {
            return;
        }

        // relational operator
        if (preg_match('/^(value|count)-([gteqnl]{2})/', $test['type'], $m)) {
            array_push($exts, 'relational');

            $out .= ' :' . $m[1] . ' "' . $m[2] . '"';
        }
        else {
            if ($test['type'] == 'regex') {
                array_push($exts, 'regex');
            }

            $out .= ' :' . $test['type'];
        }

        $this->add_comparator($test, $out, $exts);
    }

    /**
     * Extract test tokens
     */
    private function test_tokens(&$tokens)
    {
        $test   = array();
        $result = array();

        for ($i=0, $len=count($tokens); $i<$len; $i++) {
            if (!is_array($tokens[$i]) && preg_match('/^:comparator$/i', $tokens[$i])) {
                $test['comparator'] = $tokens[++$i];
            }
            else if (!is_array($tokens[$i]) && preg_match('/^:(count|value)$/i', $tokens[$i])) {
                $test['type'] = strtolower(substr($tokens[$i], 1)) . '-' . $tokens[++$i];
            }
            else if (!is_array($tokens[$i]) && preg_match('/^:(is|contains|matches|regex)$/i', $tokens[$i])) {
                $test['type'] = strtolower(substr($tokens[$i], 1));
            }
            else if (!is_array($tokens[$i]) && preg_match('/^:index$/i', $tokens[$i])) {
                $test['index'] = intval($tokens[++$i]);
                if ($tokens[$i+1] && preg_match('/^:last$/i', $tokens[$i+1])) {
                    $test['last'] = true;
                    $i++;
                }
           }
           else {
               $result[] = $tokens[$i];
           }
        }

        $tokens = $result;

        return $test;
    }

    /**
     * Extract action arguments
     */
    private function action_arguments(&$tokens, $bool_args, $val_args = array())
    {
        $action = array();
        $result = array();

        for ($i=0, $len=count($tokens); $i<$len; $i++) {
            $tok = $tokens[$i];
            if (!is_array($tok) && $tok[0] == ':') {
                $tok = strtolower(substr($tok, 1));
                if (in_array($tok, $bool_args)) {
                    $action[$tok] = true;
                }
                else if (in_array($tok, $val_args)) {
                    $action[$tok] = $tokens[++$i];
                }
                else {
                    $result[] = $tok;
                }
            }
            else {
                $result[] = $tok;
            }
        }

        $tokens = $result;

        return $action;
    }

    /**
     * Escape special chars into quoted string value or multi-line string
     * or list of strings
     *
     * @param string $str Text or array (list) of strings
     *
     * @return string Result text
     */
    static function escape_string($str)
    {
        if (is_array($str) && count($str) > 1) {
            foreach($str as $idx => $val)
                $str[$idx] = self::escape_string($val);

            return '[' . implode(',', $str) . ']';
        }
        else if (is_array($str)) {
            $str = array_pop($str);
        }

        // multi-line string
        if (preg_match('/[\r\n\0]/', $str) || strlen($str) > 1024) {
            return sprintf("text:\n%s\n.\n", self::escape_multiline_string($str));
        }
        // quoted-string
        else {
            return '"' . addcslashes($str, '\\"') . '"';
        }
    }

    /**
     * Escape special chars in multi-line string value
     *
     * @param string $str Text
     *
     * @return string Text
     */
    static function escape_multiline_string($str)
    {
        $str = preg_split('/(\r?\n)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($str as $idx => $line) {
            // dot-stuffing
            if (isset($line[0]) && $line[0] == '.') {
                $str[$idx] = '.' . $line;
            }
        }

        return implode($str);
    }

    /**
     * Splits script into string tokens
     *
     * @param string &$str    The script
     * @param mixed  $num     Number of tokens to return, 0 for all
     *                        or True for all tokens until separator is found.
     *                        Separator will be returned as last token.
     *
     * @return mixed Tokens array or string if $num=1
     */
    static function tokenize(&$str, $num=0)
    {
        $result = array();

        // remove spaces from the beginning of the string
        while (($str = ltrim($str)) !== ''
            && (!$num || $num === true || count($result) < $num)
        ) {
            switch ($str[0]) {

            // Quoted string
            case '"':
                $len = strlen($str);

                for ($pos=1; $pos<$len; $pos++) {
                    if ($str[$pos] == '"') {
                        break;
                    }
                    if ($str[$pos] == "\\") {
                        if ($str[$pos + 1] == '"' || $str[$pos + 1] == "\\") {
                            $pos++;
                        }
                    }
                }
                if ($str[$pos] != '"') {
                    // error
                }
                // we need to strip slashes for a quoted string
                $result[] = stripslashes(substr($str, 1, $pos - 1));
                $str      = substr($str, $pos + 1);
                break;

            // Parenthesized list
            case '[':
                $str = substr($str, 1);
                $result[] = self::tokenize($str, 0);
                break;
            case ']':
                $str = substr($str, 1);
                return $result;
                break;

            // list/test separator
            case ',':
            // command separator
            case ';':
            // block/tests-list
            case '(':
            case ')':
            case '{':
            case '}':
                $sep = $str[0];
                $str = substr($str, 1);
                if ($num === true) {
                    $result[] = $sep;
                    break 2;
                }
                break;

            // bracket-comment
            case '/':
                if ($str[1] == '*') {
                    if ($end_pos = strpos($str, '*/')) {
                        $str = substr($str, $end_pos + 2);
                    }
                    else {
                        // error
                        $str = '';
                    }
                }
                break;

            // hash-comment
            case '#':
                if ($lf_pos = strpos($str, "\n")) {
                    $str = substr($str, $lf_pos);
                    break;
                }
                else {
                    $str = '';
                }

            // String atom
            default:
                // empty or one character
                if ($str === '' || $str === null) {
                    break 2;
                }
                if (strlen($str) < 2) {
                    $result[] = $str;
                    $str = '';
                    break;
                }

                // tag/identifier/number
                if (preg_match('/^([a-z0-9:_]+)/i', $str, $m)) {
                    $str = substr($str, strlen($m[1]));

                    if ($m[1] != 'text:') {
                        $result[] = $m[1];
                    }
                    // multiline string
                    else {
                        // possible hash-comment after "text:"
                        if (preg_match('/^( |\t)*(#[^\n]+)?\n/', $str, $m)) {
                            $str = substr($str, strlen($m[0]));
                        }
                        // get text until alone dot in a line
                        if (preg_match('/^(.*)\r?\n\.\r?\n/sU', $str, $m)) {
                            $text = $m[1];
                            // remove dot-stuffing
                            $text = str_replace("\n..", "\n.", $text);
                            $str = substr($str, strlen($m[0]));
                        }
                        else {
                            $text = '';
                        }

                        $result[] = $text;
                    }
                }
                // fallback, skip one character as infinite loop prevention
                else {
                    $str = substr($str, 1);
                }

                break;
            }
        }

        return $num === 1 ? (isset($result[0]) ? $result[0] : null) : $result;
    }

}
