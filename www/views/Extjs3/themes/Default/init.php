<?php
//important to load focus first
//$this->registerClientScript('themes/Default/js/focus.js');
//$this->registerClientScript('ext/adapter/ext/ext-base-debug.js');
//$this->registerClientScript('ext/ext-all-debug.js');
//$this->registerClientScript('javascript/namespaces.js');
//
//
//$s = '
//var BaseHref = "'.$GLOBALS['GO_CONFIG']->host.'";
//
//GO = {};
//GO.settings='.json_encode($GLOBALS['GO_CONFIG']->get_client_settings()).';
//GO.calltoTemplate = "'.$GLOBALS['GO_CONFIG']->callto_template.'";
//
//GO.url = function(relativeUrl){
//	return BaseHref+"router.php?r="+relativeUrl
//}';
//$this->registerClientScript($s, 'inline');
//
////todo language theme based?
//$this->registerClientScript('language/common/en.js');
//$this->registerClientScript('modules/users/language/en.js');
//
//if ($GLOBALS['GO_LANGUAGE']->language != 'en') {
//	if (file_exists($GLOBALS['GO_CONFIG']->root_path . 'language/common/' . $GLOBALS['GO_LANGUAGE']->language . '.js')) {
//		$this->registerClientScript('language/common/' . $GLOBALS['GO_LANGUAGE']->language . '.js');
//	}
//
//	if (file_exists($GLOBALS['GO_CONFIG']->root_path . 'ext/src/locale/ext-lang-' . $lang['common']['extjs_lang'] . '.js')) {
//		$this->registerClientScript('ext/src/locale/ext-lang-' . $lang['common']['extjs_lang'] . '.js');
//	}
//
//	if (file_exists($GLOBALS['GO_CONFIG']->root_path . 'modules/users/language/' . $GLOBALS['GO_LANGUAGE']->language . '.js')) {
//		$this->registerClientScript('modules/users/language/' . $GLOBALS['GO_LANGUAGE']->language . '.js');
//	}
//}
//
//require($GLOBALS['GO_CONFIG']->root_path . 'language/languages.inc.php');
//$s = "GO.Languages=[];";
//
////fwrite($fp,'GO.Languages.push(["",GO.lang.userSelectedLanguage]);');
//foreach ($languages as $code => $language) {
//	$s .= 'GO.Languages.push(["' . $code . '","' . $language . '"]);';
//}
//include($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));
//foreach ($countries as $key => $country) {
//	$s .= 'GO.lang.countries["' . $key . '"] = "' . $country . '";';
//}
//
//$this->registerClientScript($s, 'inline');
//
//
//
//
//$this->registerClientScript('themes/Default/js/overrides.js');
//$this->registerClientScript('themes/Default/js/ModuleManager.js');
//$this->registerClientScript('themes/Default/js/Date.js');
//$this->registerClientScript('themes/Default/js/DeployJava.js');
//$this->registerClientScript('themes/Default/js/MainLayout.js');
//$this->registerClientScript('themes/Default/js/ColumnFitLayout.js');
//$this->registerClientScript('themes/Default/js/windows/Window.js');
//$this->registerClientScript('themes/Default/js/windows/PasswordDialog.js');
//$this->registerClientScript('themes/Default/js/DialogListeners.js');
//$this->registerClientScript('themes/Default/js/JsonMenu.js');
//$this->registerClientScript('themes/Default/js/SmallPagingToolbar.js');
//
//
//$this->registerClientScript('themes/Default/js/WeekPicker.js');
//
//$this->registerClientScript('modules/users/TimeZones.js');
//$this->registerClientScript('themes/Default/js/windows/PersonalSettingsDialog.js');
//$this->registerClientScript('modules/users/RegionalSettingsPanel.js');
//$this->registerClientScript('modules/users/LookAndFeelPanel.js');
//$this->registerClientScript('modules/users/PasswordPanel.js');
//$this->registerClientScript('modules/users/Settings.js');
//$this->registerClientScript('modules/users/ProfilePanel.js');
//$this->registerClientScript('modules/users/PersonalPanel.js');
//$this->registerClientScript('modules/users/CompanyPanel.js');
//
//$this->registerClientScript('themes/Default/js/windows/AboutDialog.js');
//$this->registerClientScript('themes/Default/js/windows/LoginDialog.js');
//
//$this->registerClientScript('themes/Default/js/data/JsonStore.js');
//$this->registerClientScript('themes/Default/js/grids/GridPanel.js');
//$this->registerClientScript('themes/Default/js/grids/MultiSelectGrid.js');
//$this->registerClientScript('themes/Default/js/grids/GroupSummary.js');
//$this->registerClientScript('themes/Default/js/grids/RemoteGridTotals.js');
//$this->registerClientScript('themes/Default/js/RecordsContextMenu.js');
//
//$this->registerClientScript('themes/Default/js/grids/SimpleSelectList.js');
//$this->registerClientScript('themes/Default/js/grids/CheckColumn.js');
//$this->registerClientScript('themes/Default/js/grids/RowAction.js');
//$this->registerClientScript('themes/Default/js/grids/RadioColumn.js');
//
//$this->registerClientScript('themes/Default/js/common.js');
//$this->registerClientScript('themes/Default/js/blinkTitle.js');
//$this->registerClientScript('themes/Default/js/state/HttpProvider.js');
//
//$this->registerClientScript('themes/Default/js/plugins/PanelCollapsedTitle.js');
//$this->registerClientScript('themes/Default/js/plugins/DataView.js');
//$this->registerClientScript('themes/Default/js/plugins/InsertAtCursorTextarea.js');
//$this->registerClientScript('themes/Default/js/plugins/HtmlEditorImageInsert.js');
//$this->registerClientScript('themes/Default/js/plugins/HtmlEditorSpellCheck.js');
//$this->registerClientScript('themes/Default/js/plugins/Ext.ux.HtmlEditor.Plugins-0.2-all.js');
//$this->registerClientScript('themes/Default/js/panels/DisplayPanel.js');
//$this->registerClientScript('themes/Default/js/panels/IframeComponent.js');
//$this->registerClientScript('themes/Default/js/windows/SelectGroups.js');
//$this->registerClientScript('themes/Default/js/windows/SelectUsers.js');
//$this->registerClientScript('themes/Default/js/windows/SelectEmail.js');
//$this->registerClientScript('themes/Default/js/windows/ErrorDialog.js');
//
//$this->registerClientScript('themes/Default/js/form/FieldHelp.js');
//$this->registerClientScript('themes/Default/js/form/TreeSelect.js');
//$this->registerClientScript('themes/Default/js/form/SearchField.js');
//$this->registerClientScript('themes/Default/js/form/Combo.js');
//$this->registerClientScript('themes/Default/js/form/ColorField.js');
//$this->registerClientScript('themes/Default/js/form/ComboReset.js');
//$this->registerClientScript('themes/Default/js/form/ComboBoxMulti.js');
//$this->registerClientScript('themes/Default/js/form/UploadFile.js');
//$this->registerClientScript('themes/Default/js/form/PlainField.js');
//$this->registerClientScript('themes/Default/js/form/SelectUser.js');
//$this->registerClientScript('themes/Default/js/form/SelectGroup.js');
//$this->registerClientScript('themes/Default/js/form/SelectCountry.js');
//$this->registerClientScript('themes/Default/js/form/SelectAddressFormat.js');
//$this->registerClientScript('themes/Default/js/form/FileUploadField.js');
//$this->registerClientScript('themes/Default/js/form/TriggerIdField.js');
//$this->registerClientScript('themes/Default/js/form/SuperBoxSelect.js');
//$this->registerClientScript('themes/Default/js/form/SelectPriority.js');
//$this->registerClientScript('themes/Default/js/form/XCheckbox.js');
//
//$this->registerClientScript('themes/Default/js/form/HtmlComponent.js');
//$this->registerClientScript('themes/Default/js/form/NumberField.js');
//$this->registerClientScript('themes/Default/js/panels/PermissionsPanel.js');
//
//$this->registerClientScript('themes/Default/js/NewMenuButton.js');
//
//$this->registerClientScript('themes/Default/js/links/LinkFolderWindow.js');
//$this->registerClientScript('themes/Default/js/links/SelectLink.js');
//$this->registerClientScript('themes/Default/js/links/LinksTree.js');
//$this->registerClientScript('themes/Default/js/links/LinksGrid.js');
//$this->registerClientScript('themes/Default/js/links/LinksDialog.js');
//$this->registerClientScript('themes/Default/js/links/LinksPanel.js');
//$this->registerClientScript('themes/Default/js/links/LinksTemplate.js');
//$this->registerClientScript('themes/Default/js/links/LinksContextMenu.js');
//$this->registerClientScript('themes/Default/js/links/LinkBrowser.js');
//$this->registerClientScript('themes/Default/js/links/LinkViewWindow.js');
//$this->registerClientScript('themes/Default/js/links/LinkTypeFilterPanel.js');
//$this->registerClientScript('themes/Default/js/links/LinkDescriptionField.js');
//$this->registerClientScript('themes/Default/js/links/LinksAccordion.js');
//
//$this->registerClientScript('themes/Default/js/panels/SearchPanel.js');
//$this->registerClientScript('themes/Default/js/checker.js');
//
//$this->registerClientScript('themes/Default/js/grids/RowExpander.js');
//
//$this->registerClientScript('themes/Default/js/panels/GroupTab.js');
//$this->registerClientScript('themes/Default/js/panels/GroupTabPanel.js');
//$this->registerClientScript('themes/Default/js/panels/Portal.js');
//$this->registerClientScript('themes/Default/js/panels/PortalColumn.js');
//$this->registerClientScript('themes/Default/js/panels/Portlet.js');
//
//$this->registerClientScript('themes/Default/js/SWFObject/swfobject.js');
//$this->registerClientScript('themes/Default/js/SWFUpload/swfupload.js');
//$this->registerClientScript('themes/Default/js/SWFUpload/SwfUploadPanel.js');
//$this->registerClientScript('themes/Default/js/SWFUpload/UploadFlashDialog.js');
//$this->registerClientScript('themes/Default/js/form/UploadPCForm.js');
//$this->registerClientScript('themes/Default/js/Export.js');
//
//$this->registerClientScript('themes/Default/js/advancedquery/AdvancedQueryPanel.js');
//$this->registerClientScript('themes/Default/js/advancedquery/QueryPanel.js');
//$this->registerClientScript('themes/Default/js/advancedquery/SavedQueriesGrid.js');
//
//$this->registerClientScript('themes/Default/js/windows/UsersInGroup.js');
//
//
////CSS
$this->registerCssFile($GLOBALS['GO_CONFIG']->root_path.'ext/resources/css/ext-all.css');
$this->registerCssFile($GLOBALS['GO_CONFIG']->root_path.'themes/Default/xtheme-groupoffice.css');
$this->registerCssFile($GLOBALS['GO_CONFIG']->root_path.'themes/Default/style.css');

