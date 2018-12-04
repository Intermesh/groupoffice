<?php
use go\core\ErrorHandler;


try {
	
	require('../vendor/autoload.php');

	require("gotest.php");
	if(!systemIsOk()) {
		header("Location: test.php");
		exit();
	}
	
	require('header.php');
	
	echo "<section><div class=\"card\"><h2>Upgrading Group-Office</h2><pre>";
	
	GO()->getInstaller()->isValidDb();
	$unavailable = GO()->getInstaller()->getUnavailableModules();
	
	if (!isset($_GET['ignore']) && count($unavailable)) {

		echo "The following modules are not available because they're missing on disk\n"
		. "or you've got an <b>invalid or missing license file</b>: \n"
		. "<ul style=\"font-size:1.5em\"><li>" . implode("</li><li>", array_map(function($a){return ($a['package'] ?? "legacy") .'/'.$a['name'];}, $unavailable)) . "</li></ul>"
		. "Please install the license file(s) and refresh this page or disable these modules.\n"
		. "If you continue the incompatible modules will be disabled.\n\n";
		
		echo '<a class="button" href="?ignore=modules">Disable &amp; Continue</a>';
		echo "</pre></div></section>";
	} else
	{
		if(count($unavailable)) {

			//todo package!			
			GO()->getDbConnection()->query("update core_module set enabled=0 where name IN ('" . implode("', '", array_column($unavailable, 'name')) . "')");
		}	
	
		GO()->getInstaller()->upgrade();		
		//GO()->getInstaller()->checkVersions();		

		echo "</pre></div>";

		echo '<a class="button" href="../">Continue</a>';

		if(GO()->getDebugger()->enabled) {
			echo "<div style=\"clear:both;margin-bottom:20px;\"></div><div class=\"card\"><h2>Debugger output</h2><pre>" . implode("\n", GO()->getDebugger()->getEntries()) . "</pre></div>";
		}

		echo "</section>";

	} 
} catch (Exception $e) {
	echo "<b>Error:</b> ". ErrorHandler::logException($e)."\n\n";;
	
	echo $e->getTraceAsString();
	
	echo "</pre></div>";
	
	if(GO()->getDebugger()->enabled) {
		echo "<div style=\"clear:both;margin-bottom:20px;\"></div><div class=\"card\"><h2>Debugger output</h2><pre>";
		
		foreach(GO()->getDebugger()->getEntries() as $line) {
			echo $line ."\n";
		}
		
		echo "</pre></div>";
	}
	
	echo "</section>";
}

require('footer.php');