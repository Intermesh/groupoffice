<?php
require('../vendor/autoload.php');
use go\core\App;
use go\core\util\IniFile;


$dbConnectError = null;
$pdo = null;
/**
 * 
 * @global PDO $pdo
 * @param type $config
 * @return PDO
 */
function dbConnect($config){
	global $pdo, $dbConnectError;
	if(isset($pdo)) {
		return $pdo;
	}
	try{		
		$pdo = new PDO($config['db']['dsn'], $config['db']['username'], $config['db']['password']);		
	}
	catch(Exception $e){
		$dbConnectError = "Could not connect to the database. The database returned this error:<br />".$e->getMessage();
	}
	
	return $pdo;
}

function dbIsEmpty($config) {
	//global $pdo;
	/* @var $pdo \PDO; */
	
	$stmt = dbConnect($config)->query("SHOW TABLES");
	$stmt->execute();
	
	return $stmt->rowCount() == 0;
}

$configFile = App::findConfigFile();

if(!$configFile) {
  $configFile = '/etc/groupoffice/config.ini';
}

if(file_exists($configFile)) {
	$config = parse_ini_file($configFile, true);	

	if(!isset($config['db'])) {
		$config = [
					"general" => [
							"dataPath" => '/var/lib/groupoffice',
							"tmpPath" =>  sys_get_temp_dir() . '/groupoffice',
							"debug" => false
					],
					"db" => [
							"dsn" => 'mysql:host=localhost;port=3306;dbname=groupoffice',
							"username" => 'groupoffice',
							"password" => ""
					],
					"limits" => [
							"maxUsers" => 0,
							"storageQuota" => 0,
							"allowedModules" => ""
					]
			];
	} else if($_SERVER['REQUEST_METHOD'] != 'POST')
  {
		
		
		//check if config.ini is already prefilled with usable data (Docker)
		$tmpFolder = new \go\core\fs\Folder($config['general']['tmpPath']);
		$dataFolder = new \go\core\fs\Folder($config['general']['dataPath']);
		if(dbConnect($config) && dbIsEmpty($config) && $dataFolder->isWritable() && $tmpFolder->isWritable()) {
			header("Location: install.php");
			exit();
		}
		
		
    $dsn = \go\core\db\Utils::parseDSN($config['db']['dsn']);
    
    
    $_POST['dbPort'] = $dsn['options']['port'];
    $_POST['dbHostname'] = $dsn['options']['host'];
    $_POST['dbName'] = $dsn['options']['dbname'];
    
    $_POST['dbUsername'] = $config['db']['username'];
    //$_POST['dbPassword'] = $config['db']['password'];
  }
}



if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$config['general']['dataPath'] = $_POST['dataPath'];
	$config['general']['tmpPath'] = $_POST['tmpPath'];
	$config['db']['dsn'] = 'mysql:host='.$_POST['dbHostname'].';port='.$_POST['dbPort'].';dbname='.$_POST['dbName'];
	$config['db']['username'] = $_POST['dbUsername'];
	$config['db']['password'] = $_POST['dbPassword'];


	$tmpFolder = new \go\core\fs\Folder($config['general']['tmpPath']);
	$dataFolder = new \go\core\fs\Folder($config['general']['dataPath']);
	

	if($dataFolder->isWritable() && $tmpFolder->isWritable() && dbConnect($config) && dbIsEmpty($config)) {
		$ini = new IniFile();
		$ini->readData($config);
		if(!$ini->write($configFile)) {
			die("Could not write INI");
		} else
		{
			header('Location: install.php');
			exit();
		}
	}
}




require('header.php');
?>

		<?php if(!is_writable($configFile)): ?>
		
		<section>
			<fieldset>
				<h2>Create config file</h2>
				<p>Please create a writeable config.ini file here: <?= $configFile; ?>.</p>
			</fieldset>
		</section>
		

		<?php else: ?>
		
		<section>
			<form method="POST" action="" onsubmit="submitButton.disabled = true;">


			<fieldset>
				<h2>Data storage</h2>
				
				<p>
					Please create a folder for storing Group-Office files and a temporary files folder and make sure the webserver user can write to these folders.
				</p>
				
				<?php
				if($_SERVER['REQUEST_METHOD'] == 'POST' && !$dataFolder->isWritable()) {
					echo '<p class="error">Not writable</p>';
				}
				?>
				
				<p>
					<input type="text" name="dataPath" value="<?=$config['general']['dataPath']?>" placeholder="" required />
					<label>Data folder</label>
				</p>
				
				
				<?php
				if($_SERVER['REQUEST_METHOD'] == 'POST' && !$tmpFolder->isWritable()) {
					echo '<p class="error">Not writable</p>';
				}
				?>
				<p>
					<input type="text" name="tmpPath" value="<?=$config['general']['tmpPath'];?>" placeholder="Temp folder" required />
					<label>Temp folder</label>
				</p>
			</fieldset>

			<fieldset>
				<h2>Configure the database</h2>
				
				<?php
				if($_SERVER['REQUEST_METHOD'] == 'POST' && !dbConnect($config)) {
					echo '<p class="error">'.$dbConnectError.'</p>';
				} elseif($_SERVER['REQUEST_METHOD'] == 'POST' && !dbIsEmpty($config)) {
					echo '<p class="error">The database must be empty</p>';
				}
				?>
				<p>
					<label>Database user</label>
					<input type="text" name="dbUsername" value="<?=$_POST['dbUsername'] ?? "groupoffice"; ?>" required />
				</p>
				<p>
					<label>Database password</label>
					<input type="password" name="dbPassword" value="<?=$_POST['dbPassword'];?>" required  />
				</p>
				<p>
					<label>Database name</label>
					<input type="text" name="dbName" value="<?=$_POST['dbName'] ?? "groupoffice";?>" required />					
				</p>
				<p>
					<label>Database hostname</label>
					<input type="text" name="dbHostname" value="<?=$_POST['dbHostname'] ?? "localhost";?>"  required />					
				</p>
				<p>
					<label>Database port</label>
					<input type="text" name="dbPort" value="<?=$_POST['dbPort'] ?? 3306;?>" required />					
				</p>
			</fieldset>

			<button name="submitButton">Continue</button>
			</form>
		</section>

		

		<?php endif; ?>

		
		<?php require('footer.php'); ?>
