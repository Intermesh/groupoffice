<?php

use GO\Base\Util\Crypt;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\module\Base;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: default_scripts.inc.php 22455 2018-03-06 15:17:33Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
$settings['max_rows_list'] = 50;

$settings['config']['theme'] = GO::config()->theme;
$settings['config']['product_name'] = GO::config()->product_name;
$settings['config']['host'] = GO::config()->host;
$settings['config']['title'] = GO::config()->title;
$settings['config']['full_url'] = GO::config()->full_url;
$settings['config']['allow_password_change'] = GO::config()->allow_password_change;
$settings['config']['allow_themes'] = GO::config()->allow_themes;
$settings['config']['allow_profile_edit'] = GO::config()->allow_profile_edit;
$settings['config']['max_users'] = GO::config()->max_users;
$settings['config']['debug'] = GO::config()->debug;
$settings['config']['max_attachment_size'] = GO::config()->max_attachment_size;
$settings['config']['max_file_size'] = GO::config()->max_file_size;
$settings['config']['help_link'] = GO::config()->help_link;
$settings['config']['support_link'] = GO::config()->support_link;
$settings['config']['report_bug_link'] = GO::config()->report_bug_link;
$settings['config']['nav_page_size'] = intval(GO::config()->nav_page_size);
$settings['config']['session_inactivity_timeout'] = intval(GO::config()->session_inactivity_timeout);
$settings['config']['tickets_no_email_required'] = GO::config()->tickets_no_email_required;
$settings['config']['default_country'] = GO::config()->default_country;
$settings['config']['checker_interval'] = (int) GO::config()->checker_interval;
$settings['config']['remember_login'] = GO::config()->remember_login;
$settings['config']['encode_callto_link'] = GO::config()->encode_callto_link;
$settings['config']['login_message'] = GO::config()->login_message;

 
$settings['state_index'] = 'go';
$settings['language'] = GO::language()->getLanguage();
$settings['show_contact_cf_tabs'] = array();
$settings['modules'] = GO::view()->exportModules();


//if (GO::modules()->addressbook) {
//	// Add the addresslist tab to the global settings panel
//	$settings['show_addresslist_tab'] = GO::config()->get_setting('globalsettings_show_tab_addresslist');
//
//	if (GO::modules()->customfields) {
//		$settings['show_contact_cf_tabs'] = array();
//
//		$tabsEnabledStmt = CfSettingTab::model()->find();
//		$tabsEnabled = $tabsEnabledStmt->fetchAll(PDO::FETCH_COLUMN);
//
//		// Add the contact customfield tabs to the global settings panel
//		$contactClassName = Contact::model()->className();
//		$customfieldsCategories = Category::model()->findByModel($contactClassName);
//		foreach ($customfieldsCategories as $cfc) {
//			if (in_array($cfc->id, $tabsEnabled))
//				$settings['show_contact_cf_tabs'][$cfc->id] = true;
//		}
//	}
//}

$settings['upload_quickselect'] = GO::config()->upload_quickselect;
$settings['html_editor_font'] = GO::config()->html_editor_font;


$root_uri = GO::config()->debug ? GO::config()->host : GO::config()->root_path;
$view_root_uri = $root_uri . 'views/Extjs3/';
$view_root_path = GO::config()->root_path . 'views/Extjs3/';



if(GO::config()->debug) {
  $cacheFile = \go\core\App::get()->getTmpFolder()->getFile('debug.js');
  $cacheFile->delete();
} else
{
  $cacheFile = \go\core\App::get()->getDataFolder()->getFolder('clientscripts')->create()->getFile('all.js');
}

//echo '<script type="text/javascript" src="' . GO::url('core/language', ['lang' => \GO::language()->getLanguage()]) . '"></script>';
echo '<script type="text/javascript" src="views/Extjs3/ext/adapter/ext/ext-base-debug.js"></script>';
echo '<script type="text/javascript" src="views/Extjs3/ext/ext-all-debug.js"></script>';
echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'lang.php?lang='.\GO()->getLanguage()->getIsoCode() . '&v='.GO()->getVersion().'"></script>';

?>

