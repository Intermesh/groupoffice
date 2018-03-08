#!/usr/bin/php
<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: GroupOfficeProsodyAuth.php 21168 2017-05-15 10:03:11Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 * 
 * test:
 * 
 * auth:username:groupoffice.domain.com:password
 */

// the logfile to which to write, should be writeable by the user which is running the server
$sLogFile 	= "/var/log/prosody/prosody.log";

// set true to debug if needed
$bDebug		= false;

$oAuth = new exAuth($sLogFile, $bDebug);

class exAuth
{

	private $sLogFile;

	private $bDebug;

	private $rLogFile;

	private function _includeGO($server){
		//Try to detect a host based
		if(class_exists('GO')){
			return true;
		}
		if(file_exists('/etc/groupoffice/'.$server.'/config.php')){
			
			$this->writeDebugLog('Include GO: /etc/groupoffice/'.$server.'/config.php');
			
			define('GO_CONFIG_FILE', '/etc/groupoffice/'.$server.'/config.php');
			require(GO_CONFIG_FILE);
			require_once($config['root_path'].'GO.php');
		}else
		{
			$this->writeDebugLog('Include GO: /usr/share/groupoffice/GO.php');
			require_once('/usr/share/groupoffice/GO.php');
		}
		
		$this->writeDebugLog("Config file: ".GO::config()->get_config_file()." db: ".GO::config()->db_name." ".GO::config()->debug);
	}
	
	public function __construct($sLogFile, $bDebug)
	{

		$this->sLogFile 	= $sLogFile;
		$this->bDebug		= $bDebug;
		
		$this->rLogFile = fopen($this->sLogFile, "a") or die("Error opening log file: ". $this->sLogFile);

		$this->writeLog("start");

		do {		
			$sData = substr(fgets(STDIN),0,-1);
			
			if($sData){
				
				$this->writeDebugLog("received data: ". $sData);
				$aCommand = explode(":", $sData);
				if (is_array($aCommand)){
					switch ($aCommand[0]){
						case "isuser":
							if (!isset($aCommand[1])){
								$this->writeLog("invalid isuser command, no username given");
								$this->failure();
							} else {
			
								$sUser = str_replace(array("%20", "(a)"), array(" ", "@"), $aCommand[1]);
								$this->writeDebugLog("checking isuser for ". $sUser);

								$this->_includeGO($aCommand[2]);
								$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $sUser);

								if ($user){								
									$this->writeLog("valid user: ". $sUser);
									
									$this->success();

								} else {
									$this->writeLog("invalid user: ". $sUser);
									$this->failure();
								}
								
								
							}
						break;
						case "auth":
							
							if (sizeof($aCommand) != 4){
								$this->writeLog("invalid auth command, data missing");
								$this->failure();
							} else {
								

								$sUser = str_replace(array("%20", "(a)"), array(" ", "@"), $aCommand[1]);
								$sPassword = $aCommand[3];
								$this->writeDebugLog("doing auth for ". $sUser);



								$this->_includeGO($aCommand[2]);

								$user = \GO::session()->login($sUser, $sPassword, false);

					
								if ($user) {
						
									$this->writeLog("authentificated user ". $sUser ." domain ". $aCommand[2]);
									$this->success();
								} else {
						
									$this->writeLog("authentification failed for user ". $sUser ." domain ". $aCommand[2]);
									$this->failure();
								}
							
							}
						break;
						case "setpass":
							// postavljanje zaporke, onemoguceno
							$this->writeLog("setpass command disabled");
							$this->failure();
						break;
						default:
							// ako je uhvaceno ista drugo
							$this->writeLog("unknown command ". $aCommand[0]);
							$this->failure();
						break;
					}
				} else {
					$this->writeDebugLog("invalid command string");
					$this->failure();
				}
			}

			unset($aCommand);
		} while (true);
	}

	public function __destruct()
	{
		$this->writeLog("stop");		
	}

	private function writeLog($sMessage)
	{
		if (is_resource($this->rLogFile)) {
			fwrite($this->rLogFile, date("r") ." [external_auth] ". $sMessage ."\n");
		}
	}

	private function writeDebugLog($sMessage)
	{
		if ($this->bDebug){
			$this->writeLog( date("r")." [external_auth_debug] ".$sMessage);
		}
	}
	
	
	private function success(){
		echo "1\n";
		if(class_exists('\GO')){
			\GO::unsetDbConnection();
		}

	}
	
	private function failure(){
		echo "0\n";
		if(class_exists('\GO')){
			\GO::unsetDbConnection();
		}
	}


	
}