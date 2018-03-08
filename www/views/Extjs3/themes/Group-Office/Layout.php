<?php
require(\GO::view()->getTheme()->getPath().'header.php');
?>
<div id="sound"></div>
<div id="loading-mask" style="width:100%;height:100%;background:#f1f1f1;position:absolute;z-index:20000;left:0;top:0;">&#160;</div>
<div id="loading">
	<div class="loading-indicator">
	<img src="<?php echo \GO::config()->host; ?>views/Extjs3/ext/resources/images/default/grid/loading.gif" style="width:16px;height:16px;vertical-align:middle" />&#160;<span id="load-status"><?php echo \GO::t('loadingCore'); ?></span>
	<div id="copyright" style="font-size:10px; font-weight:normal;margin-top:15px;">Copyright &copy; <?php
	if(\GO::config()->product_name!='Group-Office'){
		echo \GO::config()->product_name;
	}else{ echo 'Intermesh BV';

	} ?> 2003-<?php echo date('Y'); ?></div>
	</div>
</div>
<!-- include everything after the loading indicator -->


<?php
require(\GO::config()->root_path.'views/Extjs3/default_scripts.inc.php');

/*
 * If we don't have a name of the user then we don't open Group-Office yet. The login dialog will ask to complete the 
 * profile. This typically happens when IMAP authentication is used. A user without a name is added.
 * 
 * When $popup_groupoffice is set in /default_scripts.inc.php we need to display the login dialog and launch GO in a popup.
 */
if(\GO::user())
{
	?>
	<div id="mainNorthPanel">
		<div id="go-header-left">
			<div id="go-logo"></div>
			<div id="go-logged-in-as"><?php echo \GO::t('loggedInAs').' '.htmlspecialchars(\GO::user()->name); ?></div>
		</div>
		<div id="go-header-right">
                    
			<div id="secondary-menu">
		
				<div id="quick-add-menu"><span style="clear:both;"></span></div>
				<span class="plus-sign" id="quick-add-menu-collapse"></span>
				<span id="notification-area"></span>	
                            
				<img id="reminder-icon" src="<?php echo \GO::config()->host; ?>views/Extjs3/themes/Default/images/16x16/reminders.png" style="border:0;vertical-align:middle;cursor:pointer" />
				
				<span id="search_query"></span>
				
				<a id="start-menu-link" href="#"><?php echo \GO::t('startMenu'); ?></a>

													<span class="top-menu-separator">|</span>
				
				<a href="#" id="configuration-link">
					<?php echo \GO::t('settings'); ?></a>

													<span class="top-menu-separator">|</span>
	                        
				<a href="#" id="help-link">
					<?php echo \GO::t('help'); ?></a>

				<span class="top-menu-separator">|</span>
				<a href="javascript:GO.mainLayout.logout();">
				<?php echo \GO::t('logout'); ?></a>
			</div>
  	</div>
	</div>
	
	<script type="text/javascript">Ext.get("load-status").update("<?php echo \GO::t('loadingModules'); ?>");</script>	
	<script type="text/javascript">
	Ext.onReady(GO.mainLayout.init, GO.mainLayout);
	</script>
<?php
}else
{

	?>

	<div id="go-powered-by" style="position:absolute;right:10px;bottom:10px">
	Powered by <?php echo \GO::config()->product_name; ?>
	<?php if(\GO::config()->product_name=='Group-Office'){ ?>
	: <a target="_blank" class="normal-link" href="http://www.group-office.com">http://www.group-office.com</a>
	<?php } ?>
	</div>

	<div id="checker-icon"></div>
	<script type="text/javascript">Ext.get("load-status").update("<?php echo \GO::t('loadingLogin'); ?>");</script>
	<script type="text/javascript">	
	Ext.onReady(GO.mainLayout.login, GO.mainLayout);
	</script>
	
	
	<?php	
}

require(\GO::view()->getTheme()->getPath().'footer.php');