<script type="text/javascript">

	//hide mask after 10s to display errors is necessary.
//	setTimeout(function () {
//		var loadMask = document.getElementById('loading-mask');
//		var loading = document.getElementById('loading');
//		if (loadMask)
//			loadMask.style.display = 'none';
//
//		if (loading)
//			loading.style.display = 'none';
//
//	}, 10000);
	Ext.namespace("GO");

	GO.settings = <?php echo json_encode($settings); ?>;
	GO.language = "<?php echo GO::config()->language; ?>";
	GO.calltoTemplate = '<?php echo GO::config()->callto_template; ?>';
	GO.calltoOpenWindow = <?php echo GO::config()->callto_open_window ? "true" : "false"; ?>;
	
	GO.authenticationDomains = <?php echo json_encode(go\modules\core\users\model\User::getAuthenticationDomains()); ?>;
	GO.authenticationDomainDefault = "<?php echo GO()->getSettings()->defaultAuthenticationDomain; ?>";
<?php
if (isset(GO::session()->values['security_token'])) {
	echo 'GO.securityToken="' . GO::session()->values['security_token'] . '";';
}

//if (isset($_GET['SET_LANGUAGE']) && preg_match('/[a-z_]/', $_GET['SET_LANGUAGE'])) {
//	echo 'GO.loginSelectedLanguage = "' . $_GET['SET_LANGUAGE'] . '";';
//} 
echo 'window.name="' . GO::getId() . '";';

if (isset(GO::session()->values['security_token']))
	echo 'Ext.Ajax.extraParams={security_token:"' . GO::session()->values['security_token'] . '"};';

GO::router()->getController()->fireEvent('inlinescripts');
?>
</script>
<?php
  
