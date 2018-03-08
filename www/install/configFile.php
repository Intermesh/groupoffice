<?php
require('header.php');


$configFile = \GO::config()->get_config_file();
if (!$configFile) {

	//check ifconfig exists and if the config file is writable
	$config_location1 = '/etc/groupoffice/' . $_SERVER['SERVER_NAME'] . '/config.php';
	//$config_location2 = dirname(substr($_SERVER['SCRIPT_FILENAME'], 0 ,-strlen($_SERVER['PHP_SELF']))).'/config.php';
	$config_location2 = \GO::config()->root_path . 'config.php';

	printHead();
	echo '<h1>Create config file</h1>';

	echo '<p>The configuration file does not exist. You must create an empty writable file at one of the following locations:<br />';
	echo '<ol><li>' . $config_location1 . '</li><li>' . $config_location2 . ' (or any directory up that path)</li></ol>';
	echo 'Placing it outside of the web root is recommended because it\'s more secure. The sensitive information is kept outside the document root but it does require root privileges on this machine.<br />The second advantage is that you will be able to separate the source from the configuration. This can be very usefull with multiple installations on one machine.';
	echo '<br /><br />If you choose the first location then you have to make sure that in Apache\'s httpd.conf the following is set:</p>';
	echo '<div class="cmd">';
	echo 'UseCanonicalName On</div>';
	echo '<p>This is to make sure it always finds your configuration file at the correct location.';
	echo '</p>><div class="cmd">';
	echo '$ touch config.php (Or FTP an empty config.php to the server)<br />';
	echo '$ chmod 666 config.php</div>';
	echo '<p>If it does exist and you still see this message then it might be that safe_mode is enabled and the config.php is owned by another user then the ' . \GO::config()->product_name . ' files.</p>';
} else {


	try {
		if (!empty($_POST['submitted'])) {
			
			//A default config.php can be put in the install folder.
			$config=array();
			if(file_exists('config.php')){				
				require('config.php');
			}
			
			if(empty(\GO::config()->title))
				\GO::config()->title='Group-Office';
			
			//set this to a default otherwise GO will keep autodetecting values
			if(empty(\GO::config()->db_user))
				\GO::config()->db_user='groupoffice';

			$f = new \GO\Base\Fs\Folder($_POST['file_storage_path']);
			if (!$f->exists())
				\GO\Base\Html\Input::setError("file_storage_path", "File storage folder doesn't exist. Please make sure it exists and it must be writable for the webserver user.");
			elseif(!$f->isWritable())
				\GO\Base\Html\Input::setError("file_storage_path", "File storage must be writable for the webserver user.");
			
			\GO::config()->file_storage_path = $f->path() . '/';

			$f = new \GO\Base\Fs\Folder($_POST['tmpdir']);
			if (!$f->exists() && !$f->create(0777))
				\GO\Base\Html\Input::setError("tmpdir", "Temporary folder doesn't exist. Please make sure it exists and it must be writable for the webserver user.");
			elseif(!$f->isWritable())
				\GO\Base\Html\Input::setError("tmpdir", "Temporary folder must be writable for the webserver user.");

			\GO::config()->tmpdir = $f->path() . '/';
			\GO::config()->save($config);

			if (!\GO\Base\Html\Input::hasErrors())
				redirect("regional.php");
		}
	} catch (Exception $e) {
		\GO\Base\Html\Input::setError("form", $e->getMessage());
	}
	printHead();
	//check if config root_path matches the current Group-Office in case an /etc/groupoffice/config.php was found that conflicts with this installation.
	$filepath = str_replace("\\","/",__FILE__);
	if (strpos($filepath, \GO::config()->root_path) !== 0) {
		errorMessage("WARNING: The config file $configFile was found but the root path points to another location " . \GO::config()->root_path . " while you are installing in " . dirname(dirname($filepath)) . " now. You probably want to create a new config.php file for this installation.");
	}

	if (!is_writable($configFile)) {

		echo '<h1>Config file not writable</h1>';
		echo '<p>The configuration file \'' . $configFile . '\' exists but is not writable. If you wish to make changes then you have to make \'' . $configFile . '\' writable during the configuration process.';
		echo '<br /><br />Correct this and click on continue.</p>';
		echo '<div class="cmd">$ chmod 666 ' . $configFile . '</div>';
	} else {


		echo '<h1>File storage</h1>';

		\GO\Base\Html\Input::printError("form");
		?>
		<input type="hidden" name="submitted" value="1" />
		<p>
			<?php echo \GO::config()->product_name; ?> needs a place to store protected data. This folder should not be accessible through the webserver. Create a writable path for this purpose now and enter it in the box below.<br />
			The path should be have 0777 permissions or should be owned by the webserver user. You probably need to be root to do the last.
			<br /><br />
		<div class="cmd">
			$ su<br />
			$ mkdir /home/groupoffice<br />
			$ chown www-data:www-data /home/groupoffice<br />
		</div>
		</p>

		<?php
		\GO\Base\Html\Input::render(array(
				"required" => true,
				"label" => "Protected files path",
				"name" => "file_storage_path",
				"value" => \GO::config()->file_storage_path
		));
		?>
		<p>
			<?php echo \GO::config()->product_name; ?> needs a place to store temporary data such as session data or file uploads. Create a writable path for this purpose now and enter it in the box below.<br />
			The /tmp directory is a good option.
		</p>
		<?php
		\GO\Base\Html\Input::render(array(
				"required" => true,
				"label" => "Temporary files path",
				"name" => "tmpdir",
				"value" => \GO::config()->tmpdir
		));
	}
}
continueButton();

printFoot();