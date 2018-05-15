<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: default_scripts.inc.php 21274 2017-07-05 11:37:23Z devdevilnl $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

$settings['state_index'] = 'go';

$settings['language']=\GO::language()->getLanguage();

$user_id = \GO::user() ? \GO::user()->id : 0;

$load_modules = \GO::modules()->getAllModules(true);

$settings['state']=array();
if(\GO::user()) {
	//state for Ext components
	$settings['html_editor_font']=\GO::config()->html_editor_font;
	$settings['state'] = \GO\Base\Model\State::model()->getFullClientState($user_id);
	$settings['user_id']=$user_id;	
	$settings['has_admin_permission']=\GO::user()->isAdmin();	
	$settings['username'] = \GO::user()->username;
	$settings['name'] = \GO::user()->name;
	
	$settings['email'] = \GO::user()->email;
	$settings['thousands_separator'] = \GO::user()->thousands_separator;
	$settings['decimal_separator'] = \GO::user()->decimal_separator;
	$settings['date_format'] = \GO::user()->completeDateFormat;
	$settings['date_separator'] = \GO::user()->date_separator;
	$settings['time_format'] = \GO::user()->time_format;
	$settings['currency'] = \GO::user()->currency;
	$settings['lastlogin'] = \GO::user()->lastlogin;
	$settings['max_rows_list'] = \GO::user()->max_rows_list;
	$settings['timezone'] = \GO::user()->timezone;
	$settings['start_module'] = \GO::user()->start_module;
	$settings['theme'] = \GO::user()->theme;
	$settings['mute_sound'] = \GO::user()->mute_sound;
	$settings['mute_reminder_sound'] = \GO::user()->mute_reminder_sound;
	$settings['mute_new_mail_sound'] = \GO::user()->mute_new_mail_sound;
	$settings['popup_reminders'] = \GO::user()->popup_reminders;
	$settings['popup_emails'] = \GO::user()->popup_emails;
	$settings['show_smilies'] = \GO::user()->show_smilies;
	$settings['auto_punctuation'] = \GO::user()->auto_punctuation;
	$settings['first_weekday'] = \GO::user()->first_weekday;
	$settings['sort_name'] = \GO::user()->sort_name;
	$settings['list_separator'] = \GO::user()->list_separator;
	$settings['text_separator'] = \GO::user()->text_separator;
	
}

$settings['pspellSupport']=function_exists('pspell_new') && !empty(\GO::config()->spell_check_enabled);
	
//
//require_once(\GO::config()->root_path.'classes/base/theme.class.inc.php');
//$GLOBALS['GO_THEME'] = new GO_THEME();

//$settings['modules']=$GLOBALS['GO_MODULES']->modules;
//$settings['config']['theme_url']=\GO::user()->theme;
$settings['config']['theme']=\GO::config()->theme;
$settings['config']['product_name']=\GO::config()->product_name;

$settings['config']['host']=\GO::config()->host;
$settings['config']['title']=\GO::config()->title;

//these were removed for security reasons
//$settings['config']['product_version']=\GO::config()->version;
//$settings['config']['webmaster_email']=\GO::config()->webmaster_email;

$settings['config']['full_url']=\GO::config()->full_url;

$settings['config']['allow_password_change']=\GO::config()->allow_password_change;
$settings['config']['allow_themes']=\GO::config()->allow_themes;
$settings['config']['allow_profile_edit']=\GO::config()->allow_profile_edit;

$settings['config']['max_users']=\GO::config()->max_users;

$settings['config']['debug']=\GO::config()->debug;
$settings['config']['max_attachment_size']=\GO::config()->max_attachment_size;
$settings['config']['max_file_size']=\GO::config()->max_file_size;
$settings['config']['help_link']=\GO::config()->help_link;
$settings['config']['support_link']=\GO::config()->support_link;
$settings['config']['report_bug_link']=\GO::config()->report_bug_link;
$settings['config']['nav_page_size']=intval(\GO::config()->nav_page_size);
$settings['config']['session_inactivity_timeout']=intval(\GO::config()->session_inactivity_timeout);

$settings['config']['tickets_no_email_required']=GO::config()->tickets_no_email_required;

$settings['config']['default_country'] = \GO::config()->default_country;
$settings['config']['checker_interval'] = (int)\GO::config()->checker_interval;

$settings['config']['remember_login'] = \GO::config()->remember_login;

$settings['show_contact_cf_tabs'] = array();


$settings['config']['encode_callto_link']=\GO::config()->encode_callto_link;


$settings['config']['login_message']=\GO::config()->login_message;