if ($cacheFile->exists()) {
	echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'script.php?v='. GO()->getVersion() . '"></script>';
} else {

	$scripts = array();
	$load_modules = GO::modules()->getAllModules(true);

	$scripts[] = "var BaseHref = '';";

	$scripts[] = new File(GO::config()->root_path . 'views/Extjs3/javascript/namespaces.js');
	
	//for t() function to auto detect module package and name
	$scripts[] = "go.module='core';go.package='core';";
	
	$data = file_get_contents(GO::config()->root_path . 'views/Extjs3/javascript/scripts.txt');
	$lines = array_map('trim', explode("\n", $data));
	foreach ($lines as $line) {
		if (!empty($line)) {
			$scripts[] = new File(GO::config()->root_path . $line);
		}
	}

	if (count($load_modules)) {
		$modules = array();
		foreach ($load_modules as $module) {
			if ($module->moduleManager instanceof Base) {
				$prefix = dirname(str_replace("\\", "/", get_class($module->moduleManager))) . "/views/extjs3/";
				$scriptsFile = $module->moduleManager->path() . 'views/extjs3/scripts.txt';

				//fallback to old dir
				$modulePath = GO::config()->root_path . 'modules/' . $module->moduleManager->getName() . '/';
			} else {
				$scriptsFile = false;
				$modulePath = $module->moduleManager->path();
				
				
			}
			
			$scripts[] = 'Ext.ns("GO.' . $module->name  . '");';

			if (!$scriptsFile || !file_exists($scriptsFile)) {
				$scriptsFile = $modulePath . 'scripts.txt';
				if (!file_exists($scriptsFile))
					$scriptsFile = $modulePath . 'views/Extjs3/scripts.txt';

				$prefix = "";
			}
			
			

			if (file_exists($scriptsFile)) {
				$data = file_get_contents($scriptsFile);
				$lines = array_map('trim', explode("\n", $data));
				foreach ($lines as $line) {
					if (!empty($line)) {
						$scripts[] = new File(GO::config()->root_path . $prefix . trim($line));
					}
				}
			}
		}
	}

	//two modules may include the same script
	//$scripts = array_map('trim', $scripts);
	//	$scripts = array_unique($scripts);

  if(!GO::config()->debug) {
    $minify = new \MatthiasMullie\Minify\JS();
  } else
  {
    //$fp = $cacheFile->open("w");
    $js = "";
  }

	$rootFolder = new Folder(GO::config()->root_path);
	
	foreach ($scripts as $script) {

		if (GO::config()->debug) {
			if (is_string($script)) {
//        $js .=  $script ."\n;\n";
				echo '<script type="text/javascript">' . $script . '</script>' . "\n";
			} else if ($script instanceof File) {
        $relPath = $script->getRelativePath($rootFolder);
        $parts = explode('/', $relPath);
//        $js .= "\n//source: ".$relPath ."\n";
				echo '<script type="text/javascript">';
				$js = "";
        if($parts[0] == 'go' && $parts[1] == 'modules') {
					//for t() function to auto detect module package and name
          $js .= "go.module = '".$parts[3]."';";
          $js .= "go.package = '".$parts[2]."';";
          $js .= "go.Translate.setModule('".$parts[2]."', '" .$parts[3]. "');";   
        } else if($parts[0] == 'modules')
        {
					//for t() function to auto detect module package and name
          $js .= "go.module = '".$parts[1]."';";
          $js .= "go.package = 'legacy';";
          $js .= "go.Translate.setModule('legacy', '" .$parts[1]. "');";   
        }
				echo $js;
				
				echo "</script>";
//        $js .= $script->getContents()."\n;\n";
//        
//     
				echo '<script type="text/javascript" src="'.$relPath. '?mtime='.$script->getModifiedAt().'"></script>' . "\n";
			}
//      else if($script instanceof \go\core\util\Url) {
//				echo '<script type="text/javascript" src="'.$script.'"></script>' . "\n";
//			}
		} else {      
      
			if($script instanceof File) {
				$relPath = $script->getRelativePath($rootFolder);
				$parts = explode('/', $relPath);
				$js = "";
				if($parts[0] == 'go' && $parts[1] == 'modules') {
					$js .= "go.module = '".$parts[3]."';";
					$js .= "go.package = '".$parts[2]."';";
					$js .= "go.Translate.setModule('".$parts[2]."', '" .$parts[3]. "');";   
				} else if($parts[0] == 'modules')
				{
					$js .= "go.module = '".$parts[1]."';";
					$js .= "go.package = 'legacy';";
					$js .= "go.Translate.setModule('legacy', '" .$parts[1]. "');";   
				}

				if(!empty($js)) {
					$minify->add($js);   
				}
			}
			$minify->add($script);
		}
	}
	
	if (!GO::config()->debug) {
		$minify->gzip($cacheFile->getPath());		
		echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'script.php?v= '. GO()->getVersion() . '"></script>';
	} else
  {
//    $fp = $cacheFile->open('w');
//    fwrite($fp, $js);
//    fclose($fp);
  }
//  echo '<script type="text/javascript" src="' . GO::url('core/clientScripts', ['mtime' => GO::config()->mtime, 'lang' => \GO::language()->getLanguage()]) . '"></script>';
}

if (file_exists(GO::view()->getTheme()->getPath() . 'MainLayout.js')) {
	echo '<script src="' . GO::view()->getTheme()->getUrl() . 'MainLayout.js" type="text/javascript"></script>';
	echo "\n";
}
?>
<script type="text/javascript">
<?php

//direct login with token
if(isset($_POST['accessToken'])) { //defined in index.php
	?>	
	go.User.setAccessToken('<?= $_POST['accessToken']; ?>', false);
	<?php

}

//these parameter are passed by dialog.php. These are used to directly link to
//a dialog.
if (isset($_REQUEST['f'])) {
	if (substr($_REQUEST['f'], 0, 9) == '{GOCRYPT}')
		$fp = Crypt::decrypt($_REQUEST['f']);
	else
		$fp = json_decode(base64_decode($_REQUEST['f']), true);

	GO::debug("External function parameters:");
	GO::debug($fp);
	?>
		if (GO.<?php echo $fp['m']; ?>)
		{
			GO.mainLayout.on("render", function () {
				GO.<?php echo $fp['m']; ?>.<?php echo $fp['f']; ?>.call(this, <?php echo json_encode($fp['p']); ?>);
			});
		}
	<?php
}
?>

	Ext.onReady(GO.mainLayout.boot, GO.mainLayout);
</script>