$this->loadModuleStylesheets();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="x-ua-compatible" content="IE=8">
			<meta name="robots" content="noindex" />
			<link href="<?php echo $GLOBALS['GO_CONFIG']->theme_url; ?>Default/images/groupoffice.ico?" rel="shortcut icon" type="image/x-icon">
				<title><?php echo $GLOBALS['GO_CONFIG']->title; ?></title>
				<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
				<meta name="description" content="Take your office online. Share projects, calendars, files and e-mail online with co-workers and clients. Easy to use and fully customizable, Group-Office takes online colaboration to the next level." />

				

<?php
echo $this->getCachedCss();
$GLOBALS['GO_EVENTS']->fire_event('head');

if (isset($GLOBALS['GO_MODULES']->modules['customcss']) && file_exists($GLOBALS['GO_CONFIG']->file_storage_path . 'customcss/style.css'))
	echo '<style>' . file_get_contents($GLOBALS['GO_CONFIG']->file_storage_path . 'customcss/style.css') . '</style>' . "\n";
?>

				</head>
				<body>
					<div id="loading-mask" style="width:100%;height:100%;background:#f1f1f1;position:absolute;z-index:20000;left:0;top:0;">&#160;</div>
					<div id="loading">
						<div class="loading-indicator">
							<img src="<?php echo $GLOBALS['GO_CONFIG']->host; ?>ext/resources/images/default/grid/loading.gif" style="width:16px;height:16px;vertical-align:middle" />&#160;<span id="load-status"><?php echo $lang['common']['loadingCore']; ?></span>
							<div id="copyright" style="font-size:10px; font-weight:normal;margin-top:15px;">Copyright &copy; <?php
				if ($GLOBALS['GO_CONFIG']->product_name != 'Group-Office') {
					echo $GLOBALS['GO_CONFIG']->product_name;
				} else {
					echo 'Intermesh BV';
				}