if(GO::modules()->addressbook){
	// Add the addresslist tab to the global settings panel
	$settings['show_addresslist_tab'] = \GO::config()->get_setting('globalsettings_show_tab_addresslist');
	
	$addressListsForcedLimit = \GO::config()->addresslists_store_forced_limit;
	if($addressListsForcedLimit){
		$settings['addresslists_store_forced_limit'] = (int)$addressListsForcedLimit;
	}

	if(\GO::modules()->customfields){
		$settings['show_contact_cf_tabs'] = array();
		
		$tabsEnabledStmt = \GO\Users\Model\CfSettingTab::model()->find();
		$tabsEnabled = $tabsEnabledStmt->fetchAll(PDO::FETCH_COLUMN);
	
		// Add the contact customfield tabs to the global settings panel
		$contactClassName = \GO\Addressbook\Model\Contact::model()->className();
		$customfieldsCategories = \GO\Customfields\Model\Category::model()->findByModel($contactClassName);
		foreach($customfieldsCategories as $cfc){
			if(in_array($cfc->id, $tabsEnabled))
				$settings['show_contact_cf_tabs'][$cfc->id] = true;
		}
	}
}

$settings['upload_quickselect'] = GO::config()->upload_quickselect;

$root_uri = \GO::config()->debug ? \GO::config()->host : \GO::config()->root_path;
$view_root_uri = $root_uri.'views/Extjs3/';
$view_root_path = \GO::config()->root_path.'views/Extjs3/';

$scripts=array();
//important to load focus first
//$scripts[]=$view_root_uri.'javascript/focus.js';


if(\GO::config()->debug) {
	$scripts[]=$view_root_uri.'ext/adapter/ext/ext-base-debug.js';
	$scripts[]=$view_root_uri.'ext/ext-all-debug.js';
}else {
	$scripts[]=$view_root_uri.'ext/adapter/ext/ext-base.js';
	$scripts[]=$view_root_uri.'ext/ext-all.js';
}

$scripts[]=$view_root_uri.'javascript/namespaces.js';
?>
<script type="text/javascript">	
	//hide mask after 10s to display errors is necessary.
	setTimeout(function(){
		var loadMask = document.getElementById('loading-mask');
		var loading = document.getElementById('loading');
		if(loadMask)
			loadMask.style.display='none';
		
		if(loading)
			loading.style.display='none';
		
	},10000);
	
</script>

<script type="text/javascript">
	
	
	var BaseHref = '<?php echo \GO::config()->host; ?>';

	GO = {};
	GO.settings=<?php echo json_encode($settings); ?>;
	GO.calltoTemplate = '<?php echo \GO::config()->callto_template; ?>';
	
	
	
	GO.permissionLevels={
		read:10,
		create:20,
		write:30,
		writeAndDelete:40,
		manage:50		
	};

<?php
if(isset(\GO::session()->values['security_token'])){	
	echo 'GO.securityToken="'.\GO::session()->values['security_token'].'";';
}

if(isset($_REQUEST['SET_LANGUAGE']) && preg_match('/[a-z_]/', $_REQUEST['SET_LANGUAGE']))
	echo 'GO.loginSelectedLanguage="'.$_REQUEST['SET_LANGUAGE'].'";';

if(\GO::user()) {
	echo 'window.name="'.\GO::getId().'";';
}else
{
	echo 'window.name="groupoffice-login";';
}
?>
</script>
<?php

$cacheFolder = \GO::config()->getCacheFolder();

$extjsLang = \GO::t('extjs_lang');
if($extjsLang=='extjs_lang')
	$extjsLang = \GO::language()->getLanguage();
$file = 'base-'.md5($extjsLang.\GO::config()->mtime).'.js';
$path = $cacheFolder->path().'/'.$file;

if(\GO::config()->debug || !file_exists($path)) {
	echo "\n<!-- regenerated script -->\n";

	if(file_exists($view_root_path.'ext/src/locale/ext-lang-'.$extjsLang.'.js')) {
		$scripts[]=$view_root_uri.'ext/src/locale/ext-lang-'.$extjsLang.'.js';
	}

	require(\GO::config()->root_path.'language/languages.php');
	$fp=fopen($cacheFolder->path().'/languages.js','w');
	if(!$fp){
		die('Could not write to cache directory');
	}
	fwrite($fp, "GO.Languages=[];\n");
	
	foreach($languages as $code=>$language) {
		fwrite($fp,'GO.Languages.push(["'.$code.'","'.$language.'"]);');
	}	
	
	//Put all lang vars in js
	$language = new \GO\Base\Language();
	$l = $language->getAllLanguage();

	fwrite($fp,'GO.lang='.json_encode($l['base']['common']).';');
	fwrite($fp,'GO.lang.countries='.json_encode($l['base']['countries']).';');
	unset($l['base']);
	
	
	
	fclose($fp);
	if(!\GO::config()->debug){
		$scripts[]=$cacheFolder->path().'/languages.js';
	}else
	{
		$dynamic_debug_script=$cacheFolder->path().'/languages.js';		
		$scripts[]=\GO::url("core/compress", array('file'=>'languages.js', 'mtime'=>filemtime($dynamic_debug_script)));	
	}
	
	$data = file_get_contents(\GO::config()->root_path.'views/Extjs3/javascript/scripts.txt');
	$lines = explode("\n", $data);
	foreach($lines as $line) {
		if(!empty($line)) {
			$scripts[]=$root_uri.$line;
		}
	}
	
	if(!\GO::config()->debug) {
		$js='';
		foreach($scripts as $script) {
			//file_put_contents($path,"\n\n".file_get_contents($script),FILE_APPEND);
			$js .= "\n\n".file_get_contents($script);
		}
		
		if(\GO::config()->minify){
			$js = \GO\Base\Util\Minify\JSMin::minify($js);
		}

		file_put_contents($path, $js);
	}
}

