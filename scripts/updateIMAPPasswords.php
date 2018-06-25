<?php
/**
 * 
 * USE LIKE THIS:
 * sudo -u www-data php updateIMAPPasswords.php --c=/etc/groupoffice/go62.localhost/config.php --host=127.0.0.1 --passwdfile=/home/govhosts/go62.localhost/data/
 * 
 * --c					: Path to the group-office config.php file
 * --host				: The host of the em_accounts where to update the passwords for (Defaults to "localhost")
 * --username		: The username of the em_accounts where to update the passwords for (Defaults to false, then all from the host are selected)
 * --passwdfile : The location on where to generate the passwordFile to.
 */


if (PHP_SAPI != 'cli'){
	exit("ERROR: This script must be run on the command line\n\n");
}

$root = dirname(__FILE__) . '/../www/';
require_once($root . 'go/base/util/Cli.php');
$args = \GO\Base\Util\Cli::parseArgs();
if (isset($args['c'])) {
	define("GO_CONFIG_FILE", $args['c']);
}

$host = 'localhost';
if(isset($args['host'])) {
	$host = $args['host'];
}

$username = false;
if(isset($args['username'])) {
	$username = $args['username'];
}

$passwdfile = false;
if(!isset($args['passwdfile'])) {
	exit("ERROR: Please specify a location to put the password file. (--passwdfile={path})\n\n");
}
$passwdfile = $args['passwdfile'];

require($root.'GO.php');

// =============================================

class ImapHttpClient extends \GO\Base\Util\HttpClient {
	
	public function request($url, $params = array()) {
		
		
		if(empty(\GO::config()->serverclient_server_url)){
			\GO::config()->serverclient_server_url=\GO::config()->full_url;
		}
		
		$url = \GO::config()->serverclient_server_url.'?r='.$url;
		
		if(empty(\GO::config()->serverclient_token)){
			throw new \Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}
		
		$params['serverclient_token']=\GO::config()->serverclient_token;	

		return parent::request($url, $params);
	}
}

class ImapPasswordUpdater {
	
	private $_httpClient = false;
	private $_passwdfilePath = false;
	
	public function __construct($passwdfilePath) {
		$this->_passwdfilePath = $passwdfilePath;
	}
	
	public function setClient($httpClient){
		$this->_httpClient = $httpClient;
	}
	
	
	public function process($host='localhost',$username=false){
		
		if(!$this->_httpClient){
			exit('NO HTTPCLIENT SET');
		}
		
		echo "============================================\n";
		echo "Update passwords for mailboxes on ".$host."\n";
		echo "============================================\n";
		
		$this->updateAccounts($host,$username);
		
		echo "============================================\n";
		echo "DONE, please check \"".$this->_passwdfilePath."emailAccounts.csv\" for the generated password file.\n";
		echo "============================================\n";
	}
	
	
	public function updateAccounts($host, $username=false){
		
		if(!$username){
		$stmt = \GO\Email\Model\Account::model()->findByAttributes(array(
			'host' => $host
		));
		} else {
			$stmt = \GO\Email\Model\Account::model()->findByAttributes(array(
			'host' => $host,
			'username' => $username
		));
		}
		
		echo "Found ". $stmt->rowCount() ." account(s) to update.\n\n";
		
		$filePointer = fopen($this->_passwdfilePath."emailAccounts.csv","w");
		$headers = array("username","email","password");
		fputcsv($filePointer,$headers);
		
		while($account = $stmt->fetch()) {	
		
			try {
				echo "Update password for \"".$account->username."\".\n";
				echo "Obtaining new random password.\n";
				$newPassword = $this->_getNewPassword();
				echo "New password: ".$newPassword."\n";
				if($this->_updatePassword($account, $newPassword)){
					echo "Update successfull.\n";
				}

				$data = array($account->username,$account->username,$newPassword);
				fputcsv($filePointer,$data);
			} catch(\Exception $e){
				fputcsv($filePointer,array($account->username, 'failed','failed'));
				continue;
			}
		}
		
		fclose($filePointer);
	}
	
	private function _updatePassword($account,$newPassword){
		
		echo "Update password on server.\n";
		
		$url = "postfixadmin/mailbox/submit";
		$response = $this->_httpClient->request($url, array(
			"r"=>"postfixadmin/mailbox/setPassword",
			"username"=>$account->username,
			"password"=>$newPassword,
		));

		$result=json_decode($response);

		if($result->success){
			$account->password=$newPassword;
			if(!$account->save(true)){
				echo "ERROR: Failed to update password for ".$account->username." in the em_accounts table. \n";
				return false;
			}
		} else {
			return false;
		}
	
		return true;
	}
	
	/**
	 * Get a random password
	 * 
	 * @return string
	 */
	private function _getNewPassword(){
		return \GO\Base\Util\StringHelper::randomPassword();
	}
}

// RUN SCRIPT ================
$updater = new ImapPasswordUpdater($passwdfile);
$httpClient = new ImapHttpClient();
$updater->setClient($httpClient);
$updater->process($host,$username);