?> 2003-<?php echo date('Y'); ?></div>
						</div>
					</div>
					<!-- include everything after the loading indicator -->


								<?php
								require($GLOBALS['GO_CONFIG']->root_path . 'default_scripts.inc.php');

								/*
								 * If we don't have a name of the user then we don't open Group-Office yet. The login dialog will ask to complete the 
								 * profile. This typically happens when IMAP authentication is used. A user without a name is added.
								 * 
								 * When $popup_groupoffice is set in /default_scripts.inc.php we need to display the login dialog and launch GO in a popup.
								 */

								if ($GLOBALS['GO_SECURITY']->logged_in() && trim($_SESSION['GO_SESSION']['name']) != '' && !isset($popup_groupoffice)) {
									?>
						<div id="mainNorthPanel">
							<div id="headerLeft">
						<?php echo $lang['common']['loggedInAs'] . ' ' . htmlspecialchars($_SESSION['GO_SESSION']['name']); ?>
							</div>
							<div id="headerRight">

								<span id="notification-area">				
								</span>			

								<img id="reminder-icon" src="<?php echo $GLOBALS['GO_CONFIG']->host; ?>views/Extjs3/themes/Default/images/16x16/reminders.png" style="border:0;vertical-align:middle;cursor:pointer" />
								<!-- <img id="checker-icon" src="<?php echo $GLOBALS['GO_CONFIG']->host; ?>ext/resources/images/default/grid/loading.gif" style="border:0;vertical-align:middle" /> -->

	<?php
	if (isset($GLOBALS['GO_MODULES']->modules['search']) && $GLOBALS['GO_MODULES']->modules['search']['read_permission']) {
		//echo '<img src="'.$GLOBALS['GO_CONFIG']->host.'themes/Default/images/16x16/icon-search.png" style="border:0px;margin-left:10px;margin-right:1px;vertical-align:middle" />';
	}
	?>

								<span id="search_query"></span>

								<a id="start-menu-link" href="#"><?php echo $lang['common']['startMenu']; ?></a>

								<span class="top-menu-separator">|</span>

								<a href="#" id="configuration-link">
	<?php echo $lang['common']['settings']; ?></a>

								<span class="top-menu-separator">|</span>

								<a href="#" id="help-link">
									<?php echo $lang['common']['help']; ?></a>

	<?php if (!$GLOBALS['GO_SECURITY']->http_authenticated_session) { ?>
									<span class="top-menu-separator">|</span>
									<a href="javascript:GO.mainLayout.logout();">
										<?php echo $lang['common']['logout']; ?></a>
									<?php } ?>
							</div>
						</div>

						<script type="text/javascript">Ext.get("load-status").update("<?php echo $lang['common']['loadingModules']; ?>");</script>	
						<script type="text/javascript">
							/*window.onbeforeunload=function(){
							return "<?php echo addslashes($lang['common']['confirm_leave']); ?>";
						};*/

							Ext.onReady(GO.mainLayout.init, GO.mainLayout);
						</script>
	<?php
} else {
	?>

						<div id="go-powered-by" style="position:absolute;right:10px;bottom:10px">
		Powered by Group-Office: <a target="_blank" class="normal-link" href="http://www.group-office.com">http://www.group-office.com</a>
						</div>

						<div id="checker-icon"></div>
						<script type="text/javascript">Ext.get("load-status").update("<?php echo $lang['common']['loadingLogin']; ?>");</script>
						<script type="text/javascript">	
						<?php
						//set in /default_scripts.inc.php
						if (isset($popup_groupoffice)) {
							echo 'Ext.onReady(function(){
							GO.mainLayout.login();
							GO.mainLayout.launchFullscreen("' . $popup_groupoffice . '");
						}, GO.mainLayout.login);';
						} else {
							echo 'Ext.onReady(GO.mainLayout.login, GO.mainLayout);';
						}
						?>
						</script>


	<?php
}

if ($GLOBALS['GO_SECURITY']->logged_in() && empty($_SESSION['GO_SESSION']['mute_sound'])) {
	?>
						<object width="0" height="0" id="alarmSound">
							<param name="movie" value="<?php echo $GLOBALS['GO_THEME']->theme_url; ?>reminder.swf" />
							<param name="loop" value="false" />
							<param name="autostart" value="false" />
							<embed src="<?php echo $GLOBALS['GO_THEME']->theme_url; ?>reminder.swf" autostart=false loop="false" width="0" height="0" name="alarmSound"></embed>
						</object>
						<?php
					}
					?>
				</body>
				</html>