if(!\GO::config()->debug) {
	$scripts=array();
	$scripts[]=\GO::url("core/compress", array('file'=>$file, 'mtime'=>filemtime($path)));	
}

foreach($scripts as $script) {
	echo '<script type="text/javascript" src="'.$script.'"></script>'."\n";
}
?>
<script type="text/javascript">
	if(typeof(Ext)=='undefined')
	{
		alert('Could not load the application javascripts. Check the "host" property in config.php and see if the "file_storage_path" folder and it\'s contents are writable');
	}
</script>
<?php

foreach($load_modules as $module) {
	if(file_exists($module->moduleManager->path().'logged_off_scripts.inc.php')) {
		require($module->moduleManager->path().'logged_off_scripts.inc.php');
	}
	if(file_exists($module->moduleManager->path().'views/Extjs3/logged_off_scripts.inc.php')) {
		require($module->moduleManager->path().'views/Extjs3/logged_off_scripts.inc.php');
	}
}

$scripts=array();
$modulesCacheStr=array();
foreach($load_modules as $module)
	if($module->permissionLevel) 
		$modulesCacheStr[]=$module->id.($module->permissionLevel>\GO\Base\Model\Acl::READ_PERMISSION ? '1' : '0');
	
$modulesCacheStr=md5(implode('-',$modulesCacheStr));

