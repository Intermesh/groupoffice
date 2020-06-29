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
		$dsn = 'mysql:host=' . $config['db_host'] . ';port=' . $config['db_port'] . ';dbname=' . $config['db_name'];
		$pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
	}
	catch(Exception $e){
		$dbConnectError = "Could not connect to the database. The database returned this error:<br />".$e->getMessage();
	}

	if($pdo) {
        $clientVersion = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        if (strpos($clientVersion, 'mysqlnd') === false) {
            $dbConnectError = "PDO is not using the mysqlnd driver. Please make sure PDO uses mysqlnd. It's now using: " . $clientVersion;
            $pdo = null;
        }
    }
	
	return $pdo;
}



$configFile = App::findConfigFile();
if(!$configFile) {
  $configFile = '/etc/groupoffice/config.php';
} else
{
	require($configFile);
}




if($_SERVER['REQUEST_METHOD'] != 'POST')
{
	
	$config['file_storage_path'] = $config['file_storage_path'] ?? "/var/lib/groupoffice";
	$config['tmpdir'] = $config['tmpdir'] ?? "/tmp/groupoffice";
	
	//check if config.ini is already prefilled with usable data (Docker)
	$tmpFolder = new \go\core\fs\Folder($config['tmpdir']);
	$dataFolder = new \go\core\fs\Folder($config['file_storage_path']);
	
	if(dbConnect($config) && $dataFolder->isWritable() && $tmpFolder->isWritable()) {
		header("Location: install.php");
		exit();
	}

	$_POST['dbPort'] = $config['db_port'] ?? 3306;
	$_POST['dbHostname'] = $config['db_host'] ?? 'localhost';
	$_POST['dbName'] = $config['db_name'] ?? "groupoffice";

	$_POST['dbUsername'] = $config['db_user'] ?? "groupoffice";
	//$_POST['dbPassword'] = $config['db']['password'];
} else 
{
//	$config['general']['dataPath'] = $_POST['dataPath'];
//	$config['general']['tmpPath'] = $_POST['tmpPath'];
//	$config['db']['dsn'] = 'mysql:host='.$_POST['dbHostname'].';port='.$_POST['dbPort'].';dbname='.$_POST['dbName'];
//	$config['db']['username'] = $_POST['dbUsername'];
//	$config['db']['password'] = $_POST['dbPassword'];
	
	$config['file_storage_path'] = $_POST['dataPath'];
	$config['tmpdir'] = $_POST['tmpPath'];
	$config['db_host'] =$_POST['dbHostname'];
	$config['db_name'] =$_POST['dbName'];
	$config['db_user'] =$_POST['dbUsername'];
	$config['db_pass'] =$_POST['dbPassword'];
	$config['db_port'] =$_POST['dbPort'];
	
	$tmpFolder = new \go\core\fs\Folder($config['tmpdir']);
	$dataFolder = new \go\core\fs\Folder($config['file_storage_path']);
	

	if($dataFolder->isWritable() && $tmpFolder->isWritable() && dbConnect($config)) {
		$cFile = new \go\core\fs\File($configFile);
		
		if(!$cFile->putContents("<?php\n\n\$config = ".var_export($config, true) .";\n\n")) {
			$configError = "Could not write config.php";
		} else
		{
			if(function_exists("opcache_invalidate")) {
				opcache_invalidate($cFile->getPath());
			}
			
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
				<h2><?= go()->t("Config file"); ?></h2>
				<p class="error">Please create a writeable config.php file here: <?= $configFile; ?>.</p>
			</fieldset>
		</section>
		

		<?php endif ?>
		
		<section>
			<form method="POST" action="" onsubmit="submitButton.disabled = true;">


			<fieldset>
				<h2>Data storage</h2>
				
				<p>
					Please create a folder for storing Group-Office files and a temporary files folder and make sure the webserver user can write to these folders.
				</p>
				
				<?php
				if(!$dataFolder->isWritable()) {
					echo '<p class="error">Not writable</p>';
				}
				?>
				
				<p>
					<input type="text" name="dataPath" value="<?=$config['file_storage_path']?>" placeholder="" required />
					<label>Data folder</label>
				</p>
				
				
				<?php
				if(!$tmpFolder->isWritable()) {
					echo '<p class="error">Not writable</p>';
				}
				?>
				<p>
					<input type="text" name="tmpPath" value="<?=$config['tmpdir'];?>" placeholder="Temp folder" required />
					<label>Temp folder</label>
				</p>
			</fieldset>

			<fieldset>
				<h2>Configure the database</h2>
				
				<?php
				if(isset($config['db_host']) && !dbConnect($config)) {
					echo '<p class="error">'.$dbConnectError.'</p>';
				}
				?>
				<p>
					<label>Database user</label>
					<input type="text" name="dbUsername" value="<?=$_POST['dbUsername'] ?? "groupoffice"; ?>" required />
				</p>
				<p>
					<label>Database password</label>
					<input type="password" name="dbPassword" value="<?=$_POST['dbPassword'] ?? "";?>" required  />
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

			<button name="submitButton"><?= go()->t('Continue'); ?></button>
			</form>
		</section>

	
		
		<?php require('footer.php'); ?>
