<?php

use go\core\App;
use go\core\ErrorHandler;
use go\core\http\Response;
use go\core\jmap\State;
use go\modules\business\license\model\License;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);

try {


	require('../vendor/autoload.php');
	App::get();

	if(!empty($_GET['onlyIfNeeded']) && go()->getSettings()->databaseVersion == go()->getVersion()) {
		echo '<div id="success">Already up to date</div>';
		exit(0);
	}

	go()->setCache(new \go\core\cache\None());

	require("gotest.php");
	if(!systemIsOk()) {
		header("Location: test.php");
		exit();
	}

	if(!go()->isInstalled()) {
		header("Location: index.php");
		exit();
	}

	// When the database is up to date the upgrade may only be run again by an administrator
	go()->setAuthState(new State());
	if(!go()->getInstaller()->isUpgradeAllowed()) {
		Response::get()->setStatus(403);
		require('header.php');
		echo "<section><div class=\"card\"><h2>" . go()->t("Upgrade Group-Office") . "</h2><p>" .
			go()->t("The database is already up to date. Only an administrator can run the upgrade again. Please log in as an administrator first.") .
			"</p></div></section>";
		require('footer.php');
		exit();
	}

	if(empty($_GET['skip_license'])) {
		if (go()->getEnvironment()->hasIoncube() && !go()->getSettings()->licenseDenied && (empty(go()->getSettings()->license) || !License::isValid())) {
			header("Location: license.php");
			exit();
		}
	}



	require('header.php');

	echo "<section><div class=\"card\">";

	echo ' <div class="mask-message">
                <div class="x-mask-loading"></div>
                Please wait...
            </div>';

	go()->getInstaller()->isValidDb();

	go()->getDatabase()->clearCache();
	
	$unavailable = go()->getInstaller()->getUnavailableModules();

	if (!isset($_GET['confirmed'])) {
	
		echo "<h2>". go()->t("Upgrade Group-Office") ."</h2><p>";

		echo "Please <b>BACKUP</b> your database and files before proceeding. Your database is going to be upgraded and all caches will be cleared.<br />This operation can only be undone by restoring a backup.<br />";
		
		echo 'More details about this upgrade can be found in the <a target="_blank" href="https://github.com/Intermesh/groupoffice/blob/master/CHANGELOG.md">change log</a>.<br /><br />';

		echo "Note: You can also upgrade on the command line by running (replace www-data with the user of your webserver): <br />

			<code>sudo -u www-data php cli.php core/System/upgrade</code>

			";


		
		echo '</p>';
		if(empty(go()->getSettings()->license) || !License::isValid()) {
			echo '<a class="button accent" href="license.php">Install license</a>';
		}
		echo '<a class="right button primary" href="?confirmed=1" onclick="document.getElementsByClassName(\'card\')[0].classList.add(\'mask\')">Upgrade database</a></div>';
	
	} elseif (!isset($_GET['ignore']) && count($unavailable)) {
	
		echo "<h2>". go()->t("Upgrade Group-Office") ."</h2>";

		echo "<p>The following modules are not available because they're missing on disk\n"
		. "or you've got an <b>invalid or missing license file</b>: </p>"
		. "<ul><li>" . implode("</li><li>", array_map(function($a){return ($a['package'] ?? "legacy") .'/'.$a['name'];}, $unavailable)) . "</li></ul>\n"
		. "<p>Please install the license file(s) and refresh this page or disable these modules.\n"
		. "If you continue the incompatible modules will be disabled.</p>";

		if(empty(go()->getSettings()->license) || !License::isValid()) {
			echo '<a class="button secondary" href="license.php">Install license</a>';
		}
		echo '<a class="right button primary" href="?ignore=modules&confirmed=1" onclick="document.getElementsByClassName(\'card\')[0].classList.add(\'mask\')">Disable &amp; Continue</a>';

		echo "</div>";

	} else
	{

		echo "<h2>". go()->t("Upgrade Group-Office") ."</h2><pre>";

		go()->getInstaller()->upgrade();	

		echo "</pre>";
		echo '<a class="button right primary" href="../" onclick="document.getElementsByClassName(\'card\')[0].classList.add(\'mask\')">' . go()->t('Continue') . '</a>';
		echo "</div>";

		if(go()->getDebugger()->enabled) {
			echo "<div style=\"clear:both;margin-bottom:20px;\"></div><div class=\"card\"><h2>Debugger output</h2><pre style=\"max-height: 600px; overflow:scroll;\">" ;
			go()->getDebugger()->printEntries();
			echo "</pre></div>";
		}

		//Used by multi instance to check success
		echo '<div id="success"></div>';

	} 
} catch (Throwable $e) {
	
	echo "<b>Error:</b> ". ErrorHandler::logException($e)."\n\n";
	
	if(go()->getDebugger()->enabled) {
		echo $e->getTraceAsString();
	}
	
	echo "</pre></div>";
	
	if(go()->getDebugger()->enabled) {
		echo "<div style=\"clear:both;margin-bottom:20px;\"></div><div class=\"card\"><h2>Debugger output</h2><pre>";
		
		go()->getDebugger()->printEntries();
		
		echo "</pre></div>";
	}
	
	echo "</section>";
}

require('footer.php');