if(count($load_modules)) {
	
	$modLangPath =$cacheFolder->path().'/'.$settings['language'].'-'.$modulesCacheStr.'-module-languages.js';
	if(!file_exists($modLangPath) || \GO::config()->debug){
		$fp=fopen($modLangPath,'w');
		if(!$fp){
			die('Could not write to cache directory');
		}

		//Namespaces		
		$modules = \GO::modules()->getAllModules();

		while ($module=array_shift($modules)) {
			fwrite($fp, 'Ext.ns("GO.'.$module->id.'");');
		}

		//Put all lang vars in js
		$language = new \GO\Base\Language();
		$l = $language->getAllLanguage();
		unset($l['base']);
		

		fwrite($fp, 'if(GO.customfields){Ext.ns("GO.customfields.columns");Ext.ns("GO.customfields.types");}');
		foreach($l as $module=>$langVars){
			fwrite($fp,'GO.'.$module.'.lang='.json_encode($langVars).';');
		}
		fclose($fp);
	}
	
	if(!\GO::config()->debug){
		$scripts[]=$modLangPath;
	}else
	{		
		$scripts[]=\GO::url("core/compress", array('file'=>basename($modLangPath), 'mtime'=>filemtime($modLangPath)));
	}


	foreach($load_modules as $module) {
		if($module->permissionLevel) {
			if(file_exists($module->moduleManager->path().'prescripts.inc.php')) {
				require($module->moduleManager->path().'prescripts.inc.php');
			}
			if(file_exists($module->moduleManager->path().'views/Extjs3/prescripts.inc.php')) {
				require($module->moduleManager->path().'views/Extjs3/prescripts.inc.php');
			}
		}
	}


	$modules=array();
	foreach($load_modules as $module) {
		if($module->permissionLevel) {

//			$module_uri = \GO::config()->debug ? $module->moduleManager->url() : $module->moduleManager->path();
			
			$scriptsFile = $module->moduleManager->path().'scripts.txt';
			if(!file_exists($scriptsFile))
						$scriptsFile = $module->moduleManager->path().'views/Extjs3/scripts.txt';	

			if(file_exists($scriptsFile)) {
				$data = file_get_contents($scriptsFile);
				$lines = explode("\n", $data);
				foreach($lines as $line) {
					if(!empty($line)) {
						$scripts[]=$root_uri.$line;
					}
				}
			}

			$modules[]=$module->id.$module->permissionLevel;
		}
	}

	//two modules may include the same script
	$scripts = array_map('trim',$scripts);
	$scripts=array_unique($scripts);
	
	//include config file location because in some cases different URL's point to
	//the same database and this can break things if the settings are cached.
	$file = $user_id.'-'.md5(\GO::config()->mtime.\GO::config()->get_config_file().':'.\GO::language()->getLanguage().':'.$modulesCacheStr).'.js';
	$path = $cacheFolder->path().'/'.$file;
	
	
	if(!\GO::config()->debug) {
		if(!file_exists($path)) {
		
			$js='';
			file_put_contents($cacheFolder->path().'/'.$user_id.'-modules.js', 'GO.settings.modules = Ext.decode("'.addslashes(json_encode(\GO::view()->exportModules())).'");');
			array_unshift($scripts, $cacheFolder->path().'/'.$user_id.'-modules.js');


			foreach($scripts as $script) {
				//file_put_contents($path,"\n\n".file_get_contents($script),FILE_APPEND);
				$js .= "\n\n".file_get_contents($script);
			}
			
			if(\GO::config()->minify){
				$js = \GO\Base\Util\Minify\JSMin::minify($js);
			}
			
			file_put_contents($path, $js,FILE_APPEND);
		}
		
		
		
		
		$url=\GO::url("core/compress", array('file'=>$file, 'mtime'=>filemtime($path)));

		$scripts=array($url);

	}else
	{
		file_put_contents($cacheFolder->path().'/'.$user_id.'-modules.js', 'GO.settings.modules = Ext.decode("'.addslashes(json_encode(\GO::view()->exportModules())).'");');
		
		$url=\GO::url("core/compress", array('file'=>$user_id.'-modules.js', 'mtime'=>filemtime($cacheFolder->path().'/'.$user_id.'-modules.js')));		
		array_unshift($scripts, $url);
		
	}
	
	foreach($scripts as $script) {
		echo '<script type="text/javascript" src="'.$script.'"></script>'."\n";
	}

	/*
	 * The GO_SCRIPTS_JS variable can be filled with javascript code and will be
	 * executed when Group-Office loads for the first time.
	 * Modules can add stuff in their scripts.inc.php files.
	 */
	$GO_SCRIPTS_JS='';
	
	foreach($load_modules as $module) {
		if($module->permissionLevel) {
			if(file_exists($module->moduleManager->path().'scripts.inc.php')) {
				require($module->moduleManager->path().'scripts.inc.php');
			}
			if(file_exists($module->moduleManager->path().'views/Extjs3/scripts.inc.php')) {
				require($module->moduleManager->path().'views/Extjs3/scripts.inc.php');
			}
		}
	}

	$filename = $user_id.'-scripts.js';
	$path = $cacheFolder->path().'/'.$filename;

	if(!file_exists($path) || $GO_SCRIPTS_JS != file_get_contents($path)){
		file_put_contents($path, $GO_SCRIPTS_JS);
	}
	
	if(file_exists($path)){

		$url=\GO::url("core/compress", array('file'=>$filename, 'mtime'=>filemtime($path)));		
		echo '<script type="text/javascript" src="'.$url.'"></script>'."\n";
	}
}
?>
<script type="text/javascript">
Ext.BLANK_IMAGE_URL = '<?php echo \GO::config()->host; ?>views/Extjs3/ext/resources/images/default/s.gif';
Ext.state.Manager.setProvider(new GO.state.HttpProvider());
<?php
if(isset(\GO::session()->values['security_token']))		
	echo 'Ext.Ajax.extraParams={security_token:"'.\GO::session()->values['security_token'].'"};';

GO::router()->getController()->fireEvent('inlinescripts');
?>
</script>
<?php
if(file_exists(\GO::view()->getTheme()->getPath().'MainLayout.js')) {
	echo '<script src="'.\GO::view()->getTheme()->getUrl().'MainLayout.js" type="text/javascript"></script>';
	echo "\n";
}
?>
<script type="text/javascript">
<?php
//these parameter are passed by dialog.php. These are used to directly link to
//a dialog.
if(isset($_REQUEST['f']))
{
	if(substr($_REQUEST['f'],0,9)=='{GOCRYPT}')
		$fp = \GO\Base\Util\Crypt::decrypt($_REQUEST['f']);
	else
		$fp = json_decode(base64_decode($_REQUEST['f']),true);
	
	\GO::debug("External function parameters:");
	\GO::debug($fp);
	
	?>
	if(GO.<?php echo $fp['m']; ?>)
	{
		 GO.mainLayout.on("render", function(){
				GO.<?php echo $fp['m']; ?>.<?php echo $fp['f']; ?>.call(this, <?php echo json_encode($fp['p']); ?>);
		 });
	}
	<?php
	
}
?>
</script>